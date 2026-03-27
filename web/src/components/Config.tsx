export default defineComponent({
  name: 'TenantWorkspaceBadge',
  setup() {
    const userStore = useUserStore()

    const scopeLabel = computed(() => {
      const userInfo = userStore.getUserInfo()
      if (userStore.isTenantUser()) {
        return userInfo?.tenant_name || '租户'
      }
      return '平台'
    })

    const roleLabel = computed(() => {
      const roles = userStore.getRoles()
      if (roles.length === 0) {
        return userStore.isTenantUser() ? '租户账号' : '平台账号'
      }

      const roleNameMap: Record<string, string> = {
        SuperAdmin: '超管',
        PlatformAdmin: '平台管理员',
        platform_admin: '平台管理员',
        TenantAdmin: '租户管理员',
        tenant_admin: '租户管理员',
        Admin: '管理员',
        admin: '管理员',
      }

      const rolePriority = [
        'SuperAdmin',
        'PlatformAdmin',
        'platform_admin',
        'TenantAdmin',
        'tenant_admin',
        'Admin',
        'admin',
      ]

      const matchedRole = rolePriority.find(role => roles.includes(role))

      return roleNameMap[matchedRole || roles[0]] || matchedRole || roles[0]
    })

    const tooltipLabel = computed(() => `${scopeLabel.value} ${roleLabel.value}`)

    return () => (
      <el-tooltip content={tooltipLabel.value} placement="bottom">
        <div class="index_header-identity max-w-[280px]">
          <span class="index_header-identity-name truncate">
            {scopeLabel.value}
          </span>
          <span class="index_header-identity-divider" aria-hidden="true">|</span>
          <span class="index_header-identity-role">
            {roleLabel.value}
          </span>
        </div>
      </el-tooltip>
    )
  },
})
