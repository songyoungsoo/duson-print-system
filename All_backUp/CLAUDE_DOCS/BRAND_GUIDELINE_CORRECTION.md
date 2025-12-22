# 브랜드 가이드라인 수정 보고서

**작성일**: 2025-10-11
**작업 범위**: 전단지(Leaflet/Inserted) 페이지
**이슈**: 제품별 컬러 사용 → 브랜드 컬러 전환

---

## 🚨 Critical Issue: 브랜드 가이드라인 위반

### 문제 인식
Phase 2 작업 중 전단지 페이지에 녹색 제품 컬러를 적용했으나, 사용자로부터 다음과 같은 명확한 지침을 받음:

> **"제품별로 다른색 안되고 메인 컬러 Deep Navy #1E4E79, 포인트 컬러 Bright Yellow #FFD500, 보조 컬러 Light Gray #F4F4F4, White"**

### 브랜드 가이드라인 (확정)

**✅ 허용된 컬러 (ONLY 3가지)**

| 컬러 | HEX | CSS Variable | 용도 |
|------|-----|--------------|------|
| **Deep Navy** | #1E4E79 | `--dsp-primary` | 메인 컬러 (로고, 헤더, Secondary 버튼) |
| **Bright Yellow** | #FFD500 | `--dsp-accent` | 포인트 컬러 (Primary 버튼, 강조) |
| **Light Gray** | #F4F4F4 | `--dsp-gray-100` | 보조 컬러 (배경) |
| **White** | #FFFFFF | `--dsp-white` | 보조 컬러 (콘텐츠 배경) |

**❌ 금지된 컬러**
- 제품별 컬러 (녹색, 보라색, 오렌지, 핑크 등)
- 제품별 그림자 컬러
- Semantic 컬러 (green success, red error) - 향후 확인 필요

---

## 🔧 수정 작업 내역

### 1. `/var/www/html/css/btn-primary.css`

**변경 전 (Green - Product Color)**
```css
.btn-primary {
    background: linear-gradient(135deg,
        var(--dsp-product-leaflet) 0%,      /* ❌ Green #4CAF50 */
        var(--dsp-product-leaflet-dark) 100%);
    color: var(--dsp-white);
    box-shadow: 0 6px 20px var(--dsp-shadow-leaflet);
}
```

**변경 후 (Yellow - Brand Point Color)**
```css
.btn-primary {
    background: linear-gradient(135deg,
        var(--dsp-accent) 0%,               /* ✅ Yellow #FFD500 */
        var(--dsp-accent-dark) 100%);
    color: var(--dsp-gray-900);             /* Dark text for contrast */
    box-shadow: 0 4px 12px rgba(255, 213, 0, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(135deg,
        var(--dsp-accent-dark) 0%,
        var(--dsp-accent) 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 213, 0, 0.4);
}
```

### 2. `/var/www/html/mlangprintauto/inserted/css/leaflet-compact.css`

**수정된 스타일 (9개)**

#### A. Focus State (Line 190-191)
```css
/* BEFORE */
border-color: var(--dsp-success);           /* ❌ Green */
box-shadow: 0 0 0 3px var(--dsp-success-light);

/* AFTER */
border-color: var(--dsp-accent);            /* ✅ Yellow */
box-shadow: 0 0 0 3px var(--dsp-accent-lighter);
```

#### B. Price Amount (Line 229)
```css
/* BEFORE */
color: var(--dsp-success);                  /* ❌ Green */

/* AFTER */
color: var(--dsp-primary);                  /* ✅ Navy */
```

#### C. Selected Options Section (Line 242-245)
```css
/* BEFORE */
background: linear-gradient(135deg,
    var(--dsp-success-lighter) 0%,          /* ❌ Light green */
    var(--dsp-success-light) 100%);
border: 2px solid var(--dsp-success);       /* ❌ Green */

/* AFTER */
background: linear-gradient(135deg,
    var(--dsp-accent-lighter) 0%,           /* ✅ Light yellow */
    var(--dsp-gray-100) 100%);
border: 2px solid var(--dsp-accent);        /* ✅ Yellow */
```

