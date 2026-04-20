<template>
  <section class="page">
    <div class="card stack">
      <h1 class="title">Forbidden</h1>
      <p class="subtitle">You are signed in, but this route is not available for your role.</p>
      <RouterLink class="btn" :to="{ name: 'root' }">Back</RouterLink>
    </div>
  </section>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>
