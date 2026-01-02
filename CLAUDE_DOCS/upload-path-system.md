# 📁 통합 업로드 경로 시스템

## 🎯 공식 주문 품목 (9개)

두손기획인쇄 온라인 주문 시스템의 공식 품목 목록:

1. ✅ **전단지** (inserted)
2. ✅ **명함** (namecard)
3. ✅ **봉투** (envelope)
4. ✅ **카다록** (cadarok)
5. ✅ **상품권** (merchandisebond)
6. ✅ **스티커** (sticker_new)
7. ✅ **양식지** (ncrflambeau)
8. ✅ **자석스티커** (msticker)
9. ✅ **포스터** (littleprint)

> **참고**: 리플렛(leaflet)은 주문 품목에서 제외됨

## 📂 통일된 업로드 경로 구조

### 기본 원칙

**모든 품목은 동일한 경로 구조를 사용합니다:**

```
장바구니 단계: mlangorder_printauto/upload/temp_{세션ID}_{타임스탬프}/
주문 확정 후: mlangorder_printauto/upload/{주문번호}/
DB 저장값:   ImgFolder = "mlangorder_printauto/upload/{주문번호}/"
```

### 경로 설정 코드 (표준)

모든 품목의 `add_to_basket.php`에서 사용하는 표준 코드:

```php
// ✅ 통일된 업로드 경로
$base_upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/';
$temp_folder_name = 'temp_' . $session_id . '_' . time() . '/';
$upload_folder = $base_upload_dir . $temp_folder_name;

// 폴더 생성
if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0755, true);
}

// DB 저장용 경로
$upload_folder_db = 'mlangorder_printauto/upload/' . $temp_folder_name;
```

## 🔄 주문 확정 프로세스

### 1. 장바구니 단계
- 파일 업로드 → `mlangorder_printauto/upload/temp_xxx/`
- `shop_temp` 테이블에 `ImgFolder` 저장

### 2. 주문 확정 단계 (`shop/finalize_order.php`)
- 임시 폴더를 주문번호 폴더로 변경
- `temp_xxx/` → `{주문번호}/`
- `mlangorder_printauto` 테이블에 최종 저장

```php
// 폴더 이름 변경
$temp_folder = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/temp_xxx/';
$final_folder = $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/upload/' . $order_no . '/';
rename($temp_folder, $final_folder);

// DB 업데이트
$img_folder = 'mlangorder_printauto/upload/' . $order_no . '/';
```

### 3. 관리자 페이지 (`admin/mlangprintauto/admin.php`)
- `ImgFolder` 경로에서 파일 목록 표시
- 다운로드 링크 제공

## 🛠️ 구현 상태

### 완료된 품목 (9/9)

| 품목 | 폴더명 | 상태 | 경로 |
|------|--------|------|------|
| 전단지 | inserted | ✅ | mlangorder_printauto/upload/ |
| 명함 | namecard | ✅ | mlangorder_printauto/upload/ |
| 봉투 | envelope | ✅ | mlangorder_printauto/upload/ |
| 카다록 | cadarok | ✅ | mlangorder_printauto/upload/ |
| 상품권 | merchandisebond | ✅ | mlangorder_printauto/upload/ |
| 스티커 | sticker_new | ✅ | mlangorder_printauto/upload/ |
| 양식지 | ncrflambeau | ✅ | mlangorder_printauto/upload/ |
| 자석스티커 | msticker | ✅ | mlangorder_printauto/upload/ |
| 포스터 | littleprint | ✅ | mlangorder_printauto/upload/ |

## 📋 파일 구조

```
mlangorder_printauto/
└── upload/
    ├── temp_abc123_1234567890/    # 장바구니 임시 폴더
    ├── 84080/                      # 주문번호 폴더
    ├── 84081/
    └── 84082/
```

## 🔧 헬퍼 함수

### includes/upload_path_helper.php

품목별 업로드 경로를 통일된 방식으로 반환:

```php
function getUploadPath($product_type, $session_id, $order_no = null) {
    $base_dir = $_SERVER['DOCUMENT_ROOT'];
    $upload_base = '/mlangorder_printauto/upload/';
    
    if ($order_no) {
        // 주문 확정 후
        $folder_name = $order_no . '/';
    } else {
        // 장바구니 단계
        $folder_name = 'temp_' . $session_id . '_' . time() . '/';
    }
    
    return [
        'full_path' => $base_dir . $upload_base . $folder_name,
        'db_path' => 'mlangorder_printauto/upload/' . $folder_name
    ];
}
```

## ⚠️ 중요 사항

### 1. 경로 통일 원칙
- **절대 다른 경로를 사용하지 마세요**
- 모든 품목은 `mlangorder_printauto/upload/` 사용
- 날짜별, IP별 폴더 구조 사용 금지

### 2. 폴더 이름 규칙
- 장바구니: `temp_{세션ID}_{타임스탬프}`
- 주문확정: `{주문번호}` (숫자만)

### 3. DB 저장 형식
- 상대 경로로 저장: `mlangorder_printauto/upload/{주문번호}/`
- 절대 경로 사용 금지
- 앞에 `/` 또는 `../../` 붙이지 않음

### 4. 파일명 처리
```php
// 안전한 파일명 생성
$safe_filename = preg_replace('/[^a-zA-Z0-9._-가-힣]/', '_', $original_name);
```

## 🎯 마이그레이션 호환성

### 구버전 (dsp114.com) 호환
- 구버전도 동일한 경로 구조 사용
- 데이터 마이그레이션 시 경로 변환 불필요
- `MlangOrder_PrintAuto/upload/{주문번호}/` → `mlangorder_printauto/upload/{주문번호}/`

## 📝 체크리스트

새로운 품목 추가 시:

- [ ] `add_to_basket.php`에 표준 업로드 코드 추가
- [ ] `ImgFolder` 컬럼에 경로 저장
- [ ] `shop/finalize_order.php`에서 처리 확인
- [ ] `admin/mlangprintauto/admin.php`에서 파일 표시 확인
- [ ] 이 문서에 품목 추가

---

**최종 업데이트**: 2025-01-15
**작성자**: Claude AI Assistant
**버전**: 1.0
