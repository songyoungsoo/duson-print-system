import { test, expect } from '@playwright/test';

test('KB 에스크로 정상 작동 확인', async ({ page, context }) => {
  await page.goto('https://dsp114.co.kr/');
  await page.waitForLoadState('networkidle');
  
  // 푸터까지 스크롤
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.waitForTimeout(500);
  
  // KB 에스크로 링크 찾기
  const kbLink = page.locator('a[title="KB 에스크로 가입 사실 확인"]');
  await expect(kbLink).toBeVisible();
  console.log('✅ KB 에스크로 링크 발견');
  
  // 폼이 1개만 있는지 확인
  const forms = await page.locator('form[name="KB_AUTHMARK_FORM"]').count();
  console.log('폼 개수:', forms);
  expect(forms).toBe(1);
  console.log('✅ KB_AUTHMARK_FORM 1개만 존재 (정상)');
  
  // 폼 파라미터 확인 (footer.php의 올바른 값)
  const form = page.locator('form[name="KB_AUTHMARK_FORM"]').first();
  const pageParam = await form.locator('input[name="page"]').getAttribute('value');
  const ccParam = await form.locator('input[name="cc"]').getAttribute('value');
  
  console.log('폼 파라미터:');
  console.log('  page:', pageParam);
  console.log('  cc:', ccParam);
  
  expect(pageParam).toBe('C021590');
  expect(ccParam).toBe('b034066:b035526');
  console.log('✅ 올바른 파라미터 확인 (footer.php)');
  
  // 팝업 테스트
  const popupPromise = context.waitForEvent('page');
  await kbLink.click();
  console.log('🔗 KB 에스크로 링크 클릭');
  
  const popup = await popupPromise;
  console.log('✅ 팝업창 열림');
  
  await popup.waitForLoadState('domcontentloaded', { timeout: 10000 });
  const popupUrl = popup.url();
  console.log('팝업 URL:', popupUrl);
  
  // okbfex.kbstar.com으로 이동했는지 확인
  expect(popupUrl).toContain('okbfex.kbstar.com');
  console.log('✅ 올바른 KB 에스크로 서버로 이동 (okbfex.kbstar.com)');
  
  await popup.close();
  console.log('🎉 KB 에스크로 연결 정상 작동!');
});
