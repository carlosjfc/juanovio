import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('CTA arrows are decorative SVGs rather than platform-dependent glyphs', async ({ page }) => {
    for (const path of ['/', '/trade']) {
        await page.goto(path);
        const links = page.locator('.text-link');
        expect(await links.count()).toBeGreaterThan(0);
        for (const link of await links.all()) {
            await expect(link).not.toContainText('↗');
            await expect(link.locator('svg.cta-arrow')).toHaveCount(1);
            await expect(link.locator('svg')).toHaveAttribute('aria-hidden', 'true');
            expect(await link.evaluate(element => {
                const icon = element.querySelector('svg');
                const size = icon.getBoundingClientRect();
                return Math.abs(size.width - parseFloat(getComputedStyle(element).fontSize)) < 1 && Math.abs(size.width - size.height) < 1;
            })).toBe(true);
        }
        await expect(page.getByRole('link', { name: path === '/' ? 'Explore What We Trade' : 'Introduce Your Supply', exact: true })).toBeVisible();
    }
});

test('back to top appears after scrolling and supports keyboard and reduced motion', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    const button = page.getByRole('button', { name: 'Back to top', exact: true });
    await expect(button).toBeHidden();
    await page.evaluate(() => window.scrollTo(0, 800));
    await expect(button).toBeVisible();
    if (page.viewportSize().width <= 640) {
        const spacing = await button.evaluate(element => {
            const container = getComputedStyle(element.closest('.back-to-top'));
            const bounds = element.getBoundingClientRect();
            return { bottom: parseFloat(container.bottom), right: parseFloat(container.right), width: bounds.width, height: bounds.height };
        });
        expect(spacing.bottom).toBeGreaterThanOrEqual(64);
        expect(spacing.right).toBeGreaterThanOrEqual(24);
        expect(spacing.width).toBeGreaterThanOrEqual(48);
        expect(spacing.height).toBeGreaterThanOrEqual(48);
    }
    const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
    expect(results.violations).toEqual([]);
    await button.focus();
    await page.keyboard.press('Enter');
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(0);
    await expect(page.locator('#page-top')).toBeFocused();
    expect(await page.locator('#page-top').evaluate(header => getComputedStyle(header).outlineStyle)).toBe('none');
    await expect(button).toBeHidden();
    await page.keyboard.press('Tab');
    await expect(page.locator('.site-header .brand')).toBeFocused();
    expect(await page.locator('.site-header .brand').evaluate(link => getComputedStyle(link).outlineStyle)).toBe('solid');
});

test('menu navigation followed by back-to-top does not outline the header', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    if (await page.locator('.mobile-nav').isVisible()) {
        await page.getByRole('button', { name: 'Open navigation menu' }).click();
        await page.getByRole('menuitem', { name: 'What We Trade' }).click();
    } else {
        await page.getByRole('navigation', { name: 'Primary navigation' }).getByRole('link', { name: 'What We Trade' }).click();
    }
    await expect(page).toHaveURL(/\/trade$/);
    await page.evaluate(() => window.scrollTo(0, 800));
    await page.getByRole('button', { name: 'Back to top', exact: true }).click();
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(0);
    await expect(page.locator('#page-top')).toBeFocused();
    expect(await page.locator('#page-top').evaluate(header => getComputedStyle(header).outlineStyle)).toBe('none');
});

test('reopening the mobile menu after back-to-top does not outline the panel', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.goto('/');
    const trigger = page.getByRole('button', { name: 'Open navigation menu' });
    test.skip(!await trigger.isVisible(), 'Mobile navigation only');
    await trigger.click();
    await page.getByRole('menuitem', { name: 'What We Trade' }).click();
    await page.evaluate(() => window.scrollTo(0, 800));
    await page.getByRole('button', { name: 'Back to top', exact: true }).click();
    await expect.poll(() => page.evaluate(() => window.scrollY)).toBe(0);
    await trigger.click();
    const menu = page.getByRole('menu', { name: 'Mobile navigation' });
    await expect(menu).toBeVisible();
    expect(await menu.evaluate(element => getComputedStyle(element).outlineStyle)).toBe('none');
    await page.keyboard.press('ArrowDown');
    const active = menu.locator('[data-active]');
    await expect(active).toHaveCount(1);
    expect(await active.evaluate(element => getComputedStyle(element).outlineStyle)).toBe('solid');
    await page.keyboard.press('Escape');
    await expect(menu).toBeHidden();
    await expect(trigger).toBeFocused();
});

for (const path of ['/', '/about', '/trade', '/how-we-work', '/markets', '/contact', '/privacy', '/terms']) {
    test(`${path} is accessible and fits the viewport`, async ({ page }, testInfo) => {
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        const response = await page.goto(path);
        expect(response.status()).toBe(200);
        await page.evaluate(() => document.fonts.ready);
        await expect(page.locator('h1')).toHaveCount(1);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
        const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
        expect(results.violations).toEqual([]);
        expect(errors).toEqual([]);
        await page.screenshot({ path: testInfo.outputPath('page.png'), fullPage: true });
    });
}

test('keyboard navigation exposes the skip link and mobile menu', async ({ page }) => {
    await page.goto('/');
    await page.keyboard.press('Tab');
    await expect(page.getByRole('link', { name: 'Skip to content' })).toBeFocused();
    if (await page.locator('.mobile-nav').isVisible()) {
        await page.getByRole('button', { name: 'Open navigation menu' }).focus();
        await page.keyboard.press('Enter');
        await expect(page.getByRole('menu', { name: 'Mobile navigation' })).toBeVisible();
        await page.getByRole('menuitem', { name: 'What We Trade' }).click();
        await expect(page).toHaveURL(/\/trade$/);
    }
});

test('supplier link preselects inquiry type and draft form is disabled', async ({ page }) => {
    await page.goto('/contact?type=supply');
    await expect(page.getByLabel('Inquiry type', { exact: true })).toHaveValue('supply');
    await expect(page.getByRole('button', { name: 'Send Inquiry' })).toBeDisabled();
    await expect(page.getByRole('status')).toContainText('Submissions are currently unavailable');
});

test('navigation keeps one current-page marker and closes with Escape', async ({ page }) => {
    await page.goto('/about');
    if (await page.locator('.desktop-nav').isVisible()) {
        const current = page.getByRole('navigation', { name: 'Primary navigation' }).getByRole('link', { name: 'About', exact: true });
        expect(await current.evaluate(link => getComputedStyle(link).textDecorationLine)).toBe('none');
        expect(await current.evaluate(link => getComputedStyle(link, '::after').height)).toBe('2px');
        expect(await page.locator('.site-header').evaluate(header => header.getBoundingClientRect().height)).toBeLessThanOrEqual(120);
    } else {
        const trigger = page.getByRole('button', { name: 'Open navigation menu' });
        const heroWidth = await page.locator('.hero').evaluate(hero => hero.getBoundingClientRect().width);
        await trigger.click();
        const menu = page.getByRole('menu', { name: 'Mobile navigation' });
        await expect(menu).toBeVisible();
        await expect(trigger).toHaveAttribute('aria-expanded', 'true');
        expect(await page.locator('.hero').evaluate(hero => hero.getBoundingClientRect().width)).toBe(heroWidth);
        const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
        expect(results.violations).toEqual([]);
        await page.keyboard.press('Escape');
        await expect(menu).not.toBeVisible();
        await expect(trigger).toBeFocused();
    }
});
