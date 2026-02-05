import { test, expect } from '@playwright/test';

test.describe('Product Management', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('http://localhost/admin/mlangprintauto/login.php');
        await page.fill('input[name="username"]', 'admin');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
    });

    test('should display 9 product types', async ({ page }) => {
        await page.goto('http://localhost/dashboard/products/');
        
        const productCards = page.locator('.bg-white.rounded-lg.shadow');
        await expect(productCards).toHaveCount(9, { timeout: 10000 });
        
        await expect(page.locator('text=명함')).toBeVisible();
        await expect(page.locator('text=스티커')).toBeVisible();
        await expect(page.locator('text=전단지')).toBeVisible();
    });

    test('should display product options list', async ({ page }) => {
        await page.goto('http://localhost/dashboard/products/list.php?type=namecard');
        
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 10000 });
        
        await expect(page.locator('th:has-text("스타일")')).toBeVisible();
        await expect(page.locator('th:has-text("가격")')).toBeVisible();
    });
});
