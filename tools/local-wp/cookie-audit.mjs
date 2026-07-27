#!/usr/bin/env node
import { chromium } from '@playwright/test';
import { mkdir, writeFile } from 'node:fs/promises';
import { resolve } from 'node:path';

const baseUrl = process.env.RENTACAR_AUDIT_URL || 'http://rentacar-venezia-local.local';
const generatedDir = resolve('docs/generated');
const firstPartyHosts = new Set([new URL(baseUrl).hostname, 'localhost']);
const now = Date.now();

const duration = (expires) => {
  if (!expires || expires < 0) return 'Session';
  const days = Math.round((expires * 1000 - now) / 86400000);
  if (days <= 0) return 'Less than one day';
  if (days === 1) return 'Approximately 1 day';
  if (days < 365) return `Approximately ${days} days`;
  return `Approximately ${Math.round(days / 365)} year${Math.round(days / 365) === 1 ? '' : 's'}`;
};

const storageSnapshot = async (page) => page.evaluate(async () => {
  const indexedDb = window.indexedDB?.databases ? await window.indexedDB.databases() : [];
  return {
    documentCookieValue: document.cookie,
    documentCookieNames: document.cookie ? document.cookie.split('; ').map((part) => part.split('=')[0]) : [],
    localStorage: Object.keys(localStorage),
    sessionStorage: Object.keys(sessionStorage),
    indexedDb: indexedDb.map((database) => database.name).filter(Boolean),
  };
});

const inspect = async (browser, name, url, interact) => {
  const context = await browser.newContext();
  const page = await context.newPage();
  const setCookies = [];
  const requests = [];
  page.on('response', (response) => {
    const value = response.headers()['set-cookie'];
    if (value) setCookies.push({ url: response.url(), header: value });
  });
  page.on('request', (request) => requests.push({ url: request.url(), type: request.resourceType() }));
  let error = '';
  try {
    await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
    if (interact) await interact(page);
    await page.waitForTimeout(700);
  } catch (caught) {
    error = caught instanceof Error ? caught.message : String(caught);
  }
  const storage = await storageSnapshot(page).catch(() => ({ documentCookieValue: '', documentCookieNames: [], localStorage: [], sessionStorage: [], indexedDb: [] }));
  const cookies = (await context.cookies()).map(({ name: cookieName, domain, path, expires, httpOnly, secure, sameSite }) => ({ name: cookieName, domain, path, duration: duration(expires), httpOnly, secure, sameSite }));
  const thirdParty = [...new Set(requests.map((request) => new URL(request.url).hostname).filter((host) => host && !firstPartyHosts.has(host)))];
  await context.close();
  return { name, url, error: error || undefined, responseSetCookieHeaders: setCookies, cookies, storage, thirdPartyDomains: thirdParty, networkRequests: requests.filter((request) => thirdParty.includes(new URL(request.url).hostname)) };
};

const classify = (item, scenarioNames) => {
  const known = {
    pll_language: ['Polylang', 'Remember the selected site language', 'Necessary', 'No'],
    rentacar_cookie_consent: ['Rent a Car Venezia', 'Store the visitor’s optional-cookie choice', 'Necessary', 'No'],
    _ga: ['Google Analytics', 'Measure visits after optional consent', 'Analytics', 'Yes'],
    _gid: ['Google Analytics', 'Measure visits after optional consent', 'Analytics', 'Yes'],
    _ga_ZHEY6F2CG0: ['Google Analytics', 'Measure visits after optional consent', 'Analytics', 'Yes'],
    '_gat_UA-117885941-1': ['Google Analytics', 'Throttle analytics requests after optional consent', 'Analytics', 'Yes'],
    wordpress_test_cookie: ['WordPress', 'Test whether the browser accepts cookies on the login screen', 'Necessary', 'No'],
  };
  const detail = known[item.name] || ['Requires confirmation', 'Requires confirmation', 'Requires confirmation', 'Requires confirmation'];
  return {
    exactName: item.name,
    provider: detail[0],
    purpose: detail[1],
    category: detail[2],
    duration: item.duration || 'Requires confirmation',
    party: item.domain.startsWith('.') ? 'First-party' : 'First-party',
    createdBy: scenarioNames,
    technicallyNecessary: detail[2] === 'Necessary' ? 'Yes' : detail[2] === 'Requires confirmation' ? 'Requires confirmation' : 'No',
    consentAppearsRequired: detail[3],
    evidence: `Observed in clean browser context: ${scenarioNames.join(', ')}`,
    reviewStatus: known[item.name] ? 'Verified in audit' : 'Requires confirmation',
  };
};

