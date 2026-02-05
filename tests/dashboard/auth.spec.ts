import { test, expect } from '@playwright/test';

test.describe('Dashboard Authentication', () => {
    test('should redirect to login when not authenticated', async ({ page }) => {
        await page.goto('http://localhost/dashboard/');
        
        await expect(page).toHaveURL(/login/);
    });

    test('should allow access after admin login', async ({ page }) => {
        await page.goto('http://localhost/admin/mlangprintauto/login.php');
        
        await page.fill('input[name="username"]', 'admin');
        await page.fill('input[name="password"]', 'admin123');
        await page.click('button[type="submit"]');
        
        await page.goto('http://localhost/dashboard/');
        
        await expect(page.locator('h1')).toContainText('대시보드');
    });
});