#### D. Selected Options Heading (Line 264)
```css
/* BEFORE */
color: #28a745;                             /* ❌ Hardcoded green */

/* AFTER */
color: var(--dsp-primary);                  /* ✅ Navy */
```

#### E. Option Value Text (Line 300)
```css
/* BEFORE */
color: #28a745;                             /* ❌ Hardcoded green */

/* AFTER */
color: var(--dsp-primary);                  /* ✅ Navy */
```

#### F. Primary Button (Line 327-332)
```css
/* BEFORE */
background: linear-gradient(135deg,
    var(--dsp-success) 0%,                  /* ❌ Green */
    var(--dsp-success-light) 100%);
color: var(--dsp-white);
box-shadow: 0 6px 20px var(--dsp-shadow-leaflet);

/* AFTER */
background: linear-gradient(135deg,
    var(--dsp-accent) 0%,                   /* ✅ Yellow */
    var(--dsp-accent-dark) 100%);
color: var(--dsp-gray-900);
box-shadow: 0 4px 12px rgba(255, 213, 0, 0.3);
```

#### G. Secondary Button (Line 335-341)
```css
/* BEFORE */
background: linear-gradient(135deg,
    var(--dsp-product-namecard) 0%,         /* ❌ Purple */
    var(--dsp-product-namecard-light) 100%);
color: var(--dsp-white);
box-shadow: 0 6px 20px var(--dsp-shadow-namecard);

/* AFTER */
background: linear-gradient(135deg,
    var(--dsp-primary) 0%,                  /* ✅ Navy */
    var(--dsp-primary-light) 100%);
color: var(--dsp-white);
box-shadow: 0 4px 12px rgba(30, 78, 121, 0.3);
```

#### H. Calculate Button (Line 375-387, 410)
```css
/* BEFORE */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);  /* ❌ Purple gradient */
color: white;
box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
/* hover */
box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);

/* AFTER */
background: linear-gradient(135deg,
    var(--dsp-accent) 0%,                   /* ✅ Yellow */
    var(--dsp-accent-dark) 100%);
color: var(--dsp-gray-900);
box-shadow: 0 4px 12px rgba(255, 213, 0, 0.3);
/* hover */
box-shadow: 0 6px 20px rgba(255, 213, 0, 0.4);
```

#### I. Upload Order Button (Line 552-561)
```css
/* BEFORE */
background: linear-gradient(135deg,
    var(--dsp-product-leaflet),             /* ❌ Green */
    var(--dsp-product-leaflet-light)) !important;
color: var(--dsp-white) !important;
box-shadow: none !important;

/* AFTER */
background: linear-gradient(135deg,
    var(--dsp-primary) 0%,                  /* ✅ Navy */
    var(--dsp-primary-light) 100%) !important;
color: var(--dsp-white) !important;
box-shadow: 0 4px 12px rgba(30, 78, 121, 0.3) !important;
```

### 3. `/var/www/html/css/color-system-unified.css`

**헤더에 브랜드 가이드라인 명시 추가**

```css
/**
 * ⚠️⚠️⚠️ 브랜드 가이드라인 (CRITICAL) ⚠️⚠️⚠️
 *
 * 허용된 브랜드 컬러 (ONLY THESE):
 *   1. 메인 컬러: Deep Navy #1E4E79 (--dsp-primary)
 *   2. 포인트 컬러: Bright Yellow #FFD500 (--dsp-accent)
 *   3. 보조 컬러: Light Gray #F4F4F4, White (--dsp-gray-*)
 *
 * ❌ 금지사항:
 *   - 제품별 컬러 사용 금지 (녹색, 보라색, 오렌지 등)
 *   - 제품별 그림자 컬러 사용 금지
 *   - 아래 제품 컬러 변수들은 레거시 호환성만을 위해 유지
 */
```

