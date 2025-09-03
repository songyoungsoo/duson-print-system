<?php
/**
 * 스티커 갤러리 이미지 API
 * 포트폴리오 디렉토리에서 스티커 관련 이미지를 최신순으로 가져옴
 * Created: 2025년 8월 (AI Assistant)
 */

header('Content-Type: application/json; charset=utf-8');

// 포트폴리오 디렉토리 경로
$portfolio_dir = "../../bbs/upload/portfolio/";
$web_portfolio_dir = "/bbs/upload/portfolio/";

try {
    $images = [];
    
    if (is_dir($portfolio_dir)) {
        // 디렉토리의 모든 파일 스캔
        $all_files = scandir($portfolio_dir);
        $sticker_files = [];
        
        // 스티커 관련 키워드
        $sticker_keywords = ['sticker', '스티커', 'label', '라벨', 'stic'];
        
        foreach ($all_files as $file) {
            // 이미지 파일만 필터링
            if (preg_match('/\.(jpg|jpeg|png|gif|bmp)$/i', $file)) {
                $file_path = $portfolio_dir . $file;
                
                // 파일명에 스티커 관련 키워드가 있는지 확인
                $is_sticker = false;
                $filename_lower = strtolower($file);
                
                foreach ($sticker_keywords as $keyword) {
                    if (strpos($filename_lower, strtolower($keyword)) !== false) {
                        $is_sticker = true;
                        break;
                    }
                }
                
                // 스티커 이미지이거나 키워드가 없는 일반 이미지도 포함 (더 많은 샘플 제공)
                if ($is_sticker || true) { // 모든 이미지 포함하되 스티커 우선
                    if (file_exists($file_path)) {
                        $sticker_files[] = [
                            'filename' => $file,
                            'path' => $web_portfolio_dir . $file,
                            'mtime' => filemtime($file_path),
                            'filesize' => filesize($file_path),
                            'is_sticker' => $is_sticker
                        ];
                    }
                }
            }
        }
        
        // 스티커 이미지를 우선하고, 수정시간 기준 최신순 정렬
        usort($sticker_files, function($a, $b) {
            // 스티커 이미지를 우선
            if ($a['is_sticker'] && !$b['is_sticker']) return -1;
            if (!$a['is_sticker'] && $b['is_sticker']) return 1;
            // 같은 카테고리면 최신순
            return $b['mtime'] - $a['mtime'];
        });
        
        // URL 파라미터로 전체 이미지 요청 여부 확인
        $showAll = isset($_GET['all']) && $_GET['all'] === 'true';
        $limit = $showAll ? 20 : 4; // 썸네일용은 4개, 더보기용은 20개
        
        // 제한된 개수만 선택
        $display_files = array_slice($sticker_files, 0, $limit);
        
        foreach ($display_files as $index => $file_info) {
            $images[] = [
                'id' => 'sticker_' . ($index + 1),
                'title' => '스티커 샘플 ' . ($index + 1),
                'category' => '스티커',
                'thumbnail' => $file_info['path'],
                'path' => $file_info['path'],
                'full_image' => $file_info['path'],
                'src' => $file_info['path'],
                'url' => $file_info['path'],
                'description' => '프리미엄 스티커 샘플',
                'upload_date' => date('Y-m-d H:i:s', $file_info['mtime']),
                'file_size' => $file_info['filesize'],
                'filename' => $file_info['filename']
            ];
        }
    }
    
    // 응답
    echo json_encode([
        'success' => true,
        'data' => $images,
        'total' => count($images),
        'message' => count($images) > 0 ? '이미지 로드 성공' : '표시할 이미지가 없습니다'
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'data' => [],
        'total' => 0,
        'message' => '오류가 발생했습니다: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>