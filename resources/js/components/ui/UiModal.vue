<template>
  <Teleport to="body">
    <div v-if="modelValue" class="ui-modal-overlay" @click.self="$emit('update:modelValue', false)">
      <section class="ui-modal" role="dialog" :aria-label="title">
        <div class="ui-modal__header">
          <div>
            <h3 class="title">{{ title }}</h3>
            <p v-if="description" class="small">{{ description }}</p>
          </div>
          <button class="btn" type="button" @click="$emit('update:modelValue', false)">&times;</button>
        </div>
        <div class="ui-modal__body">
          <slot />
        </div>
      </section>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    required: true,
  },
  description: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue']);
</script>
