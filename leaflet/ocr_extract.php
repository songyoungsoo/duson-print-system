<?php
/**
 * ocr_extract.php — 매직 폼 (이미지/텍스트/문서) 분석 핸들러
 */

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST 요청만 허용됩니다.']);
    exit;
}

$outputBase = realpath(__DIR__ . '/../_leaflet_factory/output');
if (!$outputBase) {
    mkdir(__DIR__ . '/../_leaflet_factory/output', 0777, true);
    $outputBase = realpath(__DIR__ . '/../_leaflet_factory/output');
}

$uploadDir = $outputBase . '/tmp_ocr';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
    chmod($uploadDir, 0777);
}

$targetPath = '';
$textFilePath = '';
$isImage = false;
$isTextFile = false;

// 1. 텍스트 입력 처리
$magicText = trim($_POST['magic_text'] ?? '');
if ($magicText !== '') {
    $textFilePath = $uploadDir . '/' . time() . '_text.txt';
    file_put_contents($textFilePath, $magicText);
    chmod($textFilePath, 0666);
    $isTextFile = true;
}

// 2. 파일 업로드 처리
if (isset($_FILES['ocr_image']) && $_FILES['ocr_image']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['ocr_image']['tmp_name'];
    $fileName = time() . '_' . basename($_FILES['ocr_image']['name']);
    $fileName = preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName); 
    
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $targetPath = $uploadDir . '/' . $fileName;

    if (move_uploaded_file($tmpName, $targetPath)) {
        chmod($targetPath, 0666); 
        
        // 이미지인지 문서인지 판단 (간단하게)
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $isImage = true;
        } else {
            // 문서 파일 (txt, hwp, docx 등)은 Python에서 처리하도록 넘김
            $isTextFile = true; 
            if ($textFilePath === '') {
                $textFilePath = $targetPath; // 텍스트 입력이 없었다면 업로드된 문서를 텍스트 파일로 취급
            }
        }
    }
}

if (!$isImage && !$isTextFile) {
    echo json_encode(['error' => '분석할 파일이나 텍스트를 제공해주세요.']);
    exit;
}

$scriptPath = realpath(__DIR__ . '/../_leaflet_factory/scripts/extract_text.py');

$apiKey = '';
if (file_exists('/var/www/html/.env')) {
    $lines = file('/var/www/html/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
            $apiKey = substr(trim($line), 15);
            $apiKey = trim($apiKey, '"\'');
            break;
        }
    }
}

$pythonPath = '/home/ysung/.local/lib/python3.12/site-packages';

// 파이썬 명령어 구성 (이미지와 텍스트 파일을 모두 지원하도록)
$cmdArgs = '';
if ($isImage && $targetPath !== '') {
    $cmdArgs .= ' --image ' . escapeshellarg($targetPath);
}
if ($isTextFile && $textFilePath !== '') {
    $cmdArgs .= ' --textfile ' . escapeshellarg($textFilePath);
}

$pythonCmd = sprintf(
    'GEMINI_API_KEY=%s PYTHONPATH=%s:$PYTHONPATH python3 %s %s',
    escapeshellarg($apiKey),
    escapeshellarg($pythonPath),
    escapeshellarg($scriptPath),
    $cmdArgs
);

$output = shell_exec('bash -c ' . escapeshellarg($pythonCmd));

if (trim($output) === '') {
    echo json_encode(['error' => '파이썬 스크립트 실행 결과가 없습니다.']);
} else {
    echo trim($output);
}

// 정리
if ($targetPath !== '' && file_exists($targetPath)) @unlink($targetPath);
if ($textFilePath !== '' && $textFilePath !== $targetPath && file_exists($textFilePath)) @unlink($textFilePath);

