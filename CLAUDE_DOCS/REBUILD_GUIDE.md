# 두손기획 인쇄 시스템 재구축 가이드

> **문서 버전**: 1.0
> **최종 수정**: 2026-01-18
> **대상**: 개발자, 시스템 관리자
> **예상 소요 시간**: 14-20시간

---

## 목차

1. [개요](#1-개요)
2. [시스템 요구사항](#2-시스템-요구사항)
3. [문서 및 소스 인벤토리](#3-문서-및-소스-인벤토리)
4. [Phase 1: 환경 설정](#4-phase-1-환경-설정)
5. [Phase 2: 데이터베이스 구축](#5-phase-2-데이터베이스-구축)
6. [Phase 3: 코어 시스템 배포](#6-phase-3-코어-시스템-배포)
7. [Phase 4: 제품 페이지 배포](#7-phase-4-제품-페이지-배포)
8. [Phase 5: 주문 시스템 배포](#8-phase-5-주문-시스템-배포)
9. [Phase 6: 관리자 시스템 배포](#9-phase-6-관리자-시스템-배포)
10. [Phase 7: 테스트 및 검증](#10-phase-7-테스트-및-검증)
11. [Phase 8: 프로덕션 배포](#11-phase-8-프로덕션-배포)
12. [트러블슈팅](#12-트러블슈팅)
13. [부록](#13-부록)

---

## 1. 개요

### 1.1 시스템 소개

두손기획 인쇄 시스템은 PHP 7.4 기반의 B2B 인쇄 주문 관리 시스템입니다.

**핵심 기능**:
- 9개 인쇄 제품 주문 (전단지, 스티커, 명함, 봉투 등)
- 실시간 가격 계산
- 파일 업로드 및 교정
- 주문 관리 및 배송 추적
- 견적서 생성 및 발송

### 1.2 아키텍처 개요

```
┌─────────────────────────────────────────────────────────────┐
│                        클라이언트                            │
│                   (웹 브라우저/모바일)                        │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     Apache 2.4+ / PHP 7.4+                  │
├─────────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │  제품 페이지  │  │  주문 처리   │  │  관리자 패널  │      │
│  │ /mlangprint  │  │ /mlangorder  │  │ /mlang_admin │      │
│  └──────────────┘  └──────────────┘  └──────────────┘      │
│                              │                              │
│  ┌──────────────────────────────────────────────────┐      │
│  │              SSOT 코어 (/includes/)              │      │
│  │  QuantityFormatter │ ProductSpecFormatter │ ...  │      │
│  └──────────────────────────────────────────────────┘      │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                    MySQL 5.7+ (utf8mb4)                     │
│                      28개 테이블                             │
└─────────────────────────────────────────────────────────────┘
```

### 1.3 재구축 난이도

| 항목 | 난이도 | 비고 |
|------|--------|------|
| 환경 설정 | ⭐ 쉬움 | 표준 LAMP 스택 |
| DB 구축 | ⭐ 쉬움 | 스키마 파일 완비 |
| 코어 시스템 | ⭐⭐ 보통 | SSOT 클래스 복사 |
| 제품 페이지 | ⭐⭐ 보통 | 9개 제품 동일 구조 |
| 주문 시스템 | ⭐⭐ 보통 | 통합 처리 로직 |
| 관리자 | ⭐⭐ 보통 | 인증 시스템 포함 |
| 테스트 | ⭐⭐⭐ 어려움 | E2E 테스트 필요 |

---

## 2. 시스템 요구사항

### 2.1 서버 요구사항

| 구성요소 | 최소 버전 | 권장 버전 | 비고 |
|----------|-----------|-----------|------|
| OS | Ubuntu 20.04 | Ubuntu 22.04 | 또는 CentOS 7+ |
| Apache | 2.4 | 2.4.54+ | mod_rewrite 필수 |
| PHP | 7.4 | 8.1 | 확장 모듈 참조 |
| MySQL | 5.7 | 8.0 | utf8mb4 필수 |
| Node.js | 14 | 18+ | 테스트용 |

### 2.2 PHP 확장 모듈

```bash
# 필수 모듈
php-mysql      # MySQL 연결
php-gd         # 이미지 처리
php-mbstring   # 다국어 문자열
php-xml        # XML 처리
php-curl       # HTTP 클라이언트
php-zip        # 압축 파일

# 설치 명령 (Ubuntu)
sudo apt install php7.4-mysql php7.4-gd php7.4-mbstring \
                 php7.4-xml php7.4-curl php7.4-zip
```

### 2.3 디스크 요구사항

| 항목 | 크기 | 비고 |
|------|------|------|
| 소스 코드 | ~50MB | PHP, JS, CSS |
| Composer 패키지 | ~200MB | vendor/ |
| 업로드 공간 | 10GB+ | 고객 파일 |
| DB 공간 | 1GB+ | 주문 데이터 |
| **총계** | **최소 15GB** | 여유 포함 |

### 2.4 네트워크 요구사항

| 포트 | 용도 | 필수 |
|------|------|------|
| 80 | HTTP | ✅ |
| 443 | HTTPS | ✅ (프로덕션) |
| 3306 | MySQL | 내부만 |
| 21 | FTP | 선택 (배포용) |

---

## 3. 문서 및 소스 인벤토리

### 3.1 핵심 문서 목록

재구축 시 반드시 참조해야 할 문서들입니다.

| 문서 | 경로 | 용도 | 중요도 |
|------|------|------|--------|
| 프로젝트 규칙 | `/CLAUDE.md` | 코딩 규칙, 품목 매핑 | 🔴 필수 |
| 마스터 명세서 | `/CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md` | 시스템 전체 설계 | 🔴 필수 |
| DB 스키마 | `/CLAUDE_DOCS/DB_SCHEMA.md` | 테이블 정의 28개 | 🔴 필수 |
| API 명세서 | `/CLAUDE_DOCS/API_SPEC.md` | API 엔드포인트 | 🟡 참조 |
| 데이터 흐름 | `/CLAUDE_DOCS/DATA_LINEAGE.md` | 데이터 플로우 | 🟡 참조 |
| 폴더 구조 | `/CLAUDE_DOCS/DIRECTORY_STRUCTURE.md` | 디렉토리 설명 | 🟡 참조 |
| 컴포넌트 참조 | `/CLAUDE_DOCS/COMPONENT_REFERENCE.md` | 클래스 사용법 | 🟡 참조 |
| 설치 가이드 | `/install/INSTALLATION_GUIDE.md` | 설치 절차 | 🟢 선택 |

### 3.2 소스 디렉토리 구조

```
/var/www/html/                      # Document Root
│
├── 📄 핵심 설정 파일
│   ├── db.php                      # DB 연결 & 환경 감지
│   ├── config.env.php              # 환경 설정
│   ├── composer.json               # PHP 의존성
│   ├── package.json                # Node 의존성
│   └── .htaccess                   # URL 재작성
│
├── 📄 메인 페이지
│   ├── index.php                   # 메인 진입점
│   ├── main.php                    # 메인 레이아웃
│   ├── header.php                  # 공통 헤더
│   ├── footer.php                  # 공통 푸터
│   ├── left.php                    # 왼쪽 사이드바
│   └── right.php                   # 오른쪽 사이드바
│
├── 📁 includes/                    # SSOT 코어 (28개 파일)
│   ├── QuantityFormatter.php       # 수량 포맷팅 ⭐
│   ├── ProductSpecFormatter.php    # 사양 표시 ⭐
│   ├── PriceCalculationService.php # 가격 계산 ⭐
│   ├── DataAdapter.php             # 데이터 변환
│   ├── SpecDisplayService.php      # 사양 조회
│   ├── OrderDataService.php        # 주문 데이터
│   ├── UploadPathHelper.php        # 업로드 경로
│   ├── ImagePathResolver.php       # 이미지 경로
│   ├── StandardUploadHandler.php   # 파일 업로드
│   ├── auth.php                    # 인증
│   └── ... (18개 추가 파일)
│
├── 📁 lib/
│   └── core_print_logic.php        # 코어 로직 파사드
│
├── 📁 mlangprintauto/              # 제품 페이지 (9개 제품)
│   ├── inserted/                   # 전단지
│   ├── sticker_new/                # 스티커
│   ├── namecard/                   # 명함
│   ├── envelope/                   # 봉투
│   ├── littleprint/                # 포스터
│   ├── cadarok/                    # 카다록
│   ├── ncrflambeau/                # NCR양식지
│   ├── merchandisebond/            # 상품권
│   ├── msticker/                   # 자석스티커
│   ├── cart.php                    # 장바구니
│   └── shop/                       # shop_temp 처리
│
├── 📁 mlangorder_printauto/        # 주문 처리
│   ├── ProcessOrder_unified.php    # 주문 처리 통합
│   ├── OrderFormOrderTree.php      # 주문서 폼
│   └── OrderComplete_universal.php # 주문 완료
│
├── 📁 mlang_admin/                 # 관리자 페이지
│   ├── index.php                   # 대시보드
│   ├── login.php                   # 로그인
│   ├── order_list.php              # 주문 목록
│   └── ...
│
├── 📁 admin/                       # 관리자 인증
│   └── includes/
│       └── admin_auth.php          # 인증 미들웨어
│
├── 📁 css/                         # 전역 스타일시트
├── 📁 js/                          # 전역 자바스크립트
├── 📁 images/                      # 정적 이미지
├── 📁 upload/                      # 업로드 파일
│
├── 📁 install/                     # 설치 관련
│   ├── sql/schema.sql              # DB 스키마
│   └── INSTALLATION_GUIDE.md       # 설치 가이드
│
├── 📁 database/migrations/         # DB 마이그레이션
│   ├── grand_design/
│   └── phase_a_custom_products/
│
├── 📁 tests/                       # 테스트
│   ├── page-loading.group-a.spec.ts
│   ├── basic-price.group-b.spec.ts
│   └── ...
│
└── 📁 CLAUDE_DOCS/                 # 기술 문서
    ├── Duson_System_Master_Spec_v1.0.md
    ├── DB_SCHEMA.md
    ├── API_SPEC.md
    └── ...
```

### 3.3 9개 제품 매핑 (절대 규칙)

> ⚠️ **경고**: 아래 폴더명은 절대 변경 금지. 코드 전체에 하드코딩됨.

| # | 제품명 (한글) | 폴더명 | DB 테이블 | 단위 |
|---|--------------|--------|-----------|------|
| 1 | 전단지 | `inserted` | mlangprintauto_inserted | 연 (R) |
| 2 | 스티커 | `sticker_new` | mlangprintauto_sticker | 매 (S) |
| 3 | 자석스티커 | `msticker` | mlangprintauto_msticker | 매 (S) |
| 4 | 명함 | `namecard` | mlangprintauto_namecard | 매 (S) |
| 5 | 봉투 | `envelope` | mlangprintauto_envelope | 매 (S) |
| 6 | 포스터 | `littleprint` | mlangprintauto_littleprint | 매 (S) |
| 7 | 상품권 | `merchandisebond` | mlangprintauto_merchandisebond | 매 (S) |
| 8 | 카다록 | `cadarok` | mlangprintauto_cadarok | 부 (B) |
| 9 | NCR양식지 | `ncrflambeau` | mlangprintauto_ncrflambeau | 권 (V) |

### 3.4 단위 코드 체계

| 코드 | 단위명 | 적용 제품 | 표시 예시 |
|------|--------|-----------|-----------|
| R | 연 (Ream) | inserted | "0.5연 (2,000매)" |
| S | 매 (Sheet) | sticker_new, namecard 등 | "1,000매" |
| B | 부 (Bundle) | cadarok | "10부" |
| V | 권 (Volume) | ncrflambeau | "5권" |

---

## 4. Phase 1: 환경 설정

**예상 소요: 1시간**

### 4.1 서버 설치 (Ubuntu)

```bash
# 1. 시스템 업데이트
sudo apt update && sudo apt upgrade -y

# 2. Apache 설치
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2

# 3. PHP 7.4 설치
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php7.4 php7.4-mysql php7.4-gd php7.4-mbstring \
                 php7.4-xml php7.4-curl php7.4-zip libapache2-mod-php7.4 -y

# 4. MySQL 설치
sudo apt install mysql-server -y
sudo mysql_secure_installation

# 5. Apache 모듈 활성화
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### 4.2 가상 호스트 설정

```bash
# /etc/apache2/sites-available/duson.conf
sudo nano /etc/apache2/sites-available/duson.conf
```

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/duson_error.log
    CustomLog ${APACHE_LOG_DIR}/duson_access.log combined
</VirtualHost>
```

```bash
# 사이트 활성화
sudo a2ensite duson.conf
sudo systemctl reload apache2
```

### 4.3 디렉토리 권한 설정

```bash
# Document Root 소유권
sudo chown -R www-data:www-data /var/www/html

# 기본 권한
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;

# 업로드 디렉토리 쓰기 권한
sudo chmod -R 775 /var/www/html/upload
sudo chmod -R 775 /var/www/html/ImgFolder
```

### 4.4 PHP 설정 조정

```bash
# php.ini 수정
sudo nano /etc/php/7.4/apache2/php.ini
```

```ini
; 업로드 크기 증가
upload_max_filesize = 50M
post_max_size = 50M

; 메모리 및 실행 시간
memory_limit = 256M
max_execution_time = 300

; 한글 처리
default_charset = "UTF-8"

; 세션 설정
session.gc_maxlifetime = 28800
```

```bash
sudo systemctl restart apache2
```

### 4.5 검증 체크리스트

```bash
# Apache 상태 확인
sudo systemctl status apache2

# PHP 버전 확인
php -v

# MySQL 상태 확인
sudo systemctl status mysql

# PHP 모듈 확인
php -m | grep -E "(mysql|gd|mbstring|xml|curl|zip)"
```

✅ **Phase 1 완료 기준**:
- `http://localhost/` 접속 시 Apache 기본 페이지 표시
- `php -v` 출력에서 7.4+ 버전 확인
- MySQL 접속 가능

---

## 5. Phase 2: 데이터베이스 구축

**예상 소요: 30분**

### 5.1 데이터베이스 생성

```bash
# MySQL 접속
sudo mysql -u root -p
```

```sql
-- 데이터베이스 생성
CREATE DATABASE dsp1830 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 사용자 생성 및 권한 부여
CREATE USER 'dsp1830'@'localhost' IDENTIFIED BY 'ds701018';
GRANT ALL PRIVILEGES ON dsp1830.* TO 'dsp1830'@'localhost';
FLUSH PRIVILEGES;

-- 확인
SHOW DATABASES;
EXIT;
```

### 5.2 스키마 적용

```bash
# 메인 스키마 적용
mysql -u dsp1830 -p dsp1830 < /var/www/html/install/sql/schema.sql

# 마이그레이션 적용 (순서대로)
mysql -u dsp1830 -p dsp1830 < /var/www/html/database/migrations/grand_design/01_schema.sql
mysql -u dsp1830 -p dsp1830 < /var/www/html/database/migrations/phase_a_custom_products/02_alter_*.sql
```

### 5.3 테이블 확인

```bash
mysql -u dsp1830 -p dsp1830 -e "SHOW TABLES;"
```

**예상 테이블 목록 (28개)**:

| 카테고리 | 테이블명 |
|----------|----------|
| 주문 | `mlangorder_printauto`, `shop_temp` |
| 가격 | `mlangprintauto_inserted`, `mlangprintauto_sticker`, `mlangprintauto_namecard`, `mlangprintauto_envelope`, `mlangprintauto_littleprint`, `mlangprintauto_cadarok`, `mlangprintauto_ncrflambeau`, `mlangprintauto_merchandisebond`, `mlangprintauto_msticker` |
| 카테고리 | `mlangprintauto_transactioncate` |
| 사용자 | `member_user`, `admin_users` |
| 견적 | `quotations`, `quotation_items` |
| 옵션 | `additional_options_config`, `mlangprintauto_leaflet_fold` |
| 기타 | (시스템 테이블들) |

### 5.4 초기 데이터 삽입

```sql
-- 관리자 계정 생성
INSERT INTO admin_users (username, password_hash, created_at)
VALUES ('admin', '$2y$10$...', NOW());

-- 카테고리 기본 데이터 (필요시)
-- 가격 테이블 데이터 (별도 SQL 파일에서 import)
```

### 5.5 검증

```bash
# 테이블 수 확인
mysql -u dsp1830 -p dsp1830 -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'dsp1830';"

# 주요 테이블 구조 확인
mysql -u dsp1830 -p dsp1830 -e "DESCRIBE mlangorder_printauto;"
```

✅ **Phase 2 완료 기준**:
- 28개 테이블 생성 확인
- `dsp1830` 사용자로 접속 가능

---

## 6. Phase 3: 코어 시스템 배포

**예상 소요: 2시간**

### 6.1 설정 파일 배포

#### db.php (DB 연결)

```php
<?php
/**
 * 데이터베이스 연결 설정
 * 환경 자동 감지: localhost / dsp1830.shop
 */

// 환경 감지
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

if (strpos($server_name, 'localhost') !== false) {
    // 로컬 개발 환경
    $db_host = 'localhost';
    $db_user = 'dsp1830';
    $db_pass = 'ds701018';
    $db_name = 'dsp1830';
    $admin_url = 'http://localhost';
} else {
    // 프로덕션 환경
    $db_host = 'localhost';
    $db_user = 'dsp1830';
    $db_pass = 'ds701018';
    $db_name = 'dsp1830';
    $admin_url = 'http://' . $server_name;
}

// MySQLi 연결
$db = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$db) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// UTF-8 설정
mysqli_set_charset($db, 'utf8mb4');

// 레거시 호환 (일부 파일에서 $conn 사용)
$conn = $db;
```

#### config.env.php (환경 설정)

```php
<?php
/**
 * 환경 설정 파일
 */

// 타임존
date_default_timezone_set('Asia/Seoul');

// 에러 리포팅 (개발 환경)
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 세션 설정
ini_set('session.gc_maxlifetime', 28800);

// 업로드 경로
define('UPLOAD_BASE_PATH', '/var/www/html/upload');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
```

### 6.2 SSOT 코어 파일 배포

`/includes/` 디렉토리의 28개 파일을 복사합니다.

**필수 파일 (우선순위 순)**:

```bash
# 1. 핵심 SSOT 클래스
/includes/QuantityFormatter.php       # 수량 포맷팅
/includes/ProductSpecFormatter.php    # 사양 표시
/includes/PriceCalculationService.php # 가격 계산
/includes/DataAdapter.php             # 데이터 변환
/includes/SpecDisplayService.php      # 사양 조회

# 2. 서비스 클래스
/includes/OrderDataService.php        # 주문 데이터
/includes/UploadPathHelper.php        # 업로드 경로
/includes/ImagePathResolver.php       # 이미지 경로
/includes/StandardUploadHandler.php   # 파일 업로드

# 3. 인증
/includes/auth.php                    # 인증 함수
/includes/auth_functions.php          # 인증 헬퍼

# 4. 기타 유틸리티
/includes/GalleryImageComponent.php
/includes/AdditionalOptionsComponent.php
/includes/EmailService.php
/includes/ExcelExport.php
# ... (나머지 파일들)
```

### 6.3 라이브러리 파일 배포

```bash
# 코어 로직 파사드
/lib/core_print_logic.php
```

### 6.4 Composer 의존성 설치

```bash
cd /var/www/html

# composer.json 확인
cat composer.json

# 의존성 설치
composer install --no-dev --optimize-autoloader
```

**composer.json 내용**:

```json
{
    "require": {
        "tecnickcom/tcpdf": "^6.10",
        "dompdf/dompdf": "^3.1",
        "mpdf/mpdf": "^8.2",
        "phpmailer/phpmailer": "^7.0"
    }
}
```

### 6.5 검증

```php
<?php
// /test_core.php (테스트 후 삭제)
require_once 'db.php';
require_once 'includes/QuantityFormatter.php';

// DB 연결 테스트
echo "DB 연결: " . ($db ? "성공" : "실패") . "\n";

// QuantityFormatter 테스트
$result = QuantityFormatter::format(0.5, 'R', 2000);
echo "QuantityFormatter: " . $result . "\n";
// 예상 출력: "0.5연 (2,000매)"
```

✅ **Phase 3 완료 기준**:
- `db.php` 로드 시 DB 연결 성공
- `QuantityFormatter::format()` 정상 작동
- `vendor/` 디렉토리에 패키지 설치 완료

---

## 7. Phase 4: 제품 페이지 배포

**예상 소요: 3시간**

### 7.1 제품 디렉토리 구조

각 제품은 동일한 구조를 따릅니다:

```
/mlangprintauto/[product_name]/
├── index.php                 # 제품 페이지 (메인)
├── calculate_price_ajax.php  # 가격 계산 API
├── add_to_basket.php         # 장바구니 추가 API
├── inc.php                   # 제품 설정
├── upload/                   # 업로드 폴더
├── css/                      # 제품별 CSS
│   └── style.css
├── js/                       # 제품별 JS
│   └── script.js
└── config/                   # 설정 파일
```

### 7.2 제품별 배포 체크리스트

| 제품 | 폴더 | index.php | price API | basket API | 확인 |
|------|------|-----------|-----------|------------|------|
| 전단지 | inserted | ☐ | ☐ | ☐ | ☐ |
| 스티커 | sticker_new | ☐ | ☐ | ☐ | ☐ |
| 명함 | namecard | ☐ | ☐ | ☐ | ☐ |
| 봉투 | envelope | ☐ | ☐ | ☐ | ☐ |
| 포스터 | littleprint | ☐ | ☐ | ☐ | ☐ |
| 카다록 | cadarok | ☐ | ☐ | ☐ | ☐ |
| NCR양식지 | ncrflambeau | ☐ | ☐ | ☐ | ☐ |
| 상품권 | merchandisebond | ☐ | ☐ | ☐ | ☐ |
| 자석스티커 | msticker | ☐ | ☐ | ☐ | ☐ |

### 7.3 장바구니 배포

```bash
/mlangprintauto/cart.php          # 장바구니 페이지
/mlangprintauto/shop/
├── shop_temp_helper.php          # shop_temp 처리
└── update_cart.php               # 장바구니 업데이트
```

### 7.4 가격 계산 API 검증

```bash
# 전단지 가격 계산 테스트
curl -X POST "http://localhost/mlangprintauto/inserted/calculate_price_ajax.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "papersize=A4&quantity=0.5&sides=2&paper=아트지"

# 예상 응답
# {"success":true,"price":12000,"price_formatted":"12,000원"}
```

### 7.5 전역 CSS/JS 배포

```bash
/css/
├── common-styles.css        # 공통 스타일
├── cart.css                 # 장바구니
├── product-page.css         # 제품 페이지
└── responsive.css           # 반응형

/js/
├── common.js                # 공통 함수
├── cart.js                  # 장바구니
└── price-calculator.js      # 가격 계산
```

✅ **Phase 4 완료 기준**:
- 9개 제품 페이지 모두 접속 가능
- 가격 계산 API 정상 응답
- 장바구니 추가 기능 작동

---

## 8. Phase 5: 주문 시스템 배포

**예상 소요: 2시간**

### 8.1 주문 처리 파일

```bash
/mlangorder_printauto/
├── ProcessOrder_unified.php      # 주문 처리 (핵심)
├── OrderFormOrderTree.php        # 주문서 폼
├── OrderComplete_universal.php   # 주문 완료
├── order_confirm.php             # 주문 확인
└── includes/
    └── order_helpers.php         # 주문 헬퍼 함수
```

### 8.2 주문 처리 흐름

```
장바구니 (cart.php)
    │
    ▼ [주문하기 버튼]
주문서 작성 (OrderFormOrderTree.php)
    │
    ▼ [결제/주문 버튼]
주문 처리 (ProcessOrder_unified.php)
    │ - shop_temp → mlangorder_printauto 이동
    │ - 주문번호 생성
    │ - 파일 경로 업데이트
    ▼
주문 완료 (OrderComplete_universal.php)
    │
    ▼ [이메일 발송, 알림톡]
```

### 8.3 주문번호 생성 규칙

```php
// 형식: YYYYMMDD + 4자리 순번
// 예: 2026011800001

function generateOrderNumber($db) {
    $today = date('Ymd');
    $query = "SELECT MAX(order_idx) as max_idx FROM mlangorder_printauto
              WHERE order_idx LIKE '{$today}%'";
    $result = mysqli_query($db, $query);
    $row = mysqli_fetch_assoc($result);

    if ($row['max_idx']) {
        $last_num = intval(substr($row['max_idx'], -5));
        $new_num = $last_num + 1;
    } else {
        $new_num = 1;
    }

    return $today . str_pad($new_num, 5, '0', STR_PAD_LEFT);
}
```

### 8.4 검증

```bash
# 주문 처리 테스트 (장바구니에 상품 추가 후)
1. 장바구니 → 주문하기
2. 주문서 작성 → 주문 완료
3. mlangorder_printauto 테이블 확인

mysql -u dsp1830 -p dsp1830 -e \
  "SELECT order_idx, memname, product_type, order_status
   FROM mlangorder_printauto ORDER BY order_idx DESC LIMIT 5;"
```

✅ **Phase 5 완료 기준**:
- 주문 프로세스 전체 흐름 작동
- DB에 주문 데이터 저장 확인
- 주문 완료 페이지 표시

---

## 9. Phase 6: 관리자 시스템 배포

**예상 소요: 1.5시간**

### 9.1 관리자 파일 구조

```bash
/mlang_admin/                    # 관리자 메인
├── index.php                    # 대시보드
├── login.php                    # 로그인 (단순)
├── order_list.php               # 주문 목록
├── order_view.php               # 주문 상세
├── order_edit.php               # 주문 수정
├── price_manage.php             # 가격 관리
├── product_list.php             # 제품 관리
├── category_manage.php          # 카테고리 관리
└── includes/
    └── admin_header.php         # 관리자 헤더

/admin/                          # 인증 시스템
├── mlangprintauto/
│   └── login.php                # 보안 로그인
└── includes/
    └── admin_auth.php           # 인증 미들웨어
```

### 9.2 관리자 인증 설정

```php
// /admin/includes/admin_auth.php
// 기본 자격증명
return [
    'username' => 'admin',
    'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
    'needs_change' => false
];
```

### 9.3 인증 흐름

```
로그인 페이지 (/admin/mlangprintauto/login.php)
    │
    ▼ [ID/PW 입력]
인증 처리 (admin_auth.php::adminLogin)
    │ - 브루트포스 체크
    │ - 비밀번호 검증
    │ - 세션 생성
    ▼
관리자 페이지 (/mlang_admin/)
    │ - requireAdminAuth() 체크
    │ - 8시간 세션 유지
    ▼
로그아웃 (adminLogout)
```

### 9.4 검증

```bash
# 관리자 로그인 테스트
1. http://localhost/admin/mlangprintauto/login.php 접속
2. admin / admin123 입력
3. 관리자 대시보드 리다이렉트 확인

# 주문 목록 확인
http://localhost/mlang_admin/order_list.php
```

✅ **Phase 6 완료 기준**:
- 관리자 로그인 성공
- 주문 목록 페이지 정상 표시
- 주문 상세 조회 가능

---

## 10. Phase 7: 테스트 및 검증

**예상 소요: 3시간**

### 10.1 Node.js 테스트 환경 설정

```bash
cd /var/www/html

# Node.js 의존성 설치
npm install

# Playwright 브라우저 설치
npx playwright install chromium
```

### 10.2 테스트 실행

```bash
# 전체 테스트 실행
npm test

# 그룹별 테스트
npx playwright test --grep "@group-a"  # 페이지 로딩
npx playwright test --grep "@group-b"  # 가격 계산
npx playwright test --grep "@group-c"  # 파일 업로드
npx playwright test --grep "@group-d"  # E2E 시나리오
```

### 10.3 수동 테스트 체크리스트

#### 제품 페이지 테스트

| 테스트 항목 | 전단지 | 스티커 | 명함 | 봉투 | 포스터 | 카다록 | NCR | 상품권 | 자석 |
|------------|--------|--------|------|------|--------|--------|-----|--------|------|
| 페이지 로딩 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| 옵션 선택 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| 가격 계산 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| 파일 업로드 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |
| 장바구니 추가 | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ | ☐ |

#### 주문 프로세스 테스트

| 단계 | 테스트 항목 | 확인 |
|------|-------------|------|
| 1 | 장바구니 표시 | ☐ |
| 2 | 수량/사양 정확성 | ☐ |
| 3 | 주문서 폼 작성 | ☐ |
| 4 | 주문 처리 완료 | ☐ |
| 5 | DB 저장 확인 | ☐ |
| 6 | 주문 완료 페이지 | ☐ |

#### 관리자 기능 테스트

| 기능 | 테스트 항목 | 확인 |
|------|-------------|------|
| 로그인 | admin/admin123 | ☐ |
| 주문 목록 | 목록 표시 | ☐ |
| 주문 상세 | 상세 조회 | ☐ |
| 상태 변경 | 주문 상태 업데이트 | ☐ |
| 로그아웃 | 세션 종료 | ☐ |

### 10.4 성능 테스트

```bash
# Apache Bench 테스트
ab -n 100 -c 10 http://localhost/mlangprintauto/namecard/

# 예상 결과
# Requests per second: 50+ req/sec
# Time per request: <200ms
```

✅ **Phase 7 완료 기준**:
- Playwright 테스트 전체 통과
- 9개 제품 수동 테스트 완료
- 주문 프로세스 E2E 검증 완료

---

## 11. Phase 8: 프로덕션 배포

**예상 소요: 2시간**

### 11.1 프로덕션 체크리스트

#### 보안 설정

```bash
# 1. 에러 표시 비활성화
# php.ini
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# 2. 디렉토리 리스팅 비활성화
# .htaccess
Options -Indexes

# 3. 민감한 파일 접근 차단
<FilesMatch "\.(env|json|md|sql)$">
    Require all denied
</FilesMatch>
```

#### SSL 인증서

```bash
# Let's Encrypt 설치
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

#### 백업 설정

```bash
# 일일 DB 백업 스크립트
#!/bin/bash
DATE=$(date +%Y%m%d)
mysqldump -u dsp1830 -pds701018 dsp1830 > /backup/db_$DATE.sql
find /backup -name "db_*.sql" -mtime +7 -delete
```

### 11.2 FTP 배포 (기존 서버)

```bash
# 파일 업로드
curl -T "file.php" -u "dsp1830:ds701018" \
  "ftp://dsp1830.shop/public_html/file.php"

# 디렉토리 업로드 (ncftp 사용)
ncftpput -R -u dsp1830 -p ds701018 dsp1830.shop /public_html /var/www/html/*
```

### 11.3 배포 후 검증

```bash
# 1. 사이트 접속 확인
curl -I https://yourdomain.com

# 2. DB 연결 확인
curl https://yourdomain.com/api/health_check.php

# 3. 제품 페이지 확인
curl -s https://yourdomain.com/mlangprintauto/namecard/ | head -50
```

✅ **Phase 8 완료 기준**:
- HTTPS 접속 가능
- 모든 기능 정상 작동
- 백업 스케줄 설정 완료

---

## 12. 트러블슈팅

### 12.1 일반적인 문제

#### DB 연결 실패

```
Error: Database connection failed
```

**해결**:
```bash
# MySQL 서비스 확인
sudo systemctl status mysql

# 사용자 권한 확인
mysql -u root -p -e "SELECT user, host FROM mysql.user WHERE user='dsp1830';"

# 권한 재부여
GRANT ALL PRIVILEGES ON dsp1830.* TO 'dsp1830'@'localhost';
FLUSH PRIVILEGES;
```

#### 파일 권한 오류

```
Error: Permission denied on upload
```

**해결**:
```bash
sudo chown -R www-data:www-data /var/www/html/upload
sudo chmod -R 775 /var/www/html/upload
```

#### 한글 깨짐

```
문자가 ??? 또는 깨진 문자로 표시
```

**해결**:
```php
// db.php에 추가
mysqli_set_charset($db, 'utf8mb4');

// HTML에 추가
<meta charset="UTF-8">
```

### 12.2 가격 계산 오류

#### 소수점 반올림

```
0.5연이 1연으로 표시됨
```

**해결**:
```php
// ❌ 잘못된 방법
number_format($value)  // 0.5 → 1

// ✅ 올바른 방법
number_format($value, 1)  // 0.5 → 0.5
```

#### bind_param 개수 불일치

```
Warning: mysqli_stmt_bind_param(): Number of elements in type definition string doesn't match number of bind variables
```

**해결**: 3번 검증 규칙 적용
```php
$placeholder_count = substr_count($query, '?');
$type_count = strlen($type_string);
$var_count = count($params);
// 3개 값이 모두 같아야 함
```

### 12.3 세션 문제

#### 로그인 유지 안됨

```
로그인 후 페이지 이동 시 로그아웃됨
```

**해결**:
```php
// 모든 페이지 상단에 session_start() 확인
session_start();

// 세션 저장 경로 확인
session_save_path('/var/lib/php/sessions');

// 세션 디렉토리 권한
sudo chmod 777 /var/lib/php/sessions
```

---

## 13. 부록

### 13.1 유용한 SQL 쿼리

```sql
-- 최근 주문 조회
SELECT order_idx, memname, product_type, order_status, reg_date
FROM mlangorder_printauto
ORDER BY order_idx DESC
LIMIT 20;

-- 제품별 주문 통계
SELECT product_type, COUNT(*) as count, SUM(total_price) as total
FROM mlangorder_printauto
WHERE YEAR(reg_date) = 2026
GROUP BY product_type;

-- 장바구니 데이터 확인
SELECT * FROM shop_temp
WHERE session_id = 'current_session_id';
```

### 13.2 파일 동기화 명령

```bash
# rsync로 서버 간 동기화
rsync -avz --exclude='upload/*' --exclude='vendor/*' \
  /var/www/html/ user@remote:/var/www/html/

# 특정 디렉토리만 동기화
rsync -avz /var/www/html/includes/ user@remote:/var/www/html/includes/
```

### 13.3 로그 확인

```bash
# Apache 에러 로그
tail -f /var/log/apache2/error.log

# PHP 에러 로그
tail -f /var/log/php/error.log

# MySQL 쿼리 로그
tail -f /var/log/mysql/query.log
```

### 13.4 관련 문서 링크

| 문서 | 경로 |
|------|------|
| 마스터 명세서 | `/CLAUDE_DOCS/Duson_System_Master_Spec_v1.0.md` |
| DB 스키마 | `/CLAUDE_DOCS/DB_SCHEMA.md` |
| API 명세서 | `/CLAUDE_DOCS/API_SPEC.md` |
| 데이터 흐름 | `/CLAUDE_DOCS/DATA_LINEAGE.md` |
| 컴포넌트 참조 | `/CLAUDE_DOCS/COMPONENT_REFERENCE.md` |
| 설치 가이드 | `/install/INSTALLATION_GUIDE.md` |

---

## 변경 이력

| 버전 | 날짜 | 변경 내용 |
|------|------|-----------|
| 1.0 | 2026-01-18 | 초기 작성 |

---

*이 문서는 두손기획 인쇄 시스템의 완전한 재구축을 위한 가이드입니다.*
*문의: 시스템 관리자*
