# 🔧 Environment Configuration

**자동 환경 감지 및 도메인 설정 시스템**

## 📋 Overview

시스템은 3가지 환경(로컬/개발/운영)을 자동으로 감지하고, 도메인 URL과 쿠키 설정을 자동으로 적용합니다. 이를 통해 **도메인 교체 시 코드 수정 없이** 자동으로 작동합니다.

## 🎯 핵심 파일

### 1. config.env.php
**위치**: `/var/www/html/config.env.php`

**역할**: 환경 자동 감지 및 데이터베이스 설정

```php
class EnvironmentDetector {
    public static function detectEnvironment() {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $serverPath = $_SERVER['DOCUMENT_ROOT'] ?? '';

        // 로컬 환경 감지
        if (strpos($host, 'localhost') !== false ||
            strpos($host, '127.0.0.1') !== false) {
            return 'local';
        }

        // 프로덕션 환경 (dsp1830.shop 또는 dsp1830.shop)
        return 'production';
    }

    public static function getDatabaseConfig() {
        $env = self::detectEnvironment();

        if ($env === 'local') {
            return [
                'host' => 'localhost',
                'user' => 'root',
                'password' => '',
                'database' => 'dsp1830',
                'charset' => 'utf8mb4'
            ];
        } else {
            return [
                'host' => 'localhost',
                'user' => 'dsp1830',
                'password' => 'ds701018',
                'database' => 'dsp1830',
                'charset' => 'utf8mb4'
            ];
        }
    }
}
```

### 2. db.php
**위치**: `/var/www/html/db.php`

**역할**: 도메인 URL 및 쿠키 자동 설정

```php
// 환경별 URL 자동 설정
$current_env = get_current_environment();

if ($current_env === 'local') {
    $admin_url = "http://localhost";
    $home_cookie_url = "localhost";
} else {
    // 프로덕션: 현재 접속 도메인 자동 감지
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'dsp1830.shop';
    $admin_url = $protocol . $host;

    // 쿠키 도메인 설정
    if (strpos($host, 'dsp1830.shop') !== false) {
        $home_cookie_url = ".dsp1830.shop";
    } else {
        $home_cookie_url = ".dsp1830.shop";
    }
}

$Homedir = $admin_url;
```

## 🌍 환경별 동작

### Local Environment (localhost)

**감지 조건:**
- `$_SERVER['HTTP_HOST']` 포함: `localhost`, `127.0.0.1`, `::1`
- 또는 `$_SERVER['DOCUMENT_ROOT']` 포함: `xampp`, `wamp`, `mamp`

**설정값:**
```php
환경: 'local'
DB 사용자: 'root'
DB 비밀번호: '' (빈 문자열)
$admin_url: 'http://localhost'
$home_cookie_url: 'localhost'
```

**용도:** WSL, XAMPP 개발 환경

---

### Development Server (dsp1830.shop)

**감지 조건:**
- `$_SERVER['HTTP_HOST']` = `dsp1830.shop` 또는 `www.dsp1830.shop`

**설정값:**
```php
환경: 'production'
DB 사용자: 'dsp1830'
DB 비밀번호: 'ds701018'
$admin_url: 'http://dsp1830.shop' (자동 감지)
$home_cookie_url: '.dsp1830.shop'
```

**용도:**
- PHP 7.4 코드 개발 및 테스트
- 운영 전 최종 검증
- 임시 스테이징 도메인

---

### Production Server (dsp1830.shop)

**감지 조건:**
- `$_SERVER['HTTP_HOST']` = `dsp1830.shop` 또는 `www.dsp1830.shop`

**설정값:**
```php
환경: 'production'
DB 사용자: 'dsp1830'
DB 비밀번호: 'ds701018'
$admin_url: 'http://dsp1830.shop' (자동 감지)
$home_cookie_url: '.dsp1830.shop'
```

**용도:**
- 최종 운영 도메인 (DNS 전환 후)
- 고객 접속 URL
- 동일 서버, 다른 도메인만

## 🔄 도메인 전환 시나리오

### Phase 1: 현재 개발 (dsp1830.shop)

```
사용자 접속: http://dsp1830.shop
    ↓
환경 감지: 'production'
    ↓
$admin_url = "http://dsp1830.shop"
$home_cookie_url = ".dsp1830.shop"
    ↓
모든 링크와 리소스: dsp1830.shop 도메인 사용
```

### Phase 2: DNS 전환 (dsp1830.shop)

**DNS 레코드 변경:**
```dns
dsp1830.shop A → [dsp1830.shop 서버 IP]
www.dsp1830.shop CNAME → dsp1830.shop
```

**코드 변경: 없음!**

```
사용자 접속: http://dsp1830.shop
    ↓
환경 감지: 'production'
    ↓
$admin_url = "http://dsp1830.shop" (자동 감지!)
$home_cookie_url = ".dsp1830.shop"
    ↓
모든 링크와 리소스: dsp1830.shop 도메인 사용
```

### Phase 3: 완료

```
✅ 고객: 익숙한 dsp1830.shop 계속 사용
✅ 서버: 신규 PHP 7.4 서버
✅ 코드: 한 줄도 수정 안 함
✅ 쿠키: 자동으로 .dsp1830.shop 도메인 적용
```

## 🔍 디버깅 방법

