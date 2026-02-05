import { test, expect } from '@playwright/test';

test.describe('Member Management', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto('http://localhost/admin/mlangprintauto/login.php');
        await page.fill('input[name="username"]', 'admin');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
    });

    test('should display members list', async ({ page }) => {
        await page.goto('http://localhost/dashboard/members/');
        
        const table = page.locator('table');
        await expect(table).toBeVisible({ timeout: 10000 });
        
        await expect(page.locator('th:has-text("아이디")')).toBeVisible();
        await expect(page.locator('th:has-text("이름")')).toBeVisible();
        await expect(page.locator('th:has-text("이메일")')).toBeVisible();
    });

    test('should search members', async ({ page }) => {
        await page.goto('http://localhost/dashboard/members/');
        
        await page.fill('#searchInput', 'test');
        await page.click('#searchBtn');
        
        await page.waitForTimeout(1000);
        
        const tbody = page.locator('#membersTableBody');
        await expect(tbody).toBeVisible();
    });
});
