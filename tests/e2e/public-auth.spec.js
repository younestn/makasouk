import { expect, test } from '@playwright/test';
import { forceEnglishLocale, loginFromSpa } from './helpers/auth';
import { seededAccounts } from './helpers/accounts';

test('public homepage loads', async ({ page }) => {
  await forceEnglishLocale(page);
  const response = await page.goto('/');

  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('a[href="/app/login"]').first()).toBeVisible();
});

test('login page loads', async ({ page }) => {
  await forceEnglishLocale(page);
  const response = await page.goto('/app/login');

  expect(response?.ok()).toBeTruthy();
  await expect(page.locator('#email')).toBeVisible();
  await expect(page.locator('#password')).toBeVisible();
});

test('protected customer route redirects guests to login', async ({ page }) => {
  await forceEnglishLocale(page);
  await page.goto('/app/customer');

  await expect(page).toHaveURL(/\/app\/login/);
});

test('customer cannot access tailor route directly', async ({ page }) => {
  await loginFromSpa(page, seededAccounts.customer);
  await expect(page).toHaveURL(/\/app\/customer(?:\?.*)?$/, { timeout: 20_000 });

  await page.goto('/app/tailor/orders/active');
  await expect(page).toHaveURL(/\/app\/forbidden$/);
});

test('admin panel login route loads', async ({ page }) => {
  const response = await page.goto('/admin-panel/login');

  expect(response?.ok()).toBeTruthy();
  await expect(page).toHaveURL(/\/admin-panel\/login/);
});
