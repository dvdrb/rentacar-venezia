import { expect, test } from '@playwright/test';
import { readFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const localRuntimeConfigured = Boolean(process.env.PLAYWRIGHT_BASE_URL);

test.describe('final theme experience', () => {
  test.skip(!localRuntimeConfigured, 'Set PLAYWRIGHT_BASE_URL after starting the LocalWP site.');

  test('renders the data-driven homepage with one H1 and vehicle cards', async ({ page }) => {
    await page.goto('/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('.vehicle-card').first()).toBeVisible();
    await expect(page.locator('[data-reservation-trigger]').first()).toHaveAttribute('data-vehicle-id', /\d+/);
  });

  test('opens a selected-vehicle modal and restores focus after Escape', async ({ page }) => {
    await page.goto('/');
    const trigger = page.locator('[data-reservation-trigger]').first();
    const title = await trigger.getAttribute('data-vehicle-title');

    await trigger.focus();
    await trigger.click();
    await expect(page.locator('[data-reservation-modal]')).toBeVisible();
    await expect(page.locator('[data-reservation-title]')).toHaveText(title || '');
    await page.keyboard.press('Escape');
    await expect(page.locator('[data-reservation-modal]')).toBeHidden();
    await expect(trigger).toBeFocused();
  });

  test('presents accessible validation errors without a network request', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    await page.locator('[data-reservation-form] button[type="submit"]').click();
    await expect(page.locator('[data-reservation-errors]')).toBeFocused();
    await expect(page.locator('[aria-invalid="true"]')).not.toHaveCount(0);
  });

  test('shows a non-confirming success state only from an accepted response', async ({ page }) => {
    await page.route('**/wp-admin/admin-post.php', async (route) => {
      await route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: { reference: 'LOCAL-TEST-001' } }) });
    });
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    await page.locator('input[name="pickup_date"]').fill('2027-04-10');
    await page.locator('input[name="pickup_time"]').fill('10:00');
    await page.locator('input[name="return_date"]').fill('2027-04-12');
    await page.locator('input[name="return_time"]').fill('10:00');
    await page.locator('input[name="pickup_location"]').fill('Venice');
    await page.locator('input[name="return_location"]').fill('Venice');
    await page.locator('input[name="full_name"]').fill('Local test');
    await page.locator('input[name="phone"]').fill('+39000000000');
    await page.locator('input[name="email"]').fill('local-test@example.invalid');
    await page.locator('input[name="privacy"]').check();
    await page.locator('[data-reservation-form] button[type="submit"]').click();
    await expect(page.locator('[data-reservation-success]')).toContainText('Request received');
    await expect(page.locator('[data-reservation-success]')).not.toContainText(/confirmed reservation/i);
  });

  test('keeps the fleet grid responsive at a 320 pixel viewport', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await page.goto('/fleet/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('renders an accessible WPML language disclosure instead of separate language buttons', async ({ page }) => {
    await page.goto('/');
    const switcher = page.locator('[data-language-switcher]');

    test.skip(await switcher.count() === 0, 'WPML must have at least two enabled languages for this test.');
    await expect(switcher).toHaveCount(1);

    const trigger = switcher.locator('[data-language-trigger]');
    await expect(trigger).toHaveAttribute('aria-expanded', 'false');
    await expect(trigger.locator('.language-switcher__code')).toHaveText(/\S+/);
    await expect(trigger.locator('.language-switcher__flag, .language-switcher__flag-fallback')).toHaveCount(1);
    await expect(page.locator('.language-switcher > ul')).toHaveCount(0);

    await trigger.click();
    await expect(trigger).toHaveAttribute('aria-expanded', 'true');
    const menu = switcher.locator('[data-language-menu]');
    const links = menu.locator('.language-switcher__link');
    await expect(menu).toBeVisible();
    expect(await links.count()).toBeGreaterThan(1);
    await expect(menu.locator('.language-switcher__name')).not.toHaveCount(0);
    await expect(menu.locator('[aria-current="page"]')).toHaveCount(1);
    await expect(links).toHaveAttribute('href', /.+/);
    await expect(links).toHaveAttribute('lang', /.+/);
    await expect(links).toHaveAttribute('hreflang', /.+/);
    await expect(menu.locator('img[src=""]')).toHaveCount(0);
    expect(await switcher.innerText()).not.toMatch(/\p{Regional_Indicator}/u);

    await page.keyboard.press('Escape');
    await expect(menu).toBeHidden();
    await expect(trigger).toBeFocused();

    await trigger.click();
    await page.mouse.click(2, 500);
    await expect(menu).toBeHidden();
  });

  test('keeps the language selector and mobile menu within a 320 pixel header', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await page.goto('/');
    const switcher = page.locator('[data-language-switcher]');

    test.skip(await switcher.count() === 0, 'WPML must have at least two enabled languages for this test.');
    await switcher.locator('[data-language-trigger]').click();
    await expect(switcher.locator('[data-language-menu]')).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('keeps WPML language configuration and flag asset paths out of the theme source', async () => {
    const component = await readFile(resolve('theme/rentacar-venezia-v2/template-parts/global/language-switcher.php'), 'utf8');

    expect(component).toContain("'wpml_active_languages'");
    expect(component).not.toContain('/wp-content/plugins/sitepress-multilingual-cms/res/flags/');
    expect(component).not.toMatch(/(?:\bRU\b|\bRO\b|\bIT\b|\bEN\b).*https?:/);
  });
});
