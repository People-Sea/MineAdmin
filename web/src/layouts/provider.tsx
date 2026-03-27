/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import { merge } from 'lodash-es'
import zhCn from 'element-plus/dist/locale/zh-cn.mjs'
import zhTw from 'element-plus/dist/locale/zh-tw.mjs'
import en from 'element-plus/dist/locale/en.mjs'
import { useI18n } from 'vue-i18n'

export default defineComponent({
  name: 'MineProvider',
  setup(_, { attrs, slots }) {
    interface Locale {
      [key: string]: string
    }
    const locales: Locale = {
      zh_CN: zhCn,
      zh_TW: zhTw,
      en,
    }
    const resolveLocaleName = (lang?: string) => {
      const normalized = lang?.trim().replace(/-/g, '_').toLowerCase()
      switch (normalized) {
        case 'zh_tw':
        case 'zh_hk':
        case 'zh_hant':
          return 'zh_TW'
        case 'en':
        case 'en_us':
        case 'en_gb':
          return 'en'
        case 'zh_cn':
        case 'zh_hans':
        default:
          return 'zh_CN'
      }
    }
    const userStore = useUserStore()
    const { locale } = useI18n()
    useMenuStore().init()
    const attrsMerged: any = ref(merge({ locale: locales[resolveLocaleName(userStore.getLanguage())], button: { autoInsertSpace: true } }, attrs))

    watch(() => userStore.getLanguage(), (lang: string) => {
      const localeName = resolveLocaleName(lang)
      attrsMerged.value.locale = locales[localeName]
      if (locale.value !== localeName) {
        locale.value = localeName
      }
    }, { immediate: true })

    onMounted(async () => await usePluginStore().callHooks('setup'))
    return () => (
      <el-config-provider {...attrsMerged.value}>
        {slots.default?.()}
      </el-config-provider>
    )
  },
})
