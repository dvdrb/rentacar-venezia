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
await capture('fleet-desktop', '/fleet/');
await capture('contact-mobile', '/contatti/', { width: 390, height: 844 });
await capture('terms-desktop', '/terms-and-conditions/');
await capture('how-it-works-mobile', '/how-it-works/', { width: 390, height: 844 });
await page.setViewportSize({ width: 390, height: 844 });
await page.goto(new URL('/', baseUrl).toString(), { waitUntil: 'networkidle' });
await page.locator('[data-reservation-trigger]').first().click();
await page.screenshot({ path: fileURLToPath(new URL('reservation-modal-mobile.png', outputDirectory)), fullPage: true });

await browser.close();
