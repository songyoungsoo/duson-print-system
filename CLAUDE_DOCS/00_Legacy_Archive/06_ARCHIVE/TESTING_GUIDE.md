# 🧪 배포 전 테스트 가이드

## 📍 주요 URL 목록

### 🛒 고객 사이트
| 기능 | URL |
|------|-----|
| 메인 페이지 | `http://localhost/` |
| 로그인 | `http://localhost/mlangprintauto/includes/login.php` |
| 회원가입 | `http://localhost/mlangprintauto/includes/register.php` |
| 마이페이지 | `http://localhost/mypage/` |
| 주문 내역 | `http://localhost/mypage/orders.php` |
| 견적 내역 | `http://localhost/mypage/quotes.php` |
| 장바구니 | `http://localhost/mlangprintauto/cart/` |

### 📦 제품 페이지 (9개 제품)
| 제품명 | URL |
|--------|-----|
| 명함 | `http://localhost/mlangprintauto/namecard/` |
| 전단지 | `http://localhost/mlangprintauto/inserted/` |
| 봉투 | `http://localhost/mlangprintauto/envelope/` |
| 스티커 | `http://localhost/mlangprintauto/sticker_new/` |
| 자석스티커 | `http://localhost/mlangprintauto/msticker/` |
| 카다록 | `http://localhost/mlangprintauto/cadarok/` |
| 포스터 | `http://localhost/mlangprintauto/littleprint/` |
| 상품권 | `http://localhost/mlangprintauto/merchandisebond/` |
| NCR양식 | `http://localhost/mlangprintauto/ncrflambeau/` |

### 💰 견적 시스템
| 기능 | URL |
|------|-----|
| 견적서 작성 | `http://localhost/mlangprintauto/quote/` |
| 견적 상세 | `http://localhost/mlangprintauto/quote/detail.php?id={quote_id}` |

### 🔧 관리자 페이지
| 기능 | URL |
|------|-----|
| 대시보드 | `http://localhost/admin/mlangprintauto/` |
| 주문 관리 | `http://localhost/admin/mlangprintauto/orderlist_improved.php` |
| 견적 관리 | `http://localhost/admin/mlangprintauto/quote_manager.php` |
| 제품 관리 | `http://localhost/admin/mlangprintauto/product_manager.php` |
| 명함 관리 | `http://localhost/admin/mlangprintauto/namecard_admin.php` |
| 전단지 관리 | `http://localhost/admin/mlangprintauto/inserted_admin.php` |

---

## 🎯 E2E 테스트 시나리오

### 시나리오 1: 일반 주문 프로세스 (명함)

#### 1단계: 회원가입/로그인
```
1. http://localhost/mlangprintauto/includes/register.php 접속
2. 회원가입:
   - 아이디: testuser
   - 비밀번호: test1234
   - 이름: 테스트유저
   - 이메일: test@example.com
   - 전화번호: 010-1234-5678
3. 로그인
```

#### 2단계: 제품 선택 및 견적 계산
```
1. http://localhost/mlangprintauto/namecard/ 접속
2. 옵션 선택:
   - 용지: 랑데부 250g (또는 다른 용지)
   - 규격: 일반명함 (90x50)
   - 인쇄면: 양면
   - 수량: 0.2연 (200매)
   - 작업방식: 인쇄만
3. "가격 계산" 버튼 클릭
4. 가격 확인 (예: 22,000원)
```

#### 3단계: 파일 업로드 및 주문
```
1. "🛒 파일 업로드 및 주문하기" 버튼 클릭
2. 업로드 모달에서:
   - 파일 선택 (이미지 또는 PDF)
   - 파일 업로드
3. 고객 정보 입력:
   - 이름: 테스트유저
   - 이메일: test@example.com
   - 전화번호: 010-1234-5678
   - 배송지 주소 입력
4. "주문하기" 버튼 클릭
5. 주문 완료 페이지 확인
   - 주문번호 표시 확인
```

#### 4단계: 마이페이지에서 주문 확인
```
1. http://localhost/mypage/orders.php 접속
2. 방금 생성한 주문 확인:
   - 주문번호
   - 제품명: 명함
   - 상태: 견적접수
   - 금액 확인
3. 주문 상세 클릭하여 세부 정보 확인
```

---

### 시나리오 2: 견적 요청 → 주문 전환 프로세스

