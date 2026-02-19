<?php
require_once __DIR__ . '/base.php';

$allowed_keys = ['nav_default_mode', 'quote_widget_enabled', 'quote_widget_right', 'quote_widget_top', 'en_version_enabled'];
$allowed_values = [
    'nav_default_mode' => ['simple', 'detailed'],
    'quote_widget_enabled' => ['0', '1'],
    'en_version_enabled' => ['0', '1'],
];

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $key = $_GET['key'] ?? '';
    if ($key && !in_array($key, $allowed_keys)) {
        jsonResponse(false, '허용되지 않는 설정 키입니다.');
    }

    if ($key) {
        $stmt = mysqli_prepare($db, "SELECT setting_value FROM site_settings WHERE setting_key = ?");
        mysqli_stmt_bind_param($stmt, "s", $key);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        jsonResponse(true, 'OK', ['key' => $key, 'value' => $row ? $row['setting_value'] : null]);
    }

    $q = mysqli_query($db, "SELECT setting_key, setting_value FROM site_settings");
    $all = [];
    while ($r = mysqli_fetch_assoc($q)) {
        $all[$r['setting_key']] = $r['setting_value'];
    }
    jsonResponse(true, 'OK', $all);
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action !== 'update') {
        jsonResponse(false, '알 수 없는 action입니다.');
    }

    $key = $input['key'] ?? '';
    $value = $input['value'] ?? '';

    if (!in_array($key, $allowed_keys)) {
        jsonResponse(false, '허용되지 않는 설정 키입니다.');
    }

    if (isset($allowed_values[$key]) && !in_array($value, $allowed_values[$key])) {
        jsonResponse(false, '허용되지 않는 값입니다: ' . $value);
    }

    // 숫자 범위 검증 (quote_widget_right: 0~500, quote_widget_top: 0~100)
    if ($key === 'quote_widget_right') {
        $num = intval($value);
        if ($num < 0 || $num > 500) {
            jsonResponse(false, 'right 값은 0~500 범위여야 합니다.');
        }
        $value = (string) $num;
    }
    if ($key === 'quote_widget_top') {
        $num = intval($value);
        if ($num < 0 || $num > 100) {
            jsonResponse(false, 'top 값은 0~100 범위여야 합니다.');
        }
        $value = (string) $num;
    }

    $stmt = mysqli_prepare($db, "INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    mysqli_stmt_bind_param($stmt, "sss", $key, $value, $value);
    $ok = mysqli_stmt_execute($stmt);

    if ($ok) {
        jsonResponse(true, '설정이 저장되었습니다.', ['key' => $key, 'value' => $value]);
    } else {
        jsonResponse(false, 'DB 저장 실패: ' . mysqli_error($db));
    }
}

jsonResponse(false, '지원하지 않는 요청입니다.');
