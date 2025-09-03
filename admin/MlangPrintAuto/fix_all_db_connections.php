<?php
/**
 * admin/MlangPrintAuto 디렉토리의 모든 데이터베이스 연결 문제 수정
 */

$directory = __DIR__;
$files_to_fix = [
    '250323/admin.php',
    '250327admin/admin250318.php',
    'admin.php',
    'admin250425.php',
    'admin250717.php',
    'LittlePrint_List.php',
    'MemberOrderOfficeList.php',
    'envelope_admin.php',
    'envelope_List.php',
    'NcrFlambeau_List.php',
    '250323/CateList.php',
    'envelope_Script.php',
    'NameCard_Script.php',
    'cadarok_List.php',
    'sticker_NoFild.php',
    'cadarok_NoFild.php',
    'NcrFlambeau_NoFild.php',
    'LittlePrint_ScriptSearch.php',
    'NameCard_NoFild.php',
    'LittlePrint_NoFild.php',
    'inserted_NoFild.php',
    'cadarokTwo_NoFild.php',
    'envelope_NoFild.php',
    '250323/CateView.php'
];

echo "<h2>🔧 데이터베이스 연결 수정</h2>";
echo "<pre>";

$success_count = 0;
$fail_count = 0;

foreach ($files_to_fix as $file) {
    $file_path = $directory . '/' . $file;
    
    if (!file_exists($file_path)) {
        echo "❌ 파일 없음: $file\n";
        $fail_count++;
        continue;
    }
    
    // 파일 읽기
    $content = file_get_contents($file_path);
    
    // 문제가 되는 패턴 찾기
    $old_pattern = '/\$mysqli\s*=\s*new\s+mysqli\(\$host,\s*\$user,\s*\$password,\s*\$dataname\);/';
    $new_code = '$mysqli = $db;';
    
    // 패턴이 있는지 확인
    if (preg_match($old_pattern, $content)) {
        // 패턴 교체
        $new_content = preg_replace($old_pattern, $new_code, $content);
        
        // 파일 저장
        if (file_put_contents($file_path, $new_content)) {
            echo "✅ 수정 완료: $file\n";
            $success_count++;
        } else {
            echo "❌ 저장 실패: $file\n";
            $fail_count++;
        }
    } else {
        echo "⏭️  이미 수정됨 또는 패턴 없음: $file\n";
    }
}

echo "\n=== 결과 ===\n";
echo "✅ 성공: {$success_count}개\n";
echo "❌ 실패: {$fail_count}개\n";

echo "\n🎉 데이터베이스 연결 문제 수정 완료!\n";

echo "</pre>";

echo '<br><br>';
echo '<a href="/admin/mlangprintauto/OrderList.php" style="background:#007cba;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;font-size:16px;">📋 주문 목록 확인</a> ';
echo '<a href="/admin/mlangprintauto/admin.php" style="background:#28a745;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;margin-left:10px;font-size:16px;">🔧 관리자 페이지</a>';
?>