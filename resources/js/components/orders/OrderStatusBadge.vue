<template>
  <span class="badge" :class="meta.className">{{ meta.label }}</span>
</template>

<script setup>
import { computed } from 'vue';
import { statusMeta } from '@/utils/orderStatus';

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
});

const meta = computed(() => statusMeta(props.status));
</script>
