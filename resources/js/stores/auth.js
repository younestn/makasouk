import { defineStore } from 'pinia';
import { login as loginRequest, logout as logoutRequest, me as meRequest } from '@/services/authService';

const TOKEN_KEY = 'makasouk_access_token';

function readToken() {
  return localStorage.getItem(TOKEN_KEY);
}

function storeToken(token) {
  if (!token) {
    localStorage.removeItem(TOKEN_KEY);
    return;
  }

  localStorage.setItem(TOKEN_KEY, token);
}

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: readToken(),
    user: null,
    bootstrapComplete: false,
    isLoading: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
    isCustomer: (state) => state.user?.role === 'customer',
    isTailor: (state) => state.user?.role === 'tailor',
  },

  actions: {
    clearAuth() {
      this.token = null;
      this.user = null;
      storeToken(null);
    },

    setAuth(token, user) {
      this.token = token;
      this.user = user;
      storeToken(token);
    },

    async bootstrap() {
      if (this.bootstrapComplete) {
        return;
      }

      if (!this.token) {
        this.bootstrapComplete = true;
        return;
      }

      this.isLoading = true;

      try {
        const response = await meRequest();
        this.user = response.data;
      } catch {
        this.clearAuth();
      } finally {
        this.isLoading = false;
        this.bootstrapComplete = true;
      }
    },

    async login(credentials) {
      this.isLoading = true;

      try {
        const response = await loginRequest(credentials);
        this.setAuth(response.token, response.data);
        this.bootstrapComplete = true;
        return response.data;
      } finally {
        this.isLoading = false;
      }
    },

    async refreshUser() {
      if (!this.token) {
        return null;
      }

      const response = await meRequest();
      this.user = response.data;
      return this.user;
    },

    async logout() {
      try {
        if (this.token) {
          await logoutRequest();
        }
      } finally {
        this.clearAuth();
        this.bootstrapComplete = true;
      }
    },
  },
});
