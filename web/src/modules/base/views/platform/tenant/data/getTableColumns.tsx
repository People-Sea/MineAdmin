import type { MaProTableColumns, MaProTableExpose } from '@mineadmin/pro-table'
import type { UseDialogExpose } from '@/hooks/useDialog.ts'

import { ElTag } from 'element-plus'
import { useMessage } from '@/hooks/useMessage.ts'
import { deleteByIds } from '~/base/api/tenant.ts'
import { ResultCode } from '@/utils/ResultCode.ts'
import hasAuth from '@/utils/permission/hasAuth.ts'

export default function getTableColumns(dialog: UseDialogExpose, t: any): MaProTableColumns[] {
  const dictStore = useDictStore()
  const msg = useMessage()
  const labels = {
    name: '租户名称',
    code: '租户编码',
    contactName: '联系人',
    contactPhone: '联系电话',
    createdAt: '创建时间',
  }

  return [
    { type: 'selection', showOverflowTooltip: false, label: () => t('crud.selection') },
    { type: 'index' },
    { label: () => labels.name, prop: 'name', minWidth: '180px' },
    { label: () => labels.code, prop: 'code', minWidth: '160px' },
    { label: () => labels.contactName, prop: 'contact_name', minWidth: '140px' },
    { label: () => labels.contactPhone, prop: 'contact_phone', minWidth: '140px' },
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
    { label: () => labels.createdAt, prop: 'created_at', minWidth: '180px' },
    {
      type: 'operation',
      label: () => t('crud.operation'),
      width: '200px',
      operationConfigure: {
        type: 'tile',
        actions: [
          {
            name: 'edit',
            icon: 'material-symbols:person-edit',
            show: () => hasAuth('platform:tenant:update'),
            text: () => t('crud.edit'),
            onClick: ({ row }) => {
              dialog.setTitle(t('crud.edit'))
              dialog.open({ formType: 'edit', data: row })
            },
          },
          {
            name: 'del',
            show: () => hasAuth('platform:tenant:delete'),
            icon: 'mdi:delete',
            text: () => t('crud.delete'),
            onClick: ({ row }, proxy: MaProTableExpose) => {
              msg.delConfirm(t('crud.delDataMessage')).then(async () => {
                const response = await deleteByIds([row.id])
                if (response.code === ResultCode.SUCCESS) {
                  msg.success(t('crud.delSuccess'))
                  await proxy.refresh()
                }
              })
            },
          },
        ],
      },
    },
  ]
}
