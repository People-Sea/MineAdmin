<script setup lang="ts">
import type { PlatformAppVo } from '~/base/api/platformApp.ts'
import { create, save } from '~/base/api/platformApp.ts'
import getFormItems from './data/getFormItems.tsx'
import type { MaFormExpose } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'platform:platform-app:form' })

const { formType = 'add', data = null } = defineProps<{
  formType?: 'add' | 'edit'
  data?: PlatformAppVo | null
}>()

const t = useTrans().globalTrans
const appForm = ref<MaFormExpose>()
const appModel = ref<PlatformAppVo>({})

useForm('platformAppForm').then((form: MaFormExpose) => {
  if (formType === 'edit' && data) {
    Object.keys(data).map((key: string) => {
      appModel.value[key] = data[key]
    })
  }

  form.setItems(getFormItems(formType, t, appModel.value))
  form.setOptions({
    labelWidth: '100px',
  })
})

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(appModel.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    save(appModel.value.id as number, appModel.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

defineExpose({
  add,
  edit,
  maForm: appForm,
})
</script>

<template>
  <ma-form ref="appForm" v-model="appModel" />
</template>
