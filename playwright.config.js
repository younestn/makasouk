import { defineConfig, devices } from '@playwright/test';

const appPort = Number(process.env.APP_PORT || 8000);
const appUrl = process.env.E2E_BASE_URL || `http://127.0.0.1:${appPort}`;

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 60_000,
  expect: {
    timeout: 10_000,
  },
  fullyParallel: !process.env.CI,
  workers: process.env.CI ? 1 : undefined,
  forbidOnly: Boolean(process.env.CI),
  retries: process.env.CI ? 1 : 0,
  reporter: process.env.CI ? [['line']] : [['list']],
  use: {
    baseURL: appUrl,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: {
    command: process.env.PLAYWRIGHT_WEB_SERVER_COMMAND
      || `php artisan migrate:fresh --seed --force && php artisan serve --host=127.0.0.1 --port=${appPort}`,
    url: appUrl,
    timeout: 240_000,
    reuseExistingServer: false,
  },
});
