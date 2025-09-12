<?php
ini_set('display_errors', '0');

$HomeDir = "..";
$PageCode = "PrintAuto";
include "$HomeDir/db.php";
// include $_SERVER['DOCUMENT_ROOT'] . "/MlangPrintAuto/MlangPrintAutoTop.php";

// 데이터베이스 연결은 이미 db.php에서 완료됨
// $db 변수가 이미 설정되어 있음
if (!$db) {
    die("Connection failed: Database connection not established");
}
$db->set_charset("utf8");

// 'no' 값 확인 및 초기화
$no = isset($_REQUEST['no']) ? intval($_REQUEST['no']) : 0;

if ($no > 0) {
    $stmt = $db->prepare("SELECT * FROM mlangorder_printauto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $View_No = htmlspecialchars($row['no']);
        $View_Type = htmlspecialchars($row['Type']);  
        $View_ImgFolder = htmlspecialchars($row['ImgFolder']);    
        $View_Type_1 = $row['Type_1']; // JSON 데이터는 htmlspecialchars 적용하지 않음    
        $View_money_1 = htmlspecialchars($row['money_1']);    
        $View_money_2 = htmlspecialchars($row['money_2']);    
        $View_money_3 = htmlspecialchars($row['money_3']);   
        $View_money_4 = htmlspecialchars($row['money_4']);    
        $View_money_5 = htmlspecialchars($row['money_5']);    
        $View_name = htmlspecialchars($row['name']);    
        $View_email = htmlspecialchars($row['email']);    
        $View_zip = htmlspecialchars($row['zip']);    
        $View_zip1 = htmlspecialchars($row['zip1']);    
        $View_zip2 = htmlspecialchars($row['zip2']);    
        $View_phone = htmlspecialchars($row['phone']);    
        $View_Hendphone = htmlspecialchars($row['Hendphone']); 
        $View_delivery = htmlspecialchars($row['delivery']);       
        $View_bizname = htmlspecialchars($row['bizname']);    
        $View_bank = htmlspecialchars($row['bank']);    
        $View_bankname = htmlspecialchars($row['bankname']);    
        $View_cont = htmlspecialchars($row['cont']);    
        $View_date = htmlspecialchars($row['date']);    
        $View_OrderStyle = htmlspecialchars($row['OrderStyle']);    
        $View_ThingCate = htmlspecialchars($row['ThingCate']);  
        $View_Gensu = htmlspecialchars($row['Gensu']);   
    } else {
        echo ("<script>
            alert('Database error.');
            window.self.close();
        </script>");
        exit;
    }
    $stmt->close();
}
// $db->close(); // 연결 유지 - admin.php에서 계속 사용
?>

<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>주문 상세 정보 - 두손기획인쇄</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script>
var NUM = "0123456789"; 
var SALPHA = "abcdefghijklmnopqrstuvwxyz";
var ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" + SALPHA;

function TypeCheck(s, spc) {
    for (var i = 0; i < s.length; i++) {
        if (spc.indexOf(s.substring(i, i + 1)) < 0) {
            return false;
        }
    }
    return true;
}

function zipcheck() {
    window.open("/MlangPrintAuto/zip.php?mode=search", "zip", "scrollbars=yes,resizable=yes,width=550,height=510,top=10,left=50");
}

function JoinCheckField() {
    var f = document.JoinInfo;
    
    if (f.name.value.trim() == "") {
        alert("성명/상호를 입력해 주세요.");
        f.name.focus();
        return false;
    }
    
    if (f.email.value.trim() == "" || f.email.value.indexOf("@") == -1) {
        alert("올바른 이메일을 입력해 주세요.");
        f.email.focus();
        return false;
    }
    
    if (f.phone.value.trim() == "" && f.Hendphone.value.trim() == "") {
        alert("전화번호 또는 휴대폰 중 하나는 입력해 주세요.");
        f.phone.focus();
        return false;
    }
    
    return true;
}

function printOrder() {
    // PDF 파일명을 주문자명_주문번호 형식으로 설정
    const customerName = "<?=htmlspecialchars($View_name)?>";
    const orderNumber = "<?=$View_No?>";
    
    // 파일명에 사용할 수 없는 문자 제거
    const sanitizeName = (name) => {
        return name.replace(/[^\w가-힣]/g, '_');
    };
    
    const fileName = sanitizeName(customerName) + '_' + orderNumber + '.pdf';
    
    // 페이지 제목을 임시로 변경 (PDF 저장 시 파일명으로 사용됨)
    const originalTitle = document.title;
    document.title = fileName.replace('.pdf', '');
    
    window.print();
    
    // 제목 복원
    setTimeout(() => {
        document.title = originalTitle;
    }, 1000);
}
</script>
<link href="/MlangPrintAuto/css/board.css" rel="stylesheet" type="text/css">
<style>
/* 모던한 관리자 페이지 스타일 */
body {
    font-family: 'Noto Sans KR', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin: 0;
    padding: 15px;
    min-height: 100vh;
    font-size: 14px;
}

.admin-container {
    max-width: 1000px;
    width: calc(100vw - 30px);
    min-height: 600px;
    margin: 0 auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    overflow: visible;
}

.admin-header {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #ffffff;
    padding: 15px 25px;
    border-bottom: 2px solid #3498db;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.admin-header h1 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.admin-header .order-info {
    margin-top: 8px;
    opacity: 1;
    font-size: 0.85rem;
    color: #ffffff;
    font-weight: 500;
}

.admin-content {
    padding: 15px 25px;
    background: #f8f9fa;
    min-height: 520px;
    overflow-y: visible;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.info-card {
    background: white;
    border-radius: 8px;
    padding: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.07);
    border: 1px solid #e9ecef;
}

.info-card h3 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 0.95rem;
    font-weight: 600;
    padding-bottom: 6px;
    border-bottom: 1px solid #e9ecef;
}

.form-section {
    background: white;
    border-radius: 8px;
    padding: 12px 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.07);
    border: 1px solid #e9ecef;
    margin-top: 8px;
}

