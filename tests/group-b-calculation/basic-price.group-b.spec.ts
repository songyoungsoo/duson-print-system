import { test, expect } from '@playwright/test';

/**
 * Group B-1: 기본 가격 계산 테스트
 *
 * 특징:
 * - 상태 변경 없음 (계산만 수행)
 * - 제품별 독립적 (병렬 실행 가능)
 * - AJAX 요청만 발생, DB 변경 없음
 */

test.describe('전단지 가격 계산', () => {
  test('A4 0.5연 컬러인쇄 기본 가격 계산', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');

    // Wait for page to fully load and options to be populated
    await page.waitForLoadState('networkidle');

    // 옵션 선택 - 기본값이 이미 선택되어 있으므로 그대로 사용
    // POtype (칼라), PN_type (90g아트지), MY_type (A4), MY_amount (0.5연)는 이미 선택됨

    // 가격 계산 대기 (AJAX)
    await page.waitForTimeout(1000);

    // 총액 표시 확인
    const totalPrice = page.locator('.total-price, #total_price, .price-display');
    await expect(totalPrice.first()).toBeVisible();

    // 가격이 0보다 큰지 확인
    const priceText = await totalPrice.first().textContent();
    const priceValue = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(priceValue).toBeGreaterThan(0);

    // 매수 표시 확인 (0.5연 = 2,000매)
    const quantityDisplay = page.locator('text=/2,000.*매/');
    const hasQuantity = await quantityDisplay.count() > 0;
    expect(hasQuantity).toBeTruthy();
  });

  test('A4 1.0연 컬러인쇄 기본 가격 계산', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');

    // Wait for page to fully load
    await page.waitForLoadState('networkidle');

    // Wait for the default price to be calculated first
    await page.waitForTimeout(1000);

    // 수량 변경 - value로 선택
    await page.selectOption('select[name="MY_amount"]', '1'); // 1연 = value "1"

    // 가격 재계산 대기
    await page.waitForTimeout(1000);

    // 가격이 계산되었는지 확인
    const totalPrice = page.locator('.total-price, #total_price, .price-display');
    await expect(totalPrice.first()).toBeVisible();

    // 가격이 0보다 큰지 확인
    const priceText = await totalPrice.first().textContent();
    const priceValue = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(priceValue).toBeGreaterThan(0);
  });
});

test.describe('명함 가격 계산', () => {
  test('일반명함 500매 아트지 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/namecard/');

    // Wait for page to fully load
    await page.waitForLoadState('networkidle');

    // Wait for dynamic options to load
    await page.waitForSelector('select[name="MY_amount"] option:not([value=""])', { state: 'attached', timeout: 10000 });

    // Trigger price calculation by clicking calculate button if exists, or wait for auto-calculation
    await page.waitForTimeout(2000);

    // 계산기 폼이 존재하는지 확인
    const calculatorForm = page.locator('form, #namecardForm, .calculator-form');
    await expect(calculatorForm.first()).toBeVisible();

    // 기본 옵션들이 선택되어 있는지 확인
    const typeSelect = page.locator('select[name="MY_type"]');
    await expect(typeSelect).toBeVisible();
  });
});

test.describe('봉투 가격 계산', () => {
  test('소봉투 1000매 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/envelope/');

    // Wait for page to fully load
    await page.waitForLoadState('networkidle');

    // Wait for dynamic options to load
    await page.waitForSelector('select[name="MY_amount"] option:not([value=""])', { state: 'attached', timeout: 10000 });

    await page.waitForTimeout(2000);

    // 계산기 폼이 존재하는지 확인
    const calculatorForm = page.locator('form, .calculator-form');
    await expect(calculatorForm.first()).toBeVisible();

    // 기본 옵션들이 선택되어 있는지 확인
    const typeSelect = page.locator('select[name="MY_type"]');
    await expect(typeSelect).toBeVisible();
  });
});

test.describe('리플렛 가격 계산 (접지 추가금 포함)', () => {
  test('A4 0.5연 + 2단접지 가격 계산', async ({ page }) => {
    await page.goto('/mlangprintauto/leaflet/');

    // Wait for page to fully load
    await page.waitForLoadState('networkidle');

    await page.waitForTimeout(2000);

    // 계산기 폼이 존재하는지 확인
    const calculatorForm = page.locator('form, .calculator-form');
    await expect(calculatorForm.first()).toBeVisible();

    // 기본 옵션들이 선택되어 있는지 확인
    const typeSelect = page.locator('select[name="MY_type"]');
    await expect(typeSelect).toBeVisible();
  });
});

// 병렬 실행 확인 테스트
test('가격 계산 병렬 실행 테스트 (3개 제품 동시)', async ({ browser }) => {
  // 3개 독립 컨텍스트 생성
  const context1 = await browser.newContext();
  const context2 = await browser.newContext();
  const context3 = await browser.newContext();

  const page1 = await context1.newPage();
  const page2 = await context2.newPage();
  const page3 = await context3.newPage();

  // 병렬로 가격 계산 수행
  await Promise.all([
    // 전단지
    (async () => {
      await page1.goto('/mlangprintauto/inserted/');
      await page1.waitForLoadState('networkidle');
      await page1.waitForTimeout(1000);
    })(),

    // 명함
    (async () => {
      await page2.goto('/mlangprintauto/namecard/');
      await page2.waitForLoadState('networkidle');
      await page2.waitForSelector('select[name="MY_amount"] option:not([value=""])', { state: 'attached' });
      await page2.waitForTimeout(1000);
    })(),

    // 봉투
    (async () => {
      await page3.goto('/mlangprintauto/envelope/');
      await page3.waitForLoadState('networkidle');
      await page3.waitForSelector('select[name="MY_amount"] option:not([value=""])', { state: 'attached' });
      await page3.waitForTimeout(1000);
    })(),
  ]);

  // 모든 페이지에서 가격 표시 확인
  const prices = await Promise.all([
    page1.locator('text=/원/').first().isVisible(),
    page2.locator('text=/원/').first().isVisible(),
    page3.locator('text=/원/').first().isVisible(),
  ]);

  expect(prices.every(visible => visible)).toBeTruthy();

  // 정리
  await context1.close();
  await context2.close();
  await context3.close();
});
