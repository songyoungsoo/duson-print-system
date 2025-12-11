<?php
/**
 * 회사 정보 설정 파일
 * 견적서, 주문서 등에서 사용되는 회사 정보를 중앙에서 관리
 */

// 회사 기본 정보
define('COMPANY_NAME', '두손기획인쇄');
define('COMPANY_OWNER', '차경선');
define('COMPANY_BUSINESS_NUMBER', '107-06-45106');

// 주소 정보
define('COMPANY_ADDRESS', '서울 영등포구 영등포로 36길 9 송호빌딩 1층');
define('COMPANY_ADDRESS_DETAIL', '서울 영등포구 영등포로 36길 9');
define('COMPANY_BUILDING', '송호빌딩 1층');

// 연락처 정보
define('COMPANY_PHONE', '02-2632-1830');
define('COMPANY_FAX', '02-2632-1829'); // 팩스번호가 있으면 입력
define('COMPANY_EMAIL', 'dsp1830@naver.com'); // 이메일이 있으면 입력

// 사업 정보
define('COMPANY_BUSINESS_TYPE', '제조·도매');
define('COMPANY_BUSINESS_ITEM', '인쇄업·광고물');

// 영업시간
define('COMPANY_BUSINESS_HOURS', '평일 09:00~18:00 (토요일, 일요일, 공휴일 휴무)');

// 결제 정보
define('COMPANY_ACCOUNT_HOLDER', '두손기획인쇄 차경선');
define('COMPANY_BANK_KOOKMIN', '국민은행 999-1688-2384');
define('COMPANY_BANK_SHINHAN', '신한은행 110-342-543507');
define('COMPANY_BANK_NONGHYUP', '농협 301-2632-1829');

// 배송 정보
define('COMPANY_DELIVERY_INFO', '택배는 기본이 착불입니다');

// 회사 정보를 배열로 반환하는 함수
function getCompanyInfo() {
    return [
        'name' => COMPANY_NAME,
        'owner' => COMPANY_OWNER,
        'business_number' => COMPANY_BUSINESS_NUMBER,
        'address' => COMPANY_ADDRESS,
        'address_detail' => COMPANY_ADDRESS_DETAIL,
        'building' => COMPANY_BUILDING,
        'phone' => COMPANY_PHONE,
        'fax' => COMPANY_FAX,
        'email' => COMPANY_EMAIL,
        'business_type' => COMPANY_BUSINESS_TYPE,
        'business_item' => COMPANY_BUSINESS_ITEM,
        'business_hours' => COMPANY_BUSINESS_HOURS,
        'account_holder' => COMPANY_ACCOUNT_HOLDER,
        'bank_kookmin' => COMPANY_BANK_KOOKMIN,
        'bank_shinhan' => COMPANY_BANK_SHINHAN,
        'bank_nonghyup' => COMPANY_BANK_NONGHYUP,
        'delivery_info' => COMPANY_DELIVERY_INFO
    ];
}

// 견적서용 회사 정보 HTML 생성
function getCompanyInfoHTML($style = 'default') {
    $info = getCompanyInfo();
    
    switch ($style) {
        case 'header':
            return "
                <div class='company-info'>
                    <strong>{$info['name']}</strong><br>
                    <div style='margin-top: 8px; font-size: 13px;'>
                        <div style='display: inline-block; margin-right: 20px;'>📍 {$info['address']}</div>
                        <div style='display: inline-block;'>📞 {$info['phone']}</div>
                    </div>
                    <div style='margin-top: 5px; font-size: 12px; color: #777;'>
                        대표자: {$info['owner']} | 사업자등록번호: {$info['business_number']} | 업태: {$info['business_type']} | 종목: {$info['business_item']}
                    </div>
                </div>
            ";
            
        case 'footer':
            return "
                <p style='font-weight: bold; font-size: 10px; margin: 4px 0;'>{$info['name']} | 대표자: {$info['owner']} | 사업자등록번호: {$info['business_number']}</p>
            ";
            
        case 'contact':
            return "• 문의사항이 있으시면 <strong>{$info['phone']}</strong>으로 연락 주시기 바랍니다. • 영업시간: {$info['business_hours']}";
            
        default:
            return "
                {$info['name']}<br>
                주소: {$info['address']}<br>
                전화: {$info['phone']}<br>
                대표자: {$info['owner']} | 사업자등록번호: {$info['business_number']}
            ";
    }
}

// PDF용 회사 정보 텍스트 생성
function getCompanyInfoForPDF($type = 'header') {
    $info = getCompanyInfo();
    
    switch ($type) {
        case 'header':
            return [
                'name' => $info['name'],
                'line1' => $info['name'] . ' | 대표자: ' . $info['owner'] . ' | 사업자등록번호: ' . $info['business_number'],
                'line2' => '주소: ' . $info['address'] . ' | 전화: ' . $info['phone'],
                'line3' => '업태: ' . $info['business_type'] . ' | 종목: ' . $info['business_item']
            ];
            
        case 'footer':
            return [
                'line1' => $info['name'] . ' | 대표자: ' . $info['owner'] . ' | 사업자등록번호: ' . $info['business_number'],
                'line2' => '주소: ' . $info['address'] . ' | 전화: ' . $info['phone']
            ];
            
        default:
            return $info;
    }
}

// 결제 정보 HTML 생성
function getPaymentInfoHTML($style = 'default') {
    $info = getCompanyInfo();
    
    switch ($style) {
        case 'quote':
            return "
                <div style='background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #2c5aa0;'>
                    <h4 style='margin: 0 0 8px 0; color: #2c5aa0; font-size: 13px;'>💳 결제 안내</h4>
                    <div style='font-size: 11px; line-height: 1.4;'>
                        <p style='margin: 0 0 4px 0;'><strong>예금주:</strong> {$info['account_holder']}</p>
                        <p style='margin: 0 0 4px 0;'>• {$info['bank_kookmin']} • {$info['bank_shinhan']} • {$info['bank_nonghyup']} • 카드결제 가능</p>
                        <p style='margin: 0; color: #e74c3c; font-weight: 600; font-size: 10px;'>📦 {$info['delivery_info']}</p>
                    </div>
                </div>
            ";
            
        default:
            return "
                예금주: {$info['account_holder']}<br>
                {$info['bank_kookmin']} / {$info['bank_shinhan']} / {$info['bank_nonghyup']}<br>
                카드 결제 가능<br>
                {$info['delivery_info']}
            ";
    }
}
?>