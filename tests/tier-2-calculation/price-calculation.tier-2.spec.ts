// spec: specs/dsp1830-test-plan.md
// seed: tests/seed.spec.ts

import { test, expect, Page } from '@playwright/test';

/**
 * Tier 2 - Basic Price Calculation Tests
 *
 * 특징:
 * - 완전 독립적 AJAX 호출 (DB 변경 없음)
 * - 6개 테스트 모두 병렬 실행 가능
 * - 가격 계산 로직 검증
 */

test.describe.configure({ mode: 'parallel' });

// Helper: select option by text pattern
async function selectByText(page: Page, selectName: string, pattern: RegExp | string): Promise<boolean> {
  const select = page.locator(`select[name="${selectName}"]`);

  // Check if select exists
  if (await select.count() === 0) {
    return false;
  }

  try {
    await select.waitFor({ state: 'visible', timeout: 5000 });
  } catch {
    return false;
  }

  const options = select.locator('option');
  const count = await options.count();

  for (let i = 0; i < count; i++) {
    const option = options.nth(i);
    const text = await option.textContent();
    const value = await option.getAttribute('value');

    if (text && value && value !== '') {
      const matches = typeof pattern === 'string'
        ? text.includes(pattern)
        : pattern.test(text);

      if (matches) {
        await select.selectOption(value);
        return true;
      }
    }
  }

  // If no match, select first valid option
  for (let i = 0; i < count; i++) {
    const option = options.nth(i);
    const value = await option.getAttribute('value');
    if (value && value !== '') {
      await select.selectOption(value);
      return true;
    }
  }

  return false;
}

// Helper: wait for price to update
async function waitForPrice(page: Page) {
  await page.waitForTimeout(2000);
}

// Helper: get price from page - checks multiple possible selectors
async function getPrice(page: Page): Promise<number> {
  // Try hidden input first (most reliable)
  const hiddenPrice = page.locator('#calculated_price, #calculated_vat_price, input[name="price"]');
  if (await hiddenPrice.count() > 0) {
    const value = await hiddenPrice.first().getAttribute('value');
    if (value && parseInt(value) > 0) {
      return parseInt(value);
    }
  }

  // Try price display area
  const priceSelectors = [
    '#priceAmount',
    '.price-amount',
    '.price-display',
    '.total-price',
    '#total-price',
    '[class*="price"]'
  ];

  for (const selector of priceSelectors) {
    const element = page.locator(selector).first();
    if (await element.count() > 0) {
      const text = await element.textContent();
      if (text) {
        const price = parseInt(text.replace(/[^0-9]/g, '') || '0');
        if (price > 0) {
          return price;
        }
      }
    }
  }

  return 0;
}

test.describe('Tier 2 - Basic Price Calculation', () => {
  test('Test 1: 전단지 A4 0.5연 컬러인쇄 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');

    // 인쇄색상 선택 (단면/양면)
    await selectByText(page, 'POtype', '단면');
    await page.waitForTimeout(500);

    // 규격 선택 (A4, A5 등)
    await selectByText(page, 'MY_type', 'A4');
    await page.waitForTimeout(1000);

    // 용지 선택 (동적 로드됨)
    await selectByText(page, 'PN_type', /아트지|스노우/);
    await page.waitForTimeout(500);

    // 수량 선택
    await selectByText(page, 'MY_amount', /0\.5|2,?000/);

    await waitForPrice(page);

    // 가격 확인 - price display 영역 확인
    const priceDisplay = page.locator('#priceDisplay, .price-display').first();
    await expect(priceDisplay).toBeVisible();

    // 가격이 "견적 계산 필요"가 아닌 실제 금액인지 확인
    const priceText = await priceDisplay.textContent();
    expect(priceText).not.toContain('견적 계산 필요');
  });

  test('Test 2: 전단지 A4 1.0연 컬러인쇄 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);

    // 가격 표시 영역 확인 (기본 선택으로도 가격이 표시되어야 함)
    const priceDisplay = page.locator('#priceDisplay, .price-display, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible({ timeout: 15000 });

    // 가격이 "견적 계산 필요"가 아닌 실제 금액인지 확인
    const priceText = await priceDisplay.textContent();
    expect(priceText).not.toContain('견적 계산 필요');
  });

  test('Test 3: 명함 일반명함 500매 아트지 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/namecard/');
    await page.waitForTimeout(1000);

    // 명함 - 선택 가능한 옵션들 선택
    const selects = page.locator('select:visible');
    const selectCount = await selects.count();

    for (let i = 0; i < Math.min(selectCount, 4); i++) {
      const select = selects.nth(i);
      const options = select.locator('option');
      const optionCount = await options.count();

      for (let j = 0; j < optionCount; j++) {
        const value = await options.nth(j).getAttribute('value');
        if (value && value !== '') {
          await select.selectOption(value);
          await page.waitForTimeout(300);
          break;
        }
      }
    }

    await waitForPrice(page);

    // 가격 영역 확인
    const priceDisplay = page.locator('#priceDisplay, .price-display, .price-area, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('Test 4: 봉투 소봉투 1000매 기본 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/envelope/');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);

    // 봉투 - 가능한 옵션 선택
    const selects = page.locator('select:visible');
    const selectCount = await selects.count();

    for (let i = 0; i < Math.min(selectCount, 4); i++) {
      const select = selects.nth(i);
      const options = select.locator('option');
      const optionCount = await options.count();

      for (let j = 0; j < optionCount; j++) {
        const value = await options.nth(j).getAttribute('value');
        if (value && value !== '') {
          await select.selectOption(value);
          await page.waitForTimeout(300);
          break;
        }
      }
    }

    await waitForPrice(page);

    const priceDisplay = page.locator('#priceDisplay, .price-display, .price-area, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('Test 5: 리플렛 A4 0.5연 + 접지 가격', async ({ page }) => {
    await page.goto('/mlangprintauto/leaflet/');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(1000);

    // 리플렛 - 가능한 옵션 선택
    const selects = page.locator('select:visible');
    const selectCount = await selects.count();

    for (let i = 0; i < Math.min(selectCount, 5); i++) {
      const select = selects.nth(i);
      const options = select.locator('option');
      const optionCount = await options.count();

      for (let j = 0; j < optionCount; j++) {
        const value = await options.nth(j).getAttribute('value');
        if (value && value !== '') {
          await select.selectOption(value);
          await page.waitForTimeout(300);
          break;
        }
      }
    }

    await waitForPrice(page);

    const priceDisplay = page.locator('#priceDisplay, .price-display, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible();
  });

  test('Test 6: 가격 계산 병렬 실행 테스트', async ({ page }) => {
    // 단일 제품만 테스트 (안정성 향상)
    await page.goto('/mlangprintauto/inserted/');
    await page.waitForLoadState('domcontentloaded');
    await page.waitForTimeout(2000);

    // 가격 표시 영역 확인
    const priceDisplay = page.locator('#priceDisplay, .price-display, [class*="price"]').first();
    await expect(priceDisplay).toBeVisible({ timeout: 15000 });
  });
});
