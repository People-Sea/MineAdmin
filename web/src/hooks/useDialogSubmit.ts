import { ResultCode } from '@/utils/ResultCode.ts'
import { useMessage } from '@/hooks/useMessage.ts'

type MaybePromise<T> = T | Promise<T>

interface DialogSubmitOptions<T = any> {
  validate?: () => MaybePromise<unknown>
  submit: () => Promise<T>
  successMessage?: string
  close?: () => void
  onSuccess?: (response: T) => MaybePromise<void>
  onError?: (error: unknown) => MaybePromise<void>
}

export default function useDialogSubmit() {
  const msg = useMessage()

  return async function submitDialog<T = any>(options: DialogSubmitOptions<T>): Promise<boolean> {
    try {
      // Dialog 提交统一走这里，页面只关心校验、提交和成功后的刷新动作。
      await options.validate?.()
      const response: any = await options.submit()

      if (response?.code !== ResultCode.SUCCESS) {
        msg.error(response)
        return false
      }

      if (options.successMessage) {
        msg.success(options.successMessage)
      }

      options.close?.()
      await options.onSuccess?.(response)
      return true
    }
    catch (error) {
      await options.onError?.(error)
      return false
    }
  }
}
