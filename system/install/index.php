<?php
session_start();
require_once __DIR__ . '/InstallerEngine.php';

$engine = new InstallerEngine();

$configFile = dirname(dirname(__DIR__)) . '/config/site.php';
if (file_exists($configFile)) {
    $alreadyInstalled = true;
} else {
    $alreadyInstalled = false;
}

$step = isset($_POST['step']) ? intval($_POST['step']) : (isset($_GET['step']) ? intval($_GET['step']) : 1);
$maxAllowed = isset($_SESSION['install_max_step']) ? $_SESSION['install_max_step'] : 1;

if ($step < 1) $step = 1;
if ($step > 8) $step = 8;

if ($step > $maxAllowed && !$alreadyInstalled) {
    $step = $maxAllowed;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if (!isset($_SESSION['install_data'])) {
        $_SESSION['install_data'] = [];
    }

    switch ($action) {
        case 'step1_next':
            $checks = $engine->checkRequirements();
            if ($engine->requirementsPassed($checks)) {
                $_SESSION['install_max_step'] = max($maxAllowed, 2);
                $step = 2;
            } else {
                $errors[] = '필수 요구사항을 모두 충족해야 합니다.';
                $step = 1;
            }
            break;

        case 'step2_next':
            $licenseKey = trim($_POST['license_key'] ?? '');
            if (empty($licenseKey)) {
                $errors[] = '라이선스 키를 입력해주세요.';
                $step = 2;
            } elseif (!$engine->validateLicense($licenseKey)) {
                $errors = $engine->getErrors();
                $step = 2;
            } else {
                $_SESSION['install_data']['license_key'] = strtoupper($licenseKey);
                $_SESSION['install_max_step'] = max($maxAllowed, 3);
                $step = 3;
            }
            break;

        case 'step3_next':
            $dbHost = trim($_POST['db_host'] ?? 'localhost');
            $dbPort = intval($_POST['db_port'] ?? 3306);
            $dbName = trim($_POST['db_name'] ?? '');
            $dbUser = trim($_POST['db_user'] ?? '');
            $dbPass = $_POST['db_pass'] ?? '';
            $autoCreate = isset($_POST['db_auto_create']);

            if (empty($dbName)) {
                $errors[] = '데이터베이스 이름을 입력해주세요.';
                $step = 3;
                break;
            }
            if (empty($dbUser)) {
                $errors[] = 'DB 사용자명을 입력해주세요.';
                $step = 3;
                break;
            }

            $testDbName = $autoCreate ? '' : $dbName;
            if (!$engine->testDbConnection($dbHost, $dbPort, $dbUser, $dbPass, $testDbName)) {
                if (!$autoCreate) {
                    $errors = $engine->getErrors();
                } else {
                    $connOk = $engine->testDbConnection($dbHost, $dbPort, $dbUser, $dbPass, '');
                    if (!$connOk) {
                        $errors = $engine->getErrors();
                    }
                }
                if (!empty($errors)) {
                    $step = 3;
                    break;
                }
            }

            $_SESSION['install_data']['db_host'] = $dbHost;
            $_SESSION['install_data']['db_port'] = $dbPort;
            $_SESSION['install_data']['db_name'] = $dbName;
            $_SESSION['install_data']['db_user'] = $dbUser;
            $_SESSION['install_data']['db_pass'] = $dbPass;
            $_SESSION['install_data']['db_auto_create'] = $autoCreate;
            $_SESSION['install_max_step'] = max($maxAllowed, 4);
            $step = 4;
            break;

        case 'step4_next':
            $shopName  = trim($_POST['shop_name'] ?? '');
            $shopOwner = trim($_POST['shop_owner'] ?? '');
            $shopPhone = trim($_POST['shop_phone'] ?? '');
            $shopFax   = trim($_POST['shop_fax'] ?? '');
            $shopEmail = trim($_POST['shop_email'] ?? '');
            $shopAddr  = trim($_POST['shop_address'] ?? '');
            $shopBiz   = trim($_POST['shop_biz_number'] ?? '');

            if (empty($shopName)) { $errors[] = '상호명을 입력해주세요.'; }
            if (empty($shopPhone)) { $errors[] = '전화번호를 입력해주세요.'; }
            if (empty($shopEmail)) { $errors[] = '이메일을 입력해주세요.'; }
            if (!empty($shopEmail) && !filter_var($shopEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = '올바른 이메일 형식을 입력해주세요.';
            }

            if (!empty($errors)) { $step = 4; break; }

            $logoPath = '';
            if (!empty($_FILES['shop_logo']['tmp_name'])) {
                $logoPath = $engine->handleLogoUpload($_FILES['shop_logo']);
                if (empty($logoPath) && !empty($engine->getErrors())) {
                    $errors = $engine->getErrors();
                    $step = 4;
                    break;
                }
            }

            $_SESSION['install_data']['shop_name']       = $shopName;
            $_SESSION['install_data']['shop_owner']       = $shopOwner;
            $_SESSION['install_data']['shop_phone']       = $shopPhone;
            $_SESSION['install_data']['shop_fax']         = $shopFax;
            $_SESSION['install_data']['shop_email']       = $shopEmail;
            $_SESSION['install_data']['shop_address']     = $shopAddr;
            $_SESSION['install_data']['shop_biz_number']  = $shopBiz;
            $_SESSION['install_data']['shop_logo']        = $logoPath;
            $_SESSION['install_max_step'] = max($maxAllowed, 5);
            $step = 5;
            break;

        case 'step5_next':
            $products = $_POST['products'] ?? [];
            if (empty($products)) {
                $errors[] = '최소 1개 이상의 제품을 선택해주세요.';
                $step = 5;
                break;
            }
            $validProducts = array_keys(InstallerEngine::$products);
            $products = array_intersect($products, $validProducts);
            if (empty($products)) {
                $errors[] = '유효한 제품을 선택해주세요.';
                $step = 5;
                break;
            }
            $_SESSION['install_data']['products'] = array_values($products);
            $_SESSION['install_max_step'] = max($maxAllowed, 6);
            $step = 6;
            break;

        case 'step6_next':
            $pgMid      = trim($_POST['pg_mid'] ?? '');
            $pgKey      = trim($_POST['pg_key'] ?? '');
            $pgSecret   = trim($_POST['pg_secret'] ?? '');
            $pgTestMode = isset($_POST['pg_test_mode']);
            $bankName   = trim($_POST['bank_name'] ?? '');
            $bankAccount= trim($_POST['bank_account'] ?? '');
            $bankHolder = trim($_POST['bank_holder'] ?? '');

            $_SESSION['install_data']['pg_mid']      = $pgMid;
            $_SESSION['install_data']['pg_key']      = $pgKey;
            $_SESSION['install_data']['pg_secret']   = $pgSecret;
            $_SESSION['install_data']['pg_test_mode'] = $pgTestMode;
            $_SESSION['install_data']['bank_name']    = $bankName;
            $_SESSION['install_data']['bank_account'] = $bankAccount;
            $_SESSION['install_data']['bank_holder']  = $bankHolder;
            $_SESSION['install_max_step'] = max($maxAllowed, 7);
            $step = 7;
            break;

        case 'step7_next':
            $adminId    = trim($_POST['admin_id'] ?? '');
            $adminPass  = $_POST['admin_pass'] ?? '';
            $adminPass2 = $_POST['admin_pass_confirm'] ?? '';
            $adminEmail = trim($_POST['admin_email'] ?? '');
            $adminName  = trim($_POST['admin_name'] ?? '');

            if (empty($adminId) || mb_strlen($adminId) < 4) {
                $errors[] = '관리자 아이디는 4자 이상이어야 합니다.';
            }
            if (empty($adminPass) || mb_strlen($adminPass) < 8) {
                $errors[] = '비밀번호는 8자 이상이어야 합니다.';
            }
            if ($adminPass !== $adminPass2) {
                $errors[] = '비밀번호가 일치하지 않습니다.';
            }
            if (empty($adminEmail) || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = '올바른 이메일을 입력해주세요.';
            }

            if (!empty($errors)) { $step = 7; break; }

            $_SESSION['install_data']['admin_id']    = $adminId;
            $_SESSION['install_data']['admin_pass']  = $adminPass;
            $_SESSION['install_data']['admin_email'] = $adminEmail;
            $_SESSION['install_data']['admin_name']  = $adminName;
            $_SESSION['install_max_step'] = max($maxAllowed, 8);
            $step = 8;
            break;

        case 'go_back':
            $targetStep = intval($_POST['target_step'] ?? 1);
            if ($targetStep >= 1 && $targetStep <= $maxAllowed) {
                $step = $targetStep;
            }
            break;
    }
}

$d = $_SESSION['install_data'] ?? [];

$stepLabels = [
    1 => '환경확인',
    2 => '라이선스',
    3 => '데이터베이스',
    4 => '상점정보',
    5 => '제품선택',
    6 => '결제설정',
    7 => '관리자',
    8 => '설치완료',
];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>두손 프린트 시스템 설치</title>
    <link rel="stylesheet" href="assets/installer.css">
</head>
<body>

<header class="installer-header">
    <h1>두손 프린트 시스템
        <span>Installation Wizard v1.0</span>
    </h1>
</header>

<nav class="step-progress">
    <?php foreach ($stepLabels as $num => $label): ?>
        <?php
        $cls = '';
        if ($num < $step) $cls = 'completed';
        elseif ($num === $step) $cls = 'active';
        ?>
        <div class="step-item <?php echo $cls; ?>">
            <div class="step-circle"><span class="step-num"><?php echo $num; ?></span></div>
            <div class="step-label"><?php echo htmlspecialchars($label); ?></div>
        </div>
    <?php endforeach; ?>
</nav>

<main class="installer-main">

<?php if ($alreadyInstalled && $step < 8): ?>
    <div class="step-card">
        <div class="alert alert-warn">
            이미 설치가 완료된 시스템입니다. 재설치하려면 <code>config/site.php</code> 파일을 삭제하고 다시 시도하세요.
        </div>
        <div class="btn-row">
            <span></span>
            <a href="/" class="btn btn-primary">사이트 바로가기</a>
        </div>
    </div>

<?php elseif ($step === 1): ?>
    <?php $checks = $engine->checkRequirements(); $allPass = $engine->requirementsPassed($checks); ?>
    <div class="step-card">
        <h2 class="step-card-title">서버 환경 확인</h2>
        <p class="step-card-desc">시스템 설치에 필요한 서버 환경을 점검합니다.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <table class="req-table">
            <thead>
                <tr><th>항목</th><th>요구사항</th><th>현재상태</th><th>결과</th></tr>
            </thead>
            <tbody>
            <?php foreach ($checks as $check): ?>
                <tr>
                    <td><?php echo htmlspecialchars($check['label']); ?></td>
                    <td><?php echo htmlspecialchars($check['required']); ?></td>
                    <td><?php echo htmlspecialchars($check['current']); ?></td>
                    <td>
                        <?php if ($check['status'] === 'pass'): ?>
                            <span class="status-icon pass">&#10004; 통과</span>
                        <?php elseif ($check['status'] === 'fail'): ?>
                            <span class="status-icon fail">&#10008; 실패</span>
                        <?php else: ?>
                            <span class="status-icon warn">&#9888; 경고</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <form method="post">
            <input type="hidden" name="action" value="step1_next">
            <div class="btn-row">
                <span></span>
                <button type="submit" class="btn btn-primary" <?php echo $allPass ? '' : 'disabled'; ?>>
                    다음 단계 &rarr;
                </button>
            </div>
        </form>
    </div>

<?php elseif ($step === 2): ?>
    <div class="step-card">
        <h2 class="step-card-title">라이선스 인증</h2>
        <p class="step-card-desc">구매 시 발급받은 라이선스 키를 입력해주세요.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <div class="alert alert-info">
            라이선스 키 형식: <strong>DUSON-XXXX-XXXX-XXXX</strong><br>
            평가판을 사용하시려면 <code>DUSON-FREE-TRIAL-2026</code>을 입력하세요.
        </div>

        <form method="post">
            <input type="hidden" name="action" value="step2_next">
            <div class="form-group">
                <label class="form-label">라이선스 키 <span class="required">*</span></label>
                <div class="license-input-wrap">
                    <input type="text" name="license_key" class="form-input mono"
                           placeholder="DUSON-XXXX-XXXX-XXXX"
                           value="<?php echo htmlspecialchars($d['license_key'] ?? ''); ?>"
                           maxlength="19" autocomplete="off" required>
                </div>
            </div>
            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="goBack(1)">&larr; 이전</button>
                <button type="submit" class="btn btn-primary">다음 단계 &rarr;</button>
            </div>
        </form>
    </div>

<?php elseif ($step === 3): ?>
    <div class="step-card">
        <h2 class="step-card-title">데이터베이스 설정</h2>
        <p class="step-card-desc">MySQL 데이터베이스 접속 정보를 입력해주세요.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <form method="post" id="dbForm">
            <input type="hidden" name="action" value="step3_next">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">호스트 <span class="required">*</span></label>
                    <input type="text" name="db_host" class="form-input"
                           value="<?php echo htmlspecialchars($d['db_host'] ?? 'localhost'); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">포트</label>
                    <input type="number" name="db_port" class="form-input"
                           value="<?php echo intval($d['db_port'] ?? 3306); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">데이터베이스 이름 <span class="required">*</span></label>
                <input type="text" name="db_name" class="form-input mono"
                       value="<?php echo htmlspecialchars($d['db_name'] ?? ''); ?>"
                       placeholder="duson_print" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">사용자명 <span class="required">*</span></label>
                    <input type="text" name="db_user" class="form-input"
                           value="<?php echo htmlspecialchars($d['db_user'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">비밀번호</label>
                    <input type="password" name="db_pass" class="form-input"
                           value="<?php echo htmlspecialchars($d['db_pass'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="db_auto_create" value="1"
                           <?php echo !empty($d['db_auto_create']) ? 'checked' : 'checked'; ?>>
                    데이터베이스가 없으면 자동 생성
                </label>
            </div>

            <button type="button" class="btn btn-test btn-sm" id="btnTestDb">
                <span class="spinner" style="display:none" id="dbSpinner"></span>
                연결 테스트
            </button>
            <div class="db-test-result" id="dbTestResult"></div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="goBack(2)">&larr; 이전</button>
                <button type="submit" class="btn btn-primary">다음 단계 &rarr;</button>
            </div>
        </form>
    </div>

<?php elseif ($step === 4): ?>
    <div class="step-card">
        <h2 class="step-card-title">상점 정보</h2>
        <p class="step-card-desc">인쇄소의 기본 정보를 입력해주세요. 견적서와 주문서에 표시됩니다.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="step4_next">

            <div class="form-group">
                <label class="form-label">상호명 <span class="required">*</span></label>
                <input type="text" name="shop_name" class="form-input"
                       value="<?php echo htmlspecialchars($d['shop_name'] ?? ''); ?>"
                       placeholder="예: 두손기획인쇄" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">대표자명</label>
                    <input type="text" name="shop_owner" class="form-input"
                           value="<?php echo htmlspecialchars($d['shop_owner'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">사업자등록번호</label>
                    <input type="text" name="shop_biz_number" class="form-input"
                           value="<?php echo htmlspecialchars($d['shop_biz_number'] ?? ''); ?>"
                           placeholder="000-00-00000">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">전화번호 <span class="required">*</span></label>
                    <input type="tel" name="shop_phone" class="form-input"
                           value="<?php echo htmlspecialchars($d['shop_phone'] ?? ''); ?>"
                           placeholder="02-0000-0000" required>
                </div>
                <div class="form-group">
                    <label class="form-label">팩스번호</label>
                    <input type="tel" name="shop_fax" class="form-input"
                           value="<?php echo htmlspecialchars($d['shop_fax'] ?? ''); ?>"
                           placeholder="02-0000-0000">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">이메일 <span class="required">*</span></label>
                <input type="email" name="shop_email" class="form-input"
                       value="<?php echo htmlspecialchars($d['shop_email'] ?? ''); ?>"
                       placeholder="info@example.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">주소</label>
                <input type="text" name="shop_address" class="form-input"
                       value="<?php echo htmlspecialchars($d['shop_address'] ?? ''); ?>"
                       placeholder="서울시 영등포구 ...">
            </div>

            <div class="form-group">
                <label class="form-label">로고 이미지 <span class="optional">(선택)</span></label>
                <input type="file" name="shop_logo" class="form-input" accept="image/jpeg,image/png,image/gif">
                <p class="form-hint">JPG, PNG, GIF / 최대 2MB / 권장 크기: 300x100px</p>
                <?php if (!empty($d['shop_logo'])): ?>
                    <p class="form-hint" style="color: var(--c-success);">&#10004; 업로드 완료: <?php echo htmlspecialchars($d['shop_logo']); ?></p>
                <?php endif; ?>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="goBack(3)">&larr; 이전</button>
                <button type="submit" class="btn btn-primary">다음 단계 &rarr;</button>
            </div>
        </form>
    </div>

<?php elseif ($step === 5): ?>
    <div class="step-card">
        <h2 class="step-card-title">제품 선택</h2>
        <p class="step-card-desc">운영할 인쇄 제품을 선택해주세요. 나중에 관리자 페이지에서 변경할 수 있습니다.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="step5_next">

            <div style="margin-bottom: 1rem; display: flex; gap: 0.5rem;">
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllProducts(true)">전체 선택</button>
                <button type="button" class="btn btn-sm btn-secondary" onclick="toggleAllProducts(false)">전체 해제</button>
            </div>

            <div class="product-grid">
                <?php
                $selectedProducts = $d['products'] ?? array_keys(InstallerEngine::$products);
                foreach (InstallerEngine::$products as $key => $prod):
                    $checked = in_array($key, $selectedProducts) ? 'checked' : '';
                    $selectedClass = $checked ? 'selected' : '';
                ?>
                <label class="product-card <?php echo $selectedClass; ?>" data-product="<?php echo $key; ?>">
                    <input type="checkbox" name="products[]" value="<?php echo $key; ?>" <?php echo $checked; ?>>
                    <div class="product-icon"><?php echo $prod['icon']; ?></div>
                    <div class="product-name"><?php echo htmlspecialchars($prod['name']); ?></div>
                    <div class="product-unit">단위: <?php echo htmlspecialchars($prod['unit']); ?></div>
                    <div class="product-desc"><?php echo htmlspecialchars($prod['desc']); ?></div>
                </label>
                <?php endforeach; ?>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="goBack(4)">&larr; 이전</button>
                <button type="submit" class="btn btn-primary">다음 단계 &rarr;</button>
            </div>
        </form>
    </div>

<?php elseif ($step === 6): ?>
    <div class="step-card">
        <h2 class="step-card-title">결제 설정</h2>
        <p class="step-card-desc">온라인 결제 및 계좌이체 정보를 설정합니다. 나중에 변경 가능합니다.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="step6_next">

            <div class="section-title">&#128179; PG사 설정 (KG이니시스)</div>

            <div class="form-group">
                <label class="form-label">상점 MID</label>
                <input type="text" name="pg_mid" class="form-input mono"
                       value="<?php echo htmlspecialchars($d['pg_mid'] ?? ''); ?>"
                       placeholder="INIpayTest (테스트용)">
                <p class="form-hint">KG이니시스에서 발급받은 상점 아이디</p>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">API 키</label>
                    <input type="text" name="pg_key" class="form-input mono"
                           value="<?php echo htmlspecialchars($d['pg_key'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">API 시크릿</label>
                    <input type="password" name="pg_secret" class="form-input"
                           value="<?php echo htmlspecialchars($d['pg_secret'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="pg_test_mode" value="1"
                           <?php echo (isset($d['pg_test_mode']) ? ($d['pg_test_mode'] ? 'checked' : '') : 'checked'); ?>>
                    테스트 모드로 시작 (실결제 없음)
                </label>
            </div>

            <div class="section-divider">
                <div class="section-title">&#127974; 계좌이체 정보</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">은행명</label>
                    <select name="bank_name" class="form-input">
                        <option value="">선택하세요</option>
                        <?php
                        $banks = ['국민은행','신한은행','우리은행','하나은행','농협은행','기업은행','SC제일은행','씨티은행','케이뱅크','카카오뱅크','토스뱅크'];
                        foreach ($banks as $b) {
                            $sel = ($d['bank_name'] ?? '') === $b ? 'selected' : '';
                            echo "<option value=\"{$b}\" {$sel}>{$b}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">예금주</label>
                    <input type="text" name="bank_holder" class="form-input"
                           value="<?php echo htmlspecialchars($d['bank_holder'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">계좌번호</label>
                <input type="text" name="bank_account" class="form-input mono"
                       value="<?php echo htmlspecialchars($d['bank_account'] ?? ''); ?>"
                       placeholder="000-000000-00-000">
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="goBack(5)">&larr; 이전</button>
                <button type="submit" class="btn btn-primary">다음 단계 &rarr;</button>
            </div>
        </form>
    </div>

<?php elseif ($step === 7): ?>
    <div class="step-card">
        <h2 class="step-card-title">관리자 계정</h2>
        <p class="step-card-desc">시스템 관리자 계정을 생성합니다. 설치 후 이 계정으로 로그인합니다.</p>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars(implode('<br>', $errors)); ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="step7_next">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">관리자 아이디 <span class="required">*</span></label>
                    <input type="text" name="admin_id" class="form-input"
                           value="<?php echo htmlspecialchars($d['admin_id'] ?? ''); ?>"
                           placeholder="admin" minlength="4" required>
                    <p class="form-hint">4자 이상, 영문/숫자</p>
                </div>
                <div class="form-group">
                    <label class="form-label">이름</label>
                    <input type="text" name="admin_name" class="form-input"
                           value="<?php echo htmlspecialchars($d['admin_name'] ?? ''); ?>"
                           placeholder="관리자">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">비밀번호 <span class="required">*</span></label>
                <input type="password" name="admin_pass" class="form-input" id="adminPass"
                       placeholder="8자 이상" minlength="8" required
                       oninput="checkPasswordStrength(this.value)">
                <div class="pw-strength">
                    <div class="pw-strength-bar" id="pwBar1"></div>
                    <div class="pw-strength-bar" id="pwBar2"></div>
                    <div class="pw-strength-bar" id="pwBar3"></div>
                    <div class="pw-strength-bar" id="pwBar4"></div>
                </div>
                <p class="pw-strength-text" id="pwStrengthText"></p>
            </div>

            <div class="form-group">
                <label class="form-label">비밀번호 확인 <span class="required">*</span></label>
                <input type="password" name="admin_pass_confirm" class="form-input" id="adminPassConfirm"
                       placeholder="비밀번호를 다시 입력" required
                       oninput="checkPasswordMatch()">
                <p class="form-error" id="pwMatchError">비밀번호가 일치하지 않습니다.</p>
            </div>

            <div class="form-group">
                <label class="form-label">이메일 <span class="required">*</span></label>
                <input type="email" name="admin_email" class="form-input"
                       value="<?php echo htmlspecialchars($d['admin_email'] ?? ''); ?>"
                       placeholder="admin@example.com" required>
            </div>

            <div class="btn-row">
                <button type="button" class="btn btn-secondary" onclick="goBack(6)">&larr; 이전</button>
                <button type="submit" class="btn btn-primary" id="btnStep7Next">설치 시작 &rarr;</button>
            </div>
        </form>
    </div>

<?php elseif ($step === 8): ?>
    <div class="step-card">
        <h2 class="step-card-title">시스템 설치</h2>
        <p class="step-card-desc">설치가 진행됩니다. 브라우저를 닫지 마세요.</p>

        <div class="install-progress" id="installProgress">
            <div class="progress-step" id="prog-1" data-label="데이터베이스 생성 중...">
                <div class="progress-icon">&#9711;</div>
                <div class="progress-label">데이터베이스 생성 중...</div>
            </div>
            <div class="progress-step" id="prog-2" data-label="테이블 생성 중...">
                <div class="progress-icon">&#9711;</div>
                <div class="progress-label">테이블 생성 중...</div>
            </div>
            <div class="progress-step" id="prog-3" data-label="초기 데이터 입력 중...">
                <div class="progress-icon">&#9711;</div>
                <div class="progress-label">초기 데이터 입력 중...</div>
            </div>
            <div class="progress-step" id="prog-4" data-label="설정 파일 생성 중...">
                <div class="progress-icon">&#9711;</div>
                <div class="progress-label">설정 파일 생성 중...</div>
            </div>
            <div class="progress-step" id="prog-5" data-label="관리자 계정 생성 중...">
                <div class="progress-icon">&#9711;</div>
                <div class="progress-label">관리자 계정 생성 중...</div>
            </div>
        </div>

        <div id="installResult" style="display:none;"></div>
    </div>

<?php endif; ?>

</main>

<footer class="installer-footer">
    &copy; <?php echo date('Y'); ?> 두손 프린트 시스템 &middot; Powered by Duson Planning
</footer>

<script>
function goBack(targetStep) {
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '';

    var actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'go_back';
    form.appendChild(actionInput);

    var stepInput = document.createElement('input');
    stepInput.type = 'hidden';
    stepInput.name = 'target_step';
    stepInput.value = targetStep;
    form.appendChild(stepInput);

    document.body.appendChild(form);
    form.submit();
}

/* DB Connection Test */
var btnTestDb = document.getElementById('btnTestDb');
if (btnTestDb) {
    btnTestDb.addEventListener('click', function() {
        var spinner = document.getElementById('dbSpinner');
        var resultDiv = document.getElementById('dbTestResult');
        var form = document.getElementById('dbForm');

        var data = new FormData();
        data.append('action', 'test_db');
        data.append('db_host', form.querySelector('[name="db_host"]').value);
        data.append('db_port', form.querySelector('[name="db_port"]').value);
        data.append('db_name', form.querySelector('[name="db_name"]').value);
        data.append('db_user', form.querySelector('[name="db_user"]').value);
        data.append('db_pass', form.querySelector('[name="db_pass"]').value);

        spinner.style.display = 'inline-block';
        btnTestDb.disabled = true;
        resultDiv.className = 'db-test-result';
        resultDiv.style.display = 'none';

        fetch('ajax.php', { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                spinner.style.display = 'none';
                btnTestDb.disabled = false;
                resultDiv.className = 'db-test-result show ' + (json.success ? 'success' : 'fail');
                resultDiv.innerHTML = json.success
                    ? '&#10004; ' + json.message
                    : '&#10008; ' + json.message;
            })
            .catch(function(err) {
                spinner.style.display = 'none';
                btnTestDb.disabled = false;
                resultDiv.className = 'db-test-result show fail';
                resultDiv.textContent = '연결 테스트 중 오류가 발생했습니다.';
            });
    });
}

/* Product Card Toggle */
document.querySelectorAll('.product-card').forEach(function(card) {
    var cb = card.querySelector('input[type="checkbox"]');
    if (!cb) return;
    cb.addEventListener('change', function() {
        card.classList.toggle('selected', cb.checked);
    });
});

function toggleAllProducts(state) {
    document.querySelectorAll('.product-card').forEach(function(card) {
        var cb = card.querySelector('input[type="checkbox"]');
        if (cb) {
            cb.checked = state;
            card.classList.toggle('selected', state);
        }
    });
}

/* Password Strength */
function checkPasswordStrength(pw) {
    var score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
    if (/\d/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;

    var level = 'weak';
    var text = '';
    if (score <= 1) { level = 'weak'; text = '약함'; }
    else if (score <= 2) { level = 'weak'; text = '보통 이하'; }
    else if (score <= 3) { level = 'medium'; text = '보통'; }
    else if (score <= 4) { level = 'strong'; text = '강함'; }
    else { level = 'strong'; text = '매우 강함'; }

    for (var i = 1; i <= 4; i++) {
        var bar = document.getElementById('pwBar' + i);
        if (bar) {
            bar.className = 'pw-strength-bar';
            if (i <= score) {
                bar.classList.add('active', level);
            }
        }
    }
    var textEl = document.getElementById('pwStrengthText');
    if (textEl) textEl.textContent = pw.length > 0 ? ('비밀번호 강도: ' + text) : '';
}

function checkPasswordMatch() {
    var pw = document.getElementById('adminPass');
    var pw2 = document.getElementById('adminPassConfirm');
    var err = document.getElementById('pwMatchError');
    if (!pw || !pw2 || !err) return;
    if (pw2.value.length > 0 && pw.value !== pw2.value) {
        err.classList.add('visible');
        pw2.classList.add('error');
    } else {
        err.classList.remove('visible');
        pw2.classList.remove('error');
    }
}

/* Step 8: Installation */
<?php if ($step === 8 && !$alreadyInstalled): ?>
(function() {
    var stepNames = [
        '데이터베이스 생성 중...',
        '테이블 생성 중...',
        '초기 데이터 입력 중...',
        '설정 파일 생성 중...',
        '관리자 계정 생성 중...'
    ];

    var currentStep = 0;

    function animateStep(idx) {
        if (idx >= stepNames.length) return;
        var el = document.getElementById('prog-' + (idx + 1));
        if (el) {
            el.classList.add('running');
            el.querySelector('.progress-icon').innerHTML = '&#8635;';
        }
    }

    function completeStep(idx, success, errorMsg) {
        var el = document.getElementById('prog-' + (idx + 1));
        if (!el) return;
        el.classList.remove('running');
        if (success) {
            el.classList.add('done');
            el.querySelector('.progress-icon').innerHTML = '&#10004;';
        } else {
            el.classList.add('error');
            el.querySelector('.progress-icon').innerHTML = '&#10008;';
            if (errorMsg) {
                el.querySelector('.progress-label').textContent = errorMsg;
            }
        }
    }

    function runInstall() {
        animateStep(0);

        fetch('ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'install' })
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            var results = json.results || [];
            var delay = 0;

            for (var i = 0; i < stepNames.length; i++) {
                (function(idx) {
                    setTimeout(function() {
                        completeStep(idx, true);
                        if (idx + 1 < stepNames.length) {
                            animateStep(idx + 1);
                        }
                    }, delay);
                })(i);
                delay += 600;
            }

            if (json.success) {
                setTimeout(function() {
                    var resultDiv = document.getElementById('installResult');
                    resultDiv.style.display = 'block';
                    resultDiv.innerHTML = '<div class="complete-box">' +
                        '<div class="complete-icon">&#127881;</div>' +
                        '<div class="complete-title">설치가 완료되었습니다!</div>' +
                        '<div class="complete-msg">' +
                            '<strong><?php echo htmlspecialchars($d['shop_name'] ?? ''); ?></strong>의 인쇄 주문 시스템이 성공적으로 설치되었습니다.<br>' +
                            '관리자 아이디: <code><?php echo htmlspecialchars($d['admin_id'] ?? ''); ?></code>' +
                        '</div>' +
                        '<div class="complete-links">' +
                            '<a href="/" class="btn btn-primary">사이트 바로가기</a>' +
                            '<a href="/admin/" class="btn btn-success">관리자 페이지</a>' +
                        '</div>' +
                        '<div class="alert alert-warn" style="text-align:left; max-width:460px; margin:0 auto;">' +
                            '&#9888;&#65039; <strong>보안 경고</strong>: 설치가 완료되었으므로 <code>system/install/</code> 폴더를 반드시 삭제해주세요.' +
                        '</div>' +
                    '</div>';
                }, delay + 400);
            } else {
                var failIdx = results.length - 1;
                var errorMsg = results.length > 0 ? results[failIdx].error : '알 수 없는 오류';

                setTimeout(function() {
                    for (var j = failIdx; j < stepNames.length; j++) {
                        if (j === failIdx) {
                            completeStep(j, false, errorMsg);
                        }
                    }

                    var resultDiv = document.getElementById('installResult');
                    resultDiv.style.display = 'block';
                    resultDiv.innerHTML = '<div class="alert alert-error">' +
                        '<strong>설치 중 오류가 발생했습니다.</strong><br>' + (errorMsg || '') +
                        '<br><br>설정을 확인한 후 다시 시도해주세요.' +
                        '</div>' +
                        '<div style="text-align:center; margin-top:1rem;">' +
                            '<button class="btn btn-secondary" onclick="goBack(3)">데이터베이스 설정으로 돌아가기</button>' +
                        '</div>';
                }, delay);
            }
        })
        .catch(function(err) {
            document.getElementById('installResult').style.display = 'block';
            document.getElementById('installResult').innerHTML =
                '<div class="alert alert-error">서버 통신 오류가 발생했습니다. 페이지를 새로고침하고 다시 시도해주세요.</div>';
        });
    }

    setTimeout(runInstall, 500);
})();
<?php endif; ?>
</script>

</body>
</html>
