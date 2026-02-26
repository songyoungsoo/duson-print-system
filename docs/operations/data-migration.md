## 🔄 데이터 마이그레이션 (dsp114.com → 2개 타겟 서버)

### 📋 빠른 참조 (Quick Reference)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    dsp114.com (소스 - 폐쇄 예정)                             │
│                    PHP 5.2 | MySQL | EUC-KR                                 │
│                    http://dsp114.com/export_api.php                         │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
              ┌─────────────────────┴─────────────────────┐
              ▼                                           ▼
┌──────────────────────────────┐       ┌──────────────────────────────────────┐
│  🏢 dsp114.co.kr             │       │  🏠 dsp1830.ipdisk.co.kr:8000        │
│     (임대 서버 - 운영)        │       │     (개인 NAS - 전체 백업)            │
├──────────────────────────────┤       ├──────────────────────────────────────┤
│ 용량: 제한적 (할당량)         │       │ 용량: 750GB+ (충분)                   │
│ PHP: 7.x | MySQL: 5.7+       │       │ PHP: 7.3.17 | MySQL: 5.6.30          │
├──────────────────────────────┤       ├──────────────────────────────────────┤
│ 📁 파일 필터:                 │       │ 📁 파일 필터:                         │
│   교정: 75000번 이상          │       │   교정: 전체 (min_no=0)               │
│   원고: 2026년 이후           │       │   원고: 전체 (min_year=2000)          │
├──────────────────────────────┤       ├──────────────────────────────────────┤
│ 🔗 대시보드:                  │       │ 🔗 대시보드:                          │
│ https://dsp114.co.kr         │       │ http://dsp1830.ipdisk.co.kr:8000     │
│ /system/migration/index.php  │       │ /system/migration/index.php          │
└──────────────────────────────┘       └──────────────────────────────────────┘
                    비밀번호: duson2026!migration (양쪽 동일)
```

### 🔧 마이그레이션 도구

| 항목 | 값 |
|------|-----|
| **대시보드** | `/system/migration/index.php` |
| **비밀번호** | `duson2026!migration` |
| **소스 API** | `http://dsp114.com/export_api.php` |
| **API 키** | `duson_migration_sync_2026_xK9m` |
| **동기화 엔진** | `/system/migration/MigrationSync.php` |

### 📊 서버별 설정 차이

| 설정 | dsp114.co.kr | NAS (dsp1830.ipdisk.co.kr) |
|------|--------------|----------------------------|
| `FILE_FILTER_MIN_NO` | **84574** | **0** |
| `FILE_FILTER_MIN_YEAR` | **2026** | **2000** |
| 교정파일 범위 | 84574번 이상 | **전체** |
| 원고파일 범위 | 2026년 이후 | **전체** |
| 목적 | 운영 (최근 데이터만) | 아카이브 (완전 백업) |

### ⚠️ 중요 규칙

1. **무시할 테이블**: `users`, `qna`, `Mlang_board_bbs`, `Mlang_portfolio_bbs` — 타겟 서버에서 미사용
2. **dsp114.co.kr 날짜 필터**: `since=2026-01-29` — 이전 데이터는 이미 존재
3. **NAS 전체 백업**: dsp114.com 폐쇄 대비 모든 데이터 영구 보관

### 🔴 파일 동기화 필터링 버그 수정 (2026-02-20)

**문제**: `MigrationSync.php`가 `min_no`/`min_year` 파라미터를 URL에 포함하여 보냈지만,
`export_api.php` (소스 서버)가 이 파라미터를 **완전히 무시**하여 전체 주문을 스캔.
교정파일 동기화 시 8만개+ 주문 디렉토리를 전부 readdir() → dsp114.com 트래픽 과부하 → 서버 다운.

**수정 파일 2개:**

| 파일 | 위치 | 수정 내용 |
|------|------|----------|
| `export_api.php` | dsp114.com (소스 서버) | `$_GET['min_no']`/`$_GET['min_year']` 읽기 + 쿼리 적용 |
| `file_sync_direct.php` | 타겟 서버 | `FILE_FILTER_MIN_NO`/`MIN_YEAR` 상수 + 쿼리 적용 |

