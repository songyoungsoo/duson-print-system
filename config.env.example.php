<?php
/**
 * 환경 설정 예시 파일
 *
 * 이 파일을 config.env.php로 복사하고 실제 값으로 변경하세요.
 * config.env.php는 .gitignore에 추가되어 있어야 합니다.
 *
 * @since 2026-01-13
 */

// =====================================================
// 네이버페이 설정
// https://developer.pay.naver.com 에서 발급
// =====================================================
putenv('NAVERPAY_CLIENT_ID=your_client_id');
putenv('NAVERPAY_CLIENT_SECRET=your_client_secret');
putenv('NAVERPAY_CHAIN_ID=your_chain_id');
putenv('NAVERPAY_MODE=sandbox'); // sandbox 또는 production

// =====================================================
// 카카오페이 설정
// https://developers.kakao.com 에서 발급
// =====================================================
putenv('KAKAOPAY_CID=TC0ONETIME'); // 테스트용 CID
putenv('KAKAOPAY_ADMIN_KEY=your_admin_key');
putenv('KAKAOPAY_MODE=sandbox'); // sandbox 또는 production

// =====================================================
// 카카오 알림톡 설정
// https://bizmsg.kr 에서 발급
// =====================================================
putenv('KAKAO_ALIMTALK_API_KEY=your_api_key');
putenv('KAKAO_ALIMTALK_USER_ID=your_user_id');
putenv('KAKAO_ALIMTALK_SENDER_KEY=your_sender_key');

// =====================================================
// CoolSMS 설정
// https://coolsms.co.kr 에서 발급
// =====================================================
putenv('COOLSMS_API_KEY=your_api_key');
putenv('COOLSMS_API_SECRET=your_api_secret');
putenv('COOLSMS_SENDER=0212345678'); // 발신번호

// =====================================================
// 스마트택배 API 설정
// https://tracking.sweettracker.co.kr 에서 발급
// =====================================================
putenv('SMARTTAEKBAE_API_KEY=your_api_key');

// =====================================================
// 이메일 설정 (SMTP)
// =====================================================
putenv('EMAIL_FROM=noreply@dsp1830.shop');
putenv('SMTP_HOST=smtp.gmail.com');
putenv('SMTP_PORT=587');
putenv('SMTP_USER=your_email@gmail.com');
putenv('SMTP_PASS=your_app_password');
