import { apiClient } from '@/services/http';

export async function fetchTailorActiveOrders(params = {}) {
  const { data } = await apiClient.get('/tailor/orders-active', { params });
  return data;
}

export async function fetchTailorOrderHistory(params = {}) {
  const { data } = await apiClient.get('/tailor/orders-history', { params });
  return data;
}

export async function fetchTailorOrder(orderId) {
  const { data } = await apiClient.get(`/tailor/orders/${orderId}`);
  return data;
}

export async function acceptOrder(orderId, notifiedTailorIds = []) {
  const payload = {};

  if (Array.isArray(notifiedTailorIds) && notifiedTailorIds.length > 0) {
    payload.notified_tailor_ids = notifiedTailorIds;
  }

  const { data } = await apiClient.post(`/tailor/orders/${orderId}/accept`, payload);
  return data;
}

export async function updateOrderStatus(orderId, status) {
  const { data } = await apiClient.patch(`/tailor/orders/${orderId}/status`, { status });
  return data;
}

export async function cancelTailorOrder(orderId, reason) {
  const { data } = await apiClient.patch(`/tailor/orders/${orderId}/cancel`, { reason });
  return data;
}

export async function fetchTailorProfile() {
  const { data } = await apiClient.get('/tailor/profile');
  return data;
}

export async function fetchAvailability() {
  const { data } = await apiClient.get('/tailor/availability');
  return data;
}

export async function toggleAvailability() {
  const { data } = await apiClient.patch('/tailor/availability/toggle');
  return data;
}
