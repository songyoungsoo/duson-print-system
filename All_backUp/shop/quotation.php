<?php 
session_start(); 
$session_id = session_id();

$HomeDir="../../";
include "../mlangprintauto/mlangprintautotop.php";
include "../lib/func.php"; 
include "../includes/AdditionalOptionsDisplay.php";
$connect = dbconn(); 

if ($connect) {
    mysqli_set_charset($connect, 'utf8');
}

// 장바구니와 정확히 동일한 로직 사용
$query = "SELECT * FROM shop_temp WHERE session_id='$session_id'"; 
$result = mysqli_query($connect, $query);
$total = 0;
$total_vat = 0;
$optionsDisplay = getAdditionalOptionsDisplay($connect);

// 아이템 배열에 저장 (basket.php와 100% 동일한 로직)
$items = [];
$debug_info = []; // 디버그 정보 저장

while ($data = mysqli_fetch_array($result)) { 
    $j = substr($data['jong'], 4, 12);
    $j1 = substr($data['jong'], 0, 3);
    $d = substr($data['domusong'], 6, 4);
    $d1 = substr($data['domusong'], 0, 5);
    
    // 디버그: 현재 아이템의 옵션 필드 확인
    $debug_item = [
        'no' => $data['no'],
        'product_type' => isset($data['product_type']) ? $data['product_type'] : 'NULL',
        'coating_enabled' => isset($data['coating_enabled']) ? $data['coating_enabled'] : 'NULL',
        'coating_price' => isset($data['coating_price']) ? $data['coating_price'] : 'NULL',
        'folding_enabled' => isset($data['folding_enabled']) ? $data['folding_enabled'] : 'NULL',
        'folding_price' => isset($data['folding_price']) ? $data['folding_price'] : 'NULL',
        'creasing_enabled' => isset($data['creasing_enabled']) ? $data['creasing_enabled'] : 'NULL',
        'creasing_price' => isset($data['creasing_price']) ? $data['creasing_price'] : 'NULL'
    ];
    $debug_info[] = $debug_item;
    
    // 추가 옵션 가격 계산 - basket.php와 동일
    $base_price = intval($data['st_price']);
    
    // 추가 옵션이 있는지 확인 (coating_price, folding_price, creasing_price 필드가 있으면)
    $has_additional_options = isset($data['coating_price']) || isset($data['folding_price']) || isset($data['creasing_price']);
    
    if ($has_additional_options) {
        // AdditionalOptionsDisplay 클래스를 사용하여 계산
        $price_with_options = $optionsDisplay->calculateTotalWithOptions($base_price, $data);
        $final_price = $price_with_options['total_price'];
        $final_price_vat = $price_with_options['total_vat'];
    } else {
        // 추가 옵션이 없으면 기본 가격 사용
        $final_price = $base_price;
        $final_price_vat = intval($data['st_price_vat']);
    }
    
    $items[] = [
        'data' => $data,
        'j' => $j,
        'd' => $d,
        'price' => $final_price,
        'price_vat' => $final_price_vat,
        'has_options' => $has_additional_options,
        'debug' => $debug_item
    ];
    
    $total += $final_price;
    $total_vat += $final_price_vat;
}

