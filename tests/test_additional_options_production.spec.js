const { test, expect } = require('@playwright/test');

test('전단지 추가옵션 가격 업데이트 확인 (프로덕션)', async ({ page }) => {
  // 1. 전단지 페이지 접속
  await page.goto('https://dsp114.co.kr/mlangprintauto/inserted/');
  await page.waitForLoadState('networkidle');
  
  // 2. 기본 옵션 선택 (옵션이 활성화될 수 있도록)
  await page.selectOption('#MY_type', '802'); // 스노우지
  await page.selectOption('#PN_type', '821'); // 150g
  await page.selectOption('#MY_Fsd', '626');  // A5
  await page.selectOption('#POtype', '1');    // 단면
  await page.waitForTimeout(500);
  
  // 3. 수량 1연 선택
  await page.selectOption('#MY_amount', '1');
  await page.waitForTimeout(500);
  
  // 4. 코팅 옵션 활성화
  const coatingCheckbox = page.locator('#coating_enabled');
  await coatingCheckbox.check();
  await page.waitForTimeout(300);
  
  // 5. 단면유광코팅 선택 (1연 기준: 80,000원)
  await page.selectOption('#coating_type', 'single');
  await page.waitForTimeout(300);
  
  // 6. 코팅 가격 확인 (1연 = 80,000원)
  const coatingPrice1 = await page.locator('#coating_price').inputValue();
  console.log('코팅 가격 (1연):', coatingPrice1);
  expect(parseInt(coatingPrice1)).toBe(80000);
  
  // 7. 수량 2연으로 변경
  await page.selectOption('#MY_amount', '2');
  await page.waitForTimeout(500);
  
  // 8. 코팅 가격 자동 업데이트 확인 (2연 = 160,000원)
  const coatingPrice2 = await page.locator('#coating_price').inputValue();
  console.log('코팅 가격 (2연):', coatingPrice2);
  expect(parseInt(coatingPrice2)).toBe(160000);
  
  // 9. 수량 3연으로 변경
  await page.selectOption('#MY_amount', '3');
  await page.waitForTimeout(500);
  
  // 10. 코팅 가격 자동 업데이트 확인 (3연 = 240,000원)
  const coatingPrice3 = await page.locator('#coating_price').inputValue();
  console.log('코팅 가격 (3연):', coatingPrice3);
  expect(parseInt(coatingPrice3)).toBe(240000);
  
  console.log('✅ 수량 변경 시 추가옵션 가격 자동 업데이트 정상 작동!');
});
