<?php
/**
 * SMTP 설정 파일
 *
 * 네이버 메일 SMTP 설정
 * - 네이버 메일 > 환경설정 > POP3/SMTP 설정 > 사용함 체크
 * - 앱 비밀번호: 네이버 > 내정보 > 보안설정 > 2단계 인증 > 애플리케이션 비밀번호
 */

return [
    'host'     => 'smtp.naver.com',
    'port'     => 587,
    'secure'   => 'tls',  // tls or ssl
    'username' => 'dsp1830@naver.com',  // 네이버 이메일
    'password' => '2CP3P5BTS83Y',
    'from_email' => 'dsp1830@naver.com',
    'from_name'  => '두손기획인쇄',
];
