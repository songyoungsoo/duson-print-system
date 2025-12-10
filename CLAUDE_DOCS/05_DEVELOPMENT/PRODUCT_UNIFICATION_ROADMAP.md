# 제품 통합 로드맵 (Product Unification Roadmap)

**생성일**: 2025-09-30
**목적**: 명함(namecard)/양식지(ncrflambeau) 통합 패턴을 나머지 6개 제품에 적용하여 업로드 모달 및 장바구니 시스템 통합

---

## 🎯 통합 현황 개요

### ✅ 통합 완료 제품 (3/9)
1. **namecard** (명함) - 기준 구현체
2. **ncrflambeau** (양식지) - 최근 통합 완료
3. **inserted** (전단지) - 통합 패턴 사용

### ❌ 통합 필요 제품 (6/9)
4. **sticker_new** (스티커)
5. **cadarok** (카다록)
6. **merchandisebond** (상품권/쿠폰)
7. **envelope** (봉투)
8. **littleprint** (포스터/리틀프린트)
9. **msticker** (자석스티커)

---

## 📋 통합 패턴 분석

### 통합된 제품의 공통점

#### 1. **파일 구조**
```
mlangprintauto/[product]/
├── index.php                           # 통합 모달 포함
├── add_to_basket.php                   # AJAX 엔드포인트
└── js/[product]-compact.js            # 제품별 JavaScript
```

#### 2. **공통 컴포넌트 사용**
```php
<!-- index.php 내부 -->
<?php include "../../includes/upload_modal.php"; ?>
<script src="../../includes/upload_modal.js"></script>
```

#### 3. **표준화된 JavaScript 패턴**
```javascript
// index.php 내 <script> 블록
window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    const formData = new FormData();
    formData.append('action', 'add_to_basket');
    formData.append('product_type', '[product_name]');

    // 제품별 옵션 수집
    formData.append('MY_type', document.getElementById('MY_type').value);
    formData.append('MY_amount', document.getElementById('MY_amount').value);
    // ... 기타 필드

    // 업로드된 파일 추가
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
    })
    .catch(error => {
        if (onError) onError('네트워크 오류가 발생했습니다.');
    });
};
```

#### 4. **add_to_basket.php 표준 구조**
```php
<?php
session_start();
include "../../db.php";
include "../../includes/functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_basket') {

    // 1. 폼 데이터 수집
    $product_type = $_POST['product_type'] ?? '';
    $MY_type = $_POST['MY_type'] ?? '';
    $MY_amount = $_POST['MY_amount'] ?? '';
    // ... 기타 필드

    // 2. 업로드된 파일 처리
    $uploaded_files = [];
    if (isset($_FILES['uploaded_files'])) {
        // 파일 저장 로직
    }

    // 3. shop_temp 테이블에 저장
    $insert_query = "INSERT INTO mlangprintauto_shop_temp (...)";

    // 4. JSON 응답
    echo json_encode(['success' => true, 'message' => '장바구니에 담았습니다']);
}
?>
```

---

## 🔍 비통합 제품 분석

### 1. sticker_new (스티커)

**현재 상태**:
- ❌ `upload_modal.php` 미사용
- ❌ `handleModalBasketAdd` 패턴 미구현
- ✅ `add_to_basket.php` 존재 (수정 필요)

**필요 작업**:
1. `index.php`에 `upload_modal.php` include 추가
2. `handleModalBasketAdd` 함수 구현 (스티커 전용 필드 포함)
3. `add_to_basket.php`를 통합 패턴에 맞게 수정

**제품별 특수 필드**:
```javascript
formData.append('jong', document.getElementById('jong').value);    // 종이 종류
formData.append('garo', document.getElementById('garo').value);    // 가로 사이즈
formData.append('sero', document.getElementById('sero').value);    // 세로 사이즈
formData.append('mesu', document.getElementById('mesu').value);    // 수량
formData.append('uhyung', document.getElementById('uhyung').value); // 옵션
formData.append('domusong', document.getElementById('domusong').value); // 모양
```

---

### 2. cadarok (카다록/리플렛)

**현재 상태**:
- ❌ `upload_modal.php` 미사용
- ❌ `handleModalBasketAdd` 패턴 미구현
- ✅ `add_to_basket.php` 존재

