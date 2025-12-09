#!/bin/bash
#
# 두손기획인쇄 원클릭 설치 스크립트
#
# 사용법:
#   curl -sL http://your-server/install/quick_install.sh | bash
#   또는
#   wget -qO- http://your-server/install/quick_install.sh | bash
#   또는
#   bash quick_install.sh
#

set -e

# 색상 정의
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color
BOLD='\033[1m'

# 기본 설정
DEFAULT_DB_HOST="localhost"
DEFAULT_DB_NAME="dsp1830"
DEFAULT_DB_USER="dsp1830"
DEFAULT_ADMIN_ID="admin"
DEFAULT_COMPANY_NAME="두손기획인쇄"
DEFAULT_COMPANY_PHONE="1688-2384"

# 배너 출력
show_banner() {
    echo -e "${CYAN}${BOLD}"
    echo "╔══════════════════════════════════════════════════════════════╗"
    echo "║                                                              ║"
    echo "║     🖨️  두손기획인쇄 원클릭 설치                              ║"
    echo "║                                                              ║"
    echo "║     Enterprise Print Management System                       ║"
    echo "║                                                              ║"
    echo "╚══════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo ""
}

# 로그 함수
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 시스템 요구사항 확인
check_requirements() {
    log_info "시스템 요구사항 확인 중..."

    # PHP 확인
    if ! command -v php &> /dev/null; then
        log_error "PHP가 설치되어 있지 않습니다."
        echo "  설치 방법: sudo apt install php php-mysql php-mbstring php-gd php-curl"
        exit 1
    fi

    PHP_VERSION=$(php -r 'echo PHP_VERSION;')
    log_success "PHP $PHP_VERSION 발견"

    # MySQL 확인
    if ! command -v mysql &> /dev/null; then
        log_warning "MySQL CLI가 설치되어 있지 않습니다."
        echo "  설치 방법: sudo apt install mysql-client"
    fi

    # Apache/Nginx 확인
    if command -v apache2 &> /dev/null || command -v httpd &> /dev/null; then
        log_success "Apache 웹서버 발견"
    elif command -v nginx &> /dev/null; then
        log_success "Nginx 웹서버 발견"
    else
        log_warning "웹서버를 찾을 수 없습니다."
    fi

    echo ""
}

# 설정 입력 받기
get_config() {
    echo -e "${CYAN}━━━ 설치 설정 ━━━${NC}"
    echo ""

    # 데이터베이스 설정
    read -p "데이터베이스 호스트 [$DEFAULT_DB_HOST]: " DB_HOST
    DB_HOST=${DB_HOST:-$DEFAULT_DB_HOST}

    read -p "데이터베이스 이름 [$DEFAULT_DB_NAME]: " DB_NAME
    DB_NAME=${DB_NAME:-$DEFAULT_DB_NAME}

    read -p "데이터베이스 사용자 [$DEFAULT_DB_USER]: " DB_USER
    DB_USER=${DB_USER:-$DEFAULT_DB_USER}

    read -sp "데이터베이스 비밀번호: " DB_PASS
    echo ""

    # 관리자 설정
    echo ""
    read -p "관리자 ID [$DEFAULT_ADMIN_ID]: " ADMIN_ID
    ADMIN_ID=${ADMIN_ID:-$DEFAULT_ADMIN_ID}

    read -sp "관리자 비밀번호 (6자 이상): " ADMIN_PASS
    echo ""

    read -p "관리자 이름: " ADMIN_NAME
    ADMIN_NAME=${ADMIN_NAME:-"관리자"}

    read -p "관리자 이메일: " ADMIN_EMAIL
    ADMIN_EMAIL=${ADMIN_EMAIL:-"admin@example.com"}

    # 회사 정보
    echo ""
    read -p "회사명 [$DEFAULT_COMPANY_NAME]: " COMPANY_NAME
    COMPANY_NAME=${COMPANY_NAME:-$DEFAULT_COMPANY_NAME}

    read -p "대표 전화 [$DEFAULT_COMPANY_PHONE]: " COMPANY_PHONE
    COMPANY_PHONE=${COMPANY_PHONE:-$DEFAULT_COMPANY_PHONE}

    echo ""
}

# 설정 파일 생성
create_config_json() {
    log_info "설정 파일 생성 중..."

    INSTALL_DIR=$(dirname "$(readlink -f "$0")")

    cat > "$INSTALL_DIR/install_config.json" << EOF
{
    "db_host": "$DB_HOST",
    "db_name": "$DB_NAME",
    "db_user": "$DB_USER",
    "db_pass": "$DB_PASS",
    "admin_id": "$ADMIN_ID",
    "admin_pass": "$ADMIN_PASS",
    "admin_name": "$ADMIN_NAME",
    "admin_email": "$ADMIN_EMAIL",
    "company_name": "$COMPANY_NAME",
    "company_phone": "$COMPANY_PHONE"
}
EOF

    log_success "설정 파일 생성 완료"
}

# PHP CLI 설치 실행
run_php_install() {
    log_info "PHP 설치 스크립트 실행 중..."

    INSTALL_DIR=$(dirname "$(readlink -f "$0")")

    if [ -f "$INSTALL_DIR/cli_install.php" ]; then
        php "$INSTALL_DIR/cli_install.php" --config="$INSTALL_DIR/install_config.json"
    else
        log_error "cli_install.php 파일을 찾을 수 없습니다."
        exit 1
    fi
}

# 정리
cleanup() {
    INSTALL_DIR=$(dirname "$(readlink -f "$0")")

    if [ -f "$INSTALL_DIR/install_config.json" ]; then
        rm -f "$INSTALL_DIR/install_config.json"
        log_info "임시 설정 파일 삭제됨"
    fi
}

# 도움말
show_help() {
    echo "사용법: $0 [옵션]"
    echo ""
    echo "옵션:"
    echo "  --auto          자동 설치 (기본값 사용)"
    echo "  --help, -h      이 도움말 표시"
    echo ""
    echo "예시:"
    echo "  $0              대화형 설치"
    echo "  $0 --auto       자동 설치"
    echo ""
}

# 메인 실행
main() {
    show_banner

    # 인수 처리
    case "${1:-}" in
        --help|-h)
            show_help
            exit 0
            ;;
        --auto)
            log_info "자동 설치 모드"
            check_requirements
            # 자동 모드에서는 기본값 사용
            DB_HOST=$DEFAULT_DB_HOST
            DB_NAME=$DEFAULT_DB_NAME
            DB_USER=$DEFAULT_DB_USER
            DB_PASS=""
            ADMIN_ID=$DEFAULT_ADMIN_ID
            ADMIN_PASS=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9' | head -c 12)
            ADMIN_NAME="관리자"
            ADMIN_EMAIL="admin@example.com"
            COMPANY_NAME=$DEFAULT_COMPANY_NAME
            COMPANY_PHONE=$DEFAULT_COMPANY_PHONE

            echo -e "${YELLOW}자동 생성된 관리자 비밀번호: $ADMIN_PASS${NC}"
            echo ""
            ;;
        *)
            check_requirements
            get_config
            ;;
    esac

    create_config_json
    run_php_install
    cleanup

    echo ""
    log_success "설치가 완료되었습니다!"
    echo ""
    echo -e "${CYAN}접속 URL:${NC}"
    echo "  사이트: http://localhost/"
    echo "  관리자: http://localhost/admin/"
    echo ""
    echo -e "${YELLOW}⚠️  보안 권장: 설치 완료 후 /install/ 폴더를 삭제하세요.${NC}"
    echo ""
}

# 종료 시 정리
trap cleanup EXIT

# 실행
main "$@"
