## ✅ member → users 마이그레이션 완료 (2026-02-02)

**상태: 6단계 완료 (7단계 member DROP은 의도적 보류)**

모든 활성 PHP 코드가 `users` 테이블을 primary로 사용하도록 전환 완료.
`member` 테이블은 backward compatibility를 위해 유지 (이중 쓰기).

### 마이그레이션 결과 요약

| 단계 | 범위 | 상태 |
|------|------|------|
| 1단계 | 회원가입/관리자 (`register_process`, `admin/member/`) | ✅ 완료 |
| 2단계 | 로그인 (`login_unified`, `session/loginProc`) | ✅ 완료 |
| 3단계 | session/ 디렉토리 (7개 파일) | ✅ 완료 |
| 4단계 | 주문 시스템 (`OnlineOrder`, `OrderFormOrderOne`, `WindowSian`) | ✅ 완료 |
| 5단계 | 관리자 (`admin/config`, `AdminConfig`, `MlangPoll/admin`) | ✅ 완료 |
| 6단계 | 나머지 전체 (BBS 23개 skin, member/, lib/, shop/, sub/ 등) | ✅ 완료 |
| 7단계 | member 테이블 DROP | ⏸️ 의도적 보류 |

### 의도적으로 member 참조를 유지하는 파일

| 파일 | 이유 |
|------|------|
| `member/register_process.php` | users INSERT + member 이중 INSERT |
| `member/change_password.php` | users UPDATE + member sync UPDATE |
| `member/password_reset.php` | users UPDATE + member sync UPDATE |
| `admin/AdminConfig.php` | users UPDATE + member sync UPDATE |
| `bbs/PointChick.php` | member.money (포인트 시스템, users에 컬럼 없음) |

### 컬럼 매핑 (member → users)

```
member.no → users.id (PK)
member.id → users.username
member.pass → users.password (bcrypt)
member.name → users.name
member.phone1-2-3 → users.phone (통합)
member.hendphone1-2-3 → users.phone
member.sample6_postcode → users.postcode
member.sample6_address → users.address
member.sample6_detailAddress → users.detail_address
member.po1-7 → users.business_number/name/owner/type/item/address/tax_invoice_email
```

### Admin 패턴
```php
// 이전: SELECT * FROM member WHERE no='1'
// 현재: SELECT username AS id, password AS pass FROM users WHERE is_admin = 1 LIMIT 1
```

