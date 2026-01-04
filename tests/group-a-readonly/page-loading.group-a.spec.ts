import { test, expect } from '@playwright/test';

/**
 * Group A-1: 제품 페이지 로딩 테스트
 *
 * 특징:
 * - 완전 독립적 (상태 변경 없음)
 * - 11개 테스트 모두 병렬 실행 가능
 * - DB/파일 변경 없음
 */

// 제품 목록 정의
const products = [
  { code: 'inserted', name: '전단지', url: '/mlangprintauto/inserted/' },
  { code: 'namecard', name: '명함', url: '/mlangprintauto/namecard/' },
  { code: 'envelope', name: '봉투', url: '/mlangprintauto/envelope/' },
  { code: 'sticker_new', name: '스티커', url: '/mlangprintauto/sticker_new/' },
  { code: 'msticker', name: '자석스티커', url: '/mlangprintauto/msticker/' },
  { code: 'cadarok', name: '카다록', url: '/mlangprintauto/cadarok/' },
  { code: 'littleprint', name: '포스터', url: '/mlangprintauto/littleprint/' },
  { code: 'merchandisebond', name: '상품권', url: '/mlangprintauto/merchandisebond/' },
  { code: 'ncrflambeau', name: '양식지', url: '/mlangprintauto/ncrflambeau/' },
  { code: 'leaflet', name: '리플렛', url: '/mlangprintauto/leaflet/' },
];

// 각 제품별로 독립적인 테스트 생성 (병렬 실행)
products.forEach(({ code, name, url }) => {
  test(`${name} 페이지 로딩 테스트`, async ({ page }) => {
    // 페이지 이동
    await page.goto(url);

    // 페이지 제목 확인 (제품명 포함)
    await expect(page).toHaveTitle(new RegExp(name));

    // 가격 계산기 폼 존재 확인
    const priceForm = page.locator('form#orderForm, form[name="choiceForm"]');
    await expect(priceForm).toBeVisible();

    // 옵션 선택 드롭다운 존재 확인
    const selects = page.locator('select');
    await expect(selects.first()).toBeVisible();

    // 장바구니 담기 버튼 존재 확인
    const addToCartButton = page.locator('button:has-text("장바구니"), button:has-text("담기")');
    await expect(addToCartButton.first()).toBeVisible();

    // 가격 표시 영역 확인
    const priceDisplay = page.locator('.price-display, #total-price, .total-amount');
    const isPriceVisible = await priceDisplay.count() > 0;
    expect(isPriceVisible).toBeTruthy();
  });
});

// 추가 검증: 모든 페이지에서 공통 요소 확인
test('모든 제품 페이지 공통 요소 확인', async ({ page }) => {
  for (const { url, name } of products.slice(0, 3)) { // 샘플로 3개만
    await page.goto(url);

    // 로고/홈 링크 확인
    const logo = page.locator('a[href*="index"], img[alt*="로고"]');
    await expect(logo.first()).toBeVisible();

    // 네비게이션 메뉴 확인
    const nav = page.locator('nav, .navigation, .menu');
    const hasNav = await nav.count() > 0;
    expect(hasNav).toBeTruthy();

    console.log(`✅ ${name} 페이지 공통 요소 확인 완료`);
  }
});
