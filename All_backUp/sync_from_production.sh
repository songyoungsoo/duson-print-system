#!/bin/bash
# 프로덕션 → 로컬 동기화 스크립트
# 날짜: 2025-12-20

FTP_HOST="dsp1830.shop"
FTP_USER="dsp1830"
FTP_PASS="ds701018"
BACKUP_DIR="/var/www/html/backup_before_sync_$(date +%Y%m%d_%H%M%S)"

echo "======================================"
echo "프로덕션 → 로컬 동기화 시작"
echo "백업 디렉토리: $BACKUP_DIR"
echo "======================================"

# 1. 기존 로컬 파일 백업
echo ""
echo "1️⃣ 기존 로컬 파일 백업 중..."
mkdir -p "$BACKUP_DIR"
cp -r /var/www/html/.htaccess "$BACKUP_DIR/" 2>/dev/null
cp -r /var/www/html/admin251121 "$BACKUP_DIR/" 2>/dev/null
echo "✅ 백업 완료: $BACKUP_DIR"

# 2. 핵심 설정 파일 다운로드
echo ""
echo "2️⃣ 핵심 설정 파일 다운로드 중..."

FILES=(
    ".htaccess"
    "admin251121/.htaccess"
    "admin251121/AdminConfig.php"
    "admin251121/BBSSinGo/index.php"
    "admin251121/BizMap/ViewFild.php"
    "admin251121/BizMap/admin.php"
    "admin251121/BizMap/db.php"
    "admin251121/BizMap/upload.php"
    "admin251121/BizMap/upload_1.php"
    "admin251121/BizMap/upload_2.php"
    "admin251121/BizMap/upload_3.php"
    "admin251121/HomePage/Customer.php"
    "admin251121/HomePage/Movic.php"
)

for file in "${FILES[@]}"; do
    echo "다운로드: $file"
    dir=$(dirname "$file")
    mkdir -p "$dir"
    curl -s -u "$FTP_USER:$FTP_PASS" "ftp://$FTP_HOST/$file" -o "$file"

    if [ $? -eq 0 ]; then
        echo "  ✅ 성공"
    else
        echo "  ❌ 실패"
    fi
done

# 3. Customer 하위 파일들
echo ""
echo "3️⃣ Customer 관리 파일 다운로드 중..."

CUSTOMER_FILES=(
    "admin251121/HomePage/Customer/CateAdmin.php"
    "admin251121/HomePage/Customer/CateView.php"
    "admin251121/HomePage/Customer/Year.php"
)

for file in "${CUSTOMER_FILES[@]}"; do
    echo "다운로드: $file"
    dir=$(dirname "$file")
    mkdir -p "$dir"
    curl -s -u "$FTP_USER:$FTP_PASS" "ftp://$FTP_HOST/$file" -o "$file"

    if [ $? -eq 0 ]; then
        echo "  ✅ 성공"
    else
        echo "  ❌ 실패"
    fi
done

# 4. Movic 하위 파일들
echo ""
echo "4️⃣ Movic 관리 파일 다운로드 중..."

MOVIC_FILES=(
    "admin251121/HomePage/Movic/CateAdmin.php"
    "admin251121/HomePage/Movic/CateView.php"
    "admin251121/HomePage/Movic/upload.php"
)

for file in "${MOVIC_FILES[@]}"; do
    echo "다운로드: $file"
    dir=$(dirname "$file")
    mkdir -p "$dir"
    curl -s -u "$FTP_USER:$FTP_PASS" "ftp://$FTP_HOST/$file" -o "$file"

    if [ $? -eq 0 ]; then
        echo "  ✅ 성공"
    else
        echo "  ❌ 실패"
    fi
done

# 5. 마이그레이션 백업 파일들 (선택적)
echo ""
echo "5️⃣ 마이그레이션 백업 확인..."
echo "⚠️  MIGRATION_BACKUPS 폴더는 용량이 클 수 있습니다."
read -p "다운로드하시겠습니까? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "마이그레이션 백업 다운로드 시작... (시간이 오래 걸릴 수 있습니다)"
    # 여기에 백업 파일 다운로드 로직 추가 (필요 시)
else
    echo "⏭️  마이그레이션 백업 건너뜀"
fi

# 6. 권한 설정
echo ""
echo "6️⃣ 파일 권한 설정 중..."
chmod -R 755 /var/www/html/admin251121/
chmod 644 /var/www/html/.htaccess
chmod 644 /var/www/html/admin251121/.htaccess
echo "✅ 권한 설정 완료"

# 7. 최종 요약
echo ""
echo "======================================"
echo "✅ 동기화 완료"
echo "======================================"
echo "백업 위치: $BACKUP_DIR"
echo ""
echo "⚠️  다음 단계:"
echo "1. 파일 비교: diff -r $BACKUP_DIR /var/www/html/admin251121"
echo "2. 테스트: http://localhost/admin251121/"
echo "3. Git 커밋: git add . && git commit -m 'sync: 프로덕션 최신 파일 동기화'"
echo "======================================"
