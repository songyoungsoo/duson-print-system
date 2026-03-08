#!/bin/bash
# 주문 테이블 delta → NAS MySQL 동기화
# cron: 0 5 * * * /var/www/html/scripts/nas_order_sync.sh

DELTA_FILE="/tmp/delta_orders.sql"
NAS_FTP="ftp://dsp1830.ipdisk.co.kr/HDD2/share/db_backups/delta_orders.sql"
NAS_USER="admin:1830"
IMPORT_URL="http://dsp1830.ipdisk.co.kr:8000/db_import.php?key=duson_nas_2026"
LOG="/var/www/html/scripts/db_backup.log"
DAYS=60  # 최근 60일 동기화

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG"; }

log "===== NAS 주문 동기화 시작 (최근 ${DAYS}일) ====="

SINCE=$(date -d "-${DAYS} days" +%Y-%m-%d)

# 최근 주문 ID 범위 조회
MIN_NO=$(mysql -u dsp1830 -pds701018 dsp1830 2>/dev/null -sNe \
  "SELECT IFNULL(MIN(no),0) FROM mlangorder_printauto WHERE DATE(date) >= '${SINCE}';")

if [ -z "$MIN_NO" ] || [ "$MIN_NO" -eq 0 ]; then
    log "동기화 대상 없음 (${SINCE} 이후)"
    exit 0
fi

log "동기화 범위: no >= $MIN_NO (${SINCE} 이후)"

# mysqldump: 스키마 포함 (NAS 테이블 재생성) + 최근 데이터만
# --add-drop-table: DROP TABLE IF EXISTS 포함 → 스키마 자동 업데이트
mysqldump -u dsp1830 -pds701018 \
    --add-drop-table \
    --single-transaction \
    --where="no >= ${MIN_NO}" \
    --set-gtid-purged=OFF \
    dsp1830 mlangorder_printauto 2>/dev/null > "$DELTA_FILE"

if [ $? -ne 0 ] || [ ! -s "$DELTA_FILE" ]; then
    log "mysqldump 실패 또는 빈 파일"
    rm -f "$DELTA_FILE"
    exit 1
fi

SIZE=$(du -sh "$DELTA_FILE" | cut -f1)
log "delta SQL 생성: $SIZE"

# NAS에 delta 파일 업로드
curl -s -T "$DELTA_FILE" "$NAS_FTP" \
    --user "$NAS_USER" --ftp-create-dirs
if [ $? -ne 0 ]; then
    log "NAS FTP 업로드 실패"
    rm -f "$DELTA_FILE"
    exit 1
fi
log "NAS FTP 업로드 완료 ($SIZE)"

# NAS PHP import 트리거 (최대 2분 대기)
RESULT=$(curl -s --max-time 120 "$IMPORT_URL")
if echo "$RESULT" | grep -q "^OK"; then
    log "NAS import 완료: $RESULT"
else
    log "NAS import 오류: $RESULT"
    rm -f "$DELTA_FILE"
    exit 1
fi

rm -f "$DELTA_FILE"
log "===== NAS 주문 동기화 완료 ====="
