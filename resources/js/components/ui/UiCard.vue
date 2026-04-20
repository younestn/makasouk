<template>
  <article class="ui-card" :class="tone ? `ui-card--${tone}` : ''">
    <slot />
  </article>
</template>

<script setup>
defineProps({
  tone: {
    type: String,
    default: '',
  },
});
</script>