**필요 작업**:
1. `index.php`에 통합 모달 추가
2. `handleModalBasketAdd` 함수 구현
3. `add_to_basket.php` 표준화

**제품별 특수 필드**:
```javascript
formData.append('MY_type', document.getElementById('MY_type').value);  // 카다록 종류
formData.append('Section', document.getElementById('Section').value);  // 재질
formData.append('POtype', document.getElementById('POtype').value);    // 인쇄면
formData.append('MY_amount', document.getElementById('MY_amount').value); // 수량
formData.append('ordertype', document.getElementById('ordertype').value); // 주문 타입
```

---

### 3. merchandisebond (상품권/쿠폰)

**현재 상태**:
- ⚠️ `upload_modal.php` include 있음 (라인 추적 필요)
- ⚠️ `handleModalBasketAdd` 존재 여부 확인 필요
- ✅ `add_to_basket.php` 존재

**필요 작업**:
1. 기존 모달 구현 확인
2. 표준 패턴과 비교하여 수정
3. 콜백 패턴 적용

**제품별 특수 필드**:
```javascript
formData.append('MY_type', document.getElementById('MY_type').value);
formData.append('Section', document.getElementById('Section').value);
formData.append('POtype', document.getElementById('POtype').value);
formData.append('MY_amount', document.getElementById('MY_amount').value);
formData.append('ordertype', document.getElementById('ordertype').value);
```

---

### 4. envelope (봉투)

**현재 상태**:
- ❌ `upload_modal.php` 미사용
- ❌ `handleModalBasketAdd` 패턴 미구현
- ✅ `add_to_basket.php` 존재

**필요 작업**:
1. 통합 모달 시스템 추가
2. `handleModalBasketAdd` 구현
3. `add_to_basket.php` 표준화

**제품별 특수 필드**:
```javascript
formData.append('MY_type', document.getElementById('MY_type').value);   // 봉투 종류
formData.append('Section', document.getElementById('Section').value);   // 재질
formData.append('POtype', document.getElementById('POtype').value);     // 인쇄면
formData.append('MY_amount', document.getElementById('MY_amount').value); // 수량
formData.append('ordertype', document.getElementById('ordertype').value); // 주문 타입
```

---

### 5. littleprint (포스터/리틀프린트)

**현재 상태**:
- ❌ `upload_modal.php` 미사용
- ❌ `handleModalBasketAdd` 패턴 미구현
- ✅ `add_to_basket.php` 존재 (매우 간단한 구조)

**필요 작업**:
1. 통합 모달 추가
2. `handleModalBasketAdd` 구현
3. `add_to_basket.php` 확장 (현재 3.9KB로 다른 제품보다 작음)

**제품별 특수 필드**:
```javascript
formData.append('MY_type', document.getElementById('MY_type').value);   // 포스터 종류
formData.append('Section', document.getElementById('Section').value);   // 재질
formData.append('PN_type', document.getElementById('PN_type').value);   // 규격
formData.append('POtype', document.getElementById('POtype').value);     // 인쇄면
formData.append('MY_amount', document.getElementById('MY_amount').value); // 수량
formData.append('ordertype', document.getElementById('ordertype').value); // 주문 타입
```

---

### 6. msticker (자석스티커)

**현재 상태**:
- ⚠️ `upload_modal.php` include 있음
- ⚠️ `handleModalBasketAdd` 존재 확인 필요
- ✅ `add_to_basket.php` 존재 (3.5KB - 비교적 간단)

**필요 작업**:
1. 기존 모달 구현 점검
2. 표준 패턴 적용
3. `add_to_basket.php` 보강

**제품별 특수 필드**:
```javascript
formData.append('MY_type', document.getElementById('MY_type').value);   // 자석스티커 종류
formData.append('Section', document.getElementById('Section').value);   // 규격
formData.append('POtype', document.getElementById('POtype').value);     // 인쇄면
formData.append('MY_amount', document.getElementById('MY_amount').value); // 수량
formData.append('ordertype', document.getElementById('ordertype').value); // 주문 타입
```

---

## 🛠️ 통합 작업 우선순위

### Priority 1 (즉시 작업 추천) - 구조가 명확하고 통합이 쉬운 제품
1. **cadarok** - 구조가 명함/양식지와 매우 유사
2. **envelope** - 표준 4옵션 구조
3. **littleprint** - add_to_basket.php가 간단하여 수정 용이

