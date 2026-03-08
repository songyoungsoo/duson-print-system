#!/bin/bash
# 상세페이지 안전 생성 래퍼
# 1) Staging 기존 이미지 백업 (타임스탬프)
# 2) 엔진 실행
# 3) 조쉬빌더인 경우: output → Staging 복사 + detail.html 생성
#
# Usage: generate_detail_safe.sh <engine> <product> [GEMINI_API_KEY]
# engine: quality | fast
# product: namecard, sticker_new, etc.

set -euo pipefail

ENGINE="${1:?Usage: $0 <engine> <product>}"
PRODUCT="${2:?Usage: $0 <engine> <product>}"
GEMINI_KEY="${3:-}"

WEB_ROOT="/var/www/html"
IMG_BASE="$WEB_ROOT/ImgFolder"
STAGING_DIR="$IMG_BASE/detail_page_staging/$PRODUCT"
ORCHESTRATOR_DIR="$WEB_ROOT/_detail_page"
ORCHESTRATOR_OUTPUT="$ORCHESTRATOR_DIR/output/$PRODUCT"
BACKUP_BASE="$IMG_BASE/detail_page_backup"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/tmp/detail_gen_${PRODUCT}.log"

# .env에서 환경변수 로드
if [ -f "$WEB_ROOT/.env" ]; then
    export $(grep -v '^#' "$WEB_ROOT/.env" | grep -v '^$' | xargs) 2>/dev/null
fi

log() { echo "[$(date '+%H:%M:%S')] $1" | tee -a "$LOG_FILE"; }

log "=== 안전 생성 시작: $PRODUCT (엔진: $ENGINE) ==="

# ─── Step 1: Staging 백업 ───
if [ -d "$STAGING_DIR" ] && [ "$(ls -A "$STAGING_DIR" 2>/dev/null)" ]; then
    BACKUP_DIR="$BACKUP_BASE/${PRODUCT}_${TIMESTAMP}"
    mkdir -p "$BACKUP_DIR"
    cp -r "$STAGING_DIR/." "$BACKUP_DIR/"
    log "✅ Staging 백업 → $BACKUP_DIR ($(ls "$BACKUP_DIR" | wc -l)개 파일)"
else
    log "⏭️  Staging 백업 없음 (비어있음)"
fi

# 조쉬빌더: orchestrator output도 백업
if [ "$ENGINE" = "quality" ] && [ -d "$ORCHESTRATOR_OUTPUT/sections" ] && [ "$(ls -A "$ORCHESTRATOR_OUTPUT/sections" 2>/dev/null)" ]; then
    ORCH_BACKUP="$BACKUP_BASE/${PRODUCT}_orch_${TIMESTAMP}"
    mkdir -p "$ORCH_BACKUP"
    cp -r "$ORCHESTRATOR_OUTPUT/." "$ORCH_BACKUP/"
    log "✅ 조쉬빌더 출력 백업 → $ORCH_BACKUP"
fi

# ─── Step 2: 엔진 실행 ───
if [ -n "$GEMINI_KEY" ]; then
    export GEMINI_API_KEY="$GEMINI_KEY"
fi
export PYTHONPATH="/home/ysung/.local/lib/python3.12/site-packages:${PYTHONPATH:-}"

