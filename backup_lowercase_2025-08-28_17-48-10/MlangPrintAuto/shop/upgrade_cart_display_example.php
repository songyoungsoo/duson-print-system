<?php
/**
 * 파일 정보가 포함된 장바구니 표시 예시
 * 경로: mlangprintauto/shop/upgrade_cart_display_example.php
 * 
 * 기존 cart.php 파일을 이 방식으로 업그레이드하면 됩니다.
 */

session_start();
include "../db.php";
include "../includes/functions.php";
include "shop_temp_helper.php";
include "file_management_helper.php";

$session_id = session_id();

// 파일 정보가 포함된 장바구니 아이템 조회
$cart_items_with_files = getCartItemsWithFiles($db, $session_id);
$total_info = calculateCartTotal($db, $session_id);

// 공통 헤더 포함
include "../includes/header.php";
include "../includes/nav.php";
?>

<div class="container">
    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; text-align: center; padding: 2rem;">
            <h2 style="margin: 0; font-size: 2rem;">🛒 장바구니</h2>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">선택하신 상품들을 확인해보세요</p>
        </div>
        
        <div style="padding: 2rem;">
            <?php if (empty($cart_items_with_files)): ?>
                <!-- 빈 장바구니 -->
                <div style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                    <h3 style="color: #6c757d; margin-bottom: 1rem;">장바구니가 비어있습니다</h3>
                    <p style="color: #6c757d; margin-bottom: 2rem;">원하시는 상품을 선택해보세요</p>
                    <a href="../" class="btn btn-primary">상품 둘러보기</a>
                </div>
            <?php else: ?>
                <!-- 장바구니 아이템 목록 -->
                <div class="cart-items">
                    <?php foreach ($cart_items_with_files as $item): ?>
                        <?php $formatted_item = formatCartItemForDisplay($db, $item); ?>
                        <div class="cart-item" data-item-no="<?php echo $item['no']; ?>">
                            <div class="item-info">
                                <h4><?php echo safe_html($formatted_item['name']); ?></h4>
                                
                                <!-- 상품 옵션 정보 -->
                                <div class="item-details">
                                    <?php foreach ($formatted_item['details'] as $key => $value): ?>
                                        <span class="detail-item">
                                            <strong><?php echo safe_html($key); ?>:</strong> 
                                            <?php echo safe_html($value); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- 첨부 파일 목록 -->
                                <?php if (!empty($item['files'])): ?>
                                    <div class="attached-files">
                                        <h5>📎 첨부된 파일 (<?php echo count($item['files']); ?>개)</h5>
                                        <div class="file-list">
                                            <?php foreach ($item['files'] as $file): ?>
                                                <div class="file-item">
                                                    <span class="file-icon"><?php echo getFileIcon($file['original_name']); ?></span>
                                                    <span class="file-name"><?php echo safe_html($file['original_name']); ?></span>
                                                    <span class="file-size">(<?php echo format_file_size($file['file_size']); ?>)</span>
                                                    <button type="button" class="btn-delete-file" 
                                                            onclick="deleteFile(<?php echo $item['no']; ?>, '<?php echo safe_html($file['saved_name']); ?>')">
                                                        삭제
                                                    </button>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <!-- 추가 파일 업로드 -->
                                        <div class="add-more-files">
                                            <input type="file" id="additional-files-<?php echo $item['no']; ?>" 
                                                   multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ai,.psd" style="display: none;">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="document.getElementById('additional-files-<?php echo $item['no']; ?>').click()">
                                                + 파일 추가
                                            </button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- 파일 업로드 영역 -->
                                    <div class="file-upload-area">
                                        <input type="file" id="files-<?php echo $item['no']; ?>" 
                                               multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ai,.psd" style="display: none;">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="document.getElementById('files-<?php echo $item['no']; ?>').click()">
                                            📎 파일 첨부
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- 요청사항 -->
                                <?php if (!empty($formatted_item['MY_comment'])): ?>
                                    <div class="item-comment">
                                        <strong>요청사항:</strong> <?php echo safe_html($formatted_item['MY_comment']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-actions">
                                <div class="item-price">
                                    <div class="price-amount"><?php echo number_format($formatted_item['st_price_vat']); ?>원</div>
                                    <div class="price-label">VAT 포함</div>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="removeCartItem(<?php echo $item['no']; ?>)">
                                    삭제
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- 총액 및 주문 버튼 -->
                <div class="cart-summary">
                    <div class="total-info">
                        <div class="total-items">총 <?php echo $total_info['count']; ?>개 상품</div>
                        <div class="total-price">
                            <span class="price-label">총 결제금액</span>
                            <span class="price-amount"><?php echo number_format($total_info['total_vat']); ?>원</span>
                        </div>
                    </div>
                    
                    <div class="cart-actions">
                        <button type="button" class="btn btn-outline-secondary" onclick="clearCart()">
                            장바구니 비우기
                        </button>
                        <a href="../MlangOrder_PrintAuto/OnlineOrder_unified.php" class="btn btn-primary btn-lg">
                            주문하기
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// 파일 업로드 처리
document.addEventListener('DOMContentLoaded', function() {
    // 모든 파일 입력에 이벤트 리스너 추가
    document.querySelectorAll('input[type="file"]').forEach(function(input) {
        input.addEventListener('change', function() {
            const cartItemNo = this.id.split('-').pop();
            uploadFiles(cartItemNo, this.files);
        });
    });
});

// 파일 업로드 함수
function uploadFiles(cartItemNo, files) {
    if (files.length === 0) return;
    
    const formData = new FormData();
    formData.append('cart_item_no', cartItemNo);
    formData.append('product_type', 'cart_upload');
    
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }
    
    fetch('file_upload_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('파일이 업로드되었습니다.');
            location.reload(); // 페이지 새로고침
        } else {
            alert('파일 업로드 실패: ' + data.message);
        }
    })
    .catch(error => {
        console.error('업로드 오류:', error);
        alert('파일 업로드 중 오류가 발생했습니다.');
    });
}

