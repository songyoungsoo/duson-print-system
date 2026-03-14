#!/bin/bash
# ──────────────────────────────────────────────────────────────────
# Step 3: 정리된 교정이미지 → NAS 2곳 FTP 업로드
# ──────────────────────────────────────────────────────────────────
# reorganize_old_proofs.sh로 정리된 플랫 구조를 NAS에 업로드.
#
# 소스: old_proofs_organized/{order_no}/파일들
# 대상: NAS1:/HDD2/share/archive_upload/{order_no}/
#        NAS2:/HDD1/duson260118/archive_upload/{order_no}/
#
# 사용법:
#   ./upload_proofs_to_nas.sh              # 전체 업로드 (both NAS)
#   ./upload_proofs_to_nas.sh --dry-run    # 미리보기
#   ./upload_proofs_to_nas.sh --nas1-only  # 1차 NAS만
#   ./upload_proofs_to_nas.sh --nas2-only  # 2차 NAS만
#
# lftp mirror --reverse로 증분 업로드 (이미 있는 파일 건너뜀)
# ──────────────────────────────────────────────────────────────────

set -euo pipefail

LOG="/var/www/html/scripts/old_proofs_upload.log"
LFTP_LOG="/tmp/old_proofs_upload_lftp.log"

# 로컬 (정리된 폴더)
LOCAL_BASE="/var/www/html/scripts/old_proofs_organized"

# 1차 NAS (dsp1830)
NAS1_HOST="dsp1830.ipdisk.co.kr"
NAS1_USER="admin"
NAS1_PASS="1830"
NAS1_BASE="/HDD2/share/archive_upload"

# 2차 NAS (sknas205)
NAS2_HOST="sknas205.ipdisk.co.kr"
NAS2_USER="sknas205"
NAS2_PASS="sknas205204203"
NAS2_BASE="/HDD1/duson260118/archive_upload"

# 옵션
DRY_RUN=""
NAS1_ENABLED=true
NAS2_ENABLED=true

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] [NAS-UP] $1" | tee -a "$LOG"; }

while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)   DRY_RUN="--dry-run"; shift ;;
        --nas1-only) NAS2_ENABLED=false; shift ;;
        --nas2-only) NAS1_ENABLED=false; shift ;;
        --help|-h)
            echo "사용법: $0 [옵션]"
            echo "  --dry-run     미리보기 (실제 전송 없음)"
            echo "  --nas1-only   1차 NAS(dsp1830)만"
            echo "  --nas2-only   2차 NAS(sknas205)만"
            exit 0
            ;;
        *) echo "알 수 없는 옵션: $1"; exit 1 ;;
    esac
done

START_TIME=$(date +%s)
NAS1_TRANSFERRED=0
NAS2_TRANSFERRED=0

log "===== 교정이미지 → NAS 업로드 시작 ====="
[ -n "$DRY_RUN" ] && log "  DRY-RUN 모드"
$NAS1_ENABLED && log "  대상: NAS1 (dsp1830) → ${NAS1_BASE}"
$NAS2_ENABLED && log "  대상: NAS2 (sknas205) → ${NAS2_BASE}"

# 소스 확인
if [ ! -d "$LOCAL_BASE" ]; then
    log "❌ 소스 디렉토리 없음: $LOCAL_BASE"
    log "   먼저 reorganize_old_proofs.sh를 실행하세요."
    exit 1
fi

TOTAL_DIRS=$(find "$LOCAL_BASE" -mindepth 1 -maxdepth 1 -type d 2>/dev/null | wc -l)
TOTAL_SIZE=$(du -sh "$LOCAL_BASE" 2>/dev/null | cut -f1)
log "  소스: ${TOTAL_DIRS}개 폴더, ${TOTAL_SIZE}"

# ─── NAS1 업로드 ───
if $NAS1_ENABLED; then
    log "📤 [1/2] 로컬 → NAS1(dsp1830) 업로드..."

    > "$LFTP_LOG"
    lftp -c "
        set ftp:charset UTF-8
        set file:charset UTF-8
        set net:timeout 60
        set net:max-retries 5
        set net:reconnect-interval-base 10
        set ftp:ssl-allow no
        open -u ${NAS1_USER},${NAS1_PASS} ftp://${NAS1_HOST}
        mirror --reverse --only-newer --no-perms --parallel=3 \
            --log=${LFTP_LOG} \
            ${DRY_RUN} \
            ${LOCAL_BASE}/ ${NAS1_BASE}/
    " 2>&1 | tail -5 | while IFS= read -r line; do log "  $line"; done

    NAS1_RESULT=$?

    if [ -f "$LFTP_LOG" ]; then
        NAS1_TRANSFERRED=$(grep -c "^put " "$LFTP_LOG" 2>/dev/null || echo 0)
    fi
    log "  NAS1: ${NAS1_TRANSFERRED}개 파일 업로드"

    if [ $NAS1_RESULT -ne 0 ]; then
        log "⚠️  NAS1 업로드 일부 실패 (exit: $NAS1_RESULT)"
    fi
fi

# ─── NAS2 업로드 ───
if $NAS2_ENABLED; then
    log "📤 [2/2] 로컬 → NAS2(sknas205) 업로드..."

    > "$LFTP_LOG"
    lftp -c "
        set ftp:charset UTF-8
        set file:charset UTF-8
        set net:timeout 60
        set net:max-retries 5
        set net:reconnect-interval-base 10
        set ftp:ssl-allow no
        open -u ${NAS2_USER},${NAS2_PASS} ftp://${NAS2_HOST}
        mirror --reverse --only-newer --no-perms --parallel=3 \
            --log=${LFTP_LOG} \
            ${DRY_RUN} \
            ${LOCAL_BASE}/ ${NAS2_BASE}/
    " 2>&1 | tail -5 | while IFS= read -r line; do log "  $line"; done

    NAS2_RESULT=$?

    if [ -f "$LFTP_LOG" ]; then
        NAS2_TRANSFERRED=$(grep -c "^put " "$LFTP_LOG" 2>/dev/null || echo 0)
    fi
    log "  NAS2: ${NAS2_TRANSFERRED}개 파일 업로드"

    if [ $NAS2_RESULT -ne 0 ]; then
        log "⚠️  NAS2 업로드 일부 실패 (exit: $NAS2_RESULT)"
    fi
fi

# ─── 결과 리포트 ───
END_TIME=$(date +%s)
ELAPSED=$((END_TIME - START_TIME))

log ""
log "===== NAS 업로드 완료 ====="
log "  소요 시간: ${ELAPSED}초"
log "  NAS1 전송: ${NAS1_TRANSFERRED}개 파일"
log "  NAS2 전송: ${NAS2_TRANSFERRED}개 파일"
log ""
log "업로드 완료 후:"
log "  1. NAS에서 파일 확인"
log "  2. 프로덕션에 NasImageProxy.php 배포"
log "  3. 로컬 임시 파일 정리: rm -rf ${LOCAL_BASE}"
log "  4. 원본 파일 정리: rm -rf /var/www/html/scripts/old_proofs_raw"
log "===== 끝 ====="

rm -f "$LFTP_LOG"
