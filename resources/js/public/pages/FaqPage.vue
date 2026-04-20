<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        title="Frequently Asked Questions"
        description="Practical answers for client-app usage and order tracking."
      />

      <div class="stack">
        <UiCard class="stack">
          <h3 class="title">How do I start as a customer?</h3>
          <p class="subtitle">Open the web app, sign in, browse catalog items, then create your first order.</p>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">How are tailors selected?</h3>
          <p class="subtitle">Orders are broadcast to nearby approved tailors, and the first valid acceptance assigns the order.</p>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">Can I track status without refreshing?</h3>
          <p class="subtitle">Yes. Customer and tailor pages consume realtime events over authenticated private channels.</p>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">Where is admin management handled?</h3>
          <p class="subtitle">All admin workflows run separately in Filament under <code>/admin-panel</code>.</p>
        </UiCard>
      </div>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">Open App</UiButton>
        <UiButton :to="{ name: 'publicContact' }" variant="ghost">Contact Support</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
</script>
