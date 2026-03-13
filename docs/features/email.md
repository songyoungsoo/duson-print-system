## 📧 Email System (주문 완료 이메일)

### 시스템 구성

| 파일 | 용도 |
|------|------|
| `mlangorder_printauto/mailer.lib.php` | PHPMailer 래퍼 (SMTP 설정) |
| `mlangorder_printauto/send_order_email.php` | 이메일 발송 API (주문 완료 이메일, 일시 포함) |
| `mlangorder_printauto/OrderComplete_universal.php` | 주문 완료 시 자동 발송 호출 + 카드결제 알림창 |
| `payment/inicis_return.php` | 카드결제 완료 시 고객/관리자 이메일 발송 |
| `mlangorder_printauto/PHPMailer/` | PHPMailer 라이브러리 |

### SMTP 설정 (네이버)

```php
$mail->Host = "smtp.naver.com";
$mail->Port = 465;
$mail->SMTPSecure = "ssl";
$mail->Username = "dsp1830";
$mail->Password = "2CP3P5BTS83Y";
```

### 이메일 발송 흐름

**일반 주문 (계좌이체/현금):**
```
1. 주문 완료 → OrderComplete_universal.php 로드
2. JavaScript에서 send_order_email.php로 POST 요청
3. send_order_email.php에서 HTML 템플릿 생성 (주문건수, 금액, 일시 포함)
4. mailer() 함수로 네이버 SMTP 통해 발송
5. 고객 이메일로 주문 확인 메일 수신
```

**카드결제:**
```
1. 결제 완료 → inicis_return.php 콜백
2. 고객 이메일 발송 (결제일시, 거래번호, 금액 포함)
3. 관리자 이메일 알림 발송 (dsp1830@naver.com)
4. OrderComplete_universal.php로 리다이렉트 (payment=card)
5. 알림창 표시: "✅ 카드 결제가 완료되었습니다!"
```

### 자동 발송 조건

- 최초 주문 완료 시에만 발송 (결제 취소/실패 시 발송 안 함)
- `sessionStorage`로 중복 발송 방지
- 이메일 주소 유효성 검증 후 발송

### mailer() 함수 시그니처

```php
function mailer($fname, $fmail, $to, $subject, $content, $type=1, $file, $cc="", $bcc="")
// $type: 0=text, 1=html, 2=text+html
// $file: 첨부파일 배열 또는 "" (빈 문자열)
```

### PHP 8.2 호환성 패치 (2026-02-05)

`PHPMailer/PHPMailer.php` Line 3612:
```php
// 변경 전 (PHP 8.2에서 오류)
filter_var('http://' . $host, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED)

// 변경 후 (PHP 8.2 호환)
filter_var('http://' . $host, FILTER_VALIDATE_URL)
```

### Critical Rules

1. ❌ `mailer()` 호출 시 `$file` 파라미터 생략 금지 → 빈 문자열 `""` 필수
   - ⚠️ 빈 배열 `[]` 사용 시 오류 가능성 (2026-03-13 수정)
2. ❌ 복잡한 HTML 템플릿에서 정의되지 않은 변수 사용 금지
3. ✅ 운영 서버 PHP 버전 확인 필수 (현재 8.2.30)

### 이메일 템플릿 구성 (2026-03-13 업데이트)

**주문 완료 이메일** (`send_order_email.php`):
- 주문 건수
- 결제 금액 (VAT포함)
- **주문 일시** (NEW: `date('Y.m.d H:i')`)
- 주문 목록 (상품명, 상세, 금액)
- 고객 정보
- 입금 안내

**카드결제 완료 이메일** (`inicis_return.php`):
- 주문번호
- 결제금액
- 결제수단
- **결제일시** (`date('Y.m.d H:i')`)
- 거래번호 (TID)
- 제작 진행 안내
- 주문 내역 확인 링크


---

## 📧 이메일 캠페인 시스템 (Email Campaign System)

### 시스템 개요

대시보드에서 회원에게 일괄 이메일을 발송하는 시스템.

| 항목 | 값 |
|------|-----|
| **대시보드 UI** | `/dashboard/email/index.php` |
| **API** | `/dashboard/api/email.php` (12개 action) |
| **이미지 업로드** | `/dashboard/email/uploads/` |
| **사이드바 메뉴** | 📧 이메일 발송 (소통·견적 그룹) |
| **SMTP** | 네이버 (`dsp1830@naver.com`) |

### DB 테이블 (3개)

