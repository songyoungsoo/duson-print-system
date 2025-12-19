#!/bin/bash
###############################################################################
# MlangPrintAuto 파일 업로드 API 테스트
# 9개 품목의 add_to_basket.php 엔드포인트 테스트
###############################################################################

# 색상 코드
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 카운터
TOTAL=0
PASSED=0
FAILED=0
SKIPPED=0

# 테스트 이미지 생성 (1x1 PNG)
TEST_FILE="/tmp/test-upload.png"
echo -n "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==" | base64 -d > "$TEST_FILE"

echo "🚀 MlangPrintAuto API 업로드 테스트 시작"
echo "========================================================"
echo ""

# 품목 배열 정의
declare -A PRODUCTS
PRODUCTS["inserted"]="전단지"
PRODUCTS["sticker_new"]="스티커"
PRODUCTS["envelope"]="봉투"
PRODUCTS["littleprint"]="소량인쇄물"
PRODUCTS["cadarok"]="카다록"
PRODUCTS["namecard"]="명함"
PRODUCTS["msticker"]="자석스티커"
PRODUCTS["ncrflambeau"]="양식지"

# 테스트 함수
test_product() {
    local product_id=$1
    local product_name=$2

    echo "🧪 [$product_name ($product_id)] 테스트 시작..."

    TOTAL=$((TOTAL + 1))

    local url="http://localhost/mlangprintauto/${product_id}/add_to_basket.php"

    # API 엔드포인트 존재 확인
    if [ ! -f "/var/www/html/mlangprintauto/${product_id}/add_to_basket.php" ]; then
        echo -e "   ${YELLOW}⏭️  add_to_basket.php 파일 없음 - 스킵${NC}"
        SKIPPED=$((SKIPPED + 1))
        echo ""
        return
    fi

    # FormData 구성하여 POST 요청
    response=$(curl -s -w "\n%{http_code}" \
        -F "action=add_to_basket" \
        -F "product_type=${product_id}" \
        -F "uploaded_files[]=@${TEST_FILE}" \
        -F "quantity=100" \
        -F "st_price=50000" \
        "$url")

    # HTTP 상태 코드 추출
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')

    echo "   📡 HTTP 상태: $http_code"

    if [ "$http_code" == "200" ]; then
        # JSON 응답 파싱
        if echo "$body" | grep -q '"success":true'; then
            echo -e "   ${GREEN}✅ 테스트 통과${NC}"
            echo "   📄 응답: $(echo "$body" | head -c 200)..."
            PASSED=$((PASSED + 1))
        else
            echo -e "   ${RED}❌ 테스트 실패: success=false${NC}"
            echo "   📄 응답: $body"
            FAILED=$((FAILED + 1))
        fi
    else
        echo -e "   ${RED}❌ 테스트 실패: HTTP $http_code${NC}"
        echo "   📄 응답: $(echo "$body" | head -c 200)"
        FAILED=$((FAILED + 1))
    fi

    echo ""
}

# 모든 품목 테스트 실행
for product_id in "${!PRODUCTS[@]}"; do
    test_product "$product_id" "${PRODUCTS[$product_id]}"
done

# merchandisebond는 별도 처리 (파일 업로드 없음)
echo "🧪 [상품권 (merchandisebond)] 테스트 시작..."
TOTAL=$((TOTAL + 1))
if [ ! -f "/var/www/html/mlangprintauto/merchandisebond/add_to_basket.php" ]; then
    echo -e "   ${YELLOW}⏭️  파일 업로드 기능 없음 - 스킵${NC}"
    SKIPPED=$((SKIPPED + 1))
else
    echo -e "   ${YELLOW}⏭️  별도 구현 사용 - 스킵${NC}"
    SKIPPED=$((SKIPPED + 1))
fi
echo ""

# 결과 요약
echo "========================================================"
echo "📊 테스트 결과 요약"
echo "========================================================"
echo "총 테스트:  $TOTAL"
echo -e "${GREEN}✅ 성공:    $PASSED${NC}"
echo -e "${RED}❌ 실패:    $FAILED${NC}"
echo -e "${YELLOW}⏭️  스킵:    $SKIPPED${NC}"
echo "========================================================"
echo ""

# 실패한 경우 파일 시스템 확인
if [ $FAILED -gt 0 ]; then
    echo "🔍 최근 생성된 업로드 폴더 확인:"
    find /www/ImgFolder/_MlangPrintAuto_* -type d -mmin -5 2>/dev/null | head -10
    echo ""
fi

# 임시 파일 삭제
rm -f "$TEST_FILE"

# 종료 코드
if [ $FAILED -gt 0 ]; then
    exit 1
else
    exit 0
fi
