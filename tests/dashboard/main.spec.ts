import { test, expect } from '@playwright/test';

test.describe('Main Dashboard', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('http://localhost/admin/mlangprintauto/login.php');
        await page.fill('input[name="username"]', 'admin');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
    });

    test('should display summary cards', async ({ page }) => {
        await page.goto('http://localhost/dashboard/');
        
        const cards = page.locator('.bg-white.rounded-lg.shadow');
        await expect(cards).toHaveCount(4, { timeout: 10000 });
        
        await expect(page.locator('text=오늘 주문')).toBeVisible();
        await expect(page.locator('text=이번달 주문')).toBeVisible();
        await expect(page.locator('text=미처리 주문')).toBeVisible();
        await expect(page.locator('text=미답변 문의')).toBeVisible();
    });

    test('should display daily trend chart', async ({ page }) => {
        await page.goto('http://localhost/dashboard/');
        
        const canvas = page.locator('canvas#dailyChart');
        await expect(canvas).toBeVisible({ timeout: 10000 });
    });

    test('should display recent orders table', async ({ page }) => {
        await page.goto('http://localhost/dashboard/');
        
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 10000 });
    });
});
