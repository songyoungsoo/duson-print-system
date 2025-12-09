#!/bin/bash
set -e

echo "╔══════════════════════════════════════════════════════════════╗"
echo "║     🖨️  두손기획인쇄 Docker Container                         ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# 환경변수 기본값
DB_HOST=${DB_HOST:-db}
DB_NAME=${DB_NAME:-dsp1830}
DB_USER=${DB_USER:-dsp1830}
DB_PASS=${DB_PASS:-ds701018}
COMPANY_NAME=${COMPANY_NAME:-두손기획인쇄}
COMPANY_PHONE=${COMPANY_PHONE:-1688-2384}

# 데이터베이스 연결 대기
echo "[INFO] 데이터베이스 연결 대기 중..."
max_tries=30
counter=0
until mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -e "SELECT 1" &> /dev/null; do
    counter=$((counter + 1))
    if [ $counter -gt $max_tries ]; then
        echo "[ERROR] 데이터베이스 연결 실패 (타임아웃)"
        exit 1
    fi
    echo "[INFO] 데이터베이스 대기 중... ($counter/$max_tries)"
    sleep 2
done
echo "[SUCCESS] 데이터베이스 연결됨"

# 설정 파일 생성 (없는 경우)
CONFIG_FILE="/var/www/html/config.env.php"
if [ ! -f "$CONFIG_FILE" ] || [ ! -s "$CONFIG_FILE" ]; then
    echo "[INFO] 설정 파일 생성 중..."

    cat > "$CONFIG_FILE" << 'EOFCONFIG'
<?php
/**
 * 두손기획 인쇄몰 환경 설정
 * Docker 자동 생성
 */

class EnvironmentDetector {
    private static $instance = null;
    private $environment;
    private $config;

    private function __construct() {
        $this->detectEnvironment();
        $this->loadConfig();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function detectEnvironment() {
        $this->environment = "docker";
    }

    private function loadConfig() {
        $this->config = [
            "db" => [
                "host" => getenv("DB_HOST") ?: "db",
                "name" => getenv("DB_NAME") ?: "dsp1830",
                "user" => getenv("DB_USER") ?: "dsp1830",
                "pass" => getenv("DB_PASS") ?: "ds701018"
            ],
            "company" => [
                "name" => getenv("COMPANY_NAME") ?: "두손기획인쇄",
                "owner" => getenv("COMPANY_OWNER") ?: "",
                "phone" => getenv("COMPANY_PHONE") ?: "1688-2384",
                "address" => getenv("COMPANY_ADDRESS") ?: "서울 영등포구 영등포로36길 9 송호빌딩 1층",
                "email" => getenv("ADMIN_EMAIL") ?: "",
                "bizno" => getenv("COMPANY_BIZNO") ?: ""
            ],
            "bank" => [
                "bank1" => ["name" => "국민은행", "account" => "999-1688-2384"],
                "bank2" => ["name" => "신한은행", "account" => "110-342-543507"],
                "bank3" => ["name" => "농협", "account" => "301-2632-1830-11"],
                "holder" => "차경선 두손기획인쇄"
            ],
            "smtp" => [
                "host" => getenv("SMTP_HOST") ?: "smtp.naver.com",
                "port" => (int)(getenv("SMTP_PORT") ?: 465),
                "user" => getenv("SMTP_USER") ?: "",
                "pass" => getenv("SMTP_PASS") ?: "",
                "secure" => getenv("SMTP_SECURE") ?: "ssl"
            ]
        ];
    }

    public function getEnvironment() {
        return $this->environment;
    }

    public function get($key) {
        $keys = explode(".", $key);
        $value = $this->config;
        foreach ($keys as $k) {
            if (isset($value[$k])) {
                $value = $value[$k];
            } else {
                return null;
            }
        }
        return $value;
    }

    public function getAdminUrl() {
        $host = $_SERVER["HTTP_HOST"] ?? "localhost";
        $protocol = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
        return $protocol . "://" . $host;
    }
}

function env($key, $default = null) {
    $value = EnvironmentDetector::getInstance()->get($key);
    return $value !== null ? $value : $default;
}

$admin_url = EnvironmentDetector::getInstance()->getAdminUrl();
EOFCONFIG

    echo "[SUCCESS] 설정 파일 생성 완료"
fi

# 관리자 계정 생성 (없는 경우)
echo "[INFO] 관리자 계정 확인 중..."
ADMIN_EXISTS=$(mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -D"$DB_NAME" -N -e "SELECT COUNT(*) FROM admin_users WHERE admin_id='admin'" 2>/dev/null || echo "0")

if [ "$ADMIN_EXISTS" = "0" ]; then
    echo "[INFO] 기본 관리자 계정 생성 중..."
    ADMIN_PASS_HASH=$(php -r "echo password_hash('admin123', PASSWORD_DEFAULT);")
    mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" -D"$DB_NAME" -e \
        "INSERT INTO admin_users (admin_id, admin_pass, admin_name, admin_email, admin_level, created_at) VALUES ('admin', '$ADMIN_PASS_HASH', '관리자', 'admin@example.com', 9, NOW())" 2>/dev/null || true
    echo "[SUCCESS] 관리자 계정 생성됨 (ID: admin, PW: admin123)"
fi

# 설치 완료 플래그
if [ ! -f "/var/www/html/config.installed.php" ]; then
    echo "<?php // Installed via Docker on $(date '+%Y-%m-%d %H:%M:%S')" > /var/www/html/config.installed.php
fi

# 권한 재설정
chown -R www-data:www-data /var/www/html/ImgFolder /var/www/html/mlangorder_printauto/upload 2>/dev/null || true

echo ""
echo "╔══════════════════════════════════════════════════════════════╗"
echo "║     ✅ 두손기획인쇄 시작됨                                    ║"
echo "╠══════════════════════════════════════════════════════════════╣"
echo "║  사이트:    http://localhost/                                ║"
echo "║  관리자:    http://localhost/admin/                          ║"
echo "║  phpMyAdmin: http://localhost:8080/ (--profile admin)        ║"
echo "╚══════════════════════════════════════════════════════════════╝"
echo ""

# Apache 실행
exec "$@"
