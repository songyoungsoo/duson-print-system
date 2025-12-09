#!/usr/bin/env php
<?php
/**
 * 두손기획인쇄 CLI 설치 스크립트
 *
 * 사용법:
 *   php cli_install.php
 *   php cli_install.php --auto (자동 설치, 기본값 사용)
 *   php cli_install.php --config=install.json (설정 파일 사용)
 */

// CLI 환경 확인
if (php_sapi_name() !== 'cli') {
    die("이 스크립트는 명령줄에서만 실행할 수 있습니다.\n");
}

// 색상 코드
define('RED', "\033[31m");
define('GREEN', "\033[32m");
define('YELLOW', "\033[33m");
define('BLUE', "\033[34m");
define('CYAN', "\033[36m");
define('RESET', "\033[0m");
define('BOLD', "\033[1m");

class CLIInstaller {
    private $config = [];
    private $autoMode = false;
    private $configFile = null;

    public function __construct($args) {
        $this->parseArgs($args);
    }

    private function parseArgs($args) {
        foreach ($args as $arg) {
            if ($arg === '--auto' || $arg === '-a') {
                $this->autoMode = true;
            } elseif (strpos($arg, '--config=') === 0) {
                $this->configFile = substr($arg, 9);
            } elseif ($arg === '--help' || $arg === '-h') {
                $this->showHelp();
                exit(0);
            }
        }

        if ($this->configFile && file_exists($this->configFile)) {
            $this->config = json_decode(file_get_contents($this->configFile), true) ?? [];
        }
    }

    private function showHelp() {
        echo CYAN . BOLD . "
╔══════════════════════════════════════════════════════════════╗
║           두손기획인쇄 CLI 설치 도구                          ║
╚══════════════════════════════════════════════════════════════╝
" . RESET . "
사용법:
  php cli_install.php [옵션]

옵션:
  --auto, -a       자동 설치 모드 (기본값 사용)
  --config=FILE    JSON 설정 파일 사용
  --help, -h       이 도움말 표시

예시:
  php cli_install.php                    대화형 설치
  php cli_install.php --auto             자동 설치
  php cli_install.php --config=my.json   설정 파일로 설치

설정 파일 형식 (JSON):
{
  \"db_host\": \"localhost\",
  \"db_name\": \"dsp1830\",
  \"db_user\": \"dsp1830\",
  \"db_pass\": \"password\",
  \"admin_id\": \"admin\",
  \"admin_pass\": \"admin123\",
  \"admin_name\": \"관리자\",
  \"admin_email\": \"admin@example.com\",
  \"company_name\": \"회사명\",
  \"company_phone\": \"02-1234-5678\"
}
";
    }

    public function run() {
        $this->showBanner();

        // Step 1: 시스템 요구사항 확인
        if (!$this->checkRequirements()) {
            $this->error("시스템 요구사항이 충족되지 않았습니다.");
            exit(1);
        }

        // Step 2: 데이터베이스 설정
        $dbConfig = $this->configurateDatabase();
        if (!$dbConfig) {
            $this->error("데이터베이스 설정에 실패했습니다.");
            exit(1);
        }

        // Step 3: 관리자 계정 생성
        $adminConfig = $this->createAdmin($dbConfig);
        if (!$adminConfig) {
            $this->error("관리자 계정 생성에 실패했습니다.");
            exit(1);
        }

        // Step 4: 사이트 설정
        $siteConfig = $this->configureSite();

        // Step 5: 설정 파일 생성
        if (!$this->generateConfigFile($dbConfig, $siteConfig)) {
            $this->error("설정 파일 생성에 실패했습니다.");
            exit(1);
        }

        $this->showComplete($adminConfig);
    }

    private function showBanner() {
        echo CYAN . BOLD . "
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║     🖨️  두손기획인쇄 설치 마법사 (CLI)                       ║
║                                                              ║
║     Enterprise Print Management System                       ║
║     Version 1.0.0                                            ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
" . RESET . "\n";

        if ($this->autoMode) {
            echo YELLOW . "⚡ 자동 설치 모드가 활성화되었습니다.\n" . RESET;
        }
        echo "\n";
    }

