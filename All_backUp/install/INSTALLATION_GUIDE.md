# 🖨️ 두손기획인쇄 설치 가이드

> **초보자도 쉽게 따라할 수 있는 복사-붙여넣기 설치 가이드**

---

## 📋 목차

1. [설치 방법 선택하기](#설치-방법-선택하기)
2. [방법 A: 웹 기반 설치 마법사](#방법-a-웹-기반-설치-마법사)
3. [방법 B: CLI (명령줄) 설치](#방법-b-cli-명령줄-설치)
4. [방법 C: Docker 설치](#방법-c-docker-설치)
5. [설치 후 확인](#설치-후-확인)
6. [문제 해결](#문제-해결)

---

## 설치 방법 선택하기

| 방법 | 난이도 | 추천 대상 | 소요 시간 |
|------|--------|-----------|-----------|
| **웹 기반** | ⭐ 쉬움 | 웹호스팅 사용자, 초보자 | 5분 |
| **CLI** | ⭐⭐ 보통 | 서버 관리 경험자 | 3분 |
| **Docker** | ⭐⭐⭐ 고급 | 개발자, DevOps | 10분 |

---

# 방법 A: 웹 기반 설치 마법사

> **가장 쉬운 방법!** 웹 브라우저에서 클릭만으로 설치

## 사전 준비

- ✅ 웹호스팅 또는 웹서버 (Apache/Nginx + PHP 7.4+)
- ✅ MySQL 5.7+ 데이터베이스
- ✅ FTP 접속 정보 (호스팅 사용 시)

## 1단계: 파일 다운로드

### 다운로드 링크
```
http://your-server.com/install/packages/duson_web_install.tar.gz
```

또는 FTP로 서버에서 직접 다운로드:
```
/var/www/html/install/packages/duson_web_install.tar.gz
```

## 2단계: 파일 업로드 및 압축 해제

### 방법 1: cPanel/Plesk 사용 시
1. 파일 관리자 열기
2. `public_html` 폴더로 이동
3. "업로드" 클릭 → `duson_web_install.tar.gz` 선택
4. 업로드된 파일 우클릭 → "Extract" 또는 "압축 해제"

### 방법 2: FTP + SSH 사용 시

**FTP로 업로드:**
```
로컬 파일: duson_web_install.tar.gz
원격 경로: /public_html/ 또는 /var/www/html/
```

**SSH로 압축 해제:** (아래 명령어 복사-붙여넣기)
```bash
cd /var/www/html
tar -xzf duson_web_install.tar.gz
```

## 3단계: 설치 마법사 실행

### 브라우저에서 접속
```
http://your-domain.com/install/
```

### 설치 단계 따라하기

#### Step 1: 시스템 요구사항 확인
- 모든 항목이 ✅ 녹색이면 "다음" 클릭
- ❌ 빨간색 항목이 있으면 호스팅 업체에 문의

#### Step 2: 데이터베이스 설정
입력 예시:
```
호스트: localhost
데이터베이스명: dsp1830
사용자명: dsp1830
비밀번호: your_password
```

#### Step 3: 관리자 계정 생성
입력 예시:
```
관리자 ID: admin
비밀번호: Admin123!@#
이름: 관리자
이메일: admin@your-domain.com
```

#### Step 4: 사이트 설정
입력 예시:
```
사이트명: 두손기획인쇄
회사명: 두손기획인쇄
대표전화: 1688-2384
```

#### Step 5: 설치 완료
"설치 완료" 버튼 클릭

## 4단계: 설치 폴더 삭제 (보안)

**SSH 또는 FTP로 삭제:**
```bash
rm -rf /var/www/html/install/
```

또는 파일 관리자에서 `install` 폴더 삭제

---

# 방법 B: CLI (명령줄) 설치

> **서버에 직접 접속하여 명령어로 설치**

## 운영체제별 터미널 열기

### 🐧 Linux / Mac

**터미널 열기:**
- Linux: `Ctrl + Alt + T`
- Mac: `Cmd + Space` → "터미널" 검색

### 🪟 Windows

**방법 1: PowerShell (권장)**
1. `Windows 키` 누르기
2. "PowerShell" 검색
3. "관리자 권한으로 실행" 클릭

**방법 2: CMD (명령 프롬프트)**
1. `Windows 키 + R` 누르기
2. `cmd` 입력 후 Enter

**방법 3: WSL (Windows Subsystem for Linux)**
1. `Windows 키` 누르기
2. "Ubuntu" 또는 "WSL" 검색
3. 클릭하여 실행

---

## Linux / Mac / WSL 설치

### 1단계: 서버 접속 (원격 서버인 경우)
```bash
ssh username@your-server.com
```

### 2단계: 웹 디렉토리로 이동
```bash
cd /var/www/html
```

### 3단계: 설치 파일 다운로드 및 압축 해제
```bash
# 파일 다운로드 (URL을 실제 주소로 변경)
wget http://your-server.com/install/packages/duson_cli_install.tar.gz

# 압축 해제
tar -xzf duson_cli_install.tar.gz
```

### 4단계-A: 대화형 설치 (권장)
```bash
php install/cli_install.php
```

화면의 안내에 따라 정보 입력:
```
데이터베이스 호스트 [localhost]: (Enter)
데이터베이스 이름 [dsp1830]: (Enter)
데이터베이스 사용자 [dsp1830]: (Enter)
데이터베이스 비밀번호: your_password
관리자 ID [admin]: (Enter)
관리자 비밀번호: Admin123!@#
...
```

### 4단계-B: 자동 설치 (설정 파일 사용)

**설정 파일 생성:**
```bash
cat > /var/www/html/install_config.json << 'EOF'
{
    "db_host": "localhost",
    "db_name": "dsp1830",
    "db_user": "dsp1830",
    "db_pass": "your_password_here",
    "admin_id": "admin",
    "admin_pass": "Admin123!@#",
    "admin_name": "관리자",
    "admin_email": "admin@example.com",
    "site_name": "두손기획인쇄",
    "company_name": "두손기획인쇄",
    "company_phone": "1688-2384"
}
EOF
```

**자동 설치 실행:**
```bash
php install/cli_install.php --auto --config=/var/www/html/install_config.json
```

### 5단계: 설치 폴더 삭제
```bash
rm -rf /var/www/html/install/
rm -f /var/www/html/install_config.json
rm -f /var/www/html/duson_cli_install.tar.gz
```

---

## Windows PowerShell 설치

### 사전 준비: PHP 설치 확인
```powershell
php -v
```

PHP가 없으면:
```powershell
# Chocolatey로 PHP 설치
Set-ExecutionPolicy Bypass -Scope Process -Force
[System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))

choco install php -y
```

### 1단계: 웹 디렉토리로 이동
```powershell
cd C:\xampp\htdocs
```
또는
```powershell
cd C:\inetpub\wwwroot
```

### 2단계: 설치 파일 다운로드
```powershell
Invoke-WebRequest -Uri "http://your-server.com/install/packages/duson_cli_install.tar.gz" -OutFile "duson_cli_install.tar.gz"
```

### 3단계: 압축 해제
```powershell
tar -xzf duson_cli_install.tar.gz
```

### 4단계: 설치 실행
```powershell
php install\cli_install.php
```

### 5단계: 정리
```powershell
Remove-Item -Recurse -Force install
Remove-Item duson_cli_install.tar.gz
```

---

## Windows CMD (명령 프롬프트) 설치

### 1단계: 웹 디렉토리로 이동
```cmd
cd C:\xampp\htdocs
```

### 2단계: 설치 파일이 이미 있다면 압축 해제
```cmd
tar -xzf duson_cli_install.tar.gz
```

### 3단계: 설치 실행
```cmd
php install\cli_install.php
```

### 4단계: 정리
```cmd
rmdir /s /q install
del duson_cli_install.tar.gz
```

---

# 방법 C: Docker 설치

> **Docker로 완전히 격리된 환경에서 실행**

## 사전 준비: Docker 설치

### 🐧 Linux (Ubuntu/Debian)

**1. Docker 설치 (한 줄씩 복사-붙여넣기)**
```bash
# 시스템 업데이트
sudo apt update

# 필수 패키지 설치
sudo apt install -y apt-transport-https ca-certificates curl gnupg lsb-release

# Docker 공식 GPG 키 추가
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Docker 저장소 추가
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Docker 설치
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# 현재 사용자를 docker 그룹에 추가 (sudo 없이 사용)
sudo usermod -aG docker $USER

# 변경사항 적용 (재로그인 또는 아래 명령)
newgrp docker
```

**2. 설치 확인**
```bash
docker --version
docker compose version
```

### 🍎 Mac

**1. Docker Desktop 다운로드 및 설치**
```bash
# Homebrew가 있는 경우
brew install --cask docker
```

또는 공식 사이트에서 다운로드:
```
https://www.docker.com/products/docker-desktop/
```

**2. Docker Desktop 실행**
- Launchpad에서 "Docker" 검색 후 실행
- 메뉴바에 🐳 고래 아이콘 나타날 때까지 대기

**3. 설치 확인**
```bash
docker --version
docker compose version
```

### 🪟 Windows

**1. WSL2 활성화 (PowerShell 관리자 권한)**
```powershell
# WSL 기능 활성화
dism.exe /online /enable-feature /featurename:Microsoft-Windows-Subsystem-Linux /all /norestart

# 가상 머신 플랫폼 활성화
dism.exe /online /enable-feature /featurename:VirtualMachinePlatform /all /norestart
```

**2. 컴퓨터 재시작**
```powershell
Restart-Computer
```

**3. WSL2를 기본값으로 설정**
```powershell
wsl --set-default-version 2
```

**4. Docker Desktop 다운로드 및 설치**
```
https://www.docker.com/products/docker-desktop/
```
- 다운로드한 설치 파일 실행
- "Use WSL 2 instead of Hyper-V" 옵션 선택
- 설치 완료 후 재시작

**5. Docker Desktop 실행**
- 시작 메뉴에서 "Docker Desktop" 검색 후 실행
- 시스템 트레이에 🐳 고래 아이콘 나타날 때까지 대기

**6. 설치 확인 (PowerShell)**
```powershell
docker --version
docker compose version
```

---

## Docker로 두손기획인쇄 설치

### 모든 운영체제 공통 (Docker 설치 후)

**1. 작업 디렉토리 생성 및 이동**

Linux/Mac:
```bash
mkdir -p ~/duson-print
cd ~/duson-print
```

Windows PowerShell:
```powershell
mkdir $HOME\duson-print
cd $HOME\duson-print
```

**2. 설치 파일 다운로드**

Linux/Mac:
```bash
wget http://your-server.com/install/packages/duson_docker_install.tar.gz
tar -xzf duson_docker_install.tar.gz
```

Windows PowerShell:
```powershell
Invoke-WebRequest -Uri "http://your-server.com/install/packages/duson_docker_install.tar.gz" -OutFile "duson_docker_install.tar.gz"
tar -xzf duson_docker_install.tar.gz
```

**3. 환경변수 설정**
```bash
# Linux/Mac
cp docker/.env.example docker/.env
nano docker/.env  # 또는 vim, code 등 편집기 사용

# Windows PowerShell
Copy-Item docker\.env.example docker\.env
notepad docker\.env
```

**.env 파일 수정 예시:**
```env
# 데이터베이스 설정 (반드시 변경!)
DB_NAME=dsp1830
DB_USER=dsp1830
DB_PASS=your_secure_password_here
MYSQL_ROOT_PASSWORD=your_root_password_here

# 회사 정보
COMPANY_NAME=두손기획인쇄
COMPANY_PHONE=1688-2384

# 관리자 이메일
ADMIN_EMAIL=admin@your-domain.com
```

**4. Docker 컨테이너 시작**
```bash
cd docker
docker compose up -d
```

**5. 시작 확인**
```bash
docker compose ps
```

출력 예시:
```
NAME              IMAGE              STATUS              PORTS
duson_web         duson-print-web    Up (healthy)        0.0.0.0:80->80/tcp
duson_db          mysql:8.0          Up (healthy)        0.0.0.0:3306->3306/tcp
```

**6. 브라우저에서 접속**
```
사이트: http://localhost/
관리자: http://localhost/admin/
기본 계정: admin / admin123
```

---

## Docker 관리 명령어

### 자주 사용하는 명령어

**컨테이너 상태 확인:**
```bash
docker compose ps
```

**로그 확인:**
```bash
docker compose logs -f
```

**컨테이너 중지:**
```bash
docker compose down
```

**컨테이너 재시작:**
```bash
docker compose restart
```

**phpMyAdmin 포함 실행 (DB 관리용):**
```bash
docker compose --profile admin up -d
```
접속: `http://localhost:8080`

### 데이터 백업

**데이터베이스 백업:**
```bash
docker exec duson_db mysqldump -u dsp1830 -p dsp1830 > backup.sql
```

**파일 백업:**
```bash
docker cp duson_web:/var/www/html/ImgFolder ./backup/ImgFolder
```

### 컨테이너 내부 접속

**웹 서버:**
```bash
docker exec -it duson_web bash
```

**MySQL:**
```bash
docker exec -it duson_db mysql -u dsp1830 -p
```

---

# 설치 후 확인

## 접속 테스트

| 페이지 | URL | 예상 결과 |
|--------|-----|-----------|
| 메인 | http://localhost/ | 인쇄몰 메인 페이지 |
| 관리자 | http://localhost/admin/ | 로그인 페이지 |
| 명함 주문 | http://localhost/mlangprintauto/namecard/ | 명함 주문 페이지 |

## 관리자 로그인

```
URL: http://localhost/admin/
ID: admin (또는 설치 시 입력한 ID)
비밀번호: 설치 시 입력한 비밀번호
```

---

# 문제 해결

## 자주 발생하는 문제

### 1. "데이터베이스 연결 실패"

**원인:** MySQL 정보가 잘못되었거나 서버가 실행 중이 아님

**해결:**
```bash
# MySQL 상태 확인
sudo systemctl status mysql

# MySQL 시작
sudo systemctl start mysql

# 접속 테스트
mysql -u dsp1830 -p -h localhost
```

### 2. "Permission denied" 오류

**해결:**
```bash
# 웹 디렉토리 권한 설정
sudo chown -R www-data:www-data /var/www/html
sudo chmod -R 755 /var/www/html
sudo chmod -R 777 /var/www/html/ImgFolder
sudo chmod -R 777 /var/www/html/mlangorder_printauto/upload
```

### 3. Docker "port already in use"

**해결:**
```bash
# 80번 포트 사용 중인 프로세스 확인
sudo lsof -i :80

# 해당 프로세스 종료 또는 docker-compose.yml에서 포트 변경
# ports: - "8080:80"  로 변경 후 http://localhost:8080 으로 접속
```

### 4. Windows에서 tar 명령어 없음

**해결 (PowerShell):**
```powershell
# 7-Zip 설치
choco install 7zip -y

# 압축 해제
7z x duson_web_install.tar.gz
7z x duson_web_install.tar
```

### 5. PHP 버전이 낮음

**확인:**
```bash
php -v
```

**Ubuntu에서 PHP 7.4 설치:**
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php7.4 php7.4-mysql php7.4-gd php7.4-mbstring php7.4-curl php7.4-zip -y
```

---

## 지원 및 문의

- **이메일:** dsp1830@naver.com
- **전화:** 1688-2384
- **주소:** 서울 영등포구 영등포로36길 9 송호빌딩 1층

---

*마지막 업데이트: 2025-12-07*
