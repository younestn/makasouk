<template>
  <section class="stack">
    <UiSectionHeader title="Tailor Profile" description="Basic profile summary from integration endpoints." />

    <LoadingState v-if="loading" label="Loading tailor profile..." />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <div v-else class="grid grid-3">
      <UiStatBlock label="Category" :value="profile.category_name || '-'" />
      <UiStatBlock label="Status" :value="profile.status || '-'" tone="info" />
      <UiStatBlock label="Average Rating" :value="profile.average_rating ?? 0" />
      <UiStatBlock label="Total Reviews" :value="profile.total_reviews ?? 0" />
      <UiStatBlock label="Latitude" :value="profile.latitude ?? '-'" />
      <UiStatBlock label="Longitude" :value="profile.longitude ?? '-'" />
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
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
