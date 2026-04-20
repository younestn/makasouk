import { describe, expect, it } from 'vitest';
import { defaultPagination, normalizePagination } from '@/utils/pagination';

describe('pagination helpers', () => {
  it('returns defaults', () => {
    const defaults = defaultPagination({ perPage: 12 });

    expect(defaults.currentPage).toBe(1);
    expect(defaults.perPage).toBe(12);
  });

  it('normalizes response meta fields', () => {
    const normalized = normalizePagination({
      meta: {
        current_page: 3,
        last_page: 9,
        per_page: 20,
        total: 177,
        from: 41,
        to: 60,
      },
    });

    expect(normalized.currentPage).toBe(3);
    expect(normalized.lastPage).toBe(9);
    expect(normalized.total).toBe(177);
    expect(normalized.from).toBe(41);
    expect(normalized.to).toBe(60);
  });
});
