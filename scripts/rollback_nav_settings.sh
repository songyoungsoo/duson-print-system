#!/bin/bash
# ============================================================
# 롤백 스크립트: 네비게이션 모드 토글 + 관리자 설정 시스템
# 커밋: f94c1ca4 → 이전: 9b99a192
# 생성일: 2026-02-14
# ============================================================
#
# 사용법:
#   ./scripts/rollback_nav_settings.sh          # 전체 롤백 (코드 + DB + 프로덕션)
#   ./scripts/rollback_nav_settings.sh code      # 코드만 롤백 (git revert)
#   ./scripts/rollback_nav_settings.sh db        # DB만 롤백 (site_settings DROP)
#   ./scripts/rollback_nav_settings.sh prod      # 프로덕션만 롤백 (FTP 업로드)
#   ./scripts/rollback_nav_settings.sh --dry-run # 미리보기 (실제 실행 없음)
#
# ============================================================

set -euo pipefail

# --- 설정 ---
COMMIT_TO_REVERT="f94c1ca4"
PREVIOUS_COMMIT="9b99a192"
FTP_HOST="dsp114.co.kr"
FTP_USER="dsp1830"
FTP_PASS="cH*j@yzj093BeTtc"
FTP_ROOT="/httpdocs"
DB_USER="dsp1830"
DB_PASS="ds701018"
DB_NAME="dsp1830"
DRY_RUN=false
MODE="${1:-all}"

# 색상
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info()  { echo -e "${BLUE}[INFO]${NC} $1"; }
log_ok()    { echo -e "${GREEN}[OK]${NC} $1"; }
log_warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# dry-run 체크
if [[ "$MODE" == "--dry-run" ]]; then
    DRY_RUN=true
    MODE="all"
    log_warn "DRY-RUN 모드: 실제 실행 없이 미리보기만 합니다."
    echo ""
fi

# ============================================================
# 1. 코드 롤백 (Git Revert)
# ============================================================
rollback_code() {
    echo ""
    echo "============================================================"
    log_info "1단계: 코드 롤백 (Git Revert)"
    echo "============================================================"
    echo ""

    log_info "되돌릴 커밋: ${COMMIT_TO_REVERT}"
    log_info "변경된 파일 목록:"
    git show --stat --format="" "${COMMIT_TO_REVERT}" | sed 's/^/  /'
    echo ""

    if $DRY_RUN; then
        log_warn "[DRY-RUN] git revert --no-edit ${COMMIT_TO_REVERT} 실행 예정"
        return
    fi

    read -p "코드를 되돌리시겠습니까? (y/N): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        log_warn "코드 롤백 건너뜀"
        return
    fi

    git revert --no-edit "${COMMIT_TO_REVERT}"
    log_ok "코드 롤백 완료 (revert 커밋 생성됨)"
    echo ""
    git log --oneline -3
}

# ============================================================
# 2. DB 롤백 (site_settings 테이블 삭제)
# ============================================================
rollback_db() {
    echo ""
    echo "============================================================"
    log_info "2단계: DB 롤백 (site_settings 테이블)"
    echo "============================================================"
    echo ""

    # 로컬 DB 확인
    local table_exists
    table_exists=$(mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e \
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME' AND table_name='site_settings';" 2>/dev/null || echo "0")

    if [[ "$table_exists" == "0" ]]; then
        log_warn "site_settings 테이블이 존재하지 않습니다. 건너뜁니다."
        return
    fi

    log_info "현재 site_settings 데이터:"
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "SELECT * FROM site_settings;" 2>/dev/null || true
    echo ""

    if $DRY_RUN; then
        log_warn "[DRY-RUN] DROP TABLE site_settings 실행 예정"
        return
    fi

    read -p "site_settings 테이블을 삭제하시겠습니까? (y/N): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        log_warn "DB 롤백 건너뜀"
        return
    fi

    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" -e "DROP TABLE IF EXISTS site_settings;" 2>/dev/null
    log_ok "site_settings 테이블 삭제 완료"
}

# ============================================================
# 3. 프로덕션 롤백 (이전 버전 파일 FTP 업로드)
# ============================================================
rollback_production() {
    echo ""
    echo "============================================================"
    log_info "3단계: 프로덕션 롤백 (FTP 업로드)"
    echo "============================================================"
    echo ""

    # 롤백 대상 파일 목록
    local files=(
        "includes/nav.php"
        "css/common-styles.css"
        "dashboard/includes/config.php"
    )

    # 삭제 대상 (신규 생성 파일)
    local new_files=(
        "dashboard/settings/index.php"
        "dashboard/api/settings.php"
    )

    log_info "이전 버전으로 복원할 파일:"
    for f in "${files[@]}"; do
        echo "  ↩ $f"
    done
    echo ""

    log_info "프로덕션에서 삭제할 파일 (신규 생성된 파일):"
    for f in "${new_files[@]}"; do
        echo "  ✕ $f"
    done
    echo ""

    if $DRY_RUN; then
        log_warn "[DRY-RUN] FTP 업로드/삭제 실행 예정"
        return
    fi

    read -p "프로덕션에 롤백하시겠습니까? (y/N): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        log_warn "프로덕션 롤백 건너뜀"
        return
    fi

    # 이전 커밋에서 파일 추출 후 업로드
    local tmp_dir
    tmp_dir=$(mktemp -d)

    for f in "${files[@]}"; do
        local dir_path
        dir_path=$(dirname "$f")
        mkdir -p "$tmp_dir/$dir_path"

        # 이전 커밋에서 파일 추출
        git show "${PREVIOUS_COMMIT}:${f}" > "$tmp_dir/$f" 2>/dev/null
        if [[ $? -eq 0 ]]; then
            curl -s -T "$tmp_dir/$f" \
                "ftp://${FTP_HOST}${FTP_ROOT}/${f}" \
                --user "${FTP_USER}:${FTP_PASS}" \
                --ftp-create-dirs
            log_ok "업로드: $f"
        else
            log_warn "이전 커밋에 $f 없음 (건너뜀)"
        fi
    done

    # 신규 파일 삭제 (FTP DELE)
    for f in "${new_files[@]}"; do
        curl -s -Q "DELE ${FTP_ROOT}/${f}" \
            "ftp://${FTP_HOST}/" \
            --user "${FTP_USER}:${FTP_PASS}" 2>/dev/null || true
        log_ok "삭제: $f"
    done

    rm -rf "$tmp_dir"
    log_ok "프로덕션 롤백 완료"
}

# ============================================================
# 실행
# ============================================================
echo ""
echo "============================================================"
echo "  네비게이션 설정 시스템 롤백 스크립트"
echo "  커밋: ${COMMIT_TO_REVERT} → ${PREVIOUS_COMMIT}"
echo "  모드: ${MODE}"
echo "============================================================"

case "$MODE" in
    all)
        rollback_code
        rollback_db
        rollback_production
        ;;
    code)
        rollback_code
        ;;
    db)
        rollback_db
        ;;
    prod)
        rollback_production
        ;;
    *)
        log_error "알 수 없는 모드: $MODE"
        echo "사용법: $0 [all|code|db|prod|--dry-run]"
        exit 1
        ;;
esac

echo ""
echo "============================================================"
log_ok "롤백 작업 완료"
echo "============================================================"
echo ""
log_info "확인 사항:"
echo "  1. git log --oneline -5      # 커밋 이력 확인"
echo "  2. curl https://dsp114.co.kr  # 프로덕션 정상 확인"
echo "  3. 프로덕션 DB에서 site_settings 테이블 확인"
echo ""
