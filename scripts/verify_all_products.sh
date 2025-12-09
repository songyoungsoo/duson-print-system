#!/bin/bash
# 모든 제품 페이지에서 공통 CSS가 올바르게 로드되는지 확인

echo "======================================================================"
echo "전체 제품 CSS 링크 검증"
echo "======================================================================"
echo ""

PRODUCTS=(
    "inserted"
    "sticker_new"
    "namecard"
    "envelope"
    "cadarok"
    "littleprint"
    "ncrflambeau"
    "merchandisebond"
    "msticker"
    "poster"
)

COMMON_CSS="upload-modal-common.css"
SUCCESS=0
FAIL=0

for product in "${PRODUCTS[@]}"; do
    echo "📦 Testing: $product"

    # index.php가 있는지 확인
    if [ ! -f "/var/www/html/mlangprintauto/$product/index.php" ]; then
        echo "   ⚠️  index.php not found"
        ((FAIL++))
        continue
    fi

    # 공통 CSS 링크가 있는지 확인
    if grep -q "$COMMON_CSS" "/var/www/html/mlangprintauto/$product/index.php"; then
        echo "   ✅ Common CSS link found"
        ((SUCCESS++))
    else
        echo "   ❌ Common CSS link NOT found"
        ((FAIL++))
    fi

    # HTTP 응답 확인 (선택적)
    HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/mlangprintauto/$product/")
    if [ "$HTTP_STATUS" = "200" ]; then
        echo "   ✅ HTTP 200 OK"
    else
        echo "   ⚠️  HTTP $HTTP_STATUS"
    fi

    echo ""
done

echo "======================================================================"
echo "검증 결과:"
echo "----------------------------------------------------------------------"
echo "  성공: $SUCCESS / ${#PRODUCTS[@]}"
echo "  실패: $FAIL / ${#PRODUCTS[@]}"
echo "======================================================================"

if [ $FAIL -eq 0 ]; then
    echo "✅ 모든 제품 검증 통과!"
    exit 0
else
    echo "⚠️  $FAIL 개 제품에서 문제 발견"
    exit 1
fi
