import { apiClient } from '@/services/http';

export async function fetchMapConfig() {
  const { data } = await apiClient.get('/map/config');
  return data;
}
