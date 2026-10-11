import { test, expect } from '@playwright/test';
import { readFile } from 'node:fs/promises';
import AxeBuilder from '@axe-core/playwright';

const source = await readFile(new URL('../../resources/js/analytics.js', import.meta.url), 'utf8');
const key = 'juanovio-analytics-consent';

async function setup(page, driver = 'gtm', stored, unavailable = false) {
    await page.route('https://www.googletagmanager.com/**', route => route.fulfill({ body: '' }));
    await page.goto('/');
    await page.evaluate(({ key, stored }) => {
        localStorage.removeItem(key);
        if (stored) localStorage.setItem(key, JSON.stringify(stored));
    }, { key, stored });
    await page.setContent(`<!doctype html><html lang="en"><head><title>Analytics test</title>
        <script id="analytics-settings" type="application/json">${JSON.stringify({ driver, id: driver === 'gtm' ? 'GTM-TEST123' : 'G-TEST123', version: '2026-10-11', maxAgeDays: 180 })}</script>
        </head><body><main><h1>Analytics test</h1>
        <button data-analytics-preferences>Analytics preferences</button>
        <section data-analytics-banner hidden aria-label="Analytics choice">
        <button data-analytics-choice="accept">Allow analytics</button>
        <button data-analytics-choice="reject">Reject analytics</button>
        <p data-analytics-status role="status"></p></section></main></body></html>`);
    if (unavailable) await page.evaluate(() => {
        Object.defineProperty(window, 'localStorage', { get() { throw new Error('Storage unavailable'); } });
    });
    await page.addScriptTag({ content: source });
}

for (const driver of ['gtm', 'gtag']) {
    test(`${driver}: consent gates the single provider and lead payload`, async ({ page }) => {
        await setup(page, driver);
        await expect(page.locator('[data-analytics-banner]')).toBeVisible();
        await expect(page.locator('[data-analytics-loader]')).toHaveCount(0);
        await page.evaluate(() => document.dispatchEvent(new CustomEvent('inquiry-submitted', { detail: { inquiry_type: 'buy' } })));
        expect(await page.evaluate(() => window.dataLayer.some(entry => entry.event === 'generate_lead' || entry[1] === 'generate_lead'))).toBe(false);
        await page.getByRole('button', { name: 'Allow analytics' }).click();
        await expect(page.locator('[data-analytics-loader]')).toHaveCount(1);
        await expect(page.locator('[data-analytics-loader]')).toHaveAttribute('data-analytics-loader', driver);
        await page.evaluate(() => {
            document.dispatchEvent(new Event('livewire:navigated'));
            document.dispatchEvent(new Event('livewire:navigated'));
            document.dispatchEvent(new CustomEvent('inquiry-submitted', { detail: { inquiry_type: 'buy', email: 'private@example.test', message: 'PRIVATE' } }));
        });
        const events = await page.evaluate(() => window.dataLayer.map(entry => entry.event ? entry : Array.from(entry)));
        const leads = events.filter(entry => entry.event === 'generate_lead' || entry[1] === 'generate_lead');
        expect(leads).toHaveLength(1);
        expect(JSON.stringify(leads)).not.toContain('PRIVATE');
        expect(JSON.stringify(leads)).not.toContain('private@example.test');
        expect(events.filter(entry => entry.event === 'juanovio_page_view' || entry[1] === 'page_view')).toHaveLength(1);
        expect(events[0][0]).toBe('consent');
        expect(events[0][2].analytics_storage).toBe('denied');
        expect(events[1][1]).toBe('ads_data_redaction');
        expect(events[2][2].ad_storage).toBe('denied');
        await page.getByRole('button', { name: 'Analytics preferences' }).click();
        await expect(page.getByRole('button', { name: 'Allow analytics' })).toBeFocused();
        expect((await new AxeBuilder({ page }).analyze()).violations).toEqual([]);
        await page.getByRole('button', { name: 'Reject analytics' }).click();
        await page.waitForLoadState();
        expect(await page.evaluate(key => JSON.parse(localStorage.getItem(key)).analytics, key)).toBe(false);
        await expect(page.locator('[data-analytics-loader]')).toHaveCount(0);
    });
}

test('rejection, expired and versioned preferences do not load tags', async ({ page }) => {
    for (const stored of [
        { version: '2026-10-11', analytics: false, decidedAt: Date.now() },
        { version: 'old', analytics: true, decidedAt: Date.now() },
        { version: '2026-10-11', analytics: true, decidedAt: Date.now() - 181 * 86400000 },
    ]) {
        await setup(page, 'gtm', stored);
        await expect(page.locator('[data-analytics-loader]')).toHaveCount(0);
    }
});

test('saved acceptance loads once; unavailable storage keeps the page usable', async ({ page }) => {
    await setup(page, 'gtm', { version: '2026-10-11', analytics: true, decidedAt: Date.now() });
    await expect(page.locator('[data-analytics-loader]')).toHaveCount(1);
    await setup(page, 'gtm', undefined, true);
    await expect(page.locator('[data-analytics-loader]')).toHaveCount(0);
    await page.getByRole('button', { name: 'Reject analytics' }).click();
    await expect(page.locator('[data-analytics-loader]')).toHaveCount(0);
});
