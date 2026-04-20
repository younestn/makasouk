<template>
  <div class="ui-card state-wrapper">
    <div class="stack" style="align-items: center;">
      <div class="loading-row">
        <span class="loading-spinner" aria-hidden="true"></span>
        <strong>{{ resolvedLabel }}</strong>
      </div>
      <p v-if="hint" class="small">{{ hint }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
  label: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
});

const { t } = useI18n();

const resolvedLabel = computed(() => {
  return props.label || `${t('common.loading')}...`;
});
</script>
