<?php
/**
 * 롤스티커 견적 계산기
 * 경로: /shop/roll_sticker_calculator.php
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// POST 데이터 받기
$company_name = $_POST['company_name'] ?? ''; // 받을 회사 이름
$width = floatval($_POST['width'] ?? 0); // 가로 (mm)
$height = floatval($_POST['height'] ?? 0); // 세로 (mm)
$quantity = intval($_POST['quantity'] ?? 0); // 매수
$knife_count = intval($_POST['knife_count'] ?? 1); // 칼(톰슨칼) 개수
$material = $_POST['material'] ?? 'art'; // 재질
$colors = intval($_POST['colors'] ?? 1); // 도수 (1~5)
$need_design = isset($_POST['need_design']) ? true : false; // 도안 필요 여부
$need_white_printing = isset($_POST['need_white_printing']) ? true : false; // 백색인쇄 필요 여부
$delivery_prepaid = intval($_POST['delivery_prepaid'] ?? 0); // 택배선불
$coating = $_POST['coating'] ?? 'none'; // 코팅 (none/glossy/matte/uv)
$foil = $_POST['foil'] ?? 'none'; // 박 (none/glossy_gold/matte_gold/glossy_silver/matte_silver)
$need_plate = isset($_POST['need_plate']) ? true : false; // 동판 필요 여부
$embossing = $_POST['embossing'] ?? 'none'; // 형압 (none/raised/recessed)
$partial_coating = isset($_POST['partial_coating']) ? true : false; // 부분코팅 필요 여부

$result = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $width > 0 && $height > 0 && $quantity > 0) {
    
    // 1. 재질선택비
    $material_prices = [
        'art' => 1.5,           // 아트지
        'yupo' => 2.5,          // 유포지
        'silver_deadlong' => 2.5, // 은데드롱
        'clear_deadlong' => 2.5,  // 투명데드롱
        'gold_paper' => 3.5,    // 금지
        'silver_paper' => 3.5,  // 은지
        'kraft' => 2.3,         // 크라프트
        'hologram' => 5.0       // 홀로그램
    ];
    
    $material_names = [
        'art' => '아트지',
        'yupo' => '유포지',
        'silver_deadlong' => '은데드롱',
        'clear_deadlong' => '투명데드롱',
        'gold_paper' => '금지',
        'silver_paper' => '은지',
        'kraft' => '크라프트',
        'hologram' => '홀로그램'
    ];
    
    $material_price = $material_prices[$material] ?? 1.5;
    $material_name = $material_names[$material] ?? '아트지';
    $result['material_price'] = $material_price;
    $result['material_name'] = $material_name;
    $result['material'] = $material;
    
    // 2. 면적비 (가로 x 세로 x 매수)
    $total_area = $width * $height * $quantity; // 제곱mm
    $area_cost = ($material_price * $total_area) / 1000;
    
    // 재질별 최소비용 적용 (재질단가 × 10,000)
    $material_minimum = $material_price * 10000;
    $area_cost = max($material_minimum, $area_cost);
    
    $result['area_cost'] = $area_cost;
    $result['total_area'] = $total_area;
    $result['material_minimum'] = $material_minimum;
    
    // 3. 도안비 (선택사항, 도당 5,000원, 최소 10,000원)
    $design_cost = 0;
    if ($need_design) {
        $design_cost = max(10000, $colors * 5000);
    }
    $result['design_cost'] = $design_cost;
    $result['need_design'] = $need_design;
    
    // 4. 필름비 (가로 x 세로, 제곱mm당 1원, 최소 4,000원) x 도수
    $film_area = $width * $height;
    $film_cost_per_color = max(4000, $film_area * 1); // 제곱mm당 1원
    $film_cost = $film_cost_per_color * $colors; // 도수만큼 곱하기
    $result['film_cost'] = $film_cost;
    $result['film_cost_per_color'] = $film_cost_per_color;
    
    // 5. 수지판비 (가로 x 세로, 제곱mm당 3원, 최소 5,000원) x 도수
    $resin_area = $width * $height;
    $resin_cost_per_color = max(5000, $resin_area * 3); // 제곱mm당 3원
    $resin_cost = $resin_cost_per_color * $colors; // 도수만큼 곱하기
    $result['resin_cost'] = $resin_cost;
    $result['resin_cost_per_color'] = $resin_cost_per_color;
    
    // 6. 도무송비 (가로 x 세로, 제곱mm당 2원, 최소 10,000원) x 칼개수
    $embossing_area = $width * $height;
    $embossing_cost_per_knife = max(10000, $embossing_area * 2); // 제곱mm당 2원
    $embossing_cost = $embossing_cost_per_knife * $knife_count; // 칼 개수만큼 곱하기
    $result['embossing_cost'] = $embossing_cost;
    $result['embossing_cost_per_knife'] = $embossing_cost_per_knife;
    $result['knife_count'] = $knife_count;
    
    // 7. 인쇄비 (매수 x 도수 x 10원, 최소 10,000원)
    $printing_cost = max(10000, $quantity * $colors * 10);
    $result['printing_cost'] = $printing_cost;
    
    // 7-1. 백색인쇄비 (선택사항, 매수 x 20원, 최소 20,000원)
    $white_printing_cost = 0;
    $white_film_cost = 0;
    $white_resin_cost = 0;
    
    if ($need_white_printing) {
        $white_printing_cost = max(20000, $quantity * 20);
        
        // 7-2. 백색인쇄 필름비 (가로 x 세로, 제곱mm당 1원, 최소 4,000원)
        $white_film_area = $width * $height;
        $white_film_cost = max(4000, $white_film_area * 1);
        
        // 7-3. 백색인쇄 수지판비 (가로 x 세로, 제곱mm당 3원, 최소 5,000원)
        $white_resin_area = $width * $height;
        $white_resin_cost = max(5000, $white_resin_area * 3);
    }
    
    $result['white_printing_cost'] = $white_printing_cost;
    $result['white_film_cost'] = $white_film_cost;
    $result['white_resin_cost'] = $white_resin_cost;
    $result['need_white_printing'] = $need_white_printing;
    
    // 8. 코팅비 (선택사항, 1000매당 가격)
    $coating_cost = 0;
    if ($coating != 'none') {
        $coating_prices = [
            'glossy' => 50000, // 유광
            'matte' => 40000,  // 무광
            'uv' => 10000      // UV
        ];
        $coating_unit_price = $coating_prices[$coating] ?? 0;
        $coating_cost = ($quantity / 1000) * $coating_unit_price;
    }
    $result['coating_cost'] = $coating_cost;
    
    // 9. 박 (선택사항, 제곱mm당 0.016원, 최소 35,000원)
    $foil_cost = 0;
    if ($foil != 'none') {
        $foil_area = $width * $height * $quantity;
        $foil_cost = max(35000, $foil_area * 0.016);
    }
    $result['foil_cost'] = $foil_cost;
    
    // 10. 동판비 (박 선택시 자동 적용, 가로 x 세로, 제곱mm당 5원, 최소 5,000원)
    $plate_cost = 0;
    // 박을 선택했거나 동판 체크박스를 선택한 경우
    if ($foil != 'none' || $need_plate) {
        $plate_area = $width * $height;
        $plate_cost = max(5000, $plate_area * 5); // 제곱mm당 5원
    }
    $result['plate_cost'] = $plate_cost;
    $result['auto_plate'] = ($foil != 'none'); // 박 선택으로 자동 적용 여부
    
    // 11. 형압비 (선택사항, 1000매당 30,000원)
    $embossing_press_cost = 0;
    if ($embossing != 'none') {
        $embossing_press_cost = ($quantity / 1000) * 30000;
    }
    $result['embossing_press_cost'] = $embossing_press_cost;
    $result['embossing'] = $embossing;
    
    // 12. 형압용수지판비 (형압 선택시 자동 적용, 가로 x 세로, 제곱mm당 3원, 최소 5,000원) x 2 (암수)
    $embossing_plate_cost = 0;
    if ($embossing != 'none') {
        $embossing_plate_area = $width * $height;
        $embossing_plate_cost_per_plate = max(5000, $embossing_plate_area * 3); // 제곱mm당 3원
        $embossing_plate_cost = $embossing_plate_cost_per_plate * 2; // 암수 2개
    }
    $result['embossing_plate_cost'] = $embossing_plate_cost;
    $result['embossing_plate_cost_per_plate'] = $embossing_plate_cost_per_plate ?? 0;
    
    // 13. 부분코팅비 (선택사항, 1000매당 30,000원)
    $partial_coating_cost = 0;
    if ($partial_coating) {
        $partial_coating_cost = ($quantity / 1000) * 30000;
    }
    $result['partial_coating_cost'] = $partial_coating_cost;
    
    // 택배선불
    $result['delivery_prepaid'] = $delivery_prepaid;
    
    // 공급가 계산: (1*2)/1000 + 3 + 4 + 5 + 6 + 7 + 7-1 + 7-2 + 7-3 + 8 + 9 + 10 + 11 + 12 + 13 + 택배선불
    $supply_price = $area_cost + $design_cost + $film_cost + $resin_cost + 
                    $embossing_cost + $printing_cost + $white_printing_cost + 
                    $white_film_cost + $white_resin_cost + $coating_cost + 
                    $foil_cost + $plate_cost + $embossing_press_cost + 
                    $embossing_plate_cost + $partial_coating_cost + $delivery_prepaid;
    
    $result['supply_price'] = round($supply_price);
    $result['vat'] = round($supply_price * 0.1);
    $result['total_price'] = round($supply_price * 1.1);
    
    // 상세 정보
    $result['company_name'] = $company_name;
    $result['width'] = $width;
    $result['height'] = $height;
    $result['quantity'] = $quantity;
    $result['colors'] = $colors;
    $result['total_area'] = $total_area;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Excel Style CSS */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', 'Segoe UI', sans-serif; background: #f0f0f0; padding: 5px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border: 1px solid #d0d0d0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #4472C4; color: white; padding: 6px 15px; text-align: center; border-bottom: 2px solid #2E5090; }
        .header h1 { font-size: 14px; margin: 0; font-weight: 600; }
        .dashboard-menu { background: #F2F2F2; padding: 5px 10px; border-bottom: 1px solid #d0d0d0; display: flex; gap: 5px; flex-wrap: wrap; }
        .dashboard-menu a { padding: 4px 10px; background: white; color: #4472C4; text-decoration: none; border: 1px solid #d0d0d0; font-size: 11px; font-weight: 600; }
        .dashboard-menu a:hover { background: #E7F3FF; }
        .dashboard-menu .menu-active { background: #4472C4; color: white; border-color: #4472C4; }
        .content { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; padding: 5px; }
        .form-section, .result-section { background: white; padding: 5px; border: 1px solid #d0d0d0; }
        .section-title { font-size: 12px; font-weight: 600; margin-bottom: 5px; color: white; background: #5B9BD5; padding: 3px 6px; }
        .form-group { margin-bottom: 3px; }
        .form-group-inline { display: grid; grid-template-columns: 100px 1fr; gap: 5px; align-items: center; margin-bottom: 3px; border-bottom: 1px solid #e0e0e0; padding: 2px 0; }
        .form-label { font-weight: 600; color: #444; font-size: 11px; background: #F2F2F2; padding: 2px 4px; margin-bottom: 2px; }
        .form-label-inline { font-weight: 600; color: #444; font-size: 11px; background: #F2F2F2; padding: 2px 4px; }
        .form-input, .form-select { width: 100%; padding: 2px 4px; border: 1px solid #d0d0d0; font-size: 11px; }
        .form-input:focus, .form-select:focus { outline: none; border-color: #4472C4; background: #FFF9E6; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 5px; }
        .checkbox-group { display: flex; align-items: center; gap: 4px; padding: 2px 4px; background: white; border: 1px solid #e0e0e0; }
        .checkbox-group input[type="checkbox"] { width: 12px; height: 12px; }
        .btn-calculate { width: 100%; padding: 5px; background: #4472C4; color: white; border: none; font-size: 12px; font-weight: 600; cursor: pointer; margin-top: 5px; }
        .btn-calculate:hover { background: #2E5090; }
        .result-item { display: flex; justify-content: space-between; padding: 2px 4px; border-bottom: 1px solid #e0e0e0; }
        .result-label { color: #444; font-size: 11px; font-weight: 600; }
        .result-value { font-weight: 600; color: #000; font-size: 11px; text-align: right; }
        .result-total { background: #FFD966; border: 1px solid #BF8F00; padding: 5px; margin-top: 5px; }
        .result-total .result-label { font-size: 13px; font-weight: 700; color: #000; }
        .result-total .result-value { font-size: 14px; font-weight: 700; color: #C00000; }
        .info-box { background: #E7F3FF; border: 1px solid #4472C4; padding: 5px; margin-top: 5px; }
        .info-box p { font-size: 10px; color: #000; line-height: 1.3; margin: 0; }
        @media (max-width: 768px) { .content { grid-template-columns: 1fr; } .form-row { grid-template-columns: 1fr; } }
    </style>
    <!-- OLD CSS COMMENTED OUT
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1466BA 0%, #0d4a8a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            padding: 30px;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
        }
        
        .result-section {
            background: #fff;
            padding: 25px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
            padding-bottom: 10px;
            border-bottom: 2px solid #1466BA;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group-inline {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        .form-label-inline {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin: 0;
        }
        
        .form-input,
        .form-select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #1466BA;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: white;
            border-radius: 6px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .btn-calculate {
            width: 100%;
            padding: 15px;
            background: #1466BA;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-calculate:hover {
            background: #0d4a8a;
        }
        
        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-label {
            color: #666;
            font-size: 14px;
        }
        
        .result-value {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .result-total {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        
        .result-total .result-item {
            border-bottom: none;
            font-size: 16px;
        }
        
        .result-total .result-label {
            font-size: 18px;
            font-weight: 600;
            color: #1466BA;
        }
        
        .result-total .result-value {
            font-size: 24px;
            color: #1466BA;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #1466BA;
            padding: 15px;
            margin-top: 20px;
            border-radius: 4px;
        }
        
        .info-box p {
            font-size: 13px;
            color: #0c5460;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* 견적서 출력 스타일 */
        .quote-print {
            display: none;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .container, .header, .content, .form-section, .result-section {
                display: none !important;
            }
            .quote-print {
                display: block !important;
                position: relative;
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 10mm;
                box-sizing: border-box;
                page-break-after: always;
                border: 2px solid #000;
            }
            @page {
                size: A4;
                margin: 0;
            }
        }
        
        .quote-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1466BA;
            padding-bottom: 20px;
        }
        
        .quote-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .quote-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .quote-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .quote-table th,
        .quote-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        
        .quote-table th {
            background: #f0f0f0;
            font-weight: 600;
        }
        
        .quote-table td.left {
            text-align: left;
        }
        
        .quote-table td.right {
            text-align: right;
        }
        
        .quote-total {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .quote-footer {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
        }
        
        .delivery-input {
            width: 100px;
            padding: 5px;
            border: 1px solid #ddd;
            text-align: right;
        }
    END OLD CSS -->
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏷️ 롤스티커 견적 계산기</h1>
                    </div>

        <!-- 대시보드 메뉴 -->
        <div style="background: #f8f9fa; padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="roll_sticker_calculator.php" style="padding: 6px 15px; background: #1466BA; color: white; text-decoration: none; border-radius: 4px; font-size: 13px; font-weight: 600;">📊 견적 계산기</a>
            <a href="quote_list.php" style="padding: 6px 15px; background: white; color: #1466BA; text-decoration: none; border: 1px solid #1466BA; border-radius: 4px; font-size: 13px; font-weight: 600;">📋 견적 리스트</a>
            <a href="../admin/roll_sticker_settings.php" style="padding: 6px 15px; background: white; color: #1466BA; text-decoration: none; border: 1px solid #1466BA; border-radius: 4px; font-size: 13px; font-weight: 600;">⚙️ 단가 설정</a>
        </div>

        <div class="content">
            <!-- 입력 폼 -->
            <div class="form-section">
                <h2 class="section-title">견적 정보 입력</h2>
                
                <form method="post">
                    <!-- 받을 회사 이름 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">받을 회사</label>
                        <input type="text" name="company_name" class="form-input" 
                               value="<?php echo htmlspecialchars($company_name); ?>" 
                               placeholder="회사명을 입력하세요">
                    </div>
                    
                    <!-- 재질 선택 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">재질 선택</label>
                        <select name="material" class="form-select" required>
                            <option value="art" <?php echo $material == 'art' ? 'selected' : ''; ?>>아트지 (1.5원/㎟)</option>
                            <option value="yupo" <?php echo $material == 'yupo' ? 'selected' : ''; ?>>유포지 (2.5원/㎟)</option>
                            <option value="silver_deadlong" <?php echo $material == 'silver_deadlong' ? 'selected' : ''; ?>>은데드롱 (2.5원/㎟)</option>
                            <option value="clear_deadlong" <?php echo $material == 'clear_deadlong' ? 'selected' : ''; ?>>투명데드롱 (2.5원/㎟)</option>
                            <option value="gold_paper" <?php echo $material == 'gold_paper' ? 'selected' : ''; ?>>금지 (3.5원/㎟)</option>
                            <option value="silver_paper" <?php echo $material == 'silver_paper' ? 'selected' : ''; ?>>은지 (3.5원/㎟)</option>
                            <option value="kraft" <?php echo $material == 'kraft' ? 'selected' : ''; ?>>크라프트 (2.3원/㎟)</option>
                            <option value="hologram" <?php echo $material == 'hologram' ? 'selected' : ''; ?>>홀로그램 (5.0원/㎟)</option>
                        </select>
                    </div>
                    
                    <!-- 가로, 세로 -->
                    <div class="form-row">
                        <div class="form-group-inline">
                            <label class="form-label-inline">가로 (mm)</label>
                            <input type="number" name="width" class="form-input" 
                                   value="<?php echo $width; ?>" 
                                   placeholder="예: 100" required>
                        </div>
                        
                        <div class="form-group-inline">
                            <label class="form-label-inline">세로 (mm)</label>
                            <input type="number" name="height" class="form-input" 
                                   value="<?php echo $height; ?>" 
                                   placeholder="예: 100" required>
                        </div>
                    </div>
                    
                    <!-- 매수, 도무송(톰슨) -->
                    <div class="form-row">
                        <div class="form-group-inline">
                            <label class="form-label-inline">매수</label>
                            <input type="number" name="quantity" class="form-input" 
                                   value="<?php echo $quantity; ?>" 
                                   placeholder="예: 1000" required>
                        </div>
                        
                        <div class="form-group-inline">
                            <label class="form-label-inline">도무송(톰슨)</label>
                            <select name="knife_count" class="form-select" required>
                                <?php for ($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $knife_count == $i ? 'selected' : ''; ?>>
                                    <?php echo $i; ?>개
                                </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <!-- 도수 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">도수 (색상 수)</label>
                        <select name="colors" class="form-select" required>
                            <option value="1" <?php echo $colors == 1 ? 'selected' : ''; ?>>1도</option>
                            <option value="2" <?php echo $colors == 2 ? 'selected' : ''; ?>>2도</option>
                            <option value="3" <?php echo $colors == 3 ? 'selected' : ''; ?>>3도</option>
                            <option value="4" <?php echo $colors == 4 ? 'selected' : ''; ?>>4도 (풀컬러)</option>
                            <option value="5" <?php echo $colors == 5 ? 'selected' : ''; ?>>5도</option>
                        </select>
                    </div>
                    
                    <!-- 편집비 -->
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="need_design" id="need_design" 
                                   <?php echo $need_design ? 'checked' : ''; ?>>
                            <label for="need_design" class="form-label" style="margin: 0;">편집비 (도당 5,000원, 최소 10,000원)</label>
                        </div>
                    </div>
                    
                    <!-- 백색인쇄 -->
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="need_white_printing" id="need_white_printing" 
                                   <?php echo $need_white_printing ? 'checked' : ''; ?>>
                            <label for="need_white_printing" class="form-label" style="margin: 0;">백색인쇄 (매수×20원, 최소 20,000원)</label>
                        </div>
                        <div class="help-text" style="font-size: 13px; color: #666; margin-top: 5px;">
                            💡 백색인쇄 선택시 백색인쇄용 필름비와 수지판비가 자동으로 계산됩니다.
                        </div>
                    </div>
                    
                    <!-- 코팅 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">코팅</label>
                        <select name="coating" class="form-select">
                            <option value="none" <?php echo $coating == 'none' ? 'selected' : ''; ?>>코팅 없음</option>
                            <option value="glossy" <?php echo $coating == 'glossy' ? 'selected' : ''; ?>>유광 코팅 (50,000원/1000매)</option>
                            <option value="matte" <?php echo $coating == 'matte' ? 'selected' : ''; ?>>무광 코팅 (40,000원/1000매)</option>
                            <option value="uv" <?php echo $coating == 'uv' ? 'selected' : ''; ?>>UV 코팅 (10,000원/1000매)</option>
                        </select>
                    </div>
                    
                    <!-- 박 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">박</label>
                        <select name="foil" class="form-select">
                            <option value="none" <?php echo $foil == 'none' ? 'selected' : ''; ?>>박 없음</option>
                            <option value="glossy_gold" <?php echo $foil == 'glossy_gold' ? 'selected' : ''; ?>>유광 금박</option>
                            <option value="matte_gold" <?php echo $foil == 'matte_gold' ? 'selected' : ''; ?>>무광 금박</option>
                            <option value="glossy_silver" <?php echo $foil == 'glossy_silver' ? 'selected' : ''; ?>>유광 은박</option>
                            <option value="matte_silver" <?php echo $foil == 'matte_silver' ? 'selected' : ''; ?>>무광 은박</option>
                        </select>
                    </div>
                    
                    <!-- 형압 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">형압</label>
                        <select name="embossing" class="form-select">
                            <option value="none" <?php echo $embossing == 'none' ? 'selected' : ''; ?>>형압 없음</option>
                            <option value="raised" <?php echo $embossing == 'raised' ? 'selected' : ''; ?>>양각 (30,000원/1000매)</option>
                            <option value="recessed" <?php echo $embossing == 'recessed' ? 'selected' : ''; ?>>음각 (30,000원/1000매)</option>
                        </select>
                    </div>
                    <div class="help-text" style="font-size: 13px; color: #666; margin-bottom: 15px;">
                        💡 형압 선택시 형압용수지판비(암수 2개)가 자동으로 계산됩니다.
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" name="partial_coating" id="partial_coating" 
                                   <?php echo $partial_coating ? 'checked' : ''; ?>>
                            <label for="partial_coating" class="form-label" style="margin: 0;">부분코팅 (30,000원/1000매)</label>
                        </div>
                    </div>
                    
                    <!-- 택배선불 -->
                    <div class="form-group-inline">
                        <label class="form-label-inline">택배선불</label>
                        <input type="number" name="delivery_prepaid" class="form-input" 
                               value="<?php echo $delivery_prepaid; ?>" 
                               placeholder="택배비를 입력하세요" min="0">
                    </div>
                    
                    <button type="submit" class="btn-calculate">💰 견적 계산하기</button>
                </form>
                
                <div class="info-box">
                    <p><strong>💡 계산 방식</strong></p>
                    <p>• 재질비 = MAX(재질단가 × 10,000원, 재질단가 × 가로 × 세로 × 매수 ÷ 1000)</p>
                    <p>• 도안비 = 도수 × 5,000원 (최소 10,000원)</p>
                    <p>• 필름비 = 가로 × 세로 × 1원 × 도수 (최소 4,000원/도)</p>
                    <p>• 수지판비 = 가로 × 세로 × 3원 × 도수 (최소 5,000원/도)</p>
                </div>
            </div>
            
            <!-- 결과 표시 -->
            <div class="result-section">
                <h2 class="section-title">견적 결과</h2>
                
                <?php if (!empty($result)): ?>
                    <?php if (!empty($company_name)): ?>
                    <div class="result-item">
                        <span class="result-label">받을 회사</span>
                        <span class="result-value" style="font-weight: 700; color: #1466BA;"><?php echo htmlspecialchars($company_name); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="result-item">
                        <span class="result-label">사이즈</span>
                        <span class="result-value"><?php echo number_format($width); ?> × <?php echo number_format($height); ?> mm</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">매수</span>
                        <span class="result-value"><?php echo number_format($quantity); ?> 매</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">도수</span>
                        <span class="result-value"><?php echo $colors; ?>도</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">총 면적</span>
                        <span class="result-value"><?php echo number_format($total_area); ?> ㎟</span>
                    </div>
                    
                    <hr style="margin: 20px 0; border: none; border-top: 2px solid #e9ecef;">
                    
                    <div class="result-item">
                        <span class="result-label">1. 재질비 (<?php echo $result['material_name']; ?> <?php echo $result['material_price']; ?>원/㎟, 최소 <?php echo number_format($result['material_minimum']); ?>원)</span>
                        <span class="result-value"><?php echo number_format($result['area_cost']); ?>원</span>
                    </div>
                    
                    <?php if ($result['need_design']): ?>
                    <div class="result-item">
                        <span class="result-label">2. 도안비</span>
                        <span class="result-value"><?php echo number_format($result['design_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="result-item">
                        <span class="result-label">3. 필름비 (<?php echo number_format($result['film_cost_per_color']); ?>원 × <?php echo $colors; ?>도)</span>
                        <span class="result-value"><?php echo number_format($result['film_cost']); ?>원</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">4. 수지판비 (<?php echo number_format($result['resin_cost_per_color']); ?>원 × <?php echo $colors; ?>도)</span>
                        <span class="result-value"><?php echo number_format($result['resin_cost']); ?>원</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">5. 도무송비 (<?php echo number_format($result['embossing_cost_per_knife']); ?>원 × <?php echo $result['knife_count']; ?>개)</span>
                        <span class="result-value"><?php echo number_format($result['embossing_cost']); ?>원</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">6. 인쇄비 (최소 10,000원)</span>
                        <span class="result-value"><?php echo number_format($result['printing_cost']); ?>원</span>
                    </div>
                    
                    <?php if ($result['white_printing_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">7. 백색인쇄비 (최소 20,000원)</span>
                        <span class="result-value"><?php echo number_format($result['white_printing_cost']); ?>원</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">7-1. 백색인쇄 필름비 (최소 4,000원)</span>
                        <span class="result-value"><?php echo number_format($result['white_film_cost']); ?>원</span>
                    </div>
                    
                    <div class="result-item">
                        <span class="result-label">7-2. 백색인쇄 수지판비 (최소 5,000원)</span>
                        <span class="result-value"><?php echo number_format($result['white_resin_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['coating_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">8. 코팅비</span>
                        <span class="result-value"><?php echo number_format($result['coating_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['foil_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">9. 박 비용</span>
                        <span class="result-value"><?php echo number_format($result['foil_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['plate_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">10. 동판비<?php echo $result['auto_plate'] ? ' (박 선택으로 자동 포함)' : ''; ?></span>
                        <span class="result-value"><?php echo number_format($result['plate_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['embossing_press_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">12. 형압비 (<?php echo $result['embossing'] == 'raised' ? '양각' : '음각'; ?>)</span>
                        <span class="result-value"><?php echo number_format($result['embossing_press_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['embossing_plate_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">13. 형압용수지판비 (<?php echo number_format($result['embossing_plate_cost_per_plate']); ?>원 × 2개 암수)</span>
                        <span class="result-value"><?php echo number_format($result['embossing_plate_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['partial_coating_cost'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">14. 부분코팅비</span>
                        <span class="result-value"><?php echo number_format($result['partial_coating_cost']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($result['delivery_prepaid'] > 0): ?>
                    <div class="result-item">
                        <span class="result-label">15. 택배선불</span>
                        <span class="result-value"><?php echo number_format($result['delivery_prepaid']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="result-total">
                        <div class="result-item">
                            <span class="result-label">공급가</span>
                            <span class="result-value"><?php echo number_format($result['supply_price']); ?>원</span>
                        </div>
                        
                        <div class="result-item">
                            <span class="result-label">부가세 (10%)</span>
                            <span class="result-value"><?php echo number_format($result['vat']); ?>원</span>
                        </div>
                        
                        <div class="result-item" style="padding-top: 15px; border-top: 2px solid #1466BA;">
                            <span class="result-label">총 금액</span>
                            <span class="result-value"><?php echo number_format($result['total_price']); ?>원</span>
                        </div>
                    </div>
                    
                    <button type="button" class="btn-calculate" onclick="printQuote()" style="margin-top: 20px; background: #28a745;">
                        📄 견적서 출력
                    </button>
                    
                    <div class="info-box">
                        <p><strong>📞 문의 및 주문</strong></p>
                        <p>전화: 02-2632-1830 / 1688-2384</p>
                        <p>이메일: dsp1830@naver.com</p>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 40px 0;">
                        왼쪽 폼에 정보를 입력하고<br>
                        견적 계산하기 버튼을 클릭하세요.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php if (!empty($result)): ?>
    <!-- 견적서 출력용 (이미지 양식과 동일) -->
    <div class="quote-print" id="quotePrint" style="width: 210mm; height: 297mm; padding: 10mm; box-sizing: border-box; background: white; position: relative; border: 2px solid #000; overflow: hidden;">
        <div style="position: absolute; top: 8px; left: 8px; font-size: 10px;">No.</div>
        
        <div style="text-align: center; margin: 15px 0 10px 0;">
            <h1 style="font-size: 28px; margin: 0; letter-spacing: 12px; font-weight: bold;">견 적 서</h1>
        </div>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 13px;">
            <tr>
                <td style="border: 1px solid #000; padding: 6px; width: 12%; font-weight: bold;">견적일</td>
                <td style="border: 1px solid #000; padding: 6px; width: 28%;"><?php echo date('Y년 m월 d일'); ?></td>
                <td rowspan="5" colspan="4" style="border: 1px solid #000; padding: 0; width: 60%; vertical-align: top;">
                    <table style="width: 100%; height: 100%; border-collapse: collapse; font-size: 13px;">
                        <tr>
                            <td colspan="4" style="border-bottom: 1px solid #000; padding: 5px; text-align: center; font-weight: bold; background: #f0f0f0;">공 급 자</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; width: 20%;">등록번호</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; width: 30%; font-weight: bold;">107-06-45106</td>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px; width: 20%;">대표자</td>
                            <td style="border-bottom: 1px solid #000; padding: 4px; width: 30%;">차경선(직인생략)</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">상 호</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; padding: 4px; font-weight: bold;">두손기획인쇄</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">주 소</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; padding: 4px;">서울 영등포구 영등포로36길9 송호빌딩 1층</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; border-bottom: 1px solid #000; padding: 4px;">연락처</td>
                            <td colspan="3" style="border-bottom: 1px solid #000; padding: 4px;">02-2632-1830</td>
                        </tr>
                        <tr>
                            <td style="border-right: 1px solid #000; padding: 4px;">업 태</td>
                            <td style="border-right: 1px solid #000; padding: 4px;">제조</td>
                            <td style="border-right: 1px solid #000; padding: 4px;">종 목</td>
                            <td style="padding: 4px;">인쇄업외</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 6px; font-weight: bold;">담당자</td>
                <td style="border: 1px solid #000; padding: 6px;"><?php echo !empty($company_name) ? htmlspecialchars($company_name) : ''; ?> 귀하</td>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 6px; font-weight: bold;">유효기간</td>
                <td style="border: 1px solid #000; padding: 6px;">발행일로부터 7일</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2" style="border: 1px solid #000; padding: 6px; text-align: center; font-weight: bold; font-size: 24px; vertical-align: middle;">
                    합계금액<br>
                    <span style="font-size: 20px;">(부가세포함)</span>
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; text-align: center; font-size: 18px;">
                    일금 <?php 
                        $total = round(($result['supply_price'] + $delivery_prepaid) * 1.1);
                        $korean_num = ['', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구'];
                        $korean_unit = ['', '십', '백', '천', '만', '십', '백', '천', '억'];
                        
                        function numberToKorean($num) {
                            global $korean_num, $korean_unit;
                            if ($num == 0) return '영';
                            
                            $result = '';
                            $unit_idx = 0;
                            
                            while ($num > 0) {
                                $digit = $num % 10;
                                if ($digit > 0) {
                                    if ($digit == 1 && ($unit_idx == 1 || $unit_idx == 2 || $unit_idx == 3 || $unit_idx == 5 || $unit_idx == 6 || $unit_idx == 7)) {
                                        $result = $korean_unit[$unit_idx] . $result;
                                    } else {
                                        $result = $korean_num[$digit] . $korean_unit[$unit_idx] . $result;
                                    }
                                }
                                if ($unit_idx == 4 && $digit > 0) $result = '만' . $result;
                                if ($unit_idx == 8 && $digit > 0) $result = '억' . $result;
                                $num = floor($num / 10);
                                $unit_idx++;
                            }
                            return $result;
                        }
                        
                        echo numberToKorean($total) . '원정 ( ₩' . number_format($total) . ' )';
                    ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 10px; text-align: center; font-size: 22px; font-weight: bold;">
                    <?php echo number_format(round(($result['supply_price'] + $delivery_prepaid) * 1.1)); ?> 원
                </td>
            </tr>
        </table>
        
        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="background: #f0f0f0;">
                    <th style="border: 1px solid #000; padding: 5px; width: 5%;">NO</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 18%;">품 목</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 22%;">규격 및 사양</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 8%;">수량</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 7%;">단위</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 13%;">단가</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 17%;">공급가액(VAT 별도)</th>
                    <th style="border: 1px solid #000; padding: 5px; width: 10%;">비 고</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                    <td style="border: 1px solid #000; padding: 4px;">롤스티커</td>
                    <td style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                        <?php echo $result['material_name']; ?><br>
                        <?php echo number_format($width); ?>×<?php echo number_format($height); ?>mm<br>
                        <?php echo $colors; ?>도
                        <?php if ($need_design): ?>, 편집<?php endif; ?>
                        <?php if ($need_white_printing): ?>, 백색<?php endif; ?>
                        <?php if ($coating != 'none'): ?>
                            , <?php 
                                $coating_names = ['glossy' => '유광', 'matte' => '무광', 'uv' => 'UV'];
                                echo $coating_names[$coating] ?? '';
                            ?>
                        <?php endif; ?>
                        <?php if ($foil != 'none'): ?>
                            , <?php 
                                $foil_names = [
                                    'glossy_gold' => '유광금', 
                                    'matte_gold' => '무광금',
                                    'glossy_silver' => '유광은',
                                    'matte_silver' => '무광은'
                                ];
                                echo $foil_names[$foil] ?? '';
                            ?>
                        <?php endif; ?>
                        <?php if ($embossing != 'none'): ?>
                            , <?php echo $embossing == 'raised' ? '양각' : '음각'; ?>
                        <?php endif; ?>
                        <?php if ($partial_coating): ?>, 부분코팅<?php endif; ?>
                    </td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;"><?php echo number_format($quantity); ?></td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">매</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;"><?php echo number_format($result['supply_price']); ?></td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;"><?php echo number_format($result['supply_price']); ?> 원</td>
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                </tr>
                <?php if ($delivery_prepaid > 0): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">2</td>
                    <td style="border: 1px solid #000; padding: 4px;">택배선불</td>
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">1</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: center;">식</td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;"><?php echo number_format($delivery_prepaid); ?></td>
                    <td style="border: 1px solid #000; padding: 4px; text-align: right;"><?php echo number_format($delivery_prepaid); ?> 원</td>
                    <td style="border: 1px solid #000; padding: 4px;"></td>
                </tr>
                <?php 
                    $empty_rows = 11;
                else: 
                    $empty_rows = 12;
                endif; 
                ?>
                <?php for ($i = 0; $i < $empty_rows; $i++): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 5px; height: 22px; text-align: center;"><?php echo ($delivery_prepaid > 0 ? $i + 3 : $i + 2); ?></td>
                    <td style="border: 1px solid #000; padding: 5px;"></td>
                    <td style="border: 1px solid #000; padding: 5px;"></td>
                    <td style="border: 1px solid #000; padding: 5px;"></td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: center;">개</td>
                    <td style="border: 1px solid #000; padding: 5px;"></td>
                    <td style="border: 1px solid #000; padding: 5px; text-align: right;">0 원</td>
                    <td style="border: 1px solid #000; padding: 5px;"></td>
                </tr>
                <?php endfor; ?>
                <tr style="background: #f0f0f0;">
                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; font-size: 11px;">공급가액 합계</td>
                    <td colspan="2" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 11px;">
                        <?php echo number_format($result['supply_price'] + $delivery_prepaid); ?> 원
                    </td>
                </tr>
                <tr style="background: #f8f8f8;">
                    <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: center; font-weight: bold; font-size: 11px;">세 액 (VAT)</td>
                    <td colspan="2" style="border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; font-size: 11px;">
                        <?php echo number_format(round(($result['supply_price'] + $delivery_prepaid) * 0.1)); ?> 원
                    </td>
                </tr>
                <tr style="background: #e0e0e0;">
                    <td colspan="6" style="border: 1px solid #000; padding: 10px; text-align: center; font-weight: bold; font-size: 13px;">합 계(부가세 포함)</td>
                    <td colspan="2" style="border: 1px solid #000; padding: 10px; text-align: right; font-weight: bold; font-size: 13px;">
                        <?php echo number_format(round(($result['supply_price'] + $delivery_prepaid) * 1.1)); ?> 원
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 12px; font-size: 10px; line-height: 1.8;">
            <p style="margin: 4px 0;"><strong>▶ 입금 계좌번호 :</strong> 국민 999-1688-2384 / 신한 110-342-543507 / 농협 301-2632-1829 예금주: 두손기획인쇄 차경선</p>
            <p style="margin: 4px 0;"><strong>▶ 담당자 :</strong></p>
            <p style="margin: 4px 0;"><strong>▶ 비 고 :</strong> 택배는 착불기준입니다</p>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    function updateTotal() {
        const deliveryFee = parseInt(document.getElementById('deliveryFee').value) || 0;
        const deliveryVat = Math.round(deliveryFee * 0.1);
        const baseSupply = <?php echo !empty($result) ? $result['supply_price'] : 0; ?>;
        const baseVat = <?php echo !empty($result) ? $result['vat'] : 0; ?>;
        
        const totalSupply = baseSupply + deliveryFee;
        const totalVat = baseVat + deliveryVat;
        const grandTotal = totalSupply + totalVat;
        
        document.getElementById('deliveryVat').textContent = deliveryVat.toLocaleString() + '원';
        document.getElementById('totalSupply').textContent = totalSupply.toLocaleString() + '원';
        document.getElementById('totalVat').textContent = totalVat.toLocaleString() + '원';
        document.getElementById('grandTotal').textContent = grandTotal.toLocaleString() + '원';
    }
    
    function printQuote() {
        window.print();
    }
    </script>
</body>
</html>





