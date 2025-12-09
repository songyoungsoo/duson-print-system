# Select 크기 조정 가이드

**작성일**: 2025-10-11
**목적**: 전체 제품 페이지의 select 박스 크기 통일

---

## 📐 현재 상태

### 제품별 Select 크기

| 제품 | 클래스 | 현재 크기 | 목표 크기 |
|------|--------|----------|----------|
| 전단지 (inserted) | - | 기본값 | 220px |
| 명함 (namecard) | `.namecard-page` | 180px | 220px |
| 봉투 (envelope) | `.envelope-page` | 160px | 220px |
| 스티커 (sticker_new) | - | 130px | 220px |
| 자석스티커 (msticker) | - | 기본값 | 220px |
| **포스터 (littleprint)** | `.littleprint-page` | **220px** ✅ | **220px** |
| 카다록 (cadarok) | - | 기본값 | 220px |
| 상품권 (merchandisebond) | - | 기본값 | 220px |
| NCR양식 (ncrflambeau) | - | 기본값 | 220px |

**기본값**: 150px (unified-inline-form.css)

---

## 🔧 수정 방법

### 1단계: CSS 파일 수정

**파일**: `/var/www/html/css/unified-inline-form.css`

**위치**: 제품별 전용 스타일 섹션

**추가할 CSS**:
```css
/* [제품명] 페이지 전용 */
.[product]-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}
```

### 2단계: HTML 파일 수정

**파일**: `/var/www/html/mlangprintauto/[product]/index.php`

**수정 위치**: `<body>` 태그

**변경 전**:
```html
<body>
```

**변경 후**:
```html
<body class="[product]-page">
```

---

## 📝 실제 작업 예시 (포스터)

### 1. CSS 수정

**파일**: `/var/www/html/css/unified-inline-form.css`

**추가된 코드** (Line 327-332):
```css
/* 포스터 페이지 전용 */
.poster-page .inline-select,
.littleprint-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}
```

### 2. HTML 수정

**파일**: `/var/www/html/mlangprintauto/littleprint/index.php`

**변경 사항** (Line 137):
```html
<!-- 변경 전 -->
<body>

<!-- 변경 후 -->
<body class="littleprint-page">
```

---

## 🚀 전체 제품 일괄 적용 방법

### CSS 파일에 모든 제품 스타일 추가

**파일**: `/var/www/html/css/unified-inline-form.css`

**추가할 섹션**:
```css
/* =================================================================== */
/* 제품별 Select 크기 통일 (220px 표준) */
/* =================================================================== */

/* 전단지 페이지 */
.inserted-page .inline-select,
.leaflet-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 명함 페이지 */
.namecard-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 봉투 페이지 */
.envelope-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 스티커 페이지 */
.sticker-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 자석스티커 페이지 */
.msticker-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 포스터 페이지 */
.poster-page .inline-select,
.littleprint-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 카다록 페이지 */
.cadarok-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 상품권 페이지 */
.merchandisebond-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* NCR양식 페이지 */
.ncrflambeau-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}
```

### 각 제품 index.php에 클래스 추가

**수정할 파일 목록**:
1. `/var/www/html/mlangprintauto/inserted/index.php` → `class="inserted-page"`
2. `/var/www/html/mlangprintauto/namecard/index.php` → `class="namecard-page"`
3. `/var/www/html/mlangprintauto/envelope/index.php` → `class="envelope-page"`
4. `/var/www/html/mlangprintauto/sticker_new/index.php` → `class="sticker-page"`
5. `/var/www/html/mlangprintauto/msticker/index.php` → `class="msticker-page"`
6. `/var/www/html/mlangprintauto/littleprint/index.php` → `class="littleprint-page"` ✅ 완료
7. `/var/www/html/mlangprintauto/cadarok/index.php` → `class="cadarok-page"`
8. `/var/www/html/mlangprintauto/merchandisebond/index.php` → `class="merchandisebond-page"`
9. `/var/www/html/mlangprintauto/ncrflambeau/index.php` → `class="ncrflambeau-page"`

---

## 🤖 자동화 스크립트

### CSS 일괄 추가 스크립트

```bash
#!/bin/bash
# 파일: /var/www/html/scripts/add_select_size_css.sh

CSS_FILE="/var/www/html/css/unified-inline-form.css"

# 백업
cp "$CSS_FILE" "$CSS_FILE.backup_$(date +%Y%m%d_%H%M%S)"

# 포스터 스타일 다음에 다른 제품들 추가
cat >> "$CSS_FILE" << 'EOF'

/* 전단지 페이지 */
.inserted-page .inline-select,
.leaflet-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 명함 페이지 */
.namecard-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 스티커 페이지 */
.sticker-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 자석스티커 페이지 */
.msticker-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 카다록 페이지 */
.cadarok-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* 상품권 페이지 */
.merchandisebond-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}

/* NCR양식 페이지 */
.ncrflambeau-page .inline-select {
    flex: 0 0 220px;
    width: 220px;
}
EOF

echo "✅ CSS 추가 완료"
```

### HTML 일괄 수정 스크립트

