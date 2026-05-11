<template>
  <section class="stack">
    <UiSectionHeader
      :eyebrow="t('customers.dashboard_badge')"
      :title="t('custom_orders.page_title')"
      :description="t('custom_orders.page_description')"
    />

    <LoadingState v-if="loading" :label="t('custom_orders.loading_init')" />

    <div v-else class="customer-dashboard-grid customer-dashboard-grid--wide">
      <form class="ui-card stack" @submit.prevent="submit">
        <div class="row" style="justify-content: space-between; align-items: center;">
          <div class="stack" style="gap: 0.2rem;">
            <strong>{{ t('custom_orders.create_title') }}</strong>
            <p class="small">{{ t('custom_orders.create_description') }}</p>
          </div>
          <span class="badge badge-warning">{{ t('custom_orders.quote_badge') }}</span>
        </div>

        <div v-if="error" class="ui-alert ui-alert--danger">
          <p class="ui-alert-message">{{ error }}</p>
        </div>

        <div class="grid grid-2">
          <UiFormField :label="t('custom_orders.title_label')" field-id="custom-order-title">
            <input id="custom-order-title" v-model="form.title" class="input" type="text" maxlength="160" required>
            <p v-if="fieldError('title')" class="field-error">{{ fieldError('title') }}</p>
          </UiFormField>

          <UiFormField :label="t('custom_orders.specialty_label')" field-id="custom-order-specialty">
            <select id="custom-order-specialty" v-model="form.tailorSpecialty" class="select" required>
              <option value="">{{ t('custom_orders.select_specialty') }}</option>
              <option v-for="specialty in specialties" :key="specialty" :value="specialty">
                {{ specialty }}
              </option>
            </select>
            <p v-if="fieldError('tailor_specialty')" class="field-error">{{ fieldError('tailor_specialty') }}</p>
          </UiFormField>

          <UiFormField :label="t('custom_orders.fabric_type_label')" field-id="custom-order-fabric">
            <input id="custom-order-fabric" v-model="form.fabricType" class="input" type="text" maxlength="120">
            <p class="small">{{ t('custom_orders.fabric_type_hint') }}</p>
            <p v-if="fieldError('fabric_type')" class="field-error">{{ fieldError('fabric_type') }}</p>
          </UiFormField>

          <UiFormField :label="t('custom_orders.reference_images_label')" field-id="custom-order-images">
            <input
              id="custom-order-images"
              class="input"
              type="file"
              accept=".jpg,.jpeg,.png,.webp"
              multiple
              @change="onImageChange"
            >
            <p class="small">{{ t('custom_orders.reference_images_hint') }}</p>
            <p v-if="fieldError('reference_images')" class="field-error">{{ fieldError('reference_images') }}</p>
            <p v-if="fieldError('reference_images.0')" class="field-error">{{ fieldError('reference_images.0') }}</p>
          </UiFormField>
        </div>

        <UiFormField :label="t('custom_orders.notes_label')" field-id="custom-order-notes">
          <textarea id="custom-order-notes" v-model="form.notes" class="textarea" rows="4"></textarea>
          <p class="small">{{ t('custom_orders.notes_hint') }}</p>
          <p v-if="fieldError('notes')" class="field-error">{{ fieldError('notes') }}</p>
        </UiFormField>

        <div class="ui-card stack" style="padding: 1rem;">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('custom_orders.measurements_title') }}</strong>
              <p class="small">{{ t('custom_orders.measurements_description') }}</p>
            </div>
            <span class="badge badge-info">{{ t('custom_orders.measurements_optional_badge') }}</span>
          </div>

          <div class="custom-order-measurements-grid">
            <UiFormField
              v-for="measurement in measurements"
              :key="measurement.slug"
              :label="measurement.name"
              :field-id="`custom-measurement-${measurement.slug}`"
            >
              <input
                :id="`custom-measurement-${measurement.slug}`"
                v-model.number="measurementInputs[measurement.slug]"
                class="input"
                type="number"
                min="0.1"
                max="350"
                step="0.1"
              >
              <p v-if="measurement.helper_text" class="small">{{ measurement.helper_text }}</p>
              <p v-if="fieldError(`measurements.${measurement.slug}`)" class="field-error">
                {{ fieldError(`measurements.${measurement.slug}`) }}
              </p>
            </UiFormField>
          </div>
        </div>

        <div class="ui-card stack" style="padding: 1rem;">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('custom_orders.delivery_title') }}</strong>
              <p class="small">{{ t('custom_orders.delivery_description') }}</p>
            </div>
            <button class="btn" type="button" :disabled="locationLoading" @click="detectCurrentLocation(true)">
              {{ locationLoading ? t('orders.detecting_location') : t('orders.use_current_location') }}
            </button>
          </div>

          <div v-if="locationError" class="ui-alert ui-alert--warning">
            <p class="ui-alert-message">{{ locationError }}</p>
          </div>

          <div class="grid grid-2">
            <UiFormField :label="t('orders.delivery_wilaya_label')" field-id="custom-order-wilaya">
              <select id="custom-order-wilaya" v-model="form.workWilaya" class="select" required>
                <option value="">{{ t('orders.select_wilaya') }}</option>
                <option v-for="wilaya in wilayas" :key="wilaya" :value="wilaya">
                  {{ wilaya }}
                </option>
              </select>
              <p v-if="fieldError('customer_location.work_wilaya')" class="field-error">{{ fieldError('customer_location.work_wilaya') }}</p>
            </UiFormField>

            <UiFormField :label="t('orders.delivery_location_label_label')" field-id="custom-order-location-note">
              <input
                id="custom-order-location-note"
                v-model="form.locationLabel"
                class="input"
                type="text"
                :placeholder="t('orders.delivery_location_label_placeholder')"
              >
            </UiFormField>
          </div>

          <LocationPickerMap
            v-model:latitude="form.latitude"
            v-model:longitude="form.longitude"
            :wilaya-options="wilayas"
            @wilaya-suggested="applySuggestedWilaya"
            @label-suggested="applySuggestedLocationLabel"
            @error="handleMapError"
          />

          <details class="ui-card" style="padding: 0.85rem;">
            <summary class="label" style="cursor: pointer;">{{ t('maps.manual_fallback') }}</summary>
            <div class="grid grid-2" style="margin-top: 0.75rem;">
              <UiFormField :label="t('orders.delivery_latitude_label')" field-id="custom-latitude">
                <input id="custom-latitude" v-model.number="form.latitude" class="input" type="number" step="0.000001" required>
                <p v-if="fieldError('customer_location.latitude')" class="field-error">{{ fieldError('customer_location.latitude') }}</p>
              </UiFormField>

              <UiFormField :label="t('orders.delivery_longitude_label')" field-id="custom-longitude">
                <input id="custom-longitude" v-model.number="form.longitude" class="input" type="number" step="0.000001" required>
                <p v-if="fieldError('customer_location.longitude')" class="field-error">{{ fieldError('customer_location.longitude') }}</p>
              </UiFormField>
            </div>
          </details>
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="submit" :disabled="submitting">
            {{ submitting ? t('custom_orders.submitting') : t('custom_orders.submit_action') }}
          </button>
        </div>
      </form>

      <div class="stack">
        <div class="ui-card stack">
          <div class="row" style="justify-content: space-between; align-items: center;">
            <div class="stack" style="gap: 0.2rem;">
              <strong>{{ t('custom_orders.manage_title') }}</strong>
              <p class="small">{{ t('custom_orders.manage_description') }}</p>
            </div>
            <div class="actions">
              <button
                class="btn"
                type="button"
                :class="{ 'btn-primary': scope === 'active' }"
                @click="changeScope('active')"
              >
                {{ t('custom_orders.scope_active') }}
              </button>
              <button
                class="btn"
                type="button"
                :class="{ 'btn-primary': scope === 'history' }"
                @click="changeScope('history')"
              >
                {{ t('custom_orders.scope_history') }}
              </button>
            </div>
          </div>

          <LoadingState v-if="ordersLoading" :label="t('custom_orders.loading_orders')" />
          <ErrorState v-else-if="ordersError" :message="ordersError" retryable @retry="loadOrders" />
          <EmptyState v-else-if="orders.length === 0" :message="scope === 'history' ? t('custom_orders.empty_history') : t('custom_orders.empty_active')" />

          <div v-else class="stack">
            <article v-for="item in orders" :key="item.id" class="customer-custom-order-card">
              <div class="row" style="justify-content: space-between; align-items: flex-start;">
                <div class="stack" style="gap: 0.2rem;">
                  <strong>{{ item.title }}</strong>
                  <p class="small">{{ formatDate(item.timestamps?.created_at) }}</p>
                </div>
                <CustomOrderStatusBadge :status="item.status" />
              </div>

              <div v-if="item.images?.length" class="customer-custom-order-card__gallery">
                <img
                  v-for="image in item.images"
                  :key="image.id"
                  :src="image.url"
                  :alt="item.title"
                  loading="lazy"
                >
              </div>

              <div class="grid grid-2">
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('custom_orders.specialty_label') }}</span>
                  <strong class="product-spec-value">{{ item.tailor_specialty }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('custom_orders.fabric_type_label') }}</span>
                  <strong class="product-spec-value">{{ item.fabric_type || t('common.not_available') }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('orders.tailor_label') }}</span>
                  <strong class="product-spec-value">{{ item.tailor?.name || t('customers.awaiting_tailor_assignment') }}</strong>
                </div>
                <div class="product-spec-card">
                  <span class="product-spec-label">{{ t('orders.delivery_wilaya_label') }}</span>
                  <strong class="product-spec-value">{{ item.delivery?.work_wilaya || '-' }}</strong>
                </div>
              </div>

              <div v-if="measurementPairs(item).length" class="stack" style="gap: 0.55rem;">
                <p class="label" style="margin: 0;">{{ t('custom_orders.measurements_title') }}</p>
                <div class="customer-measurement-tags">
                  <span v-for="measurement in measurementPairs(item)" :key="measurement.key" class="badge badge-neutral">
                    {{ measurement.label }}: {{ measurement.value }} cm
                  </span>
                </div>
              </div>

              <p v-if="item.notes" class="small">{{ item.notes }}</p>

              <div v-if="item.quote?.amount || item.quote?.note || item.quote?.rejection_note" class="ui-card stack" style="padding: 0.9rem;">
                <div class="row" style="justify-content: space-between; align-items: flex-start;">
                  <div class="stack" style="gap: 0.2rem;">
                    <strong>{{ t('custom_orders.quote_section_title') }}</strong>
                    <p class="small">{{ item.quote?.quoted_at ? formatDate(item.quote.quoted_at) : t('custom_orders.quote_pending_date') }}</p>
                  </div>
                  <span v-if="item.quote?.amount" class="badge badge-warning">{{ formatMoney(item.quote.amount) }}</span>
                </div>
                <p v-if="item.quote?.note" class="small">{{ item.quote.note }}</p>
                <p v-if="item.quote?.rejection_note" class="small">{{ t('custom_orders.rejection_note_label') }}: {{ item.quote.rejection_note }}</p>

                <div v-if="item.meta?.can_accept_quote || item.meta?.can_reject_quote" class="actions">
                  <button
                    class="btn btn-primary"
                    type="button"
                    :disabled="actionOrderId === item.id"
                    @click="acceptQuote(item)"
                  >
                    {{ actionOrderId === item.id ? t('common.submitting') : t('custom_orders.accept_quote_action') }}
                  </button>
                  <button
                    class="btn btn-danger"
                    type="button"
                    :disabled="actionOrderId === item.id"
                    @click="openRejectModal(item)"
                  >
                    {{ t('custom_orders.reject_quote_action') }}
                  </button>
                </div>
              </div>

              <OrderTimeline
                v-if="item.timeline?.length"
                :items="item.timeline"
                namespace="custom_orders"
              />
            </article>
          </div>

          <UiPagination v-if="orders.length > 0" :pagination="pagination" @page-change="loadOrders" />
        </div>

        <div class="ui-card stack">
          <strong>{{ t('custom_orders.process_title') }}</strong>
          <ul class="clean-list">
            <li>{{ t('custom_orders.process_step_1') }}</li>
            <li>{{ t('custom_orders.process_step_2') }}</li>
            <li>{{ t('custom_orders.process_step_3') }}</li>
            <li>{{ t('custom_orders.process_step_4') }}</li>
          </ul>
        </div>
      </div>
    </div>

    <UiModal
      v-model="rejectModalOpen"
      :title="t('custom_orders.reject_modal_title')"
      :description="t('custom_orders.reject_modal_description')"
    >
      <div class="stack">
        <UiFormField :label="t('custom_orders.rejection_note_label')" field-id="quote-rejection-note">
          <textarea id="quote-rejection-note" v-model="rejectNote" class="textarea" rows="4"></textarea>
        </UiFormField>

        <div class="actions">
          <button class="btn" type="button" @click="rejectModalOpen = false">{{ t('common.back') }}</button>
          <button class="btn btn-danger" type="button" :disabled="!rejectTarget || actionOrderId === rejectTarget.id" @click="confirmRejectQuote">
            {{ actionOrderId === rejectTarget?.id ? t('common.submitting') : t('custom_orders.confirm_reject_action') }}
          </button>
        </div>
      </div>
    </UiModal>
  </section>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import LoadingState from '@/components/common/LoadingState.vue';
