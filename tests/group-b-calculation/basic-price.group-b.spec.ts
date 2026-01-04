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

    // 옵션 선택
    await page.selectOption('select[name="PN_type"]', { label: /90g.*아트지/ }); // 용지
    await page.selectOption('select[name="MY_type"]', { label: /A4/ }); // 규격
    await page.selectOption('select[name="MY_amount"]', { label: /0\.5.*연/ }); // 수량
    await page.selectOption('select[name="POtype"]', { label: /컬러/ }); // 인쇄색상

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

    await page.selectOption('select[name="PN_type"]', { label: /90g.*아트지/ });
    await page.selectOption('select[name="MY_type"]', { label: /A4/ });
    await page.selectOption('select[name="MY_amount"]', { label: /1\.0.*연/ }); // 1.0연
    await page.selectOption('select[name="POtype"]', { label: /컬러/ });

    await page.waitForTimeout(1000);

    // 매수 확인 (1.0연 = 4,000매)
    const quantityDisplay = page.locator('text=/4,000.*매/');
    await expect(quantityDisplay.first()).toBeVisible();
  });
});

test.describe('명함 가격 계산', () => {
  test('일반명함 500매 아트지 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/namecard/');

    // 옵션 선택
    await page.selectOption('select[name="MY_type"]', { label: /일반.*명함/ }); // 규격
    await page.selectOption('select[name="Section"]', { label: /아트지/ }); // 재질
    await page.selectOption('select[name="POtype"]', { label: /단면.*4도/ }); // 인쇄
    await page.selectOption('select[name="MY_amount"]', { label: /500/ }); // 수량

    await page.waitForTimeout(1000);

    // 가격 표시 확인
    const totalPrice = page.locator('.total-price, #total_price');
    await expect(totalPrice.first()).toBeVisible();

    const priceText = await totalPrice.first().textContent();
    const priceValue = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(priceValue).toBeGreaterThan(10000); // 명함 최소가 확인
  });
});

test.describe('봉투 가격 계산', () => {
  test('소봉투 1000매 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/envelope/');

    // 옵션 선택
    await page.selectOption('select[name="MY_type"]', { label: /소봉투/ }); // 규격
    await page.selectOption('select[name="Section"]', { label: /모조지/ }); // 용지
    await page.selectOption('select[name="POtype"]', { label: /단면/ }); // 인쇄
    await page.selectOption('select[name="MY_amount"]', { label: /1000/ }); // 수량

    await page.waitForTimeout(1000);

    // 가격 표시 확인
    const totalPrice = page.locator('.total-price, #total_price');
    await expect(totalPrice.first()).toBeVisible();
  });
});

test.describe('리플렛 가격 계산 (접지 추가금 포함)', () => {
  test('A4 0.5연 + 2단접지 가격 계산', async ({ page }) => {
    await page.goto('/mlangprintauto/leaflet/');

    // 기본 옵션
    await page.selectOption('select[name="PN_type"]', { label: /90g.*아트지/ });
    await page.selectOption('select[name="MY_type"]', { label: /A4/ });
    await page.selectOption('select[name="MY_amount"]', { label: /0\.5.*연/ });
    await page.selectOption('select[name="POtype"]', { label: /컬러/ });

    // 접지방식 선택
    const foldingSelect = page.locator('select[name="folding_type"]');
    if (await foldingSelect.count() > 0) {
      await foldingSelect.selectOption({ label: /2단접지/ });
    }

    await page.waitForTimeout(1000);

    // 접지 추가금 표시 확인
    const foldingPrice = page.locator('text=/접지.*40,000/');
    const hasFoldingPrice = await foldingPrice.count() > 0;
    expect(hasFoldingPrice).toBeTruthy();
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
      await page1.selectOption('select[name="MY_amount"]', { label: /0\.5.*연/ });
      await page1.waitForTimeout(500);
    })(),

    // 명함
    (async () => {
      await page2.goto('/mlangprintauto/namecard/');
      await page2.selectOption('select[name="MY_amount"]', { label: /500/ });
      await page2.waitForTimeout(500);
    })(),

    // 봉투
    (async () => {
      await page3.goto('/mlangprintauto/envelope/');
      await page3.selectOption('select[name="MY_amount"]', { label: /1000/ });
      await page3.waitForTimeout(500);
    })(),
  ]);

  // 모든 페이지에서 가격 표시 확인
  const prices = await Promise.all([
    page1.locator('.total-price').first().isVisible(),
    page2.locator('.total-price').first().isVisible(),
    page3.locator('.total-price').first().isVisible(),
  ]);

  expect(prices.every(visible => visible)).toBeTruthy();

  // 정리
  await context1.close();
  await context2.close();
  await context3.close();
});
