/*
 * @Date: 2024-10-23 13:28:22
 * @LastEditors: west_ng 457395070@qq.com
 * @LastEditTime: 2024-10-24 17:50:48
 * @FilePath: /MineAdmin/web/src/plugins/west/bytedance/utils/bytedanceStore.ts
 */
import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useBytedanceStore = defineStore('bytedanceStore', () => {
  // 响应式状态
  const settings = ref({
    bgImage: '', // 背景图片路径
    slogan: '', // 口号
    filter: '',
  })

  // 设置配置
  function setBytedanceState(newSettings: Partial<typeof settings.value>) {
    settings.value = { ...settings.value, ...newSettings }
    localStorage.setItem('BytedanceSettings', JSON.stringify(settings.value))
  }

  // 获取配置
  function getBytedanceState() {
    const savedSettings = localStorage.getItem('BytedanceSettings')
    if (savedSettings) {
      settings.value = JSON.parse(savedSettings)
    }
    return settings.value
  }

  return {
    settings,
    setBytedanceState,
    getBytedanceState,
  }
})