import ErrorState from '@/components/common/ErrorState.vue';
import EmptyState from '@/components/common/EmptyState.vue';
import LocationPickerMap from '@/components/maps/LocationPickerMap.vue';
import CustomOrderStatusBadge from '@/components/orders/CustomOrderStatusBadge.vue';
import OrderTimeline from '@/components/orders/OrderTimeline.vue';
import UiFormField from '@/components/ui/UiFormField.vue';
import UiModal from '@/components/ui/UiModal.vue';
import UiPagination from '@/components/ui/UiPagination.vue';
import UiSectionHeader from '@/components/ui/UiSectionHeader.vue';
import {
  acceptCustomerCustomOrderQuote,
  createCustomerCustomOrder,
  fetchCustomOrderMetadata,
  fetchCustomerCustomOrders,
  rejectCustomerCustomOrderQuote,
} from '@/services/customerCustomOrderService';
import { getErrorMessage } from '@/services/errorMessage';
import { defaultPagination, normalizePagination } from '@/utils/pagination';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const { successToast, errorToast } = useToast();

const loading = ref(false);
const ordersLoading = ref(false);
const submitting = ref(false);
const locationLoading = ref(false);
const actionOrderId = ref(null);
const error = ref('');
const ordersError = ref('');
const locationError = ref('');
const validationErrors = ref({});
const orders = ref([]);
const scope = ref('active');
const pagination = reactive(defaultPagination({ perPage: 6, scope: 'active' }));
const metadata = reactive({
  specialties: [],
  wilayas: [],
  measurements: [],
});
const measurementInputs = reactive({});
const rejectModalOpen = ref(false);
const rejectTarget = ref(null);
const rejectNote = ref('');

