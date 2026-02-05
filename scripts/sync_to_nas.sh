#!/bin/bash
#
# Git → NAS FTP 동기화 스크립트
# 두손기획인쇄 - dsp1830.ipdisk.co.kr
#
# 사용법:
#   ./scripts/sync_to_nas.sh              # 마지막 커밋 이후 변경된 파일 동기화
#   ./scripts/sync_to_nas.sh HEAD~3       # 최근 3개 커밋의 변경 파일 동기화
#   ./scripts/sync_to_nas.sh abc123       # 특정 커밋 이후 변경 파일 동기화
#   ./scripts/sync_to_nas.sh --dry-run    # 실제 업로드 없이 미리보기
#   ./scripts/sync_to_nas.sh --file payment/inicis_return.php  # 특정 파일만 업로드
#

set -e

# ============================================
# NAS FTP 설정
# ============================================
NAS_HOST="dsp1830.ipdisk.co.kr"
NAS_USER="admin"
NAS_PASS="1830"
NAS_ROOT="/HDD2/share"
NAS_PORT="21"

# 로컬 Git 저장소 루트
LOCAL_ROOT="/var/www/html"

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# ============================================
# 함수 정의
# ============================================

print_header() {
    echo -e "${BLUE}============================================${NC}"
    echo -e "${BLUE}  Git → NAS FTP 동기화${NC}"
    echo -e "${BLUE}  Host: ${NAS_HOST}${NC}"
    echo -e "${BLUE}  Path: ${NAS_ROOT}${NC}"
    echo -e "${BLUE}============================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_info() {
    echo -e "${BLUE}→ $1${NC}"
}

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

# FTP로 단일 파일 업로드
upload_file() {
    local file="$1"
    local dry_run="$2"
    
    # 상대 경로 계산
    local rel_path="${file#$LOCAL_ROOT/}"
    local remote_path="${NAS_ROOT}/${rel_path}"
    local remote_dir=$(dirname "$remote_path")
    
    if [ "$dry_run" = "true" ]; then
        echo "  [DRY-RUN] $rel_path → $remote_path"
        return 0
    fi
    
    # 원격 디렉토리 생성 (필요 시)
    # FTP는 자동으로 디렉토리를 만들지 않으므로, 에러 무시하고 시도
    
    # 파일 업로드
    if curl -s -T "$file" "ftp://${NAS_HOST}${remote_path}" --user "${NAS_USER}:${NAS_PASS}" --ftp-create-dirs 2>/dev/null; then
        print_success "$rel_path"
        return 0
    else
        print_error "$rel_path (업로드 실패)"
        return 1
    fi
}

# FTP로 파일 삭제
delete_file() {
    local file="$1"
    local dry_run="$2"
    
    local rel_path="${file#$LOCAL_ROOT/}"
    local remote_path="${NAS_ROOT}/${rel_path}"
    
    if [ "$dry_run" = "true" ]; then
        echo "  [DRY-RUN] DELETE: $rel_path"
        return 0
    fi
    
    # FTP DELE 명령으로 파일 삭제
    if curl -s -Q "DELE ${remote_path}" "ftp://${NAS_HOST}/" --user "${NAS_USER}:${NAS_PASS}" 2>/dev/null; then
        print_warning "삭제됨: $rel_path"
        return 0
    else
        print_error "삭제 실패: $rel_path"
        return 1
    fi
}

# Git 변경 파일 목록 가져오기
get_changed_files() {
    local since="$1"
    
    cd "$LOCAL_ROOT"
    
    if [ -z "$since" ]; then
        # 마지막 커밋의 변경 파일
        git diff-tree --no-commit-id --name-status -r HEAD
    else
        # 특정 커밋 이후 변경 파일
        git diff --name-status "$since" HEAD
    fi
}

# 사용법 출력
show_usage() {
    echo "사용법: $0 [옵션] [커밋ID]"
    echo ""
    echo "옵션:"
    echo "  --dry-run              실제 업로드 없이 미리보기"
    echo "  --file <path>          특정 파일만 업로드"
    echo "  --since <commit>       특정 커밋 이후 변경 파일 동기화"
    echo "  --last <n>             최근 n개 커밋의 변경 파일 동기화"
    echo "  --all                  추적 중인 모든 파일 동기화 (주의!)"
    echo "  --help                 이 도움말 출력"
    echo ""
    echo "예시:"
    echo "  $0                     마지막 커밋의 변경 파일 동기화"
    echo "  $0 --last 3            최근 3개 커밋 변경 파일"
    echo "  $0 --since abc123      특정 커밋 이후 변경"
    echo "  $0 --file payment/inicis_return.php  특정 파일만"
    echo "  $0 --dry-run           미리보기 모드"
}

# ============================================
# 메인 로직
# ============================================

DRY_RUN="false"
SINCE_COMMIT=""
SPECIFIC_FILE=""
SYNC_ALL="false"
LAST_N=""

