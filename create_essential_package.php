<?php
/**
 * Create essential deployment package for Cafe24 with only necessary files
 */

echo "카페24 핵심 배포 패키지 생성 시작...\n";

$source_dir = __DIR__;
$zip_file = $source_dir . '/cafe24_essential_files.zip';

// Essential files and directories to include
$essential_files = [
    // Core files
    'index.php',
    'db.php', 
    'header.php',
    'footer.php', 
    'left.php',
    
    // Core directories with all contents
    'css/',
    'js/',
    'includes/',
    'MlangPrintAuto/',
    'MlangOrder_PrintAuto/',
    'admin/MlangPrintAuto/',
    'shop/',
    'member/',
    'sub/',
    
    // Individual important files
    'setup_msticker_data.php'
];

// Files to exclude even from essential directories
$exclude_patterns = [
    '/\.git/',
    '/node_modules/',
    '/temp/',
    '/cache/',
    '/fix_.*\.php$/',
    '/create_.*\.php$/',
    '/debug_.*\.php$/',
    '/test_.*\.php$/',
    '/backup.*\.php$/',
    '/cafe24_.*\.zip$/',
];

function shouldExclude($file_path, $exclude_patterns) {
    foreach ($exclude_patterns as $pattern) {
        if (preg_match($pattern, $file_path)) {
            return true;
        }
    }
    return false;
}

function addDirectoryToZip($zip, $source_dir, $target_dir, $exclude_patterns = []) {
    $files_added = 0;
    
    if (is_dir($source_dir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $target_dir . '/' . substr($filePath, strlen($source_dir) + 1);
                
                if (!shouldExclude($relativePath, $exclude_patterns)) {
                    $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
                    $files_added++;
                }
            }
        }
    }
    
    return $files_added;
}

// Create ZIP file
if (file_exists($zip_file)) {
    unlink($zip_file);
}

$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
    $total_files = 0;
    
    foreach ($essential_files as $item) {
        $source_path = $source_dir . '/' . $item;
        
        if (substr($item, -1) === '/') {
            // Directory
            $dir_name = rtrim($item, '/');
            echo "디렉토리 추가: $dir_name\n";
            $files_added = addDirectoryToZip($zip, $source_path, $dir_name, $exclude_patterns);
            echo "  파일 $files_added 개 추가\n";
            $total_files += $files_added;
        } else {
            // Individual file
            if (file_exists($source_path)) {
                $zip->addFile($source_path, $item);
                $total_files++;
                echo "파일 추가: $item\n";
            } else {
                echo "파일 없음: $item\n";
            }
        }
    }
    
    // Add deployment readme
    $deployment_readme = "카페24 배포 패키지 - " . date('Y-m-d H:i:s') . "

=== 적용된 수정사항 ===

1. MySQL 테이블명 대소문자 수정 (101개 파일)
   - mlangprintauto_ → MlangPrintAuto_
   - Linux 환경에서 테이블명 대소문자 구분 문제 해결

2. db_constants.php 참조 제거 (28개 파일) 
   - Cafe24에서 누락된 파일 참조 제거

3. gallery_helper.php 조건부 처리 (10개 파일)
   - file_exists() 체크 추가로 선택적 포함

4. PHP 문법 오류 수정 (13개 오류)
   - include_product_gallery 함수 호출 수정
   - getCategoryOptions 따옴표 수정
   - NcrFlambeau auth.php 경로 수정

=== 배포 방법 ===

1. 기존 H:\\dsp114.com\\ 파일들을 backup_" . date('Ymd_His') . " 폴더로 백업

2. 이 ZIP 파일의 모든 내용을 H:\\dsp114.com\\ 루트에 압축 해제

3. db.php 파일 확인 (이미 수정되어 있어야 함):
   \$host = \"localhost\";
   \$user = \"dsp1830\"; 
   \$password = \"ds701018\";
   \$dataname = \"dsp1830\";

4. 업로드 후 테스트 필수 항목:
   □ 명함 (NameCard) - http://dsp114.com/MlangPrintAuto/NameCard/
   □ 자석스티커 (msticker) - http://dsp114.com/MlangPrintAuto/msticker/
   □ 봉투 (envelope) - http://dsp114.com/MlangPrintAuto/envelope/
   □ 카다록 (cadarok) - http://dsp114.com/MlangPrintAuto/cadarok/
   □ 양식지 (NcrFlambeau) - http://dsp114.com/MlangPrintAuto/NcrFlambeau/
   □ 상품권 (MerchandiseBond) - http://dsp114.com/MlangPrintAuto/MerchandiseBond/
   □ 포스터 (LittlePrint) - http://dsp114.com/MlangPrintAuto/LittlePrint/
   □ 전단지 (inserted) - http://dsp114.com/MlangPrintAuto/inserted/
   □ 일반스티커 (sticker_new) - http://dsp114.com/MlangPrintAuto/sticker_new/

=== 문제 발생시 체크사항 ===

1. HTTP 500 에러 시:
   - PHP 오류 로그 확인
   - 파일 권한 확인 (0644 or 0755)
   - include 경로 확인

2. 자동 계산 안되는 경우:
   - 브라우저 개발자도구에서 JavaScript 에러 확인
   - 테이블명 대소문자 확인
   - AJAX 요청 응답 확인

3. 데이터베이스 연결 에러:
   - db.php의 연결정보 확인
   - MariaDB 서비스 상태 확인

총 파일 수: $total_files 개
생성 일시: " . date('Y-m-d H:i:s') . "
";
    
    $zip->addFromString('DEPLOYMENT_README.txt', $deployment_readme);
    
    $zip->close();
    echo "\n✅ ZIP 파일 생성 완료: $zip_file\n";
    echo "총 파일 수: " . ($total_files + 1) . "개 (README 포함)\n";
} else {
    echo "❌ ZIP 파일 생성 실패\n";
}

echo "\n=== 배포 패키지 생성 완료 ===\n";
echo "파일 위치: $zip_file\n";
echo "이 ZIP 파일을 카페24 FTP 루트 디렉토리에 업로드하여 압축 해제하세요.\n";
?>