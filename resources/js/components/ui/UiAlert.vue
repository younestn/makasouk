<template>
  <div class="ui-alert" :class="`ui-alert--${variant}`" role="status">
    <p v-if="title" class="ui-alert-title">{{ title }}</p>
    <p class="ui-alert-message"><slot /></p>
  </div>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'info',
  },
  title: {
    type: String,
    default: '',
  },
});
</script>
