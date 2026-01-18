# 두손기획인쇄 설정 가이드

이 문서는 시스템의 설정 파일과 환경 구성 방법을 설명합니다.

---

## 목차

1. [설정 파일 개요](#1-설정-파일-개요)
2. [config.env.php 상세](#2-configenvphp-상세)
3. [db.php 환경 자동 감지](#3-dbphp-환경-자동-감지)
4. [도메인별 설정](#4-도메인별-설정)
5. [인증 설정](#5-인증-설정)
6. [환경별 설정 예시](#6-환경별-설정-예시)

---

## 1. 설정 파일 개요

### 1.1 핵심 설정 파일

| 파일 | 경로 | 용도 |
|------|------|------|
| config.env.php | `/var/www/html/config.env.php` | 환경별 설정 관리 |
| db.php | `/var/www/html/db.php` | 데이터베이스 연결 |
| auth.php | `/var/www/html/includes/auth.php` | 인증 및 세션 관리 |

### 1.2 설정 로드 순서

```
1. config.env.php    - 환경 감지 및 설정 정의
       ↓
2. db.php            - 데이터베이스 연결 설정 적용
       ↓
3. auth.php          - 세션 및 인증 설정 적용
```

---

## 2. config.env.php 상세

### 2.1 EnvironmentDetector 클래스

환경을 자동으로 감지하고 적절한 설정을 반환하는 클래스입니다.

```php
class EnvironmentDetector {
    // 환경 감지 메서드
    public static function detectEnvironment();

    // 데이터베이스 설정 반환
    public static function getDatabaseConfig();

    // 환경 확인 메서드
    public static function isLocal();
    public static function isProduction();
}
```

### 2.2 환경 감지 기준

시스템은 다음 기준으로 환경을 자동 감지합니다:

| 감지 조건 | 환경 |
|-----------|------|
| 호스트에 `localhost` 포함 | 로컬 |
| 호스트에 `127.0.0.1` 포함 | 로컬 |
| 서버 경로에 `xampp`, `wamp`, `mamp` 포함 | 로컬 |
| 호스트에 `dsp1830.shop` 포함 | 프로덕션 |
| 호스트에 `dsp114.com` 포함 | 프로덕션 |
| 기타 | 프로덕션 (기본값) |

### 2.3 데이터베이스 설정 항목

```php
self::$config = [
    'host' => 'localhost',        // 데이터베이스 서버 주소
    'user' => 'dsp1830',          // 데이터베이스 사용자명
    'password' => 'ds701018',     // 데이터베이스 비밀번호
    'database' => 'dsp1830',      // 데이터베이스명
    'charset' => 'utf8mb4',       // 문자셋
    'environment' => 'local',     // 환경 이름
    'debug' => true               // 디버그 모드
];
```

| 항목 | 설명 | 기본값 |
|------|------|--------|
| host | DB 서버 주소 | `localhost` |
| user | DB 사용자명 | `dsp1830` |
| password | DB 비밀번호 | `ds701018` |
| database | DB 이름 | `dsp1830` |
| charset | 문자셋 | `utf8mb4` |
| environment | 환경 식별자 | `local` 또는 `production` |
| debug | 디버그 모드 | 로컬: `true`, 프로덕션: `false` |

### 2.4 환경별 오류 리포팅

```php
// config.env.php 하단
if ($config['debug']) {
    // 로컬 환경: 모든 오류 표시
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // 프로덕션: 오류 숨김
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
}
```

### 2.5 편의 함수

```php
// 데이터베이스 설정 가져오기
$db_config = get_db_config();

// 로컬 환경 확인
if (is_local_environment()) {
    // 로컬 환경 전용 코드
}

// 프로덕션 환경 확인
if (is_production_environment()) {
    // 프로덕션 환경 전용 코드
}

// 현재 환경 이름 가져오기
$env = get_current_environment();  // 'local' 또는 'production'
```

---

## 3. db.php 환경 자동 감지

### 3.1 db.php의 역할

`db.php`는 다음 기능을 수행합니다:

1. `config.env.php` 로드
2. 환경별 DB 설정 적용
3. 데이터베이스 연결 수립
4. 사이트 전역 변수 설정

### 3.2 연결 변수

```php
// 주요 연결 변수
$db    // mysqli 연결 객체 (권장)
$conn  // $db의 별칭 (레거시 호환)
```

사용 예:

```php
include_once($_SERVER['DOCUMENT_ROOT'] . '/db.php');

// 쿼리 실행
$result = mysqli_query($db, "SELECT * FROM users");

// 또는 레거시 호환
$result = mysqli_query($conn, "SELECT * FROM users");
```

### 3.3 환경별 URL 자동 설정

```php
// db.php에서 자동 설정되는 URL 변수
$admin_url        // 사이트 기본 URL
$home_cookie_url  // 쿠키 도메인
$Homedir          // $admin_url 별칭
```

| 환경 | $admin_url | $home_cookie_url |
|------|------------|------------------|
| 로컬 | `http://localhost` | `localhost` |
| 프로덕션 | `http://dsp1830.shop` | `.dsp1830.shop` |

### 3.4 사이트 정보 변수

```php
$admin_email  = "dsp1830@naver.com";        // 관리자 이메일
$admin_name   = "두손기획";                  // 사이트 이름
$SiteTitle    = $admin_name;                 // 사이트 제목
$admin_Tname  = "Mlang";                     // 테이블 접두사
```

### 3.5 디버그 모드

로컬 환경에서 데이터베이스 연결 정보를 확인할 수 있습니다:

```
http://localhost/?debug_db=1
```

출력 예:
```
환경: local
호스트: localhost
사용자: dsp1830
데이터베이스: dsp1830
문자셋: utf8mb4
```

---

## 4. 도메인별 설정

### 4.1 지원 도메인

| 도메인 | 환경 | 용도 |
|--------|------|------|
| localhost | 로컬 | 개발 |
| 127.0.0.1 | 로컬 | 개발 |
| dsp1830.shop | 프로덕션 | 스테이징/프로덕션 |
| dsp114.com | 프로덕션 | 레거시 도메인 |

### 4.2 새 도메인 추가 방법

`config.env.php`의 `detectEnvironment()` 메서드를 수정합니다:

```php
public static function detectEnvironment() {
    // ...

    // 새 도메인 추가
    else if (
        strpos($host, 'newdomain.com') !== false ||
        strpos($host, 'www.newdomain.com') !== false
    ) {
        self::$environment = 'production';
    }

    // ...
}
```

### 4.3 스테이징 환경 분리 (선택사항)

별도의 스테이징 환경이 필요한 경우:

```php
// config.env.php 수정

case 'staging':
    self::$config = [
        'host' => 'localhost',
        'user' => 'staging_user',
        'password' => 'staging_pass',
        'database' => 'staging_db',
        'charset' => 'utf8mb4',
        'environment' => 'staging',
        'debug' => true  // 스테이징에서는 디버그 활성화
    ];
    break;
```

---

## 5. 인증 설정

### 5.1 세션 설정 (auth.php)

```php
// 세션 유효 시간: 8시간
$session_lifetime = 28800;

// 세션 쿠키 설정
session_set_cookie_params([
    'lifetime' => $session_lifetime,  // 8시간
    'path' => '/',
    'domain' => '',
    'secure' => false,    // HTTPS 사용 시 true로 변경
    'httponly' => true,   // JavaScript 접근 차단
    'samesite' => 'Lax'   // CSRF 방지
]);
```

| 설정 | 값 | 설명 |
|------|-----|------|
| lifetime | 28800 | 세션 유효 시간 (8시간) |
| httponly | true | JavaScript에서 쿠키 접근 차단 |
| samesite | Lax | 크로스 사이트 요청 제한 |

### 5.2 자동 로그인 설정

```php
// 자동 로그인 유지 기간
define('REMEMBER_ME_DAYS', 30);  // 30일

// 자동 로그인 쿠키 이름
define('REMEMBER_ME_COOKIE', 'remember_token');
```

### 5.3 세션 저장 경로

```php
// 세션 저장 경로 (프로젝트 내 sessions 디렉토리)
$session_path = dirname(__DIR__) . '/sessions';
ini_set('session.save_path', $session_path);
```

### 5.4 HTTPS 환경 설정

HTTPS를 사용하는 경우 `auth.php`에서 다음 설정을 변경합니다:

```php
// 세션 쿠키 설정
session_set_cookie_params([
    // ...
    'secure' => true,  // HTTPS 전용
    // ...
]);

// Remember Me 쿠키 설정
function setRememberMeCookie($token) {
    setcookie(REMEMBER_ME_COOKIE, $token, [
        // ...
        'secure' => true,  // HTTPS 전용
        // ...
    ]);
}
```

---

## 6. 환경별 설정 예시

### 6.1 로컬 개발 환경

```php
// config.env.php - 로컬 설정
self::$config = [
    'host' => 'localhost',
    'user' => 'dsp1830',
    'password' => 'ds701018',
    'database' => 'dsp1830',
    'charset' => 'utf8mb4',
    'environment' => 'local',
    'debug' => true
];
```

특징:
- 모든 오류 표시
- 디버그 정보 출력 가능
- `?debug_db=1` 파라미터 사용 가능

### 6.2 프로덕션 환경

```php
// config.env.php - 프로덕션 설정
self::$config = [
    'host' => 'localhost',
    'user' => 'dsp1830',
    'password' => 'ds701018',
    'database' => 'dsp1830',
    'charset' => 'utf8mb4',
    'environment' => 'production',
    'debug' => false
];
```

특징:
- 오류 메시지 숨김
- 디버그 정보 비활성화
- 로그 파일에만 오류 기록

### 6.3 설정 확인 스크립트

다음 스크립트로 현재 설정을 확인할 수 있습니다:

```php
<?php
// check_config.php (로컬 환경에서만 사용)
require_once __DIR__ . '/config.env.php';

if (!is_local_environment()) {
    die('로컬 환경에서만 실행 가능합니다.');
}

echo "현재 환경: " . get_current_environment() . "\n";
echo "디버그 모드: " . (get_db_config()['debug'] ? '활성화' : '비활성화') . "\n";

$info = EnvironmentDetector::getEnvironmentInfo();
print_r($info);
```

---

## 설정 변경 체크리스트

설정 변경 시 다음 사항을 확인하세요:

- [ ] `config.env.php` 변경 시 문법 오류 확인
- [ ] 데이터베이스 연결 정보 정확성 확인
- [ ] 변경 후 웹 브라우저에서 정상 동작 확인
- [ ] 프로덕션 배포 전 디버그 모드 비활성화 확인
- [ ] HTTPS 환경이면 secure 쿠키 설정 확인

---

## 다음 단계

설정이 완료되면 다음 문서를 참조하세요:

- [PRODUCT_SETUP.md](./PRODUCT_SETUP.md) - 제품 설정 가이드

---

*Version: 1.0*
*Last Updated: 2026-01-18*
