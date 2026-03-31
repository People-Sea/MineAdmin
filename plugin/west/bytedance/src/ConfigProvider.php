<?php

/*
 * @Date: 2024-10-23 11:58:44
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-10-23 12:02:33
 * @FilePath: /MineAdmin/plugin/west/bytedance/src/ConfigProvider.php
 */

declare(strict_types=1);

namespace Plugin\West\Bytedance;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 合并到  config/autoload/annotations.php 文件
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
