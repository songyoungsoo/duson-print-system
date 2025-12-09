@echo off
REM 이메일 발송 크론잡 실행 배치 파일
REM 두손기획인쇄 - Windows Task Scheduler에서 실행

REM 현재 시간 로그
echo ========================================
echo 크론잡 배치 파일 실행: %date% %time%
echo ========================================

REM PHP 실행 경로 (XAMPP 기본 경로)
set PHP_PATH=C:\xampp\php\php.exe

REM 크론잡 스크립트 경로
set SCRIPT_PATH=C:\xampp\htdocs\cron\send_emails.php

REM 로그 파일 경로
set LOG_PATH=C:\xampp\htdocs\cron\batch_log.txt

REM PHP 스크립트 실행 및 로그 기록
echo [%date% %time%] 크론잡 시작 >> %LOG_PATH%
%PHP_PATH% %SCRIPT_PATH%

REM 종료 코드 확인
if %ERRORLEVEL% EQU 0 (
    echo [%date% %time%] 크론잡 성공 >> %LOG_PATH%
) else (
    echo [%date% %time%] 크론잡 실패 (종료 코드: %ERRORLEVEL%) >> %LOG_PATH%
)

echo ========================================
echo 크론잡 배치 파일 종료: %date% %time%
echo ========================================

REM 종료
exit /b %ERRORLEVEL%
