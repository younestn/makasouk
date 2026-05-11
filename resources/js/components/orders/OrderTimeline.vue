<template>
  <ol class="order-timeline" :class="{ 'order-timeline--compact': compact }">
    <li
      v-for="item in visibleItems"
      :key="item.code"
      class="order-timeline__item"
      :class="`is-${item.state || 'pending'}`"
    >
      <span class="order-timeline__dot" aria-hidden="true"></span>
      <div class="order-timeline__content">
        <div class="order-timeline__head">
          <strong>{{ t(`${namespace}.timeline_labels.${item.code}`) }}</strong>
          <span v-if="item.occurred_at" class="small">{{ formatDate(item.occurred_at) }}</span>
        </div>
        <p v-if="item.description" class="small">{{ item.description }}</p>
        <p v-else-if="!compact" class="small">{{ t(`${namespace}.timeline_descriptions.${item.code}`) }}</p>
        <span v-if="item.responsible_role" class="badge badge-neutral">{{ t(`roles.${item.responsible_role}`) }}</span>
      </div>
    </li>
  </ol>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  compact: {
    type: Boolean,
    default: false,
  },
  limit: {
    type: Number,
    default: 0,
  },
  namespace: {
    type: String,
    default: 'orders',
  },
});

const { t } = useI18n();

const visibleItems = computed(() => {
  if (!props.limit || props.items.length <= props.limit) {
    return props.items;
  }

  return props.items.slice(0, props.limit);
});

function formatDate(value) {
  return new Date(value).toLocaleString();
}
</script>
