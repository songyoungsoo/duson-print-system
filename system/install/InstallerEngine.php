<?php
/**
 * Duson Print System - Installer Engine
 * 
 * Core installation logic for the web installer wizard.
 * Handles environment checks, DB setup, schema import, config generation, and admin creation.
 * 
 * @package DusonPrintSystem
 * @version 1.0.0
 * @requires PHP >= 7.4, MySQL >= 5.7
 */

class InstallerEngine
{
    /** @var string */
    private $basePath;

    /** @var string */
    private $sqlPath;

    /** @var string */
    private $configPath;

    /** @var mysqli|null */
    private $db = null;

    /** @var array */
    private $errors = [];

    /** @var array Offline valid license keys */
    private $validKeys = [
        'DUSON-FREE-TRIAL-2026',
        'DUSON-BETA-TEST-2026',
        'DUSON-DEMO-MODE-2026',
    ];

    /** @var array The 9 standard products */
    public static $products = [
        'inserted'        => ['name' => '전단지',     'unit' => '연', 'unit_code' => 'R', 'icon' => '📄', 'desc' => '전단지, 리플렛, 홍보물 인쇄'],
        'sticker_new'     => ['name' => '스티커',     'unit' => '매', 'unit_code' => 'S', 'icon' => '🏷️', 'desc' => '일반/원형/타원형 스티커 인쇄'],
        'msticker'        => ['name' => '자석스티커', 'unit' => '매', 'unit_code' => 'S', 'icon' => '🧲', 'desc' => '자석 부착형 스티커 인쇄'],
        'namecard'        => ['name' => '명함',       'unit' => '매', 'unit_code' => 'S', 'icon' => '💼', 'desc' => '일반/고급 명함 인쇄'],
        'envelope'        => ['name' => '봉투',       'unit' => '매', 'unit_code' => 'S', 'icon' => '✉️', 'desc' => '소봉투, 대봉투, 규격봉투'],
        'littleprint'     => ['name' => '포스터',     'unit' => '매', 'unit_code' => 'S', 'icon' => '🖼️', 'desc' => '소량 포스터, 실사출력'],
        'merchandisebond' => ['name' => '상품권',     'unit' => '매', 'unit_code' => 'S', 'icon' => '🎫', 'desc' => '상품권, 쿠폰, 이용권'],
        'cadarok'         => ['name' => '카다록',     'unit' => '부', 'unit_code' => 'B', 'icon' => '📚', 'desc' => '카탈로그, 소책자 제작'],
        'ncrflambeau'     => ['name' => 'NCR양식지', 'unit' => '권', 'unit_code' => 'V', 'icon' => '📋', 'desc' => '복사식 양식지 (2~5매 복사)'],
    ];

    public function __construct()
    {
        $this->basePath   = dirname(dirname(__DIR__));
        $this->sqlPath    = __DIR__ . '/sql';
        $this->configPath = $this->basePath . '/config';
    }

    /**
     * Get accumulated errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get last error message
     */
    public function getLastError(): string
    {
        return end($this->errors) ?: '';
    }

    // =========================================================================
    // Step 1: Environment Requirements Check
    // =========================================================================

