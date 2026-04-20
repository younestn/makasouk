import { beforeEach, describe, expect, it, vi } from 'vitest';
import { createPinia, setActivePinia } from 'pinia';
import { useToastStore } from '@/stores/toast';

describe('toast store', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.useRealTimers();
  });

  it('adds and removes toast entries', () => {
    const store = useToastStore();

    const id = store.success('Saved successfully');

    expect(store.items).toHaveLength(1);
    expect(store.items[0].variant).toBe('success');
    expect(id).toBeTypeOf('number');

    store.remove(id);
    expect(store.items).toHaveLength(0);
  });

  it('auto dismisses non-persistent toasts', () => {
    vi.useFakeTimers();

    const store = useToastStore();
    store.info('Auto close', { duration: 1200 });

    expect(store.items).toHaveLength(1);

    vi.advanceTimersByTime(1300);
    expect(store.items).toHaveLength(0);
  });
});
