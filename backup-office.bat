@echo off
cd /d C:\xampp\htdocs
echo ==============================================
echo   두손기획인쇄 Git 백업 시스템 (사무실용 - GitHub만)
echo ==============================================
echo.

echo [1/3] 최신 버전 확인 및 가져오기...
git pull origin main
if %ERRORLEVEL% NEQ 0 (
    echo ❌ 최신 버전 가져오기 실패! 인터넷 연결을 확인하세요.
    pause
    exit /b 1
) else (
    echo ✅ 최신 버전 동기화 완료!
)
echo.

echo [2/3] 변경된 파일들을 스테이징 영역에 추가 중...
git add .
if %ERRORLEVEL% NEQ 0 (
    echo ❌ 파일 추가 실패!
    pause
    exit /b 1
)

echo [3/3] 커밋 및 GitHub 백업 중...
set /p commit_msg="커밋 메시지를 입력하세요 (기본: 사무실 작업): "
if "%commit_msg%"=="" set commit_msg=사무실 작업

git commit -m "%commit_msg%"
if %ERRORLEVEL% NEQ 0 (
    echo ⚠️ 커밋할 변경사항이 없습니다.
    echo.
) else (
    echo ✅ 커밋 성공!
    echo.
    
    git push origin main
    if %ERRORLEVEL% NEQ 0 (
        echo ❌ GitHub 백업 실패! 인터넷 연결을 확인하세요.
        pause
        exit /b 1
    ) else (
        echo ✅ GitHub 백업 완료!
    )
)

echo.
echo ==============================================
echo   사무실 백업 완료!
echo   - GitHub: https://github.com/songyoungsoo/duson-print-system
echo   - 집에서 자동으로 동기화됩니다
echo ==============================================
echo.
pause