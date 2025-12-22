# DNS 마이그레이션 작업 (보류 중)

**작성일**: 2025-12-21
**상태**: 보류
**목적**: dsp114.com 도메인을 dsp1830.shop 서버로 임시 전환 테스트

---

## 현재 상황 요약

### ✅ 완료된 작업

1. **DNS 설정 확인**:
   - dsp114.com: 175.119.156.230 (구 운영 서버 - Apache 2.2.34, PHP 5.2.17)
   - dsp1830.shop: 220.73.160.27 (신 개발 서버 - nginx, PHP 7.4+)

2. **hosts 파일 테스트 환경 구축**:
   - 백업: `/etc/hosts.backup_hosts_test`
   - 테스트 엔트리 추가/제거 스크립트 준비 완료

3. **SSH 접속 및 서버 환경 확인**:
   - 호스트: dsp1830.shop (실제: uws7-232.cafe24.com)
   - 계정: dsp1830 / ds701018
   - 웹루트: `/home/dsp1830/www/`
   - 환경: **Cafe24 웹 호스팅** (nginx 직접 설정 불가)

### 🔴 발견된 문제

**HTTP 403 Forbidden 오류**:
```bash
# hosts 파일로 dsp114.com → 220.73.160.27 리다이렉트 시
curl -I http://dsp114.com
# HTTP/1.1 403 Forbidden

# 원인: nginx에 dsp114.com virtual host 설정 없음
curl -I -H "Host: dsp1830.shop" http://220.73.160.27  # 200 OK ✅
curl -I -H "Host: dsp114.com" http://220.73.160.27    # 403 Forbidden ❌
```

**근본 원인**: Cafe24 웹 호스팅은 `/etc/nginx` 접근 불가 (사용자 권한으로 nginx 설정 파일 수정 불가능)

---

## 해결 방법: Cafe24 관리 페이지 사용

### 옵션 1: Cafe24 호스팅 관리 페이지에서 도메인 연결 (권장)

**접속 정보**:
```
URL: https://hosting.cafe24.com/
계정: dsp1830
비밀번호: ds701018
```

**설정 경로**:
1. **My Services** → **Hosting Management** → **Basic Settings** → **Domain Connection Management**
2. "도메인 직접 입력하기" 선택
3. `dsp114.com` 입력 후 연결
4. 약 30분~1시간 대기 (DNS 전파)

**또는**:
1. **My Services** → **Domain Management** → **DNS Management**
2. "Add A Record" 선택
3. Host: `@` (또는 dsp114.com)
4. IP: `220.73.160.27`
5. TTL: `300` (빠른 롤백을 위해 5분)

### 옵션 2: 도메인 등록업체에서 DNS A 레코드 직접 변경

dsp114.com의 DNS를 관리하는 곳(도메인 등록업체)에서:

```
Type: A
Host: @ (또는 dsp114.com)
Value: 220.73.160.27
TTL: 300 (5분 - 빠른 롤백을 위해)
```

**www 레코드도 추가** (선택사항):
```
Type: A
Host: www
Value: 220.73.160.27
TTL: 300
```

---

## hosts 파일 테스트 스크립트

### 테스트 시작 (dsp114.com → 220.73.160.27)

```bash
# 1. 백업
echo "3305" | sudo -S cp /etc/hosts /etc/hosts.backup_hosts_test

# 2. 테스트 엔트리 추가
echo "3305" | sudo -S bash -c 'echo "220.73.160.27  dsp114.com" >> /etc/hosts'

# 3. 확인
getent hosts dsp114.com
# → 220.73.160.27 dsp114.com

# 4. HTTP 테스트
curl -I http://dsp114.com
# → 현재는 403 Forbidden (Cafe24에서 도메인 연결 후 200 OK 예상)
```

### 테스트 종료 (원상복구)

