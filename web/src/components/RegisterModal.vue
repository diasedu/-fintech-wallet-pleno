<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue'
import { usePoliticaSenha } from '../composables/usePoliticaSenha'

const props = defineProps<{
  open: boolean
  loading?: boolean
  error?: string | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: { name: string; email: string; password: string; password_confirmation: string }): void
}>()

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const tentouEnviar = ref(false)

const { min, mensagem, patternSource, avaliarForca, avaliarCriterios } = usePoliticaSenha()

const forca = computed(() => avaliarForca(form.password))
const criterios = computed(() => avaliarCriterios(form.password))

const senhasConferem = computed(
  () => form.password_confirmation.length === 0 || form.password === form.password_confirmation
)

const podeEnviar = computed(() => forca.value.valida && form.password === form.password_confirmation)

function onSubmit() {
  tentouEnviar.value = true
  if (!podeEnviar.value) return

  emit('submit', { ...form })
}

function onBackdropClick() {
  if (!props.loading) emit('close')
}

// limpa o formulário sempre que o modal fecha
watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) {
      tentouEnviar.value = false
      Object.assign(form, { name: '', email: '', password: '', password_confirmation: '' })
    }
  }
)
</script>

<template>
  <template v-if="open">
    <div
      class="modal fade show d-block"
      tabindex="-1"
      role="dialog"
      aria-modal="true"
      @click.self="onBackdropClick"
    >
      <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" @submit.prevent="onSubmit">
          <div class="modal-header">
            <h2 class="modal-title h5">Criar conta</h2>
            <button
              type="button"
              class="btn-close"
              aria-label="Fechar"
              :disabled="loading"
              @click="emit('close')"
            ></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="reg-nome" class="form-label">Nome</label>
              <input
                id="reg-nome"
                v-model="form.name"
                type="text"
                class="form-control"
                maxlength="255"
                required
                :disabled="loading"
              />
            </div>

            <div class="mb-3">
              <label for="reg-email" class="form-label">E-mail</label>
              <input
                id="reg-email"
                v-model="form.email"
                type="email"
                class="form-control"
                maxlength="255"
                required
                :disabled="loading"
              />
            </div>

            <div class="mb-3">
              <label for="reg-senha" class="form-label">Senha</label>
              <input
                id="reg-senha"
                v-model="form.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': tentouEnviar && !forca.valida }"
                :minlength="min"
                :pattern="patternSource"
                :title="mensagem"
                maxlength="255"
                required
                :disabled="loading"
              />

              <div class="progress mt-2" style="height: 6px" role="presentation">
                <div
                  class="progress-bar"
                  :class="forca.cor"
                  :style="{ width: forca.percentual + '%' }"
                  role="progressbar"
                  :aria-valuenow="forca.percentual"
                  aria-valuemin="0"
                  aria-valuemax="100"
                ></div>
              </div>
              <small class="text-muted">Força da senha: {{ forca.label }}</small>

              <div class="alert alert-info py-2 px-3 mt-2 mb-0 small" role="status">
                <p class="mb-1 fw-semibold">A senha precisa ter:</p>
                <ul class="list-unstyled mb-0">
                  <li
                    v-for="criterio in criterios"
                    :key="criterio.chave"
                    :class="criterio.atendido ? 'text-success' : 'text-body-secondary'"
                  >
                    <i
                      :class="criterio.atendido ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'"
                      class="me-2"
                      aria-hidden="true"
                    ></i>
                    {{ criterio.label }}
                    <span class="fst-italic">({{ criterio.exemplo }})</span>
                  </li>
                </ul>
              </div>

              <div v-if="tentouEnviar && !forca.valida" class="invalid-feedback d-block">
                {{ mensagem }}
              </div>
            </div>

            <div class="mb-3">
              <label for="reg-senha-confirmacao" class="form-label">Confirmar senha</label>
              <input
                id="reg-senha-confirmacao"
                v-model="form.password_confirmation"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': (tentouEnviar || form.password_confirmation) && !senhasConferem }"
                maxlength="255"
                required
                :disabled="loading"
              />
              <div v-if="(tentouEnviar || form.password_confirmation) && !senhasConferem" class="invalid-feedback d-block">
                As senhas não coincidem.
              </div>
            </div>

            <div v-if="error" class="alert alert-danger mb-0" role="alert">
              {{ error }}
            </div>
          </div>

          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-outline-secondary"
              :disabled="loading"
              @click="emit('close')"
            >
              <i class="fa-solid fa-xmark me-1" aria-hidden="true"></i>Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm me-1"
                aria-hidden="true"
              ></span>
              <i v-else class="fa-solid fa-user-plus me-1" aria-hidden="true"></i>
              {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal-backdrop fade show"></div>
  </template>
</template>