### Priority 2 (중간 난이도) - 부분적으로 구현되어 있는 제품
4. **merchandisebond** - 모달 포함 확인 후 콜백 패턴만 적용
5. **msticker** - 기존 구현 확인 후 표준화

### Priority 3 (복잡도 높음) - 특수 필드가 많은 제품
6. **sticker_new** - 가로/세로/모양 등 커스텀 필드 많음

---

## 📝 단계별 통합 가이드

### Step 1: 준비 작업
```bash
# 1. 백업 생성
cp mlangprintauto/[product]/index.php mlangprintauto/[product]/index.php.backup
cp mlangprintauto/[product]/add_to_basket.php mlangprintauto/[product]/add_to_basket.php.backup

# 2. 공통 컴포넌트 확인
ls -la includes/upload_modal.php
ls -la includes/upload_modal.js
```

### Step 2: index.php 수정

#### 2.1 upload_modal 포함 추가
```php
<!-- 파일 업로드 모달 (공통 컴포넌트) -->
<?php include "../../includes/upload_modal.php"; ?>
<script src="../../includes/upload_modal.js"></script>
```

#### 2.2 handleModalBasketAdd 함수 구현
```javascript
<script>
// [제품명] 전용 장바구니 추가 함수
window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    console.log('[제품명] 장바구니 추가 시작');

    // 가격 계산 확인
    if (!window.currentPriceData) {
        console.error('가격 계산이 필요합니다');
        if (onError) onError('먼저 가격을 계산해주세요.');
        return;
    }

    const formData = new FormData();
    formData.append('action', 'add_to_basket');
    formData.append('product_type', '[product_type]');

    // 제품별 필드 수집 (여기에 각 제품의 특수 필드 추가)
    formData.append('MY_type', document.getElementById('MY_type').value);
    formData.append('MY_amount', document.getElementById('MY_amount').value);
    // ... 기타 필드

    // 가격 정보
    formData.append('calculated_price', Math.round(window.currentPriceData.total_price));
    formData.append('calculated_vat_price', Math.round(window.currentPriceData.vat_price));

    // 작업 메모
    const workMemo = document.getElementById('modalWorkMemo');
    if (workMemo) formData.append('work_memo', workMemo.value);

    // 업로드 방식
    formData.append('upload_method', window.selectedUploadMethod || 'upload');

    // 업로드된 파일
    if (uploadedFiles && uploadedFiles.length > 0) {
        uploadedFiles.forEach((file, index) => {
            formData.append('uploaded_files[' + index + ']', file);
        });
    }

    // AJAX 요청
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
    })
    .catch(error => {
        console.error('장바구니 추가 오류:', error);
        if (onError) onError('네트워크 오류가 발생했습니다.');
    });
};
</script>
```

#### 2.3 기존 중복 함수 제거
```javascript
// 다음 함수들이 product-compact.js에 있다면 제거:
// - window.addToBasketFromModal
// - window.openUploadModal
// - window.closeUploadModal
// (upload_modal.js가 제공하므로 불필요)
```

### Step 3: add_to_basket.php 표준화

