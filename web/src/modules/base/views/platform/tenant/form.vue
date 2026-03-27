<script setup lang="ts">
import type { TenantVo } from '~/base/api/tenant'
import { create, save } from '~/base/api/tenant'
import getFormItems from './data/getFormItems.tsx'
import type { MaFormExpose } from '@mineadmin/form'
import useForm from '@/hooks/useForm.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'platform:tenant:form' })

const { formType = 'add', data = null } = defineProps<{
  formType: 'add' | 'edit'
  data?: TenantVo | null
}>()

const t = useTrans().globalTrans
const tenantForm = ref<MaFormExpose>()
const tenantModel = ref<TenantVo>({})

useForm('tenantForm').then((form: MaFormExpose) => {
  if (formType === 'edit' && data) {
    Object.keys(data).map((key: string) => {
      tenantModel.value[key] = data[key]
    })
  }
  form.setItems(getFormItems(formType, t, tenantModel.value))
  form.setOptions({
    labelWidth: '100px',
  })
})

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(tenantModel.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    save(tenantModel.value.id as number, tenantModel.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

defineExpose({
  add,
  edit,
  maForm: tenantForm,
})
</script>

<template>
  <ma-form ref="tenantForm" v-model="tenantModel" />
</template>
