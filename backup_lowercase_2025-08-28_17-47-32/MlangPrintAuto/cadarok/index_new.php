<?php 
/**
 * 카다록 주문 페이지 (통합 장바구니 연동 버전)
 * 경로: mlangprintauto/cadarok/index_new.php
 */

session_start(); 
$session_id = session_id();

// 데이터베이스 연결
include "../../db.php";
$connect = $db;

// 공통 인증 처리
include "../../includes/auth.php";

// 헬퍼 함수 포함
include "../shop_temp_helper.php";

// 페이지 설정
$page_title = '📖 두손기획인쇄 - 카다록 제작';
$current_page = 'cadarok';

// 카다록 관련 설정
$page = "cadarok";
$GGTABLE = "MlangPrintAuto_transactionCate";

// 드롭다운 옵션을 가져오는 함수
function getOptions($connect, $GGTABLE, $page, $BigNo) {
    $options = [];
    $res = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='$BigNo' ORDER BY no ASC");
    while ($row = mysqli_fetch_assoc($res)) {
        $options[] = $row;
    }
    return $options;
}

// 초기 구분값 가져오기
$initial_type = "";
$type_result = mysqli_query($connect, "SELECT no, title FROM $GGTABLE WHERE Ttable='$page' AND BigNo='0' ORDER BY no ASC LIMIT 1");
if ($type_row = mysqli_fetch_assoc($type_result)) {
    $initial_type = $type_row['no'];
}

// 초기 규격 옵션 가져오기
$size_options = getOptions($connect, $GGTABLE, $page, $initial_type);
$initial_size = "";
if (!empty($size_options)) {
    $initial_size = $size_options[0]['no'];
}

// 공통 헤더 포함
include "../../includes/header.php";

// 네비게이션 포함
include "../../includes/nav.php";
?>