---

## 📊 수정 통계

| 파일 | 수정된 스타일 규칙 | 변경된 컬러 속성 | 제거된 제품 컬러 |
|------|-------------------|-----------------|----------------|
| `btn-primary.css` | 2개 (.btn-primary, :hover) | 5개 | Green → Yellow |
| `leaflet-compact.css` | 9개 (focus, price, buttons 등) | 20개 | Green/Purple → Yellow/Navy |
| `color-system-unified.css` | 문서화 업데이트 | - | 가이드라인 명시 |

**총계**: 11개 스타일 규칙, 25개 컬러 속성 수정

---

## 🎨 시각적 변경 사항

### Before (Phase 2 초기 - 잘못된 적용)
- **Primary 버튼**: 녹색 그라데이션 (#4CAF50)
- **Secondary 버튼**: 보라색 그라데이션 (#667EEA)
- **계산 버튼**: 보라색 그라데이션 (#667eea → #764ba2)
- **가격 표시**: 녹색 텍스트 (#28A745)
- **선택 옵션 섹션**: 녹색 테두리/배경
- **업로드 버튼**: 녹색 그라데이션

### After (브랜드 가이드라인 준수)
- **Primary 버튼**: 노란색 그라데이션 (#FFD500)
- **Secondary 버튼**: 네이비 그라데이션 (#1E4E79)
- **계산 버튼**: 노란색 그라데이션 (#FFD500)
- **가격 표시**: 네이비 텍스트 (#1E4E79)
- **선택 옵션 섹션**: 노란색 테두리/배경
- **업로드 버튼**: 네이비 그라데이션 (#1E4E79)

---

## ⚠️ 향후 확인 필요 사항

### 1. Semantic 컬러 사용 여부
현재 다음 semantic 컬러들이 정의되어 있으나 사용 정책 미확정:
- **Success**: #28A745 (녹색) - 성공 메시지, 체크 아이콘
- **Warning**: #FFC107 (노란색) - 경고 메시지
- **Error**: #DC3545 (빨간색) - 에러 메시지

**질문**: Semantic 컬러도 브랜드 컬러로 대체해야 하는가?
- Success → Yellow (#FFD500)?
- Error → Navy (#1E4E79)?
- Warning → Gray?

### 2. 제품 컬러 변수 처리
`color-system-unified.css`에 54개 제품 컬러 변수가 정의되어 있음:
- 현재: "레거시 호환성"으로 유지
- 향후: 완전 제거 또는 deprecation warning 추가 고려

### 3. 나머지 8개 제품 페이지
다음 제품 페이지들도 동일한 수정 필요:
1. 명함 (Namecard)
2. 봉투 (Envelope)
3. 스티커 (Sticker)
4. 자석스티커 (MSticker)
5. 포스터 (Poster/LittlePrint)
6. 카다록 (Cadarok)
7. 상품권 (Merchandise Bond)
8. NCR양식 (NCR Flambeau)

---

## ✅ 완료 상태

- [x] btn-primary.css → Yellow gradient 적용
- [x] leaflet-compact.css → 9개 스타일 규칙 수정
- [x] color-system-unified.css → 브랜드 가이드라인 문서화
- [x] 시각적 일관성 확보 (Yellow Primary + Navy Secondary)
- [x] 컬러 대비 개선 (Yellow 버튼 + Dark gray 텍스트)

---

## 🎯 다음 단계

1. **사용자 확인**: 수정된 전단지 페이지 시각적 검토
2. **Semantic 컬러 정책**: Success/Error 컬러 사용 여부 확정
3. **Phase 2 재개**: 나머지 8개 제품 페이지에 브랜드 컬러 적용
4. **제품 컬러 변수**: 54개 변수 제거 여부 결정
5. **문서 업데이트**: Phase 1/2 완료 보고서 수정

---

**작성자**: Claude (AI Assistant)
**검토 필요**: 브랜드 담당자, UI/UX 디자이너
