#!/bin/bash
# 주문내역 → NAS 일별 CSV 저장
# cron: 0 4 * * * /var/www/html/scripts/order_export_to_nas.sh

DATE=$(date +%Y%m%d)
YESTERDAY=$(date -d '-1 day' +%Y-%m-%d)
TODAY=$(date +%Y-%m-%d)
TMP_FILE="/tmp/orders_${DATE}.csv"
NAS_DIR="ftp://dsp1830.ipdisk.co.kr/HDD2/share/order_exports"
NAS_USER="admin:1830"
LOG="/var/www/html/scripts/db_backup.log"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG"; }

log "===== 주문내역 NAS 저장 시작 ($YESTERDAY ~ $TODAY) ====="

# CSV 헤더 + 데이터 생성
mysql -u dsp1830 -pds701018 dsp1830 2>/dev/null << SQLEOF > "$TMP_FILE"
SELECT
  '주문번호','주문일시','품목','규격','수량','공급가','부가세','합계',
  '주문자명','연락처','이메일','배송지','사업자명','입금은행','입금자',
  '주문상태','결제방식','운송장번호','배송완료일'
UNION ALL
SELECT
  no,
  date,
  IFNULL(product_type, Type),
  CONCAT_WS(' / ', spec_material, spec_size, spec_sides, spec_design),
  IFNULL(quantity_display, CONCAT(mesu, '매')),
  IFNULL(price_supply, money_1),
  IFNULL(price_vat_amount, ''),
  money_1,
  name,
  IFNULL(Hendphone, phone),
  email,
  CONCAT_WS(' ', zip1, zip2, delivery),
  IFNULL(bizname, ''),
  bankname,
  bank,
  OrderStyle,
  cont,
  IFNULL(waybill_no, IFNULL(logen_tracking_no, '')),
  IFNULL(waybill_date, '')
FROM mlangorder_printauto
WHERE DATE(date) >= '$YESTERDAY'
ORDER BY 1 DESC;
SQLEOF

ROWS=$(wc -l < "$TMP_FILE")
if [ "$ROWS" -le 1 ]; then
    log "ℹ️  신규 주문 없음 (${YESTERDAY})"
    rm -f "$TMP_FILE"
    exit 0
fi

log "주문 $((ROWS-1))건 추출 완료"

# 전체 누적 파일도 생성 (월별)
MONTH=$(date +%Y%m)
MONTHLY_FILE="/tmp/orders_${MONTH}_all.csv"

mysql -u dsp1830 -pds701018 dsp1830 2>/dev/null << SQLEOF > "$MONTHLY_FILE"
SELECT
  '주문번호','주문일시','품목','규격','수량','공급가','합계',
  '주문자명','연락처','주문상태','운송장번호'
UNION ALL
SELECT
  no, date,
  IFNULL(product_type, Type),
  CONCAT_WS(' / ', spec_material, spec_size, spec_sides),
  IFNULL(quantity_display, CONCAT(mesu, '매')),
  IFNULL(price_supply, money_1),
  money_1,
  name,
  IFNULL(Hendphone, phone),
  OrderStyle,
  IFNULL(waybill_no, IFNULL(logen_tracking_no, ''))
FROM mlangorder_printauto
WHERE DATE_FORMAT(date, '%Y%m') = '$MONTH'
ORDER BY 1 DESC;
SQLEOF

MONTHLY_ROWS=$(wc -l < "$MONTHLY_FILE")
log "${MONTH} 월 전체 누적: $((MONTHLY_ROWS-1))건"

# NAS 업로드
log "NAS 업로드 중..."

# 일별 파일
curl -s -T "$TMP_FILE" "${NAS_DIR}/daily/orders_${DATE}.csv" \
  --user "$NAS_USER" --ftp-create-dirs && log "✅ 일별: orders_${DATE}.csv ($((ROWS-1))건)"

# 월별 누적 파일 (덮어쓰기)
curl -s -T "$MONTHLY_FILE" "${NAS_DIR}/monthly/orders_${MONTH}.csv" \
  --user "$NAS_USER" --ftp-create-dirs && log "✅ 월별: orders_${MONTH}.csv ($((MONTHLY_ROWS-1))건)"

rm -f "$TMP_FILE" "$MONTHLY_FILE"

# 90일 이전 일별 파일 삭제
OLD_DATE=$(date -d "-90 days" +%Y%m%d)
curl -s "${NAS_DIR}/daily/" --user "$NAS_USER" -l 2>/dev/null | while read fname; do
    fdate=$(echo "$fname" | grep -oP '\d{8}')
    if [ -n "$fdate" ] && [ "$fdate" -lt "$OLD_DATE" ]; then
        curl -s -Q "DELE /HDD2/share/order_exports/daily/$fname" \
          "ftp://dsp1830.ipdisk.co.kr" --user "$NAS_USER" > /dev/null
        log "  🗑️ 삭제: $fname"
    fi
done

log "===== 주문내역 저장 완료 ====="
