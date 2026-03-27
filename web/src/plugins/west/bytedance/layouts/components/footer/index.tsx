/*
 * @Date: 2024-10-18 11:19:45
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-10-23 10:10:03
 * @FilePath: /MineAdmin/web/src/plugins/west/bytedance/layouts/components/footer/index.tsx
 */
/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import { useBytedanceStore } from '@/plugins/west/bytedance/utils/bytedanceStore'
import '@/layouts/style/footer.scss'

export default defineComponent({
  name: 'Footer',
  setup() {
    const settingStore = useSettingStore()
    const footerSetting = settingStore.getSettings('copyright')
    const route = useRoute()
    const bytedanceStore = useBytedanceStore()

    return () => (
      <footer>
        {
          ((footerSetting.enable && route.meta?.copyright === true) && route.meta?.type !== 'I')
          && (
            <div class="mine-footer">
              <span>{bytedanceStore.settings.slogan ?? null}</span>
            </div>
          )
        }
      </footer>
    )
  },
})
