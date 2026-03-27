import { useBytedanceStore } from '@/plugins/west/bytedance/utils/bytedanceStore'
import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
import { ElAlert } from 'element-plus'

export default defineComponent({
  name: 'Palette',
  setup() {
    const visible = ref(false)
    const bytedanceStore = useBytedanceStore() // 实例化 store

    const i18n = useTrans() as TransType
    const t = i18n.globalTrans
    interface Settings {
      bgImage: string
      slogan: string
      filter: number
    }
    // 创建响应式对象来保存配置信息
    const newSettings = reactive<Settings>({
      bgImage: '', // 背景图片路径
      slogan: '', // 口号
      filter: 1,
    })

    // 读取配置方法
    const loadConfig = () => {
      const settings = bytedanceStore.getBytedanceState() // 获取已保存的配置
      if (settings) {
        newSettings.bgImage = settings.bgImage || '' // 设置背景图片
        newSettings.slogan = settings.slogan || '' // 设置口号
        newSettings.filter = Number(settings.filter) || 1
      }
    }

    // 保存配置方法
    const saveConfig = async () => {
      // 调用 store 方法保存配置
      bytedanceStore.setBytedanceState(newSettings)
      visible.value = false // 关闭抽屉
      await nextTick()
    }

    // 在组件加载时读取配置
    onMounted(() => {
      loadConfig()
    })

    return () => (
      <div class="flex items-center">
        <ma-svg-icon
          class="tool-icon"
          name="ri:palette-line"
          size={18}
          onClick={() => {
            visible.value = true
          }}
        />
        <el-drawer
          v-model={visible.value}
          contentClass="w-380px"
          show-close={false}
          modalClass="oc-drawer-box"
          v-slots={{
            header: () => (
              <div class="oc-page-header-main">
                <div class="text-color-text-1 overflow-hidden text-ellipsis whitespace-nowrap text-[20px] text-black font-semibold">{t('bytedance.title')}</div>
              </div>
            ),
            default: () => (
              <div class>
                <div
                  class="oc-drawer-close"
                  onClick={async () => {
                    visible.value = false
                    await nextTick()
                  }}
                >
                  <ma-svg-icon
                    class="tool-icon !text-white"
                    name="line-md:close"
                    size={15}
                  />
                </div>
                <div class="w-full flex flex-col gap-[10px]">
                  <ElAlert
                    class="hidden w-full md:flex"
                    type="success"
                    closable={false}
                  >
                    { t('bytedance.configTips') }
                  </ElAlert>
                  <div class="mine-card !m-0">
                    <div class="flex items-center justify-between">
                      <div class="desc-label">{t('bytedance.backgroundImage')}</div>
                      <div class="desc-value">
                        <ma-upload-image
                          v-model={newSettings.bgImage}
                          size={70}
                          title={t('bytedance.upload')}
                        />
                      </div>
                    </div>
                    <div class="flex items-center justify-between">
                      <div class="desc-label">{t('bytedance.blurValue')}</div>
                      <div class="desc-value">
                        <el-input-number
                          v-model={newSettings.filter}
                          min={1}
                          max={100}
                          placeholder={t('bytedance.blurValue')}
                        />
                      </div>
                    </div>
                    <el-divider />
                    <div class="flex items-center justify-between">
                      <div class="desc-label">{t('bytedance.slogan')}</div>
                      <div class="desc-value">
                        <el-input
                          v-model={newSettings.slogan}
                          style="width: 240px"
                          placeholder={t('bytedance.sloganPlaceholder')}
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            ),
            footer: () => (
              <div style="flex: auto">
                <el-button onClick={() => { visible.value = false }}>{t('bytedance.cancel')}</el-button>
                <el-button type="primary" onClick={saveConfig}>{t('bytedance.save')}</el-button>
              </div>
            ),
          }}
        />
      </div>
    )
  },
})
