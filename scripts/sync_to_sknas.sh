#!/bin/bash
#
# Git → ipTIME NAS FTP 동기화 스크립트
# 두손기획인쇄 - sknas205.ipdisk.co.kr (ipTIME NAS2 Dual Plus)
#
# 사용법:
#   ./scripts/sync_to_sknas.sh                    # 전체 파일 미러링
#   ./scripts/sync_to_sknas.sh --dry-run          # 미리보기 (실제 업로드 없음)
#   ./scripts/sync_to_sknas.sh --changed          # Git 마지막 커밋 변경분만
#   ./scripts/sync_to_sknas.sh --changed HEAD~3   # 최근 3커밋 변경분만
#   ./scripts/sync_to_sknas.sh --file <path>      # 특정 파일만 업로드
#

set -e

# Git safe.directory (www-data에서 실행 시 필요)
# LOCAL_ROOT가 설정된 후에 적용 (아래 참조)

# ============================================
# NAS FTP 설정
# ============================================
NAS_HOST="${NAS_HOST:-sknas205.ipdisk.co.kr}"
NAS_USER="${NAS_USER:-sknas205}"
NAS_PASS="${NAS_PASS:-sknas205204203}"
NAS_ROOT="${NAS_ROOT:-/HDD1/duson260118}"
NAS_PORT="${NAS_PORT:-21}"

# 로컬 웹루트 (환경변수로 오버라이드 가능)
LOCAL_ROOT="${LOCAL_ROOT:-/var/www/html}"

# Git safe.directory 동적 설정
export GIT_CONFIG_COUNT=1
export GIT_CONFIG_KEY_0=safe.directory
export GIT_CONFIG_VALUE_0="$LOCAL_ROOT"

# 제외할 파일/디렉토리
EXCLUDE_PATTERNS=(
    "^\.git/"
    "^node_modules/"
    "^test-results/"
    "^playwright-report/"
    "^\.claude/"
    "^CLAUDE_DOCS/"
    "\.tar$"
    "\.zip$"
    "\.tar\.gz$"
    "^\.gitignore$"
    "^\.gitattributes$"
    "^package-lock\.json$"
    "^playwright\.config\."
    "^tests/"
)
# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ============================================
# 함수 정의
# ============================================

