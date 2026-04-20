<template>
  <div class="card">
    <p class="subtitle">{{ message }}</p>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: 'No data found.',
  },
});
</script>
