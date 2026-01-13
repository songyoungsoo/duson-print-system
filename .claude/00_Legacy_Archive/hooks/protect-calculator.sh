#!/bin/bash
# 드롭다운 자동계산 보호 시스템
# 9개 품목의 계산기 코드를 검증하고 문제 발견 시 복구

FILE_PATH="${1}"

# 9개 품목 정의
PRODUCTS=(
    "inserted"
    "namecard"
    "envelope"
    "sticker_new"
    "msticker"
    "cadarok"
    "littleprint"
    "ncrflambeau"
    "merchandisebond"
)

# 제품 디렉토리인지 확인
PRODUCT=""
for p in "${PRODUCTS[@]}"; do
    if [[ "$FILE_PATH" == *"/mlangprintauto/$p/"* ]]; then
        PRODUCT="$p"
        break
    fi
done

# 제품 파일이 아니면 건너뛰기
if [[ -z "$PRODUCT" ]]; then
    exit 0
fi

# calculator.js 또는 index.php 파일만 검사
if [[ ! "$FILE_PATH" =~ (calculator\.js|index\.php)$ ]]; then
    exit 0
fi

echo "🔍 [$PRODUCT] 드롭다운 자동계산 검증 중..."

# 검증 패턴 정의
declare -A REQUIRED_PATTERNS

# JavaScript 파일 검증
if [[ "$FILE_PATH" == *"calculator.js"* ]] || [[ "$FILE_PATH" == *.js ]]; then
    REQUIRED_PATTERNS=(
        ["calculatePrice"]="calculatePrice 함수가 존재해야 합니다"
        ["calculatePriceAjax"]="AJAX 가격 계산 함수가 필요합니다"
        ["addEventListener.*change"]="드롭다운 change 이벤트 리스너가 필요합니다"
        ["window\.currentPriceData"]="가격 데이터 저장이 필요합니다"
    )
fi

# PHP 파일 검증
if [[ "$FILE_PATH" == *"index.php" ]]; then
    REQUIRED_PATTERNS=(
        ["<select.*onchange"]="드롭다운에 onchange 핸들러가 필요합니다"
        ["calculatePrice|autoCalculatePrice"]="계산 함수 호출이 필요합니다"
        ["id=['\"].*['\"]"]="폼 요소에 ID가 필요합니다"
    )
fi

# 패턴 검증
ERRORS=0
for pattern in "${!REQUIRED_PATTERNS[@]}"; do
    message="${REQUIRED_PATTERNS[$pattern]}"

    if ! grep -qE "$pattern" "$FILE_PATH"; then
        echo "❌ 오류: $message"
        echo "   패턴: $pattern"
        ERRORS=$((ERRORS + 1))
    fi
done

# 골든 백업 경로
GOLDEN_DIR="/var/www/html/.claude/golden-backups/$PRODUCT"
GOLDEN_FILE="$GOLDEN_DIR/$(basename "$FILE_PATH")"

# 골든 백업이 없으면 현재 파일을 골든으로 저장
if [[ ! -f "$GOLDEN_FILE" ]] && [[ $ERRORS -eq 0 ]]; then
    mkdir -p "$GOLDEN_DIR"
    cp "$FILE_PATH" "$GOLDEN_FILE"
    echo "✅ 골든 백업 생성: $GOLDEN_FILE"
    exit 0
fi

# 오류가 있고 골든 백업이 있으면 복구 제안
if [[ $ERRORS -gt 0 ]] && [[ -f "$GOLDEN_FILE" ]]; then
    echo ""
    echo "🚨 경고: $ERRORS개의 패턴 오류 발견!"
    echo ""
    echo "골든 백업으로 복구 가능합니다:"
    echo "  cp \"$GOLDEN_FILE\" \"$FILE_PATH\""
    echo ""
    echo "또는 차이점 확인:"
    echo "  diff \"$GOLDEN_FILE\" \"$FILE_PATH\""
    echo ""

    # 자동 복구 여부 확인 (환경변수)
    if [[ "$AUTO_RESTORE_CALCULATOR" == "true" ]]; then
        echo "🔧 자동 복구 시작..."
        cp "$GOLDEN_FILE" "$FILE_PATH"
        echo "✅ 골든 백업으로 복구 완료!"
        exit 0
    else
        echo "💡 자동 복구를 원하시면:"
        echo "   export AUTO_RESTORE_CALCULATOR=true"
        exit 1  # 경고만 표시, 저장은 허용
    fi
fi

if [[ $ERRORS -eq 0 ]]; then
    echo "✅ 드롭다운 자동계산 검증 통과"
fi

exit 0
