<?php
/**
 * 통합 장바구니 주문 완료 페이지
 * 경로: MlangOrder_PrintAuto/OrderComplete_unified.php
 */

session_start();

// 데이터베이스 연결
include "../db.php";
$connect = $db;

// 카테고리 번호로 한글명 조회 함수
function getCategoryName($connect, $category_no) {
    if (!$category_no) return '';
    
    $query = "SELECT title FROM MlangPrintAuto_transactionCate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) {
        return $category_no;
    }
    
    mysqli_stmt_bind_param($stmt, 's', $category_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $category_no;
}

// GET 파라미터에서 데이터 가져오기
$orders = $_GET['orders'] ?? '';
$email = $_GET['email'] ?? '';
$name = $_GET['name'] ?? '';

if (empty($orders)) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='../MlangPrintAuto/shop/cart.php';</script>";
    exit;
}

// 주문 번호들을 배열로 변환
$order_numbers = explode(',', $orders);
$order_list = [];
$total_amount = 0;
$total_amount_vat = 0;

// 각 주문 정보 조회
foreach ($order_numbers as $order_no) {
    $order_no = trim($order_no);
    if (!empty($order_no)) {
        $query = "SELECT * FROM MlangOrder_PrintAuto WHERE no = ?";
        $stmt = mysqli_prepare($connect, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($result && mysqli_num_rows($result) > 0) {
                $order = mysqli_fetch_assoc($result);
                $order_list[] = $order;
                $total_amount += floatval($order['money_4']);
                $total_amount_vat += floatval($order['money_5']);
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (empty($order_list)) {
    echo "<script>alert('주문 정보를 찾을 수 없습니다.'); location.href='../MlangPrintAuto/shop/cart.php';</script>";
    exit;
}

// 첫 번째 주문의 고객 정보 사용
$first_order = $order_list[0];

// 페이지 설정
$page_title = '✅ 주문 완료';
$current_page = 'order_complete';

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<div class="container">
    <!-- 주문 완료 성공 메시지 -->
    <div style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 1.5rem; border-radius: 20px; text-align: center; margin-bottom: 2rem; box-shadow: 0 15px 35px rgba(39, 174, 96, 0.3);">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; gap: 15px;">
            <span style="font-size: 2.5rem; animation: bounce 2s infinite;">🎉</span>
            주문이 완료되었습니다!
        </h1>
        <p style="font-size: 1.3rem; opacity: 0.95; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($name); ?> 고객님, 주문해 주셔서 감사합니다.</p>
        <div style="background: rgba(255,255,255,0.2); padding: 0.8rem; border-radius: 10px; margin-top: 1rem; display: inline-block;">
            <p style="margin: 0; font-size: 1.1rem;">📧 주문 확인 메일이 발송되었습니다</p>
        </div>
    </div>

    <!-- 주문 요약 정보 -->
    <div style="background: #f8f9fa; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; border-left: 5px solid #3498db;">
        <h2 style="color: #2c3e50; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px;">
            📊 주문 요약
        </h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div style="text-align: center; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: bold; color: #3498db; margin-bottom: 0.5rem;"><?php echo count($order_list); ?>개</div>
                <div style="color: #666; font-weight: 600;">주문 건수</div>
            </div>
            <div style="text-align: center; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: bold; color: #27ae60; margin-bottom: 0.5rem;"><?php echo number_format($total_amount); ?>원</div>
                <div style="color: #666; font-weight: 600;">총 주문금액</div>
            </div>
            <div style="text-align: center; background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <div style="font-size: 2rem; font-weight: bold; color: #e74c3c; margin-bottom: 0.5rem;"><?php echo number_format($total_amount_vat); ?>원</div>
                <div style="color: #666; font-weight: 600;">VAT 포함 총액</div>
            </div>
        </div>
    </div>

    <!-- 주문 상세 내역 -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 1.5rem;">
            <h2 style="margin: 0; font-size: 1.8rem; display: flex; align-items: center; gap: 10px;">
                📋 주문 상세 내역
            </h2>
        </div>
        <div style="padding: 0;">
            <?php foreach ($order_list as $index => $order): ?>
            <div style="padding: 2rem; border-bottom: <?php echo $index < count($order_list) - 1 ? '1px solid #eee' : 'none'; ?>; <?php echo $index % 2 == 0 ? 'background: #f9f9f9;' : 'background: white;'; ?>">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 1rem;">
                            <span style="background: #3498db; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.9rem; font-weight: bold;">
                                주문 #<?php echo htmlspecialchars($order['no']); ?>
                            </span>
                            <span style="color: #666; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($order['date']); ?>
                            </span>
                        </div>
                        
                        <h3 style="color: #2c3e50; margin-bottom: 1rem; font-size: 1.3rem;">
                            <?php echo htmlspecialchars($order['Type']); ?>
                        </h3>
                        
                        <?php if (!empty($order['Type_1'])): ?>
                        <div style="background: #e8f4fd; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <h4 style="color: #2c3e50; margin-bottom: 0.5rem; font-size: 1rem;">📝 상품 상세 정보</h4>
                            <div style="color: #495057; line-height: 1.6;">
                                <?php 
                                // JSON 데이터인지 확인하고 파싱
                                $type_data = $order['Type_1'];
                                $json_data = json_decode($type_data, true);
                                
                                if ($json_data && isset($json_data['formatted_display'])) {
                                    // formatted_display가 있으면 사용
                                    echo nl2br(htmlspecialchars($json_data['formatted_display']));
                                } else if ($json_data && is_array($json_data)) {
                                    // JSON 객체인 경우 읽기 쉬운 형태로 변환
                                    $display_text = "";
                                    
                                    // 상품 타입별로 표시
                                    if (isset($json_data['product_type'])) {
                                        $product_type = $json_data['product_type'];
                                        
                                        switch($product_type) {
                                            case 'envelope':
                                                $display_text = "✉️ 봉투 주문\n";
                                                if (isset($json_data['MY_type'])) $display_text .= "• 타입: " . getCategoryName($connect, $json_data['MY_type']) . "\n";
                                                if (isset($json_data['MY_Fsd'])) $display_text .= "• 용지: " . getCategoryName($connect, $json_data['MY_Fsd']) . "\n";
                                                if (isset($json_data['MY_amount'])) $display_text .= "• 수량: " . number_format($json_data['MY_amount']) . "매\n";
                                                if (isset($json_data['POtype'])) $display_text .= "• 인쇄면: " . ($json_data['POtype'] == '1' ? '단면' : '양면') . "\n";
                                                if (isset($json_data['ordertype'])) $display_text .= "• 주문타입: " . ($json_data['ordertype'] == 'design' ? '디자인+인쇄' : '인쇄만') . "\n";
                                                break;
                                                
                                            case 'sticker':
                                                $display_text = "🏷️ 스티커 주문\n";
                                                if (isset($json_data['jong'])) $display_text .= "• 재질: " . $json_data['jong'] . "\n";
                                                if (isset($json_data['garo']) && isset($json_data['sero'])) {
                                                    $display_text .= "• 크기: " . $json_data['garo'] . " × " . $json_data['sero'] . "mm\n";
                                                }
                                                if (isset($json_data['mesu'])) $display_text .= "• 수량: " . number_format($json_data['mesu']) . "매\n";
                                                if (isset($json_data['domusong'])) $display_text .= "• 모양: " . $json_data['domusong'] . "\n";
                                                if (isset($json_data['uhyung'])) $display_text .= "• 편집비: " . ($json_data['uhyung'] > 0 ? '있음' : '없음') . "\n";
                                                break;
                                                
                                            case 'namecard':
                                                $display_text = "📇 명함 주문\n";
                                                if (isset($json_data['MY_type'])) $display_text .= "• 명함종류: " . getCategoryName($connect, $json_data['MY_type']) . "\n";
                                                if (isset($json_data['PN_type'])) $display_text .= "• 용지종류: " . getCategoryName($connect, $json_data['PN_type']) . "\n";
                                                if (isset($json_data['MY_amount'])) $display_text .= "• 수량: " . number_format($json_data['MY_amount']) . "매\n";
                                                if (isset($json_data['POtype'])) $display_text .= "• 인쇄면: " . ($json_data['POtype'] == '1' ? '단면' : '양면') . "\n";
                                                if (isset($json_data['ordertype'])) $display_text .= "• 디자인: " . ($json_data['ordertype'] === 'total' ? '디자인+인쇄' : ($json_data['ordertype'] === 'design' ? '디자인만' : '인쇄만')) . "\n";
                                                break;
                                                
                                            case 'merchandisebond':
                                                $display_text = "🎫 상품권/쿠폰 주문\n";
                                                if (isset($json_data['MY_type'])) $display_text .= "• 구분: " . getCategoryName($connect, $json_data['MY_type']) . "\n";
                                                if (isset($json_data['MY_Fsd'])) $display_text .= "• 종류: " . getCategoryName($connect, $json_data['MY_Fsd']) . "\n";
                                                if (isset($json_data['MY_amount'])) $display_text .= "• 수량: " . number_format($json_data['MY_amount']) . "매\n";
                                                if (isset($json_data['POtype'])) $display_text .= "• 인쇄면: " . ($json_data['POtype'] == '1' ? '단면' : '양면') . "\n";
                                                if (isset($json_data['ordertype'])) $display_text .= "• 주문타입: " . ($json_data['ordertype'] == 'design' ? '디자인+인쇄' : '인쇄만') . "\n";
                                                break;
                                                
                                            case 'cadarok':
                                                $display_text = "📖 카다록 주문\n";
                                                if (isset($json_data['MY_type'])) $display_text .= "• 타입: " . getCategoryName($connect, $json_data['MY_type']) . "\n";
                                                if (isset($json_data['MY_Fsd'])) $display_text .= "• 스타일: " . getCategoryName($connect, $json_data['MY_Fsd']) . "\n";
                                                if (isset($json_data['PN_type'])) $display_text .= "• 섹션: " . getCategoryName($connect, $json_data['PN_type']) . "\n";
                                                if (isset($json_data['MY_amount'])) $display_text .= "• 수량: " . number_format($json_data['MY_amount']) . "\n";
                                                if (isset($json_data['ordertype'])) $display_text .= "• 주문타입: " . ($json_data['ordertype'] == 'design' ? '디자인+인쇄' : '인쇄만') . "\n";
                                                break;
                                                
                                            case 'littleprint':
                                                $display_text = "🎨 포스터 주문\n";
                                                if (isset($json_data['MY_type'])) $display_text .= "• 타입: " . getCategoryName($connect, $json_data['MY_type']) . "\n";
                                                if (isset($json_data['MY_Fsd'])) $display_text .= "• 용지: " . getCategoryName($connect, $json_data['MY_Fsd']) . "\n";
                                                if (isset($json_data['PN_type'])) $display_text .= "• 규격: " . getCategoryName($connect, $json_data['PN_type']) . "\n";
                                                if (isset($json_data['MY_amount'])) $display_text .= "• 수량: " . number_format($json_data['MY_amount']) . "\n";
                                                if (isset($json_data['ordertype'])) $display_text .= "• 주문타입: " . ($json_data['ordertype'] == 'design' ? '디자인+인쇄' : '인쇄만') . "\n";
                                                break;
                                                
                                            case 'msticker':
                                                $display_text = "🧲 자석스티커 주문\n";
                                                if (isset($json_data['MY_type'])) $display_text .= "• 종류: " . getCategoryName($connect, $json_data['MY_type']) . "\n";
                                                if (isset($json_data['PN_type'])) $display_text .= "• 규격: " . getCategoryName($connect, $json_data['PN_type']) . "\n";
                                                if (isset($json_data['MY_amount'])) $display_text .= "• 수량: " . number_format($json_data['MY_amount']) . "매\n";
                                                if (isset($json_data['ordertype'])) $display_text .= "• 편집비: " . ($json_data['ordertype'] == 'design' ? '디자인+인쇄' : '인쇄만') . "\n";
                                                break;
                                                
                                            default:
                                                // 기본적으로 모든 필드를 표시
                                                foreach ($json_data as $key => $value) {
                                                    if (!empty($value) && $key != 'product_type') {
                                                        $display_key = '';
                                                        switch($key) {
                                                            case 'MY_type': $display_key = '타입'; break;
                                                            case 'MY_Fsd': $display_key = '용지/스타일'; break;
                                                            case 'PN_type': $display_key = '규격/섹션'; break;
                                                            case 'MY_amount': $display_key = '수량'; break;
                                                            case 'POtype': $display_key = '인쇄면'; break;
                                                            case 'ordertype': $display_key = '주문타입'; break;
                                                            default: $display_key = ucfirst($key); break;
                                                        }
                                                        
                                                        $display_value = $value;
                                                        if (in_array($key, ['MY_type', 'MY_Fsd', 'PN_type'])) {
                                                            $display_value = getCategoryName($connect, $value) ?: $value;
                                                        }
                                                        
                                                        $display_text .= "• " . $display_key . ": " . $display_value . "\n";
                                                    }
                                                }
                                                break;
                                        }
                                        
                                        echo nl2br(htmlspecialchars(trim($display_text)));
                                    } else {
                                        // 일반적인 JSON 필드 표시
                                        $display_parts = [];
                                        foreach ($json_data as $key => $value) {
                                            if (!empty($value)) {
                                                $display_parts[] = ucfirst($key) . ": " . $value;
                                            }
                                        }
                                        echo htmlspecialchars(implode(", ", $display_parts));
                                    }
                                } else {
                                    // JSON이 아닌 일반 텍스트
                                    echo nl2br(htmlspecialchars($type_data));
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($order['cont'])): ?>
                        <div style="background: #fff3cd; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                            <h4 style="color: #856404; margin-bottom: 0.5rem; font-size: 1rem;">💬 요청사항</h4>
                            <div style="color: #856404; line-height: 1.6;">
                                <?php echo nl2br(htmlspecialchars($order['cont'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="text-align: right; margin-left: 2rem;">
                        <div style="font-size: 1.5rem; font-weight: bold; color: #e74c3c; margin-bottom: 0.5rem;">
                            <?php echo number_format($order['money_5']); ?>원
                        </div>
                        <div style="font-size: 0.9rem; color: #666;">
                            (VAT 포함)
                        </div>
                        <div style="font-size: 0.85rem; color: #999; margin-top: 0.3rem;">
                            기본금액: <?php echo number_format($order['money_4']); ?>원
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 고객 정보 -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white; padding: 1.5rem;">
            <h2 style="margin: 0; font-size: 1.8rem;">👤 고객 정보</h2>
        </div>
        <div style="padding: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">👤 성명</label>
                        <div style="color: #495057; font-size: 1.1rem;"><?php echo htmlspecialchars($first_order['name']); ?></div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">📧 이메일</label>
                        <div style="color: #495057; font-size: 1.1rem;"><?php echo htmlspecialchars($first_order['email']); ?></div>
                    </div>
                </div>
                <div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">📞 연락처</label>
                        <div style="color: #495057; font-size: 1.1rem;">
                            <?php if(!empty($first_order['phone'])): ?>
                                전화: <?php echo htmlspecialchars($first_order['phone']); ?>
                            <?php endif; ?>
                            <?php if(!empty($first_order['phone']) && !empty($first_order['Hendphone'])): ?>
                                <br>
                            <?php endif; ?>
                            <?php if(!empty($first_order['Hendphone'])): ?>
                                휴대폰: <?php echo htmlspecialchars($first_order['Hendphone']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">🏠 주소</label>
                        <div style="color: #495057; font-size: 1.1rem;">
                            <?php if(!empty($first_order['zip'])): ?>
                                (<?php echo htmlspecialchars($first_order['zip']); ?>)
                            <?php endif; ?>
                            <?php echo htmlspecialchars($first_order['zip1'] . ' ' . $first_order['zip2']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 입금 안내 -->
    <div class="card" style="margin-bottom: 2rem;">
        <div class="card-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 1.5rem;">
            <h2 style="margin: 0; font-size: 1.8rem;">💳 입금 안내</h2>
        </div>
        <div style="padding: 2rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">👤 예금주</label>
                        <div style="color: #495057; font-size: 1.1rem;">두손기획인쇄 차경선</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">🏦 국민은행</label>
                        <div style="color: #495057; font-size: 1.1rem;">999-1688-2384</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">🏦 신한은행</label>
                        <div style="color: #495057; font-size: 1.1rem;">110-342-543507</div>
                    </div>
                </div>
                <div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">🏦 농협</label>
                        <div style="color: #495057; font-size: 1.1rem;">301-2632-1829</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="font-weight: bold; color: #2c3e50; display: block; margin-bottom: 0.3rem;">💳 카드 결제</label>
                        <div style="color: #495057; font-size: 1.1rem;">1688-2384로 전화주세요</div>
                    </div>
                    <div style="margin-bottom: 1rem; padding: 1rem; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                        <div style="color: #856404; font-size: 0.95rem; font-weight: 600;">
                            ⚠️ 입금 확인 후 작업이 시작됩니다.<br>
                            입금자명을 주문자명과 동일하게 해주세요.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼들 -->
    <div style="text-align: center; margin: 3rem 0;">
        <a href="../MlangPrintAuto/cadarok/index.php" 
           style="display: inline-block; background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 18px 35px; border-radius: 30px; text-decoration: none; font-weight: 700; box-shadow: 0 8px 25px rgba(39, 174, 96, 0.4); font-size: 1.1rem; transition: all 0.3s ease;">
            📋 새 주문하기
        </a>
    </div>
</div>

<!-- 하단 연락처 정보 -->
<div style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white; padding: 3rem 0; margin-top: 3rem;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2rem;">📞 문의 안내</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; text-align: center;">
            <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 15px;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">📞</div>
                <div style="font-weight: 700; margin-bottom: 0.5rem; font-size: 1.2rem;">고객센터</div>
                <div style="font-size: 1.1rem;">02-2632-1830</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 15px;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">🏢</div>
                <div style="font-weight: 700; margin-bottom: 0.5rem; font-size: 1.2rem;">주소</div>
                <div style="font-size: 1.1rem;">서울 영등포구 영등포로36길 9<br>송호빌딩 1층 두손기획인쇄</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 2rem; border-radius: 15px;">
                <div style="font-size: 2rem; margin-bottom: 1rem;">🌐</div>
                <div style="font-weight: 700; margin-bottom: 0.5rem; font-size: 1.2rem;">홈페이지</div>
                <div style="font-size: 1.1rem;">www.dsp114.com</div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

a:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 35px rgba(0,0,0,0.2) !important;
}
</style>

<?php
// 메일 발송 기능 추가
if ($processed_orders && !empty($email)) {
    try {
        include "mailer.lib.php";
        
        // 메일 내용 생성
        $mail_content = "<div style='font-family: Noto Sans KR, sans-serif; max-width: 600px; margin: 0 auto;'>";
        $mail_content .= "<h2 style='color: #2c3e50; text-align: center; border-bottom: 2px solid #3498db; padding-bottom: 10px;'>주문 확인서</h2>";
        
        // 고객 정보
        $first_order = $processed_orders[0];
        $mail_content .= "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
        $mail_content .= "<h3 style='color: #495057; margin-bottom: 15px;'>👤 주문자 정보</h3>";
        $mail_content .= "<p><strong>성명:</strong> " . htmlspecialchars($first_order['name']) . "</p>";
        $mail_content .= "<p><strong>이메일:</strong> " . htmlspecialchars($first_order['email']) . "</p>";
        $mail_content .= "<p><strong>연락처:</strong> " . htmlspecialchars($first_order['phone']) . "</p>";
        $mail_content .= "<p><strong>주문일시:</strong> " . htmlspecialchars($first_order['date']) . "</p>";
        $mail_content .= "</div>";
        
        // 주문 상품 정보
        $mail_content .= "<div style='background: white; border: 1px solid #dee2e6; border-radius: 8px; margin: 20px 0;'>";
        $mail_content .= "<h3 style='color: #495057; padding: 15px; margin: 0; background: #e9ecef; border-radius: 8px 8px 0 0;'>📦 주문 상품</h3>";
        
        $total_amount = 0;
        foreach ($processed_orders as $order) {
            $mail_content .= "<div style='padding: 15px; border-bottom: 1px solid #eee;'>";
            $mail_content .= "<div style='display: flex; justify-content: space-between; align-items: center;'>";
            $mail_content .= "<div>";
            $mail_content .= "<strong>주문번호:</strong> " . $order['no'] . "<br>";
            
            // 상품 상세 정보 (JSON 파싱)
            if (!empty($order['Type_1'])) {
                $json_data = json_decode($order['Type_1'], true);
                if ($json_data && isset($json_data['formatted_display'])) {
                    $mail_content .= "<div style='margin-top: 10px; font-size: 0.9em; color: #6c757d;'>";
                    $mail_content .= $json_data['formatted_display'];
                    $mail_content .= "</div>";
                }
            }
            $mail_content .= "</div>";
            $mail_content .= "<div style='text-align: right; font-weight: bold; color: #007bff;'>";
            $mail_content .= number_format($order['money_5']) . "원";
            $mail_content .= "</div>";
            $mail_content .= "</div>";
            $mail_content .= "</div>";
            
            $total_amount += intval($order['money_5']);
        }
        
        // 총 금액
        $mail_content .= "<div style='padding: 15px; background: #f8f9fa; border-radius: 0 0 8px 8px; text-align: right;'>";
        $mail_content .= "<h4 style='margin: 0; color: #dc3545;'>총 주문금액: " . number_format($total_amount) . "원</h4>";
        $mail_content .= "</div>";
        $mail_content .= "</div>";
        
        // 회사 정보
        $mail_content .= "<div style='background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;'>";
        $mail_content .= "<h3 style='color: white; margin-bottom: 15px;'>두손기획인쇄</h3>";
        $mail_content .= "<p>📞 02-2632-1830 | 🏢 서울 영등포구 영등포로36길 9, 송호빌딩 1층</p>";
        $mail_content .= "<p>🌐 www.dsp114.com</p>";
        $mail_content .= "</div>";
        
        $mail_content .= "</div>";
        
        // 메일 발송
        $mail_subject = "[두손기획인쇄] 주문이 접수되었습니다 - " . htmlspecialchars($first_order['name']) . "님";
        $from_name = "두손기획인쇄";
        $from_email = "dsp1830@naver.com";
        
        mailer($from_name, $from_email, $email, $mail_subject, $mail_content, 1, "");
        
        // 관리자에게도 메일 발송
        $admin_subject = "[주문알림] " . htmlspecialchars($first_order['name']) . "님 주문 접수";
        mailer($from_name, $from_email, "dsp1830@naver.com", $admin_subject, $mail_content, 1, "");
        
    } catch (Exception $e) {
        error_log("메일 발송 오류: " . $e->getMessage());
    }
}

// 공통 푸터 포함
include "../includes/footer.php";

// 데이터베이스 연결 종료
if ($connect) {
    mysqli_close($connect);
}
?>