#!/bin/bash
# ============================================================
# 두손기획인쇄 — 재난 복구 스크립트
# Duson Planning Print System — Disaster Recovery Restore
# ============================================================
#
# 사용법:
#   ./disaster_restore.sh --password "복원암호" [옵션]
#
# 옵션:
#   --password PASS   인증파일 복호화 암호 (필수)
#   --source DIR      DR 패키지 폴더 (기본: ./latest)
#   --target DIR      복원 대상 웹루트 (기본: /var/www/html)
#   --db-only         DB만 복원
#   --code-only       소스코드만 복원
#   --skip-db         DB 복원 건너뛰기
#   --dry-run         실제 복원 없이 검증만
#   --help            도움말
#
# 전제조건:
#   - Ubuntu/Debian 계열 서버
#   - root 또는 sudo 권한
#   - NAS에서 disaster_recovery/latest/ 폴더를 다운로드한 상태
#
# PHP 버전 참고:
#   프로덕션: PHP 8.2 ← 이 버전으로 설치해야 함
#   NAS: PHP 7.3 (NAS 전용, 복원 대상 아님)
#   로컬개발: PHP 7.4
# ============================================================

set -euo pipefail

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# ─── 기본값 ───
PASSWORD=""
SOURCE_DIR="./latest"
TARGET_DIR="/var/www/html"
DB_ONLY=false
CODE_ONLY=false
SKIP_DB=false
DRY_RUN=false

# ─── 인수 파싱 ───
while [[ $# -gt 0 ]]; do
    case $1 in
        --password)  PASSWORD="$2"; shift 2 ;;
        --source)    SOURCE_DIR="$2"; shift 2 ;;
        --target)    TARGET_DIR="$2"; shift 2 ;;
        --db-only)   DB_ONLY=true; shift ;;
        --code-only) CODE_ONLY=true; shift ;;
        --skip-db)   SKIP_DB=true; shift ;;
        --dry-run)   DRY_RUN=true; shift ;;
        --help|-h)
            head -30 "$0" | grep "^#" | sed 's/^# //'
            exit 0
            ;;
        *) echo -e "${RED}알 수 없는 옵션: $1${NC}"; exit 1 ;;
    esac
done

# ─── 검증 ───
if [ -z "$PASSWORD" ]; then
    echo -e "${RED}❌ --password 필수입니다${NC}"
    echo "사용법: ./disaster_restore.sh --password \"복원암호\""
    exit 1
fi

if [ ! -d "$SOURCE_DIR" ]; then
    echo -e "${RED}❌ DR 패키지 폴더를 찾을 수 없습니다: $SOURCE_DIR${NC}"
    echo "NAS에서 disaster_recovery/latest/ 폴더를 먼저 다운로드하세요."
    exit 1
fi

log() { echo -e "${BLUE}[$(date '+%H:%M:%S')]${NC} $1"; }
ok()  { echo -e "${GREEN}  ✅ $1${NC}"; }
warn(){ echo -e "${YELLOW}  ⚠️  $1${NC}"; }
err() { echo -e "${RED}  ❌ $1${NC}"; }

echo ""
echo "============================================================"
echo "  두손기획인쇄 — 재난 복구 시작"
echo "  Duson Planning Print System — Disaster Recovery"
echo "============================================================"
echo ""
echo "  소스: $SOURCE_DIR"
echo "  대상: $TARGET_DIR"
[ "$DRY_RUN" = true ] && echo -e "  ${YELLOW}DRY-RUN 모드 (실제 변경 없음)${NC}"
echo ""

# ─── 매니페스트 확인 ───
log "📋 매니페스트 확인..."
if [ -f "$SOURCE_DIR/manifest.json" ]; then
    ok "매니페스트 발견"
    # 간단히 생성일 출력
    grep '"created"' "$SOURCE_DIR/manifest.json" 2>/dev/null | head -1 || true
else
    warn "매니페스트 없음 — 파일 무결성 검증 건너뜀"
fi

# ============================================================
# 1단계: 시스템 패키지 설치
# ============================================================
if [ "$DB_ONLY" = false ] && [ "$DRY_RUN" = false ]; then
    log "📦 [1/6] 시스템 패키지 확인..."

    # PHP 8.2 설치 (프로덕션 환경 일치)
    if ! php -v 2>/dev/null | grep -q "8.2"; then
        log "  PHP 8.2 설치 중..."
        if command -v apt-get &>/dev/null; then
            sudo add-apt-repository -y ppa:ondrej/php 2>/dev/null || true
            sudo apt-get update -qq
            sudo apt-get install -y -qq \
                php8.2 php8.2-fpm php8.2-mysql php8.2-gd php8.2-curl \
                php8.2-mbstring php8.2-xml php8.2-zip php8.2-intl \
                apache2 libapache2-mod-php8.2 2>/dev/null
            ok "PHP 8.2 설치 완료"
        else
            warn "apt-get 없음 — PHP 8.2를 수동 설치하세요"
        fi
    else
        ok "PHP 8.2 이미 설치됨"
    fi

    # MySQL 설치
    if ! command -v mysql &>/dev/null; then
        log "  MySQL 설치 중..."
        sudo apt-get install -y -qq mysql-server 2>/dev/null
        sudo systemctl start mysql
        ok "MySQL 설치 완료"
    else
        ok "MySQL 이미 설치됨"
    fi

    # lftp (NAS 동기화용)
    if ! command -v lftp &>/dev/null; then
        sudo apt-get install -y -qq lftp 2>/dev/null
        ok "lftp 설치 완료"
    fi
