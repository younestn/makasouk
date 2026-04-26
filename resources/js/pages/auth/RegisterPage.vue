<template>
  <AuthShell
    :tone="tone"
    :badge="t('auth.register_badge')"
    :brand-title="brandTitle"
    :brand-subtitle="brandSubtitle"
    :points="brandPoints"
    :trust-note="t('auth.trust_note')"
    :form-title="t('auth.create_account_title')"
    :form-subtitle="t('auth.create_account_subtitle')"
  >
    <div class="mk-auth-switch">
      <button
        type="button"
        :class="{ 'mk-active': tone === 'customer' }"
        @click="switchTone('customer')"
      >
        {{ t('auth.customer_label') }}
      </button>
      <button
        type="button"
        :class="{ 'mk-active': tone === 'tailor' }"
        @click="switchTone('tailor')"
      >
        {{ t('auth.tailor_label') }}
      </button>
    </div>

    <div v-if="error" class="alert alert-danger" style="margin-top: 0.9rem;">{{ error }}</div>

    <form class="stack" style="margin-top: 0.9rem;" @submit.prevent="submit">
      <div>
        <label class="label" for="register-name">{{ t('auth.full_name') }}</label>
        <input id="register-name" v-model="form.name" type="text" class="input" required autocomplete="name" />
        <p v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</p>
      </div>

      <div>
        <label class="label" for="register-email">{{ t('auth.email') }}</label>
        <input id="register-email" v-model="form.email" type="email" class="input" required autocomplete="email" />
        <p v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</p>
      </div>

      <div>
        <label class="label" for="register-password">{{ t('auth.password') }}</label>
        <div style="position: relative;">
          <input
            id="register-password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            class="input"
            required
            autocomplete="new-password"
            style="padding-right: 3rem;"
          />
          <button
            type="button"
            class="btn"
            :aria-label="showPassword ? t('auth.hide_password') : t('auth.show_password')"
            style="position: absolute; top: 50%; right: 0.4rem; transform: translateY(-50%); min-height: 2rem; padding: 0.25rem 0.55rem;"
            @click="showPassword = !showPassword"
          >
            {{ showPassword ? t('auth.hide_short') : t('auth.show_short') }}
          </button>
        </div>
        <p v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</p>
      </div>

      <div>
        <label class="label" for="register-password-confirmation">{{ t('auth.confirm_password') }}</label>
        <input
          id="register-password-confirmation"
          v-model="form.password_confirmation"
          :type="showPassword ? 'text' : 'password'"
          class="input"
          required
          autocomplete="new-password"
        />
        <p v-if="fieldError('password_confirmation')" class="field-error">{{ fieldError('password_confirmation') }}</p>
      </div>

      <template v-if="tone === 'tailor'">
        <div class="alert alert-info">
          {{ t('auth.tailor_registration_details_hint') }}
        </div>

        <div>
          <label class="label" for="register-phone">{{ t('auth.phone_number') }}</label>
          <input
            id="register-phone"
            v-model="form.phone"
            type="tel"
            class="input"
            :placeholder="t('auth.phone_number_placeholder')"
            required
            autocomplete="tel"
          />
          <p class="small">{{ t('auth.phone_number_helper') }}</p>
          <p v-if="fieldError('phone')" class="field-error">{{ fieldError('phone') }}</p>
        </div>

        <div>
          <label class="label" for="register-specialization">{{ t('auth.tailor_specialization') }}</label>
          <select id="register-specialization" v-model="form.specialization" class="select" required>
            <option value="">{{ t('auth.select_specialization') }}</option>
            <option v-for="specialization in specializationOptions" :key="specialization" :value="specialization">
              {{ specialization }}
            </option>
          </select>
          <p v-if="fieldError('specialization')" class="field-error">{{ fieldError('specialization') }}</p>
        </div>

        <div>
          <label class="label" for="register-work-wilaya">{{ t('auth.work_wilaya') }}</label>
          <select id="register-work-wilaya" v-model="form.work_wilaya" class="select" required>
            <option value="">{{ t('auth.select_wilaya') }}</option>
            <option v-for="wilaya in wilayaOptions" :key="wilaya" :value="wilaya">
              {{ wilaya }}
            </option>
          </select>
          <p v-if="fieldError('work_wilaya')" class="field-error">{{ fieldError('work_wilaya') }}</p>
        </div>

        <div class="grid grid-2">
          <div>
            <label class="label" for="register-years">{{ t('auth.years_of_experience') }}</label>
            <input
              id="register-years"
              v-model.number="form.years_of_experience"
              type="number"
              class="input"
              min="0"
              max="80"
              required
            />
            <p v-if="fieldError('years_of_experience')" class="field-error">{{ fieldError('years_of_experience') }}</p>
          </div>

          <div>
            <label class="label" for="register-workers">{{ t('auth.workers_count') }}</label>
            <input
              id="register-workers"
              v-model.number="form.workers_count"
              type="number"
              class="input"
              min="1"
              max="1000"
              required
            />
            <p v-if="fieldError('workers_count')" class="field-error">{{ fieldError('workers_count') }}</p>
          </div>
        </div>

        <div>
          <label class="label" for="register-gender">{{ t('auth.gender') }}</label>
          <select id="register-gender" v-model="form.gender" class="select" required>
            <option value="">{{ t('auth.select_gender') }}</option>
            <option v-for="(label, key) in genderOptions" :key="key" :value="key">
              {{ label }}
            </option>
          </select>
          <p v-if="fieldError('gender')" class="field-error">{{ fieldError('gender') }}</p>
        </div>

        <div>
          <label class="label" for="register-commercial-register">{{ t('auth.commercial_register_file') }}</label>
          <input
            id="register-commercial-register"
            ref="commercialRegisterInput"
            type="file"
            class="input"
            accept=".jpg,.jpeg,.png,.webp,.pdf"
            @change="handleCommercialRegisterChange"
          />
          <p class="small">{{ t('auth.commercial_register_helper') }}</p>
          <p v-if="fieldError('commercial_register_file')" class="field-error">{{ fieldError('commercial_register_file') }}</p>
        </div>
      </template>

      <button class="btn btn-primary" type="submit" :disabled="loading">
        {{ loading ? t('auth.creating_account') : t('auth.create_account_button') }}
      </button>
    </form>

    <div class="row" style="justify-content: space-between; margin-top: 0.65rem;">
      <p class="small" style="margin: 0;">{{ registerHint }}</p>
      <RouterLink class="small" :to="{ name: 'login', query: { role: tone } }">
        {{ t('auth.have_account') }}
      </RouterLink>
    </div>
  </AuthShell>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import AuthShell from '@/components/auth/AuthShell.vue';
