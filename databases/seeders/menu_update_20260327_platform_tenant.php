<?php

declare(strict_types=1);
use App\Model\Permission\Menu;
use App\Model\Permission\Meta;
use Hyperf\Database\Seeders\Seeder;

class menu_update_20260327_platform_tenant extends Seeder
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
}
