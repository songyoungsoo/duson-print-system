/**
 * MlangPrintAuto E2E 업로드/다운로드 테스트
 * Playwright를 사용한 9개 품목 전체 테스트
 */

const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

// 테스트 설정
const BASE_URL = 'http://localhost/mlangprintauto';
const ADMIN_URL = 'http://localhost/admin/mlangprintauto';
const TEST_FILE = path.join(__dirname, 'test-upload.jpg');

// 9개 품목 정의 (leaflet 제외!)
const PRODUCTS = [
    { id: 'inserted', name: '전단지', hasUpload: true },
    { id: 'sticker_new', name: '스티커', hasUpload: true },
    { id: 'envelope', name: '봉투', hasUpload: true },
    { id: 'littleprint', name: '소량인쇄물', hasUpload: true },
    { id: 'cadarok', name: '카다록', hasUpload: true },
    { id: 'merchandisebond', name: '상품권', hasUpload: false }, // 파일 업로드 없음
    { id: 'namecard', name: '명함', hasUpload: true },
    { id: 'msticker', name: '자석스티커', hasUpload: true },
    { id: 'ncrflambeau', name: '양식지', hasUpload: true }
];

// 테스트 결과 저장
const results = {
    timestamp: new Date().toISOString(),
    products: {},
    summary: {
        total: 0,
        passed: 0,
        failed: 0,
        skipped: 0
    }
};

// 테스트용 이미지 파일 생성 (존재하지 않으면)
function createTestFile() {
    if (!fs.existsSync(TEST_FILE)) {
        console.log('📄 테스트 파일 생성 중...');
        // 1x1 픽셀 PNG 이미지 (base64)
        const pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        fs.writeFileSync(TEST_FILE, Buffer.from(pngBase64, 'base64'));
        console.log('✅ 테스트 파일 생성 완료:', TEST_FILE);
    }
}

// 로그인 함수 (필요시)
async function login(page) {
    // 로그인이 필요한 경우 여기에 구현
    // 현재는 세션 기반으로 동작한다고 가정
}

// 단일 품목 업로드 테스트
async function testProductUpload(browser, product) {
    const context = await browser.newContext();
    const page = await context.newPage();

    const result = {
        product: product.name,
        productId: product.id,
        uploadTest: null,
        basketTest: null,
        errors: []
    };

    try {
        console.log(`\n🧪 [${product.name}] 테스트 시작...`);

        // 상품 페이지 접속
        const url = `${BASE_URL}/${product.id}/index.php`;
        console.log(`   📍 접속: ${url}`);
        await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });

        // 파일 업로드가 없는 품목은 스킵
        if (!product.hasUpload) {
            console.log(`   ⏭️  파일 업로드 기능 없음 - 스킵`);
            result.uploadTest = { status: 'skipped', message: '파일 업로드 기능 없음' };
            results.summary.skipped++;
            return result;
        }

        // 업로드 버튼 찾기
        const uploadButton = await page.locator('button:has-text("파일 업로드")').first();
        if (await uploadButton.count() === 0) {
            throw new Error('업로드 버튼을 찾을 수 없습니다');
        }

        // 업로드 모달 열기
        console.log(`   🖱️  업로드 버튼 클릭`);
        await uploadButton.click();
        await page.waitForTimeout(1000);

        // 파일 입력 요소 찾기
        const fileInput = await page.locator('#modalFileInput').first();
        if (await fileInput.count() === 0) {
            throw new Error('파일 입력 요소를 찾을 수 없습니다');
        }

        // 파일 선택
        console.log(`   📎 파일 선택: ${TEST_FILE}`);
        await fileInput.setInputFiles(TEST_FILE);
        await page.waitForTimeout(2000);

        // 업로드된 파일 확인
        const uploadedFileList = await page.locator('#modalFileList .file-item');
        const fileCount = await uploadedFileList.count();
        console.log(`   📋 업로드된 파일 수: ${fileCount}`);

        if (fileCount === 0) {
            throw new Error('파일이 목록에 추가되지 않았습니다');
        }

        result.uploadTest = {
            status: 'passed',
            fileCount: fileCount,
            message: '파일 업로드 성공'
        };

        // 장바구니 추가 버튼 클릭
        console.log(`   🛒 장바구니 추가 시도`);
        const basketButton = await page.locator('button:has-text("장바구니")').first();

        // 네트워크 요청 모니터링
        let basketResponse = null;
        page.on('response', async response => {
            if (response.url().includes('add_to_basket.php')) {
                basketResponse = response;
            }
        });

        await basketButton.click();

        // 응답 대기 (최대 10초)
        await page.waitForTimeout(3000);

        if (basketResponse) {
            const status = basketResponse.status();
            console.log(`   📡 서버 응답: ${status}`);

            if (status === 200) {
                try {
                    const responseData = await basketResponse.json();
                    console.log(`   ✅ 응답 데이터:`, responseData);

                    result.basketTest = {
                        status: 'passed',
                        response: responseData,
                        message: '장바구니 추가 성공'
                    };
                    results.summary.passed++;
                } catch (e) {
                    result.basketTest = {
                        status: 'failed',
                        error: 'JSON 파싱 실패',
                        message: e.message
                    };
                    result.errors.push(`JSON 파싱 오류: ${e.message}`);
                    results.summary.failed++;
                }
            } else {
                result.basketTest = {
                    status: 'failed',
                    httpStatus: status,
                    message: `서버 오류: ${status}`
                };
                result.errors.push(`HTTP 상태 코드: ${status}`);
                results.summary.failed++;
            }
        } else {
            result.basketTest = {
                status: 'failed',
                message: '서버 응답 없음'
            };
            result.errors.push('add_to_basket.php 응답 없음');
            results.summary.failed++;
        }

        console.log(`   ${result.basketTest.status === 'passed' ? '✅' : '❌'} [${product.name}] 테스트 완료`);

    } catch (error) {
        console.error(`   ❌ [${product.name}] 오류:`, error.message);
        result.errors.push(error.message);
        results.summary.failed++;

        if (!result.uploadTest) {
            result.uploadTest = { status: 'failed', error: error.message };
        }
        if (!result.basketTest) {
            result.basketTest = { status: 'failed', error: error.message };
        }
    } finally {
        await context.close();
    }

    results.summary.total++;
    return result;
}

