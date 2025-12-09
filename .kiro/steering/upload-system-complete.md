# 📦 통합 파일 업로드 시스템 - 완전 가이드

## 🎯 시스템 개요

모든 9개 품목이 `UploadPathHelper` 클래스를 사용하여 통일된 경로 구조로 파일을 관리합니다.

## 📁 표준 경로 구조

```
/ImgFolder/_MlangPrintAuto_{품목}_index.php/{년도}/{월일}/{IP주소}/{타임스탬프}/파일명.jpg
```

### 예시
```
/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1116/124.195.240.61/1763246681/sample.jpg
```

## 🔧 품목별 매핑

| 품목 | 코드 | 폴더명 |
|------|------|--------|
| 전단지 | `inserted` | `_MlangPrintAuto_inserted_index.php` |
| 명함 | `namecard` | `_MlangPrintAuto_namecard_index.php` |
| 봉투 | `envelope` | `_MlangPrintAuto_envelope_index.php` |
| 스티커 | `sticker` | `_MlangPrintAuto_sticker_new_index.php` |
| 자석스티커 | `msticker` | `_MlangPrintAuto_msticker_index.php` |
| 카다록 | `cadarok` | `_MlangPrintAuto_cadarok_index.php` |
| 포스터 | `littleprint` | `_MlangPrintAuto_littleprint_index.php` |
| 양식지 | `ncrflambeau` | `_MlangPrintAuto_ncrflambeau_index.php` |
| 상품권 | `merchandisebond` | `_MlangPrintAuto_merchandisebond_index.php` |

## 💻 사용 방법

### PHP에서 경로 생성

```php
require_once __DIR__ . '/../../includes/UploadPathHelper.php';

// 경로 생성
$paths = UploadPathHelper::generateUploadPath('inserted');

$upload_folder = $paths['full_path'];      // /dsp1830/www/ImgFolder/_MlangPrintAuto_inserted_index.php/...
$upload_folder_db = $paths['db_path'];     // _MlangPrintAuto_inserted_index.php/... (DB 저장용)

// 폴더 생성
if (!file_exists($upload_folder)) {
    mkdir($upload_folder, 0755, true);
}

// 파일 업로드
foreach ($_FILES['uploaded_files']['name'] as $key => $filename) {
    if ($_FILES['uploaded_files']['error'][$key] == UPLOAD_ERR_OK) {
        $temp_file = $_FILES['uploaded_files']['tmp_name'][$key];
        $target_path = $upload_folder . $filename;
        move_uploaded_file($temp_file, $target_path);
    }
}

// DB에 경로 저장
$update_query = "UPDATE shop_temp SET ImgFolder = ? WHERE no = ?";
$stmt = mysqli_prepare($db, $update_query);
mysqli_stmt_bind_param($stmt, "si", $upload_folder_db, $cart_id);
mysqli_stmt_execute($stmt);
```

### JavaScript에서 파일 전송

```javascript
const formData = new FormData();

// 파일 추가 (명시적 인덱스 사용)
uploadedFiles.forEach((fileObj, index) => {
    formData.append(`uploaded_files[${index}]`, fileObj.file);
});

// 서버로 전송
fetch('add_to_basket.php', {
    method: 'POST',
    body: formData
});
```

## 📊 데이터베이스 구조

### shop_temp (장바구니)
```sql
- ImgFolder VARCHAR(255)  -- 업로드 경로 (예: _MlangPrintAuto_inserted_index.php/2025/1116/...)
- ThingCate VARCHAR(255)  -- 품목 카테고리
- uploaded_files TEXT     -- 파일 정보 JSON
```

### mlangorder_printauto (주문)
```sql
- ImgFolder VARCHAR(255)  -- 업로드 경로
- ThingCate VARCHAR(255)  -- 품목 카테고리
```

## 🔍 관리자 다운로드 시스템

### 개별 파일 다운로드
```
admin/mlangprintauto/download.php?order_no={주문번호}&file={파일명}
```

### ZIP 일괄 다운로드
```
admin/mlangprintauto/download_all.php?order_no={주문번호}
```

### 경로 자동 감지 로직
1. `ImgFolder` 컬럼 확인
2. 레거시 경로 시도: `_MlangPrintAuto_NameCard_index.php` ↔ `_MlangPrintAuto_namecard_index.php`
3. 신버전 경로 시도: `mlangorder_printauto/upload/{주문번호}/`

## ✅ 구현 상태

| 품목 | UploadPathHelper | 파일 업로드 | DB 저장 | 다운로드 |
|------|------------------|-------------|---------|----------|
| 전단지 | ✅ | ✅ | ✅ | ✅ |
| 명함 | ✅ | ✅ | ✅ | ✅ |
| 봉투 | ✅ | ✅ | ✅ | ✅ |
| 스티커 | ✅ | ✅ | ✅ | ✅ |
| 자석스티커 | ✅ | ✅ | ✅ | ✅ |
| 카다록 | ✅ | ✅ | ✅ | ✅ |
| 포스터 | ✅ | ✅ | ✅ | ✅ |
| 양식지 | ✅ | ✅ | ✅ | ✅ |
| 상품권 | ✅ | ✅ | ✅ | ✅ |

## 🐛 트러블슈팅

### 파일이 업로드되지 않음
1. JavaScript에서 `uploaded_files[0]` 형식으로 전송하는지 확인
2. PHP에서 `$_FILES['uploaded_files']` 배열 구조 확인
3. 폴더 쓰기 권한 확인: `is_writable($upload_folder)`

### 다운로드가 안 됨
1. `ImgFolder` 컬럼에 경로가 저장되었는지 확인
2. 실제 파일이 서버에 존재하는지 확인
3. 레거시 경로 매핑 확인

### 경로가 잘못됨
1. `DOCUMENT_ROOT` 확인: `/dsp1830/www`
2. `UploadPathHelper::generateUploadPath()` 반환값 확인
3. 품목 코드가 올바른지 확인

## 📝 유지보수 가이드

### 새 품목 추가 시
1. `UploadPathHelper.php`의 `$productPaths` 배열에 추가
2. `add_to_basket.php`에서 `generateUploadPath('품목코드')` 사용
3. JavaScript에서 `uploaded_files[index]` 형식으로 전송
4. DB에 `ImgFolder` 저장

### 경로 변경 시
1. `UploadPathHelper.php`만 수정
2. 모든 품목에 자동 반영됨

---

**최종 업데이트**: 2025-11-16
**작성자**: Kiro AI Assistant
