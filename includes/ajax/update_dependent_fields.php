<?php
/**
 * 🔄 연관 필드 업데이트 AJAX 엔드포인트
 * 
 * SmartFieldComponent에서 호출되는 AJAX 엔드포인트로,
 * 하나의 필드가 변경되었을 때 연관된 필드들의 옵션을 업데이트합니다.
 * 
 * 작성일: 2025년 8월 9일
 * 상태: 스마트 컴포넌트 시스템 지원 모듈
 */

header('Content-Type: application/json; charset=utf-8');

// 데이터베이스 연결
include "../../db.php";
require_once "../SmartFieldComponent.php";

try {
    // POST 데이터 읽기
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('잘못된 요청 데이터');
    }
    
    $product_type = $input['product_type'] ?? '';
    $field_name = $input['field_name'] ?? '';
    $field_value = $input['field_value'] ?? '';
    
    if (empty($product_type) || empty($field_name)) {
        throw new Exception('필수 파라미터 누락');
    }
    
    // SmartFieldComponent 인스턴스 생성
    $component = new SmartFieldComponent($db, $product_type);
    
    // 연관 필드 업데이트 데이터 가져오기
    $update_data = $component->getFieldUpdateData($field_name, $field_value);
    
    // 성공 응답
    echo json_encode([
        'success' => true,
        'updates' => $update_data,
        'message' => '연관 필드 업데이트 완료'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    // 오류 응답
    echo json_encode([
        'success' => false,
        'updates' => [],
        'message' => $e->getMessage(),
        'debug' => [
            'product_type' => $product_type ?? 'undefined',
            'field_name' => $field_name ?? 'undefined',
            'field_value' => $field_value ?? 'undefined'
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} finally {
    // 데이터베이스 연결 종료
    if (isset($db)) {
        mysqli_close($db);
    }
}
?>