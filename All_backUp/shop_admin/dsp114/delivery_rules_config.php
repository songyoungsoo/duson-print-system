<?php
/**
 * 택배 수량별 가격 배정 규칙
 * PHP 5.2 호환 / EUC-KR
 *
 * 각 제품별 수량 구간에 따른 박스 수량과 택배비를 정의합니다.
 */

return array(
    // ===== 명함 (NameCard) =====
    'namecard' => array(
        array('min' => 0, 'max' => 5000, 'box' => 1, 'price' => 3000, 'label' => '5000매 이하'),
        array('min' => 5001, 'max' => 10000, 'box' => 2, 'price' => 4000, 'label' => '10000매'),
        array('min' => 10001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 5000, 'label' => '10000매 이상')
    ),

    // ===== 상품권 (MerchandiseBond) =====
    'merchandisebond' => array(
        array('min' => 0, 'max' => 5000, 'box' => 1, 'price' => 3000, 'label' => '5000매 이하'),
        array('min' => 5001, 'max' => 10000, 'box' => 2, 'price' => 4000, 'label' => '10000매'),
        array('min' => 10001, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 5000, 'label' => '10000매 이상')
    ),

    // ===== 전단지 90g아트지 A4 (합판인쇄) =====
    'inserted_90g_a4' => array(
        array('min' => 0, 'max' => 499, 'box' => 1, 'price' => 3500, 'label' => '0.5연 (500매 미만)'),
        array('min' => 500, 'max' => 999, 'box' => 1, 'price' => 6000, 'label' => '1연 (1000매 미만)'),
        array('min' => 1000, 'max' => 1999, 'box' => 2, 'price' => 12000, 'label' => '2연'),
        array('min' => 2000, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 18000, 'label' => '3연 이상')
    ),

    // ===== 전단지 B5(16절) 182x257 =====
    'inserted_b5_16' => array(
        array('min' => 0, 'max' => 499, 'box' => 1, 'price' => 3500, 'label' => '0.5연'),
        array('min' => 500, 'max' => 999, 'box' => 2, 'price' => 7000, 'label' => '1연'),
        array('min' => 1000, 'max' => PHP_INT_MAX, 'box' => 3, 'price' => 10500, 'label' => '2연 이상')
    ),

    // ===== 스티커 (기본 규칙) =====
    'sticker' => array(
        array('min' => 0, 'max' => 1000, 'box' => 1, 'price' => 2500, 'label' => '1000매 이하'),
        array('min' => 1001, 'max' => 3000, 'box' => 1, 'price' => 3000, 'label' => '3000매 이하'),
        array('min' => 3001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 4000, 'label' => '3000매 이상')
    ),

    // ===== 봉투 (envelope) =====
    'envelope' => array(
        array('min' => 0, 'max' => 1000, 'box' => 1, 'price' => 3000, 'label' => '1000개 이하'),
        array('min' => 1001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 4000, 'label' => '1000개 이상')
    ),

    // ===== 기본 규칙 (매칭되지 않는 제품) =====
    'default' => array(
        array('min' => 0, 'max' => 1000, 'box' => 1, 'price' => 3000, 'label' => '기본 (1000 이하)'),
        array('min' => 1001, 'max' => PHP_INT_MAX, 'box' => 2, 'price' => 4000, 'label' => '기본 (1000 이상)')
    )
);
?>
