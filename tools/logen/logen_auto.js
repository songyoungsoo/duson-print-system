/**
 * 로젠택배 자동 운송장 등록 스크립트
 *
 * 워크플로우:
 * 1. delivery_manager.php에서 로젠 엑셀 다운로드
 * 2. 로젠 iLOGEN 사이트 로그인
 * 3. 주문등록/출력(복수건) → 파일 업로드 → 서버전송
 * 4. 결과 엑셀 다운로드 (운송장번호 배정)
 * 5. delivery_manager.php에 운송장 일괄 등록
 *
 * 사용법:
 *   node logen_auto.js --date-from=2026-02-01 --date-to=2026-02-09
 *   node logen_auto.js --production --date-from=2026-02-10 --date-to=2026-02-10
 *   node logen_auto.js --password=xxxx --status=pending
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');
const readline = require('readline');
const XLSX = require('xlsx');

const crypto = require('crypto');

// ── 설정 ──────────────────────────────────────────
const CONFIG = {
  // 관리자 사이트
  adminBase: 'http://localhost',
  prodBase: 'https://dsp114.co.kr',
  deliveryManagerPath: '/shop_admin/delivery_manager.php',
  importWaybillPath: '/tools/logen/import_waybill.php',
  embedSecret: 'duson_embed_2026_secret',

  // 로젠 iLOGEN
  logenLoginUrl: 'https://logis.ilogen.com/',
  logenCompanyCode: '51',  // 시스템 코드 (login-51b.html)
  logenUser: '23058114',   // 상점번호 = 아이디
  logenDefaultPass: 'du1830/*',  // 기본 비밀번호

  // 파일 경로
  downloadDir: path.join(__dirname, 'downloads'),
  screenshotDir: path.join(__dirname, 'downloads'),
};

// ── CLI 인자 파싱 ──────────────────────────────────
function parseArgs() {
  const args = {};
  for (const arg of process.argv.slice(2)) {
    const match = arg.match(/^--(\w[\w-]*)(?:=(.*))?$/);
    if (match) {
      args[match[1]] = match[2] !== undefined ? match[2] : true;
    }
  }

  const today = new Date();
  const weekAgo = new Date(today);
  weekAgo.setDate(weekAgo.getDate() - 7);

  return {
    dateFrom: args['date-from'] || formatDate(weekAgo),
    dateTo: args['date-to'] || formatDate(today),
    status: args['status'] || 'pending',
    password: args['password'] || null,
    headless: args['headless'] === 'true' || args['headless'] === true,
    production: args['production'] === true || args['production'] === 'true',
  };
}

function formatDate(d) {
  return d.toISOString().split('T')[0];
}

// ── 비밀번호 프롬프트 ────────────────────────────────
function promptPassword() {
  return new Promise((resolve) => {
    const rl = readline.createInterface({
      input: process.stdin,
      output: process.stdout,
    });
    rl.question('로젠 iLOGEN 비밀번호: ', (answer) => {
      rl.close();
      resolve(answer.trim());
    });
  });
}

// ── HTML .xls → 실제 .xlsx 변환 ──────────────────────
function convertHtmlXlsToXlsx(htmlXlsPath) {
  console.log('  🔄 HTML .xls → .xlsx 변환 중...');
  const html = fs.readFileSync(htmlXlsPath, 'utf-8');

  // HTML 테이블에서 데이터 추출
  const wb = XLSX.read(html, { type: 'string' });
  const xlsxPath = htmlXlsPath.replace(/\.xls$/i, '.xlsx');
  XLSX.writeFile(wb, xlsxPath);

  const size = fs.statSync(xlsxPath).size;
  console.log(`  ✅ 변환 완료: ${path.basename(xlsxPath)} (${(size / 1024).toFixed(1)} KB)`);
  return xlsxPath;
}

// ── 로젠 사이트 모달 모두 닫기 ─────────────────────────
async function closeAllModals(page) {
  // 1. 모든 모달의 닫기/확인 버튼 클릭 (뒤에서부터 - z-index 높은 것 먼저)
  const modalBtns = page.locator('.modalContainer button.close, .modalContainer button.btn');
  const count = await modalBtns.count();
  for (let i = count - 1; i >= 0; i--) {
    if (await modalBtns.nth(i).isVisible().catch(() => false)) {
      await modalBtns.nth(i).click({ force: true }).catch(() => {});
      await page.waitForTimeout(300);
    }
  }
  // 2. swal 팝업 닫기
  const swalBtn = page.locator('.swal-button, .swal-button--confirm').first();
  if (await swalBtn.isVisible().catch(() => false)) {
    await swalBtn.click({ force: true }).catch(() => {});
    await page.waitForTimeout(300);
  }
  // 3. popmodal-back 오버레이 강제 숨김
  await page.evaluate(() => {
    document.querySelectorAll('.popmodal-back').forEach(el => {
      el.style.display = 'none';
    });
    document.querySelectorAll('.modalContainer').forEach(el => {
      el.style.display = 'none';
    });
  }).catch(() => {});
}

// ── 스크린샷 저장 ──────────────────────────────────
async function saveScreenshot(page, name) {
  const filePath = path.join(CONFIG.screenshotDir, `${name}_${Date.now()}.png`);
  await page.screenshot({ path: filePath, fullPage: true });
  console.log(`  📸 스크린샷: ${filePath}`);
}

// ── Step 1: 배송관리에서 로젠 엑셀 다운로드 (HTTP 직접 호출) ────────────
async function step1_downloadLogenExcel(browser, opts) {
  console.log('\n═══ Step 1: 배송관리 로젠 엑셀 다운로드 ═══');

  // _eauth 토큰 생성 (embed 인증)
  const today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
  const eauth = crypto.createHmac('sha256', CONFIG.embedSecret)
    .update(CONFIG.deliveryManagerPath + today)
    .digest('hex');

  const postData = `action=export_logen&date_from=${opts.dateFrom}&date_to=${opts.dateTo}&export_status=${opts.status}`;
  const baseUrl = opts.production ? CONFIG.prodBase : CONFIG.adminBase;
  const url = `${baseUrl}${CONFIG.deliveryManagerPath}?_eauth=${eauth}`;

  console.log(`  📥 엑셀 다운로드 (${opts.dateFrom} ~ ${opts.dateTo}, 상태: ${opts.status})...`);
  if (opts.production) console.log(`  🌐 프로덕션 모드: ${baseUrl}`);

  const httpModule = url.startsWith('https') ? require('https') : require('http');
  const downloadPath = await new Promise((resolve, reject) => {
    const req = httpModule.request(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Content-Length': Buffer.byteLength(postData),
      },
    }, (res) => {
      if (res.statusCode === 302 || res.statusCode === 301) {
        reject(new Error('인증 실패 (리다이렉트). _eauth 토큰 확인 필요'));
        return;
      }
      if (res.statusCode !== 200) {
        reject(new Error(`HTTP ${res.statusCode}`));
        return;
      }

      // Content-Disposition에서 파일명 추출
      const cd = res.headers['content-disposition'] || '';
      const fnMatch = cd.match(/filename="?([^";\n]+)"?/);
      const filename = fnMatch ? fnMatch[1] : `logen_${today.replace(/-/g, '')}.xls`;
      const filePath = path.join(CONFIG.downloadDir, filename);

      const chunks = [];
      res.on('data', chunk => chunks.push(chunk));
      res.on('end', () => {
        const data = Buffer.concat(chunks);
        if (data.length < 100) {
          reject(new Error('다운로드된 파일이 비어있습니다 (발송 대기 건이 없을 수 있습니다)'));
          return;
        }
        fs.writeFileSync(filePath, data);
        console.log(`  ✅ 다운로드 완료: ${filename} (${(data.length / 1024).toFixed(1)} KB)`);
        resolve(filePath);
      });
    });
    req.on('error', reject);
    req.write(postData);
    req.end();
  });

  return downloadPath;
}

// ── Step 2-6: 로젠 iLOGEN 사이트 자동화 ──────────────
async function step2to6_logenProcess(browser, excelPath, logenPassword) {
  console.log('\n═══ Step 2: 로젠 iLOGEN 로그인 ═══');

  const context = await browser.newContext({
    acceptDownloads: true,
    viewport: { width: 1920, height: 1080 },
  });
  const page = await context.newPage();

  // dialog 자동 처리 (confirm/alert)
  page.on('dialog', async (dialog) => {
    console.log(`  💬 Dialog [${dialog.type()}]: ${dialog.message()}`);
    await dialog.accept();
  });

  // Step 2: 로그인 페이지 접속 (직접 로그인 페이지 URL)
  try {
    await page.goto('https://logis.ilogen.com/');
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    console.log(`  🌐 로젠 사이트 접속 완료 (${page.url()})`);

    // 로그인 폼 작성
    // 입력 필드: #user.id, #user.pw (CSS에서 dot은 이스케이프 필요)
    // 상점번호(23058114)가 아이디, companyCode는 시스템 기본값 "51" 유지
    await page.fill('#user\\.id', CONFIG.logenUser);
    await page.fill('#user\\.pw', logenPassword);

    console.log(`  📝 로그인 폼 입력 완료 (ID: ${CONFIG.logenUser})`);

    // 로그인 버튼: <a onclick="basicLogin()"> 안의 div.login-btn
    await page.locator('a[onclick="basicLogin()"]').click();

    // 로그인 후 페이지 로드 대기
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);

    console.log(`  📍 로그인 후 URL: ${page.url()}`);

    // 로그인 실패 체크: 에러 모달 확인 (SweetAlert2 스타일 팝업)
    const loginError = await page.evaluate(() => {
      // 모달 내 에러 메시지 확인
      const modals = document.querySelectorAll('.modalContainer, .swal2-container, [role="dialog"]');
      for (const modal of modals) {
        const style = window.getComputedStyle(modal);
        if (style.display !== 'none' && modal.offsetHeight > 0) {
          const text = modal.textContent.trim();
          if (text.includes('NoSuch') || text.includes('실패') || text.includes('오류') ||
              text.includes('일치하는') || text.includes('확인해')) {
            return text.replace(/\s+/g, ' ').substring(0, 200);
          }
        }
      }
      return null;
    });

    if (loginError) {
      await saveScreenshot(page, 'logen_login_failed');
      throw new Error(loginError);
    }

    // URL 체크: 여전히 로그인 페이지에 있으면 (main.html로 리다이렉트 안 됨)
    const afterUrl = page.url();
    if (!afterUrl.includes('main.html') && !afterUrl.includes('common/html')) {
      // 로그인 페이지에 아직 있을 수 있음 - 추가 확인
      const hasLoginForm = await page.locator('#user\\.id').isVisible().catch(() => false);
      if (hasLoginForm) {
        await saveScreenshot(page, 'logen_login_failed');
        throw new Error('로그인 실패: 로그인 페이지에서 벗어나지 못했습니다');
      }
    }

    console.log('  ✅ 로젠 로그인 성공');

    // 로그인 직후 팝업 모달 자동 닫기 (popupModal1~4, popupModalUserInfo 등)
    await page.waitForTimeout(2000);
    await closeAllModals(page);
  } catch (err) {
    console.error('  ❌ 로그인 실패 - 스크린샷 저장');
    await saveScreenshot(page, 'login_failed');
    throw new Error('로젠 로그인 실패: ' + err.message);
  }

  // Step 3: 주문등록/출력(복수건) 메뉴 이동
  console.log('\n═══ Step 3: 주문등록/출력(복수건) 이동 ═══');
  try {
    await page.waitForTimeout(1000);
    await saveScreenshot(page, 'after_login');

    // 좌측 사이드바: 예약관리 → 주문등록/출력(복수건)
    // 메뉴 구조: a.lnb.toggle-menu(예약관리) → li.menu-item.deps2(주문등록/출력(복수건))

    // 1단계: "예약관리" 대메뉴 클릭 (서브메뉴 펼치기)
    console.log('  📂 예약관리 메뉴 펼치기...');
    const yeyakMenu = page.locator('a.toggle-menu:has-text("예약관리")').first();
    await yeyakMenu.click({ force: true });
    await page.waitForTimeout(1000);

    // 2단계: "주문등록/출력(복수건)" 서브메뉴 클릭
    console.log('  📂 주문등록/출력(복수건) 클릭...');
    const orderMenu = page.locator('li.menu-item:has-text("주문등록/출력(복수건)")').first();
    await orderMenu.click({ force: true });
    await page.waitForTimeout(3000);

    await saveScreenshot(page, 'order_page');
    console.log('  ✅ 주문등록/출력(복수건) 페이지 이동 완료');
  } catch (err) {
    console.error('  ❌ 메뉴 이동 실패');
    await saveScreenshot(page, 'menu_failed');
    throw new Error('메뉴 이동 실패: ' + err.message);
  }

  // ── 핵심: 콘텐츠 프레임 참조 확보 ──
  // 로젠 시스템은 iframe 구조: Frame[0]=main.html, Frame[2]=lrm01f0040.html (작업화면)
  // 모든 버튼/그리드/함수는 Frame[2]에 존재, 모달은 Frame[0]에 존재
  let contentFrame = page.frames().find(f => f.url().includes('lrm01f0040'));
  if (!contentFrame) {
    await page.waitForTimeout(3000);
    contentFrame = page.frames().find(f => f.url().includes('lrm01f0040'));
  }
  if (!contentFrame) {
    await saveScreenshot(page, 'no_content_frame');
    throw new Error('주문등록/출력 콘텐츠 프레임(lrm01f0040)을 찾을 수 없습니다');
  }
  console.log('  ✅ 콘텐츠 프레임 확보:', contentFrame.url().substring(0, 80));

  // Helper: 모달 "확인" 클릭 (메인 프레임 + 콘텐츠 프레임 모두 시도)
  const clickModalConfirm = async () => {
    await page.waitForTimeout(500);
    // 양쪽 프레임에서 "확인" 버튼 클릭 + closeModal() + 오버레이 제거
    for (const frame of [contentFrame, page]) {
      await frame.evaluate(() => {
        // 1. 보이는 "확인" 버튼 클릭
        const btns = document.querySelectorAll('button');
        for (const btn of btns) {
          if (btn.textContent.trim() === '확인' && btn.offsetParent !== null && btn.offsetHeight > 0) {
            btn.click();
          }
        }
        // 2. closeModal() 호출
        if (typeof closeModal === 'function') closeModal();
        // 3. 오버레이/모달 강제 숨김
        document.querySelectorAll('.popmodal-back').forEach(el => { el.style.display = 'none'; });
        document.querySelectorAll('.modalContainer').forEach(el => { el.style.display = 'none'; });
      }).catch(() => {});
    }
    await page.waitForTimeout(500);
  };

  // Step 4: 엑셀 파일 업로드
  console.log('\n═══ Step 4: 엑셀 파일 업로드 ═══');
  try {
    // "1.파일열기" 버튼은 contentFrame에 있음 (onclick="fn_openFile()")
    const fileChooserPromise = page.waitForEvent('filechooser', { timeout: 10000 }).catch(() => null);
    await contentFrame.locator('button:has-text("1.파일열기")').click({ force: true });

    const fileChooser = await fileChooserPromise;
    if (fileChooser) {
      await fileChooser.setFiles(excelPath);
      console.log(`  ✅ 파일 업로드 완료: ${path.basename(excelPath)}`);
    } else {
      // 폴백: input[type=file] 직접 설정
      const fileInput = contentFrame.locator('input[type="file"]').first();
      if (await fileInput.count() > 0) {
        await fileInput.setInputFiles(excelPath);
        console.log(`  ✅ 파일 업로드 완료 (input 직접): ${path.basename(excelPath)}`);
      } else {
        throw new Error('파일 업로드 방법을 찾을 수 없습니다');
      }
    }

    await page.waitForTimeout(3000);
    await saveScreenshot(page, 'after_upload');
    // 업로드 후 모달 닫기
    await clickModalConfirm();
  } catch (err) {
    console.error('  ❌ 파일 업로드 실패');
    await saveScreenshot(page, 'upload_failed');
    throw new Error('파일 업로드 실패: ' + err.message);
  }

  // Step 5: 서버전송
  console.log('\n═══ Step 5: 서버전송 ═══');
  try {
    // "2.서버전송" 버튼 (contentFrame, onclick="fn_btnSendServer()")
    await contentFrame.locator('button:has-text("2.서버전송")').click({ force: true });
    console.log('  ⏳ 서버 처리 대기중...');
    await page.waitForTimeout(8000);
    await saveScreenshot(page, 'server_sent');

    // 서버전송 완료 모달 "확인" 클릭 (메인 프레임의 모달)
    console.log('  🔄 서버전송 완료 모달 "확인" 클릭...');
    await clickModalConfirm();

    // 한번 더 확인 (모달이 여러 겹일 수 있음)
    await page.waitForTimeout(1000);
    await clickModalConfirm();
    console.log('  ✅ 서버전송 완료');
  } catch (err) {
    console.error('  ❌ 서버전송 실패');
    await saveScreenshot(page, 'send_failed');
    throw new Error('서버전송 실패: ' + err.message);
  }

  // Step 5.5: 조회 (F2) → 데이터 갱신
  console.log('\n═══ Step 5.5: 조회 (F2) ═══');
  try {
    await contentFrame.evaluate(() => fn_retrieve('A'));
    console.log('  ✅ fn_retrieve("A") 호출');
    await page.waitForTimeout(3000);

    // fn_retrieve 후 "변환대상 데이터 없음" 모달 닫기
    await clickModalConfirm();
    await page.waitForTimeout(1000);

    // 탭 건수 확인
    const tabCounts = await contentFrame.evaluate(() => {
      const get = (id) => {
        const el = document.getElementById(id);
        if (el) { const m = el.textContent.match(/(\d+)/); return m ? parseInt(m[1]) : 0; }
        return 0;
      };
      return { miPrint: get('tabNotPrintLabel'), prtDone: get('tabPrtLabel') };
    });
    console.log(`  📊 미출력: ${tabCounts.miPrint}건, 출력완료: ${tabCounts.prtDone}건`);
    await saveScreenshot(page, 'after_query');
  } catch (err) {
    console.log('  ⚠️ fn_retrieve 실패, F2 키 입력...');
    await page.keyboard.press('F2');
    await page.waitForTimeout(5000);
    await clickModalConfirm();
  }

  // Step 5.6: 운송장 출력 (운송장번호 배정)
  console.log('\n═══ Step 5.6: 운송장 출력 (번호 배정) ═══');
  try {
    // 미출력 탭으로 이동
    console.log('  📋 미출력 탭으로 이동...');
    await contentFrame.evaluate(() => {
      const radio = document.getElementById('tabNotPrint');
      if (radio) { radio.checked = true; radio.click(); if (typeof fn_selectData === 'function') fn_selectData(radio); }
    });
    await page.waitForTimeout(2000);

    const miCount = await contentFrame.evaluate(() => {
      const el = document.getElementById('tabNotPrintLabel');
      if (el) { const m = el.textContent.match(/(\d+)/); return m ? parseInt(m[1]) : 0; }
      return 0;
    });
    console.log(`  📊 미출력 건수: ${miCount}건`);

    if (miCount === 0) {
      console.log('  ⚠️ 미출력 건이 없습니다. 이미 출력완료된 데이터가 있는지 확인합니다.');
    } else {
      // showMessage 오버라이드 (fn_silpPrint가 MSG000 연륙도서지역 등 확인 모달을 띄움)
      // 콜백 자동실행 필수 - 콜백 안에 실제 운송장번호 배정 AJAX가 있음
      await contentFrame.evaluate(() => {
        window._origShowMessage = window.showMessage;
        window.showMessage = function(msgId, type, buttons, params, callback) {
          console.log('showMessage intercepted:', msgId, buttons);
          if (typeof callback === 'function') {
            console.log('Auto-confirming callback for', msgId);
            callback();
            return;
          }
        };
      }).catch(() => {});

      // ── 핵심: sheet4.setAllCheck('isCheck', true) 로 전체 체크 ──
      console.log('  ☑️ 전체 행 선택 중...');
      const selectedCount = await contentFrame.evaluate(() => {
        if (typeof sheet4 === 'undefined') return 0;
        // TreeGrid API: setAllCheck(colName, checked)
        try {
          sheet4.setAllCheck('isCheck', true);
          return sheet4.getRowsByChecked('isCheck').length;
        } catch (e) {
          console.log('setAllCheck failed:', e.message);
          return 0;
        }
      });
      console.log(`  ☑️ ${selectedCount}건 선택 완료`);

      // window.print() 가로채기 (모든 프레임)
      for (const f of page.frames()) {
        await f.evaluate(() => { window.print = function() { console.log('PRINT_INTERCEPTED'); }; }).catch(() => {});
      }

      // "3.운송장출력" 실행 → "운송장 발행" 모달이 contentFrame 안에 열림
      console.log('  🖨️ fn_silpPrint() 호출...');
      await contentFrame.evaluate(() => {
        const btn = document.getElementById('btnSilpPrint');
        if (btn) btn.classList.remove('b-disabled');
        if (typeof fn_silpPrint === 'function') fn_silpPrint();
      });
      // showMessage callback → 서버 AJAX → 운송장번호 배정 대기
      await page.waitForTimeout(8000);

      // "선택 된 행이 없습니다" 모달 체크
      const printModalMsg = await contentFrame.evaluate(() => {
        const modals = document.querySelectorAll('.modalContainer, [role="dialog"]');
        for (const m of modals) {
          if (m.offsetHeight > 0 && m.textContent.includes('MSG')) {
            return m.textContent.replace(/\s+/g, ' ').substring(0, 200);
          }
        }
        return '';
      }).catch(() => '');

      if (printModalMsg.includes('MSG124') || printModalMsg.includes('선택')) {
        console.log('  ⚠️ 행 선택 실패. 모달:', printModalMsg);
        await clickModalConfirm();
      } else {
        // ── "운송장 발행" 미리보기 모달 확인 (실제 출력 버튼은 클릭하지 않음) ──
        // fn_silpPrint() 호출 시 운송장번호가 배정됨
        // #prtBtn은 실제 프린터 출력이므로 클릭하지 않고, 번호만 수집
        await page.waitForTimeout(2000);
        await saveScreenshot(page, 'print_preview');

        const prtBtnVisible = await contentFrame.locator('#prtBtn').isVisible().catch(() => false);
        if (prtBtnVisible) {
          console.log('  📋 운송장 발행 미리보기 확인 (운송장번호 배정 완료)');
          console.log('  ⏭️ 실제 출력(#prtBtn) 건너뜀 - 번호만 수집');
        }

        // "닫기" 버튼 (#clsBtn) 클릭하여 미리보기 모달 닫기
        const clsBtnVisible = await contentFrame.locator('#clsBtn').isVisible().catch(() => false);
        if (clsBtnVisible) {
          console.log('  🔄 미리보기 모달 닫기...');
          await contentFrame.locator('#clsBtn').click({ force: true });
          await page.waitForTimeout(1000);
        }
        await clickModalConfirm();

        console.log('  ✅ 운송장번호 배정 완료 (미출력 상태 유지)');
      }

      // showMessage 복원
      await contentFrame.evaluate(() => {
        if (window._origShowMessage) window.showMessage = window._origShowMessage;
      }).catch(() => {});

      // fn_silpPrint() 후 조회 갱신하지 않음 (fn_retrieve 호출 시 fixTakeNo 소실)
      // Step 6에서 탭 전환 시 데이터 자동 갱신됨
      await page.waitForTimeout(1000);
    }
    await saveScreenshot(page, 'after_print');
    console.log('  ✅ 운송장 출력 단계 완료');
  } catch (err) {
    console.error('  ⚠️ 운송장 출력 오류:', err.message);
    await saveScreenshot(page, 'print_error');
  }

  // Step 6: 운송장번호 수집 (TreeGrid API 직접 접근)
  // sheet4(미출력)/sheet5(출력완료)의 setAllCheck + getRowsByChecked로 데이터 수집
  console.log('\n═══ Step 6: 운송장번호 수집 ═══');
  let collectedData = [];
  let resultPath = null;
  try {
    // TreeGrid API로 데이터 수집 (Primary)
    // sheet4=미출력 grid, sheet5=출력완료 grid
    // Key fields: slipNo, rcvCustNm, rcvTelno, rcvCellNo, fixTakeNo(=DB주문번호), itemNm
    const collectFromTreeGrid = async (sheetName, tabId) => {
      // 탭 활성화
      await contentFrame.evaluate((id) => {
        const radio = document.getElementById(id);
        if (radio) { radio.checked = true; radio.click(); if (typeof fn_selectData === 'function') fn_selectData(radio); }
      }, tabId);
      await page.waitForTimeout(2000);

      return await contentFrame.evaluate((sName) => {
        const sheet = window[sName];
        if (!sheet || typeof sheet.setAllCheck !== 'function') return [];

        // 전체 행 체크 → 데이터 수집 → 체크 해제
        sheet.setAllCheck('isCheck', true);
        const rows = sheet.getRowsByChecked('isCheck');
        const data = [];
        for (let i = 0; i < rows.length; i++) {
          const r = rows[i];
          data.push({
            slipNo: String(r.slipNo || '').replace(/-/g, ''),
            rcvNm: String(r.rcvCustNm || ''),
            rcvAddr1: String(r.rcvCustAddr1 || ''),
            rcvTel: String(r.rcvTelno || ''),
            rcvHp: String(r.rcvCellNo || ''),
            goodsNm: String(r.itemNm || ''),
            ordNo: String(r.fixTakeNo || ''), // fixTakeNo = DB 주문번호
          });
        }
        sheet.setAllCheck('isCheck', false);
        return data;
      }, sheetName).catch(() => []);
    };

    // 미출력 탭 (sheet4) 먼저 수집 (Step 5.6 이후 이미 미출력 탭 → 탭 전환 없이 즉시 수집)
    // 탭 전환(fn_selectData) 시 fixTakeNo가 소실되므로, 현재 탭에서 직접 수집
    console.log('  📋 미출력 탭 데이터 수집 (TreeGrid API, fixTakeNo 보존)...');
    const miData = await contentFrame.evaluate(() => {
      if (typeof sheet4 === 'undefined' || typeof sheet4.setAllCheck !== 'function') return [];
      sheet4.setAllCheck('isCheck', true);
      const rows = sheet4.getRowsByChecked('isCheck');
      const data = [];
      for (let i = 0; i < rows.length; i++) {
        const r = rows[i];
        data.push({
          slipNo: String(r.slipNo || '').replace(/-/g, ''),
          rcvNm: String(r.rcvCustNm || ''),
          rcvAddr1: String(r.rcvCustAddr1 || ''),
          rcvTel: String(r.rcvTelno || ''),
          rcvHp: String(r.rcvCellNo || ''),
          goodsNm: String(r.itemNm || ''),
          ordNo: String(r.fixTakeNo || ''),
        });
      }
      sheet4.setAllCheck('isCheck', false);
      return data;
    }).catch(() => []);
    const miWithSlip = miData.filter(d => d.slipNo && /^4\d{10,11}$/.test(d.slipNo));
    console.log(`  📊 미출력 탭: ${miData.length}건 (운송장번호: ${miWithSlip.length}건, ordNo 있는 건: ${miData.filter(d => d.ordNo).length}건)`);
    collectedData = [...miWithSlip];

    // 출력완료 탭 (sheet5) 데이터 수집
    console.log('  📋 출력완료 탭 데이터 수집...');
    const prtData = await collectFromTreeGrid('sheet5', 'tabPrt');
    const prtWithSlip = prtData.filter(d => d.slipNo && /^4\d{10,11}$/.test(d.slipNo));
    console.log(`  📊 출력완료 탭: ${prtData.length}건 (운송장번호: ${prtWithSlip.length}건)`);
    if (prtWithSlip.length > 0) {
      collectedData = [...collectedData, ...prtWithSlip];
    }

    // 폴백: DOM 수집 실패 시 excelDownload() 시도
    if (collectedData.length === 0) {
      console.log('  🔄 DOM 수집 실패. excelDownload() 시도...');
      // 출력완료 탭 활성화
      await contentFrame.evaluate(() => {
        const radio = document.getElementById('tabPrt');
        if (radio) { radio.checked = true; radio.click(); if (typeof fn_selectData === 'function') fn_selectData(radio); }
      });
      await page.waitForTimeout(2000);
      const downloadPromise = page.waitForEvent('download', { timeout: 30000 }).catch(() => null);
      await contentFrame.evaluate(() => { if (typeof excelDownload === 'function') excelDownload(); });
      await page.waitForTimeout(5000);
      const download = await downloadPromise;
      if (download) {
        const resultName = download.suggestedFilename() || `logen_result_${Date.now()}.xlsx`;
        resultPath = path.join(CONFIG.downloadDir, resultName);
        await download.saveAs(resultPath);
        console.log(`  ✅ 결과 엑셀 다운로드: ${resultName}`);
      } else {
        await clickModalConfirm();
      }
    }

    if (collectedData.length === 0 && !resultPath) {
      await saveScreenshot(page, 'step6_no_data');
      throw new Error('출력완료/미출력 탭에서 데이터를 찾을 수 없습니다');
    }

    // 수집 데이터 미리보기
    if (collectedData.length > 0) {
      console.log(`  ✅ 총 ${collectedData.length}건 수집 완료`);
      for (const d of collectedData.slice(0, 5)) {
        console.log(`    운송장: ${d.slipNo || '-'} | 수하인: ${d.rcvNm || '-'} | 품목: ${d.goodsNm || '-'}`);
      }
      if (collectedData.length > 5) console.log(`    ... 외 ${collectedData.length - 5}건`);

      // 백업: 수집 데이터를 엑셀로도 저장
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.json_to_sheet(collectedData.map(d => ({
        '운송장번호': d.slipNo || '',
        '수하인명': d.rcvNm || '',
        '수하인주소': d.rcvAddr1 || '',
        '수하인전화': d.rcvTel || '',
        '수하인HP': d.rcvHp || '',
        '품목명': d.goodsNm || '',
      })));
      XLSX.utils.book_append_sheet(wb, ws, 'Result');
      resultPath = path.join(CONFIG.downloadDir, `logen_result_${Date.now()}.xlsx`);
      XLSX.writeFile(wb, resultPath);
      console.log(`  💾 백업 파일: ${path.basename(resultPath)}`);
    }
  } catch (err) {
    console.error('  ❌ 운송장번호 수집 실패');
    await saveScreenshot(page, 'step6_failed');
    throw new Error('Step 6 실패: ' + err.message);
  }

  await context.close();
  return collectedData.length > 0 ? collectedData : resultPath;
}

// ── Step 7: 운송장번호 일괄 등록 (API 방식: 주문번호 + 이름 매칭) ──
async function step7_importWaybill(browser, resultData, orderMapping = [], opts = {}) {
  console.log('\n═══ Step 7: 운송장 번호 일괄 등록 ═══');

  // resultData: TreeGrid에서 수집한 배열 또는 엑셀 파일 경로
  let items = [];

  if (Array.isArray(resultData)) {
    // TreeGrid에서 직접 수집한 데이터
    items = resultData.filter(d => d.slipNo && d.slipNo.match(/^4\d{10}$/));

    // orderMapping으로 ordNo 보강 (엑셀의 "기타" 컬럼 = DB 주문번호)
    if (orderMapping.length > 0) {
      const usedMapIdx = new Set();
      for (const item of items) {
        if (item.ordNo) continue; // 이미 ordNo가 있으면 스킵
        const phone = (item.rcvHp || item.rcvTel || '').replace(/[^0-9]/g, '');
        // 이름+전화번호로 매칭
        for (let mi = 0; mi < orderMapping.length; mi++) {
          if (usedMapIdx.has(mi)) continue;
          const m = orderMapping[mi];
          if (m.rcvNm === item.rcvNm && (phone === m.phone || !m.phone)) {
            item.ordNo = m.dbNo;
            usedMapIdx.add(mi);
            break;
          }
        }
      }
      const enriched = items.filter(d => d.ordNo).length;
      if (enriched > 0) {
        console.log(`  📋 엑셀 매핑으로 ${enriched}건 주문번호 보강`);
      }
    }
  } else if (typeof resultData === 'string' && fs.existsSync(resultData)) {
    // 엑셀 파일에서 읽기
    console.log(`  📄 엑셀 파일에서 데이터 읽기: ${path.basename(resultData)}`);
    const wb = XLSX.readFile(resultData);
    const ws = wb.Sheets[wb.SheetNames[0]];
    const rows = XLSX.utils.sheet_to_json(ws, { header: 1 });

    // 운송장번호(4+10자리)와 수하인명/전화 컬럼 자동 감지
    // 로젠 엑셀 구조: Row0=제목, Row1=카테고리헤더(수하인 반복), Row2=세부헤더(이름/주소/전화/휴대폰)
    let slipCol = -1, nameCol = -1, phoneCol = -1, ordCol = -1;
    for (let r = 0; r < Math.min(20, rows.length); r++) {
      const row = rows[r];
      if (!row) continue;
      for (let c = 0; c < row.length; c++) {
        const val = String(row[c] || '').trim();
        if (slipCol === -1 && /^4\d{10}$/.test(val)) slipCol = c;
        if (ordCol === -1 && /dsno\d+/i.test(val)) ordCol = c;
      }
      // 세부헤더(Row2)에서 "이름" 컬럼 찾기 (수하인 하위의 첫 번째 "이름")
      if (nameCol === -1) {
        for (let c = 0; c < row.length; c++) {
          const val = String(row[c] || '').trim();
          if (val === '이름' || val === '수하인명' || val === '받는분') {
            nameCol = c;
            break; // 첫 번째 매칭 사용 (수하인 이름, 송하인 이름 구분)
          }
        }
      }
      // 세부헤더에서 "휴대폰"/"전화" 컬럼 찾기 (수하인 하위)
      if (phoneCol === -1 && nameCol >= 0) {
        for (let c = nameCol + 1; c < Math.min(nameCol + 6, row.length); c++) {
          const val = String(row[c] || '').trim();
          if (val === '휴대폰' || val === '전화') { phoneCol = c; break; }
        }
      }
      if (slipCol !== -1) break;
    }

    // 카테고리헤더에서 "수하인" 위치로 폴백 (세부헤더 없는 경우)
    if (nameCol === -1) {
      for (let r = 0; r < Math.min(5, rows.length); r++) {
        const row = rows[r];
        if (!row) continue;
        for (let c = 0; c < row.length; c++) {
          const val = String(row[c] || '').trim();
          if (val === '수하인') { nameCol = c; break; }
        }
        if (nameCol >= 0) break;
      }
    }

    console.log(`  📊 컬럼 감지: 운송장=${slipCol}, 이름=${nameCol}, 전화=${phoneCol}, 주문번호=${ordCol}`);

    for (const row of rows) {
      if (!row) continue;
      const slip = String(row[slipCol] || '').trim();
      if (!/^4\d{10}$/.test(slip)) continue;
      items.push({
        slipNo: slip,
        rcvNm: nameCol >= 0 ? String(row[nameCol] || '').trim() : '',
        rcvHp: phoneCol >= 0 ? String(row[phoneCol] || '').trim() : '',
        ordNo: ordCol >= 0 ? String(row[ordCol] || '').trim() : '',
      });
    }
  }

  if (items.length === 0) {
    console.log('  ⚠️ 등록할 운송장 데이터가 없습니다.');
    return { updated: 0, failed: 0 };
  }

  console.log(`  📦 등록 대상: ${items.length}건`);
  for (const d of items.slice(0, 5)) {
    console.log(`    운송장: ${d.slipNo} | 이름: ${d.rcvNm || '-'} | 주문: ${d.ordNo || '-'}`);
  }
  if (items.length > 5) console.log(`    ... 외 ${items.length - 5}건`);

  // PHP API 호출 (import_waybill.php)
  const useProduction = opts.production || false;
  const baseUrl = useProduction ? CONFIG.prodBase : CONFIG.adminBase;
  const apiPath = CONFIG.importWaybillPath;

  // _eauth 토큰 생성 (프로덕션 모드에서 필수)
  let apiUrl = apiPath;
  if (useProduction) {
    const today = new Date().toISOString().split('T')[0];
    const eauth = crypto.createHmac('sha256', CONFIG.embedSecret)
      .update(apiPath + today)
      .digest('hex');
    apiUrl = `${apiPath}?_eauth=${eauth}`;
  }

  console.log(`  🔗 import_waybill.php API 호출...`);
  if (useProduction) console.log(`  🌐 프로덕션 모드: ${baseUrl}`);

  const fullUrl = `${baseUrl}${apiUrl}`;
  const httpModule = fullUrl.startsWith('https') ? require('https') : require('http');

  const apiResult = await new Promise((resolve, reject) => {
    const postData = JSON.stringify({ items });
    const req = httpModule.request(fullUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(postData),
      },
    }, (res) => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try { resolve(JSON.parse(body)); }
        catch (e) { reject(new Error('API 응답 파싱 실패: ' + body.substring(0, 500))); }
      });
    });
    req.on('error', reject);
    req.write(postData);
    req.end();
  });

  console.log(`  ✅ 등록 결과: ${apiResult.updated}건 성공, ${apiResult.failed}건 실패 (전체 ${apiResult.total}건)`);

  // 상세 결과 출력
  if (apiResult.details) {
    for (const d of apiResult.details) {
      if (d.status === 'ok') {
        console.log(`    ✅ ${d.slipNo} → DB#${d.dbNo} (${d.matchedBy === 'name' ? '이름매칭' : '주문번호매칭'}: ${d.rcvNm})`);
      } else if (d.status === 'fail') {
        console.log(`    ❌ ${d.slipNo} (${d.rcvNm}): ${d.reason}`);
      } else if (d.status === 'skip') {
        console.log(`    ⏭️ ${d.slipNo} (${d.rcvNm}): ${d.reason}`);
      }
    }
  }

  return apiResult;
}

// ── 메인 실행 ──────────────────────────────────────
async function main() {
  console.log('╔══════════════════════════════════════════╗');
  console.log('║  로젠택배 자동 운송장 등록 스크립트 v1.0  ║');
  console.log('╚══════════════════════════════════════════╝');

  const opts = parseArgs();
  console.log(`\n📅 기간: ${opts.dateFrom} ~ ${opts.dateTo}`);
  console.log(`📊 상태: ${opts.status}`);
  if (opts.production) {
    console.log(`🌐 모드: 프로덕션 (${CONFIG.prodBase})`);
  } else {
    console.log(`🖥️  모드: 로컬 (${CONFIG.adminBase})`);
  }

  // 로젠 비밀번호 확인
  let logenPassword = opts.password || CONFIG.logenDefaultPass;
  if (!logenPassword) {
    logenPassword = await promptPassword();
  }
  if (!logenPassword) {
    console.error('❌ 비밀번호가 필요합니다.');
    process.exit(1);
  }

  // downloads 디렉토리 확인
  if (!fs.existsSync(CONFIG.downloadDir)) {
    fs.mkdirSync(CONFIG.downloadDir, { recursive: true });
  }

  const browser = await chromium.launch({
    headless: opts.headless,
    slowMo: 300,
  });

  let excelPath = null;
  let resultPath = null;

  try {
    // Step 1: 배송관리에서 엑셀 다운로드 (HTTP 직접 호출, browser 불필요)
    excelPath = await step1_downloadLogenExcel(null, opts);

    // Step 1.5: HTML .xls → 실제 .xlsx 변환 (로젠 시스템은 실제 Excel 형식만 지원)
    const xlsxPath = convertHtmlXlsToXlsx(excelPath);

    // Step 1.6: 엑셀에서 수하인→주문번호 매핑 추출 (Step 7에서 사용)
    let orderMapping = [];
    try {
      const wb = XLSX.readFile(xlsxPath);
      const ws = wb.Sheets[wb.SheetNames[0]];
      const rows = XLSX.utils.sheet_to_json(ws, { header: 1 });
      // 헤더: [수하인명, 우편번호, 주소, 전화, 핸드폰, 박스수량, 택배비, 운임구분, Type, 기타(=DB no), 품목]
      for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        if (!row || row.length < 10) continue;
        const dbNo = String(row[9] || '').trim();
        if (!dbNo || !(/^\d+$/.test(dbNo))) continue;
        orderMapping.push({
          rcvNm: String(row[0] || '').trim(),
          phone: String(row[4] || row[3] || '').replace(/[^0-9]/g, ''),
          dbNo: dbNo,
        });
      }
      if (orderMapping.length > 0) {
        console.log(`  📋 주문 매핑: ${orderMapping.length}건 (수하인→DB번호)`);
      }
    } catch (e) { /* 매핑 실패해도 계속 진행 */ }

    // Step 2-6: 로젠 사이트 자동화
    const logenResult = await step2to6_logenProcess(browser, xlsxPath, logenPassword);

    // Step 7: 운송장 일괄 등록 (배열 또는 파일 경로)
    const importResult = await step7_importWaybill(browser, logenResult, orderMapping, opts);

    // Step 8: 결과 요약
    console.log('\n═══ Step 8: 결과 요약 ═══');
    console.log('  ✅ 전체 프로세스 완료!');
    console.log(`  📁 다운로드 엑셀: ${excelPath}`);
    if (importResult) {
      console.log(`  📋 등록 결과: ${importResult.updated || 0}건 성공, ${importResult.failed || 0}건 실패`);
    }

  } catch (err) {
    console.error(`\n❌ 오류 발생: ${err.message}`);
    console.error('  각 단계별 스크린샷을 확인해주세요.');
    console.log(`  📁 스크린샷 경로: ${CONFIG.screenshotDir}`);
    process.exit(1);
  } finally {
    await browser.close();
  }
}

main();
