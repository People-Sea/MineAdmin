<?php

declare(strict_types=1);

namespace App\Http;

use App\Exception\BusinessException;
use App\Http\Common\ResultCode;
use App\Model\Enums\User\Status;
use App\Model\Permission\User;
use App\Service\PassportService;
use App\Service\Permission\MenuService;
use App\Service\Permission\UserService;
use Hyperf\Context\Context;
use Lcobucci\JWT\Token\RegisteredClaims;
use Mine\Jwt\Traits\RequestScopedTokenTrait;

final class CurrentUser
{
    use RequestScopedTokenTrait;

    public function __construct(
        private readonly PassportService $service,
        private readonly UserService $userService,
        private readonly MenuService $menuService
    ) {}

    public static function ctxUser(): ?User
    {
        return Context::get('current_user');
    }

    public function user(): ?User
    {
        if (Context::has('current_user')) {
            return Context::get('current_user');
        }

        $user = $this->userService->getInfo($this->id());
        if ($user === null) {
            throw new BusinessException(ResultCode::UNAUTHORIZED, trans('jwt.unauthorized'));
        }

        Context::set('current_user', $user);
        return $user;
    }

    public function refresh(): array
    {
        return $this->service->refreshToken($this->getToken());
    }

    public function id(): int
    {
        return (int) $this->getToken()->claims()->get(RegisteredClaims::ID);
    }

    public function isSuperAdmin(): bool
    {
        return $this->user()?->isSuperAdmin() ?? false;
    }

    public function isTenantUser(): bool
    {
        return $this->user()?->isTenantUser() ?? false;
    }

    public function tenantId(): int
    {
        return (int) ($this->user()?->tenant_id ?? 0);
    }

    public function isTenantAdmin(): bool
    {
        return $this->user()?->isTenantAdmin() ?? false;
    }

    public function filterCurrentUser(): array
    {
        $user = $this->user();
        $permissions = $user
            ->getPermissions()
            ->pluck('name')
            ->unique();
        if ($user->isTenantUser()) {
            $allowedNames = array_map('strval', (array) config('tenant.allowed_menu_names', []));
            $allowedPrefixes = array_map('strval', (array) config('tenant.allowed_menu_prefixes', []));
            $permissions = $permissions
                ->filter(static function ($name) use ($allowedNames, $allowedPrefixes) {
                    $name = (string) $name;
                    if (in_array($name, $allowedNames, true)) {
                        return true;
                    }

                    foreach ($allowedPrefixes as $prefix) {
                        if (str_starts_with($name, $prefix)) {
                            return true;
                        }
                    }

                    return false;
                })
                ->values();
        }
        $menuList = $permissions->isEmpty()
            ? []
            : $this->menuService
                ->getList(['status' => Status::Normal, 'name' => $permissions->toArray()])
                ->toArray();
        $tree = [];
        $map = [];
        foreach ($menuList as &$menu) {
            $menu['children'] = [];
            $map[$menu['id']] = &$menu;
        }
        unset($menu);
        foreach ($menuList as &$menu) {
            $pid = $menu['parent_id'];
            if ($pid === 0 || ! isset($map[$pid])) {
                $tree[] = &$menu;
            } else {
                $map[$pid]['children'][] = &$menu;
            }
        }
        unset($menu);
        return $tree;
    }
}