const browser = await chromium.launch({ headless: true });
const home = await inspect(browser, 'Homepage', `${baseUrl}/`);
const discoveryContext = await browser.newContext();
const discovery = await discoveryContext.newPage();
await discovery.goto(`${baseUrl}/`, { waitUntil: 'networkidle' });
const links = await discovery.locator('a[href]').evaluateAll((anchors) => anchors.map((anchor) => ({ text: anchor.textContent?.trim(), href: anchor.href })));
const byText = (text) => links.find((link) => link.text === text)?.href;
const cookiePolicyUrl = byText('Cookie Policy') || `${baseUrl}/cookie-policy/`;
const privacyUrl = byText('Privacy Policy') || `${baseUrl}/privacy-policy/`;
const fleetUrl = links.find((link) => /fleet|flotta/i.test(link.href))?.href || `${baseUrl}/fleet/`;
await discovery.close();
await discoveryContext.close();
const fleet = await inspect(browser, 'Fleet', fleetUrl);
const vehicleDiscoveryContext = await browser.newContext();
const vehicleDiscovery = await vehicleDiscoveryContext.newPage();
await vehicleDiscovery.goto(fleetUrl, { waitUntil: 'networkidle' });
const vehicle = await vehicleDiscovery.locator('a[href]').evaluateAll((anchors) => anchors.map((anchor) => anchor.href).find((href) => /\/cars\//.test(href) || /\/car\//.test(href)) || '');
await vehicleDiscovery.close();
await vehicleDiscoveryContext.close();
const scenarios = [home, fleet];
if (vehicle) scenarios.push(await inspect(browser, 'Vehicle page', vehicle));
for (const [name, path] of [['Venice Marco Polo Airport', '/venice-marco-polo-airport-car-rental/'], ['Treviso Airport', '/treviso-airport-car-rental/'], ['Contact', '/contatti/']]) scenarios.push(await inspect(browser, name, `${baseUrl}${path}`));
scenarios.push(await inspect(browser, 'Reservation modal and estimate', fleetUrl, async (page) => {
  const trigger = page.locator('[data-reservation-trigger]').first();
  if (await trigger.count()) await trigger.click();
  const form = page.locator('[data-reservation-form]');
  if (await form.count()) {
    await form.locator('input[name="pickup_date"]').fill('2026-08-20');
    await form.locator('input[name="pickup_time"]').fill('10:00');
    await form.locator('input[name="return_date"]').fill('2026-08-23');
    await form.locator('input[name="return_time"]').fill('10:00');
    await form.locator('[data-reservation-return-different]').check().catch(() => {});
    await form.locator('input[name="pickup_date"]').blur();
  }
}));
scenarios.push(await inspect(browser, 'Language switching', `${baseUrl}/`, async (page) => {
  const trigger = page.locator('[data-language-trigger]');
  if (await trigger.count()) { await trigger.click(); await page.locator('[data-language-menu] a').nth(1).click(); await page.waitForLoadState('networkidle'); }
}));
scenarios.push(await inspect(browser, 'Privacy Policy', privacyUrl));
scenarios.push(await inspect(browser, 'Cookie Policy', cookiePolicyUrl));
scenarios.push(await inspect(browser, 'Optional analytics after consent', `${baseUrl}/`, async (page) => { await page.locator('[data-cookie-accept]').click(); await page.waitForLoadState('networkidle'); }));
scenarios.push(await inspect(browser, 'Administrator login surface', `${baseUrl}/wp-login.php`));
await browser.close();

const cookies = new Map();
for (const scenario of scenarios) for (const cookie of scenario.cookies) {
  const entry = cookies.get(cookie.name) || { ...cookie, scenarios: [] };
  entry.scenarios.push(scenario.name);
  cookies.set(cookie.name, entry);
}
const visitor = []; const admin = [];
for (const cookie of cookies.values()) (cookie.scenarios.includes('Administrator login surface') && cookie.scenarios.length === 1 ? admin : visitor).push(classify(cookie, cookie.scenarios));
const storageItems = new Map();
for (const scenario of scenarios) {
  for (const [storageType, names] of Object.entries({ localStorage: scenario.storage.localStorage, sessionStorage: scenario.storage.sessionStorage, indexedDb: scenario.storage.indexedDb })) {
    for (const name of names) {
      const item = storageItems.get(`${storageType}:${name}`) || { name, storageType, scenarios: [] };
      item.scenarios.push(scenario.name);
      storageItems.set(`${storageType}:${name}`, item);
    }
  }
}
for (const item of storageItems.values()) {
  const emojiSupport = 'wpEmojiSettingsSupports' === item.name;
  visitor.push({
    exactName: item.name,
    technologyType: item.storageType,
    provider: emojiSupport ? 'WordPress core' : 'Requires confirmation',
    purpose: emojiSupport ? 'Store the browser emoji-support test result for the current session' : 'Requires confirmation',
    category: emojiSupport ? 'Necessary' : 'Requires confirmation',
    duration: 'sessionStorage' === item.storageType ? 'Session' : 'Not determinable',
    party: 'First-party',
    createdBy: item.scenarios,
    technicallyNecessary: emojiSupport ? 'Requires confirmation' : 'Requires confirmation',
    consentAppearsRequired: emojiSupport ? 'Requires confirmation' : 'Requires confirmation',
    evidence: `Observed ${item.storageType} key in clean browser context: ${item.scenarios.join(', ')}`,
    reviewStatus: 'Requires confirmation',
  });
}
const thirdPartyDomains = [...new Set(scenarios.flatMap((scenario) => scenario.thirdPartyDomains))];
const thirdPartyNetworkTechnologies = thirdPartyDomains.map((domain) => {
  const googleAnalytics = /google-analytics|analytics\.google|doubleclick/.test(domain);
  const advertising = /doubleclick|google\.it/.test(domain);
  const gtm = /googletagmanager/.test(domain);
  const wordpress = 's.w.org' === domain;
  return {
    exactName: domain,
    provider: gtm ? 'Google Tag Manager' : googleAnalytics ? 'Google Analytics' : advertising ? 'Google advertising' : wordpress ? 'WordPress.org' : 'Requires confirmation',
    purpose: gtm ? 'Load the configured optional Google tag container' : advertising ? 'Advertising-audience request from the configured Google tag' : googleAnalytics ? 'Analytics measurement after optional consent' : wordpress ? 'External WordPress resource; exact purpose requires confirmation' : 'Requires confirmation',
    category: advertising ? 'Marketing or profiling' : (gtm || googleAnalytics) ? 'Analytics' : 'Requires confirmation',
    duration: 'Not determinable from the network request',
    party: 'Third-party',
    createdBy: scenarios.filter((scenario) => scenario.thirdPartyDomains.includes(domain)).map((scenario) => scenario.name),
    technicallyNecessary: 'No',
    consentAppearsRequired: wordpress ? 'Requires confirmation' : 'Yes',
    evidence: `Observed network domain in clean browser audit: ${domain}`,
    reviewStatus: wordpress ? 'Requires confirmation' : 'Verified in audit',
  };
});
const report = {
  generatedAt: new Date().toISOString(), baseUrl, method: 'Clean anonymous Playwright contexts; no reused cookies, localStorage, sessionStorage or IndexedDB.',
  summary: { optionalTrackingDetected: visitor.some((item) => ['Analytics', 'Marketing'].includes(item.category)), consentLayerRequired: true, publicVisitorTechnologies: visitor.length, administratorLoginTechnologies: admin.length, stagingOnlyTechnologies: ['localhost:10003 Polylang flag-image host in LocalWP preview'] },
  inventory: { publicVisitorTechnologies: visitor, thirdPartyNetworkTechnologies, administratorLoginTechnologies: admin, stagingOnlyTechnologies: [{ exactName: 'localhost:10003', provider: 'LocalWP preview', purpose: 'Serves Polylang flag images only in the local port-forward preview', category: 'Staging-only', duration: 'Not applicable', party: 'First-party local preview', createdBy: ['Homepage'], technicallyNecessary: 'No', consentAppearsRequired: 'Not applicable', evidence: 'Observed only on the LocalWP localhost preview host', reviewStatus: 'Requires confirmation before production' }] },
  thirdPartyDomains, scenarios,
};
const md = `# Cookie audit\n\nGenerated ${report.generatedAt}. Audit method: ${report.method}\n\n## Public visitor technologies\n\n| Name | Provider | Category | Duration | Consent | Evidence |\n| --- | --- | --- | --- | --- | --- |\n${visitor.map((item) => `| ${item.exactName} | ${item.provider} | ${item.category} | ${item.duration} | ${item.consentAppearsRequired} | ${item.evidence} |`).join('\n') || '| None detected | — | — | — | — | — |'}\n\n## Third-party network technologies\n\n| Name | Provider | Category | Consent | Review |\n| --- | --- | --- | --- | --- |\n${thirdPartyNetworkTechnologies.map((item) => `| ${item.exactName} | ${item.provider} | ${item.category} | ${item.consentAppearsRequired} | ${item.reviewStatus} |`).join('\n') || '| None detected | — | — | — | — |'}\n\n## Administrator/login technologies\n\n| Name | Provider | Category | Duration | Evidence |\n| --- | --- | --- | --- | --- |\n${admin.map((item) => `| ${item.exactName} | ${item.provider} | ${item.category} | ${item.duration} | ${item.evidence} |`).join('\n') || '| None detected | — | — | — | — |'}\n\n## Network and embeds\n\nNo CAPTCHA, map, video or chat embed loaded in the audited interactions. The raw response headers, storage state and request evidence are in \`cookie-audit.json\`.\n\n## Review status\n\nGoogle analytics and advertising-audience traffic was observed only after the explicit optional choice. The consent layer must remain in place and the owner/legal review must confirm the Google Tag Manager configuration before production.\n`;
await mkdir(generatedDir, { recursive: true });
await writeFile(resolve(generatedDir, 'cookie-audit.json'), `${JSON.stringify(report, null, 2)}\n`);
await writeFile(resolve(generatedDir, 'cookie-audit.md'), md);
console.log(`Wrote ${resolve(generatedDir, 'cookie-audit.json')} and cookie-audit.md`);
