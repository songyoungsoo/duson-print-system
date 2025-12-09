#!/bin/bash

# ============================================================================
# 프로덕션 배포 스크립트
# 2025-10-10 - 포스터 추가 옵션 시스템 배포
# ============================================================================

# FTP 접속 정보 (환경 변수 또는 직접 입력)
FTP_HOST="${FTP_HOST:-dsp1830.shop}"
FTP_USER="${FTP_USER:-dsp1830}"
FTP_PASS="${FTP_PASS:-ds701018}"
FTP_REMOTE_DIR="${FTP_REMOTE_DIR:-/public_html}"

# 로컬 경로
LOCAL_ROOT="/var/www/html"

# 색상 출력
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "============================================================================"
echo "프로덕션 배포 시작"
echo "============================================================================"
echo ""

# FTP 연결 테스트
echo "🔍 FTP 연결 테스트..."
ftp -inv $FTP_HOST <<EOF
user $FTP_USER $FTP_PASS
bye
EOF

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ FTP 연결 실패${NC}"
    exit 1
fi

echo -e "${GREEN}✅ FTP 연결 성공${NC}"
echo ""

# 배포 확인
echo -e "${YELLOW}⚠️  경고: 프로덕션 서버에 파일을 배포합니다.${NC}"
echo "계속하시겠습니까? (yes/no)"
read -r CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "배포가 취소되었습니다."
    exit 0
fi

echo ""
echo "============================================================================"
echo "파일 업로드 시작"
echo "============================================================================"

# FTP 업로드 함수
upload_file() {
    local file=$1
    local remote_path=$2

    echo "📤 업로드: $file -> $remote_path"

    ftp -inv $FTP_HOST <<EOF
user $FTP_USER $FTP_PASS
binary
cd $FTP_REMOTE_DIR
mkdir -p $(dirname $remote_path)
cd $(dirname $remote_path)
put $LOCAL_ROOT/$file $(basename $file)
bye
EOF

    if [ $? -eq 0 ]; then
        echo -e "${GREEN}   ✅ 완료${NC}"
    else
        echo -e "${RED}   ❌ 실패${NC}"
    fi
}

# 디렉토리 업로드 함수
upload_directory() {
    local dir=$1

    echo "📁 디렉토리 업로드: $dir"

    # lftp 사용 (설치 필요: sudo apt-get install lftp)
    if command -v lftp &> /dev/null; then
        lftp -c "
        set ftp:ssl-allow no;
        open -u $FTP_USER,$FTP_PASS $FTP_HOST;
        mirror -R $LOCAL_ROOT/$dir $FTP_REMOTE_DIR/$dir;
        bye
        "
    else
        echo -e "${YELLOW}   ⚠️  lftp가 설치되지 않았습니다. 개별 파일 업로드를 사용하세요.${NC}"
    fi
}

# ============================================================================
# 개별 파일 업로드
# ============================================================================

echo ""
echo "📦 핵심 파일 업로드..."

# 포스터 파일
upload_file "mlangprintauto/littleprint/index.php" "mlangprintauto/littleprint/index.php"
upload_file "mlangprintauto/littleprint/calculate_price_ajax.php" "mlangprintauto/littleprint/calculate_price_ajax.php"
upload_file "mlangprintauto/littleprint/add_to_basket.php" "mlangprintauto/littleprint/add_to_basket.php"
upload_file "mlangprintauto/littleprint/calculator.js" "mlangprintauto/littleprint/calculator.js"

# 주문 처리
upload_file "mlangorder_printauto/OrderComplete_universal.php" "mlangorder_printauto/OrderComplete_universal.php"
upload_file "mlangorder_printauto/OnlineOrder_unified.php" "mlangorder_printauto/OnlineOrder_unified.php"

# 공통 컴포넌트
upload_file "includes/AdditionalOptionsDisplay.php" "includes/AdditionalOptionsDisplay.php"

# 관리자
upload_file "admin/MlangPrintAuto/admin.php" "admin/MlangPrintAuto/admin.php"

# CSS
upload_file "css/common-styles.css" "css/common-styles.css"
upload_file "css/product-layout.css" "css/product-layout.css"

echo ""
echo "============================================================================"
echo "배포 완료"
echo "============================================================================"
echo ""
echo -e "${GREEN}✅ 파일 업로드 완료${NC}"
echo ""
echo "📌 다음 단계:"
echo "1. SSH 접속하여 심볼릭 링크 생성:"
echo "   cd /home/$FTP_USER/public_html/admin"
echo "   ln -s MlangPrintAuto mlangprintauto"
echo ""
echo "2. 프로덕션 사이트 테스트:"
echo "   https://dsp1830.shop/mlangprintauto/littleprint/"
echo ""
echo "3. 관리자 페이지 테스트:"
echo "   https://dsp1830.shop/admin/MlangPrintAuto/admin.php"
echo ""
