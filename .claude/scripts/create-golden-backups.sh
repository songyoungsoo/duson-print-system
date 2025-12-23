#!/bin/bash
# 9개 품목의 골든 백업 생성 스크립트
# 현재 정상 작동하는 계산기 코드를 골든 백업으로 저장

PRODUCTS=(
    "inserted"
    "namecard"
    "envelope"
    "sticker_new"
    "msticker"
    "cadarok"
    "littleprint"
    "ncrflambeau"
    "merchandisebond"
)

BASE_DIR="/var/www/html/mlangprintauto"
GOLDEN_DIR="/var/www/html/.claude/golden-backups"

echo "📦 9개 품목의 골든 백업 생성 중..."
echo ""

TOTAL=0
SUCCESS=0

for product in "${PRODUCTS[@]}"; do
    echo "[$product] 백업 생성..."

    PRODUCT_DIR="$BASE_DIR/$product"
    BACKUP_DIR="$GOLDEN_DIR/$product"

    if [[ ! -d "$PRODUCT_DIR" ]]; then
        echo "  ⚠️  디렉토리 없음: $PRODUCT_DIR"
        continue
    fi

    mkdir -p "$BACKUP_DIR"

    # 핵심 파일 백업
    FILES_TO_BACKUP=(
        "index.php"
        "calculator.js"
        "calculate_price_ajax.php"
        "js/${product}.js"
        "js/${product}-compact.js"
    )

    for file in "${FILES_TO_BACKUP[@]}"; do
        SOURCE="$PRODUCT_DIR/$file"
        if [[ -f "$SOURCE" ]]; then
            cp "$SOURCE" "$BACKUP_DIR/$(basename "$file")"
            echo "  ✅ $file"
            SUCCESS=$((SUCCESS + 1))
        fi
        TOTAL=$((TOTAL + 1))
    done

    echo ""
done

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ 골든 백업 생성 완료!"
echo "   성공: $SUCCESS / $TOTAL 파일"
echo "   위치: $GOLDEN_DIR"
echo ""
echo "💡 복구 방법:"
echo "   bash /var/www/html/.claude/scripts/restore-from-golden.sh [product]"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
