<?php
/**
 * 견적서 상세 정보 AJAX 페이지
 * 경로: /admin/quote_detail.php
 */

include "../db.php";
include "../includes/functions.php";

$quote_number = $_GET['quote_number'] ?? '';

if (empty($quote_number)) {
    echo '<p style="color: red;">견적서 번호가 없습니다.</p>';
    exit;
}

// UTF-8 설정
mysqli_set_charset($db, 'utf8');

// 견적서 기본 정보 조회
$quote_query = "SELECT * FROM quote_log WHERE quote_number = ? LIMIT 1";
$quote_stmt = mysqli_prepare($db, $quote_query);
mysqli_stmt_bind_param($quote_stmt, 's', $quote_number);
mysqli_stmt_execute($quote_stmt);
$quote_result = mysqli_stmt_get_result($quote_stmt);
$quote = mysqli_fetch_assoc($quote_result);

if (!$quote) {
    echo '<p style="color: red;">견적서를 찾을 수 없습니다.</p>';
    exit;
}

// 견적서 상품 상세 정보 조회
$items_query = "SELECT * FROM quote_items WHERE quote_number = ? ORDER BY no ASC";
$items_stmt = mysqli_prepare($db, $items_query);
mysqli_stmt_bind_param($items_stmt, 's', $quote_number);
mysqli_stmt_execute($items_stmt);
$items_result = mysqli_stmt_get_result($items_stmt);

// 상품명 매핑 함수
function getProductDisplayName($product_type) {
    $names = [
        'sticker' => '일반 스티커',
        'msticker' => '자석 스티커', 
        'namecard' => '명함',
        'envelope' => '봉투',
        'merchandisebond' => '상품권',
        'cadarok' => '카다록',
        'inserted' => '전단지',
        'littleprint' => '포스터',
        'ncrflambeau' => '양식지'
    ];
    return $names[$product_type] ?? $product_type;
}

// 관리자 조회 표시 업데이트
if ($quote['admin_viewed'] == 0) {
    $update_query = "UPDATE quote_log SET admin_viewed = 1, admin_viewed_at = NOW() WHERE quote_number = ?";
    $update_stmt = mysqli_prepare($db, $update_query);
    mysqli_stmt_bind_param($update_stmt, 's', $quote_number);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);
}

mysqli_stmt_close($quote_stmt);
?>

<style>
.detail-section {
    margin: 1rem 0;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #f9f9f9;
}

.detail-title {
    font-size: 1.1rem;
    font-weight: bold;
    color: #2c5aa0;
    margin-bottom: 0.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #ddd;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin: 1rem 0;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-label {
    font-weight: 600;
    color: #555;
    font-size: 0.9rem;
}

