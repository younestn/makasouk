import axios from 'axios';

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

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response) {
      const responseData = error.response.data || {};
      const message = responseData.message || 'Request failed.';

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
        message: 'Unable to reach the server. Please check your connection.',
        data: null,
        errors: {},
      });
    }

    return Promise.reject({
      status: 0,
      message: error.message || 'Unexpected client error.',
      data: null,
      errors: {},
    });
  },
);
