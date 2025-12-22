#!/bin/bash
# 프로덕션 DB 스키마 가져오기 스크립트

echo "🔍 프로덕션 데이터베이스 스키마 가져오기 시작..."

# 프로덕션 DB 정보
PROD_HOST="dsp1830.shop"
PROD_USER="dsp1830"
PROD_PASS="ds701018"
PROD_DB="dsp1830"

# 출력 파일
OUTPUT_FILE="/var/www/html/claudedocs/production_schema_dump.sql"

echo "📊 테이블 구조만 가져옵니다 (데이터 제외)..."

# mysqldump로 스키마만 가져오기
mysqldump -h "$PROD_HOST" -u "$PROD_USER" -p"$PROD_PASS" \
  --no-data \
  --skip-add-drop-table \
  --skip-comments \
  "$PROD_DB" \
  mlangprintauto_inserted \
  mlangprintauto_envelope \
  mlangprintauto_namecard \
  mlangprintauto_sticker \
  mlangprintauto_msticker \
  mlangprintauto_cadarok \
  mlangprintauto_littleprint \
  mlangprintauto_merchandisebond \
  mlangprintauto_ncrflambeau \
  mlangorder_printauto \
  users \
  mlangprintauto_transactioncate \
  shop_temp \
  shop_order \
  > "$OUTPUT_FILE" 2>&1

if [ $? -eq 0 ]; then
    echo "✅ 프로덕션 스키마 덤프 완료: $OUTPUT_FILE"
    ls -lh "$OUTPUT_FILE"
else
    echo "❌ 프로덕션 스키마 가져오기 실패"
    cat "$OUTPUT_FILE"
    exit 1
fi
