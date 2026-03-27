<?php

declare(strict_types=1);

namespace App\Service\Tenant;

use App\Model\Enums\User\Type;
use App\Model\Permission\Role;
use App\Model\Permission\User;
use App\Model\Tenant;
use App\Model\TenantProject;
use App\Repository\Tenant\TenantRepository;
use App\Service\IService;
use Hyperf\Collection\Collection;
use Hyperf\DbConnection\Db;

/**
 * @extends IService<Tenant>
 */
final class TenantService extends IService
{
    public function __construct(
        protected readonly TenantRepository $repository
    ) {}

    public function options(): Collection
    {
        return $this->repository->list()->map(static function (Tenant $tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'code' => $tenant->code,
                'status' => $tenant->status,
            ];
        });
    }

    public function create(array $data): mixed
    {
        return Db::transaction(function () use ($data) {
            $adminData = [
                'name' => $data['admin_name'] ?? '',
                'username' => $data['admin_username'] ?? '',
                'email' => $data['admin_email'] ?? '',
                'phone' => $data['admin_phone'] ?? '',
                'password' => $data['admin_password'] ?? '',
            ];

            unset($data['admin_name'], $data['admin_username'], $data['admin_email'], $data['admin_phone'], $data['admin_password']);

            /** @var Tenant $tenant */
            $tenant = parent::create($data);
            $operatorId = (int) ($data['updated_by'] ?? $data['created_by'] ?? 0);

            /** @var TenantProject $project */
            $project = TenantProject::query()->create([
                'tenant_id' => $tenant->id,
                'name' => '主项目',
                'is_default' => 1,
                'status' => 1,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
                'remark' => '',
            ]);

            /** @var User $member */
            $member = User::query()->create([
                'username' => $adminData['username'],
                'user_type' => Type::USER,
                'tenant_id' => $tenant->id,
                'nickname' => $adminData['name'],
                'email' => $adminData['email'],
                'phone' => $adminData['phone'],
                'password' => $adminData['password'],
                'status' => 1,
                'last_project_id' => $project->id,
                'avatar' => '',
                'signed' => '',
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
                'remark' => '租户创建时自动生成的租户管理员',
            ]);
            $member->roles()->syncWithoutDetaching([
                $this->getRoleId(
                    (string) config('tenant.admin_role_code', 'TenantAdmin'),
                    '租户管理员'
                ),
            ]);

            $project->members()->sync([
                $member->id => [
                    'tenant_id' => $tenant->id,
                    'created_by' => $operatorId,
                    'updated_by' => $operatorId,
                ],
            ]);

            return $tenant;
        });
    }

    public function deleteById(mixed $id): int
    {
        return Db::transaction(function () use ($id) {
            $ids = is_array($id) ? $id : [$id];

            TenantProject::query()->whereIn('tenant_id', $ids)->get()->each(static function (TenantProject $project) {
                $project->delete();
            });

            User::query()
                ->where('user_type', Type::USER)
                ->whereIn('tenant_id', $ids)
                ->get()
                ->each(static function (User $member) {
                $member->delete();
            });

            return parent::deleteById($ids);
        });
    }

    private function getRoleId(string $code, string $name): int
    {
        /** @var Role $entity */
        $entity = Role::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'status' => 1,
                'sort' => 0,
                'created_by' => 0,
                'updated_by' => 0,
                'remark' => $name,
            ]
        );

        return (int) $entity->id;
    }
}
