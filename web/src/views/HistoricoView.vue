<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { storeToRefs } from 'pinia'
import { useCarteiraStore } from '../stores/carteira'
import TabelaMovimentacoes from '../components/TabelaMovimentacoes.vue'

const carteira = useCarteiraStore()
const { historico, paginacao, filtros, carregando } = storeToRefs(carteira)

const periodoInvalido = computed(() =>
  filtros.value.data_inicial !== '' &&
  filtros.value.data_final !== '' &&
  filtros.value.data_final < filtros.value.data_inicial,
)

// Páginas exibidas: a atual e até duas de cada lado.
const paginas = computed(() => {
  const { pagina_atual, ultima_pagina } = paginacao.value
  const inicio = Math.max(1, pagina_atual - 2)
  const fim    = Math.min(ultima_pagina, pagina_atual + 2)

  return Array.from({ length: fim - inicio + 1 }, (_, i) => inicio + i)
})

onMounted(() => carteira.carregarHistorico())

function filtrar() {
  if (periodoInvalido.value) return

  carteira.carregarHistorico(1) // filtro novo sempre volta para a primeira página
}

function limpar() {
  carteira.limparFiltros()
  carteira.carregarHistorico(1)
}

function irPara(pagina: number) {
  if (pagina < 1 || pagina > paginacao.value.ultima_pagina || pagina === paginacao.value.pagina_atual) return

  carteira.carregarHistorico(pagina)
}
</script>

<template>
  <h1 class="h3 mb-3">
    <i class="fa-solid fa-clock-rotate-left me-2" aria-hidden="true"></i>Histórico de transações
  </h1>

  <form class="card card-body bg-body-tertiary shadow-sm mb-3" novalidate @submit.prevent="filtrar">
    <div class="row g-3 align-items-start">
      <div class="col-12 col-md-3">
        <label for="filtro-tipo" class="form-label">Tipo</label>
        <select id="filtro-tipo" v-model="filtros.tipo" class="form-select" :disabled="carregando">
          <option value="">Todos</option>
          <option value="C">Crédito</option>
          <option value="D">Débito</option>
        </select>
      </div>

      <div class="col-6 col-md-3">
        <label for="filtro-data-inicial" class="form-label">Data inicial</label>
        <input
          id="filtro-data-inicial"
          v-model="filtros.data_inicial"
          type="date"
          class="form-control"
          :disabled="carregando"
        />
      </div>

      <div class="col-6 col-md-3">
        <label for="filtro-data-final" class="form-label">Data final</label>
        <input
          id="filtro-data-final"
          v-model="filtros.data_final"
          type="date"
          class="form-control"
          :class="{ 'is-invalid': periodoInvalido }"
          :min="filtros.data_inicial || undefined"
          :disabled="carregando"
        />
        <div class="invalid-feedback">A data final deve ser igual ou posterior à data inicial.</div>
      </div>

      <div class="col-12 col-md-3">
        <span class="form-label d-none d-md-block">&nbsp;</span>
        <div class="d-flex gap-2">
          <button class="btn btn-primary" type="submit" :disabled="carregando || periodoInvalido">
            <i class="fa-solid fa-filter me-1" aria-hidden="true"></i>Filtrar
          </button>
          <button class="btn btn-outline-secondary" type="button" :disabled="carregando" @click="limpar">
            <i class="fa-solid fa-eraser me-1" aria-hidden="true"></i>Limpar
          </button>
        </div>
      </div>
    </div>
  </form>

  <div class="card bg-body-tertiary shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <span>{{ paginacao.total }} {{ paginacao.total === 1 ? 'transação' : 'transações' }}</span>
      <span v-if="carregando" class="spinner-border spinner-border-sm" role="status" aria-label="Carregando"></span>
    </div>

    <TabelaMovimentacoes
      :movimentacoes="historico"
      :vazio="carregando ? 'Carregando...' : 'Nenhuma transação encontrada para os filtros informados.'"
    />

    <div v-if="paginacao.ultima_pagina > 1" class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
      <small class="text-body-secondary">
        Página {{ paginacao.pagina_atual }} de {{ paginacao.ultima_pagina }}
      </small>

      <nav aria-label="Paginação do histórico">
        <ul class="pagination pagination-sm mb-0">
          <li class="page-item" :class="{ disabled: paginacao.pagina_atual === 1 || carregando }">
            <button class="page-link" type="button" @click="irPara(paginacao.pagina_atual - 1)">
              <i class="fa-solid fa-chevron-left me-1" aria-hidden="true"></i>Anterior
            </button>
          </li>

          <li
            v-for="p in paginas"
            :key="p"
            class="page-item"
            :class="{ active: p === paginacao.pagina_atual, disabled: carregando }"
          >
            <button class="page-link" type="button" @click="irPara(p)">{{ p }}</button>
          </li>

          <li class="page-item" :class="{ disabled: paginacao.pagina_atual === paginacao.ultima_pagina || carregando }">
            <button class="page-link" type="button" @click="irPara(paginacao.pagina_atual + 1)">
              Próxima<i class="fa-solid fa-chevron-right ms-1" aria-hidden="true"></i>
            </button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>
