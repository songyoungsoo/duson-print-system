## 🔐 Authentication System

### System Architecture (4 Independent Layers)

#### 1. User Authentication
- **Files**: `/includes/auth.php`, `/member/login_unified.php`
- **Database**: `users` table (bcrypt), `member` table (legacy)
- **Features**: Remember me (30 days), auto-upgrade plaintext passwords

#### 2. Admin Authentication
- **Files**: `/admin/includes/admin_auth.php`
- **Database**: `admin_users` table
- **Features**: Role-based access, session timeout

#### 3. Order Management Authentication
- **Files**: `/sub/checkboard_auth.php`
- **Access**: Order verification with password

#### 4. Customer Order Lookup
- **Files**: `/sub/my_orders_auth.php`
- **Access**: Phone + password verification

### Password Storage Standards

#### Bcrypt Format (Modern)
```php
// ✅ ALWAYS: New passwords use bcrypt
$hash = password_hash($password, PASSWORD_DEFAULT);
// Result: $2y$10$... (60 characters)
```

#### Plaintext Support (Legacy)
```php
// ✅ ALWAYS: Support legacy plaintext + auto-upgrade
if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
    // Bcrypt verification
    $login_success = password_verify($password, $stored_password);
} else {
    // Plaintext verification + auto-upgrade
    if ($password === $stored_password) {
        $login_success = true;
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        // UPDATE users SET password = $new_hash WHERE id = ?
    }
}
```

### Critical SSOT Files
- `includes/auth.php` - Main user authentication (bcrypt + plaintext support)
- `member/login_unified.php` - Header login handler
- `mlangorder_printauto/OnlineOrder_unified.php` - Order page modal login

### Session Management
- **Session Duration**: 8 hours
- **Remember Token**: 30 days (stored in `remember_tokens` table)
- **Cart Session Preservation**: Session ID passed via hidden field during login/signup

### Cart (장바구니) System

**테이블**: `shop_temp`

**장바구니 흐름**:
```
1. 제품 페이지 "장바구니 담기" → shop_temp INSERT
2. 장바구니/주문 페이지 → shop_temp 조회 (session_id로)
3. "주문완료" 클릭 → mlangorder_printauto INSERT + shop_temp DELETE
```

**세션 만료 시 장바구니**:
- 세션 만료(8시간) 후 새 세션 ID 발급
- 이전 session_id와 달라서 장바구니 조회 불가
- 데이터는 DB에 남아있음 (orphaned data)

**자동 정리 기능 (2026-02-05 추가)**:
```php
// mlangprintauto/shop_temp_helper.php - cleanupOldCartItems()
// 장바구니 조회 시 7일 이상 된 데이터 자동 삭제
DELETE FROM shop_temp WHERE regdate < UNIX_TIMESTAMP(NOW() - INTERVAL 7 DAY)
```

| 항목 | 값 |
|------|-----|
| 정리 주기 | 장바구니 조회 시 자동 실행 |
| 삭제 기준 | 7일 이상 경과 |
| 로그 | error_log에 삭제 건수 기록 |

### Authentication Consistency Rule (CRITICAL)

```php
// ❌ WRONG: Header login supports plaintext, order login doesn't
// Header (login_unified.php): password_verify() + plaintext fallback ✓
// Order page (auth.php): password_verify() only ✗
// Result: Same user can't login on order page!

// ✅ CORRECT: Both use identical verification logic
// Header login: bcrypt + plaintext with auto-upgrade
// Order login: bcrypt + plaintext with auto-upgrade
// Result: Consistent behavior across all login points
```

