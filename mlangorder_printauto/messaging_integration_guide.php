<?php
/**
 * 메시징 통합 가이드 - 카카오톡 & SMS
 * 사무용 표형태 주문완료 시스템용
 * Created: 2025년 8월 (AI Assistant)
 */

// 보안을 위해 직접 접근 차단
if (!defined('MESSAGING_GUIDE_ACCESS')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access not allowed');
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>메시징 통합 시스템 구현 가이드</title>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .guide-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .guide-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .guide-header h1 {
            margin: 0;
            font-size: 2rem;
        }
        .guide-content {
            padding: 30px;
        }
        .section {
            margin-bottom: 40px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
        }
        .section h2 {
            color: #2c3e50;
            margin-top: 0;
        }
        .api-box {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            padding: 20px;
            margin: 15px 0;
        }
        .api-box h3 {
            color: #667eea;
            margin-top: 0;
        }
        .code-block {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        .status-ready {
            background: #d4edda;
            color: #155724;
        }
        .status-setup {
            background: #fff3cd;
            color: #856404;
        }
        .status-pending {
            background: #f8d7da;
            color: #721c24;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .feature-card {
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 6px;
            padding: 20px;
        }
        .feature-card h4 {
            color: #667eea;
            margin-top: 0;
        }
        .step-list {
            counter-reset: step-counter;
            list-style: none;
            padding: 0;
        }
        .step-list li {
            counter-increment: step-counter;
            margin-bottom: 15px;
            padding-left: 40px;
            position: relative;
        }
        .step-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            background: #667eea;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="guide-container">
        <div class="guide-header">
            <h1>📱 메시징 통합 시스템 구현 가이드</h1>
            <p>카카오톡 알림톡 & SMS 문자 발송 시스템</p>
        </div>
        
        <div class="guide-content">
            <!-- 현재 상태 -->
            <div class="section">
                <h2>📊 현재 구현 상태</h2>
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4>📧 이메일 발송 <span class="status-badge status-ready">완료</span></h4>
                        <p>PHPMailer 기반 HTML 이메일 발송 시스템이 완전히 구현되어 있습니다.</p>
                        <ul>
                            <li>사무용 표형태 이메일 템플릿</li>
                            <li>주문내역 상세 정보 포함</li>
                            <li>발송 로그 기록</li>
                            <li>에러 처리 및 재시도</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h4>💬 카카오톡 알림톡 <span class="status-badge status-setup">API 키 필요</span></h4>
                        <p>구현 준비는 완료되었으나 카카오톡 비즈니스 계정과 API 키가 필요합니다.</p>
                        <ul>
                            <li>템플릿 구조 설계 완료</li>
                            <li>메시지 발송 로직 준비</li>
                            <li>비즈니스 계정 승인 대기</li>
                        </ul>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📱 SMS 문자 <span class="status-badge status-setup">API 키 필요</span></h4>
                        <p>국내 주요 SMS 서비스와 연동 가능한 구조로 설계되었습니다.</p>
                        <ul>
                            <li>네이버 클라우드 플랫폼 준비</li>
                            <li>KT 클라우드 연동 가능</li>
                            <li>단문/장문 메시지 지원</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 카카오톡 알림톡 -->
            <div class="section">
                <h2>💬 카카오톡 알림톡 구현</h2>
                
                <div class="api-box">
                    <h3>1. 카카오톡 비즈니스 계정 준비 (필수)</h3>
                    <ol class="step-list">
                        <li><strong>카카오톡 채널 개설</strong><br>
                            - 카카오톡 채널 관리자센터(center-pf.kakao.com) 접속<br>
                            - 사업자등록증으로 비즈니스 계정 인증</li>
                        <li><strong>알림톡 서비스 신청</strong><br>
                            - 카카오톡 비즈니스 → 메시지 → 알림톡 신청<br>
                            - 사업자 인증 및 템플릿 심사 (3-5일 소요)</li>
                        <li><strong>API 키 발급</strong><br>
                            - 카카오 개발자센터(developers.kakao.com)<br>
                            - 앱 생성 → 비즈니스 > 카카오톡 메시지 활성화</li>
                    </ol>
                </div>

                <div class="api-box">
                    <h3>2. 추천 카카오톡 API 서비스</h3>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h4>🥇 솔루션모비 (추천)</h4>
                            <p><strong>비용:</strong> 알림톡 8원/건, 친구톡 15원/건</p>
                            <p><strong>장점:</strong> 국내 1위, 안정성 우수, 24시간 지원</p>
                            <p><strong>웹사이트:</strong> https://www.solutionmobi.com</p>
                        </div>
                        
                        <div class="feature-card">
                            <h4>🥈 NHN 토스트</h4>
                            <p><strong>비용:</strong> 알림톡 9원/건, 친구톡 16원/건</p>
                            <p><strong>장점:</strong> 네이버 계열, 개발자 친화적</p>
                            <p><strong>웹사이트:</strong> https://toast.com/service/notification</p>
                        </div>
                        
                        <div class="feature-card">
                            <h4>🥉 카카오톡 비즈니스 API</h4>
                            <p><strong>비용:</strong> 직접 연동 시 더 저렴</p>
                            <p><strong>단점:</strong> 복잡한 인증 과정, 높은 기술 난이도</p>
                            <p><strong>웹사이트:</strong> https://business.kakao.com</p>
                        </div>
                    </div>
                </div>

                <div class="api-box">
                    <h3>3. 구현 코드 샘플 (솔루션모비 기준)</h3>
                    <div class="code-block">
// 카카오톡 알림톡 발송 함수
function sendKakaoAlimtalk($phone, $templateCode, $templateData) {
    $url = 'https://apis.solutionmobi.com/rest/kakao/v1/alimtalk/send';
    
    $headers = [
        'Content-Type: application/json; charset=UTF-8',
        'Authorization: Bearer ' . KAKAO_API_KEY
    ];
    
    $data = [
        'senderKey' => KAKAO_SENDER_KEY,
        'templateCode' => $templateCode,
        'recipientList' => [
            [
                'recipientNo' => $phone,
                'templateParameter' => $templateData
            ]
        ]
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// 주문완료 알림톡 발송
$templateData = [
    'customerName' => $name,
    'orderCount' => count($orderList),
    'totalAmount' => number_format($totalAmountVat),
    'orderNumbers' => $orders
];

$result = sendKakaoAlimtalk($phone, 'ORDER_COMPLETE', $templateData);
                    </div>
                </div>
            </div>

            <!-- SMS 문자 발송 -->
            <div class="section">
                <h2>📱 SMS 문자 발송 구현</h2>
                
                <div class="api-box">
                    <h3>1. 추천 SMS API 서비스</h3>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h4>🥇 네이버 클라우드 플랫폼 (추천)</h4>
                            <p><strong>비용:</strong> 단문 8원/건, 장문 26원/건</p>
                            <p><strong>장점:</strong> 네이버 안정성, 무료 크레딧 제공</p>
                            <p><strong>웹사이트:</strong> https://www.ncloud.com</p>
                        </div>
                        
                        <div class="feature-card">
                            <h4>🥈 KT 클라우드</h4>
                            <p><strong>비용:</strong> 단문 9원/건, 장문 27원/건</p>
                            <p><strong>장점:</strong> 통신사 직접 연동, 높은 도달률</p>
                            <p><strong>웹사이트:</strong> https://cloud.kt.com</p>
                        </div>
                        
                        <div class="feature-card">
                            <h4>🥉 솔루션모비</h4>
                            <p><strong>비용:</strong> 단문 8원/건, 장문 25원/건</p>
                            <p><strong>장점:</strong> 카카오톡과 통합 관리 가능</p>
                            <p><strong>웹사이트:</strong> https://www.solutionmobi.com</p>
                        </div>
                    </div>
                </div>

                <div class="api-box">
                    <h3>2. 네이버 클라우드 플랫폼 설정</h3>
                    <ol class="step-list">
                        <li><strong>계정 생성 및 인증</strong><br>
                            - 네이버 클라우드 플랫폼 가입<br>
                            - 사업자 인증 또는 개인 인증</li>
                        <li><strong>Simple & Easy Notification Service (SENS) 활성화</strong><br>
                            - 콘솔에서 SENS 서비스 신청<br>
                            - SMS 발송 번호 등록 (080 번호 등록)</li>
                        <li><strong>API 키 발급</strong><br>
                            - 마이페이지 → 인증키 관리<br>
                            - Access Key ID, Secret Key 발급</li>
                    </ol>
                </div>

                <div class="api-box">
                    <h3>3. 구현 코드 샘플 (네이버 클라우드)</h3>
                    <div class="code-block">
// SMS 발송 함수 (네이버 클라우드 SENS)
function sendSMS($phone, $message, $subject = '') {
    $url = 'https://sens.apigw.ntruss.com/sms/v2/services/' . NCLOUD_SERVICE_ID . '/messages';
    $timestamp = time() * 1000;
    
    // 시그니처 생성
    $method = 'POST';
    $uri = '/sms/v2/services/' . NCLOUD_SERVICE_ID . '/messages';
    $message_body = json_encode([
        'type' => strlen($message) > 90 ? 'LMS' : 'SMS',
        'from' => SENDER_PHONE,
        'subject' => $subject,
        'content' => $message,
        'messages' => [
            ['to' => $phone]
        ]
    ]);
    
    $signature_string = $method . ' ' . $uri . "\n" . $timestamp . "\n" . NCLOUD_ACCESS_KEY;
    $signature = base64_encode(hash_hmac('sha256', $signature_string, NCLOUD_SECRET_KEY, true));
    
    $headers = [
        'Content-Type: application/json; charset=utf-8',
        'x-ncp-apigw-timestamp: ' . $timestamp,
        'x-ncp-iam-access-key: ' . NCLOUD_ACCESS_KEY,
        'x-ncp-apigw-signature-v2: ' . $signature
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $message_body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// 주문완료 SMS 발송
$smsMessage = "[두손기획인쇄] {$name}님 주문완료\n";
$smsMessage .= "주문건수: " . count($orderList) . "건\n";
$smsMessage .= "결제금액: " . number_format($totalAmountVat) . "원\n";
$smsMessage .= "입금계좌: 국민은행 999-1688-2384\n";
$smsMessage .= "문의: 02-2632-1830";

$result = sendSMS($phone, $smsMessage, '주문완료 안내');
                    </div>
                </div>
            </div>

            <!-- 통합 구현 -->
            <div class="section">
                <h2>🔗 통합 메시징 시스템 구현</h2>
                
                <div class="api-box">
                    <h3>통합 메시징 클래스</h3>
                    <div class="code-block">
class OrderMessaging {
    private $emailSender;
    private $kakaoSender;
    private $smsSender;
    
    public function __construct() {
        $this->emailSender = new EmailSender();
        $this->kakaoSender = new KakaoSender();
        $this->smsSender = new SMSSender();
    }
    
    public function sendOrderComplete($orderData, $channels = ['email']) {
        $results = [];
        
        foreach ($channels as $channel) {
            switch ($channel) {
                case 'email':
                    $results['email'] = $this->emailSender->sendOrderComplete($orderData);
                    break;
                case 'kakao':
                    $results['kakao'] = $this->kakaoSender->sendOrderComplete($orderData);
                    break;
                case 'sms':
                    $results['sms'] = $this->smsSender->sendOrderComplete($orderData);
                    break;
            }
        }
        
        return $results;
    }
}

// 사용 예제
$messaging = new OrderMessaging();
$result = $messaging->sendOrderComplete($orderData, ['email', 'kakao', 'sms']);
                    </div>
                </div>
            </div>

            <!-- 설정 가이드 -->
            <div class="section">
                <h2>⚙️ 설정 및 활성화 방법</h2>
                
                <div class="api-box">
                    <h3>1. 즉시 활성화 가능한 기능</h3>
                    <div class="feature-card">
                        <h4>📧 이메일 발송 (현재 사용 가능)</h4>
                        <p>이미 완전히 구현되어 있으며 바로 사용할 수 있습니다.</p>
                        <ul>
                            <li>✅ PHPMailer 설정 완료</li>
                            <li>✅ HTML 이메일 템플릿 완성</li>
                            <li>✅ 발송 로그 시스템 구축</li>
                            <li>✅ 에러 처리 및 재시도 로직</li>
                        </ul>
                    </div>
                </div>

                <div class="api-box">
                    <h3>2. 추가 설정이 필요한 기능</h3>
                    <div class="feature-grid">
                        <div class="feature-card">
                            <h4>💬 카카오톡 알림톡</h4>
                            <p><strong>소요 시간:</strong> 3-7일</p>
                            <p><strong>예상 비용:</strong> 월 5-10만원 (1,000건 기준)</p>
                            <ol>
                                <li>카카오톡 채널 개설</li>
                                <li>비즈니스 계정 인증</li>
                                <li>알림톡 템플릿 승인</li>
                                <li>API 키 발급 및 연동</li>
                            </ol>
                        </div>
                        
                        <div class="feature-card">
                            <h4>📱 SMS 문자</h4>
                            <p><strong>소요 시간:</strong> 1-2일</p>
                            <p><strong>예상 비용:</strong> 월 2-5만원 (1,000건 기준)</p>
                            <ol>
                                <li>네이버 클라우드 가입</li>
                                <li>SENS 서비스 신청</li>
                                <li>발송 번호 등록</li>
                                <li>API 키 발급 및 연동</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 우선순위 -->
            <div class="section">
                <h2>🎯 구현 우선순위 추천</h2>
                
                <ol class="step-list">
                    <li><strong>이메일 발송 시스템 활용</strong> (현재 완료)<br>
                        모든 고객에게 상세한 주문내역을 전달할 수 있는 완벽한 시스템</li>
                    
                    <li><strong>SMS 문자 발송 추가</strong> (1-2일 소요)<br>
                        빠른 알림과 높은 확인률을 위한 보조 채널</li>
                    
                    <li><strong>카카오톡 알림톡 도입</strong> (1주일 소요)<br>
                        젊은 층 고객과 브랜드 인지도 향상을 위한 최신 채널</li>
                </ol>
            </div>
        </div>
    </div>
</body>
</html>