<script setup lang="ts">
import type { Movimentacao } from '../stores/carteira'
import { formatarDataHora, formatarMoeda } from '../utils/formato'

defineProps<{
  movimentacoes: Movimentacao[]
  vazio?: string
}>()
</script>

<template>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0" style="--bs-table-bg: transparent">
      <thead>
        <tr>
          <th scope="col">Data/hora</th>
          <th scope="col">Tipo</th>
          <th scope="col" class="text-end">Valor</th>
          <th scope="col" class="text-end">Saldo após</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="m in movimentacoes" :key="m.id">
          <td class="text-nowrap">{{ formatarDataHora(m.data) }}</td>
          <td>
            <span class="badge" :class="m.tipo === 'C' ? 'text-bg-success' : 'text-bg-danger'">
              <i
                class="fa-solid me-1"
                :class="m.tipo === 'C' ? 'fa-arrow-down' : 'fa-arrow-up'"
                aria-hidden="true"
              ></i>{{ m.tipo === 'C' ? 'Crédito' : 'Débito' }}
            </span>
          </td>
          <td class="text-end text-nowrap" :class="m.tipo === 'C' ? 'text-success-emphasis' : 'text-danger-emphasis'">
            {{ m.tipo === 'C' ? '+' : '−' }} {{ formatarMoeda(m.valor) }}
          </td>
          <td class="text-end text-nowrap fw-semibold">{{ formatarMoeda(m.saldo_apos) }}</td>
        </tr>
        <tr v-if="movimentacoes.length === 0">
          <td colspan="4" class="text-center text-body-secondary py-4">
            {{ vazio ?? 'Nenhuma transação encontrada.' }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
