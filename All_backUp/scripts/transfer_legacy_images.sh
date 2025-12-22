#!/bin/bash
# dsp114.com → dsp1830.shop 레거시 이미지 전송 스크립트
# 2018년 이후 주문만 전송

FTP_HOST="dsp114.com"
FTP_USER="duson1830"
FTP_PASS="du1830"
FTP_PATH="/www/MlangOrder_PrintAuto/upload"

LOCAL_PATH="/var/www/html/mlangorder_printauto/upload"
TRANSFER_LIST="/tmp/final_transfer_list.txt"
LOG_FILE="/tmp/transfer_log_$(date +%Y%m%d_%H%M%S).log"
PROGRESS_FILE="/tmp/transfer_progress.txt"

echo "=== 레거시 이미지 전송 시작 ===" | tee -a "$LOG_FILE"
echo "시작 시간: $(date)" | tee -a "$LOG_FILE"
echo "전송 대상: $(wc -l < $TRANSFER_LIST)개 폴더" | tee -a "$LOG_FILE"

total=$(wc -l < "$TRANSFER_LIST")
count=0
success=0
failed=0

while IFS= read -r folder_no; do
    ((count++))

    # 진행 상황 표시
    if [ $((count % 100)) -eq 0 ]; then
        echo "[$count/$total] 진행 중... (성공: $success, 실패: $failed)" | tee -a "$LOG_FILE"
    fi

    # 로컬 폴더 생성
    mkdir -p "$LOCAL_PATH/$folder_no"

    # FTP에서 파일 목록 가져오기
    files=$(curl -s --list-only -u "$FTP_USER:$FTP_PASS" "ftp://$FTP_HOST$FTP_PATH/$folder_no/" 2>/dev/null)

    if [ -z "$files" ]; then
        echo "[$folder_no] 빈 폴더 또는 접근 실패" >> "$LOG_FILE"
        ((failed++))
        continue
    fi

    # 각 파일 다운로드
    folder_success=true
    while IFS= read -r file; do
        if [ -n "$file" ] && [ "$file" != "." ] && [ "$file" != ".." ]; then
            curl -s -u "$FTP_USER:$FTP_PASS" \
                "ftp://$FTP_HOST$FTP_PATH/$folder_no/$file" \
                -o "$LOCAL_PATH/$folder_no/$file" 2>/dev/null

            if [ $? -ne 0 ]; then
                echo "[$folder_no] 파일 다운로드 실패: $file" >> "$LOG_FILE"
                folder_success=false
            fi
        fi
    done <<< "$files"

    if $folder_success; then
        ((success++))
    else
        ((failed++))
    fi

    # 진행 상황 저장 (재시작 시 활용)
    echo "$folder_no" > "$PROGRESS_FILE"

done < "$TRANSFER_LIST"

echo "" | tee -a "$LOG_FILE"
echo "=== 전송 완료 ===" | tee -a "$LOG_FILE"
echo "종료 시간: $(date)" | tee -a "$LOG_FILE"
echo "성공: $success개" | tee -a "$LOG_FILE"
echo "실패: $failed개" | tee -a "$LOG_FILE"
echo "로그 파일: $LOG_FILE"
