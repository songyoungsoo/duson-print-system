# 체크박스 정렬 시스템 가이드

## 📋 개요

웹 페이지에서 체크박스와 텍스트 라벨의 정렬과 간격을 일관되게 관리하는 시스템입니다.

## 🎯 핵심 원칙

### 1. **Flexbox 기반 정렬**
```css
.option-headers-row {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

.option-checkbox-group {
    display: flex;
    align-items: center;
}
```

### 2. **간격 제어 레벨**
- **그룹간 간격**: `gap` 속성으로 제어
- **체크박스-텍스트 간격**: `margin-left`로 제어
- **컨테이너 여백**: `padding-left/right`로 제어

## 🔧 구현 패턴

### 기본 HTML 구조
```html
<div class="option-group" style="padding-left: 20px; padding-right: 20px;">
    <div class="option-headers-row" style="display: flex; gap: 0px; align-items: center; flex-wrap: wrap;">
        <div class="option-checkbox-group" style="display: flex; align-items: center;">
            <input type="checkbox" id="option1" class="option-toggle"
                   style="margin: 0; padding: 0; margin-right: -4px;">
            <label for="option1" class="option-label"
                   style="margin: 0; padding: 0; margin-left: 6px; text-align: left;">옵션1</label>
        </div>
        <div class="option-checkbox-group" style="display: flex; align-items: center;">
            <input type="checkbox" id="option2" class="option-toggle"
                   style="margin: 0; padding: 0; margin-right: -4px;">
            <label for="option2" class="option-label"
                   style="margin: 0; padding: 0; margin-left: 6px; text-align: left;">옵션2</label>
        </div>
    </div>
</div>
```

## 📏 간격 설정 값

### 표준 간격값
| 요소 | 속성 | 권장값 | 설명 |
|------|------|--------|------|
| 컨테이너 여백 | `padding-left/right` | `20px` | 전체 영역 좌우 여백 |
| 그룹간 간격 | `gap` | `0px-8px` | 체크박스 그룹 사이 간격 |
| 체크박스 우측 | `margin-right` | `-4px` | 체크박스와 라벨 사이 조정 |
| 라벨 좌측 | `margin-left` | `6px` | 최적 가독성 간격 |

### 간격 조정 가이드
```css
/* 매우 촘촘 */
gap: 0px; margin-left: 2px;

/* 표준 (권장) */
gap: 0px; margin-left: 6px;

/* 여유로운 */
gap: 8px; margin-left: 8px;

/* 모바일 최적화 */
gap: 4px; margin-left: 8px;
```

## 🎨 스타일링 옵션

### 1. 호버 효과 CSS
```css
.option-checkbox-group:hover .option-label {
    color: #4299e1;
    transition: color 0.2s ease;
}
```

### 2. 선택 상태 스타일
```css
.option-checkbox-group input[type="checkbox"]:checked + .option-label {
    color: #2b6cb0;
    font-weight: 600;
}
```

### 3. 반응형 디자인
```css
@media (max-width: 768px) {
    .option-headers-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }

    .option-checkbox-group {
        gap: 8px;
    }
}
```

## 🔨 사용법 및 적용 단계

### STEP 1: 기본 구조 생성
1. 컨테이너 div에 `option-group` 클래스 및 좌우 패딩 적용
2. 헤더 row에 flexbox 설정 및 gap 조정
3. 각 체크박스 그룹을 `option-checkbox-group`으로 감싸기

### STEP 2: 간격 조정
1. **그룹간 간격**: `gap` 값 조정 (0px-8px)
2. **라벨 간격**: `margin-left` 값 조정 (2px-8px)
3. **컨테이너 여백**: `padding-left/right` 조정 (10px-30px)

### STEP 3: 텍스트 정렬
1. 모든 라벨에 `text-align: left` 적용
2. 일관된 시작점으로 텍스트 길이 차이 해결

## 📋 체크리스트

### 적용 전 확인사항
- [ ] 체크박스 개수 및 텍스트 길이 확인
- [ ] 컨테이너 폭 및 반응형 요구사항 확인
- [ ] 기존 CSS와의 충돌 여부 확인

### 적용 후 테스트
- [ ] 데스크톱에서 정렬 확인
- [ ] 모바일에서 반응형 동작 확인
- [ ] 텍스트 길이가 다른 경우 간격 일관성 확인
- [ ] 호버/선택 상태 동작 확인

## 🚀 빠른 적용 템플릿

### 기본 템플릿
```html
<!-- 복사해서 사용 -->
<div class="option-group" style="padding-left: 20px; padding-right: 20px;">
    <div class="option-headers-row" style="display: flex; gap: 0px; align-items: center; flex-wrap: wrap;">
        <!-- 체크박스 반복 구조 -->
        <div class="option-checkbox-group" style="display: flex; align-items: center;">
            <input type="checkbox" id="OPTION_ID" class="option-toggle"
                   style="margin: 0; padding: 0; margin-right: -4px;">
            <label for="OPTION_ID" class="option-label"
                   style="margin: 0; padding: 0; margin-left: 6px; text-align: left;">OPTION_TEXT</label>
        </div>
    </div>
</div>
```

### 변수 치환
- `OPTION_ID`: 고유한 체크박스 ID
- `OPTION_TEXT`: 표시할 텍스트
- 필요에 따라 간격값 조정

## 📱 반응형 고려사항

### 모바일 최적화
```css
@media (max-width: 768px) {
    /* 세로 배치로 변경 */
    .option-headers-row {
        flex-direction: column !important;
        gap: 12px !important;
    }

    /* 터치 친화적 크기 */
    .option-checkbox-group input[type="checkbox"] {
        width: 18px !important;
        height: 18px !important;
    }
}
```

## 🎯 사용 시나리오

### 1. 전자상거래 옵션 선택
```
☑️무료배송  ☑️당일배송  ☑️포장지  ☑️카드할인
```

### 2. 폼 필터링
```
☑️전체  ☑️완료  ☑️진행중  ☑️대기
```

### 3. 설정 페이지
```
☑️알림받기  ☑️이메일수신  ☑️SMS수신  ☑️마케팅동의
```

## 💡 팁 & 트릭

### 간격 미세조정
- 한글 텍스트: `margin-left: 6px` 권장
- 영문 텍스트: `margin-left: 4px` 권장
- 숫자만: `margin-left: 3px` 권장

### 브라우저 호환성
- `accent-color` 속성으로 체크박스 색상 통일
- `user-select: none`으로 텍스트 선택 방지

### 접근성 고려
- 라벨과 체크박스를 `for/id`로 연결
- 충분한 클릭 영역 확보
- 색상 대비 확인

## 🔄 업데이트 이력

- **v1.0** (2025-01-28): 초기 시스템 구축
- 기본 flexbox 정렬 시스템
- 간격 제어 체계 확립
- 반응형 고려사항 정의

---

**참고**: 이 시스템은 두손기획인쇄 프로젝트의 명함 페이지에서 검증되었으며, 다른 제품 페이지에도 동일하게 적용 가능합니다.