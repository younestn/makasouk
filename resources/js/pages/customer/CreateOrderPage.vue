<template>
  <section class="stack">
    <UiSectionHeader
      title="Create Order"
      description="Submit measurements and delivery location using the integration-ready payload."
    />

    <div class="ui-card stack">
      <div v-if="successMessage" class="alert alert-info">{{ successMessage }}</div>
      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <form class="stack" @submit.prevent="submit">
        <UiFormField label="Product" field-id="product">
          <select id="product" v-model="form.productId" class="select" required>
            <option value="">Select product</option>
            <option v-for="product in products" :key="product.id" :value="String(product.id)">
              {{ product.name }} ({{ product.price }})
            </option>
          </select>
        </UiFormField>

        <div class="grid grid-2">
          <UiFormField label="Delivery Latitude" field-id="latitude">
            <input id="latitude" v-model.number="form.latitude" class="input" type="number" step="0.000001" required />
          </UiFormField>

          <UiFormField label="Delivery Longitude" field-id="longitude">
            <input id="longitude" v-model.number="form.longitude" class="input" type="number" step="0.000001" required />
          </UiFormField>
        </div>

        <UiFormField label="Measurements JSON" field-id="measurements" hint='Example: {"height": 170, "waist": 80}'>
          <textarea
            id="measurements"
            v-model="form.measurementsJson"
            class="textarea"
            rows="6"
            required
          ></textarea>
        </UiFormField>

        <button class="btn btn-primary" type="submit" :disabled="loading">
          {{ loading ? 'Submitting...' : 'Create Order' }}
        </button>
      </form>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { fetchProducts } from '@/services/catalogService';
import { createOrder } from '@/services/customerOrderService';
import { getErrorMessage } from '@/services/errorMessage';

const route = useRoute();
const router = useRouter();

const loading = ref(false);
const error = ref('');
const successMessage = ref('');
const products = ref([]);

const form = reactive({
  productId: '',
  latitude: 33.5731,
  longitude: -7.5898,
  measurementsJson: '{"height":170,"waist":80}',
});

async function loadProducts() {
  const response = await fetchProducts({ per_page: 100 });
  products.value = response.data || [];

  if (!form.productId && route.query.productId) {
    form.productId = String(route.query.productId);
  }
}

async function submit() {
  error.value = '';
  successMessage.value = '';
  loading.value = true;

  try {
    const measurements = JSON.parse(form.measurementsJson);

    const response = await createOrder({
      product_id: Number(form.productId),
      measurements,
      customer_location: {
        latitude: Number(form.latitude),
        longitude: Number(form.longitude),
      },
    });

    successMessage.value = response.message || 'Order created.';

    if (response?.data?.id) {
      router.push({ name: 'customerOrderDetails', params: { id: response.data.id } });
    }
  } catch (err) {
    if (err instanceof SyntaxError) {
      error.value = 'Measurements must be valid JSON.';
    } else {
      error.value = getErrorMessage(err, 'Failed to create order.');
    }
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  try {
    await loadProducts();
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load products for order creation.');
  }
});
</script>
