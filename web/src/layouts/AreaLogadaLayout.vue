<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { api, auth } from '../services/api'
import { useCarteiraStore } from '../stores/carteira'

const router   = useRouter()
const carteira = useCarteiraStore()
const saindo   = ref(false)

async function sair() {
  saindo.value = true
  try {
    await api.post('/area-logada/logout') // revoga o token no servidor
  } catch {
    // token já inválido: basta limpar localmente
  } finally {
    auth.clearToken()
    carteira.limpar()
    router.push('/login')
  }
}
</script>

<template>
  <!-- Fundo escuro como no login; data-bs-theme="dark" ajusta textos, cartões, tabelas e campos. -->
  <div class="min-vh-100 bg-dark text-body" data-bs-theme="dark">
    <header class="bg-body-tertiary border-bottom shadow-sm">
      <nav class="container d-flex flex-wrap align-items-center gap-2 py-2" aria-label="Área logada">
        <RouterLink class="navbar-brand fw-semibold text-body me-3" to="/area-logada/principal">
          <i class="fa-solid fa-wallet me-2 text-primary" aria-hidden="true"></i>Carteira Digital
        </RouterLink>

        <ul class="nav nav-pills me-auto">
          <li class="nav-item">
            <RouterLink class="nav-link" active-class="active" to="/area-logada/principal">
              <i class="fa-solid fa-gauge-high me-1" aria-hidden="true"></i>Dashboard
            </RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink class="nav-link" active-class="active" to="/area-logada/depositar">
              <i class="fa-solid fa-circle-arrow-down me-1" aria-hidden="true"></i>Depositar
            </RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink class="nav-link" active-class="active" to="/area-logada/sacar">
              <i class="fa-solid fa-circle-arrow-up me-1" aria-hidden="true"></i>Sacar
            </RouterLink>
          </li>
          <li class="nav-item">
            <RouterLink class="nav-link" active-class="active" to="/area-logada/historico">
              <i class="fa-solid fa-clock-rotate-left me-1" aria-hidden="true"></i>Histórico
            </RouterLink>
          </li>
        </ul>

        <button class="btn btn-outline-danger btn-sm" :disabled="saindo" @click="sair">
          <i class="fa-solid fa-right-from-bracket me-1" aria-hidden="true"></i>
          {{ saindo ? 'Saindo...' : 'Sair' }}
        </button>
      </nav>
    </header>

    <main class="container py-4">
      <RouterView />
    </main>
  </div>
</template>
