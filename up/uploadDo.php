<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>uploadDo</title>
</head>

<body>
	<?php
$uploadBase = 'upload/';

foreach ($_FILES['upload']['name'] as $f => $name) {   

    $name = $_FILES['upload']['name'][$f];
    $uploadName = explode('.', $name);

    // $fileSize = $_FILES['upload']['size'][$f];
    // $fileType = $_FILES['upload']['type'][$f];
    $uploadname = time().$f.'.'.$uploadName[1];
    $uploadFile = $uploadBase.$uploadname;

    if(move_uploaded_file($_FILES['upload']['tmp_name'][$f], $uploadFile)){
        echo 'success';
    }else{
        echo 'error';
    }
}  

print_r($_FILES['upload']) // 확인용
?>
</body>
</html>
?>