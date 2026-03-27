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
import '@/layouts/style/main-aside.scss'
import type { MineRoute } from '#/global'
import menuGotoHandle from '@/utils/menuGotoHandle'
import useParentNode from '@/hooks/useParentNode'

export default defineComponent ({
  name: 'MainAside',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const menuStore = useMenuStore()
    const { getSettings, showMineHeader, isBannerLayout, isMixedLayout, isColumnsLayout, getUserBarState, setUserBarState } = useSettingStore()
    const mainAsideSetting = getSettings('mainAside')

    const mainAsideRef = ref()
    const shadowTop = ref<boolean>(false)
    const shadowBottom = ref<boolean>(false)

    function onAsideScroll() {
      const scrollTop = mainAsideRef.value.scrollTop
      shadowTop.value = scrollTop > 0
      const clientHeight = mainAsideRef.value.clientHeight
      const scrollHeight = mainAsideRef.value.scrollHeight
      shadowBottom.value = Math.ceil(scrollTop + clientHeight) < scrollHeight
    }
    const asideListClass = computed(() => {
      return {
        'mine-main-aside-list': true,
        'shadow-top': shadowTop.value,
        'shadow-bottom': shadowBottom.value,
        'pt-2': !showMineHeader(),
        'flex gap-x-2 px-2 items-center': showMineHeader(),
      }
    })
    const goToAppoint = async (e: any, route: MineRoute.routeRecord) => {
      await menuGotoHandle(router, route)
      menuStore.activeTopMenu = route
      if (getSettings('mainAside').enableOpenFirstRoute || route?.children || !route?.meta?.hidden) {
        const aNode = useParentNode(e, 'a')
        document.querySelector('a.active')?.classList.remove('active')
        aNode.classList.add('active')
      }
    }
    return () => {
      return (
        <Transition name={isBannerLayout() ? 'mine-main-header' : 'mine-main-aside'}>
          <div class={{
            'mine-main-aside-content': true,
            'flex-col': !showMineHeader(),
            '!w-full px-3': showMineHeader(),
            '!hidden !lg:flex': true,
          }}
          >
            <div
              ref={mainAsideRef}
              class={asideListClass.value}
              onScroll={onAsideScroll}
              style={`${showMineHeader() ? 'width: 600px; overflow-x: auto;' : ''}`}
            >
              {menuStore.topMenu.map((menu: MineRoute.routeRecord, _: number) => (
                <a
                  v-show={!menu?.meta?.hidden}
                  class={{
                    'newActive': isMixedLayout() && (menu.name === route?.meta?.activeName || menu.name === route.name || menuStore.activeTopMenu?.name === menu.name),
                    'ocIsMixed': isMixedLayout(),
                    'active': (menu.name === route?.meta?.activeName || menu.name === route.name || menuStore.activeTopMenu?.name === menu.name),
                    'w-[40%] max-w-[40%]': !mainAsideSetting.showTitle,
                    'w-[50%] max-w-[50%]': !mainAsideSetting.showIcon,
                    'h-[35px]': !mainAsideSetting.showIcon,
                    'mx-auto mb-1.5 gap-y-0.5 px-2 py-1 block items-center': !showMineHeader(),
                    '!w-auto flex items-center px-2 h-11 gap-x-1': showMineHeader(),
                    '!my-10px py-10px': isColumnsLayout(),
                  }}
                  title={menu?.meta?.i18n ? useTrans(menu.meta?.i18n) : menu?.meta?.title}
                  onClick={async (e: any) => await goToAppoint(e, menu)}
                >
                  {!isMixedLayout() && mainAsideSetting.showIcon && menu?.meta?.icon && (
                    <ma-svg-icon
                      name={menu?.meta?.icon}
                      size={20}
                    />
                  )}
                  {mainAsideSetting.showTitle && (
                    <span
                      class={{
                        'route-link': true,
                        'truncate': !isMixedLayout(),
                      }}
                      to={menu.path}
                    >
                      {menu?.meta?.i18n ? useTrans(menu?.meta?.i18n) : menu?.meta?.title}
                    </span>
                  )}
                </a>
              ))}
            </div>
            <div class="flex items-center justify-center gap-x-3">
              {
                router.hasRoute('MineAppStoreRoute')
                && (
                  <m-tooltip text={useTrans('menu.appstore')} placement="right">
                    <a
                      class="h-14 flex cursor-pointer items-center justify-center"
                      onClick={() => router.push({ path: '/appstore' })}
                      title={useTrans('menu.appstore')}
                    >
                      <ma-svg-icon name="vscode-icons:file-type-azure" size={30} />
                    </a>
                  </m-tooltip>
                )
              }
            </div>
          </div>
        </Transition>
      )
    }
  },
})
