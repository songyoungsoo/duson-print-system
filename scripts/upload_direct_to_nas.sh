#!/bin/bash
# Direct FTP upload - handles Korean filenames and spaces
SRC="/var/www/html/scripts/old_proofs_organized"
NAS_HOST="sknas205.ipdisk.co.kr"
NAS_USER="sknas205"
NAS_PASS="sknas205204203"
NAS_BASE="/HDD1/duson260118/archive_upload"
LOG="/var/www/html/scripts/direct_upload.log"

TOTAL=$(ls -d "$SRC"/*/ 2>/dev/null | wc -l)
COUNT=0
FILES=0
ERRORS=0
START=$(date +%s)

# URL-encode function for FTP paths
urlencode() {
    python3 -c "import urllib.parse; print(urllib.parse.quote('$1', safe=''))"
}

echo "=== Direct FTP Upload Start: $(date) ===" | tee "$LOG"
echo "Source: $SRC ($TOTAL folders)" | tee -a "$LOG"

for dir in "$SRC"/*/; do
    [ ! -d "$dir" ] && continue
    folder=$(basename "$dir")
    COUNT=$((COUNT+1))

    for file in "$dir"*; do
        [ ! -f "$file" ] && continue
        fname=$(basename "$file")
        encoded_fname=$(urlencode "$fname")
        FILES=$((FILES+1))

        curl -s --ftp-create-dirs \
            -T "$file" \
            "ftp://${NAS_HOST}/${NAS_BASE}/${folder}/${encoded_fname}" \
            --user "${NAS_USER}:${NAS_PASS}" \
            --connect-timeout 10 \
            --max-time 120 2>/dev/null
        if [ $? -ne 0 ]; then
            echo "  ERROR: $folder/$fname" >> "$LOG"
            ERRORS=$((ERRORS+1))
        fi
    done

    if [ $((COUNT % 500)) -eq 0 ]; then
        ELAPSED=$(( $(date +%s) - START ))
        RATE=$(echo "scale=1; $COUNT / ($ELAPSED / 60)" | bc 2>/dev/null || echo "?")
        ETA_MIN=$(echo "scale=0; ($TOTAL - $COUNT) * $ELAPSED / $COUNT / 60" | bc 2>/dev/null || echo "?")
        echo "[$(date +%H:%M)] $COUNT/$TOTAL folders, $FILES files (${RATE} folders/min, ETA ~${ETA_MIN}min, errors: $ERRORS)" | tee -a "$LOG"
    fi
done

END=$(date +%s)
DURATION=$(( (END - START) / 60 ))
echo "=== Done: $(date) ===" | tee -a "$LOG"
echo "Total: $COUNT folders, $FILES files, $ERRORS errors, ${DURATION}min" | tee -a "$LOG"
