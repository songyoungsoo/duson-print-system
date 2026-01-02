# 파일 업로드 시스템

## 인쇄용 파일 요구사항

### 허용 파일 형식
```php
const ALLOWED_EXTENSIONS = [
    'ai',      // Adobe Illustrator
    'psd',     // Adobe Photoshop
    'pdf',     // PDF (인쇄용)
    'eps',     // EPS
    'cdr',     // CorelDRAW
    'jpg', 'jpeg',
    'png',
    'tif', 'tiff',
    'zip',     // 압축파일
];

const MAX_FILE_SIZE = 100 * 1024 * 1024;  // 100MB
```

### 권장 사양
```
- 해상도: 300dpi 이상
- 색상 모드: CMYK
- 재단선(도련): 3mm
- 폰트: 아웃라인 처리
```

## php.ini 설정

```ini
; 대용량 파일 업로드를 위한 설정
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

## 단일 파일 업로드

### HTML
```html
<form id="uploadForm" enctype="multipart/form-data">
    <div class="upload-area" id="dropZone">
        <input type="file" id="fileInput" name="print_file" 
               accept=".ai,.psd,.pdf,.eps,.cdr,.jpg,.jpeg,.png,.tif,.tiff,.zip">
        <p>파일을 드래그하거나 클릭하여 선택</p>
        <p class="hint">AI, PSD, PDF, EPS, CDR, JPG, PNG, TIF, ZIP (최대 100MB)</p>
    </div>
    
    <div id="uploadProgress" style="display:none;">
        <div class="progress-bar">
            <div class="progress" id="progressBar"></div>
        </div>
        <span id="progressText">0%</span>
    </div>
    
    <div id="uploadResult" style="display:none;">
        <span class="filename"></span>
        <button type="button" onclick="removeFile()">삭제</button>
    </div>
</form>
```

### JavaScript (AJAX + 프로그레스)
```javascript
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');

// 드래그 앤 드롭
dropZone.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropZone.classList.add('dragover');
});

dropZone.addEventListener('dragleave', () => {
    dropZone.classList.remove('dragover');
});

dropZone.addEventListener('drop', (e) => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const files = e.dataTransfer.files;
    if (files.length) uploadFile(files[0]);
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length) uploadFile(e.target.files[0]);
});

// 파일 업로드
async function uploadFile(file) {
    // 확장자 검사
    const ext = file.name.split('.').pop().toLowerCase();
    const allowed = ['ai', 'psd', 'pdf', 'eps', 'cdr', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'zip'];
    if (!allowed.includes(ext)) {
        alert('허용되지 않는 파일 형식입니다.');
        return;
    }
    
    // 용량 검사
    if (file.size > 100 * 1024 * 1024) {
        alert('파일 크기는 100MB 이하여야 합니다.');
        return;
    }
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('product_type', currentProductType);
    
    // 프로그레스 표시
    document.getElementById('uploadProgress').style.display = 'block';
    
    const xhr = new XMLHttpRequest();
    
    xhr.upload.addEventListener('progress', (e) => {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressText').textContent = percent + '%';
        }
    });
    
    xhr.addEventListener('load', () => {
        document.getElementById('uploadProgress').style.display = 'none';
        
        if (xhr.status === 200) {
            const result = JSON.parse(xhr.responseText);
            if (result.success) {
                document.getElementById('uploadResult').style.display = 'block';
                document.querySelector('#uploadResult .filename').textContent = file.name;
                uploadedFilePath = result.file_path;
            } else {
                alert(result.error);
            }
        } else {
            alert('업로드 실패');
        }
    });
    
    xhr.open('POST', '/api/upload.php');
    xhr.send(formData);
}
```

### PHP 처리 (api/upload.php)
```php
<?php
header('Content-Type: application/json');

// 파일 검증
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE => '파일이 너무 큽니다 (서버 제한)',
        UPLOAD_ERR_FORM_SIZE => '파일이 너무 큽니다',
        UPLOAD_ERR_PARTIAL => '파일이 일부만 업로드되었습니다',
        UPLOAD_ERR_NO_FILE => '파일이 선택되지 않았습니다',
    ];
    $error_code = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    echo json_encode(['success' => false, 'error' => $errors[$error_code] ?? '업로드 실패']);
    exit;
}

$file = $_FILES['file'];
$product_type = $_POST['product_type'] ?? 'general';

// 확장자 검사
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['ai', 'psd', 'pdf', 'eps', 'cdr', 'jpg', 'jpeg', 'png', 'tif', 'tiff', 'zip'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => '허용되지 않는 파일 형식입니다.']);
    exit;
}

// 용량 검사
if ($file['size'] > 100 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => '파일 크기는 100MB 이하여야 합니다.']);
    exit;
}

// 저장 경로 생성
$upload_dir = '/uploads/print_files/' . date('Y/m/d');
$full_dir = $_SERVER['DOCUMENT_ROOT'] . $upload_dir;

