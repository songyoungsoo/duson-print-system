<?php
/**
 * 세션 초기화 공통 파일
 * 경로: includes/session_init.php
 *
 * 용도: 모든 PHP 파일에서 세션 시작 전에 include하여 권한 문제 해결
 */

// 세션이 이미 시작되었는지 확인
if (session_status() == PHP_SESSION_NONE) {
    // 세션 저장 경로 설정 (권한 문제 해결)
    $session_path = dirname(__DIR__) . '/sessions';

    // 디렉토리가 없으면 생성
    if (!is_dir($session_path)) {
        mkdir($session_path, 0777, true);
    }

    // 세션 저장 경로 지정
    ini_set('session.save_path', $session_path);

    // 세션 시작
    session_start();
}
