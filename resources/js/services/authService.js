import { apiClient } from '@/services/http';

export async function login(payload) {
  const { data } = await apiClient.post('/auth/login', payload);
  return data;
}

export async function me() {
  const { data } = await apiClient.get('/auth/me');
  return data;
}

export async function logout(allDevices = false) {
  const { data } = await apiClient.post('/auth/logout', { all_devices: allDevices });
  return data;
}
