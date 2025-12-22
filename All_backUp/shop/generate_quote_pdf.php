<?php
/**
 * PDF 견적서 생성
 * 경로: /shop/generate_quote_pdf.php
 * 기능: 주문 정보를 바탕으로 PDF 견적서를 생성하여 다운로드
 */

session_start();
include "../db.php";

// 로그인 체크
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['id_login_ok']) || isset($_COOKIE['id_login_ok']);

if (!$is_logged_in) {
    header('Location: /member/login.php');
    exit;
}

// 사용자 정보
if (isset($_SESSION['user_id'])) {
    $user_name = $_SESSION['user_name'] ?? '';
} elseif (isset($_SESSION['id_login_ok'])) {
    $user_name = $_SESSION['id_login_ok']['id'] ?? '';
} elseif (isset($_COOKIE['id_login_ok'])) {
    $user_name = $_COOKIE['id_login_ok'];
}

// 주문번호 파라미터
$order_no = $_GET['order_no'] ?? '';

if (empty($order_no)) {
    echo "<script>alert('주문번호가 없습니다.'); history.back();</script>";
    exit;
}

// 주문 정보 조회
$query = "SELECT * FROM mlangorder_printauto WHERE no = ? AND name = ?";
$stmt = mysqli_prepare($db, $query);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "is", $order_no, $user_name);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    
    if (!$order) {
        echo "<script>alert('주문 정보를 찾을 수 없습니다.'); history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('데이터베이스 오류가 발생했습니다.'); history.back();</script>";
    exit;
}

// HTML to PDF conversion using built-in method
$filename = "견적서_" . $order_no . "_" . date('Y-m-d') . ".html";

