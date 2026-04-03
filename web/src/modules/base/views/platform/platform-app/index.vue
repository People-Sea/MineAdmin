<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { Ref } from 'vue'
import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'

import { page } from '~/base/api/platformApp'
import getSearchItems from './data/getSearchItems.tsx'
import getTableColumns from './data/getTableColumns.tsx'
import useDialog from '@/hooks/useDialog.ts'
import useDialogSubmit from '@/hooks/useDialogSubmit.ts'

import PlatformAppForm from './form.vue'

defineOptions({ name: 'platform:platform-app' })

const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const i18n = useTrans() as TransType
const t = i18n.globalTrans
const submitDialog = useDialogSubmit()

async function refreshTable() {
  await proTableRef.value.refresh()
}

const maDialog: UseDialogExpose = useDialog({
  lgWidth: '620px',
  ok: async ({ formType }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    try {
      await submitDialog({
        validate: () => formRef.value.maForm.getElFormRef().validate(),
        submit: () => formType === 'add' ? formRef.value.add() : formRef.value.edit(),
        successMessage: formType === 'add' ? t('crud.createSuccess') : t('crud.updateSuccess'),
        close: maDialog.close,
        onSuccess: refreshTable,
      })
    }
    finally {
      okLoadingState(false)
    }
  },
})

const options = ref<MaProTableOptions>({
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => '平台应用管理',
    subTitle: () => '平台统一维护腾讯广告第三方应用，供租户授权广告主账号使用。',
  },
  searchOptions: {
    fold: true,
    text: {
      searchBtn: () => t('crud.search'),
      resetBtn: () => t('crud.reset'),
      isFoldBtn: () => t('crud.searchFold'),
      notFoldBtn: () => t('crud.searchUnFold'),
    },
  },
  searchFormOptions: { labelWidth: '90px' },
  requestOptions: {
    api: page,
  },
})

const schema = ref<MaProTableSchema>({
  searchItems: getSearchItems(t),
  tableColumns: getTableColumns(maDialog, t, refreshTable),
})
</script>

<template>
  <div class="mine-layout pt-3">
    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['platform:platform-app:save']"
          type="primary"
          @click="() => {
            maDialog.setTitle(t('crud.add'))
            maDialog.open({ formType: 'add' })
          }"
        >
          {{ t('crud.add') }}
        </el-button>
      </template>

      <template #empty>
        <el-empty>
          <el-button
            v-auth="['platform:platform-app:save']"
            type="primary"
            @click="() => {
              maDialog.setTitle(t('crud.add'))
              maDialog.open({ formType: 'add' })
            }"
          >
            {{ t('crud.add') }}
          </el-button>
        </el-empty>
      </template>
    </MaProTable>

    <component :is="maDialog.Dialog">
      <template #default="{ formType, data }">
        <PlatformAppForm ref="formRef" :form-type="formType" :data="data" />
      </template>
    </component>
  </div>
</template>
