// spec: specs/dsp1830-test-plan.md
// seed: tests/seed.spec.ts

import { test, expect } from '@playwright/test';

test.describe.configure({ mode: 'parallel' });

test.describe('Tier 1 - Product Page Loading (Read-Only)', () => {
  
  test('1.1. 전단지 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/inserted/
    await page.goto('/mlangprintauto/inserted/');
    
    // 2. Verify page title contains '전단지'
    await expect(page).toHaveTitle(/전단지/);
    
    // 3. Check that price calculator form exists (form#orderForm or form[name='choiceForm'])
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
    
    // 4. Verify option selection dropdowns are visible (용지, 규격, 수량, 인쇄색상)
    await expect(page.locator('select[name="PN_type"], select#PN_type')).toBeVisible();
    await expect(page.locator('select[name="MY_type"], select#MY_type')).toBeVisible();
    await expect(page.locator('select[name="MY_amount"], select#MY_amount')).toBeVisible();
    await expect(page.locator('select[name="POtype"], select#POtype')).toBeVisible();
    
    // 5. Confirm '장바구니 담기' button is visible
    const addToCartButton = page.locator('button:has-text("장바구니"), input[type="button"][value*="장바구니"], a:has-text("장바구니")').first();
    await expect(addToCartButton).toBeVisible();
    
    // 6. Check price display area exists (.price-display, #total-price, .total-amount)
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('1.2. 명함 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/namecard/
    await page.goto('/mlangprintauto/namecard/');
    
    // 2. Verify page title contains '명함'
    await expect(page).toHaveTitle(/명함/);
    
    // 3. Check that price calculator form exists
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
    
    // 4. Verify option dropdowns are visible (재질, 수량, 규격)
    await expect(page.locator('select[name="Section"], select#Section')).toBeVisible();
    await expect(page.locator('select[name="MY_amount"], select#MY_amount')).toBeVisible();
    await expect(page.locator('select[name="MY_type"], select#MY_type')).toBeVisible();
    
    // 5. Confirm '장바구니 담기' button is visible
    const addToCartButton = page.locator('button:has-text("장바구니"), input[type="button"][value*="장바구니"], a:has-text("장바구니")').first();
    await expect(addToCartButton).toBeVisible();
    
    // 6. Check price display area exists
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('1.3. 봉투 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/envelope/
    await page.goto('/mlangprintauto/envelope/');
    
    // 2. Verify page title contains '봉투'
    await expect(page).toHaveTitle(/봉투/);
    
    // 3. Check that price calculator form exists
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
    
    // 4. Verify envelope-specific options are visible (규격, 용지, 인쇄, 수량)
    await expect(page.locator('select[name="MY_type"], select#MY_type')).toBeVisible();
    await expect(page.locator('select[name="PN_type"], select#PN_type')).toBeVisible();
    await expect(page.locator('select[name="POtype"], select#POtype')).toBeVisible();
    await expect(page.locator('select[name="MY_amount"], select#MY_amount')).toBeVisible();
    
    // 5. Confirm '장바구니 담기' button is visible
    const addToCartButton = page.locator('button:has-text("장바구니"), input[type="button"][value*="장바구니"], a:has-text("장바구니")').first();
    await expect(addToCartButton).toBeVisible();
    
    // 6. Check price display area exists
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('1.4. 스티커 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/sticker_new/
    await page.goto('/mlangprintauto/sticker_new/');
    
    // 2. Verify page title contains '스티커'
    await expect(page).toHaveTitle(/스티커/);
    
    // 3. Check form elements exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
    
    // 4. Verify sticker-specific options are available
    const optionSelects = page.locator('select');
    await expect(optionSelects.first()).toBeVisible();
  });

  test('1.5. 자석스티커 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/msticker/
    await page.goto('/mlangprintauto/msticker/');
    
    // 2. Verify page title contains '자석' or '스티커'
    const title = await page.title();
    expect(title).toMatch(/자석|스티커/);
    
    // 3. Check form elements and options exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
  });

  test('1.6. 카다록 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/cadarok/
    await page.goto('/mlangprintauto/cadarok/');
    
    // 2. Verify page title contains '카다록'
    await expect(page).toHaveTitle(/카다록/);
    
    // 3. Check form and options exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
  });

  test('1.7. 포스터 페이지 로딩 테스트 (littleprint)', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/littleprint/
    await page.goto('/mlangprintauto/littleprint/');
    
    // 2. Verify page title contains '포스터'
    await expect(page).toHaveTitle(/포스터/);
    
    // 3. Check form elements exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
    
    // 4. Note: Directory is 'littleprint' but product name is '포스터' (legacy code)
    // Verification complete
  });

  test('1.8. 상품권 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/merchandisebond/
    await page.goto('/mlangprintauto/merchandisebond/');
    
    // 2. Verify page title contains '상품권'
    await expect(page).toHaveTitle(/상품권/);
    
    // 3. Check form and options exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
  });

  test('1.9. NCR양식 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/ncrflambeau/
    await page.goto('/mlangprintauto/ncrflambeau/');
    
    // 2. Verify page title contains 'NCR' or '양식'
    const title = await page.title();
    expect(title).toMatch(/NCR|양식/);
    
    // 3. Check form elements exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
  });

  test('1.10. 리플렛 페이지 로딩 테스트', async ({ page }) => {
    // 1. Navigate to http://dsp1830.shop/mlangprintauto/leaflet/
    await page.goto('/mlangprintauto/leaflet/');
    
    // 2. Verify page title contains '리플렛'
    await expect(page).toHaveTitle(/리플렛/);
    
    // 3. Check form and folding options exist
    const form = page.locator('form#orderForm, form[name="choiceForm"]').first();
    await expect(form).toBeVisible();
  });

  test('1.11. 모든 제품 공통 요소 확인', async ({ page }) => {
    // For each product page (sample 3 products)
    const sampleProducts = [
      '/mlangprintauto/inserted/',
      '/mlangprintauto/namecard/',
      '/mlangprintauto/envelope/'
    ];
    
    for (const productUrl of sampleProducts) {
      // 1. Navigate to product URL
      await page.goto(productUrl);
      
      // 2. Verify logo/home link is visible (a[href*='index'], img[alt*='로고'])
      const logo = page.locator('a[href*="index"], img[alt*="로고"], a[href="/"], .logo').first();
      await expect(logo).toBeVisible();
      
      // 3. Check navigation menu exists (nav, .navigation, .menu)
      const navigation = page.locator('nav, .navigation, .menu, header').first();
      await expect(navigation).toBeVisible();
      
      // 4. Verify footer elements are present
      const footer = page.locator('footer, .footer, [class*="footer"]').first();
      await expect(footer).toBeVisible();
    }
  });
});