| 테이블 | 용도 |
|--------|------|
| `email_campaigns` | 캠페인 (제목, 본문, 상태, 수신자수, 성공/실패 카운트) |
| `email_send_log` | 개별 발송 로그 (수신자별 상태, 에러 메시지) |
| `email_templates` | 저장된 템플릿 (이름, 제목, HTML 본문) |

### API 엔드포인트 (`/dashboard/api/email.php`)

| action | Method | 용도 |
|--------|--------|------|
| `get_recipients` | GET | 수신자 목록/카운트 (전체/필터/수동) |
| `send` | POST | 캠페인 생성 + 발송 시작 |
| `send_batch` | POST | 배치 발송 (100명씩) |
| `send_test` | POST | dsp1830@naver.com으로 테스트 발송 |
| `save_draft` | POST | 임시저장 |
| `campaigns` | GET | 캠페인 목록 (페이지네이션) |
| `campaign_detail` | GET | 캠페인 상세 + 발송 로그 |
| `templates` | GET | 템플릿 목록 |
| `load_template` | GET | 템플릿 불러오기 |
| `save_template` | POST | 템플릿 저장/수정 |
| `delete_template` | POST | 템플릿 삭제 |
| `upload_image` | POST | 이미지 업로드 (5MB, JPG/PNG/GIF/WebP) |

### WYSIWYG 에디터 (2026-02-12)

3가지 편집 모드:
- **편집기** (기본): `contenteditable` div + 서식 도구모음
- **HTML편집**: raw textarea (고급 사용자용)
- **미리보기**: 렌더링된 HTML 확인

도구모음: B, I, U, H1, H2, P, 🔗링크, 📷이미지업로드, •목록, 1.목록, ─구분선, 색상, ✕서식제거

```javascript
// 모드 전환 시 콘텐츠 자동 동기화
function getEmailBody() {
    if (currentEditorMode === 'wysiwyg') {
        document.getElementById('email-body').value = 
            document.getElementById('wysiwyg-editor').innerHTML;
    }
    return document.getElementById('email-body').value.trim();
}
```

### 네이버 SMTP 제한 (Critical Rules)

```
1회 최대: 100명
일일 한도: ~500통 (안전 기준)
배치 간격: 3초 대기 (클라이언트 측)
Gmail 수신: ⚠️ 스팸 분류 가능성
앱 비밀번호: 2CP3P5BTS83Y (mailer.lib.php에 설정됨)
```

### 발송 흐름

```
1. UI에서 "이메일 발송" 클릭
2. action=send → email_campaigns INSERT + email_send_log INSERT (수신자별)
3. action=send_batch → 100명씩 mailer() 호출 → 성공/실패 로그 UPDATE
4. 3초 대기 → 다음 배치 반복
5. 전체 완료 → campaign status='completed'
```

### 수신자 필터

- **전체 회원**: `users` 테이블에서 admin/test/봇 제외 (328명, 2026-02-12 기준)
- **조건 필터**: 최근 로그인 기간 + 이메일 도메인
- **직접 입력**: 쉼표 구분 이메일 주소

### `{{name}}` 치환

이메일 본문에서 `{{name}}`은 수신자 이름으로 자동 치환됨. 이름 없으면 '고객'으로 표시.

### 회원 이메일 현황 (2026-02-12 기준)

- 총 328명 (고유 이메일 기준, admin/test 제외)
- naver.com: 193명, hanmail.net: 37명, gmail.com: 28명, daum.net: 14명
- ⚠️ 오타 이메일 4건: `nate.ocm`, `naver.vom`, `naver.coml`, `naver.co.kr`
- 289명 미로그인 (구 사이트에서 마이그레이션된 회원)

### 기본 템플릿 (2개)

1. **설날 인사**: 2026 구정 인사 + 새 홈페이지 안내
2. **새 홈페이지 오픈**: dsp114.co.kr 오픈 안내 (2월 23일)

### 이메일 푸터 (고정)

```
두손기획인쇄 | 서울특별시 영등포구 영등포로 36길9 송호빌딩 1층 두손기획인쇄 | Tel. 02-2632-1830
본 메일은 두손기획인쇄 회원님께 발송됩니다. 수신을 원하지 않으시면 [여기]를 클릭해주세요.
```

### 미완료 작업

- [ ] 프로덕션 배포 (dsp114.co.kr FTP)
- [ ] 오타 이메일 4건 수정 (users 테이블)
- [ ] 실제 회원 발송 (2단계: 2/13 설날 + 2/23 오픈)

