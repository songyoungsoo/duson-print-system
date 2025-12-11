#!/bin/bash
# Color Usage Analysis Script
echo "두손기획인쇄 컬러 사용 현황 분석"
echo "=================================="
echo ""

CSS_DIR="/var/www/html/css"
PRODUCT_DIR="/var/www/html/mlangprintauto"

echo "[1] Hardcoded 컬러 값 통계"
echo "----------------------------"
HEX6=$(grep -roh "#[0-9a-fA-F]\{6\}" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo "Hex 6자리: $HEX6 개"

RGB=$(grep -roh "rgba\?([^)]*)" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo "RGB/RGBA: $RGB 개"

TOTAL=$((HEX6 + RGB))
echo "총 Hardcoded: $TOTAL 개"
echo ""

echo "[2] 가장 많이 사용되는 컬러 Top 10"
echo "-----------------------------------"
grep -roh "#[0-9a-fA-F]\{6\}" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | tr '[:lower:]' '[:upper:]' | sort | uniq -c | sort -rn | head -10
echo ""

echo "[3] CSS 변수 사용 현황"
echo "----------------------"
VAR_DEF=$(grep -roh "\-\-[a-z-]*:\s*#" "$CSS_DIR" --include="*.css" 2>/dev/null | wc -l)
echo "CSS 변수 정의: $VAR_DEF 개"

VAR_USE=$(grep -roh "var(--[a-z-]*)" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo "CSS 변수 사용: $VAR_USE 개"
echo ""

echo "분석 완료!"
