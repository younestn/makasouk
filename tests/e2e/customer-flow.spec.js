import { expect, test } from '@playwright/test';
import { loginFromSpa } from './helpers/auth';
import { seededAccounts } from './helpers/accounts';

test('customer can browse catalog, open product details, and create an order', async ({ page }) => {
  await loginFromSpa(page, seededAccounts.customer);
  await expect(page).toHaveURL(/\/app\/customer(?:\?.*)?$/, { timeout: 20_000 });

  await page.goto('/app/customer/catalog');
  await expect(page).toHaveURL(/\/app\/customer\/catalog/);

  const productDetailsLink = page.locator('a[href*="/app/customer/products/"]').first();
  await expect(productDetailsLink).toBeVisible();
  await productDetailsLink.click();

  await expect(page).toHaveURL(/\/app\/customer\/products\/\d+/);
  const createOrderLink = page.locator('a[href*="/app/customer/orders/create"]').first();
  await expect(createOrderLink).toBeVisible();
  await createOrderLink.click();

  await expect(page).toHaveURL(/\/app\/customer\/orders\/create/);
  await expect(page.locator('#product')).toBeVisible();
  await expect
    .poll(() => page.locator('#product option').count(), { timeout: 20_000 })
    .toBeGreaterThan(1);
  await page.selectOption('#product', { index: 1 });

  await page.click('button[type="submit"]');
  await expect(page).toHaveURL(/\/app\/customer\/orders\/\d+/);
});
