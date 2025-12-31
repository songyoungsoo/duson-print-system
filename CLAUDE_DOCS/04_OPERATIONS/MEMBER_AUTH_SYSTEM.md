# 회원 인증 시스템 (Member Authentication System)

**최종 업데이트**: 2025-12-31
**상태**: 운영 중

---

## 1. 시스템 개요

두손기획 인쇄 시스템의 회원 인증은 **듀얼 테이블 구조**로 운영됩니다:
- **`users` 테이블**: 신규 시스템 (bcrypt 해시)
- **`member` 테이블**: 레거시 시스템 (평문 비밀번호)

### 핵심 원칙
1. 로그인 시 `users` 테이블 우선 확인
2. 실패 시 `member` 테이블 폴백
3. `member`로 로그인 성공 시 자동으로 `users`로 마이그레이션

---

## 2. 데이터베이스 테이블 구조

### 2.1 users 테이블 (신규 시스템)

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,      -- 로그인 ID
    password VARCHAR(255) NOT NULL,            -- bcrypt 해시 (60자)
    name VARCHAR(100),                         -- 사용자 이름
    email VARCHAR(100),
    phone VARCHAR(20),
    hendphone VARCHAR(20),                     -- 휴대폰
    member_id VARCHAR(50),                     -- 기존 member.id 참조
    old_password VARCHAR(255),                 -- 마이그레이션용 원본 비밀번호
    login_count INT DEFAULT 0,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 2.2 member 테이블 (레거시 시스템)

```sql
-- 기존 레거시 테이블 (읽기 전용 권장)
CREATE TABLE member (
    id VARCHAR(50) PRIMARY KEY,               -- 로그인 ID
    pass VARCHAR(50),                          -- 평문 비밀번호 (!)
    name VARCHAR(100),
    email VARCHAR(100),
    phone1 VARCHAR(4),                         -- 전화번호 분리
    phone2 VARCHAR(4),
    phone3 VARCHAR(4),
    hendphone1 VARCHAR(4),                     -- 휴대폰 분리
    hendphone2 VARCHAR(4),
    hendphone3 VARCHAR(4),
    po1 VARCHAR(20),                           -- 사업자등록번호
    po3 VARCHAR(50),                           -- 대표자명
    po4 VARCHAR(50),                           -- 업태
    po5 VARCHAR(50),                           -- 종목
    po6 VARCHAR(200),                          -- 사업장주소
    po7 VARCHAR(100),                          -- 세금용메일
    Logincount INT DEFAULT 0,
    EndLogin DATETIME
);
```

---

## 3. 로그인 프로세스

### 3.1 처리 파일
- **`/member/login_unified.php`** - 통합 로그인 처리

### 3.2 로그인 흐름

```
사용자 입력 (ID/PW)
      ↓
┌─────────────────────────────────────┐
│ Step 1: users 테이블 확인           │
│ SELECT * FROM users WHERE username = ? │
└─────────────────────────────────────┘
      ↓
   사용자 존재?
      ↓
   [YES] → bcrypt 검증 (password_verify)
            ↓
         검증 성공? → 로그인 완료
            ↓
         [NO] → Step 2로 이동
      ↓
   [NO]
      ↓
┌─────────────────────────────────────┐
│ Step 2: member 테이블 확인 (폴백)   │
│ SELECT * FROM member WHERE id = ?   │
└─────────────────────────────────────┘
      ↓
   사용자 존재 & 비밀번호 일치?
      ↓
   [YES] → users 테이블에 이미 존재?
            ↓
         [YES] → 기존 users로 로그인
         [NO]  → users에 마이그레이션 후 로그인
      ↓
   [NO] → 로그인 실패
```

### 3.3 비밀번호 검증 로직

