import { test, expect } from '@playwright/test';

test('푸터 로고 이미지 확인', async ({ page }) => {
  // 메인 페이지 접속
  await page.goto('https://dsp114.co.kr/');
  await page.waitForLoadState('networkidle');
  
  // 푸터까지 스크롤
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.waitForTimeout(500);
  
  // 공정거래위원회 로고 확인
  const ftcLogo = page.locator('.ftc-logo');
  const ftcSrc = await ftcLogo.getAttribute('src');
  console.log('공정거래위원회 로고 src:', ftcSrc);
  expect(ftcSrc).toBe('/images/logo-ftc.png');
  
  // 금융결제원 로고 확인
  const kftcLogo = page.locator('.kftc-logo');
  const kftcSrc = await kftcLogo.getAttribute('src');
  console.log('금융결제원 로고 src:', kftcSrc);
  expect(kftcSrc).toBe('/images/logo-kftc.png');
  
  // CSS 크기 확인
  const ftcBox = await ftcLogo.boundingBox();
  const kftcBox = await kftcLogo.boundingBox();
  
  console.log('공정거래위원회 로고 크기:', ftcBox?.width, '×', ftcBox?.height);
  console.log('금융결제원 로고 크기:', kftcBox?.width, '×', kftcBox?.height);
  
  // 크기 검증 (약간의 오차 허용)
  expect(ftcBox?.width).toBeCloseTo(107, 2);
  expect(ftcBox?.height).toBeCloseTo(35, 2);
  expect(kftcBox?.width).toBeCloseTo(98, 2);
  expect(kftcBox?.height).toBeCloseTo(35, 2);
  
  console.log('✅ 푸터 로고 이미지 및 크기 정상!');
});
