# DSP114.COM 파일 업로드/다운로드 시스템 분석 문서

**분석 날짜**: 2025-11-19
**대상 사이트**: http://www.dsp114.com (두손기획인쇄)
**FTP 접속**: ftp://dsp114.com (duson1830/du1830)
**목적**: 신규 시스템(localhost/dsp1830.shop)으로 마이그레이션을 위한 구조 분석

---

## 📊 시스템 개요

DSP114.COM은 레거시 PHP 인쇄 주문 시스템으로, **계층적 디렉토리 구조**를 사용하여 업로드 파일을 관리합니다.

### 핵심 특징
- **연도/월일/IP/타임스탬프** 기반 4단계 디렉토리 구조
- **제품별 독립적인 업로드 경로** 관리
- **주문 번호 기반 다운로드** 시스템
- **MySQL (레거시)** 데이터베이스 사용 (mysql_* 함수)

---

## 🗂️ 파일 업로드 시스템

### 1. 업로드 경로 구조

#### 제품별 업로드 경로 (ImgFolder)

```
/ImgFolder/_MlangPrintAuto_{제품}_index.php/{년}/{월일}/{IP주소}/{타임스탬프}/{파일명}
```

**실제 예시**:
```
/ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0315/124.195.229.162/1741998390/book_001.jpg
             └─────────┬──────────┘       └┬┘  └┬┘   └──────┬─────┘  └────┬───┘  └────┬────┘
                    제품코드              년   월일      IP 주소       타임스탬프    원본파일명
```

#### 제품별 디렉토리 매핑

| 제품 코드 | 디렉토리 이름 | 제품명 |
|----------|--------------|--------|
| NameCard | `_MlangPrintAuto_NameCard_index.php` | 명함 |
| inserted | `_MlangPrintAuto_inserted_index.php` | 전단지 |
| envelope | `_MlangPrintAuto_envelope_index.php` | 봉투 |
| sticker | `_MlangPrintAuto_sticker_index.php` | 스티커 |
| LittlePrint | `_MlangPrintAuto_LittlePrint_index.php` | 포스터 |
| cadarok | `_MlangPrintAuto_cadarok_index.php` | 카다록 |
| NcrFlambeau | `_MlangPrintAuto_NcrFlambeau_index.php` | 양식지 |
| MerchandiseBond | `_MlangPrintAuto_MerchandiseBond_index.php` | 상품권 |

### 2. 경로 생성 로직

**연도 (4자리)**: `2025`
**월일 (4자리)**: `0315` (3월 15일)
**IP 주소**: `124.195.229.162` (업로더 IP)
**타임스탬프**: `1741998390` (Unix timestamp)

```php
// 경로 생성 예시 (레거시 코드)
$year = date('Y');           // 2025
$monthday = date('md');      // 0315
$ip = $_SERVER['REMOTE_ADDR']; // 124.195.229.162
$timestamp = time();         // 1741998390

$upload_path = "/ImgFolder/_MlangPrintAuto_{$product}_index.php/{$year}/{$monthday}/{$ip}/{$timestamp}/";
```

### 3. 장점과 단점

#### ✅ 장점
1. **시간 기반 추적**: 파일 업로드 시각을 경로에서 바로 확인 가능
2. **IP 기반 분리**: 동시 업로드 충돌 방지
3. **타임스탬프 고유성**: 동일 시각 업로드도 구분 가능
4. **제품별 격리**: 제품 간 파일 혼재 방지

#### ⚠️ 단점
1. **깊은 디렉토리 구조**: 파일 접근 경로가 길고 복잡
2. **관리 어려움**: 수동 파일 정리가 매우 어려움
3. **백업 복잡성**: 연도별/월별 백업 전략 필요
4. **IPv6 미지원**: IP 주소가 디렉토리명에 사용되어 IPv6 문제 발생 가능

---

## 📥 주문 시 업로드 시스템

### 1. 주문 업로드 경로 (MlangOrder_PrintAuto)

주문 확정 시에는 **주문 번호 기반 디렉토리**로 파일을 복사/이동합니다.

```
/MlangOrder_PrintAuto/upload/{주문번호}/{파일명}
```

**실제 예시**:
```
/MlangOrder_PrintAuto/upload/103456/namecard_front.jpg
                              └─┬──┘  └───────┬────────┘
                             주문번호      원본 파일명
```

