<?php
/**
 * 통합 장바구니 사용 예시
 * 경로: MlangPrintAuto/usage_example.php
 */

session_start();
include "../db.php";
include "shop_temp_helper.php";

$connect = $db;
$session_id = session_id();

// 페이지 설정
$page_title = '🧪 통합 장바구니 테스트';
$current_page = 'test';

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🧪 통합 장바구니 시스템 테스트</h2>
            <p class="card-subtitle">모든 상품 유형의 장바구니 기능을 테스트해보세요</p>
        </div>
        
        <div style="padding: 2rem;">
            <h3>📋 테스트 메뉴</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem; margin: 2rem 0;">
                <!-- 테이블 설치 -->
                <div class="test-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 10px;">
                    <h4>🔧 1. 테이블 설치</h4>
                    <p>통합 장바구니 테이블을 생성합니다</p>
                    <a href="shop/install_table.php" class="btn btn-primary" target="_blank">테이블 설치하기</a>
                </div>
                
                <!-- 장바구니 확인 -->
                <div class="test-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 10px;">
                    <h4>🛒 2. 장바구니 확인</h4>
                    <p>현재 장바구니 내용을 확인합니다</p>
                    <a href="shop/cart.php" class="btn btn-secondary" target="_blank">장바구니 보기</a>
                </div>
                
                <!-- 상품 페이지들 -->
                <div class="test-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 10px;">
                    <h4>📖 3. 카다록 주문</h4>
                    <p>카다록 주문 페이지 (통합 장바구니 연동)</p>
                    <a href="cadarok/index_new.php" class="btn btn-success" target="_blank">카다록 주문하기</a>
                </div>
                
                <div class="test-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 10px;">
                    <h4>🏷️ 4. 스티커 주문</h4>
                    <p>스티커 주문 페이지 (공통 인클루드 적용)</p>
                    <a href="shop/view_modern_new.php" class="btn btn-info" target="_blank">스티커 주문하기</a>
                </div>
                
                <div class="test-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 10px;">
                    <h4>📇 5. 명함 주문</h4>
                    <p>명함 주문 페이지 (준비 중)</p>
                    <a href="NameCard/index.php" class="btn btn-warning" target="_blank">명함 주문하기</a>
                </div>
                
                <div class="test-card" style="border: 1px solid #ddd; padding: 1.5rem; border-radius: 10px;">
                    <h4>📄 6. 전단지 주문</h4>
                    <p>전단지 주문 페이지 (준비 중)</p>
                    <a href="inserted/index.php" class="btn btn-warning" target="_blank">전단지 주문하기</a>
                </div>
            </div>
            
            <hr style="margin: 3rem 0;">
            
            <h3>🧪 API 테스트</h3>
            
            <div style="margin: 2rem 0;">
                <h4>장바구니 API 테스트</h4>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin: 1rem 0;">
                    <button onclick="testAddSticker()" class="btn btn-primary">스티커 추가 테스트</button>
                    <button onclick="testAddCadarok()" class="btn btn-primary">카다록 추가 테스트</button>
                    <button onclick="testGetItems()" class="btn btn-secondary">장바구니 조회</button>
                    <button onclick="testClearCart()" class="btn btn-danger">장바구니 비우기</button>
                </div>
                
                <div id="testResults" style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 1rem; min-height: 100px;">
                    <strong>테스트 결과:</strong><br>
                    <span id="resultText">테스트 버튼을 클릭해보세요.</span>
                </div>
            </div>
            
            <hr style="margin: 3rem 0;">
            
            <h3>📊 현재 장바구니 상태</h3>
            
            <?php
            // 현재 장바구니 아이템 조회
            $cart_result = getCartItems($connect, $session_id);
            $cart_items = [];
            
            if ($cart_result) {
                while ($item = mysqli_fetch_assoc($cart_result)) {
                    $cart_items[] = formatCartItemForDisplay($connect, $item);
                }
            }
            
            if (!empty($cart_items)): ?>
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>상품명</th>
                            <th>상세정보</th>
                            <th>가격</th>
                            <th>VAT포함</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo $item['no']; ?></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>
                                <?php foreach ($item['details'] as $key => $value): ?>
                                    <small><?php echo htmlspecialchars($key); ?>: <?php echo htmlspecialchars($value); ?></small><br>
                                <?php endforeach; ?>
                            </td>
                            <td><?php echo number_format($item['st_price']); ?>원</td>
                            <td><strong><?php echo number_format($item['st_price_vat']); ?>원</strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php
                $total_info = calculateCartTotal($connect, $session_id);
                ?>
                <div style="text-align: right; margin-top: 1rem; padding: 1rem; background: #e8f5e8; border-radius: 5px;">
                    <strong>총 <?php echo $total_info['count']; ?>개 상품 | 총액: <?php echo number_format($total_info['total_vat']); ?>원 (VAT 포함)</strong>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #666;">
                    <h4>📭 장바구니가 비어있습니다</h4>
                    <p>위의 상품 페이지에서 상품을 추가해보세요!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// 스티커 추가 테스트
function testAddSticker() {
    const formData = new FormData();
    formData.append('product_type', 'sticker');
    formData.append('jong', 'jsp 투명스티커');
    formData.append('garo', '100');
    formData.append('sero', '100');
    formData.append('mesu', '1000');
    formData.append('domusong', '00000 사각');
    formData.append('uhyung', '0');
    formData.append('st_price', '50000');
    formData.append('st_price_vat', '55000');
    
    fetch('shop/add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('resultText').innerHTML = 
            '<strong>스티커 추가 결과:</strong><br>' + 
            JSON.stringify(data, null, 2);
        if (data.success) {
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        document.getElementById('resultText').innerHTML = 
            '<strong>오류:</strong><br>' + error.message;
    });
}

// 카다록 추가 테스트
function testAddCadarok() {
    const formData = new FormData();
    formData.append('product_type', 'cadarok');
    formData.append('MY_type', '691');
    formData.append('MY_Fsd', '697');
    formData.append('PN_type', '699');
    formData.append('MY_amount', '100');
    formData.append('ordertype', 'print');
    formData.append('st_price', '80000');
    formData.append('st_price_vat', '88000');
    formData.append('MY_comment', '테스트 주문입니다');
    
    fetch('shop/add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('resultText').innerHTML = 
            '<strong>카다록 추가 결과:</strong><br>' + 
            JSON.stringify(data, null, 2);
        if (data.success) {
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        document.getElementById('resultText').innerHTML = 
            '<strong>오류:</strong><br>' + error.message;
    });
}

// 장바구니 조회 테스트
function testGetItems() {
    fetch('shop/get_basket_items.php')
    .then(response => response.json())
    .then(data => {
        document.getElementById('resultText').innerHTML = 
            '<strong>장바구니 조회 결과:</strong><br>' + 
            JSON.stringify(data, null, 2);
    })
    .catch(error => {
        document.getElementById('resultText').innerHTML = 
            '<strong>오류:</strong><br>' + error.message;
    });
}

// 장바구니 비우기 테스트
function testClearCart() {
    if (confirm('장바구니를 비우시겠습니까?')) {
        fetch('shop/clear_basket.php', {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('resultText').innerHTML = 
                '<strong>장바구니 비우기 결과:</strong><br>' + 
                JSON.stringify(data, null, 2);
            if (data.success) {
                setTimeout(() => location.reload(), 1000);
            }
        })
        .catch(error => {
            document.getElementById('resultText').innerHTML = 
                '<strong>오류:</strong><br>' + error.message;
        });
    }
}
</script>

<?php
// 공통 푸터 포함
include "../includes/footer.php";
?>