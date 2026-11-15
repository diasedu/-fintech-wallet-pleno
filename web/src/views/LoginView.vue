<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { api, auth, ApiError } from '../services/api'
import RegisterModal from '../components/RegisterModal.vue'

const router = useRouter()

// login
const form = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref<string | null>(null)

// cadastro
const showRegister = ref(false)
const registerLoading = ref(false)
const registerError = ref<string | null>(null)
const registerSuccess = ref(false)

async function onSubmit() {
  loading.value = true
  error.value = null
  registerSuccess.value = false
  try {
    const { data } = await api.post<{ data: { token: string } }>('/autenticar', form)
    auth.setToken(data.token)
    router.push('/area-logada/principal')
  } catch (e) {
    error.value =
      e instanceof ApiError ? e.message : 'Erro ao entrar. Tente novamente.'
  } finally {
    loading.value = false
  }
}

async function onRegister(payload: { name: string; email: string; password: string }) {
  registerLoading.value = true
  registerError.value = null
  try {
    await api.post('/usuarios/registrar', payload)

    form.email = payload.email // facilita o login logo em seguida
    form.password = ''
    showRegister.value = false
    registerSuccess.value = true
  } catch (e) {
    registerError.value =
      e instanceof ApiError ? e.message : 'Não foi possível criar a conta.'
  } finally {
    registerLoading.value = false
  }
}

function openRegister() {
  registerError.value = null
  showRegister.value = true
}
</script>

<template>
  <main
    class="wrapper d-flex justify-content-center align-items-center min-vh-100 shadow bg-dark"
  >
    <form
      @submit.prevent="onSubmit"
      class="card card-body bg-body-tertiary m-3 shadow-sm border border-2"
      style="width: 100%; max-width: 400px; margin: auto"
    >
      <h1 class="h4 text-center mb-3">Carteira Digital</h1>

      <div class="text-center alert alert-info">
        <small>
          <p class="mb-0">
            Faça o login para acessar sua carteira digital. Se você ainda não
            possui uma conta, clique em "Cadastre-se" para criar uma nova conta.
          </p>
        </small>
      </div>

      <div v-if="registerSuccess" class="alert alert-success py-2" role="status">
        <small>Conta criada! Faça login para continuar.</small>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="form-control"
          required
          :disabled="loading"
          placeholder="foo.bar@exemplo.com"
        />
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Senha</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          class="form-control"
          required
          :disabled="loading"
          placeholder="*********"
        />
      </div>

      <button class="btn btn-primary mb-3" type="submit" :disabled="loading">
        <span
          v-if="loading"
          class="spinner-border spinner-border-sm me-2"
          aria-hidden="true"
        ></span>
        {{ loading ? 'Entrando...' : 'Acessar a minha carteira' }}
      </button>

      <small>
        <p class="text-center mb-0">
          Não possui uma conta?
          <button
            type="button"
            class="btn btn-link p-0 align-baseline"
            @click="openRegister"
          >
            Cadastre-se
          </button>
        </p>
      </small>
    </form>

    <RegisterModal
      :open="showRegister"
      :loading="registerLoading"
      :error="registerError"
      @close="showRegister = false"
      @submit="onRegister"
    />
  </main>
</template>