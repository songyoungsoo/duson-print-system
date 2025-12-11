# MlangPrintAuto 프로젝트 가이드

이 문서는 MlangPrintAuto 인쇄 주문 시스템의 구조와 개발 가이드라인을 제공합니다.

## 📦 시스템 개요

MlangPrintAuto는 9개 품목의 인쇄물 주문을 처리하는 웹 기반 시스템입니다.

### 9개 품목 (필수 암기)

1. **inserted** (전단지) - `/mlangprintauto/inserted/`
2. **sticker_new** (스티커) - `/mlangprintauto/sticker_new/`
3. **envelope** (봉투) - `/mlangprintauto/envelope/`
4. **littleprint** (소량인쇄물) - `/mlangprintauto/littleprint/`
5. **cadarok** (카다록) - `/mlangprintauto/cadarok/`
6. **merchandisebond** (상품권) - `/mlangprintauto/merchandisebond/`
7. **namecard** (명함) - `/mlangprintauto/namecard/`
8. **msticker** (자석스티커) - `/mlangprintauto/msticker/`
9. **ncrflambeau** (양식지) - `/mlangprintauto/ncrflambeau/`

**⚠️ 중요**: 모든 코드 변경, 디버깅, 기능 추가 시 **9개 품목 전체를 항상 고려**해야 합니다.

---

## 🚨 **매우 중요: leaflet 디렉토리 사용 금지** 🚨

**절대 leaflet 디렉토리를 사용하지 마세요!**

- ❌ **사용 금지**: `/mlangprintauto/leaflet/` - 구 디렉토리, 현재 사용 안 함
- ✅ **정상 사용**: `/mlangprintauto/inserted/` - 전단지(flyers)의 올바른 디렉토리

**중요 사항**:
- `leaflet` 디렉토리가 존재하지만 **완전히 무시**해야 합니다
- 전단지 관련 작업은 **무조건 `inserted`만 사용**합니다
- 이 혼동으로 인해 과거 많은 디버깅 시간이 소모되었습니다
- **코드 수정, 검색, 분석 시 leaflet은 제외**하고 inserted만 사용하세요

**이유**:
- leaflet은 레거시 디렉토리로 현재 시스템에서 사용하지 않음
- 모든 전단지 기능은 inserted로 통합됨
- leaflet 관련 코드 수정은 시간 낭비입니다

---

## 🗂️ 파일 업로드 시스템

### 업로드 시스템 구성

#### 1. upload_path_manager.php (표준 시스템)

**사용 품목**: inserted, sticker_new, envelope, cadarok, merchandisebond, ncrflambeau

**경로 구조**:
```
/ImgFolder/_MlangPrintAuto_{product}_index.php/YYYY/MMDD/IP/timestamp/
```

**주요 함수**:
- `generateUploadPath($product_type)` - 업로드 경로 생성
- `createUploadDirectory($path)` - 디렉토리 생성
- `generateUniqueFilename($original_filename, $timestamp)` - 파일명 생성

**파일명 형식**: `{random}{YYYYMMDDHHmmss}.{ext}`
예: `4820251120145623.pdf`

**사용 예시**:
```php
include "../../includes/upload_path_manager.php";

$upload_path_info = generateUploadPath('inserted');
$physical_path = $upload_path_info['physical_path'];  // 끝에 '/' 포함됨
$img_folder = $upload_path_info['img_folder'];

$unique_filename = generateUniqueFilename($filename, $upload_path_info['timestamp']);
$target_path = $upload_path_info['physical_path'] . $unique_filename;  // '/' 추가 불필요

if (move_uploaded_file($temp_file, $target_path)) {
    // 성공
}
```

#### 2. UploadPathHelper.php (헬퍼 클래스)

**사용 품목**: namecard, msticker, littleprint

**경로 구조**: 동일 (`/ImgFolder/_MlangPrintAuto_{product}_index.php/YYYY/MMDD/IP/timestamp/`)

**주요 메서드**:
- `UploadPathHelper::generateUploadPath($product)` - 경로 생성
- `UploadPathHelper::createDirectory($path)` - 디렉토리 생성

**⚠️ 중요 차이점**:
- `full_path`에 **마지막 `/`가 포함되지 않음**
- 파일 저장 시 **반드시 `'/' .`를 추가**해야 함