// 파일 삭제 함수
function deleteFile(cartItemNo, fileName) {
    if (!confirm('이 파일을 삭제하시겠습니까?')) return;
    
    fetch('file_delete_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            cart_item_no: cartItemNo,
            file_name: fileName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('파일이 삭제되었습니다.');
            location.reload();
        } else {
            alert('파일 삭제 실패: ' + data.message);
        }
    })
    .catch(error => {
        console.error('삭제 오류:', error);
        alert('파일 삭제 중 오류가 발생했습니다.');
    });
}

// 장바구니 아이템 삭제
function removeCartItem(itemNo) {
    if (!confirm('이 상품을 장바구니에서 삭제하시겠습니까?')) return;
    
    fetch('remove_from_basket.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            item_no: itemNo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('삭제 실패: ' + data.message);
        }
    });
}

// 장바구니 비우기
function clearCart() {
    if (!confirm('장바구니를 모두 비우시겠습니까?')) return;
    
    fetch('clear_basket.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('장바구니 비우기 실패: ' + data.message);
        }
    });
}
</script>

<!-- CSS -->
<style>
.cart-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 1rem;
    background: white;
}

.item-info {
    flex: 1;
    margin-right: 2rem;
}

.item-details {
    margin: 0.5rem 0;
}

.detail-item {
    display: inline-block;
    margin-right: 1rem;
    color: #666;
    font-size: 0.9rem;
}

.attached-files {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.file-list {
    margin: 0.5rem 0;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.file-icon {
    margin-right: 0.5rem;
    font-size: 1.2rem;
}

.file-name {
    flex: 1;
    margin-right: 0.5rem;
}

.file-size {
    color: #666;
    font-size: 0.8rem;
    margin-right: 0.5rem;
}

.btn-delete-file {
    background: #dc3545;
    color: white;
    border: none;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    cursor: pointer;
}

.item-actions {
    text-align: right;
}

.item-price {
    margin-bottom: 1rem;
}

.price-amount {
    font-size: 1.2rem;
    font-weight: bold;
    color: #e74c3c;
}

.cart-summary {
    border-top: 2px solid #e9ecef;
    padding-top: 2rem;
    margin-top: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-price .price-amount {
    font-size: 1.5rem;
    font-weight: bold;
    color: #e74c3c;
}
</style>

<?php
include "../includes/footer.php";

// 파일 아이콘 함수
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icon_map = [
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
        'pdf' => '📄', 'ai' => '🎨', 'psd' => '🎨'
    ];
    return $icon_map[$ext] ?? '📎';
}

function format_file_size($bytes) {
    $units = array('B', 'KB', 'MB', 'GB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>