### 2. 주문 처리 플로우

```
1. 장바구니 (shop_temp)
   ├─ ImgFolder: /ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0315/124.195.229.162/1741998390/
   └─ ThingCate: book_001.jpg

2. 주문 확정 (OnlineOrder.php)
   ├─ 주문 번호 생성 (max(no) + 1)
   ├─ 디렉토리 생성: mkdir("upload/$new_no", 0755)
   └─ 권한 설정: chmod 777

3. 파일 이동/복사
   ├─ 원본: /ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0315/.../book_001.jpg
   └─ 대상: /MlangOrder_PrintAuto/upload/103456/book_001.jpg

4. DB 저장 (MlangOrder_PrintAuto 테이블)
   ├─ no: 103456
   ├─ ImgFolder: (원본 경로 또는 주문 경로)
   └─ ThingCate: book_001.jpg
```

### 3. 주문 처리 코드 분석

**OnlineOrder.php** (핵심 코드):

```php
// 주문 번호 생성
$Table_result = mysql_query("SELECT max(no) FROM MlangOrder_PrintAuto");
$row = mysql_fetch_row($Table_result);
if($row[0]) {
   $new_no = $row[0] + 1;
} else {
   $new_no = 1;
}

// 업로드 폴더 생성
$dir = "upload/$new_no";
$dir_handle = is_dir("$dir");
if(!$dir_handle){
    mkdir("$dir", 0755);
    exec("chmod 777 $dir");
}

// DB 삽입
$dbinsert = "INSERT INTO MlangOrder_PrintAuto VALUES(
    '$new_no',
    '$Type',
    '$ImgFolder',
    ...
)";
$result_insert = mysql_query($dbinsert, $db);
```

---

## 📤 관리자 다운로드 시스템

### 1. 다운로드 엔드포인트

**파일**: `/admin/MlangPrintAuto/download.php`
**용도**: 주문 첨부 파일 다운로드

### 2. 다운로드 로직 분석

```php
<?php
ob_start();

// 파일이 있는 디렉토리
$downfiledir = "../../shop/data/";

// 파일 이름
$downfile = $_GET['downfile'];

// Referer 체크 (외부 접근 차단)
if (!eregi($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'])) {
    Error("외부에서의 다운로드 접근이 차단되어 있습니다.");
}

// 파일 존재 확인
if (file_exists($downfiledir.$downfile)) {
    $save_file = urlencode($save_file);
    Header("Content-Type: application/octet-stream");
    Header("Content-Disposition: attachment; filename=$downfile");
    header("Content-Transfer-Encoding: binary");
    Header("Content-Length: ".(string)(filesize($downfiledir.$downfile)));
    Header("Cache-Control: cache, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    $fp = fopen($downfiledir.$downfile, "rb");
    while (!feof($fp)) {
        echo fread($fp, 100*1024); // 100KB씩 읽기
    }
    fclose($fp);
    flush();
} else {
    echo "<script>alert('존재하지 않는 파일입니다.');history.back()</script>";
}
?>
```

### 3. 다운로드 호출 예시

```
http://www.dsp114.com/admin/MlangPrintAuto/download.php?downfile=namecard_front.jpg
```

**문제점**:
- ⚠️ 경로가 하드코딩됨 (`../../shop/data/`)
- ⚠️ 주문 번호 기반 다운로드 미지원 (파일명만 전달)
- ⚠️ 보안 취약 (Referer 체크만으로 인증)

---

## 🔄 마이그레이션 고려사항

### 1. 현재 시스템 (dsp1830.shop) vs 레거시 (dsp114.com)

| 항목 | DSP114 (레거시) | DSP1830 (신규) |
|------|----------------|----------------|
| **업로드 경로** | `/ImgFolder/_MlangPrintAuto_{제품}_index.php/{년}/{월일}/{IP}/{타임스탬프}/` | `/ImgFolder/_MlangPrintAuto_{제품}_index.php/{년}/{월일}/{IP}/{타임스탬프}/` |
| **주문 경로** | `/MlangOrder_PrintAuto/upload/{주문번호}/` | 장바구니와 동일 경로 사용 |
| **DB 컬럼** | `ImgFolder`, `ThingCate` | `ImgFolder`, `ThingCate`, `uploaded_files` (JSON) |
| **다운로드** | 하드코딩 경로 (`../../shop/data/`) | 자동 경로 감지 (3가지 패턴) |
| **PHP 버전** | PHP 5.2 (mysql_*) | PHP 7.4+ (mysqli_*) |

