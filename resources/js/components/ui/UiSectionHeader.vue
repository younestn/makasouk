<template>
  <div class="ui-section-header" :class="`ui-section-header--${align}`">
    <p v-if="eyebrow" class="ui-section-eyebrow">{{ eyebrow }}</p>
    <h2 class="ui-section-title">{{ title }}</h2>
    <p v-if="description" class="ui-section-description">{{ description }}</p>
  </div>
</template>

<script setup>
defineProps({
  eyebrow: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
  align: {
    type: String,
    default: 'left',
  },
});
</script>
