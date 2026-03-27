import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'

export default defineComponent({
  name: 'TenantWorkspaceBadge',
  setup() {
    const workspaceStore = useTenantWorkspaceStore()

    onMounted(() => {
      workspaceStore.init().catch(() => {})
    })

    return () => (
      <el-tooltip content={workspaceStore.currentTenantName || '暂无租户'} placement="bottom">
        <div class="index_header-cc-all max-w-[160px] truncate">
          {workspaceStore.currentTenantName || '暂无租户'}
        </div>
      </el-tooltip>
    )
  },
})
