# 두손기획 시스템 개선 계획

## 현재 상태 진단

### 주요 문제점
| 영역 | 문제 | 위험도 |
|------|------|--------|
| DB | 스키마-코드 불일치, 마이그레이션 없음 | 🔴 높음 |
| 파일 | 중복 파일 다수 (unified, universal 등) | 🟡 중간 |
| 환경 | 로컬/프로덕션 설정 혼재 | 🟡 중간 |
| 배포 | 수동 FTP, 일관성 없음 | 🟡 중간 |
| 테스트 | 자동화 테스트 없음 | 🟢 낮음 |

---

## Phase 1: 기반 정비 (1-2주)

### 1.1 DB 마이그레이션 시스템 도입

**목표**: DB 변경 이력 관리, 로컬/프로덕션 동기화

```
/var/www/html/
├── database/
│   ├── migrations/
│   │   ├── 001_initial_schema.sql
│   │   ├── 002_add_product_type.sql
│   │   ├── 003_add_spec_columns.sql
│   │   └── ...
│   ├── migrate.php          # 마이그레이션 실행 스크립트
│   └── schema_version.sql   # 현재 버전 추적 테이블
```

**schema_version 테이블**:
```sql
CREATE TABLE schema_version (
    version INT PRIMARY KEY,
    name VARCHAR(255),
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

**migrate.php 핵심 로직**:
```php
<?php
// 현재 버전 확인 → 미적용 마이그레이션 실행 → 버전 업데이트
$current = getCurrentVersion($db);
$migrations = glob('migrations/*.sql');
foreach ($migrations as $file) {
    $version = intval(basename($file));
    if ($version > $current) {
        executeMigration($db, $file);
        updateVersion($db, $version, basename($file));
    }
}
```

### 1.2 환경 설정 분리

**목표**: 로컬/프로덕션 설정 명확히 분리

```
/var/www/html/
├── config/
│   ├── config.php           # 환경 자동 감지 및 설정 로드
│   ├── config.local.php     # 로컬 설정 (git 제외)
│   ├── config.prod.php      # 프로덕션 설정 (git 제외)
│   └── config.example.php   # 템플릿 (git 포함)
```

**config.php**:
```php
<?php
$env = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false)
    ? 'local' : 'prod';

$config = require __DIR__ . "/config.{$env}.php";

// 전역 상수 정의
define('DB_HOST', $config['db']['host']);
define('DB_NAME', $config['db']['name']);
define('DB_USER', $config['db']['user']);
define('DB_PASS', $config['db']['pass']);
define('BASE_URL', $config['base_url']);
define('ENV', $env);
```

### 1.3 Git 정리

**.gitignore 추가**:
```
config/config.local.php
config/config.prod.php
*.bak
*.bak2
*_backup*
*_old*
```

---

## Phase 2: 중복 파일 정리 (2-3주)

### 2.1 주문 완료 페이지 통합

**현재 상태**:
```
mlangorder_printauto/
├── OrderComplete_unified.php      # 31KB - 구버전
├── OrderComplete_universal.php    # 60KB - 현재 사용
├── OrderComplete_universal251217.php  # 백업
├── OrderComplete_universal_20251219.php  # 백업
└── OrderComplete_office_table.php  # 특수 용도?
```

**목표 상태**:
```
mlangorder_printauto/
├── OrderComplete.php              # 통합 버전
└── archive/                       # 백업 보관 (git 제외)
    └── OrderComplete_*.php
