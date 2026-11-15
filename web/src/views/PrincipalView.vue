<script setup lang="ts">
import { onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useCarteiraStore } from '../stores/carteira'
import { formatarMoeda } from '../utils/formato'
import TabelaMovimentacoes from '../components/TabelaMovimentacoes.vue'

const carteira = useCarteiraStore()
const { resumo, ultimas, carregando } = storeToRefs(carteira)

onMounted(() => carteira.carregarDashboard())
</script>

<template>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Dashboard</h1>

    <div class="d-flex gap-2">
      <RouterLink class="btn btn-success" to="/area-logada/depositar">
        <i class="fa-solid fa-circle-arrow-down me-1" aria-hidden="true"></i>Depositar
      </RouterLink>
      <RouterLink class="btn btn-outline-danger" to="/area-logada/sacar">
        <i class="fa-solid fa-circle-arrow-up me-1" aria-hidden="true"></i>Sacar
      </RouterLink>
    </div>
  </div>

  <div v-if="!resumo && carregando" class="text-center text-body-secondary py-5">
    <span class="spinner-border" aria-hidden="true"></span>
    <p class="mt-2 mb-0">Carregando a sua carteira...</p>
  </div>

  <!-- A carga falhou (a mensagem do erro foi para o toast): oferece nova tentativa. -->
  <div v-else-if="!resumo" class="text-center py-5">
    <button class="btn btn-outline-primary" @click="carteira.carregarDashboard()">
      <i class="fa-solid fa-rotate-right me-1" aria-hidden="true"></i>Tentar novamente
    </button>
  </div>

  <template v-if="resumo">
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-4">
        <div class="card h-100 text-bg-primary border-0 shadow-sm">
          <div class="card-body">
            <p class="mb-1"><i class="fa-solid fa-wallet me-1" aria-hidden="true"></i>Saldo atual</p>
            <p class="h3 mb-0">{{ formatarMoeda(resumo.saldo) }}</p>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card h-100 bg-body-tertiary shadow-sm">
          <div class="card-body">
            <p class="mb-1 text-body-secondary">
              <i class="fa-solid fa-circle-arrow-down me-1" aria-hidden="true"></i>Total depositado no mês
            </p>
            <p class="h3 mb-0 text-success-emphasis">{{ formatarMoeda(resumo.total_depositado_mes) }}</p>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-4">
        <div class="card h-100 bg-body-tertiary shadow-sm">
          <div class="card-body">
            <p class="mb-1 text-body-secondary">
              <i class="fa-solid fa-circle-arrow-up me-1" aria-hidden="true"></i>Total sacado no mês
            </p>
            <p class="h3 mb-0 text-danger-emphasis">{{ formatarMoeda(resumo.total_sacado_mes) }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="card bg-body-tertiary shadow-sm">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">
          <i class="fa-solid fa-list me-1" aria-hidden="true"></i>Últimas 5 transações
        </h2>
        <RouterLink to="/area-logada/historico">
          <i class="fa-solid fa-clock-rotate-left me-1" aria-hidden="true"></i>Ver histórico completo
        </RouterLink>
      </div>

      <TabelaMovimentacoes :movimentacoes="ultimas" vazio="Você ainda não fez nenhuma transação." />
    </div>
  </template>
</template>
