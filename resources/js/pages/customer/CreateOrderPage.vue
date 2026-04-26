<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('orders.create_title')"
      :description="t('orders.create_description')"
    />

    <div class="ui-card stack">
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <div v-if="locationError" class="alert alert-warning">{{ locationError }}</div>
      <div v-if="locationMessage" class="alert alert-info">{{ locationMessage }}</div>

      <form class="stack" @submit.prevent="submit">
        <UiFormField :label="t('orders.product_label')" field-id="product">
          <select id="product" v-model="form.productId" class="select" required>
            <option value="">{{ t('orders.select_product') }}</option>
            <option v-for="product in products" :key="product.id" :value="String(product.id)">
              {{ product.name }} ({{ product.price }})
            </option>
          </select>
          <p v-if="fieldError('product_id')" class="field-error">{{ fieldError('product_id') }}</p>
        </UiFormField>

        <div
          v-if="selectedProduct && hasSelectedProductFabricInfo"
          class="ui-card stack"
          style="border: 1px solid rgba(148, 163, 184, 0.25);"
        >
          <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.fabric_section_title') }}</h3>

          <div class="grid grid-2">
            <UiStatBlock :label="t('orders.fabric_type_label')" :value="selectedProduct.fabric_type || '-'" />
            <UiStatBlock :label="t('orders.fabric_country_label')" :value="selectedProduct.fabric_country || '-'" />
          </div>

          <p v-if="selectedProduct.fabric_description" class="small" style="margin: 0;">
            {{ selectedProduct.fabric_description }}
          </p>

          <div v-if="selectedProduct.fabric_image_url" class="stack" style="gap: 0.45rem;">
            <a
              :href="selectedProduct.fabric_image_url"
              class="btn"
              target="_blank"
              rel="noopener noreferrer"
              style="width: fit-content;"
            >
              {{ t('orders.open_fabric_image') }}
            </a>
            <img
              :src="selectedProduct.fabric_image_url"
              :alt="t('orders.fabric_image_alt', { name: selectedProduct.name })"
              style="max-width: 240px; border-radius: 0.6rem; border: 1px solid rgba(148, 163, 184, 0.25);"
            />
          </div>
        </div>

        <div v-if="selectedProduct" class="ui-card stack" style="border: 1px solid rgba(148, 163, 184, 0.25);">
          <div class="row" style="justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.measurements_required_title') }}</h3>
            <p class="small" style="margin: 0;">
              {{ hasStructuredMeasurements ? t('orders.measurements_fields_count', { count: measurementDefinitions.length }) : t('orders.measurements_legacy_mode') }}
            </p>
          </div>

          <template v-if="hasStructuredMeasurements">
            <div
              v-for="measurement in measurementDefinitions"
              :key="measurement.id"
              class="stack"
              style="border: 1px solid rgba(148, 163, 184, 0.2); border-radius: 0.75rem; padding: 0.85rem;"
            >
              <div class="row" style="justify-content: space-between; align-items: flex-start; gap: 0.75rem;">
                <div>
                  <p class="label" style="margin-bottom: 0.2rem;">{{ measurement.name }}</p>
                  <p v-if="measurement.helper_text" class="small" style="margin: 0;">{{ measurement.helper_text }}</p>
                </div>
                <button
                  class="btn"
                  type="button"
                  style="white-space: nowrap;"
                  @click="toggleGuide(measurement.slug)"
                >
                  {{ isGuideOpen(measurement.slug) ? t('orders.hide_measurement_guide') : t('orders.show_measurement_guide') }}
                </button>
              </div>

              <div class="grid grid-2">
                <UiFormField :label="t('orders.measurement_input_label', { name: measurement.name })" :field-id="`measurement-${measurement.slug}`">
                  <input
                    :id="`measurement-${measurement.slug}`"
                    v-model.number="measurementInputs[measurement.slug]"
                    class="input"
                    type="number"
                    min="0.1"
                    max="350"
                    step="0.1"
                    required
                  />
                  <p v-if="fieldError(`measurements.${measurement.slug}`)" class="field-error">
                    {{ fieldError(`measurements.${measurement.slug}`) }}
                  </p>
                </UiFormField>
              </div>

              <div
                v-if="isGuideOpen(measurement.slug)"
                class="stack"
                style="background: rgba(241, 245, 249, 0.65); border-radius: 0.75rem; padding: 0.75rem;"
              >
                <p class="small" style="margin: 0;">
                  {{ measurement.guide_text || t('orders.measurement_no_guide') }}
                </p>

                <div v-if="measurement.guide_image_url" class="stack" style="gap: 0.45rem;">
                  <a
                    :href="measurement.guide_image_url"
                    class="btn"
                    target="_blank"
                    rel="noopener noreferrer"
                    style="width: fit-content;"
                  >
                    {{ t('orders.open_measurement_guide_image') }}
                  </a>
                  <img
                    :src="measurement.guide_image_url"
                    :alt="t('orders.measurement_guide_alt', { name: measurement.name })"
                    style="max-width: 240px; border-radius: 0.6rem; border: 1px solid rgba(148, 163, 184, 0.25);"
                  />
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <p class="small" style="margin: 0;">
              {{ t('orders.measurements_legacy_description') }}
            </p>
            <UiFormField :label="t('orders.measurements_json_label')" field-id="measurements" :hint="t('orders.measurements_json_hint')">
              <textarea
                id="measurements"
                v-model="form.legacyMeasurementsJson"
                class="textarea"
                rows="6"
                required
              ></textarea>
              <p v-if="fieldError('measurements')" class="field-error">{{ fieldError('measurements') }}</p>
            </UiFormField>
          </template>
        </div>

        <div class="row" style="justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
          <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.delivery_location_title') }}</h3>
          <button class="btn" type="button" :disabled="locationLoading" @click="detectCurrentLocation(false)">
            {{ locationLoading ? t('orders.detecting_location') : t('orders.use_current_location') }}
          </button>
        </div>

        <LocationPickerMap
          v-model:latitude="form.latitude"
          v-model:longitude="form.longitude"
          :wilaya-options="wilayaOptions"
          @wilaya-suggested="applySuggestedWilaya"
          @label-suggested="applySuggestedLocationLabel"
          @error="handleMapError"
        />

        <details class="ui-card" style="padding: 0.85rem;">
          <summary class="label" style="cursor: pointer;">{{ t('maps.manual_fallback') }}</summary>
          <div class="grid grid-2" style="margin-top: 0.75rem;">
            <UiFormField :label="t('orders.delivery_latitude_label')" field-id="latitude">
              <input id="latitude" v-model.number="form.latitude" class="input" type="number" step="0.000001" required />
              <p v-if="fieldError('customer_location.latitude')" class="field-error">{{ fieldError('customer_location.latitude') }}</p>
            </UiFormField>

            <UiFormField :label="t('orders.delivery_longitude_label')" field-id="longitude">
              <input id="longitude" v-model.number="form.longitude" class="input" type="number" step="0.000001" required />
              <p v-if="fieldError('customer_location.longitude')" class="field-error">{{ fieldError('customer_location.longitude') }}</p>
            </UiFormField>
          </div>
        </details>

        <div class="grid grid-2">
          <UiFormField :label="t('orders.delivery_wilaya_label')" field-id="delivery-work-wilaya">
            <select id="delivery-work-wilaya" v-model="form.workWilaya" class="select">
              <option value="">{{ t('orders.select_wilaya') }}</option>
              <option v-for="wilaya in wilayaOptions" :key="wilaya" :value="wilaya">
                {{ wilaya }}
              </option>
            </select>
            <p v-if="fieldError('customer_location.work_wilaya')" class="field-error">{{ fieldError('customer_location.work_wilaya') }}</p>
          </UiFormField>

          <UiFormField :label="t('orders.delivery_location_label_label')" field-id="location-label">
            <input
              id="location-label"
              v-model="form.locationLabel"
              class="input"
              type="text"
              maxlength="255"
              :placeholder="t('orders.delivery_location_label_placeholder')"
            />
            <p v-if="fieldError('customer_location.label')" class="field-error">{{ fieldError('customer_location.label') }}</p>
          </UiFormField>
        </div>

        <button class="btn btn-primary" type="submit" :disabled="loading">
          {{ loading ? t('orders.creating_order') : t('orders.create_order_action') }}
        </button>
      </form>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import LocationPickerMap from '@/components/maps/LocationPickerMap.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import UiStatBlock from '@/components/ui/UiStatBlock.vue';
