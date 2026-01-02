<?php
/**
 * Step 1: 시스템 요구사항 확인
 */

$requirements = [];
$all_passed = true;

// PHP 버전 체크
$php_version = phpversion();
$php_required = '7.4.0';
$php_pass = version_compare($php_version, $php_required, '>=');
$requirements[] = [
    'name' => 'PHP 버전',
    'required' => $php_required . ' 이상',
    'current' => $php_version,
    'status' => $php_pass ? 'pass' : 'fail'
];
if (!$php_pass) $all_passed = false;

// MySQL 확장 체크
$mysqli_pass = extension_loaded('mysqli');
$requirements[] = [
    'name' => 'MySQLi 확장',
    'required' => '필수',
    'current' => $mysqli_pass ? '설치됨' : '미설치',
    'status' => $mysqli_pass ? 'pass' : 'fail'
];
if (!$mysqli_pass) $all_passed = false;

// JSON 확장 체크
$json_pass = extension_loaded('json');
$requirements[] = [
    'name' => 'JSON 확장',
    'required' => '필수',
    'current' => $json_pass ? '설치됨' : '미설치',
    'status' => $json_pass ? 'pass' : 'fail'
];
if (!$json_pass) $all_passed = false;

// mbstring 확장 체크
$mbstring_pass = extension_loaded('mbstring');
$requirements[] = [
    'name' => 'mbstring 확장',
    'required' => '필수 (한글 처리)',
    'current' => $mbstring_pass ? '설치됨' : '미설치',
    'status' => $mbstring_pass ? 'pass' : 'fail'
];
if (!$mbstring_pass) $all_passed = false;

// GD 확장 체크 (이미지 처리)
$gd_pass = extension_loaded('gd');
$requirements[] = [
    'name' => 'GD 확장',
    'required' => '권장 (이미지 처리)',
    'current' => $gd_pass ? '설치됨' : '미설치',
    'status' => $gd_pass ? 'pass' : 'warn'
];

// cURL 확장 체크 (이메일 발송)
$curl_pass = extension_loaded('curl');
$requirements[] = [
    'name' => 'cURL 확장',
    'required' => '권장 (외부 통신)',
    'current' => $curl_pass ? '설치됨' : '미설치',
    'status' => $curl_pass ? 'pass' : 'warn'
];

// OpenSSL 확장 체크 (SMTP SSL)
$openssl_pass = extension_loaded('openssl');
$requirements[] = [
    'name' => 'OpenSSL 확장',
    'required' => '필수 (이메일 SSL)',
    'current' => $openssl_pass ? '설치됨' : '미설치',
    'status' => $openssl_pass ? 'pass' : 'fail'
];
if (!$openssl_pass) $all_passed = false;

// 디렉토리 쓰기 권한 체크
$writable_dirs = [
    '../' => '루트 디렉토리',
    '../ImgFolder/' => '이미지 업로드',
    '../mlangorder_printauto/upload/' => '주문 파일 업로드'
];
// 참고: PHP 세션은 시스템 기본 경로(/tmp)를 사용합니다

foreach ($writable_dirs as $dir => $name) {
    $is_writable = is_writable($dir) || !file_exists($dir);
    $requirements[] = [
        'name' => $name . ' 쓰기 권한',
        'required' => '쓰기 가능',
        'current' => file_exists($dir) ? (is_writable($dir) ? '쓰기 가능' : '쓰기 불가') : '생성 예정',
        'status' => $is_writable ? 'pass' : 'fail'
    ];
    if (!$is_writable && file_exists($dir)) $all_passed = false;
}

// 세션에 결과 저장
$_SESSION['requirements_passed'] = $all_passed;
?>

<h2 class="step-title">Step 1: 시스템 요구사항 확인</h2>

<?php if (!$all_passed): ?>
<div class="alert alert-danger">
    일부 필수 요구사항이 충족되지 않았습니다. 아래 항목을 확인하세요.
</div>
<?php else: ?>
<div class="alert alert-success">
    모든 필수 요구사항이 충족되었습니다. 다음 단계로 진행하세요.
</div>
<?php endif; ?>

<?php foreach ($requirements as $req): ?>
<div class="check-item">
    <div class="status <?php echo $req['status']; ?>">
        <?php
        switch ($req['status']) {
            case 'pass': echo '✓'; break;
            case 'fail': echo '✗'; break;
            case 'warn': echo '!'; break;
        }
        ?>
    </div>
    <div class="info">
        <strong><?php echo $req['name']; ?></strong>
        <small>
            필요: <?php echo $req['required']; ?> |
            현재: <?php echo $req['current']; ?>
        </small>
    </div>
</div>
<?php endforeach; ?>

<div class="btn-group">
    <span></span>
    <?php if ($all_passed): ?>
    <a href="?step=2" class="btn btn-primary">다음 단계 →</a>
    <?php else: ?>
    <button class="btn btn-secondary" disabled>요구사항 미충족</button>
    <?php endif; ?>
</div>
