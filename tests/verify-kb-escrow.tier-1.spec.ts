import { test, expect } from '@playwright/test';

test('KB 에스크로 링크 동작 확인', async ({ page, context }) => {
  // 팝업 차단 비활성화를 위한 컨텍스트 설정은 이미 되어있음
  
  // 메인 페이지 접속
  await page.goto('https://dsp114.co.kr/');
  await page.waitForLoadState('networkidle');
  
  // 푸터까지 스크롤
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.waitForTimeout(500);
  
  // KB 에스크로 링크 찾기
  const kbLink = page.locator('a[title="KB 에스크로 가입 사실 확인"]');
  await expect(kbLink).toBeVisible();
  console.log('✅ KB 에스크로 링크 발견');
  
  // href 확인
  const href = await kbLink.getAttribute('href');
  console.log('KB 에스크로 href:', href);
  
  // 폼 존재 확인
  const form = page.locator('form[name="KB_AUTHMARK_FORM"]');
  await expect(form).toBeAttached();
  console.log('✅ KB_AUTHMARK_FORM 존재 확인');
  
  // 폼 파라미터 확인
  const pageParam = await page.locator('input[name="page"]').getAttribute('value');
  const ccParam = await page.locator('input[name="cc"]').getAttribute('value');
  const mHValue = await page.locator('input[name="mHValue"]').getAttribute('value');
  
  console.log('폼 파라미터:');
  console.log('  page:', pageParam);
  console.log('  cc:', ccParam);
  console.log('  mHValue:', mHValue);
  
  // 새 탭이 열리는지 확인
  const popupPromise = context.waitForEvent('page');
  
  await kbLink.click();
  console.log('🔗 KB 에스크로 링크 클릭');
  
  try {
    const popup = await popupPromise;
    console.log('✅ 팝업창 열림');
    
    // 팝업 URL 확인
    await popup.waitForLoadState('domcontentloaded', { timeout: 10000 });
    const popupUrl = popup.url();
    console.log('팝업 URL:', popupUrl);
    
    // KB 사이트로 이동했는지 확인
    if (popupUrl.includes('kbstar.com') || popupUrl.includes('okbfex')) {
      console.log('✅ KB 사이트로 정상 이동');
    } else {
      console.log('⚠️ 예상치 못한 URL:', popupUrl);
    }
    
    // 팝업 타이틀 확인
    const popupTitle = await popup.title();
    console.log('팝업 타이틀:', popupTitle);
    
    await popup.close();
  } catch (error) {
    console.error('❌ 팝업 열기 실패:', error);
    throw error;
  }
});