    /**
     * Check all server requirements.
     * Returns array of check results with status: 'pass', 'fail', 'warn'
     */
    public function checkRequirements(): array
    {
        $checks = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $checks['php_version'] = [
            'label'    => 'PHP 버전',
            'required' => '>= 7.4',
            'current'  => $phpVersion,
            'status'   => version_compare($phpVersion, '7.4.0', '>=') ? 'pass' : 'fail',
        ];

        // MySQL extension
        $checks['mysqli'] = [
            'label'    => 'MySQLi 확장',
            'required' => '필수',
            'current'  => extension_loaded('mysqli') ? '설치됨' : '미설치',
            'status'   => extension_loaded('mysqli') ? 'pass' : 'fail',
        ];

        // mbstring
        $checks['mbstring'] = [
            'label'    => 'mbstring 확장',
            'required' => '필수',
            'current'  => extension_loaded('mbstring') ? '설치됨' : '미설치',
            'status'   => extension_loaded('mbstring') ? 'pass' : 'fail',
        ];

        // GD
        $checks['gd'] = [
            'label'    => 'GD 라이브러리',
            'required' => '필수',
            'current'  => extension_loaded('gd') ? '설치됨' : '미설치',
            'status'   => extension_loaded('gd') ? 'pass' : 'fail',
        ];

        // JSON
        $checks['json'] = [
            'label'    => 'JSON 확장',
            'required' => '필수',
            'current'  => extension_loaded('json') ? '설치됨' : '미설치',
            'status'   => extension_loaded('json') ? 'pass' : 'fail',
        ];

        // zlib
        $checks['zlib'] = [
            'label'    => 'Zlib 확장',
            'required' => '필수',
            'current'  => extension_loaded('zlib') ? '설치됨' : '미설치',
            'status'   => extension_loaded('zlib') ? 'pass' : 'fail',
        ];

        // fileinfo (optional)
        $checks['fileinfo'] = [
            'label'    => 'Fileinfo 확장',
            'required' => '권장',
            'current'  => extension_loaded('fileinfo') ? '설치됨' : '미설치',
            'status'   => extension_loaded('fileinfo') ? 'pass' : 'warn',
        ];

        // config directory writable
        $configWritable = is_dir($this->configPath) && is_writable($this->configPath);
        $checks['config_writable'] = [
            'label'    => 'config/ 디렉토리 쓰기 권한',
            'required' => '필수',
            'current'  => $configWritable ? '쓰기 가능' : '쓰기 불가',
            'status'   => $configWritable ? 'pass' : 'fail',
        ];

        // SQL files readable
        $schemaExists = is_readable($this->sqlPath . '/schema.sql');
        $seedExists   = is_readable($this->sqlPath . '/seed.sql');
        $checks['sql_files'] = [
            'label'    => 'SQL 설치 파일',
            'required' => '필수',
            'current'  => ($schemaExists && $seedExists) ? '확인됨' : '파일 없음',
            'status'   => ($schemaExists && $seedExists) ? 'pass' : 'fail',
        ];

        // uploads directory
        $uploadsPath = $this->basePath . '/uploads';
        $uploadsOk   = (is_dir($uploadsPath) && is_writable($uploadsPath));
        $checks['uploads_writable'] = [
            'label'    => 'uploads/ 디렉토리',
            'required' => '권장',
            'current'  => $uploadsOk ? '쓰기 가능' : '쓰기 불가 (설치 후 생성 가능)',
            'status'   => $uploadsOk ? 'pass' : 'warn',
        ];

        return $checks;
    }

    /**
     * Check if all required items pass
     */
    public function requirementsPassed(array $checks): bool
    {
        foreach ($checks as $check) {
            if ($check['status'] === 'fail') {
                return false;
            }
        }
        return true;
    }

    // =========================================================================
    // Step 2: License Validation
    // =========================================================================

    /**
     * Validate license key format and check against offline key list.
     * Format: DUSON-XXXX-XXXX-XXXX (alphanumeric groups)
     */
    public function validateLicense(string $key): bool
    {
        $key = strtoupper(trim($key));

        // Check offline key list first
        if (in_array($key, $this->validKeys, true)) {
            return true;
        }

        // Format validation: DUSON-XXXX-XXXX-XXXX
        if (!preg_match('/^DUSON-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $key)) {
            $this->errors[] = '라이선스 키 형식이 올바르지 않습니다. (DUSON-XXXX-XXXX-XXXX)';
            return false;
        }

        return true;
    }

    // =========================================================================
    // Step 3: Database Connection
    // =========================================================================

    /**
     * Test database connection with given credentials.
     * Returns true on success, false on failure.
     */
    public function testDbConnection(string $host, int $port, string $user, string $pass, string $dbName = ''): bool
    {
        mysqli_report(MYSQLI_REPORT_OFF);

        $conn = @mysqli_connect($host, $user, $pass, '', $port);

        if (!$conn) {
            $this->errors[] = 'DB 연결 실패: ' . mysqli_connect_error();
            return false;
        }

        // Check MySQL version
        $version = mysqli_get_server_info($conn);
        if (version_compare($version, '5.7.0', '<')) {
            $this->errors[] = "MySQL 버전이 너무 낮습니다. (현재: {$version}, 필요: 5.7 이상)";
            mysqli_close($conn);
            return false;
        }

        // If database name provided, check if it exists
        if ($dbName !== '') {
            $result = mysqli_select_db($conn, $dbName);
            if (!$result) {
                // DB doesn't exist yet — not an error if auto-create is on
                $this->errors[] = "데이터베이스 '{$dbName}'가 존재하지 않습니다. 자동 생성을 선택하세요.";
                mysqli_close($conn);
                return false;
            }
        }

        mysqli_close($conn);
        return true;
    }

    /**
     * Get MySQL server version string
     */
    public function getMysqlVersion(string $host, int $port, string $user, string $pass): string
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @mysqli_connect($host, $user, $pass, '', $port);
        if (!$conn) {
            return '연결 실패';
        }
        $ver = mysqli_get_server_info($conn);
        mysqli_close($conn);
        return $ver;
    }

