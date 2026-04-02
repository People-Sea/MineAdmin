<!--
 - MineAdmin is committed to providing solutions for quickly building web applications
 - Please view the LICENSE file that was distributed with this source code,
 - For the full copyright and license information.
 - Thank you very much for using MineAdmin.
 -
 - @Author X.Mo<root@imoi.cn>
 - @Link   https://github.com/mineadmin
-->
<script setup lang="tsx">
import type { MaProTableExpose, MaProTableOptions, MaProTableSchema } from '@mineadmin/pro-table'
import type { Ref } from 'vue'
import type { TransType } from '@/hooks/auto-imports/useTrans.ts'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'

import { deleteByIds, page } from '~/base/api/role'
import getSearchItems from './data/getSearchItems.tsx'
import getTableColumns from './data/getTableColumns.tsx'
import useDialog from '@/hooks/useDialog.ts'
import useDialogSubmit from '@/hooks/useDialogSubmit.ts'
import { useMessage } from '@/hooks/useMessage.ts'
import { ResultCode } from '@/utils/ResultCode.ts'

import RoleForm from './form.vue'
import SetPermission from './setPermissionForm.vue'

defineOptions({ name: 'permission:role' })

const proTableRef = ref<MaProTableExpose>() as Ref<MaProTableExpose>
const formRef = ref()
const setFormRef = ref()
const selections = ref<any[]>([])
const i18n = useTrans() as TransType
const t = i18n.globalTrans
const msg = useMessage()
const submitDialog = useDialogSubmit()

// 弹窗配置
const maDialog: UseDialogExpose = useDialog({
  lgWidth: '550px',
  ok: async ({ formType }, okLoadingState: (state: boolean) => void) => {
    okLoadingState(true)
    try {
      if (['add', 'edit'].includes(formType)) {
        await submitDialog({
          validate: () => formRef.value.maForm.getElFormRef().validate(),
          submit: () => formType === 'add' ? formRef.value.add() : formRef.value.edit(),
          successMessage: formType === 'add' ? t('crud.createSuccess') : t('crud.updateSuccess'),
          close: maDialog.close,
          onSuccess: async () => {
            await proTableRef.value.refresh()
          },
        })
        return
      }

      await submitDialog({
        validate: () => setFormRef.value.maForm.getElFormRef().validate(),
        submit: () => setFormRef.value.saveUserRole(),
        successMessage: t('baseUserManage.setRoleSuccess'),
        close: maDialog.close,
      })
    }
    finally {
      okLoadingState(false)
    }
  },
})

// 参数配置
const options = ref<MaProTableOptions>({
  // 表格距离底部的像素偏移适配
  adaptionOffsetBottom: 161,
  header: {
    mainTitle: () => t('baseRoleManage.mainTitle'),
    subTitle: () => t('baseRoleManage.subTitle'),
  },
  // 表格参数
  tableOptions: {
    on: {
      // 表格选择事件
      onSelectionChange: (selection: any[]) => selections.value = selection,
    },
  },
  // 搜索参数
  searchOptions: {
    fold: true,
    text: {
      searchBtn: () => t('crud.search'),
      resetBtn: () => t('crud.reset'),
      isFoldBtn: () => t('crud.searchFold'),
      notFoldBtn: () => t('crud.searchUnFold'),
    },
  },
  // 搜索表单参数
  searchFormOptions: { labelWidth: '90px' },
  // 请求配置
  requestOptions: {
    api: page,
  },
})
// 架构配置
const schema = ref<MaProTableSchema>({
  // 搜索项
  searchItems: getSearchItems(t),
  // 表格列
  tableColumns: getTableColumns(maDialog, formRef, t),
})

// 批量删除
function handleDelete() {
  const ids = selections.value.map((item: any) => item.id)
  msg.confirm(t('crud.delMessage')).then(async () => {
    const response = await deleteByIds(ids)
    if (response.code === ResultCode.SUCCESS) {
      msg.success(t('crud.delSuccess'))
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
          v-auth="['permission:role:save']"
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
          v-auth="['permission:role:delete']"
          type="danger"
          plain
          :disabled="selections.length < 1"
          @click="handleDelete"
        >
          {{ t('crud.delete') }}
        </el-button>
      </template>
      <!-- 数据为空时 -->
      <template #empty>
        <el-empty>
          <el-button
            v-auth="['permission:role:save']"
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
        <!-- 新增、编辑表单 -->
        <RoleForm v-if="formType !== 'setPermission'" ref="formRef" :form-type="formType" :data="data" />
        <!-- 赋予菜单表单 -->
        <SetPermission v-else ref="setFormRef" :data="data" />
      </template>
    </component>
  </div>
</template>

<style scoped lang="scss">

</style>
