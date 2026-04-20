<template>
  <section class="container page" style="max-width: 520px;">
    <div class="card stack">
      <h1 class="title">Makasouk Web Client</h1>
      <p class="subtitle">Login for customer/tailor shell validation.</p>

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

      <div class="small">
        Admin users are redirected to <code>/admin-panel</code>.
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
