import { expect, test } from '@playwright/test';

test.describe('production smoke', () => {
  test('serves the public homepage and fleet page without page errors', async ({ page }) => {
    const pageErrors: string[] = [];
    page.on('pageerror', (error) => pageErrors.push(error.message));

    for (const path of ['/', '/fleet/']) {
      const response = await page.goto(path, { waitUntil: 'domcontentloaded' });
      expect(response?.status(), `${path} response`).toBe(200);
      await expect(page.locator('body')).toBeVisible();
      await expect(page.locator('title')).not.toHaveText('');
    }

    expect(pageErrors).toEqual([]);
  });
});
