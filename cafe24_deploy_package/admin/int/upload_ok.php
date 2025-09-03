<?php
declare(strict_types=1);

// 업로드 함수 불러오기
require_once __DIR__ . '/upload.inc.php';

// 업로드 금지 확장자 목록
$forbid_ext = ['php', 'asp', 'jsp', 'inc', 'c', 'cpp', 'sh'];

// 업로드 대상이 있는지 확인
if (isset($_FILES['upfile'])) {
    $tmpNames = $_FILES['upfile']['tmp_name'];
    $names    = $_FILES['upfile']['name'];
    $sizes    = $_FILES['upfile']['size'];
    $types    = $_FILES['upfile']['type'];
    
    // func_multi_upload 정의 예시:
//   func_multi_upload(array $tmpNames, array $names, array $sizes, array $types, string $destDir, array $forbidExt): int|false
    $uploadDir = __DIR__ . '/';
    $result = func_multi_upload($tmpNames, $names, $sizes, $types, $uploadDir, $forbid_ext);

    if ($result !== false && $result > 0) {
        echo "<script>
                alert('{$result} 개의 파일이 업로드 되었습니다.');
              </script>";
    } else {
        echo "<script>
                alert('파일이 업로드되지 않았습니다.');
                history.back();
              </script>";
    }
} else {
    echo "<script>
            alert('업로드할 파일이 선택되지 않았습니다.');
            history.back();
          </script>";
}
?>
