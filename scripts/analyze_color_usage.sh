#!/bin/bash

###############################################################################
# 두손기획인쇄 컬러 사용 현황 분석 스크립트
# 작성일: 2025-10-11
# 목적: CSS 파일 내 hardcoded 컬러 값 추출 및 분석
###############################################################################

echo "=========================================="
echo "두손기획인쇄 컬러 사용 현황 분석"
echo "=========================================="
echo ""

# 색상 코드 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# 작업 디렉토리
CSS_DIR="/var/www/html/css"
PRODUCT_DIR="/var/www/html/mlangprintauto"
OUTPUT_DIR="/var/www/html/CLAUDE_DOCS"
OUTPUT_FILE="$OUTPUT_DIR/color_analysis_$(date +%Y%m%d_%H%M%S).txt"

# 출력 파일 생성
mkdir -p "$OUTPUT_DIR"
touch "$OUTPUT_FILE"

echo "분석 시작: $(date)" | tee -a "$OUTPUT_FILE"
echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 1. Hardcoded 컬러 값 통계
###############################################################################

echo -e "${BLUE}[1] Hardcoded 컬러 값 통계${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

# Hex 컬러 (6자리)
HEX6_COUNT=$(grep -roh "#[0-9a-fA-F]\{6\}" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo -e "${YELLOW}Hex 6자리 (#RRGGBB):${NC} $HEX6_COUNT 개" | tee -a "$OUTPUT_FILE"

# Hex 컬러 (3자리)
HEX3_COUNT=$(grep -roh "#[0-9a-fA-F]\{3\}[^0-9a-fA-F]" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo -e "${YELLOW}Hex 3자리 (#RGB):${NC} $HEX3_COUNT 개" | tee -a "$OUTPUT_FILE"

# RGB/RGBA
RGB_COUNT=$(grep -roh "rgba\?([^)]*)" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo -e "${YELLOW}RGB/RGBA:${NC} $RGB_COUNT 개" | tee -a "$OUTPUT_FILE"

TOTAL_HARDCODED=$((HEX6_COUNT + HEX3_COUNT + RGB_COUNT))
echo -e "${RED}총 Hardcoded 컬러:${NC} $TOTAL_HARDCODED 개" | tee -a "$OUTPUT_FILE"
echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 2. 가장 많이 사용되는 컬러 Top 20
###############################################################################

echo -e "${BLUE}[2] 가장 많이 사용되는 컬러 Top 20${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

grep -roh "#[0-9a-fA-F]\{6\}" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | \
  tr '[:lower:]' '[:upper:]' | \
  sort | uniq -c | sort -rn | head -20 | \
  tee -a "$OUTPUT_FILE"

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 3. 제품별 컬러 사용 현황
###############################################################################

echo -e "${BLUE}[3] 제품별 컬러 사용 현황${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

PRODUCTS=("inserted" "namecard" "envelope" "sticker_new" "msticker" "littleprint" "cadarok" "merchandisebond" "ncrflambeau")

for product in "${PRODUCTS[@]}"; do
  if [ -d "$PRODUCT_DIR/$product" ]; then
    COUNT=$(grep -roh "#[0-9a-fA-F]\{6\}" "$PRODUCT_DIR/$product" --include="*.css" 2>/dev/null | wc -l)
    echo -e "${GREEN}$product:${NC} $COUNT 개" | tee -a "$OUTPUT_FILE"
  fi
done

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 4. CSS 변수 사용 현황
###############################################################################

echo -e "${BLUE}[4] CSS 변수 사용 현황${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

# CSS 변수 정의
VAR_DEFINITION_COUNT=$(grep -roh "\-\-[a-z-]*:\s*#" "$CSS_DIR" --include="*.css" 2>/dev/null | wc -l)
echo -e "${GREEN}CSS 변수 정의:${NC} $VAR_DEFINITION_COUNT 개" | tee -a "$OUTPUT_FILE"

# CSS 변수 사용
VAR_USAGE_COUNT=$(grep -roh "var(--[a-z-]*)" "$CSS_DIR" "$PRODUCT_DIR" --include="*.css" 2>/dev/null | wc -l)
echo -e "${GREEN}CSS 변수 사용:${NC} $VAR_USAGE_COUNT 개" | tee -a "$OUTPUT_FILE"

# 변수 vs Hardcoded 비율
if [ $TOTAL_HARDCODED -gt 0 ]; then
  RATIO=$(awk "BEGIN {printf \"%.2f\", ($VAR_USAGE_COUNT / ($VAR_USAGE_COUNT + $TOTAL_HARDCODED)) * 100}")
  echo -e "${YELLOW}변수 사용 비율:${NC} $RATIO%" | tee -a "$OUTPUT_FILE"
fi

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 5. 주요 디자인 토큰 파일
###############################################################################

echo -e "${BLUE}[5] 주요 디자인 토큰 파일${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

TOKEN_FILES=(
  "css/design-tokens.css"
  "css/brand-design-system.css"
  "css/mlang-design-system.css"
  "css/color-system-unified.css"
)

for file in "${TOKEN_FILES[@]}"; do
  if [ -f "/var/www/html/$file" ]; then
    SIZE=$(du -h "/var/www/html/$file" | cut -f1)
    VAR_COUNT=$(grep -c "\-\-" "/var/www/html/$file" 2>/dev/null || echo "0")
    echo -e "${GREEN}✓${NC} $file (크기: $SIZE, 변수: $VAR_COUNT 개)" | tee -a "$OUTPUT_FILE"
  else
    echo -e "${RED}✗${NC} $file (없음)" | tee -a "$OUTPUT_FILE"
  fi
done

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 6. 파일 크기 통계
###############################################################################

echo -e "${BLUE}[6] CSS 파일 크기 통계${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

TOTAL_CSS_SIZE=$(find "$CSS_DIR" -name "*.css" -type f -exec du -ch {} + 2>/dev/null | grep total | cut -f1)
CSS_FILE_COUNT=$(find "$CSS_DIR" -name "*.css" -type f 2>/dev/null | wc -l)

echo -e "${YELLOW}CSS 파일 개수:${NC} $CSS_FILE_COUNT 개" | tee -a "$OUTPUT_FILE"
echo -e "${YELLOW}총 CSS 크기:${NC} $TOTAL_CSS_SIZE" | tee -a "$OUTPUT_FILE"

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 7. Phase2 백업 파일
###############################################################################

echo -e "${BLUE}[7] Phase2 백업 파일${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

PHASE2_COUNT=$(find "$CSS_DIR" -name "*.phase2" -type f 2>/dev/null | wc -l)
BACKUP_COUNT=$(find "$CSS_DIR" -name "*.backup*" -type f 2>/dev/null | wc -l)

echo -e "${YELLOW}*.phase2 파일:${NC} $PHASE2_COUNT 개" | tee -a "$OUTPUT_FILE"
echo -e "${YELLOW}*.backup* 파일:${NC} $BACKUP_COUNT 개" | tee -a "$OUTPUT_FILE"

if [ $PHASE2_COUNT -gt 0 ]; then
  echo "" | tee -a "$OUTPUT_FILE"
  echo "Phase2 백업 파일 목록:" | tee -a "$OUTPUT_FILE"
  find "$CSS_DIR" -name "*.phase2" -type f 2>/dev/null | tee -a "$OUTPUT_FILE"
fi

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 8. 권장 사항
###############################################################################

echo -e "${BLUE}[8] 권장 사항${NC}" | tee -a "$OUTPUT_FILE"
echo "-------------------------------------------" | tee -a "$OUTPUT_FILE"

if [ $TOTAL_HARDCODED -gt 100 ]; then
  echo -e "${RED}⚠ Hardcoded 컬러가 $TOTAL_HARDCODED 개나 있습니다.${NC}" | tee -a "$OUTPUT_FILE"
  echo "   → 통합 컬러 시스템 구축을 강력히 권장합니다." | tee -a "$OUTPUT_FILE"
fi

if [ $PHASE2_COUNT -gt 0 ]; then
  echo -e "${YELLOW}⚠ Phase2 백업 파일이 $PHASE2_COUNT 개 있습니다.${NC}" | tee -a "$OUTPUT_FILE"
  echo "   → 불필요한 백업 파일을 정리하세요." | tee -a "$OUTPUT_FILE"
fi

if [ ! -f "$CSS_DIR/color-system-unified.css" ]; then
  echo -e "${RED}⚠ 통합 컬러 시스템 파일이 없습니다.${NC}" | tee -a "$OUTPUT_FILE"
  echo "   → color-system-unified.css 파일을 생성하세요." | tee -a "$OUTPUT_FILE"
fi

echo "" | tee -a "$OUTPUT_FILE"

###############################################################################
# 완료
###############################################################################

echo -e "${GREEN}=========================================="
echo "분석 완료!"
echo "==========================================${NC}"
echo ""
echo "결과 파일: $OUTPUT_FILE"
echo ""
echo "다음 단계:"
echo "1. 통합 컬러 시스템 파일 생성 (color-system-unified.css)"
echo "2. 제품별 CSS 마이그레이션 시작"
echo "3. Phase2 백업 파일 정리"
echo ""

# 요약 출력
echo "=== 요약 ===" | tee -a "$OUTPUT_FILE"
echo "Hardcoded 컬러: $TOTAL_HARDCODED 개" | tee -a "$OUTPUT_FILE"
echo "CSS 변수 사용: $VAR_USAGE_COUNT 개" | tee -a "$OUTPUT_FILE"
echo "CSS 파일 개수: $CSS_FILE_COUNT 개" | tee -a "$OUTPUT_FILE"
echo "총 CSS 크기: $TOTAL_CSS_SIZE" | tee -a "$OUTPUT_FILE"
echo "" | tee -a "$OUTPUT_FILE"

echo "분석 완료: $(date)" | tee -a "$OUTPUT_FILE"
