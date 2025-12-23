/**
 * NCR (양식지) 페이지 체크박스 토글 테스트
 *
 * 테스트 목표:
 * 1. 넘버링 체크박스 클릭 시 셀렉트 표시/숨김
 * 2. 미싱 체크박스 클릭 시 셀렉트 표시/숨김
 * 3. 선택한 옵션 값이 유지되는지 확인
 * 4. 체크 해제 시 선택값 초기화 확인
 */

const { test, expect } = require('@playwright/test');

test.describe('NCR 체크박스 토글 기능 테스트', () => {
    const baseUrl = 'http://localhost/mlangprintauto/ncrflambeau/index.php';

    test.beforeEach(async ({ page }) => {
        await page.goto(baseUrl);
        // 페이지 로드 대기
        await page.waitForLoadState('networkidle');
    });

    test('넘버링 체크박스 클릭 시 셀렉트가 표시되어야 함', async ({ page }) => {
        // 넘버링 체크박스 찾기
        const numberingCheckbox = page.locator('#numbering_enabled');
        await expect(numberingCheckbox).toBeVisible();

        // 초기 상태: 셀렉트 숨김
        const numberingOptions = page.locator('#numbering_options');
        await expect(numberingOptions).toBeHidden();

        // 체크박스 클릭
        await numberingCheckbox.check();

        // 셀렉트가 표시되어야 함
        await expect(numberingOptions).toBeVisible();
        await expect(numberingOptions).toHaveClass(/show/);

        // 콘솔 로그 확인
        const logs = [];
        page.on('console', msg => logs.push(msg.text()));

        console.log('✅ 넘버링 체크박스 클릭 시 셀렉트 표시 성공');
    });

    test('넘버링 셀렉트에서 옵션 선택 시 값이 유지되어야 함', async ({ page }) => {
        // 넘버링 체크박스 체크
        await page.locator('#numbering_enabled').check();

        // 셀렉트가 표시될 때까지 대기
        await page.waitForSelector('#numbering_options', { state: 'visible' });

        // 옵션 선택
        const numberingSelect = page.locator('#numbering_type');
        await numberingSelect.selectOption('single');

        // 선택된 값 확인
        const selectedValue = await numberingSelect.inputValue();
        expect(selectedValue).toBe('single');

        console.log('✅ 넘버링 옵션 선택값 유지 성공');
    });

    test('넘버링 체크 해제 시 셀렉트가 숨겨지고 값이 초기화되어야 함', async ({ page }) => {
        // 넘버링 체크 및 옵션 선택
        await page.locator('#numbering_enabled').check();
        await page.waitForSelector('#numbering_options', { state: 'visible' });
        await page.locator('#numbering_type').selectOption('double');

        // 체크 해제
        await page.locator('#numbering_enabled').uncheck();

        // 셀렉트 숨김 확인
        const numberingOptions = page.locator('#numbering_options');
        await expect(numberingOptions).toBeHidden();
        await expect(numberingOptions).toHaveClass(/hide/);

        // 다시 체크했을 때 값이 초기화되었는지 확인
        await page.locator('#numbering_enabled').check();
        await page.waitForSelector('#numbering_options', { state: 'visible' });
        const selectedValue = await page.locator('#numbering_type').inputValue();
        expect(selectedValue).toBe('');

        console.log('✅ 넘버링 체크 해제 및 값 초기화 성공');
    });

    test('미싱 체크박스 클릭 시 셀렉트가 표시되어야 함', async ({ page }) => {
        // 미싱 체크박스 찾기
        const perforationCheckbox = page.locator('#perforation_enabled');
        await expect(perforationCheckbox).toBeVisible();

        // 초기 상태: 셀렉트 숨김
        const perforationOptions = page.locator('#perforation_options');
        await expect(perforationOptions).toBeHidden();

        // 체크박스 클릭
        await perforationCheckbox.check();

        // 셀렉트가 표시되어야 함
        await expect(perforationOptions).toBeVisible();
        await expect(perforationOptions).toHaveClass(/show/);

        console.log('✅ 미싱 체크박스 클릭 시 셀렉트 표시 성공');
    });

    test('미싱 셀렉트에서 옵션 선택 시 값이 유지되어야 함', async ({ page }) => {
        // 미싱 체크박스 체크
        await page.locator('#perforation_enabled').check();

        // 셀렉트가 표시될 때까지 대기
        await page.waitForSelector('#perforation_options', { state: 'visible' });

        // 옵션 선택
        const perforationSelect = page.locator('#perforation_type');
        await perforationSelect.selectOption('horizontal');

        // 선택된 값 확인
        const selectedValue = await perforationSelect.inputValue();
        expect(selectedValue).toBe('horizontal');

        console.log('✅ 미싱 옵션 선택값 유지 성공');
    });

    test('미싱 체크 해제 시 셀렉트가 숨겨지고 값이 초기화되어야 함', async ({ page }) => {
        // 미싱 체크 및 옵션 선택
        await page.locator('#perforation_enabled').check();
        await page.waitForSelector('#perforation_options', { state: 'visible' });
        await page.locator('#perforation_type').selectOption('cross');

        // 체크 해제
        await page.locator('#perforation_enabled').uncheck();

        // 셀렉트 숨김 확인
        const perforationOptions = page.locator('#perforation_options');
        await expect(perforationOptions).toBeHidden();
        await expect(perforationOptions).toHaveClass(/hide/);

        // 다시 체크했을 때 값이 초기화되었는지 확인
        await page.locator('#perforation_enabled').check();
        await page.waitForSelector('#perforation_options', { state: 'visible' });
        const selectedValue = await page.locator('#perforation_type').inputValue();
        expect(selectedValue).toBe('');

        console.log('✅ 미싱 체크 해제 및 값 초기화 성공');
    });

    test('넘버링과 미싱 동시 선택 가능 확인', async ({ page }) => {
        // 넘버링 선택
        await page.locator('#numbering_enabled').check();
        await page.waitForSelector('#numbering_options', { state: 'visible' });
        await page.locator('#numbering_type').selectOption('single');

        // 미싱 선택
        await page.locator('#perforation_enabled').check();
        await page.waitForSelector('#perforation_options', { state: 'visible' });
        await page.locator('#perforation_type').selectOption('horizontal');

        // 두 옵션 모두 표시되고 값이 유지되는지 확인
        await expect(page.locator('#numbering_options')).toBeVisible();
        await expect(page.locator('#perforation_options')).toBeVisible();

        const numberingValue = await page.locator('#numbering_type').inputValue();
        const perforationValue = await page.locator('#perforation_type').inputValue();

        expect(numberingValue).toBe('single');
        expect(perforationValue).toBe('horizontal');

        console.log('✅ 넘버링과 미싱 동시 선택 성공');
    });

    test('CSS 클래스가 올바르게 적용되는지 확인', async ({ page }) => {
        const numberingOptions = page.locator('#numbering_options');

        // 초기 상태
        await expect(numberingOptions).toHaveCSS('display', 'none');

        // 체크 시
        await page.locator('#numbering_enabled').check();
        await expect(numberingOptions).toHaveCSS('display', 'block');
        await expect(numberingOptions).toHaveClass(/show/);

        // 체크 해제 시
        await page.locator('#numbering_enabled').uncheck();
        await expect(numberingOptions).toHaveCSS('display', 'none');
        await expect(numberingOptions).toHaveClass(/hide/);

        console.log('✅ CSS 클래스 적용 확인 성공');
    });
});
