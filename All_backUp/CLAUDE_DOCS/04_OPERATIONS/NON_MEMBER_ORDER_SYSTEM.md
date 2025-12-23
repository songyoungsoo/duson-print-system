# 비회원 주문 시스템 구현 가이드

## 📋 개요

회원과 비회원 모두 주문할 수 있도록 시스템을 확장하였습니다.
- **단일 테이블 구조**: 별도 비회원 테이블 없이 `is_member` 플래그로 구분
- **간단한 검증**: 전화번호 뒷자리 4자리만으로 교정확인 가능
- **이메일 필수**: 비회원도 이메일 입력 필수 (주문 확인 및 알림용)

## 🎯 핵심 기능

### 1. 회원/비회원 구분
- `mlangorder_printauto.is_member` 필드로 구분
  - `1`: 회원 주문
  - `0`: 비회원 주문

### 2. 비회원 필수 입력 항목
- **이름** (name)
- **이메일** (email) - 주문 확인 및 알림용
- **전화번호** (phone)
- **휴대폰** (Hendphone)
- **배송 주소** (Daum API 사용)

### 3. 교정확인 검증 방식
- **회원**: 전화번호 뒷자리 4자리
- **비회원**: 전화번호 뒷자리 4자리 (동일)
- **관리자**: 비밀번호 입력 없이 바로 접근

## ✅ 완료된 작업

### 1. 데이터베이스 스키마 변경

**파일**: `add_is_member_field.sql`

```sql
-- mlangorder_printauto 테이블에 is_member 필드 추가
ALTER TABLE mlangorder_printauto
ADD COLUMN IF NOT EXISTS is_member TINYINT(1) DEFAULT 0 COMMENT '회원여부: 0=비회원, 1=회원';

-- 기존 주문들은 모두 회원 주문으로 표시 (email이 users 테이블에 있는 경우)
UPDATE mlangorder_printauto o
SET is_member = 1
WHERE EXISTS (
    SELECT 1 FROM users u WHERE u.email = o.Hemail
);
```

### 2. 주문 처리 로직 수정

**파일**: `mlangorder_printauto/ProcessOrder_unified.php`

**변경 내용**:

#### Line 363-364: 회원 여부 자동 판별
```php
// 회원 여부 확인
$is_member = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']) ? 1 : 0;
```

#### Line 367-378: INSERT 쿼리에 is_member 추가
```php
$insert_query = "INSERT INTO mlangorder_printauto (
    no, Type, ImgFolder, Type_1, money_4, money_5, name, email, zip, zip1, zip2,
    phone, Hendphone, cont, date, OrderStyle, ThingCate,
    coating_enabled, coating_type, coating_price,
    folding_enabled, folding_type, folding_price,
    creasing_enabled, creasing_lines, creasing_price,
    additional_options_total,
    premium_options, premium_options_total,
    envelope_tape_enabled, envelope_tape_quantity, envelope_tape_price,
    envelope_additional_options_total,
    is_member
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
```

#### Line 435-446: bind_param에 is_member 값 추가
```php
mysqli_stmt_bind_param($stmt, 'isssiiissssssssssisissiiiiisiiiiii',
    $new_no, $product_type_name, $img_folder_path, $product_info, $item['st_price'], $item['st_price_vat'],
    $username, $email, $postcode, $address, $full_address,
    $phone, $hendphone, $final_cont, $date, $order_style, $thing_cate,
    $coating_enabled, $coating_type, $coating_price,
    $folding_enabled, $folding_type, $folding_price,
    $creasing_enabled, $creasing_lines, $creasing_price,
    $additional_options_total,
    $premium_options, $premium_options_total,
    $envelope_tape_enabled, $envelope_tape_quantity, $envelope_tape_price,
    $envelope_additional_options_total,
    $is_member
);
```

### 3. 비회원 주문 UI 지원

**파일**: `mlangorder_printauto/OnlineOrder_unified.php`

이미 구현되어 있는 기능:
- Line 535-607: 비로그인 사용자를 위한 입력 폼
- 로그인 상태에 따라 자동으로 UI 변경
- 회원은 자동 입력, 비회원은 수동 입력

### 4. 교정확인 검증 시스템

**파일**: `sub/verify_popup.php`

이미 구현되어 있는 검증 로직:

