<template>
  <section class="hero">
    <div class="container hero-grid">
      <div class="stack">
        <span class="badge badge-info">Makasouk Marketplace</span>
        <h1 class="hero-title">Book trusted tailoring and track each order in real time.</h1>
        <p class="hero-subtitle">
          Makasouk connects customers with nearby tailors through a practical order flow, clear status updates,
          and realtime progress across every step.
        </p>

        <div class="actions">
          <UiButton as="a" href="/app/login" variant="primary">Start in App</UiButton>
          <UiButton :to="{ name: 'publicHowItWorks' }" variant="secondary">See How It Works</UiButton>
        </div>
      </div>

      <UiCard class="hero-card stack">
        <UiSectionHeader
          title="Order Journey"
          description="From measurement submission to completion in a predictable lifecycle."
        />

        <div class="journey-list">
          <div class="journey-item">
            <span class="journey-step">1</span>
            <p>Select a category and product, then submit your order measurements.</p>
          </div>
          <div class="journey-item">
            <span class="journey-step">2</span>
            <p>Nearby approved tailors receive the order and can accept it quickly.</p>
          </div>
          <div class="journey-item">
            <span class="journey-step">3</span>
            <p>Track status updates in realtime until ready for delivery and completion.</p>
          </div>
        </div>
      </UiCard>
    </div>
  </section>

  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader
        title="Why customers and tailors trust Makasouk"
        description="Built for reliability, transparency, and fast local coordination."
        align="center"
      />

      <div class="grid grid-3">
        <UiCard class="stack">
          <h3 class="title">Predictable lifecycle</h3>
          <p class="subtitle">Every order follows explicit status transitions with clear actor permissions.</p>
        </UiCard>
        <UiCard class="stack">
          <h3 class="title">Realtime visibility</h3>
          <p class="subtitle">Customers and tailors receive near-instant updates from private channels.</p>
        </UiCard>
        <UiCard class="stack">
          <h3 class="title">Secure operations</h3>
          <p class="subtitle">Role-based access and audited admin workflows protect day-to-day operations.</p>
        </UiCard>
      </div>
    </div>
  </section>

  <section class="page-section page-section-alt">
    <div class="container stack">
      <UiSectionHeader title="Two-sided value" description="Clear outcomes for both customers and tailors." align="center" />

      <div class="grid grid-2">
        <UiCard class="stack">
          <h3 class="title">For Customers</h3>
          <ul class="clean-list">
            <li>Browse categories and products with transparent pricing.</li>
            <li>Create orders with measurement payloads and location data.</li>
            <li>Track active and historical orders from one dashboard.</li>
          </ul>
          <UiButton :to="{ name: 'publicForCustomers' }" variant="ghost">Explore Customer Flow</UiButton>
        </UiCard>

        <UiCard class="stack">
          <h3 class="title">For Tailors</h3>
          <ul class="clean-list">
            <li>Receive nearby order offers and accept only when available.</li>
            <li>Move orders through allowed statuses safely.</li>
            <li>Manage availability and profile visibility in-app.</li>
          </ul>
          <UiButton :to="{ name: 'publicForTailors' }" variant="ghost">Explore Tailor Flow</UiButton>
        </UiCard>
      </div>
    </div>
  </section>

  <section class="page-section">
    <div class="container stack">
      <UiSectionHeader title="Platform snapshot" description="Current operational foundation." align="center" />

      <div class="grid grid-4">
        <UiStatBlock label="Client Areas" value="2" hint="Customer + Tailor" />
        <UiStatBlock label="Realtime" value="Live" hint="Reverb private channels" />
        <UiStatBlock label="Admin Ops" value="Filament" hint="Validated and secured" />
        <UiStatBlock label="Stack" value="Laravel + Vue" hint="Integration-ready" />
      </div>
    </div>
  </section>

  <section class="page-section cta-band">
    <div class="container row" style="justify-content: space-between;">
      <div>
        <h2 class="title" style="font-size: 1.6rem;">Ready to try Makasouk?</h2>
        <p class="subtitle">Sign in to the web client and validate customer or tailor workflows now.</p>
      </div>
      <div class="actions">
        <UiButton as="a" href="/app/login" variant="primary">Open Web App</UiButton>
        <UiButton :to="{ name: 'publicFaq' }" variant="secondary">Read FAQ</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
</script>
