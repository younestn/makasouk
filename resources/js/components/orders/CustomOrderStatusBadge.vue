<template>
  <span class="badge" :class="meta.className">{{ meta.label }}</span>
</template>

<script setup>
import { computed } from 'vue';
import { customOrderStatusMeta } from '@/utils/customOrderStatus';

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
});

const meta = computed(() => customOrderStatusMeta(props.status));
</script>
