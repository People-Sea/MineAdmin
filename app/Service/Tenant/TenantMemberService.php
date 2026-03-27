<?php

declare(strict_types=1);

namespace App\Service\Tenant;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Http\CurrentUser;
use App\Model\Enums\User\Type;
use App\Model\Permission\Role;
use App\Model\Permission\User;
use App\Model\TenantProject;
use App\Repository\Tenant\TenantMemberRepository;
use App\Service\IService;
use App\Service\Permission\UserService;
use Hyperf\Collection\Collection;
use Hyperf\Collection\Enumerable;
use Hyperf\DbConnection\Db;

/**
 * @extends IService<User>
 */
final class TenantMemberService extends IService
{
    public function __construct(
        protected readonly TenantMemberRepository $repository,
        private readonly CurrentUser $currentUser,
        private readonly UserService $userService
    ) {}

    public function page(array $params, int $page = 1, int $pageSize = 10): array
    {
        return parent::page($this->applyScope($params), $page, $pageSize);
    }

    public function options(array $params = []): Enumerable
    {
        return $this->repository->list($this->applyScope($params))->map(static function (User $member) {
            return [
                'id' => $member->id,
                'tenant_id' => $member->tenant_id,
                'name' => $member->nickname,
                'username' => $member->username,
                'role_id' => $member->role_id,
                'role' => $member->role,
                'role_label' => $member->role_label,
                'status' => $member->status,
            ];
        });
    }

