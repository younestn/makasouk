<template>
  <section class="stack">
    <div class="actions">
      <RouterLink class="btn" :to="{ name: 'customerCatalog' }">{{ t('products.back_to_catalog') }}</RouterLink>
    </div>

    <LoadingState v-if="loading" :label="t('products.loading_details')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else-if="product" class="ui-card stack">
      <UiSectionHeader :title="product.name" :description="product.description || t('products.no_description_available')" />

      <div v-if="product.main_image_url" class="ui-card" style="padding: 0.6rem;">
        <img
          :src="product.main_image_url"
          :alt="product.name"
          style="width: 100%; border-radius: 0.8rem; max-height: 360px; object-fit: cover;"
        />
      </div>

      <div class="grid grid-2">
        <UiStatBlock :label="t('products.category_label')" :value="product.category?.name || '-'" />
        <UiStatBlock :label="t('products.price_label')" :value="`${product.price} (${product.pricing_type})`" tone="info" />
      </div>

      <div v-if="hasFabricInfo" class="ui-card stack" style="border: 1px solid rgba(148, 163, 184, 0.25);">
        <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('products.fabric_section_title') }}</h3>

        <div class="grid grid-2">
          <UiStatBlock :label="t('products.fabric_type_label')" :value="product.fabric_type || '-'" />
          <UiStatBlock :label="t('products.fabric_country_label')" :value="product.fabric_country || '-'" />
        </div>

        <p v-if="product.fabric_description" class="small" style="margin: 0;">
          {{ product.fabric_description }}
        </p>

        <div v-if="product.fabric_image_url" class="stack" style="gap: 0.45rem;">
          <a
            class="btn"
            :href="product.fabric_image_url"
            target="_blank"
            rel="noopener noreferrer"
            style="width: fit-content;"
          >
            {{ t('products.view_fabric_image') }}
          </a>
          <img
            :src="product.fabric_image_url"
            :alt="t('products.fabric_image_alt', { name: product.name })"
            style="max-width: 260px; border-radius: 0.7rem; border: 1px solid rgba(148, 163, 184, 0.25);"
          />
        </div>
      </div>

      <div class="actions">
        <RouterLink class="btn btn-primary" :to="{ name: 'customerCreateOrder', query: { productId: product.id } }">
          {{ t('orders.create_order_action') }}
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { fetchProduct } from '@/services/catalogService';
import { getErrorMessage } from '@/services/errorMessage';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const { t } = useI18n();
const loading = ref(false);
const error = ref('');
const product = ref(null);

const hasFabricInfo = computed(() => {
  if (!product.value) {
    return false;
  }

  return Boolean(product.value.fabric_type || product.value.fabric_country || product.value.fabric_description || product.value.fabric_image_url);
});

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchProduct(route.params.id);
    product.value = response.data;
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.product_details_load_failed'));
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
