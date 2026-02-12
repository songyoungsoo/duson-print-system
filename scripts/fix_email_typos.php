<?php
/**
 * CLI: 이메일 오타 검사 및 수정 스크립트
 * 
 * Usage:
 *   php scripts/fix_email_typos.php          # 검사만 (dry-run)
 *   php scripts/fix_email_typos.php --fix    # 검사 + 수정 실행
 */
if (php_sapi_name() !== 'cli') {
    die('CLI only');
}

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.env.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/EmailTypoFixer.php';

$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_ADDR'] = '127.0.0.1';
$db_config = EnvironmentDetector::getDatabaseConfig();
$db = mysqli_connect($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
if (!$db) {
    fwrite(STDERR, "DB 연결 실패: " . mysqli_connect_error() . "\n");
    exit(1);
}
mysqli_set_charset($db, 'utf8mb4');

$doFix = in_array('--fix', $argv);

echo "\n=== 이메일 오타 검사 ===\n\n";

$result = EmailTypoFixer::scanAll($db);
$typos = $result['typos'];

echo "검사 대상: {$result['total_checked']}명\n";
echo "감지된 오타: " . count($typos) . "건\n\n";

if (empty($typos)) {
    echo "오타가 없습니다.\n";
    exit(0);
}

echo str_pad('ID', 6) . str_pad('아이디', 16) . str_pad('이름', 12) . str_pad('현재 이메일', 32) . str_pad('수정 제안', 32) . "감지방법\n";
echo str_repeat('-', 110) . "\n";

foreach ($typos as $t) {
    echo str_pad($t['user_id'], 6)
       . str_pad(mb_strimwidth($t['username'], 0, 14, '..'), 16)
       . str_pad(mb_strimwidth($t['name'], 0, 10, '..'), 12)
       . str_pad($t['original'], 32)
       . str_pad($t['suggested'], 32)
       . $t['method'] . "\n";
}

echo "\n";

if (!$doFix) {
    echo "수정하려면: php scripts/fix_email_typos.php --fix\n\n";
    exit(0);
}

echo "수정을 진행합니다...\n\n";

$fixResult = EmailTypoFixer::fixAll($db, $typos);

foreach ($fixResult['details'] as $d) {
    $icon = $d['status'] === 'fixed' ? '✅' : '❌';
    echo "  {$icon} ID {$d['user_id']}: {$d['from']} → {$d['to']}\n";
}

echo "\n완료: 수정 {$fixResult['fixed']}건, 실패 {$fixResult['failed']}건\n\n";

mysqli_close($db);
