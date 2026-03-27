import type { MaFormItem } from '@mineadmin/form'
import type { TenantVo } from '~/base/api/tenant.ts'
import MaDictRadio from '@/components/ma-dict-picker/ma-dict-radio.vue'

export default function getFormItems(formType: 'add' | 'edit' = 'add', t: any, model: TenantVo): MaFormItem[] {
  const labels = {
    name: '租户名称',
    code: '租户编码',
    contactName: '联系人',
    contactPhone: '联系电话',
  }

  if (formType === 'add') {
    model.status = 1
  }

  return [
    {
      label: () => labels.name,
      prop: 'name',
      render: 'input',
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: labels.name }),
      },
      itemProps: {
        rules: [{ required: true, message: t('form.requiredInput', { msg: labels.name }) }],
      },
    },
    {
      label: () => labels.code,
      prop: 'code',
      render: 'input',
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: labels.code }),
      },
      itemProps: {
        rules: [{ required: true, message: t('form.requiredInput', { msg: labels.code }) }],
      },
    },
    {
      label: () => labels.contactName,
      prop: 'contact_name',
      render: 'input',
      cols: { md: 12, xs: 24 },
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: labels.contactName }),
      },
    },
    {
      label: () => labels.contactPhone,
      prop: 'contact_phone',
      render: 'input',
      cols: { md: 12, xs: 24 },
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: labels.contactPhone }),
      },
    },
    {
      label: () => t('crud.status'),
      prop: 'status',
      render: () => MaDictRadio,
      cols: { md: 12, xs: 24 },
      renderProps: {
        dictName: 'system-status',
      },
    },
    {
      label: () => t('crud.remark'),
      prop: 'remark',
      render: 'input',
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: t('crud.remark') }),
        type: 'textarea',
      },
    },
  ]
}
