<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { TenantOptionVo } from '~/base/api/tenant'
import type { TenantProjectVo } from '~/base/api/tenantProject'
import type { TenantMemberOptionVo } from '~/base/api/tenantMember'
import { create, save } from '~/base/api/tenantProject'
import { options as memberOptionsApi } from '~/base/api/tenantMember'
import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'platform:tenant-project:form' })

const props = defineProps<{
  formType: 'add' | 'edit'
  data?: TenantProjectVo | null
  currentTenantId?: number
  tenantOptions?: TenantOptionVo[]
}>()

const workspaceStore = useTenantWorkspaceStore()
const projectFormRef = ref<FormInstance>()
const memberOptions = ref<TenantMemberOptionVo[]>([])
const projectModel = ref<TenantProjectVo>({
  tenant_id: props.currentTenantId,
  status: 1,
  member_ids: [],
})

const rules = computed<FormRules>(() => ({
  tenant_id: [{ required: true, message: '请选择所属租户', trigger: 'change' }],
  name: [{ required: true, message: '请输入项目名称', trigger: 'blur' }],
}))

const canEditTenant = computed(() => !workspaceStore.isTenantMode && props.formType === 'add')

const currentTenantName = computed(() => {
  const selectedTenant = props.tenantOptions?.find(item => item.id === projectModel.value.tenant_id)
  return selectedTenant?.name || props.data?.tenant_name || workspaceStore.currentTenantName || '未选择租户'
})

async function loadMemberOptions(tenantId?: number) {
  if (!tenantId && !workspaceStore.isTenantMode) {
    memberOptions.value = []
    return
  }

  const query = workspaceStore.isTenantMode
    ? { status: 1 }
    : { tenant_id: tenantId, status: 1 }

  const res = await memberOptionsApi(query)
  memberOptions.value = res.data ?? []
}

function buildPayload() {
  const payload = { ...projectModel.value }

  if (props.formType === 'edit' || workspaceStore.isTenantMode) {
    delete payload.tenant_id
  }

  return payload
}

function fillModel() {
  projectModel.value = {
    tenant_id: props.currentTenantId,
    status: 1,
    member_ids: [],
    remark: '',
  }

  if (props.formType === 'edit' && props.data) {
    projectModel.value = {
      ...projectModel.value,
      ...props.data,
      member_ids: props.data.member_ids ?? [],
    }
  }
}

watch(() => [props.formType, props.data, props.currentTenantId], fillModel, { immediate: true })
watch(
  () => projectModel.value.tenant_id,
  (tenantId, previousTenantId) => {
    if (props.formType === 'add' && tenantId !== previousTenantId) {
      projectModel.value.member_ids = []
    }

    loadMemberOptions(tenantId).catch(() => {})
  },
  { immediate: true },
)

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(buildPayload()).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    save(projectModel.value.id as number, buildPayload()).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

defineExpose({
  add,
  edit,
  maForm: {
    getElFormRef: () => projectFormRef.value,
  },
})
</script>

<template>
  <div>
    <el-alert
      v-if="projectModel.is_default === 1"
      type="success"
      :closable="false"
      title="主项目不能删除，建议始终保留为租户默认工作上下文。"
      class="mb-4"
    />

    <el-form ref="projectFormRef" :model="projectModel" :rules="rules" label-width="100px">
      <el-form-item v-if="canEditTenant" label="所属租户" prop="tenant_id">
        <el-select
          v-model="projectModel.tenant_id"
          class="w-full"
          filterable
          placeholder="请选择所属租户"
        >
          <el-option
            v-for="item in props.tenantOptions || []"
            :key="item.id"
            :label="item.name"
            :value="item.id || 0"
          />
        </el-select>
      </el-form-item>
      <el-form-item v-else label="所属租户">
        <el-input :model-value="currentTenantName" disabled />
      </el-form-item>
      <el-form-item label="项目名称" prop="name">
        <el-input v-model="projectModel.name" maxlength="60" placeholder="请输入项目名称" />
      </el-form-item>
      <el-form-item label="项目成员" prop="member_ids">
        <el-select
          v-model="projectModel.member_ids"
          class="w-full"
          multiple
          filterable
          collapse-tags
          collapse-tags-tooltip
          placeholder="请选择项目成员"
        >
          <el-option
            v-for="item in memberOptions"
            :key="item.id"
            :label="`${item.name}（${item.role_label || item.role || ''}）`"
            :value="item.id || 0"
          />
        </el-select>
      </el-form-item>
      <el-form-item label="状态" prop="status">
        <el-radio-group v-model="projectModel.status">
          <el-radio :value="1">
            正常
          </el-radio>
          <el-radio :value="2">
            停用
          </el-radio>
        </el-radio-group>
      </el-form-item>
      <el-form-item label="备注" prop="remark">
        <el-input
          v-model="projectModel.remark"
          type="textarea"
          maxlength="255"
          show-word-limit
          placeholder="请输入备注"
        />
      </el-form-item>
    </el-form>
  </div>
</template>
