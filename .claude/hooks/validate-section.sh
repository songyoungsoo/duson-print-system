#!/bin/bash
# validate-section.sh
# PostToolUse: Bash 도구 실행 후 섹션 이미지 생성 결과 검증
#
# orchestrator.py 실행 후 output 디렉토리를 검사하여
# 비정상 이미지(너무 작거나 누락)를 감지하고 경고한다.

INPUT=$(cat)

TOOL_NAME=$(echo "$INPUT" | python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('tool_name',''))" 2>/dev/null)
COMMAND=$(echo "$INPUT" | python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('tool_input',{}).get('command',''))" 2>/dev/null)

# orchestrator.py 실행 후에만 검증
if [[ "$TOOL_NAME" != "Bash" ]]; then
    exit 0
fi

if ! echo "$COMMAND" | grep -q "orchestrator.py"; then
    exit 0
fi

# 섹션 디렉토리 추출
PRODUCT=$(echo "$COMMAND" | grep -oP '(?<=--product )\S+')
VERSION=$(echo "$COMMAND" | grep -oP '(?<=--version )\S+')

if [[ -z "$PRODUCT" ]]; then
    exit 0
fi

if [[ "$VERSION" == "2" ]]; then
    SECTIONS_DIR="/var/www/html/_detail_page/output/${PRODUCT}/v2/sections"
else
    SECTIONS_DIR="/var/www/html/_detail_page/output/${PRODUCT}/sections"
fi

if [[ ! -d "$SECTIONS_DIR" ]]; then
    exit 0
fi

# 섹션 이미지 검증
TOTAL=0
SMALL=0
MISSING=0

for i in $(seq -w 1 13); do
    FILE="$SECTIONS_DIR/section_${i}.png"
    if [[ ! -f "$FILE" ]]; then
        MISSING=$((MISSING + 1))
    else
        SIZE=$(stat -c%s "$FILE" 2>/dev/null || echo 0)
        TOTAL=$((TOTAL + 1))
        if [[ $SIZE -lt 10240 ]]; then  # 10KB 미만 = 비정상
            SMALL=$((SMALL + 1))
            echo "⚠️ 비정상 이미지: section_${i}.png (${SIZE} bytes)" >&2
        fi
    fi
done

if [[ $MISSING -gt 0 ]]; then
    echo "⚠️ 누락 섹션: ${MISSING}개" >&2
fi

if [[ $SMALL -gt 0 ]]; then
    echo "⚠️ 비정상 이미지: ${SMALL}개 (재생성 필요)" >&2
fi

if [[ $MISSING -eq 0 && $SMALL -eq 0 ]]; then
    echo "✅ 섹션 검증 통과: ${TOTAL}/13 정상" >&2
fi

exit 0