```

**작업 순서**:
1. `OrderComplete_universal.php` → `OrderComplete.php`로 복사
2. `ProcessOrder_unified.php` 리다이렉트 수정
3. 테스트 후 구버전 archive로 이동
4. 1주일 모니터링 후 archive 삭제

### 2.2 주문 처리 페이지 통합

**현재 상태**:
```
mlangorder_printauto/
├── ProcessOrder_unified.php       # 현재 사용
├── ProcessOrder_unified251217.php # 백업
├── OnlineOrder_unified.php        # 현재 사용
├── OnlineOrder_unified251227.php  # 백업
├── OnlineOrder_unified_20251219.php # 백업
└── OnlineOrder.php                # 구버전?
```

**목표 상태**:
```
mlangorder_printauto/
├── ProcessOrder.php               # 통합
├── OnlineOrder.php                # 통합
└── archive/
```

### 2.3 파일 명명 규칙 표준화

| 현재 | 개선 |
|------|------|
| `*_unified.php` | 제거 (통합 완료 의미) |
| `*_universal.php` | 제거 |
| `*251217.php` | archive로 이동 |
| `*_20251219.php` | archive로 이동 |

---

## Phase 3: 코드 품질 개선 (3-4주)

### 3.1 공통 함수 정리

**현재 문제**: 동일 함수가 여러 파일에 중복 정의

**목표**:
```
/var/www/html/
├── includes/
│   ├── functions/
│   │   ├── format.php       # formatQuantity, formatPrice 등
│   │   ├── validation.php   # 입력 검증 함수
│   │   ├── database.php     # DB 헬퍼 함수
│   │   └── upload.php       # 업로드 관련 함수
│   └── autoload.php         # 자동 로드
```

### 3.2 SQL 인젝션 방지 표준화

**체크리스트**:
- [ ] 모든 쿼리에 prepared statement 사용
- [ ] bind_param 개수 검증 로직 표준화
- [ ] 입력값 sanitize 함수 통일

### 3.3 에러 처리 표준화

```php
// includes/ErrorHandler.php
class ErrorHandler {
    public static function handle(Exception $e, $context = []) {
        error_log(json_encode([
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'context' => $context,
            'time' => date('Y-m-d H:i:s')
        ]));

        if (ENV === 'local') {
            throw $e; // 개발 환경: 에러 표시
        } else {
            // 프로덕션: 사용자 친화적 메시지
            header('Location: /error.php?code=500');
            exit;
        }
    }
}
```

---

## Phase 4: 배포 자동화 (1-2주)

### 4.1 배포 스크립트

```bash
#!/bin/bash
# deploy.sh

ENV=$1  # local, staging, prod

if [ "$ENV" == "prod" ]; then
    echo "=== 프로덕션 배포 ==="

    # 1. DB 마이그레이션
    php database/migrate.php --env=prod

    # 2. 파일 업로드 (변경된 파일만)
    lftp -u dsp1830,ds701018 ftp://dsp1830.shop << EOF
        mirror -R --only-newer --exclude .git/ --exclude config/*.local.php ./ ./
        quit
EOF

    # 3. 캐시 클리어
    curl -s "https://dsp1830.shop/admin/clear_cache.php?key=xxx"

    echo "=== 배포 완료 ==="
fi
```

### 4.2 배포 체크리스트

```markdown
## 배포 전 확인사항
- [ ] 로컬 테스트 완료
- [ ] DB 마이그레이션 파일 준비
- [ ] git commit 완료
- [ ] 백업 확인

## 배포 후 확인사항
- [ ] 메인 페이지 로드 확인
- [ ] 장바구니 → 주문 플로우 테스트
- [ ] 에러 로그 확인
```

---

## Phase 5: 모니터링 & 테스트 (지속)

### 5.1 기본 헬스체크

```php
// healthcheck.php
<?php
$checks = [
    'db' => checkDatabase(),
    'disk' => checkDiskSpace(),
    'uploads' => checkUploadDir(),
];

echo json_encode([
    'status' => array_sum($checks) === count($checks) ? 'ok' : 'error',
    'checks' => $checks,
    'time' => date('Y-m-d H:i:s')
]);
```

### 5.2 핵심 플로우 테스트

```php
// tests/OrderFlowTest.php
class OrderFlowTest {
    public function testCartToOrderComplete() {
        // 1. 장바구니 추가
        // 2. 주문 정보 입력
        // 3. 주문 처리
        // 4. 완료 페이지 확인
    }
}
```

---

## 실행 우선순위

| 순서 | 작업 | 예상 시간 | 효과 |
|------|------|----------|------|
| 1 | DB 마이그레이션 시스템 | 2일 | 🔴 높음 |
| 2 | 환경 설정 분리 | 1일 | 🟡 중간 |
| 3 | 중복 파일 정리 | 3-5일 | 🟡 중간 |
| 4 | 배포 스크립트 | 1일 | 🟡 중간 |
| 5 | 공통 함수 정리 | 1주 | 🟢 낮음 |
| 6 | 테스트 코드 | 지속 | 🟢 낮음 |

---

## 즉시 실행 가능한 것

### 오늘 당장:
1. `database/migrations/` 디렉토리 생성
2. 현재 DB 스키마를 `001_initial_schema.sql`로 저장
3. 오늘 추가한 컬럼들을 `002_add_order_spec_columns.sql`로 저장

### 이번 주:
1. 프로덕션 DB에 컬럼 추가 (위 SQL 실행)
2. 백업 파일들 archive 폴더로 이동
3. .gitignore 업데이트

---

*작성일: 2026-01-13*
*버전: 1.0*
