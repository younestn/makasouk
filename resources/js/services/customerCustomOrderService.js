import { apiClient } from '@/services/http';

export async function fetchCustomOrderMetadata() {
  const { data } = await apiClient.get('/customer/custom-orders-metadata');
  return data;
}

export async function fetchCustomerCustomOrders(params = {}) {
  const { data } = await apiClient.get('/customer/custom-orders', { params });
  return data;
}

export async function fetchCustomerCustomOrder(customOrderId) {
  const { data } = await apiClient.get(`/customer/custom-orders/${customOrderId}`);
  return data;
}

export async function createCustomerCustomOrder(payload) {
  const config = payload instanceof FormData
    ? { headers: { 'Content-Type': 'multipart/form-data' } }
    : undefined;

  const { data } = await apiClient.post('/customer/custom-orders', payload, config);
  return data;
}

export async function acceptCustomerCustomOrderQuote(customOrderId) {
  const { data } = await apiClient.post(`/customer/custom-orders/${customOrderId}/accept-quote`);
  return data;
}

export async function rejectCustomerCustomOrderQuote(customOrderId, payload) {
  const { data } = await apiClient.post(`/customer/custom-orders/${customOrderId}/reject-quote`, payload);
  return data;
}
