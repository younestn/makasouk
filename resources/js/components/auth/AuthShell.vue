<template>
  <section class="container page mk-auth-shell mk-auth-tone" :data-auth-tone="tone" style="max-width: 1040px;">
    <div class="mk-auth-grid">
      <aside class="mk-auth-brand-panel">
        <div class="mk-auth-brand-content">
          <span class="mk-auth-pill">
            {{ badge }}
          </span>

          <div class="mk-auth-brandmark" aria-hidden="true">MK</div>

          <h1 class="mk-auth-brand-title">{{ brandTitle }}</h1>
          <p class="mk-auth-brand-subtitle">{{ brandSubtitle }}</p>

          <ul class="mk-auth-brand-list">
            <li v-for="(point, idx) in points" :key="`${idx}-${point}`">{{ point }}</li>
          </ul>

          <p class="mk-auth-trust-note">{{ trustNote }}</p>
        </div>
      </aside>

      <div class="mk-auth-form-panel">
        <article class="mk-auth-form-card">
          <header class="mk-auth-form-header">
            <h2 class="mk-auth-form-title">{{ formTitle }}</h2>
            <p v-if="formSubtitle" class="mk-auth-form-subtitle">{{ formSubtitle }}</p>
          </header>

          <slot />
        </article>
      </div>
    </div>
  </section>
</template>

<script setup>
defineProps({
  tone: {
    type: String,
    default: 'customer',
  },
  badge: {
    type: String,
    required: true,
  },
  brandTitle: {
    type: String,
    required: true,
  },
  brandSubtitle: {
    type: String,
    required: true,
  },
  points: {
    type: Array,
    default: () => [],
  },
  trustNote: {
    type: String,
    required: true,
  },
  formTitle: {
    type: String,
    required: true,
  },
  formSubtitle: {
    type: String,
    default: '',
  },
});
</script>
