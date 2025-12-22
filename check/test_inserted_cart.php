<?php
// 전단지 장바구니 파일 업로드 E2E 테스트

// CLI 실행 시 $_SERVER['DOCUMENT_ROOT'] 설정
if (php_sapi_name() === 'cli') {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

session_start();
$session_id = session_id();

echo "<!DOCTYPE html>\n<html>\n<head>\n<meta charset='utf-8'>\n<title>전단지 파일 업로드 테스트</title>\n</head>\n<body>\n<pre>\n";

echo "=== 전단지 파일 업로드 E2E 테스트 ===\n";
echo "Session ID: $session_id\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Remote IP: " . $_SERVER['REMOTE_ADDR'] . "\n\n";

// 테스트 파일 생성
$temp_file = tempnam(sys_get_temp_dir(), 'upload_');
file_put_contents($temp_file, 'Test file content - ' . date('Y-m-d H:i:s'));
echo "✅ Test file created: $temp_file\n";

// $_FILES 시뮬레이션
$_FILES['uploaded_files'] = [
    'name' => ['test_document.txt'],
    'type' => ['text/plain'],
    'tmp_name' => [$temp_file],
    'error' => [UPLOAD_ERR_OK],
    'size' => [filesize($temp_file)]
];

// $_POST 시뮬레이션
$_POST = [
    'MY_type' => '802',
    'PN_type' => 'A4',
    'MY_Fsd' => '100',
    'MY_amount' => '1000',
    'POtype' => '4',
    'ordertype' => '양면인쇄',
    'calculated_price' => 100000,
    'calculated_vat_price' => 110000,
    'product_type' => 'inserted',
    'work_memo' => '테스트 주문입니다',
    'upload_method' => 'upload'
];

echo "✅ POST data prepared\n";
echo "✅ FILES data prepared\n\n";

// UploadPathHelper 테스트
require_once __DIR__ . '/includes/UploadPathHelper.php';

$paths = UploadPathHelper::generateUploadPath('inserted');
echo "Upload paths:\n";
echo "  Full: " . $paths['full_path'] . "\n";
echo "  DB: " . $paths['db_path'] . "\n\n";

// 디렉토리 생성 테스트
if (!file_exists($paths['full_path'])) {
    if (mkdir($paths['full_path'], 0775, true)) {
        echo "✅ Upload directory created\n";
    }
} else {
    echo "✅ Upload directory exists\n";
}

// 파일 복사 테스트
$target = $paths['full_path'] . '/' . $_FILES['uploaded_files']['name'][0];
if (copy($temp_file, $target)) {
    echo "✅ File copied to: $target\n";
    echo "✅ File size: " . filesize($target) . " bytes\n";
    echo "✅ File exists: " . (file_exists($target) ? 'YES' : 'NO') . "\n\n";

    // 데이터베이스 연결 및 저장 테스트
    include __DIR__ . '/db.php';

    if ($db) {
        echo "✅ Database connected\n";

        // shop_temp에 저장
        $stmt = mysqli_prepare($db, "INSERT INTO shop_temp (session_id, product_type, MY_type, PN_type, MY_Fsd, MY_amount, POtype, ordertype, st_price, st_price_vat, work_memo, upload_method, upload_folder) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $price = (float)$_POST['calculated_price'];
            $vat_price = (float)$_POST['calculated_vat_price'];

            mysqli_stmt_bind_param($stmt, "ssssssssddsss",
                $session_id,
                $_POST['product_type'],
                $_POST['MY_type'],
                $_POST['PN_type'],
                $_POST['MY_Fsd'],
                $_POST['MY_amount'],
                $_POST['POtype'],
                $_POST['ordertype'],
                $price,
                $vat_price,
                $_POST['work_memo'],
                $_POST['upload_method'],
                $paths['db_path']
            );

            if (mysqli_stmt_execute($stmt)) {
                $cart_id = mysqli_insert_id($db);
                echo "✅ Cart item inserted with ID: $cart_id\n";

                // 저장된 데이터 확인
                $check = mysqli_query($db, "SELECT * FROM shop_temp WHERE no = $cart_id");
                if ($row = mysqli_fetch_assoc($check)) {
                    echo "\n=== 저장된 데이터 ===\n";
                    echo "  ID: " . $row['no'] . "\n";
                    echo "  Session: " . $row['session_id'] . "\n";
                    echo "  Product: " . $row['product_type'] . "\n";
                    echo "  Price: " . $row['st_price'] . " 원\n";
                    echo "  VAT Price: " . $row['st_price_vat'] . " 원\n";
                    echo "  Upload folder: " . $row['upload_folder'] . "\n";
                }

                echo "\n✅✅✅ E2E 테스트 성공! ✅✅✅\n";

                // 정리
                unlink($target);
                rmdir($paths['full_path']);
                mysqli_query($db, "DELETE FROM shop_temp WHERE no = $cart_id");
                echo "\n✅ Test data cleaned up\n";

            } else {
                echo "❌ Failed to insert cart item: " . mysqli_stmt_error($stmt) . "\n";
            }

            mysqli_stmt_close($stmt);
        } else {
            echo "❌ Failed to prepare statement: " . mysqli_error($db) . "\n";
        }

        mysqli_close($db);
    } else {
        echo "❌ Database connection failed\n";
    }
} else {
    echo "❌ Failed to copy file\n";
}

unlink($temp_file);

echo "\n</pre>\n</body>\n</html>";
?>
