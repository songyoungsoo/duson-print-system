# 제품 통합 시스템 - 빠른 참조 (Quick Reference)

**최종 업데이트**: 2025-09-30
**상세 문서**: [PRODUCT_UNIFICATION_ROADMAP.md](PRODUCT_UNIFICATION_ROADMAP.md)

---

## 🎯 통합 현황 (한눈에 보기)

| 제품명 | 상태 | 우선순위 | 복잡도 | 비고 |
|--------|------|----------|--------|------|
| namecard (명함) | ✅ 완료 | - | - | 기준 구현체 |
| ncrflambeau (양식지) | ✅ 완료 | - | - | 최근 통합 |
| inserted (전단지) | ✅ 완료 | - | - | 통합 패턴 사용 |
| **cadarok** (카다록) | ❌ 필요 | 🔴 P1 | 쉬움 | 표준 4옵션 |
| **envelope** (봉투) | ❌ 필요 | 🔴 P1 | 쉬움 | 표준 4옵션 |
| **littleprint** (포스터) | ❌ 필요 | 🔴 P1 | 쉬움 | 간단한 구조 |
| **merchandisebond** (상품권) | ❌ 필요 | 🟡 P2 | 중간 | 부분 구현됨 |
| **msticker** (자석스티커) | ❌ 필요 | 🟡 P2 | 중간 | 부분 구현됨 |
| **sticker_new** (스티커) | ❌ 필요 | 🟢 P3 | 복잡 | 커스텀 필드 많음 |

**진행률**: 33.3% (3/9 완료)

---

## 🚀 Quick Start - 제품 통합 3단계

### 1️⃣ 준비 (5분)
```bash
# 백업 생성
cp mlangprintauto/[product]/index.php mlangprintauto/[product]/index.php.backup
cp mlangprintauto/[product]/add_to_basket.php mlangprintauto/[product]/add_to_basket.php.backup
```

### 2️⃣ 통합 (15분)
- **index.php**: `upload_modal.php` include + `handleModalBasketAdd` 함수 추가
- **add_to_basket.php**: 표준 패턴 적용

### 3️⃣ 테스트 (10분)
- [ ] 모달 열림
- [ ] 파일 업로드
- [ ] 장바구니 담기
- [ ] cart.php 이동

---

## 📋 핵심 패턴 (복사해서 사용)

### Pattern 1: index.php에 추가
```php
<!-- 파일 업로드 모달 (공통 컴포넌트) -->
<?php include "../../includes/upload_modal.php"; ?>
<script src="../../includes/upload_modal.js"></script>

<script>
// [제품명] 전용 장바구니 추가 함수
window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    if (!window.currentPriceData) {
        if (onError) onError('먼저 가격을 계산해주세요.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_to_basket');
    formData.append('product_type', '[product_type]');

    // 제품별 필드 (여기에 필드 추가)
    formData.append('MY_type', document.getElementById('MY_type').value);
    formData.append('MY_amount', document.getElementById('MY_amount').value);

    // 가격 및 파일
    formData.append('calculated_price', Math.round(window.currentPriceData.total_price));
    if (uploadedFiles && uploadedFiles.length > 0) {
        uploadedFiles.forEach((file, index) => {
            formData.append('uploaded_files[' + index + ']', file);
        });
    }

    fetch('add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (onSuccess) onSuccess(data);
        } else {
            if (onError) onError(data.message);
        }
    });
};
</script>
```

### Pattern 2: add_to_basket.php 표준 구조
```php
<?php
session_start();
include "../../db.php";
include "../../includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_basket') {
    try {
        // 1. 데이터 수집
        $product_type = $_POST['product_type'] ?? '';
        $session_id = session_id();

        // 2. 제품별 필드
        $MY_type = $_POST['MY_type'] ?? '';
        $MY_amount = $_POST['MY_amount'] ?? '';

        // 3. 가격
        $calculated_price = $_POST['calculated_price'] ?? 0;

        // 4. 파일 처리
        // (파일 업로드 로직)

        // 5. DB 저장
        // (INSERT 쿼리)

        echo json_encode(['success' => true, 'message' => '장바구니에 담았습니다']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>
```

---

## 🎯 제품별 필수 필드

### 표준 4옵션 제품 (cadarok, envelope)
```javascript
formData.append('MY_type', ...);      // 종류
formData.append('Section', ...);      // 재질
formData.append('POtype', ...);       // 인쇄면
formData.append('MY_amount', ...);    // 수량
formData.append('ordertype', ...);    // 주문타입
```

### 5옵션 제품 (littleprint)
```javascript
formData.append('MY_type', ...);      // 종류
formData.append('Section', ...);      // 재질
formData.append('PN_type', ...);      // 규격
formData.append('POtype', ...);       // 인쇄면
formData.append('MY_amount', ...);    // 수량
formData.append('ordertype', ...);    // 주문타입
```

### 커스텀 제품 (sticker_new)
```javascript
formData.append('jong', ...);         // 종이종류
formData.append('garo', ...);         // 가로
formData.append('sero', ...);         // 세로
formData.append('mesu', ...);         // 수량
formData.append('uhyung', ...);       // 옵션
formData.append('domusong', ...);     // 모양
```

---

## 🔧 SuperClaude 명령어

### 한 제품 통합
```bash
/sc:task "cadarok 제품을 명함 통합 패턴에 맞게 수정"
```

### 여러 제품 병렬 통합
```bash
/sc:spawn "cadarok, envelope, littleprint 통합" --parallel
```

### 테스트
```bash
/sc:test mlangprintauto/cadarok/ --focus integration
```

---

## ⚡ 빠른 체크리스트

### 통합 전 확인사항
- [ ] 백업 파일 생성됨
- [ ] 기존 가격 계산 로직 확인
- [ ] 제품별 필수 필드 목록 작성

### 통합 작업
- [ ] `upload_modal.php` include 추가
- [ ] `handleModalBasketAdd` 함수 작성
- [ ] 제품별 필드 모두 포함
- [ ] 기존 중복 함수 제거

### 통합 후 테스트
- [ ] 모달 열림 확인
- [ ] 파일 업로드 작동
- [ ] 장바구니 담기 성공
- [ ] cart.php 리다이렉트 확인
- [ ] 콘솔 에러 없음

---

## 📊 통합 효과

| 항목 | 통합 전 | 통합 후 |
|------|---------|---------|
| 코드 중복 | 9개 제품 × 독립 구현 | 1개 공통 컴포넌트 |
| 유지보수 | 9곳 수정 필요 | 1곳만 수정 |
| 사용자 경험 | 제품마다 다름 | 완전 일관성 |
| 버그 수정 시간 | 9배 시간 소요 | 1배 시간 |

---

## 🎓 참고 문서

- **상세 가이드**: [PRODUCT_UNIFICATION_ROADMAP.md](PRODUCT_UNIFICATION_ROADMAP.md)
- **아키텍처**: [../02_ARCHITECTURE/WORKFLOW_PATTERNS.md](../02_ARCHITECTURE/WORKFLOW_PATTERNS.md)

### 기준 구현체 확인
- 명함: `mlangprintauto/namecard/index.php` (Lines 790-850)
- 양식지: `mlangprintauto/ncrflambeau/index.php` (Lines 266-320)
- 공통 모달: `includes/upload_modal.php` + `includes/upload_modal.js`

---

**💡 TIP**: Priority 1 제품(cadarok, envelope, littleprint)부터 시작하면 30분 내 통합 가능합니다!