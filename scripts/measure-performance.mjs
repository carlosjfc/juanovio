import { chromium } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';

const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));
const css = readFileSync(`public/build/${manifest['resources/css/app.css'].file}`);
const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'https://juanovio.test';
const browser = await chromium.launch();
const results = [];
try {
    for (let run = 1; run <= 3; run++) {
        const context = await browser.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, deviceScaleFactor: 2 });
        const page = await context.newPage();
        await page.addInitScript(() => {
            window.pageMetrics = { lcp: 0, cls: 0 };
            new PerformanceObserver(list => {
                for (const entry of list.getEntries()) window.pageMetrics.lcp = entry.startTime;
            }).observe({ type: 'largest-contentful-paint', buffered: true });
            new PerformanceObserver(list => {
                for (const entry of list.getEntries()) if (!entry.hadRecentInput) window.pageMetrics.cls += entry.value;
            }).observe({ type: 'layout-shift', buffered: true });
        });
        await page.goto(baseURL, { waitUntil: 'networkidle' });
        results.push(await page.evaluate(() => ({
            ...window.pageMetrics,
            fcp: performance.getEntriesByName('first-contentful-paint')[0]?.startTime,
            domReady: performance.getEntriesByType('navigation')[0].domContentLoadedEventEnd,
        })));
        await context.close();
    }
    console.log(JSON.stringify({
        conditions: 'Fresh Chromium contexts; mobile viewport; no network or CPU throttling; local lab measurements, not field Core Web Vitals',
        cssBytes: css.length, gzipBytes: gzipSync(css).length, runs: results,
    }, null, 2));
} finally {
    await browser.close();
}
