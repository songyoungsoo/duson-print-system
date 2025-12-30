# 견적서 규격 표시 수정 - 멀티라인 지원

**날짜**: 2025-12-28
**이슈**: 규격 정보가 한 줄로 표시됨 (예: "아트지유광기본사각")
**목표**: 각 항목을 줄바꿈하여 표시 (아트지유광 / 기본사각 / ...)

---

## 🔧 수정 내용

### 1. HTML 구조 변경 (create.php:730)

**변경 전**:
```html
<td class="col-spec">
    <input type="text" name="items[${itemIndex}][specification]" placeholder="규격/사양">
</td>
```

**문제점**:
- `<input type="text">`는 줄바꿈(`\n`) 표시 불가
- 모든 텍스트가 한 줄로 연결됨

**변경 후**:
```html
<td class="col-spec">
    <span class="spec-display" style="display: block; min-height: 20px; white-space: pre-line;"></span>
    <input type="hidden" name="items[${itemIndex}][specification]" value="">
</td>
```

**개선점**:
- `<span>` + `white-space: pre-line` → `\n`을 줄바꿈으로 렌더링
- hidden input으로 폼 제출 기능 유지
- 시각적으로 깔끔한 멀티라인 표시

---

### 2. JavaScript 수정 (calculator_modal.js:228-241)

**변경 전**:
```javascript
// 2. 규격 설정
const specInput = row.querySelector('input[name*="[specification]"]');
if (specInput) {
    specInput.value = data.specification || '';
    console.log('✅ 규격 설정:', data.specification);
}
```

**문제점**:
- input만 찾아서 value 설정
- 새로운 span 구조 지원 안 함

**변경 후**:
```javascript
// 2. 규격 설정 (span 표시 + hidden input 저장)
const specDisplay = row.querySelector('.spec-display');
const specInput = row.querySelector('input[name*="[specification]"]');

if (specDisplay && specInput) {
    const specText = data.specification || '';
    specDisplay.textContent = specText;  // span에 표시 (white-space: pre-line으로 줄바꿈 처리)
    specInput.value = specText;          // hidden input에 저장
    console.log('✅ 규격 설정:', specText);
} else if (specInput) {
    // Fallback: 기존 input 방식 (하위 호환성)
    specInput.value = data.specification || '';
    console.log('✅ 규격 설정 (legacy):', data.specification);
}
```

**개선점**:
- `.spec-display` span 찾아서 `textContent` 설정
- hidden input에도 동일한 값 저장 (폼 제출용)
- fallback 로직으로 하위 호환성 유지

---

## 🎯 작동 원리

### 규격 생성 (quotation-modal-common.js)
```javascript
// 예: 스티커
function buildStickerSpecification() {
    const parts = [];

    const jong = document.getElementById('jong');
    if (jong && jong.selectedOptions[0]) {
        parts.push(jong.selectedOptions[0].text);  // "아트지유광"
    }

    const domusong = document.getElementById('domusong');
    if (domusong && domusong.selectedOptions[0]) {
        parts.push(domusong.selectedOptions[0].text);  // "기본사각"
    }

    return parts.join('\n');  // "아트지유광\n기본사각"
}
```

### 데이터 전송
```javascript
window.parent.postMessage({
    type: 'CALCULATOR_PRICE_DATA',
    payload: {
        specification: "아트지유광\n기본사각\n고급 편집"
    }
}, window.location.origin);
```

### 표시 (calculator_modal.js)
```javascript
// 1. span 찾기
const specDisplay = row.querySelector('.spec-display');

// 2. textContent 설정 (줄바꿈 포함)
specDisplay.textContent = "아트지유광\n기본사각\n고급 편집";

// 3. CSS white-space: pre-line으로 렌더링
// → 실제 표시:
//   아트지유광
//   기본사각
//   고급 편집
```

---

## ✅ 예상 결과

### 변경 전
| 품목 | 규격 |
|------|------|
| 스티커 | 아트지유광기본사각고급 편집 |
| 카다록 | A4유광150g중철 |

### 변경 후
| 품목 | 규격 |
|------|------|
| 스티커 | 아트지유광<br>기본사각<br>고급 편집 |
| 카다록 | A4<br>유광150g<br>중철 |

---

## 🔍 브라우저 테스트 체크리스트

### 테스트 단계
1. **브라우저 하드 리프레시** (Ctrl+Shift+R)
   - create.php 캐시 무효화
   - calculator_modal.js 캐시 무효화

2. **각 품목별 테스트** (9개 품목)
   - [ ] 전단지 (inserted)
   - [ ] 명함 (namecard)
   - [ ] 봉투 (envelope)
   - [ ] 스티커 (sticker_new)
   - [ ] 자석스티커 (msticker)
   - [ ] 카다록 (cadarok)
   - [ ] 포스터 (littleprint)
   - [ ] 상품권 (merchandisebond)
   - [ ] NCR양식 (ncrflambeau)

3. **확인 사항**
   - 계산기 모달 열기
   - 옵션 선택
   - "견적서에 적용" 클릭
   - **규격/사양 칼럼 확인**: 멀티라인 표시 여부
   - **콘솔 로그 확인**: `✅ 규격 설정:` 메시지

4. **폼 제출 확인**
   - 견적서 저장/미리보기 클릭
   - 규격 데이터가 DB에 정상 저장되는지 확인

---

## 📋 수정 파일 목록

| 파일 | 라인 | 변경 내용 |
|------|------|-----------|
| `/var/www/html/mlangprintauto/quote/create.php` | 730 | input → span + hidden input 구조로 변경 |
| `/var/www/html/mlangprintauto/quote/includes/calculator_modal.js` | 228-241 | span 찾아서 textContent 설정 로직 추가 |

---

## 🎯 핵심 개념

### white-space: pre-line
```css
.spec-display {
    white-space: pre-line;  /* \n을 줄바꿈으로 렌더링 */
}
```

**동작**:
- 텍스트 내의 `\n` (newline) 문자를 실제 줄바꿈으로 표시
- 연속된 공백은 하나로 합침
- 텍스트가 컨테이너 너비를 초과하면 자동 줄바꿈

**다른 옵션과 비교**:
- `white-space: normal`: `\n` 무시 (기본값)
- `white-space: pre`: `\n` 표시, 자동 줄바꿈 X, 공백 유지
- `white-space: pre-wrap`: `\n` 표시, 자동 줄바꿈 O, 공백 유지
- **`white-space: pre-line`**: `\n` 표시, 자동 줄바꿈 O, 공백 정리 (✅ 가장 적합)

---

## 🚀 Git 커밋 메시지 (예정)

```bash
git add mlangprintauto/quote/create.php
git add mlangprintauto/quote/includes/calculator_modal.js

git commit -m "Fix specification display - support multi-line formatting

- Change create.php line 730: input → span with white-space: pre-line
- Update calculator_modal.js lines 228-241: populate span.textContent
- Add hidden input for form submission compatibility
- Support \n characters in specification strings
- Improve readability of specification column in quote table"

git push origin main
```

---

**작성일**: 2025-12-28
**작성자**: Claude Code
**상태**: 수정 완료 (브라우저 테스트 필요)
**다음 단계**: 브라우저에서 9개 품목 전체 테스트 후 결과 보고
