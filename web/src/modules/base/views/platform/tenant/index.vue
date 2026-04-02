<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { Ref } from 'vue'
import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'

import { deleteByIds, page } from '~/base/api/tenant'
import getSearchItems from './data/getSearchItems.tsx'
import getTableColumns from './data/getTableColumns.tsx'
import useDialog from '@/hooks/useDialog.ts'
import useDialogSubmit from '@/hooks/useDialogSubmit.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'
import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'

import TenantForm from './form.vue'

defineOptions({ name: 'platform:tenant' })

const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const selections = ref<any[]>([])
const i18n = useTrans() as TransType
const t = i18n.globalTrans
const msg = useMessage()
const submitDialog = useDialogSubmit()
const workspaceStore = useTenantWorkspaceStore()
const pageTitle = '租户管理'
const pageSubTitle = '创建租户时自动生成主项目和首个租户管理员。'

const maDialog: UseDialogExpose = useDialog({
  lgWidth: '600px',
  ok: async ({ formType }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    try {
      await submitDialog({
        validate: () => formRef.value.maForm.getElFormRef().validate(),
        submit: () => formType === 'add' ? formRef.value.add() : formRef.value.edit(),
        successMessage: formType === 'add' ? t('crud.createSuccess') : t('crud.updateSuccess'),
        close: maDialog.close,
        onSuccess: async () => {
          await workspaceStore.refreshTenants()
          await proTableRef.value.refresh()
        },
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
    mainTitle: () => pageTitle,
    subTitle: () => pageSubTitle,
  },
  tableOptions: {
    on: {
      onSelectionChange: (selection: any[]) => selections.value = selection,
    },
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
  tableColumns: getTableColumns(maDialog, t),
})

function handleDelete() {
  const ids = selections.value.map((item: any) => item.id)
  msg.confirm(t('crud.delMessage')).then(async () => {
    const response = await deleteByIds(ids)
    if (response.code === ResultCode.SUCCESS) {
      msg.success(t('crud.delSuccess'))
      await workspaceStore.refreshTenants()
      proTableRef.value.refresh()
    }
  })
}
</script>

<template>
  <div class="mine-layout pt-3">
    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['platform:tenant:save']"
          type="primary"
          @click="() => {
            maDialog.setTitle(t('crud.add'))
            maDialog.open({ formType: 'add' })
          }"
        >
          {{ t('crud.add') }}
        </el-button>
      </template>

      <template #toolbarLeft>
        <el-button
          v-auth="['platform:tenant:delete']"
          type="danger"
          plain
          :disabled="selections.length < 1"
          @click="handleDelete"
        >
          {{ t('crud.delete') }}
        </el-button>
      </template>

      <template #empty>
        <el-empty>
          <el-button
            v-auth="['platform:tenant:save']"
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
        <TenantForm ref="formRef" :form-type="formType" :data="data" />
      </template>
    </component>
  </div>
</template>
