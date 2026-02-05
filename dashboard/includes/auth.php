<?php
/**
 * 대시보드 인증 미들웨어
 * 경로: /dashboard/includes/auth.php
 *
 * 대시보드의 모든 페이지에서 require_once로 포함
 * 기존 /admin/includes/admin_auth.php의 requireAdminAuth() 함수를 활용
 *
 * 사용법:
 * require_once __DIR__ . '/auth.php';
 * // 이 시점에서 관리자 인증이 필수
 */

// 기존 관리자 인증 시스템 로드
require_once __DIR__ . '/../../admin/includes/admin_auth.php';

// 대시보드 접근 시 관리자 인증 필수
// 로그인되지 않은 경우 /admin/mlangprintauto/login.php로 리다이렉트
requireAdminAuth('/dashboard/');
