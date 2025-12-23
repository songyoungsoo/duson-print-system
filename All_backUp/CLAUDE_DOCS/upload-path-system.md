# 파일 업로드 경로 표준화 시스템

**작성일**: 2025-11-15
**목적**: 9개 품목의 파일 업로드 경로를 통일된 규칙으로 관리하고 관리자 페이지에서 파일 다운로드 제공

## 개요

전체 9개 인쇄물 품목(전단지, 명함, 봉투, 스티커, 자석스티커, 카다록, 포스터, NCR양식, 상품권)의 파일 업로드를 표준화된 경로 구조로 관리하며, 관리자가 주문 파일을 손쉽게 다운로드할 수 있는 통합 시스템입니다.

## 경로 구조

```
/ImgFolder/_MlangPrintAuto_{product}_index.php/{year}/{mmdd}/{ip}/{timestamp}/{filename}
```

### 구성 요소

- **{product}**: 품목 코드 (inserted, namecard, envelope 등)
- **{year}**: 4자리 연도 (예: 2025)
- **{mmdd}**: 월일 4자리 (예: 1115)
- **{ip}**: 클라이언트 IP 주소 (예: 127.0.0.1)
- **{timestamp}**: Unix 타임스탬프 (예: 1731654321)
- **{filename}**: 원본 파일명 또는 커스텀 파일명

### 예시

```
/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1115/127.0.0.1/1731654321/sample.jpg
```

## UploadPathHelper 클래스

**위치**: `/var/www/html/includes/UploadPathHelper.php`

### 지원 품목 (9개)

| 코드 | 품목명 | 디렉토리명 |
|------|--------|-----------|
| `inserted` | 전단지 | `_MlangPrintAuto_inserted_index.php` |
| `namecard` | 명함 | `_MlangPrintAuto_namecard_index.php` |
| `envelope` | 봉투 | `_MlangPrintAuto_envelope_index.php` |
| `sticker` | 스티커 | `_MlangPrintAuto_sticker_new_index.php` |
| `msticker` | 자석스티커 | `_MlangPrintAuto_msticker_index.php` |
| `cadarok` | 카다록 | `_MlangPrintAuto_cadarok_index.php` |
| `littleprint` | 포스터 | `_MlangPrintAuto_littleprint_index.php` |
| `ncrflambeau` | NCR양식 | `_MlangPrintAuto_ncrflambeau_index.php` |
| `merchandisebond` | 상품권 | `_MlangPrintAuto_merchandisebond_index.php` |

## 주요 기능

### 1. 파일 업로드

#### 단일 파일 업로드
```php
<?php
require_once __DIR__ . '/includes/UploadPathHelper.php';

if (isset($_FILES['upload_file'])) {
    $result = UploadPathHelper::uploadFile('inserted', $_FILES['upload_file']);

    if ($result['success']) {
        // 데이터베이스 저장
        $imgFolder = $result['db_img_folder'];
        $filename = $result['db_thing_cate'];

        echo "업로드 성공: " . $result['web_path'];
    } else {
        echo "업로드 실패: " . $result['error'];
    }
}
```

#### 다중 파일 업로드 (NEW - 2025-11-15)
```php
<?php
require_once __DIR__ . '/includes/UploadPathHelper.php';

if (isset($_FILES['upload_files'])) {
    $result = UploadPathHelper::uploadMultipleFiles('inserted', $_FILES['upload_files']);

    if ($result['success']) {
        echo "업로드 성공: " . count($result['uploaded']) . "개 파일";
        foreach ($result['uploaded'] as $file) {
            echo $file['filename'] . ": " . $file['web_path'] . "\n";
        }
    } else {
        echo "업로드 실패: " . count($result['failed']) . "개 파일\n";
        foreach ($result['failed'] as $fail) {
            echo $fail['filename'] . ": " . $fail['error'] . "\n";
        }
    }
}
```

### 2. DB에서 파일 경로 복원

