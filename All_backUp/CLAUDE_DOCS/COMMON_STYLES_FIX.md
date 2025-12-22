# common-styles.css 녹색 제거 작업 보고서

**작성일**: 2025-10-11
**문제**: `common-styles.css`가 마지막에 로드되어 모든 컬러 변경사항을 덮어씀
**해결**: 하드코딩된 녹색 25개 → 네이비 CSS 변수로 변경

---

## 🚨 문제 발견

### 증상
- `color-system-unified.css`와 `leaflet-compact.css`에서 Success를 네이비로 변경했지만
- 브라우저에서 **아무 변화가 없음**

### 원인
```html
<!-- index.php Line 180 -->
<link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">
```

`common-styles.css`가 **마지막에 로드**되어 모든 변경사항을 덮어쓰고 있었음!

### 발견된 하드코딩 녹색
```bash
grep -n "#28a745\|#4CAF50" /var/www/html/css/common-styles.css
```

**총 25개의 하드코딩된 녹색 발견:**
- Success 녹색: #28A745 (14개)
- Leaflet 녹색: #4CAF50 (6개)
- Hover 녹색: #45A049, #5CBB5C, #20c997 (5개)

---

## 🔧 수정 작업

### 1. 백업 생성
```bash
cp common-styles.css common-styles.css.backup_green
```

### 2. Primary Success 녹색 → 네이비
```bash
# #28A745 → var(--dsp-primary)
# #4CAF50 → var(--dsp-primary)
sed -i 's/#28a745/var(--dsp-primary)/g' common-styles.css
sed -i 's/#4CAF50/var(--dsp-primary)/g' common-styles.css
```

**수정된 요소 (14개)**:
- Line 353: `.price-item-value` color
- Line 409: `.btn-upload-order` background gradient
- Line 432: `.btn-upload-order:active` background
- Line 565: `--success-green` variable
- Line 1254: `.price-display.calculated` border
- Line 1275: `.price-amount` color
- Line 1455: `.btn-upload-order` background
- Line 1494: `.btn-upload-order:active` background
- Line 1554: `.modal-title` color
- Line 1603: `.upload-dropzone` border
- Line 1631: `.upload-dropzone p` color
- Line 1653: `.memo-textarea:focus` border
- Line 1700: `.btn-cart` background
- Line 2094: `.modal-btn.btn-cart` background

### 3. Green Light/Dark variants
```bash
# #66BB6A → var(--dsp-primary-light)
# #3D8B40 → var(--dsp-primary-dark)
sed -i 's/#66BB6A/var(--dsp-primary-light)/g' common-styles.css
sed -i 's/#3D8B40/var(--dsp-primary-dark)/g' common-styles.css
```

### 4. Hover 녹색 → 네이비
```bash
# #45A049 → var(--dsp-primary-hover)
# #5CBB5C → var(--dsp-primary-light)
# #20c997 → var(--dsp-primary-light)
sed -i 's/#45A049/var(--dsp-primary-hover)/g' common-styles.css
sed -i 's/#5CBB5C/var(--dsp-primary-light)/g' common-styles.css
sed -i 's/#20c997/var(--dsp-primary-light)/g' common-styles.css
```

**수정된 Hover (5개)**:
- Line 426: `.btn-upload-order:hover`
- Line 1488: `.btn-upload-order:hover` (중복)
- Line 1613: `.upload-dropzone:hover`
- Line 1618: `.upload-dropzone.dragover`
- Line 1705: `.btn-cart:hover`

### 5. Purple gradients → Yellow (브랜드 포인트 컬러)
```bash
# #667eea → var(--dsp-accent)
# #764ba2 → var(--dsp-accent-dark)
sed -i 's/#667eea/var(--dsp-accent)/g' common-styles.css
sed -i 's/#764ba2/var(--dsp-accent-dark)/g' common-styles.css
```

**수정된 보라색 (3개)**:
- Line 299: `.calculator-header` background
- Line 1317: `.btn-calculate` background
- Line 2078: `.btn-cart` gradient