```bash
# 백업에서 복원
echo "3305" | sudo -S cp /etc/hosts.backup_hosts_test /etc/hosts

# 확인
getent hosts dsp114.com
# → 175.119.156.230 dsp114.com (원래 IP로 복원)
```

---

## 다음 단계 (작업 재개 시)

1. **Cafe24 관리 페이지 접속**:
   - https://hosting.cafe24.com/
   - 계정: dsp1830 / ds701018

2. **dsp114.com 도메인 연결**:
   - Domain Connection Management에서 추가
   - 또는 DNS A 레코드 직접 변경

3. **30분~1시간 대기** (DNS 전파)

4. **hosts 파일 테스트**:
   ```bash
   # 테스트 엔트리 추가
   echo "3305" | sudo -S bash -c 'echo "220.73.160.27  dsp114.com" >> /etc/hosts'

   # HTTP 200 확인
   curl -I http://dsp114.com
   # → HTTP/1.1 200 OK 확인

   # 주요 기능 테스트
   curl -I http://dsp114.com/mlangprintauto/inserted/
   curl -I http://dsp114.com/admin/

   # 테스트 완료 후 복원
   echo "3305" | sudo -S cp /etc/hosts.backup_hosts_test /etc/hosts
   ```

5. **실제 DNS 전환** (테스트 성공 시):
   - TTL 300초로 사전 변경 (빠른 롤백 가능하도록)
   - A 레코드 변경: 175.119.156.230 → 220.73.160.27
   - 24시간 모니터링
   - 문제 발생 시 즉시 롤백

---

## 중요 참고사항

### 자동 도메인 감지 시스템

`db.php` 파일에 이미 자동 도메인 감지 기능이 구현되어 있습니다:

```php
$current_host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (strpos($current_host, 'localhost') !== false) {
    $admin_url = "http://localhost";
} elseif (strpos($current_host, 'dsp1830.shop') !== false) {
    $admin_url = "http://dsp1830.shop";
} elseif (strpos($current_host, 'dsp114.com') !== false) {
    $admin_url = "http://dsp114.com";  // 자동 감지됨
}

$cookie_domain = ($current_host === 'localhost') ? 'localhost' : '.' . $current_host;
```

**결론**: DNS만 변경하면 코드 수정 없이 자동으로 작동합니다.

### 서버 환경 차이

| 항목 | 구 서버 (dsp114.com) | 신 서버 (dsp1830.shop) |
|------|---------------------|----------------------|
| IP | 175.119.156.230 | 220.73.160.27 |
| 웹서버 | Apache 2.2.34 | nginx |
| PHP | 5.2.17 (레거시) | 7.4+ (최신) |
| 호스팅 | ? | Cafe24 웹 호스팅 |

**주의**: PHP 버전 차이로 인한 호환성 문제 가능성 있음 → 테스트 필수

---

## 참고 문서

- [Cafe24 2차 도메인 설정 가이드](https://help.cafe24.com/docs/domain/secondary-domain-setup-master-guide/)
- [카페24 호스팅과 도메인 연결](https://cafe24.zendesk.com/hc/ko/articles/18323473845017)
- [DNS 설정 - 카페24 Help Center](https://support.cafe24.com/hc/ko/articles/7671674036249)
- [구매한 도메인을 내 쇼핑몰과 연결](https://support.cafe24.com/hc/ko/articles/8468713483673)

---

## SSH 접속 정보 (참고)

```bash
# SSH 접속
sshpass -p 'ds701018' ssh -o StrictHostKeyChecking=no dsp1830@dsp1830.shop

# 또는
ssh dsp1830@dsp1830.shop
# Password: ds701018

# 서버 정보
hostname  # → uws7-232.cafe24.com
pwd       # → /home/dsp1830
ls ~/www/ # → 웹루트 확인
```

---

**작업 보류 사유**: 사용자 요청
**재개 시 필요한 것**: Cafe24 관리 페이지 접속 및 도메인 연결 설정
