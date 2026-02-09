#!/bin/bash
# collect-changes.sh - Stop 훅에서 호출됨
# 의미있는 변경이 있을 때만 MEMORY.md 업데이트 지시 출력
# 출력이 없으면 Claude는 추가 동작 안 함

cd "$(git rev-parse --show-toplevel 2>/dev/null || echo /var/www/html)"

# 변경된 파일 목록 (staged + unstaged)
CHANGED=$(git diff --name-only HEAD 2>/dev/null | sort -u)
STAGED=$(git diff --cached --name-only 2>/dev/null | sort -u)
UNTRACKED=$(git ls-files --others --exclude-standard 2>/dev/null | sort -u)

# 전체 변경 파일 (중복 제거)
ALL_CHANGED=$(echo -e "$CHANGED\n$STAGED\n$UNTRACKED" | sort -u | grep -v '^$')
TOTAL=$(echo "$ALL_CHANGED" | grep -c -v '^$' 2>/dev/null || echo 0)

# PHP 파일만 필터
PHP_FILES=$(echo "$ALL_CHANGED" | grep '\.php$' | head -15)
PHP_COUNT=$(echo "$PHP_FILES" | grep -c -v '^$' 2>/dev/null || echo 0)

# JS 파일 필터
JS_FILES=$(echo "$ALL_CHANGED" | grep '\.js$' | head -10)
JS_COUNT=$(echo "$JS_FILES" | grep -c -v '^$' 2>/dev/null || echo 0)

# 의미있는 변경이 없으면 아무것도 출력하지 않음 (Claude가 계속하지 않음)
if [ "$TOTAL" -lt 1 ]; then
    exit 0
fi

# 핵심 파일 변경 감지 (중요 디렉토리)
CORE_CHANGES=$(echo "$ALL_CHANGED" | grep -E '^(includes/|shop_admin/|dashboard/|mypage/|tools/|api/)' | head -10)
CORE_COUNT=$(echo "$CORE_CHANGES" | grep -c -v '^$' 2>/dev/null || echo 0)

# 핵심 파일 변경이 3개 이상이거나 PHP 5개 이상일 때만 메모리 업데이트 지시
if [ "$CORE_COUNT" -lt 3 ] && [ "$PHP_COUNT" -lt 5 ]; then
    exit 0
fi

# Claude에게 MEMORY.md 업데이트 지시
cat <<EOF
[세션 종료 훅] 이번 세션에서 의미있는 변경이 감지되었습니다.

변경 파일 수: ${TOTAL}개 (PHP: ${PHP_COUNT}, JS: ${JS_COUNT})
핵심 변경 파일:
$(echo "$CORE_CHANGES" | sed 's/^/  - /')

PHP 변경 파일:
$(echo "$PHP_FILES" | sed 's/^/  - /')

📝 작업: /home/ysung/.claude/projects/-var-www-html/memory/MEMORY.md를 업데이트해주세요.
- 이번 세션에서 수행한 작업 요약
- 발견한 버그나 패턴
- 중요한 기술적 결정사항
- 향후 참조할 정보
EOF

exit 0
