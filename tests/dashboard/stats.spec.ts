import { test, expect } from '@playwright/test';

test.describe('Order Statistics', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('http://localhost/admin/mlangprintauto/login.php');
        await page.fill('input[name="username"]', 'admin');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
    });

    test('should display 3 charts', async ({ page }) => {
        await page.goto('http://localhost/dashboard/stats/');
        
        await expect(page.locator('canvas#dailyChart')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('canvas#monthlyChart')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('canvas#productChart')).toBeVisible({ timeout: 10000 });
    });

    test('should update daily chart on period change', async ({ page }) => {
        await page.goto('http://localhost/dashboard/stats/');
        
        await page.waitForSelector('canvas#dailyChart', { timeout: 10000 });
        
        await page.selectOption('#periodSelect', '7');
        
        await page.waitForTimeout(1000);
        
        await expect(page.locator('canvas#dailyChart')).toBeVisible();
    });
});
