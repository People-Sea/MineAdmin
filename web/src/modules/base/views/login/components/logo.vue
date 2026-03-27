<!--
 - MineAdmin is committed to providing solutions for quickly building web applications
 - Please view the LICENSE file that was distributed with this source code,
 - For the full copyright and license information.
 - Thank you very much for using MineAdmin.
 -
 - @Author X.Mo<root@imoi.cn>
 - @Link   https://github.com/mineadmin
-->
<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import LogoImage from '@/assets/images/mh_logo.png'

const { t } = useI18n()

const appTitle = ref<string>(import.meta.env.VITE_APP_TITLE)
</script>

<template>
  <div class="relative w-auto flex items-center gap-x-3">
    <img :alt="appTitle" :src="LogoImage" class="login-logo">
    <h3 class="whitespace-nowrap text-4xl leading-none text-white tracking-[3px] lg:text-[#2d2d33ff]">
      {{ appTitle }} <span class="text-xl font-medium tracking-normal align-middle lg:text-2xl">· {{ t('mineAdmin.mark') }}</span>
    </h3>
  </div>
</template>
