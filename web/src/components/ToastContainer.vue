<script setup lang="ts">
import { useToast } from '../composables/useToast'

const { toasts, remove } = useToast()
</script>

<template>
  <div class="toast-container position-fixed top-0 end-0 p-3">
    <div
      v-for="t in toasts"
      :key="t.id"
      class="toast show align-items-center text-bg-primary border-0"
      :class="`text-bg-${t.type}`"
      role="alert"
    >
      <div class="d-flex">
        <div class="toast-body">{{ t.message }}</div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          aria-label="Fechar"
          @click="remove(t.id)"
        />
      </div>
    </div>
  </div>
</template>