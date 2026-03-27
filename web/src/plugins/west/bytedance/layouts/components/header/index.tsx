/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import { Transition } from 'vue'
import '@/layouts/style/header.scss'
import MainAside from '$/west/bytedance/layouts/components/main-aside'

export default defineComponent({
  name: 'Header',
  setup() {
    const settingStore = useSettingStore()
    return () => {
      return (
        <Transition name="mine-header">
          {settingStore.showMineHeader() && (
            <div class="hidden lg:flex">
              <div class="w-full">
                { settingStore.getSettings('app').layout === 'mixed' && <MainAside /> }
              </div>
            </div>
          )}
        </Transition>
      )
    }
  },
})
