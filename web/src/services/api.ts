import { useToast } from '../composables/useToast'

const BASE_URL  = '/api'
const TOKEN_KEY = 'token'
const toast = useToast()

export class ApiError extends Error {
  status: number
  errors?: Record<string, string[]>

  constructor(status: number, message: string, errors?: Record<string, string[]>) {
    super(message)
    this.status = status
    this.errors = errors
  }
}

interface RequestOptions extends RequestInit {
  /** não exibe toasts (ex.: checagem de sessão feita pelo router) */
  silent?: boolean
}

// Token de API do Sanctum: recebido no login e enviado em "Authorization: Bearer ..."
export const auth = {
  getToken:   ()              => localStorage.getItem(TOKEN_KEY),
  setToken:   (token: string) => localStorage.setItem(TOKEN_KEY, token),
  clearToken: ()              => localStorage.removeItem(TOKEN_KEY),
}

async function request<T>(path: string, { silent, ...options }: RequestOptions = {}): Promise<T> {
  const token = auth.getToken()

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    'Accept'      : 'application/json',
  }

  if (token) {
    headers.Authorization = `Bearer ${token}`
  }

  const res  = await fetch(`${BASE_URL}${path}`, { ...options, headers })
  const data = res.status === 204 ? null : await res.json().catch(() => null)

  if (!res.ok) {
    // 401 com token enviado = token inválido, expirado ou revogado: descarta
    if (res.status === 401 && token) {
      auth.clearToken()
    }

    const message = data?.message ?? 'Erro na requisição'
    if (!silent) toast.error(message)
    throw new ApiError(res.status, message, data?.errors)
  }

  if (!silent) toast.success(data?.message ?? 'Sua solicitação foi processada com sucesso!')

  return data as T
}

export const api = {
  get:    <T>(path: string, options?: RequestOptions) => request<T>(path, options),
  post:   <T>(path: string, body?: unknown)  => request<T>(path, { method: 'POST',  body: JSON.stringify(body) }),
  put:    <T>(path: string, body?: unknown)  => request<T>(path, { method: 'PUT',   body: JSON.stringify(body) }),
  delete: <T>(path: string)                  => request<T>(path, { method: 'DELETE' }),
}