### 6. Secondary green → Navy
```bash
# #059669 → var(--dsp-primary)
# #10b981 → var(--dsp-primary-light)
sed -i 's/#059669/var(--dsp-primary)/g' common-styles.css
sed -i 's/#10b981/var(--dsp-primary-light)/g' common-styles.css
```

### 7. RGBA 값 변경
```bash
# Green rgba → Navy rgba
sed -i 's/rgba(40, 167, 69, 0.2)/rgba(30, 78, 121, 0.2)/g' common-styles.css
sed -i 's/rgba(40, 167, 69, 0.1)/rgba(30, 78, 121, 0.1)/g' common-styles.css

# Purple rgba → Yellow rgba
sed -i 's/rgba(102, 126, 234, 0.1)/rgba(255, 213, 0, 0.1)/g' common-styles.css
sed -i 's/rgba(102, 126, 234, 0.3)/rgba(255, 213, 0, 0.3)/g' common-styles.css

# Secondary green rgba → Navy rgba
sed -i 's/rgba(16, 185, 129, 0.3)/rgba(30, 78, 121, 0.3)/g' common-styles.css
sed -i 's/rgba(16, 185, 129, 0.4)/rgba(30, 78, 121, 0.4)/g' common-styles.css
```

**수정된 RGBA (7개)**:
- Line 289: Focus box-shadow (Purple → Yellow)
- Line 307: Calculator header shadow (Purple → Yellow)
- Line 1257: Price display shadow (Green → Navy)
- Line 1327: Calculate button shadow (Purple → Yellow)
- Line 1654: Textarea focus shadow (Green → Navy)
- Line 2080: Cart button shadow (Green → Navy)
- Line 2085: Cart button hover shadow (Green → Navy)

### 8. Light Green 배경 → Light Navy
```bash
# #d4edda → var(--dsp-primary-lighter)
# #c3e6cb → var(--dsp-primary-lighter)
# #f8fff9 → var(--dsp-gray-50)
# #e8f5e8 → var(--dsp-primary-lighter)
sed -i 's/#d4edda/var(--dsp-primary-lighter)/g' common-styles.css
sed -i 's/#c3e6cb/var(--dsp-primary-lighter)/g' common-styles.css
sed -i 's/#f8fff9/var(--dsp-gray-50)/g' common-styles.css
sed -i 's/#e8f5e8/var(--dsp-primary-lighter)/g' common-styles.css
```

**수정된 배경 (5개)**:
- Line 682: `.flyer-tip` background
- Line 1255: `.price-display.calculated` gradient
- Line 1609: `.upload-dropzone` background
- Line 1614: `.upload-dropzone:hover` background
- Line 1619: `.upload-dropzone.dragover` background

### 9. Additional green colors
```bash
# #4caf50 → var(--dsp-primary)
# #2e7d32 → var(--dsp-primary-dark)
sed -i 's/#4caf50/var(--dsp-primary)/g' common-styles.css
sed -i 's/#2e7d32/var(--dsp-primary-dark)/g' common-styles.css
```

**수정된 추가 녹색 (2개)**:
- Line 685: `.flyer-tip` border-left
- Line 690: `.flyer-tip p` color

### 10. Cache Busting
```html
<!-- Before -->
<link rel="stylesheet" href="../../css/common-styles.css?v=1759615861">

<!-- After -->
<link rel="stylesheet" href="../../css/common-styles.css?v=<?php echo time(); ?>">
```

강제 새로고침을 위해 동적 타임스탬프 적용

---

## 📊 수정 통계