### 2. 호환성 유지 전략

#### ✅ 이미 구현됨 (dsp1830.shop)

1. **UploadPathHelper 클래스** ([includes/UploadPathHelper.php](../../../includes/UploadPathHelper.php))
   - 레거시 경로 구조 100% 재현
   - IPv6 안전 변환 (::1 → ipv6_1)
   - 자동 디렉토리 생성

2. **업로드 파일 JSON 저장** ([uploaded_files 컬럼](../02_ARCHITECTURE/DATABASE_SETUP.md))
   ```json
   [
     {
       "original_name": "book_001.jpg",
       "saved_name": "book_001.jpg",
       "path": "/var/www/html/ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0315/ipv6_1/1741998390/book_001.jpg",
       "size": 45678,
       "web_url": "/ImgFolder/_MlangPrintAuto_NameCard_index.php/2025/0315/ipv6_1/1741998390/book_001.jpg"
     }
   ]
   ```

3. **다운로드 경로 자동 감지** ([admin/mlangprintauto/download.php](../../../admin/mlangprintauto/download.php))
   - 패턴 1: `/ImgFolder/{ImgFolder}/{filename}`
   - 패턴 2: `/{ImgFolder}/{filename}`
   - 패턴 3: `/mlangorder_printauto/upload/{no}/{filename}` (레거시 호환)

#### 🔧 추가 구현 필요

1. **주문 확정 시 파일 복사 로직**
   ```php
   // OnlineOrder_unified.php에 추가
   if (!empty($item['uploaded_files'])) {
       $files = json_decode($item['uploaded_files'], true);
       $order_upload_dir = __DIR__ . "/upload/{$order_no}/";

       if (!file_exists($order_upload_dir)) {
           mkdir($order_upload_dir, 0755, true);
       }

       foreach ($files as $file) {
           if (file_exists($file['path'])) {
               $dest = $order_upload_dir . $file['saved_name'];
               copy($file['path'], $dest);
           }
       }
   }
   ```

2. **레거시 다운로드 경로 지원**
   ```php
   // download.php에 추가
   $legacy_path = "../../MlangOrder_PrintAuto/upload/{$no}/{$filename}";
   if (file_exists($legacy_path)) {
       downloadFile($legacy_path, $filename);
   }
   ```

### 3. 데이터 마이그레이션 체크리스트

- [ ] **기존 업로드 파일 복사**
  - [ ] `/ImgFolder/` 전체 디렉토리 rsync
  - [ ] `/MlangOrder_PrintAuto/upload/` 디렉토리 rsync
  - [ ] 권한 설정 확인 (755/644)

- [ ] **DB 데이터 마이그레이션**
  - [ ] `shop_temp` 테이블 동기화
  - [ ] `mlangorder_printauto` 테이블 동기화
  - [ ] `uploaded_files` JSON 생성 (레거시 데이터용)

- [ ] **경로 호환성 테스트**
  - [ ] 레거시 경로 파일 다운로드 테스트
  - [ ] 신규 경로 파일 업로드 테스트
  - [ ] 관리자 페이지 다운로드 테스트

---

## 📋 비교표: 레거시 vs 신규 시스템

### 업로드 시스템 비교

| 기능 | DSP114 (레거시) | DSP1830 (신규) | 호환성 |
|------|----------------|----------------|--------|
| 경로 구조 | `{년}/{월일}/{IP}/{타임스탬프}/` | 동일 | ✅ 100% |
| 제품별 디렉토리 | `_MlangPrintAuto_{제품}_index.php/` | 동일 | ✅ 100% |
| IP 처리 | IPv4만 지원 | IPv4/IPv6 (변환) | ✅ 향상 |
| 파일 메타데이터 | DB에 경로만 저장 | JSON으로 상세 저장 | ✅ 향상 |
| 디렉토리 권한 | 777 (보안 취약) | 755 (권장) | ⚠️ 변경 |

### 다운로드 시스템 비교

| 기능 | DSP114 (레거시) | DSP1830 (신규) | 호환성 |
|------|----------------|----------------|--------|
| 경로 감지 | 하드코딩 | 자동 감지 (3패턴) | ✅ 향상 |
| Referer 체크 | eregi (deprecated) | 제거 (세션 기반) | ⚠️ 변경 |
| ZIP 다운로드 | 미지원 | 지원 | ✅ 신규 |
| 개별 다운로드 | 지원 | 지원 | ✅ 100% |

