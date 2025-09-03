<?php
/**
 * Create deployment package for Cafe24 with all fixes applied
 */

echo "카페24 배포 패키지 생성 시작...\n";

$source_dir = __DIR__;
$deploy_dir = $source_dir . '/cafe24_deploy_package';
$zip_file = $source_dir . '/cafe24_fixed_files.zip';

// Create deployment directory
if (!is_dir($deploy_dir)) {
    mkdir($deploy_dir, 0755, true);
    echo "배포 디렉토리 생성: $deploy_dir\n";
}

// Files and directories to exclude from deployment
$exclude_patterns = [
    '/^\./',              // Hidden files
    '/\.git/',           // Git files
    '/node_modules/',    // Node modules
    '/vendor/',          // Composer vendor
    '/temp/',            // Temp files
    '/cache/',           // Cache files
    '/fix_.*\.php$/',    // Fix scripts
    '/create_deployment_package\.php$/', // This script itself
    '/cafe24_deploy_package/', // The deployment directory itself
    '/cafe24_fixed_files\.zip$/', // Previous zip files
    '/debug_/',          // Debug files
    '/test_/',           // Test files
    '/backup_/',         // Backup files
];

/**
 * Copy directory recursively with exclusions
 */
function copyDirectory($src, $dst, $exclude_patterns = []) {
    $dir = opendir($src);
    @mkdir($dst);
    
    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            $src_file = $src . '/' . $file;
            $dst_file = $dst . '/' . $file;
            
            // Check if file should be excluded
            $should_exclude = false;
            foreach ($exclude_patterns as $pattern) {
                if (preg_match($pattern, $src_file) || preg_match($pattern, $file)) {
                    $should_exclude = true;
                    break;
                }
            }
            
            if ($should_exclude) {
                echo "제외: $src_file\n";
                continue;
            }
            
            if (is_dir($src_file)) {
                copyDirectory($src_file, $dst_file, $exclude_patterns);
            } else {
                copy($src_file, $dst_file);
                echo "복사: $file\n";
            }
        }
    }
    closedir($dir);
}

// Copy all necessary files
echo "파일 복사 중...\n";
copyDirectory($source_dir, $deploy_dir, $exclude_patterns);

// Create a summary file for the deployment
$summary_content = "카페24 배포 패키지 - " . date('Y-m-d H:i:s') . "

적용된 수정사항:
1. MySQL 테이블명 대소문자 수정 (101개 파일)
   - mlangprintauto_ → MlangPrintAuto_

2. db_constants.php 참조 제거 (28개 파일)
   - Cafe24에서 누락된 파일 참조 제거

3. gallery_helper.php 조건부 처리 (10개 파일)
   - file_exists() 체크 추가

4. PHP 문법 오류 수정 (13개 오류)
   - include_product_gallery 함수 호출 수정
   - getCategoryOptions 따옴표 수정
   - NcrFlambeau auth.php 경로 수정

주요 변경 파일:
- MlangPrintAuto/*/index.php (모든 제품 페이지)
- db.php (데이터베이스 설정)
- includes/auth.php
- admin/MlangPrintAuto/*.php

배포 방법:
1. 기존 H:\\dsp114.com\\ 파일들 백업
2. 이 패키지의 모든 파일을 H:\\dsp114.com\\에 업로드
3. db.php 파일에서 데이터베이스 연결정보 확인:
   Host: localhost
   User: dsp1830
   Password: ds701018
   Database: dsp1830

테스트 필요 항목:
□ 명함 주문 시스템 (NameCard)
□ 자석스티커 주문 시스템 (msticker) 
□ 봉투 주문 시스템 (envelope)
□ 카다록 주문 시스템 (cadarok)
□ 양식지 주문 시스템 (NcrFlambeau)
□ 상품권 주문 시스템 (MerchandiseBond)
□ 포스터 주문 시스템 (LittlePrint)
□ 전단지 주문 시스템 (inserted)
□ 일반스티커 주문 시스템 (sticker_new)

모든 시스템에서 자동 가격 계산이 정상적으로 작동해야 합니다.
";

file_put_contents($deploy_dir . '/DEPLOYMENT_README.txt', $summary_content);

// Create ZIP file
if (file_exists($zip_file)) {
    unlink($zip_file);
}

$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($deploy_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($iterator as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($deploy_dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    
    $zip->close();
    echo "\n✅ ZIP 파일 생성 완료: $zip_file\n";
} else {
    echo "❌ ZIP 파일 생성 실패\n";
}

// Clean up deployment directory
function removeDirectory($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir."/".$object)) {
                    removeDirectory($dir."/".$object);
                } else {
                    unlink($dir."/".$object);
                }
            }
        }
        rmdir($dir);
    }
}

removeDirectory($deploy_dir);
echo "임시 배포 디렉토리 정리 완료\n";

echo "\n=== 배포 패키지 생성 완료 ===\n";
echo "파일 위치: $zip_file\n";
echo "이 ZIP 파일을 카페24 FTP에 업로드하세요.\n";
echo "자세한 내용은 ZIP 파일 내부의 DEPLOYMENT_README.txt를 참조하세요.\n";
?>