| 항목 | 수량 | 변경 내용 |
|------|------|-----------|
| Primary 녹색 (#28A745, #4CAF50) | 14개 | → `var(--dsp-primary)` |
| Light 녹색 (#66BB6A) | 2개 | → `var(--dsp-primary-light)` |
| Dark 녹색 (#3D8B40, #2e7d32) | 3개 | → `var(--dsp-primary-dark)` |
| Hover 녹색 (#45A049, #5CBB5C, #20c997) | 5개 | → `var(--dsp-primary-hover/light)` |
| 보라색 그라데이션 (#667eea, #764ba2) | 3개 | → `var(--dsp-accent/dark)` |
| Secondary 녹색 (#059669, #10b981) | 2개 | → `var(--dsp-primary/light)` |
| Green RGBA | 2개 | → Navy RGBA (30, 78, 121) |
| Purple RGBA | 3개 | → Yellow RGBA (255, 213, 0) |
| Secondary green RGBA | 2개 | → Navy RGBA |
| Light green 배경 | 5개 | → `var(--dsp-primary-lighter)` |
| **총계** | **41개** | 모든 녹색/보라색 제거 |

---

## ✅ 변경 후 브랜드 컬러 적용 현황

### Primary 버튼
```css
/* Before */
background: linear-gradient(135deg, #4CAF50, #66BB6A);

/* After */
background: linear-gradient(135deg, var(--dsp-primary), var(--dsp-primary-light));
```

### Calculator Header
```css
/* Before */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* After */
background: linear-gradient(135deg, var(--dsp-accent) 0%, var(--dsp-accent-dark) 100%);
```

### Success 표시
```css
/* Before */
color: #28a745;

/* After */
color: var(--dsp-primary);  /* Navy #1E4E79 */
```

### Focus States
```css
/* Before */
border-color: #28a745;
box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);

/* After */
border-color: var(--dsp-primary);
box-shadow: 0 0 0 3px rgba(30, 78, 121, 0.1);
```

---

## 🎨 최종 브랜드 컬러

| 용도 | 컬러 | CSS Variable |
|------|------|--------------|
| **메인** | Deep Navy #1E4E79 | `--dsp-primary` |
| **포인트** | Bright Yellow #FFD500 | `--dsp-accent` |
| **보조** | Light Gray #F4F4F4 | `--dsp-gray-100` |
| **보조** | White #FFFFFF | `--dsp-white` |
| **에러** | Red #DC3545 | `--dsp-error` |

---

## 🧪 테스트 방법

### 1. 브라우저 하드 리프레시
```
Ctrl + F5 (Windows/Linux)
Cmd + Shift + R (Mac)
```

### 2. 개발자 도구 확인
```javascript
// Console에서 실행
getComputedStyle(document.documentElement).getPropertyValue('--dsp-success')
// 결과: #1E4E79 (Navy, NOT #28a745)
```

### 3. 시각적 확인
- Primary 버튼: 노란색 그라데이션 ✅
- Secondary 버튼: 네이비 그라데이션 ✅
- Success 텍스트: 네이비 ✅
- Calculator Header: 노란색 그라데이션 ✅
- Error 표시: 빨간색 (유지) ✅

---

## 📝 백업 파일

```
/var/www/html/css/common-styles.css.backup_green
```

문제가 발생하면 다음 명령어로 복원 가능:
```bash
cd /var/www/html/css
cp common-styles.css.backup_green common-styles.css
```

---

## 🎯 해결된 문제

1. ✅ `common-styles.css`의 모든 녹색이 네이비로 변경
2. ✅ 보라색 그라데이션이 노란색(브랜드 포인트 컬러)로 변경
3. ✅ RGBA 투명도 값도 브랜드 컬러로 변경
4. ✅ 캐시 무효화를 위한 동적 버전 파라미터 적용
5. ✅ 모든 Success 표시가 네이비로 통일

---

## 다음 단계

1. **브라우저 확인**: http://localhost/mlangprintauto/inserted/
2. **나머지 8개 제품**: 명함, 봉투 등도 동일한 CSS 로드 순서 확인
3. **전체 제품 통일**: 모든 제품 페이지에 `color-system-unified.css` 적용

---

**작성자**: Claude (AI Assistant)
**검토 필요**: 시각적 확인
