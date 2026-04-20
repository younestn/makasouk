<template>
  <div class="ui-card state-wrapper">
    <div class="stack" style="align-items: center;">
      <div class="loading-row">
        <span class="loading-spinner" aria-hidden="true"></span>
        <strong>{{ label }}</strong>
      </div>
      <p v-if="hint" class="small">{{ hint }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    default: 'Loading...',
  },
  hint: {
    type: String,
    default: '',
  },
});
</script>
