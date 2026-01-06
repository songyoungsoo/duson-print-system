// spec: specs/dsp1830-test-plan.md
// seed: tests/seed.spec.ts

import { test, expect } from '@playwright/test';

/**
 * Tier 2 - Basic Price Calculation Tests
 * 
 * 특징:
 * - 완전 독립적 AJAX 호출 (DB 변경 없음)
 * - 6개 테스트 모두 병렬 실행 가능
 * - 가격 계산 로직 검증
 */

// 병렬 실행 설정
test.describe.configure({ mode: 'parallel' });

test.describe('Tier 2 - Basic Price Calculation', () => {
  test('Test 1: 전단지 A4 0.5연 컬러인쇄 기본 가격', async ({ page }) => {
    // 1. Navigate to 전단지 page
    await page.goto('/mlangprintauto/inserted/');
    
    // 2. Select 용지 (PN_type) = '90g 아트지'
    const paperSelect = page.locator('select[name="PN_type"]');
    await paperSelect.selectOption({ label: /90g.*아트지/ });
    
    // 3. Select 규격 (MY_type) = 'A4'
    const sizeSelect = page.locator('select[name="MY_type"]');
    await sizeSelect.selectOption({ label: 'A4' });
    
    // 4. Select 수량 (MY_amount) = '0.5연'
    const quantitySelect = page.locator('select[name="MY_amount"]');
    await quantitySelect.selectOption({ label: /0\.5연/ });
    
    // 5. Select 인쇄색상 (POtype) = '컬러'
    const colorSelect = page.locator('select[name="POtype"]');
    await colorSelect.selectOption({ label: /컬러/ });
    
    // 6. Wait for AJAX calculation (1000ms)
    await page.waitForTimeout(1000);
    
    // 7. Verify price > 0
    const priceDisplay = page.locator('.total-price, #total-price, .price-display').first();
    const priceText = await priceDisplay.textContent();
    const price = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(price).toBeGreaterThan(0);
    
    // 8. Verify quantity shows '2,000매' (0.5연 = 2,000 sheets)
    const quantityDisplay = page.locator('text=/2,?000매/');
    await expect(quantityDisplay.first()).toBeVisible();
  });

  test('Test 2: 전단지 A4 1.0연 컬러인쇄 기본 가격', async ({ page }) => {
    // 1. Navigate to 전단지 page
    await page.goto('/mlangprintauto/inserted/');
    
    // 2. Select options (same as Test 1 but quantity = 1.0연)
    await page.locator('select[name="PN_type"]').selectOption({ label: /90g.*아트지/ });
    await page.locator('select[name="MY_type"]').selectOption({ label: 'A4' });
    await page.locator('select[name="MY_amount"]').selectOption({ label: /1\.0연/ });
    await page.locator('select[name="POtype"]').selectOption({ label: /컬러/ });
    
    // 3. Wait for AJAX
    await page.waitForTimeout(1000);
    
    // 4. Verify price > 0
    const priceDisplay = page.locator('.total-price, #total-price, .price-display').first();
    const priceText = await priceDisplay.textContent();
    const price = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(price).toBeGreaterThan(0);
    
    // 5. Verify quantity shows '4,000매' (1.0연 = 4,000 sheets)
    const quantityDisplay = page.locator('text=/4,?000매/');
    await expect(quantityDisplay.first()).toBeVisible();
    
    // Note: Price should be higher than 0.5연 but we can't compare without Test 1's price
  });

  test('Test 3: 명함 일반명함 500매 아트지 기본 가격', async ({ page }) => {
    // 1. Navigate to 명함 page
    await page.goto('/mlangprintauto/namecard/');
    
    // 2. Select 규격 = '일반 명함'
    const sizeSelect = page.locator('select[name="MY_type"]');
    await sizeSelect.selectOption({ label: /일반.*명함/ });
    
    // 3. Select 재질 (Section) = '아트지'
    const materialSelect = page.locator('select[name="Section"]');
    await materialSelect.selectOption({ label: /아트지/ });
    
    // 4. Select 인쇄 (POtype) = '단면 4도'
    const printSelect = page.locator('select[name="POtype"]');
    await printSelect.selectOption({ label: /단면.*4도/ });
    
    // 5. Select 수량 = '500'
    const quantitySelect = page.locator('select[name="MY_amount"]');
    await quantitySelect.selectOption({ label: '500' });
    
    // 6. Wait for AJAX
    await page.waitForTimeout(1000);
    
    // 7. Verify price > 10,000원
    const priceDisplay = page.locator('.total-price, #total-price, .price-display').first();
    const priceText = await priceDisplay.textContent();
    const price = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(price).toBeGreaterThan(10000);
  });

  test('Test 4: 봉투 소봉투 1000매 기본 가격', async ({ page }) => {
    // 1. Navigate to 봉투 page
    await page.goto('/mlangprintauto/envelope/');
    
    // 2. Select 규격 = '소봉투'
    const sizeSelect = page.locator('select[name="MY_type"]');
    await sizeSelect.selectOption({ label: /소봉투/ });
    
    // 3. Select 용지 = '모조지'
    const paperSelect = page.locator('select[name="PN_type"]');
    await paperSelect.selectOption({ label: /모조지/ });
    
    // 4. Select 인쇄 = '단면'
    const printSelect = page.locator('select[name="POtype"]');
    await printSelect.selectOption({ label: /단면/ });
    
    // 5. Select 수량 = '1000'
    const quantitySelect = page.locator('select[name="MY_amount"]');
    await quantitySelect.selectOption({ label: '1000' });
    
    // 6. Wait for AJAX
    await page.waitForTimeout(1000);
    
    // 7. Verify price displayed
    const priceDisplay = page.locator('.total-price, #total-price, .price-display').first();
    await expect(priceDisplay).toBeVisible();
    const priceText = await priceDisplay.textContent();
    const price = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
    expect(price).toBeGreaterThan(0);
  });

  test('Test 5: 리플렛 A4 0.5연 + 2단접지 가격', async ({ page }) => {
    // 1. Navigate to 리플렛 page
    await page.goto('/mlangprintauto/leaflet/');
    
    // 2. Select 용지 = '90g 아트지'
    const paperSelect = page.locator('select[name="PN_type"]');
    await paperSelect.selectOption({ label: /90g.*아트지/ });
    
    // 3. Select 규격 = 'A4'
    const sizeSelect = page.locator('select[name="MY_type"]');
    await sizeSelect.selectOption({ label: 'A4' });
    
    // 4. Select 수량 = '0.5연'
    const quantitySelect = page.locator('select[name="MY_amount"]');
    await quantitySelect.selectOption({ label: /0\.5연/ });
    
    // 5. Select 색상 = '컬러'
    const colorSelect = page.locator('select[name="POtype"]');
    await colorSelect.selectOption({ label: /컬러/ });
    
    // 6. Wait for base price calculation
    await page.waitForTimeout(1000);
    
    // 7. Get base price before folding
    const priceDisplay = page.locator('.total-price, #total-price, .price-display').first();
    const basePriceText = await priceDisplay.textContent();
    const basePrice = parseInt(basePriceText?.replace(/[^0-9]/g, '') || '0');
    
    // 8. Select folding_type = '2단접지' if it exists
    const foldingSelect = page.locator('select[name="folding_type"]');
    const foldingExists = await foldingSelect.count() > 0;
    
    if (foldingExists) {
      await foldingSelect.selectOption({ label: /2단접지/ });
      await page.waitForTimeout(1000);
      
      // 9. Verify folding surcharge displayed (40,000원)
      const foldingFee = page.locator('text=/접지.*40,?000/');
      await expect(foldingFee.first()).toBeVisible();
      
      // 10. Check total = base + folding fee
      const totalPriceText = await priceDisplay.textContent();
      const totalPrice = parseInt(totalPriceText?.replace(/[^0-9]/g, '') || '0');
      expect(totalPrice).toBeGreaterThan(basePrice);
      expect(totalPrice).toBe(basePrice + 40000);
    } else {
      // If folding option doesn't exist, just verify base price
      expect(basePrice).toBeGreaterThan(0);
    }
  });

  test('Test 6: 가격 계산 병렬 실행 테스트', async ({ browser }) => {
    // 1. Create 3 independent contexts
    const context1 = await browser.newContext();
    const context2 = await browser.newContext();
    const context3 = await browser.newContext();
    
    const page1 = await context1.newPage();
    const page2 = await context2.newPage();
    const page3 = await context3.newPage();
    
    try {
      // 2. Parallel execution: Calculate 전단지 + 명함 + 봉투 prices
      await Promise.all([
        // Context 1: 전단지 0.5연
        (async () => {
          await page1.goto('/mlangprintauto/inserted/');
          await page1.locator('select[name="PN_type"]').selectOption({ label: /90g.*아트지/ });
          await page1.locator('select[name="MY_type"]').selectOption({ label: 'A4' });
          await page1.locator('select[name="MY_amount"]').selectOption({ label: /0\.5연/ });
          await page1.locator('select[name="POtype"]').selectOption({ label: /컬러/ });
          await page1.waitForTimeout(1000);
        })(),
        
        // Context 2: 명함 500매
        (async () => {
          await page2.goto('/mlangprintauto/namecard/');
          await page2.locator('select[name="MY_type"]').selectOption({ label: /일반.*명함/ });
          await page2.locator('select[name="Section"]').selectOption({ label: /아트지/ });
          await page2.locator('select[name="POtype"]').selectOption({ label: /단면.*4도/ });
          await page2.locator('select[name="MY_amount"]').selectOption({ label: '500' });
          await page2.waitForTimeout(1000);
        })(),
        
        // Context 3: 봉투 1000매
        (async () => {
          await page3.goto('/mlangprintauto/envelope/');
          await page3.locator('select[name="MY_type"]').selectOption({ label: /소봉투/ });
          await page3.locator('select[name="PN_type"]').selectOption({ label: /모조지/ });
          await page3.locator('select[name="POtype"]').selectOption({ label: /단면/ });
          await page3.locator('select[name="MY_amount"]').selectOption({ label: '1000' });
          await page3.waitForTimeout(1000);
        })(),
      ]);
      
      // 3. Verify all 3 prices are correct
      const price1 = await page1.locator('.total-price, #total-price, .price-display').first().textContent();
      const price2 = await page2.locator('.total-price, #total-price, .price-display').first().textContent();
      const price3 = await page3.locator('.total-price, #total-price, .price-display').first().textContent();
      
      const value1 = parseInt(price1?.replace(/[^0-9]/g, '') || '0');
      const value2 = parseInt(price2?.replace(/[^0-9]/g, '') || '0');
      const value3 = parseInt(price3?.replace(/[^0-9]/g, '') || '0');
      
      expect(value1).toBeGreaterThan(0);
      expect(value2).toBeGreaterThan(10000);
      expect(value3).toBeGreaterThan(0);
      
      // 4. Verify quantity displays
      await expect(page1.locator('text=/2,?000매/').first()).toBeVisible();
      
    } finally {
      // 5. Close all contexts
      await context1.close();
      await context2.close();
      await context3.close();
    }
  });
});
