import { expect, test } from '@playwright/test';
import { mkdir } from 'node:fs/promises';
import { resolve } from 'node:path';

const base = process.env.PLAYWRIGHT_BASE_URL || 'http://rentacar-venezia-local.local';
const matrix: Array<[string, number]> = [
  ['/en/car-rental-pickup-locations-in-venice-and-treviso/', 1440], ['/en/car-rental-pickup-locations-in-venice-and-treviso/', 320],
  ['/en/venice-marco-polo-airport-car-rental-2/', 1440], ['/treviso-airport-car-rental/', 1024],
  ['/punti-di-ritiro-auto-a-venezia-e-treviso/stazione-treviso-gd-rent-a-car/', 1024], ['/ro/locatii-de-preluare-a-masinii-in-venetia-si-treviso/gara-venezia-mestre-gd-rent-a-car/', 768],
  ['/ru/%D0%BC%D0%B5%D1%81%D1%82%D0%B0-%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D1%8F-%D0%B0%D0%B2%D1%82%D0%BE%D0%BC%D0%BE%D0%B1%D0%B8%D0%BB%D1%8F-%D0%B2-%D0%B2%D0%B5%D0%BD%D0%B5%D1%86%D0%B8%D0%B8/%D0%BF%D1%8C%D1%8F%D1%86%D1%86%D0%B0%D0%BB%D0%B5-%D1%80%D0%BE%D0%BC%D0%B0-gd-rent-a-car/', 390],
  ['/ro/locatii-de-preluare-a-masinii-in-venetia-si-treviso/hotel-in-treviso-gd-rent-a-car/', 430], ['/ru/%D0%BC%D0%B5%D1%81%D1%82%D0%B0-%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D1%8F-%D0%B0%D0%B2%D1%82%D0%BE%D0%BC%D0%BE%D0%B1%D0%B8%D0%BB%D1%8F-%D0%B2-%D0%B2%D0%B5%D0%BD%D0%B5%D1%86%D0%B8%D0%B8/%D0%BE%D1%82%D0%B5%D0%BB%D1%8C-%D0%B2-%D0%B2%D0%B5%D0%BD%D0%B5%D1%86%D0%B8%D0%B8-gd-rent-a-car/', 320],
  ['/en/economy-rental-cars/', 1440], ['/ro/masini-automate-de-inchiriat/', 430], ['/ru/%D0%B0%D0%B2%D1%82%D0%BE%D0%BC%D0%BE%D0%B1%D0%B8%D0%BB%D0%B8-%D0%BD%D0%B0%D0%BF%D1%80%D0%BE%D0%BA%D0%B0%D1%82-%D0%BD%D0%B0-7-%D0%BC%D0%B5%D1%81%D1%82/', 390], ['/auto-a-9-posti-a-noleggio/', 768], ['/en/family-rental-cars/', 320],
  ['/', 1440], ['/en/fleet/', 1024], ['/en/cars/fiat-tipo/', 768], ['/en/contact/', 390], ['/en/guides-2/', 390],
];

test('all representative landing templates remain responsive and navigable', async ({ page }) => {
  const errors: string[] = [];
  page.on('pageerror', error => errors.push(error.message));
  for (const [path, width] of matrix) {
    await page.setViewportSize({ width, height: 900 });
    await page.goto(`${base}${path}`, { waitUntil: 'networkidle' });
    await expect(page.locator('h1')).toHaveCount(1);
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
    await expect(page.locator('.site-header')).toBeVisible();
    await expect(page.locator('.site-footer')).toBeVisible();
    expect(await page.locator('img:not([loading="lazy"])').evaluateAll(items => items.every(image => image.complete && image.naturalWidth > 0))).toBeTruthy();
  }
  expect(errors).toEqual([]);
});

test('captures the final design-lead review set', async ({ page }) => {
  const output = resolve(process.cwd(), 'docs/generated/final-acceptance'); await mkdir(output, { recursive: true });
  const shots: Array<[string, string, number]> = [
    ['pickup-hub-desktop', '/en/car-rental-pickup-locations-in-venice-and-treviso/', 1440], ['pickup-hub-mobile', '/en/car-rental-pickup-locations-in-venice-and-treviso/', 390],
    ['treviso-station-desktop', '/punti-di-ritiro-auto-a-venezia-e-treviso/stazione-treviso-gd-rent-a-car/', 1440], ['treviso-station-mobile', '/punti-di-ritiro-auto-a-venezia-e-treviso/stazione-treviso-gd-rent-a-car/', 390],
    ['piazzale-roma-mobile', '/ru/%D0%BC%D0%B5%D1%81%D1%82%D0%B0-%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D1%8F-%D0%B0%D0%B2%D1%82%D0%BE%D0%BC%D0%BE%D0%B1%D0%B8%D0%BB%D1%8F-%D0%B2-%D0%B2%D0%B5%D0%BD%D0%B5%D1%86%D0%B8%D0%B8/%D0%BF%D1%8C%D1%8F%D1%86%D1%86%D0%B0%D0%BB%D0%B5-%D1%80%D0%BE%D0%BC%D0%B0-gd-rent-a-car/', 390],
    ['economy-desktop', '/en/economy-rental-cars/', 1440], ['economy-mobile', '/en/economy-rental-cars/', 390], ['automatic-mobile', '/ro/masini-automate-de-inchiriat/', 390],
  ];
  for (const [name, path, width] of shots) { await page.setViewportSize({ width, height: 900 }); await page.goto(`${base}${path}`, { waitUntil: 'networkidle' }); await page.screenshot({ path: resolve(output, `${name}.png`), fullPage: true }); }
});
