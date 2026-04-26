import { defineStore } from 'pinia';
import {
  fetchTailorRegistrationMetadata,
  login as loginRequest,
  logout as logoutRequest,
  me as meRequest,
  register as registerRequest,
  sendPhoneVerificationCode as sendPhoneVerificationCodeRequest,
  verifyPhoneCode as verifyPhoneCodeRequest,
} from '@/services/authService';

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
    authMeta: {},
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token && state.user),
    isCustomer: (state) => state.user?.role === 'customer',
    isTailor: (state) => state.user?.role === 'tailor',
    needsTailorPhoneVerification: (state) => (
      state.user?.role === 'tailor'
      && Boolean(state.user?.phone)
      && !state.user?.phone_is_verified
    ),
    needsTailorEmailVerification: (state) => (
      state.user?.role === 'tailor'
      && Boolean(state.authMeta?.requires_email_verification)
      && !state.user?.email_is_verified
    ),
  },

  actions: {
    clearAuth() {
      this.token = null;
      this.user = null;
      this.authMeta = {};
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
        this.authMeta = response.meta || {};
        this.bootstrapComplete = true;
        return response.data;
      } finally {
        this.isLoading = false;
      }
    },

    async register(payload) {
      this.isLoading = true;

      try {
        const response = await registerRequest(payload);
        this.setAuth(response.token, response.data);
        this.authMeta = response.meta || {};
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

    async loadTailorRegistrationMetadata() {
      const response = await fetchTailorRegistrationMetadata();
      return response.data || {};
    },

    async sendPhoneVerificationCode() {
      const response = await sendPhoneVerificationCodeRequest();
      if (response?.data) {
        this.user = response.data;
      }
      this.authMeta = response?.meta || {};
      return response;
    },

    async verifyPhoneCode(payload) {
      const response = await verifyPhoneCodeRequest(payload);
      if (response?.data) {
        this.user = response.data;
      }
      this.authMeta = response?.meta || {};
      return response;
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
