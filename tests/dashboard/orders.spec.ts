import { test, expect } from '@playwright/test';

test.describe('Order Management', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('http://localhost/admin/mlangprintauto/login.php');
        await page.fill('input[name="username"]', 'admin');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
    });

    test('should display orders list', async ({ page }) => {
        await page.goto('http://localhost/dashboard/orders/');
        
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 10000 });
        
        await expect(page.locator('th:has-text("주문번호")')).toBeVisible();
        await expect(page.locator('th:has-text("품목")')).toBeVisible();
        await expect(page.locator('th:has-text("주문자")')).toBeVisible();
    });

    test('should filter orders by period', async ({ page }) => {
        await page.goto('http://localhost/dashboard/orders/');
        
        await page.selectOption('#periodFilter', 'today');
        await page.click('#searchBtn');
        
        await page.waitForTimeout(1000);
        
        const tbody = page.locator('#ordersTableBody');
        await expect(tbody).toBeVisible();
    });
});
