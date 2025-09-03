<?php
/**
 * LittlePrint 종이규격 조회 API
 * 선택된 종류(category)에 따른 종이규격 목록을 반환합니다.
 * 
 * 사용법: GET /get_paper_sizes.php?category_id=1
 * 
 * LittlePrint 관계: TreeNo = 종류.no
 */

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    // Ajax 컨트롤러 생성
    $controller = createAjaxController();
    
    // 종이규격 목록 조회 처리
    $controller->getPaperSizes();
    
} catch (Exception $e) {
    // 예외는 부트스트랩의 예외 핸들러에서 처리됩니다
    throw $e;
}