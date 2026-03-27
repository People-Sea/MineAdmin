import type { MaSearchItem } from '@mineadmin/search'
import MaDictSelect from '@/components/ma-dict-picker/ma-dict-select.vue'

export default function getSearchItems(t: any): MaSearchItem[] {
  const labels = {
    name: '租户名称',
    code: '租户编码',
  }

  return [
    {
      label: () => labels.name,
      prop: 'name',
      render: 'input',
    },
    {
      label: () => labels.code,
      prop: 'code',
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
  ]
}