// 관리자 페이지에서 다운로드 테스트
async function testAdminDownload(browser) {
    const context = await browser.newContext();
    const page = await context.newPage();

    try {
        console.log(`\n📥 관리자 페이지 다운로드 테스트 시작...`);

        // 관리자 페이지 접속
        await page.goto(`${ADMIN_URL}/admin.php`, { waitUntil: 'networkidle' });

        // 최근 주문 찾기
        const orderLinks = await page.locator('a:has-text("주문정보")').all();

        if (orderLinks.length > 0) {
            console.log(`   📋 발견된 주문: ${orderLinks.length}개`);

            // 첫 번째 주문 클릭
            await orderLinks[0].click();
            await page.waitForTimeout(2000);

            // 다운로드 링크 확인
            const downloadLinks = await page.locator('a[href*="download.php"]').all();
            console.log(`   📎 다운로드 가능 파일: ${downloadLinks.length}개`);

            return {
                status: 'passed',
                orderCount: orderLinks.length,
                fileCount: downloadLinks.length
            };
        } else {
            return {
                status: 'warning',
                message: '테스트할 주문이 없습니다'
            };
        }

    } catch (error) {
        console.error(`   ❌ 관리자 페이지 테스트 오류:`, error.message);
        return {
            status: 'failed',
            error: error.message
        };
    } finally {
        await context.close();
    }
}

// 메인 테스트 실행
async function runTests() {
    console.log('🚀 MlangPrintAuto E2E 테스트 시작\n');
    console.log('=' .repeat(60));

    // 테스트 파일 생성
    createTestFile();

    const browser = await chromium.launch({
        headless: false, // 브라우저 UI 표시
        slowMo: 500 // 동작 속도 늦춤 (디버깅용)
    });

    try {
        // 각 품목 테스트
        for (const product of PRODUCTS) {
            const result = await testProductUpload(browser, product);
            results.products[product.id] = result;
        }

        // 관리자 페이지 테스트
        console.log('\n' + '='.repeat(60));
        results.adminDownload = await testAdminDownload(browser);

    } finally {
        await browser.close();
    }

    // 결과 출력
    console.log('\n' + '='.repeat(60));
    console.log('📊 테스트 결과 요약\n');
    console.log(`총 테스트: ${results.summary.total}`);
    console.log(`✅ 성공: ${results.summary.passed}`);
    console.log(`❌ 실패: ${results.summary.failed}`);
    console.log(`⏭️  스킵: ${results.summary.skipped}`);

    // 실패한 품목 상세 정보
    if (results.summary.failed > 0) {
        console.log('\n❌ 실패한 품목:');
        for (const [productId, result] of Object.entries(results.products)) {
            if (result.errors && result.errors.length > 0) {
                console.log(`\n   ${result.product} (${productId}):`);
                result.errors.forEach(err => console.log(`      - ${err}`));
            }
        }
    }

    // JSON 파일로 저장
    const reportPath = path.join(__dirname, 'test-results.json');
    fs.writeFileSync(reportPath, JSON.stringify(results, null, 2));
    console.log(`\n📄 상세 결과 저장: ${reportPath}`);

    console.log('\n' + '='.repeat(60));
    console.log('🏁 테스트 완료\n');

    // 종료 코드 (실패가 있으면 1)
    process.exit(results.summary.failed > 0 ? 1 : 0);
}

// 테스트 실행
runTests().catch(error => {
    console.error('💥 테스트 실행 오류:', error);
    process.exit(1);
});
