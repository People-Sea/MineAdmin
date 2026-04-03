<?php

declare(strict_types=1);

namespace App\Repository\Tenant;

use App\Model\Enums\User\Type;
use App\Model\Permission\User;
use App\Repository\IRepository;
use Hyperf\Collection\Arr;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Builder;

/**
 * @extends IRepository<User>
 */
final class TenantMemberRepository extends IRepository
{
    public function __construct(
        protected readonly User $model
    ) {}

    public function handleSearch(Builder $query, array $params): Builder
    {
        $projectModel = (new User())->projects()->getRelated();
        $tenantModel = (new User())->tenant()->getRelated();
        $roleModel = (new User())->roles()->getRelated();

        $query
            ->where('user_type', Type::USER)
            ->with([
                'tenant' => static function ($query) use ($tenantModel) {
                    $query->select([
                        $tenantModel->qualifyColumn('id'),
                        $tenantModel->qualifyColumn('name'),
                    ]);
                },
                'roles' => static function ($query) use ($roleModel) {
                    $query->select([
                        $roleModel->qualifyColumn('id'),
                        $roleModel->qualifyColumn('name'),
                        $roleModel->qualifyColumn('code'),
                    ]);
                },
                'projects' => static function ($query) use ($projectModel) {
                    $query->select([
                        $projectModel->qualifyColumn('id'),
                        $projectModel->qualifyColumn('name'),
                    ]);
                },
            ])
            ->when(Arr::get($params, 'tenant_id'), static function (Builder $query, mixed $tenantId): void {
                $query->where('tenant_id', (int) $tenantId);
            })
            ->when(Arr::get($params, 'name'), static function (Builder $query, mixed $name): void {
                $query->where('nickname', 'like', '%' . $name . '%');
            })
            ->when(Arr::get($params, 'username'), static function (Builder $query, mixed $username): void {
                $query->where('username', 'like', '%' . $username . '%');
            })
            ->when(Arr::get($params, 'email'), static function (Builder $query, mixed $email): void {
                $query->where('email', 'like', '%' . $email . '%');
            })
            ->when(Arr::get($params, 'phone'), static function (Builder $query, mixed $phone): void {
                $query->where('phone', 'like', '%' . $phone . '%');
            })
            ->when(Arr::get($params, 'role'), static function (Builder $query, mixed $role): void {
                $query->whereHas('roles', static function (Builder $query) use ($role): void {
                    $query->where('code', $role);
                });
            })
            ->when(Arr::exists($params, 'status'), static function (Builder $query) use ($params): void {
                $query->where('status', Arr::get($params, 'status'));
            })
            ->when(Arr::get($params, 'project_id'), static function (Builder $query, mixed $projectId): void {
                $query->whereHas('projects', static function (Builder $query) use ($projectId): void {
                    $query->whereKey((int) $projectId);
                });
            })
            ->orderByDesc('id');

        return $query;
    }

    public function handleItems(Collection $items): Collection
    {
        $assignableRoleCodes = array_map(
            'strval',
            (array) config('tenant.assignable_role_codes', ['TenantAdmin', 'Optimizer'])
        );

        return $items->map(static function (User $item) use ($assignableRoleCodes) {
            $projects = $item->projects ?? collect();
            $roleEntity = ($item->roles ?? collect())
                ->first(static fn ($role) => \in_array($role->code, $assignableRoleCodes, true));

            $item->setAttribute('tenant_name', (string) $item->tenant?->name);
            $item->setAttribute('name', $item->nickname);
            $item->setAttribute('project_ids', $projects->pluck('id')->values()->all());
            $item->setAttribute('project_names', $projects->pluck('name')->values()->all());
            $item->setAttribute('project_count', $projects->count());
            $item->setAttribute('role_id', $roleEntity?->id);
            $item->setAttribute('role', (string) $roleEntity?->code);
            $item->setAttribute('role_label', (string) $roleEntity?->name);

            return $item;
        });
    }
}
