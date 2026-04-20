<template>
  <section class="stack">
    <div class="card stack">
      <h1 class="title">Tailor Profile</h1>
      <p class="subtitle">Basic profile summary from the integration endpoints.</p>

      <LoadingState v-if="loading" label="Loading tailor profile..." />
      <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

      <template v-else>
        <div class="grid grid-2">
          <div class="card">
            <p class="label">Category</p>
            <p>{{ profile.category_name || '-' }}</p>
          </div>
          <div class="card">
            <p class="label">Status</p>
            <p>{{ profile.status || '-' }}</p>
          </div>
          <div class="card">
            <p class="label">Average Rating</p>
            <p>{{ profile.average_rating ?? 0 }}</p>
          </div>
          <div class="card">
            <p class="label">Total Reviews</p>
            <p>{{ profile.total_reviews ?? 0 }}</p>
          </div>
          <div class="card">
            <p class="label">Latitude</p>
            <p>{{ profile.latitude ?? '-' }}</p>
          </div>
          <div class="card">
            <p class="label">Longitude</p>
            <p>{{ profile.longitude ?? '-' }}</p>
          </div>
        </div>
      </template>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import { fetchTailorProfile } from '@/services/tailorService';
import { getErrorMessage } from '@/services/errorMessage';

const loading = ref(false);
const error = ref('');

const profile = reactive({
  category_name: '',
  status: '',
  average_rating: 0,
  total_reviews: 0,
  latitude: null,
  longitude: null,
});

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchTailorProfile();
    Object.assign(profile, response.data || {});
  } catch (err) {
    error.value = getErrorMessage(err, 'Failed to load profile data.');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