```php
<?php
session_start();
include "../../db.php";
include "../../includes/functions.php";

// POST 요청 및 액션 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_basket') {

    try {
        // 1. 기본 데이터 수집
        $product_type = $_POST['product_type'] ?? '';
        $session_id = session_id();

        // 2. 제품별 옵션 수집
        $MY_type = $_POST['MY_type'] ?? '';
        $MY_amount = $_POST['MY_amount'] ?? '';
        // ... 기타 필드

        // 3. 가격 정보
        $calculated_price = $_POST['calculated_price'] ?? 0;
        $calculated_vat_price = $_POST['calculated_vat_price'] ?? 0;

        // 4. 작업 메모
        $work_memo = $_POST['work_memo'] ?? '';

        // 5. 업로드 방식
        $upload_method = $_POST['upload_method'] ?? 'upload';

        // 6. 파일 업로드 처리
        $upload_folder = '';
        if (isset($_FILES['uploaded_files'])) {
            $upload_folder = '../uploads/' . $session_id . '/';
            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }

            foreach ($_FILES['uploaded_files']['tmp_name'] as $index => $tmp_name) {
                if (!empty($tmp_name)) {
                    $filename = basename($_FILES['uploaded_files']['name'][$index]);
                    move_uploaded_file($tmp_name, $upload_folder . $filename);
                }
            }
        }

        // 7. shop_temp 테이블에 저장
        $insert_query = "INSERT INTO mlangprintauto_shop_temp (
            session_id,
            product_type,
            MY_type,
            MY_amount,
            calculated_price,
            calculated_vat_price,
            work_memo,
            upload_method,
            upload_folder,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($db, $insert_query);
        mysqli_stmt_bind_param($stmt, 'ssssiisss',
            $session_id,
            $product_type,
            $MY_type,
            $MY_amount,
            $calculated_price,
            $calculated_vat_price,
            $work_memo,
            $upload_method,
            $upload_folder
        );

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode([
                'success' => true,
                'message' => '장바구니에 담았습니다',
                'cart_count' => 1 // 실제로는 세션의 총 개수 반환
            ]);
        } else {
            throw new Exception('데이터베이스 저장 실패');
        }

        mysqli_stmt_close($stmt);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }

} else {
    echo json_encode([
        'success' => false,
        'message' => '잘못된 요청입니다'
    ]);
}
?>
```

### Step 4: 테스트

#### 4.1 기능 테스트 체크리스트
- [ ] 가격 계산 후 "업로드" 버튼 클릭 시 모달 열림
- [ ] 파일 드래그 앤 드롭 업로드 정상 작동
- [ ] "장바구니에 담기" 버튼 클릭 시 AJAX 요청 발생
- [ ] 성공 시 자동으로 cart.php로 리다이렉트
- [ ] 실패 시 오류 메시지 표시
- [ ] 브라우저 콘솔에 JavaScript 오류 없음

#### 4.2 테스트 URL
```
http://localhost/mlangprintauto/[product]/index.php
```

#### 4.3 디버깅 팁
```javascript
// 브라우저 콘솔에서 확인
console.log('handleModalBasketAdd 존재:', typeof window.handleModalBasketAdd);
console.log('현재 가격 데이터:', window.currentPriceData);
console.log('업로드된 파일:', window.uploadedFiles);
```

---

## 🚀 제품별 통합 체크리스트

### cadarok (카다록)
- [ ] `upload_modal.php` include 추가
- [ ] `handleModalBasketAdd` 함수 구현
- [ ] 제품별 필드: MY_type, Section, POtype, MY_amount, ordertype
- [ ] `add_to_basket.php` 표준화
- [ ] 테스트: 파일 업로드 → 장바구니 담기 → cart.php 이동

### envelope (봉투)
- [ ] `upload_modal.php` include 추가
- [ ] `handleModalBasketAdd` 함수 구현
- [ ] 제품별 필드: MY_type, Section, POtype, MY_amount, ordertype
- [ ] `add_to_basket.php` 표준화
- [ ] 테스트 완료

### littleprint (포스터)
- [ ] `upload_modal.php` include 추가
- [ ] `handleModalBasketAdd` 함수 구현
- [ ] 제품별 필드: MY_type, Section, PN_type, POtype, MY_amount, ordertype
- [ ] `add_to_basket.php` 확장 및 표준화
- [ ] 테스트 완료

### merchandisebond (상품권)
- [ ] 기존 upload_modal 구현 확인
- [ ] `handleModalBasketAdd` 콜백 패턴 적용
- [ ] 제품별 필드: MY_type, Section, POtype, MY_amount, ordertype
- [ ] `add_to_basket.php` 검토 및 수정
- [ ] 테스트 완료

### msticker (자석스티커)
- [ ] 기존 upload_modal 구현 확인
- [ ] `handleModalBasketAdd` 콜백 패턴 적용
- [ ] 제품별 필드: MY_type, Section, POtype, MY_amount, ordertype
- [ ] `add_to_basket.php` 보강
- [ ] 테스트 완료

### sticker_new (스티커)
- [ ] `upload_modal.php` include 추가
- [ ] `handleModalBasketAdd` 함수 구현
- [ ] 제품별 필드: jong, garo, sero, mesu, uhyung, domusong
- [ ] `add_to_basket.php` 표준화 (커스텀 필드 처리)
- [ ] 테스트 완료

