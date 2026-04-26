import { describe, expect, it } from 'vitest';
import { router } from '@/router';

describe('spa routes', () => {
  it('registers core customer and tailor routes', () => {
    const routeNames = router.getRoutes().map((route) => route.name).filter(Boolean);

    expect(routeNames).toContain('login');
    expect(routeNames).toContain('register');
    expect(routeNames).toContain('verifyPhone');
    expect(routeNames).toContain('customerDashboard');
    expect(routeNames).toContain('customerCatalog');
    expect(routeNames).toContain('customerCreateOrder');
    expect(routeNames).toContain('tailorDashboard');
    expect(routeNames).toContain('tailorActiveOrders');
    expect(routeNames).toContain('tailorAvailability');
  });
});
