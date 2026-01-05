<?php
/**
 * fetch_categories.php
 * 제품 카테고리 목록을 JSON으로 반환
 * AdminMlangOrdert 페이지의 품목 선택 드롭다운에서 사용
 */

header('Content-Type: application/json; charset=utf-8');

// 기본 제품 카테고리 목록
$categories = [
    '전단지',
    '스티커',
    '명함',
    '봉투',
    '포스터',
    '리플렛',
    '카다록',
    '상품권',
    '양식지',
    '자석스티커',
    '투명스티커',
    '유포지스티커',
    '은데드롱스티커',
    '소봉투',
    '대봉투',
    '중봉투',
    '자켓봉투'
];

echo json_encode($categories, JSON_UNESCAPED_UNICODE);
