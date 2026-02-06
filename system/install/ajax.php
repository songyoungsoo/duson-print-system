<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/InstallerEngine.php';

$engine = new InstallerEngine();

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
    $action = $input['action'] ?? '';
} else {
    $input = $_POST;
    $action = $_POST['action'] ?? '';
}

switch ($action) {
    case 'test_db':
        $host = trim($input['db_host'] ?? 'localhost');
        $port = intval($input['db_port'] ?? 3306);
        $user = trim($input['db_user'] ?? '');
        $pass = $input['db_pass'] ?? '';
        $name = trim($input['db_name'] ?? '');

        if (empty($user)) {
            echo json_encode(['success' => false, 'message' => 'DB 사용자명을 입력해주세요.']);
            exit;
        }

        $connOk = $engine->testDbConnection($host, $port, $user, $pass, '');
        if (!$connOk) {
            echo json_encode(['success' => false, 'message' => $engine->getLastError()]);
            exit;
        }

        $mysqlVer = $engine->getMysqlVersion($host, $port, $user, $pass);
        $dbExists = false;

        if (!empty($name)) {
            $dbExists = $engine->testDbConnection($host, $port, $user, $pass, $name);
        }

        $msg = "MySQL {$mysqlVer} 연결 성공";
        if (!empty($name)) {
            $msg .= $dbExists
                ? " | 데이터베이스 '{$name}' 확인됨"
                : " | 데이터베이스 '{$name}' 미존재 (자동 생성 필요)";
        }

        echo json_encode(['success' => true, 'message' => $msg, 'db_exists' => $dbExists]);
        break;

    case 'install':
        $data = $_SESSION['install_data'] ?? [];

        if (empty($data['db_host']) || empty($data['db_name']) || empty($data['db_user'])) {
            echo json_encode(['success' => false, 'message' => '설치 데이터가 없습니다. 처음부터 다시 시작해주세요.', 'results' => []]);
            exit;
        }
        if (empty($data['admin_id']) || empty($data['admin_pass'])) {
            echo json_encode(['success' => false, 'message' => '관리자 정보가 없습니다.', 'results' => []]);
            exit;
        }

        $results = $engine->runInstallation($data);

        $allOk = true;
        foreach ($results as $r) {
            if (!$r['success']) {
                $allOk = false;
                break;
            }
        }

        if ($allOk) {
            unset($_SESSION['install_data']);
            unset($_SESSION['install_max_step']);
        }

        echo json_encode(['success' => $allOk, 'results' => $results]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => '알 수 없는 요청입니다.']);
        break;
}