**필터 적용 매핑:**

| 파일 타입 | 필터 | 적용 쿼리 |
|----------|------|----------|
| upload (교정파일) | `min_no` | `AND no >= 84574` |
| shop (원고-스티커) | `min_year` | `AND date >= '2026-01-01'` |
| imgfolder (원고-일반) | `min_year` | `AND date >= '2026-01-01'` |

**⚠️ export_api.php는 dsp114.com에 배포해야 효과 적용** (로컬/타겟 서버가 아님)

### 🗄️ 3개 서버 상세 사양

#### 소스: dsp114.com (폐쇄 예정)
| 항목 | 값 |
|------|-----|
| **PHP** | 5.2 (mysql_* 함수) |
| **DB** | MySQL (EUC-KR) |
| **DB 계정** | `duson1830` / `du1830` |
| **웹루트** | `/home/neo_web2/duson1830/www/` |
| **상태** | ⚠️ 일일 트래픽 제한, 폐쇄 예정 |

#### 타겟 1: dsp114.co.kr (운영 서버)
| 항목 | 값 |
|------|-----|
| **유형** | Plesk 임대 서버 |
| **PHP** | 7.x |
| **DB** | MySQL 5.7+ |
| **FTP** | `dsp1830` / `cH*j@yzj093BeTtc` |
| **웹루트** | `/httpdocs/` |
| **용량** | 제한적 (할당량 주의) |

#### 타겟 2: dsp1830.ipdisk.co.kr:8000 (NAS 백업)
| 항목 | 값 |
|------|-----|
| **유형** | 개인 NAS |
| **웹 서버** | Apache/2.4.43 (Unix) |
| **PHP** | 7.3.17 |
| **MySQL** | 5.6.30 (Source distribution) |
| **문자셋** | UTF-8 Unicode (utf8) |
| **Collation** | utf8mb4_unicode_ci |
| **phpMyAdmin** | 5.0.2 |
| **PHP 확장** | mysqli, curl, mbstring |
| **FTP** | `admin` / `1830` |
| **웹루트** | `/HDD2/share/` |
| **용량** | 750GB+ (충분) |

### 📁 파일 경로 매핑

| 파일 유형 | dsp114.com (소스) | 타겟 서버 |
|----------|------------------|-----------|
| 교정파일 | `/www/MlangOrder_PrintAuto/upload/{no}/` | `/mlangorder_printauto/upload/{no}/` |
| 원고(스티커) | `/www/shop/data/` | `/shop/data/` |
| 원고(일반) | `/www/ImgFolder/_MlangPrintAuto_*/` | `/ImgFolder/_MlangPrintAuto_*/` |

### ✅ DB 동기화 완료 기록 (2026-02-02)

| 테이블 | 결과 |
|--------|------|
| member | +10건 (중복 19건 제외) |
| MlangOrder_PrintAuto | +9건 |
| 제품 테이블 9개 | 3,398건 INSERT |
| shop_order/list/list01/temp | +7,775건 |
| orderDB/orderDB2 | +613건 |
| ❌ users, qna, BBS | 무시 |

### 🛠️ 유틸리티 API (index.php)

| action | 설명 |
|--------|------|
| `check_permissions` | 디렉토리 쓰기 권한 확인 |
| `disk_usage` | 디스크 용량 확인 |
| `cleanup_upload` | 오래된 교정파일 삭제 (threshold 파라미터) |
| `file_sync` | 파일 동기화 실행 |
| `file_stats` | 파일 현황 조회 |

### ⚡ 호환성 참고

```
dsp114.com (소스)     →  PHP 5.2, mysql_* 함수, EUC-KR
dsp114.co.kr          →  PHP 7.x, mysqli_* 함수, UTF-8  ✅ 동일 코드
dsp1830.ipdisk.co.kr  →  PHP 7.3, mysqli_* 함수, UTF-8  ✅ 동일 코드
```

- 소스 API(export_api.php)만 PHP 5.2 호환 문법 사용
- 타겟 서버 2개는 동일한 MigrationSync.php 사용 (설정값만 다름)

