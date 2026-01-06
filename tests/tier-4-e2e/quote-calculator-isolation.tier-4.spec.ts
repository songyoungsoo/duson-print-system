import { test, expect } from '@playwright/test';

/**
 * E2E Test: 견적서와 계산기 격리 검증
 *
 * 목적: 견적서 시스템과 품목별 계산기가 서로 간섭하지 않는다는 것을 입증
 *
 * 테스트 시나리오:
 * 1. 계산기 단독: quotation_temp → quote_items
 * 2. 장바구니 단독: shop_temp → quote_items
 * 3. 혼합 시나리오: 장바구니 + 계산기 → 독립적 저장 및 합산
 * 4. 가격 계산 격리: Phase 3 필드 우선 사용 검증
 * 5. 데이터 무결성: quote_items에 독립적 저장 확인
 */

test.describe('Quote-Calculator Isolation E2E Tests', () => {

    test.beforeEach(async ({ page }) => {
        // 세션 초기화
        await page.goto('http://localhost/');
        await page.evaluate(() => {
            // 쿠키 초기화
            document.cookie.split(";").forEach(c => {
                document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/");
            });
        });

        // 테스트 데이터 정리
        await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=clear_test_data');
        await page.waitForLoadState('networkidle');
        console.log('🧹 테스트 데이터 정리 완료\n');
    });

    /**
     * 테스트 1: 계산기 단독 플로우
     * 계산기 → quotation_temp → 견적서 생성
     */
    test('Scenario 1: Calculator only - quotation_temp isolation', async ({ page }) => {
        console.log('\n🧪 [Test 1] 계산기 단독 플로우 시작');

        // 1. 전단지 계산기 접속
        await page.goto('http://localhost/mlangprintauto/inserted/');
        await page.waitForLoadState('networkidle');
        console.log('✅ 전단지 페이지 로드 완료');

        // 2. 계산기 옵션 선택
        await page.selectOption('select[name="MY_type"]', '64'); // 180g 모조
        await page.selectOption('select[name="MY_Fsd"]', '46x4'); // 46x4
        await page.selectOption('select[name="PN_type"]', '46x188'); // 규격
        await page.selectOption('select[name="POtype"]', '1'); // 단면
        await page.fill('input[name="MY_amount"]', '0.5'); // 0.5연
        console.log('✅ 옵션 선택 완료');

        // 3. 가격 계산
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);
        console.log('✅ 가격 계산 완료');

        // 4. 가격 확인
        const priceText = await page.locator('#result_price').textContent();
        console.log(`📊 계산된 가격: ${priceText}`);
        const calculatedPrice = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
        expect(calculatedPrice).toBeGreaterThan(0);

        // 5. 견적서에 담기 (quotation_temp 저장)
        await page.click('button:has-text("견적서에 담기")');
        await page.waitForTimeout(1500);
        console.log('✅ 견적서에 담기 완료');

        // 6. DB 검증: quotation_temp에 저장되었는지
        const verifyResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=quotation_temp_count');
        const verifyData = await verifyResp!.json();
        console.log(`📊 quotation_temp 품목 수: ${verifyData.data.count}`);
        expect(verifyData.data.count).toBeGreaterThan(0);

        // 7. 견적서 작성 페이지 접속
        await page.goto('http://localhost/mlangprintauto/quote/create.php');
        await page.waitForLoadState('networkidle');
        console.log('✅ 견적서 작성 페이지 접속');

        // 8. 품목 표시 확인
        const itemRows = await page.locator('tr[data-item-id]').count();
        console.log(`📊 표시된 품목 수: ${itemRows}`);
        expect(itemRows).toBeGreaterThanOrEqual(1);

        // 9. 금액 확인
        const supplyPriceText = await page.locator('.price-summary .supply-price').textContent();
        const totalPriceText = await page.locator('.price-summary .total-price').textContent();
        console.log(`📊 공급가: ${supplyPriceText}, 총액: ${totalPriceText}`);

        const displayedSupplyPrice = parseInt(supplyPriceText?.replace(/[^0-9]/g, '') || '0');
        expect(displayedSupplyPrice).toBeGreaterThan(0);

        console.log('✅ [Test 1] 계산기 단독 플로우 완료\n');
    });

    /**
     * 테스트 2: 장바구니 단독 플로우
     * 장바구니 → shop_temp → 견적서 생성
     */
    test('Scenario 2: Cart only - shop_temp isolation', async ({ page }) => {
        console.log('\n🧪 [Test 2] 장바구니 단독 플로우 시작');

        // 1. 명함 페이지 접속
        await page.goto('http://localhost/mlangprintauto/namecard/');
        await page.waitForLoadState('networkidle');
        console.log('✅ 명함 페이지 로드 완료');

        // 2. 옵션 선택
        await page.selectOption('select[name="MY_type"]', '5'); // 랑데부 250g
        await page.selectOption('select[name="Section"]', '86x52'); // 일반형
        await page.fill('input[name="MY_amount"]', '100'); // 100매
        console.log('✅ 옵션 선택 완료');

        // 3. 가격 계산
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);
        console.log('✅ 가격 계산 완료');

        // 4. 가격 확인
        const priceText = await page.locator('.price-display').first().textContent();
        console.log(`📊 계산된 가격: ${priceText}`);
        const calculatedPrice = parseInt(priceText?.replace(/[^0-9]/g, '') || '0');
        expect(calculatedPrice).toBeGreaterThan(0);

        // 5. 장바구니 담기 (shop_temp 저장)
        await page.click('button:has-text("장바구니담기")');
        await page.waitForTimeout(1500);
        console.log('✅ 장바구니 담기 완료');

        // 6. DB 검증: shop_temp에 저장되었는지
        const verifyResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=shop_temp_count');
        const verifyData = await verifyResp!.json();
        console.log(`📊 shop_temp 품목 수: ${verifyData.data.count}`);
        expect(verifyData.data.count).toBeGreaterThan(0);

        // 7. 장바구니에서 견적요청
        await page.goto('http://localhost/mlangprintauto/shop/cart.php');
        await page.waitForLoadState('networkidle');
        console.log('✅ 장바구니 페이지 접속');

        // 8. 견적요청 버튼 클릭
        await page.click('button:has-text("견적요청")');
        await page.waitForLoadState('networkidle');
        console.log('✅ 견적서 작성 페이지 이동');

        // 9. 품목 표시 확인
        const itemRows = await page.locator('tr[data-item-id]').count();
        console.log(`📊 표시된 품목 수: ${itemRows}`);
        expect(itemRows).toBeGreaterThanOrEqual(1);

        // 10. 금액 확인
        const supplyPriceText = await page.locator('.price-summary .supply-price').textContent();
        const totalPriceText = await page.locator('.price-summary .total-price').textContent();
        console.log(`📊 공급가: ${supplyPriceText}, 총액: ${totalPriceText}`);

        const displayedSupplyPrice = parseInt(supplyPriceText?.replace(/[^0-9]/g, '') || '0');
        expect(displayedSupplyPrice).toBeGreaterThan(0);

        console.log('✅ [Test 2] 장바구니 단독 플로우 완료\n');
    });

    /**
     * 테스트 3: 혼합 플로우 (핵심 격리 검증)
     * 장바구니 + 계산기 → 독립적 저장 및 합산
     */
    test('Scenario 3: Mixed flow - cart + calculator isolation', async ({ page }) => {
        console.log('\n🧪 [Test 3] 혼합 플로우 시작 (격리 검증)');

        // === Part 1: 장바구니에 명함 추가 ===
        console.log('\n📦 Step 1: 장바구니에 명함 추가');
        await page.goto('http://localhost/mlangprintauto/namecard/');
        await page.waitForLoadState('networkidle');

        await page.selectOption('select[name="MY_type"]', '5'); // 랑데부 250g
        await page.selectOption('select[name="Section"]', '86x52');
        await page.fill('input[name="MY_amount"]', '100');
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);

        const namecardPriceText = await page.locator('.price-display').first().textContent();
        const namecardPrice = parseInt(namecardPriceText?.replace(/[^0-9]/g, '') || '0');
        console.log(`📊 명함 가격: ${namecardPrice}원`);

        await page.click('button:has-text("장바구니담기")');
        await page.waitForTimeout(1500);
        console.log('✅ 명함 장바구니 추가 완료');

        // === Part 2: 계산기로 전단지 추가 ===
        console.log('\n📦 Step 2: 계산기로 전단지 추가');
        await page.goto('http://localhost/mlangprintauto/inserted/');
        await page.waitForLoadState('networkidle');

        await page.selectOption('select[name="MY_type"]', '64');
        await page.selectOption('select[name="MY_Fsd"]', '46x4');
        await page.selectOption('select[name="PN_type"]', '46x188');
        await page.selectOption('select[name="POtype"]', '1');
        await page.fill('input[name="MY_amount"]', '1');
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);

        const leafletPriceText = await page.locator('#result_price').textContent();
        const leafletPrice = parseInt(leafletPriceText?.replace(/[^0-9]/g, '') || '0');
        console.log(`📊 전단지 가격: ${leafletPrice}원`);

        await page.click('button:has-text("견적서에 담기")');
        await page.waitForTimeout(1500);
        console.log('✅ 전단지 견적서 추가 완료');

        // === Part 3: DB 격리 검증 ===
        console.log('\n🔍 Step 3: DB 격리 검증');

        const shopTempResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=shop_temp_latest');
        const shopTempData = await shopTempResp!.json();
        console.log('📊 shop_temp (명함):', JSON.stringify(shopTempData.data, null, 2));

        const quotationTempResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=quotation_temp_latest');
        const quotationTempData = await quotationTempResp!.json();
        console.log('📊 quotation_temp (전단지):', JSON.stringify(quotationTempData.data, null, 2));

        // 두 테이블이 독립적으로 존재하는지 확인
        expect(shopTempData.data.product_type).toBe('namecard');
        expect(quotationTempData.data.product_type).toBe('inserted');

        // === Part 4: 견적서 생성 페이지에서 합산 확인 ===
        console.log('\n💰 Step 4: 견적서 페이지에서 합산 확인');
        await page.goto('http://localhost/mlangprintauto/quote/create.php?from=cart');
        await page.waitForLoadState('networkidle');

        // 품목 수 확인 (2개 품목)
        const itemRows = await page.locator('tr[data-item-id]').count();
        console.log(`📊 표시된 품목 수: ${itemRows}`);
        expect(itemRows).toBe(2); // 명함 1개 + 전단지 1개

        // 총 금액 확인
        const supplyPriceText = await page.locator('.price-summary .supply-price').textContent();
        const totalPriceText = await page.locator('.price-summary .total-price').textContent();
        const displayedSupplyPrice = parseInt(supplyPriceText?.replace(/[^0-9]/g, '') || '0');
        const displayedTotalPrice = parseInt(totalPriceText?.replace(/[^0-9]/g, '') || '0');

        console.log(`📊 화면 표시 - 공급가: ${displayedSupplyPrice}원, 총액: ${displayedTotalPrice}원`);
        console.log(`📊 개별 가격 - 명함: ${namecardPrice}원, 전단지: ${leafletPrice}원`);

        // 합산 검증 (±10원 오차 허용, 반올림 때문)
        const expectedTotal = namecardPrice + leafletPrice;
        const priceDiff = Math.abs(displayedTotalPrice - expectedTotal);
        console.log(`📊 가격 차이: ${priceDiff}원 (허용: 10원 이내)`);
        expect(priceDiff).toBeLessThanOrEqual(10);

        // === Part 5: 견적서 생성 및 quote_items 검증 ===
        console.log('\n📝 Step 5: 견적서 생성');

        // 고객 정보 입력
        await page.fill('input[name="customer_name"]', 'E2E 테스트 고객');
        await page.fill('input[name="customer_phone"]', '010-1234-5678');

        // 견적서 생성
        await page.click('button:has-text("견적서 생성")');
        await page.waitForTimeout(2000);
        console.log('✅ 견적서 생성 완료');

        // quote_items 검증
        const quoteItemsResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=quote_items_latest');
        const quoteItemsData = await quoteItemsResp!.json();
        console.log('📊 quote_items:', JSON.stringify(quoteItemsData.data, null, 2));

        expect(quoteItemsData.data.count).toBe(2);

        // 두 품목이 서로 다른 source_type을 가지는지 확인
        const sources = quoteItemsData.data.items.map((item: any) => item.source_type);
        expect(sources).toContain('cart');
        expect(sources).toContain('quotation_temp');

        console.log('✅ [Test 3] 혼합 플로우 완료 - 격리 검증 성공\n');
    });

    /**
     * 테스트 4: 가격 계산 격리 (Phase 3 우선 사용)
     * ProductSpecFormatter가 Phase 3 필드를 우선 읽는지 검증
     */
    test('Scenario 4: Price calculation isolation - Phase 3 priority', async ({ page }) => {
        console.log('\n🧪 [Test 4] 가격 계산 격리 검증 시작');

        // 1. 계산기로 품목 추가 (Phase 3 데이터 생성)
        await page.goto('http://localhost/mlangprintauto/inserted/');
        await page.waitForLoadState('networkidle');

        await page.selectOption('select[name="MY_type"]', '64');
        await page.selectOption('select[name="MY_Fsd"]', '46x4');
        await page.selectOption('select[name="PN_type"]', '46x188');
        await page.selectOption('select[name="POtype"]', '1');
        await page.fill('input[name="MY_amount"]', '0.5');
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);

        const originalPrice = await page.locator('#result_price').textContent();
        const originalPriceValue = parseInt(originalPrice?.replace(/[^0-9]/g, '') || '0');
        console.log(`📊 원본 가격 (계산기): ${originalPriceValue}원`);

        await page.click('button:has-text("견적서에 담기")');
        await page.waitForTimeout(1500);

        // 2. DB에서 Phase 3 필드 확인
        const verifyResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=quotation_temp_latest');
        const verifyData = await verifyResp!.json();
        console.log('📊 quotation_temp Phase 3 데이터:', JSON.stringify(verifyData.data, null, 2));

        const { st_price, st_price_vat, price_supply, price_vat, data_version } = verifyData.data;

        // data_version=2 확인
        expect(data_version).toBe('2');
        console.log('✅ data_version=2 (Phase 3) 확인');

        // 3. 견적서 페이지에서 가격 읽기
        await page.goto('http://localhost/mlangprintauto/quote/create.php');
        await page.waitForLoadState('networkidle');

        const displayedPriceText = await page.locator('.price-summary .total-price').textContent();
        const displayedPrice = parseInt(displayedPriceText?.replace(/[^0-9]/g, '') || '0');
        console.log(`📊 화면 표시 가격: ${displayedPrice}원`);

        // 4. Phase 3 필드(price_vat)가 사용되었는지 검증
        const priceVatInt = parseInt(price_vat || '0');
        const priceDiff = Math.abs(displayedPrice - priceVatInt);
        console.log(`📊 price_vat: ${priceVatInt}원, 표시 가격: ${displayedPrice}원, 차이: ${priceDiff}원`);
        expect(priceDiff).toBeLessThanOrEqual(10); // 반올림 오차 허용

        // 5. Legacy 필드(st_price_vat)가 아닌 Phase 3 필드가 사용되었음을 확인
        const stPriceVatFloat = parseFloat(st_price_vat || '0');
        if (stPriceVatFloat !== priceVatInt) {
            console.log('✅ Phase 3 필드(price_vat) 우선 사용 확인');
            console.log(`   Legacy(st_price_vat): ${stPriceVatFloat}원 (미사용)`);
            console.log(`   Phase 3(price_vat): ${priceVatInt}원 (사용됨)`);
        }

        console.log('✅ [Test 4] 가격 계산 격리 검증 완료\n');
    });

    /**
     * 테스트 5: 견적서 생성 후 데이터 무결성
     * 장바구니와 계산기 품목이 quote_items에 독립적으로 저장되는지 확인
     */
    test('Scenario 5: Data integrity after quote creation', async ({ page }) => {
        console.log('\n🧪 [Test 5] 데이터 무결성 검증 시작');

        // 1. 장바구니 + 계산기 품목 추가
        console.log('📦 Step 1: 혼합 품목 추가');

        // 명함 추가
        await page.goto('http://localhost/mlangprintauto/namecard/');
        await page.waitForLoadState('networkidle');
        await page.selectOption('select[name="MY_type"]', '5');
        await page.selectOption('select[name="Section"]', '86x52');
        await page.fill('input[name="MY_amount"]', '200');
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);
        await page.click('button:has-text("장바구니담기")');
        await page.waitForTimeout(1500);

        // 전단지 추가
        await page.goto('http://localhost/mlangprintauto/inserted/');
        await page.waitForLoadState('networkidle');
        await page.selectOption('select[name="MY_type"]', '64');
        await page.selectOption('select[name="MY_Fsd"]', '46x4');
        await page.selectOption('select[name="PN_type"]', '46x188');
        await page.selectOption('select[name="POtype"]', '2'); // 양면
        await page.fill('input[name="MY_amount"]', '1.5');
        await page.click('button:has-text("가격계산")');
        await page.waitForTimeout(2000);
        await page.click('button:has-text("견적서에 담기")');
        await page.waitForTimeout(1500);

        // 2. 견적서 생성
        console.log('📝 Step 2: 견적서 생성');
        await page.goto('http://localhost/mlangprintauto/quote/create.php?from=cart');
        await page.waitForLoadState('networkidle');

        await page.fill('input[name="customer_name"]', '데이터 무결성 테스트');
        await page.fill('input[name="customer_phone"]', '010-9999-8888');
        await page.click('button:has-text("견적서 생성")');
        await page.waitForTimeout(2000);

        // 3. quote_items 데이터 무결성 검증
        console.log('🔍 Step 3: 데이터 무결성 검증');

        const integrityResp = await page.goto('http://localhost/mlangprintauto/quote/api/test_verify.php?action=phase3_validation');
        const integrityData = await integrityResp!.json();
        console.log('📊 데이터 무결성 검증 결과:', JSON.stringify(integrityData.data, null, 2));

        // 모든 품목이 VALID 또는 LEGACY 상태여야 함
        expect(integrityData.data.has_invalid).toBe(false);

        // 명함(cart)과 전단지(quotation_temp)가 모두 존재
        const productTypes = integrityData.data.items.map((item: any) => item.product_type);
        expect(productTypes).toContain('namecard');
        expect(productTypes).toContain('inserted');

        // source_type이 올바른지 확인
        const sourceTypes = integrityData.data.items.map((item: any) => item.source_type);
        expect(sourceTypes).toContain('cart'); // 명함
        expect(sourceTypes).toContain('quotation_temp'); // 전단지

        console.log('✅ [Test 5] 데이터 무결성 검증 완료\n');
    });

    test.afterEach(async ({ page }) => {
        // 테스트 후 정리
        console.log('🧹 테스트 정리 완료');
        await page.close();
    });
});
