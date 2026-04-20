<template>
  <div class="ui-card stack">
    <div class="alert alert-danger">
      <strong style="display: block; margin-bottom: 0.25rem;">{{ title }}</strong>
      {{ message }}
    </div>

    <div v-if="retryable" class="actions">
      <button class="btn" type="button" @click="$emit('retry')">Retry</button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Unable to load data',
  },
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
