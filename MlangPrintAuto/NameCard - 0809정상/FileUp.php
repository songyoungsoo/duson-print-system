<?php
session_start();

// 파라미터 받기
$Turi = $_GET['Turi'] ?? '';
$Ty = $_GET['Ty'] ?? '';
$Tmd = $_GET['Tmd'] ?? '';
$Tip = $_GET['Tip'] ?? '';
$Ttime = $_GET['Ttime'] ?? '';

// 업로드 디렉토리 설정
$upload_dir = "../../uploads/" . $Ty . "/" . $Tmd . "/" . $Tip . "/" . $Ttime . "/";

// 디렉토리가 없으면 생성
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// 파일 업로드 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['upload_file'])) {
    $file = $_FILES['upload_file'];
    
    if ($file['error'] == UPLOAD_ERR_OK) {
        $filename = basename($file['name']);
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            echo "<script>
                if (window.opener && window.opener.document.forms['namecardForm']) {
                    var parentList = window.opener.document.forms['namecardForm'].parentList;
                    var option = new Option('$filename', '$filename');
                    parentList.options[parentList.options.length] = option;
                }
                window.close();
            </script>";
            exit;
        } else {
            $error_msg = "파일 업로드에 실패했습니다.";
        }
    } else {
        $error_msg = "파일 업로드 오류: " . $file['error'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>파일 업로드</title>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Noto Sans KR', sans-serif; padding: 20px; }
        .upload-form { border: 1px solid #ccc; padding: 20px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ccc; }
        .btn { padding: 10px 20px; background-color: #336699; color: white; border: none; cursor: pointer; }
        .btn:hover { background-color: #2a5580; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="upload-form">
        <h3>파일 업로드</h3>
        
        <?php if (isset($error_msg)): ?>
            <div class="error"><?= htmlspecialchars($error_msg) ?></div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="upload_file">파일 선택:</label>
                <input type="file" name="upload_file" id="upload_file" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn">업로드</button>
                <button type="button" class="btn" onclick="window.close();" style="background-color: #666; margin-left: 10px;">취소</button>
            </div>
        </form>
        
        <div style="margin-top: 20px; font-size: 12px; color: #666;">
            <p>• 지원 파일 형식: 이미지, PDF, 문서 파일</p>
            <p>• 최대 파일 크기: 10MB</p>
        </div>
    </div>
</body>
</html>