import type { PageList, ResponseStruct } from '#/global'

export interface TenantProjectVo {
  id?: number
  tenant_id?: number
  tenant_name?: string
  name?: string
  is_default?: number
  status?: number
  member_ids?: number[]
  member_names?: string[]
  member_count?: number
  updated_at?: string
  remark?: string
}

export interface TenantProjectSearchVo {
  tenant_id?: number
  name?: string
  status?: number
  [key: string]: any
}

export interface TenantProjectOptionVo {
  id?: number
  tenant_id?: number
  name?: string
  is_default?: number
  status?: number
}

export function page(data: TenantProjectSearchVo): Promise<ResponseStruct<PageList<TenantProjectVo>>> {
  return useHttp().get('/admin/tenant-project/list', { params: data })
}

export function options(data: TenantProjectSearchVo = {}): Promise<ResponseStruct<TenantProjectOptionVo[]>> {
  return useHttp().get('/admin/tenant-project/options', { params: data })
}

export function create(data: TenantProjectVo): Promise<ResponseStruct<null>> {
  return useHttp().post('/admin/tenant-project', data)
}

export function save(id: number, data: TenantProjectVo): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/tenant-project/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('/admin/tenant-project', { data: ids })
}
