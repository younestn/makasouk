<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        title="How Makasouk Works"
        description="A clear order lifecycle for customers, tailors, and operations teams."
      />

      <div class="grid grid-3">
        <UiCard class="stack">
          <span class="badge badge-info">Step 1</span>
          <h3 class="title">Create an order</h3>
          <p class="subtitle">Customers choose a product, submit measurements, and provide delivery coordinates.</p>
        </UiCard>

        <UiCard class="stack">
          <span class="badge badge-warning">Step 2</span>
          <h3 class="title">Tailor acceptance</h3>
          <p class="subtitle">Nearby approved tailors receive realtime notifications and can accept available work.</p>
        </UiCard>

        <UiCard class="stack">
          <span class="badge badge-success">Step 3</span>
          <h3 class="title">Track to completion</h3>
          <p class="subtitle">Order progress moves through controlled statuses until ready and completed.</p>
        </UiCard>
      </div>

      <UiAlert variant="info" title="Realtime contract">
        Status changes are emitted through private channels and consumed by the web client in near real time.
      </UiAlert>

      <div class="actions">
        <UiButton :to="{ name: 'publicForCustomers' }" variant="primary">Customer View</UiButton>
        <UiButton :to="{ name: 'publicForTailors' }" variant="secondary">Tailor View</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiAlert from '@/components/ui/UiAlert.vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
</script>