// HTML 콘텐츠 생성
ob_start();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>견적서 - 두손기획인쇄</title>
    <style>
        body { 
            font-family: '맑은 고딕', Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4;
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name { 
            font-size: 24px; 
            font-weight: bold; 
            margin-bottom: 10px;
        }
        .contact-info { 
            font-size: 11px; 
            color: #666;
        }
        .quote-title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 20px 0;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 15px 0;
        }
        th, td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left;
        }
        th { 
            background-color: #f5f5f5; 
            font-weight: bold;
            text-align: center;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { 
            font-weight: bold; 
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            font-size: 11px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- 헤더 -->
    <div class="header">
        <div class="company-name">두손기획인쇄</div>
        <div class="contact-info">
            서울 영등포구 영등포로 36길 9, 송호빌딩 1F<br>
            TEL: 02-2632-1830 | FAX: 02-2632-1831 | 무료전화: 1688-2384<br>
            사업자등록번호: 201-10-69847
        </div>
    </div>

    <!-- 견적서 제목 -->
    <div class="quote-title">견 적 서</div>

    <!-- 주문 정보 -->
    <table>
        <tr>
            <th width="120">견적번호</th>
            <td><?php echo htmlspecialchars($order['no']); ?></td>
            <th width="120">견적일자</th>
            <td><?php echo date('Y-m-d', strtotime($order['date'])); ?></td>
        </tr>
        <tr>
            <th>주문자명</th>
            <td><?php echo htmlspecialchars($order['name']); ?></td>
            <th>연락처</th>
            <td><?php echo htmlspecialchars($order['phone'] ?? $order['Hendphone']); ?></td>
        </tr>
        <tr>
            <th>회사명</th>
            <td><?php echo htmlspecialchars($order['bizname'] ?? '-'); ?></td>
            <th>이메일</th>
            <td><?php echo htmlspecialchars($order['email'] ?? '-'); ?></td>
        </tr>
        <tr>
            <th>배송지</th>
            <td colspan="3">
                <?php echo htmlspecialchars($order['zip'] ?? ''); ?>
                <?php echo htmlspecialchars($order['zip1'] ?? ''); ?>
                <?php echo htmlspecialchars($order['zip2'] ?? ''); ?>
            </td>
        </tr>
    </table>

    <!-- 상품 정보 -->
    <table>
        <thead>
            <tr>
                <th width="50">번호</th>
                <th>품명</th>
                <th width="80">규격</th>
                <th width="80">수량</th>
                <th width="100">단가</th>
                <th width="120">금액</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><?php echo htmlspecialchars($order['Type']); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($order['ThingCate'] ?? '-'); ?></td>
                <td class="text-center"><?php echo number_format($order['Gensu']); ?>부</td>
                <td class="text-right"><?php echo $order['Gensu'] > 0 ? number_format($order['money_1'] / $order['Gensu']) : '0'; ?>원</td>
                <td class="text-right"><?php echo number_format($order['money_1']); ?>원</td>
            </tr>
            <?php if ($order['money_2'] > 0): ?>
            <tr>
                <td class="text-center">2</td>
                <td>디자인비</td>
                <td class="text-center">-</td>
                <td class="text-center">1식</td>
                <td class="text-right"><?php echo number_format($order['money_2']); ?>원</td>
                <td class="text-right"><?php echo number_format($order['money_2']); ?>원</td>
            </tr>
            <?php endif; ?>
            <?php if ($order['additional_options_total'] > 0): ?>
            <tr>
                <td class="text-center">3</td>
                <td>추가옵션</td>
                <td class="text-center">-</td>
                <td class="text-center">1식</td>
                <td class="text-right"><?php echo number_format($order['additional_options_total']); ?>원</td>
                <td class="text-right"><?php echo number_format($order['additional_options_total']); ?>원</td>
            </tr>
            <?php endif; ?>
            <?php if ($order['money_4'] > 0): ?>
            <tr>
                <td class="text-center">4</td>
                <td>배송비</td>
                <td class="text-center">-</td>
                <td class="text-center">1회</td>
                <td class="text-right"><?php echo number_format($order['money_4']); ?>원</td>
                <td class="text-right"><?php echo number_format($order['money_4']); ?>원</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-center">합계</td>
                <td class="text-right"><?php echo number_format($order['money_5'] ?: $order['money_1']); ?>원</td>
            </tr>
        </tfoot>
    </table>

    <!-- 추가 정보 -->
    <?php if (!empty($order['cont'])): ?>
    <table>
        <tr>
            <th>특이사항</th>
        </tr>
        <tr>
            <td style="min-height: 60px; vertical-align: top;">
                <?php echo nl2br(htmlspecialchars($order['cont'])); ?>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <!-- 결제 정보 -->
    <table>
        <tr>
            <th width="120">결제방법</th>
            <td><?php echo htmlspecialchars($order['bank'] ?? '무통장입금'); ?></td>
        </tr>
        <?php if (!empty($order['bankname'])): ?>
        <tr>
            <th>입금자명</th>
            <td><?php echo htmlspecialchars($order['bankname']); ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <!-- 입금계좌 정보 -->
    <table>
        <tr>
            <th colspan="2" style="background-color: #e9ecef;">입금계좌</th>
        </tr>
        <tr>
            <td>기업은행 016-044463-01-019 (예금주: 두손기획)</td>
        </tr>
        <tr>
            <td>국민은행 267901-04-158843 (예금주: 두손기획)</td>
        </tr>
    </table>

    <!-- 푸터 -->
    <div class="footer">
        <p>본 견적서는 <?php echo date('Y년 m월 d일'); ?>부로 30일간 유효합니다.</p>
        <p>견적 문의: 02-2632-1830 / 1688-2384 (무료전화)</p>
        <p style="margin-top: 10px; font-weight: bold;">두손기획인쇄를 이용해 주셔서 감사합니다.</p>
    </div>
</body>
</html>
<?php
$html_content = ob_get_clean();

// 브라우저에 HTML로 출력 (사용자가 인쇄 기능으로 PDF 저장 가능)
header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: inline; filename="' . $filename . '"');

echo $html_content;
?>
<script>
// 자동으로 인쇄 다이얼로그 열기 (선택사항)
// window.print();
</script>