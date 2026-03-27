/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import { useI18n } from 'vue-i18n'
import Message from 'vue-m-message'
import Quick from '../../../quick/index'
import { ElDivider } from 'element-plus'
import MineShortcutsDesc from './dropdownMenuComponents/shortcuts-desc.tsx'
import MineSystemInfo from './dropdownMenuComponents/system-info.tsx'
import moreIcon from '@/plugins/west/bytedance/assets/svg/more.svg'

export default defineComponent({
  name: 'UserBar',
  setup() {
    const {
      getMobileState,
    } = useSettingStore()
    const modules = import.meta.glob('@/components/Config.{js,ts,tsx,vue}')
    const bytedanceConfig = shallowRef<null | (() => JSX.Element)>(null)

    const loadBytedanceConfig = async () => {
      const modulePaths = Object.keys(modules)
      if (modulePaths.length > 0) {
        const firstModulePath = modulePaths[0]
        const mod = await modules[firstModulePath]() as { default: () => JSX.Element }
        bytedanceConfig.value = mod.default
      }
    }

    onMounted(() => {
      loadBytedanceConfig().then()
    })

    const userStore = useUserStore()
    const router = useRouter()
    const userInfo = userStore.getUserInfo()
    const { t } = useI18n()
    const spacer = h(ElDivider, { direction: 'vertical' })
    const elDivider = h(ElDivider, { direction: 'vertical' })
    const links: any[] = [
      {
        label: 'mineAdmin.userBar.uc',
        icon: 'material-symbols:account-circle-outline',
        handle: () => router.push({ path: '/uc' }),
      },
      {
        label: 'mineAdmin.userBar.clearCache',
        icon: 'mingcute:broom-line',
        handle: async () => {
          await userStore.clearCache()
          Message.success(t('mineAdmin.common.clearCache'))
        },
      },
      { label: 'divider' },
      {
        label: 'mineAdmin.userBar.shortcuts',
        icon: 'i-material-symbols:keyboard-keys',
        handle: () => userStore.setDropdownMenuState('shortcuts', true),
      },
      {
        label: 'mineAdmin.userBar.systemInfo',
        icon: 'i-bi:info-circle',
        handle: () => userStore.setDropdownMenuState('systemInfo', true),
      },
      { label: 'divider' },
      {
        label: 'mineAdmin.userBar.logout',
        icon: 'hugeicons:logout-04',
        handle: () => userStore.logout(),
      },
    ]

    return () => (
      <div class="mine-user-bar">
        <el-space spacer={elDivider}>
          <div class="index_header-user">
            <el-space spacer={spacer} class="!gap-0">
              <m-dropdown
                class="min-w-[6rem] p-1"
                triggers={['click']}
                v-slots={{
                  default: () => (
                    <div class="oc-userinfo cursor-pointer">
                      {userInfo.avatar && <img src={userInfo.avatar} alt={userInfo.username} class="mine-img-avatar" />}
                      {!userInfo.avatar && <div class="mine-text-avatar text-white !bg-[rgb(var(--ui-primary))] !text-3.5">{userInfo.username[0].toUpperCase()}</div>}
                      <a class="username hidden items-center text-[12px] lg:flex">
                        {userInfo.username}
                        <ma-svg-icon name="i-iconamoon:arrow-down-2-duotone" size={12} />
                      </a>
                    </div>
                  ),
                  popper: () => (
                    <div class="max-w-320px w-240px">
                      <m-dropdown-item
                        type="default"
                        v-slots={{
                          default: () => {
                            return (
                              <div class="flex gap-3">
                                <div>
                                  <el-avatar
                                    class="text-lg text-white !bg-[rgb(var(--ui-primary))]"
                                    src={
                                      userInfo.avatar && userInfo.avatar
                                    }
                                  >
                                    {!userInfo.avatar && userInfo.username[0].toUpperCase()}
                                  </el-avatar>
                                </div>
                                <div>
                                  <div class="text-4 font-bold">{userInfo.username}</div>
                                  <div class="text-gray">{userInfo.email}</div>
                                </div>
                              </div>
                            )
                          },
                        }}
                      />
                      <m-dropdown-divider />
                      {links.map((item: any) => (
                        <div>
                          {item.label !== 'divider' && (
                            <m-dropdown-item
                              type="default"
                              handle={item.handle}
                              class="group"
                              v-slots={{
                                'default': () => <span>{useTrans(item.label)}</span>,
                                'prefix-icon': () => <ma-svg-icon name={item.icon} size={18} />,
                                'suffix-icon': () => <ma-svg-icon class="text-gray opacity-0 transition-opacity duration-300 group-hover:opacity-100" name="i-ic:round-arrow-outward" size={14} />,
                              }}
                            />
                          )}
                          {item.label === 'divider' && <m-dropdown-divider />}
                        </div>
                      ))}
                    </div>
                  ),
                }}
              />
              {!getMobileState() && (
                bytedanceConfig.value && <bytedanceConfig.value />
              )}
            </el-space>
          </div>
          {/* 更多 */}
          {!getMobileState() && (
            <m-dropdown
              triggers={['click']}
              placement="bottom-end"
              v-slots={{
                default: () => (
                  <div class="brand-matrix-entrance">
                    <div class="brand-matrix-entrance-icon">
                      <el-image style="width: 16px; height: 16px" src={moreIcon} fit="cover" />
                    </div>
                  </div>
                ),
                popper: () => (
                  <div>
                    <Quick />
                  </div>
                ),
              }}
            />
          )}
        </el-space>
        <MineSystemInfo />
        <MineShortcutsDesc />
      </div>
    )
  },
})
