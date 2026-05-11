import { apiClient } from '@/services/http';

export async function createOrder(payload) {
  const { data } = await apiClient.post('/customer/orders', payload);
  return data;
}

export async function fetchOrderMetadata() {
  const { data } = await apiClient.get('/customer/orders-metadata');
  return data;
}

export async function fetchActiveOrders(params = {}) {
  const { data } = await apiClient.get('/customer/orders-active', { params });
  return data;
}

export async function fetchPurchasedOrders(params = {}) {
  const { data } = await apiClient.get('/customer/orders-purchased', { params });
  return data;
}

export async function fetchOrderHistory(params = {}) {
  const { data } = await apiClient.get('/customer/orders-history', { params });
  return data;
}

export async function fetchOrder(orderId) {
  const { data } = await apiClient.get(`/customer/orders/${orderId}`);
  return data;
}

export async function cancelOrder(orderId, reason) {
  const { data } = await apiClient.patch(`/customer/orders/${orderId}/cancel`, { reason });
  return data;
}

export async function submitReview(orderId, payload) {
  const { data } = await apiClient.post(`/customer/orders/${orderId}/reviews`, payload);
  return data;
}

export async function fetchCustomerReviews(params = {}) {
  const { data } = await apiClient.get('/customer/reviews', { params });
  return data;
}
