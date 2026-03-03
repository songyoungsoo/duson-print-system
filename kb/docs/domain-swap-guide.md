# 🎯 도메인 스왑 (dsp114.com ↔ dsp114.co.kr) 완벽 가이드

## 📊 검색 결과 요약

**총 검색 항목**: 6개 병렬 에이전트 + 4개 직접 검색
- ✅ 도메인 설정 파일: 15개 파일 발견
- ✅ 결제 시스템: 8개 파일 (4개 수정 필요)
- ✅ 이메일 템플릿: 9개 파일 (5개 수정 필요)
- ✅ JavaScript: 6개 파일 (5개 수정 필요)
- ✅ .htaccess: 7개 파일 (모두 안전)
- ✅ DB 스키마: 5개 테이블 (3개 UPDATE 필요)

---

## 🚨 CRITICAL: 바로 수정 필요한 파일 (우선순위 순)

### 1위: 결제 시스템 (4개 파일)

| 파일 | 라인 | 현재 URL | 수정 방법 |
|------|------|---------|----------|
| `/payment/inicis_config.production.php` | 53-54 | `https://dsp114.co.kr/payment/inicis_return.php` | `$baseUrl . '/payment/inicis_return.php'` |
| `/payment/inicis_return.php` | 414 | `https://dsp114.co.kr/admin.php` | `$protocol . $_SERVER['HTTP_HOST'] . '/admin.php'` |
| `/payment/inicis_request.php` | 168 | `guest@dsp114.co.kr` | `'guest@' . $_SERVER['HTTP_HOST']` |
| `/payment/request.php` | 109 | `guest@dsp114.co.kr` | `'guest@' . $_SERVER['SERVER_NAME']` |

### 2위: 이메일 시스템 (5개 파일)

| 파일 | 라인 | 현재 URL | 수정 방법 |
|------|------|---------|----------|
| `/includes/quote_request_api.php` | 223 | `https://dsp114.co.kr/mlangprintauto/{type}/` | `$baseUrl . '/mlangprintauto/{type}/'` |
| `/includes/shipping_api.php` | 296 | `https://dsp114.co.kr/mypage/order_detail.php` | `$baseUrl . '/mypage/order_detail.php'` |
| `/includes/shipping_api.php` | 446 | `https://dsp114.co.kr/mypage/order_detail.php` | `$baseUrl . '/mypage/order_detail.php'` |
| `/mlangprintauto/shop/send_cart_quotation.php` | 316 | `https://dsp114.co.kr/mlangprintauto/shop/cart.php` | `$baseUrl . '/mlangprintauto/shop/cart.php'` |
| `/dashboard/api/email.php` | 588-590 | `strpos($_SERVER['HTTP_HOST'], 'dsp114.co.kr')` | ✅ 이미 동적 감지 (수정 불필요) |

### 3위: JavaScript (5개 파일)

| 파일 | 절대 URL 개수 | 심각도 |
|------|-------------|--------|
| `/chat/chat.js` | 11개 | 🔴 CRITICAL |
| `/mlangprintauto/cadarok/js/cadarok.js` | 2개 | 🟠 HIGH |
| `/mlangprintauto/ncrflambeau/js/ncrflambeau-compact.js` | 1개 | 🟠 HIGH |
| `/includes/upload_modal.js` | 1개 | 🟠 HIGH |
| `/js/quote-gauge.js` | 1개 | 🟠 HIGH |

---

## 📊 현재 서버 구조 (완전 정리)

```
┌─────────────────────────────────────────────────────────────┐
│  🏢 Plesk 서버 (현재 운영 중)                                │
│  IP: 175.119.156.249                                        │
│  도메인: dsp114.co.kr (✅ 정상), cmshom.co.kr:8443 (Plesk)  │
│  웹 루트: /httpdocs/                                        │
│  FTP: dsp1830 / cH*j@yzj093BeTtc                           │
└─────────────────────────────────────────────────────────────┘
                              ↕
┌─────────────────────────────────────────────────────────────┐
│  🏚️ 구 서버 (레거시, 폐쇄 예정)                             │
│  IP: 175.119.156.230                                        │
│  도메인: dsp114.com (✅ 정상 작동 중)                        │
│  PHP: 5.2, MySQL, EUC-KR                                    │
│  FTP: duson1830 / du1830                                    │
└─────────────────────────────────────────────────────────────┘
```