    private function checkRequirements() {
        $this->section("Step 1/5: 시스템 요구사항 확인");

        $requirements = [
            ['name' => 'PHP 버전', 'check' => version_compare(phpversion(), '7.4.0', '>='), 'current' => phpversion(), 'required' => '7.4.0+'],
            ['name' => 'MySQLi 확장', 'check' => extension_loaded('mysqli'), 'current' => extension_loaded('mysqli') ? '설치됨' : '미설치', 'required' => '필수'],
            ['name' => 'JSON 확장', 'check' => extension_loaded('json'), 'current' => extension_loaded('json') ? '설치됨' : '미설치', 'required' => '필수'],
            ['name' => 'mbstring 확장', 'check' => extension_loaded('mbstring'), 'current' => extension_loaded('mbstring') ? '설치됨' : '미설치', 'required' => '필수'],
            ['name' => 'OpenSSL 확장', 'check' => extension_loaded('openssl'), 'current' => extension_loaded('openssl') ? '설치됨' : '미설치', 'required' => '필수'],
            ['name' => 'GD 확장', 'check' => extension_loaded('gd'), 'current' => extension_loaded('gd') ? '설치됨' : '미설치', 'required' => '권장'],
            ['name' => 'cURL 확장', 'check' => extension_loaded('curl'), 'current' => extension_loaded('curl') ? '설치됨' : '미설치', 'required' => '권장'],
        ];

        $allPassed = true;
        foreach ($requirements as $req) {
            $status = $req['check'] ? GREEN . "✓" . RESET : RED . "✗" . RESET;
            $current = $req['check'] ? GREEN . $req['current'] . RESET : RED . $req['current'] . RESET;
            echo "  {$status} {$req['name']}: {$current} ({$req['required']})\n";

            if (!$req['check'] && $req['required'] === '필수') {
                $allPassed = false;
            }
        }

        // 디렉토리 권한 체크
        echo "\n  디렉토리 권한:\n";
        $dirs = [
            '../' => '루트 디렉토리',
            '../ImgFolder/' => '이미지 업로드',
            '../mlangorder_printauto/upload/' => '주문 파일'
        ];

        foreach ($dirs as $dir => $name) {
            $writable = is_writable($dir) || !file_exists($dir);
            $status = $writable ? GREEN . "✓" . RESET : RED . "✗" . RESET;
            echo "  {$status} {$name}\n";
        }

        echo "\n";
        return $allPassed;
    }

