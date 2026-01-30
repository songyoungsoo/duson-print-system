#!/bin/bash

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

FTP_HOST="dsp114.co.kr"
FTP_USER="dsp1830"
FTP_PASS="cH*j@yzj093BeTtc"

LOCAL_ROOT="/var/www/html"
REMOTE_ROOT="/httpdocs"
echo ""
echo "============================================================================"
echo -e "${CYAN}📋 로컬-운영 경로 검증 시스템${NC}"
echo "============================================================================"
echo ""
echo -e "${BLUE}로컬 루트:${NC}  $LOCAL_ROOT"
echo -e "${BLUE}운영 루트:${NC}  $REMOTE_ROOT (FTP 기준)"
echo -e "${BLUE}웹 URL:${NC}    https://$FTP_HOST/"
echo ""

check_local_file() {
    local file=$1
    local full_path="$LOCAL_ROOT/$file"
    
    if [ -f "$full_path" ]; then
        local size=$(stat -f%z "$full_path" 2>/dev/null || stat -c%s "$full_path" 2>/dev/null)
        echo -e "${GREEN}✅${NC} 로컬: $file ($(numfmt --to=iec-i --suffix=B $size 2>/dev/null || echo "${size}B"))"
        return 0
    else
        echo -e "${RED}❌${NC} 로컬: $file (파일 없음)"
        return 1
    fi
}

check_remote_file() {
    local file=$1
    local remote_path="$REMOTE_ROOT/$file"
    
    local result=$(curl -s --head "ftp://$FTP_HOST$remote_path" \
        --user "$FTP_USER:$FTP_PASS" 2>/dev/null | grep -i "Content-Length" | awk '{print $2}' | tr -d '\r')
    
    if [ -n "$result" ] && [ "$result" -gt 0 ]; then
        echo -e "${GREEN}✅${NC} 운영: $remote_path ($(numfmt --to=iec-i --suffix=B $result 2>/dev/null || echo "${result}B"))"
        return 0
    else
        echo -e "${YELLOW}⚠️${NC}  운영: $remote_path (파일 없음 또는 접근 불가)"
        return 1
    fi
}

compare_files() {
    local file=$1
    local local_path="$LOCAL_ROOT/$file"
    local remote_path="$REMOTE_ROOT/$file"
    
    if [ ! -f "$local_path" ]; then
        echo -e "${RED}❌${NC} $file - 로컬 파일 없음"
        return 1
    fi
    
    local local_size=$(stat -f%z "$local_path" 2>/dev/null || stat -c%s "$local_path" 2>/dev/null)
    local remote_size=$(curl -s --head "ftp://$FTP_HOST$remote_path" \
        --user "$FTP_USER:$FTP_PASS" 2>/dev/null | grep -i "Content-Length" | awk '{print $2}' | tr -d '\r')
    
    echo ""
    echo -e "${CYAN}📄 $file${NC}"
    echo "   로컬: $local_path"
    echo "   운영: ftp://$FTP_HOST$remote_path"
    
    check_local_file "$file"
    check_remote_file "$file"
    
    if [ -n "$remote_size" ] && [ "$remote_size" -gt 0 ]; then
        if [ "$local_size" -eq "$remote_size" ]; then
            echo -e "   ${GREEN}✅ 파일 크기 동일${NC} ($local_size bytes)"
        else
            echo -e "   ${YELLOW}⚠️  파일 크기 차이${NC}"
            echo -e "      로컬: $local_size bytes"
            echo -e "      운영: $remote_size bytes"
            echo -e "      차이: $((local_size - remote_size)) bytes"
        fi
    fi
}

echo "============================================================================"
echo -e "${CYAN}📦 핵심 파일 검증${NC}"
echo "============================================================================"
echo ""

CRITICAL_FILES=(
    "payment/inicis_config.php"
    "payment/inicis_return.php"
    "payment/inicis_request.php"
    "payment/inicis_close.php"
    "includes/auth.php"
    "includes/AdditionalOptionsDisplay.php"
    "includes/QuantityFormatter.php"
    "mlangorder_printauto/OnlineOrder_unified.php"
    "mlangorder_printauto/ProcessOrder_unified.php"
    "mlangorder_printauto/OrderComplete_universal.php"
)

for file in "${CRITICAL_FILES[@]}"; do
    compare_files "$file"
done

echo ""
echo "============================================================================"
echo -e "${CYAN}📚 경로 매핑 가이드${NC}"
echo "============================================================================"
echo ""

echo -e "${GREEN}✅ 올바른 매핑:${NC}"
echo ""
printf "%-40s → %-40s\n" "로컬 경로" "운영 FTP 경로"
echo "$(printf '%.0s-' {1..85})"
printf "%-40s → %-40s\n" "$LOCAL_ROOT/index.php" "$REMOTE_ROOT/index.php"
printf "%-40s → %-40s\n" "$LOCAL_ROOT/payment/inicis_return.php" "$REMOTE_ROOT/payment/inicis_return.php"
printf "%-40s → %-40s\n" "$LOCAL_ROOT/includes/auth.php" "$REMOTE_ROOT/includes/auth.php"

echo ""
echo -e "${RED}❌ 잘못된 경로 (절대 사용 금지):${NC}"
echo ""
echo "   /public_html/            ← 웹 루트 아님!"
echo "   /payment/                ← FTP 루트에 직접 업로드 금지!"
echo "   /                        ← FTP 루트 ≠ 웹 루트"

echo ""
echo "============================================================================"
echo -e "${CYAN}🚀 배포 명령어 예시${NC}"
echo "============================================================================"
echo ""

cat << 'EOF'
curl -T /var/www/html/payment/inicis_return.php \
  ftp://dsp114.co.kr/httpdocs/payment/inicis_return.php \
  --user "dsp1830:cH*j@yzj093BeTtc"

curl -s --head ftp://dsp114.co.kr/httpdocs/payment/inicis_return.php \
  --user "dsp1830:cH*j@yzj093BeTtc" | grep "Content-Length"

curl -I https://dsp114.co.kr/payment/inicis_request.php
EOF

echo ""
echo "============================================================================"
echo -e "${CYAN}✅ 검증 완료${NC}"
echo "============================================================================"
echo ""
