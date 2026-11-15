<script setup lang="ts">
import { reactive, watch } from 'vue'

const props = defineProps<{
  open: boolean
  loading?: boolean
  error?: string | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'submit', payload: { name: string; email: string; password: string }): void
}>()

const form = reactive({ name: '', email: '', password: '' })

function onSubmit() {
  emit('submit', { ...form })
}

function onBackdropClick() {
  if (!props.loading) emit('close')
}

// limpa o formulário sempre que o modal fecha
watch(
  () => props.open,
  (isOpen) => {
    if (!isOpen) Object.assign(form, { name: '', email: '', password: '' })
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
                minlength="6"
                maxlength="255"
                required
                :disabled="loading"
              />
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
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="loading">
              <span
                v-if="loading"
                class="spinner-border spinner-border-sm me-1"
                aria-hidden="true"
              ></span>
              {{ loading ? 'Cadastrando...' : 'Cadastrar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal-backdrop fade show"></div>
  </template>
</template>