import { expect, test } from '@playwright/test';

type ScriptResponseIssue = {
  contentType: string;
  preview: string;
  route: string;
  status: number;
  url: string;
};

type InlineScriptIssue = {
  index: number;
  route: string;
  type: string;
  url: string;
};

function responsePreview(body: string): string {
  return /<!doctype\s+html|<html\b/i.test(body) ? '[HTML document returned]' : '[non-JavaScript response returned]';
}

function isJavaScriptContentType(contentType: string): boolean {
  return /^(application|text)\/(javascript|x-javascript|ecmascript)(?:;|$)/i.test(contentType);
}

test.describe('production smoke', () => {
  test('serves the public homepage and fleet page without page errors', async ({ page }) => {
    const pageErrors: string[] = [];
    const requestFailures: string[] = [];
    const invalidScriptResponses: ScriptResponseIssue[] = [];
    const invalidInlineScripts: InlineScriptIssue[] = [];
    let currentRoute = '';

    page.on('pageerror', (error) => {
      pageErrors.push(`${currentRoute || '(before navigation)'} | ${page.url()} | ${error.name}: ${error.message}${error.stack ? `\n${error.stack}` : ''}`);
    });
    page.on('requestfailed', (request) => {
      if (request.resourceType() === 'script') {
        requestFailures.push(`${currentRoute || '(before navigation)'} | ${request.url()} | script | ${request.failure()?.errorText || 'unknown failure'}`);
      }
    });
    page.on('response', async (response) => {
      if (response.request().resourceType() !== 'script') return;

      const contentType = response.headers()['content-type'] || '(missing content type)';
      if (response.status() === 304) return;
      if (response.status() < 400 && isJavaScriptContentType(contentType)) return;

      let preview = '[response body unavailable]';
      try {
        preview = responsePreview(await response.text());
      } catch {
        // The status and content type still identify the failed script safely.
      }
      invalidScriptResponses.push({ route: currentRoute || '(before navigation)', url: response.url(), status: response.status(), contentType, preview });
    });

    for (const route of ['/', '/fleet/']) {
      currentRoute = route;
      // Wait for the route's script resources before moving to the next route.
      // Navigating at DOMContentLoaded aborts late WordPress core scripts (such
      // as wp-emoji-release) and turns a navigation race into a false failure.
      const response = await page.goto(route, { waitUntil: 'load' });
      expect(response?.status(), `${route} response`).toBe(200);
      await expect(page.locator('body')).toBeVisible();
      expect(await page.title(), `${route} title`).not.toBe('');
      const phpInlineScripts = await page.locator('script:not([src])').evaluateAll((scripts) => scripts
        .map((script, index) => ({ index, type: script.type || 'text/javascript', containsPhp: /<\?php\b/i.test(script.textContent || '') }))
        .filter((script) => script.containsPhp)
        .map(({ index, type }) => ({ index, type })));
      invalidInlineScripts.push(...phpInlineScripts.map((script) => ({ ...script, route, url: page.url() })));
    }

    const diagnostics = [
      `Current URL: ${page.url()}`,
      pageErrors.length ? `Page errors:\n${pageErrors.join('\n')}` : '',
      requestFailures.length ? `Failed script requests:\n${requestFailures.join('\n')}` : '',
      invalidScriptResponses.length
        ? `Invalid script responses:\n${invalidScriptResponses.map((issue) => `${issue.route} | ${issue.status} | ${issue.contentType} | ${issue.preview} | ${issue.url}`).join('\n')}`
        : '',
      invalidInlineScripts.length
        ? `Invalid inline scripts:\n${invalidInlineScripts.map((issue) => `${issue.route} | ${issue.url} | script #${issue.index} | ${issue.type} | [PHP source emitted as JavaScript]`).join('\n')}`
        : '',
    ].filter(Boolean).join('\n\n');

    expect(pageErrors, diagnostics).toEqual([]);
    expect(requestFailures, diagnostics).toEqual([]);
    expect(invalidScriptResponses, diagnostics).toEqual([]);
    expect(invalidInlineScripts, diagnostics).toEqual([]);
  });
});
