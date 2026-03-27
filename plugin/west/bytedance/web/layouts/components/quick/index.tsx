/*
 * @Date: 2024-10-18 19:15:16
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-10-18 19:42:48
 * @FilePath: /MineAdmin/web/src/plugins/west/oceanengine/layouts/components/quick/index.tsx
 */
import { defineComponent } from 'vue'
import { useRouter } from 'vue-router'
import '@/layouts/style/header.scss'

export default defineComponent({
  name: 'ocQuick',
  setup() {
    const t = useTrans().globalTrans
    const router = useRouter()
    const routes = router.getRoutes() // 获取所有路由

    // 过滤出需要展示的路由（根据路径匹配规则）
    const filteredRoutes = computed(() =>
      routes.filter(route => /\/welcome|permission|dashboard\/.+/.test(route.path)),
    )

    // 渲染 JSX
    return () => (
      <div class="mine-card" style="margin: 0;">
        <div class="text-base">
          快捷入口
        </div>
        <div class="grid grid-cols-3 mt-3 gap-3 lg:grid-cols-4 md:grid-cols-4 xl:grid-cols-4">
          {filteredRoutes.value.map(item => (
            <div class="flex-center">
              <el-link
                key={item.path}
                underline={false}
                onClick={() => router.push(item.path)}
              >
                <div class="oc-link">
                  <ma-svg-icon name={item.meta?.icon} size={26} />
                  {item.meta?.i18n ? t(item.meta.i18n) : item.meta?.title ?? 'unknown'}
                </div>
              </el-link>
            </div>
          ))}
        </div>
      </div>
    )
  },
})
