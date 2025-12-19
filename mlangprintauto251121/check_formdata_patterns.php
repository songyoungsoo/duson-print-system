<?php
/**
 * 전체 9개 품목의 FormData 패턴 검사
 * uploaded_files[] 올바른 형식 사용 여부 확인
 */

header('Content-Type: application/json; charset=utf-8');

$products = [
    'inserted' => '전단지',
    'sticker_new' => '스티커',
    'envelope' => '봉투',
    'littleprint' => '소량인쇄물',
    'cadarok' => '카다록',
    'merchandisebond' => '상품권',
    'namecard' => '명함',
    'msticker' => '자석스티커',
    'ncrflambeau' => '양식지'
];

$results = [];

foreach ($products as $product => $name) {
    $file_path = __DIR__ . "/{$product}/index.php";

    if (!file_exists($file_path)) {
        $results[$product] = [
            'name' => $name,
            'status' => 'FILE_NOT_FOUND',
            'path' => $file_path
        ];
        continue;
    }

    $content = file_get_contents($file_path);

    // 잘못된 패턴 검사: uploaded_files[0], uploaded_files[${index}], uploaded_files[" + index + "]
    $wrong_patterns = [
        '/formData\.append\([\'"]uploaded_files\[\d+\]/',  // uploaded_files[0]
        '/formData\.append\([\'"]uploaded_files\[\$\{index\}\]/',  // uploaded_files[${index}]
        '/formData\.append\([\'"]uploaded_files\["\s*\+\s*index\s*\+\s*"\]/',  // uploaded_files[" + index + "]
    ];

    $has_wrong_pattern = false;
    $wrong_matches = [];

    foreach ($wrong_patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            $has_wrong_pattern = true;
            $wrong_matches[] = $matches[0];
        }
    }

    // 올바른 패턴 검사: uploaded_files[]
    $has_correct_pattern = preg_match('/formData\.append\([\'"]uploaded_files\[\]/', $content);

    $results[$product] = [
        'name' => $name,
        'status' => !$has_wrong_pattern && $has_correct_pattern ? 'OK' : 'ERROR',
        'has_correct_pattern' => $has_correct_pattern,
        'has_wrong_pattern' => $has_wrong_pattern,
        'wrong_matches' => $wrong_matches
    ];
}

echo json_encode([
    'timestamp' => date('Y-m-d H:i:s'),
    'results' => $results
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
