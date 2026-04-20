import { expect, test } from '@playwright/test';
import { loginFromSpa } from './helpers/auth';
import { seededAccounts } from './helpers/accounts';

test('tailor can access active orders and open order details', async ({ page }) => {
  await loginFromSpa(page, seededAccounts.tailor);
  await expect(page).toHaveURL(/\/app\/tailor(?:\?.*)?$/, { timeout: 20_000 });

  await page.goto('/app/tailor/orders/active');
  await expect(page).toHaveURL(/\/app\/tailor\/orders\/active/);

  const detailsButton = page.getByRole('link', { name: 'Open Details' }).first();
  await expect(detailsButton).toBeVisible();
  await detailsButton.click();

  await expect(page).toHaveURL(/\/app\/tailor\/orders\/\d+/);
  await expect(page.locator('h2, h1').filter({ hasText: 'Order #' }).first()).toBeVisible();
});
