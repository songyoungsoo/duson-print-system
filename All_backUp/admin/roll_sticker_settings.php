<?php
/**
 * 롤스티커 계산 설정 관리
 * 경로: /admin/roll_sticker_settings.php
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// db.php에서 $db 변수를 사용하므로 $conn으로 별칭 설정
$conn = $db;

// 관리자 권한 체크 (필요시 추가)
// if (!isset($_SESSION['admin'])) {
//     header('Location: /admin/login.php');
//     exit;
// }

// 설정 저장
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_settings'])) {
    foreach ($_POST as $key => $value) {
        if ($key != 'save_settings' && strpos($key, 'setting_') === 0) {
            $setting_key = substr($key, 8); // 'setting_' 제거
            $setting_value = floatval($value);
            
            $stmt = $conn->prepare("INSERT INTO roll_sticker_settings (setting_key, setting_value) 
                                   VALUES (?, ?) 
                                   ON DUPLICATE KEY UPDATE setting_value = ?");
            $stmt->bind_param("sdd", $setting_key, $setting_value, $setting_value);
            $stmt->execute();
            $stmt->close();
        }
    }
    $success_message = "설정이 저장되었습니다.";
}

// 현재 설정 불러오기
$settings = [];
$table_exists = false;

// 테이블 존재 여부 확인
$check_table = $conn->query("SHOW TABLES LIKE 'roll_sticker_settings'");
if ($check_table && $check_table->num_rows > 0) {
    $table_exists = true;
    $result = $conn->query("SELECT setting_key, setting_value FROM roll_sticker_settings");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}

// 기본값 설정
$default_settings = [
    // 재질 단가 (원/㎟)
    'material_art' => 1.5,
    'material_yupo' => 2.5,
    'material_silver_deadlong' => 2.5,
    'material_clear_deadlong' => 2.5,
    'material_gold_paper' => 3.5,
    'material_silver_paper' => 3.5,
    'material_kraft' => 2.3,
    'material_hologram' => 5.0,
    
    // 재질 최소비용 배수
    'material_minimum_multiplier' => 10000,
    
    // 도안비
    'design_per_color' => 5000,
    'design_minimum' => 10000,
    
    // 필름비
    'film_per_sqmm' => 1,
    'film_minimum' => 4000,
    
    // 수지판비
    'resin_per_sqmm' => 3,
    'resin_minimum' => 5000,
    
    // 도무송비
    'embossing_knife_per_sqmm' => 2,
    'embossing_knife_minimum' => 10000,
    
    // 인쇄비
    'printing_per_sheet_color' => 10,
    'printing_minimum' => 10000,
    
    // 백색인쇄비
    'white_printing_per_sheet' => 20,
    'white_printing_minimum' => 20000,
    'white_film_per_sqmm' => 1,
    'white_film_minimum' => 4000,
    'white_resin_per_sqmm' => 3,
    'white_resin_minimum' => 5000,
    
    // 코팅비 (1000매당)
    'coating_glossy' => 50000,
    'coating_matte' => 40000,
    'coating_uv' => 10000,
    
    // 박비
    'foil_per_sqmm' => 0.016,
    'foil_minimum' => 35000,
    
    // 동판비
    'plate_per_sqmm' => 5,
    'plate_minimum' => 5000,
    
    // 형압비 (1000매당)
    'embossing_press_per_1000' => 30000,
    
    // 형압용수지판비
    'embossing_plate_per_sqmm' => 3,
    'embossing_plate_minimum' => 5000,
    
    // 부분코팅비 (1000매당)
    'partial_coating_per_1000' => 30000,
];

// 설정값 병합 (DB값이 있으면 사용, 없으면 기본값)
foreach ($default_settings as $key => $value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $value;
    }
}

// 불필요한 소수점 제거 함수
function formatNumber($value) {
    // 정수인 경우 정수로 표시
    if (floor($value) == $value) {
        return number_format($value, 0, '.', '');
    }
    // 소수인 경우 불필요한 0 제거
    return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Excel Style CSS */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Noto Sans KR', 'Malgun Gothic', sans-serif; background: #f0f0f0; padding: 5px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; border: 1px solid #d0d0d0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .header { background: #4472C4; color: white; padding: 6px 15px; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #2E5090; }
        .header h1 { font-size: 14px; margin: 0; font-weight: 600; }
        .header a { background: white; color: #4472C4; padding: 4px 10px; text-decoration: none; font-weight: 600; font-size: 11px; border: 1px solid #d0d0d0; }
        .content { padding: 10px; }
        .success-message { background: #d4edda; color: #155724; padding: 8px 12px; margin-bottom: 10px; border: 1px solid #c3e6cb; font-size: 12px; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #1466BA; background: #E7F3FF; padding: 3px 6px; border-left: 3px solid #5B9BD5; }
        .settings-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 5px; }
        .setting-item { background: white; padding: 3px 8px; border: 1px solid #d0d0d0; display: flex; align-items: center; gap: 8px; }
        .setting-label { font-weight: 600; color: #444; white-space: nowrap; flex-shrink: 0; min-width: 120px; font-size: 11px; background: #F2F2F2; padding: 2px 4px; }
        .setting-input { flex: 1; padding: 2px 4px; border: 1px solid #d0d0d0; font-size: 11px; min-width: 100px; }
        .setting-input:focus { outline: none; border-color: #4472C4; background: #FFF9E6; }
        .save-button { background: #4472C4; color: white; padding: 6px 20px; border: none; font-size: 12px; font-weight: 600; cursor: pointer; margin-top: 10px; }
        .save-button:hover { background: #2E5090; }
        .help-text { font-size: 10px; color: #666; margin-left: 6px; white-space: nowrap; }
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
            padding: 5px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 4px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #1466BA 0%, #0d4a8a 100%);
            color: white;
            padding: 10px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        
        .header a {
            background: white;
            color: #1466BA;
            padding: 6px 12px;
            border-radius: 3px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
        }
        
        .content {
            padding: 10px 15px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 8px 12px;
            border-radius: 3px;
            margin-bottom: 10px;
            border: 1px solid #c3e6cb;
            font-size: 13px;
        }
        
        .section {
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
            padding-bottom: 3px;
            border-bottom: 2px solid #1466BA;
        }
        
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 5px;
        }
        
        .setting-item {
            background: #f8f9fa;
            padding: 4px 10px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .setting-label {
            font-weight: 600;
            color: #333;
            white-space: nowrap;
            flex-shrink: 0;
            min-width: 120px;
            font-size: 12px;
        }
        
        .setting-input {
            flex: 1;
            padding: 4px 6px;
            border: 1px solid #ddd;
            border-radius: 3px;
            font-size: 12px;
            min-width: 100px;
        }
        
        .setting-input:focus {
            outline: none;
            border-color: #1466BA;
        }
        
        .save-button {
            background: #1466BA;
            color: white;
            padding: 8px 24px;
            border: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .save-button:hover {
            background: #0d4a8a;
        }
        
        .help-text {
            font-size: 10px;
            color: #666;
            margin-left: 6px;
            white-space: nowrap;
        }
    END OLD CSS -->
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚙️ 롤스티커 계산 설정 관리</h1>
            <a href="../shop/roll_sticker_calculator.php">계산기로 이동</a>
        </div>
        
        <div class="content">
            <?php if (!$table_exists): ?>
            <div class="error-message" style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <h3>⚠️ 테이블이 생성되지 않았습니다</h3>
                <p>먼저 설정 테이블을 생성해주세요.</p>
                <a href="create_settings_table.php" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: #dc3545; color: white; text-decoration: none; border-radius: 6px;">테이블 생성하기</a>
            </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)): ?>
            <div class="success-message">
                ✓ <?php echo $success_message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($table_exists): ?>
            <form method="post">
                <!-- 1. 재질 단가 -->
                <div class="section">
                    <h2 class="section-title">1. 재질 단가 (원/㎟)</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">아트지</label>
                            <input type="number" step="0.1" name="setting_material_art" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_art']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">유포지</label>
                            <input type="number" step="0.1" name="setting_material_yupo" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_yupo']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">은데드롱</label>
                            <input type="number" step="0.1" name="setting_material_silver_deadlong" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_silver_deadlong']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">투명데드롱</label>
                            <input type="number" step="0.1" name="setting_material_clear_deadlong" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_clear_deadlong']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">금지</label>
                            <input type="number" step="0.1" name="setting_material_gold_paper" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_gold_paper']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">은지</label>
                            <input type="number" step="0.1" name="setting_material_silver_paper" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_silver_paper']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">크라프트</label>
                            <input type="number" step="0.1" name="setting_material_kraft" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_kraft']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">홀로그램</label>
                            <input type="number" step="0.1" name="setting_material_hologram" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_hologram']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">재질 최소비용 배수</label>
                            <input type="number" step="1" name="setting_material_minimum_multiplier" class="setting-input" 
                                   value="<?php echo formatNumber($settings['material_minimum_multiplier']); ?>">
                            <div class="help-text">재질단가 × 이 값 = 최소비용</div>
                        </div>
                    </div>
                </div>
                
                <!-- 2. 도안비 -->
                <div class="section">
                    <h2 class="section-title">2. 편집비 (도안비)</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">도당 단가 (원)</label>
                            <input type="number" step="1" name="setting_design_per_color" class="setting-input" 
                                   value="<?php echo formatNumber($settings['design_per_color']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_design_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['design_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 3. 필름비 -->
                <div class="section">
                    <h2 class="section-title">3. 필름비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">제곱mm당 단가 (원)</label>
                            <input type="number" step="0.01" name="setting_film_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['film_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_film_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['film_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 4. 수지판비 -->
                <div class="section">
                    <h2 class="section-title">4. 수지판비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">제곱mm당 단가 (원)</label>
                            <input type="number" step="0.01" name="setting_resin_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['resin_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_resin_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['resin_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 5. 도무송비 -->
                <div class="section">
                    <h2 class="section-title">5. 도무송비 (톰슨칼)</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">제곱mm당 단가 (원)</label>
                            <input type="number" step="0.01" name="setting_embossing_knife_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['embossing_knife_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_embossing_knife_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['embossing_knife_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 6. 인쇄비 -->
                <div class="section">
                    <h2 class="section-title">6. 인쇄비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">매당 도당 단가 (원)</label>
                            <input type="number" step="1" name="setting_printing_per_sheet_color" class="setting-input" 
                                   value="<?php echo formatNumber($settings['printing_per_sheet_color']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_printing_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['printing_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 7. 백색인쇄비 -->
                <div class="section">
                    <h2 class="section-title">7. 백색인쇄비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">매당 단가 (원)</label>
                            <input type="number" step="1" name="setting_white_printing_per_sheet" class="setting-input" 
                                   value="<?php echo formatNumber($settings['white_printing_per_sheet']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_white_printing_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['white_printing_minimum']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">백색 필름비 (원/㎟)</label>
                            <input type="number" step="0.01" name="setting_white_film_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['white_film_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">백색 필름 최소 (원)</label>
                            <input type="number" step="1" name="setting_white_film_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['white_film_minimum']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">백색 수지판비 (원/㎟)</label>
                            <input type="number" step="0.01" name="setting_white_resin_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['white_resin_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">백색 수지판 최소 (원)</label>
                            <input type="number" step="1" name="setting_white_resin_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['white_resin_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 8. 코팅비 -->
                <div class="section">
                    <h2 class="section-title">8. 코팅비 (1000매당)</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">유광 코팅 (원)</label>
                            <input type="number" step="1" name="setting_coating_glossy" class="setting-input" 
                                   value="<?php echo formatNumber($settings['coating_glossy']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">무광 코팅 (원)</label>
                            <input type="number" step="1" name="setting_coating_matte" class="setting-input" 
                                   value="<?php echo formatNumber($settings['coating_matte']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">UV 코팅 (원)</label>
                            <input type="number" step="1" name="setting_coating_uv" class="setting-input" 
                                   value="<?php echo formatNumber($settings['coating_uv']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 9. 박비 -->
                <div class="section">
                    <h2 class="section-title">9. 박비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">제곱mm당 단가 (원)</label>
                            <input type="number" step="0.001" name="setting_foil_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['foil_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_foil_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['foil_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 10. 동판비 -->
                <div class="section">
                    <h2 class="section-title">10. 동판비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">제곱mm당 단가 (원)</label>
                            <input type="number" step="0.01" name="setting_plate_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['plate_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_plate_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['plate_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 11. 형압비 -->
                <div class="section">
                    <h2 class="section-title">11. 형압비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">1000매당 단가 (원)</label>
                            <input type="number" step="1" name="setting_embossing_press_per_1000" class="setting-input" 
                                   value="<?php echo formatNumber($settings['embossing_press_per_1000']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 12. 형압용수지판비 -->
                <div class="section">
                    <h2 class="section-title">12. 형압용수지판비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">제곱mm당 단가 (원)</label>
                            <input type="number" step="0.01" name="setting_embossing_plate_per_sqmm" class="setting-input" 
                                   value="<?php echo formatNumber($settings['embossing_plate_per_sqmm']); ?>">
                        </div>
                        <div class="setting-item">
                            <label class="setting-label">최소 금액 (원)</label>
                            <input type="number" step="1" name="setting_embossing_plate_minimum" class="setting-input" 
                                   value="<?php echo formatNumber($settings['embossing_plate_minimum']); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- 13. 부분코팅비 -->
                <div class="section">
                    <h2 class="section-title">13. 부분코팅비</h2>
                    <div class="settings-grid">
                        <div class="setting-item">
                            <label class="setting-label">1000매당 단가 (원)</label>
                            <input type="number" step="1" name="setting_partial_coating_per_1000" class="setting-input" 
                                   value="<?php echo formatNumber($settings['partial_coating_per_1000']); ?>">
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="save_settings" class="save-button">💾 설정 저장</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
