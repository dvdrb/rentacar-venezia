import { mkdir } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import { chromium } from '@playwright/test';

const baseUrl = process.env.PLAYWRIGHT_BASE_URL || 'http://localhost:10003';
const outputDirectory = new URL('../../docs/generated/screenshots/', import.meta.url);

await mkdir(outputDirectory, { recursive: true });

const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 }, deviceScaleFactor: 1 });

async function capture(name, path, viewport = { width: 1440, height: 1000 }) {
  await page.setViewportSize(viewport);
  await page.goto(new URL(path, baseUrl).toString(), { waitUntil: 'networkidle' });
  await page.screenshot({ path: fileURLToPath(new URL(`${name}.png`, outputDirectory)), fullPage: true });
}

await capture('homepage-desktop', '/');
await capture('homepage-mobile', '/', { width: 390, height: 844 });
await capture('fleet-desktop', '/fleet/');
await capture('fleet-mobile', '/fleet/', { width: 390, height: 844 });
await capture('venice-airport-desktop', '/venice-marco-polo-airport-car-rental/');
await capture('venice-airport-mobile', '/venice-marco-polo-airport-car-rental/', { width: 390, height: 844 });
await capture('treviso-airport-desktop', '/treviso-airport-car-rental/');
await capture('treviso-airport-mobile', '/treviso-airport-car-rental/', { width: 390, height: 844 });
await capture('how-it-works-mobile', '/how-it-works/', { width: 390, height: 844 });
await capture('rental-requirements-desktop', '/rental-requirements/');
await capture('faq-desktop', '/faq/');
await capture('contact-mobile', '/contatti/', { width: 390, height: 844 });
await capture('guides-desktop', '/guides/');
await capture('terms-desktop', '/terms-and-conditions/');
await capture('not-found-desktop', '/this-route-does-not-exist/');

await page.setViewportSize({ width: 390, height: 844 });
await page.goto(new URL('/fleet/', baseUrl).toString(), { waitUntil: 'networkidle' });
await page.locator('[data-mobile-filter-trigger]').click();
await page.screenshot({ path: fileURLToPath(new URL('fleet-filter-drawer-mobile.png', outputDirectory)), fullPage: true });
await page.keyboard.press('Escape');

await page.goto(new URL('/', baseUrl).toString(), { waitUntil: 'networkidle' });
await page.locator('[data-menu-toggle]').click();
await page.screenshot({ path: fileURLToPath(new URL('mobile-navigation.png', outputDirectory)), fullPage: true });
await page.keyboard.press('Escape');

const vehicleHref = await page.locator('.vehicle-card h3 a').first().getAttribute('href');
if (vehicleHref) {
  await capture('vehicle-desktop', vehicleHref);
  await capture('vehicle-mobile', vehicleHref, { width: 390, height: 844 });
}

async function fillReservationStepOne() {
  const form = page.locator('[data-reservation-form]');
  await form.locator('input[name="pickup_date"]').fill('2027-04-10');
  await form.locator('input[name="pickup_time"]').fill('10:00');
  await form.locator('input[name="return_date"]').fill('2027-04-12');
  await form.locator('input[name="return_time"]').fill('10:00');
}

await page.setViewportSize({ width: 1440, height: 1000 });
await page.goto(new URL('/', baseUrl).toString(), { waitUntil: 'networkidle' });
await page.locator('[data-reservation-trigger]').first().click();
await fillReservationStepOne();
await page.screenshot({ path: fileURLToPath(new URL('reservation-step-one-desktop.png', outputDirectory)), fullPage: true });
await page.locator('[data-reservation-continue]').click();
await page.screenshot({ path: fileURLToPath(new URL('reservation-step-two-desktop.png', outputDirectory)), fullPage: true });
await page.keyboard.press('Escape');

await page.setViewportSize({ width: 390, height: 844 });
await page.goto(new URL('/', baseUrl).toString(), { waitUntil: 'networkidle' });
await page.locator('[data-reservation-trigger]').first().click();
await fillReservationStepOne();
await page.screenshot({ path: fileURLToPath(new URL('reservation-modal-mobile.png', outputDirectory)), fullPage: true });
await page.locator('[data-reservation-continue]').click();
await page.screenshot({ path: fileURLToPath(new URL('reservation-step-two-mobile.png', outputDirectory)), fullPage: true });
await page.route('**/wp-admin/admin-post.php', (route) => route.fulfill({ contentType: 'application/json', body: JSON.stringify({ success: true, data: { reference: 'LOCAL-REVIEW-001' } }) }));
const reservationForm = page.locator('[data-reservation-form]');
await reservationForm.locator('input[name="first_name"]').fill('Local');
await reservationForm.locator('input[name="last_name"]').fill('Review');
await reservationForm.locator('input[name="phone"]').fill('+39000000000');
await reservationForm.locator('input[name="email"]').fill('local-review@example.invalid');
await reservationForm.locator('input[name="terms"]').check();
await reservationForm.locator('input[name="privacy"]').check();
await reservationForm.locator('button[type="submit"]').click();
await page.locator('[data-reservation-success]').waitFor();
await page.screenshot({ path: fileURLToPath(new URL('reservation-success-mobile.png', outputDirectory)), fullPage: true });

await browser.close();
