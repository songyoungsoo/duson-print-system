# Phase 1: 코드 감사 결과

**날짜**: 2025-11-19
**목적**: 파일 업로드 시스템 현황 파악 및 표준화 계획 수립

---

## 📊 전체 현황

**총 14개 add_to_basket.php 파일 발견**

### 분류 기준
1. **safe_json_response** - 안전한 JSON 응답 (출력 버퍼 정리)
2. **UploadPathHelper** - 통합 업로드 경로 헬퍼
3. **uploaded_files** - JSON 파일 메타데이터 저장

---

## ✅ 완전 표준화 (1개)

### namecard (명함)
- ✅ safe_json_response
- ✅ UploadPathHelper
- ✅ uploaded_files JSON
- 📏 278 라인
- **상태**: 표준 완벽, 다른 제품의 템플릿으로 사용 가능

---

## ⚠️ 부분 표준화 (8개)

### 그룹 A: safe_json_response + 레거시 함수 (5개)
1. **cadarok** (카다록) - 222 라인
2. **envelope** (봉투) - 310 라인
3. **merchandisebond** (상품권) - 257 라인
4. **ncrflambeau** (양식지) - 206 라인
5. **sticker_new** (스티커) - 314 라인

**문제점**:
- `generateUploadPath()` 또는 `generateLegacyUploadPath()` 사용
- UploadPathHelper로 대체 필요

**해결 방법**:
```php
// BEFORE
$upload_path_info = generateLegacyUploadPath('product');

// AFTER
require_once __DIR__ . '/../../includes/UploadPathHelper.php';
$paths = UploadPathHelper::generateUploadPath('product');
```

### 그룹 B: safe_json_response + 업로드 헬퍼 미사용 (2개)
1. **sticker** (스티커 구버전) - 174 라인
2. **inserted** (전단지) - 158 라인 ⚠️ 최근 수정됨

**문제점**:
- 인라인 업로드 로직 사용
- 표준화 필요

### 그룹 C: 레거시 response + UploadPathHelper (2개)
1. **littleprint** (포스터) - 194 라인
2. **msticker** (자석스티커) - 152 라인

**문제점**:
- `error_response()` / `success_response()` 사용 (버퍼 오염 위험)
- safe_json_response로 전환 필요

**해결 방법**:
```php
// BEFORE
error_response('에러 메시지');

// AFTER
require_once __DIR__ . '/../../includes/safe_json_response.php';
safe_json_response(false, null, '에러 메시지');
```

---

## ❌ 표준화 필요 (1개)

### leaflet (리플렛)
- ❌ 레거시 response 함수
- ❌ 업로드 헬퍼 미사용
- ✅ uploaded_files JSON
- 📏 293 라인

**문제점**:
- 전면 재작성 필요
- 전단지(inserted)와 유사한 구조

---

## 🗑️ 삭제 또는 무시 (3개)

### inserted251028 (백업)
- 레거시 백업 파일
- **조치**: 삭제 또는 보관

### poster (미사용?)
- 완전 레거시 코드
- **조치**: littleprint로 통합 또는 삭제

### shop (일반 shop)
- 범용 장바구니?
- **조치**: 용도 확인 후 처리

---

## 📋 표준화 우선순위

### 🔴 High Priority (즉시 수정)
1. **littleprint** - 레거시 response → safe_json_response
2. **msticker** - 레거시 response → safe_json_response
3. **leaflet** - 전면 재작성

### 🟡 Medium Priority (점진적 개선)
4. **cadarok** - UploadPathHelper 적용
5. **envelope** - UploadPathHelper 적용
6. **merchandisebond** - UploadPathHelper 적용
7. **ncrflambeau** - UploadPathHelper 적용
8. **sticker_new** - UploadPathHelper 적용

### 🟢 Low Priority (추후 정리)
9. **sticker** - 업로드 헬퍼 추가
10. **inserted** - 업로드 헬퍼 추가

---

## 🎯 Phase 2 목표

### StandardUploadHandler 클래스 생성
모든 제품이 사용할 통합 업로드 헬퍼:

```php
class StandardUploadHandler {
    public static function processUpload($product, $files) {
        // 1. 경로 생성 (UploadPathHelper)
        // 2. 파일 검증
        // 3. 파일 이동
        // 4. JSON 메타데이터 생성
        // 5. 에러 처리
        return ['success' => true, 'files' => $uploaded_files];
    }
}
```

### 적용 패턴

모든 add_to_basket.php에서 동일한 패턴 사용:

```php
<?php
// 1. 안전한 JSON 응답
require_once __DIR__ . '/../../includes/safe_json_response.php';

// 2. 표준 업로드 핸들러
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

// 3. 세션 및 DB
session_start();
include "../../db.php";

// 4. 파일 업로드 처리
$result = StandardUploadHandler::processUpload('product_name', $_FILES);

if (!$result['success']) {
    safe_json_response(false, null, $result['error']);
}

$uploaded_files_json = json_encode($result['files'], JSON_UNESCAPED_UNICODE);
$img_folder = $result['img_folder'];
$thing_cate = $result['thing_cate'];

// 5. DB 저장
// ... INSERT 로직 ...

safe_json_response(true, ['basket_id' => $basket_id]);
```

---

## 📊 통계

| 항목 | 개수 | 비율 |
|------|------|------|
| 완전 표준화 | 1 | 7% |
| 부분 표준화 | 8 | 57% |
| 표준화 필요 | 1 | 7% |
| 삭제/무시 | 3 | 21% |
| **활성 제품** | **10** | **71%** |

---

## 🚀 Next Steps

1. ✅ Git 커밋: Phase 1 감사 결과 저장
2. → Phase 2: StandardUploadHandler 클래스 생성
3. → Phase 2: 10개 제품 파일 표준화 적용
4. → Phase 2: 테스트 및 검증

---

**작성**: Phase 1 감사 완료
**다음**: Phase 2 표준화 시작