```php
// users 테이블 - bcrypt 또는 평문 지원
if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
    // bcrypt 해시 검증
    if (password_verify($pass, $stored_password)) {
        $login_success = true;
    }
} else {
    // 평문 비밀번호 (레거시)
    if ($pass === $stored_password) {
        $login_success = true;
        $need_hash_upgrade = true;  // 해시 업그레이드 플래그
    }
}

// 평문 로그인 성공 시 bcrypt로 업그레이드
if ($login_success && $need_hash_upgrade) {
    $new_hash = password_hash($pass, PASSWORD_DEFAULT);
    $update_query = "UPDATE users SET password = ? WHERE id = ?";
    // ... 업데이트 실행
}
```

### 3.4 자동 마이그레이션 로직

```php
// member 테이블에서 로그인 성공 시
if ($pass === $member['pass']) {
    // users 테이블에 이미 존재하는지 확인
    $check_users = "SELECT id FROM users WHERE username = ?";

    if (!$existing_user) {
        // 신규 마이그레이션
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
        $migrate_query = "INSERT INTO users (username, password, name, email, phone, ...)
                         VALUES (?, ?, ?, ?, ?, ...)";
        // ... 마이그레이션 실행
    }
}
```

---

## 4. 회원가입 프로세스

### 4.1 관련 파일
- **`/member/join.php`** - 회원가입 페이지 (라우터)
- **`/member/form.php`** - 회원가입 폼 (UI)
- **`/member/register_unified.php`** - 회원가입 처리
- **`/member/register_process.php`** - 레거시 처리 (form.php용)

### 4.2 회원가입 폼 필드

#### 기본 정보
| 필드명 | 설명 | 필수 |
|--------|------|------|
| id | 아이디 (4~12자, 영숫자) | ✅ |
| pass1/pass2 | 비밀번호/확인 (6자 이상) | ✅ |
| name | 이름 | ✅ |
| phone1/2/3 | 전화번호 | |
| hendphone1/2/3 | 휴대폰 | |
| email | 이메일 | |
| zipcode/addr1/addr2 | 주소 | |

#### 사업자 정보 (선택)
| 필드명 | DB 컬럼 | 설명 |
|--------|---------|------|
| po1 | po1 | 사업자등록번호 |
| po3 | po3 | 대표자명 |
| po4 | po4 | 업태 |
| po5 | po5 | 종목 |
| po6 | po6 | 사업장주소 |
| po7 | po7 | 세금용 이메일 |

### 4.3 회원가입 처리 흐름

```php
// register_unified.php
1. 폼 데이터 수집 및 검증
2. 아이디 중복 확인 (users + member 테이블)
3. 비밀번호 bcrypt 해시
4. users 테이블 INSERT
5. member 테이블 INSERT (호환성)
6. 완료 후 login.php로 리다이렉트
```

---

## 5. 세션 관리

### 5.1 세션 변수

```php
// 신규 시스템
$_SESSION['user_id']    // users.id (AUTO_INCREMENT)
$_SESSION['username']   // users.username (로그인 ID)
$_SESSION['user_name']  // users.name (사용자 이름)

// 레거시 호환
$_SESSION['id_login_ok'] = array(
    'id' => $username,
    'pass' => $password  // 평문 (레거시 호환용)
);
```

### 5.2 쿠키 설정

```php
// 로그인 상태 유지 쿠키
setcookie("id_login_ok", $username, 0, "/");
```

### 5.3 세션 유효 시간
- **기본**: 8시간 (28800초)
- **자동 로그인**: 30일 (remember_tokens 테이블)

---

## 6. 자동 로그인 (Remember Me)

### 6.1 remember_tokens 테이블

```sql
CREATE TABLE remember_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);
```

### 6.2 관련 함수 (includes/auth.php)

```php
createRememberToken($user_id)     // 토큰 생성 및 DB 저장
validateRememberToken($token)     // 토큰 검증 및 자동 로그인
deleteRememberToken($user_id)     // 로그아웃 시 토큰 삭제
setRememberMeCookie($token)       // 쿠키 설정 (30일)
clearRememberMeCookie()           // 쿠키 삭제
```

