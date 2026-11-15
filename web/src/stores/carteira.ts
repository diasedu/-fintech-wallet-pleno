import { computed, reactive, ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../services/api'

export type TipoMovimentacao = 'C' | 'D'

export interface Movimentacao {
  id: number
  tipo: TipoMovimentacao
  data: string
  valor: string
  saldo_apos: string
}

export interface Resumo {
  saldo: string
  total_depositado_mes: string
  total_sacado_mes: string
}

export interface Paginacao {
  pagina_atual: number
  por_pagina: number
  total: number
  ultima_pagina: number
}

export interface FiltrosHistorico {
  tipo: '' | TipoMovimentacao
  data_inicial: string
  data_final: string
}

interface RespostaMovimentacoes {
  data: {
    resumo: Resumo
    movimentacoes: Movimentacao[]
    paginacao: Paginacao
  }
}

const ULTIMAS_NO_DASHBOARD = 5
const POR_PAGINA_HISTORICO = 10

const paginacaoVazia = (): Paginacao => ({
  pagina_atual: 1,
  por_pagina: POR_PAGINA_HISTORICO,
  total: 0,
  ultima_pagina: 1,
})

export const useCarteiraStore = defineStore('carteira', () => {
  // estado
  const resumo    = ref<Resumo | null>(null)
  const ultimas   = ref<Movimentacao[]>([])
  const historico = ref<Movimentacao[]>([])
  const paginacao = ref<Paginacao>(paginacaoVazia())
  const filtros   = reactive<FiltrosHistorico>({ tipo: '', data_inicial: '', data_final: '' })

  const carregando = ref(false)

  // derivados
  const saldo = computed(() => Number(resumo.value?.saldo ?? 0))

  // Todo feedback de requisição aparece no toast, exibido pelo próprio "api".
  // As consultas usam "silentSuccess": carregar dados não gera toast de sucesso, mas erro gera.
  async function buscarMovimentacoes(parametros: Record<string, string | number>) {
    const query = new URLSearchParams()

    for (const [chave, valor] of Object.entries(parametros)) {
      if (valor !== '') query.set(chave, String(valor))
    }

    carregando.value = true

    try {
      const { data } = await api.get<RespostaMovimentacoes>(
        `/area-logada/carteira/movimentacoes?${query}`,
        { silentSuccess: true },
      )

      resumo.value = data.resumo
      return data
    } catch {
      return null // o toast de erro já foi exibido pelo "api"
    } finally {
      carregando.value = false
    }
  }

  /** Saldo, totais do mês e as últimas 5 movimentações. */
  async function carregarDashboard() {
    const data = await buscarMovimentacoes({ por_pagina: ULTIMAS_NO_DASHBOARD })

    if (data) ultimas.value = data.movimentacoes
  }

  /** Histórico paginado, aplicando os filtros atuais. */
  async function carregarHistorico(pagina = 1) {
    const data = await buscarMovimentacoes({ ...filtros, pagina, por_pagina: POR_PAGINA_HISTORICO })

    if (data) {
      historico.value = data.movimentacoes
      paginacao.value = data.paginacao
    }
  }

  function limparFiltros() {
    Object.assign(filtros, { tipo: '', data_inicial: '', data_final: '' })
  }

  // Depósito e saque: o toast de sucesso/erro é exibido pelo próprio "api".
  // Em caso de erro a ApiError é propagada para o formulário saber que não concluiu.
  async function depositar(valor: number) {
    await api.post('/area-logada/carteira/depositar', { valor })
    await carregarDashboard() // atualiza saldo e resumo
  }

  async function sacar(valor: number) {
    await api.post('/area-logada/carteira/sacar', { valor })
    await carregarDashboard()
  }

  /** Usado no logout, para não sobrar dado de um usuário na sessão do próximo. */
  function limpar() {
    resumo.value    = null
    ultimas.value   = []
    historico.value = []
    paginacao.value = paginacaoVazia()
    limparFiltros()
  }

  return {
    resumo, ultimas, historico, paginacao, filtros, carregando, saldo,
    carregarDashboard, carregarHistorico, limparFiltros, depositar, sacar, limpar,
  }
})