# 인자 파싱
while [[ $# -gt 0 ]]; do
    case $1 in
        --dry-run)
            DRY_RUN="true"
            shift
            ;;
        --file)
            SPECIFIC_FILE="$2"
            shift 2
            ;;
        --since)
            SINCE_COMMIT="$2"
            shift 2
            ;;
        --last)
            LAST_N="$2"
            shift 2
            ;;
        --all)
            SYNC_ALL="true"
            shift
            ;;
        --help|-h)
            show_usage
            exit 0
            ;;
        *)
            # 커밋 ID로 간주
            SINCE_COMMIT="$1"
            shift
            ;;
    esac
done

# 헤더 출력
print_header

# DRY RUN 모드 안내
if [ "$DRY_RUN" = "true" ]; then
    print_warning "DRY-RUN 모드: 실제 업로드 없이 미리보기만 합니다"
    echo ""
fi

# FTP 연결 테스트 (dry-run이 아닐 때만)
if [ "$DRY_RUN" != "true" ]; then
    if ! test_ftp_connection; then
        exit 1
    fi
    echo ""
fi

# 특정 파일 업로드 모드
if [ -n "$SPECIFIC_FILE" ]; then
    local_file="${LOCAL_ROOT}/${SPECIFIC_FILE}"
    
    if [ ! -f "$local_file" ]; then
        print_error "파일을 찾을 수 없습니다: $SPECIFIC_FILE"
        exit 1
    fi
    
    print_info "단일 파일 업로드: $SPECIFIC_FILE"
    upload_file "$local_file" "$DRY_RUN"
    exit $?
fi

# Git 디렉토리 확인
cd "$LOCAL_ROOT"
if [ ! -d ".git" ]; then
    print_error "Git 저장소가 아닙니다: $LOCAL_ROOT"
    exit 1
fi

# 변경 파일 목록 가져오기
print_info "변경 파일 목록 조회 중..."

if [ -n "$LAST_N" ]; then
    SINCE_COMMIT="HEAD~${LAST_N}"
fi

if [ "$SYNC_ALL" = "true" ]; then
    print_warning "모든 추적 파일 동기화 모드"
    CHANGED_FILES=$(git ls-files)
    MODE="ADD"
else
    if [ -n "$SINCE_COMMIT" ]; then
        print_info "기준 커밋: $SINCE_COMMIT"
    else
        print_info "마지막 커밋 변경 사항"
    fi
    CHANGED_FILES=$(get_changed_files "$SINCE_COMMIT")
fi

if [ -z "$CHANGED_FILES" ]; then
    print_warning "변경된 파일이 없습니다"
    exit 0
fi

# 통계
TOTAL=0
UPLOADED=0
DELETED=0
FAILED=0

echo ""
print_info "동기화 시작..."
echo "----------------------------------------"

# 파일별 처리
if [ "$SYNC_ALL" = "true" ]; then
    # 모든 파일 업로드
    while IFS= read -r file; do
        local_file="${LOCAL_ROOT}/${file}"
        if [ -f "$local_file" ]; then
            TOTAL=$((TOTAL + 1))
            if upload_file "$local_file" "$DRY_RUN"; then
                UPLOADED=$((UPLOADED + 1))
            else
                FAILED=$((FAILED + 1))
            fi
        fi
    done <<< "$CHANGED_FILES"
else
    # 변경된 파일만 처리
    while IFS=$'\t' read -r status file; do
        TOTAL=$((TOTAL + 1))
        local_file="${LOCAL_ROOT}/${file}"
        
        case "$status" in
            A|M|C|R*)
                # Added, Modified, Copied, Renamed - 업로드
                if [ -f "$local_file" ]; then
                    if upload_file "$local_file" "$DRY_RUN"; then
                        UPLOADED=$((UPLOADED + 1))
                    else
                        FAILED=$((FAILED + 1))
                    fi
                else
                    print_warning "파일 없음 (스킵): $file"
                fi
                ;;
            D)
                # Deleted - 삭제
                if delete_file "$local_file" "$DRY_RUN"; then
                    DELETED=$((DELETED + 1))
                else
                    FAILED=$((FAILED + 1))
                fi
                ;;
            *)
                print_warning "알 수 없는 상태 ($status): $file"
                ;;
        esac
    done <<< "$CHANGED_FILES"
fi

echo "----------------------------------------"
echo ""

# 결과 요약
print_info "동기화 완료"
echo "  총 파일: $TOTAL"
echo "  업로드: $UPLOADED"
echo "  삭제: $DELETED"
echo "  실패: $FAILED"

if [ "$DRY_RUN" = "true" ]; then
    echo ""
    print_warning "DRY-RUN 모드였습니다. 실제 변경 없음."
    echo "실제 동기화: $0 ${@/--dry-run/}"
fi

if [ $FAILED -gt 0 ]; then
    exit 1
fi

exit 0
