#!/bin/bash
# ============================================================================
# FTP→FTP Relay Backup Script
# dsp114.com (source) → local temp → NAS (dsp1830.ipdisk.co.kr)
#
# Usage:
#   ./ftp_nas_backup.sh                  # Sync all 3 file types
#   ./ftp_nas_backup.sh upload           # Upload (교정파일) only
#   ./ftp_nas_backup.sh shop             # Shop/data (원고-스티커) only
#   ./ftp_nas_backup.sh imgfolder        # ImgFolder (원고-일반) only
#   ./ftp_nas_backup.sh upload --dry-run # Preview without transferring
#   ./ftp_nas_backup.sh upload --batch=100 # Process 100 folders at a time
#
# Features:
#   - Resume/restart safe (lftp mirror skips existing files)
#   - Bandwidth throttling (500KB/s default, configurable)
#   - Progress logging with timestamps
#   - Batch mode for upload folders (avoid long-running sessions)
#   - Dry-run mode for previewing
# ============================================================================

set -euo pipefail

# ---- Configuration ----
SRC_HOST="dsp114.com"
SRC_USER="duson1830"
SRC_PASS="du1830"

NAS_HOST="dsp1830.ipdisk.co.kr"
NAS_USER="admin"
NAS_PASS="1830"

# Source paths (absolute filesystem paths — lftp sees system root, not FTP home)
SRC_UPLOAD="/home/neo_web2/duson1830/www/MlangOrder_PrintAuto/upload"
SRC_SHOP="/home/neo_web2/duson1830/www/shop/data"
SRC_IMGFOLDER="/home/neo_web2/duson1830/www/ImgFolder"

# Source paths for curl (relative to FTP home dir — curl uses different root)
CURL_SRC_UPLOAD="/www/MlangOrder_PrintAuto/upload"
CURL_SRC_SHOP="/www/shop/data"
CURL_SRC_IMGFOLDER="/www/ImgFolder"

# NAS target paths
NAS_UPLOAD="/HDD2/share/mlangorder_printauto/upload"
NAS_SHOP="/HDD2/share/shop/data"
NAS_IMGFOLDER="/HDD2/share/ImgFolder"

# Local temp directory (relay point)
TEMP_DIR="/tmp/ftp_nas_relay"

# Bandwidth limit (bytes/sec) - 500KB/s to not overload source
BW_LIMIT="500000"

# lftp parallel connections
PARALLEL=3

# Log file
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
LOG_DIR="${SCRIPT_DIR}/logs"
LOG_FILE="${LOG_DIR}/nas_backup_$(date +%Y%m%d_%H%M%S).log"

# ImgFolder product directories to sync
IMGFOLDER_DIRS=(
    "_MlangPrintAuto_NameCard_index.php"
    "_MlangPrintAuto_inserted_index.php"
    "_MlangPrintAuto_LittlePrint_index.php"
    "_MlangPrintAuto_MerchandiseBond_index.php"
    "_MlangPrintAuto_NcrFlambeau_index.php"
    "_MlangPrintAuto_cadarok_index.php"
    "_MlangPrintAuto_envelope_index.php"
    "_MlangPrintAuto_sticker_index.php"
    "_MlangPrintAuto_sticker_stickerindex.php"
    "_MlangPrintAuto_sticker_basket2.php"
)

# ---- Parse Arguments ----
SYNC_TYPE="${1:-all}"
DRY_RUN=false
BATCH_SIZE=0
LFTP_EXTRA=""

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=true ;;
        --batch=*) BATCH_SIZE="${arg#*=}" ;;
        --bw=*) BW_LIMIT="${arg#*=}" ;;
        --parallel=*) PARALLEL="${arg#*=}" ;;
    esac
done

if [ "$DRY_RUN" = true ]; then
    LFTP_EXTRA="--dry-run"
fi

# ---- Functions ----

log() {
    local msg="[$(date '+%Y-%m-%d %H:%M:%S')] $1"
    echo "$msg"
    echo "$msg" >> "$LOG_FILE"
}

