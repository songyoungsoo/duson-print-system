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

    // 옵션 선택 드롭다운 존재 확인 (가장 기본적인 요소)
    const selects = page.locator('select');
    await expect(selects.first()).toBeVisible();

    // 주문/업로드 버튼 존재 확인 (각 제품마다 버튼 텍스트가 다를 수 있음)
    const actionButton = page.locator('button:has-text("업로드"), button:has-text("주문"), button:has-text("장바구니"), button:has-text("담기")');
    await expect(actionButton.first()).toBeVisible();

    // 가격 표시 영역 확인 (금액이 표시되는 영역)
    const priceDisplay = page.locator('text=/[0-9,]+원/');
    const isPriceVisible = await priceDisplay.count() > 0;
    expect(isPriceVisible).toBeTruthy();
  });
});

// 추가 검증: 모든 페이지에서 공통 요소 확인
test('모든 제품 페이지 공통 요소 확인', async ({ page }) => {
  for (const { url, name } of products.slice(0, 3)) { // 샘플로 3개만
    await page.goto(url);
    await page.waitForLoadState('networkidle');

    // 로고/홈 링크 확인 - 짧은 타임아웃으로 빠르게 확인
    const logo = page.locator('a[href="/"], img[alt*="로고"]');
    await expect(logo.first()).toBeVisible({ timeout: 5000 });

    // 제품 네비게이션 링크 확인 (상단 메뉴 링크들)
    const productLinks = page.locator('a:has-text("전단지"), a:has-text("명함"), a:has-text("스티커")');
    const hasProductLinks = await productLinks.count() > 0;
    expect(hasProductLinks).toBeTruthy();

    console.log(`✅ ${name} 페이지 공통 요소 확인 완료`);
  }
});