---

## 🎯 도메인 스왑 2가지 시나리오

### **시나리오 A: DNS만 변경 (권장 ⭐⭐⭐)**

```
목표: dsp114.com이 Plesk 서버(신규)를 가리키도록 변경
      dsp114.co.kr은 그대로 유지

현재:
  dsp114.com   → 175.119.156.230 (구 서버)
  dsp114.co.kr → 175.119.156.249 (Plesk 서버)

변경 후:
  dsp114.com   → 175.119.156.249 (Plesk 서버) ⬅️ 변경!
  dsp114.co.kr → 175.119.156.249 (Plesk 서버)
```

**장점:**
- ✅ 코드 수정 최소화 (하드코딩된 dsp114.co.kr 그대로 유지)
- ✅ 광고 URL 변경 불필요
- ✅ 두 도메인 모두 신규 서버로 연결

**필요한 작업:**
1. **가비아 DNS 변경** (직접 또는 호스팅 회사 요청)
   - dsp114.com A 레코드 → 175.119.156.249로 변경
2. **Plesk에 dsp114.com 도메인 추가** (직접 가능)
3. **SSL 인증서 발급** (Plesk에서 직접 가능)
4. **DNS 전파 대기** (1~24시간)

---

### **시나리오 B: 도메인 주소 교체 (복잡함 ⭐)**

```
목표: dsp114.com을 메인으로, dsp114.co.kr은 리다이렉트

현재:
  dsp114.com   → 구 서버 (광고 URL)
  dsp114.co.kr → 신 서버 (메인)

변경 후:
  dsp114.com   → 신 서버 (메인) ⬅️ 주 도메인
  dsp114.co.kr → 신 서버 → 301 리다이렉트 dsp114.com
```

**필요한 작업:**
1. 시나리오 A의 모든 작업
2. **코드 내 모든 dsp114.co.kr → dsp114.com 변경** (30개+ 파일)
3. **DB 내 URL 일괄 UPDATE** (3개 테이블)
4. **dsp114.co.kr → dsp114.com 리다이렉트 설정** (.htaccess)

---

## 💡 가비아 도메인 관리 — 도메인 스왑 방법

**WHOIS 정보**: 가비아웹(gabiweb.co.kr) 네임서버 사용 중
- NS1.GABIWEB.CO.KR
- NS2.GABIWEB.CO.KR

### ✅ 방법 A: DNS A 레코드 변경 (권장 ⭐⭐⭐)

**가장 간단하고 안전합니다!**

```
1. 가비아 DNS 관리 페이지 접속
   https://dns.gabia.com

2. dsp114.com 도메인 선택

3. A 레코드 변경
   현재: dsp114.com → [구 서버 IP: 175.119.156.230]
   변경: dsp114.com → [Plesk 서버 IP: 175.119.156.249]

4. dsp114.co.kr도 동일한 IP로 설정 확인

5. DNS 전파 대기 (1~24시간)
```

**장점:**
- ✅ FTP/코드 수정 불필요
- ✅ 광고 URL 변경 불필요 (dsp114.com 그대로 사용 가능)
- ✅ 이전 과정 완전히 생략

**단점:**
- DNS 전파 시간 필요 (보통 1~2시간, 최대 24시간)

---

### ✅ 방법 B: Plesk 도메인 추가 (병행 운영)

```
1. Plesk 접속
   https://cmshom.co.kr:8443/login_up.php
   ID: 두손기획
   PW: h%42D9u2m

2. 도메인 추가
   도메인 → 도메인 추가 → dsp114.com 입력
   웹 루트: /httpdocs (dsp114.co.kr와 동일)

3. SSL 인증서 발급
   SSL/TLS 인증서 → Let's Encrypt 무료 인증서

4. 두 도메인 모두 작동 확인
   https://dsp114.com → 정상
   https://dsp114.co.kr → 정상
```

**장점:**
- ✅ 두 도메인 동시 운영
- ✅ 점진적 마이그레이션 가능
- ✅ 롤백 쉬움

