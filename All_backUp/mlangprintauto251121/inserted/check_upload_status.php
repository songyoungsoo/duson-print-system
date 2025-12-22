<?php
/**
 * 업로드 디렉토리 상태 확인 페이지
 * 웹 서버에서 실제 파일 시스템 상태를 확인
 */

header('Content-Type: application/json; charset=utf-8');

// 최근 업로드 디렉토리 경로 (사용자가 보고한 경로)
$test_paths = [
    '/www/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1119/222.108.84.120/1763537333',
    '/www/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1119/222.108.84.120/1763543725',
    '/www/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1119/222.108.84.120/1763545022',
    '/www/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1119/222.108.84.120/1763545606',
];

$result = [
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'NOT SET',
    'current_user' => get_current_user(),
    'php_version' => PHP_VERSION,
    'directories' => []
];

foreach ($test_paths as $path) {
    $dir_info = [
        'path' => $path,
        'exists' => file_exists($path),
        'is_dir' => is_dir($path),
        'is_writable' => is_writable($path),
        'files' => []
    ];

    if ($dir_info['is_dir']) {
        $files = scandir($path);
        $dir_info['files'] = array_filter($files, function($f) { return $f !== '.' && $f !== '..'; });
        $dir_info['file_count'] = count($dir_info['files']);

        // 파일 상세 정보
        $dir_info['file_details'] = [];
        foreach ($dir_info['files'] as $file) {
            $filepath = $path . '/' . $file;
            $dir_info['file_details'][] = [
                'name' => $file,
                'size' => filesize($filepath),
                'is_file' => is_file($filepath),
                'is_readable' => is_readable($filepath)
            ];
        }
    } else if (!$dir_info['exists']) {
        // 상위 디렉토리 확인
        $parent = dirname($path);
        $dir_info['parent_exists'] = file_exists($parent);
        $dir_info['parent_writable'] = is_writable($parent);
    }

    $result['directories'][] = $dir_info;
}

// ImgFolder 루트 확인
$imgfolder_root = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/_MlangPrintAuto_inserted_index.php/2025/1119/222.108.84.120';
$result['imgfolder_root'] = [
    'path' => $imgfolder_root,
    'exists' => file_exists($imgfolder_root),
    'is_dir' => is_dir($imgfolder_root),
    'subdirs' => []
];

if (is_dir($imgfolder_root)) {
    $subdirs = scandir($imgfolder_root);
    $result['imgfolder_root']['subdirs'] = array_filter($subdirs, function($f) { return $f !== '.' && $f !== '..'; });
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
