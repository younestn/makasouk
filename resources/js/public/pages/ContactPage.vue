<template>
  <section class="page-section">
    <div class="container stack" style="max-width: 860px;">
      <UiSectionHeader
        title="Contact and Support"
        description="This placeholder support page is ready for future ticketing/chat integrations."
      />

      <UiCard class="stack">
        <p class="subtitle">
          For local testing, please use project maintainers and seeded accounts. For production handoff, connect this
          page to your final support workflow.
        </p>

        <div class="grid grid-2">
          <div>
            <p class="label">Support Email</p>
            <p>support@makasouk.local</p>
          </div>
          <div>
            <p class="label">Business Hours</p>
            <p>Monday to Saturday, 09:00-18:00</p>
          </div>
        </div>
      </UiCard>

      <UiCard class="stack">
        <UiFormField label="Full Name" field-id="supportName">
          <input id="supportName" type="text" class="input" placeholder="Your name" disabled />
        </UiFormField>

        <UiFormField label="Message" field-id="supportMessage">
          <textarea id="supportMessage" class="textarea" rows="5" placeholder="Support form placeholder" disabled></textarea>
        </UiFormField>

        <UiAlert variant="neutral" title="Next step">
          Phase 7 can wire this placeholder to real contact APIs or external helpdesk providers.
        </UiAlert>
      </UiCard>

      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">Back to App Login</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiAlert from '@/components/ui/UiAlert.vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
</script>