.info-value {
    font-size: 1rem;
    margin-top: 0.25rem;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

.items-table th,
.items-table td {
    padding: 0.75rem;
    text-align: left;
    border: 1px solid #ddd;
    font-size: 0.9rem;
}

.items-table th {
    background: #f5f5f5;
    font-weight: 600;
}

.items-table tbody tr:nth-child(even) {
    background: #fafafa;
}

.total-amount {
    font-size: 1.2rem;
    font-weight: bold;
    color: #e74c3c;
    text-align: right;
    margin: 1rem 0;
    padding: 1rem;
    background: #fff3cd;
    border-radius: 5px;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-generated { background: #e3f2fd; color: #1976d2; }
.status-viewed { background: #fff3e0; color: #f57c00; }
.status-ordered { background: #e8f5e8; color: #388e3c; }
.status-cancelled { background: #ffebee; color: #d32f2f; }
</style>

<div class="detail-section">
    <div class="detail-title">📋 견적서 기본 정보</div>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">견적번호</span>
            <span class="info-value"><strong><?php echo htmlspecialchars($quote['quote_number']); ?></strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">발송일시</span>
            <span class="info-value"><?php echo date('Y-m-d H:i:s', strtotime($quote['created_at'])); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">상태</span>
            <span class="info-value">
                <span class="status-badge status-<?php echo $quote['status']; ?>">
                    <?php 
                    $status_labels = [
                        'generated' => '발송완료',
                        'viewed' => '확인됨',
                        'ordered' => '주문완료',
                        'cancelled' => '취소됨'
                    ];
                    echo $status_labels[$quote['status']] ?? $quote['status'];
                    ?>
                </span>
            </span>
        </div>
        <div class="info-item">
            <span class="info-label">관리자 확인</span>
            <span class="info-value">
                <?php if ($quote['admin_viewed']): ?>
                    ✅ 확인됨 (<?php echo $quote['admin_viewed_at'] ? date('m-d H:i', strtotime($quote['admin_viewed_at'])) : '방금'; ?>)
                <?php else: ?>
                    ⚠️ 미확인
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>

<div class="detail-section">
    <div class="detail-title">👤 고객 정보</div>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">고객명</span>
            <span class="info-value"><strong><?php echo htmlspecialchars($quote['customer_name']); ?></strong></span>
        </div>
        <div class="info-item">
            <span class="info-label">연락처</span>
            <span class="info-value"><?php echo htmlspecialchars($quote['customer_phone']); ?></span>
        </div>
        <?php if (!empty($quote['customer_company'])): ?>
        <div class="info-item">
            <span class="info-label">회사명</span>
            <span class="info-value"><?php echo htmlspecialchars($quote['customer_company']); ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($quote['customer_email'])): ?>
        <div class="info-item">
            <span class="info-label">이메일</span>
            <span class="info-value"><?php echo htmlspecialchars($quote['customer_email']); ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php if (!empty($quote['quote_memo'])): ?>
    <div class="info-item" style="margin-top: 1rem;">
        <span class="info-label">요청사항</span>
        <div class="info-value" style="margin-top: 0.5rem; padding: 0.75rem; background: white; border-radius: 5px; border: 1px solid #ddd;">
            <?php echo nl2br(htmlspecialchars($quote['quote_memo'])); ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="detail-section">
    <div class="detail-title">📦 주문 상품 상세</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>상품명</th>
                <th>상세 옵션</th>
                <th>수량/크기</th>
                <th>기본금액</th>
                <th>VAT포함</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total_base = 0;
            $total_vat_included = 0;
            
            while ($item = mysqli_fetch_assoc($items_result)): 
                $product_name = getProductDisplayName($item['product_type']);
                $base_price = intval($item['st_price']);
                $vat_price = intval($item['st_price_vat']);
                
                $total_base += $base_price;
                $total_vat_included += $vat_price;
                
                // 상세 옵션 구성
                $details = [];
                if (!empty($item['jong'])) $details[] = '재질: ' . $item['jong'];
                if (!empty($item['garo']) && !empty($item['sero'])) {
                    $details[] = '크기: ' . $item['garo'] . '×' . $item['sero'] . 'mm';
                }
                if (!empty($item['mesu'])) $details[] = '수량: ' . number_format($item['mesu']) . '매';
                if (!empty($item['domusong'])) $details[] = '모양: ' . $item['domusong'];
                if (!empty($item['MY_type'])) $details[] = '구분: ' . $item['MY_type'];
                if (!empty($item['PN_type'])) $details[] = '종류: ' . $item['PN_type'];
                if (!empty($item['POtype'])) $details[] = '인쇄면: ' . ($item['POtype'] == '1' ? '단면' : '양면');
                
                $details_text = implode('<br>', $details);
                $quantity_info = '';
                if (!empty($item['mesu'])) $quantity_info .= number_format($item['mesu']) . '매';
                if (!empty($item['garo']) && !empty($item['sero'])) {
                    $quantity_info .= ($quantity_info ? '<br>' : '') . $item['garo'] . '×' . $item['sero'] . 'mm';
                }
            ?>
            <tr>
                <td><strong><?php echo $product_name; ?></strong></td>
                <td><?php echo $details_text ?: '-'; ?></td>
                <td><?php echo $quantity_info ?: '-'; ?></td>
                <td style="text-align: right;"><?php echo number_format($base_price); ?>원</td>
                <td style="text-align: right;"><strong><?php echo number_format($vat_price); ?>원</strong></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3">합계</td>
                <td style="text-align: right;"><?php echo number_format($total_base); ?>원</td>
                <td style="text-align: right; color: #e74c3c;"><?php echo number_format($total_vat_included); ?>원</td>
            </tr>
        </tfoot>
    </table>
</div>

<div class="detail-section">
    <div class="detail-title">🔍 시스템 정보</div>
    <div class="info-grid">
        <div class="info-item">
            <span class="info-label">세션 ID</span>
            <span class="info-value" style="font-family: monospace; font-size: 0.8rem;"><?php echo htmlspecialchars(substr($quote['session_id'], 0, 16) . '...'); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">IP 주소</span>
            <span class="info-value"><?php echo htmlspecialchars($quote['ip_address']); ?></span>
        </div>
        <div class="info-item">
            <span class="info-label">총 상품 수</span>
            <span class="info-value"><?php echo number_format($quote['total_items']); ?>개</span>
        </div>
        <div class="info-item">
            <span class="info-label">브라우저</span>
            <span class="info-value" style="font-size: 0.8rem; color: #666;">
                <?php 
                $ua = $quote['user_agent'];
                if (strpos($ua, 'Chrome') !== false) echo '🔵 Chrome';
                elseif (strpos($ua, 'Firefox') !== false) echo '🟠 Firefox';
                elseif (strpos($ua, 'Safari') !== false && strpos($ua, 'Chrome') === false) echo '🔵 Safari';
                elseif (strpos($ua, 'Edge') !== false) echo '🔵 Edge';
                else echo '❓ 기타';
                
                if (strpos($ua, 'Mobile') !== false) echo ' (모바일)';
                ?>
            </span>
        </div>
    </div>
    
    <?php if (!empty($quote['notes'])): ?>
    <div class="info-item" style="margin-top: 1rem;">
        <span class="info-label">관리자 메모</span>
        <div class="info-value" style="margin-top: 0.5rem; padding: 0.75rem; background: #e8f5e8; border-radius: 5px; border: 1px solid #c8e6c9;">
            <?php echo nl2br(htmlspecialchars($quote['notes'])); ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="total-amount">
    💰 총 견적 금액: <?php echo number_format($quote['total_price_vat']); ?>원 (VAT 포함)
</div>

<?php
mysqli_stmt_close($items_stmt);
mysqli_close($db);
?>