**사용 예시**:
```php
require_once __DIR__ . '/../../includes/UploadPathHelper.php';

$paths = UploadPathHelper::generateUploadPath('namecard');
$upload_folder = $paths['full_path'];  // 끝에 '/' 없음!
$upload_folder_db = $paths['db_path'];

// ⚠️ 필수: '/' 추가
$target_path = $upload_folder . '/' . $target_filename;  // '/' 반드시 추가!
$web_url = '/ImgFolder/' . $upload_folder_db . '/' . $target_filename;  // '/' 반드시 추가!

if (move_uploaded_file($temp_file, $target_path)) {
    // 성공
}
```

### 품목별 업로드 시스템 매핑

| 품목 | 업로드 시스템 | 경로에 `/` 포함 | 파일 저장 시 주의사항 |
|------|--------------|----------------|---------------------|
| inserted | upload_path_manager.php | ✅ Yes | `/` 추가 불필요 |
| sticker_new | upload_path_manager.php | ✅ Yes | `/` 추가 불필요 |
| envelope | upload_path_manager.php | ✅ Yes | `/` 추가 불필요 |
| cadarok | upload_path_manager.php | ✅ Yes | `/` 추가 불필요 |
| merchandisebond | upload_path_manager.php | ✅ Yes | `/` 추가 불필요 |
| ncrflambeau | upload_path_manager.php | ✅ Yes | `/` 추가 불필요 |
| namecard | UploadPathHelper | ❌ No | **`'/' .` 필수 추가** |
| msticker | UploadPathHelper | ❌ No | **`'/' .` 필수 추가** |
| littleprint | UploadPathHelper | ❌ No | **`'/' .` 필수 추가** |

---

## 🗄️ 데이터베이스 구조

### 주요 테이블

#### mlangorder_printauto (주문 정보)
- `no` - 주문번호 (PK)
- `product_type` - 품목 타입 (9개 중 하나)
- `ImgFolder` - 업로드 경로 (예: `_MlangPrintAuto_inserted_index.php/2025/1120/IP/timestamp`)
- `ThingCate` - 대표 파일명
- `uploaded_files` - JSON 형식 파일 정보 배열
- `st_price` - 인쇄비
- `st_price_vat` - 부가세 포함 금액

#### shop_temp (임시 장바구니)
- mlangorder_printauto와 동일한 구조
- 결제 완료 시 mlangorder_printauto로 이동

---

## 🎨 프론트엔드 구조

### 공통 모달 시스템

**파일**: `/includes/upload_modal.js`

**주요 함수**:
- `window.openUploadModal()` - 업로드 모달 열기
- `window.closeUploadModal()` - 업로드 모달 닫기
- `window.processFiles(files)` - 파일 처리 (검증 + 래퍼 객체 생성)
- `window.addToBasketFromModal()` - 장바구니 추가 (각 품목별 함수 호출)

**파일 래퍼 객체 구조**:
```javascript
{
    id: 'timestamp_random',
    file: File,              // ⚠️ 실제 File 객체는 여기!
    name: 'filename.jpg',
    size: '1.5 MB',
    type: '.jpg'
}
```

**⚠️ 중요**: FormData 전송 시 `fileObj.file`을 사용해야 함
```javascript
// ❌ 잘못된 예
formData.append("uploaded_files[]", fileObj);  // 래퍼 객체 전송

// ✅ 올바른 예
formData.append("uploaded_files[]", fileObj.file);  // 실제 File 전송
```

### 품목별 index.php 패턴

각 품목의 `index.php`는 다음 함수를 구현해야 합니다:

```javascript
// 장바구니 추가 처리 (모달에서 호출)
window.handleModalBasketAdd = function(uploadedFiles, onSuccess, onError) {
    const formData = new FormData();
    formData.append("action", "add_to_basket");
    formData.append("product_type", "{product}");

    // 품목별 필드 추가
    // ...

    // ⚠️ 중요: fileObj.file 사용
    uploadedFiles.forEach((fileObj, index) => {
        formData.append("uploaded_files[]", fileObj.file);
    });

    fetch("add_to_basket.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            onSuccess(data);
        } else {
            onError(data.message);
        }
    })
    .catch(error => onError(error.message));
};
```

---

## 🔧 백엔드 구조

### add_to_basket.php 표준 패턴

