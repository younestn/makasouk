import { apiClient } from '@/services/http';

export async function fetchCustomerProfile() {
  const { data } = await apiClient.get('/customer/profile');
  return data;
}

export async function updateCustomerProfile(payload) {
  const config = payload instanceof FormData
    ? { headers: { 'Content-Type': 'multipart/form-data' } }
    : undefined;

  const { data } = await apiClient.post('/customer/profile', payload, config);
  return data;
}

export async function updateCustomerPassword(payload) {
  const { data } = await apiClient.patch('/customer/profile/password', payload);
  return data;
}