import { fetchProduct, fetchProducts } from '@/services/catalogService';
import { createOrder } from '@/services/customerOrderService';
import { fetchTailorRegistrationMetadata } from '@/services/authService';
import { getErrorMessage } from '@/services/errorMessage';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const router = useRouter();
const { successToast, errorToast } = useToast();
const { t } = useI18n();

const loading = ref(false);
const locationLoading = ref(false);
const error = ref('');
const locationError = ref('');
const locationMessage = ref('');
const validationErrors = ref({});
const products = ref([]);
const selectedProduct = ref(null);
const onboardingMetadata = ref({ wilayas: [] });
const guideOpenState = ref({});

const measurementInputs = reactive({});

const wilayaOptions = computed(() => onboardingMetadata.value.wilayas || []);
const measurementDefinitions = computed(() => selectedProduct.value?.measurements || []);
const hasStructuredMeasurements = computed(() => measurementDefinitions.value.length > 0);
const hasSelectedProductFabricInfo = computed(() => {
  if (!selectedProduct.value) {
    return false;
  }

  return Boolean(
    selectedProduct.value.fabric_type
      || selectedProduct.value.fabric_country
      || selectedProduct.value.fabric_description
      || selectedProduct.value.fabric_image_url,
  );
});

const form = reactive({
  productId: '',
  latitude: 36.7538,
  longitude: 3.0588,
  workWilaya: '',
  locationLabel: '',
  legacyMeasurementsJson: '{"height":170,"waist":80}',
});

