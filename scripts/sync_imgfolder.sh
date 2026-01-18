#!/bin/bash
#
# ImgFolder 동기화 스크립트
# 두손기획인쇄 이미지 동기화
#
# 사용법:
#   ./sync_imgfolder.sh [ftp|rsync|lftp] [source] [options]
#
# 예시:
#   ./sync_imgfolder.sh ftp dsp114.com           # FTP에서 다운로드
#   ./sync_imgfolder.sh rsync user@server:/path  # rsync 동기화
#   ./sync_imgfolder.sh lftp dsp1830.shop        # lftp 미러링
#

set -e

# 설정
LOCAL_PATH="/var/www/html/ImgFolder"
LOG_FILE="/var/log/imgfolder_sync.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# 색상
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 로그 함수
log() {
    echo -e "${GREEN}[INFO]${NC} $1"
    echo "[$DATE] [INFO] $1" >> "$LOG_FILE" 2>/dev/null || true
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
    echo "[$DATE] [WARN] $1" >> "$LOG_FILE" 2>/dev/null || true
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    echo "[$DATE] [ERROR] $1" >> "$LOG_FILE" 2>/dev/null || true
    exit 1
}

# 사용법 출력
usage() {
    cat << EOF
ImgFolder 동기화 스크립트

사용법:
    $0 <method> <source> [options]

방법 (method):
    ftp     - curl을 사용한 FTP 다운로드
    lftp    - lftp를 사용한 FTP 미러링 (병렬 다운로드)
    rsync   - rsync를 사용한 동기화

예시:
    $0 ftp dsp114.com --user=duson1830 --pass=du1830
    $0 lftp dsp1830.shop --user=dsp1830 --pass=ds701018
    $0 rsync user@192.168.1.100:/var/www/html/ImgFolder/
    $0 create-structure   # 빈 폴더 구조만 생성

옵션:
    --user=USERNAME     FTP 사용자명
    --pass=PASSWORD     FTP 비밀번호
    --products=LIST     동기화할 제품 (콤마 구분)
                        예: inserted,namecard,envelope
    --years=LIST        동기화할 연도 (콤마 구분, 기본값: 2026)
                        예: --years=2025,2026
    --dry-run           실제 다운로드 없이 시뮬레이션
    --help              이 도움말 출력

제품 목록:
    inserted, sticker_new, msticker, namecard, envelope,
    littleprint, merchandisebond, cadarok, ncrflambeau

EOF
    exit 0
}

# 폴더 구조 생성
create_structure() {
    log "ImgFolder 구조 생성 중..."

    # 주문 업로드 폴더
    local products=(
        "_MlangPrintAuto_inserted_index.php"
        "_MlangPrintAuto_sticker_new_index.php"
        "_MlangPrintAuto_msticker_index.php"
        "_MlangPrintAuto_NameCard_index.php"
        "_MlangPrintAuto_envelope_index.php"
        "_MlangPrintAuto_littleprint_index.php"
        "_MlangPrintAuto_MerchandiseBond_index.php"
        "_MlangPrintAuto_cadarok_index.php"
        "_MlangPrintAuto_NcrFlambeau_index.php"
    )

    for product in "${products[@]}"; do
        mkdir -p "$LOCAL_PATH/$product"
        log "  생성: $product"
    done

    # 갤러리 폴더
    local galleries=(
        "leaflet/gallery"
        "namecard/gallery"
        "envelope/gallery"
        "littleprint/gallery"
        "msticker/gallery"
        "merchandisebond/gallery"
        "samplegallery"
        "paper_texture"
        "gate_picto"
    )

    for gallery in "${galleries[@]}"; do
        mkdir -p "$LOCAL_PATH/$gallery"
        log "  생성: $gallery"
    done

    # 권한 설정
    if [ "$EUID" -eq 0 ]; then
        chown -R www-data:www-data "$LOCAL_PATH"
        chmod -R 775 "$LOCAL_PATH"
        log "권한 설정 완료 (www-data:www-data, 775)"
    else
        warn "root가 아니므로 권한 설정 생략. sudo로 실행 권장"
    fi

    log "ImgFolder 구조 생성 완료!"
}

# FTP 동기화 (curl)
sync_ftp() {
    local host=$1
    local user=$2
    local pass=$3
    local products=$4
    local years=$5
    local dry_run=$6

    log "FTP 동기화 시작: $host"

    if [ -z "$user" ] || [ -z "$pass" ]; then
        error "FTP 사용자명과 비밀번호가 필요합니다"
    fi

    # 제품 폴더 매핑
    declare -A product_folders=(
        ["inserted"]="_MlangPrintAuto_inserted_index.php"
        ["sticker_new"]="_MlangPrintAuto_sticker_new_index.php"
        ["msticker"]="_MlangPrintAuto_msticker_index.php"
        ["namecard"]="_MlangPrintAuto_NameCard_index.php"
        ["envelope"]="_MlangPrintAuto_envelope_index.php"
        ["littleprint"]="_MlangPrintAuto_littleprint_index.php"
        ["merchandisebond"]="_MlangPrintAuto_MerchandiseBond_index.php"
        ["cadarok"]="_MlangPrintAuto_cadarok_index.php"
        ["ncrflambeau"]="_MlangPrintAuto_NcrFlambeau_index.php"
    )

    # 기본값: 모든 제품
    if [ -z "$products" ]; then
        products="inserted,sticker_new,msticker,namecard,envelope,littleprint,merchandisebond,cadarok,ncrflambeau"
    fi

    # 기본값: 2026년만 (필요시 --years=2025,2026 옵션으로 확장)
    if [ -z "$years" ]; then
        years="2026"
    fi

    IFS=',' read -ra PRODUCTS <<< "$products"
    IFS=',' read -ra YEARS <<< "$years"

    for product in "${PRODUCTS[@]}"; do
        folder="${product_folders[$product]}"
        if [ -z "$folder" ]; then
            warn "알 수 없는 제품: $product"
            continue
        fi

        for year in "${YEARS[@]}"; do
            remote_path="www/ImgFolder/$folder/$year/"
            local_dir="$LOCAL_PATH/$folder/$year/"

            log "다운로드: $product/$year"

            if [ "$dry_run" = "true" ]; then
                echo "  [DRY-RUN] curl -u $user:*** ftp://$host/$remote_path -> $local_dir"
            else
                mkdir -p "$local_dir"

                # curl로 디렉토리 목록 가져오기 및 다운로드
                curl -s -u "$user:$pass" "ftp://$host/$remote_path" | while read -r line; do
                    filename=$(echo "$line" | awk '{print $NF}')
                    if [ -n "$filename" ] && [ "$filename" != "." ] && [ "$filename" != ".." ]; then
                        if [[ "$line" == d* ]]; then
                            # 디렉토리인 경우
                            log "  폴더: $filename (하위 폴더 다운로드는 lftp 권장)"
                        else
                            # 파일인 경우
                            curl -s -u "$user:$pass" "ftp://$host/$remote_path$filename" -o "$local_dir$filename"
                        fi
                    fi
                done
            fi
        done
    done

    log "FTP 동기화 완료!"
}

