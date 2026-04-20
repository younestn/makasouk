import { describe, it, expect } from 'vitest';
import { statusMeta } from '@/utils/orderStatus';

describe('order status meta', () => {
  it('maps known status to label/class', () => {
    const meta = statusMeta('completed');

    expect(meta.label).toBe('Completed');
    expect(meta.className).toBe('badge-success');
  });

  it('returns fallback for unknown statuses', () => {
    const meta = statusMeta('unknown_status');

    expect(meta.label).toBe('unknown_status');
    expect(meta.className).toBe('badge-neutral');
  });
});
