<template>
  <div class="ui-card state-wrapper">
    <div class="stack" style="align-items: center;">
      <strong>{{ resolvedTitle }}</strong>
      <p class="small">{{ resolvedMessage }}</p>
      <slot name="actions" />
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
});

const { t } = useI18n();

const resolvedTitle = computed(() => props.title || t('common.empty_default_title'));
const resolvedMessage = computed(() => props.message || t('common.empty_default_message'));
</script>
