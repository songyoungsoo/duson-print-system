#!/bin/bash
# protect-v1-output.sh
# PreToolUse: Write/Edit 시 v1 output 덮어쓰기 방지
#
# v2 실행 중에 output/{product}/ (v1 경로)에
# 직접 쓰기를 차단한다.
# output/{product}/v2/ 경로는 허용.

INPUT=$(cat)

TOOL_NAME=$(echo "$INPUT" | python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('tool_name',''))" 2>/dev/null)
FILE_PATH=$(echo "$INPUT" | python3 -c "import json,sys; d=json.load(sys.stdin); print(d.get('tool_input',{}).get('file_path',''))" 2>/dev/null)

# Write/Edit 도구만 검사
if [[ "$TOOL_NAME" != "Write" && "$TOOL_NAME" != "Edit" ]]; then
    exit 0
fi

# v1 output 경로 패턴 (v2 하위 경로는 제외)
if echo "$FILE_PATH" | grep -qP "_detail_page/output/[^/]+/(?!v2/)(sections/|final_detail_page|metadata|copy|design|product_brief|research_brief)"; then
    echo "🛡️ V1 보호: $FILE_PATH 는 v1 출력 경로입니다. v2는 output/{product}/v2/ 에 저장하세요." >&2
    exit 2
fi

exit 0