import { useAuthStore } from '@/stores/auth';
import { getErrorMessage } from '@/services/errorMessage';
import { homeRouteForRole } from '@/utils/roleRoutes';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const DEFAULT_SPECIALIZATIONS = [
  'Traditionnel',
  'Haute Couture / Soirée',
  'Classique',
  'Moderne',
  'Regular sewing',
];

const DEFAULT_WILAYAS = [
  'Adrar',
  'Chlef',
  'Laghouat',
  'Oum El Bouaghi',
  'Batna',
  'Bejaia',
  'Biskra',
  'Bechar',
  'Blida',
  'Bouira',
  'Tamanrasset',
  'Tebessa',
  'Tlemcen',
  'Tiaret',
  'Tizi Ouzou',
  'Algiers',
  'Djelfa',
  'Jijel',
  'Setif',
  'Saida',
  'Skikda',
  'Sidi Bel Abbes',
  'Annaba',
  'Guelma',
  'Constantine',
  'Medea',
  'Mostaganem',
  "M'Sila",
  'Mascara',
  'Ouargla',
  'Oran',
  'El Bayadh',
  'Illizi',
  'Bordj Bou Arreridj',
  'Boumerdes',
  'El Tarf',
  'Tindouf',
  'Tissemsilt',
  'El Oued',
  'Khenchela',
  'Souk Ahras',
  'Tipaza',
  'Mila',
  'Ain Defla',
  'Naama',
  'Ain Temouchent',
  'Ghardaia',
  'Relizane',
  'Timimoun',
  'Bordj Badji Mokhtar',
  'Ouled Djellal',
  'Beni Abbes',
  'In Salah',
  'In Guezzam',
  'Touggourt',
  'Djanet',
  "El M'Ghair",
  'El Meniaa',
];

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const { successToast, errorToast, infoToast } = useToast();

const loading = ref(false);
const error = ref('');
const validationErrors = ref({});
const showPassword = ref(false);
const tone = ref(resolveTone(route));
const commercialRegisterFile = ref(null);
const commercialRegisterInput = ref(null);
const onboardingMetadata = ref({
  specializations: DEFAULT_SPECIALIZATIONS,
  genders: {
    female: t('tailors.gender_female'),
    male: t('tailors.gender_male'),
  },
  wilayas: DEFAULT_WILAYAS,
});

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: tone.value,
  device_name: 'web-client',
  phone: '',
  specialization: '',
  work_wilaya: '',
  years_of_experience: null,
  gender: '',
  workers_count: null,
});