---

## 7. 로그인 모달

### 7.1 관련 파일
- **`/includes/login_modal.php`** - 모달 HTML/CSS
- **`/js/common-auth.js`** - 모달 JavaScript

### 7.2 모달 구조

```html
<div id="loginModal" class="login-modal">
    <div class="login-modal-content">
        <!-- 로그인 탭 -->
        <button class="login-tab active" onclick="showLoginTab(event)">로그인</button>
        <!-- 회원가입 탭 - 전체 폼으로 리다이렉트 -->
        <button class="login-tab" onclick="location.href='/member/join.php'">회원가입</button>

        <!-- 로그인 폼 -->
        <form id="loginForm" method="post" action="/member/login_unified.php">
            <input type="hidden" name="mode" value="member_login">
            <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
            <!-- ID, Password 필드 -->
        </form>
    </div>
</div>
```

### 7.3 JavaScript 함수

```javascript
// js/common-auth.js
showLoginModal()      // 모달 표시
hideLoginModal()      // 모달 숨김
showLoginTab(event)   // 로그인 탭 활성화
```

---

## 8. 보안 고려사항

### 8.1 구현된 보안 기능
- ✅ bcrypt 비밀번호 해시 (`PASSWORD_DEFAULT`)
- ✅ SQL Injection 방지 (Prepared Statements)
- ✅ 세션 고정 공격 방지 (`session_regenerate_id(true)`)
- ✅ XSS 방지 (`htmlspecialchars()`)

### 8.2 레거시 취약점
- ⚠️ member 테이블: 평문 비밀번호 저장
- ⚠️ $_SESSION['id_login_ok']['pass']: 평문 비밀번호 저장

### 8.3 권장 개선사항
1. member 테이블의 모든 사용자를 users로 마이그레이션
2. 마이그레이션 완료 후 member 테이블 deprecate
3. $_SESSION['id_login_ok'] 레거시 호환 제거

---

## 9. 관련 파일 목록

### 인증 처리
| 파일 | 설명 |
|------|------|
| `/member/login_unified.php` | 통합 로그인 처리 |
| `/member/register_unified.php` | 통합 회원가입 처리 |
| `/member/register_process.php` | 레거시 회원가입 처리 |
| `/includes/auth.php` | 인증 헬퍼 함수 |

### UI 컴포넌트
| 파일 | 설명 |
|------|------|
| `/member/join.php` | 회원가입 페이지 라우터 |
| `/member/form.php` | 회원가입 폼 (사업자 정보 포함) |
| `/member/login.php` | 로그인 페이지 |
| `/includes/login_modal.php` | 로그인 모달 |
| `/js/common-auth.js` | 인증 관련 JavaScript |

### 설정
| 파일 | 설명 |
|------|------|
| `/db.php` | 데이터베이스 연결 |
| `/config.env.php` | 환경 설정 |

---

## 10. 트러블슈팅

### 문제: 로그인 후 세션이 유지되지 않음
**원인**: 세션 쿠키 도메인 불일치
**해결**: `config.env.php`에서 도메인별 쿠키 설정 확인

### 문제: "좀비 로그인" - 브라우저는 로그인 상태인데 서버 세션 만료
**원인**: 세션 유효 시간 짧음 (기본 24분)
**해결**: `includes/auth.php`에서 8시간으로 연장됨 (2025-12-11)

### 문제: 회원가입 후 로그인 실패
**원인**: users 테이블에만 저장되고 member 테이블 미저장
**해결**: `register_unified.php`에서 양쪽 테이블에 INSERT

### 문제: 사업자 정보가 저장되지 않음
**원인**: 간소화된 모달 폼 사용
**해결**: 모달의 회원가입 탭을 `/member/join.php`로 리다이렉트 (2025-12-31)

---

*이 문서는 두손기획 인쇄 시스템의 회원 인증 시스템을 설명합니다.*
