const HANDLED_HTTP_ERROR = Symbol('handled-http-error')
const RECENT_ERROR_TTL = 2000
const recentErrorMessages = new Map<string, number>()

function normalizeMessage(message: string): string {
  return message.trim()
}

function cleanupRecentErrors(now = Date.now()) {
  recentErrorMessages.forEach((timestamp, message) => {
    if (now - timestamp >= RECENT_ERROR_TTL) {
      recentErrorMessages.delete(message)
    }
  })
}

function getObjectMessage(error: Record<string, any>): unknown {
  return error.message
    ?? error.error
    ?? error.msg
    ?? error.response?.data?.message
    ?? error.response?.data?.error
    ?? error.data?.message
    ?? error.data?.error
}

export function extractErrorMessage(error: unknown, fallback = '服务器错误'): string {
  if (typeof error === 'string') {
    const message = normalizeMessage(error)
    return message || fallback
  }

  if (error instanceof Error) {
    const message = normalizeMessage(error.message)
    return message || fallback
  }

  if (error && typeof error === 'object') {
    const candidate = getObjectMessage(error as Record<string, any>)
    if (typeof candidate === 'string') {
      const message = normalizeMessage(candidate)
      return message || fallback
    }
  }

  return fallback
}

export function markHandledError(error: unknown): void {
  const now = Date.now()
  cleanupRecentErrors(now)

  if (error && typeof error === 'object') {
    // 给当前错误对象打标，避免同一条异常在页面层再次弹窗。
    Reflect.set(error as object, HANDLED_HTTP_ERROR, true)
  }

  const message = extractErrorMessage(error, '')
  if (message) {
    recentErrorMessages.set(normalizeMessage(message), now)
  }
}

export function isHandledError(error: unknown): boolean {
  cleanupRecentErrors()

  if (error && typeof error === 'object' && Reflect.get(error as object, HANDLED_HTTP_ERROR) === true) {
    return true
  }

  const message = extractErrorMessage(error, '')
  if (!message) {
    return false
  }

  // 某些错误会在 Promise 链里被重新包装，这里再按短时间相同文案兜底去重一次。
  return recentErrorMessages.has(normalizeMessage(message))
}