<div class="container">
    <!-- 주문 폼 -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📖 카다록 주문 옵션 선택</h2>
            <p class="card-subtitle">고품질 카다록을 합리적인 가격으로 제작해드립니다</p>
        </div>
        
        <form id="cadarokOrderForm" method="post">
            <input type="hidden" name="product_type" value="cadarok">
            <input type="hidden" name="action" value="calculate">
            
            <table class="order-form-table">
                <tbody>
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📋</span>
                                <span>1. 구분 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="MY_type" id="type_select" class="form-control-modern" onchange="updateSizes()">
                                <?php
                                $type_options = getOptions($connect, $GGTABLE, $page, '0');
                                foreach ($type_options as $option) {
                                    $selected = ($option['no'] == $initial_type) ? 'selected' : '';
                                    echo "<option value='{$option['no']}' $selected>{$option['title']}</option>";
                                }
                                ?>
                            </select>
                            <small class="help-text">카다록의 종류를 선택해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📏</span>
                                <span>2. 규격 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="MY_Fsd" id="size_select" class="form-control-modern" onchange="updatePaperTypes()">
                                <?php
                                foreach ($size_options as $option) {
                                    $selected = ($option['no'] == $initial_size) ? 'selected' : '';
                                    echo "<option value='{$option['no']}' $selected>{$option['title']}</option>";
                                }
                                ?>
                            </select>
                            <small class="help-text">카다록의 규격을 선택해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📄</span>
                                <span>3. 용지 선택</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="PN_type" id="paper_select" class="form-control-modern">
                                <option value="">용지를 선택해주세요</option>
                            </select>
                            <small class="help-text">용지 종류를 선택해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">📦</span>
                                <span>4. 수량 입력</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <input type="number" name="MY_amount" class="form-control-modern" 
                                   placeholder="수량을 입력하세요" min="1" max="100000" value="100" required>
                            <small class="help-text">제작하실 수량을 입력해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">🎨</span>
                                <span>5. 주문 타입</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <select name="ordertype" class="form-control-modern">
                                <option value="print">인쇄만 (파일 준비완료)</option>
                                <option value="design">디자인+인쇄 (+디자인비)</option>
                            </select>
                            <small class="help-text">디자인이 필요하시면 디자인+인쇄를 선택해주세요</small>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="label-cell">
                            <div class="icon-label">
                                <span class="icon">💬</span>
                                <span>6. 요청사항</span>
                            </div>
                        </td>
                        <td class="input-cell">
                            <textarea name="MY_comment" class="form-control-modern" rows="3" 
                                      placeholder="특별한 요청사항이 있으시면 입력해주세요"></textarea>
                            <small class="help-text">색상, 마감, 배송 등 요청사항을 자세히 적어주세요</small>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div style="text-align: center; margin: 3rem 0;">
                <button type="button" onclick="calculateCadarokPrice()" class="btn-calculate">
                    💰 실시간 가격 계산하기
                </button>
            </div>
        </form>
    </div>
    
    <!-- 가격 계산 결과 -->
    <div id="priceSection" class="price-result" style="display: none;">
        <h3>💎 견적 결과</h3>
        <div class="price-amount" id="priceAmount">0원</div>
        <div>부가세 포함: <span id="priceVat" style="font-size: 1.5rem; font-weight: 700;">0원</span></div>
        
        <div class="action-buttons">
            <button onclick="addCadarokToBasket()" class="btn-action btn-primary">
                🛒 장바구니에 담기
            </button>
            <a href="../shop/cart.php" class="btn-action btn-secondary">
                👀 장바구니 보기
            </a>
        </div>
    </div>
    
    <!-- 최근 주문 내역 -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">📋 최근 카다록 주문 내역</h3>
            <p class="card-subtitle">현재 세션의 주문 내역입니다</p>
        </div>
        
        <table class="modern-table">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>구분</th>
                    <th>규격</th>
                    <th>용지</th>
                    <th>수량</th>
                    <th>타입</th>
                    <th>금액</th>
                    <th>VAT포함</th>
                    <th>삭제</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 카다록 주문 내역 조회
                $query = "SELECT * FROM shop_temp WHERE session_id='$session_id' AND product_type='cadarok' ORDER BY no DESC LIMIT 5";  
                $result = mysqli_query($connect, $query);
                
                if (mysqli_num_rows($result) > 0) {
                    while ($data = mysqli_fetch_array($result)) {
                        ?>
                        <tr>
                            <td><?php echo $data['no'] ?></td>
                            <td><?php echo getCategoryName($connect, $data['MY_type']); ?></td>
                            <td><?php echo getCategoryName($connect, $data['MY_Fsd']); ?></td>
                            <td><?php echo getCategoryName($connect, $data['PN_type']); ?></td>
                            <td><?php echo $data['MY_amount']; ?>부</td>
                            <td><?php echo $data['ordertype'] === 'design' ? '디자인+인쇄' : '인쇄만'; ?></td>
                            <td><strong><?php echo number_format($data['st_price']) ?>원</strong></td>
                            <td><strong><?php echo number_format($data['st_price_vat']) ?>원</strong></td>
                            <td>
                                <button onclick="removeCartItem(<?php echo $data['no'] ?>)" 
                                        class="btn-action btn-secondary" style="padding: 8px 15px; font-size: 0.9rem;">
                                    삭제
                                </button>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="9" class="empty-state">
                            <div>
                                <h4>📭 주문 내역이 없습니다</h4>
                                <p>첫 번째 카다록 주문을 시작해보세요!</p>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// 구분 변경 시 규격 업데이트
function updateSizes() {
    const typeSelect = document.getElementById('type_select');
    const sizeSelect = document.getElementById('size_select');
    const selectedType = typeSelect.value;
    
    fetch('get_sizes_new.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'type=' + encodeURIComponent(selectedType)
    })
    .then(response => response.json())
    .then(data => {
        sizeSelect.innerHTML = '';
        data.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.no;
            optionElement.textContent = option.title;
            sizeSelect.appendChild(optionElement);
        });
        
        // 첫 번째 규격 선택 후 용지 업데이트
        if (data.length > 0) {
            updatePaperTypes();
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// 규격 변경 시 용지 업데이트
function updatePaperTypes() {
    const sizeSelect = document.getElementById('size_select');
    const paperSelect = document.getElementById('paper_select');
    const selectedSize = sizeSelect.value;
    
    fetch('get_paper_types_new.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'size=' + encodeURIComponent(selectedSize)
    })
    .then(response => response.json())
    .then(data => {
        paperSelect.innerHTML = '';
        data.forEach(option => {
            const optionElement = document.createElement('option');
            optionElement.value = option.no;
            optionElement.textContent = option.title;
            paperSelect.appendChild(optionElement);
        });
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// 가격 계산 함수
function calculateCadarokPrice() {
    const formData = new FormData(document.getElementById('cadarokOrderForm'));
    
    fetch('price_cal_new.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('priceAmount').textContent = data.price + '원';
            document.getElementById('priceVat').textContent = data.price_vat + '원';
            document.getElementById('priceSection').style.display = 'block';
        } else {
            alert('가격 계산 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('가격 계산 중 오류가 발생했습니다.');
    });
}

// 장바구니 추가 함수
function addCadarokToBasket() {
    const formData = new FormData(document.getElementById('cadarokOrderForm'));
    formData.append('action', 'add_to_basket');
    
    // 현재 계산된 가격 추가
    const priceText = document.getElementById('priceAmount').textContent;
    const priceVatText = document.getElementById('priceVat').textContent;
    const price = parseInt(priceText.replace(/[^0-9]/g, ''));
    const priceVat = parseInt(priceVatText.replace(/[^0-9]/g, ''));
    
    formData.append('st_price', price);
    formData.append('st_price_vat', priceVat);
    
    fetch('../shop/add_to_basket.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('카다록이 장바구니에 추가되었습니다!');
            location.reload(); // 페이지 새로고침으로 최근 주문 내역 업데이트
        } else {
            alert('장바구니 추가 중 오류가 발생했습니다: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('장바구니 추가 중 오류가 발생했습니다.');
    });
}

// 장바구니 아이템 삭제
function removeCartItem(itemNo) {
    if (confirm('이 상품을 장바구니에서 삭제하시겠습니까?')) {
        const formData = new FormData();
        formData.append('no', itemNo);
        
        fetch('../shop/remove_from_basket.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('상품이 삭제되었습니다.');
                location.reload();
            } else {
                alert('삭제 중 오류가 발생했습니다: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('삭제 중 오류가 발생했습니다.');
        });
    }
}

// 페이지 로드 시 초기 용지 타입 로드
document.addEventListener('DOMContentLoaded', function() {
    updatePaperTypes();
});
</script>

<?php
// 로그인 모달 포함
include "../../includes/login_modal.php";

// 공통 푸터 포함
include "../../includes/footer.php";
?>