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

  test('renders the covered airport hero image and three-field trip search', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/en/');
    const hero = page.locator('.hero');

    await expect(hero).toHaveClass(/hero--split/);
    await expect(hero.locator('.hero__media')).toHaveCount(0);
    const background = hero.locator('.hero__background-image');
    await expect(background).toHaveAttribute('src', /hero-airport-background\.webp$/);
    expect(await background.evaluate((element) => getComputedStyle(element).objectFit)).toBe('cover');
    await expect(page.locator('.trip-form')).toHaveCount(1);
    await expect(page.locator('.trip-form select[name="pickup_location"]')).toHaveCount(1);
    await expect(page.locator('.trip-form select[name="pickup_location"] option')).toHaveCount(7);
    await expect(page.locator('.trip-form details')).toHaveCount(0);
    await expect(page.locator('.trip-form input')).toHaveCount(2);

    const layout = await page.evaluate(() => {
      const rect = (selector: string) => document.querySelector(selector)?.getBoundingClientRect();
      const heroRect = rect('.hero');
      const headerRect = rect('.site-header');
      const headerInnerRect = rect('.site-header__inner');
      const formRect = rect('.hero__trip-form');
      const trustRect = rect('.trust-strip');
      return {
        heroHeight: heroRect?.height ?? 0,
        headerBottom: headerRect?.bottom ?? 0,
        headerInnerTop: headerInnerRect?.top ?? 0,
        heroTop: heroRect?.top ?? 0,
        formBottom: formRect?.bottom ?? 0,
        heroBottom: heroRect?.bottom ?? 0,
        trustTop: trustRect?.top ?? 0,
      };
    });

    expect(layout.heroHeight).toBeGreaterThanOrEqual(760);
    expect(layout.headerBottom).toBeGreaterThan(layout.heroTop);
    expect(layout.headerInnerTop).toBeGreaterThanOrEqual(8);
    expect(layout.formBottom).toBeLessThanOrEqual(layout.heroBottom);
    expect(layout.trustTop).toBeGreaterThanOrEqual(layout.heroBottom);
  });

  test('keeps the mobile hero copy above the trip form with the requested background crop', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/');
    const copyBottom = await page.locator('.hero__copy').evaluate((element) => element.getBoundingClientRect().bottom);
    const finderTop = await page.locator('.hero__trip-form').evaluate((element) => element.getBoundingClientRect().top);

    expect(copyBottom).toBeLessThanOrEqual(finderTop);
    expect(await page.locator('.hero__background-image').evaluate((element) => getComputedStyle(element).objectPosition)).toContain('70%');
    expect(await page.locator('.site-header__inner').evaluate((element) => element.getBoundingClientRect().top)).toBeGreaterThanOrEqual(8);
  });

  test('keeps every configured pickup option available in the homepage filter', async ({ page }) => {
    await page.goto('/en/');
    const form = page.locator('[data-trip-form]');
    const pickup = form.locator('select[name="pickup_location"]');

    await expect(pickup.locator('option')).toHaveCount(7);
    await expect(pickup).toContainText('Venice Marco Polo Airport');
    await expect(pickup).toContainText('Treviso Train Station');
    await expect(pickup).toContainText('Venice Mestre Train Station');
    await expect(form.locator('input[name="pickup_time"], input[name="return_time"], select[name="dropoff_location"]')).toHaveCount(0);
  });

  test('uses a numeric three-day minimum without rolling a July pickup into the next year', async ({ page }) => {
    await page.goto('/');
    const tripForm = page.locator('[data-trip-form]');
    const pickupDate = tripForm.locator('input[name="pickup_date"]');
    const returnDate = tripForm.locator('input[name="return_date"]');

    await pickupDate.fill('2026-07-28');
    await expect(pickupDate).toHaveValue('2026-07-28');
    await expect(returnDate).toHaveAttribute('min', '2026-07-31');
  });

  test('uses a fixed increasing-price fleet order without public filters', async ({ page }) => {
    await page.goto('/fleet/');
    await expect(page.locator('#fleet-filters, [data-fleet-filters], [data-fleet-filter-drawer], .fleet-active-filters, .fleet-airport-links')).toHaveCount(0);
    await expect(page.locator('select[name="transmission"], select[name="passengers"], select[name="doors"], input[name="air_conditioning"]')).toHaveCount(0);
    await expect(page.locator('select[name="sort"], .fleet-sort')).toHaveCount(0);
    await expect(page.locator('.fleet-page .page-intro > .eyebrow')).toHaveCount(0);
    await expect(page.locator('body')).not.toContainText(/Filter and sort cars|Filtra e ordina le auto|Apply filters|Clear filters/i);
  });

  test('renders two crawlable airport arrival options without an empty image stage', async ({ page }) => {
    await page.goto('/en/');
    const cards = page.locator('.arrivals-grid .arrival-card');

    await expect(cards).toHaveCount(2);
    await expect(page.locator('.arrivals-grid')).not.toContainText('We come where you need');
    expect(await cards.evaluateAll((items) => items.every((item) => item instanceof HTMLAnchorElement && Boolean(item.getAttribute('href'))))).toBe(true);
    expect(await cards.evaluateAll((items) => items.every((item) => Boolean(item.querySelector('.arrival-card__media img, .arrival-card__media svg'))))).toBe(true);
  });

  test('keeps the approved homepage section order and six real featured vehicles', async ({ page }) => {
    await page.goto('/en/');
    const sections = page.locator('.hero, .trust-strip, .fleet-section, .arrivals-section, .benefits-section, .final-cta');
    const positions = await sections.evaluateAll((items) => items.map((item) => item.getBoundingClientRect().top));

    expect(positions).toHaveLength(6);
    expect(positions.every((position, index) => index === 0 || position > positions[index - 1])).toBe(true);
    await expect(page.locator('.vehicle-grid--featured .vehicle-card')).toHaveCount(6);
    await expect(page.locator('.vehicle-grid--featured [data-reservation-trigger]')).toHaveCount(6);
    expect(await page.locator('.vehicle-grid--featured h3 a').evaluateAll((links) => links.every((link) => Boolean(link.getAttribute('href'))))).toBe(true);
  });

  test('keeps the approved benefits and conversion CTA in their intended hierarchy', async ({ page }) => {
    await page.goto('/en/');
    const benefits = page.locator('.benefits-section');
    const finalCta = page.locator('.final-cta');

    await expect(benefits.locator('.benefits-section__heading .eyebrow')).toHaveText('Why rent with us');
    await expect(benefits.locator('h2')).toHaveText('Direct support for every journey');
    await expect(benefits.locator('.benefits-grid > li')).toHaveCount(3);
    await expect(benefits.locator('.assistance-panel')).toContainText('Talk to our local team');
    await expect(finalCta.locator('h2')).toHaveText('Ready to choose your car?');
    await expect(finalCta).toContainText('Submitting this request does not immediately confirm the reservation.');
    await expect(finalCta.locator('a.button').first()).toHaveAttribute('href', /\/en\/fleet\/$/);
    expect(await benefits.evaluate((element) => element.getBoundingClientRect().top)).toBeLessThan(await finalCta.evaluate((element) => element.getBoundingClientRect().top));
  });

  test('keeps the homepage SEO head under Yoast ownership without duplicates', async ({ page }) => {
    await page.goto('/en/');

    await expect(page.locator('head title')).toHaveCount(1);
    await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
    await expect(page.locator('meta[name="description"]')).toHaveCount(1);
    await expect(page.locator('meta[name="description"]')).toHaveAttribute('content', /.{70,}/);
    await expect(page.locator('meta[name="keywords"]')).toHaveCount(0);
    const openGraphProperties = await page.locator('meta[property^="og:"]').evaluateAll((items) => items.map((item) => item.getAttribute('property')).filter(Boolean));
    const uniqueOpenGraphProperties = openGraphProperties.filter((property) => property !== 'og:locale:alternate');
    expect(new Set(uniqueOpenGraphProperties).size).toBe(uniqueOpenGraphProperties.length);
  });

  test('keeps the homepage filter and conversion sections usable without overflow at approved widths', async ({ page }) => {
    for (const width of [1440, 1024, 768, 430, 390, 320]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/en/');
      await expect(page.locator('[data-trip-form] select[name="pickup_location"]')).toBeVisible();
      await expect(page.locator('.arrivals-grid .arrival-card')).toHaveCount(2);
      await expect(page.locator('.benefits-grid > li')).toHaveCount(3);
      await expect(page.locator('.final-cta a.button').first()).toBeVisible();
      await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
    }
  });

  test('keeps the mobile hero focused and limits arrival choices to airports', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/en/');

    await expect(page.locator('.hero-location-card')).toHaveCount(0);
    await expect(page.locator('.arrivals-grid .arrival-card')).toHaveCount(2);
    await expect(page.locator('.arrivals-grid')).not.toContainText('We come where you need');
  });

  test('renders three precise, equal-width customer assurances below the hero', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/en/');
    const strip = page.locator('.trust-strip');
    const items = strip.locator('.trust-strip__item');

    await expect(items).toHaveCount(3);
    await expect(items.nth(0)).toContainText('Venice and Treviso airport pickup');
    await expect(items.nth(1)).toContainText('Fast and simple reservation');
    await expect(items.nth(2)).toContainText('Direct local assistance');
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
      expect(rows[0].text).toContain('Venice and Treviso airport pickup');
      expect(rows[1].text).toContain('Fast and simple reservation');
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

  test('renders only configured reservation extras and does not collect flight details', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    const extras = page.locator('[data-reservation-form] input[name="extras[]"]');
    await expect(extras).toHaveCount(3);
    await expect(extras.nth(0)).toHaveAttribute('value', 'child_seat');
    await expect(extras.nth(1)).toHaveAttribute('value', 'additional_driver');
    await expect(extras.nth(2)).toHaveAttribute('value', 'authorization_abroad');
    await expect(page.locator('[data-reservation-form] input[value="gps"], [data-reservation-form] input[value="internet_sim"]')).toHaveCount(0);
    await expect(page.locator('[data-reservation-form] input[name="similar_vehicle"]')).toHaveCount(0);
    await expect(page.locator('.reservation-extras small')).toHaveCount(3);
    await expect(page.locator('.reservation-extras')).toContainText(/€5[.,]00/);
    await expect(page.locator('.reservation-extras')).toContainText(/€80[.,]00/);
    await expect(page.locator('.reservation-form__hint')).toHaveCount(0);
    await expect(page.locator('[data-reservation-step="1"] .reservation-form__options summary')).toContainText(/extras|extra|дополн/i);
    await expect(page.locator('[data-reservation-step="2"] .reservation-form__options')).toHaveCount(0);
    await expect(page.locator('.reservation-flight, input[name="flight_number"], input[name="airline"]')).toHaveCount(0);
    await expect(page.locator('[data-reservation-progress]')).toContainText(/1 of 2|1 di 2|1 din 2|1 из 2/);
  });

  test('offers reservation times only in quarter-hour intervals', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    const form = page.locator('[data-reservation-form]');

    const pickupTimes = form.locator('select[name="pickup_time"] option');
    const returnTimes = form.locator('select[name="return_time"] option');
    await expect(pickupTimes).toHaveCount(97);
    await expect(returnTimes).toHaveCount(97);
    await expect(pickupTimes.nth(1)).toHaveAttribute('value', '00:00');
    await expect(pickupTimes.nth(2)).toHaveAttribute('value', '00:15');
    await expect(pickupTimes.nth(96)).toHaveAttribute('value', '23:45');
  });

  test('uses the shared searchable international phone control in reservation and contact forms', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    const reservationForm = page.locator('[data-reservation-form]');
    await reservationForm.locator('input[name="pickup_date"]').fill('2027-04-10');
    await reservationForm.locator('select[name="pickup_time"]').selectOption('10:00');
    await reservationForm.locator('input[name="return_date"]').fill('2027-04-13');
    await reservationForm.locator('select[name="return_time"]').selectOption('10:00');
    await reservationForm.locator('[data-reservation-continue]').click();
    const reservationPhone = page.locator('[data-reservation-form] [data-phone-field]');
    await expect(reservationPhone).toHaveCount(1);
    await expect(reservationPhone.locator('[data-phone-native]')).toBeHidden();
    await expect(reservationPhone.locator('[data-phone-option]')).toHaveCount(245);
    const selectorBox = await reservationPhone.locator('[data-phone-trigger]').boundingBox();
    const numberBox = await reservationPhone.locator('[data-phone-number]').boundingBox();
    expect(selectorBox?.y).toBe(numberBox?.y);
    expect(Math.round((selectorBox?.x || 0) + (selectorBox?.width || 0))).toBe(Math.round(numberBox?.x || 0));

    await reservationPhone.locator('[data-phone-trigger]').click();
    const search = reservationPhone.locator('[data-phone-search]');
    await expect(reservationPhone.locator('[data-phone-clear]')).toHaveCount(0);
    await search.fill('MD');
    await expect(reservationPhone.locator('[data-phone-option]:visible')).toHaveCount(1);
    await search.fill('+373');
    await expect(reservationPhone.locator('[data-phone-option]:visible')).toHaveCount(1);
    await search.fill('Moldova');
    await expect(reservationPhone.locator('[data-phone-option]:visible')).toHaveCount(1);
    await search.press('ArrowDown');
    await page.keyboard.press('Enter');
    await expect(reservationPhone.locator('[data-phone-code]')).toHaveText('+373');
    expect(await reservationPhone.locator('[data-phone-flag]').evaluate((element) => (
      element.textContent?.trim() || element.querySelector('img')?.getAttribute('alt') || ''
    ))).toBe('🇲🇩');
    await reservationPhone.locator('[data-phone-number]').fill('+373 (69) 123 456');
    await reservationPhone.locator('[data-phone-number]').blur();
    await expect(reservationPhone.locator('input[name="phone_calling_code"]')).toHaveValue('+373');

    await page.goto('/contact/');
    const contactPhone = page.locator('.contact-form [data-phone-field]');
    await expect(contactPhone).toHaveCount(1);
    await expect(contactPhone.locator('[data-phone-option]')).toHaveCount(245);
    const contactSelectorBox = await contactPhone.locator('[data-phone-trigger]').boundingBox();
    const contactNumberBox = await contactPhone.locator('[data-phone-number]').boundingBox();
    expect(contactSelectorBox?.y).toBe(contactNumberBox?.y);
    expect(Math.round((contactSelectorBox?.x || 0) + (contactSelectorBox?.width || 0))).toBe(Math.round(contactNumberBox?.x || 0));
  });

  test('keeps the phone country picker inside a narrow mobile viewport', async ({ page }) => {
    for (const width of [320, 360, 375, 390, 430]) {
      await page.setViewportSize({ width, height: 700 });
      await page.goto('/contact/');
      const phone = page.locator('.contact-form [data-phone-field]');
      const selectorBox = await phone.locator('[data-phone-trigger]').boundingBox();
      const numberBox = await phone.locator('[data-phone-number]').boundingBox();
      expect(selectorBox?.y).toBe(numberBox?.y);
      expect((numberBox?.x || 0) + (numberBox?.width || 0)).toBeLessThanOrEqual(width);
      await phone.locator('[data-phone-trigger]').click();
      const box = await phone.locator('[data-phone-dialog]').boundingBox();
      expect(box).not.toBeNull();
      expect((box?.x || 0) >= 0 && (box?.x || 0) + (box?.width || 0) <= width).toBeTruthy();
    }
  });

  test('localizes the shared phone picker in every supported interface language', async ({ page }) => {
    const routes = [
      ['/contatti/', 'Cerca Paesi'],
      ['/en/contact/', 'Search countries'],
      ['/ro/contacte-si-asistenta/', 'Căutați țări'],
      ['/ru/kontakty-i-pomoshch/', 'Поиск стран'],
    ] as const;
    for (const [route, searchLabel] of routes) {
      await page.goto(route);
      const phone = page.locator('.contact-form [data-phone-field]');
      await expect(phone.locator('[data-phone-search]')).toHaveAttribute('placeholder', searchLabel);
      await expect(phone.locator('[data-phone-option]')).toHaveCount(245);
    }
  });

  test('keeps a native country selector and telephone input without JavaScript', async ({ browser }) => {
    const context = await browser.newContext({ javaScriptEnabled: false });
    const page = await context.newPage();
    await page.goto(`${process.env.PLAYWRIGHT_BASE_URL || 'http://rentacar-venezia-local.local'}/contact/`);
    const form = page.locator('.contact-form form');
    await expect(form.locator('select[name="phone_country"]')).toBeVisible();
    await expect(form.locator('input[name="phone"]')).toHaveAttribute('type', 'tel');
    await expect(form.locator('input[name="phone_calling_code"]')).toHaveValue('+39');
    await context.close();
  });

  test('returns a localized field-level error when the contact phone is invalid', async ({ page }) => {
    await page.goto('/contatti/');
    const form = page.locator('.contact-form form');
    await form.locator('input[name="name"]').fill('Local Contact Test');
    await form.locator('input[name="phone"]').fill('invalid-number');
    await form.locator('input[name="email"]').fill(`contact-invalid-${Date.now()}@example.invalid`);
    await form.locator('select[name="topic"]').selectOption('general');
    await form.locator('textarea[name="message"]').fill('This checks the invalid phone response.');
    await form.locator('input[name="privacy"]').check();
    await Promise.all([
      page.waitForURL(/contact_status=invalid_phone/),
      form.locator('button[type="submit"]').click(),
    ]);
    await expect(page.locator('.international-phone__error')).toContainText('numero di telefono');
  });

  test('uses configured pickup selects and sends the authoritative inter-airport locations for an estimate', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    const form = page.locator('[data-reservation-form]');

    await expect(form.locator('select[name="pickup_location"] option')).toHaveCount(7);
    await expect(form.locator('select[name="return_location"] option')).toHaveCount(7);
    await expect(form.locator('input[name="pickup_location"], input[name="return_location"]')).toHaveCount(0);
    await expect(form.locator('.reservation-location-fee')).toContainText(/€25[.,]00/);

    await form.locator('input[name="pickup_date"]').fill('2027-04-10');
    await form.locator('select[name="pickup_time"]').selectOption('10:00');
    await form.locator('input[name="return_date"]').fill('2027-04-13');
    await form.locator('select[name="return_time"]').selectOption('10:00');
    await form.locator('select[name="pickup_location"]').selectOption('Airport Venice Marco Polo');
    await form.locator('[data-reservation-return-different]').check();
    const estimateRequest = page.waitForRequest((request) => request.url().includes('/wp-json/rentacar/v1/estimate') && request.postData()?.includes('return_location=Treviso+Airport+Arrivals'));
    await form.locator('select[name="return_location"]').selectOption('Treviso Airport Arrivals');
    await expect(await estimateRequest).toBeTruthy();
    // The estimate remains server-authoritative. A vehicle without a matching
    // pricing band may legitimately return no indicative total, so assert the
    // location payload rather than manufacturing a client-side amount.
  });

  test('keeps refundable deposit and estimate disclaimer out of the customer calculation', async ({ page }) => {
    await page.route('**/wp-json/rentacar/v1/estimate', async (route) => {
      await route.fulfill({
        contentType: 'application/json',
        body: JSON.stringify({
          days: 3,
          line_items: [{ key: 'vehicle_base_rate', label: 'Vehicle subtotal', amount: 180 }],
          estimate_total: 540,
          included_km: 450,
          excess_km_rate: 0.1,
          deposit: 350,
          disclaimer: 'This is an indicative estimate. Availability and final price are confirmed by our team.',
        }),
      });
    });
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    const form = page.locator('[data-reservation-form]');
    await form.locator('input[name="pickup_date"]').fill('2027-04-10');
    await form.locator('select[name="pickup_time"]').selectOption('10:00');
    await form.locator('input[name="return_date"]').fill('2027-04-13');
    await form.locator('select[name="return_time"]').selectOption('10:00');

    const calculation = form.locator('[data-reservation-estimate-content]');
    await expect(calculation).toContainText('540');
    await expect(calculation).not.toContainText(/Refundable security deposit|Deposito cauzionale rimborsabile/i);
    await expect(calculation).not.toContainText('This is an indicative estimate. Availability and final price are confirmed by our team.');
  });

  test('presents accessible validation errors without a network request', async ({ page }) => {
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    await page.locator('[data-reservation-continue]').click();
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
    await form.locator('select[name="pickup_time"]').selectOption('10:00');
    await form.locator('input[name="return_date"]').fill('2027-04-13');
    await form.locator('select[name="return_time"]').selectOption('10:00');
    await form.locator('select[name="pickup_location"]').selectOption('Airport Venice Marco Polo');
    await form.locator('[data-reservation-continue]').click();
    await form.locator('input[name="full_name"]').fill('Local Test');
    await form.locator('input[name="phone"]').fill('+39000000000');
    await form.locator('input[name="email"]').fill('local-test@example.invalid');
    await form.locator('input[name="terms"]').check();
    await form.locator('button[type="submit"]').click();
    await expect(page.locator('[data-reservation-modal-title]')).toContainText(/Request received|Richiesta ricevuta/);
    await expect(page.locator('[data-reservation-success]')).not.toContainText(/confirmed reservation/i);
    await expect(page.locator('[data-reservation-modal]')).toHaveClass(/reservation-modal--success/);
    await expect(page.locator('[data-reservation-success] h2')).toHaveCount(0);
  });

  test('keeps the fleet grid responsive at a 320 pixel viewport', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 568 });
    await page.goto('/fleet/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('keeps the request form and contact page readable at a 320 pixel viewport', async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 640 });
    await page.goto('/');
    await page.locator('[data-reservation-trigger]').first().click();
    await expect(page.locator('[data-reservation-form] select[name="pickup_location"]')).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();

    await page.keyboard.press('Escape');
    await page.goto('/contatti/');
    await expect(page.locator('.contact-page')).toBeVisible();
    await expect(page.locator('.contact-page h1')).toHaveCount(1);
    await expect(page.locator('.contact-form input[name="name"]')).toBeVisible();
    await expect(page.locator('.contact-form select[name="topic"]')).toBeVisible();
    await expect(page.locator('.contact-form input[name="privacy"]')).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('keeps the selected vehicle compact and optional details progressive on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/fleet/');
    await page.locator('[data-reservation-trigger]').first().click();

    const summary = page.locator('.reservation-summary');
    const summaryBox = await summary.boundingBox();
    expect(summaryBox?.height).toBeLessThan(120);
    await expect(page.locator('.reservation-form__options')).not.toHaveAttribute('open', '');
    await expect(page.locator('.reservation-form__optional-note')).not.toHaveAttribute('open', '');
    await expect(page.locator('.reservation-location-fee')).toBeHidden();
    await page.locator('[data-reservation-return-different]').check();
    await expect(page.locator('.reservation-location-fee')).toBeVisible();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('keeps reservation contact details short and focused', async ({ page }) => {
    await page.goto('/fleet/');
    await page.locator('[data-reservation-trigger]').first().click();
    const form = page.locator('[data-reservation-form]');

    await expect(form.locator('input[name="first_name"], input[name="last_name"]')).toHaveCount(0);
    await expect(form.locator('input[name="full_name"]')).toHaveCount(1);
    await expect(form.locator('input[name="privacy"]')).toHaveCount(0);
    await expect(form.locator('.reservation-form__disclaimer')).toHaveCount(0);
    await expect(page.locator('.reservation-summary__image')).toHaveCount(0);
    await expect(form.locator('.reservation-form__intro')).toHaveCount(0);
    await expect(form.locator('.reservation-form__options')).not.toHaveAttribute('open', '');
    await expect(form.locator('.reservation-form__optional-note')).not.toHaveAttribute('open', '');
  });

  test('sends the separate general contact form through the protected contact endpoint', async ({ page }) => {
    await page.goto('/contatti/');
    const form = page.locator('.contact-form form');
    await form.locator('input[name="name"]').fill('Local Contact Test');
    await form.locator('input[name="phone"]').fill('+39000000000');
    await form.locator('input[name="email"]').fill(`contact-browser-${Date.now()}@example.invalid`);
    await form.locator('select[name="topic"]').selectOption('general');
    await form.locator('textarea[name="message"]').fill('This checks the LocalWP contact request path.');
    await form.locator('input[name="privacy"]').check();
    await Promise.all([
      page.waitForURL(/contact_status=(sent|delivery)/),
      form.locator('button[type="submit"]').click(),
    ]);
    await expect(page.locator('.contact-form__status')).toBeVisible();
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

  test('keeps transactional legacy pages and user archives out of search discovery', async ({ page }) => {
    for (const path of ['/total/', '/la-tua-richiesta-e-stata-inviata/', '/posti/']) {
      await page.goto(path);
      await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', /noindex/i);
    }

    const sitemap = await page.request.get('/wp-sitemap.xml');
    expect(sitemap.ok()).toBeTruthy();
    await expect(sitemap.text()).resolves.not.toContain('wp-sitemap-users');

    const pageSitemap = await page.request.get('/wp-sitemap-posts-page-1.xml');
    expect(pageSitemap.ok()).toBeTruthy();
    await expect(pageSitemap.text()).resolves.not.toContain('/total/');
    await expect(pageSitemap.text()).resolves.not.toContain('/posti/');
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

    const fleetHeadings: Record<string, string> = {
      en: 'Rental cars in Venice and Treviso',
      ro: 'Mașini de închiriat în Veneția și Treviso',
      ru: 'Автомобили в аренду в Венеции и Тревизо',
    };

    for (const language of languages) {
      if (!language.href || !language.lang) continue;
      await page.goto(language.href);
      await expect(page.locator('html')).toHaveAttribute('lang', new RegExp(`^${language.lang}(?:-|$)`, 'i'));
      await expect(page.locator('h1')).toHaveCount(1);
      const languageCode = language.lang.toLowerCase().slice(0, 2);
      await expect(page.locator('.benefits-section h2')).toHaveCount(1);
      const fleetLink = page.locator('.hero a.button').first();
      await expect(fleetLink).toHaveCount(1);
      await expect(page.locator('.trust-strip .trust-strip__item')).toHaveCount(3);
      const fleetHref = await fleetLink.getAttribute('href');
      expect(fleetHref).toBeTruthy();
      const fleetUrl = new URL(fleetHref || '', page.url());
      // The multilingual provider owns the translated page slug. Assert that
      // the generated link stays in the active language without baking a
      // particular translated slug into the test.
      if (languageCode === 'it') {
        expect(fleetUrl.pathname).toBe('/fleet/');
      } else {
        expect(fleetUrl.pathname).toMatch(new RegExp(`^/${languageCode}/[^/]+/$`));
        expect(fleetUrl.pathname).not.toMatch(/\/fleet-\d+\/$/);
      }
      await page.goto(fleetUrl.toString());
      await expect(page.locator('html')).toHaveAttribute('lang', new RegExp(`^${language.lang}(?:-|$)`, 'i'));
      await expect(page.locator('h1')).toHaveCount(1);
      const expectedFleetHeading = fleetHeadings[languageCode];
      if (expectedFleetHeading) await expect(page.locator('h1')).toHaveText(expectedFleetHeading);
    }
  });

  test('renders the managed primary menu in every enabled language', async ({ page }) => {
    const navigation: Record<string, string[]> = {
      '/': ['Flotta', 'Aeroporto Venezia Marco Polo', 'Aeroporto Treviso', 'Come funziona', 'FAQ', 'Guide', 'Contatti'],
      '/en/': ['Fleet', 'Venice Marco Polo Airport', 'Treviso Airport', 'How it works', 'FAQ', 'Guides', 'Contact'],
      '/ro/': ['Flotă', 'Aeroportul Veneția Marco Polo', 'Aeroportul Treviso', 'Cum funcționează', 'Întrebări frecvente', 'Ghiduri', 'Contacte'],
      '/ru/': ['Автопарк', 'Аэропорт Венеция Марко Поло', 'Аэропорт Тревизо', 'Как это работает', 'Частые вопросы', 'Путеводители', 'Контакты'],
    };

    for (const [path, labels] of Object.entries(navigation)) {
      await page.goto(path);
      const links = page.locator('.primary-navigation__list > li > a');
      await expect(links).toHaveCount(labels.length);
      await expect(links).toHaveText(labels);
      expect(await links.evaluateAll((items) => items.every((item) => Boolean(item.getAttribute('href'))))).toBe(true);
    }
  });

  test('keeps shared footer, contact and reservation controls translated', async ({ page }) => {
    const languages = [
      ['/', ['Termini e condizioni', 'Informativa sulla privacy'], '/contatti/', 'Invia messaggio'],
      ['/en/', ['Terms and Conditions', 'Privacy Policy'], '/en/contact/', 'Send message'],
      ['/ro/', ['Termeni și condiții', 'Politica de confidențialitate'], '/ro/contacte-si-asistenta/', 'Trimiteți mesajul'],
      ['/ru/', ['Условия и положения', 'Политика конфиденциальности'], '/ru/kontakty-i-pomoshch/', 'Отправить сообщение'],
    ] as const;

    for (const [homePath, legalLabels, contactPath, contactSubmitLabel] of languages) {
      await page.goto(homePath);
      await expect(page.locator('.site-footer__legal')).toContainText(legalLabels[0]);
      await expect(page.locator('.site-footer__legal')).toContainText(legalLabels[1]);

      await page.goto(contactPath);
      await expect(page.locator('.contact-form button[type="submit"]')).toHaveText(contactSubmitLabel);
      await expect(page.locator('.contact-form label').filter({ hasText: 'Full name' })).toHaveCount(homePath === '/en/' ? 1 : 0);
    }
  });

  test('does not log browser console errors while loading the homepage', async ({ page }) => {
    const errors: string[] = [];
    page.on('console', (message) => {
      if (message.type() === 'error') errors.push(message.text());
    });

    await page.goto('/en/');
    await page.waitForLoadState('networkidle');
    expect(errors).toEqual([]);
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

  test('keeps the desktop header focused on the established logo and essential navigation', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto('/');

    await expect(page.locator('.site-header .site-brand img')).toHaveCount(1);
    await expect(page.locator('.site-header .site-brand img')).toBeVisible();
    await expect(page.locator('.primary-navigation__list > li:visible')).toHaveCount(3);
    await expect(page.locator('.site-header__actions .button--whatsapp')).toBeHidden();
    await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
  });

  test('exposes the configured social destinations in the footer', async ({ page }) => {
    await page.goto('/');
    const links = page.locator('.site-footer__social-link');

    await expect(links).toHaveCount(4);
    await expect(links.nth(0)).toHaveAttribute('href', 'https://www.instagram.com/rentacar_veniceairport/');
    await expect(links.nth(1)).toHaveAttribute('href', 'https://www.facebook.com/people/Rent-A-Car-Venezia-no-credit-card/61585973730435/#');
    await expect(links.nth(2)).toHaveAttribute('href', 'https://wa.me/393445068823');
    await expect(links.nth(3)).toHaveAttribute('href', 'https://t.me/+393445068823');
    expect(await links.evaluateAll((items) => items.every((item) => item.getAttribute('target') === '_blank' && item.getAttribute('rel') === 'noopener noreferrer'))).toBe(true);
  });

  test('renders translated, WordPress-managed Cookie Policy pages with their own footer targets', async ({ page }) => {
    const policies = [
      ['/', 'Cookie Policy', 'cookie-policy'],
      ['/en/', 'Cookie Policy', 'cookies'],
      ['/ro/', 'Politica privind cookie-urile', 'politica-privind-cookie-urile'],
      ['/ru/', 'Политика использования файлов cookie', 'politika-ispolzovaniya-faylov-cookie'],
    ] as const;

    for (const [prefix, title, slug] of policies) {
      await page.goto(`${prefix}${slug}/`);
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('h1')).toHaveText(title);
      await expect(page.locator('link[rel="canonical"]')).toHaveCount(1);
      await expect(page.locator('.site-footer__legal a')).toHaveCount(2);
    }
  });

  test('keeps the Cookie Policy and inventory usable without document overflow', async ({ page }) => {
    for (const width of [320, 390, 430, 768, 1024, 1440]) {
      await page.setViewportSize({ width, height: 900 });
      await page.goto('/en/cookie-policy/');
      await expect(page.locator('.legal-page__toc')).toBeVisible();
      await expect(page.locator('.legal-page__inventory-wrap')).toBeVisible();
      await expect(page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).resolves.toBeTruthy();
    }
  });

  test('keeps optional tracking disabled and removes the custom cookie interface', async ({ page }) => {
    const optionalRequests: string[] = [];
    page.on('request', (request) => {
      if (/googletagmanager|google-analytics|doubleclick|google\.it\/ads/i.test(request.url())) optionalRequests.push(request.url());
    });
    await page.goto('/');
    await expect(page.locator('[data-cookie-consent], [data-cookie-settings], [data-cookie-preferences-dialog]')).toHaveCount(0);
    expect(optionalRequests).toEqual([]);
  });

  test('keeps the generated anonymous inventory aligned with analytics-disabled state', async ({ page, context }) => {
    const audit = JSON.parse(await readFile(resolve('docs/generated/cookie-audit.json'), 'utf8')) as { inventory: { publicVisitorTechnologies: Array<{ exactName: string }> } };
    await page.goto('/');
    const names = (await context.cookies()).map((cookie) => cookie.name).sort();
    const auditedNames = audit.inventory.publicVisitorTechnologies.map((item) => item.exactName);
    expect(names.every((name) => auditedNames.includes(name))).toBe(true);
    expect(names).toEqual(['pll_language']);
  });

  test('keeps Cookie Policy provisioning owner-managed instead of template-hardcoded', async () => {
    const provisioner = await readFile(resolve('tools/local-wp/provision-site.php'), 'utf8');
    const template = await readFile(resolve('theme/rentacar-venezia-v2/page.php'), 'utf8');

    expect(provisioner).toContain("'cookie_policy'");
    expect(provisioner).toContain("'owner_managed' => true");
    expect(provisioner).toContain('if ( $owner_managed )');
    expect(provisioner).toContain("'' === trim( (string) get_post_field( 'post_content'");
    expect(template).toContain('the_content()');
    expect(template).not.toContain('Cookie Policy');
  });

  test('uses an inert, escapable mobile navigation drawer', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/');
    const toggle = page.locator('[data-menu-toggle]');
    await toggle.click();
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('#main-content')).toHaveAttribute('inert', '');
    await page.keyboard.press('Escape');
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    await expect(page.locator('#main-content')).not.toHaveAttribute('inert', '');
    await expect(toggle).toBeFocused();
  });

  test('removes fixed mobile conversion actions', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/');
    await expect(page.locator('.mobile-action-bar')).toHaveCount(0);

    await page.goto('/fleet/');
    await expect(page.locator('.mobile-action-bar, [data-mobile-filter-trigger], .fleet-filter-drawer')).toHaveCount(0);

    const vehicleUrl = await page.locator('.vehicle-card h3 a').first().getAttribute('href');
    test.skip(!vehicleUrl, 'A published vehicle is required for mobile vehicle actions.');
    await page.goto(vehicleUrl!);
    await expect(page.locator('.mobile-action-bar')).toHaveCount(0);
  });

  test('renders structured airport and information pages without legacy flight collection', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/en/venice-marco-polo-airport-car-rental/');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('.landing-process li')).toHaveCount(4);
    await expect(page.locator('.landing-hero .button')).toHaveCount(1);
    await expect(page.locator('body')).not.toContainText(/flight number/i);
    await expect(page.locator('.mobile-action-bar')).toHaveCount(0);

    await page.goto('/en/how-it-works/');
    await expect(page.locator('.information-steps li')).toHaveCount(5);
    await expect(page.locator('body')).not.toContainText(/flight number/i);

    await page.goto('/en/rental-requirements/');
    await expect(page.locator('.requirements-grid section')).toHaveCount(6);
    await expect(page.locator('.requirements-grid')).toContainText('150 km');
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
