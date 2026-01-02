<?php
/**
 * dsp114.com에서 교정 이미지 다운로드
 * FTP: dsp114.com - duson1830/du1830
 */

require_once "/var/www/html/db.php";

// FTP 설정
$ftp_host = "dsp114.com";
$ftp_user = "duson1830";
$ftp_pass = "du1830";

// 로컬 저장 경로
$local_base = "/var/www/html";

echo "╔══════════════════════════════════════════╗\n";
echo "║   dsp114.com 이미지 다운로드              ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// FTP 연결
echo "FTP 연결 중...\n";
$ftp = ftp_connect($ftp_host);
if (!$ftp) {
    die("FTP 연결 실패: $ftp_host\n");
}

if (!ftp_login($ftp, $ftp_user, $ftp_pass)) {
    die("FTP 로그인 실패\n");
}

ftp_pasv($ftp, true);
echo "FTP 연결 성공!\n\n";

// 84277 이후 주문 이미지 조회
$result = mysqli_query($db, "SELECT no, Type, ImgFolder, ThingCate FROM mlangorder_printauto WHERE no > 84277");

$downloaded = 0;
$failed = 0;
$skipped = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $no = $row['no'];
    $imgFolder = trim($row['ImgFolder']);
    $thingCate = trim($row['ThingCate']);
    
    // 패턴 1: _MlangPrintAuto_ 경로
    if (!empty($imgFolder) && strpos($imgFolder, '_MlangPrintAuto_') !== false) {
        $remote_dir = "/ImgFolder/" . $imgFolder;
        $local_dir = $local_base . "/ImgFolder/" . $imgFolder;
        
        // 디렉토리 생성
        if (!is_dir($local_dir)) {
            mkdir($local_dir, 0755, true);
        }
        
        // 원격 디렉토리의 파일 목록 가져오기
        $files = @ftp_nlist($ftp, $remote_dir);
        if ($files && count($files) > 0) {
            foreach ($files as $remote_file) {
                $filename = basename($remote_file);
                if ($filename == '.' || $filename == '..') continue;
                
                $local_file = $local_dir . "/" . $filename;
                if (!file_exists($local_file)) {
                    if (@ftp_get($ftp, $local_file, $remote_file, FTP_BINARY)) {
                        echo "  ✓ no=$no: $filename\n";
                        $downloaded++;
                    } else {
                        echo "  ✗ no=$no: $filename (다운로드 실패)\n";
                        $failed++;
                    }
                } else {
                    $skipped++;
                }
            }
        }
    }
    // 패턴 2: ../shop/data/ 경로
    elseif (!empty($imgFolder) && strpos($imgFolder, '../shop/data/') !== false) {
        $filename = basename($imgFolder);
        if (empty($filename)) continue;
        
        $remote_file = "/shop/data/" . $filename;
        $local_dir = $local_base . "/shop/data";
        $local_file = $local_dir . "/" . $filename;
        
        if (!is_dir($local_dir)) {
            mkdir($local_dir, 0755, true);
        }
        
        if (!file_exists($local_file)) {
            if (@ftp_get($ftp, $local_file, $remote_file, FTP_BINARY)) {
                echo "  ✓ no=$no: $filename\n";
                $downloaded++;
            } else {
                echo "  ✗ no=$no: $filename (다운로드 실패)\n";
                $failed++;
            }
        } else {
            $skipped++;
        }
    }
    // 패턴 3: ThingCate만 있음 (ImgFolder 비어있음)
    elseif (empty($imgFolder) && !empty($thingCate) && strpos($thingCate, '.') !== false) {
        // ThingCate 파일은 보통 ImgFolder/Order/ 또는 다른 경로에 있음
        // 여러 가능한 경로 시도
        $possible_paths = [
            "/ImgFolder/" . $thingCate,
            "/ImgFolder/Order/" . $thingCate,
            "/shop/data/" . $thingCate
        ];
        
        $local_dir = $local_base . "/ImgFolder";
        if (!is_dir($local_dir)) {
            mkdir($local_dir, 0755, true);
        }
        $local_file = $local_dir . "/" . $thingCate;
        
        if (!file_exists($local_file)) {
            $found = false;
            foreach ($possible_paths as $remote_file) {
                if (@ftp_get($ftp, $local_file, $remote_file, FTP_BINARY)) {
                    echo "  ✓ no=$no: $thingCate\n";
                    $downloaded++;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo "  ? no=$no: $thingCate (경로 찾지 못함)\n";
                $failed++;
            }
        } else {
            $skipped++;
        }
    }
}

ftp_close($ftp);

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "결과:\n";
echo "  - 다운로드 완료: $downloaded 건\n";
echo "  - 이미 존재 (스킵): $skipped 건\n";
echo "  - 실패: $failed 건\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
?>
