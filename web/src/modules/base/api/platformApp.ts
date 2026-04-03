import type { PageList, ResponseStruct } from '#/global'

export interface PlatformAppVo {
  id?: number
  name?: string
  app_id?: string
  app_secret?: string
  callback_url?: string
  status?: number
  availability_status?: string
  last_check_at?: string
  last_error_code?: string
  last_error_message?: string
  remark?: string
  created_at?: string
  updated_at?: string
}

export interface PlatformAppSearchVo {
  name?: string
  app_id?: string
  status?: number
  availability_status?: string
  [key: string]: any
}

export interface PlatformAppOptionVo {
  id?: number
  name?: string
  app_id?: string
  status?: number
  availability_status?: string
}

export function page(data: PlatformAppSearchVo): Promise<ResponseStruct<PageList<PlatformAppVo>>> {
  return useHttp().get('/admin/platform-app/list', { params: data })
}

export function options(): Promise<ResponseStruct<PlatformAppOptionVo[]>> {
  return useHttp().get('/admin/platform-app/options')
}

export function create(data: PlatformAppVo): Promise<ResponseStruct<null>> {
  return useHttp().post('/admin/platform-app', data)
}

export function save(id: number, data: PlatformAppVo): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/platform-app/${id}`, data)
}

export function enable(id: number): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/platform-app/${id}/enable`)
}

export function disable(id: number): Promise<ResponseStruct<null>> {
  return useHttp().put(`/admin/platform-app/${id}/disable`)
}
