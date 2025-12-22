<?php
/**
 * 버전 관리 컨트롤러
 * admin.php 버전 교체 및 롤백 관리
 */

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'replace':
            // 현재 admin.php를 rollback용으로 백업
            if (file_exists('admin.php')) {
                copy('admin.php', 'admin_rollback_' . date('YmdHis') . '.php');
            }

            // admin_74_test.php를 admin.php로 교체
            if (file_exists('admin_74_test.php')) {
                copy('admin_74_test.php', 'admin.php');
                $response = [
                    'success' => true,
                    'message' => 'admin_7.4 버전으로 성공적으로 교체되었습니다.'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'admin_74_test.php 파일을 찾을 수 없습니다.'
                ];
            }
            break;

        case 'rollback':
            // 가장 최근 rollback 파일 찾기
            $rollback_files = glob('admin_rollback_*.php');
            if (!empty($rollback_files)) {
                // 파일명 기준으로 정렬 (최신순)
                rsort($rollback_files);
                $latest_rollback = $rollback_files[0];

                // 현재 admin.php 백업 후 롤백
                if (file_exists('admin.php')) {
                    copy('admin.php', 'admin_replaced_backup.php');
                }

                copy($latest_rollback, 'admin.php');
                $response = [
                    'success' => true,
                    'message' => "성공적으로 롤백되었습니다. ({$latest_rollback})"
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => '롤백할 백업 파일을 찾을 수 없습니다.'
                ];
            }
            break;

        case 'status':
            // 현재 상태 확인
            $current_size = file_exists('admin.php') ? filesize('admin.php') : 0;
            $test_size = file_exists('admin_74_test.php') ? filesize('admin_74_test.php') : 0;
            $rollback_files = count(glob('admin_rollback_*.php'));

            $response = [
                'success' => true,
                'data' => [
                    'current_size' => $current_size,
                    'current_version' => ($current_size > 40000) ? 'admin_7.4' : 'current',
                    'test_size' => $test_size,
                    'rollback_count' => $rollback_files
                ]
            ];
            break;

        default:
            $response = [
                'success' => false,
                'message' => '알 수 없는 액션입니다.'
            ];
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => '오류 발생: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>