if [ "$ENGINE" = "quality" ]; then
    log "🎨 조쉬빌더 실행 중..."
    cd "$ORCHESTRATOR_DIR"
    python3 scripts/orchestrator.py --product "$PRODUCT" 2>&1 | tee -a "$LOG_FILE"
    RESULT=${PIPESTATUS[0]}
    
    if [ $RESULT -ne 0 ]; then
        log "❌ 조쉬빌더 실행 실패 (exit: $RESULT)"
        exit $RESULT
    fi
    
    # ─── Step 3: 조쉬빌더 output → Staging 복사 ───
    SECTIONS_DIR="$ORCHESTRATOR_OUTPUT/sections"
    if [ ! -d "$SECTIONS_DIR" ]; then
        log "❌ 조쉬빌더 출력 디렉토리 없음: $SECTIONS_DIR"
        exit 1
    fi
    
    mkdir -p "$STAGING_DIR"
    
    # PNG 복사
    PNG_COUNT=$(ls "$SECTIONS_DIR"/section_*.png 2>/dev/null | wc -l)
    if [ "$PNG_COUNT" -gt 0 ]; then
        cp "$SECTIONS_DIR"/section_*.png "$STAGING_DIR/"
        log "✅ PNG $PNG_COUNT개 → Staging 복사"
    fi
    
    # copy.json 복사
    if [ -f "$ORCHESTRATOR_OUTPUT/copy.json" ]; then
        cp "$ORCHESTRATOR_OUTPUT/copy.json" "$STAGING_DIR/"
        log "✅ copy.json → Staging 복사"
    fi
    
    # detail.html 생성 (조쉬빌더 PNG는 이미지 자체에 텍스트 포함)
    PRODUCT_LABEL=$(python3 -c "
import json
with open('$WEB_ROOT/_detail_page/config/products.json') as f:
    products = json.load(f)
for p in products:
    if p.get('folder') == '$PRODUCT' or p.get('type') == '$PRODUCT':
        print(p.get('name_ko', '$PRODUCT'))
        break
else:
    print('$PRODUCT')
" 2>/dev/null || echo "$PRODUCT")
    
    cat > "$STAGING_DIR/detail.html" << HTMLEOF
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>${PRODUCT_LABEL} 상세페이지 (A 조쉬빌더)</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Pretendard','Apple SD Gothic Neo',sans-serif;background:#c8c8c8;}
.wrap{width:1100px;margin:0 auto;padding:40px 0 80px;}
.meta{font-size:12px;color:#999;margin-bottom:8px;}
.meta strong{font-size:16px;font-weight:800;color:#333;margin-right:8px;}
.meta .engine{display:inline-block;padding:2px 8px;border-radius:4px;background:#3b82f6;color:#fff;font-size:11px;font-weight:700;margin-left:8px;}
.sections{border-radius:20px;overflow:hidden;box-shadow:0 8px 48px rgba(0,0,0,.18);background:#fff;}
.sections img{width:100%;display:block;}
</style>
</head>
<body>
<div class="wrap">
  <div class="meta"><strong>${PRODUCT_LABEL}</strong>엔진: A 조쉬빌더<span class="engine">A 조쉬빌더</span> · 생성: $(date '+%Y-%m-%d %H:%M')</div>
  <div class="sections">
HTMLEOF
    
    for i in $(seq -w 1 13); do
        if [ -f "$STAGING_DIR/section_${i}.png" ]; then
            echo "    <img src=\"section_${i}.png\" alt=\"섹션 ${i}\" loading=\"lazy\">" >> "$STAGING_DIR/detail.html"
        fi
    done
    
    cat >> "$STAGING_DIR/detail.html" << HTMLEOF
  </div>
</div>
</body>
</html>
HTMLEOF
    
    log "✅ detail.html 생성 → Staging"
    log "🎉 완료! Staging 미리보기: http://localhost/ImgFolder/detail_page_staging/$PRODUCT/detail.html"

else
    # Fast 엔진은 이미 Staging에 직접 출력
    log "⚡ Fast 엔진 실행 중..."
    cd "$WEB_ROOT"
    python3 scripts/ai_detail_page.py generate-builtin "$PRODUCT" 2>&1 | tee -a "$LOG_FILE"
    RESULT=${PIPESTATUS[0]}
    
    if [ $RESULT -ne 0 ]; then
        log "❌ Fast 엔진 실행 실패 (exit: $RESULT)"
        exit $RESULT
    fi
    log "🎉 완료! Staging 미리보기: http://localhost/ImgFolder/detail_page_staging/$PRODUCT/detail.html"
fi

log "=== 안전 생성 완료: $PRODUCT ==="
