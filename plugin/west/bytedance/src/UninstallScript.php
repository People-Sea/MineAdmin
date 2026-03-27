<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace Plugin\West\Bytedance;

class UninstallScript
{
    public function __invoke()
    {
        echo "正在卸载插件...\n";
        echo "执行卸载插件的命令\n";

        // 禁用插件
        $this->disablePluginInIndexFile();
    }

    protected function disablePluginInIndexFile()
    {
        $indexPath = BASE_PATH . '/web/src/plugins/west/bytedance/index.ts';

        if (file_exists($indexPath)) {
            $content = file_get_contents($indexPath);
            // 使用正则表达式匹配 enable 配置项并将其设置为 false
            $updatedContent = preg_replace('/enable\s*:\s*true/', 'enable: false', $content);

            if ($updatedContent !== null) {
                file_put_contents($indexPath, $updatedContent);
                echo "已成功将插件启用状态设置为 false。\n";
            } else {
                echo "修改插件启用状态失败，请手动检查 index.ts 文件。\n";
            }
        } else {
            echo "index.ts 文件未找到。\n";
        }
    }
}
