<template>
  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        title="For Tailors"
        description="Receive nearby jobs, manage availability, and move orders through valid statuses."
      />

      <div class="grid grid-2">
        <UiCard class="stack">
          <h3 class="title">Operational flow</h3>
          <ul class="clean-list">
            <li>Set your availability and location through the tailor shell.</li>
            <li>Receive realtime nearby order offers on private channels.</li>
            <li>Accept orders and update statuses according to lifecycle rules.</li>
            <li>Cancel only within allowed windows when continuation is not possible.</li>
          </ul>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">Built for reliability</h3>
          <ul class="clean-list">
            <li>Clear status contracts reduce accidental transitions.</li>
            <li>Profile and rating summary are available in one place.</li>
            <li>Active order focus helps prioritize in-progress work.</li>
          </ul>
        </UiCard>
      </div>

      <UiAlert variant="warning" title="Approval requirement">
        Tailor access to work opportunities depends on account approval and active account state.
      </UiAlert>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">Sign In as Tailor</UiButton>
        <UiButton :to="{ name: 'publicHowItWorks' }" variant="secondary">Review Lifecycle</UiButton>
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
