import { expect, test } from '@playwright/test';
import { readFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const localRuntimeConfigured = Boolean(process.env.PLAYWRIGHT_BASE_URL);

test.describe('final theme experience', () => {
  test.skip(!localRuntimeConfigured, 'Set PLAYWRIGHT_BASE_URL after starting the LocalWP site.');

  test('renders the data-driven homepage with one H1 and vehicle cards', async ({ page }) => {
    await page.goto('/en/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('.vehicle-card').first()).toBeVisible();
    await expect(page.locator('[data-reservation-trigger]').first()).toHaveAttribute('data-vehicle-id', /\d+/);
    await expect(page.locator('.vehicle-card__starting-price').first()).toBeVisible();
  });

  test('keeps the supplied responsive hero and trip filter flow', async ({ page }) => {
    await page.goto('/');
    const hero = page.locator('.hero');

    await expect(hero.locator('picture')).toHaveCount(1);
    await expect(hero.locator('source[media="(max-width: 767px)"]')).toHaveAttribute('srcset', /hero-venice-mobile\.webp$/);
    await expect(hero.locator('img')).toHaveAttribute('src', /hero-venice-desktop\.webp$/);
    await expect(hero.locator('img')).toHaveAttribute('width', '1672');
    await expect(hero.locator('img')).toHaveAttribute('height', '941');
    await expect(hero.locator('img')).toHaveAttribute('fetchpriority', 'high');
    await expect(hero.locator('a[href="#trip-filter"]')).toHaveCount(1);
    await expect(page.locator('.trip-form')).toHaveCount(1);
    await expect(page.locator('.trip-filter-section__help')).toBeVisible();
    await expect(page.locator('.trip-form select[name="pickup_location"]')).toHaveCount(1);
    await expect(page.locator('.trip-form select[name="dropoff_location"]')).toHaveCount(1);
  });

  test('renders three precise, equal-width customer assurances below the trip filter', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/en/');
    const strip = page.locator('.trust-strip');
    const items = strip.locator('.trust-strip__item');

    await expect(items).toHaveCount(3);
    await expect(strip).not.toContainText('Car rental in Venice and Treviso');
    await expect(items.nth(0)).toContainText('Pickup at Venice Marco Polo and Treviso Airport');
    await expect(items.nth(1)).toContainText('No payment required to send a request');
    await expect(items.nth(2)).toContainText('Availability, final price and rental conditions confirmed personally');
    await expect(strip.locator('.trust-strip__icon[aria-hidden="true"]')).toHaveCount(3);
    await expect(strip.locator('a, button')).toHaveCount(0);
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();

    const layout = await items.evaluateAll((elements) => elements.map((element) => {
      const styles = getComputedStyle(element);
      const rect = element.getBoundingClientRect();
      return { width: rect.width, top: rect.top, borderLeft: styles.borderLeftWidth };
    }));
    expect(Math.max(...layout.map((item) => item.width)) - Math.min(...layout.map((item) => item.width))).toBeLessThanOrEqual(1);
    expect(layout.slice(1).every((item) => item.borderLeft !== '0px')).toBe(true);
  });

  test('stacks the three trust assurances without clipping on narrow screens', async ({ page }) => {
    for (const width of [320, 390]) {
      await page.setViewportSize({ width, height: 780 });
      await page.goto('/en/');
      const items = page.locator('.trust-strip__item');
      await expect(items).toHaveCount(3);
      await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();

      const rows = await items.evaluateAll((elements) => elements.map((element) => {
        const rect = element.getBoundingClientRect();
        return { top: rect.top, width: rect.width, height: rect.height, text: element.textContent || '' };
      }));
      expect(rows.every((row) => row.width <= width && row.height > 0)).toBe(true);
      expect(rows[0].top).toBeLessThan(rows[1].top);
      expect(rows[1].top).toBeLessThan(rows[2].top);
      expect(rows[0].text).toContain('Venice Marco Polo');
      expect(rows[1].text).toContain('No payment required to send a request');
    }
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

  test('renders only configured reservation extras with stable submitted keys', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    const extras = page.locator('[data-reservation-form] input[name="extras[]"]');
    await expect(extras).toHaveCount(2);
    await expect(extras.nth(0)).toHaveAttribute('value', 'child_seat');
    await expect(extras.nth(1)).toHaveAttribute('value', 'additional_driver');
    await expect(page.locator('[data-reservation-form] input[value="gps"], [data-reservation-form] input[value="internet_sim"]')).toHaveCount(0);
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
    const form = page.locator('[data-reservation-form]');
    await form.locator('input[name="pickup_date"]').fill('2027-04-10');
    await form.locator('input[name="pickup_time"]').fill('10:00');
    await form.locator('input[name="return_date"]').fill('2027-04-12');
    await form.locator('input[name="return_time"]').fill('10:00');
    await form.locator('input[name="pickup_location"]').fill('Venice');
    await form.locator('input[name="return_location"]').fill('Venice');
    await form.locator('input[name="first_name"]').fill('Local');
    await form.locator('input[name="last_name"]').fill('Test');
    await form.locator('input[name="phone"]').fill('+39000000000');
    await form.locator('input[name="email"]').fill('local-test@example.invalid');
    await form.locator('input[name="privacy"]').check();
    await form.locator('button[type="submit"]').click();
    await expect(page.locator('[data-reservation-success]')).toContainText(/Request received|Richiesta ricevuta/);
    await expect(page.locator('[data-reservation-success]')).not.toContainText(/confirmed reservation/i);
  });

  test('keeps the fleet grid responsive at a 320 pixel viewport', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await page.goto('/fleet/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('keeps fleet indexing signals specific to clean, filtered and paginated catalogue requests', async ({ page }) => {
    await page.goto('/fleet/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
    await expect(page.locator('meta[name="keywords"]')).toHaveCount(0);
    const cleanCanonical = await page.locator('link[rel="canonical"]').getAttribute('href');
    expect(cleanCanonical).toBeTruthy();

    await page.goto('/fleet/?transmission=manual');
    await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', cleanCanonical || '');
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex.*follow|follow.*noindex/i);

    await page.goto('/fleet/page/2/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
    await expect(page.locator('link[rel="canonical"]')).toHaveAttribute('href', /\/page\/2\/?$/);
  });

  test('provides crawlable vehicle breadcrumbs, meaningful primary image alt text and factual schema', async ({ page }) => {
    await page.goto('/');
    const detailsUrl = await page.locator('.vehicle-card h3 a').first().getAttribute('href');
    test.skip(!detailsUrl, 'A published vehicle is required for vehicle SEO coverage.');
    await page.goto(detailsUrl!);

    await expect(page.locator('h1')).toHaveCount(1);
    const breadcrumbs = page.locator('.breadcrumbs');
    await expect(breadcrumbs.locator('a')).toHaveCount(2);
    await expect(breadcrumbs.locator('[aria-current="page"]')).toHaveCount(1);
    await expect(page.locator('.vehicle-gallery__image--primary img')).toHaveAttribute('alt', /\S+/);
    const schema = await page.locator('script[type="application/ld+json"]').filter({ hasText: 'Product' }).allTextContents();
    expect(schema.join('\n')).not.toMatch(/"Offer"|"availability"|"InStock"|"aggregateRating"|"review"/i);
  });

  test('keeps current-language URLs intact when navigating enabled languages', async ({ page }) => {
    await page.goto('/');
    const switcher = page.locator('[data-language-switcher]');
    test.skip(await switcher.count() === 0, 'At least two enabled languages are required for this test.');
    await switcher.locator('[data-language-trigger]').click();
    const languages = await switcher.locator('.language-switcher__link').evaluateAll((links) => links.map((link) => ({ href: link.getAttribute('href'), lang: link.getAttribute('lang') })));

    const trustCopy: Record<string, string[]> = {
      en: ['Pickup at Venice Marco Polo and Treviso Airport', 'No payment required to send a request', 'Availability, final price and rental conditions confirmed personally'],
      it: ['Ritiro agli aeroporti di Venezia e Treviso', 'Nessun pagamento richiesto per inviare la richiesta', 'Disponibilità, prezzo finale e condizioni confermati personalmente'],
      ro: ['Preluare la aeroporturile din Veneția și Treviso', 'Nu este necesară nicio plată pentru a trimite o solicitare', 'Disponibilitatea, prețul final și condițiile de închiriere sunt confirmate personal'],
      ru: ['Получение в аэропортах Венеции и Тревизо', 'Для отправки запроса оплата не требуется', 'Доступность, окончательная цена и условия аренды подтверждаются лично'],
    };

    for (const language of languages) {
      if (!language.href || !language.lang) continue;
      await page.goto(language.href);
      await expect(page.locator('html')).toHaveAttribute('lang', new RegExp(`^${language.lang}(?:-|$)`, 'i'));
      await expect(page.locator('h1')).toHaveCount(1);
      const fleetLink = page.locator('a[href]').filter({ hasText: /View all cars|Vedi tutte|Vezi toate|Все/i }).first();
      await expect(fleetLink).toHaveCount(1);
      const copy = trustCopy[language.lang.toLowerCase().slice(0, 2)];
      if (copy) {
        const strip = page.locator('.trust-strip');
        await expect(strip.locator('.trust-strip__item')).toHaveCount(3);
        for (const line of copy) await expect(strip).toContainText(line);
      }
      const fleetHref = await fleetLink.getAttribute('href');
      expect(fleetHref).toBeTruthy();
      const fleetUrl = new URL(fleetHref || '', page.url());
      const expectedFleetPath = language.lang.toLowerCase().startsWith('it')
        ? '/fleet/'
        : `/${language.lang.toLowerCase().slice(0, 2)}/fleet/`;
      expect(fleetUrl.pathname).toBe(expectedFleetPath);
      await page.goto(fleetUrl.toString());
      await expect(page.locator('html')).toHaveAttribute('lang', new RegExp(`^${language.lang}(?:-|$)`, 'i'));
      await expect(page.locator('h1')).toHaveCount(1);
    }
  });

  test('returns a real noindex 404 with useful internal links', async ({ page }) => {
    const response = await page.goto('/en/this-route-does-not-exist/');
    expect(response?.status()).toBe(404);
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/i);
    await expect(page.locator('a[href]').filter({ hasText: /home|home page|iniziale|acasă|глав/i })).not.toHaveCount(0);
    await expect(page.locator('a[href]').filter({ hasText: /fleet|flotta|flotă|автопарк/i })).not.toHaveCount(0);
    await expect(page.locator('a[href]').filter({ hasText: /back to home/i })).toHaveAttribute('href', /\/en\/$/);
    await expect(page.locator('a[href]').filter({ hasText: /view the fleet/i })).toHaveAttribute('href', /\/en\/fleet\/$/);
  });

  test('renders an accessible language disclosure instead of separate language buttons', async ({ page }) => {
    await page.goto('/');
    const switcher = page.locator('[data-language-switcher]');

    test.skip(await switcher.count() === 0, 'At least two enabled languages are required for this test.');
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
    expect(await links.evaluateAll((items) => items.every((item) => item.getAttribute('href') && item.getAttribute('lang') && item.getAttribute('hreflang')))).toBe(true);
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

    test.skip(await switcher.count() === 0, 'At least two enabled languages are required for this test.');
    await switcher.locator('[data-language-trigger]').click();
    await expect(switcher.locator('[data-language-menu]')).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('keeps multilingual configuration and flag asset paths out of the theme source', async () => {
    const component = await readFile(resolve('theme/rentacar-venezia-v2/template-parts/global/language-switcher.php'), 'utf8');
    const functions = await readFile(resolve('theme/rentacar-venezia-v2/functions.php'), 'utf8');
    const multilingual = await readFile(resolve('theme/rentacar-venezia-v2/inc/multilingual.php'), 'utf8');

    expect(functions).not.toContain("'wpml_active_languages'");
    expect(multilingual).toContain("'wpml_active_languages'");
    expect(component).not.toContain('/wp-content/plugins/sitepress-multilingual-cms/res/flags/');
    expect(component).not.toMatch(/(?:\bRU\b|\bRO\b|\bIT\b|\bEN\b).*https?:/);
  });
});
