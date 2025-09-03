<?php
/**
 * LittlePrint 메인 카테고리(종류) 조회 API
 * 최상위 종류 목록을 반환합니다.
 * 
 * 사용법: GET /get_main_categories.php
 */

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    // Ajax 컨트롤러 생성
    $controller = createAjaxController();
    
    // 메인 카테고리 목록 조회 처리
    $controller->getMainCategories();
    
} catch (Exception $e) {
    // 예외는 부트스트랩의 예외 핸들러에서 처리됩니다
    throw $e;
}