```php
<?php
// 1. 초기화
require_once __DIR__ . '/../../includes/safe_json_response.php';
header('Content-Type: application/json; charset=utf-8');
session_start();

include "../../includes/functions.php";
include "../../includes/upload_path_manager.php";  // 또는 UploadPathHelper
include "../../db.php";

// 2. 업로드 경로 생성
$upload_path_info = generateUploadPath('{product}');
$physical_path = $upload_path_info['physical_path'];
$img_folder = $upload_path_info['img_folder'];

// 3. 디렉토리 생성
if (!createUploadDirectory($physical_path)) {
    safe_json_response(false, null, '업로드 디렉토리 생성 실패');
}

// 4. 파일 업로드 처리
$uploaded_files = [];
if (!empty($_FILES['uploaded_files'])) {
    // 배열 정규화
    $files_to_process = [];
    if (is_array($files['name'])) {
        for ($i = 0; $i < count($files['name']); $i++) {
            $files_to_process[] = [
                'name' => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
        }
    } else {
        $files_to_process[] = $files;
    }

    foreach ($files_to_process as $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $unique_filename = generateUniqueFilename($file['name'], $upload_path_info['timestamp']);

            // upload_path_manager: '/' 추가 불필요
            $target_path = $upload_path_info['physical_path'] . $unique_filename;

            // UploadPathHelper: '/' 필수 추가
            // $target_path = $upload_folder . '/' . $unique_filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $uploaded_files[] = [
                    'original_name' => $file['name'],
                    'saved_name' => $unique_filename,
                    'path' => $target_path,
                    'size' => $file['size']
                ];
            }
        }
    }
}

// 5. DB 저장
$thing_cate = !empty($uploaded_files) ? $uploaded_files[0]['saved_name'] : '';
$uploaded_files_json = json_encode($uploaded_files, JSON_UNESCAPED_UNICODE);

$insert_query = "INSERT INTO shop_temp (..., ImgFolder, ThingCate, uploaded_files) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($db, $insert_query);
mysqli_stmt_bind_param($stmt, "sss", $img_folder, $thing_cate, $uploaded_files_json);
mysqli_stmt_execute($stmt);

// 6. 응답
safe_json_response(true, [
    'basket_id' => mysqli_insert_id($db),
    'uploaded_files_count' => count($uploaded_files),
    'upload_path' => $img_folder
]);
?>
```

---

## 🚨 공통 주의사항

### 1. 9개 품목 일관성 유지

모든 기능 추가/수정 시:
- ✅ 9개 품목 모두에 동일하게 적용
- ✅ 품목별 차이점 문서화
- ✅ 테스트는 9개 품목 모두 수행

### 2. 경로 `/` 문제 방지

**upload_path_manager.php 사용 시**:
```php
// ✅ 올바른 예 (physical_path에 이미 / 포함)
$target_path = $upload_path_info['physical_path'] . $unique_filename;
```

**UploadPathHelper 사용 시**:
```php
// ✅ 올바른 예 (반드시 '/' 추가)
$target_path = $upload_folder . '/' . $target_filename;
$web_url = '/ImgFolder/' . $upload_folder_db . '/' . $target_filename;

// ❌ 잘못된 예 (파일명이 폴더명에 붙음)
$target_path = $upload_folder . $target_filename;  // .../1763607803filename.jpg
```

### 3. FormData 파일 전송

```javascript
// ✅ 올바른 예
uploadedFiles.forEach((fileObj, index) => {
    formData.append("uploaded_files[]", fileObj.file);  // .file 속성 사용
});

// ❌ 잘못된 예
uploadedFiles.forEach((fileObj, index) => {
    formData.append("uploaded_files[]", fileObj);  // 래퍼 객체 전송
});
```

### 4. 파일 배열 처리

PHP에서 FormData 배열 인식:
```javascript
// ✅ 올바른 예 (PHP가 자동으로 배열 인식)
formData.append("uploaded_files[]", file);

// ❌ 잘못된 예 (마지막 파일만 저장됨)
formData.append("uploaded_files[0]", file);
formData.append("uploaded_files[1]", file);
```

---

## 📁 디렉토리 구조

```
/var/www/html/
├── mlangprintauto/
│   ├── inserted/           # 1. 전단지
│   │   ├── index.php
│   │   └── add_to_basket.php
│   ├── sticker_new/        # 2. 스티커
│   ├── envelope/           # 3. 봉투
│   ├── littleprint/        # 4. 소량인쇄물
│   ├── cadarok/            # 5. 카다록
│   ├── merchandisebond/    # 6. 상품권
│   ├── namecard/           # 7. 명함
│   ├── msticker/           # 8. 자석스티커
│   └── ncrflambeau/        # 9. 양식지
├── includes/
│   ├── upload_path_manager.php    # 표준 업로드 시스템
│   ├── UploadPathHelper.php       # 헬퍼 클래스 시스템
│   ├── upload_modal.js            # 공통 모달 JavaScript
│   ├── functions.php
│   └── db.php
├── admin/
│   └── mlangprintauto/
│       ├── admin.php              # 주문 관리
│       └── download.php           # 파일 다운로드
└── ImgFolder/
    └── _MlangPrintAuto_{product}_index.php/
        └── YYYY/MMDD/IP/timestamp/
            └── files...
```

