import type { MaFormItem } from '@mineadmin/form'
import type { PlatformAppVo } from '~/base/api/platformApp.ts'
import MaDictRadio from '@/components/ma-dict-picker/ma-dict-radio.vue'

export default function getFormItems(formType: 'add' | 'edit' = 'add', t: any, model: PlatformAppVo): MaFormItem[] {
  if (formType === 'add') {
    model.status = 1
  }

  const appSecretPlaceholder = formType === 'add'
    ? '请输入应用密钥'
    : '留空则不修改应用密钥'

  return [
    {
      label: () => '应用名称',
      prop: 'name',
      render: 'input',
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: '应用名称' }),
      },
      itemProps: {
        rules: [{ required: true, message: t('form.requiredInput', { msg: '应用名称' }) }],
      },
    },
    {
      label: () => '应用 ID',
      prop: 'app_id',
      render: 'input',
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: '应用 ID' }),
      },
      itemProps: {
        rules: [{ required: true, message: t('form.requiredInput', { msg: '应用 ID' }) }],
      },
    },
    {
      label: () => '应用密钥',
      prop: 'app_secret',
      render: 'input',
      renderProps: {
        placeholder: appSecretPlaceholder,
        type: 'password',
        showPassword: true,
      },
      itemProps: {
        rules: formType === 'add'
          ? [{ required: true, message: t('form.requiredInput', { msg: '应用密钥' }) }]
          : [],
      },
    },
    {
      label: () => '回调地址',
      prop: 'callback_url',
      render: 'input',
      renderProps: {
        placeholder: t('form.pleaseInput', { msg: '回调地址' }),
      },
      itemProps: {
        rules: [{ required: true, message: t('form.requiredInput', { msg: '回调地址' }) }],
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