.form-section h3 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.form-row {
    display: grid;
    grid-template-columns: 120px 1fr;
    gap: 12px;
    margin-bottom: 10px;
    align-items: center;
}

.form-label {
    font-family: 'Noto Sans KR', sans-serif;
    font-weight: 600;
    color: #495057;
    background: #f8f9fa;
    padding: 6px 10px;
    border-radius: 4px;
    text-align: center;
    border: 1px solid #dee2e6;
    font-size: 0.85rem;
}

.form-input {
    font-family: 'Noto Sans KR', sans-serif;
    padding: 6px 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    min-width: 120px;
}

.form-input:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.btn-group {
    text-align: center;
    margin-top: 15px;
    padding-top: 12px;
    border-top: 1px solid #e9ecef;
}

.btn {
    padding: 8px 16px;
    margin: 0 6px;
    border: none;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,123,255,0.3);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    color: white;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(108,117,125,0.3);
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 8px;
    }
    
    .form-label {
        text-align: left;
    }
    
    .admin-content {
        padding: 20px;
    }
}

/* 기존 테이블 스타일 개선 */
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

td {
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: top;
}

/* 텍스트 영역 스타일 개선 */
textarea {
    width: 100%;
    padding: 15px;
    border: 1px solid #ced4da;
    border-radius: 8px;
    font-family: 'Noto Sans KR', sans-serif;
    font-size: 0.95rem;
    line-height: 1.5;
    resize: vertical;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

textarea:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

/* 프린트 전용 스타일 */
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    body {
        font-family: 'Noto Sans KR', 'Malgun Gothic', sans-serif !important;
        font-size: 9pt !important;
        line-height: 1.2 !important;
        color: black !important;
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* 화면 전용 요소 숨기기 */
    .admin-header,
    .btn-group,
    .admin-container,
    .admin-content {
        all: unset !important;
    }

    .admin-header,
    .btn-group {
        display: none !important;
    }

    /* A5 크기 주문서 컨테이너 */
    .print-container {
        display: flex !important;
        flex-direction: column !important;
        width: 190mm !important;
        height: 277mm !important;
        background: white !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-order {
        width: 190mm;
        height: 135mm;
        padding: 3mm;
        box-sizing: border-box;
        position: relative;
        page-break-inside: avoid;
    }

    .print-order:first-child {
        border-bottom: none;
    }

    .print-order:last-child {
        border-top: none;
    }

    /* 절취선 */
    .print-divider {
        width: 100%;
        height: 7mm;
        position: relative;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .print-divider::before {
        content: "";
        width: 100%;
        height: 0;
        border-top: 1px dashed #333;
        position: absolute;
        top: 50%;
        left: 0;
        z-index: 1;
    }

    .print-divider::after {
        content: "✂ 절취선 ✂";
        background: white;
        padding: 0 8px;
        font-size: 8pt;
        color: #333;
        z-index: 2;
        position: relative;
    }

    .print-title {
        text-align: center;
        font-size: 13pt;
        font-weight: bold;
        margin-bottom: 3mm;
        border-bottom: 1px solid #000;
        padding-bottom: 1mm;
    }

    .print-info-section {
        margin-bottom: 2mm;
    }

    .print-info-title {
        font-size: 10pt;
        font-weight: bold;
        margin-bottom: 1mm;
        background: #f0f0f0;
        padding: 1mm 2mm;
        border: 1px solid #000;
    }

    .print-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2mm;
        font-size: 8pt;
    }

    .print-table td,
    .print-table th {
        border: 0.1pt solid #808080;
        padding: 1mm 2mm;
        text-align: left;
        vertical-align: top;
        line-height: 1.1;
    }

    .print-table th {
        background: #f5f5f5;
        font-weight: bold;
        width: 20%;
    }

    .print-table .full-width {
        width: 80%;
    }

    .print-order-details {
        background: #fafafa;
        border: 0.1pt solid #808080;
        padding: 3mm;
        margin-bottom: 2mm;
        min-height: 15mm;
        font-size: 11pt;
        line-height: 0.7;
        font-weight: 600;
        columns: 2;
        column-gap: 5mm;
        column-rule: 0.3pt solid #ccc;
        break-inside: avoid-column;
    }

    .print-price-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2mm;
        font-size: 11pt;
    }

    .print-price-table td {
        border: 0.1pt solid #808080;
        padding: 2mm 3mm;
        text-align: right;
        line-height: 1.4;
        font-weight: 600;
        font-size: 15pt;
    }

    .print-price-table .label {
        background: #f5f5f5;
        font-weight: bold;
        text-align: center;
        width: 25%;
        font-size: 11pt;
    }

    .print-price-table .total {
        background: #ffe6e6;
        font-weight: bold;
        font-size: 15pt;
        color: #dc3545;
        border: 0.1pt solid #808080;
    }
    
    .print-price-table .total td:last-child {
        font-size: 15pt;
    }

    .print-footer {
        margin-top: 2mm;
        text-align: center;
        font-size: 7pt;
        color: #666;
    }

    /* 모든 form 요소 숨기기 */
    form, input, button, textarea {
        display: none !important;
    }

    /* 프린트 전용 내용만 표시 */
    .print-only {
        display: block !important;
    }

    .screen-only {
        display: none !important;
    }
}

/* 화면에서는 프린트 전용 내용 숨기기 */
.print-only {
    display: none;
}
</style>
</head>

<body>

<!-- 프린트 전용 내용 -->
<div class="print-only">
<div class="print-container">
    <!-- 첫 번째 주문서 (관리자용) -->
    <div class="print-order">
        <div class="print-title">주문서 (관리자용)</div>
        
        <!-- 주요 정보를 크게 표시 (노인 친화적) -->
        <div style="margin-bottom: 3mm; padding: 2mm; border: 0.3pt solid #666;">
            <div style="display: flex; gap: 3mm; align-items: center; font-size: 14pt; font-weight: bold; line-height: 1.2;">
                <div style="flex: 1;">
                    <span style="color: #000;">주문번호: <?=$View_No?></span>
                </div>
                <div style="flex: 1;">
                    <span style="color: #000;">일시: <?=htmlspecialchars($View_date)?></span>
                </div>
                <div style="flex: 1;">
                    <span style="color: #000;">주문자: <?=htmlspecialchars($View_name)?></span>
                </div>
                <div style="flex: 1;">
                    <span style="color: #000;">전화: <?=htmlspecialchars($View_phone)?></span>
                </div>
            </div>
        </div>
        
        <!-- 주문 상세 -->
        <div class="print-info-section">
            <div class="print-info-title">주문상세</div>
            <div class="print-order-details">
                <?php 
                if (!empty($View_Type_1) && trim($View_Type_1) != '') {
                    $json_data = json_decode($View_Type_1, true);
                    if ($json_data && isset($json_data['formatted_display'])) {
                        // JSON formatted_display를 2단에 맞게 포맷팅
                        $content = $json_data['formatted_display'];
                        // 각 줄에 여백 추가하여 가독성 향상
                        $content = str_replace("\n", "\n\n", $content);
                        echo nl2br(htmlspecialchars($content));
                    } else {
                        $content = trim($View_Type_1);
                        // 일반 텍스트도 줄 간격 조정
                        $content = str_replace("\n", "\n\n", $content);
                        echo nl2br(htmlspecialchars($content));
                    }
                } else {
                    echo "주문 상세 정보가 없습니다.";
                }
                ?>
            </div>
            <!-- 가격 정보를 주문상세 바로 아래에 한 줄로 표시 -->
            <div style="margin-top: 2mm; padding-top: 2mm; border-top: 0.1pt solid #808080; font-size: 11pt; font-weight: bold;">
                인쇄비 <?=number_format($View_money_4)?> / 디자인비 <?=number_format($View_money_2)?> / 합계 <?=number_format($View_money_5)?>
            </div>
        </div>

        <!-- 고객 정보 -->
        <div class="print-info-section">
            <div class="print-info-title">고객정보</div>
            <table class="print-table">
                <tr><th>성명</th><td><?=htmlspecialchars($View_name)?></td><th>전화</th><td><?=htmlspecialchars($View_phone)?></td></tr>
                <tr><th>주소</th><td colspan="3">[<?=$View_zip?>] <?=htmlspecialchars($View_zip1)?> <?=htmlspecialchars($View_zip2)?></td></tr>
                <?php if (!empty($View_bizname)) { ?>
                <tr><th>업체명</th><td><?=htmlspecialchars($View_bizname)?></td><th>입금</th><td><?=htmlspecialchars($View_bank)?></td></tr>
                <?php } ?>
            </table>
        </div>

        <!-- 기타 사항 및 사업자 정보 -->
        <?php if (!empty($View_cont) && trim($View_cont) != '') { ?>
        <div class="print-info-section">
            <div class="print-info-title">기타사항</div>
            <div style="padding: 2mm; border: 0.3pt solid #666; min-height: 10mm; font-size: 8pt; line-height: 1.2;">
                <?php echo nl2br(htmlspecialchars($View_cont)); ?>
            </div>
        </div>
        <?php } ?>

        <div class="print-footer">두손기획인쇄 02-2632-1830</div>
    </div>

    <!-- 절취선 -->
    <div class="print-divider"></div>

    <!-- 두 번째 주문서 (직원용) -->
    <div class="print-order">
        <div class="print-title">주문서 (직원용)</div>
        
        <!-- 주요 정보를 크게 표시 -->
        <div style="margin-bottom: 3mm; padding: 2mm; border: 0.3pt solid #666;">
            <div style="display: flex; gap: 3mm; align-items: center; font-size: 12pt; font-weight: bold; line-height: 1.2;">
                <div style="flex: 1;">
                    <span style="color: #000;">주문번호: <?=$View_No?></span>
                </div>
                <div style="flex: 1;">
                    <span style="color: #000;">일시: <?=htmlspecialchars($View_date)?></span>
                </div>
                <div style="flex: 1;">
                    <span style="color: #000;">주문자: <?=htmlspecialchars($View_name)?></span>
                </div>
                <div style="flex: 1;">
                    <span style="color: #000;">전화: <?=htmlspecialchars($View_phone)?></span>
                </div>
            </div>
        </div>
        
        <!-- 주문 상세 -->
        <div class="print-info-section">
            <div class="print-info-title">주문상세</div>
            <div class="print-order-details">
                <?php 
                if (!empty($View_Type_1) && trim($View_Type_1) != '') {
                    $json_data = json_decode($View_Type_1, true);
                    if ($json_data && isset($json_data['formatted_display'])) {
                        // JSON formatted_display를 2단에 맞게 포맷팅
                        $content = $json_data['formatted_display'];
                        // 각 줄에 여백 추가하여 가독성 향상
                        $content = str_replace("\n", "\n\n", $content);
                        echo nl2br(htmlspecialchars($content));
                    } else {
                        $content = trim($View_Type_1);
                        // 일반 텍스트도 줄 간격 조정
                        $content = str_replace("\n", "\n\n", $content);
                        echo nl2br(htmlspecialchars($content));
                    }
                } else {
                    echo "주문 상세 정보가 없습니다.";
                }
                ?>
            </div>
            <!-- 가격 정보를 주문상세 바로 아래에 한 줄로 표시 -->
            <div style="margin-top: 2mm; padding-top: 2mm; border-top: 0.1pt solid #808080; font-size: 11pt; font-weight: bold;">
                인쇄비 <?=number_format($View_money_4)?> / 디자인비 <?=number_format($View_money_2)?> / 합계 <?=number_format($View_money_5)?>
            </div>
        </div>

        <!-- 고객 정보 -->
        <div class="print-info-section">
            <div class="print-info-title">고객정보</div>
            <table class="print-table">
                <tr><th>성명</th><td><?=htmlspecialchars($View_name)?></td><th>전화</th><td><?=htmlspecialchars($View_phone)?></td></tr>
                <tr><th>주소</th><td colspan="3">[<?=$View_zip?>] <?=htmlspecialchars($View_zip1)?> <?=htmlspecialchars($View_zip2)?></td></tr>
                <?php if (!empty($View_bizname)) { ?>
                <tr><th>업체명</th><td><?=htmlspecialchars($View_bizname)?></td><th>입금</th><td><?=htmlspecialchars($View_bank)?></td></tr>
                <?php } ?>
            </table>
        </div>

        <!-- 기타 사항 및 사업자 정보 -->
        <?php if (!empty($View_cont) && trim($View_cont) != '') { ?>
        <div class="print-info-section">
            <div class="print-info-title">기타사항</div>
            <div style="padding: 2mm; border: 0.3pt solid #666; min-height: 10mm; font-size: 8pt; line-height: 1.2;">
                <?php echo nl2br(htmlspecialchars($View_cont)); ?>
            </div>
        </div>
        <?php } ?>

        <div class="print-footer">두손기획인쇄 02-2632-1830</div>
    </div>
</div>
</div>

<!-- 화면 표시용 내용 -->
<div class="screen-only">
<div class="admin-container">
    <div class="admin-header">
        <h1>📋 주문 상세 정보</h1>
        <div class="order-info">
            <span style="color: #ffffff; font-weight: 600; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">📅 주문일시: <?=$View_date?></span> | 
            <span style="color: #ffffff; font-weight: 600; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">🔢 주문번호: <?=$View_No?></span> | 
            <span style="color: #ffffff; font-weight: 600; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">👤 주문자: <?=$View_name?></span>
        </div>
    </div>
    
    <div class="admin-content">

        <form name='JoinInfo' method='post' enctype='multipart/form-data' onsubmit='return JoinCheckField()' action='/admin/MlangPrintAuto/admin.php'>
            <?php if ($no) { ?>
            <input type="hidden" name="no" value="<?=$no?>">
            <input type="hidden" name="mode" value="ModifyOk">
            <?php } else { ?>
            <input type="hidden" name="mode" value="SubmitOk">
            <?php } ?>

            <?php if ($no) { ?>
            <div class="info-grid">
                <div class="info-card">
                    <div style='font-size: 0.8rem; font-weight: 600; color: #2c3e50; margin-bottom: 15px; border-bottom: 1px solid #e0e0e0; padding-bottom: 8px;'>📦 주문 상세 정보</div>
                    <div>
                        <?php 
                        // Type_1 필드에서 주문 정보 파싱 및 표시
                        if (!empty($View_Type_1) && trim($View_Type_1) != '') {
                            // JSON 형태인지 확인
                            $json_data = json_decode($View_Type_1, true);
                            if ($json_data && isset($json_data['formatted_display'])) {
                                // 새로운 JSON 형태 데이터
                                $product_type = $json_data['product_type'] ?? '상품';
                                $product_icon = '';
                                switch($product_type) {
                                    case 'sticker': $product_icon = '🏷️'; break;
                                    case 'namecard': $product_icon = '📇'; break;
                                    case 'cadarok': $product_icon = '📚'; break;
                                    case 'leaflet': $product_icon = '📄'; break;
                                    default: $product_icon = '📦';
                                }
                                
                                echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745; margin-bottom: 15px;'>";
                                echo "<span style='font-size: 0.8rem; font-weight: 600; color: #155724;'>$product_icon " . htmlspecialchars($product_type) . " 주문 상세</span>";
                                echo "</div>";
                                
                                echo "<div style='background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0; font-family: \"Noto Sans KR\", sans-serif; font-size: 0.8rem; line-height: 1.6; color: #495057;'>";
                                echo nl2br(htmlspecialchars($json_data['formatted_display']));
                                echo "</div>";
                                
                                // 주문 시간 표시
                                if (isset($json_data['created_at'])) {
                                    echo "<div style='margin-top: 10px; color: #6c757d; font-size: 0.8rem;'>"; 
                                    echo "📅 주문 처리 시간: " . htmlspecialchars($json_data['created_at']);
                                    echo "</div>";
                                }
                            } elseif ($json_data && isset($json_data['order_details'])) {
                                // JSON 데이터가 있지만 formatted_display가 없는 경우
                                echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin-bottom: 15px;'>";
                                echo "<span style='font-size: 0.8rem; font-weight: 600; color: #856404;'>📦 주문 정보 (구조화된 데이터)</span>";
                                echo "</div>";
                                
                                echo "<div style='background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0; font-family: \"Noto Sans KR\", sans-serif; font-size: 0.8rem; line-height: 1.6; color: #495057;'>";
                                foreach ($json_data['order_details'] as $key => $value) {
                                    if (!empty($value)) {
                                        echo "<strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "<br>";
                                    }
                                }
                                echo "</div>";
                            } else {
                                // 기존 텍스트 형태 데이터
                                $content = trim($View_Type_1);
                                if ($content === '\n\n\n\n\n' || empty($content)) {
                                    echo "<div style='color: #dc3545; font-weight: bold;'>⚠️ 주문 상세 정보가 올바르게 저장되지 않았습니다.</div>";
                                    echo "<div style='color: #6c757d; font-size: 0.8rem; margin-top: 15px;'>";
                                    echo "주문번호: " . htmlspecialchars($View_No) . "<br>";
                                    echo "상품유형: " . htmlspecialchars($View_Type) . "<br>";
                                    echo "주문일시: " . htmlspecialchars($View_date) . "<br>";
                                    echo "</div>";
                                } else {
                                    // 주문 상세 정보와 추가옵션 정보를 분리하여 표시
                                    $mainContent = $content;
                                    $additionalOptionsFound = [];
                                    
                                    // 추가옵션 정보 추출
                                    $optionPatterns = [
                                        '코팅' => '/코팅[:：\s]*([^\n\r]+)/u',
                                        '접지' => '/(접지|접기|2절|3절|4절|Z절)[:：\s]*([^\n\r]*)/u',
                                        '오시' => '/오시[:：\s]*([^\n\r]+)/u',
                                        '후가공' => '/후가공[:：\s]*([^\n\r]+)/u'
                                    ];
                                    
                                    foreach ($optionPatterns as $optionName => $pattern) {
                                        if (preg_match($pattern, $content, $matches)) {
                                            $optionValue = trim($matches[1] ?? $matches[0]);
                                            if ($optionValue && $optionValue !== '없음' && $optionValue !== 'X' && $optionValue !== '') {
                                                $additionalOptionsFound[$optionName] = $optionValue;
                                            }
                                        }
                                    }
                                    
                                    echo "<div style='font-family: \"Noto Sans KR\", sans-serif; font-size: 0.8rem; line-height: 1.6; color: #495057;'>";
                                    echo nl2br(htmlspecialchars($content));
                                    echo "</div>";
                                    
                                    // 추가옵션이 발견되면 별도 박스로 강조 표시
                                    if (!empty($additionalOptionsFound)) {
                                        echo "<div style='margin-top: 15px; padding: 15px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #28a745;'>";
                                        echo "<div style='font-size: 0.8rem; font-weight: 600; color: #155724; margin-bottom: 10px;'>🔧 발견된 추가옵션:</div>";
                                        
                                        foreach ($additionalOptionsFound as $optionName => $optionValue) {
                                            $icon = '🔧';
                                            switch ($optionName) {
                                                case '코팅': $icon = '📄'; break;
                                                case '접지': $icon = '📁'; break;
                                                case '오시': $icon = '📐'; break;
                                                case '후가공': $icon = '⚙️'; break;
                                            }
                                            echo "<div style='font-size: 0.8rem; color: #155724; margin-bottom: 6px;'>";
                                            echo "• $icon $optionName: " . htmlspecialchars($optionValue);
                                            echo "</div>";
                                        }
                                        echo "</div>";
                                    }
                                }
                            }
                        } else {
                            echo "<div style='color: #dc3545; font-weight: bold;'>❌ 주문 상세 정보가 없습니다.</div>";
                            echo "<div style='color: #6c757d; font-size: 0.8rem; margin-top: 15px;'>";
                            echo "이 주문의 상세 정보가 저장되지 않았습니다.<br>";
                            echo "주문번호: " . htmlspecialchars($View_No) . "<br>";
                            echo "상품유형: " . htmlspecialchars($View_Type) . "<br>";
                            echo "</div>";
                        }
                        ?>
                        </div>
                    </td>
                    <td>
                        <div style='background: #f0f8ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff; margin-bottom: 15px;'>
                            <span style='font-size: 0.8rem; font-weight: 600; color: #2c3e50;'>💰 가격 정보</span>
                        </div>
                        
                        <div style='background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;'>
                            <table style='width: 100%; border-collapse: collapse; font-size: 0.8rem;'>
                                <tr style='border-bottom: 1px solid #eee;'>
                                    <td style='padding: 10px 0; font-weight: bold; color: #495057; font-size: 0.8rem;'>인쇄비</td>
                                    <td style='padding: 10px 0; text-align: right; color: #007bff; font-weight: 600; font-size: 0.8rem;'>
                                        <?=number_format($View_money_4)?> 원
                                    </td>
                                </tr>
                                <tr style='border-bottom: 1px solid #eee;'>
                                    <td style='padding: 10px 0; font-weight: bold; color: #495057; font-size: 0.8rem;'>디자인비</td>
                                    <td style='padding: 10px 0; text-align: right; color: #17a2b8; font-weight: 600; font-size: 0.8rem;'>
                                        <?=number_format($View_money_2)?> 원
                                    </td>
                                </tr>
                                
                                <?php 
                                // 추가옵션 정보 파싱 및 표시
                                $additionalOptionsTotal = 0;
                                $additionalOptionsHTML = '';
                                
                                if (!empty($View_Type_1)) {
                                    // JSON 데이터인지 확인
                                    $typeData = json_decode($View_Type_1, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($typeData)) {
                                        // JSON 형태의 추가옵션 처리
                                        
                                        // 1. 표준 additional_options 필드 처리
                                        if (isset($typeData['additional_options'])) {
                                            $options = $typeData['additional_options'];
                                            
                                            // 코팅 옵션
                                            if (isset($options['coating']) && $options['coating']['enabled']) {
                                                $price = intval($options['coating']['price'] ?? 0);
                                                $type = htmlspecialchars($options['coating']['type'] ?? '');
                                                $additionalOptionsTotal += $price;
                                                $additionalOptionsHTML .= "
                                                <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                    <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 코팅 ($type)</td>
                                                    <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                        +" . number_format($price) . " 원
                                                    </td>
                                                </tr>";
                                            }
                                            
                                            // 접지 옵션
                                            if (isset($options['folding']) && $options['folding']['enabled']) {
                                                $price = intval($options['folding']['price'] ?? 0);
                                                $type = htmlspecialchars($options['folding']['type'] ?? '');
                                                $additionalOptionsTotal += $price;
                                                $additionalOptionsHTML .= "
                                                <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                    <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 접지 ($type)</td>
                                                    <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                        +" . number_format($price) . " 원
                                                    </td>
                                                </tr>";
                                            }
                                            
                                            // 오시 옵션
                                            if (isset($options['creasing']) && $options['creasing']['enabled']) {
                                                $price = intval($options['creasing']['price'] ?? 0);
                                                $lines = htmlspecialchars($options['creasing']['lines'] ?? '');
                                                $additionalOptionsTotal += $price;
                                                $additionalOptionsHTML .= "
                                                <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                    <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 오시 ($lines)</td>
                                                    <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                        +" . number_format($price) . " 원
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                        
                                        // 2. JSON의 order_details에서 코팅 정보 감지 (jong 필드 등)
                                        if (isset($typeData['order_details'])) {
                                            $orderDetails = $typeData['order_details'];
                                            
                                            // jong 필드에서 코팅 키워드 검색 (명함 제외)
                                            if ($View_Type !== 'NameCard' && isset($orderDetails['jong'])) {
                                                $jong = $orderDetails['jong'];
                                                if (strpos($jong, '코팅') !== false) {
                                                    $additionalOptionsHTML .= "
                                                    <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                        <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 재질코팅 (" . htmlspecialchars($jong) . ")</td>
                                                        <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                            포함됨
                                                        </td>
                                                    </tr>";
                                                }
                                            }
                                        }
                                        
                                        // 3. JSON의 formatted_display에서도 검색 (명함 제외)
                                        if ($View_Type !== 'NameCard' && isset($typeData['formatted_display'])) {
                                            $formattedContent = $typeData['formatted_display'];
                                            if (strpos($formattedContent, '코팅') !== false && empty($additionalOptionsHTML)) {
                                                $additionalOptionsHTML .= "
                                                <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                    <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 코팅 (상세정보 참조)</td>
                                                    <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                        포함됨
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                    } else {
                                        // 일반 텍스트에서 추가옵션 정보 추출 (상세한 키워드 검색)
                                        if (!empty($View_Type_1)) {
                                            $content = $View_Type_1;
                                            
                                            // 코팅 옵션 검색 (명함 제외)
                                            if ($View_Type !== 'NameCard' && preg_match('/(.*?)코팅(:?[:：\s]*([^\n\r]+))?/u', $content, $matches)) {
                                                $coating_prefix = trim($matches[1]); // "칼라", "유광", "무광" 등
                                                $coating_suffix = isset($matches[3]) ? trim($matches[3]) : '';
                                                
                                                // 코팅 정보 구성
                                                $coating_parts = array_filter([$coating_prefix, '코팅', $coating_suffix]);
                                                $coating_info = implode(' ', $coating_parts);
                                                
                                                if ($coating_info && $coating_info !== '없음' && $coating_info !== 'X') {
                                                    $additionalOptionsHTML .= "
                                                    <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                        <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 코팅 (" . htmlspecialchars($coating_info) . ")</td>
                                                        <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                            포함됨
                                                        </td>
                                                    </tr>";
                                                }
                                            }
                                            
                                            // 접지 옵션 검색
                                            if (preg_match('/(접지|접기|2절|3절|4절|Z절)[:：\s]*([^\n\r]*)/u', $content, $matches)) {
                                                $folding_info = trim($matches[0]);
                                                if ($folding_info && $folding_info !== '없음' && $folding_info !== 'X') {
                                                    $additionalOptionsHTML .= "
                                                    <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                        <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 접지 (" . htmlspecialchars($folding_info) . ")</td>
                                                        <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                            포함됨
                                                        </td>
                                                    </tr>";
                                                }
                                            }
                                            
                                            // 오시 옵션 검색
                                            if (preg_match('/오시[:：\s]*([^\n\r]+)/u', $content, $matches)) {
                                                $creasing_info = trim($matches[1]);
                                                if ($creasing_info && $creasing_info !== '없음' && $creasing_info !== 'X') {
                                                    $additionalOptionsHTML .= "
                                                    <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                        <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 오시 (" . htmlspecialchars($creasing_info) . ")</td>
                                                        <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                            포함됨
                                                        </td>
                                                    </tr>";
                                                }
                                            }
                                            
                                            // 후가공 키워드 처리 (개선된 버전) (명함 제외)
                                            if ($View_Type !== 'NameCard' && (strpos($content, '후가공') !== false || 
                                                strpos($content, '가공') !== false || 
                                                strpos($content, '라미네이팅') !== false)) {
                                                
                                                // 후가공 정보 추출
                                                if (preg_match('/후가공[:：\s]*([^\n\r]*)/u', $content, $matches)) {
                                                    $processing_info = trim($matches[1]);
                                                    if (empty($processing_info)) {
                                                        $processing_info = '별도 문의';
                                                    }
                                                } else {
                                                    $processing_info = '포함됨';
                                                }
                                                
                                                $additionalOptionsHTML .= "
                                                <tr style='border-bottom: 1px solid #eee; background: #f8f9fa;'>
                                                    <td style='padding: 8px 0; color: #6c757d; font-size: 0.8rem; padding-left: 15px;'>└ 후가공 ($processing_info)</td>
                                                    <td style='padding: 8px 0; text-align: right; color: #28a745; font-weight: 500; font-size: 0.8rem;'>
                                                        포함됨
                                                    </td>
                                                </tr>";
                                            }
                                        }
                                    }
                                }
                                
                                // 추가옵션이 있으면 표시
                                echo $additionalOptionsHTML;
                                ?>
                                
                                <tr style='border-bottom: 2px solid #007bff;'>
                                    <td style='padding: 10px 0; font-weight: bold; color: #495057; font-size: 0.8rem;'>소계</td>
                                    <td style='padding: 10px 0; text-align: right; color: #495057; font-weight: 600; font-size: 0.8rem;'>
                                        <?=number_format($View_money_4 + $View_money_2 + $additionalOptionsTotal)?> 원
                                    </td>
                                </tr>
                                <tr style='border-bottom: 1px solid #eee;'>
                                    <td style='padding: 10px 0; font-weight: bold; color: #495057; font-size: 0.8rem;'>부가세 (10%)</td>
                                    <td style='padding: 10px 0; text-align: right; color: #ffc107; font-weight: 600; font-size: 0.8rem;'>
                                        <?=number_format($View_money_3)?> 원
                                    </td>
                                </tr>
                                <tr style='background: #ffe6e6; border: 2px solid #dc3545;'>
                                    <td style='padding: 12px 0; font-weight: bold; font-size: 0.8rem; color: #dc3545;'>총 합계</td>
                                    <td style='padding: 12px 0; text-align: right; color: #dc3545; font-weight: bold; font-size: 0.8rem;'>
                                        <?=number_format($View_money_5)?> 원
                                    </td>
                                </tr>
                            </table>
                        </div>
                        
                        <!-- 추가옵션 상세 정보 섹션 -->
                        <?php 
                        // 추가옵션이 있는지 더 광범위하게 체크
                        $showAdditionalOptionsSection = !empty($additionalOptionsHTML);
                        if (!$showAdditionalOptionsSection && !empty($View_Type_1)) {
                            // 텍스트에서 추가옵션 키워드가 있는지 확인
                            $content = $View_Type_1;
                            $showAdditionalOptionsSection = (
                                ($View_Type !== 'NameCard' && strpos($content, '코팅') !== false) ||
                                strpos($content, '접지') !== false ||
                                strpos($content, '접기') !== false ||
                                strpos($content, '오시') !== false ||
                                ($View_Type !== 'NameCard' && strpos($content, '후가공') !== false) ||
                                strpos($content, '2절') !== false ||
                                strpos($content, '3절') !== false ||
                                strpos($content, '4절') !== false ||
                                strpos($content, 'Z절') !== false ||
                                strpos($content, '라미네이팅') !== false
                            );
                        }
                        
                        if ($showAdditionalOptionsSection): ?>
                        <div style='margin-top: 15px; background: #e8f5e8; padding: 15px; border-radius: 8px; border: 1px solid #c3e6c3; border-left: 4px solid #28a745;'>
                            <div style='margin-bottom: 12px;'>
                                <span style='font-size: 0.8rem; font-weight: 600; color: #155724;'>🔧 추가옵션 (후가공)</span>
                            </div>
                            <div style='background: white; padding: 12px; border-radius: 6px; font-size: 0.8rem;'>
                                <?php
                                // 선택된 추가옵션들을 텍스트로 표시
                                $optionsList = [];
                                if (!empty($View_Type_1)) {
                                    $typeData = json_decode($View_Type_1, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($typeData) && isset($typeData['additional_options'])) {
                                        $options = $typeData['additional_options'];
                                        
                                        if (isset($options['coating']['enabled']) && $options['coating']['enabled']) {
                                            $optionsList[] = "📄 코팅: " . htmlspecialchars($options['coating']['type'] ?? '') . " (+".number_format($options['coating']['price'] ?? 0)."원)";
                                        }
                                        
                                        if (isset($options['folding']['enabled']) && $options['folding']['enabled']) {
                                            $optionsList[] = "📁 접지: " . htmlspecialchars($options['folding']['type'] ?? '') . " (+".number_format($options['folding']['price'] ?? 0)."원)";
                                        }
                                        
                                        if (isset($options['creasing']['enabled']) && $options['creasing']['enabled']) {
                                            $optionsList[] = "📐 오시: " . htmlspecialchars($options['creasing']['lines'] ?? '') . " (+".number_format($options['creasing']['price'] ?? 0)."원)";
                                        }
                                    }
                                }
                                
                                if (!empty($optionsList)) {
                                    foreach ($optionsList as $option) {
                                        echo "<div style='margin-bottom: 4px; color: #155724; font-weight: 500;'>• $option</div>";
                                    }
                                    echo "<div style='margin-top: 8px; padding-top: 8px; border-top: 1px solid #c3e6c3; font-weight: 600; color: #155724;'>";
                                    echo "💰 추가옵션 총액: <span style='color: #28a745;'>" . number_format($additionalOptionsTotal) . "원</span>";
                                    echo "</div>";
                                } else {
                                    // JSON에서 추가옵션을 찾을 수 없는 경우, 데이터베이스에서 직접 확인
                                    $hasAdditionalOptions = false;
                                    $optionsInfo = [];
                                    
                                    // Type_1 필드에서 코팅, 접지, 오시 키워드 검색
                                    if (!empty($View_Type_1)) {
                                        $content = $View_Type_1;
                                        
                                        // 코팅 관련 키워드 검색 (명함 제외)
                                        if ($View_Type !== 'NameCard' && (preg_match('/코팅[:：]\s*([^\n\r]+)/u', $content, $matches) || 
                                            preg_match('/코팅\s*([^\n\r]+)/u', $content, $matches))) {
                                            $coating_info = trim($matches[1]);
                                            if ($coating_info && $coating_info !== '없음' && $coating_info !== 'X') {
                                                $optionsInfo[] = "📄 코팅: " . htmlspecialchars($coating_info);
                                                $hasAdditionalOptions = true;
                                            }
                                        }
                                        
                                        // 접지 관련 키워드 검색
                                        if (preg_match('/접지[:：]\s*([^\n\r]+)/u', $content, $matches) || 
                                            preg_match('/접기[:：]\s*([^\n\r]+)/u', $content, $matches) ||
                                            preg_match('/(2절|3절|4절|Z절|접지)/u', $content, $matches)) {
                                            $folding_info = trim($matches[1] ?? $matches[0]);
                                            if ($folding_info && $folding_info !== '없음' && $folding_info !== 'X') {
                                                $optionsInfo[] = "📁 접지: " . htmlspecialchars($folding_info);
                                                $hasAdditionalOptions = true;
                                            }
                                        }
                                        
                                        // 오시 관련 키워드 검색
                                        if (preg_match('/오시[:：]\s*([^\n\r]+)/u', $content, $matches) || 
                                            preg_match('/(?:오시|골[선선])\s*([^\n\r]+)/u', $content, $matches)) {
                                            $creasing_info = trim($matches[1]);
                                            if ($creasing_info && $creasing_info !== '없음' && $creasing_info !== 'X') {
                                                $optionsInfo[] = "📐 오시: " . htmlspecialchars($creasing_info);
                                                $hasAdditionalOptions = true;
                                            }
                                        }
                                        
                                        // 후가공 일반 키워드 검색 (명함 제외)
                                        if ($View_Type !== 'NameCard' && !$hasAdditionalOptions && 
                                            (strpos($content, '후가공') !== false || 
                                             strpos($content, '가공') !== false ||
                                             strpos($content, '코팅') !== false ||
                                             strpos($content, '라미네이팅') !== false)) {
                                            $optionsInfo[] = "🔧 후가공: 포함됨";
                                            $hasAdditionalOptions = true;
                                        }
                                    }
                                    
                                    if ($hasAdditionalOptions) {
                                        foreach ($optionsInfo as $option) {
                                            echo "<div style='margin-bottom: 6px; color: #155724; font-weight: 500;'>• $option</div>";
                                        }
                                    } else {
                                        echo "<div style='color: #6c757d; font-style: italic;'>선택된 추가옵션이 없습니다.</div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div style='margin-top: 15px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;'>
                            <div style='margin-bottom: 12px; font-size: 0.8rem;'>
                                <span style='font-weight: 600; color: #495057;'>📦 상품 유형:</span> 
                                <span style='background: #e3f2fd; padding: 6px 12px; border-radius: 4px; color: #1976d2; font-weight: 600; margin-left: 8px;'>
                                    <?=htmlspecialchars($View_Type)?>
                                </span>
                            </div>
                            <div style='font-size: 0.8rem;'>
                                <span style='font-weight: 600; color: #495057;'>📋 주문 상태:</span> 
                                <span style='background: <?php 
                                    switch($View_OrderStyle) {
                                        case '1': echo '#fff3cd; color: #856404;'; break; // 주문접수
                                        case '2': echo '#d4edda; color: #155724;'; break; // 신규주문
                                        case '3': echo '#cce5ff; color: #004085;'; break; // 확인완료
                                        case '6': echo '#f8d7da; color: #721c24;'; break; // 시안
                                        case '7': echo '#e2e3e5; color: #383d41;'; break; // 교정
                                        default: echo '#f8f9fa; color: #6c757d;'; // 상태미정
                                    }
                                ?> padding: 6px 12px; border-radius: 4px; font-weight: 600; margin-left: 8px;'>
                                    <?php 
                                    switch($View_OrderStyle) {
                                        case '1': echo '📥 주문접수'; break;
                                        case '2': echo '🆕 신규주문'; break;
                                        case '3': echo '✅ 확인완료'; break;
                                        case '6': echo '🎨 시안'; break;
                                        case '7': echo '📝 교정'; break;
                                        default: echo '❓ 상태미정';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </td>
                    <?php } else { ?>
                    <td>
                        <textarea name="TypeOne" cols="80" rows="5"><?=$View_Type_1?></textarea>
                    </td>
                    <?php } ?>
                </tr>
            </table>
        </td>
    </tr>

        <!-- 컴팩트한 주문 개수 섹션 -->
        <div class="form-section" style="margin-top: 8px; padding: 10px 15px;">
            <div class="form-row" style="margin-bottom: 0;">
                <div class="form-label" style="width: 80px; font-size: 0.8rem; padding: 4px 8px;">주문개수</div>
                <div>
                    <input name="Gensu" type="text" class="form-input" style="width: 80px; display: inline-block; padding: 4px 8px; font-size: 0.85rem;" value='<?=$View_Gensu?>'>
                    <span style="color: #6c757d; font-size: 0.8rem; margin-left: 8px;">* 주문 제품 개수를 입력해 주세요</span>
                </div>
            </div>
        </div>

        <!-- 컴팩트한 신청자 정보 섹션 -->
        <div class="form-section" style="margin-top: 8px; padding: 10px 15px;">
            <h3 style="margin-bottom: 8px; font-size: 0.9rem; color: #2c3e50;">📝 신청자 정보 <span style="color: #dc3545; font-size: 0.75rem; font-weight: normal;">(정확히 입력해 주세요)</span></h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 15px; margin-bottom: 6px;">
                <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                    <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">성명/상호</div>
                    <input name="name" type="text" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_name?>'>
                </div>
                <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                    <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">E-MAIL</div>
                    <input name="email" type="text" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_email?>'>
                </div>
            </div>

            <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 6px;">
                <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">우편번호</div>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <input type="text" name="zip" class="form-input" style="width: 70px; padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_zip?>'>
                    <button type="button" class="btn btn-secondary" style="padding: 4px 8px; font-size: 0.7rem;">검색</button>
                </div>
            </div>

            <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 6px;">
                <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">주소</div>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <input type="text" name="zip1" class="form-input" placeholder="기본주소" style="flex: 2; padding: 4px 8px; min-width: 120px; font-size: 0.8rem;" value='<?=$View_zip1?>'>
                    <input type="text" name="zip2" class="form-input" placeholder="상세주소" style="flex: 1; padding: 4px 8px; min-width: 80px; font-size: 0.8rem;" value='<?=$View_zip2?>'>
                </div>
            </div>

            <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 6px;">
                <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">배송지</div>
                <input type="text" name="delivery" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_delivery?>'>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 15px; margin-bottom: 6px;">
                <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                    <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">전화번호</div>
                    <input name="phone" type="text" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_phone?>'>
                </div>
                <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                    <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">휴대폰</div>
                    <input name="Hendphone" type="text" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_Hendphone?>'>
                </div>
            </div>

            <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 6px;">
                <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">사업자명</div>
                <input type="text" name="bizname" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_bizname?>'>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px 15px; margin-bottom: 6px;">
                <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                    <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">입금은행</div>
                    <input name="bank" type="text" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_bank?>'>
                </div>
                <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                    <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">입금자명</div>
                    <input name="bankname" type="text" class="form-input" style="padding: 4px 8px; font-size: 0.8rem;" value='<?=$View_bankname?>'>
                </div>
            </div>

            <div class="form-row" style="grid-template-columns: 60px 1fr; gap: 8px; margin-bottom: 0;">
                <div class="form-label" style="font-size: 0.75rem; padding: 4px 6px;">비고사항</div>
                <textarea name="cont" class="form-input" rows="2" style="padding: 4px 8px; resize: vertical; font-size: 0.8rem;"><?=$View_cont?></textarea>
            </div>
        </div>

        <!-- 관리자 버튼 -->
        <div class="btn-group" style="margin-top: 15px;">
            <?php if ($no) { ?>
                <button type="submit" class="btn btn-primary" style="padding: 8px 20px; font-size: 0.9rem; margin-right: 10px;">💾 정보 수정</button>
                <button type="button" onclick="printOrder();" class="btn btn-success" style="padding: 8px 20px; font-size: 0.9rem; margin-right: 10px; background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); color: white;">🖨️ 주문서 출력</button>
            <?php } ?>
            <button type="button" onclick="window.close();" class="btn btn-secondary" style="padding: 8px 16px; font-size: 0.9rem;">✖️ 창 닫기</button>
        </div>

</form>
</table>
</div>
</div> <!-- screen-only 종료 -->

</body>
</html>
