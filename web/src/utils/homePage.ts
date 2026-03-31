import type { SystemSettings } from '#/global'

export function getAuthHomePage(): SystemSettings.welcomePage {
  return useSettingStore().getSettings('welcomePage')
}
