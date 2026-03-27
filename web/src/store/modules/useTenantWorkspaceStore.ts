import type { TenantOptionVo } from '~/base/api/tenant'
import type { TenantProjectOptionVo } from '~/base/api/tenantProject'
import { options as tenantOptions } from '~/base/api/tenant'
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

    function getStorageKey() {
      return isTenantMode.value ? 'tenant-workspace-tenant' : 'tenant-workspace-platform'
    }

    function restore() {
      const raw = localStorage.getItem(getStorageKey())
      if (!raw) {
        return
      }

      try {
        const parsed = JSON.parse(raw)
        currentTenantId.value = parsed.currentTenantId
        currentProjectId.value = parsed.currentProjectId
      }
      catch {
        currentTenantId.value = undefined
        currentProjectId.value = undefined
      }
    }

    function persist() {
      localStorage.setItem(getStorageKey(), JSON.stringify({
        currentTenantId: currentTenantId.value,
        currentProjectId: currentProjectId.value,
      }))
    }

    async function loadTenants() {
      if (isTenantMode.value) {
        const info = userStore.getUserInfo()
        currentTenantId.value = info?.tenant_id

        tenantOptionsList.value = info?.tenant_id
          ? [{ id: info.tenant_id, name: info.tenant_name, status: 1 }]
          : []
        return
      }

      const res = await tenantOptions()
      tenantOptionsList.value = res.data ?? []

      if (!tenantOptionsList.value.some(item => item.id === currentTenantId.value)) {
        currentTenantId.value = tenantOptionsList.value[0]?.id
      }
    }

    async function loadProjects(tenantId = currentTenantId.value) {
      if (!tenantId) {
        projectOptionsList.value = []
        currentProjectId.value = undefined
        persist()
        return
      }

      const res = await tenantProjectOptions(isTenantMode.value ? { status: 1 } : { tenant_id: tenantId })

      projectOptionsList.value = res.data ?? []

      if (!projectOptionsList.value.some(item => item.id === currentProjectId.value)) {
        const lastProjectId = isTenantMode.value
          ? userStore.getUserInfo()?.last_project_id
          : undefined
        currentProjectId.value = projectOptionsList.value.find(item => item.id === lastProjectId)?.id
          ?? projectOptionsList.value.find(item => item.is_default === 1)?.id
          ?? projectOptionsList.value[0]?.id
      }

      persist()
    }

    async function init(force = false) {
      const currentScope = isTenantMode.value ? 'tenant' : 'platform'
      if (scope.value !== currentScope) {
        scope.value = currentScope
        initialized.value = false
        tenantOptionsList.value = []
        projectOptionsList.value = []
      }

      if (force) {
        initialized.value = false
      }

      if (initialized.value) {
        return
      }

      restore()
      await loadTenants()
      await loadProjects()
      initialized.value = true
    }

    async function changeTenant(tenantId?: number) {
      if (isTenantMode.value) {
        return
      }

      currentTenantId.value = tenantId
      currentProjectId.value = undefined
      await loadProjects(tenantId)
    }

    function changeProject(projectId?: number) {
      currentProjectId.value = projectId
      if (isTenantMode.value) {
        userStore.setLastProjectId(projectId)
      }
      persist()
    }

    async function refreshTenants() {
      await loadTenants()
      await loadProjects()
    }

    async function refreshProjects() {
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
