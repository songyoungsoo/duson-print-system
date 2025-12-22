/**
 * 디버그: 실제 옵션값 추출
 * 경로: tests/debug-options.spec.js
 */

const { test } = require('@playwright/test');

test('전단지 페이지 실제 옵션값 추출', async ({ page }) => {
  // 1. 페이지 접속
  await page.goto('http://localhost/mlangprintauto/inserted/');

  // 2. 페이지 로딩 대기
  await page.waitForTimeout(2000);

  // 3. 용지 사이즈 (MY_type) 옵션 추출
  const colorOptions = await page.$$eval('select#MY_type option',
    opts => opts.map(o => ({ value: o.value, text: o.textContent.trim() }))
  );
  console.log('\n=== 용지 사이즈 (MY_type) 옵션 ===');
  colorOptions.forEach((opt, idx) => {
    console.log(`${idx + 1}. value="${opt.value}" text="${opt.text}"`);
  });

  // 4. 용지 종류 선택 후 (MY_Fsd) 옵션 추출
  if (colorOptions.length > 0) {
    await page.selectOption('select#MY_type', colorOptions[0].value);
    await page.waitForTimeout(1000);

    const paperTypeOptions = await page.$$eval('select#MY_Fsd option',
      opts => opts.map(o => ({ value: o.value, text: o.textContent.trim() }))
    );
    console.log('\n=== 용지 종류 (MY_Fsd) 옵션 ===');
    paperTypeOptions.forEach((opt, idx) => {
      console.log(`${idx + 1}. value="${opt.value}" text="${opt.text}"`);
    });

    // 5. 용지 규격 (PN_type) 옵션 추출
    if (paperTypeOptions.length > 0) {
      await page.selectOption('select#MY_Fsd', paperTypeOptions[0].value);
      await page.waitForTimeout(1000);

      const paperSizeOptions = await page.$$eval('select#PN_type option',
        opts => opts.map(o => ({ value: o.value, text: o.textContent.trim() }))
      );
      console.log('\n=== 용지 규격 (PN_type) 옵션 ===');
      paperSizeOptions.forEach((opt, idx) => {
        console.log(`${idx + 1}. value="${opt.value}" text="${opt.text}"`);
      });

      // 6. 수량 (MY_amount) 옵션 추출
      if (paperSizeOptions.length > 0) {
        await page.selectOption('select#PN_type', paperSizeOptions[0].value);
        await page.waitForTimeout(1000);

        const quantityOptions = await page.$$eval('select#MY_amount option',
          opts => opts.map(o => ({ value: o.value, text: o.textContent.trim() }))
        );
        console.log('\n=== 수량 (MY_amount) 옵션 ===');
        quantityOptions.forEach((opt, idx) => {
          console.log(`${idx + 1}. value="${opt.value}" text="${opt.text}"`);
        });
      }
    }
  }

  // 7. 갤러리 구조 확인
  const galleryHTML = await page.evaluate(() => {
    const gallery = document.querySelector('.product-gallery');
    if (!gallery) return 'Gallery not found';

    const images = gallery.querySelectorAll('img');
    return {
      galleryExists: true,
      imageCount: images.length,
      imageSelectors: Array.from(images).map(img => ({
        src: img.src,
        class: img.className,
        parent: img.parentElement.className
      }))
    };
  });

  console.log('\n=== 갤러리 구조 ===');
  console.log(JSON.stringify(galleryHTML, null, 2));

  // 8. 스크린샷
  await page.screenshot({
    path: 'tests/screenshots/debug-page-structure.png',
    fullPage: true
  });

  console.log('\n✅ 옵션값 추출 완료');
  console.log('스크린샷: tests/screenshots/debug-page-structure.png');
});