    public function roleOptions(): Collection
    {
        return Role::query()
            ->whereIn('code', $this->assignableRoleCodes())
            ->where('status', 1)
            ->orderBy('sort')
            ->orderBy('id')
            ->get(['id', 'name', 'code', 'remark'])
            ->map(static fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'code' => $role->code,
                'remark' => $role->remark,
            ]);
    }

    public function create(array $data): mixed
    {
        return Db::transaction(function () use ($data) {
            $this->ensureTenantAdminCanManage();

            $projectIds = $data['project_ids'] ?? null;
            $roleId = $this->resolveRoleId((int) $data['role_id']);
            unset($data['project_ids'], $data['role_id']);

            $data['tenant_id'] = $this->resolveTenantId($data['tenant_id'] ?? null);
            $data['user_type'] = Type::USER;
            $data['nickname'] = $data['name'] ?? '';
            unset($data['name'], $data['role']);

            /** @var User $member */
            $member = parent::create($data);
            $this->syncRole($member, $roleId);
            $this->syncProjects($member, \is_array($projectIds) ? $projectIds : null, (int) ($data['updated_by'] ?? $data['created_by'] ?? 0));
            $this->userService->syncLastProjectId($member);

            return $member;
        });
    }

    public function updateById(mixed $id, array $data): mixed
    {
        return Db::transaction(function () use ($id, $data) {
            $this->ensureTenantAdminCanManage();

            /** @var User $member */
            $member = $this->findTenantUserOrFail((int) $id);

            $this->ensureAdminStillExists($member, $data);

            $projectIds = $data['project_ids'] ?? null;
            unset($data['project_ids']);

            if (($data['password'] ?? '') === '') {
                unset($data['password']);
            }

            if (isset($data['tenant_id'])) {
                $data['tenant_id'] = $this->resolveTenantId($data['tenant_id']);
            }
            if (isset($data['name'])) {
                $data['nickname'] = $data['name'];
                unset($data['name']);
            }

            $roleId = isset($data['role_id']) ? $this->resolveRoleId((int) $data['role_id']) : null;
            unset($data['role_id']);

            $member->fill($data)->save();
            if ($roleId !== null) {
                $this->syncRole($member, $roleId);
            }
            $this->syncProjects($member, \is_array($projectIds) ? $projectIds : null, (int) ($data['updated_by'] ?? $data['created_by'] ?? 0));
            $this->userService->syncLastProjectId($member);
            return $member;
        });
    }

    public function deleteById(mixed $id): int
    {
        return Db::transaction(function () use ($id) {
            $this->ensureTenantAdminCanManage();
            $ids = \is_array($id) ? $id : [$id];
            $members = User::query()
                ->where('user_type', Type::USER)
                ->whereIn('id', $ids)
                ->when($this->currentUser->isTenantUser(), function ($query) {
                    $query->where('tenant_id', $this->currentUser->tenantId());
                })
                ->get();

            $members
                ->groupBy('tenant_id')
                ->each(static function (Collection $items, int|string $tenantId) {
                    $adminIds = $items->pluck('id')->all();
                    if ($adminIds === []) {
                        return;
                    }

                    $remaining = User::query()
                        ->where('tenant_id', (int) $tenantId)
                        ->where('user_type', Type::USER)
                        ->whereHas('roles', static function ($query) {
                            $query->where('code', (string) config('tenant.admin_role_code', 'TenantAdmin'));
                        })
                        ->whereNotIn('id', $adminIds)
                        ->count();

                    if ($remaining === 0) {
                        throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, '每个租户至少保留一个租户管理员');
                    }
                });

            $members->each(static function (User $member) {
                $member->delete();
            });

            return \count($ids);
        });
    }

    private function ensureAdminStillExists(User $member, array $data): void
    {
        $newRoleId = isset($data['role_id']) ? (int) $data['role_id'] : $this->resolveRoleIdFromUser($member);
        $newRole = $newRoleId === null ? null : $this->findAssignableRole($newRoleId);

        if (! $member->isTenantAdmin() || $newRole?->code === $this->tenantAdminRoleCode()) {
            return;
        }

        $remaining = User::query()
            ->where('tenant_id', $member->tenant_id)
            ->where('user_type', Type::USER)
            ->whereHas('roles', static function ($query) {
                $query->where('code', (string) config('tenant.admin_role_code', 'TenantAdmin'));
            })
            ->where('id', '!=', $member->id)
            ->count();

        if ($remaining === 0) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, '每个租户至少保留一个租户管理员');
        }
    }

    private function applyScope(array $params): array
    {
        if ($this->currentUser->isTenantUser()) {
            $params['tenant_id'] = $this->currentUser->tenantId();
        }

        return $params;
    }

    private function ensureTenantAdminCanManage(): void
    {
        if ($this->currentUser->isTenantUser() && ! $this->currentUser->isTenantAdmin()) {
            throw new BusinessException(ResultCode::FORBIDDEN);
        }
    }

    private function resolveTenantId(?int $tenantId): int
    {
        if ($this->currentUser->isTenantUser()) {
            return $this->currentUser->tenantId();
        }

        return (int) $tenantId;
    }

    private function findTenantUserOrFail(int $id): User
    {
        /** @var null|User $member */
        $member = User::query()
            ->whereKey($id)
            ->where('user_type', Type::USER)
            ->when($this->currentUser->isTenantUser(), function ($query) {
                $query->where('tenant_id', $this->currentUser->tenantId());
            })
            ->first();

        if ($member === null) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $member;
    }

    private function syncRole(User $member, int $roleId): void
    {
        $member->roles()->sync([$roleId]);
    }

    private function resolveRoleId(int $roleId): int
    {
        return (int) $this->findAssignableRole($roleId)->id;
    }

    private function resolveRoleIdFromUser(User $member): ?int
    {
        return $member->roles()
            ->whereIn('code', $this->assignableRoleCodes())
            ->orderBy('id')
            ->value('id');
    }

    private function findAssignableRole(int $roleId): Role
    {
        /** @var null|Role $role */
        $role = Role::query()
            ->whereKey($roleId)
            ->whereIn('code', $this->assignableRoleCodes())
            ->where('status', 1)
            ->first();

        if ($role === null) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, '所选角色不可分配给租户成员');
        }

        return $role;
    }

    /**
     * @return string[]
     */
    private function assignableRoleCodes(): array
    {
        return array_map(
            'strval',
            (array) config('tenant.assignable_role_codes', ['TenantAdmin', 'Optimizer'])
        );
    }

    private function tenantAdminRoleCode(): string
    {
        return (string) config('tenant.admin_role_code', 'TenantAdmin');
    }

    private function syncProjects(User $member, ?array $projectIds, int $operatorId): void
    {
        if ($projectIds === null) {
            return;
        }

        $validIds = TenantProject::query()
            ->where('tenant_id', $member->tenant_id)
            ->whereIn('id', $projectIds)
            ->pluck('id')
            ->map(static fn ($projectId) => (int) $projectId)
            ->all();

        if (\count($validIds) !== \count(array_unique($projectIds))) {
            throw new BusinessException(ResultCode::UNPROCESSABLE_ENTITY, '存在不属于当前租户的项目，无法分配给成员');
        }

        $syncData = [];
        foreach ($validIds as $projectId) {
            $syncData[$projectId] = [
                'tenant_id' => (int) $member->tenant_id,
                'created_by' => $operatorId,
                'updated_by' => $operatorId,
            ];
        }

        $member->projects()->sync($syncData);
    }
}
