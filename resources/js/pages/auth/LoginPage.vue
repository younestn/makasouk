<template>
  <section class="container page" style="max-width: 960px;">
    <div class="grid grid-2" style="align-items: stretch;">
      <div class="ui-card stack" style="justify-content: center;">
        <span class="badge badge-info">{{ t('auth.access_badge') }}</span>
        <h1 class="title" style="font-size: 1.65rem;">{{ t('auth.welcome_title') }}</h1>
        <p class="subtitle">{{ t('auth.welcome_subtitle') }}</p>

        <div class="stack" style="gap: 0.55rem;">
          <p class="small"><strong>{{ t('auth.customer_seed') }}:</strong> customer@makasouk.local</p>
          <p class="small"><strong>{{ t('auth.tailor_seed') }}:</strong> tailor@makasouk.local</p>
          <p class="small"><strong>{{ t('auth.admin_seed') }}:</strong> admin@makasouk.local ({{ t('auth.admin_redirect') }})</p>
          <p class="small"><strong>Password:</strong> Password@123 (admin: Admin@12345)</p>
        </div>

        <div class="actions">
          <a class="btn" href="/">{{ t('auth.public_website') }}</a>
        </div>
      </div>

      <div class="ui-card stack">
        <h2 class="title" style="font-size: 1.2rem;">{{ t('auth.sign_in_title') }}</h2>

        <div v-if="error" class="alert alert-danger">{{ error }}</div>

        <form class="stack" @submit.prevent="submit">
          <div>
            <label class="label" for="email">{{ t('auth.email') }}</label>
            <input id="email" v-model="form.email" type="email" class="input" required autocomplete="email" />
            <p v-if="fieldError('email')" class="field-error">{{ fieldError('email') }}</p>
          </div>

          <div>
            <label class="label" for="password">{{ t('auth.password') }}</label>
            <input id="password" v-model="form.password" type="password" class="input" required autocomplete="current-password" />
            <p v-if="fieldError('password')" class="field-error">{{ fieldError('password') }}</p>
          </div>

          <button class="btn btn-primary" type="submit" :disabled="loading">
            {{ loading ? t('auth.signing_in') : t('auth.sign_in_button') }}
          </button>
        </form>
      </div>
    </div>
  </section>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { getErrorMessage } from '@/services/errorMessage';
import { homeRouteForRole } from '@/utils/roleRoutes';
import { useI18n } from '@/composables/useI18n';
import { useToast } from '@/composables/useToast';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { t } = useI18n();
const { successToast, errorToast } = useToast();

const loading = ref(false);
const error = ref('');
const validationErrors = ref({});

const form = reactive({
  email: '',
  password: '',
  device_name: 'web-client',
});

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