### 데이터베이스 비교

| 항목 | DSP114 (레거시) | DSP1830 (신규) | 호환성 |
|------|----------------|----------------|--------|
| PHP 함수 | mysql_* | mysqli_* | ⚠️ 변경 |
| 문자 인코딩 | EUC-KR | UTF-8 | ⚠️ 변경 |
| Prepared Statements | 미사용 | 사용 (보안 강화) | ✅ 향상 |
| `shop_temp.uploaded_files` | 없음 | JSON 컬럼 | ✅ 신규 |
| `shop_temp.original_filename` | 없음 (dsp114에만 있음) | 추가됨 | ✅ 동기화 |

---

## 🚀 마이그레이션 실행 계획

### Phase 1: 파일 시스템 동기화

```bash
# FTP를 통한 파일 복사 (lftp 사용)
lftp -u duson1830,du1830 dsp114.com << EOF
mirror -c /www/ImgFolder /var/www/html/ImgFolder
mirror -c /www/MlangOrder_PrintAuto/upload /var/www/html/mlangorder_printauto/upload
quit
EOF

# 권한 설정
chmod -R 755 /var/www/html/ImgFolder
chmod -R 755 /var/www/html/mlangorder_printauto/upload
find /var/www/html/ImgFolder -type f -exec chmod 644 {} \;
find /var/www/html/mlangorder_printauto/upload -type f -exec chmod 644 {} \;
```

### Phase 2: 데이터베이스 동기화

```bash
# 원격 DB 덤프
mysqldump -h dsp114.com -u duson1830 -pdu1830 duson1830 \
  shop_temp mlangorder_printauto > /tmp/dsp114_migration.sql

# 로컬 DB 임포트
mysql -u dsp1830 -pds701018 dsp1830 < /tmp/dsp114_migration.sql

# uploaded_files JSON 생성 스크립트 실행
php /var/www/html/scripts/migrate_uploaded_files.php
```

### Phase 3: 호환성 검증

```bash
# 다운로드 테스트
curl -I "http://localhost/admin/mlangprintauto/download.php?no=103456&downfile=book_001.jpg"

# 업로드 테스트
curl -F "uploaded_files[]=@test.jpg" \
     -F "product_type=namecard" \
     http://localhost/mlangprintauto/namecard/add_to_basket.php
```

---

## 🔐 보안 고려사항

### 레거시 시스템의 보안 문제

1. **chmod 777 사용**
   ```php
   mkdir("$dir", 0755);
   exec("chmod 777 $dir");  // ⚠️ 모든 사용자가 쓰기 가능
   ```
   **해결**: 신규 시스템에서는 755 사용

2. **Referer 기반 인증**
   ```php
   if (!eregi($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'])) {
       Error("외부에서의 다운로드 접근이 차단되어 있습니다.");
   }
   ```
   **문제**: Referer 헤더는 쉽게 위조 가능
   **해결**: 세션 기반 인증으로 변경

3. **SQL Injection 취약점**
   ```php
   $dbinsert = "INSERT INTO MlangOrder_PrintAuto VALUES('$new_no', '$Type', ...)";
   mysql_query($dbinsert, $db);  // ⚠️ Prepared Statement 미사용
   ```
   **해결**: 신규 시스템은 mysqli_prepare() 사용

4. **XSS 취약점**
   ```php
   echo "<script>alert('$message');history.back()</script>";  // ⚠️ 이스케이프 없음
   ```
   **해결**: htmlspecialchars() 사용

---

## 📖 참고 문서

- [UploadPathHelper.php](../../../includes/UploadPathHelper.php) - 업로드 경로 관리 클래스
- [upload-system-complete.md](../../../.kiro/steering/upload-system-complete.md) - 신규 업로드 시스템 가이드
- [download.php](../../../admin/mlangprintauto/download.php) - 신규 다운로드 시스템
- [download_all.php](../../../admin/mlangprintauto/download_all.php) - ZIP 일괄 다운로드

---

**작성자**: Claude Code
**검토**: 마이그레이션 팀
**버전**: 1.0
**최종 업데이트**: 2025-11-19
