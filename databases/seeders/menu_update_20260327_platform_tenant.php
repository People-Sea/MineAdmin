<?php

declare(strict_types=1);
use App\Model\Permission\Menu;
use App\Model\Permission\Meta;
use App\Model\Permission\Role;
use Hyperf\Database\Seeders\Seeder;

class MenuUpdate20260327PlatformTenant extends Seeder
{
    public const BASE_DATA = [
        'path' => '',
        'component' => '',
        'redirect' => '',
        'created_by' => 0,
        'updated_by' => 0,
        'remark' => '',
        'status' => 1,
        'sort' => 0,
    ];

    public function run(): void
    {
        $this->create($this->data());
        $this->syncRoleMenus();
    }

    public function data(): array
    {
        return [
            [
                'name' => 'platform',
                'path' => '/platform',
                'meta' => new Meta([
                    'title' => '平台管理',
                    'icon' => 'ri:building-line',
                    'type' => 'M',
                    'hidden' => 0,
                    'componentPath' => 'modules/',
                    'componentSuffix' => '.vue',
                    'breadcrumbEnable' => 1,
                    'copyright' => 1,
                    'cache' => 1,
                    'affix' => 0,
                ]),
                'children' => [
                    [
                        'name' => 'platform:tenant',
                        'path' => '/platform/tenant',
                        'component' => 'base/views/platform/tenant/index',
                        'meta' => new Meta([
                            'title' => '租户管理',
                            'icon' => 'ri:building-2-line',
                            'type' => 'M',
                            'hidden' => 0,
                            'componentPath' => 'modules/',
                            'componentSuffix' => '.vue',
                            'breadcrumbEnable' => 1,
                            'copyright' => 1,
                            'cache' => 1,
                            'affix' => 0,
                        ]),
                        'children' => [
                            [
                                'name' => 'platform:tenant:index',
                                'meta' => new Meta([
                                    'title' => '租户列表',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant:save',
                                'meta' => new Meta([
                                    'title' => '租户保存',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant:update',
                                'meta' => new Meta([
                                    'title' => '租户更新',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant:delete',
                                'meta' => new Meta([
                                    'title' => '租户删除',
                                    'type' => 'B',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'config',
                'path' => '/config',
                'meta' => new Meta([
                    'title' => '配置管理',
                    'icon' => 'ri:settings-3-line',
                    'type' => 'M',
                    'hidden' => 0,
                    'componentPath' => 'modules/',
                    'componentSuffix' => '.vue',
                    'breadcrumbEnable' => 1,
                    'copyright' => 1,
                    'cache' => 1,
                    'affix' => 0,
                ]),
                'children' => [
                    [
                        'name' => 'platform:tenant-member',
                        'path' => '/config/member',
                        'component' => 'base/views/platform/tenant-member/index',
                        'meta' => new Meta([
                            'title' => '团队人员管理',
                            'icon' => 'material-symbols:group-outline-rounded',
                            'type' => 'M',
                            'hidden' => 0,
                            'componentPath' => 'modules/',
                            'componentSuffix' => '.vue',
                            'breadcrumbEnable' => 1,
                            'copyright' => 1,
                            'cache' => 1,
                            'affix' => 0,
                        ]),
                        'children' => [
                            [
                                'name' => 'platform:tenant-member:index',
                                'meta' => new Meta([
                                    'title' => '租户成员列表',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant-member:save',
                                'meta' => new Meta([
                                    'title' => '租户成员保存',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant-member:update',
                                'meta' => new Meta([
                                    'title' => '租户成员更新',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant-member:delete',
                                'meta' => new Meta([
                                    'title' => '租户成员删除',
                                    'type' => 'B',
                                ]),
                            ],
                        ],
                    ],
                    [
                        'name' => 'platform:tenant-project',
                        'path' => '/config/project',
                        'component' => 'base/views/platform/tenant-project/index',
                        'meta' => new Meta([
                            'title' => '团队项目管理',
                            'icon' => 'material-symbols:folder-managed-outline-rounded',
                            'type' => 'M',
                            'hidden' => 0,
                            'componentPath' => 'modules/',
                            'componentSuffix' => '.vue',
                            'breadcrumbEnable' => 1,
                            'copyright' => 1,
                            'cache' => 1,
                            'affix' => 0,
                        ]),
                        'children' => [
                            [
                                'name' => 'platform:tenant-project:index',
                                'meta' => new Meta([
                                    'title' => '租户项目列表',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant-project:save',
                                'meta' => new Meta([
                                    'title' => '租户项目保存',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant-project:update',
                                'meta' => new Meta([
                                    'title' => '租户项目更新',
                                    'type' => 'B',
                                ]),
                            ],
                            [
                                'name' => 'platform:tenant-project:delete',
                                'meta' => new Meta([
                                    'title' => '租户项目删除',
                                    'type' => 'B',
                                ]),
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function create(array $data, int $parentId = 0): void
    {
        foreach ($data as $item) {
            $current = $item;
            $children = $current['children'] ?? [];
            unset($current['children']);

            $menu = Menu::query()->firstOrNew(['name' => $current['name']]);
            $menu->fill(array_merge(self::BASE_DATA, $current, ['parent_id' => $parentId]));
            $menu->save();

            if ($children !== []) {
                $this->create($children, $menu->id);
            }
        }
    }

    private function syncRoleMenus(): void
    {
        $tenantAdmin = $this->firstOrCreateRole('TenantAdmin', '租户管理员');
        $optimizer = $this->firstOrCreateRole('Optimizer', '优化师');

        $tenantAdmin->menus()->sync($this->menuIds([
            'config',
            'platform:tenant-member',
            'platform:tenant-member:index',
            'platform:tenant-member:save',
            'platform:tenant-member:update',
            'platform:tenant-member:delete',
            'platform:tenant-project',
            'platform:tenant-project:index',
            'platform:tenant-project:save',
            'platform:tenant-project:update',
            'platform:tenant-project:delete',
            'log',
            'log:userLogin',
            'log:userLogin:list',
            'log:userLogin:delete',
            'log:userOperation',
            'log:userOperation:list',
            'log:userOperation:delete',
        ]));

        $optimizer->menus()->sync($this->menuIds([
            'config',
            'platform:tenant-project',
            'platform:tenant-project:index',
        ]));
    }

    private function firstOrCreateRole(string $code, string $name): Role
    {
        /** @var Role $role */
        $role = Role::query()->firstOrCreate(
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

        return $role;
    }

    /**
     * @param string[] $names
     * @return int[]
     */
    private function menuIds(array $names): array
    {
        return Menu::query()
            ->whereIn('name', $names)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();
    }
}
