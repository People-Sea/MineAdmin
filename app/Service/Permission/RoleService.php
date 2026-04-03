<?php

declare(strict_types=1);

namespace App\Service\Permission;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Permission\Role;
use App\Repository\Permission\MenuRepository;
use App\Repository\Permission\RoleRepository;
use App\Service\IService;
use Hyperf\Collection\Collection;

/**
 * @extends IService<Role>
 */
final class RoleService extends IService
{
    public function __construct(
        protected readonly RoleRepository $repository,
        protected readonly MenuRepository $menuRepository
    ) {}

    public function getRolePermission(int $id): Collection
    {
        return $this->findRoleOrFail($id)->menus()->get();
    }

    public function batchGrantPermissionsForRole(int $id, array $permissionsCode): void
    {
        $role = $this->findRoleOrFail($id);
        if (\count($permissionsCode) === 0) {
            $role->menus()->detach();
            return;
        }
        $role
            ->menus()
            ->sync(
                $this->menuRepository
                    ->list([
                        'code' => $permissionsCode,
                    ])
                    ->map(static fn ($item) => $item->id)
                    ->toArray()
            );
    }

    private function findRoleOrFail(int $id): Role
    {
        /** @var null|Role $role */
        $role = $this->repository->findById($id);
        if ($role === null) {
            throw new BusinessException(ResultCode::NOT_FOUND);
        }

        return $role;
    }
}
