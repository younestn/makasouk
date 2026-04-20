import { beforeEach, describe, expect, it, vi } from 'vitest';
import { apiClient } from '@/services/http';
import { fetchOrderHistory } from '@/services/customerOrderService';
import { fetchTailorOrder, updateOrderStatus } from '@/services/tailorService';

describe('order services', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('fetches customer history with query params', async () => {
    const getSpy = vi.spyOn(apiClient, 'get').mockResolvedValue({
      data: { data: [], meta: { current_page: 2 } },
    });

    const response = await fetchOrderHistory({ page: 2, per_page: 10 });

    expect(getSpy).toHaveBeenCalledWith('/customer/orders-history', {
      params: { page: 2, per_page: 10 },
    });

    expect(response.meta.current_page).toBe(2);
  });

  it('fetches tailor order details by id', async () => {
    const getSpy = vi.spyOn(apiClient, 'get').mockResolvedValue({
      data: { data: { id: 15 } },
    });

    const response = await fetchTailorOrder(15);

    expect(getSpy).toHaveBeenCalledWith('/tailor/orders/15');
    expect(response.data.id).toBe(15);
  });

  it('updates tailor order status', async () => {
    const patchSpy = vi.spyOn(apiClient, 'patch').mockResolvedValue({
      data: { message: 'updated' },
    });

    const response = await updateOrderStatus(22, 'processing');

    expect(patchSpy).toHaveBeenCalledWith('/tailor/orders/22/status', {
      status: 'processing',
    });

    expect(response.message).toBe('updated');
  });
});
