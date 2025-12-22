const { chromium } = require('playwright');

(async () => {
    const browser = await chromium.launch({ headless: false });
    const page = await browser.newPage();

    // alert 메시지 캡처
    page.on('dialog', async dialog => {
        console.log(`  📢 Alert: ${dialog.message()}`);
        await dialog.accept();
    });

    console.log('🧪 카다록 추가 옵션 시스템 E2E 테스트 시작...\n');

    try {
        // 1. 페이지 로드
        console.log('1️⃣ 카다록 페이지 로드...');
        await page.goto('http://localhost/mlangprintauto/cadarok/');
        await page.waitForLoadState('networkidle');
        console.log('✅ 페이지 로드 완료\n');

        // 2. 필수 옵션 선택
        console.log('2️⃣ 필수 옵션 선택...');

        // 카다록 종류 선택
        await page.waitForFunction(() => {
            const select = document.querySelector('#MY_type');
            return select && select.options.length > 1;
        }, { timeout: 5000 });

        const typeOptions = await page.$$eval('#MY_type option:not([value=""])', options =>
            options.map(opt => ({ value: opt.value, text: opt.textContent }))
        );
        if (typeOptions.length > 0) {
            await page.selectOption('#MY_type', { value: typeOptions[0].value });
            console.log(`  - 종류: ${typeOptions[0].text} 선택`);
        }

        // 재질 선택
        await page.waitForTimeout(1000);
        await page.waitForFunction(() => {
            const select = document.querySelector('#Section');
            return select && select.options.length > 1;
        }, { timeout: 5000 });

        const sectionOptions = await page.$$eval('#Section option:not([value=""])', options =>
            options.map(opt => ({ value: opt.value, text: opt.textContent }))
        );
        if (sectionOptions.length > 0) {
            await page.selectOption('#Section', { value: sectionOptions[0].value });
            console.log(`  - 재질: ${sectionOptions[0].text} 선택`);
        }

        // 인쇄면 선택
        await page.selectOption('#POtype', { value: '1' });
        console.log('  - 인쇄면: 단면 선택');

        // 수량 선택
        await page.waitForTimeout(500);
        await page.waitForFunction(() => {
            const select = document.querySelector('#MY_amount');
            return select && select.options.length > 1;
        }, { timeout: 3000 });

        const amountOptions = await page.$$eval('#MY_amount option:not([value=""])', options =>
            options.map(opt => ({ value: opt.value, text: opt.textContent }))
        );
        if (amountOptions.length > 0) {
            // 100부 옵션 찾기 또는 첫 번째 옵션 선택
            const targetOption = amountOptions.find(opt => opt.value === '100') || amountOptions[0];
            await page.selectOption('#MY_amount', { value: targetOption.value });
            console.log(`  - 수량: ${targetOption.text} 선택`);
        }

        // 편집비 선택
        await page.selectOption('#ordertype', { value: 'print' });
        console.log('  - 편집비: 인쇄만 의뢰 선택');

        console.log('✅ 필수 옵션 선택 완료\n');

        // 3. 추가 옵션 테스트
        console.log('3️⃣ 추가 옵션 테스트...');

        // 코팅 옵션
        const coatingToggle = await page.locator('#coating-toggle');
        if (await coatingToggle.isVisible()) {
            await coatingToggle.click();
            console.log('  - 코팅 옵션 활성화');

            await page.waitForTimeout(500);

            const coatingSelect = await page.locator('#coating-type');
            if (await coatingSelect.isVisible()) {
                const coatingOptions = await page.$$eval('#coating-type option:not([value=""])', options =>
                    options.map(opt => ({ value: opt.value, text: opt.textContent }))
                );
                if (coatingOptions.length > 0) {
                    await coatingSelect.selectOption({ value: coatingOptions[0].value });
                    console.log(`  - 코팅 종류: ${coatingOptions[0].text} 선택`);
                }
            }
        } else {
            console.log('  ⚠️ 코팅 옵션이 없습니다');
        }

        // 접지 옵션
        const foldingToggle = await page.locator('#folding-toggle');
        if (await foldingToggle.isVisible()) {
            await foldingToggle.click();
            console.log('  - 접지 옵션 활성화');

            await page.waitForTimeout(500);

            const foldingSelect = await page.locator('#folding-type');
            if (await foldingSelect.isVisible()) {
                const foldingOptions = await page.$$eval('#folding-type option:not([value=""])', options =>
                    options.map(opt => ({ value: opt.value, text: opt.textContent }))
                );
                if (foldingOptions.length > 0) {
                    await foldingSelect.selectOption({ value: foldingOptions[0].value });
                    console.log(`  - 접지 종류: ${foldingOptions[0].text} 선택`);
                }
            }
        } else {
            console.log('  ⚠️ 접지 옵션이 없습니다');
        }

        // 오시 옵션
        const creasingToggle = await page.locator('#creasing-toggle');
        if (await creasingToggle.isVisible()) {
            await creasingToggle.click();
            console.log('  - 오시 옵션 활성화');

            await page.waitForTimeout(500);

            const creasingLines = await page.locator('#creasing-lines');
            if (await creasingLines.isVisible()) {
                const creasingOptions = await page.$$eval('#creasing-lines option:not([value=""])', options =>
                    options.map(opt => ({ value: opt.value, text: opt.textContent }))
                );
                if (creasingOptions.length > 0) {
                    const targetOption = creasingOptions.find(opt => opt.value === '2') || creasingOptions[0];
                    await creasingLines.selectOption({ value: targetOption.value });
                    console.log(`  - 오시선: ${targetOption.text} 선택`);
                }
            }
        } else {
            console.log('  ⚠️ 오시 옵션이 없습니다');
        }

        console.log('✅ 추가 옵션 설정 완료\n');

        // 4. 가격 계산
        console.log('4️⃣ 가격 계산 대기...');
        await page.waitForTimeout(2000); // AJAX 응답 대기

        // 가격 표시 확인
        const priceDisplay = await page.locator('.price-display').textContent();
        console.log(`  - 계산된 가격: ${priceDisplay}`);
        console.log('✅ 가격 계산 완료\n');

        // 5. 장바구니 추가
        console.log('5️⃣ 장바구니 추가 테스트...');

        // 주문하기 버튼 클릭하여 모달 열기
        const orderButton = await page.locator('button.btn-upload-order').first();
        if (await orderButton.isVisible()) {
            await orderButton.click();
            console.log('  - 주문하기 버튼 클릭 (모달 열기)');

            // 모달이 열릴 때까지 대기
            await page.waitForTimeout(1000);

            // 업로드 모달에서 파일 선택은 건너뛰기 (선택사항)
            console.log('  - 파일 업로드는 건너뜁니다 (선택사항)');

            // 장바구니에 저장 버튼 클릭
            const addToCartButton = await page.locator('button:has-text("장바구니에 저장")');
            if (await addToCartButton.isVisible()) {
                await addToCartButton.click();
                console.log('  - 장바구니에 저장 버튼 클릭');

                // 응답 대기
                await page.waitForTimeout(2000);
                console.log('  - 장바구니 추가 요청 전송 완료');
            } else {
                console.log('  ❌ 장바구니에 저장 버튼을 찾을 수 없습니다');
            }
        } else {
            console.log('  ❌ 주문하기 버튼을 찾을 수 없습니다');
        }

        console.log('\n✅ 카다록 추가 옵션 시스템 테스트 완료!');

        // 디버그 로그 확인
        console.log('\n📋 디버그 로그 확인:');
        console.log('  - /var/www/html/mlangprintauto/cadarok/debug_cart.log');

    } catch (error) {
        console.error('\n❌ 테스트 실패:', error.message);
        console.error(error.stack);
    } finally {
        await page.waitForTimeout(3000); // 결과 확인을 위해 대기
        await browser.close();
        console.log('\n🎬 테스트 종료');
    }
})();