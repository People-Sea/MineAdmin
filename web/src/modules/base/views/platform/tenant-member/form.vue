<script setup lang="ts">
import type { FormInstance, FormRules } from 'element-plus'
import type { TenantMemberVo, TenantRoleOptionVo } from '~/base/api/tenantMember'
import type { TenantOptionVo } from '~/base/api/tenant'
import { create, roleOptions as roleOptionsApi, save } from '~/base/api/tenantMember'
import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

defineOptions({ name: 'platform:tenant-member:form' })

const props = defineProps<{
  formType: 'add' | 'edit'
  data?: TenantMemberVo | null
  currentTenantId?: number
  tenantOptions?: TenantOptionVo[]
}>()

const workspaceStore = useTenantWorkspaceStore()
const memberFormRef = ref<FormInstance>()
const roleOptions = ref<TenantRoleOptionVo[]>([])
const memberModel = ref<TenantMemberVo>({
  tenant_id: props.currentTenantId,
  role_id: undefined,
  status: 1,
  project_ids: [],
})

const rules = computed<FormRules>(() => ({
  tenant_id: [{ required: true, message: '请选择所属租户', trigger: 'change' }],
  name: [{ required: true, message: '请输入成员姓名', trigger: 'blur' }],
  username: [{ required: true, message: '请输入登录账号', trigger: 'blur' }],
  password: props.formType === 'add'
    ? [{ required: true, message: '请输入登录密码', trigger: 'blur' }]
    : [],
  role_id: [{ required: true, message: '请选择角色', trigger: 'change' }],
}))

const canEditTenant = computed(() => !workspaceStore.isTenantMode && props.formType === 'add')

const currentTenantName = computed(() => {
  const selectedTenant = props.tenantOptions?.find(item => item.id === memberModel.value.tenant_id)
  return selectedTenant?.name || props.data?.tenant_name || workspaceStore.currentTenantName || '未选择租户'
})

function fillModel() {
  memberModel.value = {
    tenant_id: props.currentTenantId,
    role_id: undefined,
    status: 1,
    project_ids: [],
    remark: '',
  }

  if (props.formType === 'edit' && props.data) {
    memberModel.value = {
      ...memberModel.value,
      ...props.data,
      password: '',
    }
  }
}

watch(() => [props.formType, props.data, props.currentTenantId], fillModel, { immediate: true })

onMounted(async () => {
  const res = await roleOptionsApi()
  roleOptions.value = res.data ?? []
})

function buildPayload() {
  const payload = { ...memberModel.value }

  if (props.formType === 'edit' || workspaceStore.isTenantMode) {
    delete payload.tenant_id
  }

  return payload
}

function add(): Promise<any> {
  return new Promise((resolve, reject) => {
    create(buildPayload()).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

function edit(): Promise<any> {
  return new Promise((resolve, reject) => {
    const payload = buildPayload()
    if (!payload.password) {
      delete payload.password
    }
    save(memberModel.value.id as number, payload).then((res: any) => {
      res.code === ResultCode.SUCCESS ? resolve(res) : reject(res)
    }).catch(reject)
  })
}

defineExpose({
  add,
  edit,
  maForm: {
    getElFormRef: () => memberFormRef.value,
  },
})
</script>

<template>
  <el-form ref="memberFormRef" :model="memberModel" :rules="rules" label-width="100px">
    <el-form-item v-if="canEditTenant" label="所属租户" prop="tenant_id">
      <el-select
        v-model="memberModel.tenant_id"
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
    <el-form-item label="成员姓名" prop="name">
      <el-input v-model="memberModel.name" maxlength="30" placeholder="请输入成员姓名" />
    </el-form-item>
    <el-form-item label="登录账号" prop="username">
      <el-input v-model="memberModel.username" maxlength="20" placeholder="请输入登录账号" />
    </el-form-item>
    <el-form-item :label="formType === 'add' ? '登录密码' : '重置密码'" prop="password">
      <el-input
        v-model="memberModel.password"
        type="password"
        show-password
        maxlength="100"
        :placeholder="formType === 'add' ? '请输入登录密码' : '留空则不修改密码'"
      />
    </el-form-item>
    <el-form-item label="联系电话" prop="phone">
      <el-input v-model="memberModel.phone" maxlength="20" placeholder="请输入联系电话" />
    </el-form-item>
    <el-form-item label="角色" prop="role_id">
      <el-select v-model="memberModel.role_id" class="w-full" placeholder="请选择角色">
        <el-option
          v-for="item in roleOptions"
          :key="item.id"
          :label="item.name"
          :value="item.id"
        />
      </el-select>
    </el-form-item>
    <el-form-item label="状态" prop="status">
      <el-radio-group v-model="memberModel.status">
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
        v-model="memberModel.remark"
        type="textarea"
        maxlength="255"
        show-word-limit
        placeholder="请输入备注"
      />
    </el-form-item>
  </el-form>
</template>
