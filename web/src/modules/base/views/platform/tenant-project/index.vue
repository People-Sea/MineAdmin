<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { Ref } from 'vue'
import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'
import { ElTag } from 'element-plus'
import MaDictSelect from '@/components/ma-dict-picker/ma-dict-select.vue'
import useDialog from '@/hooks/useDialog.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'
import { deleteByIds, page } from '~/base/api/tenantProject'
import TenantProjectForm from './form.vue'

defineOptions({ name: 'platform:tenant-project' })

const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const selections = ref<any[]>([])
const i18n = useTrans() as TransType
const t = i18n.globalTrans
const msg = useMessage()
const workspaceStore = useTenantWorkspaceStore()

onMounted(() => {
  workspaceStore.init().then(() => {
    proTableRef.value?.refresh()
  })
})

watch(() => workspaceStore.currentTenantId, () => {
  proTableRef.value?.refresh()
})

const maDialog: UseDialogExpose = useDialog({
  lgWidth: '760px',
  ok: async ({ formType }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    try {
      const elForm = formRef.value.maForm.getElFormRef()
      await elForm.validate()
      const response = formType === 'add' ? await formRef.value.add() : await formRef.value.edit()
      if (response.code === ResultCode.SUCCESS) {
        msg.success(formType === 'add' ? t('crud.createSuccess') : t('crud.updateSuccess'))
        maDialog.close()
        await workspaceStore.refreshProjects()
        await proTableRef.value.refresh()
      }
      else {
        msg.error(response.message)
      }
    }
    catch (error) {
      msg.alertError(error instanceof Error ? error.message : String(error))
    }
    finally {
      okLoadingState(false)
    }
  },
})

const options = ref<MaProTableOptions>({
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => '团队项目管理',
    subTitle: () => workspaceStore.currentTenantName
      ? `当前租户：${workspaceStore.currentTenantName}`
      : '请先创建租户并在顶部选择当前租户',
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
    api: (params: any) => page({
      ...params,
      tenant_id: workspaceStore.currentTenantId,
    }),
  },
})

const schema = ref<MaProTableSchema>({
  searchItems: [
    {
      label: () => '项目名称',
      prop: 'name',
      render: 'input',
    },
    {
      label: () => t('crud.status'),
      prop: 'status',
      render: () => MaDictSelect,
      renderProps: {
        clearable: true,
        placeholder: '',
        dictName: 'system-status',
      },
    },
  ],
  tableColumns: [
    { type: 'selection', showOverflowTooltip: false, label: () => t('crud.selection') },
    { type: 'index' },
    { label: () => '项目名称', prop: 'name', minWidth: '180px' },
    {
      label: () => '项目类型',
      prop: 'is_default',
      width: '110px',
      cellRender: ({ row }) => (
        row.is_default === 1
          ? <ElTag type="success">主项目</ElTag>
          : <ElTag type="info">普通项目</ElTag>
      ),
    },
    {
      label: () => '项目成员',
      prop: 'member_names',
      minWidth: '240px',
      cellRender: ({ row }) => row.member_names?.length
        ? row.member_names.join('、')
        : <span class="text-gray-400">暂无成员</span>,
    },
    { label: () => '成员数', prop: 'member_count', width: '90px' },
    {
      label: () => t('crud.status'),
      prop: 'status',
      width: '110px',
      cellRender: ({ row }) => {
        const dictStore = useDictStore()
        return (
          <ElTag type={dictStore.t('system-status', row.status, 'color')}>
            {t(dictStore.t('system-status', row.status, 'i18n'))}
          </ElTag>
        )
      },
    },
    { label: () => '更新时间', prop: 'updated_at', minWidth: '180px' },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: '200px',
      operationConfigure: {
        type: 'tile',
        actions: [
          {
            name: 'edit',
            icon: 'material-symbols:folder-managed-outline-rounded',
            show: () => hasAuth('platform:tenant-project:update'),
            text: () => t('crud.edit'),
            onClick: ({ row }) => {
              maDialog.setTitle(t('crud.edit'))
              maDialog.open({ formType: 'edit', data: row })
            },
          },
          {
            name: 'del',
            show: () => hasAuth('platform:tenant-project:delete'),
            icon: 'mdi:delete',
            text: () => t('crud.delete'),
            onClick: async ({ row }, proxy: MaProTableExpose) => {
              await msg.delConfirm(t('crud.delDataMessage'))
              const response = await deleteByIds([row.id])
              if (response.code === ResultCode.SUCCESS) {
                msg.success(t('crud.delSuccess'))
                await workspaceStore.refreshProjects()
                await proxy.refresh()
              }
            },
          },
        ],
      },
    },
  ],
})

async function handleDelete() {
  const ids = selections.value.map((item: any) => item.id)
  await msg.confirm(t('crud.delMessage'))
  const response = await deleteByIds(ids)
  if (response.code === ResultCode.SUCCESS) {
    msg.success(t('crud.delSuccess'))
    await workspaceStore.refreshProjects()
    await proTableRef.value.refresh()
  }
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-alert
      v-if="!workspaceStore.currentTenantId"
      type="warning"
      :closable="false"
      title="当前没有可用租户，请先在平台租户管理中创建租户。"
      class="mb-4"
    />

    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['platform:tenant-project:save']"
          type="primary"
          :disabled="!workspaceStore.currentTenantId"
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
          v-auth="['platform:tenant-project:delete']"
          type="danger"
          plain
          :disabled="selections.length < 1"
          @click="handleDelete"
        >
          {{ t('crud.delete') }}
        </el-button>
      </template>
    </MaProTable>

    <component :is="maDialog.Dialog">
      <template #default="{ formType, data }">
        <TenantProjectForm
          ref="formRef"
          :form-type="formType"
          :data="data"
          :current-tenant-id="workspaceStore.currentTenantId"
        />
      </template>
    </component>
  </div>
</template>
