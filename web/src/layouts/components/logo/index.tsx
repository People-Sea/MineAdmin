/**
 * MineAdmin is committed to providing solutions for quickly building web applications
 * Please view the LICENSE file that was distributed with this source code,
 * For the full copyright and license information.
 * Thank you very much for using MineAdmin.
 *
 * @Author X.Mo<root@imoi.cn>
 * @Link   https://github.com/mineadmin
 */
import '@/layouts/style/logo.scss'
import LogoImage from '@/assets/images/mh_logo.png'
import { getAuthHomePage } from '@/utils/homePage.ts'

export default defineComponent({
  name: 'Logo',
  props: {
    showLogo: { type: Boolean, default: true },
    showTitle: { type: Boolean, default: true },
    title: { type: String, default: null },
  },
  setup(props) {
    const title = props.title ?? import.meta.env.VITE_APP_TITLE
    const settings = computed(() => getAuthHomePage())
    return () => {
      return (
        <router-link to={settings.value.path} class={['mine-main-logo', 'cursor-pointer']} title={title}>
          {props.showLogo && (
            <img src={LogoImage} alt={title} class="mine-logo-img" />
          )}
          {props.showTitle && (
            <span class="mine-logo-title">{title}</span>
          )}
        </router-link>
      )
    }
  },
})
