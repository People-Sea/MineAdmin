import type { PageList, ResponseStruct } from '#/global'

export interface TenantVo {
  id?: number
  name?: string
  code?: string
  contact_name?: string
  contact_phone?: string
  status?: number
  remark?: string
  created_at?: string
  updated_at?: string
}

export interface TenantSearchVo {
  name?: string
  code?: string
  contact_name?: string
  contact_phone?: string
  status?: number
  [key: string]: any
}

export function page(data: TenantSearchVo): Promise<ResponseStruct<PageList<TenantVo>>> {
  return useHttp().get('/admin/tenant/list', { params: data })
}

export function create(data: TenantVo): Promise<ResponseStruct<null>> {
  return useHttp().post('/admin/tenant', data)
}

export function save(id: number, data: TenantVo): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/tenant/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('/admin/tenant', { data: ids })
}