```php
// Line 32-68: 검증 로직
if ($is_admin) {
    // Admin: no password required
    $response['success'] = true;
    $response['redirect_url'] = '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' . $order_no;
} else {
    // Regular user/guest: verify phone last 4 digits
    $query = "SELECT name, phone, Hendphone FROM mlangorder_printauto WHERE no = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $order_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($order = mysqli_fetch_array($result)) {
        // Extract last 4 digits from phone
        $phone_last4 = '';
        $hendphone_last4 = '';

        if (!empty($order['phone'])) {
            $phone_last4 = substr(preg_replace('/[^0-9]/', '', $order['phone']), -4);
        }

        if (!empty($order['Hendphone'])) {
            $hendphone_last4 = substr(preg_replace('/[^0-9]/', '', $order['Hendphone']), -4);
        }

        // Verify phone last 4 digits only
        if ($password === $phone_last4 || $password === $hendphone_last4) {
            $response['success'] = true;
            $response['redirect_url'] = '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' . $order_no;
        } else {
            $response['message'] = '전화번호 뒷자리 4자리가 일치하지 않습니다.';
        }
    }
}
```

## 🚀 배포 순서

### Step 1: 데이터베이스 스키마 업데이트

1. phpMyAdmin 접속: http://dsp1830.shop/phpmyadmin/
2. 데이터베이스 `dsp1830` 선택
3. SQL 탭 클릭
4. `add_is_member_field.sql` 파일 내용 복사 후 실행

### Step 2: 파일 업로드

FTP로 다음 파일 업로드:
```
mlangorder_printauto/ProcessOrder_unified.php
```

### Step 3: 테스트

#### 3.1 비회원 주문 테스트
1. 로그아웃 상태로 주문 진행
2. 비회원 정보 입력 폼이 표시되는지 확인
3. 필수 항목 입력:
   - 이름
   - 이메일
   - 전화번호
   - 휴대폰
   - 배송 주소
4. 주문 완료 후 데이터베이스 확인:
```sql
SELECT no, name, email, phone, Hendphone, is_member
FROM mlangorder_printauto
ORDER BY no DESC LIMIT 1;
```
Expected: `is_member = 0`

#### 3.2 회원 주문 테스트
1. 로그인 상태로 주문 진행
2. 회원 정보가 자동으로 입력되는지 확인
3. 주문 완료 후 데이터베이스 확인:
```sql
SELECT no, name, email, phone, Hendphone, is_member
FROM mlangorder_printauto
ORDER BY no DESC LIMIT 1;
```
Expected: `is_member = 1`

#### 3.3 교정확인 검증 테스트
1. 교정확인 페이지 접속: http://dsp1830.shop/sub/checkboard.php?page=3
2. 주문 번호 클릭
3. 전화번호 뒷자리 4자리 입력
4. 정상적으로 주문 상세 페이지로 이동되는지 확인

## 📊 데이터베이스 구조

### mlangorder_printauto 테이블

| 필드명 | 타입 | 설명 | 비고 |
|--------|------|------|------|
| is_member | TINYINT(1) | 회원 여부 | 0=비회원, 1=회원 |
| name | VARCHAR | 주문자 이름 | 필수 |
| email | VARCHAR | 이메일 | 필수 (주문 확인용) |
| phone | VARCHAR | 전화번호 | 필수 (검증용) |
| Hendphone | VARCHAR | 휴대폰 | 필수 (검증용) |
| zip | VARCHAR | 우편번호 | 필수 |
| zip1 | VARCHAR | 주소 | 필수 |
| zip2 | VARCHAR | 상세주소 | 필수 |

## 🔐 보안 고려사항

### 1. 검증 방식
- 전화번호 뒷자리 4자리만으로 간단하게 검증
- 관리자는 별도 검증 없이 접근 가능
- SQL Injection 방지: Prepared Statement 사용

### 2. 개인정보 보호
- 이메일 마스킹 표시 (예: abc***@gmail.com)
- 비회원 주문도 이메일 필수 입력 (주문 확인 및 알림용)

### 3. 세션 관리
- 회원: `$_SESSION['user_id']` 존재 여부로 판별
- 비회원: 세션에 user_id가 없는 경우

## 🎯 주요 특징

✅ **단일 테이블 구조**: 별도 비회원 테이블 불필요, `is_member` 플래그로 구분
✅ **간단한 검증**: 전화번호 뒷자리 4자리만으로 교정확인 가능
✅ **이메일 필수**: 비회원도 이메일 입력 필수 (주문 확인 및 알림용)
✅ **기존 시스템과 호환**: 현재 검증 시스템(`verify_popup.php`)과 완벽하게 호환
✅ **자동 판별**: 세션 정보로 회원/비회원 자동 구분

## 📝 향후 개선 사항

- [ ] 비회원 주문 조회 페이지 추가
- [ ] 비회원 주문 알림 이메일 발송
- [ ] 비회원 주문 통계 리포트
- [ ] 휴대폰 SMS 인증 추가 (선택사항)

---

**작성일**: 2025-01-31
**작성자**: Claude Code
**관련 이슈**: 비회원 주문 시스템 구현
