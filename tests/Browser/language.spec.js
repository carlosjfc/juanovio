import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

test('language choice survives navigation, reloads, cookie deletion and switching back', async ({ page, context }) => {
    await page.goto('/contact?type=supply');
    await page.getByRole('button', { name: 'Choose language' }).click();
    await page.getByRole('menuitem', { name: 'Español' }).click();
    await expect(page).toHaveURL(/\/es\/contact\?type=supply$/);
    await expect(page.getByLabel('Tipo de consulta', { exact: true })).toHaveValue('supply');
    expect(await page.evaluate(() => localStorage.getItem('juanovio.language'))).toBe('es');
    await page.reload();
    await expect(page.locator('html')).toHaveAttribute('lang', 'es');
    await context.clearCookies();
    await page.goto('/about');
    await expect(page).toHaveURL(/\/es\/about$/);
    await expect(page.locator('html')).toHaveAttribute('lang', 'es');
    await page.getByRole('button', { name: 'Elegir idioma' }).click();
    await page.getByRole('menuitem', { name: 'English' }).click();
    await expect(page).toHaveURL(/\/about$/);
    await page.goto('/');
    await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    expect(await page.evaluate(() => localStorage.getItem('juanovio.language'))).toBe('en');
});

test('language links still work without local storage', async ({ page }) => {
    await page.addInitScript(() => {
        Storage.prototype.getItem = () => { throw new Error('Storage blocked'); };
        Storage.prototype.setItem = () => { throw new Error('Storage blocked'); };
    });
    await page.goto('/');
    await page.getByRole('button', { name: 'Choose language' }).click();
    await page.getByRole('menuitem', { name: 'Español' }).click();
    await expect(page.locator('html')).toHaveAttribute('lang', 'es');
    await page.goto('/trade');
    await expect(page).toHaveURL(/\/es\/trade$/);
});

test('long Spanish labels fit narrow phones and intermediate desktop widths', async ({ page }) => {
    for (const width of [320, 1100, 1280]) {
        await page.setViewportSize({ width, height: 900 });
        await page.goto('/es');
        await page.evaluate(() => document.fonts.ready);
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
    }
});

test('language menu supports keyboard navigation', async ({ page }) => {
    await page.goto('/');
    const trigger = page.getByRole('button', { name: 'Choose language' });
    await trigger.focus();
    await page.keyboard.press('Enter');
    await expect(page.getByRole('menu', { name: 'Choose language' })).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(trigger).toBeFocused();
});

for (const path of ['/es', '/es/about', '/es/trade', '/es/how-we-work', '/es/markets', '/es/contact', '/es/privacy', '/es/terms']) {
    test(`${path} is accessible and fits the viewport`, async ({ page }, testInfo) => {
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        const response = await page.goto(path);
        expect(response.status()).toBe(200);
        await page.evaluate(() => document.fonts.ready);
        await expect(page.locator('html')).toHaveAttribute('lang', 'es');
        expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
        const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
        expect(results.violations).toEqual([]);
        expect(errors).toEqual([]);
        await page.screenshot({ path: testInfo.outputPath('page.png'), fullPage: true });
    });
}