if (!is_dir($full_dir)) {
    mkdir($full_dir, 0755, true);
}

// 파일명 생성 (유니크)
$new_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
$file_path = $upload_dir . '/' . $new_filename;

// 파일 이동
if (move_uploaded_file($file['tmp_name'], $full_dir . '/' . $new_filename)) {
    // 업로드 로그 저장
    logUpload($file['name'], $file_path, $file['size'], $product_type);
    
    echo json_encode([
        'success' => true,
        'file_path' => $file_path,
        'file_name' => $file['name'],
        'file_size' => $file['size']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => '파일 저장 실패']);
}
```

## 청크 업로드 (대용량)

### JavaScript
```javascript
async function uploadLargeFile(file) {
    const CHUNK_SIZE = 2 * 1024 * 1024;  // 2MB 청크
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
    const uploadId = Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    
    for (let i = 0; i < totalChunks; i++) {
        const start = i * CHUNK_SIZE;
        const end = Math.min(start + CHUNK_SIZE, file.size);
        const chunk = file.slice(start, end);
        
        const formData = new FormData();
        formData.append('chunk', chunk);
        formData.append('upload_id', uploadId);
        formData.append('chunk_index', i);
        formData.append('total_chunks', totalChunks);
        formData.append('file_name', file.name);
        
        await fetch('/api/upload_chunk.php', {
            method: 'POST',
            body: formData
        });
        
        // 프로그레스 업데이트
        const progress = Math.round(((i + 1) / totalChunks) * 100);
        updateProgress(progress);
    }
    
    // 청크 병합 요청
    const mergeResult = await fetch('/api/merge_chunks.php', {
        method: 'POST',
        body: JSON.stringify({ upload_id: uploadId, file_name: file.name })
    });
    
    return await mergeResult.json();
}
```

### PHP 청크 처리
```php
// api/upload_chunk.php
$upload_id = $_POST['upload_id'];
$chunk_index = (int)$_POST['chunk_index'];
$total_chunks = (int)$_POST['total_chunks'];

$chunk_dir = sys_get_temp_dir() . '/chunks/' . $upload_id;
if (!is_dir($chunk_dir)) mkdir($chunk_dir, 0755, true);

$chunk_path = $chunk_dir . '/chunk_' . str_pad($chunk_index, 5, '0', STR_PAD_LEFT);
move_uploaded_file($_FILES['chunk']['tmp_name'], $chunk_path);

echo json_encode(['success' => true, 'chunk' => $chunk_index]);
```

```php
// api/merge_chunks.php
$data = json_decode(file_get_contents('php://input'), true);
$upload_id = $data['upload_id'];
$file_name = $data['file_name'];

$chunk_dir = sys_get_temp_dir() . '/chunks/' . $upload_id;
$chunks = glob($chunk_dir . '/chunk_*');
sort($chunks);

// 최종 파일 경로
$final_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/print_files/' . date('Y/m/d');
if (!is_dir($final_dir)) mkdir($final_dir, 0755, true);

$final_path = $final_dir . '/' . uniqid() . '_' . $file_name;

// 청크 병합
$fp = fopen($final_path, 'wb');
foreach ($chunks as $chunk) {
    fwrite($fp, file_get_contents($chunk));
    unlink($chunk);  // 청크 삭제
}
fclose($fp);

// 청크 디렉토리 삭제
rmdir($chunk_dir);

echo json_encode([
    'success' => true,
    'file_path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $final_path)
]);
```

## 파일 보안

### .htaccess (uploads 폴더)
```apache
# PHP 실행 방지
<FilesMatch "\.php$">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# 직접 접근 제한 (옵션)
# Order Deny,Allow
# Deny from all
```

### 다운로드 처리 (직접 링크 방지)
```php
// download.php
$file_id = $_GET['id'];
$token = $_GET['token'];

// 토큰 검증
if (!validateDownloadToken($file_id, $token)) {
    die('잘못된 접근입니다.');
}

$file = getFileInfo($file_id);
$file_path = $_SERVER['DOCUMENT_ROOT'] . $file['path'];

if (!file_exists($file_path)) {
    die('파일을 찾을 수 없습니다.');
}

// 다운로드 헤더
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
```

## 이미지 썸네일 생성

```php
function createThumbnail($source_path, $thumb_path, $max_width = 200) {
    $info = getimagesize($source_path);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_path);
            break;
        default:
            return false;
    }
    
    $width = imagesx($source);
    $height = imagesy($source);
    
    $ratio = $max_width / $width;
    $new_width = $max_width;
    $new_height = (int)($height * $ratio);
    
    $thumb = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
    imagejpeg($thumb, $thumb_path, 80);
    
    imagedestroy($source);
    imagedestroy($thumb);
    
    return true;
}
```
