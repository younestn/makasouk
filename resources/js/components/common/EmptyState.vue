<template>
  <div class="ui-card state-wrapper">
    <div class="stack" style="align-items: center;">
      <strong>{{ title }}</strong>
      <p class="small">{{ message }}</p>
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Nothing to show yet',
  },
  message: {
    type: String,
    default: 'No data found.',
  },
});
</script>
