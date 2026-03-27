import type { PageList, ResponseStruct } from '#/global'

export interface TenantMemberVo {
  id?: number
  tenant_id?: number
  tenant_name?: string
  name?: string
  username?: string
  email?: string
  phone?: string
  password?: string
  role_id?: number
  role?: string
  role_label?: string
  status?: number
  project_ids?: number[]
  project_names?: string[]
  project_count?: number
  updated_at?: string
  remark?: string
}

export interface TenantMemberSearchVo {
  tenant_id?: number
  name?: string
  username?: string
  status?: number
  [key: string]: any
}

export interface TenantMemberOptionVo {
  id?: number
  tenant_id?: number
  name?: string
  username?: string
  role_id?: number
  role?: string
  role_label?: string
  status?: number
}

export interface TenantRoleOptionVo {
  id: number
  name: string
  code: string
  remark?: string
}

export function page(data: TenantMemberSearchVo): Promise<ResponseStruct<PageList<TenantMemberVo>>> {
  return useHttp().get('/admin/tenant-member/list', { params: data })
}

export function options(data: TenantMemberSearchVo = {}): Promise<ResponseStruct<TenantMemberOptionVo[]>> {
  return useHttp().get('/admin/tenant-member/options', { params: data })
}

export function roleOptions(): Promise<ResponseStruct<TenantRoleOptionVo[]>> {
  return useHttp().get('/admin/tenant-member/role-options')
}

export function create(data: TenantMemberVo): Promise<ResponseStruct<null>> {
  return useHttp().post('/admin/tenant-member', data)
}

export function save(id: number, data: TenantMemberVo): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/tenant-member/${id}`, data)
}

export function deleteByIds(ids: number[]): Promise<ResponseStruct<null>> {
  return useHttp().delete('/admin/tenant-member', { data: ids })
}
