# 전단지(Leaflet) 컬러 매핑 테이블

**작성일**: 2025-10-11
**대상 제품**: 전단지 (inserted/leaflet)
**Phase**: 2

---

## 📊 현재 사용 중인 컬러 분석

### 전단지 CSS 파일 목록
1. `/var/www/html/mlangprintauto/inserted/styles.css`
2. `/var/www/html/mlangprintauto/inserted/css/leaflet-compact.css`
3. `/var/www/html/css/btn-primary.css` (전단지 스타일 사용)

### 컬러 사용 빈도 Top 20
```
6회  #28A745  - 성공 녹색
4회  #E9ECEF  - 밝은 회색
4회  #6C757D  - 중간 회색
4회  #336699  - 파란색
3회  #FFFFFF  - 흰색
3회  #F8F9FA  - 매우 밝은 회색
3회  #4CAF50  - 전단지 브랜드 녹색
2회  #FF9800  - 오렌지
2회  #FF5722  - 딥오렌지
2회  #E91E63  - 핑크
2회  #9C27B0  - 진보라
2회  #667EEA  - 보라
```

---

## 🎯 컬러 매핑 테이블

| 현재 Hardcoded 값 | 용도 | 통합 변수 | 비고 |
|------------------|------|----------|------|
| `#4CAF50` | 전단지 브랜드 컬러 | `var(--dsp-product-leaflet)` | 메인 브랜드 |
| `#2E7D32` | 전단지 Dark | `var(--dsp-product-leaflet-dark)` | hover 상태 |
| `#1B5E20` | 전단지 Darker | `var(--dsp-product-leaflet-darker)` | active 상태 |
| `#81C784` | 전단지 Light | `var(--dsp-product-leaflet-light)` | 배경 |
| `rgba(76, 175, 80, 0.25)` | 전단지 그림자 | `var(--dsp-shadow-leaflet)` | box-shadow |
| `rgba(76, 175, 80, 0.35)` | 전단지 강한 그림자 | `var(--dsp-shadow-leaflet)` | hover 그림자 |
| `#28A745` | 성공 컬러 | `var(--dsp-success)` | 시맨틱 |
| `#FFFFFF` | 흰색 | `var(--dsp-white)` | 배경/텍스트 |
| `#F8F9FA` | 밝은 회색 | `var(--dsp-gray-50)` | 배경 |
| `#E9ECEF` | 회색 | `var(--dsp-gray-200)` | 배경 |
| `#6C757D` | 중간 회색 | `var(--dsp-gray-600)` | 텍스트 |
| `#495057` | 어두운 회색 | `var(--dsp-gray-700)` | 텍스트 |
| `#336699` | 파란색 | `var(--dsp-primary)` 또는 `var(--dsp-info)` | 링크 |

---

## 📝 변경 예시

### 1. btn-primary.css

**BEFORE**:
```css
.btn-primary {
    background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
    box-shadow: 0 4px 12px rgba(76, 175, 80, 0.25);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
    box-shadow: 0 6px 20px rgba(76, 175, 80, 0.35);
}
```

**AFTER**:
```css
.btn-primary {
    background: linear-gradient(135deg,
        var(--dsp-product-leaflet) 0%,
        var(--dsp-product-leaflet-dark) 100%);
    box-shadow: 0 4px 12px var(--dsp-shadow-leaflet);
}

.btn-primary:hover {
    background: linear-gradient(135deg,
        var(--dsp-product-leaflet-dark) 0%,
        var(--dsp-product-leaflet-darker) 100%);
    box-shadow: 0 6px 20px var(--dsp-shadow-leaflet);
}
```

### 2. 배경 컬러

**BEFORE**:
```css
background-color: #F8F9FA;
border: 1px solid #E9ECEF;
color: #6C757D;
```

**AFTER**:
```css
background-color: var(--dsp-gray-50);
border: 1px solid var(--dsp-gray-200);
color: var(--dsp-gray-600);
```

### 3. 성공 상태

**BEFORE**:
```css
.success {
    color: #28A745;
    border-color: #28A745;
}
```

**AFTER**:
```css
.success {
    color: var(--dsp-success);
    border-color: var(--dsp-success);
}
```

---

## 🔄 작업 순서

### 1단계: btn-primary.css
- [ ] Hardcoded 컬러 → 변수 교체
- [ ] 시각 검증
- [ ] 기능 테스트

### 2단계: leaflet-compact.css
- [ ] Hardcoded 컬러 → 변수 교체
- [ ] 시각 검증
- [ ] 기능 테스트

### 3단계: styles.css
- [ ] Hardcoded 컬러 → 변수 교체
- [ ] 시각 검증
- [ ] 기능 테스트

### 4단계: 인라인 스타일 제거
- [ ] index.php 인라인 스타일 확인
- [ ] 필요 시 CSS 클래스로 이동

### 5단계: 최종 검증
- [ ] 전/후 스크린샷 비교
- [ ] 모든 기능 동작 확인
- [ ] 크로스 브라우저 테스트

---

## ⚠️ 주의사항

### 변경하지 않을 컬러
- 제품별 브랜드 컬러 (다른 제품): 해당 제품 마이그레이션 시 변경
- 일시적/테스트 컬러: 확인 후 제거 또는 변수화

### 테스트 필수 항목
- 버튼 hover/active 상태
- 가격 표시 컬러
- 폼 요소 focus 상태
- 갤러리 이미지 테두리

---

*다음 파일: btn-primary.css 마이그레이션*
