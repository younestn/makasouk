import axios from 'axios';
import { readStoredLocale, translate } from '@/i18n';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '/api';
const TOKEN_KEY = 'makasouk_access_token';

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 20000,
});

apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY);
  const locale = readStoredLocale();

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  config.headers['X-Locale'] = locale;
  config.headers['Accept-Language'] = locale;

  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const locale = readStoredLocale();

    if (error.response) {
      const responseData = error.response.data || {};
      const message = responseData.message || translate(locale, 'messages.request_failed');

      return Promise.reject({
        status: error.response.status,
        message,
        data: responseData,
        errors: responseData.errors || {},
      });
    }

    if (error.request) {
      return Promise.reject({
        status: 0,
        message: translate(locale, 'messages.server_unreachable'),
        data: null,
        errors: {},
      });
    }

    return Promise.reject({
      status: 0,
      message: error.message || translate(locale, 'messages.unexpected_client_error'),
      data: null,
      errors: {},
    });
  },
);
