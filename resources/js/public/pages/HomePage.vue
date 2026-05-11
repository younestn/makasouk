<template>
  <section v-if="isSectionVisible('hero')" class="premium-home-hero">
    <div class="container premium-home-hero-grid">
      <div class="stack premium-home-hero-copy">
        <span class="premium-eyebrow">{{ hero.badge || t('public.home_badge') }}</span>
        <h1 class="hero-title">{{ hero.title || t('public.home_title') }}</h1>
        <p class="hero-subtitle">{{ hero.subtitle || t('public.home_subtitle') }}</p>

        <div class="actions">
          <UiButton as="a" :href="hero.primary_cta_url || '/shop'" variant="primary" class="gold-button">
            <span aria-hidden="true">✦</span>
            {{ hero.primary_cta_label || t('public.home_cta_shop') }}
          </UiButton>
          <UiButton as="a" :href="hero.secondary_cta_url || '/how-it-works'" variant="secondary">
            {{ hero.secondary_cta_label || t('public.home_cta_how_it_works') }}
          </UiButton>
        </div>

        <div class="premium-mini-proof">
          <span>{{ t('public.home_proof_1') }}</span>
          <span>{{ t('public.home_proof_2') }}</span>
          <span>{{ t('public.home_proof_3') }}</span>
        </div>
      </div>

      <div class="premium-atelier-card">
        <img v-if="hero.image_url" :src="hero.image_url" :alt="hero.title || t('public.home_title')" />
        <div v-else class="premium-atelier-visual" aria-hidden="true">
          <span class="thread-line thread-line-one"></span>
          <span class="thread-line thread-line-two"></span>
          <span class="atelier-monogram">MK</span>
          <p>{{ t('public.home_visual_label') }}</p>
        </div>
      </div>
    </div>
  </section>

  <section v-if="isSectionVisible('best_sellers')" class="page-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.home_best_sellers_title')"
        :description="t('public.home_best_sellers_description')"
        align="center"
      />

      <div v-if="bestSellerCards.length" class="storefront-product-grid">
        <ProductCard
          v-for="product in bestSellerCards"
          :key="product.id"
          :product="product"
          :detail-href="product.url"
          :detail-label="t('public.home_view_product')"
          :show-order-action="false"
        />
      </div>

      <UiCard v-else class="stack" style="text-align: center;">
        <h3 class="title">{{ t('public.home_no_best_sellers_title') }}</h3>
        <p class="subtitle">{{ t('public.home_no_best_sellers_description') }}</p>
      </UiCard>
    </div>
  </section>

  <section v-if="isSectionVisible('stats')" class="page-section premium-stats-band">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.home_stats_title')"
        :description="t('public.home_stats_description')"
        align="center"
      />

      <div class="grid grid-4">
        <UiStatBlock :label="t('public.home_sales_count')" :value="formatNumber(stats.sales_count)" :hint="t('public.home_sales_hint')" />
        <UiStatBlock :label="t('public.home_orders_count')" :value="formatNumber(stats.orders_count)" :hint="t('public.home_orders_hint')" />
        <UiStatBlock :label="t('public.home_tailors_count')" :value="formatNumber(stats.tailors_count)" :hint="t('public.home_tailors_hint')" />
        <UiStatBlock :label="t('public.home_customers_count')" :value="formatNumber(stats.customers_count)" :hint="t('public.home_customers_hint')" />
      </div>
    </div>
  </section>

  <section v-if="isSectionVisible('testimonials')" class="page-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.home_testimonials_title')"
        :description="t('public.home_testimonials_description')"
        align="center"
      />

      <div v-if="testimonials.length" class="grid grid-3">
        <UiCard v-for="testimonial in testimonials" :key="testimonial.id" class="premium-testimonial stack">
          <div class="row" style="justify-content: space-between;">
            <strong>{{ testimonial.name }}</strong>
            <span class="premium-rating">{{ ratingStars(testimonial.rating) }}</span>
          </div>
          <p class="subtitle">“{{ testimonial.comment }}”</p>
        </UiCard>
      </div>

      <UiCard v-else class="premium-testimonial stack" style="text-align: center;">
        <strong>{{ t('public.home_testimonial_fallback_name') }}</strong>
        <p class="subtitle">“{{ t('public.home_testimonial_fallback_comment') }}”</p>
      </UiCard>
    </div>
  </section>

  <section v-if="isSectionVisible('trust')" class="page-section page-section-alt premium-trust-section">
    <div class="container stack">
      <UiSectionHeader
        :title="t('public.home_trust_title')"
        :description="t('public.home_trust_description')"
        align="center"
      />

      <div class="grid grid-3">
        <UiCard class="premium-feature-card stack">
          <span class="feature-icon">✂</span>
          <h3 class="title">{{ t('public.home_trust_card_1_title') }}</h3>
          <p class="subtitle">{{ t('public.home_trust_card_1_description') }}</p>
        </UiCard>
        <UiCard class="premium-feature-card stack">
          <span class="feature-icon">◆</span>
          <h3 class="title">{{ t('public.home_trust_card_2_title') }}</h3>
          <p class="subtitle">{{ t('public.home_trust_card_2_description') }}</p>
        </UiCard>
        <UiCard class="premium-feature-card stack">
          <span class="feature-icon">✓</span>
          <h3 class="title">{{ t('public.home_trust_card_3_title') }}</h3>
          <p class="subtitle">{{ t('public.home_trust_card_3_description') }}</p>
        </UiCard>
      </div>
    </div>
  </section>

  <section class="page-section cta-band premium-gold-cta">
    <div class="container row" style="justify-content: space-between;">
      <div>
        <h2 class="title" style="font-size: 1.6rem;">{{ t('public.home_bottom_title') }}</h2>
        <p class="subtitle">{{ t('public.home_bottom_description') }}</p>
      </div>
      <div class="actions">
        <UiButton as="a" href="/shop" variant="primary" class="gold-button">{{ t('public.home_cta_shop') }}</UiButton>
        <UiButton as="a" href="/app/login" variant="secondary">{{ t('public.home_bottom_cta_app') }}</UiButton>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import UiButton from '@/components/ui/UiButton.vue';
