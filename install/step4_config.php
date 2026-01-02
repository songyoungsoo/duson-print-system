<?php
/**
 * Step 4: 사이트 설정 (회사 정보, 이메일 설정)
 */

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 회사 정보
    $company_name = trim($_POST['company_name'] ?? '');
    $company_owner = trim($_POST['company_owner'] ?? '');
    $company_phone = trim($_POST['company_phone'] ?? '');
    $company_address = trim($_POST['company_address'] ?? '');
    $company_email = trim($_POST['company_email'] ?? '');
    $company_bizno = trim($_POST['company_bizno'] ?? '');

    // 은행 정보
    $bank1_name = trim($_POST['bank1_name'] ?? '');
    $bank1_account = trim($_POST['bank1_account'] ?? '');
    $bank2_name = trim($_POST['bank2_name'] ?? '');
    $bank2_account = trim($_POST['bank2_account'] ?? '');
    $bank3_name = trim($_POST['bank3_name'] ?? '');
    $bank3_account = trim($_POST['bank3_account'] ?? '');
    $bank_holder = trim($_POST['bank_holder'] ?? '');

    // SMTP 설정
    $smtp_host = trim($_POST['smtp_host'] ?? '');
    $smtp_port = intval($_POST['smtp_port'] ?? 465);
    $smtp_user = trim($_POST['smtp_user'] ?? '');
    $smtp_pass = $_POST['smtp_pass'] ?? '';
    $smtp_secure = $_POST['smtp_secure'] ?? 'ssl';

    // 유효성 검사
    if (empty($company_name) || empty($company_phone)) {
        $error = '회사명과 전화번호는 필수입니다.';
    } else {
        // 설정 저장
        $_SESSION['config'] = [
            'company' => [
                'name' => $company_name,
                'owner' => $company_owner,
                'phone' => $company_phone,
                'address' => $company_address,
                'email' => $company_email,
                'bizno' => $company_bizno
            ],
            'bank' => [
                'bank1_name' => $bank1_name,
                'bank1_account' => $bank1_account,
                'bank2_name' => $bank2_name,
                'bank2_account' => $bank2_account,
                'bank3_name' => $bank3_name,
                'bank3_account' => $bank3_account,
                'holder' => $bank_holder
            ],
            'smtp' => [
                'host' => $smtp_host,
                'port' => $smtp_port,
                'user' => $smtp_user,
                'pass' => $smtp_pass,
                'secure' => $smtp_secure
            ]
        ];

        // 설정 파일 생성
        $config_content = generate_config_file($_SESSION);

        if (file_put_contents('../config.env.php', $config_content)) {
            // 설치 완료 플래그 생성
            file_put_contents('../config.installed.php', '<?php // Installed on ' . date('Y-m-d H:i:s'));

            header('Location: ?step=5');
            exit;
        } else {
            $error = '설정 파일 생성 실패. 디렉토리 쓰기 권한을 확인하세요.';
        }
    }
}

