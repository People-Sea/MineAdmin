import type { Router } from 'vue-router'
import type { MineToolbarExpose, Plugin } from '#/global'

const pluginConfig: Plugin.PluginConfig = {
  install(app) {
    const $toolbars = app.config.globalProperties.$toolbars as MineToolbarExpose

    $toolbars.add({
      name: 'Palette',
      title: 'bytedance.layoutConfig',
      show: true,
      icon: 'ri:palette-line',
      component: () => import('./layouts/components/palette'),
    })
  },
  hooks: {
    start: (config): any => {
      console.log('您正在使用插件', `插件名称: ${config.info.description}`, `插件版本: ${config.info.version}`)
    },
    registerRoute: (router: Router, routesRaw: Array<any>): void => {
      const targetRouteName = 'MineRootLayoutRoute'
      router.removeRoute(targetRouteName)
      const newLayoutRoute = routesRaw.find(route => route.name === targetRouteName)
      // 处理奇奇怪怪的问题
      if (newLayoutRoute) {
        newLayoutRoute.component = newLayoutRoute.component || newLayoutRoute.components?.default
        delete newLayoutRoute.components
        newLayoutRoute.component = () => import('./layouts/index')
        router.addRoute(newLayoutRoute)
      }
      else {
        console.log(`Route with name "${targetRouteName}" not found in routesRaw.`)
      }
    },
  },
  config: {
    enable: true,
    info: {
      name: 'west/bytedance',
      version: '3.1.5',
      author: 'west',
      description: 'MineAdmin 3.0布局插件，高度复刻巨量引擎布局',
    },
  },
}

export default pluginConfig
