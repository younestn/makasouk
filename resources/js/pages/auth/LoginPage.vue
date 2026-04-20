<template>
  <section class="container page" style="max-width: 960px;">
    <div class="grid grid-2" style="align-items: stretch;">
      <div class="ui-card stack" style="justify-content: center;">
        <span class="badge badge-info">Web Client Access</span>
        <h1 class="title" style="font-size: 1.65rem;">Welcome to Makasouk App</h1>
        <p class="subtitle">
          Sign in as customer or tailor to validate end-to-end flows. Admin accounts are redirected to Filament.
        </p>

        <div class="stack" style="gap: 0.55rem;">
          <p class="small"><strong>Customer:</strong> customer@makasouk.local</p>
          <p class="small"><strong>Tailor:</strong> tailor@makasouk.local</p>
          <p class="small"><strong>Admin:</strong> admin@makasouk.local (redirected to /admin-panel)</p>
        </div>

        <div class="actions">
          <a class="btn" href="/">Public Website</a>
        </div>
      </div>

      <div class="ui-card stack">
        <h2 class="title" style="font-size: 1.2rem;">Sign In</h2>

        <div v-if="error" class="alert alert-danger">{{ error }}</div>

        <form class="stack" @submit.prevent="submit">
          <div>
            <label class="label" for="email">Email</label>
            <input id="email" v-model="form.email" type="email" class="input" required autocomplete="email" />
          </div>

          <div>
            <label class="label" for="password">Password</label>
            <input id="password" v-model="form.password" type="password" class="input" required autocomplete="current-password" />
          </div>

          <button class="btn btn-primary" type="submit" :disabled="loading">
            {{ loading ? 'Signing in...' : 'Sign in' }}
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

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const loading = ref(false);
const error = ref('');

const form = reactive({
  email: '',
  password: '',
  device_name: 'web-client',
});

async function submit() {
  loading.value = true;
  error.value = '';

  try {
    const user = await authStore.login(form);

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
    error.value = getErrorMessage(err, 'Unable to sign in.');
  } finally {
    loading.value = false;
  }
}
</script>