print_header() {
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}  Git → ipTIME NAS FTP 동기화${NC}"
    echo -e "${BLUE}  Host: ${NAS_HOST}${NC}"
    echo -e "${BLUE}  Path: ${NAS_ROOT}${NC}"
    echo -e "${BLUE}============================================${NC}"
    echo ""
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_error()   { echo -e "${RED}✗ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_info()    { echo -e "${BLUE}→ $1${NC}"; }

# FTP 연결 테스트
test_ftp_connection() {
    print_info "FTP 연결 테스트 중..."
    if curl -s --connect-timeout 5 "ftp://${NAS_HOST}/" --user "${NAS_USER}:${NAS_PASS}" > /dev/null 2>&1; then
        print_success "FTP 연결 성공"
        return 0
    else
        print_error "FTP 연결 실패"
        return 1
    fi
}

# lftp exclude 옵션 생성
build_exclude_opts() {
    local opts=""
    for pattern in "${EXCLUDE_PATTERNS[@]}"; do
        opts="$opts -x $pattern"
    done
    echo "$opts"
}

# lftp mirror로 전체 동기화
sync_mirror() {
    local dry_run="$1"
    local exclude_opts=$(build_exclude_opts)
    local dry_flag=""

    if [ "$dry_run" = "true" ]; then
        dry_flag="--dry-run"
        print_warning "DRY-RUN 모드: 실제 업로드 없이 미리보기만 합니다"
        echo ""
    fi

    print_info "lftp mirror 동기화 시작..."
    echo ""

    lftp -c "
        set ftp:charset UTF-8
        set file:charset UTF-8
        set net:timeout 10
        set net:max-retries 3
        set net:reconnect-interval-base 5
        open -u ${NAS_USER},${NAS_PASS} ftp://${NAS_HOST}
        mirror --reverse --verbose --only-newer --no-perms \
            ${dry_flag} \
            ${exclude_opts} \
            ${LOCAL_ROOT}/ ${NAS_ROOT}/
    "

    return $?
}

# curl로 단일 파일 업로드
upload_file() {
    local file="$1"
    local dry_run="$2"
    local rel_path="${file#$LOCAL_ROOT/}"
    local remote_path="${NAS_ROOT}/${rel_path}"

    if [ "$dry_run" = "true" ]; then
        echo "  [DRY-RUN] $rel_path → $remote_path"
        return 0
    fi

    if curl -s -T "$file" "ftp://${NAS_HOST}${remote_path}" \
        --user "${NAS_USER}:${NAS_PASS}" --ftp-create-dirs 2>/dev/null; then
        print_success "$rel_path"
        return 0
    else
        print_error "$rel_path (업로드 실패)"
        return 1
    fi
}

# Git 변경분만 동기화
sync_changed() {
    local since="$1"
    local dry_run="$2"

    cd "$LOCAL_ROOT"

    local changes
    if [ -z "$since" ]; then
        changes=$(git diff-tree --no-commit-id --name-status -r HEAD)
    elif [[ "$since" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
        # 날짜 형식(YYYY-MM-DD)이면 --since 사용
        changes=$(git log --since="$since" --name-status --pretty=format: HEAD | grep -v '^$' | sort -t$'\t' -k2 -u)
    else
        # 커밋 참조(HEAD~3, SHA 등)이면 기존 방식
        changes=$(git diff --name-status "$since" HEAD)
    fi

    if [ -z "$changes" ]; then
        print_warning "변경된 파일이 없습니다"
        return 0
    fi

    local total=0 uploaded=0 failed=0

    while IFS=$'\t' read -r status file; do
        total=$((total + 1))
        local local_file="${LOCAL_ROOT}/${file}"

        # 제외 패턴 체크
        local skip=false
        for pattern in "${EXCLUDE_PATTERNS[@]}"; do
            if [[ "$file" == ${pattern}* ]] || [[ "$file" == *"${pattern}" ]]; then
                skip=true
                break
            fi
        done
        if [ "$skip" = "true" ]; then
            continue
        fi

        case "$status" in
            A|M|C|R*)
                if [ -f "$local_file" ]; then
                    if upload_file "$local_file" "$dry_run"; then
                        uploaded=$((uploaded + 1))
                    else
                        failed=$((failed + 1))
                    fi
                fi
                ;;
            D)
                if [ "$dry_run" = "true" ]; then
                    echo "  [DRY-RUN] DELETE: $file"
                fi
                ;;
        esac
    done <<< "$changes"

    echo ""
    echo "  총 파일: $total | 업로드: $uploaded | 실패: $failed"
}

# 사용법
show_usage() {
    echo "사용법: $0 [옵션]"
    echo ""
    echo "옵션:"
    echo "  (없음)                 전체 파일 미러링 (lftp)"
    echo "  --dry-run              미리보기 (실제 업로드 없음)"
    echo "  --changed [commit]     Git 변경분만 업로드"
    echo "  --file <path>          특정 파일만 업로드"
    echo "  --help                 도움말"
    echo ""
    echo "예시:"
    echo "  $0                     전체 동기화"
    echo "  $0 --dry-run           전체 미리보기"
    echo "  $0 --changed           마지막 커밋 변경분"
    echo "  $0 --changed HEAD~3   최근 3커밋 변경분"
    echo "  $0 --file payment/inicis_return.php"
}

# ============================================
# 메인 로직
# ============================================

DRY_RUN="false"
MODE="mirror"  # mirror | changed | file
SINCE_COMMIT=""
SPECIFIC_FILE=""

while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN="true"
            shift
            ;;
        --changed)
            MODE="changed"
            shift
            if [[ $# -gt 0 && "$1" != --* ]]; then
                SINCE_COMMIT="$1"
                shift
            fi
            ;;
        --file)
            MODE="file"
            SPECIFIC_FILE="$2"
            shift 2
            ;;
        --help|-h)
            show_usage
            exit 0
            ;;
        *)
            echo "알 수 없는 옵션: $1"
            show_usage
            exit 1
            ;;
    esac
done

# 헤더
print_header

# FTP 연결 테스트
if ! test_ftp_connection; then
    exit 1
fi
echo ""

# 모드별 실행
case "$MODE" in
    mirror)
        print_info "전체 미러링 모드 (lftp)"
        sync_mirror "$DRY_RUN"
        ;;
    changed)
        if [ -n "$SINCE_COMMIT" ]; then
            print_info "Git 변경분 동기화 (기준: $SINCE_COMMIT)"
        else
            print_info "Git 변경분 동기화 (마지막 커밋)"
        fi
        sync_changed "$SINCE_COMMIT" "$DRY_RUN"
        ;;
    file)
        local_file="${LOCAL_ROOT}/${SPECIFIC_FILE}"
        if [ ! -f "$local_file" ]; then
            print_error "파일을 찾을 수 없습니다: $SPECIFIC_FILE"
            exit 1
        fi
        print_info "단일 파일 업로드: $SPECIFIC_FILE"
        upload_file "$local_file" "$DRY_RUN"
        ;;
esac

echo ""
if [ "$DRY_RUN" = "true" ]; then
    print_warning "DRY-RUN 모드였습니다. 실제 변경 없음."
    echo "실제 동기화: $0 ${@/--dry-run/}"
fi

print_success "완료!"
