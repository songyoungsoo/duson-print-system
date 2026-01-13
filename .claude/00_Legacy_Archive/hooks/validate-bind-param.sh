#!/bin/bash
# bind_param 자동 검증 Hook
# PHP 파일 저장 시 타입 문자 개수와 파라미터 개수 자동 확인

FILE_PATH="${1}"

# PHP 파일만 검사
if [[ ! "$FILE_PATH" == *.php ]]; then
    exit 0
fi

# 파일이 존재하지 않으면 종료
if [[ ! -f "$FILE_PATH" ]]; then
    exit 0
fi

# bind_param이 없는 파일은 건너뛰기
if ! grep -q "mysqli_stmt_bind_param" "$FILE_PATH"; then
    exit 0
fi

echo "🔍 bind_param 검증 중: $FILE_PATH"

# 임시 파일 생성
TEMP_FILE=$(mktemp)

# bind_param 라인 추출 및 검증
grep -n "mysqli_stmt_bind_param" "$FILE_PATH" > "$TEMP_FILE"

while IFS=: read -r LINE_NUM LINE_CONTENT; do
    # 타입 문자열 추출 (첫 번째 따옴표 안의 문자열)
    TYPE_STRING=$(echo "$LINE_CONTENT" | grep -oP '\$stmt,\s*["\047]([isdb]+)["\047]' | grep -oP '["\047]\K[isdb]+(?=["\047])')

    if [[ -z "$TYPE_STRING" ]]; then
        continue
    fi

    TYPE_COUNT=${#TYPE_STRING}

    # 해당 라인 근처의 SQL 쿼리 찾기 (이전 10줄 내)
    START_LINE=$((LINE_NUM - 10))
    if [[ $START_LINE -lt 1 ]]; then
        START_LINE=1
    fi

    # SQL 쿼리에서 ? 개수 세기
    QUERY_CONTEXT=$(sed -n "${START_LINE},${LINE_NUM}p" "$FILE_PATH")
    PLACEHOLDER_COUNT=$(echo "$QUERY_CONTEXT" | grep -o '?' | wc -l)

    # ? 개수와 타입 문자 개수 비교
    if [[ $PLACEHOLDER_COUNT -gt 0 && $PLACEHOLDER_COUNT -ne $TYPE_COUNT ]]; then
        echo ""
        echo "❌ bind_param 오류 발견!"
        echo "   파일: $FILE_PATH:$LINE_NUM"
        echo "   타입 문자 개수: $TYPE_COUNT (\"$TYPE_STRING\")"
        echo "   SQL ? 개수: $PLACEHOLDER_COUNT"
        echo ""
        echo "   🔴 개수가 일치하지 않습니다!"
        echo "   🔴 데이터 손실 위험! 저장을 중단합니다."
        echo ""
        rm -f "$TEMP_FILE"
        exit 2  # 저장 중단
    fi

    # 타입 문자가 i/s/d/b만 포함하는지 확인
    if ! echo "$TYPE_STRING" | grep -qE '^[isdb]+$'; then
        echo ""
        echo "⚠️  경고: 잘못된 타입 문자 발견"
        echo "   파일: $FILE_PATH:$LINE_NUM"
        echo "   타입 문자열: \"$TYPE_STRING\""
        echo "   허용: i(int), s(string), d(double), b(blob)"
        echo ""
    fi
done < "$TEMP_FILE"

rm -f "$TEMP_FILE"

# 검증 완료
echo "✅ bind_param 검증 통과"
exit 0