---

## 📊 통합 완료 후 기대 효과

### 1. 코드 유지보수성 향상
- 중복 코드 제거 (각 제품별 모달 구현 → 공통 컴포넌트)
- 버그 수정 시 한 곳만 수정 (upload_modal.js)
- 새로운 기능 추가 시 모든 제품에 자동 적용

### 2. 사용자 경험 일관성
- 모든 제품에서 동일한 업로드 플로우
- 동일한 에러 처리 및 피드백
- 통일된 UI/UX

### 3. 개발 효율성
- 새 제품 추가 시 표준 템플릿 사용 가능
- 테스트 케이스 공유
- 문서화 간소화

---

## 🔧 SuperClaude 활용 가이드

### 추천 SuperClaude 명령어

#### 1. 체계적 통합 작업
```bash
/sc:task "cadarok 제품을 명함 통합 패턴에 맞게 수정"
```

#### 2. 품질 검증
```bash
/sc:test mlangprintauto/cadarok/ --focus integration
```

#### 3. 코드 분석
```bash
/sc:analyze mlangprintauto/cadarok/ --focus architecture
```

#### 4. 리팩토링
```bash
/sc:refactor mlangprintauto/cadarok/add_to_basket.php --pattern standardize
```

### 병렬 처리 전략

6개 제품을 3개씩 2그룹으로 나누어 병렬 처리:

**Group 1** (표준 구조):
```bash
/sc:spawn "cadarok, envelope, littleprint 3개 제품을 병렬로 통합 패턴 적용" --parallel
```

**Group 2** (부분 구현):
```bash
/sc:spawn "merchandisebond, msticker, sticker_new 3개 제품 통합" --parallel
```

---

## ⚠️ 주의사항

### 1. 백업 필수
모든 수정 전에 반드시 백업:
```bash
cp mlangprintauto/[product]/index.php mlangprintauto/[product]/index.php.backup
cp mlangprintauto/[product]/add_to_basket.php mlangprintauto/[product]/add_to_basket.php.backup
```

### 2. 제품별 커스터마이징 보존
- 각 제품의 고유한 필드는 반드시 유지
- 가격 계산 로직은 절대 수정하지 않음
- 기존 디자인/CSS는 변경하지 않음

### 3. 단계적 배포
- 한 제품씩 통합 후 테스트
- 로컬 환경에서 완전히 검증 후 운영 반영
- 사용자 피드백 수집 후 다음 제품 진행

### 4. 롤백 계획
문제 발생 시 즉시 백업 파일로 복원:
```bash
cp mlangprintauto/[product]/index.php.backup mlangprintauto/[product]/index.php
cp mlangprintauto/[product]/add_to_basket.php.backup mlangprintauto/[product]/add_to_basket.php
```

---

## 📈 진행 상황 추적

### 현재 진행률: 33.3% (3/9 완료)

```
✅✅✅⬜⬜⬜⬜⬜⬜  33.3%
namecard, ncrflambeau, inserted 완료
```

### 목표: 100% (9/9 통합 완료)

```
✅✅✅✅✅✅✅✅✅  100%
모든 제품 통합 완료
```

---

## 🎓 참고 자료

### 관련 문서
- [CLAUDE_DOCS/02_ARCHITECTURE/WORKFLOW_PATTERNS.md](../02_ARCHITECTURE/WORKFLOW_PATTERNS.md)
- [CLAUDE_DOCS/03_PRODUCTS/PRODUCT_MANAGER_SYSTEM_V1_FINAL.md](../03_PRODUCTS/PRODUCT_MANAGER_SYSTEM_V1_FINAL.md)

### 기준 구현체
- `mlangprintauto/namecard/index.php` (Lines 790-850)
- `mlangprintauto/ncrflambeau/index.php` (Lines 266-320)
- `includes/upload_modal.php`
- `includes/upload_modal.js`

### 테스트 페이지
- 명함: http://localhost/mlangprintauto/namecard/index.php
- 양식지: http://localhost/mlangprintauto/ncrflambeau/index.php
- 전단지: http://localhost/mlangprintauto/inserted/index.php

---

**작성자**: Claude Code AI Assistant
**최종 업데이트**: 2025-09-30
**버전**: 1.0