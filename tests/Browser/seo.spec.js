import { test, expect } from '@playwright/test';

test('all pages expose consistent metadata, schema identities and reachable internal links', async ({ page, request }) => {
    const paths = ['/', '/about', '/trade', '/how-we-work', '/markets', '/contact', '/privacy', '/terms'];
    const titles = new Set();
    const descriptions = new Set();
    const links = new Set();
    for (const path of paths) {
        const response = await page.goto(`${path}?utm_source=seo-check`);
        expect(response.status()).toBe(200);
        const metadata = await page.evaluate(() => {
            const content = selector => document.querySelector(selector)?.content;
            const canonical = document.querySelector('link[rel="canonical"]').getAttribute('href');
            const schema = JSON.parse(document.querySelector('script[type="application/ld+json"]').textContent);
            return {
                title: document.title, description: content('meta[name="description"]'), canonical,
                ogUrl: content('meta[property="og:url"]'), image: content('meta[property="og:image"]'),
                imageAlt: content('meta[property="og:image:alt"]'), twitterImage: content('meta[name="twitter:image"]'),
                scripts: document.querySelectorAll('script[type="application/ld+json"]').length,
                pageUrl: schema['@graph'].find(node => node['@id'] === `${canonical}#webpage`)?.url,
                links: [...document.querySelectorAll('a[href]')].map(link => link.href),
                imagesLoaded: [...document.images].filter(image => image.loading !== 'lazy').every(image => image.complete && image.naturalWidth > 0),
            };
        });
        expect(new URL(metadata.canonical).href).toBe(new URL(path, page.url()).href);
        expect(metadata.ogUrl).toBe(metadata.canonical);
        expect(metadata.pageUrl).toBe(metadata.canonical);
        expect(metadata.scripts).toBe(1);
        expect(metadata.imageAlt).toBeTruthy();
        expect(metadata.twitterImage).toBe(metadata.image);
        expect(metadata.imagesLoaded).toBe(true);
        expect(titles.has(metadata.title)).toBe(false);
        expect(descriptions.has(metadata.description)).toBe(false);
        titles.add(metadata.title);
        descriptions.add(metadata.description);
        for (const href of metadata.links) {
            const url = new URL(href);
            if (url.origin === new URL(page.url()).origin) {
                url.hash = '';
                links.add(url.href);
            }
        }
        links.add(metadata.image);
    }
    for (const href of links) {
        const response = await request.get(href);
        expect(response.status(), href).toBe(200);
    }
});

test('contact controls and legal breadcrumbs have accessible descriptions', async ({ page }) => {
    await page.goto('/contact');
    await expect(page.getByLabel('Message', { exact: true })).toHaveAccessibleDescription(/Do not include sensitive financial information/);
    for (const path of ['/privacy', '/terms']) {
        await page.goto(path);
        const heading = await page.locator('h1').textContent();
        await expect(page.getByRole('navigation', { name: 'Breadcrumb' }).locator('[aria-current="page"]')).toHaveText(heading);
    }
});