    private function configurateDatabase() {
        $this->section("Step 2/5: 데이터베이스 설정");

        $defaults = [
            'db_host' => 'localhost',
            'db_name' => 'dsp1830',
            'db_user' => 'dsp1830',
            'db_pass' => ''
        ];

        // 설정 파일이나 자동 모드에서 값 가져오기
        $config = array_merge($defaults, $this->config);

        if (!$this->autoMode) {
            $config['db_host'] = $this->prompt("데이터베이스 호스트", $config['db_host']);
            $config['db_name'] = $this->prompt("데이터베이스 이름", $config['db_name']);
            $config['db_user'] = $this->prompt("데이터베이스 사용자", $config['db_user']);
            $config['db_pass'] = $this->prompt("데이터베이스 비밀번호", $config['db_pass'], true);
        } else {
            echo "  호스트: {$config['db_host']}\n";
            echo "  데이터베이스: {$config['db_name']}\n";
            echo "  사용자: {$config['db_user']}\n";
        }

        // 연결 테스트
        echo "\n  연결 테스트 중...";
        $conn = @mysqli_connect($config['db_host'], $config['db_user'], $config['db_pass']);

        if (!$conn) {
            echo RED . " 실패\n" . RESET;
            $this->error("데이터베이스 연결 실패: " . mysqli_connect_error());
            return false;
        }
        echo GREEN . " 성공\n" . RESET;

        // 데이터베이스 생성/선택
        echo "  데이터베이스 확인 중...";
        $dbExists = mysqli_select_db($conn, $config['db_name']);

        if (!$dbExists) {
            echo YELLOW . " 생성 중...\n" . RESET;
            if (!mysqli_query($conn, "CREATE DATABASE `{$config['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
                $this->error("데이터베이스 생성 실패: " . mysqli_error($conn));
                return false;
            }
            mysqli_select_db($conn, $config['db_name']);
        }
        echo GREEN . " 완료\n" . RESET;

        // 스키마 적용
        echo "  테이블 생성 중...";
        $schemaFile = __DIR__ . '/sql/schema.sql';
        if (file_exists($schemaFile)) {
            $schema = file_get_contents($schemaFile);
            mysqli_multi_query($conn, $schema);

            // 모든 결과 처리
            do {
                if ($result = mysqli_store_result($conn)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($conn));

            echo GREEN . " 완료\n" . RESET;
        } else {
            echo YELLOW . " 스키마 파일 없음 (건너뜀)\n" . RESET;
        }

        mysqli_close($conn);
        echo "\n";

        return $config;
    }

    private function createAdmin($dbConfig) {
        $this->section("Step 3/5: 관리자 계정 생성");

        $defaults = [
            'admin_id' => 'admin',
            'admin_pass' => '',
            'admin_name' => '관리자',
            'admin_email' => 'admin@example.com'
        ];

        $config = array_merge($defaults, $this->config);

        if (!$this->autoMode) {
            $config['admin_id'] = $this->prompt("관리자 ID (4자 이상)", $config['admin_id']);
            $config['admin_pass'] = $this->prompt("관리자 비밀번호 (6자 이상)", '', true);
            $config['admin_name'] = $this->prompt("관리자 이름", $config['admin_name']);
            $config['admin_email'] = $this->prompt("관리자 이메일", $config['admin_email']);
        } else {
            if (empty($config['admin_pass'])) {
                $config['admin_pass'] = bin2hex(random_bytes(8)); // 자동 생성
                echo YELLOW . "  자동 생성된 비밀번호: {$config['admin_pass']}\n" . RESET;
            }
            echo "  관리자 ID: {$config['admin_id']}\n";
            echo "  관리자 이름: {$config['admin_name']}\n";
            echo "  관리자 이메일: {$config['admin_email']}\n";
        }

        // 유효성 검사
        if (strlen($config['admin_id']) < 4) {
            $this->error("관리자 ID는 4자 이상이어야 합니다.");
            return false;
        }
        if (strlen($config['admin_pass']) < 6) {
            $this->error("비밀번호는 6자 이상이어야 합니다.");
            return false;
        }

        // DB에 저장
        $conn = mysqli_connect($dbConfig['db_host'], $dbConfig['db_user'], $dbConfig['db_pass'], $dbConfig['db_name']);
        mysqli_set_charset($conn, 'utf8mb4');

        $hashedPass = password_hash($config['admin_pass'], PASSWORD_DEFAULT);

        echo "\n  관리자 계정 생성 중...";

        // 기존 계정 확인
        $check = mysqli_query($conn, "SELECT admin_id FROM admin_users WHERE admin_id = '{$config['admin_id']}'");
        if (mysqli_num_rows($check) > 0) {
            echo YELLOW . " 이미 존재함 (업데이트)\n" . RESET;
            $stmt = mysqli_prepare($conn, "UPDATE admin_users SET admin_pass = ?, admin_name = ?, admin_email = ? WHERE admin_id = ?");
            mysqli_stmt_bind_param($stmt, 'ssss', $hashedPass, $config['admin_name'], $config['admin_email'], $config['admin_id']);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO admin_users (admin_id, admin_pass, admin_name, admin_email, admin_level, created_at) VALUES (?, ?, ?, ?, 9, NOW())");
            mysqli_stmt_bind_param($stmt, 'ssss', $config['admin_id'], $hashedPass, $config['admin_name'], $config['admin_email']);
        }

        if (mysqli_stmt_execute($stmt)) {
            echo GREEN . " 완료\n" . RESET;
        } else {
            echo RED . " 실패\n" . RESET;
            $this->error("계정 생성 실패: " . mysqli_error($conn));
            return false;
        }

        mysqli_close($conn);
        echo "\n";

        return $config;
    }

    private function configureSite() {
        $this->section("Step 4/5: 사이트 설정");

        $defaults = [
            'company_name' => '두손기획인쇄',
            'company_owner' => '',
            'company_phone' => '1688-2384',
            'company_address' => '서울 영등포구 영등포로36길 9 송호빌딩 1층',
            'company_email' => '',
            'company_bizno' => '',
            'bank1_name' => '국민은행',
            'bank1_account' => '999-1688-2384',
            'bank2_name' => '신한은행',
            'bank2_account' => '110-342-543507',
            'bank3_name' => '농협',
            'bank3_account' => '301-2632-1830-11',
            'bank_holder' => '차경선 두손기획인쇄',
            'smtp_host' => 'smtp.naver.com',
            'smtp_port' => 465,
            'smtp_user' => '',
            'smtp_pass' => '',
            'smtp_secure' => 'ssl'
        ];

        $config = array_merge($defaults, $this->config);

        if (!$this->autoMode) {
            echo "  [회사 정보]\n";
            $config['company_name'] = $this->prompt("  회사명", $config['company_name']);
            $config['company_phone'] = $this->prompt("  대표 전화", $config['company_phone']);
            $config['company_address'] = $this->prompt("  주소", $config['company_address']);

            echo "\n  [은행 정보]\n";
            $config['bank1_name'] = $this->prompt("  은행1 이름", $config['bank1_name']);
            $config['bank1_account'] = $this->prompt("  은행1 계좌", $config['bank1_account']);
            $config['bank_holder'] = $this->prompt("  예금주", $config['bank_holder']);

            $setupSmtp = $this->confirm("  SMTP 이메일 설정을 하시겠습니까?");
            if ($setupSmtp) {
                $config['smtp_host'] = $this->prompt("  SMTP 서버", $config['smtp_host']);
                $config['smtp_port'] = (int)$this->prompt("  SMTP 포트", $config['smtp_port']);
                $config['smtp_user'] = $this->prompt("  SMTP 사용자", $config['smtp_user']);
                $config['smtp_pass'] = $this->prompt("  SMTP 비밀번호", '', true);
            }
        } else {
            echo "  회사명: {$config['company_name']}\n";
            echo "  전화: {$config['company_phone']}\n";
            echo "  주소: {$config['company_address']}\n";
        }

        echo "\n";
        return $config;
    }

    private function generateConfigFile($dbConfig, $siteConfig) {
        $this->section("Step 5/5: 설정 파일 생성");

        $configContent = $this->buildConfigContent($dbConfig, $siteConfig);

        $configPath = dirname(__DIR__) . '/config.env.php';

        echo "  설정 파일 생성 중...";
        if (file_put_contents($configPath, $configContent)) {
            echo GREEN . " 완료\n" . RESET;
        } else {
            echo RED . " 실패\n" . RESET;
            return false;
        }

        // 설치 완료 플래그
        $installedPath = dirname(__DIR__) . '/config.installed.php';
        echo "  설치 플래그 생성 중...";
        if (file_put_contents($installedPath, '<?php // Installed on ' . date('Y-m-d H:i:s') . ' via CLI')) {
            echo GREEN . " 완료\n" . RESET;
        }

        echo "\n";
        return true;
    }

    private function buildConfigContent($dbConfig, $siteConfig) {
        $date = date('Y-m-d H:i:s');

        return '<?php
/**
 * 두손기획 인쇄몰 환경 설정
 * CLI 설치로 자동 생성됨: ' . $date . '
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
        $host = $_SERVER["HTTP_HOST"] ?? "localhost";

        if (in_array($host, ["localhost", "127.0.0.1", "::1"])) {
            $this->environment = "local";
        } else {
            $this->environment = "production";
        }
    }

    private function loadConfig() {
        $this->config = [
            "db" => [
                "host" => "' . addslashes($dbConfig['db_host']) . '",
                "name" => "' . addslashes($dbConfig['db_name']) . '",
                "user" => "' . addslashes($dbConfig['db_user']) . '",
                "pass" => "' . addslashes($dbConfig['db_pass']) . '"
            ],
            "company" => [
                "name" => "' . addslashes($siteConfig['company_name']) . '",
                "owner" => "' . addslashes($siteConfig['company_owner'] ?? '') . '",
                "phone" => "' . addslashes($siteConfig['company_phone']) . '",
                "address" => "' . addslashes($siteConfig['company_address'] ?? '') . '",
                "email" => "' . addslashes($siteConfig['company_email'] ?? '') . '",
                "bizno" => "' . addslashes($siteConfig['company_bizno'] ?? '') . '"
            ],
            "bank" => [
                "bank1" => ["name" => "' . addslashes($siteConfig['bank1_name'] ?? '') . '", "account" => "' . addslashes($siteConfig['bank1_account'] ?? '') . '"],
                "bank2" => ["name" => "' . addslashes($siteConfig['bank2_name'] ?? '') . '", "account" => "' . addslashes($siteConfig['bank2_account'] ?? '') . '"],
                "bank3" => ["name" => "' . addslashes($siteConfig['bank3_name'] ?? '') . '", "account" => "' . addslashes($siteConfig['bank3_account'] ?? '') . '"],
                "holder" => "' . addslashes($siteConfig['bank_holder'] ?? '') . '"
            ],
            "smtp" => [
                "host" => "' . addslashes($siteConfig['smtp_host'] ?? '') . '",
                "port" => ' . intval($siteConfig['smtp_port'] ?? 465) . ',
                "user" => "' . addslashes($siteConfig['smtp_user'] ?? '') . '",
                "pass" => "' . addslashes($siteConfig['smtp_pass'] ?? '') . '",
                "secure" => "' . addslashes($siteConfig['smtp_secure'] ?? 'ssl') . '"
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

// 전역 함수
function env($key, $default = null) {
    $value = EnvironmentDetector::getInstance()->get($key);
    return $value !== null ? $value : $default;
}

$admin_url = EnvironmentDetector::getInstance()->getAdminUrl();
';
    }

    private function showComplete($adminConfig) {
        echo GREEN . BOLD . "
╔══════════════════════════════════════════════════════════════╗
║                                                              ║
║     ✅ 설치가 완료되었습니다!                                 ║
║                                                              ║
╚══════════════════════════════════════════════════════════════╝
" . RESET . "
" . CYAN . "관리자 정보:" . RESET . "
  ID: " . GREEN . $adminConfig['admin_id'] . RESET . "
  이름: {$adminConfig['admin_name']}
  이메일: {$adminConfig['admin_email']}

" . CYAN . "접속 URL:" . RESET . "
  사이트: http://localhost/
  관리자: http://localhost/admin/

" . YELLOW . "⚠️  보안 권장:" . RESET . "
  설치 완료 후 /install/ 폴더를 삭제하세요.
  rm -rf " . dirname(__DIR__) . "/install/

" . CYAN . "다음 단계:" . RESET . "
  1. 관리자 페이지에서 제품 가격표 설정
  2. 샘플 이미지를 ImgFolder에 업로드
  3. 이메일 발송 테스트
  4. SSL 인증서 설치 (권장)

";
    }

    // 유틸리티 함수들
    private function section($title) {
        echo BLUE . BOLD . "\n━━━ {$title} ━━━\n\n" . RESET;
    }

    private function prompt($question, $default = '', $hidden = false) {
        $defaultText = $default ? " [{$default}]" : '';
        echo "  {$question}{$defaultText}: ";

        if ($hidden && function_exists('readline')) {
            system('stty -echo');
            $input = trim(fgets(STDIN));
            system('stty echo');
            echo "\n";
        } else {
            $input = trim(fgets(STDIN));
        }

        return $input !== '' ? $input : $default;
    }

    private function confirm($question) {
        echo "  {$question} (y/n): ";
        $input = strtolower(trim(fgets(STDIN)));
        return in_array($input, ['y', 'yes', '예', 'ㅇ']);
    }

    private function error($message) {
        echo "\n" . RED . "❌ 오류: {$message}\n" . RESET;
    }

    private function success($message) {
        echo GREEN . "✓ {$message}\n" . RESET;
    }
}

// 실행
$installer = new CLIInstaller(array_slice($argv, 1));
$installer->run();
