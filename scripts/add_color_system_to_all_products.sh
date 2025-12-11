#!/bin/bash

# 모든 제품 페이지에 color-system-unified.css를 추가하는 스크립트
# 작성일: 2025-10-11

PRODUCTS=(
    "namecard"
    "envelope"
    "sticker_new"
    "msticker"
    "littleprint"
    "cadarok"
    "merchandisebond"
    "ncrflambeau"
)

BASE_DIR="/var/www/html/mlangprintauto"
COLOR_SYSTEM_LINE='    <link rel="stylesheet" href="../../css/color-system-unified.css">'

echo "========================================="
echo "모든 제품에 color-system-unified.css 추가"
echo "========================================="
echo ""

for product in "${PRODUCTS[@]}"; do
    INDEX_FILE="$BASE_DIR/$product/index.php"

    if [ ! -f "$INDEX_FILE" ]; then
        echo "❌ $product: index.php 파일이 없습니다"
        continue
    fi

    # 이미 추가되어 있는지 확인
    if grep -q "color-system-unified.css" "$INDEX_FILE"; then
        echo "✓ $product: 이미 추가되어 있음"
        continue
    fi

    # <head> 태그 다음에 추가
    if grep -q "<head>" "$INDEX_FILE"; then
        # 백업
        cp "$INDEX_FILE" "$INDEX_FILE.backup_$(date +%Y%m%d_%H%M%S)"

        # <head> 다음 줄에 color-system-unified.css 추가
        sed -i '/<head>/a\    <!-- 🎨 통합 컬러 시스템 -->\n    <link rel="stylesheet" href="../../css/color-system-unified.css">' "$INDEX_FILE"

        echo "✅ $product: color-system-unified.css 추가 완료"
    else
        echo "⚠️  $product: <head> 태그를 찾을 수 없음"
    fi
done

echo ""
echo "========================================="
echo "작업 완료!"
echo "========================================="
