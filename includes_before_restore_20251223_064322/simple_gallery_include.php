<?php
/**
 * 초간단 갤러리 인클루드 파일
 * 각 품목 페이지에서 단 한 줄로 갤러리 포함
 *
 * 사용법:
 * <?php
 *   $gallery_product = 'namecard'; // 또는 'sticker', 'envelope' 등
 *   include '../../includes/simple_gallery_include.php';
 * ?>
 *
 * 특징:
 * - 기존 이미지 데이터 사용 (gallery_data_adapter.php)
 * - 기존 샘플더보기 모달 사용 (common-gallery-popup.js)
 * - 500×400 메인 컨테이너 + 200% 마우스 오버 줌
 * - 계산 로직 절대 건드리지 않음
 */

// 제품 타입 확인
if (!isset($gallery_product)) {
    echo '<p style="color: red;">오류: $gallery_product 변수가 설정되지 않았습니다.</p>';
    return;
}

// 필요한 파일 인클루드
if (!function_exists('load_gallery_items')) {
    include_once __DIR__ . '/gallery_data_adapter.php';
}

if (!function_exists('render_new_gallery_with_existing_data')) {
    include_once __DIR__ . '/new_gallery_wrapper.php';
}

if (!function_exists('render_gallery_modal')) {
    include_once __DIR__ . '/gallery_component.php';
}

// GALLERY_UNIFIED 플래그 설정
if (!defined('GALLERY_UNIFIED')) {
    define('GALLERY_UNIFIED', true);
}

// 갤러리 렌더링
echo render_new_gallery_with_existing_data($gallery_product);

// 모달 포함 (한 번만)
if (!defined('GALLERY_MODAL_INCLUDED')) {
    echo render_gallery_modal();
    define('GALLERY_MODAL_INCLUDED', true);
}
?>
