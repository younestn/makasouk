<template>
  <nav v-if="pagination.lastPage > 1" class="ui-pagination" aria-label="Pagination controls">
    <div class="small">
      {{ t('pagination.summary', summaryParams) }} · {{ t('pagination.page', pageParams) }}
    </div>

    <div class="actions">
      <button class="btn" type="button" :disabled="isFirstPage" @click="emitPage(pagination.currentPage - 1)">
        {{ t('common.previous') }}
      </button>

      <button
        v-for="page in visiblePages"
        :key="page"
        class="btn"
        :class="page === pagination.currentPage ? 'btn-secondary' : ''"
        type="button"
        @click="emitPage(page)"
      >
        {{ page }}
      </button>

      <button class="btn" type="button" :disabled="isLastPage" @click="emitPage(pagination.currentPage + 1)">
        {{ t('common.next') }}
      </button>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
  pagination: {
    type: Object,
    required: true,
  },
  maxButtons: {
    type: Number,
    default: 5,
  },
});

const emit = defineEmits(['page-change']);

const { t } = useI18n();

const isFirstPage = computed(() => props.pagination.currentPage <= 1);
const isLastPage = computed(() => props.pagination.currentPage >= props.pagination.lastPage);

const summaryParams = computed(() => ({
  from: props.pagination.from ?? 0,
  to: props.pagination.to ?? 0,
  total: props.pagination.total ?? 0,
}));

const pageParams = computed(() => ({
  current: props.pagination.currentPage,
  last: props.pagination.lastPage,
}));

const visiblePages = computed(() => {
  const total = Number(props.pagination.lastPage || 1);
  const current = Number(props.pagination.currentPage || 1);
  const maxButtons = Math.max(3, Number(props.maxButtons || 5));

  if (total <= maxButtons) {
    return Array.from({ length: total }, (_, index) => index + 1);
  }

  const half = Math.floor(maxButtons / 2);
  let start = Math.max(1, current - half);
  let end = start + maxButtons - 1;

  if (end > total) {
    end = total;
    start = Math.max(1, end - maxButtons + 1);
  }

  return Array.from({ length: end - start + 1 }, (_, index) => start + index);
});

function emitPage(page) {
  const nextPage = Number(page);

  if (!Number.isFinite(nextPage)) {
    return;
  }

  if (nextPage < 1 || nextPage > Number(props.pagination.lastPage || 1)) {
    return;
  }

  if (nextPage === Number(props.pagination.currentPage || 1)) {
    return;
  }

  emit('page-change', nextPage);
}
</script>