    // =========================================================================
    // Step 8: Installation Execution
    // =========================================================================

    /**
     * Create database if not exists
     */
    public function createDatabase(string $host, int $port, string $user, string $pass, string $dbName): bool
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $conn = @mysqli_connect($host, $user, $pass, '', $port);

        if (!$conn) {
            $this->errors[] = 'DB 연결 실패: ' . mysqli_connect_error();
            return false;
        }

        $dbNameEsc = mysqli_real_escape_string($conn, $dbName);
        $sql = "CREATE DATABASE IF NOT EXISTS `{$dbNameEsc}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        if (!mysqli_query($conn, $sql)) {
            $this->errors[] = '데이터베이스 생성 실패: ' . mysqli_error($conn);
            mysqli_close($conn);
            return false;
        }

        mysqli_close($conn);
        return true;
    }

    /**
     * Connect to specific database and store connection
     */
    private function connectToDb(string $host, int $port, string $user, string $pass, string $dbName): bool
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->db = @mysqli_connect($host, $user, $pass, $dbName, $port);

        if (!$this->db) {
            $this->errors[] = 'DB 연결 실패: ' . mysqli_connect_error();
            return false;
        }

        mysqli_set_charset($this->db, 'utf8mb4');
        return true;
    }

    /**
     * Import schema.sql — creates all tables
     */
    public function importSchema(string $host, int $port, string $user, string $pass, string $dbName): bool
    {
        if (!$this->connectToDb($host, $port, $user, $pass, $dbName)) {
            return false;
        }

        $filePath = $this->sqlPath . '/schema.sql';
        if (!is_readable($filePath)) {
            $this->errors[] = 'schema.sql 파일을 찾을 수 없습니다.';
            return false;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $this->errors[] = 'schema.sql 파일 읽기 실패';
            return false;
        }

        // Execute multi-query
        if (mysqli_multi_query($this->db, $sql)) {
            // Consume all results to avoid "commands out of sync"
            do {
                if ($result = mysqli_store_result($this->db)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($this->db));
        }

        // Check for errors after consuming all results
        if (mysqli_errno($this->db)) {
            $this->errors[] = '테이블 생성 중 오류: ' . mysqli_error($this->db);
            mysqli_close($this->db);
            $this->db = null;
            return false;
        }

        mysqli_close($this->db);
        $this->db = null;
        return true;
    }

    /**
     * Import seed.sql — inserts initial config data
     */
    public function importSeed(string $host, int $port, string $user, string $pass, string $dbName): bool
    {
        if (!$this->connectToDb($host, $port, $user, $pass, $dbName)) {
            return false;
        }

        $filePath = $this->sqlPath . '/seed.sql';
        if (!is_readable($filePath)) {
            $this->errors[] = 'seed.sql 파일을 찾을 수 없습니다.';
            return false;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $this->errors[] = 'seed.sql 파일 읽기 실패';
            return false;
        }

        if (mysqli_multi_query($this->db, $sql)) {
            do {
                if ($result = mysqli_store_result($this->db)) {
                    mysqli_free_result($result);
                }
            } while (mysqli_next_result($this->db));
        }

        if (mysqli_errno($this->db)) {
            $this->errors[] = '초기 데이터 입력 중 오류: ' . mysqli_error($this->db);
            mysqli_close($this->db);
            $this->db = null;
            return false;
        }

        mysqli_close($this->db);
        $this->db = null;
        return true;
    }

    /**
     * Write config/site.php with all installation settings
     */
    public function writeConfig(array $data): bool
    {
        $configFile = $this->configPath . '/site.php';

        $dbHost = addslashes($data['db_host'] ?? 'localhost');
        $dbPort = intval($data['db_port'] ?? 3306);
        $dbName = addslashes($data['db_name'] ?? '');
        $dbUser = addslashes($data['db_user'] ?? '');
        $dbPass = addslashes($data['db_pass'] ?? '');

        $shopName    = addslashes($data['shop_name'] ?? '');
        $shopOwner   = addslashes($data['shop_owner'] ?? '');
        $shopPhone   = addslashes($data['shop_phone'] ?? '');
        $shopFax     = addslashes($data['shop_fax'] ?? '');
        $shopEmail   = addslashes($data['shop_email'] ?? '');
        $shopAddress = addslashes($data['shop_address'] ?? '');
        $shopBizNum  = addslashes($data['shop_biz_number'] ?? '');
        $shopLogo    = addslashes($data['shop_logo'] ?? '');

        $licenseKey = addslashes($data['license_key'] ?? '');

        $products = $data['products'] ?? array_keys(self::$products);
        $productsStr = "'" . implode("', '", array_map('addslashes', $products)) . "'";

        $pgMid       = addslashes($data['pg_mid'] ?? '');
        $pgKey       = addslashes($data['pg_key'] ?? '');
        $pgSecret    = addslashes($data['pg_secret'] ?? '');
        $pgTestMode  = !empty($data['pg_test_mode']) ? 'true' : 'false';
        $bankName    = addslashes($data['bank_name'] ?? '');
        $bankAccount = addslashes($data['bank_account'] ?? '');
        $bankHolder  = addslashes($data['bank_holder'] ?? '');

        $installDate = date('Y-m-d H:i:s');

        $config = <<<PHP
<?php
/**
 * Duson Print System - Site Configuration
 * 
 * 자동 생성됨: {$installDate}
 * 이 파일을 직접 수정하지 마세요. 관리자 페이지에서 설정을 변경하세요.
 */

// 데이터베이스 설정
define('DB_HOST', '{$dbHost}');
define('DB_PORT', {$dbPort});
define('DB_NAME', '{$dbName}');
define('DB_USER', '{$dbUser}');
define('DB_PASS', '{$dbPass}');
define('DB_CHARSET', 'utf8mb4');

// 상점 정보
define('SHOP_NAME', '{$shopName}');
define('SHOP_OWNER', '{$shopOwner}');
define('SHOP_PHONE', '{$shopPhone}');
define('SHOP_FAX', '{$shopFax}');
define('SHOP_EMAIL', '{$shopEmail}');
define('SHOP_ADDRESS', '{$shopAddress}');
define('SHOP_BIZ_NUMBER', '{$shopBizNum}');
define('SHOP_LOGO', '{$shopLogo}');

// 라이선스
define('LICENSE_KEY', '{$licenseKey}');

// 활성 제품
define('ACTIVE_PRODUCTS', serialize([{$productsStr}]));

// 결제 설정 (KG이니시스)
define('PG_PROVIDER', 'inicis');
define('PG_MID', '{$pgMid}');
define('PG_API_KEY', '{$pgKey}');
define('PG_API_SECRET', '{$pgSecret}');
define('PG_TEST_MODE', {$pgTestMode});

// 계좌이체 정보
define('BANK_NAME', '{$bankName}');
define('BANK_ACCOUNT', '{$bankAccount}');
define('BANK_HOLDER', '{$bankHolder}');

// 시스템 설정
define('INSTALLED', true);
define('INSTALL_DATE', '{$installDate}');
define('SYSTEM_VERSION', '1.0.0');
PHP;

        $result = file_put_contents($configFile, $config);
        if ($result === false) {
            $this->errors[] = '설정 파일 생성 실패: config/site.php 쓰기 권한을 확인하세요.';
            return false;
        }

        return true;
    }

    /**
     * Create admin user account with bcrypt password
     */
    public function createAdmin(
        string $host, int $port, string $user, string $pass, string $dbName,
        string $adminId, string $adminPass, string $adminEmail, string $adminName
    ): bool {
        if (!$this->connectToDb($host, $port, $user, $pass, $dbName)) {
            return false;
        }

        $hashedPassword = password_hash($adminPass, PASSWORD_DEFAULT);

        // Insert into users table
        $stmt = mysqli_prepare($this->db,
            "INSERT INTO `users` (`username`, `password`, `is_admin`, `name`, `email`, `level`, `created_at`, `migrated_from_member`)
             VALUES (?, ?, 1, ?, ?, '1', NOW(), 0)"
        );

        if (!$stmt) {
            $this->errors[] = '관리자 계정 생성 실패: ' . mysqli_error($this->db);
            mysqli_close($this->db);
            $this->db = null;
            return false;
        }

        // 3-step bind_param verification
        $query = "INSERT INTO `users` (`username`, `password`, `is_admin`, `name`, `email`, `level`, `created_at`, `migrated_from_member`) VALUES (?, ?, 1, ?, ?, '1', NOW(), 0)";
        $placeholder_count = substr_count($query, '?'); // 4
        $type_string = 'ssss';
        $type_count = strlen($type_string); // 4
        $var_count = 4;

        if ($placeholder_count !== $type_count || $type_count !== $var_count) {
            $this->errors[] = '내부 오류: bind_param 매개변수 불일치';
            mysqli_close($this->db);
            $this->db = null;
            return false;
        }

        mysqli_stmt_bind_param($stmt, $type_string, $adminId, $hashedPassword, $adminName, $adminEmail);

        if (!mysqli_stmt_execute($stmt)) {
            $this->errors[] = '관리자 계정 생성 실패: ' . mysqli_stmt_error($stmt);
            mysqli_stmt_close($stmt);
            mysqli_close($this->db);
            $this->db = null;
            return false;
        }

        mysqli_stmt_close($stmt);

        // Also insert into legacy admin table
        $stmtAdmin = mysqli_prepare($this->db,
            "DELETE FROM `admin` WHERE 1=1"
        );
        if ($stmtAdmin) {
            mysqli_stmt_execute($stmtAdmin);
            mysqli_stmt_close($stmtAdmin);
        }

        $stmtAdmin2 = mysqli_prepare($this->db,
            "INSERT INTO `admin` (`id`, `pwd`) VALUES (?, ?)"
        );
        if ($stmtAdmin2) {
            mysqli_stmt_bind_param($stmtAdmin2, 'ss', $adminId, $adminPass);
            mysqli_stmt_execute($stmtAdmin2);
            mysqli_stmt_close($stmtAdmin2);
        }

        mysqli_close($this->db);
        $this->db = null;
        return true;
    }

    /**
     * Update company_settings table with shop info
     */
    public function updateCompanySettings(
        string $host, int $port, string $user, string $pass, string $dbName,
        array $shopData
    ): bool {
        if (!$this->connectToDb($host, $port, $user, $pass, $dbName)) {
            return false;
        }

        $stmt = mysqli_prepare($this->db,
            "UPDATE `company_settings` SET
                `company_name` = ?,
                `business_number` = ?,
                `representative` = ?,
                `address` = ?,
                `phone` = ?,
                `fax` = ?,
                `email` = ?,
                `bank_name` = ?,
                `bank_account` = ?,
                `account_holder` = ?,
                `logo_path` = ?
             WHERE `id` = 1"
        );

        if (!$stmt) {
            // Table may not have data yet, try INSERT
            $stmtIns = mysqli_prepare($this->db,
                "INSERT INTO `company_settings` (`id`, `company_name`, `business_number`, `representative`, `address`, `phone`, `fax`, `email`, `bank_name`, `bank_account`, `account_holder`, `logo_path`)
                 VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            if ($stmtIns) {
                mysqli_stmt_bind_param($stmtIns, 'sssssssssss',
                    $shopData['shop_name'],
                    $shopData['shop_biz_number'],
                    $shopData['shop_owner'],
                    $shopData['shop_address'],
                    $shopData['shop_phone'],
                    $shopData['shop_fax'],
                    $shopData['shop_email'],
                    $shopData['bank_name'],
                    $shopData['bank_account'],
                    $shopData['bank_holder'],
                    $shopData['shop_logo']
                );
                mysqli_stmt_execute($stmtIns);
                mysqli_stmt_close($stmtIns);
            }
            mysqli_close($this->db);
            $this->db = null;
            return true;
        }

        mysqli_stmt_bind_param($stmt, 'sssssssssss',
            $shopData['shop_name'],
            $shopData['shop_biz_number'],
            $shopData['shop_owner'],
            $shopData['shop_address'],
            $shopData['shop_phone'],
            $shopData['shop_fax'],
            $shopData['shop_email'],
            $shopData['bank_name'],
            $shopData['bank_account'],
            $shopData['bank_holder'],
            $shopData['shop_logo']
        );

        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            $this->errors[] = '상점 정보 저장 실패: ' . mysqli_stmt_error($stmt);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($this->db);
        $this->db = null;
        return $result;
    }

    /**
     * Handle logo file upload
     * Returns relative path on success, empty string on failure/skip
     */
    public function handleLogoUpload(array $file): string
    {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return '';
        }

        // Validate file size (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            $this->errors[] = '로고 파일 크기는 2MB 이하여야 합니다.';
            return '';
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = function_exists('finfo_open')
            ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name'])
            : $file['type'];

        if (!in_array($finfo, $allowedTypes, true)) {
            $this->errors[] = '로고 파일은 JPG, PNG, GIF만 허용됩니다.';
            return '';
        }

        // Create upload directory
        $uploadDir = $this->basePath . '/uploads/logo';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . date('Ymd_His') . '.' . strtolower($ext);
        $destPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return '/uploads/logo/' . $filename;
        }

        $this->errors[] = '로고 파일 업로드에 실패했습니다.';
        return '';
    }

    /**
     * Run the full installation process
     * Returns array with step-by-step results
     */
    public function runInstallation(array $data): array
    {
        $results = [];

        $dbHost = $data['db_host'] ?? 'localhost';
        $dbPort = intval($data['db_port'] ?? 3306);
        $dbUser = $data['db_user'] ?? '';
        $dbPass = $data['db_pass'] ?? '';
        $dbName = $data['db_name'] ?? '';
        $autoCreate = !empty($data['db_auto_create']);

        // Step 1: Create database
        if ($autoCreate) {
            $ok = $this->createDatabase($dbHost, $dbPort, $dbUser, $dbPass, $dbName);
            $results[] = ['step' => '데이터베이스 생성', 'success' => $ok, 'error' => $ok ? '' : $this->getLastError()];
            if (!$ok) return $results;
        } else {
            $results[] = ['step' => '데이터베이스 확인', 'success' => true, 'error' => ''];
        }

        // Step 2: Import schema
        $ok = $this->importSchema($dbHost, $dbPort, $dbUser, $dbPass, $dbName);
        $results[] = ['step' => '테이블 생성', 'success' => $ok, 'error' => $ok ? '' : $this->getLastError()];
        if (!$ok) return $results;

        // Step 3: Import seed data
        $ok = $this->importSeed($dbHost, $dbPort, $dbUser, $dbPass, $dbName);
        $results[] = ['step' => '초기 데이터 입력', 'success' => $ok, 'error' => $ok ? '' : $this->getLastError()];
        if (!$ok) return $results;

        // Step 4: Write config file
        $ok = $this->writeConfig($data);
        $results[] = ['step' => '설정 파일 생성', 'success' => $ok, 'error' => $ok ? '' : $this->getLastError()];
        if (!$ok) return $results;

        // Step 5: Create admin account
        $ok = $this->createAdmin(
            $dbHost, $dbPort, $dbUser, $dbPass, $dbName,
            $data['admin_id'] ?? '',
            $data['admin_pass'] ?? '',
            $data['admin_email'] ?? '',
            $data['admin_name'] ?? ''
        );
        $results[] = ['step' => '관리자 계정 생성', 'success' => $ok, 'error' => $ok ? '' : $this->getLastError()];
        if (!$ok) return $results;

        // Step 6: Update company settings
        $this->updateCompanySettings(
            $dbHost, $dbPort, $dbUser, $dbPass, $dbName,
            $data
        );
        $results[] = ['step' => '상점 정보 저장', 'success' => true, 'error' => ''];

        return $results;
    }
}