**단점:**
- Plesk에서 추가 도메인 비용 발생 가능 (확인 필요)

---

## 🎯 추천 작업 흐름

### Phase 1: 준비 (1시간)

```bash
# 1. 전체 백업
mysqldump -u dsp1830 -p dsp1830 > backup_20260225.sql
tar -czf /var/www/html_backup_20260225.tar.gz /var/www/html/

# 2. Plesk 서버 IP 확인
curl ifconfig.me
# 또는 Plesk 관리자 → 도구 및 설정 → IP 주소

# 3. dsp114.co.kr 현재 IP 확인
curl -s -o /dev/null -w "IP: %{remote_ip}\n" https://dsp114.co.kr
```

### Phase 2: DNS 변경 (5분)

```
1. 가비아 DNS 관리 접속
2. dsp114.com A 레코드 변경 → Plesk 서버 IP (175.119.156.249)
3. TTL 3600 (1시간) 설정
```

### Phase 3: Plesk 도메인 추가 (10분)

```
1. Plesk → 도메인 추가 → dsp114.com
2. 웹 루트: /httpdocs
3. Let's Encrypt SSL 발급
```

### Phase 4: 코드 수정 (30분)

```
1. payment/ 파일 4개 수정
2. includes/ 이메일 파일 4개 수정
3. en/index.php 메타 태그 3곳 수정
```

### Phase 5: 테스트 (30분)

```
1. hosts 파일 수정으로 로컬 테스트
   echo "[Plesk IP]  dsp114.com" | sudo tee -a /etc/hosts

2. 브라우저에서 https://dsp114.com 접속 확인

3. 결제 테스트 (100원 소액)

4. 이메일 발송 테스트
```

### Phase 6: 배포 (5분)

```bash
# FTP 업로드 (수정한 파일만)
curl -T /var/www/html/payment/inicis_config.production.php \
  ftp://dsp114.co.kr/httpdocs/payment/inicis_config.production.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### Phase 7: 검증 (1시간)

```
1. DNS 전파 확인
   curl -s -o /dev/null -w "IP: %{remote_ip}\n" http://dsp114.com
   
2. 브라우저 캐시 삭제 후 접속

3. 주문 → 결제 → 이메일 수신 전체 흐름 테스트

4. 9개 제품 페이지 모두 접속 확인
```

---

## 📋 최종 체크리스트

```
□ Plesk 서버 IP 확인 완료 (175.119.156.249)
□ 가비아 DNS A 레코드 변경 완료
□ Plesk에 dsp114.com 도메인 추가 완료
□ SSL 인증서 발급 완료
□ payment/ 파일 4개 수정 완료
□ includes/ 이메일 파일 4개 수정 완료
□ 전체 백업 완료 (DB + 파일)
□ localhost 테스트 완료
□ 프로덕션 배포 완료
□ DNS 전파 확인 완료
□ 결제 테스트 완료 (100원)
□ 이메일 발송 테스트 완료
□ 9개 제품 페이지 접속 확인 완료
```

---

## 📦 백업 계획

### **전체 백업 체크리스트**

```bash
# 1️⃣ DB 백업 (Plesk phpMyAdmin)
- Plesk → 데이터베이스 → phpMyAdmin
- dsp1830 DB 선택 → 내보내기 → SQL 파일 다운로드
- 파일명: dsp1830_backup_20260225.sql
- 예상 크기: ~50MB

# 2️⃣ 파일 백업 (FTP 전체 다운로드)
- FileZilla로 /httpdocs/ 전체 다운로드
- 예상 크기: ~2GB (이미지 포함)
- 소요 시간: 10~30분 (인터넷 속도에 따라)

