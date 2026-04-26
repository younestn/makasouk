<template>
  <section class="stack">
    <UiSectionHeader :title="t('tailors.profile_title')" :description="t('tailors.profile_description')" />

    <LoadingState v-if="loading" :label="t('tailors.loading_profile')" />
    <ErrorState v-else-if="error" :message="error" retryable @retry="load" />

    <template v-else>
      <div class="grid grid-3">
        <UiStatBlock :label="t('tailors.profile_category')" :value="profile.category_name || '-'" />
        <UiStatBlock :label="t('tailors.profile_status')" :value="tailorStatusLabel(profile.status)" tone="info" />
        <UiStatBlock :label="t('tailors.profile_specialization')" :value="profile.specialization || '-'" />
        <UiStatBlock :label="t('tailors.profile_work_wilaya')" :value="profile.work_wilaya || '-'" />
        <UiStatBlock :label="t('tailors.profile_experience_years')" :value="profile.years_of_experience ?? '-'" />
        <UiStatBlock :label="t('tailors.profile_gender')" :value="genderLabel" />
        <UiStatBlock :label="t('tailors.profile_workers')" :value="profile.workers_count ?? '-'" />
        <UiStatBlock :label="t('tailors.profile_average_rating')" :value="profile.average_rating ?? 0" />
        <UiStatBlock :label="t('tailors.profile_total_reviews')" :value="profile.total_reviews ?? 0" />
        <UiStatBlock :label="t('tailors.score_label')" :value="`${profile.score ?? 100}/100`" tone="success" />
        <UiStatBlock :label="t('tailors.profile_latitude')" :value="profile.latitude ?? '-'" />
        <UiStatBlock :label="t('tailors.profile_longitude')" :value="profile.longitude ?? '-'" />
      </div>

      <div class="ui-card stack">
        <div class="row" style="justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
          <h2 class="title" style="font-size: 1rem; margin: 0;">{{ t('tailors.saved_location_title') }}</h2>
          <button class="btn" type="button" :disabled="locationLoading" @click="useCurrentLocation">
            {{ locationLoading ? t('orders.detecting_location') : t('orders.use_current_location') }}
          </button>
        </div>

        <div v-if="locationError" class="alert alert-warning">{{ locationError }}</div>

        <form class="stack" @submit.prevent="saveLocation">
          <LocationPickerMap
            v-model:latitude="locationForm.latitude"
            v-model:longitude="locationForm.longitude"
            :wilaya-options="wilayaOptions"
            @wilaya-suggested="applySuggestedWilaya"
            @error="handleMapError"
          />

          <details class="ui-card" style="padding: 0.85rem;">
            <summary class="label" style="cursor: pointer;">{{ t('maps.manual_fallback') }}</summary>
            <div class="grid grid-2" style="margin-top: 0.75rem;">
              <UiFormField :label="t('tailors.profile_latitude')" field-id="tailor-latitude">
                <input
                  id="tailor-latitude"
                  v-model.number="locationForm.latitude"
                  class="input"
                  type="number"
                  step="0.000001"
                  placeholder="36.753800"
                />
              </UiFormField>

              <UiFormField :label="t('tailors.profile_longitude')" field-id="tailor-longitude">
                <input
                  id="tailor-longitude"
                  v-model.number="locationForm.longitude"
                  class="input"
                  type="number"
                  step="0.000001"
                  placeholder="3.058800"
                />
              </UiFormField>
            </div>
          </details>

          <UiFormField :label="t('tailors.profile_work_wilaya')" field-id="tailor-work-wilaya">
            <select id="tailor-work-wilaya" v-model="locationForm.workWilaya" class="select">
              <option value="">{{ t('orders.select_wilaya') }}</option>
              <option v-for="wilaya in wilayaOptions" :key="wilaya" :value="wilaya">
                {{ wilaya }}
              </option>
            </select>
          </UiFormField>

          <button class="btn btn-primary" type="submit" :disabled="saveLoading">
            {{ saveLoading ? t('common.saving') : t('tailors.save_location_action') }}
          </button>
        </form>
      </div>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import LocationPickerMap from '@/components/maps/LocationPickerMap.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { fetchTailorProfile, updateTailorLocation } from '@/services/tailorService';
import { fetchTailorRegistrationMetadata } from '@/services/authService';
import { getErrorMessage } from '@/services/errorMessage';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const { successToast, errorToast } = useToast();
const { t } = useI18n();

const loading = ref(false);
const saveLoading = ref(false);
const locationLoading = ref(false);
const error = ref('');
const locationError = ref('');
const wilayaOptions = ref([]);

const profile = reactive({
  category_name: '',
  status: '',
  specialization: '',
  work_wilaya: '',
  years_of_experience: null,
  gender: '',
  workers_count: null,
  average_rating: 0,
  total_reviews: 0,
  score: 100,
  latitude: null,
  longitude: null,
});

const locationForm = reactive({
  latitude: null,
  longitude: null,
  workWilaya: '',
});

const genderLabel = computed(() => {
  if (!profile.gender) {
    return t('tailors.gender_unknown');
  }

  const key = `tailors.gender_${profile.gender}`;
  const label = t(key);

  return label === key ? profile.gender : label;
});

function tailorStatusLabel(status) {
  if (!status) {
    return t('tailors.status_unknown');
  }

  const key = `tailors.status_${status}`;
  const label = t(key);

  return label === key ? status : label;
}

function syncLocationForm() {
  locationForm.latitude = profile.latitude;
  locationForm.longitude = profile.longitude;
  locationForm.workWilaya = profile.work_wilaya || '';
}

function applySuggestedWilaya(wilaya) {
  if (wilaya) {
    locationForm.workWilaya = wilaya;
  }
}

function handleMapError(message) {
  locationError.value = message;
}

async function loadMetadata() {
  const response = await fetchTailorRegistrationMetadata();
  wilayaOptions.value = response.data?.wilayas || [];
}

async function load() {
  loading.value = true;
  error.value = '';

  try {
    const [profileResponse] = await Promise.all([fetchTailorProfile(), loadMetadata()]);
    Object.assign(profile, profileResponse.data || {});
    syncLocationForm();
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.tailor_profile_load_failed'));
  } finally {
    loading.value = false;
  }
}

function readBrowserLocation() {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocation is not supported by this browser.'));
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (position) => resolve(position),
      (positionError) => reject(positionError),
      {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 0,
      },
    );
  });
}

async function useCurrentLocation() {
  locationLoading.value = true;
  locationError.value = '';

  try {
    const position = await readBrowserLocation();
    locationForm.latitude = Number(position.coords.latitude.toFixed(6));
    locationForm.longitude = Number(position.coords.longitude.toFixed(6));
  } catch (err) {
    locationError.value = t('tailors.detect_location_failed');
    errorToast(locationError.value);
  } finally {
    locationLoading.value = false;
  }
}

async function saveLocation() {
  saveLoading.value = true;
  locationError.value = '';

  try {
    const response = await updateTailorLocation({
      latitude: locationForm.latitude,
      longitude: locationForm.longitude,
      work_wilaya: locationForm.workWilaya || null,
    });

    Object.assign(profile, response.data || {});
    syncLocationForm();
    successToast(response.message || t('tailors.location_saved_success'));
  } catch (err) {
    locationError.value = getErrorMessage(err, t('messages.tailor_save_location_failed'));
    errorToast(locationError.value);
  } finally {
    saveLoading.value = false;
  }
}

onMounted(load);
</script>
