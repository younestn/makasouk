<template>
  <div class="card">
    <div class="alert alert-danger">{{ message }}</div>
    <div v-if="retryable" class="actions" style="margin-top: 0.75rem;">
      <button class="btn" type="button" @click="$emit('retry')">Retry</button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: 'Something went wrong.',
  },
  retryable: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['retry']);
</script>