---

## 🔍 디버깅 가이드

### 파일 업로드 문제 진단

1. **폴더는 생성되지만 파일이 없는 경우**
   - 원인: 경로에 `/` 누락
   - 확인: `target_path` 변수 출력
   - 해결: UploadPathHelper 사용 시 `'/' .` 추가

2. **파일은 저장되지만 관리자 페이지에서 안 보이는 경우**
   - 원인: `ImgFolder` DB 값과 실제 경로 불일치
   - 확인: DB의 `ImgFolder` 컬럼 값
   - 해결: `$img_folder` 값이 올바르게 저장되는지 확인

3. **다중 파일 중 1개만 저장되는 경우**
   - 원인: FormData 배열 표기법 오류
   - 확인: `uploaded_files[]` vs `uploaded_files[0]`
   - 해결: `[]` 사용 (인덱스 없이)

4. **파일이 전송되지 않는 경우**
   - 원인: `fileObj.file` 대신 `fileObj` 전송
   - 확인: JavaScript FormData 구성 코드
   - 해결: `fileObj.file` 사용

---

## 📝 체크리스트

### 새 기능 추가 시

- [ ] 9개 품목 모두에 적용했는가?
- [ ] upload_path_manager vs UploadPathHelper 차이 고려했는가?
- [ ] 경로에 `/` 올바르게 처리했는가?
- [ ] FormData에서 `fileObj.file` 사용했는가?
- [ ] 배열 표기는 `uploaded_files[]` 사용했는가?
- [ ] DB 저장 시 `ImgFolder`, `ThingCate` 올바른가?
- [ ] 로컬과 웹 서버 모두 테스트했는가?
- [ ] 관리자 페이지에서 파일 다운로드 확인했는가?

---

## 📦 택배주소록 시스템

### 서버 환경

#### dsp1830.shop (PHP 7.4, UTF-8)
- **주문 목록**: http://dsp1830.shop/shop_admin/post_list74.php
- **엑셀 다운로드**: http://dsp1830.shop/shop_admin/export_excel74.php
- **데이터베이스**: dsp1830 / dsp1830 / ds701018
- **테이블**: `mlangorder_printauto` (소문자)
- **문자셋**: UTF-8 (utf8mb4)

#### dsp114.com (PHP 5.2, EUC-KR)
- **주문 목록**: http://dsp114.com/shop_admin/post_list52.php
- **엑셀 다운로드**: http://dsp114.com/shop_admin/export_excel52.php
- **데이터베이스**: duson1830 / duson1830 / du1830
- **테이블**: `MlangOrder_PrintAuto` (대소문자 혼합)
- **문자셋**: EUC-KR

### 주요 기능

#### 1. 검색 기능
- **이름 검색**: 수하인명 또는 회사명
- **날짜 범위**: 주문일시 기준
- **주문번호 범위**: 시작~종료 번호
- **주소 필터**: `(zip1 like '%구%') or (zip2 like '%-%')`

#### 2. 데이터 표시
- **페이지당**: 20개 항목
- **정렬**: 주문번호 내림차순 (최신순)
- **JSON 파싱**: `formatted_display` 자동 추출
- **포맷**: 한 줄 표시 (| 구분자)

예시:
```
칼라인쇄(CMYK) | 90g아트지(합판인쇄) | A4 (210x297) | 단면 | 1매 | 인쇄만
```

#### 3. 엑셀 다운로드
- **형식**: UTF-8 BOM 포함 (.xls)
- **제외 컬럼**: 주문번호, 날짜 (택배사 양식 호환)
- **포함 컬럼**: 수하인명, 우편번호, 주소, 전화, 핸드폰, 박스수량, 택배비, 운임구분, 품목명, 기타, 배송메세지
- **다운로드 방식**:
  - 선택 항목만 (체크박스)
  - 전체 다운로드 (검색 조건 포함)

### 데이터베이스 필드

