<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        title="For Customers"
        description="A straightforward ordering experience with transparent progress updates."
      />

      <div class="grid grid-2">
        <UiCard class="stack">
          <h3 class="title">What you can do</h3>
          <ul class="clean-list">
            <li>Browse active catalog categories and products.</li>
            <li>Create orders with measurement JSON and delivery location.</li>
            <li>Track active orders and review full history.</li>
            <li>Cancel in allowed lifecycle windows and submit post-completion reviews.</li>
          </ul>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">Why it matters</h3>
          <ul class="clean-list">
            <li>Predictable states reduce uncertainty during fulfillment.</li>
            <li>Realtime updates remove the need for manual refresh loops.</li>
            <li>Order details capture product, tailor assignment, and lifecycle hints.</li>
          </ul>
        </UiCard>
      </div>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">Sign In as Customer</UiButton>
        <UiButton :to="{ name: 'publicFaq' }" variant="ghost">Read Customer FAQ</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
</script>
