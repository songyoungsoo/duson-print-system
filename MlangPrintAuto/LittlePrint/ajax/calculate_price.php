<?php
/**
 * LittlePrint 가격 계산 API
 * 모든 선택 옵션을 받아서 실시간 가격을 계산합니다.
 * 
 * 사용법: GET /calculate_price.php?MY_type=1&MY_Fsd=2&PN_type=3&MY_amount=100&POtype=2&ordertype=total
 * 
 * 파라미터:
 * - MY_type: 종류 ID
 * - MY_Fsd: 종이종류 ID  
 * - PN_type: 종이규격 ID
 * - MY_amount: 수량 (100, 200, ..., 1000)
 * - POtype: 인쇄면 (1=단면, 2=양면)
 * - ordertype: 주문형태 (total=디자인+인쇄, print=인쇄만, design=디자인만)
 */

// 부트스트랩 로드
require_once __DIR__ . '/bootstrap.php';

try {
    // Ajax 컨트롤러 생성
    $controller = createAjaxController();
    
    // 가격 계산 처리
    $controller->calculatePrice();
    
} catch (Exception $e) {
    // 예외는 부트스트랩의 예외 핸들러에서 처리됩니다
    throw $e;
}