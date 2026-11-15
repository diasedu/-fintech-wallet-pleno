import { reactive } from 'vue'

type ToastType = 'success' | 'danger' | 'warning' | 'info'

interface Toast {
  id: number
  message: string
  type: ToastType
}

const toasts = reactive<Toast[]>([])
let nextId = 1

function remove(id: number) {
  const index = toasts.findIndex(t => t.id === id)

  if (index !== -1) {
    toasts.splice(index, 1)
  }
}

function show(message: string, type: ToastType = 'info', timeout = 3000) {
  const id = nextId++
  toasts.push({ id, message, type })
  setTimeout(() => remove(id), timeout)
}

export function useToast() {
  return {
    toasts,
    remove,
    success: (msg: string) => show(msg, 'success'),
    error:   (msg: string) => show(msg, 'danger'),
    warning: (msg: string) => show(msg, 'warning'),
    info:    (msg: string) => show(msg, 'info'),
  }
}