#### 1단계: 견적 요청 (전단지)
```
1. http://localhost/mlangprintauto/inserted/ 접속
2. 옵션 선택:
   - 인쇄색상: 4도(컬러)
   - 용지: 스노우화이트 150g
   - 규격: A4 (210x297)
   - 인쇄면: 단면
   - 수량: 1.0연 (4000매)
   - 매수: 4000
   - 작업방식: 인쇄만
3. "가격 계산" 버튼 클릭
4. "💰 견적 요청" 버튼 클릭
5. 견적 페이지로 리다이렉트 확인
```

#### 2단계: 견적서 작성
```
1. http://localhost/mlangprintauto/quote/ 접속
2. 장바구니에 추가된 품목 확인
3. 고객 정보 입력:
   - 회사명: 테스트회사
   - 담당자: 홍길동
   - 이메일: customer@example.com
   - 전화번호: 02-1234-5678
   - 배송 주소: 서울시 강남구 테헤란로 123
4. "견적서 생성" 버튼 클릭
5. 견적 번호 확인 (예: Q-20251225-001)
```

#### 3단계: 마이페이지에서 견적 확인
```
1. http://localhost/mypage/quotes.php 접속
2. 생성한 견적서 확인:
   - 견적번호
   - 상태: 임시 저장 또는 발송됨
   - 금액 확인
3. "상세보기" 클릭하여 품목 확인
```

#### 4단계: 견적 → 주문 전환
```
1. 견적 내역에서 "🛒 주문하기" 버튼 클릭
2. 확인 모달 표시:
   - 견적번호 확인
   - "주문 전환" 버튼 클릭
3. 로딩 스피너 확인 ("주문 처리 중...")
4. 성공 메시지 확인:
   - "✅ 주문 전환이 완료되었습니다!"
   - 주문 번호 표시
   - 생성된 주문 개수 표시
5. 페이지 새로고침 후 상태가 "주문 완료"로 변경 확인
```

#### 5단계: 주문 내역 확인
```
1. http://localhost/mypage/orders.php 접속
2. 방금 전환된 주문 확인
3. 견적서 번호로 연결 확인
```

---

### 시나리오 3: 관리자 페이지 확인

#### 1단계: 관리자 로그인
```
1. http://localhost/admin/ 접속
2. 관리자 계정으로 로그인
   (계정 정보가 없으면 아래 "관리자 계정 생성" 참고)
```

#### 2단계: 대시보드 확인
```
1. http://localhost/admin/mlangprintauto/ 접속
2. 통계 카드 확인:
   - 전체 주문
   - 견적 접수
   - 작업 중
   - 전체 견적
3. 퀵링크 확인:
   - 주문 관리
   - 제품 관리
   - 견적 관리
```

#### 3단계: 주문 관리
```
1. http://localhost/admin/mlangprintauto/orderlist_improved.php 접속
2. 주문 리스트 확인:
   - 테스트로 생성한 주문들 표시
   - 주문번호, 고객명, 제품, 상태, 금액
3. 검색 기능 테스트:
   - 주문번호로 검색
   - 고객명으로 검색
4. 필터 기능 테스트:
   - 상태별 필터
   - 제품별 필터
5. 주문 상세 보기:
   - 주문 클릭
   - Type_1 JSON 데이터 확인
   - 파일 업로드 정보 확인
```

#### 4단계: 견적 관리
```
1. http://localhost/admin/mlangprintauto/quote_manager.php 접속
2. 견적 리스트 확인:
   - 테스트로 생성한 견적들 표시
   - 견적번호, 고객명, 상태, 금액
3. 검색 기능 테스트:
   - 견적번호로 검색
   - 고객명으로 검색
4. 상태별 필터:
   - 임시 저장
   - 발송됨
   - 승인됨
   - 주문 완료
5. 견적 상세 보기:
   - 견적 클릭
   - 품목 리스트 확인
   - 전환된 주문번호 확인 (있는 경우)
```

#### 5단계: 제품별 관리 페이지
```
1. 명함 관리:
   - http://localhost/admin/mlangprintauto/namecard_admin.php
   - 명함 주문만 필터링되어 표시
   - 제품 특화 정보 확인

2. 전단지 관리:
   - http://localhost/admin/mlangprintauto/inserted_admin.php
   - 전단지 주문만 표시
   - 연수/매수 정보 확인
```

---

## 🔧 관리자 계정 생성

관리자 계정이 없는 경우 MySQL에서 직접 생성:

```sql
-- MySQL/phpMyAdmin에서 실행
INSERT INTO users (username, password, email, phone, role, created_at)
VALUES (
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- 비밀번호: password
    'admin@dsp1830.shop',
    '02-1234-5678',
    'admin',
    NOW()
);
```

