<?php
/**
 * 롤스티커 견적서 상세보기
 * 경로: /shop/quote_view.php
 */

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

// db.php에서 $db 변수를 사용하므로 $conn으로 별칭 설정
$conn = $db;

$id = intval($_GET['id'] ?? 0);

if ($id == 0) {
    header('Location: quote_list.php');
    exit;
}

$sql = "SELECT * FROM roll_sticker_quotes WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header('Location: quote_list.php');
    exit;
}

$quote = $result->fetch_assoc();
$conn->close();

// 재질명 변환
$materials = [
    'art' => '아트지',
    'yupo' => '유포지',
    'silver_deadlong' => '은데드롱',
    'clear_deadlong' => '투명데드롱',
    'gold_paper' => '금지',
    'silver_paper' => '은지',
    'kraft' => '크라프트',
    'hologram' => '홀로그램'
];
$material_name = $materials[$quote['material']] ?? $quote['material'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 상세보기 - <?php echo htmlspecialchars($quote['quote_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Malgun Gothic', 'Segoe UI', sans-serif;
            background: #f0f0f0;
            padding: 5px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border: 1px solid #d0d0d0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .header {
            background: #4472C4;
            color: white;
            padding: 8px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #2E5090;
        }
        
        .header h1 {
            font-size: 16px;
            font-weight: 600;
        }
        
        .header .buttons {
            display: flex;
            gap: 5px;
        }
        
        .header a, .header button {
            background: white;
            color: #4472C4;
            padding: 4px 12px;
            border-radius: 2px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 12px;
        }
        
        .content {
            padding: 10px;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-section h2 {
            font-size: 13px;
            margin-bottom: 5px;
            color: white;
            background: #5B9BD5;
            padding: 4px 8px;
            font-weight: 600;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            border: 1px solid #d0d0d0;
        }
        
        .info-item {
            display: flex;
            padding: 4px 8px;
            border-bottom: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }
        
        .info-item:nth-child(2n) {
            border-right: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #444;
            min-width: 100px;
            font-size: 12px;
            background: #F2F2F2;
            padding: 2px 6px;
            margin-right: 8px;
        }
        
        .info-value {
            color: #000;
            font-size: 12px;
            padding: 2px 0;
        }
        
        .price-box {
            background: #FFF2CC;
            border: 1px solid #d0d0d0;
            padding: 8px;
            margin-top: 10px;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            padding: 2px 5px;
            font-size: 12px;
        }
        
        .price-label {
            color: #444;
            font-weight: 600;
        }
        
        .price-value {
            color: #000;
            font-weight: 600;
        }
        
        .price-total {
            background: #FFD966;
            padding: 5px 8px;
            margin-top: 5px;
            border: 1px solid #BF8F00;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 700;
            color: #000;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
            }
            .header .buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>견적서 상세보기</h1>
            <div class="buttons">
                <a href="quote_list.php">목록으로</a>
                <button onclick="window.print()">인쇄</button>
            </div>
        </div>
        
        <div class="content">
            <div class="info-section">
                <h2>기본 정보</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">견적번호</span>
                        <span class="info-value"><?php echo htmlspecialchars($quote['quote_number']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">작성일</span>
                        <span class="info-value"><?php echo date('Y년 m월 d일', strtotime($quote['created_at'])); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">회사명</span>
                        <span class="info-value"><?php echo htmlspecialchars($quote['company_name'] ?: '-'); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>제품 사양</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">규격</span>
                        <span class="info-value"><?php echo number_format($quote['width']); ?> × <?php echo number_format($quote['height']); ?> mm</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">매수</span>
                        <span class="info-value"><?php echo number_format($quote['quantity']); ?> 매</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">재질</span>
                        <span class="info-value"><?php echo $material_name; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">도수</span>
                        <span class="info-value"><?php echo $quote['colors']; ?>도</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">칼(톰슨) 개수</span>
                        <span class="info-value"><?php echo $quote['knife_count']; ?>개</span>
                    </div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>추가 옵션</h2>
                <div class="info-grid">
                    <?php if ($quote['need_design']): ?>
                    <div class="info-item">
                        <span class="info-label">편집비</span>
                        <span class="info-value">포함</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['need_white_printing']): ?>
                    <div class="info-item">
                        <span class="info-label">백색인쇄</span>
                        <span class="info-value">포함</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['coating'] != 'none'): ?>
                    <div class="info-item">
                        <span class="info-label">코팅</span>
                        <span class="info-value"><?php 
                            $coatings = ['glossy' => '유광', 'matte' => '무광', 'uv' => 'UV'];
                            echo $coatings[$quote['coating']] ?? $quote['coating'];
                        ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['foil'] != 'none'): ?>
                    <div class="info-item">
                        <span class="info-label">박</span>
                        <span class="info-value"><?php 
                            $foils = [
                                'glossy_gold' => '유광금박',
                                'matte_gold' => '무광금박',
                                'glossy_silver' => '유광은박',
                                'matte_silver' => '무광은박'
                            ];
                            echo $foils[$quote['foil']] ?? $quote['foil'];
                        ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['embossing'] != 'none'): ?>
                    <div class="info-item">
                        <span class="info-label">형압</span>
                        <span class="info-value"><?php echo $quote['embossing'] == 'raised' ? '양각' : '음각'; ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['partial_coating']): ?>
                    <div class="info-item">
                        <span class="info-label">부분코팅</span>
                        <span class="info-value">포함</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote['delivery_prepaid'] > 0): ?>
                    <div class="info-item">
                        <span class="info-label">택배선불</span>
                        <span class="info-value"><?php echo number_format($quote['delivery_prepaid']); ?>원</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="price-box">
                <div class="price-item">
                    <span>공급가</span>
                    <span><?php echo number_format($quote['supply_price']); ?>원</span>
                </div>
                <div class="price-item">
                    <span>부가세 (10%)</span>
                    <span><?php echo number_format($quote['vat']); ?>원</span>
                </div>
                <div class="price-item">
                    <span>총 금액</span>
                    <span><?php echo number_format($quote['total_price']); ?>원</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