const form = reactive({
  title: '',
  tailorSpecialty: '',
  fabricType: '',
  notes: '',
  latitude: 36.7538,
  longitude: 3.0588,
  workWilaya: '',
  locationLabel: '',
  referenceImages: [],
});

const specialties = computed(() => metadata.specialties || []);
const wilayas = computed(() => metadata.wilayas || []);
const measurements = computed(() => metadata.measurements || []);

function fieldError(field) {
  return validationErrors.value?.[field]?.[0] || '';
}

function queryScope() {
  return ['active', 'history'].includes(route.query.scope) ? route.query.scope : 'active';
}

function queryPage() {
  const page = Number(route.query.page || 1);
  return Number.isInteger(page) && page > 0 ? page : 1;
}

function syncQuery(page = pagination.currentPage) {
  router.replace({
    query: {
      ...route.query,
      scope: scope.value !== 'active' ? scope.value : undefined,
      page: page > 1 ? String(page) : undefined,
    },
  });
}

function resetMeasurementInputs() {
  Object.keys(measurementInputs).forEach((key) => {
    delete measurementInputs[key];
  });

  measurements.value.forEach((measurement) => {
    measurementInputs[measurement.slug] = '';
  });
}

function resetForm() {
  form.title = '';
  form.tailorSpecialty = '';
  form.fabricType = '';
  form.notes = '';
  form.latitude = 36.7538;
  form.longitude = 3.0588;
  form.workWilaya = '';
  form.locationLabel = '';
  form.referenceImages = [];
  validationErrors.value = {};
  locationError.value = '';
  resetMeasurementInputs();
}

