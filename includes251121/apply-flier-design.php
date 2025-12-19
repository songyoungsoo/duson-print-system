<?php
/**
 * 전단지 디자인 시스템 적용 헬퍼
 * 기존 계산 로직은 건드리지 않고 CSS만 적용
 */

// 전단지 디자인 CSS를 페이지에 추가하는 함수
function applyFlierDesign() {
    echo '<link rel="stylesheet" href="/css/flier-design-system.css">';
}

// 기존 폼에 전단지 스타일 클래스만 추가하는 함수
function addFlierClasses($html) {
    // select 태그에 클래스 추가
    $html = str_replace('<select', '<select class="field-selector"', $html);
    
    // 버튼에 클래스 추가 (기존 onclick은 유지)
    $html = preg_replace('/<button([^>]*)>/', '<button$1 class="action-button">', $html);
    $html = preg_replace('/<input type="submit"([^>]*)>/', '<input type="submit"$1 class="action-button">', $html);
    
    // 테이블을 카드 형태로 (구조는 유지)
    $html = str_replace('<table', '<table class="flier-card"', $html);
    
    return $html;
}

// 헤더 스타일만 적용 (내용은 그대로)
function wrapWithFlierHeader($title) {
    return '<div class="flier-header">' . $title . '</div>';
}
?>