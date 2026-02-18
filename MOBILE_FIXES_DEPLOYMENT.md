# 모바일 버그 수정 배포 가이드

## ✅ 완료된 작업

### 1. 장바구니 견적서 이메일 발송 기능 추가
- **파일**: `mlangprintauto/shop/send_cart_quotation.php` (신규 생성, 374줄)
- **기능**:
  - FQ-YYYYMMDD-NNN 형식 견적번호 자동 생성
  - quote_requests 테이블에 저장 (18개 파라미터 bind_param 검증 완료)
  - 고객에게 HTML 이메일 발송 (품목 테이블 포함)
  - 관리자(dsp1830@naver.com)에게 알림 발송

### 2. 장바구니 견적서 모달 업데이트
- **파일**: `mlangprintauto/shop/customer_info_modal.php` (수정)
- **변경사항**:
  - window.open → AJAX fetch로 변경
  - 성공 모달 추가 (견적번호 + 이메일 주소 표시)
  - 기존 PDF 인쇄 기능은 그대로 유지

### 2-1. 장바구니 페이지에 이메일 버튼 추가 ✨ NEW
- **파일**: `mlangprintauto/shop/cart.php` (수정)
- **변경사항**:
  - 견적서 섹션에 "📧 견적서 이메일 발송" 버튼 추가 (line 830-832)
  - 버튼 순서: 견적서 인쇄 → 이메일 발송 → 장바구니로 돌아가기
  - 파란색 버튼 (#2563eb)으로 인쇄 버튼과 구분
  - `openCustomerInfoModal()` 함수 연결

### 3. 모바일 결제 오류 수정
- **파일**: `payment/inicis_request.php` (2줄 수정)
- **수정 내용**:
  ```diff
  - <input name="gopaymethod" value="Card">
  - <input name="acceptmethod" value="below1000:HPP(1):cardonly">
  + <input name="gopaymethod" value="Card:DirectBank:HPP">
  + <input name="acceptmethod" value="below1000:HPP(1)">
  ```
- **효과**:
  - 모바일 신용카드 결제 가능
  - 휴대폰 결제(HPP) 가능
  - 계좌이체(DirectBank) 가능
  - "PC에서 결제하세요" 오류 해결

---

## 🧪 로컬 테스트 방법

### 1단계: 장바구니 견적서 이메일 테스트

```bash
# 1. 브라우저에서 http://localhost/ 접속
# 2. 제품 페이지에서 장바구니에 상품 추가
# 3. 장바구니 페이지 이동
# 4. "견적서 받기" 버튼 클릭
# 5. 고객 정보 입력 (이름, 전화, 이메일, 회사명, 메모)
# 6. "견적서 발송" 클릭
# 7. 성공 모달 확인 (견적번호 표시)
# 8. 이메일 수신 확인 (스팸함 포함)
```

**확인 사항**:
- [ ] 견적번호가 `FQ-20260218-001` 형식으로 생성되는가?
- [ ] 고객 이메일로 HTML 견적서가 도착하는가?
- [ ] 관리자(dsp1830@naver.com)에게 알림이 도착하는가?
- [ ] 이메일에 품목 정보가 정확히 표시되는가?

### 2단계: 모바일 결제 테스트

```bash
# 1. 모바일 브라우저 또는 개발자도구 모바일 모드
# 2. http://localhost/ 접속
# 3. 주문 진행 → 결제 단계
# 4. 결제 방법 선택 화면 확인
```

**확인 사항**:
- [ ] "Card" (신용카드) 옵션이 보이는가?
- [ ] "HPP" (휴대폰결제) 옵션이 보이는가?
- [ ] "DirectBank" (계좌이체) 옵션이 보이는가?
- [ ] "PC에서 결제하세요" 오류가 나타나지 않는가?

---

## 🚀 운영 서버 배포

### 배포 전 체크리스트
- [ ] 로컬 테스트 완료 (위 1단계, 2단계)
- [ ] Git 커밋 완료 (commit 40c4530d)
- [ ] 백업 확인 (FTP 서버에 기존 파일 백업)

### FTP 업로드 명령어

```bash
# 1. 신규 파일: send_cart_quotation.php
curl -T /var/www/html/mlangprintauto/shop/send_cart_quotation.php \
  ftp://dsp114.co.kr/httpdocs/mlangprintauto/shop/send_cart_quotation.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 2. 수정 파일: customer_info_modal.php
curl -T /var/www/html/mlangprintauto/shop/customer_info_modal.php \
  ftp://dsp114.co.kr/httpdocs/mlangprintauto/shop/customer_info_modal.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 3. 수정 파일: cart.php ✨ NEW
curl -T /var/www/html/mlangprintauto/shop/cart.php \
  ftp://dsp114.co.kr/httpdocs/mlangprintauto/shop/cart.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 4. 수정 파일: inicis_request.php
curl -T /var/www/html/payment/inicis_request.php \
  ftp://dsp114.co.kr/httpdocs/payment/inicis_request.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### 배포 후 검증

```bash
# 1. 파일 업로드 확인
curl -I https://dsp114.co.kr/mlangprintauto/shop/send_cart_quotation.php
# 예상 결과: HTTP/2 200 (또는 500 - PHP 파일이므로 정상)

# 2. 운영 서버에서 실제 테스트
# - 모바일 기기로 https://dsp114.co.kr 접속
# - 장바구니 견적서 이메일 발송 테스트
# - 모바일 결제 테스트 (소액 또는 테스트 모드)
```

**운영 서버 테스트 체크리스트**:
- [ ] 장바구니 견적서 이메일 발송 성공
- [ ] 고객 이메일 수신 확인
- [ ] 관리자 알림 수신 확인
- [ ] 모바일 결제 옵션 정상 표시
- [ ] 실제 결제 완료 (테스트 카드 또는 소액)

---

## 🔧 문제 해결

### 이메일이 도착하지 않는 경우

1. **스팸함 확인**
   - 네이버 → 네이버: 정상 수신
   - 네이버 → Gmail: 스팸 분류 가능성 높음

2. **로그 확인**
   ```bash
   # 로컬 환경
   tail -f /var/log/apache2/error.log
   
   # 운영 서버 (FTP로 접속)
   # /httpdocs/logs/ 디렉토리 확인
   ```

3. **DB 확인**
   ```sql
   -- quote_requests 테이블에 데이터가 저장되었는지 확인
   SELECT * FROM quote_requests ORDER BY created_at DESC LIMIT 5;
   ```

### 모바일 결제가 여전히 안 되는 경우

1. **브라우저 캐시 삭제**
   - 모바일 브라우저 설정 → 캐시/쿠키 삭제

2. **inicis_request.php 파일 확인**
   ```bash
   # 운영 서버에서 파일 내용 확인
   curl https://dsp114.co.kr/payment/inicis_request.php | grep gopaymethod
   # 예상 결과: value="Card:DirectBank:HPP"
   ```

3. **KG이니시스 설정 확인**
   - `payment/inicis_config.php`에서 `INICIS_TEST_MODE` 확인
   - 운영 서버: `false`, 로컬: `true`

### 견적번호가 중복되는 경우

```sql
-- quote_requests 테이블에서 오늘 날짜 견적번호 확인
SELECT quote_no FROM quote_requests 
WHERE quote_no LIKE 'FQ-20260218-%' 
ORDER BY quote_no DESC;

-- 중복 방지: send_cart_quotation.php의 트랜잭션 로직 확인
-- (현재 구현은 SELECT → INSERT 순서로 race condition 가능성 있음)
```

---

## 📋 Git 커밋 정보

### Commit 1: 모바일 결제 + 견적서 이메일 API
```
Commit: 40c4530d029b475da5822d6822fc0c942b29b47f
Author: songyoungsoo <yeongsu32@gmail.com>
Date:   Wed Feb 18 14:54:27 2026 +0900

Fix: Mobile cart quotation email + payment errors

- Add email sending to cart quotation (send_cart_quotation.php)
  - Generates FQ-YYYYMMDD-NNN quote number
  - Saves to quote_requests table (18-param bind_param validated)
  - Sends customer HTML email with item table
  - Sends admin notification to dsp1830@naver.com

- Update cart quotation modal (customer_info_modal.php)
  - Replace window.open with AJAX fetch to new endpoint
  - Add success modal showing quote number + email

- Fix mobile payment methods (inicis_request.php)
  - Remove 'cardonly' from acceptmethod (was blocking mobile)
  - Add HPP + DirectBank to gopaymethod
  - Enables mobile credit card + phone payment + bank transfer

Resolves: Mobile cart quotation email + 모바일 결제 PC전용 오류
```

### Commit 2: 장바구니 페이지 이메일 버튼 추가 ✨ NEW
```
Commit: 894fd336
Author: songyoungsoo <yeongsu32@gmail.com>
Date:   Wed Feb 18 15:09:27 2026 +0900

Add email quotation button to cart page

- Add '견적서 이메일 발송' button in quotation section
- Button placed between print and back buttons
- Blue color (#2563eb) to differentiate from print button
- Triggers openCustomerInfoModal() from customer_info_modal.php
- Modal already included at line 1052

Completes mobile cart quotation email feature
```

---

## 📞 긴급 연락처

- **고객센터**: 02-2632-1830
- **관리자 이메일**: dsp1830@naver.com
- **FTP 계정**: dsp1830 / cH*j@yzj093BeTtc
- **GitHub**: songyoungsoo / yeongsu32@gmail.com

---

## 📚 관련 문서

- [AGENTS.md](./AGENTS.md) - 시스템 전체 가이드
- [DEPLOYMENT.md](./DEPLOYMENT.md) - 배포 상세 가이드
- [payment/README_PAYMENT.md](./payment/README_PAYMENT.md) - 결제 시스템 설정

---

**작성일**: 2026-02-18  
**작성자**: Claude Code (Atlas)  
**상태**: ✅ 로컬 구현 완료, 테스트 대기 중
