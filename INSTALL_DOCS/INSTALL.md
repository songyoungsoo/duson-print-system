# 두손기획인쇄 설치 가이드

이 문서는 두손기획인쇄 시스템의 설치 과정을 단계별로 안내합니다.

---

## 목차

1. [시스템 요구사항](#1-시스템-요구사항)
2. [설치 전 준비사항](#2-설치-전-준비사항)
3. [단계별 설치 방법](#3-단계별-설치-방법)
4. [데이터베이스 설정](#4-데이터베이스-설정)
5. [웹 서버 설정](#5-웹-서버-설정)
6. [설치 검증](#6-설치-검증)
7. [문제 해결 FAQ](#7-문제-해결-faq)

---

## 1. 시스템 요구사항

### 1.1 필수 소프트웨어

| 소프트웨어 | 최소 버전 | 권장 버전 | 확인 방법 |
|------------|----------|----------|-----------|
| PHP | 7.4 | 8.0+ | `php -v` |
| MySQL | 5.7 | 8.0+ | `mysql --version` |
| Apache | 2.4 | 2.4+ | `apache2 -v` |

### 1.2 필수 PHP 확장 모듈

다음 PHP 확장 모듈이 설치되어 있어야 합니다:

| 모듈 | 용도 |
|------|------|
| mysqli | MySQL 데이터베이스 연결 |
| mbstring | 멀티바이트 문자열 처리 (한글) |
| gd | 이미지 처리 |
| json | JSON 데이터 처리 |
| session | 세션 관리 |
| fileinfo | 파일 타입 감지 |

확장 모듈 설치 상태 확인:

```bash
php -m | grep -E "(mysqli|mbstring|gd|json|session|fileinfo)"
```

### 1.3 하드웨어 요구사항

| 항목 | 최소 사양 | 권장 사양 |
|------|----------|----------|
| RAM | 1GB | 2GB+ |
| 저장공간 | 5GB | 20GB+ |
| CPU | 1 Core | 2 Core+ |

### 1.4 지원 운영체제

| 운영체제 | 지원 상태 | 비고 |
|----------|----------|------|
| Ubuntu 20.04+ | 지원 | 권장 |
| WSL2 (Ubuntu) | 지원 | 개발용 권장 |
| Windows (XAMPP) | 지원 | 개발용 |
| CentOS 7+ | 지원 | |
| macOS (MAMP) | 지원 | 개발용 |

---

## 2. 설치 전 준비사항

### 2.1 Linux (Ubuntu/WSL2)

#### PHP 설치

```bash
# 패키지 목록 업데이트
sudo apt update

# PHP 및 필수 모듈 설치
sudo apt install php7.4 php7.4-mysqli php7.4-mbstring php7.4-gd php7.4-json php7.4-xml
```

#### MySQL 설치

```bash
# MySQL 서버 설치
sudo apt install mysql-server

# MySQL 보안 설정
sudo mysql_secure_installation
```

#### Apache 설치

```bash
# Apache 설치
sudo apt install apache2

# PHP 모듈 활성화
sudo apt install libapache2-mod-php7.4

# mod_rewrite 활성화
sudo a2enmod rewrite

# Apache 재시작
sudo service apache2 restart
```

### 2.2 Windows (XAMPP)

1. [XAMPP 다운로드](https://www.apachefriends.org/download.html)에서 최신 버전 다운로드
2. 설치 프로그램 실행
3. 설치 경로: `C:\xampp` (기본값 권장)
4. 설치 구성요소 선택:
   - Apache
   - MySQL
   - PHP
   - phpMyAdmin

---

## 3. 단계별 설치 방법

### 3.1 소스 코드 배포

#### Linux

```bash
# 웹 루트 디렉토리로 이동
cd /var/www/html

# 기존 파일 백업 (선택사항)
sudo mv /var/www/html /var/www/html_backup

# 새 디렉토리 생성
sudo mkdir /var/www/html

# 소스 코드 복사 (FTP 또는 Git)
# FTP 사용 시:
# sudo apt install ftp
# ftp [서버주소]

# Git 사용 시:
# git clone [저장소주소] /var/www/html
```

#### Windows (XAMPP)

1. XAMPP 설치 폴더의 `htdocs` 디렉토리로 이동 (`C:\xampp\htdocs`)
2. 기존 파일 백업
3. 소스 코드 복사

### 3.2 파일 권한 설정 (Linux)

```bash
# 소유권 설정
sudo chown -R www-data:www-data /var/www/html

# 디렉토리 권한 (755)
sudo find /var/www/html -type d -exec chmod 755 {} \;

# 파일 권한 (644)
sudo find /var/www/html -type f -exec chmod 644 {} \;

# 업로드 디렉토리 쓰기 권한
sudo chmod -R 775 /var/www/html/upload
sudo chmod -R 775 /var/www/html/sessions
```

### 3.3 업로드 디렉토리 생성

```bash
# 업로드 디렉토리 생성
mkdir -p /var/www/html/upload/printauto
mkdir -p /var/www/html/sessions

# 권한 설정
chmod 775 /var/www/html/upload
chmod 775 /var/www/html/upload/printauto
chmod 775 /var/www/html/sessions
```

---

## 4. 데이터베이스 설정

### 4.1 데이터베이스 및 사용자 생성

```bash
# MySQL 접속
sudo mysql -u root -p
```

```sql
-- 데이터베이스 생성
CREATE DATABASE dsp1830 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 사용자 생성
CREATE USER 'dsp1830'@'localhost' IDENTIFIED BY 'ds701018';

-- 권한 부여
GRANT ALL PRIVILEGES ON dsp1830.* TO 'dsp1830'@'localhost';

-- 권한 적용
FLUSH PRIVILEGES;

-- 종료
EXIT;
```

### 4.2 테이블 생성

데이터베이스 스키마 파일을 실행하여 테이블을 생성합니다:

```bash
# 스키마 파일이 있는 경우
mysql -u dsp1830 -p dsp1830 < /path/to/schema.sql
```

또는 phpMyAdmin에서 SQL 파일을 가져옵니다.

### 4.3 주요 테이블 구조

시스템에서 사용하는 주요 테이블:

| 테이블명 | 용도 |
|----------|------|
| `mlangorder_printauto` | 주문 데이터 |
| `mlangprintauto_namecard` | 명함 가격표 |
| `mlangprintauto_inserted` | 전단지 가격표 |
| `mlangprintauto_envelope` | 봉투 가격표 |
| `mlangprintauto_sticker_new` | 스티커 가격표 |
| `mlangprintauto_msticker` | 자석스티커 가격표 |
| `mlangprintauto_littleprint` | 포스터 가격표 |
| `mlangprintauto_cadarok` | 카다록 가격표 |
| `mlangprintauto_ncrflambeau` | NCR양식지 가격표 |
| `mlangprintauto_merchandisebond` | 상품권 가격표 |
| `mlangprintauto_transactioncate` | 카테고리 옵션 |
| `users` | 사용자 계정 |
| `remember_tokens` | 자동 로그인 토큰 |

### 4.4 연결 테스트

```bash
# MySQL 연결 테스트
mysql -u dsp1830 -p dsp1830 -e "SELECT 1 AS test;"
```

결과: `test: 1` 출력되면 성공

---

## 5. 웹 서버 설정

### 5.1 Apache 가상 호스트 설정 (Linux)

```bash
# 가상 호스트 설정 파일 생성
sudo nano /etc/apache2/sites-available/duson.conf
```

설정 내용:

```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # PHP 설정
    <FilesMatch \.php$>
        SetHandler application/x-httpd-php
    </FilesMatch>

    # 로그 설정
    ErrorLog ${APACHE_LOG_DIR}/duson_error.log
    CustomLog ${APACHE_LOG_DIR}/duson_access.log combined
</VirtualHost>
```

가상 호스트 활성화:

```bash
# 사이트 활성화
sudo a2ensite duson.conf

# 기본 사이트 비활성화 (선택사항)
sudo a2dissite 000-default.conf

# Apache 재시작
sudo service apache2 restart
```

### 5.2 PHP 설정 조정

`php.ini` 파일 위치:
- Linux: `/etc/php/7.4/apache2/php.ini`
- XAMPP: `C:\xampp\php\php.ini`

권장 설정:

```ini
; 파일 업로드 설정
upload_max_filesize = 50M
post_max_size = 50M

; 메모리 설정
memory_limit = 256M

; 타임아웃 설정
max_execution_time = 300

; 시간대 설정
date.timezone = Asia/Seoul

; 세션 설정
session.gc_maxlifetime = 28800
```

설정 변경 후 Apache 재시작:

```bash
sudo service apache2 restart
```

### 5.3 방화벽 설정 (Linux)

```bash
# HTTP 포트 허용
sudo ufw allow 80/tcp

# HTTPS 포트 허용 (SSL 사용 시)
sudo ufw allow 443/tcp

# 방화벽 상태 확인
sudo ufw status
```

---

## 6. 설치 검증

### 6.1 서비스 상태 확인

```bash
# Apache 상태
sudo service apache2 status

# MySQL 상태
sudo service mysql status
```

### 6.2 웹 접속 테스트

브라우저에서 다음 URL 접속:

```
http://localhost/
```

### 6.3 데이터베이스 연결 테스트

브라우저에서 다음 URL 접속 (로컬 환경에서만):

```
http://localhost/?debug_db=1
```

데이터베이스 연결 정보가 표시되면 정상입니다.

### 6.4 PHP 정보 확인

임시 PHP 정보 파일 생성:

```bash
echo "<?php phpinfo(); ?>" | sudo tee /var/www/html/info.php
```

브라우저에서 확인:

```
http://localhost/info.php
```

확인 후 파일 삭제:

```bash
sudo rm /var/www/html/info.php
```

---

## 7. 문제 해결 FAQ

### Q1: "데이터베이스 연결에 실패했습니다" 오류

**원인**: MySQL 연결 정보 불일치 또는 서비스 미실행

**해결 방법**:

1. MySQL 서비스 상태 확인:
   ```bash
   sudo service mysql status
   ```

2. 서비스 시작:
   ```bash
   sudo service mysql start
   ```

3. 연결 정보 확인 (`/var/www/html/config.env.php`):
   - 호스트: `localhost`
   - 사용자: `dsp1830`
   - 비밀번호: `ds701018`
   - 데이터베이스: `dsp1830`

4. MySQL 사용자 권한 확인:
   ```bash
   mysql -u root -p -e "SHOW GRANTS FOR 'dsp1830'@'localhost';"
   ```

---

### Q2: "Permission denied" 오류

**원인**: 파일/디렉토리 권한 문제

**해결 방법**:

```bash
# 소유권 확인
ls -la /var/www/html

# 소유권 변경
sudo chown -R www-data:www-data /var/www/html

# 권한 설정
sudo chmod -R 755 /var/www/html
sudo chmod -R 775 /var/www/html/upload
sudo chmod -R 775 /var/www/html/sessions
```

---

### Q3: 한글이 깨져서 표시됨

**원인**: 문자셋 설정 불일치

**해결 방법**:

1. 데이터베이스 문자셋 확인:
   ```sql
   SHOW VARIABLES LIKE 'character_set%';
   ```

2. 데이터베이스 문자셋 변경:
   ```sql
   ALTER DATABASE dsp1830 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. PHP 파일 상단에 확인:
   ```php
   header('Content-Type: text/html; charset=utf-8');
   ```

4. `db.php`에서 charset 설정 확인:
   ```php
   mysqli_set_charset($db, 'utf8mb4');
   ```

---

### Q4: 페이지가 빈 화면(White Screen)으로 표시됨

**원인**: PHP 오류 발생

**해결 방법**:

1. PHP 오류 표시 활성화 (개발 환경):
   ```bash
   sudo nano /etc/php/7.4/apache2/php.ini
   ```

   설정 변경:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```

2. Apache 재시작:
   ```bash
   sudo service apache2 restart
   ```

3. Apache 오류 로그 확인:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   ```

---

### Q5: 파일 업로드가 되지 않음

**원인**: 업로드 디렉토리 권한 또는 PHP 설정 문제

**해결 방법**:

1. 업로드 디렉토리 권한 확인:
   ```bash
   ls -la /var/www/html/upload
   ```

2. 쓰기 권한 부여:
   ```bash
   sudo chmod -R 775 /var/www/html/upload
   sudo chown -R www-data:www-data /var/www/html/upload
   ```

3. PHP 업로드 설정 확인 (`php.ini`):
   ```ini
   file_uploads = On
   upload_max_filesize = 50M
   post_max_size = 50M
   ```

4. Apache 재시작:
   ```bash
   sudo service apache2 restart
   ```

---

### Q6: 세션이 유지되지 않음

**원인**: 세션 디렉토리 권한 문제

**해결 방법**:

1. 세션 디렉토리 생성 및 권한 설정:
   ```bash
   mkdir -p /var/www/html/sessions
   sudo chmod 775 /var/www/html/sessions
   sudo chown www-data:www-data /var/www/html/sessions
   ```

2. `php.ini`에서 세션 설정 확인:
   ```ini
   session.save_path = "/var/www/html/sessions"
   session.gc_maxlifetime = 28800
   ```

---

### Q7: WSL2에서 localhost 접속 불가

**원인**: Windows와 WSL2 네트워크 분리

**해결 방법**:

1. WSL2 IP 주소 확인:
   ```bash
   hostname -I
   ```

2. Windows 브라우저에서 해당 IP로 접속:
   ```
   http://[WSL2_IP]/
   ```

3. 또는 `/etc/wsl.conf` 설정으로 localhost 포워딩 활성화

---

### Q8: XAMPP에서 Apache 시작 실패

**원인**: 포트 80 충돌 (Skype, IIS 등)

**해결 방법**:

1. 포트 사용 프로그램 확인:
   ```
   netstat -ano | findstr :80
   ```

2. 충돌 프로그램 종료 또는 Apache 포트 변경:
   - XAMPP Control Panel > Apache > Config > httpd.conf
   - `Listen 80`을 `Listen 8080`으로 변경
   - 접속: `http://localhost:8080/`

---

## 다음 단계

설치가 완료되면 다음 문서를 참조하여 시스템을 설정하세요:

1. [CONFIGURATION.md](./CONFIGURATION.md) - 환경 설정
2. [PRODUCT_SETUP.md](./PRODUCT_SETUP.md) - 제품 설정

---

*Version: 1.0*
*Last Updated: 2026-01-18*
