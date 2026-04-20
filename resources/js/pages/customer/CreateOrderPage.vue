<template>
  <section class="stack">
    <div class="card stack">
      <h1 class="title">Create Order</h1>
      <p class="subtitle">Submit a customer order using the finalized contract payload.</p>

      <div v-if="successMessage" class="alert alert-info">{{ successMessage }}</div>
      <div v-if="error" class="alert alert-danger">{{ error }}</div>

      <form class="stack" @submit.prevent="submit">
        <div>
          <label class="label" for="product">Product</label>
          <select id="product" v-model="form.productId" class="select" required>
            <option value="">Select product</option>
            <option v-for="product in products" :key="product.id" :value="String(product.id)">
              {{ product.name }} ({{ product.price }})
            </option>
          </select>
        </div>

        <div class="grid grid-2">
          <div>
            <label class="label" for="latitude">Delivery Latitude</label>
            <input id="latitude" v-model.number="form.latitude" class="input" type="number" step="0.000001" required />
          </div>
          <div>
            <label class="label" for="longitude">Delivery Longitude</label>
            <input id="longitude" v-model.number="form.longitude" class="input" type="number" step="0.000001" required />
          </div>
        </div>

        <div>
          <label class="label" for="measurements">Measurements JSON</label>
          <textarea
            id="measurements"
            v-model="form.measurementsJson"
            class="textarea"
            rows="6"
            required
          ></textarea>
          <p class="small">Example: {"height": 170, "waist": 80}</p>
        </div>

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
