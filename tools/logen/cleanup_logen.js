const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();
  page.on('dialog', async (d) => { console.log('Dialog:', d.message()); await d.accept(); });

  // 로그인
  await page.goto('https://logis.ilogen.com/');
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(2000);
  await page.fill('#user\\.id', '23058114');
  await page.fill('#user\\.pw', 'du1830/*');
  await page.locator('a[onclick="basicLogin()"]').click();
  await page.waitForLoadState('networkidle');
  await page.waitForTimeout(3000);
  await page.evaluate(() => {
    document.querySelectorAll('.popmodal-back, .modalContainer').forEach(el => el.style.display = 'none');
  }).catch(() => {});

  // 메뉴 이동
  await page.locator('a.toggle-menu:has-text("예약관리")').first().click({ force: true });
  await page.waitForTimeout(1000);
  await page.locator('li.menu-item:has-text("주문등록/출력(복수건)")').first().click({ force: true });
  await page.waitForTimeout(4000);

  const cf = page.frames().find(f => f.url().includes('lrm01f0040'));
  if (!cf) { console.log('No contentFrame'); await browser.close(); return; }

  // showMessage 오버라이드: OKCANCEL 콜백 자동 실행
  // fn_updateDelYn은 showMessage('MSG133', 'W', 'OKCANCEL', params, callback) 사용
  // callback이 실행되어야 실제 삭제 AJAX가 호출됨
  const overrideShowMessage = async () => {
    await cf.evaluate(() => {
      const origShowMessage = window.showMessage;
      window.showMessage = function(msgId, type, buttons, params, callback) {
        console.log('showMessage intercepted:', msgId, buttons);
        if (buttons === 'OKCANCEL' && typeof callback === 'function') {
          console.log('Auto-confirming OKCANCEL callback');
          callback();
          return;
        }
        if (typeof callback === 'function' && (buttons === 'OK' || !buttons)) {
          callback();
          return;
        }
        // 다른 모달은 무시
      };
      window.confirm = () => true;
      window.alert = () => {};
    }).catch(() => {});
  };

  await overrideShowMessage();

  // 조회
  await cf.evaluate(() => fn_retrieve('A')).catch(() => {});
  await page.waitForTimeout(3000);

  // 모달 정리
  for (const f of [cf, page]) {
    await f.evaluate(() => {
      document.querySelectorAll('button').forEach(b => { if (b.textContent.trim() === '확인' && b.offsetParent) b.click(); });
      document.querySelectorAll('.popmodal-back,.modalContainer').forEach(e => e.style.display = 'none');
    }).catch(() => {});
  }

  // 현재 건수 확인
  const getCounts = async () => {
    return await cf.evaluate(() => {
      const mi = document.getElementById('tabNotPrintLabel');
      const prt = document.getElementById('tabPrtLabel');
      return {
        mi: mi ? (mi.textContent.match(/(\d+)/) || [0,0])[1] : 0,
        prt: prt ? (prt.textContent.match(/(\d+)/) || [0,0])[1] : 0,
      };
    });
  };

  let counts = await getCounts();
  console.log(`현재 상태: 미출력 ${counts.mi}건, 출력완료 ${counts.prt}건`);

  // ── 미출력 탭 삭제 ──
  if (parseInt(counts.mi) > 0) {
    console.log('\n=== 미출력 탭 삭제 ===');
    // 탭 전환
    await cf.evaluate(() => {
      const r = document.getElementById('tabNotPrint');
      if (r) { r.checked = true; r.click(); if (typeof fn_selectData === 'function') fn_selectData(r); }
    });
    await page.waitForTimeout(2000);

    // showMessage 오버라이드 재적용
    await overrideShowMessage();

    // 전체 선택
    const miChecked = await cf.evaluate(() => {
      sheet4.setAllCheck('isCheck', true);
      return sheet4.getRowsByChecked('isCheck').length;
    }).catch(() => 0);
    console.log(`  전체 선택: ${miChecked}건`);

    if (miChecked > 0) {
      console.log('  삭제 호출 중 (showMessage 자동확인)...');
      await cf.evaluate(() => fn_updateDelYn('Y')).catch(e => console.log('  호출 결과:', e.message));
      // AJAX 완료 대기 (getScanCnt → callback → updateFixCustFileDelYn → callback)
      await page.waitForTimeout(8000);
      console.log('  삭제 완료 대기 중...');
    }
  }

  // 재조회 (showMessage callback에서 fn_retrieve가 호출되지만, override했으므로 직접 호출)
  await overrideShowMessage();
  await cf.evaluate(() => fn_retrieve('A')).catch(() => {});
  await page.waitForTimeout(3000);

  counts = await getCounts();
  console.log(`\n미출력 삭제 후: 미출력 ${counts.mi}건, 출력완료 ${counts.prt}건`);

  // ── 출력완료 탭 삭제 ──
  if (parseInt(counts.prt) > 0) {
    console.log('\n=== 출력완료 탭 삭제 ===');
    await cf.evaluate(() => {
      const r = document.getElementById('tabPrt');
      if (r) { r.checked = true; r.click(); if (typeof fn_selectData === 'function') fn_selectData(r); }
    });
    await page.waitForTimeout(2000);

    await overrideShowMessage();

    const prtChecked = await cf.evaluate(() => {
      sheet5.setAllCheck('isCheck', true);
      return sheet5.getRowsByChecked('isCheck').length;
    }).catch(() => 0);
    console.log(`  전체 선택: ${prtChecked}건`);

    if (prtChecked > 0) {
      console.log('  삭제 호출 중 (showMessage 자동확인)...');
      await cf.evaluate(() => fn_updateDelYn('Y')).catch(e => console.log('  호출 결과:', e.message));
      await page.waitForTimeout(8000);
      console.log('  삭제 완료 대기 중...');
    }
  }

  // 최종 재조회
  await overrideShowMessage();
  await cf.evaluate(() => fn_retrieve('A')).catch(() => {});
  await page.waitForTimeout(3000);

  counts = await getCounts();
  console.log(`\n최종 결과: 미출력 ${counts.mi}건, 출력완료 ${counts.prt}건`);

  if (parseInt(counts.mi) === 0 && parseInt(counts.prt) === 0) {
    console.log('\n✅ 로젠 데이터 정리 완료!');
  } else {
    console.log('\n⚠️ 아직 데이터가 남아있습니다.');
  }

  await browser.close();
})();
