# File Upload/Download System

---

## 📤 통합 파일 업로드 시스템

**날짜**: 2025-11-19 (최종 검증)
**범위**: 9개 품목 (inserted, namecard, envelope, sticker, msticker, cadarok, littleprint, ncrflambeau, merchandisebond)
**상태**: ✅ 전체 시스템 완성 및 검증 완료

---

## 📁 경로 구조

```
/ImgFolder/_MlangPrintAuto_{product}_index.php/{YYYY}/{MMDD}/{IP}/{timestamp}/{filename}

예시:
/ImgFolder/_MlangPrintAuto_namecard_index.php/2025/1119/ipv6_1/1763508971/test.png
```

**IPv6 처리**: `::1` → `ipv6_1` (파일시스템 안전 변환)

---

## 🔧 StandardUploadHandler 사용법

```php
// 1. StandardUploadHandler 임포트
require_once __DIR__ . '/../../includes/StandardUploadHandler.php';

// 2. 파일 업로드 처리 (한 줄로 완료)
$upload_result = StandardUploadHandler::processUpload('product_name', $_FILES);

if (!$upload_result['success']) {
    safe_json_response(false, null, $upload_result['error']);
}

// 3. 결과 추출
$uploaded_files = $upload_result['files'];
$img_folder = $upload_result['img_folder'];
$thing_cate = $upload_result['thing_cate'];
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

// 4. DB 저장 (단일 INSERT)
$sql = "INSERT INTO shop_temp (..., uploaded_files, ImgFolder, ThingCate)
        VALUES (?, ..., ?, ?, ?)";
mysqli_stmt_bind_param($stmt, "...sss", ..., $uploaded_files_json, $img_folder, $thing_cate);
```

---

## 📊 JSON Metadata 구조

```json
[
  {
    "original_name": "test.png",
    "saved_name": "test.png",
    "path": "/var/www/html/ImgFolder/...",
    "size": 113,
    "web_url": "/ImgFolder/..."
  }
]
```

---

## 💾 Database Storage

**장바구니** (`shop_temp`):
- `ImgFolder`: 상대 경로
- `uploaded_files`: JSON 배열 (TEXT)

**주문 확정** (`mlangorder_printauto`):
- 장바구니에서 복사
- 동일 JSON 구조 유지

---

## 📥 Download System

### 개별 파일 다운로드
```php
// admin/mlangprintauto/download.php
// 자동 경로 감지 (레거시 호환)

http://localhost/admin/mlangprintauto/download.php?no=103703&downfile=test.png
```

### 일괄 ZIP 다운로드
```php
// admin/mlangprintauto/download_all.php
// JSON 파싱하여 ZIP 압축

http://localhost/admin/mlangprintauto/download_all.php?no=103703
```

---

## 🧪 Testing

### 업로드 테스트 (curl)
```bash
curl -X POST http://localhost/mlangprintauto/namecard/add_to_basket.php \
  -F "uploaded_files[]=@/tmp/test.png" \
  -F "product_type=namecard" \
  -F "calculated_price=50000"
```

### DB 확인
```sql
SELECT no, product_type, ImgFolder, uploaded_files
FROM shop_temp
WHERE session_id = 'your_session_id'
ORDER BY no DESC LIMIT 1;
```

---

## 🔴 Common Issues

| 문제 | 원인 | 해결 |
|------|------|------|
| 파일 못 찾음 | path 필드 누락 | JSON에 전체 경로 포함 확인 |
| IPv6 디렉토리 생성 실패 | `::1` 파일명 불가 | 자동으로 `ipv6_1` 변환됨 |
| JSON 파싱 에러 | `'0'` 문자열 저장 | `json_encode([])` 사용 |
| 다운로드 404 | 잘못된 경로 | `download.php`가 3가지 경로 시도 |

---

*Loaded only when: File upload/download operations needed*
*Full Guide: 업로드다운로드251118.md*
