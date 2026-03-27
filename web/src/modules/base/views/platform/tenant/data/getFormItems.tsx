import type { MaFormItem } from '@mineadmin/form'
import type { TenantVo } from '~/base/api/tenant.ts'
import MaDictRadio from '@/components/ma-dict-picker/ma-dict-radio.vue'

export default function getFormItems(formType: 'add' | 'edit' = 'add', t: any, model: TenantVo): MaFormItem[] {
  const labels = {
    name: '租户名称',
    code: '租户编码',
    contactName: '联系人',
    contactPhone: '联系电话',
    adminName: '租户管理员姓名',
    adminUsername: '租户管理员账号',
    adminEmail: '租户管理员邮箱',
    adminPhone: '租户管理员电话',
    adminPassword: '租户管理员密码',
  }

  if (formType === 'add') {
    model.status = 1
  }

  const items: MaFormItem[] = [
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
  ]

  if (formType === 'add') {
    items.push(
      {
        label: () => labels.adminName,
        prop: 'admin_name',
        render: 'input',
        cols: { md: 12, xs: 24 },
        renderProps: {
          placeholder: t('form.pleaseInput', { msg: labels.adminName }),
        },
        itemProps: {
          rules: [{ required: true, message: t('form.requiredInput', { msg: labels.adminName }) }],
        },
      },
      {
        label: () => labels.adminUsername,
        prop: 'admin_username',
        render: 'input',
        cols: { md: 12, xs: 24 },
        renderProps: {
          placeholder: t('form.pleaseInput', { msg: labels.adminUsername }),
        },
        itemProps: {
          rules: [{ required: true, message: t('form.requiredInput', { msg: labels.adminUsername }) }],
        },
      },
      {
        label: () => labels.adminEmail,
        prop: 'admin_email',
        render: 'input',
        cols: { md: 12, xs: 24 },
        renderProps: {
          placeholder: t('form.pleaseInput', { msg: labels.adminEmail }),
        },
      },
      {
        label: () => labels.adminPhone,
        prop: 'admin_phone',
        render: 'input',
        cols: { md: 12, xs: 24 },
        renderProps: {
          placeholder: t('form.pleaseInput', { msg: labels.adminPhone }),
        },
      },
      {
        label: () => labels.adminPassword,
        prop: 'admin_password',
        render: 'input',
        cols: { md: 12, xs: 24 },
        renderProps: {
          placeholder: t('form.pleaseInput', { msg: labels.adminPassword }),
          type: 'password',
          showPassword: true,
        },
        itemProps: {
          rules: [{ required: true, message: t('form.requiredInput', { msg: labels.adminPassword }) }],
        },
      },
    )
  }

  items.push(
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
  )

  return items
}