function fieldError(field) {
  if (!validationErrors.value || !validationErrors.value[field]) {
    return '';
  }

  return String(validationErrors.value[field][0] || '');
}

function resetMeasurementInputs(definitions) {
  Object.keys(measurementInputs).forEach((key) => {
    delete measurementInputs[key];
  });

  definitions.forEach((definition) => {
    measurementInputs[definition.slug] = '';
  });
}

function isGuideOpen(slug) {
  return Boolean(guideOpenState.value[slug]);
}

function toggleGuide(slug) {
  guideOpenState.value[slug] = !guideOpenState.value[slug];
}

function applySuggestedWilaya(wilaya) {
  if (wilaya) {
    form.workWilaya = wilaya;
  }
}

function applySuggestedLocationLabel(label) {
  if (label && !form.locationLabel) {
    form.locationLabel = label;
  }
}

function handleMapError(message) {
  locationError.value = message;
}

async function loadProducts() {
  const response = await fetchProducts({ per_page: 100 });
  products.value = response.data || [];

  if (!form.productId && route.query.productId) {
    form.productId = String(route.query.productId);
  }
}

async function loadSelectedProduct(productId) {
  const response = await fetchProduct(productId);
  selectedProduct.value = response.data || null;
  resetMeasurementInputs(measurementDefinitions.value);
  guideOpenState.value = {};
}

async function loadMetadata() {
  const response = await fetchTailorRegistrationMetadata();
  onboardingMetadata.value = response.data || { wilayas: [] };
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

async function detectCurrentLocation(showErrorToast = true) {
  locationLoading.value = true;
  locationError.value = '';
  locationMessage.value = '';

  try {
    const position = await readBrowserLocation();
    form.latitude = Number(position.coords.latitude.toFixed(6));
    form.longitude = Number(position.coords.longitude.toFixed(6));
    locationMessage.value = t('orders.location_captured_success');
  } catch (err) {
    locationError.value = t('orders.location_capture_failed');

    if (showErrorToast) {
      errorToast(locationError.value);
    }
  } finally {
    locationLoading.value = false;
  }
}

function buildMeasurementPayload() {
  if (hasStructuredMeasurements.value) {
    return measurementDefinitions.value.reduce((accumulator, definition) => {
      accumulator[definition.slug] = Number(measurementInputs[definition.slug]);
      return accumulator;
    }, {});
  }

  return JSON.parse(form.legacyMeasurementsJson);
}

async function submit() {
  error.value = '';
  validationErrors.value = {};
  loading.value = true;

  try {
    const measurements = buildMeasurementPayload();

    const response = await createOrder({
      product_id: Number(form.productId),
      measurements,
      customer_location: {
        latitude: Number(form.latitude),
        longitude: Number(form.longitude),
        work_wilaya: form.workWilaya || null,
        label: form.locationLabel || null,
      },
    });

    successToast(response.message || t('notifications.order_created'));

    if (response?.data?.id) {
      router.push({ name: 'customerOrderDetails', params: { id: response.data.id } });
    }
  } catch (err) {
    validationErrors.value = err?.errors || {};

    if (err instanceof SyntaxError) {
      error.value = t('orders.measurements_json_invalid');
    } else {
      error.value = getErrorMessage(err, t('messages.orders_create_failed'));
    }

    errorToast(error.value || t('notifications.action_failed'));
  } finally {
    loading.value = false;
  }
}

watch(
  () => form.productId,
  async (productId) => {
    validationErrors.value = {};

    if (!productId) {
      selectedProduct.value = null;
      resetMeasurementInputs([]);
      guideOpenState.value = {};
      return;
    }

    try {
      await loadSelectedProduct(productId);
    } catch (err) {
      selectedProduct.value = null;
      error.value = getErrorMessage(err, t('messages.measurements_load_failed'));
      errorToast(error.value);
    }
  },
);

onMounted(async () => {
  try {
    await Promise.all([loadProducts(), loadMetadata()]);
    await detectCurrentLocation(false);
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.orders_create_init_failed'));
    errorToast(error.value);
  }
});
</script>
