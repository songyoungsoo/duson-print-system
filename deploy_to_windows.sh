#!/bin/bash

TARGET_DIR="/mnt/c/KoreanInputKeeper"

echo "=== 한글 입력 유지 프로그램 Windows 배포 스크립트 ==="
echo ""

if [ ! -d "$TARGET_DIR" ]; then
    echo "📁 Windows 폴더 생성: C:\\KoreanInputKeeper"
    mkdir -p "$TARGET_DIR"
fi

echo "📦 파일 복사 중..."
cp /var/www/html/korean_input_keeper.py "$TARGET_DIR/"
cp /var/www/html/requirements.txt "$TARGET_DIR/"
cp /var/www/html/start_korean_keeper.bat "$TARGET_DIR/"
cp /var/www/html/README_KOREAN_INPUT_KEEPER.md "$TARGET_DIR/"

if [ $? -eq 0 ]; then
    echo "✅ 복사 완료!"
    echo ""
    echo "다음 단계:"
    echo "1. Windows PowerShell 열기"
    echo "2. 다음 명령 실행:"
    echo ""
    echo "   cd C:\\KoreanInputKeeper"
    echo "   pip install -r requirements.txt"
    echo "   python korean_input_keeper.py"
    echo ""
    echo "또는 start_korean_keeper.bat 더블클릭"
else
    echo "❌ 복사 실패"
    exit 1
fi