```bash
#!/bin/bash
# 파일: /var/www/html/scripts/add_page_classes.sh

declare -A PRODUCTS=(
    ["inserted"]="inserted-page"
    ["namecard"]="namecard-page"
    ["envelope"]="envelope-page"
    ["sticker_new"]="sticker-page"
    ["msticker"]="msticker-page"
    # ["littleprint"]="littleprint-page"  # 이미 완료
    ["cadarok"]="cadarok-page"
    ["merchandisebond"]="merchandisebond-page"
    ["ncrflambeau"]="ncrflambeau-page"
)

for product in "${!PRODUCTS[@]}"; do
    file="/var/www/html/mlangprintauto/$product/index.php"
    class="${PRODUCTS[$product]}"

    if [ -f "$file" ]; then
        # 백업
        cp "$file" "$file.backup_$(date +%Y%m%d_%H%M%S)"

        # <body> → <body class="xxx-page">
        sed -i "s/<body>/<body class=\"$class\">/" "$file"

        echo "✅ $product: class=\"$class\" 추가 완료"
    else
        echo "❌ $product: 파일을 찾을 수 없음"
    fi
done
```

---

## 🧪 테스트 방법

### 1. 브라우저 개발자 도구

```javascript
// Console에서 실행
document.querySelectorAll('.inline-select').forEach(el => {
    console.log(el.offsetWidth + 'px');
});
// 모두 220 출력되어야 함
```

### 2. 시각적 확인

각 제품 페이지 방문:
- http://localhost/mlangprintauto/inserted/
- http://localhost/mlangprintauto/namecard/
- http://localhost/mlangprintauto/envelope/
- ... (모든 제품)

**확인 사항**:
- ✅ 모든 select 박스가 동일한 너비 (220px)
- ✅ 옵션 텍스트가 잘리지 않음
- ✅ 레이아웃이 깨지지 않음
- ✅ 반응형에서도 정상 작동

### 3. CSS 검증

```bash
# 모든 제품 클래스가 추가되었는지 확인
grep -E "\.(inserted|namecard|envelope|sticker|msticker|littleprint|cadarok|merchandisebond|ncrflambeau)-page .inline-select" /var/www/html/css/unified-inline-form.css

# 결과: 9개 제품 모두 출력되어야 함
```

---

## 📊 작업 체크리스트

### CSS 수정
- [x] 포스터 (littleprint) - 220px ✅
- [ ] 전단지 (inserted) - 220px
- [ ] 명함 (namecard) - 220px
- [ ] 봉투 (envelope) - 220px
- [ ] 스티커 (sticker_new) - 220px
- [ ] 자석스티커 (msticker) - 220px
- [ ] 카다록 (cadarok) - 220px
- [ ] 상품권 (merchandisebond) - 220px
- [ ] NCR양식 (ncrflambeau) - 220px

### HTML 수정
- [x] 포스터 (littleprint) - `class="littleprint-page"` ✅
- [ ] 전단지 (inserted) - `class="inserted-page"`
- [ ] 명함 (namecard) - `class="namecard-page"`
- [ ] 봉투 (envelope) - `class="envelope-page"`
- [ ] 스티커 (sticker_new) - `class="sticker-page"`
- [ ] 자석스티커 (msticker) - `class="msticker-page"`
- [ ] 카다록 (cadarok) - `class="cadarok-page"`
- [ ] 상품권 (merchandisebond) - `class="merchandisebond-page"`
- [ ] NCR양식 (ncrflambeau) - `class="ncrflambeau-page"`

### 테스트
- [ ] 전 제품 브라우저 확인
- [ ] 반응형 테스트
- [ ] 크로스 브라우저 테스트

---

## 🎯 예상 효과

### 사용성 개선
- ✅ 모든 제품에서 일관된 UI/UX
- ✅ 긴 옵션명도 잘리지 않고 표시
- ✅ 시각적 정렬 및 가독성 향상

### 유지보수성
- ✅ 제품별 독립적인 스타일 관리
- ✅ 향후 개별 조정 용이
- ✅ 명확한 클래스 네이밍 규칙

### 통일성
- ✅ 220px 표준 크기로 브랜드 일관성
- ✅ 모든 제품 페이지 동일한 사용자 경험

---

## 📌 참고사항

### 기존 크기 정의 위치

**파일**: `/var/www/html/css/unified-inline-form.css`

```css
/* Line 96-100: 기본 크기 */
.inline-select,
.options-grid .inline-select {
    flex: 0 0 150px;
    width: 150px;
}

/* Line 103-107: Wide 크기 */
.inline-select.wide,
.options-grid .inline-select.wide {
    flex: 0 0 180px;
    width: 180px;
}

/* Line 110-114: Narrow 크기 */
.inline-select.narrow,
.options-grid .inline-select.narrow {
    flex: 0 0 100px;
    width: 100px;
}
```

### CSS 우선순위

제품별 스타일이 기본 스타일보다 우선 적용됨:
```
.littleprint-page .inline-select  (우선순위 높음)
> .inline-select                  (우선순위 낮음)
```

---

**작성자**: Claude (AI Assistant)
**문서 버전**: 1.0
**최종 수정**: 2025-10-11