# lftp 동기화 (병렬 다운로드)
sync_lftp() {
    local host=$1
    local user=$2
    local pass=$3
    local products=$4
    local years=$5
    local dry_run=$6

    if ! command -v lftp &> /dev/null; then
        error "lftp가 설치되어 있지 않습니다. sudo apt install lftp"
    fi

    log "lftp 미러링 시작: $host"

    # 제품 폴더 매핑
    declare -A product_folders=(
        ["inserted"]="_MlangPrintAuto_inserted_index.php"
        ["sticker_new"]="_MlangPrintAuto_sticker_new_index.php"
        ["msticker"]="_MlangPrintAuto_msticker_index.php"
        ["namecard"]="_MlangPrintAuto_NameCard_index.php"
        ["envelope"]="_MlangPrintAuto_envelope_index.php"
        ["littleprint"]="_MlangPrintAuto_littleprint_index.php"
        ["merchandisebond"]="_MlangPrintAuto_MerchandiseBond_index.php"
        ["cadarok"]="_MlangPrintAuto_cadarok_index.php"
        ["ncrflambeau"]="_MlangPrintAuto_NcrFlambeau_index.php"
    )

    # 기본값
    if [ -z "$products" ]; then
        products="inserted,sticker_new,msticker,namecard,envelope,littleprint,merchandisebond,cadarok,ncrflambeau"
    fi
    if [ -z "$years" ]; then
        years="2026"
    fi

    IFS=',' read -ra PRODUCTS <<< "$products"
    IFS=',' read -ra YEARS <<< "$years"

    for product in "${PRODUCTS[@]}"; do
        folder="${product_folders[$product]}"
        if [ -z "$folder" ]; then
            warn "알 수 없는 제품: $product"
            continue
        fi

        for year in "${YEARS[@]}"; do
            remote_path="/www/ImgFolder/$folder/$year"
            local_dir="$LOCAL_PATH/$folder/$year"

            log "미러링: $product/$year"

            mkdir -p "$local_dir"

            if [ "$dry_run" = "true" ]; then
                echo "  [DRY-RUN] lftp mirror $remote_path -> $local_dir"
            else
                lftp -u "$user,$pass" "ftp://$host" << EOF
set ssl:verify-certificate no
set ftp:passive-mode yes
set mirror:parallel-transfer-count 5
mirror --verbose --continue "$remote_path" "$local_dir"
bye
EOF
            fi
        done
    done

    log "lftp 미러링 완료!"
}

# rsync 동기화
sync_rsync() {
    local source=$1
    local dry_run=$2

    if ! command -v rsync &> /dev/null; then
        error "rsync가 설치되어 있지 않습니다. sudo apt install rsync"
    fi

    log "rsync 동기화 시작: $source"

    local opts="-avz --progress"
    if [ "$dry_run" = "true" ]; then
        opts="$opts --dry-run"
    fi

    rsync $opts "$source" "$LOCAL_PATH/"

    log "rsync 동기화 완료!"
}

# 메인 로직
main() {
    local method=$1
    shift

    local source=""
    local user=""
    local pass=""
    local products=""
    local years=""
    local dry_run="false"

    # 인수 파싱
    while [[ $# -gt 0 ]]; do
        case $1 in
            --user=*)
                user="${1#*=}"
                shift
                ;;
            --pass=*)
                pass="${1#*=}"
                shift
                ;;
            --products=*)
                products="${1#*=}"
                shift
                ;;
            --years=*)
                years="${1#*=}"
                shift
                ;;
            --dry-run)
                dry_run="true"
                shift
                ;;
            --help|-h)
                usage
                ;;
            *)
                if [ -z "$source" ]; then
                    source=$1
                fi
                shift
                ;;
        esac
    done

    case $method in
        create-structure|create|init)
            create_structure
            ;;
        ftp)
            sync_ftp "$source" "$user" "$pass" "$products" "$years" "$dry_run"
            ;;
        lftp)
            sync_lftp "$source" "$user" "$pass" "$products" "$years" "$dry_run"
            ;;
        rsync)
            sync_rsync "$source" "$dry_run"
            ;;
        --help|-h|"")
            usage
            ;;
        *)
            error "알 수 없는 방법: $method"
            ;;
    esac
}

# 실행
main "$@"
