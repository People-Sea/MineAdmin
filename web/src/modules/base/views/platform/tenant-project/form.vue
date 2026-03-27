<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
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
  name: [{ required: true, message: '请输入项目名称', trigger: 'blur' }],
}))

const currentTenantName = computed(() => workspaceStore.currentTenantName || '未选择租户')

async function loadMemberOptions(tenantId?: number) {
  if (!tenantId) {
    memberOptions.value = []
    return
  }

  const res = await memberOptionsApi({ tenant_id: tenantId, status: 1 })
  memberOptions.value = res.data ?? []
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

  loadMemberOptions(projectModel.value.tenant_id).catch(() => {})
}

watch(() => [props.formType, props.data, props.currentTenantId], fillModel, { immediate: true })

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(projectModel.value).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    save(projectModel.value.id as number, projectModel.value).then((res: any) => {
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
      <el-form-item label="当前租户">
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
