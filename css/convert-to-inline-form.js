/**
 * 기존 페이지를 통일 인라인 폼 스타일로 자동 변환하는 스크립트
 *
 * 사용법:
 * node convert-to-inline-form.js [파일경로]
 * 또는 HTML 페이지에서 직접 실행
 *
 * @version 1.0
 * @date 2025-01-14
 */

// HTML 변환 함수
function convertToInlineForm() {
    console.log('🔄 인라인 폼 스타일 변환 시작...');

    let changes = 0;

    // 1. CSS 링크 추가 (아직 없다면)
    if (!document.querySelector('link[href*="unified-inline-form.css"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = '../../css/unified-inline-form.css';
        document.head.appendChild(link);
        console.log('✅ CSS 링크 추가 완료');
        changes++;
    }

    // 2. 기존 form-grid를 inline-form-container로 변환
    document.querySelectorAll('.form-grid, .calculator-form, .order-form').forEach(container => {
        container.classList.add('inline-form-container');
        container.classList.remove('form-grid', 'calculator-form', 'order-form');
        console.log('✅ 컨테이너 클래스 변환:', container);
        changes++;
    });

    // 3. form-group을 inline-form-row로 변환
    document.querySelectorAll('.form-group, .option-row, .field-group').forEach(row => {
        row.classList.add('inline-form-row');
        row.classList.remove('form-group', 'option-row', 'field-group');
        changes++;
    });

    // 4. label을 span.inline-label로 변환 (편집디자인 → 편집비 자동 변경)
    document.querySelectorAll('label').forEach(label => {
        if (!label.classList.contains('inline-label')) {
            const span = document.createElement('span');
            span.className = 'inline-label';
            // 편집디자인을 편집비로 자동 변경
            span.textContent = label.textContent.replace('편집디자인', '편집비');

            // 라벨 길이에 따라 클래스 추가 (편집비는 기본 크기 사용)
            const text = span.textContent;
            if (text === '편집비') {
                // 편집비는 기본 크기 사용 (wide 클래스 제거)
            } else if (text.length > 4) {
                span.classList.add('wide');
            } else if (text.length < 3) {
                span.classList.add('narrow');
            }

            label.parentNode.replaceChild(span, label);
            console.log('✅ 라벨 변환:', span.textContent);
            changes++;
        }
    });

    // 5. select, input 클래스 변환
    document.querySelectorAll('select:not(.inline-select)').forEach(select => {
        select.classList.add('inline-select');
        select.classList.remove('form-control', 'form-select', 'option-select');

        // 편집비 셀렉트박스는 기본 크기 사용, 다른 것들은 옵션명 길이에 따라 조정
        const parentRow = select.closest('.inline-form-row');
        const label = parentRow ? parentRow.querySelector('.inline-label') : null;

        if (label && label.textContent === '편집비') {
            // 편집비는 기본 크기 사용 (wide 클래스 제거)
        } else {
            // 옵션명 길이에 따라 폭 조정
            let maxLength = 0;
            select.querySelectorAll('option').forEach(option => {
                if (option.textContent.length > maxLength) {
                    maxLength = option.textContent.length;
                }
            });

            if (maxLength > 10) {
                select.classList.add('wide');
            } else if (maxLength < 6) {
                select.classList.add('narrow');
            }
        }

        changes++;
    });

    document.querySelectorAll('input[type="text"], input[type="number"]:not(.inline-input)').forEach(input => {
        input.classList.add('inline-input');
        input.classList.remove('form-control', 'form-input');
        changes++;
    });

    // 6. 도움말 텍스트 추가/변환
    document.querySelectorAll('.help-text, .form-text, .field-help').forEach(help => {
        help.classList.add('inline-note');
        help.classList.remove('help-text', 'form-text', 'field-help');

        // 경고/정보 스타일 자동 감지
        if (help.textContent.includes('주의') || help.textContent.includes('※') || help.textContent.includes('경고')) {
            help.classList.add('warning');
        } else if (help.textContent.includes('정보') || help.textContent.includes('ℹ️')) {
            help.classList.add('info');
        }

        changes++;
    });

    // 7. 누락된 설명 텍스트 자동 생성
    document.querySelectorAll('.inline-form-row').forEach(row => {
        const hasNote = row.querySelector('.inline-note');
        const select = row.querySelector('.inline-select');
        const input = row.querySelector('.inline-input');
        const label = row.querySelector('.inline-label');

        if (!hasNote && (select || input) && label) {
            const note = document.createElement('span');
            note.className = 'inline-note';

            // 라벨에 따른 기본 설명 생성
            const labelText = label.textContent.toLowerCase();
            if (labelText.includes('수량') || labelText.includes('매수')) {
                note.textContent = '원하시는 수량을 선택하세요';
            } else if (labelText.includes('크기') || labelText.includes('가로') || labelText.includes('세로')) {
                note.textContent = '단위: mm';
            } else if (labelText.includes('재질') || labelText.includes('종류')) {
                note.textContent = '용도에 맞는 옵션을 선택하세요';
            } else if (labelText.includes('편집') || labelText.includes('디자인')) {
                note.textContent = '디자인 작업 포함 여부';
            } else {
                note.textContent = '옵션을 선택해주세요';
            }

            row.appendChild(note);
            console.log('✅ 설명 텍스트 자동 생성:', note.textContent);
            changes++;
        }
    });

    // 8. 제품별 바디 클래스 추가
    const currentPath = window.location.pathname.toLowerCase();
    if (currentPath.includes('sticker')) {
        document.body.classList.add('sticker-page');
    } else if (currentPath.includes('namecard')) {
        document.body.classList.add('namecard-page');
    } else if (currentPath.includes('envelope')) {
        document.body.classList.add('envelope-page');
    } else if (currentPath.includes('inserted') || currentPath.includes('flyer')) {
        document.body.classList.add('flyer-page');
    }

    console.log(`🎉 변환 완료! 총 ${changes}개 요소 변경됨`);

    // 결과 알림
    if (changes > 0) {
        alert(`✅ 인라인 폼 스타일 변환 완료!\n\n변경된 요소: ${changes}개\n\n새로고침하여 결과를 확인하세요.`);
    } else {
        alert('ℹ️ 변환할 요소가 없습니다. 이미 적용되어 있거나 해당 요소를 찾을 수 없습니다.');
    }
}

// 자동 실행 함수 (페이지 로드 시)
function autoConvertOnLoad() {
    document.addEventListener('DOMContentLoaded', function() {
        // URL에 ?convert=true가 있으면 자동 변환
        if (window.location.search.includes('convert=true')) {
            setTimeout(convertToInlineForm, 1000);
        }
    });
}

// Node.js 환경에서 사용하는 경우
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { convertToInlineForm };
}

// 브라우저 환경에서 전역 함수로 등록
if (typeof window !== 'undefined') {
    window.convertToInlineForm = convertToInlineForm;
    autoConvertOnLoad();
}

// 콘솔에서 사용할 수 있는 단축키
console.log('🎨 인라인 폼 변환 도구 로드됨');
console.log('사용법: convertToInlineForm() 실행 또는 URL에 ?convert=true 추가');