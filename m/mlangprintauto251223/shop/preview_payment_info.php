<?php
// 결제 정보 미리보기 테스트 파일
include '../includes/company_info.php';
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>결제 정보 미리보기</title>
    <style>
        body {
            font-family: "Malgun Gothic", "맑은 고딕", Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .preview-section {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
        }
        h2 {
            color: #2c5aa0;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>🔍 견적서 결제 정보 미리보기</h1>
    
    <div class="preview-section">
        <h2>HTML 버전 (견적서에 표시될 모습)</h2>
        <?php echo getPaymentInfoHTML('quote'); ?>
    </div>
    
    <div class="preview-section">
        <h2>기본 버전</h2>
        <?php echo getPaymentInfoHTML(); ?>
    </div>
    
    <div class="preview-section">
        <h2>회사 정보 전체</h2>
        <?php
        $info = getCompanyInfo();
        echo "<pre>";
        print_r($info);
        echo "</pre>";
        ?>
    </div>
    
    <div class="preview-section">
        <h2>테스트 링크</h2>
        <p><a href="/mlangprintauto/shop/generate_quote_pdf.php?customer_name=홍길동&customer_phone=010-1234-5678" target="_blank">📄 견적서 테스트 (HTML)</a></p>
        <p><a href="generate_quote_tcpdf.php?customer_name=홍길동&customer_phone=010-1234-5678" target="_blank">📄 견적서 테스트 (TCPDF)</a></p>
        <p><a href="cart.php">🛒 장바구니로 돌아가기</a></p>
    </div>
</body>
</html>