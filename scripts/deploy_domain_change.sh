#!/bin/bash
# =============================================
# 도메인 변경 배포 스크립트
# dsp114.co.kr/dsp1830.shop → dsp114.com
# + 이메일 SMTP 통합
# =============================================

FTP_HOST="dsp114.co.kr"
FTP_USER="dsp1830"
FTP_PASS="cH*j@yzj093BeTtc"
FTP_ROOT="/httpdocs"
LOCAL_ROOT="/var/www/html"

# 배포 대상 파일 목록
FILES=(
  "MlangPrintAutoTop.php"
  "admin/add_production_indexes.php"
  "admin/member/MaillingJoinAdminInfo.php"
  "bottom.htm"
  "chat/admin.php"
  "chat/chat.js"
  "config.env.php"
  "db.php"
  "footer.php"
  "includes/EmailNotification.php"
  "includes/ImagePathResolver.php"
  "includes/OrderNotificationManager.php"
  "includes/OrderStatusManager.php"
  "includes/footer.php"
  "includes/product_schema.php"
  "includes/schema_markup.php"
  "includes/services/NotificationService.php"
  "includes/upload_path_manager.php"
  "index.php"
  "mlangorder_printauto/OrderFormPrint.php"
  "mlangorder_printauto/OrderFormPrintExcel.php"
  "mlangorder_printauto/OrderResult.php"
  "mlangorder_printauto/mail_send.php"
  "mlangprintauto/cadarok/index.php"
  "mlangprintauto/envelope/index.php"
  "mlangprintauto/inserted/index.php"
  "mlangprintauto/littleprint/index.php"
  "mlangprintauto/merchandisebond/index.php"
  "mlangprintauto/msticker/index.php"
  "mlangprintauto/namecard/index.php"
  "mlangprintauto/ncrflambeau/index.php"
  "mlangprintauto/quote/diagnostic.php"
  "mlangprintauto/quote/standard/mail.php"
  "mlangprintauto/shop/quotation_respond.php"
  "mlangprintauto/sticker_new/index.php"
  "payment/config.php"
  "payment/inicis_request.php"
  "payment/request.php"
  "payment/return.php"
  "robots.txt"
  "session/db.php"
  "setup_admin.php"
  "shop/db.php"
  "shop_admin/left.php"
  "shop_admin/left01.html"
  "shop_admin/left01.php"
  "sub/db.php"
  "template_generator.php"
  "tools/logen/logen_auto.js"
  "v2/config/app.php"
  "v2/src/Controllers/OrderController.php"
  "v2/templates/components/chat-widget.php"
)

TOTAL=${#FILES[@]}
SUCCESS=0
FAIL=0
FAILED_FILES=()

echo "========================================"
echo " 도메인 변경 배포 시작"
echo " 대상: ${TOTAL}개 파일 → ${FTP_HOST}"
echo "========================================"
echo ""

for i in "${!FILES[@]}"; do
  FILE="${FILES[$i]}"
  NUM=$((i + 1))
  
  printf "[%2d/%d] %-60s " "$NUM" "$TOTAL" "$FILE"
  
  # 로컬 파일 존재 확인
  if [ ! -f "${LOCAL_ROOT}/${FILE}" ]; then
    echo "⚠️  SKIP (로컬 파일 없음)"
    continue
  fi
  
  # FTP 업로드
  RESULT=$(curl -s -T "${LOCAL_ROOT}/${FILE}" \
    "ftp://${FTP_HOST}${FTP_ROOT}/${FILE}" \
    --user "${FTP_USER}:${FTP_PASS}" \
    --connect-timeout 10 \
    --max-time 30 \
    -w "%{http_code}" \
    -o /dev/null 2>&1)
  
  if [ $? -eq 0 ]; then
    echo "✅"
    SUCCESS=$((SUCCESS + 1))
  else
    echo "❌ FAIL"
    FAIL=$((FAIL + 1))
    FAILED_FILES+=("$FILE")
  fi
done

echo ""
echo "========================================"
echo " 배포 완료"
echo " 성공: ${SUCCESS}/${TOTAL}"
echo " 실패: ${FAIL}/${TOTAL}"
echo "========================================"

if [ ${FAIL} -gt 0 ]; then
  echo ""
  echo "❌ 실패한 파일:"
  for F in "${FAILED_FILES[@]}"; do
    echo "  - ${F}"
  done
  echo ""
  echo "실패한 파일은 수동으로 재시도하세요:"
  echo "  curl -T /var/www/html/<파일> ftp://${FTP_HOST}${FTP_ROOT}/<파일> --user \"${FTP_USER}:${FTP_PASS}\""
fi

echo ""
echo "🔍 배포 후 확인:"
echo "  1. https://dsp114.com/ 접속 → canonical이 dsp114.com인지 확인"
echo "  2. 브라우저 캐시 제거 (Ctrl+Shift+R)"
echo "  3. 페이지 소스보기에서 og:url이 dsp114.com인지 확인"