| 필드명 | 타입 | 설명 |
|--------|------|------|
| no | mediumint(8) | 주문번호 (PK) |
| date | datetime | 주문일시 |
| name | varchar(250) | 수하인명 |
| bizname | varchar(50) | 회사명 |
| zip | varchar(10) | 우편번호 |
| zip1 | varchar(250) | 주소1 |
| zip2 | varchar(250) | 주소2 |
| phone | varchar(20) | 전화번호 |
| Hendphone | varchar(20) | 핸드폰 |
| Type | varchar(250) | 품목 타입 |
| Type_1 | text | 주문 상세 (JSON) |
| ImgFolder | text | 업로드 경로 |
| uploaded_files | text | 파일 목록 (JSON) |

### 수하인명 처리 로직

```php
// name이 "0"이거나 빈값인 경우 처리
if (name != '0' && !empty(name)) {
    표시: name
} else if (!empty(bizname)) {
    표시: bizname
} else {
    표시: '-'
}
```

### JSON 데이터 처리

#### 입력 형식
```json
{
  "product_type": "inserted",
  "formatted_display": "인쇄색상: 칼라인쇄(CMYK)\n용지: 90g아트지\n규격: A4 (210x297)\n인쇄면: 단면\n수량: 1매\n디자인: 인쇄만"
}
```

#### 출력 형식
```php
// 1. "항목명: " 제거
$formatted = preg_replace('/^[^:]+:\s*/m', '', $formatted);

// 2. 줄바꿈을 | 로 변경
$formatted = str_replace("\n", ' | ', $formatted);

// 결과: "칼라인쇄(CMYK) | 90g아트지 | A4 (210x297) | 단면 | 1매 | 인쇄만"
```

### 파일 구조

```
/shop_admin/
├── post_list74.php          # 주문 목록 (PHP 7.4)
├── export_excel74.php        # 엑셀 다운로드 (PHP 7.4)
├── post_list52.php          # 주문 목록 (PHP 5.2)
├── export_excel52.php        # 엑셀 다운로드 (PHP 5.2)
├── lib.php                  # 구버전 DB 연결 (사용 안 함)
└── 택배주소록_README.md      # 상세 문서
```

### 환경별 데이터베이스 연결

```php
// db.php 사용 (환경 자동 감지)
include "../db.php";
$connect = $db;

// safe_mysqli_query 사용 (테이블명 자동 매핑)
$result = safe_mysqli_query($connect, $query);
```

### FTP 업로드 명령

```bash
# dsp1830.shop 업로드
curl -T /path/to/file.php ftp://dsp1830.shop/shop_admin/ --user dsp1830:ds701018

# 여러 파일 동시 업로드
curl -T /tmp/post_list74.php ftp://dsp1830.shop/shop_admin/ --user dsp1830:ds701018 && \
curl -T /tmp/export_excel74.php ftp://dsp1830.shop/shop_admin/ --user dsp1830:ds701018
```

### 트러블슈팅

#### 1. 데이터베이스 연결 오류
- ✅ `db.php` 경로: `include "../db.php";`
- ✅ `safe_mysqli_query()` 함수 사용
- ✅ 계정 정보: dsp1830 / ds701018

#### 2. 한글 깨짐
- ✅ UTF-8 BOM 포함
- ✅ `mysqli_set_charset($db, 'utf8mb4');`
- ✅ HTML 헤더: `<meta charset="utf-8">`

#### 3. 수하인명 "0" 표시
- ✅ `bizname` 대체값 확인
- ✅ 빈값은 "-" 표시

#### 4. JSON 파싱 오류
- ✅ JSON 시작 확인: `$data[0] == '{'`
- ✅ `json_decode($data, true)` 사용
- ✅ `formatted_display` 키 존재 확인

---

## 🔗 관련 문서

### 인쇄 주문 시스템
- `/admin/mlangprintauto/admin.php` - 주문 관리 인터페이스
- `/includes/upload_path_manager.php` - 업로드 경로 생성 로직
- `/includes/UploadPathHelper.php` - 헬퍼 클래스 업로드 시스템
- `/includes/upload_modal.js` - 공통 모달 JavaScript

### 택배주소록 시스템
- `/shop_admin/post_list74.php` - 주문 목록 (PHP 7.4, UTF-8)
- `/shop_admin/export_excel74.php` - 엑셀 다운로드 (PHP 7.4)
- `/shop_admin/post_list52.php` - 주문 목록 (PHP 5.2, EUC-KR)
- `/shop_admin/export_excel52.php` - 엑셀 다운로드 (PHP 5.2)
- `/shop_admin/택배주소록_README.md` - 택배주소록 상세 가이드
- `/db.php` - 환경별 자동 DB 연결
- `/config.env.php` - 환경 설정 관리

---

**마지막 업데이트**: 2025-11-27
**작성자**: Claude Code Assistant
