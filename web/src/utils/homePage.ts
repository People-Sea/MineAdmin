import type { SystemSettings } from '#/global'

export const TENANT_HOME_PAGE: SystemSettings.welcomePage = {
  name: 'platform:tenant-project',
  path: '/config/project',
  title: '团队项目管理',
  icon: 'material-symbols:folder-managed-outline-rounded',
}

export function getAuthHomePage(): SystemSettings.welcomePage {
  const userInfo = useUserStore().getUserInfo()
  return userInfo?.user_type === 200
    ? TENANT_HOME_PAGE
    : useSettingStore().getSettings('welcomePage')
}
