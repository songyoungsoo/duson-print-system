<?php
/**
 * 파일 업로드 디버깅 테스트 페이지
 * 실제로 무엇이 전송되고 있는지 확인
 */

header('Content-Type: application/json; charset=utf-8');

// 모든 POST 데이터 로깅
error_log("=== UPLOAD DEBUG TEST ===");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

// JSON 응답
$response = [
    'success' => true,
    'debug' => [
        'post_keys' => array_keys($_POST),
        'files_keys' => array_keys($_FILES),
        'files_structure' => $_FILES,
        'upload_method' => $_POST['upload_method'] ?? 'not set',
        'product_type' => $_POST['product_type'] ?? 'not set',
        'has_uploaded_files' => !empty($_FILES['uploaded_files']),
    ]
];

// uploaded_files가 있다면 상세 분석
if (!empty($_FILES['uploaded_files'])) {
    $files = $_FILES['uploaded_files'];
    $response['debug']['uploaded_files_analysis'] = [
        'is_array' => is_array($files['name']),
        'name_type' => gettype($files['name']),
        'name_value' => $files['name'],
        'tmp_name_type' => gettype($files['tmp_name']),
        'tmp_name_value' => $files['tmp_name'],
        'error_type' => gettype($files['error']),
        'error_value' => $files['error'],
        'size_type' => gettype($files['size']),
        'size_value' => $files['size']
    ];

    // 파일 개수 계산
    if (is_array($files['name'])) {
        $response['debug']['file_count'] = count($files['name']);
        $response['debug']['files_list'] = [];
        for ($i = 0; $i < count($files['name']); $i++) {
            $response['debug']['files_list'][] = [
                'index' => $i,
                'name' => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
                'tmp_exists' => file_exists($files['tmp_name'][$i])
            ];
        }
    } else {
        $response['debug']['file_count'] = 1;
        $response['debug']['single_file'] = [
            'name' => $files['name'],
            'tmp_name' => $files['tmp_name'],
            'error' => $files['error'],
            'size' => $files['size'],
            'tmp_exists' => file_exists($files['tmp_name'])
        ];
    }
}

error_log("Response: " . json_encode($response, JSON_UNESCAPED_UNICODE));
error_log("=== DEBUG TEST END ===");

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
