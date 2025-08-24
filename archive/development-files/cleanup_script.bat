@echo off
REM Duson Print System Safe Cleanup Script
REM This script removes temporary files, backups, and duplicates safely

echo =================================================
echo   두손기획인쇄 시스템 정리 스크립트 v1.0
echo   Duson Print System Cleanup Script
echo =================================================
echo.

REM Create backup directory first
if not exist "cleanup_backup" mkdir cleanup_backup
echo [INFO] 백업 폴더 생성완료: cleanup_backup\

REM 1. Remove Korean copy files (복사본)
echo [1/6] 복사본 파일 삭제 중...
for /r . %%f in (*복사본*) do (
    if exist "%%f" (
        echo 삭제: %%f
        move "%%f" "cleanup_backup\" 2>nul
    )
)

REM 2. Remove backup files  
echo [2/6] 백업 파일 삭제 중...
for /r . %%f in (*backup*.php *backup*.css *.bak) do (
    if exist "%%f" (
        echo 삭제: %%f
        move "%%f" "cleanup_backup\" 2>nul
    )
)

REM 3. Remove debug files
echo [3/6] 디버그 파일 삭제 중...
for /r . %%f in (debug_*.php test_*.sql *_test_data.sql) do (
    if exist "%%f" (
        echo 삭제: %%f
        move "%%f" "cleanup_backup\" 2>nul
    )
)

REM 4. Remove versioned admin folders (keep main versions)
echo [4/6] 구버전 관리자 폴더 삭제 중...
if exist "admin\MlangPrintAuto250410" (
    echo 이동: admin\MlangPrintAuto250410 
    move "admin\MlangPrintAuto250410" "cleanup_backup\" 2>nul
)
if exist "admin\MlangPrintAuto250418" (
    echo 이동: admin\MlangPrintAuto250418
    move "admin\MlangPrintAuto250418" "cleanup_backup\" 2>nul  
)
if exist "admin\MlangPrintAuto250425" (
    echo 이동: admin\MlangPrintAuto250425
    move "admin\MlangPrintAuto250425" "cleanup_backup\" 2>nul
)
if exist "admin\bbs250512" (
    echo 이동: admin\bbs250512
    move "admin\bbs250512" "cleanup_backup\" 2>nul
)

REM 5. Remove duplicate envelope folder
echo [5/6] 중복 봉투 폴더 삭제 중...
if exist "MlangPrintAuto\envelope - 정상250809" (
    echo 이동: MlangPrintAuto\envelope - 정상250809
    move "MlangPrintAuto\envelope - 정상250809" "cleanup_backup\" 2>nul
)

REM 6. Clean development documentation (optional - comment out if needed)
echo [6/6] 개발 문서 정리 중...
for /r . %%f in (*.md) do (
    if not "%%f"=="CLAUDE.md" if not "%%f"=="README.md" (
        echo 확인필요: %%f
        REM Uncomment next line to move docs to backup:
        REM move "%%f" "cleanup_backup\" 2>nul
    )
)

echo.
echo =================================================
echo   정리 완료! 
echo   - 삭제된 파일들은 cleanup_backup\ 폴더에 보관됨
echo   - 필요시 복원 가능
echo   - SuperClaude/, node_modules/ 수동 확인 필요
echo =================================================
echo.
echo [선택사항] 용량이 큰 폴더 삭제:
echo - SuperClaude\ 폴더 (Python CLI 도구 - PHP용 불필요)
echo - node_modules\ 폴더 (테스트용 - 운영환경 불필요)
echo.
pause

REM Optional: Remove large third-party folders (uncomment if needed)
REM rmdir /s /q SuperClaude
REM rmdir /s /q node_modules