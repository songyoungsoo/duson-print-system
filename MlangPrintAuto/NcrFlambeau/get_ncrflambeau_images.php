<?php
// 명함 성공 패턴 적용 - 안전한 JSON 응답 처리
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

include "../../includes/functions.php";
include "../../db.php";

check_db_connection($db);
mysqli_set_charset($db, "utf8");

// 안전한 JSON 응답 함수 (명함 패턴)
function safe_json_response($success = true, $data = null, $message = '') {
    ob_clean(); // 이전 출력 완전 정리
    
    $response = array(
        'success' => $success,
        'message' => $message
    );
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // 명함 패턴과 동일한 방식으로 양식지 이미지 조회
    $query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
              FROM Mlang_portfolio_bbs 
              WHERE Mlang_bbs_reply='0' AND (CATEGORY LIKE '%양식%' OR CATEGORY LIKE '%NCR%' OR CATEGORY LIKE '%양식지%' OR CATEGORY='양식지')
              ORDER BY Mlang_bbs_no DESC 
              LIMIT 8";
    
    $result = mysqli_query($db, $query);
    
    if (!$result) {
        throw new Exception("쿼리 실행 오류: " . mysqli_error($db));
    }
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $image_path = '';
        $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
        
        // 이미지 경로 결정 (connent 우선, 없으면 link 사용)
        if (!empty($row['Mlang_bbs_connent'])) {
            $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
        } else if (!empty($row['Mlang_bbs_link'])) {
            $image_path = $row['Mlang_bbs_link'];
        }
        
        // 이미지가 있는 경우에만 배열에 추가
        if (!empty($image_path)) {
            $images[] = [
                'id' => $row['Mlang_bbs_no'],
                'title' => $image_title ?: '양식지 샘플 ' . $row['Mlang_bbs_no'],
                'image_path' => $image_path,
                'thumbnail_path' => $image_path, // 썸네일도 같은 이미지 사용
                'path' => $image_path, // 호환성을 위해 추가
                'thumbnail' => $image_path, // 호환성을 위해 추가
                'url' => $image_path, // 호환성을 위해 추가
                'thumb' => $image_path // 호환성을 위해 추가
            ];
        }
    }
    
    // 이미지가 없으면 기본 샘플 이미지 제공
    if (empty($images)) {
        // 명함 이미지를 임시로 사용 (양식지 샘플이 없는 경우)
        $fallback_query = "SELECT Mlang_bbs_no, Mlang_bbs_title, Mlang_bbs_connent, Mlang_bbs_link 
                          FROM Mlang_portfolio_bbs 
                          WHERE Mlang_bbs_reply='0' AND CATEGORY='명함'
                          ORDER BY Mlang_bbs_no DESC 
                          LIMIT 4";
        
        $fallback_result = mysqli_query($db, $fallback_query);
        
        if ($fallback_result) {
            while ($row = mysqli_fetch_assoc($fallback_result)) {
                $image_path = '';
                $image_title = htmlspecialchars($row['Mlang_bbs_title'], ENT_QUOTES);
                
                if (!empty($row['Mlang_bbs_connent'])) {
                    $image_path = '/bbs/upload/portfolio/' . $row['Mlang_bbs_connent'];
                } else if (!empty($row['Mlang_bbs_link'])) {
                    $image_path = $row['Mlang_bbs_link'];
                }
                
                if (!empty($image_path)) {
                    $images[] = [
                        'id' => $row['Mlang_bbs_no'],
                        'title' => '양식지 샘플 (참고용)',
                        'image_path' => $image_path,
                        'thumbnail_path' => $image_path,
                        'path' => $image_path,
                        'thumbnail' => $image_path,
                        'url' => $image_path,
                        'thumb' => $image_path
                    ];
                }
            }
        }
    }
    
    // 여전히 이미지가 없으면 기본 플레이스홀더 제공
    if (empty($images)) {
        $images[] = [
            'id' => 1,
            'title' => '양식지 샘플 준비중',
            'image_path' => 'data:image/svg+xml;base64,' . base64_encode('
                <svg width="400" height="300" xmlns="http://www.w3.org/2000/svg">
                    <rect width="400" height="300" fill="#f8f9fa" stroke="#dee2e6" stroke-width="2"/>
                    <text x="200" y="140" text-anchor="middle" font-family="Arial" font-size="16" fill="#6c757d">📋</text>
                    <text x="200" y="170" text-anchor="middle" font-family="Arial" font-size="14" fill="#6c757d">양식지 샘플</text>
                    <text x="200" y="190" text-anchor="middle" font-family="Arial" font-size="12" fill="#6c757d">준비중입니다</text>
                </svg>
            '),
            'thumbnail_path' => 'data:image/svg+xml;base64,' . base64_encode('
                <svg width="80" height="80" xmlns="http://www.w3.org/2000/svg">
                    <rect width="80" height="80" fill="#f8f9fa" stroke="#dee2e6" stroke-width="1"/>
                    <text x="40" y="45" text-anchor="middle" font-family="Arial" font-size="20" fill="#6c757d">📋</text>
                </svg>
            ')
        ];
    }
    
    error_log("NcrFlambeau 갤러리 이미지 조회: " . count($images) . "개");
    
    safe_json_response(true, $images, '갤러리 이미지 조회 완료');
    
} catch (Exception $e) {
    error_log("NcrFlambeau 갤러리 이미지 조회 오류: " . $e->getMessage());
    safe_json_response(false, null, '갤러리 이미지 조회 중 오류가 발생했습니다.');
}

mysqli_close($db);
?>