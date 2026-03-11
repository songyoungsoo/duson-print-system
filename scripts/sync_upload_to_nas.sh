#!/bin/bash
# 교정이미지(upload/) 동기화: 프로덕션 → 로컬 → NAS
# cron: 30 5 * * * /var/www/html/scripts/sync_upload_to_nas.sh
#
# 흐름:
#   1) 프로덕션(dsp114.com) FTP에서 새 교정파일 다운로드 → 로컬
#   2) 로컬에서 NAS(dsp1830) FTP로 업로드
#   모두 --only-newer 로 변경분만 처리 (첫 실행만 전체 전송)
#
# 사용법:
#   ./sync_upload_to_nas.sh              # 전체 (프로덕션→로컬→NAS)
#   ./sync_upload_to_nas.sh --nas-only   # 로컬→NAS만 (프로덕션 건너뜀)
#   ./sync_upload_to_nas.sh --dry-run    # 미리보기 (실제 전송 없음)
#   ./sync_upload_to_nas.sh --dry-run --nas-only  # NAS 미리보기만

set -euo pipefail

LOG="/var/www/html/scripts/db_backup.log"
STATUS_FILE="/var/www/html/scripts/.upload_sync_status.json"
LFTP_LOG="/tmp/upload_sync_lftp.log"

# 프로덕션 FTP (교정파일 원본)
PROD_HOST="dsp114.com"
PROD_USER="dsp1830"
PROD_PASS='cH*j@yzj093BeTtc'
PROD_UPLOAD="/httpdocs/mlangorder_printauto/upload"

# NAS FTP (dsp1830 - checkboard.php 서버)
NAS_HOST="dsp1830.ipdisk.co.kr"
NAS_USER="admin"
NAS_PASS="1830"
NAS_UPLOAD="/HDD2/share/mlangorder_printauto/upload"

# 로컬 경로
LOCAL_UPLOAD="/var/www/html/mlangorder_printauto/upload"

# 옵션 기본값
DRY_RUN=""
NAS_ONLY=false

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] [UPLOAD] $1" | tee -a "$LOG"; }

save_status() {
    local success="$1"
    local error_msg="${2:-}"
    local end_time=$(date +%s)
    local elapsed=$((end_time - START_TIME))
    local local_files=$(find "$LOCAL_UPLOAD" -type f 2>/dev/null | wc -l)
    local local_size=$(du -sh "$LOCAL_UPLOAD" 2>/dev/null | cut -f1)

    cat > "$STATUS_FILE" << STATUSEOF
{
    "last_run": "$(date -Iseconds)",
    "elapsed_seconds": ${elapsed},
    "prod_files_transferred": ${PROD_TRANSFERRED},
    "nas_files_transferred": ${NAS_TRANSFERRED},
    "local_total_files": ${local_files},
    "local_total_size": "${local_size}",
    "dry_run": $([ -n "$DRY_RUN" ] && echo true || echo false),
    "nas_only": ${NAS_ONLY},
    "success": ${success},
    "error": "${error_msg}"
}
STATUSEOF
}

# 인수 파싱
while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)  DRY_RUN="--dry-run"; shift ;;
        --nas-only) NAS_ONLY=true; shift ;;
        --help|-h)
            echo "사용법: $0 [옵션]"
            echo "  --dry-run    미리보기 (실제 전송 없음)"
            echo "  --nas-only   로컬→NAS만 (프로덕션 다운로드 건너뜀)"
            exit 0
            ;;
        *) echo "알 수 없는 옵션: $1"; exit 1 ;;
    esac
done

START_TIME=$(date +%s)
PROD_TRANSFERRED=0
NAS_TRANSFERRED=0

log "===== 교정이미지 동기화 시작 ====="
[ -n "$DRY_RUN" ] && log "  DRY-RUN 모드 (실제 전송 없음)"
[ "$NAS_ONLY" = true ] && log "  NAS-ONLY 모드 (프로덕션 건너뜀)"

# ─── 1단계: 프로덕션 → 로컬 ───
if [ "$NAS_ONLY" = false ]; then
    log "📥 [1/2] 프로덕션(dsp114.com) → 로컬 다운로드..."

    > "$LFTP_LOG"
    lftp -c "
        set ftp:charset UTF-8
        set file:charset UTF-8
        set net:timeout 30
        set net:max-retries 3
        set net:reconnect-interval-base 5
        set ftp:ssl-allow no
        open -u ${PROD_USER},${PROD_PASS} ftp://${PROD_HOST}
        mirror --only-newer --no-perms --parallel=3 \
            --log=${LFTP_LOG} \
            ${DRY_RUN} \
            ${PROD_UPLOAD}/ ${LOCAL_UPLOAD}/
    " 2>&1 | tail -3 | while IFS= read -r line; do log "  $line"; done || true

    # 전송된 파일 수 세기 (lftp log에서 "get" 라인)
    if [ -f "$LFTP_LOG" ]; then
        PROD_TRANSFERRED=$(grep -c "^get " "$LFTP_LOG" 2>/dev/null || echo 0)
    fi
    log "  프로덕션 → 로컬: ${PROD_TRANSFERRED}개 파일 전송"
else
    log "📥 [1/2] 프로덕션 다운로드 건너뜀 (--nas-only)"
fi

# ─── 2단계: 로컬 → NAS ───
log "📤 [2/2] 로컬 → NAS(dsp1830) 업로드..."

> "$LFTP_LOG"
lftp -c "
    set ftp:charset UTF-8
    set file:charset UTF-8
    set net:timeout 30
    set net:max-retries 3
    set net:reconnect-interval-base 5
    open -u ${NAS_USER},${NAS_PASS} ftp://${NAS_HOST}
    mirror --reverse --only-newer --no-perms --parallel=3 \
        --log=${LFTP_LOG} \
        ${DRY_RUN} \
        ${LOCAL_UPLOAD}/ ${NAS_UPLOAD}/
" 2>&1 | tail -3 | while IFS= read -r line; do log "  $line"; done

NAS_RESULT=$?

if [ -f "$LFTP_LOG" ]; then
    NAS_TRANSFERRED=$(grep -c "^put " "$LFTP_LOG" 2>/dev/null || echo 0)
fi
log "  로컬 → NAS: ${NAS_TRANSFERRED}개 파일 전송"

if [ $NAS_RESULT -ne 0 ]; then
    log "❌ NAS 업로드 실패 (exit: $NAS_RESULT)"
    save_status false "NAS upload failed (exit: $NAS_RESULT)"
    exit 1
fi

# ─── 완료 ───
END_TIME=$(date +%s)
ELAPSED=$((END_TIME - START_TIME))
save_status true
log "✅ 교정이미지 동기화 완료 (${ELAPSED}초, 프로덕션:${PROD_TRANSFERRED} NAS:${NAS_TRANSFERRED})"
log "===== 교정이미지 동기화 끝 ====="

rm -f "$LFTP_LOG"
