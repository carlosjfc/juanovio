import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const markets = {
    en: {
        path: '/markets', contact: '/contact',
        sourcing: ['Dominican Republic', 'Panama', 'Guatemala', 'Colombia', 'Brazil', 'Türkiye (Turkey)'],
        destination: ['Guyana', 'Jamaica', 'Suriname', 'Bahamas', 'Belize', 'Grenada', 'Antigua and Barbuda'],
        buyer: "I'm a Buyer or Importer", supplier: "I'm a Producer or Supplier", type: 'Inquiry type',
    },
    es: {
        path: '/es/markets', contact: '/es/contact',
        sourcing: ['República Dominicana', 'Panamá', 'Guatemala', 'Colombia', 'Brasil', 'Turquía'],
        destination: ['Guyana', 'Jamaica', 'Surinam', 'Bahamas', 'Belice', 'Granada', 'Antigua y Barbuda'],
        buyer: 'Soy comprador o importador', supplier: 'Soy productor o proveedor', type: 'Tipo de consulta',
    },
};

for (const [locale, content] of Object.entries(markets)) {
    test(`${locale} market lists wrap at narrow widths and retain accessible presentation`, async ({ page }, testInfo) => {
        await page.goto(content.path);
        await page.evaluate(() => document.fonts.ready);
        for (const group of ['sourcing', 'destination']) {
            await expect(page.locator(`[data-market="${group}"] li`)).toHaveText(content[group]);
        }
        await expect(page.locator('h1')).toHaveCount(1);
        await expect(page.locator('.disclosure')).toHaveCount(1);
        const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21aa', 'wcag22aa']).analyze();
        expect(results.violations).toEqual([]);
        await page.screenshot({ path: testInfo.outputPath(`${locale}-markets.png`), fullPage: true });
        for (const width of [320, 390, 768, 1440]) {
            await page.setViewportSize({ width, height: 900 });
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
            for (const item of await page.locator('.market-countries li').all()) {
                expect(await item.evaluate(element => element.scrollWidth <= element.clientWidth)).toBe(true);
            }
            const panels = await page.locator('.market-panel').evaluateAll(elements => elements.map(element => {
                const { x, y, bottom } = element.getBoundingClientRect();
                return { x, y, bottom };
            }));
            if (width <= 390) {
                expect(panels[1].x).toBe(panels[0].x);
                expect(panels[1].y).toBeGreaterThan(panels[0].bottom);
            }
        }
    });

    test(`${locale} market CTAs preselect existing inquiry types and language switch preserves markets`, async ({ page }) => {
        for (const [label, type] of [[content.buyer, 'buy'], [content.supplier, 'supply']]) {
            await page.goto(content.path);
            const link = page.getByRole('link', { name: label, exact: true });
            await link.focus();
            await expect(link).toBeFocused();
            await page.keyboard.press('Enter');
            await expect(page).toHaveURL(new RegExp(`${content.contact}\\?type=${type}$`));
            await expect(page.getByLabel(content.type, { exact: true })).toHaveValue(type);
        }
        await page.goto(content.path);
        await page.getByRole('button', { name: locale === 'en' ? 'Choose language' : 'Elegir idioma' }).click();
        await page.getByRole('menuitem', { name: locale === 'en' ? 'Español' : 'English' }).click();
        await expect(page).toHaveURL(new RegExp(`${markets[locale === 'en' ? 'es' : 'en'].path}$`));
    });
}
