<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { storeToRefs } from 'pinia'
import { useCarteiraStore } from '../stores/carteira'
import { desmascararMoeda, formatarMoeda, mascararMoeda } from '../utils/formato'

// Formulário único para depósito e saque: muda o texto, a ação e,
// no saque, a validação de saldo insuficiente antes de enviar.
const props = defineProps<{
  tipo: 'deposito' | 'saque'
}>()

const carteira = useCarteiraStore()
const { resumo, saldo } = storeToRefs(carteira)

// Texto já com a máscara brasileira (ex.: "1.234,56"); o valor numérico é derivado dele.
const valorTexto = ref('')
const valorNumero = computed(() => desmascararMoeda(valorTexto.value))
const enviando = ref(false)

const ehSaque = computed(() => props.tipo === 'saque')

const textos = computed(() =>
  ehSaque.value
    ? { titulo: 'Sacar', botao: 'Confirmar saque', enviando: 'Sacando...', classeBotao: 'btn-danger' }
    : { titulo: 'Depositar', botao: 'Confirmar depósito', enviando: 'Depositando...', classeBotao: 'btn-success' },
)

// Validação no frontend: só acusa depois que o usuário digita algum valor.
const erroValidacao = computed<string | null>(() => {
  if (valorTexto.value === '') {
    return null
  }
 
  if (valorNumero.value <= 0) { 
    return 'Informe um valor maior que zero.'
  }

  // só valida o saldo depois que ele foi carregado
  if (ehSaque.value && resumo.value && valorNumero.value > saldo.value) {
    return `Saldo insuficiente. Disponível para saque: ${formatarMoeda(saldo.value)}.`
  }

  return null
})

const podeEnviar = computed(() => valorTexto.value !== '' && !erroValidacao.value && !enviando.value)

function onInputValor(event: Event) {
  valorTexto.value = mascararMoeda((event.target as HTMLInputElement).value)
}

onMounted(() => {
  // garante o saldo na tela mesmo abrindo esta página direto pela URL
  if (!resumo.value) {
    carteira.carregarDashboard()
  }
})

async function onSubmit() {
  if (!podeEnviar.value || valorTexto.value === '') return

  const valorEnviado = valorNumero.value

  enviando.value = true

  try {
    if (ehSaque.value) {
      await carteira.sacar(valorEnviado)
    } else {
      await carteira.depositar(valorEnviado)
    }

    valorTexto.value = '' // sucesso: a mensagem da API já apareceu no toast
  } catch {
    // erro: a mensagem da API já apareceu no toast; mantém o valor para o usuário corrigir
  } finally {
    enviando.value = false
  }
}
</script>

<template>
  <div class="row justify-content-center">
    <div class="col-12" style="max-width: 440px">
      <h1 class="h3 mb-3">
        <i
          class="fa-solid me-2"
          :class="ehSaque ? 'fa-circle-arrow-up text-danger-emphasis' : 'fa-circle-arrow-down text-success-emphasis'"
          aria-hidden="true"
        ></i>{{ textos.titulo }}
      </h1>

      <form class="card card-body bg-body-tertiary shadow-sm" novalidate @submit.prevent="onSubmit">
        <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-3">
          <span class="text-body-secondary">
            <i class="fa-solid fa-wallet me-1" aria-hidden="true"></i>Saldo disponível
          </span>
          <strong class="fs-5">{{ resumo ? formatarMoeda(resumo.saldo) : '...' }}</strong>
        </div>

        <div class="mb-3">
          <label for="valor" class="form-label">Valor</label>
          <div class="input-group input-group-lg has-validation">
            <span class="input-group-text">R$</span>
            <input
              id="valor"
              :value="valorTexto"
              @input="onInputValor"
              type="text"
              class="form-control form-control-lg"
              :class="{ 'is-invalid': erroValidacao }"
              inputmode="decimal"
              placeholder="0,00"
              required
              :disabled="enviando"
              aria-describedby="valor-erro"
            />
            <div id="valor-erro" class="invalid-feedback">{{ erroValidacao }}</div>
          </div>
        </div>

        <div class="d-flex gap-2">
          <button class="btn flex-grow-1" :class="textos.classeBotao" type="submit" :disabled="!podeEnviar">
            <span v-if="enviando" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
            <i v-else class="fa-solid fa-check me-1" aria-hidden="true"></i>
            {{ enviando ? textos.enviando : textos.botao }}
          </button>
          <RouterLink class="btn btn-outline-secondary" to="/area-logada/principal">
            <i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i>Voltar
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
