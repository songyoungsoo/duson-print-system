<?php
session_start();
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// TCPDF 라이브러리 포함 (Composer로 설치된 경우)
// composer require tecnickcom/tcpdf
if (file_exists('../../vendor/autoload.php')) {
    require_once('../../vendor/autoload.php');
    $tcpdf_available = true;
} elseif (file_exists('../../lib/tcpdf/tcpdf.php')) {
    // 직접 다운로드한 경우
    require_once('../../lib/tcpdf/tcpdf.php');
    $tcpdf_available = true;
} else {
    // TCPDF가 없으면 HTML 버전으로 리다이렉트
    header('Location: /MlangPrintAuto/shop/generate_quote_pdf.php');
    exit;
}

require_once('../../includes/functions.php');
require_once('../includes/company_info.php');

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// ID로 한글명 가져오기 함수
function getKoreanName($connect, $id) {
    if (!$connect || !$id) {
        return $id;
    }
    
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return $id;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $id;
}

// 장바구니 데이터 가져오기
function getCartItemsForQuote($connect, $session_id) {
    $query = "SELECT * FROM shop_temp WHERE session_id = ? ORDER BY no DESC";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return [];
    }
    
    mysqli_stmt_bind_param($stmt, 's', $session_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $items = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    
    mysqli_stmt_close($stmt);
    return $items;
}

// 고객 정보 받기
$customer_info = [
    'name' => $_GET['customer_name'] ?? '고객님',
    'phone' => $_GET['customer_phone'] ?? '',
    'company' => $_GET['customer_company'] ?? '',
    'email' => $_GET['customer_email'] ?? '',
    'memo' => $_GET['quote_memo'] ?? ''
];

// 장바구니 데이터 조회
$cart_items = getCartItemsForQuote($connect, $session_id);

if (empty($cart_items)) {
    die('장바구니가 비어있습니다.');
}

// TCPDF 인스턴스 생성
class QuotePDF extends TCPDF {
    // 헤더 설정
    public function Header() {
        // 로고 (있는 경우)
        // $this->Image('../../images/logo.png', 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // 회사명
        $this->SetFont('nanumgothic', 'B', 20);
        $this->SetTextColor(44, 90, 160);
        $this->Cell(0, 15, COMPANY_NAME, 0, 1, 'C');
        
        // 견적서 제목
        $this->SetFont('nanumgothic', 'B', 24);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 15, '견 적 서', 0, 1, 'C');
        
        // 선 그리기
        $this->SetDrawColor(44, 90, 160);
        $this->SetLineWidth(0.5);
        $this->Line(15, 45, 195, 45);
        