또는 간단한 비밀번호 해시 생성:
```php
<?php
// create_admin.php 파일 생성
echo password_hash('admin1234', PASSWORD_DEFAULT);
// 출력된 해시값을 위 SQL의 password 필드에 사용
?>
```

**관리자 로그인 정보:**
- 아이디: `admin`
- 비밀번호: `password` 또는 직접 설정한 비밀번호

---

## 📝 테스트 체크리스트

### ✅ 고객 사이트 기능
- [ ] 회원가입 → 로그인 가능
- [ ] 제품 페이지 접속 (9개 전체)
- [ ] 가격 계산 정상 작동
- [ ] 파일 업로드 가능
- [ ] 주문 완료 (주문번호 생성)
- [ ] 마이페이지 주문 내역 표시
- [ ] 장바구니 추가 가능

### ✅ 견적 시스템
- [ ] 제품에서 "견적 요청" 버튼 클릭 (9개 전체)
- [ ] 견적 페이지로 리다이렉트
- [ ] 견적서 생성 가능
- [ ] 마이페이지 견적 내역 표시
- [ ] 견적 → 주문 전환 가능
- [ ] 확인 모달 표시
- [ ] 로딩 스피너 표시
- [ ] 성공 메시지 표시
- [ ] 주문 내역에 전환된 주문 표시

### ✅ 관리자 페이지
- [ ] 관리자 로그인 가능
- [ ] 대시보드 통계 정상 표시
- [ ] 주문 리스트 표시
- [ ] 주문 검색 기능
- [ ] 주문 상태별 필터
- [ ] 주문 상세 보기
- [ ] 견적 리스트 표시
- [ ] 견적 검색 기능
- [ ] 견적 상태별 필터
- [ ] 견적 상세 보기

### ✅ 이메일 발송
- [ ] 주문 완료 이메일 발송 (선택적)
- [ ] 견적 → 주문 전환 이메일 발송

---

## 🐛 디버그 모드

개발 중 문제 발생 시:

### PHP 에러 표시
```php
// 파일 상단에 추가
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### MySQL 쿼리 확인
```php
// db.php에서 쿼리 로깅
mysqli_query($db, $query) or die(mysqli_error($db));
```

### JavaScript 콘솔 확인
```
브라우저 개발자 도구 (F12) → Console 탭
네트워크 요청 확인 → Network 탭
```

---

## 📊 테스트 데이터 확인

### phpMyAdmin에서 확인
```
1. http://localhost/phpmyadmin/ 접속
2. 데이터베이스: dsp1830 선택
3. 주요 테이블:
   - users: 회원 정보
   - shop_temp: 장바구니
   - mlangorder_printauto: 주문
   - quotes: 견적
   - quote_items: 견적 품목
```

### 주요 쿼리
```sql
-- 최근 주문 확인
SELECT * FROM mlangorder_printauto ORDER BY no DESC LIMIT 10;

-- 최근 견적 확인
SELECT * FROM quotes ORDER BY id DESC LIMIT 10;

-- 견적 품목 확인
SELECT * FROM quote_items WHERE quote_id = {견적ID};

-- 사용자 주문 확인
SELECT * FROM mlangorder_printauto WHERE email = 'test@example.com';
```

---

## 🚨 문제 해결

### 로그인 안 됨
```
1. 세션 확인: session_start() 호출 여부
2. 비밀번호 해시 확인: password_verify() 사용
3. 쿠키 설정 확인: 브라우저 쿠키 삭제 후 재시도
```

### 가격 계산 안 됨
```
1. JavaScript 콘솔 확인 (F12)
2. calculate_price_ajax.php 응답 확인
3. mlangprintauto_transactioncate 테이블 데이터 확인
```

### 파일 업로드 안 됨
```
1. ImgFolder 디렉토리 권한 확인 (777 또는 755)
2. PHP upload_max_filesize 확인
3. StandardUploadHandler.php 로그 확인
```

### 주문 생성 안 됨
```
1. mlangorder_printauto 테이블 구조 확인
2. bind_param 개수 일치 여부 (3번 검증!)
3. ProcessOrder_unified.php 로그 확인
```

### 견적 → 주문 전환 실패
```
1. convert_to_order.php API 응답 확인
2. quotes 테이블 status 확인
3. quote_items 테이블 데이터 확인
4. Type_1 JSON 구조 확인
```

---

## 📞 추가 지원

테스트 중 문제가 발생하면:
1. 브라우저 콘솔 에러 메시지 확인
2. PHP 에러 로그 확인 (`/var/log/apache2/error.log`)
3. 구체적인 에러 메시지와 함께 질문

**Happy Testing! 🎉**
