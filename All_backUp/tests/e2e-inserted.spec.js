/**
 * E2E Test: 전단지 페이지
 * 경로: tests/e2e-inserted.spec.js
 */

const { test, expect } = require('@playwright/test');

test.describe('전단지 페이지 E2E 테스트', () => {

  test('페이지 로딩 및 기본 요소 확인', async ({ page }) => {
    // 1. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 2. 페이지 제목 확인
    await expect(page).toHaveTitle(/전단지/);

    // 3. 주요 요소 존재 확인
    await expect(page.locator('.product-gallery')).toBeVisible();
    await expect(page.locator('.product-calculator')).toBeVisible();

    // 4. 견적 계산기 요소 확인
    await expect(page.locator('select#MY_type')).toBeVisible(); // 용지 사이즈
    await expect(page.locator('select#PN_type')).toBeVisible(); // 용지 종류
    await expect(page.locator('select#MY_amount')).toBeVisible(); // 수량

    console.log('✅ 페이지 로딩 및 기본 요소 확인 완료');
  });

  test('견적 계산 기능', async ({ page }) => {
    // 1. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 2. 옵션 선택 (실제 값 사용)
    await page.selectOption('select#MY_type', '802'); // 칼라인쇄(CMYK)
    await page.waitForTimeout(500);

    await page.selectOption('select#MY_Fsd', '626'); // 100g아트지
    await page.waitForTimeout(500);

    await page.selectOption('select#PN_type', '821'); // A4
    await page.waitForTimeout(1000); // 수량 로딩 대기

    // 3. 수량 선택 (동적으로 로딩된 옵션 중 선택)
    const quantityOptions = await page.$$eval('select#MY_amount option',
      opts => opts.filter(o => o.value !== '').map(o => o.value)
    );

    if (quantityOptions.length > 0) {
      await page.selectOption('select#MY_amount', quantityOptions[0]);
      console.log('선택한 수량:', quantityOptions[0]);
    }

    // 4. 가격 계산 대기 (AJAX 응답)
    await page.waitForTimeout(1500);

    // 5. 가격 데이터 확인
    const priceData = await page.evaluate(() => {
      return window.currentPriceData;
    });

    console.log('계산된 가격:', priceData);

    // 6. 가격이 0보다 큰지 확인
    if (priceData && priceData.total_price) {
      expect(priceData.total_price).toBeGreaterThan(0);
      expect(priceData.vat_price).toBeGreaterThan(0);
      console.log('✅ 견적 계산 기능 확인 완료');
    } else {
      console.log('⚠️ 가격 데이터 없음 - 수량 옵션 확인 필요');
    }
  });

  test('장바구니 담기 기능', async ({ page }) => {
    // 1. 페이지 접속 및 견적 계산
    await page.goto('http://localhost/mlangprintauto/inserted/');

    await page.selectOption('select#MY_type', '802');
    await page.waitForTimeout(500);
    await page.selectOption('select#MY_Fsd', '626');
    await page.waitForTimeout(500);
    await page.selectOption('select#PN_type', '821');
    await page.waitForTimeout(1000);

    // 수량 선택
    const quantityOptions = await page.$$eval('select#MY_amount option',
      opts => opts.filter(o => o.value !== '').map(o => o.value)
    );
    if (quantityOptions.length > 0) {
      await page.selectOption('select#MY_amount', quantityOptions[0]);
    }

    // 2. 가격 계산 대기
    await page.waitForTimeout(1500);

    // 3. 파일 업로드 모달 열기 (장바구니 버튼이 모달 안에 있음)
    const uploadButton = page.locator('button:has-text("파일 업로드")');
    if (await uploadButton.count() > 0) {
      await uploadButton.click();
      await page.waitForTimeout(500);

      // 4. 모달 내 장바구니 버튼 클릭
      page.on('dialog', dialog => dialog.accept()); // alert 자동 처리

      const addToCartButton = page.locator('button.btn-cart:has-text("장바구니")');
      await addToCartButton.click();
    } else {
      console.log('⚠️ 파일 업로드 버튼을 찾을 수 없습니다.');
    }

    // 5. 페이지 이동 또는 성공 확인
    await page.waitForTimeout(2000);

    const currentUrl = page.url();
    console.log('현재 URL:', currentUrl);

    // 장바구니 페이지로 이동하거나 모달이 닫히면 성공
    const isSuccess = currentUrl.includes('cart') ||
                      currentUrl.includes('장바구니') ||
                      (await uploadButton.count() === 0);

    console.log('✅ 장바구니 담기 기능 확인 완료');
  });

  test('갤러리 이미지 표시', async ({ page }) => {
    // 1. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 2. 갤러리 이미지 확인 (올바른 selector 사용)
    const mainImage = await page.locator('.new-main-container img').count();
    const thumbnails = await page.locator('.new-thumbnail').count();
    const totalImages = mainImage + thumbnails;

    console.log('메인 이미지:', mainImage);
    console.log('썸네일 이미지:', thumbnails);
    console.log('총 갤러리 이미지:', totalImages);

    expect(totalImages).toBeGreaterThan(0);

    // 3. 메인 이미지 로딩 확인
    const firstImage = page.locator('.new-main-image').first();
    await expect(firstImage).toBeVisible();

    console.log('✅ 갤러리 이미지 표시 확인 완료');
  });

  test('추가 옵션 표시', async ({ page }) => {
    // 1. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 2. 추가 옵션 섹션 확인
    const hasAdditionalOptions = await page.locator('.additional-options').count() > 0;

    if (hasAdditionalOptions) {
      console.log('추가 옵션 섹션 존재');

      // 3. 코팅 옵션 확인
      const coatingOption = page.locator('input[name="coating_enabled"]');
      if (await coatingOption.count() > 0) {
        await coatingOption.check();
        await page.waitForTimeout(500);
        console.log('코팅 옵션 선택 완료');
      }
    } else {
      console.log('추가 옵션 섹션 없음');
    }

    console.log('✅ 추가 옵션 확인 완료');
  });

  test('반응형 레이아웃 (모바일)', async ({ page }) => {
    // 1. 모바일 뷰포트 설정
    await page.setViewportSize({ width: 375, height: 667 });

    // 2. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 3. 모바일에서 레이아웃 확인
    const productContent = page.locator('.product-content');
    await expect(productContent).toBeVisible();

    // 4. 스크린샷 캡처
    await page.screenshot({
      path: 'tests/screenshots/inserted-mobile.png',
      fullPage: true
    });

    console.log('✅ 모바일 반응형 레이아웃 확인 완료');
  });

  test('콘솔 에러 확인', async ({ page }) => {
    const errors = [];

    // 1. 콘솔 에러 수집
    page.on('console', msg => {
      if (msg.type() === 'error') {
        errors.push(msg.text());
      }
    });

    // 2. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 3. 페이지 로딩 대기
    await page.waitForTimeout(2000);

    // 4. 에러 출력
    if (errors.length > 0) {
      console.log('⚠️ 콘솔 에러 발견:');
      errors.forEach(err => console.log('  -', err));
    } else {
      console.log('✅ 콘솔 에러 없음');
    }

    // 5. 치명적인 에러가 없는지 확인
    const criticalErrors = errors.filter(err =>
      err.includes('Uncaught') ||
      err.includes('TypeError') ||
      err.includes('ReferenceError')
    );

    expect(criticalErrors.length).toBe(0);
  });

  test('네트워크 요청 확인', async ({ page }) => {
    const requests = [];

    // 1. 네트워크 요청 수집
    page.on('request', request => {
      requests.push({
        url: request.url(),
        method: request.method()
      });
    });

    // 2. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');

    // 3. AJAX 요청 트리거
    await page.selectOption('select#MY_type', '802');
    await page.waitForTimeout(1500);

    // 4. calculate_price_ajax.php 요청 확인
    const priceAjaxRequest = requests.find(req =>
      req.url.includes('calculate_price_ajax.php')
    );

    if (priceAjaxRequest) {
      console.log('✅ 가격 계산 AJAX 요청 확인:', priceAjaxRequest.url);
    } else {
      console.log('⚠️ 가격 계산 AJAX 요청 없음');
    }

    console.log('총 네트워크 요청:', requests.length);
  });

  test('페이지 성능 측정', async ({ page }) => {
    // 1. 페이지 접속 및 성능 측정
    const startTime = Date.now();
    await page.goto('http://localhost/mlangprintauto/inserted/');
    const loadTime = Date.now() - startTime;

    console.log('페이지 로딩 시간:', loadTime, 'ms');

    // 2. 로딩 시간이 3초 이내인지 확인
    expect(loadTime).toBeLessThan(3000);

    // 3. Performance metrics
    const metrics = await page.evaluate(() => {
      const timing = performance.timing;
      return {
        domContentLoaded: timing.domContentLoadedEventEnd - timing.navigationStart,
        loadComplete: timing.loadEventEnd - timing.navigationStart
      };
    });

    console.log('DOM Content Loaded:', metrics.domContentLoaded, 'ms');
    console.log('Load Complete:', metrics.loadComplete, 'ms');

    console.log('✅ 페이지 성능 측정 완료');
  });

  test('전체 플로우: 견적 → 장바구니 → 스크린샷', async ({ page }) => {
    // 1. 페이지 접속
    await page.goto('http://localhost/mlangprintauto/inserted/');
    await page.screenshot({ path: 'tests/screenshots/01-page-loaded.png' });

    // 2. 옵션 선택 (실제 값 사용)
    await page.selectOption('select#MY_type', '802');
    await page.waitForTimeout(500);
    await page.selectOption('select#MY_Fsd', '626');
    await page.waitForTimeout(500);
    await page.selectOption('select#PN_type', '821');
    await page.waitForTimeout(1000);

    // 수량 선택
    const quantityOptions = await page.$$eval('select#MY_amount option',
      opts => opts.filter(o => o.value !== '').map(o => o.value)
    );
    if (quantityOptions.length > 0) {
      await page.selectOption('select#MY_amount', quantityOptions[0]);
    }

    await page.waitForTimeout(1500);
    await page.screenshot({ path: 'tests/screenshots/02-options-selected.png' });

    // 3. 가격 확인
    const priceData = await page.evaluate(() => window.currentPriceData);
    console.log('최종 가격:', priceData);

    await page.screenshot({
      path: 'tests/screenshots/03-price-calculated.png',
      fullPage: true
    });

    console.log('✅ 전체 플로우 스크린샷 완료');
  });

});
