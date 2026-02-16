<?php
/**
 * 견적서 관리 - 상세 페이지
 * 두손기획인쇄 관리자용
 */
session_start();
require_once __DIR__ . '/../../db.php';
$conn = $db;

mysqli_set_charset($db, 'utf8mb4');

// ID 확인
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: quotation_list.php');
    exit;
}

// 상태 업데이트 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $new_status = $_POST['status'] ?? '';
        $allowed_statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired'];
        if (in_array($new_status, $allowed_statuses)) {
            $stmt = mysqli_prepare($db, "UPDATE quotations SET status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $new_status, $id);
            mysqli_stmt_execute($stmt);
        }
    }
    header("Location: quotation_detail.php?id={$id}&updated=1");
    exit;
}

// 견적서 조회
$stmt = mysqli_prepare($db, "SELECT * FROM quotations WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$quotation = mysqli_fetch_assoc($result);

if (!$quotation) {
    header('Location: quotation_list.php');
    exit;
}

// 장바구니 아이템 파싱
$cart_items = json_decode($quotation['cart_items_json'], true) ?? [];
$custom_items = json_decode($quotation['custom_items_json'], true) ?? [];

// 제품명 매핑
$product_names = [
    'cadarok' => '카달로그',
    'sticker' => '스티커',
    'msticker' => '자석스티커',
    'leaflet' => '전단지',
    'namecard' => '명함',
    'envelope' => '봉투',
    'merchandisebond' => '상품권',
    'littleprint' => '포스터',
    'ncrflambeau' => '양식지',
    'inserted' => '전단지'
];

// 상태 라벨
$status_labels = [
    'draft' => ['label' => '작성중', 'color' => '#6c757d'],
    'sent' => ['label' => '발송완료', 'color' => '#0d6efd'],
    'accepted' => ['label' => '승인', 'color' => '#198754'],
    'rejected' => ['label' => '거절', 'color' => '#dc3545'],
    'expired' => ['label' => '만료', 'color' => '#6c757d']
];

// 한글 이름 조회 함수
function getKoreanName($db, $code) {
    if (empty($code)) return '';
    $stmt = mysqli_prepare($db, "SELECT sec_name FROM mlangprintauto_sectioncode WHERE sec_no = ?");
    mysqli_stmt_bind_param($stmt, "s", $code);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['sec_name'] ?? $code;
}

// 상품 규격 표시 함수
function getProductSpecs($item, $db) {
    $product_type = $item['product_type'] ?? '';
    $specs = [];
    
    switch ($product_type) {
        case 'sticker':
            $jong = $item['jong'] ?? '';
            $jong = preg_replace('/^jil\s*/i', '', $jong);
            if (!empty($jong)) $specs[] = '재질: ' . $jong;
            if (!empty($item['garo']) && !empty($item['sero'])) {
                $specs[] = '크기: ' . $item['garo'] . 'mm × ' . $item['sero'] . 'mm';
            }
            $domusong = $item['domusong'] ?? '';
            $domusong = preg_replace('/^[0\s]+/', '', $domusong);
            if (!empty($domusong)) $specs[] = '모양: ' . $domusong;
            break;
            
        case 'msticker':
            if (!empty($item['MY_type'])) $specs[] = '종류: ' . getKoreanName($db, $item['MY_type']);
            if (!empty($item['Section'])) $specs[] = '규격: ' . getKoreanName($db, $item['Section']);
            break;
            
        case 'envelope':
            if (!empty($item['MY_type'])) $specs[] = '종류: ' . getKoreanName($db, $item['MY_type']);
            if (!empty($item['Section'])) $specs[] = '재질: ' . getKoreanName($db, $item['Section']);
            break;
            
        default:
            if (!empty($item['MY_type'])) $specs[] = '종류: ' . getKoreanName($db, $item['MY_type']);
            if (!empty($item['PN_type'])) $specs[] = '규격: ' . getKoreanName($db, $item['PN_type']);
            if (!empty($item['Section'])) $specs[] = '재질: ' . getKoreanName($db, $item['Section']);
            break;
    }
    
    if (!empty($item['POtype'])) {
        $specs[] = '인쇄: ' . getPOtypeLabel($item['product_type'] ?? '', $item['POtype'], $item['POtype_name'] ?? '');
    }
    
    return implode(' / ', $specs);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>견적서 상세 - <?php echo htmlspecialchars($quotation['quotation_no']); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Malgun Gothic', sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .back-link { display: inline-block; margin-bottom: 15px; color: #0d6efd; text-decoration: none; font-size: 14px; }
        .back-link:hover { text-decoration: underline; }
        
        .card { background: #fff; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: #f8f9fa; padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .card-header h2 { font-size: 18px; color: #333; }
        .card-body { padding: 20px; }
        
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .info-item { }
        .info-item label { display: block; font-size: 12px; color: #888; margin-bottom: 4px; }
        .info-item .value { font-size: 15px; color: #333; font-weight: 500; }
        
        .status-badge { padding: 6px 14px; border-radius: 16px; font-size: 13px; font-weight: 500; color: #fff; display: inline-block; }
        
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #333; font-size: 13px; }
        td { font-size: 14px; color: #555; }
        .text-right { text-align: right; }
        
        .price-summary { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .price-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
        .price-row.total { border-top: 2px solid #333; margin-top: 10px; padding-top: 15px; font-size: 18px; font-weight: bold; color: #0d6efd; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #0d6efd; color: #fff; }
        .btn-primary:hover { background: #0b5ed7; }
        .btn-success { background: #198754; color: #fff; }
        .btn-success:hover { background: #157347; }
        .btn-outline { background: #fff; border: 1px solid #ddd; color: #333; }
        .btn-outline:hover { background: #f8f9fa; }
        
        .action-bar { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        
        .status-form { display: flex; gap: 10px; align-items: center; }
        .status-form select { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        
        .public-link { background: #e7f3ff; padding: 15px; border-radius: 8px; margin-top: 15px; }
        .public-link label { font-size: 12px; color: #666; margin-bottom: 5px; display: block; }
        .public-link input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 13px; }
        .public-link .copy-btn { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="quotation_list.php" class="back-link">← 견적서 목록으로</a>
        
        <?php if (isset($_GET['updated'])): ?>
            <div class="alert alert-success">견적서가 업데이트되었습니다.</div>
        <?php endif; ?>

        <!-- 기본 정보 -->
        <div class="card">
            <div class="card-header">
                <h2>📋 견적서 정보</h2>
                <?php
                $status = $quotation['status'];
                $label = $status_labels[$status]['label'] ?? $status;
                $color = $status_labels[$status]['color'] ?? '#6c757d';
                ?>
                <span class="status-badge" style="background: <?php echo $color; ?>"><?php echo $label; ?></span>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>견적번호</label>
                        <div class="value"><?php echo htmlspecialchars($quotation['quotation_no']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>고객명</label>
                        <div class="value"><?php echo htmlspecialchars($quotation['customer_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>이메일</label>
                        <div class="value"><?php echo htmlspecialchars($quotation['customer_email'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <label>연락처</label>
                        <div class="value"><?php echo htmlspecialchars($quotation['customer_phone'] ?? '-'); ?></div>
                    </div>
                    <div class="info-item">
                        <label>작성일</label>
                        <div class="value"><?php echo date('Y-m-d H:i', strtotime($quotation['created_at'])); ?></div>
                    </div>
                    <div class="info-item">
                        <label>유효기간</label>
                        <div class="value"><?php echo $quotation['expires_at'] ? date('Y-m-d', strtotime($quotation['expires_at'])) : '-'; ?></div>
                    </div>
                </div>
                
                <?php if (!empty($quotation['public_token'])): ?>
                <div class="public-link">
                    <label>고객 공개 링크 (이 링크를 고객에게 전달하세요)</label>
                    <?php
                    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
                    $public_url = $base_url . '/mlangprintauto/shop/quotation_view.php?token=' . $quotation['public_token'];
                    ?>
                    <input type="text" id="publicUrl" value="<?php echo htmlspecialchars($public_url); ?>" readonly onclick="this.select()">
                    <button type="button" class="btn btn-outline copy-btn" onclick="copyLink()">링크 복사</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- 상품 목록 -->
        <div class="card">
            <div class="card-header">
                <h2>📦 주문 상품</h2>
            </div>
            <div class="card-body" style="padding: 0;">
                <table>
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>품목</th>
                            <th>규격/옵션</th>
                            <th>수량</th>
                            <th class="text-right">금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo $product_names[$item['product_type']] ?? '인쇄상품'; ?></strong></td>
                                <td style="font-size: 13px; color: #666;">
                                    <?php echo htmlspecialchars(getProductSpecs($item, $db)); ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($item['mesu'])) {
                                        echo number_format($item['mesu']);
                                    } elseif (!empty($item['MY_amount'])) {
                                        echo htmlspecialchars($item['MY_amount']);
                                    } else {
                                        echo '1';
                                    }
                                    ?>
                                </td>
                                <td class="text-right"><?php echo number_format(intval($item['st_price'] ?? 0)); ?>원</td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (!empty($custom_items)): ?>
                            <?php foreach ($custom_items as $index => $item): ?>
                                <tr>
                                    <td><?php echo count($cart_items) + $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($item['name'] ?? '추가항목'); ?></strong></td>
                                    <td style="font-size: 13px; color: #666;"><?php echo htmlspecialchars($item['spec'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($item['quantity'] ?? 1); ?></td>
                                    <td class="text-right"><?php echo number_format(intval($item['price'] ?? 0)); ?>원</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="price-summary">
                    <div class="price-row">
                        <span>공급가액</span>
                        <span><?php echo number_format($quotation['total_supply']); ?>원</span>
                    </div>
                    <div class="price-row">
                        <span>부가세 (10%)</span>
                        <span><?php echo number_format($quotation['total_vat']); ?>원</span>
                    </div>
                    <?php if ($quotation['delivery_price'] > 0): ?>
                    <div class="price-row">
                        <span>배송비 (<?php echo htmlspecialchars($quotation['delivery_type'] ?? '택배'); ?>)</span>
                        <span><?php echo number_format($quotation['delivery_price']); ?>원</span>
                    </div>
                    <?php endif; ?>
                    <div class="price-row total">
                        <span>총 합계</span>
                        <span><?php echo number_format($quotation['total_price']); ?>원</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 관리 기능 -->
        <div class="card">
            <div class="card-header">
                <h2>⚙️ 관리</h2>
            </div>
            <div class="card-body">
                <form method="POST" class="status-form">
                    <input type="hidden" name="action" value="update_status">
                    <label>상태 변경:</label>
                    <select name="status">
                        <option value="draft" <?php echo $quotation['status'] == 'draft' ? 'selected' : ''; ?>>작성중</option>
                        <option value="sent" <?php echo $quotation['status'] == 'sent' ? 'selected' : ''; ?>>발송완료</option>
                        <option value="accepted" <?php echo $quotation['status'] == 'accepted' ? 'selected' : ''; ?>>승인</option>
                        <option value="rejected" <?php echo $quotation['status'] == 'rejected' ? 'selected' : ''; ?>>거절</option>
                        <option value="expired" <?php echo $quotation['status'] == 'expired' ? 'selected' : ''; ?>>만료</option>
                    </select>
                    <button type="submit" class="btn btn-primary">저장</button>
                </form>
                
                <div class="action-bar">
                    <a href="../shop/generate_quote_pdf.php?quotation_id=<?php echo $quotation['id']; ?>" class="btn btn-success" target="_blank">📄 PDF 다운로드</a>
                    <a href="quotation_list.php" class="btn btn-outline">목록으로</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copyLink() {
        var input = document.getElementById('publicUrl');
        input.select();
        document.execCommand('copy');
        alert('링크가 복사되었습니다.');
    }
    </script>
</body>
</html>