# 3️⃣ 설정 파일 백업 (중요 파일만)
- payment/inicis_config.production.php
- config.env.php
- .htaccess
- 예상 크기: ~1MB
```

**복구 시간:** 
- DB 복구: 5분 (Import SQL)
- 파일 복구: 30분~1시간 (FTP 업로드)

---

## 🔍 상세 파일 목록

### 도메인 설정 파일 (15개)

| 파일 | 용도 | 수정 필요 |
|------|------|----------|
| `/var/www/html/config.env.php` | 환경 감지 SSOT | ✅ dsp114.com 추가 |
| `/var/www/html/payment/inicis_config.production.php` | 결제 URL | ✅ 동적 생성으로 변경 |
| `/var/www/html/payment/config.php` | 레거시 설정 | ⚠️ 확인 필요 |
| `/var/www/html/v2/config/app.php` | v2 프레임워크 | ⚠️ dsp1830.shop → dsp114.co.kr |
| `/var/www/html/dashboard/api/email.php` | 이메일 도메인 감지 | ✅ 이미 동적 |

### JavaScript 파일 (5개)

| 파일 | 절대 URL | 수정 방법 |
|------|---------|----------|
| `/chat/chat.js` | 11개 | 모두 상대 경로로 변경 |
| `/mlangprintauto/cadarok/js/cadarok.js` | 2개 | window.location.origin 사용 |
| `/includes/upload_modal.js` | 1개 | 이미 origin 사용 중 |
| `/js/quote-gauge.js` | 1개 | 상대 경로로 변경 |

### DB 테이블 (3개)

| 테이블 | 컬럼 | UPDATE 필요 |
|--------|------|------------|
| `email_templates` | `body_html` | ✅ REPLACE 쿼리 |
| `email_campaigns` | `body_html` | ✅ REPLACE 쿼리 |
| `mlangorder_printauto` | `uploaded_files` | ⚠️ JSON 파싱 필요 |

**UPDATE 쿼리 예시:**

```sql
-- email_templates 도메인 변경
UPDATE email_templates 
SET body_html = REPLACE(body_html, 'https://dsp114.co.kr', 'https://dsp114.com')
WHERE body_html LIKE '%dsp114.co.kr%';

-- email_campaigns 도메인 변경
UPDATE email_campaigns 
SET body_html = REPLACE(body_html, 'https://dsp114.co.kr', 'https://dsp114.com')
WHERE body_html LIKE '%dsp114.co.kr%';
```

---

## ⚠️ 주의사항

### 1. DNS 전파 시간
- 최소: 1~2시간
- 최대: 24~48시간
- 이 기간 동안 일부 사용자는 구 서버 접속 가능
- **주말이나 업무 시간 외 작업 권장**

### 2. 결제 시스템 (이니시스)
- 운영 모드에서 도메인 변경 시 **이니시스 관리자에서도 변경 필요**
- Return URL, Close URL이 정확히 일치해야 함
- 테스트 결제 필수 (100원 소액)

### 3. SSL 인증서
- Let's Encrypt는 90일마다 갱신
- Plesk에서 자동 갱신 활성화 확인
- 두 도메인 모두 SSL 인증서 발급 필요

### 4. 검색엔진 색인
- Google Search Console에 dsp114.com 추가
- 사이트맵 재제출
- 301 리다이렉트 설정 시 SEO 점수 유지

---

## 💰 예상 비용

| 항목 | 비용 |
|------|------|
| DNS 변경 | 무료 (직접) 또는 3~5만원 (호스팅 회사 대행) |
| SSL 인증서 | 무료 (Let's Encrypt) |
| Plesk 추가 도메인 | 확인 필요 (보통 무료~5천원/월) |
| 개발 작업 | 직접 작업 시 무료 |
| **총 예상** | **0~5만원** |

---

## 🆘 롤백 계획

**문제 발생 시 즉시 복구:**

```bash
# 1. DNS 원복 (가비아)
dsp114.com A 레코드 → 175.119.156.230 (구 서버)

# 2. Plesk 도메인 비활성화
Plesk → dsp114.com → 호스팅 설정 → 일시중지

# 3. 파일 복구 (필요시)
FTP로 백업 파일 업로드

# 4. DB 복구 (필요시)
phpMyAdmin → Import → backup_20260225.sql
```

**복구 시간**: 10~30분

---

## 📞 긴급 연락처

```
고객센터: 02-2632-1830
Plesk 관리: https://cmshom.co.kr:8443
FTP: dsp1830 / cH*j@yzj093BeTtc
DB: Plesk → phpMyAdmin
```

---

**최종 업데이트**: 2026-02-25  
**작성자**: AI Assistant  
**검증 상태**: 정보 수집 완료, 실행 대기 중
