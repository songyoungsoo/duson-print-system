#!/bin/bash

# FTP 정보
FTP_USER="dsp1830"
FTP_PASS="ds701018"
FTP_HOST="dsp1830.shop"
FTP_BASE="/public_html"

# 업로드 함수
upload_file() {
    local_file=$1
    remote_path=$2

    echo "📤 업로드: $local_file -> $remote_path"

    curl --upload-file "$local_file" \
         --user "${FTP_USER}:${FTP_PASS}" \
         --ftp-create-dirs \
         "ftp://${FTP_HOST}${FTP_BASE}/${remote_path}"

    if [ $? -eq 0 ]; then
        echo "   ✅ 완료"
    else
        echo "   ❌ 실패"
    fi
}

echo "=== 프로덕션 FTP 업로드 시작 ==="
echo ""

# 포스터 파일 업로드
upload_file "/var/www/html/mlangprintauto/littleprint/index.php" "mlangprintauto/littleprint/index.php"
upload_file "/var/www/html/mlangprintauto/littleprint/calculate_price_ajax.php" "mlangprintauto/littleprint/calculate_price_ajax.php"
upload_file "/var/www/html/mlangprintauto/littleprint/add_to_basket.php" "mlangprintauto/littleprint/add_to_basket.php"
upload_file "/var/www/html/mlangprintauto/littleprint/calculator.js" "mlangprintauto/littleprint/calculator.js"
upload_file "/var/www/html/mlangprintauto/littleprint/js/littleprint-premium-options.js" "mlangprintauto/littleprint/js/littleprint-premium-options.js"

# 주문 처리
upload_file "/var/www/html/mlangorder_printauto/OrderComplete_universal.php" "mlangorder_printauto/OrderComplete_universal.php"
upload_file "/var/www/html/mlangorder_printauto/OnlineOrder_unified.php" "mlangorder_printauto/OnlineOrder_unified.php"

# 공통 파일
upload_file "/var/www/html/includes/AdditionalOptionsDisplay.php" "includes/AdditionalOptionsDisplay.php"

# 관리자
upload_file "/var/www/html/admin/MlangPrintAuto/admin.php" "admin/MlangPrintAuto/admin.php"

# CSS
upload_file "/var/www/html/css/common-styles.css" "css/common-styles.css"
upload_file "/var/www/html/css/product-layout.css" "css/product-layout.css"

echo ""
echo "=== 업로드 완료 ==="
