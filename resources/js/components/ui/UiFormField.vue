<template>
  <div class="ui-form-field">
    <label class="label" :for="fieldId">{{ label }}</label>
    <slot />
    <p v-if="hint" class="small">{{ hint }}</p>
  </div>
</template>

<script setup>
defineProps({
  label: {
    type: String,
    required: true,
  },
  fieldId: {
    type: String,
    required: true,
  },
  hint: {
    type: String,
    default: '',
  },
});
</script>
