<?php
/**
 * 단순하고 강력한 파일 업로드 핸들러
 * 모든 9개 품목에서 동일하게 사용
 */
class SimpleUploadHandler {
    
    private static $products = [
        'inserted'        => '_MlangPrintAuto_inserted_index.php',
        'namecard'        => '_MlangPrintAuto_namecard_index.php',
        'envelope'        => '_MlangPrintAuto_envelope_index.php',
        'sticker'         => '_MlangPrintAuto_sticker_new_index.php',
        'msticker'        => '_MlangPrintAuto_msticker_index.php',
        'cadarok'         => '_MlangPrintAuto_cadarok_index.php',
        'littleprint'     => '_MlangPrintAuto_littleprint_index.php',
        'ncrflambeau'     => '_MlangPrintAuto_ncrflambeau_index.php',
        'merchandisebond' => '_MlangPrintAuto_merchandisebond_index.php'
    ];
    
    /**
     * 파일 업로드 처리
     * @return array ['files' => [], 'folder' => '', 'count' => 0]
     */
    public static function process($productCode, $files) {
        $result = ['files' => [], 'folder' => '', 'count' => 0];
        
        if (!isset(self::$products[$productCode])) {
            error_log("❌ 지원하지 않는 품목: $productCode");
            return $result;
        }
        
        // 경로 생성
        $base = self::$products[$productCode];
        $path = sprintf("%s/%s/%s/%s/%s/", 
            $base, date('Y'), date('md'), $_SERVER['REMOTE_ADDR'], time());
        
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/ImgFolder/' . $path;
        
        // 폴더 생성
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
        
        // 파일 처리
        if (empty($files['uploaded_files'])) {
            $result['folder'] = $path;
            return $result;
        }
        
        // uploaded_files[]로 전송하면 다차원 배열
        if (is_array($files['uploaded_files']['name'])) {
            $count = count($files['uploaded_files']['name']);
            for ($i = 0; $i < $count; $i++) {
                if ($files['uploaded_files']['error'][$i] !== UPLOAD_ERR_OK) {
                    error_log("파일 에러 [{$i}]: " . $files['uploaded_files']['error'][$i]);
                    continue;
                }
                
                $name = $files['uploaded_files']['name'][$i];
                $tmp = $files['uploaded_files']['tmp_name'][$i];
                $size = $files['uploaded_files']['size'][$i];
                
                error_log("파일 처리: name=$name, tmp=$tmp, size=$size");
                
                if (move_uploaded_file($tmp, $fullPath . $name)) {
                    $result['files'][] = [
                        'original_name' => $name,
                        'saved_name' => $name,
                        'path' => $fullPath . $name,
                        'size' => $size,
                        'web_url' => '/ImgFolder/' . $path . $name
                    ];
                    $result['count']++;
                    error_log("✅ 파일 저장 성공: " . $fullPath . $name);
                } else {
                    error_log("❌ move_uploaded_file 실패: $tmp → " . $fullPath . $name);
                }
            }
        } else {
            // 단일 파일
            $name = $files['uploaded_files']['name'];
            $tmp = $files['uploaded_files']['tmp_name'];
            $size = $files['uploaded_files']['size'];
            
            if (move_uploaded_file($tmp, $fullPath . $name)) {
                $result['files'][] = [
                    'original_name' => $name,
                    'saved_name' => $name,
                    'path' => $fullPath . $name,
                    'size' => $size,
                    'web_url' => '/ImgFolder/' . $path . $name
                ];
                $result['count']++;
            }
        }
        
        $result['folder'] = $path;
        return $result;
    }
    
    /**
     * DB 저장
     */
    public static function saveDB($db, $basketId, $upload, $thingCate, $workMemo = '', $uploadMethod = 'upload') {
        $json = json_encode($upload['files'], JSON_UNESCAPED_UNICODE);
        
        $sql = "UPDATE shop_temp SET ImgFolder=?, ThingCate=?, uploaded_files=?, work_memo=?, upload_method=? WHERE no=?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $upload['folder'], $thingCate, $json, $workMemo, $uploadMethod, $basketId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
?>
