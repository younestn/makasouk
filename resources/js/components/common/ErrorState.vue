<template>
  <div class="ui-card stack">
    <div class="alert alert-danger">
      <strong style="display: block; margin-bottom: 0.25rem;">{{ resolvedTitle }}</strong>
      {{ resolvedMessage }}
    </div>

    <div v-if="retryable" class="actions">
      <button class="btn" type="button" @click="$emit('retry')">{{ t('common.retry') }}</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
  message: {
    type: String,
    default: '',
  },
  retryable: {
    type: Boolean,
    default: false,
  },
});

defineEmits(['retry']);

const { t } = useI18n();

const resolvedTitle = computed(() => props.title || t('common.error_default_title'));
const resolvedMessage = computed(() => props.message || t('common.error_default_message'));
</script>
