import { expect } from '@playwright/test';

export async function forceEnglishLocale(page) {
  await page.addInitScript(() => {
    window.localStorage.setItem('makasouk_locale', 'en');
  });
}

export async function loginFromSpa(page, credentials) {
  await forceEnglishLocale(page);
  await page.goto('/app/login');

  await expect(page.locator('#email')).toBeVisible();
  await page.fill('#email', credentials.email);
  await page.fill('#password', credentials.password);

  const [loginResponse] = await Promise.all([
    page.waitForResponse((response) => response.url().includes('/api/auth/login') && response.request().method() === 'POST'),
    page.click('button[type="submit"]'),
  ]);

  if (!loginResponse.ok()) {
    const body = await loginResponse.text();
    throw new Error(`Login request failed (${loginResponse.status()}): ${body}`);
  }
}