import UiCard from '@/components/ui/UiCard.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import ProductCard from '@/components/catalog/ProductCard.vue';
import { useI18n } from '@/composables/useI18n';
import { apiClient } from '@/services/http';

const { locale, t } = useI18n();

const homepage = ref(null);

const hero = computed(() => homepage.value?.hero || {});
const stats = computed(() => homepage.value?.stats || {});
const bestSellers = computed(() => homepage.value?.best_sellers || []);
const bestSellerCards = computed(() => bestSellers.value.map((product) => ({
  ...product,
  main_image_url: product.image_url || '',
  category: product.category ? { name: product.category } : { name: t('public.home_product_category_fallback') },
  details: product.fabric_type || '',
  description: product.fabric_type || '',
  sale_price: null,
  reviews_count: 0,
  rating_average: null,
  is_featured: false,
  is_best_seller: true,
})));
const testimonials = computed(() => homepage.value?.testimonials || []);
const settings = computed(() => homepage.value?.settings || {});

onMounted(loadHomepage);

watch(locale, () => {
  loadHomepage();
});

async function loadHomepage() {
  try {
    const { data } = await apiClient.get('/public/homepage');
    homepage.value = data.data;
  } catch {
    homepage.value = {
      settings: {},
      hero: {},
      stats: {},
      best_sellers: [],
      testimonials: [],
    };
  }
}

function isSectionVisible(section) {
  const enabledMap = {
    hero: settings.value.hero_enabled !== false,
    stats: settings.value.stats_enabled !== false,
    best_sellers: settings.value.best_sellers_enabled !== false,
    testimonials: settings.value.testimonials_enabled !== false,
    trust: true,
  };

  return enabledMap[section] !== false;
}

function formatNumber(value) {
  return new Intl.NumberFormat(locale.value).format(Number(value || 0));
}

function ratingStars(rating) {
  return '★'.repeat(Math.max(1, Math.min(Number(rating || 5), 5)));
}
</script>
