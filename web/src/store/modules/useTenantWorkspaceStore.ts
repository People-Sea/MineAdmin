import type { TenantOptionVo } from '~/base/api/tenant'
import type { TenantProjectOptionVo } from '~/base/api/tenantProject'
import { options as tenantProjectOptions } from '~/base/api/tenantProject'

const useTenantWorkspaceStore = defineStore(
  'useTenantWorkspaceStore',
  () => {
    const userStore = useUserStore()
    const initialized = ref(false)
    const scope = ref<'platform' | 'tenant'>(userStore.isTenantUser() ? 'tenant' : 'platform')
    const tenantOptionsList = ref<TenantOptionVo[]>([])
    const projectOptionsList = ref<TenantProjectOptionVo[]>([])
    const currentTenantId = ref<number | undefined>(undefined)
    const currentProjectId = ref<number | undefined>(undefined)
    const isTenantMode = computed(() => userStore.isTenantUser())

    const currentTenant = computed(() =>
      tenantOptionsList.value.find(item => item.id === currentTenantId.value),
    )

    const currentProject = computed(() =>
      projectOptionsList.value.find(item => item.id === currentProjectId.value),
    )

    const currentTenantName = computed(() => currentTenant.value?.name ?? '')
    const currentProjectName = computed(() => currentProject.value?.name ?? '')

    function resetWorkspace() {
      tenantOptionsList.value = []
      projectOptionsList.value = []
      currentTenantId.value = undefined
      currentProjectId.value = undefined
    }

    function getStorageKey() {
      return isTenantMode.value ? 'tenant-workspace-tenant' : 'tenant-workspace-platform'
    }

    function restore() {
      currentTenantId.value = undefined
      currentProjectId.value = undefined
    }

    function persist() {
      if (!isTenantMode.value) {
        return
      }

      localStorage.setItem(getStorageKey(), JSON.stringify({
        currentTenantId: currentTenantId.value,
        currentProjectId: currentProjectId.value,
      }))
    }

    async function loadTenants() {
      if (!isTenantMode.value) {
        resetWorkspace()
        return
      }

      const info = userStore.getUserInfo()
      currentTenantId.value = info?.tenant_id
      tenantOptionsList.value = info?.tenant_id
        ? [{ id: info.tenant_id, name: info.tenant_name, status: 1 }]
        : []
    }

    async function loadProjects(tenantId = currentTenantId.value) {
      if (!isTenantMode.value) {
        projectOptionsList.value = []
        currentProjectId.value = undefined
        return
      }

      if (!tenantId) {
        projectOptionsList.value = []
        currentProjectId.value = undefined
        persist()
        return
      }

      const res = await tenantProjectOptions({ status: 1 })

      projectOptionsList.value = res.data ?? []

      const lastProjectId = userStore.getUserInfo()?.last_project_id
      const normalizedLastProjectId = lastProjectId ?? undefined

      currentProjectId.value = projectOptionsList.value.find(item => item.id === normalizedLastProjectId)?.id
        ?? projectOptionsList.value.find(item => item.is_default === 1)?.id
        ?? projectOptionsList.value[0]?.id

      if (currentProjectId.value !== normalizedLastProjectId) {
        await userStore.setLastProjectId(currentProjectId.value, true)
      }

      persist()
    }

    async function init(force = false) {
      const currentScope = isTenantMode.value ? 'tenant' : 'platform'
      if (scope.value !== currentScope) {
        scope.value = currentScope
        initialized.value = false
        resetWorkspace()
      }

      if (force) {
        initialized.value = false
      }

      if (initialized.value) {
        return
      }

      restore()
      if (!isTenantMode.value) {
        initialized.value = true
        return
      }

      await loadTenants()
      await loadProjects()
      initialized.value = true
    }

    async function changeTenant(tenantId?: number) {
      if (!isTenantMode.value) {
        return
      }

      currentTenantId.value = tenantId
      currentProjectId.value = undefined
      await loadProjects(tenantId)
    }

    async function changeProject(projectId?: number) {
      if (!isTenantMode.value) {
        return
      }

      const nextProjectId = projectId ?? undefined
      await userStore.setLastProjectId(nextProjectId, true)
      currentProjectId.value = nextProjectId
      persist()
    }

    async function refreshTenants() {
      if (!isTenantMode.value) {
        return
      }

      await loadTenants()
      await loadProjects()
    }

    async function refreshProjects() {
      if (!isTenantMode.value) {
        return
      }

      await loadProjects()
    }

    return {
      initialized,
      tenantOptions: tenantOptionsList,
      projectOptions: projectOptionsList,
      scope,
      isTenantMode,
      currentTenantId,
      currentProjectId,
      currentTenant,
      currentProject,
      currentTenantName,
      currentProjectName,
      init,
      changeTenant,
      changeProject,
      refreshTenants,
      refreshProjects,
    }
  },
)

export default useTenantWorkspaceStore
