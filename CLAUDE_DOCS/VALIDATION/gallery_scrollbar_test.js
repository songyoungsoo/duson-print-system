/**
 * 갤러리 스크롤바 완전 제거 검증 스크립트
 * 사용법: 브라우저 개발자 도구 콘솔에서 실행
 *
 * Usage: 스티커 페이지에서 F12 → Console → 이 스크립트 복사/붙여넣기 → Enter
 */

function validateGalleryScrollbarRemoval() {
    console.log('🔍 갤러리 스크롤바 검증 시작...\n');

    const results = {
        totalElements: 0,
        scrollbarElements: 0,
        passedElements: 0,
        details: []
    };

    // 검사할 갤러리 관련 요소들
    const selectors = [
        '.gallery-section',
        '.gallery-container',
        '.gallery-grid',
        '.calculator-section',
        '#stickerGallery',
        '.common-gallery-section',
        '.proof-gallery',
        '.proof-thumbs'
    ];

    selectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);

        elements.forEach((el, index) => {
            results.totalElements++;

            const computedStyle = window.getComputedStyle(el);
            const rect = el.getBoundingClientRect();

            // 스크롤바 존재 여부 검사
            const hasVerticalScrollbar = el.scrollHeight > el.clientHeight;
            const hasHorizontalScrollbar = el.scrollWidth > el.clientWidth;
            const hasAnyScrollbar = hasVerticalScrollbar || hasHorizontalScrollbar;

            // CSS overflow 속성 검사
            const overflowX = computedStyle.overflowX;
            const overflowY = computedStyle.overflowY;
            const overflow = computedStyle.overflow;

            // 문제가 있는 overflow 속성들
            const problematicOverflow = [
                overflowX === 'auto',
                overflowX === 'scroll',
                overflowY === 'auto',
                overflowY === 'scroll',
                overflow === 'auto',
                overflow === 'scroll'
            ].some(Boolean);

            const elementResult = {
                selector: selector,
                index: index,
                element: el,
                hasScrollbar: hasAnyScrollbar,
                hasVerticalScrollbar,
                hasHorizontalScrollbar,
                overflowX,
                overflowY,
                overflow,
                problematicOverflow,
                scrollHeight: el.scrollHeight,
                clientHeight: el.clientHeight,
                scrollWidth: el.scrollWidth,
                clientWidth: el.clientWidth,
                dimensions: `${Math.round(rect.width)}×${Math.round(rect.height)}`,
                passed: !hasAnyScrollbar && !problematicOverflow
            };

            results.details.push(elementResult);

            if (elementResult.passed) {
                results.passedElements++;
            } else {
                results.scrollbarElements++;
            }

            // 콘솔에 상세 결과 출력
            const status = elementResult.passed ? '✅' : '❌';
            const issues = [];

            if (hasVerticalScrollbar) issues.push('세로 스크롤바');
            if (hasHorizontalScrollbar) issues.push('가로 스크롤바');
            if (problematicOverflow) issues.push(`문제 있는 overflow: ${overflowX}/${overflowY}`);

            console.log(`${status} ${selector}[${index}] - ${elementResult.dimensions}`,
                       issues.length > 0 ? `문제: ${issues.join(', ')}` : '✅ 정상');
        });
    });

    // 최종 결과 출력
    console.log('\n🎯 최종 검증 결과:');
    console.log(`총 검사 요소: ${results.totalElements}개`);
    console.log(`정상 요소: ${results.passedElements}개`);
    console.log(`문제 요소: ${results.scrollbarElements}개`);

    const successRate = results.totalElements > 0 ?
        Math.round((results.passedElements / results.totalElements) * 100) : 0;

    console.log(`성공률: ${successRate}%`);

    if (results.scrollbarElements === 0) {
        console.log('\n🎉 축하합니다! 모든 갤러리 요소에서 스크롤바가 완전히 제거되었습니다!');
        console.log('✅ 스크롤바 제거 작업이 성공적으로 완료되었습니다.');
    } else {
        console.log('\n⚠️ 아직 해결해야 할 문제가 있습니다:');

        results.details
            .filter(detail => !detail.passed)
            .forEach(detail => {
                console.log(`❌ ${detail.selector}[${detail.index}]:`, detail);
            });
    }

    // 브라우저 정보도 출력
    console.log(`\n🌐 브라우저 정보: ${navigator.userAgent.split(' ').pop()}`);
    console.log(`화면 크기: ${window.innerWidth}×${window.innerHeight}`);

    return results;
}

// 추가 도우미 함수들
function highlightScrollbarElements() {
    console.log('🖍️ 스크롤바가 있는 요소들을 하이라이트합니다...');

    const results = validateGalleryScrollbarRemoval();

    results.details
        .filter(detail => detail.hasScrollbar)
        .forEach(detail => {
            detail.element.style.outline = '3px solid red';
            detail.element.style.backgroundColor = 'rgba(255, 0, 0, 0.1)';

            // 3초 후 하이라이트 제거
            setTimeout(() => {
                detail.element.style.outline = '';
                detail.element.style.backgroundColor = '';
            }, 3000);
        });
}

function checkSpecificElement(selector) {
    console.log(`🔍 특정 요소 검사: ${selector}`);

    const element = document.querySelector(selector);

    if (!element) {
        console.log(`❌ 요소를 찾을 수 없습니다: ${selector}`);
        return;
    }

    const computedStyle = window.getComputedStyle(element);
    const hasVerticalScrollbar = element.scrollHeight > element.clientHeight;
    const hasHorizontalScrollbar = element.scrollWidth > element.clientWidth;

    console.log('요소 정보:', {
        selector,
        scrollHeight: element.scrollHeight,
        clientHeight: element.clientHeight,
        scrollWidth: element.scrollWidth,
        clientWidth: element.clientWidth,
        hasVerticalScrollbar,
        hasHorizontalScrollbar,
        overflowX: computedStyle.overflowX,
        overflowY: computedStyle.overflowY,
        overflow: computedStyle.overflow
    });
}

// 자동 실행
console.log('🚀 갤러리 스크롤바 검증 도구가 로드되었습니다!');
console.log('📝 사용 가능한 함수들:');
console.log('  - validateGalleryScrollbarRemoval(): 전체 검증');
console.log('  - highlightScrollbarElements(): 문제 요소 하이라이트');
console.log('  - checkSpecificElement(selector): 특정 요소 검사');
console.log('\n자동 검증을 시작합니다...\n');

// 페이지 로드 완료 후 자동 검증 실행
if (document.readyState === 'complete') {
    validateGalleryScrollbarRemoval();
} else {
    window.addEventListener('load', validateGalleryScrollbarRemoval);
}