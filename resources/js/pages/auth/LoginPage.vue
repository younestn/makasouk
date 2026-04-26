<template>
  <AuthShell
    :tone="tone"
    :badge="t('auth.access_badge')"
    :brand-title="brandTitle"
    :brand-subtitle="brandSubtitle"
    :points="brandPoints"
    :trust-note="t('auth.trust_note')"
    :form-title="t('auth.sign_in_title')"
    :form-subtitle="t('auth.sign_in_subtitle')"
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
        <label class="label" for="email">{{ t('auth.email') }}</label>
        <input id="email" v-model="form.email" type="email" class="input" required autocomplete="email" />
        <p v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</p>
      </div>

      <div>
        <label class="label" for="password">{{ t('auth.password') }}</label>
        <div style="position: relative;">
          <input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            class="input"
            required
            autocomplete="current-password"
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

      <button class="btn btn-primary" type="submit" :disabled="loading">
        {{ loading ? t('auth.signing_in') : t('auth.sign_in_button') }}
      </button>
    </form>

    <div class="row" style="justify-content: space-between; margin-top: 0.65rem;">
      <p class="small" style="margin: 0;">{{ seedHint }}</p>
      <RouterLink class="small" :to="{ name: 'register', query: { role: tone } }">
        {{ t('auth.no_account') }}
      </RouterLink>
    </div>
  </AuthShell>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import AuthShell from '@/components/auth/AuthShell.vue';
import { useAuthStore } from '@/stores/auth';
import { getErrorMessage } from '@/services/errorMessage';
import { homeRouteForRole } from '@/utils/roleRoutes';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

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

const form = reactive({
  email: '',
  password: '',
  device_name: 'web-client',
});

const toneMeta = computed(() => ({
  customer: {
    title: t('auth.customer_brand_title'),
    subtitle: t('auth.customer_brand_subtitle'),
    points: [
      t('auth.customer_point_one'),
      t('auth.customer_point_two'),
      t('auth.customer_point_three'),
    ],
    seed: 'customer@makasouk.local / Password@123',
  },
  tailor: {
    title: t('auth.tailor_brand_title'),
    subtitle: t('auth.tailor_brand_subtitle'),
    points: [
      t('auth.tailor_point_one'),
      t('auth.tailor_point_two'),
      t('auth.tailor_point_three'),
    ],
    seed: 'tailor@makasouk.local / Password@123',
  },
}[tone.value]));

const brandTitle = computed(() => toneMeta.value.title);
const brandSubtitle = computed(() => toneMeta.value.subtitle);
const brandPoints = computed(() => toneMeta.value.points);
const seedHint = computed(() => `${t('auth.sample_credentials')}: ${toneMeta.value.seed}`);

watch(
  () => route.query.role,
  () => {
    tone.value = resolveTone(route);
  },
);

function resolveTone(currentRoute) {
  const role = String(currentRoute.query.role || '').toLowerCase();
  if (role === 'tailor') {
    return 'tailor';
  }
  return 'customer';
}

function switchTone(role) {
  tone.value = role;
  router.replace({ name: route.name || 'login', query: { ...route.query, role } });
}

function fieldError(field) {
  if (!validationErrors.value || !validationErrors.value[field]) {
    return '';
  }

  return String(validationErrors.value[field][0] || '');
}

async function submit() {
  loading.value = true;
  error.value = '';
  validationErrors.value = {};

  try {
    const user = await authStore.login(form);
    successToast(t('notifications.login_success'));

    if (user.role === 'admin') {
      infoToast(t('notifications.admin_redirect'));
      window.location.assign('/admin-panel');
      return;
    }

    if (route.query.redirect) {
      router.push(String(route.query.redirect));
      return;
    }

    router.push(homeRouteForRole(user.role));
  } catch (err) {
    validationErrors.value = err?.errors || {};
    error.value = getErrorMessage(err, t('auth.invalid_credentials'));
    errorToast(error.value);
  } finally {
    loading.value = false;
  }
}
</script>
