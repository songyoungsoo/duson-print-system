<?php
/**
 * 구 URL 대소문자 리다이렉트
 * MlangPrintAuto/NameCard/ → mlangprintauto/namecard/ (301)
 * 리눅스 서버는 대소문자 구분하므로 구 URL을 소문자로 변환하여 리다이렉트
 */
$path = strtolower(trim($_GET['path'] ?? '', '/'));
$query = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
// path 파라미터 제거
$query = preg_replace('/[?&]path=[^&]*/', '', $query);
$query = ltrim($query, '?&');
$query = $query ? '?' . $query : '';

header("Location: /mlangprintauto/{$path}{$query}", true, 301);
exit;
