import { test, expect } from '@playwright/test';

test.describe('Test group', () => {
  test('inspect flyer page selects', async ({ page }) => {
    await page.goto('http://dsp1830.shop/mlangprintauto/inserted/');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);

    // Get all select options
    const myTypeOptions = await page.locator('select[name="MY_type"] option').allTextContents();
    const myFsdOptions = await page.locator('select[name="MY_Fsd"] option').allTextContents();
    const pnTypeOptions = await page.locator('select[name="PN_type"] option').allTextContents();
    const potypeOptions = await page.locator('select[name="POtype"] option').allTextContents();
    const myAmountOptions = await page.locator('select[name="MY_amount"] option').allTextContents();

    console.log('MY_type (색상):', myTypeOptions);
    console.log('MY_Fsd (종류/용지):', myFsdOptions);
    console.log('PN_type (규격):', pnTypeOptions);
    console.log('POtype (인쇄면):', potypeOptions);
    console.log('MY_amount (수량):', myAmountOptions);

    // Pause to inspect
    await page.pause();
  });
});
