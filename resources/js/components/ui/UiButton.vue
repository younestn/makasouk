<template>
  <component
    :is="componentTag"
    v-bind="componentProps"
    class="ui-btn"
    :class="[
      `ui-btn--${variant}`,
      `ui-btn--${size}`,
      { 'ui-btn--block': block, 'ui-btn--disabled': disabled },
    ]"
    :disabled="componentTag === 'button' ? disabled : undefined"
    :type="componentTag === 'button' ? type : undefined"
  >
    <slot />
  </component>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink } from 'vue-router';

const props = defineProps({
  as: {
    type: String,
    default: 'button',
  },
  to: {
    type: [String, Object],
    default: null,
  },
  href: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
  },
  size: {
    type: String,
    default: 'md',
  },
  block: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const componentTag = computed(() => {
  if (props.to) {
    return RouterLink;
  }

  if (props.as === 'a' || props.href) {
    return 'a';
  }

  return 'button';
});

const componentProps = computed(() => {
  if (props.to) {
    return {
      to: props.to,
      'aria-disabled': props.disabled ? 'true' : undefined,
      tabindex: props.disabled ? '-1' : undefined,
    };
  }

  if (componentTag.value === 'a') {
    return {
      href: props.href || '#',
      'aria-disabled': props.disabled ? 'true' : undefined,
      tabindex: props.disabled ? '-1' : undefined,
    };
  }

  return {};
});
</script>
