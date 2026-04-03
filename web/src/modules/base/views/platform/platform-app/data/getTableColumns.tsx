import type { MaProTableColumns } from '@mineadmin/pro-table'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'
import type { PlatformAppVo } from '~/base/api/platformApp.ts'

import { ElTag } from 'element-plus'
import { useMessage } from '@/hooks/useMessage.ts'
import { disable, enable } from '~/base/api/platformApp.ts'
import { ResultCode } from '@/utils/ResultCode.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

function availabilityTagType(status?: string): '' | 'success' | 'warning' | 'danger' | 'info' {
  switch (status) {
    case 'available':
      return 'success'
    case 'partial':
      return 'warning'
    case 'unavailable':
      return 'danger'
    default:
      return 'info'
  }
}

function availabilityLabel(status?: string): string {
  switch (status) {
    case 'available':
      return '可用'
    case 'partial':
      return '部分可用'
    case 'unavailable':
      return '不可用'
    default:
      return '待确认'
  }
}

export default function getTableColumns(dialog: UseDialogExpose, t: any, refresh: () => Promise<void>): MaProTableColumns[] {
  const dictStore = useDictStore()
  const msg = useMessage()

  return [
    { type: 'index' },
    { label: () => '应用名称', prop: 'name', minWidth: '160px' },
    { label: () => '应用 ID', prop: 'app_id', minWidth: '180px' },
    { label: () => '回调地址', prop: 'callback_url', minWidth: '220px', showOverflowTooltip: true },
    {
      label: () => t('crud.status'),
      prop: 'status',
      width: '110px',
      cellRender: ({ row }) => (
        <ElTag type={dictStore.t('system-status', row.status, 'color')}>
          {t(dictStore.t('system-status', row.status, 'i18n'))}
        </ElTag>
      ),
    },
    {
      label: () => '可用状态',
      prop: 'availability_status',
      width: '120px',
      cellRender: ({ row }) => (
        <ElTag type={availabilityTagType(row.availability_status)}>
          {availabilityLabel(row.availability_status)}
        </ElTag>
      ),
    },
    { label: () => '最近校验时间', prop: 'last_check_at', minWidth: '180px' },
    { label: () => '最近错误摘要', prop: 'last_error_message', minWidth: '220px', showOverflowTooltip: true },
    { label: () => '更新时间', prop: 'updated_at', minWidth: '180px' },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: '240px',
      operationConfigure: {
        type: 'tile',
        actions: [
          {
            name: 'edit',
            icon: 'material-symbols:person-edit',
            show: () => hasAuth('platform:platform-app:update'),
            text: () => t('crud.edit'),
            onClick: ({ row }: { row: PlatformAppVo }) => {
              dialog.setTitle(t('crud.edit'))
              dialog.open({ formType: 'edit', data: row })
            },
          },
          {
            name: 'disable',
            icon: 'ri:pause-circle-line',
            show: ({ row }: { row: PlatformAppVo }) => hasAuth('platform:platform-app:disable') && row.status === 1,
            text: () => '停用',
            onClick: ({ row }: { row: PlatformAppVo }) => {
              msg.confirm('确认停用该平台应用吗？').then(async () => {
                const response = await disable(row.id as number)
                if (response.code === ResultCode.SUCCESS) {
                  msg.success('停用成功')
                  await refresh()
                }
              })
            },
          },
          {
            name: 'enable',
            icon: 'ri:play-circle-line',
            show: ({ row }: { row: PlatformAppVo }) => hasAuth('platform:platform-app:disable') && row.status === 2,
            text: () => '启用',
            onClick: ({ row }: { row: PlatformAppVo }) => {
              msg.confirm('确认启用该平台应用吗？').then(async () => {
                const response = await enable(row.id as number)
                if (response.code === ResultCode.SUCCESS) {
                  msg.success('启用成功')
                  await refresh()
                }
              })
            },
          },
        ],
      },
    },
  ]
}
