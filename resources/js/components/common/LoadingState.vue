<template>
  <div class="card">
    <p class="subtitle">{{ label }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    default: 'Loading...',
  },
});
</script>