```php
<?php
require_once __DIR__ . '/includes/UploadPathHelper.php';

$fileInfo = UploadPathHelper::getFilePathFromDB($row['ImgFolder'], $row['ThingCate']);

if ($fileInfo['exists']) {
    echo '<a href="' . $fileInfo['url'] . '" download>다운로드</a>';
} else {
    echo '파일 없음';
}
```

### 3. 주문의 모든 파일 조회 (NEW - 2025-11-15)

```php
<?php
require_once __DIR__ . '/includes/UploadPathHelper.php';

$files = UploadPathHelper::getOrderFiles($db, $orderNo);

foreach ($files as $file) {
    echo $file['filename'] . " - ";
    echo $file['exists'] ? "존재" : "없음";
    echo "\n";
}
```

### 4. ZIP 압축 다운로드 (NEW - 2025-11-15)

```php
<?php
require_once __DIR__ . '/includes/UploadPathHelper.php';

$files = UploadPathHelper::getOrderFiles($db, $orderNo);
$zipResult = UploadPathHelper::createZipArchive($files, 'order_' . $orderNo . '.zip');

if ($zipResult['success']) {
    UploadPathHelper::sendZipDownload($zipResult['zip_path'], 'order_files.zip');
}
```

## 관리자 파일 다운로드 시스템 (NEW - 2025-11-15)

### 구성 요소

1. **다운로드 엔드포인트**: `admin/mlangprintauto/download_files.php`
2. **UI 컴포넌트**: `admin/mlangprintauto/includes/FileDownloadComponent.php`
3. **통합 페이지**: `admin/mlangprintauto/orderlist.php`

### 다운로드 액션

#### 단일 파일 다운로드
```
GET download_files.php?action=single&order_no=123&filename=sample.jpg
```

#### 이미지 미리보기
```
GET download_files.php?action=preview&order_no=123&filename=sample.jpg
```

#### 전체 파일 ZIP 다운로드
```
GET download_files.php?action=zip&order_no=123
```

### UI 컴포넌트 사용

```php
<?php
require_once __DIR__ . '/../../includes/UploadPathHelper.php';
require_once __DIR__ . '/includes/FileDownloadComponent.php';

// 다운로드 버튼 렌더링
echo FileDownloadComponent::renderDownloadButton(
    $orderNo,
    $filename,
    $imgFolder,
    'btn btn-sm btn-primary'
);

// ZIP 다운로드 버튼 렌더링 (파일이 2개 이상일 때)
echo FileDownloadComponent::renderZipDownloadButton($orderNo, $fileCount);

// 파일 목록 테이블 렌더링
echo FileDownloadComponent::renderFileList($db, $orderNo);

// CSS 및 JavaScript 추가 (페이지 하단)
echo FileDownloadComponent::renderCSS();
echo FileDownloadComponent::renderJavaScript();
```

### 주요 기능

- **단일 파일 다운로드**: 개별 파일을 직접 다운로드
- **이미지 미리보기**: 이미지 파일을 팝업 창에서 미리보기 (JPG, PNG, GIF, WebP, BMP 지원)
- **ZIP 압축 다운로드**: 주문의 모든 파일을 ZIP 파일로 압축하여 한 번에 다운로드
- **파일 존재 여부 확인**: DB에 기록된 파일이 실제 디스크에 존재하는지 검증

## 데이터베이스 저장

`mlangorder_printauto` 테이블:

| 컬럼 | 타입 | 저장 내용 |
|------|------|----------|
| `ImgFolder` | text | 디렉토리 경로 (상대 경로) |
| `ThingCate` | varchar(250) | 파일명 |

**저장 예시**:
- `ImgFolder`: `_MlangPrintAuto_inserted_index.php/2025/1115/127.0.0.1/1731654321`
- `ThingCate`: `sample.jpg`

## 시스템 통합 (2025-11-15)

### 1. UploadPathHelper 확장 기능