function onImageChange(event) {
  form.referenceImages = Array.from(event.target.files || []);
}

function measurementPairs(order) {
  return Object.entries(order.measurements || {})
    .filter(([, value]) => value !== null && value !== '')
    .map(([key, value]) => ({
      key,
      value,
      label: measurements.value.find((measurement) => measurement.slug === key)?.name || key,
    }));
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString() : '-';
}

function formatMoney(value) {
  return `${new Intl.NumberFormat(undefined, { maximumFractionDigits: 2 }).format(Number(value || 0))} MAD`;
}

function applySuggestedWilaya(wilaya) {
  if (wilaya && !form.workWilaya) {
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

function readBrowserLocation() {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocation unavailable'));
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

async function detectCurrentLocation(showToast = false) {
  locationLoading.value = true;
  locationError.value = '';

  try {
    const position = await readBrowserLocation();
    form.latitude = Number(position.coords.latitude.toFixed(6));
    form.longitude = Number(position.coords.longitude.toFixed(6));
  } catch (err) {
    locationError.value = t('orders.location_capture_failed');

    if (showToast) {
      errorToast(locationError.value);
    }
  } finally {
    locationLoading.value = false;
  }
}

async function loadMetadata() {
  const response = await fetchCustomOrderMetadata();
  Object.assign(metadata, response.data || {});
  resetMeasurementInputs();
}

async function loadOrders(page = pagination.currentPage) {
  ordersLoading.value = true;
  ordersError.value = '';

  try {
    const response = await fetchCustomerCustomOrders({
      scope: scope.value,
      page,
      per_page: pagination.perPage,
    });

    orders.value = response.data || [];

    Object.assign(
      pagination,
      normalizePagination(response, {
        ...pagination,
        currentPage: page,
        scope: scope.value,
      }),
    );

    syncQuery(pagination.currentPage);
  } catch (err) {
    ordersError.value = getErrorMessage(err, t('messages.custom_orders_load_failed'));
  } finally {
    ordersLoading.value = false;
  }
}

function buildPayload() {
  const payload = new FormData();

  payload.append('title', form.title);
  payload.append('tailor_specialty', form.tailorSpecialty);
  payload.append('fabric_type', form.fabricType || '');
  payload.append('notes', form.notes || '');
  payload.append('customer_location[latitude]', String(form.latitude));
  payload.append('customer_location[longitude]', String(form.longitude));
  payload.append('customer_location[work_wilaya]', form.workWilaya);

  Object.entries(measurementInputs).forEach(([slug, value]) => {
    if (value !== '' && value !== null && value !== undefined) {
      payload.append(`measurements[${slug}]`, String(value));
    }
  });

  form.referenceImages.forEach((image) => {
    payload.append('reference_images[]', image);
  });

  return payload;
}

async function submit() {
  error.value = '';
  validationErrors.value = {};
  submitting.value = true;

  try {
    const response = await createCustomerCustomOrder(buildPayload());
    successToast(response.message || t('notifications.custom_order_created'));
    resetForm();
    scope.value = 'active';
    await loadOrders(1);
  } catch (err) {
    validationErrors.value = err?.errors || {};
    error.value = getErrorMessage(err, t('messages.custom_order_create_failed'));
    errorToast(error.value);
  } finally {
    submitting.value = false;
  }
}

function changeScope(nextScope) {
  scope.value = nextScope;
  loadOrders(1);
}

function openRejectModal(order) {
  rejectTarget.value = order;
  rejectNote.value = '';
  rejectModalOpen.value = true;
}

async function acceptQuote(order) {
  actionOrderId.value = order.id;

  try {
    const response = await acceptCustomerCustomOrderQuote(order.id);
    successToast(response.message || t('notifications.custom_order_quote_accepted'));
    await loadOrders(pagination.currentPage);
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.custom_order_accept_failed')));
  } finally {
    actionOrderId.value = null;
  }
}

async function confirmRejectQuote() {
  if (!rejectTarget.value) {
    return;
  }

  actionOrderId.value = rejectTarget.value.id;

  try {
    const response = await rejectCustomerCustomOrderQuote(rejectTarget.value.id, {
      note: rejectNote.value,
    });

    rejectModalOpen.value = false;
    rejectTarget.value = null;
    rejectNote.value = '';
    successToast(response.message || t('notifications.custom_order_quote_rejected'));
    await loadOrders(pagination.currentPage);
  } catch (err) {
    errorToast(getErrorMessage(err, t('messages.custom_order_reject_failed')));
  } finally {
    actionOrderId.value = null;
  }
}

onMounted(async () => {
  loading.value = true;
  scope.value = queryScope();

  try {
    await Promise.all([
      loadMetadata(),
      loadOrders(queryPage()),
    ]);

    await detectCurrentLocation(false);
  } catch (err) {
    error.value = getErrorMessage(err, t('messages.custom_orders_init_failed'));
  } finally {
    loading.value = false;
  }
});
</script>