### 현재 환경 확인

```php
// 페이지 상단에 추가 (localhost에서만 표시)
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    echo "<pre style='background:#fff3cd; padding:10px; border:1px solid #ffc107;'>";
    echo "환경: " . get_current_environment() . "\n";
    echo "도메인: " . $_SERVER['HTTP_HOST'] . "\n";
    echo "admin_url: " . $admin_url . "\n";
    echo "cookie_url: " . $home_cookie_url . "\n";
    echo "DB 설정: " . print_r(get_db_config(), true);
    echo "</pre>";
}
```

### 데이터베이스 연결 테스트

```bash
# URL 파라미터로 확인
http://localhost/?debug_db=1
http://dsp1830.shop/?debug_db=1
```

**출력 예시:**
```
🔧 데이터베이스 연결 정보
환경: production
호스트: localhost
사용자: dsp1830
데이터베이스: dsp1830
문자셋: utf8mb4
```

## ⚠️ 주의사항

### 1. 하드코딩 금지

**❌ 나쁜 예:**
```php
$base_url = "http://dsp1830.shop";
$redirect_url = "http://dsp1830.shop/login.php";
```

**✅ 좋은 예:**
```php
$base_url = $admin_url; // 자동 감지
$redirect_url = $admin_url . "/login.php";
```

### 2. 쿠키 도메인 설정

**중요:** 쿠키는 앞에 점(.)을 붙여야 서브도메인에서도 작동합니다.

```php
// ✅ 올바른 설정
setcookie('user_id', $id, time()+3600, '/', '.dsp1830.shop');

// ❌ 잘못된 설정 (서브도메인 미작동)
setcookie('user_id', $id, time()+3600, '/', 'dsp1830.shop');
```

### 3. HTTPS 전환

현재는 HTTP이지만, SSL 인증서 설치 시:

```php
// 자동으로 HTTPS 감지
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    ? "https://" : "http://";
$admin_url = $protocol . $host;
```

### 4. 세션 도메인

세션도 쿠키 기반이므로 자동 적용됨:
```php
session_set_cookie_params([
    'domain' => $home_cookie_url,
    'path' => '/',
    'secure' => false, // HTTPS 전환 시 true
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
```

## 📊 환경 변수 참조표

| 변수 | Local | dsp1830.shop | dsp1830.shop |
|------|-------|--------------|------------|
| `$current_env` | local | production | production |
| `$db_host` | localhost | localhost | localhost |
| `$db_user` | root | dsp1830 | dsp1830 |
| `$db_password` | (빈 문자열) | ds701018 | ds701018 |
| `$admin_url` | http://localhost | http://dsp1830.shop | http://dsp1830.shop |
| `$home_cookie_url` | localhost | .dsp1830.shop | .dsp1830.shop |
| `$Homedir` | http://localhost | http://dsp1830.shop | http://dsp1830.shop |

## 🔐 보안 고려사항

### 1. 환경별 에러 표시

```php
// config.env.php에서 자동 설정
if ($config['debug']) {
    // 로컬: 모든 오류 표시
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // 프로덕션: 오류 숨김
    error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
    ini_set('display_errors', 0);
}
```

### 2. 민감한 정보 보호

- `.env` 파일은 `.gitignore`에 포함
- 데이터베이스 비밀번호는 config 파일에만 저장
- 로컬 환경에서만 디버그 정보 표시

### 3. 크로스 도메인 문제 방지

쿠키 도메인이 자동으로 설정되므로:
- ✅ dsp1830.shop에서 설정한 세션 → dsp1830.shop에서만 유효
- ✅ dsp1830.shop에서 설정한 세션 → dsp1830.shop에서만 유효
- ✅ 도메인 전환 시 자동 로그아웃 (보안상 안전)

## 🚀 배포 체크리스트

### DNS 전환 전
- [ ] dsp1830.shop에서 전체 기능 테스트 완료
- [ ] 환경 감지 정상 작동 확인
- [ ] 쿠키/세션 정상 작동 확인
- [ ] 데이터베이스 동기화 (필요시)

### DNS 전환
- [ ] DNS A 레코드: `dsp1830.shop` → `[신규 서버 IP]`
- [ ] DNS CNAME: `www.dsp1830.shop` → `dsp1830.shop`
- [ ] TTL 확인 (전파 시간: 1~24시간)

### DNS 전환 후
- [ ] http://dsp1830.shop 접속 테스트
- [ ] http://www.dsp1830.shop 접속 테스트
- [ ] 환경 감지: `get_current_environment()` = 'production'
- [ ] URL 확인: `$admin_url` = 'http://dsp1830.shop'
- [ ] 쿠키 확인: `$home_cookie_url` = '.dsp1830.shop'
- [ ] 로그인/로그아웃 정상 작동
- [ ] 주문 처리 정상 작동

## 📚 관련 문서

- [PROJECT_OVERVIEW.md](../01_CORE/PROJECT_OVERVIEW.md) - 도메인 전환 전략
- [DEPLOYMENT.md](../04_OPERATIONS/DEPLOYMENT.md) - 배포 절차
- [DATABASE_SETUP.md](DATABASE_SETUP.md) - 데이터베이스 설정

---

*Last Updated: 2025-11-03*
*Maintained by: Development Team*
