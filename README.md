# Duson Planning Print System (두손기획인쇄)

PHP 7.4 기반 인쇄 주문 관리 시스템

---

## 🚨 배포 시 필독! (CRITICAL for Deployment)

### ⚠️ 서버 변경 안내
```
❌ 구 서버: dsp1830.shop (더 이상 사용 안 함)
❌ 구 도메인: dsp114.co.kr (dsp114.com으로 변경됨)
✅ 현재 운영: dsp114.com
```

### 운영 서버 FTP 정보 (dsp114.com)
```
Host: dsp114.com
User: dsp1830
Pass: cH*j@yzj093BeTtc
Port: 21 (FTP)
```

### ⚠️ 웹 루트 경로 (절대 잊지 마세요!)

```
FTP 루트 (/)
└─ httpdocs/  ← ✅ 실제 웹 루트 (https://dsp114.com/)

❌ 잘못된 경로: /payment/inicis_return.php
✅ 올바른 경로: /httpdocs/payment/inicis_return.php
```

### 파일 업로드 예시 (curl)
```bash
curl -T /var/www/html/payment/inicis_return.php \
  ftp://dsp114.com/httpdocs/payment/inicis_return.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

**📖 상세 가이드:** [DEPLOYMENT.md](./DEPLOYMENT.md)

---

## 📚 핵심 문서

| 문서 | 설명 |
|------|------|
| [AGENTS.md](./AGENTS.md) | AI 에이전트용 시스템 가이드 (배포, 코드 규칙) |
| [DEPLOYMENT.md](./DEPLOYMENT.md) | **배포 전 필독** - FTP 업로드 완벽 가이드 |
| [CLAUDE.md](./CLAUDE.md) | Claude Code 작업 지침 |
| [payment/README_PAYMENT.md](./payment/README_PAYMENT.md) | KG이니시스 결제 시스템 설정 |

---

## 🏗️ 시스템 개요

- **Backend**: PHP 7.4+ with MySQL 5.7+
- **Frontend**: PHP templates + JavaScript
- **Testing**: Playwright (E2E)
- **Local**: `/var/www/html`
- **Production**: `/httpdocs/` (via FTP)

---

## 🚀 빠른 시작

### 로컬 개발 환경
```bash
# 서버 시작 (WSL2)
sudo service apache2 start
sudo service mysql start

# 접속
http://localhost/
```

### 테스트 실행
```bash
# Playwright 테스트
npx playwright test

# 특정 그룹 테스트
npx playwright test --project="group-a-readonly"
```

### 운영 서버 배포
```bash
# 단일 파일 업로드
curl -T 로컬파일.php \
  ftp://dsp114.com/httpdocs/경로/파일.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 배포 스크립트 (개발 중)
./scripts/deploy_to_production.sh
```

---

## 📦 주요 제품 (9가지)

| 제품 | 폴더명 | 단위 |
|------|--------|------|
| 전단지 | `inserted` | 연 |
| 스티커 | `sticker_new` | 매 |
| 자석스티커 | `msticker` | 매 |
| 명함 | `namecard` | 매 |
| 봉투 | `envelope` | 매 |
| 포스터 | `littleprint` | 매 |
| 상품권 | `merchandisebond` | 매 |
| 카다록 | `cadarok` | 부 |
| NCR양식지 | `ncrflambeau` | 권 |

---

## 💳 결제 시스템

**KG이니시스 표준결제 연동**
- 테스트 모드: `INICIS_TEST_MODE = true` (localhost)
- 운영 모드: `INICIS_TEST_MODE = false` (dsp114.com)
- MID: `dsp1147479` (운영)

**설정 파일:** `payment/inicis_config.php`

---

## 🔧 코드 작성 규칙

### PHP 필수 규칙

#### bind_param 3단계 검증
```php
// ❌ NEVER: 눈으로 대충 세기
mysqli_stmt_bind_param($stmt, "issss...", ...);

// ✅ ALWAYS: 3단계 검증
$placeholder_count = substr_count($query, '?');
$type_count = strlen($type_string);
$var_count = 7; // 손으로 세기

if ($placeholder_count === $type_count && $type_count === $var_count) {
    mysqli_stmt_bind_param($stmt, $type_string, ...);
}
```

#### CSS !important 금지
```css
/* ❌ 절대 금지 */
.product-nav { display: grid !important; }

/* ✅ 명시도로 해결 */
.mobile-view .product-nav { display: grid; }
```

---

## 📞 긴급 연락처

```
고객센터: 02-2632-1830
DB 접속: dsp1830 / ds701018
FTP: dsp1830 / cH*j@yzj093BeTtc
GitHub: songyoungsoo / yeongsu32@gmail.com
```

---

## 📂 프로젝트 구조

```
/var/www/html/
├─ mlangprintauto/          # 제품 페이지
│  ├─ namecard/             # 명함
│  ├─ inserted/             # 전단지
│  ├─ sticker_new/          # 스티커
│  └─ ...
├─ mlangorder_printauto/    # 주문 처리
├─ payment/                 # 결제 시스템
├─ includes/                # 공통 라이브러리
├─ admin/                   # 관리자
├─ scripts/                 # 배포/테스트 스크립트
└─ DEPLOYMENT.md            # ⭐ 배포 가이드
```

---

**운영 도메인:** https://dsp114.com  
**마지막 업데이트:** 2026-03-01