// 오늘 날짜
$today = date('Y년 m월 d일');
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 - 두손기획인쇄</title>
    <style>
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
        
        body {
            font-family: 'Malgun Gothic', '맑은 고딕', sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 36px;
            margin: 0;
            color: #2c3e50;
        }
        
        .date-section {
            text-align: right;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        th {
            background: #34495e;
            color: white;
            padding: 12px;
            text-align: center;
            font-weight: normal;
        }
        
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }
        
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .total-section {
            background: #ecf0f1;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .total-row.final {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            border-top: 2px solid #34495e;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        .footer {
            border-top: 2px solid #34495e;
            padding-top: 20px;
            margin-top: 40px;
            color: #666;
            font-size: 14px;
        }
        
        .print-button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin: 20px 0;
        }
        
        .print-button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button onclick="window.print()" class="print-button">🖨️ 견적서 인쇄하기</button>
        <button onclick="window.close()" class="print-button" style="background: #95a5a6;">닫기</button>
    </div>
    
    <!-- 디버그 정보 표시 (인쇄 시 숨김) -->
    <div class="no-print" style="background: #f0f8ff; border: 2px solid #4169e1; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <h3 style="color: #4169e1; margin-top: 0;">🔍 디버그 정보 (옵션 데이터 확인)</h3>
        <div style="font-size: 12px;">
            <strong>세션 ID:</strong> <?php echo $session_id; ?><br>
            <strong>총 아이템 수:</strong> <?php echo count($items); ?><br>
            <strong>데이터베이스 쿼리:</strong> SELECT * FROM shop_temp WHERE session_id='<?php echo $session_id; ?>'<br><br>
            
            <?php foreach ($debug_info as $i => $debug): ?>
            <div style="background: white; padding: 10px; margin: 5px 0; border-left: 3px solid #4169e1;">
                <strong>아이템 #<?php echo $debug['no']; ?></strong><br>
                - product_type: <?php echo $debug['product_type']; ?><br>
                - coating_enabled: <?php echo $debug['coating_enabled']; ?> | coating_price: <?php echo $debug['coating_price']; ?><br>
                - folding_enabled: <?php echo $debug['folding_enabled']; ?> | folding_price: <?php echo $debug['folding_price']; ?><br>
                - creasing_enabled: <?php echo $debug['creasing_enabled']; ?> | creasing_price: <?php echo $debug['creasing_price']; ?><br>
                - has_options: <?php echo $items[$i]['has_options'] ? 'TRUE' : 'FALSE'; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="header">
        <h1>견 적 서</h1>
        <div style="margin-top: 10px; font-size: 14px; color: #666;">
            두손기획인쇄 | 사업자등록번호: 201-10-69847<br>
            TEL: 02-2632-1830 | FAX: 02-2632-1831
        </div>
    </div>
    
    <div class="date-section">
        <strong>견적일자:</strong> <?php echo $today; ?>
    </div>
    
    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 30px;">
        <h3 style="margin-top: 0; color: #2c3e50;">고객님께</h3>
        <p>아래와 같이 견적을 제출합니다.</p>
    </div>
    
    <!-- 장바구니와 동일한 테이블 구조 -->
    <table>
        <thead>
            <tr>
                <th width="5%">NO</th>
                <th width="10%">재질</th>
                <th width="8%">가로(mm)</th>
                <th width="8%">세로(mm)</th>
                <th width="8%">매수(매)</th>
                <th width="12%">도무송<br>(타입)</th>
                <th width="20%">추가옵션</th>
                <th width="10%">도안비</th>
                <th width="10%">금액</th>
                <th width="9%">부가세포함</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): 
                $data = $item['data'];
            ?>
            <tr>
                <td><?php echo $data['no'] ?></td>
                <td><?php echo $item['j'] ?></td>
                <td><?php echo $data['garo'] ?></td>
                <td><?php echo $data['sero'] ?></td>
                <td><?php echo $data['mesu'] ?></td>
                <td><?php echo $item['d'] ?></td>
                <td class="text-left" style="font-size: 11px; padding: 4px;">
                    <?php 
                    // 장바구니와 정확히 동일한 옵션 표시 로직
                    if ($item['has_options']) {
                        echo $optionsDisplay->getCartColumnHtml($data);
                    } else {
                        echo '<span style="color: #6c757d;">옵션 없음</span>';
                    }
                    ?>
                </td>
                <td><?php echo $data['uhyung'] ?></td>
                <td class="text-right"><strong><?php echo number_format($item['price']); ?>원</strong></td>
                <td class="text-right"><strong><?php echo number_format($item['price_vat']); ?>원</strong></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="total-section">
        <div class="total-row">
            <span>공급가액 (VAT 제외):</span>
            <span><?php echo number_format($total); ?>원</span>
        </div>
        <div class="total-row">
            <span>부가세(10%):</span>
            <span><?php echo number_format($total_vat - $total); ?>원</span>
        </div>
        <div class="total-row final">
            <span>총 합계금액 (VAT 포함):</span>
            <span><?php echo number_format($total_vat); ?>원</span>
        </div>
    </div>
    
    <div class="footer">
        <div>
            <strong>두손기획인쇄</strong><br>
            서울특별시 영등포구 영등포로 36길 9 송호빌딩 1층<br>
            전화: 02-2632-1830 | 팩스: 02-2632-1831<br>
            이메일: dsp1830@naver.com
        </div>
        
        <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 15px;">
            <strong>입금계좌 안내</strong><br>
            국민은행: 123-456-789012 (예금주: 두손기획인쇄)<br>
            신한은행: 987-654-321098 (예금주: 두손기획인쇄)
        </div>
        
        <p style="margin-top: 20px; font-size: 12px; color: #999;">
            ※ 본 견적서의 유효기간은 발행일로부터 30일입니다.<br>
            ※ 상기 금액은 부가세가 포함된 금액입니다.<br>
            ※ 디자인 수정 및 추가 작업 시 별도 비용이 발생할 수 있습니다.
        </p>
    </div>
</body>
</html>

<?php
mysqli_close($connect);
?>