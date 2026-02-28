# DEPLOYMENT.md - 운영 서버 배포 가이드

## 🚨 CRITICAL - 운영 서버 정보

### ⚠️ 서버 변경 내역
```
❌ 구 서버: dsp1830.shop (더 이상 사용 안 함)
❌ 구 도메인: dsp114.co.kr (dsp114.com으로 변경됨)
✅ 현재 운영: dsp114.com
```

### FTP 접속 정보 (dsp114.com)
```
Host: dsp114.com
User: dsp1830
Pass: cH*j@yzj093BeTtc
Port: 21 (FTP)
Protocol: FTP (plain)
```

### 웹 루트 구조
```
FTP 루트 (/)
├─ httpdocs/          ← ✅ 실제 웹 루트 (https://dsp114.com/)
│  ├─ index.php
│  ├─ payment/
│  ├─ mlangprintauto/
│  ├─ mlangorder_printauto/
│  ├─ includes/
│  ├─ admin/
│  └─ ...
├─ public_html/       ← ❌ 웹 루트 아님! (별도 디렉토리)
├─ logs/
└─ error_docs/
```

### ⚠️ 배포 시 주의사항

**절대 잊지 말 것:**
1. 웹 루트는 `/httpdocs/` 입니다!
2. `/public_html/`은 웹 루트가 아닙니다!
3. FTP 루트에 직접 업로드하지 마세요!

**올바른 업로드 경로:**
```
✅ /httpdocs/payment/inicis_return.php
✅ /httpdocs/mlangprintauto/namecard/index.php
✅ /httpdocs/includes/QuantityFormatter.php

❌ /payment/inicis_return.php (웹 루트 빠짐)
❌ /public_html/payment/inicis_return.php (잘못된 웹 루트)
```

---

## 📦 배포 방법

### 방법 1: curl로 개별 파일 업로드 (권장)

```bash
# 기본 형식
curl -T /로컬/경로/파일.php \
  ftp://dsp114.com/httpdocs/원격/경로/파일.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 예시: 결제 파일 업로드
curl -T /var/www/html/payment/inicis_return.php \
  ftp://dsp114.com/httpdocs/payment/inicis_return.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 예시: 명함 페이지 업로드
curl -T /var/www/html/mlangprintauto/namecard/index.php \
  ftp://dsp114.com/httpdocs/mlangprintauto/namecard/index.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### 방법 2: FTP 클라이언트 (FileZilla 등)

**접속 정보:**
- 호스트: dsp114.com
- 사용자명: dsp1830
- 비밀번호: cH*j@yzj093BeTtc
- 포트: 21
- 프로토콜: FTP

**업로드 경로:**
1. 접속 후 `httpdocs` 폴더로 이동
2. 로컬 파일을 드래그 앤 드롭

### 방법 3: 배포 스크립트 (개발 중)

```bash
# 스크립트 실행
./scripts/deploy_to_production.sh

# 특정 파일만 배포
./scripts/deploy_single_file.sh payment/inicis_return.php
```

---

## 📋 배포 체크리스트

### 배포 전
- [ ] 로컬에서 기능 테스트 완료
- [ ] Git commit 완료 (변경 내용 기록)
- [ ] 업로드할 파일 목록 확인
- [ ] 웹 루트 경로 확인 (`/httpdocs/`)

### 배포 중
- [ ] FTP 접속 성공 확인
- [ ] `httpdocs` 디렉토리로 이동 확인
- [ ] 파일 업로드 성공 메시지 확인
- [ ] 파일 크기 일치 확인 (로컬 vs 원격)

### 배포 후
- [ ] https://dsp114.com 에서 기능 동작 확인
- [ ] 브라우저 캐시 제거 (Ctrl+Shift+R)
- [ ] 에러 로그 확인 (`/httpdocs/logs/`)
- [ ] 결제 시스템 테스트 (소액 결제)

---

## 🔧 자주 사용하는 배포 경로

| 기능 | 로컬 경로 | 원격 경로 (FTP) |
|------|----------|----------------|
| 결제 시스템 | `/var/www/html/payment/` | `/httpdocs/payment/` |
| 명함 | `/var/www/html/mlangprintauto/namecard/` | `/httpdocs/mlangprintauto/namecard/` |
| 전단지 | `/var/www/html/mlangprintauto/inserted/` | `/httpdocs/mlangprintauto/inserted/` |
| 스티커 | `/var/www/html/mlangprintauto/sticker_new/` | `/httpdocs/mlangprintauto/sticker_new/` |
| 주문 완료 | `/var/www/html/mlangorder_printauto/` | `/httpdocs/mlangorder_printauto/` |
| 공통 파일 | `/var/www/html/includes/` | `/httpdocs/includes/` |
| 관리자 | `/var/www/html/admin/` | `/httpdocs/admin/` |

---

## 🚨 긴급 롤백 절차

### 1. 백업 파일 확인
```bash
# FTP 루트의 백업 디렉토리 확인
curl -s --list-only ftp://dsp114.com/backups/ \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### 2. 이전 버전 복구
```bash
# 백업 파일을 웹 루트로 복사
curl -T backup_file.php \
  ftp://dsp114.com/httpdocs/path/file.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### 3. Git에서 복구
```bash
# 로컬에서 이전 버전 체크아웃
git checkout HEAD~1 -- path/to/file.php

# 다시 업로드
curl -T path/to/file.php \
  ftp://dsp114.com/httpdocs/path/to/file.php \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

---

## 📞 문제 발생 시

### 업로드 실패
```bash
# FTP 연결 테스트
curl -v ftp://dsp114.com/ \
  --user "dsp1830:cH*j@yzj093BeTtc"

# 디렉토리 구조 확인
curl --list-only ftp://dsp114.com/httpdocs/ \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

### 파일 권한 오류
- FTP로 업로드한 파일은 자동으로 실행 권한 설정됨
- PHP 파일은 별도 권한 설정 불필요
- 이미지/CSS는 644 권한 자동 적용

### 페이지 500 오류
1. 로그 확인: `/httpdocs/logs/error_log`
2. PHP 문법 오류 확인
3. 파일 인코딩 확인 (UTF-8 without BOM)

---

## 🔍 로그 확인

### 운영 서버 로그 다운로드
```bash
# 에러 로그
curl ftp://dsp114.com/logs/error_log \
  --user "dsp1830:cH*j@yzj093BeTtc" > error_log

# 결제 로그
curl ftp://dsp114.com/httpdocs/payment/logs/inicis_$(date +%Y-%m-%d).log \
  --user "dsp1830:cH*j@yzj093BeTtc"
```

---

**마지막 업데이트:** 2026-03-01  
**작성자:** System Documentation
