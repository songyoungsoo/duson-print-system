# WSL Environment Setup Guide

사무실/집 개발 환경 통일 가이드

## 🏢 환경 현황

| 환경 | WSL 버전 | 위치 | 용도 | 상태 |
|------|----------|------|------|------|
| **집** | WSL2 | `/var/www/html` | 개발/테스트 | ✅ 최신 |
| **사무실** | WSL1 | `/var/www/html` | 개발/배포 | ⚠️ 업그레이드 권장 |
| **프로덕션** | Linux | dsp1830.shop | 웹 서비스 | ✅ 운영 중 |

## 🎯 권장 방안: WSL1 → WSL2 업그레이드

### 왜 WSL2인가?

**성능 향상**:
- 파일 I/O: 20-50배 빠름 (Apache/MySQL 성능 향상)
- Git 작업: 매우 빠름
- PHP 파일 처리: 네이티브 Linux 성능

**기능 확장**:
- Docker Desktop 완벽 지원
- 거의 모든 Linux 앱 실행 가능
- 최신 Linux 커널 기능 사용

**호환성**:
- 집/사무실 환경 완전 동일
- 배포 환경과 유사한 구조

### 업그레이드 절차 (사무실 PC)

#### 1️⃣ 백업 (필수!)

```powershell
# 관리자 권한 PowerShell 실행

# 현재 상태 확인
wsl --list --verbose

# 결과 예시:
#   NAME      STATE           VERSION
# * Ubuntu    Running         1        ← WSL1

# 전체 백업 (중요!)
wsl --export Ubuntu C:\wsl-backup\ubuntu-backup.tar
# 약 5-15분 소요 (파일 크기에 따라)
```

#### 2️⃣ WSL2로 변환

```powershell
# WSL2로 변환 (5-10분 소요)
wsl --set-version Ubuntu 2

# 진행 상황 표시:
# Conversion in progress, this may take a few minutes...
# For information on key differences with WSL 2 please visit https://aka.ms/wsl2
# Conversion complete.

# 변환 확인
wsl --list --verbose
#   NAME      STATE           VERSION
# * Ubuntu    Running         2        ← WSL2로 변경됨!
```

#### 3️⃣ 기본 버전 설정

```powershell
# 향후 새로운 배포판도 WSL2로 설치되도록 설정
wsl --set-default-version 2
```

#### 4️⃣ 환경 검증

```bash
# WSL Ubuntu 터미널에서 실행

# 1. 커널 버전 확인
cat /proc/version
# 출력에 "microsoft-standard-WSL2" 포함되어야 함

# 2. 웹 서버 테스트
sudo service apache2 start
curl http://localhost

# 3. 데이터베이스 테스트
sudo service mysql start
mysql -u root -p

# 4. 프로젝트 파일 확인
cd /var/www/html
ls -la mlangprintauto/
```

## 🔄 대안: WSL1 유지하면서 호환성 보장

업그레이드가 어려운 경우, 현재 코드는 이미 양쪽 환경을 지원합니다:

### 자동 환경 감지

`config.env.php`가 자동으로 감지:

```php
// WSL1, WSL2 모두 'local' 환경으로 인식
if (strpos($host, 'localhost') !== false) {
    self::$environment = 'local';
    // 데이터베이스: root / (비밀번호 없음)
    // 디버그 모드: ON
}
```

### WSL1 환경 주의사항

**파일 경로**:
```bash
# Linux 파일 시스템 사용 (빠름)
/var/www/html/

# Windows 파일 시스템 사용하지 말 것 (매우 느림)
/mnt/c/xampp/htdocs/  ❌
```

**성능 최적화**:
```bash
# 1. 모든 프로젝트 파일을 Linux 파일 시스템에 보관
cd /var/www/html

# 2. Windows와 파일 공유 최소화
# 필요한 경우만 /mnt/c/ 접근

# 3. Git 저장소도 Linux 파일 시스템에
git clone [repo] /var/www/html/project
```

