<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { api, auth } from '../services/api'

const router = useRouter()
const saindo = ref(false)

async function sair() {
  saindo.value = true
  try {
    await api.post('/area-logada/logout') // revoga o token no servidor
  } catch {
    // token já inválido: basta limpar localmente
  } finally {
    auth.clearToken()
    router.push('/login')
  }
}
</script>

<template>
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center">
      <h1 class="h3 mb-0">Estou logado! 🎉</h1>
      <button class="btn btn-outline-danger" :disabled="saindo" @click="sair">
        {{ saindo ? 'Saindo...' : 'Sair' }}
      </button>
    </div>
  </main>
</template>
