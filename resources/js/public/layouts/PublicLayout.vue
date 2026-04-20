<template>
  <div class="public-shell">
    <PublicHeader />

    <main>
      <RouterView />
    </main>

    <PublicFooter />
  </div>
</template>

<script setup>
import { RouterView } from 'vue-router';
import PublicHeader from '@/public/components/PublicHeader.vue';
import PublicFooter from '@/public/components/PublicFooter.vue';
</script>
