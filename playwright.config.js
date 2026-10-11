import { defineConfig } from '@playwright/test';

export default defineConfig({
    testDir: './tests/Browser',
    use: { baseURL: process.env.PLAYWRIGHT_BASE_URL || 'https://juanovio.test' },
    projects: [
        { name: 'mobile', use: { viewport: { width: 390, height: 844 } } },
        { name: 'tablet', use: { viewport: { width: 768, height: 1024 } } },
        { name: 'desktop', use: { viewport: { width: 1440, height: 1000 } } },
    ],
});