const toneMeta = computed(() => ({
  customer: {
    title: t('auth.customer_register_title'),
    subtitle: t('auth.customer_register_subtitle'),
    points: [
      t('auth.customer_point_one'),
      t('auth.customer_point_two'),
      t('auth.customer_point_three'),
    ],
  },
  tailor: {
    title: t('auth.tailor_register_title'),
    subtitle: t('auth.tailor_register_subtitle'),
    points: [
      t('auth.tailor_point_one'),
      t('auth.tailor_point_two'),
      t('auth.tailor_point_three'),
    ],
  },
}[tone.value]));

const brandTitle = computed(() => toneMeta.value.title);
const brandSubtitle = computed(() => toneMeta.value.subtitle);
const brandPoints = computed(() => toneMeta.value.points);
const registerHint = computed(() => tone.value === 'tailor' ? t('auth.tailor_register_hint') : t('auth.customer_register_hint'));
const specializationOptions = computed(() => onboardingMetadata.value.specializations || DEFAULT_SPECIALIZATIONS);
const wilayaOptions = computed(() => onboardingMetadata.value.wilayas || DEFAULT_WILAYAS);
const genderOptions = computed(() => onboardingMetadata.value.genders || {});

watch(
  () => route.query.role,
  () => {
    tone.value = resolveTone(route);
    form.role = tone.value;
  },
);

onMounted(async () => {
  try {
    const metadata = await authStore.loadTailorRegistrationMetadata();
    onboardingMetadata.value = {
      specializations: metadata?.specializations || DEFAULT_SPECIALIZATIONS,
      genders: metadata?.genders || { female: t('tailors.gender_female'), male: t('tailors.gender_male') },
      wilayas: metadata?.wilayas || DEFAULT_WILAYAS,
    };
  } catch {
    onboardingMetadata.value = {
      specializations: DEFAULT_SPECIALIZATIONS,
      genders: { female: t('tailors.gender_female'), male: t('tailors.gender_male') },
      wilayas: DEFAULT_WILAYAS,
    };
  }
});

function resolveTone(currentRoute) {
  const role = String(currentRoute.query.role || '').toLowerCase();
  if (role === 'tailor') {
    return 'tailor';
  }
  return 'customer';
}

function switchTone(role) {
  tone.value = role;
  form.role = role;
  router.replace({ name: route.name || 'register', query: { ...route.query, role } });
}

function fieldError(field) {
  if (!validationErrors.value || !validationErrors.value[field]) {
    return '';
  }

  return String(validationErrors.value[field][0] || '');
}

function handleCommercialRegisterChange(event) {
  const file = event?.target?.files?.[0];
  commercialRegisterFile.value = file || null;
}

function buildPayload() {
  if (tone.value !== 'tailor') {
    return {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation,
      role: form.role,
      device_name: form.device_name,
    };
  }

  const payload = new FormData();
  payload.append('name', form.name);
  payload.append('email', form.email);
  payload.append('password', form.password);
  payload.append('password_confirmation', form.password_confirmation);
  payload.append('role', form.role);
  payload.append('device_name', form.device_name);
  payload.append('phone', form.phone || '');
  payload.append('specialization', form.specialization || '');
  payload.append('work_wilaya', form.work_wilaya || '');
  payload.append('years_of_experience', String(form.years_of_experience ?? ''));
  payload.append('gender', form.gender || '');
  payload.append('workers_count', String(form.workers_count ?? ''));

  if (commercialRegisterFile.value) {
    payload.append('commercial_register_file', commercialRegisterFile.value);
  }

  return payload;
}

async function submit() {
  loading.value = true;
  error.value = '';
  validationErrors.value = {};

  try {
    const user = await authStore.register(buildPayload());
    successToast(t('auth.account_created_success'));

    if (user.role === 'admin') {
      infoToast(t('notifications.admin_redirect'));
      window.location.assign('/admin-panel');
      return;
    }

    if (authStore.needsTailorPhoneVerification) {
      infoToast(t('auth.phone_verification_required_notice'));
      router.push({ name: 'verifyPhone' });
      return;
    }

    router.push(homeRouteForRole(user.role));
  } catch (err) {
    validationErrors.value = err?.errors || {};
    error.value = getErrorMessage(err, t('auth.register_failed'));
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
}
</script>