        $this->Ln(10);
    }
    
    // 푸터 설정
    public function Footer() {
        $this->SetY(-25);
        $this->SetFont('nanumgothic', '', 8);
        $this->SetTextColor(128, 128, 128);
        
        // 회사 정보
        $company_info = getCompanyInfoForPDF('footer');
        $this->Cell(0, 4, $company_info['line1'], 0, 1, 'C');
        $this->Cell(0, 4, $company_info['line2'], 0, 1, 'C');
        $this->Cell(0, 4, '업태: ' . COMPANY_BUSINESS_TYPE . ' | 종목: ' . COMPANY_BUSINESS_ITEM, 0, 1, 'C');
        
        // 페이지 번호
        $this->Cell(0, 5, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

// PDF 생성
$pdf = new QuotePDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// 문서 정보 설정
$pdf->SetCreator(COMPANY_NAME . ' 견적 시스템');
$pdf->SetAuthor(COMPANY_NAME);
$pdf->SetTitle('견적서 - ' . date('Y-m-d'));
$pdf->SetSubject('인쇄물 견적서');

// 폰트 설정 (한글 지원)
$pdf->SetFont('nanumgothic', '', 10);

// 여백 설정
$pdf->SetMargins(15, 55, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(15);

// 자동 페이지 나누기
$pdf->SetAutoPageBreak(TRUE, 25);

// 페이지 추가
$pdf->AddPage();

// 견적 정보
$quote_date = date('Y년 m월 d일');
$quote_number = 'Q' . date('YmdHis');
$valid_date = date('Y년 m월 d일', strtotime('+30 days'));

// 견적 정보 테이블
$pdf->SetFont('nanumgothic', 'B', 12);
$pdf->SetFillColor(248, 249, 250);
$pdf->Cell(90, 8, '견적번호: ' . $quote_number, 1, 0, 'L', true);
$pdf->Cell(90, 8, '견적일자: ' . $quote_date, 1, 1, 'L', true);
$pdf->Cell(90, 8, '고객명: ' . $customer_info['name'], 1, 0, 'L', true);
$pdf->Cell(90, 8, '유효기간: ' . $valid_date, 1, 1, 'L', true);

// 추가 고객 정보가 있으면 표시
if (!empty($customer_info['company']) || !empty($customer_info['phone'])) {
    if (!empty($customer_info['company'])) {
        $pdf->Cell(90, 8, '회사명: ' . $customer_info['company'], 1, 0, 'L', true);
    } else {
        $pdf->Cell(90, 8, '', 1, 0, 'L', true);
    }
    
    if (!empty($customer_info['phone'])) {
        $pdf->Cell(90, 8, '연락처: ' . $customer_info['phone'], 1, 1, 'L', true);
    } else {
        $pdf->Cell(90, 8, '', 1, 1, 'L', true);
    }
}

// 요청사항이 있으면 추가
if (!empty($customer_info['memo'])) {
    $pdf->Ln(5);
    $pdf->SetFont('nanumgothic', 'B', 10);
    $pdf->Cell(0, 6, '📝 요청사항:', 0, 1, 'L');
    $pdf->SetFont('nanumgothic', '', 9);
    $pdf->MultiCell(0, 5, $customer_info['memo'], 1, 'L', true);
}

$pdf->Ln(10);

// 상품 목록 테이블 헤더
$pdf->SetFont('nanumgothic', 'B', 10);
$pdf->SetFillColor(44, 90, 160);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(15, 10, '번호', 1, 0, 'C', true);
$pdf->Cell(45, 10, '상품명', 1, 0, 'C', true);
$pdf->Cell(60, 10, '상품 상세', 1, 0, 'C', true);
$pdf->Cell(20, 10, '수량', 1, 0, 'C', true);
$pdf->Cell(25, 10, '단가', 1, 0, 'C', true);
$pdf->Cell(25, 10, '금액', 1, 1, 'C', true);

// 상품 목록
$pdf->SetFont('nanumgothic', '', 9);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(248, 249, 250);

$total_price = 0;
$item_number = 1;

foreach ($cart_items as $item) {
    $product_name = getProductName($item);
    $product_details = getProductDetailsForPDF($item, $connect);
    $quantity = getQuantity($item);
    $unit_price = intval($item['st_price'] ?? 0);
    $total_item_price = $unit_price;
    
    $total_price += $total_item_price;
    
    // 행 높이 계산 (상세 정보에 따라)
    $row_height = max(8, ceil(strlen($product_details) / 30) * 4);
    
    $fill = ($item_number % 2 == 0);
    
    $pdf->Cell(15, $row_height, $item_number, 1, 0, 'C', $fill);
    $pdf->Cell(45, $row_height, $product_name, 1, 0, 'L', $fill);
    
    // 상세 정보는 여러 줄로 표시
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(60, 4, $product_details, 1, 'L', $fill);
    $pdf->SetXY($x + 60, $y);
    
    $pdf->Cell(20, $row_height, $quantity, 1, 0, 'C', $fill);
    $pdf->Cell(25, $row_height, number_format($unit_price) . '원', 1, 0, 'R', $fill);
    $pdf->Cell(25, $row_height, number_format($total_item_price) . '원', 1, 1, 'R', $fill);
    
    $item_number++;
}

// 합계 섹션
$pdf->Ln(5);
$vat = intval($total_price * 0.1);
$total_with_vat = $total_price + $vat;

$pdf->SetFont('nanumgothic', 'B', 12);
$pdf->SetFillColor(248, 249, 250);

$pdf->Cell(135, 8, '', 0, 0);
$pdf->Cell(25, 8, '공급가액:', 1, 0, 'L', true);
$pdf->Cell(25, 8, number_format($total_price) . '원', 1, 1, 'R', true);

$pdf->Cell(135, 8, '', 0, 0);
$pdf->Cell(25, 8, '부가세(10%):', 1, 0, 'L', true);
$pdf->Cell(25, 8, number_format($vat) . '원', 1, 1, 'R', true);

$pdf->SetFont('nanumgothic', 'B', 14);
$pdf->SetFillColor(44, 90, 160);
$pdf->SetTextColor(255, 255, 255);

$pdf->Cell(135, 10, '', 0, 0);
$pdf->Cell(25, 10, '총 견적금액:', 1, 0, 'L', true);
$pdf->Cell(25, 10, number_format($total_with_vat) . '원', 1, 1, 'R', true);

// 결제 정보
$pdf->Ln(10);
$pdf->SetFont('nanumgothic', 'B', 14);
$pdf->SetTextColor(44, 90, 160);
$pdf->Cell(0, 8, '💳 결제 안내', 0, 1, 'L');

$pdf->SetFont('nanumgothic', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 6, '예금주: ' . COMPANY_ACCOUNT_HOLDER, 0, 1, 'L');

$pdf->SetFont('nanumgothic', '', 11);
$pdf->Cell(0, 5, '• ' . COMPANY_BANK_KOOKMIN, 0, 1, 'L');
$pdf->Cell(0, 5, '• ' . COMPANY_BANK_SHINHAN, 0, 1, 'L');
$pdf->Cell(0, 5, '• ' . COMPANY_BANK_NONGHYUP, 0, 1, 'L');
$pdf->Cell(0, 5, '• 카드 결제 가능', 0, 1, 'L');

$pdf->SetFont('nanumgothic', 'B', 11);
$pdf->SetTextColor(231, 76, 60);
$pdf->Cell(0, 6, '📦 ' . COMPANY_DELIVERY_INFO, 0, 1, 'L');

// 안내사항
$pdf->Ln(5);
$pdf->SetFont('nanumgothic', 'B', 13);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, '※ 안내사항', 0, 1, 'L');

$pdf->SetFont('nanumgothic', '', 11);
$pdf->Cell(0, 6, '• 본 견적서는 ' . $valid_date . '까지 유효합니다.', 0, 1, 'L');
$pdf->Cell(0, 6, '• 실제 주문 시 디자인 파일 및 세부 사양에 따라 금액이 변동될 수 있습니다.', 0, 1, 'L');
$pdf->Cell(0, 6, '• 문의사항이 있으시면 ' . COMPANY_PHONE . '으로 연락 주시기 바랍니다.', 0, 1, 'L');
$pdf->Cell(0, 6, '• 영업시간: ' . COMPANY_BUSINESS_HOURS, 0, 1, 'L');

// PDF 출력
$filename = '견적서_' . date('YmdHis') . '.pdf';
$pdf->Output($filename, 'D'); // D: 다운로드, I: 브라우저에서 보기

function getProductName($item) {
    $product_type = $item['product_type'] ?? 'unknown';
    
    switch ($product_type) {
        case 'sticker':
            return '일반 스티커';
        case 'namecard':
            return '명함';
        case 'cadarok':
            return '카다록/리플렛';
        case 'msticker':
            return '자석 스티커';
        case 'inserted':
            return '전단지';
        case 'littleprint':
            return '소량 포스터';
        case 'envelope':
            return '봉투';
        case 'merchandisebond':
            return '상품권';
        case 'ncrflambeau':
            return '양식지/NCR';
        default:
            return '인쇄물';
    }
}

function getProductDetailsForPDF($item, $connect) {
    $product_type = $item['product_type'] ?? 'unknown';
    $details = [];
    
    switch ($product_type) {
        case 'sticker':
            if (!empty($item['jong'])) $details[] = '재질: ' . $item['jong'];
            if (!empty($item['garo']) && !empty($item['sero'])) {
                $details[] = '크기: ' . $item['garo'] . 'mm × ' . $item['sero'] . 'mm';
            }
            if (!empty($item['domusong'])) $details[] = '모양: ' . $item['domusong'];
            break;
            
        case 'namecard':
            if (!empty($item['MY_type'])) {
                $details[] = '종류: ' . getKoreanName($connect, $item['MY_type']);
            }
            if (!empty($item['Section'])) {
                $details[] = '재질: ' . getKoreanName($connect, $item['Section']);
            }
            if (!empty($item['POtype'])) {
                $details[] = '인쇄면: ' . ($item['POtype'] == '1' ? '단면' : '양면');
            }
            if (!empty($item['ordertype'])) {
                $details[] = '주문방식: ' . ($item['ordertype'] == 'total' ? '디자인+인쇄' : '인쇄만');
            }
            break;
            
        default:
            if (!empty($item['MY_type'])) {
                $details[] = '구분: ' . getKoreanName($connect, $item['MY_type']);
            }
            if (!empty($item['Section'])) {
                $details[] = '옵션: ' . getKoreanName($connect, $item['Section']);
            }
            break;
    }
    
    return implode("\n", $details);
}

function getQuantity($item) {
    $product_type = $item['product_type'] ?? 'unknown';
    
    switch ($product_type) {
        case 'sticker':
            return !empty($item['mesu']) ? $item['mesu'] . '매' : '1매';
        case 'namecard':
            return !empty($item['MY_amount']) ? $item['MY_amount'] . '매' : '500매';
        default:
            return !empty($item['MY_amount']) ? $item['MY_amount'] . '개' : '1개';
    }
}

mysqli_close($connect);
?>