else
    log "📦 [1/6] 시스템 패키지 확인 (건너뜀)"
fi

# ============================================================
# 2단계: DB 복원
# ============================================================
if [ "$CODE_ONLY" = false ] && [ "$SKIP_DB" = false ]; then
    log "🗄️ [2/6] DB 복원..."

    DB_FILE=$(ls "$SOURCE_DIR"/db_full_*.sql.gz 2>/dev/null | head -1)
    if [ -n "$DB_FILE" ] && [ -f "$DB_FILE" ]; then
        if [ "$DRY_RUN" = true ]; then
            ok "DB 덤프 발견: $(basename "$DB_FILE") ($(du -sh "$DB_FILE" | cut -f1))"
        else
            # DB 유저 생성 (없으면)
            sudo mysql -e "CREATE DATABASE IF NOT EXISTS dsp1830 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null || true
            sudo mysql -e "CREATE USER IF NOT EXISTS 'dsp1830'@'localhost' IDENTIFIED BY 'ds701018';" 2>/dev/null || true
            sudo mysql -e "GRANT ALL PRIVILEGES ON dsp1830.* TO 'dsp1830'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null || true

            log "  DB import 중... (시간이 걸릴 수 있습니다)"
            gunzip -c "$DB_FILE" | mysql -u dsp1830 -pds701018 dsp1830 2>/dev/null

            TABLES=$(mysql -u dsp1830 -pds701018 dsp1830 -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='dsp1830'" 2>/dev/null || echo "?")
            ok "DB 복원 완료 (${TABLES}개 테이블)"
        fi
    else
        warn "DB 덤프 파일 없음 — 건너뜀"
    fi
else
    log "🗄️ [2/6] DB 복원 (건너뜀)"
fi

# ============================================================
# 3단계: 소스코드 복원
# ============================================================
if [ "$DB_ONLY" = false ]; then
    log "📂 [3/6] 소스코드 복원..."

    SRC_FILE=$(ls "$SOURCE_DIR"/source_code_*.tar.gz 2>/dev/null | head -1)
    if [ -n "$SRC_FILE" ] && [ -f "$SRC_FILE" ]; then
        if [ "$DRY_RUN" = true ]; then
            ok "소스코드 발견: $(basename "$SRC_FILE") ($(du -sh "$SRC_FILE" | cut -f1))"
        else
            mkdir -p "$TARGET_DIR"
            tar xzf "$SRC_FILE" -C "$TARGET_DIR" 2>/dev/null
            ok "소스코드 복원 완료 → $TARGET_DIR"
        fi
    else
        warn "소스코드 아카이브 없음"
    fi
else
    log "📂 [3/6] 소스코드 복원 (건너뜀)"
fi

# ============================================================
# 4단계: 인증파일 복원 (복호화)
# ============================================================
if [ "$DB_ONLY" = false ]; then
    log "🔐 [4/6] 인증파일 복원..."

    CRED_FILE=$(ls "$SOURCE_DIR"/credentials_*.tar.gz.enc 2>/dev/null | head -1)
    if [ -n "$CRED_FILE" ] && [ -f "$CRED_FILE" ]; then
        CRED_TAR="/tmp/dr_credentials.tar.gz"

        if [ "$DRY_RUN" = true ]; then
            # 복호화 테스트만
            openssl enc -aes-256-cbc -d -salt -pbkdf2 \
                -in "$CRED_FILE" -out "$CRED_TAR" \
                -pass "pass:${PASSWORD}" 2>/dev/null

            if [ $? -eq 0 ] && [ -s "$CRED_TAR" ]; then
                ok "인증파일 복호화 성공 (dry-run)"
            else
                err "인증파일 복호화 실패 — 암호를 확인하세요"
            fi
            rm -f "$CRED_TAR"
        else
            openssl enc -aes-256-cbc -d -salt -pbkdf2 \
                -in "$CRED_FILE" -out "$CRED_TAR" \
                -pass "pass:${PASSWORD}" 2>/dev/null

            if [ $? -eq 0 ] && [ -s "$CRED_TAR" ]; then
                tar xzf "$CRED_TAR" -C "$TARGET_DIR" 2>/dev/null
                ok "인증파일 복원 완료"
            else
                err "인증파일 복호화 실패 — 암호를 확인하세요"
            fi
            rm -f "$CRED_TAR"
        fi
    else
        warn "인증파일 없음"
    fi
else
    log "🔐 [4/6] 인증파일 복원 (건너뜀)"
fi

# ============================================================
# 5단계: .htaccess + 서버 설정 복원
# ============================================================
if [ "$DB_ONLY" = false ]; then
    log "⚙️ [5/6] 서버 설정 복원..."

    # .htaccess 복원
    HT_FILE=$(ls "$SOURCE_DIR"/htaccess_*.tar.gz 2>/dev/null | head -1)
    if [ -n "$HT_FILE" ] && [ -f "$HT_FILE" ] && [ "$DRY_RUN" = false ]; then
        tar xzf "$HT_FILE" -C / 2>/dev/null || tar xzf "$HT_FILE" -C "$TARGET_DIR" 2>/dev/null
        ok ".htaccess 파일 복원"
    fi

    # 서버 설정 복원 (참조용 — 수동 적용 필요)
    SVR_FILE=$(ls "$SOURCE_DIR"/server_config_*.tar.gz 2>/dev/null | head -1)
    if [ -n "$SVR_FILE" ] && [ -f "$SVR_FILE" ] && [ "$DRY_RUN" = false ]; then
        mkdir -p "$TARGET_DIR/scripts/restored_server_config"
        tar xzf "$SVR_FILE" -C "$TARGET_DIR/scripts/restored_server_config" 2>/dev/null
        ok "서버 설정 추출 → scripts/restored_server_config/"
        warn "Apache/PHP 설정은 수동 확인 후 적용하세요"
    fi

    # crontab 복원
    if [ -f "$TARGET_DIR/scripts/restored_server_config/crontab.txt" ] && [ "$DRY_RUN" = false ]; then
        log "  crontab 복원..."
        crontab "$TARGET_DIR/scripts/restored_server_config/crontab.txt" 2>/dev/null
        ok "crontab 복원 완료"
    fi
else
    log "⚙️ [5/6] 서버 설정 복원 (건너뜀)"
fi

# ============================================================
# 6단계: ImgFolder + 업로드 파일 복원
# ============================================================
if [ "$DB_ONLY" = false ]; then
    log "🖼️ [6/6] 업로드 파일 복원..."

    # ImgFolder (최근 1개월)
    IMG_FILE=$(ls "$SOURCE_DIR"/imgfolder_recent_*.tar.gz 2>/dev/null | head -1)
    if [ -n "$IMG_FILE" ] && [ -f "$IMG_FILE" ] && [ "$DRY_RUN" = false ]; then
        tar xzf "$IMG_FILE" -C / 2>/dev/null || tar xzf "$IMG_FILE" -C "$TARGET_DIR" 2>/dev/null
        ok "ImgFolder 복원 완료"
    fi

    # 교정이미지는 NAS에서 별도 sync 필요
    warn "교정이미지(upload/)는 NAS에서 sync 필요:"
    warn "  lftp -c 'open -u admin,1830 ftp://dsp1830.ipdisk.co.kr"
    warn "  mirror /HDD2/share/mlangorder_printauto/upload/ ${TARGET_DIR}/mlangorder_printauto/upload/'"
else
    log "🖼️ [6/6] 업로드 파일 복원 (건너뜀)"
fi

# ============================================================
# 권한 설정
# ============================================================
if [ "$DRY_RUN" = false ] && [ "$DB_ONLY" = false ]; then
    log "🔧 권한 설정..."
    chown -R www-data:www-data "$TARGET_DIR" 2>/dev/null || true
    find "$TARGET_DIR/scripts" -name "*.sh" -exec chmod +x {} \; 2>/dev/null || true
    ok "파일 권한 설정 완료"
fi

# ============================================================
# 완료 + 수동 작업 안내
# ============================================================
echo ""
echo "============================================================"
echo -e "${GREEN}  ✅ 복원 완료!${NC}"
echo "============================================================"
echo ""
echo "📋 수동 확인 필요 사항:"
echo ""
echo "  1. Apache 가상호스트 설정:"
echo "     sudo nano /etc/apache2/sites-available/000-default.conf"
echo "     DocumentRoot → $TARGET_DIR"
echo "     sudo a2enmod rewrite && sudo systemctl restart apache2"
echo ""
echo "  2. SSL 인증서 (Plesk 사용 시 자동, 아닌 경우):"
echo "     sudo apt install certbot python3-certbot-apache"
echo "     sudo certbot --apache -d dsp114.com"
echo ""
echo "  3. 교정이미지 NAS 동기화:"
echo "     ${TARGET_DIR}/scripts/sync_upload_to_nas.sh --nas-only"
echo ""
echo "  4. 사이트 접속 확인:"
echo "     http://localhost/"
echo "     https://dsp114.com/ (DNS 설정 후)"
echo ""
echo "  5. 결제 시스템 확인:"
echo "     payment/inicis_config.php → INICIS_TEST_MODE 확인"
echo ""
echo "============================================================"
