import useTenantWorkspaceStore from '@/store/modules/useTenantWorkspaceStore.ts'
import { useMessage } from '@/hooks/useMessage.ts'

export default defineComponent({
  name: 'TenantProjectSwitch',
  setup() {
    const workspaceStore = useTenantWorkspaceStore()
    const msg = useMessage()
    const loading = ref(false)

    onMounted(async () => {
      loading.value = true
      try {
        await workspaceStore.init()
      }
      finally {
        loading.value = false
      }
    })

    return () => (
      workspaceStore.isTenantMode
        ? (
            <div class="hidden items-center lg:flex">
              <el-select
                modelValue={workspaceStore.currentProjectId}
                placeholder="选择项目"
                filterable
                size="small"
                loading={loading.value}
                class="!w-[180px]"
                disabled={workspaceStore.projectOptions.length === 0}
                onUpdate:modelValue={async (value: number) => {
                  loading.value = true
                  try {
                    await workspaceStore.changeProject(value)
                  }
                  catch (error) {
                    msg.alertError(error)
                  }
                  finally {
                    loading.value = false
                  }
                }}
              >
                {workspaceStore.projectOptions.map(item => (
                  <el-option
                    key={item.id}
                    value={item.id}
                    label={item.is_default === 1 ? `${item.name}（主项目）` : item.name}
                  />
                ))}
              </el-select>
            </div>
          )
        : null
    )
  },
})
