<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { Ref } from 'vue'
import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'
import type { TenantOptionVo } from '~/base/api/tenant'
import { ElTag } from 'element-plus'
import MaDictSelect from '@/components/ma-dict-picker/ma-dict-select.vue'
import useDialog from '@/hooks/useDialog.ts'
import useDialogSubmit from '@/hooks/useDialogSubmit.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'
import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'
import { options as tenantOptionsApi } from '~/base/api/tenant'
import { deleteByIds, page } from '~/base/api/tenantMember'
import TenantMemberForm from './form.vue'

defineOptions({ name: 'platform:tenant-member' })

const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const selections = ref<any[]>([])
const i18n = useTrans() as TransType
const t = i18n.globalTrans
const msg = useMessage()
const submitDialog = useDialogSubmit()
const workspaceStore = useTenantWorkspaceStore()
const tenantOptions = ref<TenantOptionVo[]>([])
const tenantFilterId = ref<number | undefined>(undefined)
const isTenantMode = computed(() => workspaceStore.isTenantMode)
const activeTenantName = computed(() => {
  if (isTenantMode.value) {
    return workspaceStore.currentTenantName
  }

  return tenantOptions.value.find(item => item.id === tenantFilterId.value)?.name ?? ''
})

async function loadTenantOptions() {
  if (isTenantMode.value) {
    tenantOptions.value = workspaceStore.tenantOptions
    return
  }

  const res = await tenantOptionsApi()
  tenantOptions.value = res.data ?? []
}

onMounted(() => {
  if (isTenantMode.value) {
    workspaceStore.init().then(async () => {
      await loadTenantOptions()
      proTableRef.value?.refresh()
    })
    return
  }

  loadTenantOptions().then(() => {
    proTableRef.value?.refresh()
  })
})

watch(() => isTenantMode.value ? workspaceStore.currentTenantId : tenantFilterId.value, () => {
  proTableRef.value?.refresh()
})

const maDialog: UseDialogExpose = useDialog({
  lgWidth: '760px',
  ok: async ({ formType }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    try {
      await submitDialog({
        validate: () => formRef.value.maForm.getElFormRef().validate(),
        submit: () => formType === 'add' ? formRef.value.add() : formRef.value.edit(),
        successMessage: formType === 'add' ? t('crud.createSuccess') : t('crud.updateSuccess'),
        close: maDialog.close,
        onSuccess: async () => {
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
    mainTitle: () => '团队人员管理',
    subTitle: () => isTenantMode.value
      ? (activeTenantName.value ? `当前租户：${activeTenantName.value}` : '当前账号未绑定租户')
      : (activeTenantName.value ? `当前筛选租户：${activeTenantName.value}` : '默认显示全部租户成员，可按租户筛选查看'),
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
      ...(!isTenantMode.value && tenantFilterId.value ? { tenant_id: tenantFilterId.value } : {}),
    }),
  },
})

const schema = computed<MaProTableSchema>(() => ({
  searchItems: [
    {
      label: () => '成员姓名',
      prop: 'name',
      render: 'input',
    },
    {
      label: () => '登录账号',
      prop: 'username',
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
    ...(!isTenantMode.value
      ? [{ label: () => '所属租户', prop: 'tenant_name', minWidth: '180px' }]
      : []),
    { label: () => '成员姓名', prop: 'name', minWidth: '140px' },
    { label: () => '登录账号', prop: 'username', minWidth: '160px' },
    { label: () => '联系电话', prop: 'phone', minWidth: '140px' },
    {
      label: () => '角色',
      prop: 'role_label',
      width: '120px',
      cellRender: ({ row }) => (
        <ElTag type={row.role === 'TenantAdmin' ? 'danger' : 'primary'}>
          {row.role_label}
        </ElTag>
      ),
    },
    {
      label: () => '所属项目',
      prop: 'project_names',
      minWidth: '220px',
      cellRender: ({ row }) => row.project_names?.length
        ? row.project_names.join('、')
        : <span class="text-gray-400">未加入项目</span>,
    },
    {
      label: () => '项目数',
      prop: 'project_count',
      width: '90px',
    },
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
      width: '180px',
      operationConfigure: {
        type: 'tile',
        actions: [
          {
            name: 'edit',
            icon: 'material-symbols:person-edit',
            show: () => hasAuth('platform:tenant-member:update'),
            text: () => t('crud.edit'),
            onClick: ({ row }) => {
              maDialog.setTitle(t('crud.edit'))
              maDialog.open({ formType: 'edit', data: row })
            },
          },
          {
            name: 'del',
            show: () => hasAuth('platform:tenant-member:delete'),
            icon: 'mdi:delete',
            text: () => t('crud.delete'),
            onClick: async ({ row }, proxy: MaProTableExpose) => {
              await msg.delConfirm(t('crud.delDataMessage'))
              const response = await deleteByIds([row.id])
              if (response.code === ResultCode.SUCCESS) {
                msg.success(t('crud.delSuccess'))
                await proxy.refresh()
              }
            },
          },
        ],
      },
    },
  ],
}))

async function handleDelete() {
  const ids = selections.value.map((item: any) => item.id)
  await msg.confirm(t('crud.delMessage'))
  const response = await deleteByIds(ids)
  if (response.code === ResultCode.SUCCESS) {
    msg.success(t('crud.delSuccess'))
    await proTableRef.value.refresh()
  }
}
</script>

<template>
  <div class="mine-layout pt-3">
    <el-alert
      v-if="!isTenantMode && tenantOptions.length === 0"
      type="warning"
      :closable="false"
      title="当前没有可用租户，请先在平台租户管理中创建租户。"
      class="mb-4"
    />

    <MaProTable ref="proTableRef" :options="options" :schema="schema">
      <template #actions>
        <el-button
          v-auth="['platform:tenant-member:save']"
          type="primary"
          :disabled="!isTenantMode && tenantOptions.length === 0"
          @click="() => {
            maDialog.setTitle(t('crud.add'))
            maDialog.open({ formType: 'add' })
          }"
        >
          {{ t('crud.add') }}
        </el-button>
      </template>

      <template #toolbarLeft>
        <el-select
          v-if="!isTenantMode"
          v-model="tenantFilterId"
          class="mr-3 !w-[240px]"
          clearable
          filterable
          placeholder="全部租户"
        >
          <el-option
            v-for="item in tenantOptions"
            :key="item.id"
            :label="item.name"
            :value="item.id || 0"
          />
        </el-select>
        <el-button
          v-auth="['platform:tenant-member:delete']"
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
        <TenantMemberForm
          ref="formRef"
          :form-type="formType"
          :data="data"
          :current-tenant-id="isTenantMode ? workspaceStore.currentTenantId : tenantFilterId"
          :tenant-options="tenantOptions"
        />
      </template>
    </component>
  </div>
</template>
