<template>
  <div>
    <UiToast />
    <RouterView />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import UiToast from '@/components/ui/UiToast.vue';
</script>
