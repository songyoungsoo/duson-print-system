<?php
$ftp_server = 'ftp.dsp1830.shop';
$ftp_user = 'dsp1830';
$ftp_pass = 'ds701018';

$conn = ftp_connect($ftp_server);
if (!$conn) die('FTP 연결 실패');

$login = ftp_login($conn, $ftp_user, $ftp_pass);
if (!$login) die('FTP 로그인 실패');

ftp_pasv($conn, true);

echo "📤 FTP 업로드 시작\n\n";

// 업로드할 파일 목록
$files = [
    [
        'local' => '/var/www/html/execute_member_sync.php',
        'remote' => 'execute_member_sync.php'
    ],
    [
        'local' => '/var/www/html/sql251109/member_new_fixed.sql',
        'remote' => 'sql251109/member_new_fixed.sql'
    ]
];

// sql251109 디렉토리 생성 (없으면)
@ftp_mkdir($conn, 'sql251109');

foreach ($files as $file) {
    echo "업로드 중: {$file['remote']} ... ";

    if (ftp_put($conn, $file['remote'], $file['local'], FTP_BINARY)) {
        $size = filesize($file['local']);
        echo "✅ 완료 (" . number_format($size) . " bytes)\n";
    } else {
        echo "❌ 실패\n";
    }
}

ftp_close($conn);

echo "\n✅ FTP 업로드 완료!\n\n";
echo "다음 단계:\n";
echo "1. 브라우저에서 접속: http://dsp1830.shop/execute_member_sync.php\n";
echo "2. 확인 후 실행: http://dsp1830.shop/execute_member_sync.php?confirm=yes\n";
?>
