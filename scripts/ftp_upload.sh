#!/bin/bash

# FTP 정보
HOST="dsp1830.shop"
USER="dsp1830"
PASS="ds701018"

echo "=== 프로덕션 FTP 업로드 시작 ==="
echo ""

# FTP 스크립트 생성
cat > /tmp/ftp_commands.txt << 'FTPEOF'
cd public_html

# 포스터 파일 업로드
cd mlangprintauto/littleprint
lcd /var/www/html/mlangprintauto/littleprint
binary
put index.php
put calculate_price_ajax.php
put add_to_basket.php
put calculator.js

# JS 디렉토리
cd js
lcd js
put littleprint-premium-options.js
cd ..
lcd ..

# 주문 처리 파일
cd ../../mlangorder_printauto
lcd /var/www/html/mlangorder_printauto
put OrderComplete_universal.php
put OnlineOrder_unified.php

# 공통 파일
cd ../includes
lcd /var/www/html/includes
put AdditionalOptionsDisplay.php

# 관리자 파일
cd ../admin/MlangPrintAuto
lcd /var/www/html/admin/MlangPrintAuto
put admin.php

# CSS 파일
cd ../../css
lcd /var/www/html/css
put common-styles.css
put product-layout.css

bye
FTPEOF

# FTP 실행
echo "📤 FTP 업로드 중..."
ftp -inv $HOST < /tmp/ftp_commands.txt <<EOF
user $USER $PASS
$(cat /tmp/ftp_commands.txt)
EOF

if [ $? -eq 0 ]; then
    echo "✅ 업로드 완료!"
else
    echo "❌ 업로드 실패"
    exit 1
fi

# 임시 파일 삭제
rm /tmp/ftp_commands.txt

echo ""
echo "=== 배포 완료 ==="
echo ""
echo "📌 SSH 접속하여 심볼릭 링크 생성:"
echo "ssh $USER@$HOST"
echo "cd public_html/admin"
echo "ln -s MlangPrintAuto mlangprintauto"
