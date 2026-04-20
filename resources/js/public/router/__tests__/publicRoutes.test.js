import { describe, expect, it } from 'vitest';
import { publicRouter } from '@/public/router';

describe('public router', () => {
  it('registers expected public routes', () => {
    const names = publicRouter.getRoutes().map((route) => route.name).filter(Boolean);

    expect(names).toContain('publicHome');
    expect(names).toContain('publicHowItWorks');
    expect(names).toContain('publicForCustomers');
    expect(names).toContain('publicForTailors');
    expect(names).toContain('publicFaq');
    expect(names).toContain('publicContact');
  });
});