function generate_config_file($session) {
    $config = $session['config'];
    $db = $session;

    return '<?php
/**
 * 두손기획 인쇄몰 환경 설정
 * 자동 생성됨: ' . date('Y-m-d H:i:s') . '
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
                "host" => "' . addslashes($db['db_host']) . '",
                "name" => "' . addslashes($db['db_name']) . '",
                "user" => "' . addslashes($db['db_user']) . '",
                "pass" => "' . addslashes($db['db_pass']) . '"
            ],
            "company" => [
                "name" => "' . addslashes($config['company']['name']) . '",
                "owner" => "' . addslashes($config['company']['owner']) . '",
                "phone" => "' . addslashes($config['company']['phone']) . '",
                "address" => "' . addslashes($config['company']['address']) . '",
                "email" => "' . addslashes($config['company']['email']) . '",
                "bizno" => "' . addslashes($config['company']['bizno']) . '"
            ],
            "bank" => [
                "bank1" => ["name" => "' . addslashes($config['bank']['bank1_name']) . '", "account" => "' . addslashes($config['bank']['bank1_account']) . '"],
                "bank2" => ["name" => "' . addslashes($config['bank']['bank2_name']) . '", "account" => "' . addslashes($config['bank']['bank2_account']) . '"],
                "bank3" => ["name" => "' . addslashes($config['bank']['bank3_name']) . '", "account" => "' . addslashes($config['bank']['bank3_account']) . '"],
                "holder" => "' . addslashes($config['bank']['holder']) . '"
            ],
            "smtp" => [
                "host" => "' . addslashes($config['smtp']['host']) . '",
                "port" => ' . intval($config['smtp']['port']) . ',
                "user" => "' . addslashes($config['smtp']['user']) . '",
                "pass" => "' . addslashes($config['smtp']['pass']) . '",
                "secure" => "' . addslashes($config['smtp']['secure']) . '"
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

// 기본값
$config = $_SESSION['config'] ?? [];
$company = $config['company'] ?? [];
$bank = $config['bank'] ?? [];
$smtp = $config['smtp'] ?? [];
?>

<h2 class="step-title">Step 4: 사이트 설정</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<form method="post">
    <h3 style="margin: 20px 0 15px; color: #4A6741; font-weight: 600;">회사 정보</h3>

    <div class="form-row">
        <div class="form-group">
            <label>회사명 / 상호 *</label>
            <input type="text" name="company_name" value="<?php echo htmlspecialchars($company['name'] ?? ''); ?>" required placeholder="예: 두손기획인쇄">
        </div>

        <div class="form-group">
            <label>대표자명</label>
            <input type="text" name="company_owner" value="<?php echo htmlspecialchars($company['owner'] ?? ''); ?>" placeholder="예: 차경선">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>대표 전화번호 *</label>
            <input type="text" name="company_phone" value="<?php echo htmlspecialchars($company['phone'] ?? ''); ?>" required placeholder="예: 1688-2384">
        </div>

        <div class="form-group">
            <label>사업자 등록번호</label>
            <input type="text" name="company_bizno" value="<?php echo htmlspecialchars($company['bizno'] ?? ''); ?>" placeholder="예: 123-45-67890">
        </div>
    </div>

    <div class="form-group">
        <label>주소</label>
        <input type="text" name="company_address" value="<?php echo htmlspecialchars($company['address'] ?? ''); ?>" placeholder="예: 서울 영등포구 영등포로36길 9 송호빌딩 1층">
    </div>

    <div class="form-group">
        <label>대표 이메일</label>
        <input type="email" name="company_email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" placeholder="예: info@company.com">
    </div>

    <h3 style="margin: 30px 0 15px; color: #4A6741; font-weight: 600;">입금 계좌 정보</h3>

    <div class="form-row">
        <div class="form-group">
            <label>은행1 이름</label>
            <input type="text" name="bank1_name" value="<?php echo htmlspecialchars($bank['bank1_name'] ?? ''); ?>" placeholder="예: 국민은행">
        </div>
        <div class="form-group">
            <label>은행1 계좌번호</label>
            <input type="text" name="bank1_account" value="<?php echo htmlspecialchars($bank['bank1_account'] ?? ''); ?>" placeholder="예: 999-1688-2384">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>은행2 이름</label>
            <input type="text" name="bank2_name" value="<?php echo htmlspecialchars($bank['bank2_name'] ?? ''); ?>" placeholder="예: 신한은행">
        </div>
        <div class="form-group">
            <label>은행2 계좌번호</label>
            <input type="text" name="bank2_account" value="<?php echo htmlspecialchars($bank['bank2_account'] ?? ''); ?>" placeholder="예: 110-342-543507">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>은행3 이름</label>
            <input type="text" name="bank3_name" value="<?php echo htmlspecialchars($bank['bank3_name'] ?? ''); ?>" placeholder="예: 농협">
        </div>
        <div class="form-group">
            <label>은행3 계좌번호</label>
            <input type="text" name="bank3_account" value="<?php echo htmlspecialchars($bank['bank3_account'] ?? ''); ?>" placeholder="예: 301-2632-1830-11">
        </div>
    </div>

    <div class="form-group">
        <label>예금주</label>
        <input type="text" name="bank_holder" value="<?php echo htmlspecialchars($bank['holder'] ?? ''); ?>" placeholder="예: 차경선 두손기획인쇄">
    </div>

    <h3 style="margin: 30px 0 15px; color: #4A6741; font-weight: 600;">이메일 (SMTP) 설정</h3>

    <div class="form-row">
        <div class="form-group">
            <label>SMTP 서버</label>
            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($smtp['host'] ?? 'smtp.naver.com'); ?>" placeholder="예: smtp.naver.com">
        </div>
        <div class="form-group">
            <label>SMTP 포트</label>
            <input type="number" name="smtp_port" value="<?php echo intval($smtp['port'] ?? 465); ?>" placeholder="465">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>SMTP 사용자</label>
            <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($smtp['user'] ?? ''); ?>" placeholder="예: myaccount">
        </div>
        <div class="form-group">
            <label>SMTP 비밀번호</label>
            <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($smtp['pass'] ?? ''); ?>">
        </div>
    </div>

    <div class="form-group">
        <label>보안 방식</label>
        <select name="smtp_secure">
            <option value="ssl" <?php echo ($smtp['secure'] ?? 'ssl') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
            <option value="tls" <?php echo ($smtp['secure'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
            <option value="" <?php echo empty($smtp['secure'] ?? 'ssl') ? 'selected' : ''; ?>>없음</option>
        </select>
    </div>

    <div class="btn-group">
        <a href="?step=3" class="btn btn-secondary">← 이전</a>
        <button type="submit" class="btn btn-success">설치 완료 →</button>
    </div>
</form>
