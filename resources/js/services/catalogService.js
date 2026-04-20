import { apiClient } from '@/services/http';

export async function fetchCategories(params = {}) {
  const { data } = await apiClient.get('/catalog/categories', { params });
  return data;
}

export async function fetchProducts(params = {}) {
  const { data } = await apiClient.get('/catalog/products', { params });
  return data;
}

export async function fetchProduct(productId) {
  const { data } = await apiClient.get(`/catalog/products/${productId}`);
  return data;
}