**새로운 메서드**:
- `uploadMultipleFiles()`: 다중 파일 업로드 처리
- `normalizeFilesArray()`: $_FILES 배열 정규화 (단일/다중 형식 지원)
- `getOrderFiles()`: 주문 번호로 모든 파일 조회
- `createZipArchive()`: 파일 배열을 ZIP으로 압축
- `sendZipDownload()`: ZIP 파일 다운로드 전송 및 자동 삭제

### 2. FileDownloadComponent 클래스

**렌더링 메서드**:
- `renderDownloadButton()`: 다운로드 버튼 HTML 생성
- `renderZipDownloadButton()`: ZIP 다운로드 버튼 생성
- `renderFileList()`: 파일 목록 테이블 생성
- `renderJavaScript()`: 미리보기 팝업 JavaScript 생성
- `renderCSS()`: 스타일링 CSS 생성

### 3. 관리자 페이지 통합

**orderlist.php 변경사항**:
1. "파일" 컬럼 추가 (테이블 헤더)
2. 각 주문 행에 파일 다운로드 버튼 추가
3. 페이지 하단에 CSS 및 JavaScript 포함

## 장점

1. **일관성**: 9개 품목 모두 동일한 경로 구조
2. **유지보수성**: 경로 변경 시 UploadPathHelper만 수정
3. **안전성**: 디렉토리 자동 생성, 에러 처리 내장
4. **추적성**: IP와 타임스탬프로 업로드 시점 추적
5. **확장성**: 새 품목 추가 시 배열에만 추가
6. **편의성**: 관리자가 파일을 손쉽게 다운로드 및 미리보기 가능
7. **효율성**: 다중 파일을 ZIP으로 한 번에 다운로드
8. **재사용성**: FileDownloadComponent를 다른 관리자 페이지에도 활용 가능

## 기술 사양

- **압축 라이브러리**: PHP ZipArchive 클래스
- **임시 파일 저장**: `sys_get_temp_dir()/mlang_downloads/`
- **자동 정리**: ZIP 다운로드 후 임시 파일 자동 삭제
- **보안**: 파일 존재 여부 검증, prepared statements 사용
- **호환성**: 기존 레거시 경로 형식도 지원 (`getFilePathFromDB()`)

## 관련 문서

- [UploadPathHelper.php 소스코드](/var/www/html/includes/UploadPathHelper.php)
- [FileDownloadComponent.php 소스코드](/var/www/html/admin/mlangprintauto/includes/FileDownloadComponent.php)
- [download_files.php 엔드포인트](/var/www/html/admin/mlangprintauto/download_files.php)
- [orderlist.php 통합 예시](/var/www/html/admin/mlangprintauto/orderlist.php)

## 🔄 통합 완료 (2025-11-15)

### UploadPathHelper 전체 통합

**완료 사항**:
1. ✅ 9개 제품 전체 `add_to_basket.php` 파일 UploadPathHelper 통합
2. ✅ 레거시 경로 호환성 보장 (`getFilePathFromDB` 강화)
3. ✅ 대문자/소문자 경로 자동 감지 및 변환

**변경된 파일**:
- `mlangprintauto/namecard/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/inserted/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/envelope/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/cadarok/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/littleprint/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/merchandisebond/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/msticker/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/ncrflambeau/add_to_basket.php` - UploadPathHelper 적용
- `mlangprintauto/sticker_new/add_to_basket.php` - UploadPathHelper 적용
- `includes/UploadPathHelper.php` - 레거시 경로 호환 로직 추가

**레거시 호환성**:
```php
// getFilePathFromDB()는 이제 자동으로 대문자/소문자 경로 변형 시도
$fileInfo = UploadPathHelper::getFilePathFromDB($row['ImgFolder'], $row['ThingCate']);
// 예: _MlangPrintAuto_NameCard_index.php → _MlangPrintAuto_namecard_index.php 자동 변환
```

**장점**:
- 기존 레거시 주문 데이터 그대로 호환 (대문자 경로)
- 신규 주문은 표준화된 소문자 경로 사용
- 코드 중복 제거 및 유지보수성 향상

---

**최종 업데이트**: 2025-11-15
