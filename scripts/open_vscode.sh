#!/bin/bash

# Duson Print System - VS Code Quick Launch Script
# 이 스크립트를 실행하면 VS Code가 워크스페이스 모드로 자동 실행됩니다

WORKSPACE_FILE="/var/www/html/duson-print-system.code-workspace"

if [ ! -f "$WORKSPACE_FILE" ]; then
    echo "❌ 워크스페이스 파일을 찾을 수 없습니다: $WORKSPACE_FILE"
    exit 1
fi

echo "🚀 VS Code 실행 중..."
echo "📂 프로젝트: Duson Print System"
echo "📍 경로: /var/www/html"
echo ""

# WSL2에서 Windows VS Code 실행
code "$WORKSPACE_FILE"

# 잠시 대기 (VS Code가 실행될 시간을 줌)
sleep 1

echo "✅ VS Code가 실행되었습니다!"
echo ""
echo "💡 팁:"
echo "   - 이 스크립트를 터미널에서 실행: ./scripts/open_vscode.sh"
echo "   - 또는 워크스페이스 파일을 직접 실행: code duson-print-system.code-workspace"
echo "   - Windows에서 더블클릭: duson-print-system.code-workspace"