log_separator() {
    local line="================================================================"
    echo "$line"
    echo "$line" >> "$LOG_FILE"
}

ensure_dir() {
    mkdir -p "$1"
}

# Mirror from source FTP to local temp
mirror_from_source() {
    local src_path="$1"
    local local_path="$2"
    local extra="${3:-}"

    ensure_dir "$local_path"

    lftp -c "
        set net:limit-rate ${BW_LIMIT}
        set net:timeout 30
        set net:max-retries 3
        set net:reconnect-interval-base 5
        set ftp:charset EUC-KR
        set file:charset UTF-8
        open -u ${SRC_USER},${SRC_PASS} ${SRC_HOST}
        mirror --continue --parallel=${PARALLEL} ${LFTP_EXTRA} ${extra} '${src_path}' '${local_path}'
    " 2>&1 | tee -a "$LOG_FILE"
}

# Mirror from local temp to NAS FTP
mirror_to_nas() {
    local local_path="$1"
    local nas_path="$2"
    local extra="${3:-}"

    lftp -c "
        set net:timeout 30
        set net:max-retries 3
        set net:reconnect-interval-base 5
        open -u ${NAS_USER},${NAS_PASS} ${NAS_HOST}
        mirror --reverse --continue --parallel=${PARALLEL} ${LFTP_EXTRA} ${extra} '${local_path}' '${nas_path}'
    " 2>&1 | tee -a "$LOG_FILE"
}

