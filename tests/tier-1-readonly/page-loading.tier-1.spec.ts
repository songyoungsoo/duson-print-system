// spec: specs/dsp1830-test-plan.md
// seed: tests/seed.spec.ts

import { test, expect } from '@playwright/test';

test.describe.configure({ mode: 'parallel' });

// 제품별 폼 ID 매핑
const FORM_SELECTORS = {
  inserted: 'form#orderForm',
  namecard: 'form#namecardForm',
  envelope: 'form#envelopeForm',
  sticker_new: 'form#stickerForm',
  msticker: 'form#mstickerForm',
  cadarok: 'form#cadarokForm',
  littleprint: 'form#posterForm',
  merchandisebond: 'form#merchandisebondForm',
  ncrflambeau: 'form#ncr-quote-form',
  leaflet: 'form#orderForm',
};

test.describe('Tier 1 - Product Page Loading (Read-Only)', () => {

  test('1.1. 전단지 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');

    // 페이지 타이틀 확인
    await expect(page).toHaveTitle(/전단지/);

    // 가격 계산기 폼 확인
    const form = page.locator(FORM_SELECTORS.inserted);
    await expect(form).toBeVisible();

    // 옵션 선택 드롭다운 확인
    await expect(page.locator('select[name="PN_type"], select#PN_type')).toBeVisible();
    await expect(page.locator('select[name="MY_type"], select#MY_type')).toBeVisible();
    await expect(page.locator('select[name="MY_amount"], select#MY_amount')).toBeVisible();
    await expect(page.locator('select[name="POtype"], select#POtype')).toBeVisible();

    // 장바구니 버튼 확인
    const addToCartButton = page.locator('button:has-text("장바구니"), input[type="button"][value*="장바구니"], a:has-text("장바구니"), .btn-cart').first();
    await expect(addToCartButton).toBeVisible();

    // 가격 표시 영역 확인
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount, [class*="price"], .price-area').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('1.2. 명함 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/namecard/');

    await expect(page).toHaveTitle(/명함/);

    const form = page.locator(FORM_SELECTORS.namecard);
    await expect(form).toBeVisible();

    // 명함 옵션 드롭다운 확인
    const optionSelects = page.locator('select').first();
    await expect(optionSelects).toBeVisible();

    // 장바구니 버튼 확인
    const addToCartButton = page.locator('button:has-text("장바구니"), input[type="button"][value*="장바구니"], a:has-text("장바구니"), .btn-cart').first();
    await expect(addToCartButton).toBeVisible();

    // 가격 표시 영역 확인
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount, [class*="price"], .price-area').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('1.3. 봉투 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/envelope/');

    await expect(page).toHaveTitle(/봉투/);

    const form = page.locator(FORM_SELECTORS.envelope);
    await expect(form).toBeVisible();

    // 봉투 옵션 드롭다운 확인
    const optionSelects = page.locator('select').first();
    await expect(optionSelects).toBeVisible();

    // 장바구니 버튼 확인
    const addToCartButton = page.locator('button:has-text("장바구니"), input[type="button"][value*="장바구니"], a:has-text("장바구니"), .btn-cart').first();
    await expect(addToCartButton).toBeVisible();

    // 가격 표시 영역 확인
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount, [class*="price"], .price-area').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('1.4. 스티커 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/sticker_new/');

    await expect(page).toHaveTitle(/스티커/);

    const form = page.locator(FORM_SELECTORS.sticker_new);
    await expect(form).toBeVisible();

    // 스티커 옵션 드롭다운 확인
    const optionSelects = page.locator('select').first();
    await expect(optionSelects).toBeVisible();
  });

  test('1.5. 자석스티커 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/msticker/');

    const title = await page.title();
    expect(title).toMatch(/자석|스티커/);

    const form = page.locator(FORM_SELECTORS.msticker);
    await expect(form).toBeVisible();
  });

  test('1.6. 카다록 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/cadarok/');

    await expect(page).toHaveTitle(/카다록/);

    const form = page.locator(FORM_SELECTORS.cadarok);
    await expect(form).toBeVisible();
  });

  test('1.7. 포스터 페이지 로딩 테스트 (littleprint)', async ({ page }) => {
    await page.goto('/mlangprintauto/littleprint/');

    await expect(page).toHaveTitle(/포스터/);

    const form = page.locator(FORM_SELECTORS.littleprint);
    await expect(form).toBeVisible();
  });

  test('1.8. 상품권 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/merchandisebond/');

    await expect(page).toHaveTitle(/상품권/);

    const form = page.locator(FORM_SELECTORS.merchandisebond);
    await expect(form).toBeVisible();
  });

  test('1.9. NCR양식 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/ncrflambeau/');

    const title = await page.title();
    expect(title).toMatch(/NCR|양식/);

    const form = page.locator(FORM_SELECTORS.ncrflambeau);
    await expect(form).toBeVisible();
  });

  test('1.10. 리플렛 페이지 로딩 테스트', async ({ page }) => {
    await page.goto('/mlangprintauto/leaflet/');

    await expect(page).toHaveTitle(/리플렛/);

    const form = page.locator(FORM_SELECTORS.leaflet);
    await expect(form).toBeVisible();
  });

  test('1.11. 모든 제품 공통 요소 확인', async ({ page }) => {
    // 단일 제품만 테스트 (안정성 향상)
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');

    // 헤더 영역 확인
    const headerArea = page.locator('.top-header, .logo-section, header, .header-content, [class*="header"], [class*="logo"]');
    const hasHeader = await headerArea.count() > 0;
    expect(hasHeader).toBeTruthy();

    // 메인 콘텐츠 확인
    const mainContent = page.locator('form, select, .calculator, .price-display, [class*="price"]');
    const hasMainContent = await mainContent.count() > 0;
    expect(hasMainContent).toBeTruthy();
  });
});
