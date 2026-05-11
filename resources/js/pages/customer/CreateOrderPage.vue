<template>
  <section class="stack">
    <UiSectionHeader
      :title="t('orders.create_title')"
      :description="t('orders.create_description')"
    />

    <div class="ui-card stack">
      <div class="row" style="justify-content: space-between; align-items: center;">
        <div class="stack" style="gap: 0.2rem;">
          <strong>{{ t('custom_orders.discovery_title') }}</strong>
          <p class="small">{{ t('custom_orders.discovery_description') }}</p>
        </div>
        <RouterLink class="btn btn-primary" :to="{ name: 'customerCustomOrders' }">
          {{ t('customers.custom_orders_nav') }}
        </RouterLink>
      </div>
    </div>

    <div class="order-flow-steps">
      <div class="order-flow-step" :class="{ 'is-active': currentStep === 1, 'is-complete': currentStep > 1 }">
        <span class="order-flow-step__index">1</span>
        <div>
          <strong>{{ t('orders.setup_step_label') }}</strong>
          <p>{{ t('orders.setup_step_description') }}</p>
        </div>
      </div>
      <div class="order-flow-step" :class="{ 'is-active': currentStep === 2 }">
        <span class="order-flow-step__index">2</span>
        <div>
          <strong>{{ t('orders.shipping_step_label') }}</strong>
          <p>{{ t('orders.shipping_step_description') }}</p>
        </div>
      </div>
    </div>

    <div class="ui-card stack">
      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <div v-if="locationError" class="alert alert-warning">{{ locationError }}</div>
      <div v-if="locationMessage" class="alert alert-info">{{ locationMessage }}</div>

      <form class="stack" @submit.prevent="submit">
        <template v-if="currentStep === 1">
          <div class="stack">
            <UiFormField :label="t('orders.product_label')" field-id="product">
              <select id="product" v-model="form.productId" class="select" required>
                <option value="">{{ t('orders.select_product') }}</option>
                <option v-for="item in products" :key="item.id" :value="String(item.id)">
                  {{ item.name }} ({{ formatMoney(item.sale_price ?? item.price) }})
                </option>
              </select>
              <p v-if="fieldError('product_id')" class="field-error">{{ fieldError('product_id') }}</p>
            </UiFormField>

            <div v-if="selectedProduct" class="ui-card stack product-order-summary">
              <div class="product-order-summary__media">
                <img
                  v-if="selectedProduct.main_image_url"
                  :src="selectedProduct.main_image_url"
                  :alt="selectedProduct.name"
                />
                <div v-else class="product-detail-placeholder">{{ productInitials }}</div>
              </div>

              <div class="stack" style="gap: 0.55rem;">
                <p class="small">{{ selectedProduct.category?.name || '-' }}</p>
                <h2 class="title" style="margin: 0;">{{ selectedProduct.name }}</h2>
                <div class="row" style="justify-content: space-between; align-items: center;">
                  <strong class="product-detail-price">{{ selectedProductPrice }}</strong>
                  <span class="badge badge-neutral">{{ selectedProductPricingTypeLabel }}</span>
                </div>
                <p class="small">{{ selectedProduct.details || selectedProduct.description || t('products.no_description_available') }}</p>
              </div>
            </div>

            <div
              v-if="selectedProduct && selectedProduct.color_options?.length"
              class="ui-card stack"
            >
              <div class="row" style="justify-content: space-between; align-items: center;">
                <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.color_selection_title') }}</h3>
                <span class="small">{{ t('orders.color_selection_hint') }}</span>
              </div>

              <div class="selection-grid">
                <button
                  v-for="color in selectedProduct.color_options"
                  :key="color.key"
                  type="button"
                  class="selection-card selection-card--color"
                  :class="{ 'is-selected': form.selectedColor === color.key }"
                  @click="form.selectedColor = color.key"
                >
                  <span
                    v-if="color.hex"
                    class="selection-card__swatch"
                    :style="{ backgroundColor: color.hex }"
                  ></span>
                  <span>{{ color.label }}</span>
                </button>
              </div>

              <p v-if="fieldError('configuration.color')" class="field-error">{{ fieldError('configuration.color') }}</p>
            </div>

            <div
              v-if="selectedProduct && selectedProduct.available_fabrics?.length"
              class="ui-card stack"
            >
              <div class="row" style="justify-content: space-between; align-items: center;">
                <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.fabric_selection_title') }}</h3>
                <span class="small">{{ t('orders.fabric_selection_hint') }}</span>
              </div>

              <div class="selection-grid">
                <button
                  v-for="fabric in selectedProduct.available_fabrics"
                  :key="fabric.key"
                  type="button"
                  class="selection-card selection-card--fabric"
                  :class="{ 'is-selected': form.selectedFabric === fabric.key }"
                  @click="form.selectedFabric = fabric.key"
                >
                  <div class="stack" style="gap: 0.3rem; align-items: flex-start;">
                    <strong>{{ fabric.label }}</strong>
                    <span class="small" v-if="fabric.description">{{ fabric.description }}</span>
                  </div>
                </button>
              </div>

              <p v-if="fieldError('configuration.fabric')" class="field-error">{{ fieldError('configuration.fabric') }}</p>
            </div>

            <div v-if="selectedProduct" class="ui-card stack">
              <div class="row" style="justify-content: space-between; align-items: center; gap: 0.75rem;">
                <div>
                  <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.measurements_required_title') }}</h3>
                  <p class="small" style="margin: 0;">
                    {{ hasStructuredMeasurements ? t('orders.measurements_fields_count', { count: measurementDefinitions.length }) : t('orders.measurements_legacy_mode') }}
                  </p>
                </div>
                <span class="badge badge-info">{{ t('orders.measurements_help_badge') }}</span>
              </div>

              <template v-if="hasStructuredMeasurements">
                <div
                  v-for="measurement in measurementDefinitions"
                  :key="measurement.id"
                  class="stack order-measurement-card"
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

                  <div
                    v-if="isGuideOpen(measurement.slug)"
                    class="stack order-guide-card"
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
          </div>

          <div class="actions">
            <button class="btn btn-primary" type="button" @click="goToShippingStep">
              {{ t('orders.continue_to_shipping') }}
            </button>
          </div>
        </template>

        <template v-else>
          <div v-if="selectedProduct" class="ui-card stack">
            <div class="row" style="justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
              <div>
                <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.configuration_summary_title') }}</h3>
                <p class="small" style="margin: 0;">{{ t('orders.configuration_summary_description') }}</p>
              </div>
              <strong class="product-detail-price">{{ selectedProductPrice }}</strong>
            </div>

            <div class="grid grid-2">
              <div class="product-spec-card">
                <span class="product-spec-label">{{ t('orders.product_label') }}</span>
                <strong class="product-spec-value">{{ selectedProduct.name }}</strong>
              </div>
              <div class="product-spec-card">
                <span class="product-spec-label">{{ t('orders.selected_color_label') }}</span>
                <strong class="product-spec-value">{{ selectedColorLabel }}</strong>
              </div>
              <div class="product-spec-card">
                <span class="product-spec-label">{{ t('orders.selected_fabric_label') }}</span>
                <strong class="product-spec-value">{{ selectedFabricLabel }}</strong>
              </div>
              <div class="product-spec-card">
                <span class="product-spec-label">{{ t('products.price_label') }}</span>
                <strong class="product-spec-value">{{ selectedProductPrice }}</strong>
              </div>
            </div>
          </div>

          <div class="ui-card stack">
            <div class="row" style="justify-content: space-between; align-items: center;">
              <div>
                <h3 class="title" style="font-size: 1rem; margin: 0;">{{ t('orders.shipping_section_title') }}</h3>
                <p class="small" style="margin: 0;">{{ t('orders.shipping_section_description') }}</p>
              </div>
              <span class="badge badge-warning">{{ t('orders.office_pickup_only_notice') }}</span>
            </div>

            <div class="grid grid-2">
              <UiFormField :label="t('orders.shipping_company_label')" field-id="shipping-company">
                <select id="shipping-company" v-model="form.shippingCompanyId" class="select" required>
                  <option value="">{{ t('orders.select_shipping_company') }}</option>
                  <option v-for="company in shippingCompanies" :key="company.id" :value="String(company.id)">
                    {{ company.name }}
                  </option>
                </select>
                <p v-if="selectedShippingCompany?.description" class="small">{{ selectedShippingCompany.description }}</p>
                <p v-if="fieldError('shipping.company_id')" class="field-error">{{ fieldError('shipping.company_id') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_type_label')" field-id="delivery-type">
                <select id="delivery-type" v-model="form.deliveryType" class="select" required>
                  <option
                    v-for="type in deliveryTypes"
                    :key="type.value"
                    :value="type.value"
                  >
                    {{ type.label }}
                  </option>
                </select>
                <p v-if="fieldError('shipping.delivery_type')" class="field-error">{{ fieldError('shipping.delivery_type') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_phone_label')" field-id="delivery-phone">
                <input id="delivery-phone" v-model="form.phone" class="input" type="tel" maxlength="40" required />
                <p v-if="fieldError('shipping.phone')" class="field-error">{{ fieldError('shipping.phone') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_email_label')" field-id="delivery-email">
                <input id="delivery-email" v-model="form.email" class="input" type="email" required />
                <p v-if="fieldError('shipping.email')" class="field-error">{{ fieldError('shipping.email') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_wilaya_label')" field-id="delivery-work-wilaya">
                <select id="delivery-work-wilaya" v-model="form.workWilaya" class="select" required>
                  <option value="">{{ t('orders.select_wilaya') }}</option>
                  <option v-for="wilaya in wilayaOptions" :key="wilaya" :value="wilaya">
                    {{ wilaya }}
                  </option>
                </select>
                <p v-if="fieldError('customer_location.work_wilaya')" class="field-error">{{ fieldError('customer_location.work_wilaya') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_commune_label')" field-id="delivery-commune">
                <input id="delivery-commune" v-model="form.commune" class="input" type="text" maxlength="120" required />
                <p v-if="fieldError('shipping.commune')" class="field-error">{{ fieldError('shipping.commune') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_neighborhood_label')" field-id="delivery-neighborhood">
                <input id="delivery-neighborhood" v-model="form.neighborhood" class="input" type="text" maxlength="120" required />
                <p v-if="fieldError('shipping.neighborhood')" class="field-error">{{ fieldError('shipping.neighborhood') }}</p>
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

          <div class="actions">
            <button class="btn" type="button" @click="currentStep = 1">
              {{ t('orders.back_to_configuration') }}
            </button>
            <button class="btn btn-primary" type="submit" :disabled="loading">
              {{ loading ? t('orders.creating_order') : t('orders.create_order_action') }}
            </button>
          </div>
        </template>
      </form>
    </div>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import LocationPickerMap from '@/components/maps/LocationPickerMap.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import { useAuthStore } from '@/stores/auth';
import { fetchProduct, fetchProducts } from '@/services/catalogService';
import { createOrder, fetchOrderMetadata } from '@/services/customerOrderService';
import { fetchTailorRegistrationMetadata } from '@/services/authService';
import { getErrorMessage } from '@/services/errorMessage';
import { useToast } from '@/composables/useToast';
import { useI18n } from '@/composables/useI18n';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const { successToast, errorToast } = useToast();
const { t } = useI18n();

const currentStep = ref(1);
const loading = ref(false);
const locationLoading = ref(false);
const error = ref('');
const locationError = ref('');
const locationMessage = ref('');
const validationErrors = ref({});
const products = ref([]);
const selectedProduct = ref(null);
const onboardingMetadata = ref({ wilayas: [] });
const orderMetadata = ref({ shipping_companies: [], delivery_types: [], customer: {} });
const guideOpenState = ref({});

const measurementInputs = reactive({});

const wilayaOptions = computed(() => onboardingMetadata.value.wilayas || []);
const measurementDefinitions = computed(() => selectedProduct.value?.measurements || []);
const hasStructuredMeasurements = computed(() => measurementDefinitions.value.length > 0);
const shippingCompanies = computed(() => orderMetadata.value.shipping_companies || []);
const deliveryTypes = computed(() => orderMetadata.value.delivery_types || []);
const selectedShippingCompany = computed(() => shippingCompanies.value.find((company) => String(company.id) === form.shippingCompanyId) || null);
const productInitials = computed(() => selectedProduct.value?.name
  ?.split(' ')
  .filter(Boolean)
  .slice(0, 2)
  .map((segment) => segment.charAt(0).toUpperCase())
  .join('') || 'MK');
const selectedProductPrice = computed(() => formatMoney(selectedProduct.value?.sale_price ?? selectedProduct.value?.price));
const selectedProductPricingTypeLabel = computed(() => t(`products.pricing_types.${selectedProduct.value?.pricing_type || 'unknown'}`));
const selectedColorLabel = computed(() => {
  if (! selectedProduct.value?.color_options?.length) {
    return t('common.not_available');
  }

  return selectedProduct.value.color_options.find((option) => option.key === form.selectedColor)?.label || t('common.not_available');
});
const selectedFabricLabel = computed(() => {
  if (! selectedProduct.value?.available_fabrics?.length) {
    return t('common.not_available');
  }

  return selectedProduct.value.available_fabrics.find((option) => option.key === form.selectedFabric)?.label || t('common.not_available');
});

const form = reactive({
  productId: '',
  selectedColor: '',
  selectedFabric: '',
  latitude: 36.7538,
  longitude: 3.0588,
  workWilaya: '',
  locationLabel: '',
  commune: '',
  neighborhood: '',
  shippingCompanyId: '',
  deliveryType: 'office_pickup',
  phone: '',
  email: '',
  legacyMeasurementsJson: '{"height":170,"waist":80}',
});

function formatMoney(value) {
  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return `${new Intl.NumberFormat(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(Number(value))} MAD`;
}

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

function ensureDefaultSelections() {
  const colorOptions = selectedProduct.value?.color_options || [];
  const fabricOptions = selectedProduct.value?.available_fabrics || [];

  if (colorOptions.length === 1 && !form.selectedColor) {
    form.selectedColor = colorOptions[0].key;
  }

  if (fabricOptions.length === 1 && !form.selectedFabric) {
    form.selectedFabric = fabricOptions[0].key;
  }

  if (form.selectedColor && !colorOptions.some((option) => option.key === form.selectedColor)) {
    form.selectedColor = '';
  }

  if (form.selectedFabric && !fabricOptions.some((option) => option.key === form.selectedFabric)) {
    form.selectedFabric = '';
  }
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
  ensureDefaultSelections();
}

async function loadOnboardingMetadata() {
  const response = await fetchTailorRegistrationMetadata();
  onboardingMetadata.value = response.data || { wilayas: [] };
}

async function loadOrderMetadata() {
  const response = await fetchOrderMetadata();
  orderMetadata.value = response.data || { shipping_companies: [], delivery_types: [], customer: {} };

  if (!form.deliveryType && orderMetadata.value.delivery_types?.[0]?.value) {
    form.deliveryType = orderMetadata.value.delivery_types[0].value;
  }

  if (!form.shippingCompanyId && shippingCompanies.value.length) {
    form.shippingCompanyId = String(shippingCompanies.value[0].id);
  }

  if (!form.email) {
    form.email = orderMetadata.value.customer?.email || authStore.user?.email || '';
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

function validateConfigurationStep() {
  error.value = '';

  if (!selectedProduct.value) {
    error.value = t('orders.product_required_error');
    errorToast(error.value);
    return false;
  }

  if (selectedProduct.value.color_options?.length && !form.selectedColor) {
    error.value = t('orders.color_selection_required');
    errorToast(error.value);
    return false;
  }

  if (selectedProduct.value.available_fabrics?.length && !form.selectedFabric) {
    error.value = t('orders.fabric_selection_required');
    errorToast(error.value);
    return false;
  }

  if (hasStructuredMeasurements.value) {
    const isIncomplete = measurementDefinitions.value.some((definition) => {
      const value = Number(measurementInputs[definition.slug]);
      return !Number.isFinite(value) || value <= 0;
    });

    if (isIncomplete) {
      error.value = t('orders.measurements_incomplete');
      errorToast(error.value);
      return false;
    }

    return true;
  }

  try {
    buildMeasurementPayload();
    return true;
  } catch (parseError) {
    error.value = t('orders.measurements_json_invalid');
    errorToast(error.value);
    return false;
  }
}

function goToShippingStep() {
  if (!validateConfigurationStep()) {
    return;
  }

  currentStep.value = 2;
  validationErrors.value = {};
}

async function submit() {
  if (currentStep.value !== 2) {
    goToShippingStep();
    return;
  }

  error.value = '';
  validationErrors.value = {};
  loading.value = true;

  try {
    const measurements = buildMeasurementPayload();

    const response = await createOrder({
      product_id: Number(form.productId),
      configuration: {
        color: form.selectedColor || null,
        fabric: form.selectedFabric || null,
      },
      measurements,
      customer_location: {
        latitude: Number(form.latitude),
        longitude: Number(form.longitude),
        work_wilaya: form.workWilaya,
        label: form.locationLabel || null,
      },
      shipping: {
        company_id: Number(form.shippingCompanyId),
        delivery_type: form.deliveryType,
        commune: form.commune,
        neighborhood: form.neighborhood,
        phone: form.phone,
        email: form.email,
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
    currentStep.value = 1;

    if (!productId) {
      selectedProduct.value = null;
      form.selectedColor = '';
      form.selectedFabric = '';
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
    form.email = authStore.user?.email || '';
    form.phone = authStore.user?.phone || '';

    await Promise.all([
      loadProducts(),
      loadOnboardingMetadata(),
      loadOrderMetadata(),
    ]);

    await detectCurrentLocation(false);
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.orders_create_init_failed'));
    errorToast(error.value);
  }
});
</script>