## 📁 디렉토리 구조 통일

사무실/집 모두 동일하게:

```
/var/www/html/                          # Document Root
├── mlangprintauto/                     # 제품 모듈
├── includes/                           # 공유 컴포넌트
├── admin/                              # 관리자 시스템
├── db.php                              # DB 연결
├── config.env.php                      # 환경 설정
└── CLAUDE.md                           # 프로젝트 문서
```

## 🔧 Apache/MySQL 설정

### WSL1 & WSL2 공통

```bash
# Apache 시작
sudo service apache2 start

# MySQL 시작
sudo service mysql start

# 자동 시작 설정 (선택사항)
# ~/.bashrc에 추가:
if service apache2 status | grep -q "is not running"; then
    sudo service apache2 start
fi
if service mysql status | grep -q "is not running"; then
    sudo service mysql start
fi
```

### 포트 확인

```bash
# Apache 포트 확인 (80)
sudo netstat -tlnp | grep apache2

# MySQL 포트 확인 (3306)
sudo netstat -tlnp | grep mysql

# 브라우저에서 접근
# http://localhost/mlangprintauto/sticker_new/
```

## 🌐 호스트 파일 설정 (선택사항)

로컬에서 도메인 테스트:

```bash
# Windows에서 (관리자 권한 필요)
notepad C:\Windows\System32\drivers\etc\hosts

# 추가:
127.0.0.1 local.dsp1830.shop
127.0.0.1 local.dsp1830.shop

# 브라우저에서 접근:
# http://local.dsp1830.shop/mlangprintauto/sticker_new/
```

## 🚀 배포 워크플로우

### 사무실 → 프로덕션

```bash
# 1. 로컬 테스트
http://localhost/mlangprintauto/sticker_new/

# 2. Git 커밋
git add .
git commit -m "feat: 스티커 계산기 개선"
git push

# 3. 프로덕션 배포 (FTP/SSH)
# - FileZilla 또는 rsync 사용
# - dsp1830.shop 서버에 업로드

# 4. 프로덕션 검증
http://dsp1830.shop/mlangprintauto/sticker_new/
```

### 집 → 사무실 동기화

```bash
# Git 사용 (권장)
git pull origin main

# 또는 직접 파일 복사
rsync -avz /var/www/html/ [사무실-IP]:/var/www/html/
```

## ⚠️ 트러블슈팅

### WSL1에서 느린 경우

```bash
# 파일 위치 확인
pwd
# /var/www/html  ✅ 좋음
# /mnt/c/...     ❌ 느림, Linux 파일 시스템으로 이동 필요
```

### WSL2 변환 실패

```powershell
# Hyper-V 활성화 확인
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart

# WSL 업데이트
wsl --update

# 재시도
wsl --set-version Ubuntu 2
```

### 네트워크 문제 (WSL2)

```bash
# WSL2의 IP는 동적으로 변경됨
hostname -I

# Windows에서 접근 시 localhost 사용
http://localhost/
```

## 📚 참고 자료

- [Microsoft WSL 공식 문서](https://docs.microsoft.com/ko-kr/windows/wsl/)
- [WSL2 설치 가이드](https://docs.microsoft.com/ko-kr/windows/wsl/install)
- [WSL1 vs WSL2 비교](https://docs.microsoft.com/ko-kr/windows/wsl/compare-versions)

## 🎯 권장 사항 요약

1. **최우선**: 사무실 WSL1 → WSL2 업그레이드 (성능 20-50배 향상)
2. **현재 코드**: 이미 WSL1/WSL2 모두 지원 중 (`config.env.php`)
3. **파일 위치**: 항상 `/var/www/html` 사용 (Linux 파일 시스템)
4. **동기화**: Git 사용하여 집/사무실 코드 동기화

---

*Last Updated: 2025-10-27*
*Environments: WSL1 & WSL2 Compatible*
