<template>
  <UiCard class="ui-stat-block stack" :tone="tone">
    <p class="label">{{ label }}</p>
    <p class="ui-stat-value">{{ value }}</p>
    <p v-if="hint" class="small">{{ hint }}</p>
  </UiCard>
</template>

<script setup>
import UiCard from '@/components/ui/UiCard.vue';

defineProps({
  label: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  hint: {
    type: String,
    default: '',
  },
  tone: {
    type: String,
    default: '',
  },
});
</script>