# Sync upload folders (교정파일) - batch mode support
sync_upload() {
    log_separator
    log "UPLOAD (교정파일) sync start"
    log "Source: ${SRC_HOST}:${SRC_UPLOAD} → NAS: ${NAS_HOST}:${NAS_UPLOAD}"

    local temp_upload="${TEMP_DIR}/upload"
    ensure_dir "$temp_upload"

    if [ "$BATCH_SIZE" -gt 0 ]; then
        # Batch mode: list folders, process N at a time
        log "Batch mode: ${BATCH_SIZE} folders per batch"

        # Get folder list from source
        log "Listing source folders..."
        local src_folders=$(lftp -c "
            set net:timeout 30
            set ftp:charset EUC-KR
            open -u ${SRC_USER},${SRC_PASS} ${SRC_HOST}
            cls --sort=name '${SRC_UPLOAD}/'
        " 2>/dev/null | grep '/$' | sed 's|/$||' | sed "s|^${SRC_UPLOAD}/||")

        log "Listing NAS existing folders..."
        local nas_folders=$(lftp -c "
            set net:timeout 30
            open -u ${NAS_USER},${NAS_PASS} ${NAS_HOST}
            cls --sort=name '${NAS_UPLOAD}/'
        " 2>/dev/null | grep '/$' | sed 's|/$||' | sed "s|^${NAS_UPLOAD}/||" || echo "")

        # Find folders NOT on NAS yet
        local missing_folders=$(comm -23 <(echo "$src_folders" | sort) <(echo "$nas_folders" | sort))
        local total_missing=$(echo "$missing_folders" | grep -c . || echo 0)

        log "Source folders: $(echo "$src_folders" | wc -l | tr -d ' ')"
        log "NAS existing: $(echo "$nas_folders" | grep -c . || echo 0)"
        log "Missing (to transfer): ${total_missing}"

        if [ "$total_missing" -eq 0 ]; then
            log "All folders already synced!"
            return
        fi

        # Process in batches
        local batch_num=0
        local processed=0

        echo "$missing_folders" | while IFS= read -r folder; do
            [ -z "$folder" ] && continue

            processed=$((processed + 1))

            log "[${processed}/${total_missing}] Syncing folder: ${folder}"

            # Download from source
            local folder_temp="${temp_upload}/${folder}"
            ensure_dir "$folder_temp"

            lftp -c "
                set net:limit-rate ${BW_LIMIT}
                set net:timeout 30
                set net:max-retries 3
                set ftp:charset EUC-KR
                set file:charset UTF-8
                open -u ${SRC_USER},${SRC_PASS} ${SRC_HOST}
                mirror --continue ${LFTP_EXTRA} '${SRC_UPLOAD}/${folder}' '${folder_temp}'
            " 2>&1 | tee -a "$LOG_FILE"

            # Upload to NAS
            lftp -c "
                set net:timeout 30
                set net:max-retries 3
                open -u ${NAS_USER},${NAS_PASS} ${NAS_HOST}
                mirror --reverse --continue ${LFTP_EXTRA} '${folder_temp}' '${NAS_UPLOAD}/${folder}'
            " 2>&1 | tee -a "$LOG_FILE"

            # Cleanup temp to save disk space
            rm -rf "$folder_temp"

            # Batch pause
            if [ "$BATCH_SIZE" -gt 0 ] && [ $((processed % BATCH_SIZE)) -eq 0 ]; then
                log "Batch ${batch_num} complete (${processed}/${total_missing}). Pausing 5s..."
                batch_num=$((batch_num + 1))
                sleep 5
            fi
        done

    else
        # Full mirror mode: download all, then upload all
        log "Full mirror mode (may use significant temp disk space)"

        log "Step 1/2: Source → Local temp"
        mirror_from_source "$SRC_UPLOAD" "$temp_upload"

        log "Step 2/2: Local temp → NAS"
        mirror_to_nas "$temp_upload" "$NAS_UPLOAD"

        # Cleanup
        log "Cleaning temp..."
        rm -rf "$temp_upload"
    fi

    log "UPLOAD sync complete"
}

# Sync shop/data (원고-스티커)
sync_shop() {
    log_separator
    log "SHOP/DATA (원고-스티커) sync start"
    log "Source: ${SRC_HOST}:${SRC_SHOP} → NAS: ${NAS_HOST}:${NAS_SHOP}"
    log "Note: EUC-KR filenames will be converted to UTF-8"

    local temp_shop="${TEMP_DIR}/shop_data"
    ensure_dir "$temp_shop"

    log "Step 1/2: Source → Local temp"
    mirror_from_source "$SRC_SHOP" "$temp_shop"

    log "Step 2/2: Local temp → NAS"
    mirror_to_nas "$temp_shop" "$NAS_SHOP"

    # Count files
    local count=$(find "$temp_shop" -type f 2>/dev/null | wc -l)
    log "Shop files synced: ${count}"

    # Cleanup
    rm -rf "$temp_shop"
    log "SHOP/DATA sync complete"
}

# Sync ImgFolder (원고-일반)
sync_imgfolder() {
    log_separator
    log "IMGFOLDER (원고-일반) sync start"
    log "Syncing ${#IMGFOLDER_DIRS[@]} product directories"

    for dir in "${IMGFOLDER_DIRS[@]}"; do
        log "--- Syncing: ${dir} ---"

        local src_path="${SRC_IMGFOLDER}/${dir}"
        local temp_path="${TEMP_DIR}/imgfolder/${dir}"
        local nas_path="${NAS_IMGFOLDER}/${dir}"

        ensure_dir "$temp_path"

        log "  Source → Local temp"
        mirror_from_source "$src_path" "$temp_path"

        log "  Local temp → NAS"
        mirror_to_nas "$temp_path" "$nas_path"

        # Cleanup per-product to save disk
        rm -rf "$temp_path"
        log "  Done: ${dir}"
    done

    log "IMGFOLDER sync complete"
}

# Show summary of what needs to be synced
show_status() {
    log_separator
    log "=== SYNC STATUS ==="

    log ""
    log "Source: ftp://${SRC_HOST} (dsp114.com - 폐쇄 예정)"
    log "Target: ftp://${NAS_HOST} (NAS - 전체 백업)"
    log ""

    # Upload folder counts
    log "--- Upload (교정파일) ---"
    local src_upload_count=$(curl -s --max-time 30 --list-only "ftp://${SRC_USER}:${SRC_PASS}@${SRC_HOST}${CURL_SRC_UPLOAD}/" 2>/dev/null | wc -l)
    local nas_upload_count=$(curl -s --max-time 30 --list-only "ftp://${NAS_USER}:${NAS_PASS}@${NAS_HOST}${NAS_UPLOAD}/" 2>/dev/null | wc -l)
    log "  Source: ${src_upload_count} items"
    log "  NAS:    ${nas_upload_count} items"
    log "  Delta:  ~$((src_upload_count - nas_upload_count)) items to transfer"

    # Shop file counts
    log ""
    log "--- Shop/data (원고-스티커) ---"
    local src_shop_count=$(curl -s --max-time 30 --list-only "ftp://${SRC_USER}:${SRC_PASS}@${SRC_HOST}${CURL_SRC_SHOP}/" 2>/dev/null | wc -l)
    local nas_shop_count=$(curl -s --max-time 30 --list-only "ftp://${NAS_USER}:${NAS_PASS}@${NAS_HOST}${NAS_SHOP}/" 2>/dev/null | wc -l)
    log "  Source: ${src_shop_count} files"
    log "  NAS:    ${nas_shop_count} files"

    # ImgFolder
    log ""
    log "--- ImgFolder (원고-일반) ---"
    for dir in "${IMGFOLDER_DIRS[@]}"; do
        local exists_src=$(curl -s --max-time 10 --list-only "ftp://${SRC_USER}:${SRC_PASS}@${SRC_HOST}${CURL_SRC_IMGFOLDER}/${dir}/" 2>/dev/null | wc -l)
        local exists_nas=$(curl -s --max-time 10 --list-only "ftp://${NAS_USER}:${NAS_PASS}@${NAS_HOST}${NAS_IMGFOLDER}/${dir}/" 2>/dev/null | wc -l)
        log "  ${dir}: src=${exists_src}, nas=${exists_nas}"
    done

    log ""
    log "Settings: BW_LIMIT=${BW_LIMIT} bytes/s, PARALLEL=${PARALLEL}"
    log "Temp dir: ${TEMP_DIR}"
    log_separator
}

# ---- Main ----

ensure_dir "$LOG_DIR"
ensure_dir "$TEMP_DIR"

log "FTP→FTP NAS Backup started"
log "Args: type=${SYNC_TYPE}, dry_run=${DRY_RUN}, batch=${BATCH_SIZE}"
log "Log: ${LOG_FILE}"

case "$SYNC_TYPE" in
    status)
        show_status
        ;;
    upload)
        sync_upload
        ;;
    shop)
        sync_shop
        ;;
    imgfolder)
        sync_imgfolder
        ;;
    all)
        show_status
        sync_upload
        sync_shop
        sync_imgfolder
        log_separator
        log "ALL SYNC COMPLETE"
        ;;
    *)
        echo "Usage: $0 [status|upload|shop|imgfolder|all] [--dry-run] [--batch=N] [--bw=BYTES] [--parallel=N]"
        echo ""
        echo "Commands:"
        echo "  status     Show sync status (counts on both servers)"
        echo "  upload     Sync 교정파일 (upload folders)"
        echo "  shop       Sync 원고-스티커 (shop/data)"
        echo "  imgfolder  Sync 원고-일반 (ImgFolder product dirs)"
        echo "  all        Sync everything (default)"
        echo ""
        echo "Options:"
        echo "  --dry-run      Preview without transferring"
        echo "  --batch=N      Process N upload folders at a time (default: all at once)"
        echo "  --bw=BYTES     Bandwidth limit in bytes/sec (default: 500000 = 500KB/s)"
        echo "  --parallel=N   Parallel connections (default: 3)"
        echo ""
        echo "Examples:"
        echo "  $0 status                    # Check what needs syncing"
        echo "  $0 upload --dry-run          # Preview upload sync"
        echo "  $0 upload --batch=50         # Sync 50 folders at a time"
        echo "  $0 shop                      # Sync shop/data files"
        echo "  $0 all --bw=200000           # Sync all at 200KB/s"
        exit 1
        ;;
esac

log "Done. Log saved to: ${LOG_FILE}"
