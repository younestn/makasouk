import { apiClient } from '@/services/http';

export async function login(payload) {
  const { data } = await apiClient.post('/auth/login', payload);
  return data;
}

export async function register(payload) {
  const isFormData = typeof FormData !== 'undefined' && payload instanceof FormData;
  const config = isFormData
    ? { headers: { 'Content-Type': 'multipart/form-data' } }
    : undefined;

  const { data } = await apiClient.post('/auth/register', payload, config);
  return data;
}

export async function me() {
  const { data } = await apiClient.get('/auth/me');
  return data;
}

export async function fetchTailorRegistrationMetadata() {
  const { data } = await apiClient.get('/auth/tailor-registration-metadata');
  return data;
}

export async function sendPhoneVerificationCode() {
  const { data } = await apiClient.post('/auth/phone-verification/send');
  return data;
}

export async function verifyPhoneCode(payload) {
  const { data } = await apiClient.post('/auth/phone-verification/verify', payload);
  return data;
}

export async function logout(allDevices = false) {
  const { data } = await apiClient.post('/auth/logout', { all_devices: allDevices });
  return data;
}
