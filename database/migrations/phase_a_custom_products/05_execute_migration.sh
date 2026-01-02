#!/bin/bash
###############################################################################
# Phase A: 마이그레이션 실행 스크립트
# 작성일: 2025-12-26
# 용도: 백업 → 스키마 변경 → 검증 자동화
###############################################################################

set -e  # 에러 발생 시 즉시 종료

# 설정
DB_NAME="dsp1830"
DB_USER="dsp1830"
DB_PASS="ds701018"
SCRIPT_DIR="/var/www/html/database/migrations/phase_a_custom_products"

# 색상 출력
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "========================================="
echo "Phase A 마이그레이션 실행"
echo "========================================="
echo ""

# ============================================================================
# Step 1: 백업 실행
# ============================================================================
echo -e "${YELLOW}Step 1/5: DB 백업${NC}"
echo "----------------------------------------"

bash "$SCRIPT_DIR/01_backup.sh"

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ 백업 실패! 마이그레이션을 중단합니다.${NC}"
    exit 1
fi

echo -e "${GREEN}✅ 백업 완료${NC}"
echo ""

# 사용자 확인
read -p "백업이 완료되었습니다. 마이그레이션을 계속하시겠습니까? (y/N): " confirm
if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
    echo "마이그레이션을 취소했습니다."
    exit 0
fi
echo ""

# ============================================================================
# Step 2: mlangorder_printauto 테이블 수정
# ============================================================================
echo -e "${YELLOW}Step 2/5: mlangorder_printauto 테이블 수정${NC}"
echo "----------------------------------------"

mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    < "$SCRIPT_DIR/02_alter_mlangorder_printauto.sql"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ mlangorder_printauto 테이블 수정 완료${NC}"
else
    echo -e "${RED}❌ mlangorder_printauto 테이블 수정 실패${NC}"
    echo "백업에서 복원하려면 다음 명령을 실행하세요:"
    echo "bash $SCRIPT_DIR/06_restore_from_backup.sh"
    exit 1
fi
echo ""

# ============================================================================
# Step 3: quotes/quote_items 테이블 수정
# ============================================================================
echo -e "${YELLOW}Step 3/5: quotes/quote_items 테이블 수정${NC}"
echo "----------------------------------------"

mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    < "$SCRIPT_DIR/03_alter_quotes_quote_items.sql"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ quotes/quote_items 테이블 수정 완료${NC}"
else
    echo -e "${RED}❌ quotes/quote_items 테이블 수정 실패${NC}"
    echo "백업에서 복원하려면 다음 명령을 실행하세요:"
    echo "bash $SCRIPT_DIR/06_restore_from_backup.sh"
    exit 1
fi
echo ""

# ============================================================================
# Step 4: 트랜잭션 COMMIT
# ============================================================================
echo -e "${YELLOW}Step 4/5: 트랜잭션 COMMIT${NC}"
echo "----------------------------------------"

mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -e "COMMIT;"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ 트랜잭션 커밋 완료${NC}"
else
    echo -e "${RED}❌ 트랜잭션 커밋 실패${NC}"
    exit 1
fi
echo ""

# ============================================================================
# Step 5: 최종 검증
# ============================================================================
echo -e "${YELLOW}Step 5/5: 최종 검증${NC}"
echo "----------------------------------------"

# mlangorder_printauto ThingCate 확인
echo "mlangorder_printauto ThingCate ENUM 값:"
mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -e "SHOW COLUMNS FROM mlangorder_printauto LIKE 'ThingCate';"

# 신규 필드 확인
echo ""
echo "신규 필드 확인:"
mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -e "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA='$DB_NAME'
        AND TABLE_NAME='mlangorder_printauto'
        AND COLUMN_NAME IN ('is_custom_product', 'custom_product_name', 'custom_specification');"

echo ""
echo "quotes.quote_source 필드:"
mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -e "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA='$DB_NAME'
        AND TABLE_NAME='quotes'
        AND COLUMN_NAME='quote_source';"

echo ""
echo "quote_items.is_manual_entry 필드:"
mysql -h localhost -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -e "SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA='$DB_NAME'
        AND TABLE_NAME='quote_items'
        AND COLUMN_NAME='is_manual_entry';"

echo ""
echo -e "${GREEN}✅ 최종 검증 완료${NC}"
echo ""

# ============================================================================
# 완료 메시지
# ============================================================================
echo "========================================="
echo -e "${GREEN}🎉 Phase A 마이그레이션 완료!${NC}"
echo "========================================="
echo ""
echo "변경 사항:"
echo "  1. mlangorder_printauto:"
echo "     - ThingCate ENUM에 'custom' 추가"
echo "     - is_custom_product, custom_product_name, custom_specification 필드 추가"
echo "     - 인덱스 2개 추가"
echo ""
echo "  2. quotes:"
echo "     - quote_source 필드 추가"
echo "     - 인덱스 2개 추가"
echo ""
echo "  3. quote_items:"
echo "     - is_manual_entry 필드 추가"
echo "     - 인덱스 2개 추가"
echo ""
echo "다음 단계:"
echo "  Phase B: 주문 전환 로직 개선"
echo "  Phase C: 관리자 견적 생성 UI"
echo ""
echo "롤백이 필요한 경우:"
echo "  bash $SCRIPT_DIR/06_restore_from_backup.sh"
echo ""

exit 0
