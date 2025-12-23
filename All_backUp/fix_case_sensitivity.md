# 대소문자 문제 해결 가이드

## 문제 상황
- 로컬: `admin/mlangprintauto` (소문자)
- 코드: `admin/MlangPrintAuto` (대소문자 섞임) + `admin/mlangprintauto` (소문자)
- Windows는 대소문자 구분 안 함, Linux 서버는 구분함

## 해결 방법

### 1단계: 로컬 코드 수정 (완료)
모든 경로를 소문자로 통일:
```php
// 수정 전
include "../admin/MlangPrintAuto/int/info.php";
action='/admin/MlangPrintAuto/admin.php'

// 수정 후
include "../admin/mlangprintauto/int/info.php";
action='/admin/mlangprintauto/admin.php'
```

### 2단계: FTP 업로드
```powershell
.\ftp_upload_lowercase.ps1
```

### 3단계: 서버 확인 및 조치

#### 옵션 A: 서버 디렉토리명 확인
```bash
# SSH 접속 후
cd /home/dsp1830/public_html/admin
ls -la

# 만약 MlangPrintAuto 폴더가 있다면
mv MlangPrintAuto mlangprintauto_backup
# 그리고 mlangprintauto 폴더 사용
```

#### 옵션 B: 심볼릭 링크 생성 (임시 방편)
```bash
# SSH 접속 후
cd /home/dsp1830/public_html/admin
ln -s mlangprintauto MlangPrintAuto
```

#### 옵션 C: .htaccess로 리다이렉트
```apache
# /home/dsp1830/public_html/admin/.htaccess
RewriteEngine On
RewriteBase /admin/

# 대소문자 혼용 경로를 소문자로 리다이렉트
RewriteCond %{REQUEST_URI} ^/admin/MlangPrintAuto/(.*)$ [NC]
RewriteRule ^MlangPrintAuto/(.*)$ mlangprintauto/$1 [R=301,L]
```

## 수정된 파일 목록
1. mlangorder_printauto/OrderFormOrderTree.php
2. mlangorder_printauto/WindowSian.php
3. mlangorder_printauto/OrderResult_original.php
4. mlangorder_printauto/index.php

## 검증 방법
```bash
# 서버에서 확인
grep -r "MlangPrintAuto" /home/dsp1830/public_html/mlangorder_printauto/
# 결과가 없어야 함 (모두 소문자로 변경됨)
```

## 주의사항
- 서버의 실제 디렉토리명이 `mlangprintauto`인지 `MlangPrintAuto`인지 먼저 확인
- 모든 코드를 해당 디렉토리명에 맞춰 통일
- 대소문자 혼용은 Linux 서버에서 404 오류 발생 원인
