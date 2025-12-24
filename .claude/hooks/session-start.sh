#!/bin/bash
# Duson Print System - Session Start Hook
# 인쇄 쇼핑몰 프로젝트 자동 상태 확인

set -euo pipefail

# 색상 정의
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🖨️  Duson Print System - Session Ready"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. Git 브랜치 및 상태
echo -e "${BLUE}📌 Current Branch:${NC}"
CURRENT_BRANCH=$(git branch --show-current 2>/dev/null || echo "unknown")
echo "   $CURRENT_BRANCH"
echo ""

# 2. 최근 커밋 (컨텍스트 파악용)
echo -e "${BLUE}📝 Recent Commits:${NC}"
git log --oneline --decorate --color -5 2>/dev/null | sed 's/^/   /'
echo ""

# 3. Git 상태 (변경된 파일)
if [[ -n $(git status --short 2>/dev/null) ]]; then
    echo -e "${YELLOW}⚠️  Modified Files:${NC}"
    git status --short | sed 's/^/   /'
    echo ""
fi

# 4. 제품 모듈 상태 체크
echo -e "${BLUE}🛍️  Product Modules Status:${NC}"
PRODUCTS=("inserted" "namecard" "envelope" "sticker_new" "msticker" "cadarok" "littleprint" "ncrflambeau" "merchandisebond" "leaflet")
PRODUCT_COUNT=0
for product in "${PRODUCTS[@]}"; do
    if [[ -d "mlangprintauto/$product" ]]; then
        PRODUCT_COUNT=$((PRODUCT_COUNT + 1))
    fi
done
echo -e "   ${GREEN}✓${NC} $PRODUCT_COUNT/10 products available"
echo ""

# 5. CLAUDE.md의 다음 세션 작업 표시
if [[ -f "CLAUDE.md" ]]; then
    echo -e "${BLUE}📋 Next Session Tasks:${NC}"
    # "다음 세션 작업" 섹션 찾기
    if grep -q "다음 세션 작업" CLAUDE.md; then
        sed -n '/## 📌 다음 세션 작업/,/^##/p' CLAUDE.md | head -n -1 | tail -n +2 | sed 's/^/   /' || echo "   (작업 내용 없음)"
    else
        echo "   ✓ No pending tasks"
    fi
    echo ""
fi

# 6. 프로젝트 통계
echo -e "${BLUE}📊 Project Stats:${NC}"
PHP_FILES=$(find . -name "*.php" -type f 2>/dev/null | wc -l)
JS_FILES=$(find . -name "*.js" -type f 2>/dev/null | wc -l)
echo "   PHP files: $PHP_FILES"
echo "   JS files: $JS_FILES"
echo ""

# 7. 중요 파일 최근 수정 시간
echo -e "${BLUE}🔧 Recent Activity:${NC}"
IMPORTANT_FILES=("CLAUDE.md" "db.php" "config.env.php" "includes/auth.php")
for file in "${IMPORTANT_FILES[@]}"; do
    if [[ -f "$file" ]]; then
        MTIME=$(stat -c "%y" "$file" 2>/dev/null | cut -d'.' -f1 || stat -f "%Sm" -t "%Y-%m-%d %H:%M:%S" "$file" 2>/dev/null)
        echo "   $file: $MTIME"
    fi
done
echo ""

echo -e "${GREEN}✨ Ready to code!${NC}"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
