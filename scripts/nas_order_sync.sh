#!/bin/bash
# 프로덕션 주문 테이블 → NAS MySQL 동기화
# 새벽 3시 db_backup_to_nas.sh가 저장한 프로덕션 덤프에서 mlangorder_printauto만 추출
# cron: 0 5 * * * /var/www/html/scripts/nas_order_sync.sh

STATUS_FILE="/var/www/html/scripts/.db_sync_status.json"
PROD_DUMP="/tmp/prod_latest_dump.sql"
DELTA_FILE="/tmp/delta_orders.sql"
NAS_FTP="ftp://dsp1830.ipdisk.co.kr/HDD2/share/db_backups/delta_orders.sql"
NAS_USER="admin:1830"
IMPORT_URL="http://dsp1830.ipdisk.co.kr:8000/db_import.php?key=duson_nas_2026"
LOG="/var/www/html/scripts/db_backup.log"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG"; }

save_status() {
    local success="$1" error="${2:-}"
    cat > "$STATUS_FILE" << SEOF
{
    "last_run": "$(date -Iseconds)",
    "success": ${success},
    "dump_size": "${SIZE:-0}",
    "insert_count": "${INSERTS:-0}",
    "import_result": "${RESULT:-}",
    "error": "${error}"
}
SEOF
}

log "===== NAS 주문 동기화 시작 (프로덕션 → NAS) ====="

# 1. 새벽 3시에 저장된 프로덕션 덤프 확인
if [ ! -s "$PROD_DUMP" ]; then
    log "⚠️  프로덕션 덤프 없음 ($PROD_DUMP), 프로덕션에서 직접 다운로드..."
    UTC_DATE=$(date -u +%Y%m%d)
    TOKEN="duson_backup_${UTC_DATE}"
    UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
    curl -s -o "$PROD_DUMP" --max-time 180 \
        "https://dsp114.com/api/db_export.php?token=${TOKEN}" \
        -H "User-Agent: $UA"
    if [ ! -s "$PROD_DUMP" ]; then
        log "❌ 프로덕션 다운로드도 실패, 로컬 mysqldump로 폴백"
        mysqldump -u dsp1830 -pds701018 \
            --add-drop-table --single-transaction --set-gtid-purged=OFF \
            dsp1830 mlangorder_printauto 2>/dev/null > "$DELTA_FILE"
        if [ ! -s "$DELTA_FILE" ]; then
            log "❌ 모든 소스 실패"
            save_status false "All sources failed"
            exit 1
        fi
        log "로컬 mysqldump 폴백 사용"
        # 폴백이면 추출 단계 건너뛰기
        SKIP_EXTRACT=1
    fi
fi

# 2. 프로덕션 덤프에서 mlangorder_printauto 테이블만 추출
if [ -z "$SKIP_EXTRACT" ]; then
    log "프로덕션 덤프에서 mlangorder_printauto 추출 중..."
    sed -n '/^-- Table structure for table `mlangorder_printauto`/,/^-- Table structure for table `/p' \
        "$PROD_DUMP" > "$DELTA_FILE"

    if [ ! -s "$DELTA_FILE" ]; then
        log "❌ 테이블 추출 실패 (덤프에 mlangorder_printauto 없음)"
        rm -f "$DELTA_FILE"
        save_status false "Table extraction failed"
        exit 1
    fi
fi

SIZE=$(du -sh "$DELTA_FILE" | cut -f1)
INSERTS=$(grep -c '^INSERT' "$DELTA_FILE" 2>/dev/null || echo "?")
log "추출 완료: $SIZE (INSERT ${INSERTS}개)"

# 3. NAS에 delta 파일 업로드
curl -s -T "$DELTA_FILE" "$NAS_FTP" \
    --user "$NAS_USER" --ftp-create-dirs
if [ $? -ne 0 ]; then
    log "❌ NAS FTP 업로드 실패"
    rm -f "$DELTA_FILE"
    save_status false "NAS FTP upload failed"
    exit 1
fi
log "NAS FTP 업로드 완료 ($SIZE)"

# 4. NAS PHP import 트리거 (최대 3분 대기)
RESULT=$(curl -s --max-time 180 "$IMPORT_URL")
if echo "$RESULT" | grep -q "^OK"; then
    log "✅ NAS import 완료: $RESULT"
else
    log "❌ NAS import 오류: $RESULT"
    rm -f "$DELTA_FILE"
    save_status false "NAS import error: $RESULT"
    exit 1
fi

rm -f "$DELTA_FILE"
save_status true
log "===== NAS 주문 동기화 완료 (프로덕션 데이터) ====="
