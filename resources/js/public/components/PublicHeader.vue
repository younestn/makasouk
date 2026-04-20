<template>
  <header class="public-header">
    <div class="container public-header-inner">
      <RouterLink class="brand" :to="{ name: 'publicHome' }">
        <span class="brand-mark">M</span>
        <span>Makasouk</span>
      </RouterLink>

      <nav class="public-nav" aria-label="Public navigation">
        <RouterLink class="public-nav-link" :to="{ name: 'publicHowItWorks' }">How It Works</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicForCustomers' }">For Customers</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicForTailors' }">For Tailors</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicFaq' }">FAQ</RouterLink>
        <RouterLink class="public-nav-link" :to="{ name: 'publicContact' }">Contact</RouterLink>
      </nav>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="ghost">Open App</UiButton>
        <UiButton as="a" href="/app/login" variant="primary">Sign In</UiButton>
      </div>
    </div>
  </header>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import UiButton from '@/components/ui/UiButton.vue';
</script>
