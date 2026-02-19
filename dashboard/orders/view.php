<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/ImagePathResolver.php';

$no = intval($_GET['no'] ?? 0);

if ($no <= 0) {
    header('Location: /dashboard/orders/');
    exit;
}

$query = "SELECT * FROM mlangorder_printauto WHERE no = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Location: /dashboard/orders/');
    exit;
}

// 품목 한글 레이블
$type_labels = [
    'inserted' => '전단지', 'Inserted' => '전단지',
    'namecard' => '명함', 'NameCard' => '명함',
    'sticker_new' => '스티커', 'sticker' => '스티커', 'Sticker' => '스티커',
    'msticker' => '자석스티커', 'Msticker' => '자석스티커',
    'envelope' => '봉투', 'Envelope' => '봉투',
    'littleprint' => '포스터', 'LittlePrint' => '포스터',
    'merchandisebond' => '상품권', 'MerchandiseBond' => '상품권',
    'cadarok' => '카다록', 'Cadarok' => '카다록',
    'ncrflambeau' => 'NCR양식지', 'NcrFlambeau' => 'NCR양식지',
];

// 상태 매핑
$status_map = [
    '0'  => ['label' => '미선택',     'bg' => 'bg-gray-100',   'text' => 'text-gray-600'],
    '1'  => ['label' => '견적접수',   'bg' => 'bg-slate-100',  'text' => 'text-slate-700'],
    '2'  => ['label' => '주문접수',   'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800'],
    '3'  => ['label' => '접수완료',   'bg' => 'bg-amber-100',  'text' => 'text-amber-800'],
    '4'  => ['label' => '입금대기',   'bg' => 'bg-orange-100', 'text' => 'text-orange-800'],
    '5'  => ['label' => '시안제작중', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700'],
    '6'  => ['label' => '시안',       'bg' => 'bg-violet-100', 'text' => 'text-violet-700'],
    '7'  => ['label' => '교정',       'bg' => 'bg-blue-100',   'text' => 'text-blue-700'],
    '8'  => ['label' => '작업완료',   'bg' => 'bg-green-100',  'text' => 'text-green-800'],
    '9'  => ['label' => '작업중',     'bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
    '10' => ['label' => '교정작업중', 'bg' => 'bg-cyan-100',   'text' => 'text-cyan-700'],
    'deleted' => ['label' => '삭제됨', 'bg' => 'bg-red-100',   'text' => 'text-red-800'],
];

$os = (string)$order['OrderStyle'];
$status_info = $status_map[$os] ?? ['label' => $os, 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];

// 품목명 결정
$product_type = $order['product_type'] ?? '';
$type_label = $type_labels[$product_type] ?? ($type_labels[$order['Type']] ?? htmlspecialchars($order['Type']));

// Type_1 파싱: JSON(v2) 또는 레거시(파이프/줄바꿈) 형식
$type1_data = null;
$legacy_specs = [];
$type1_raw = trim($order['Type_1'] ?? '');

if (!empty($type1_raw) && $type1_raw[0] === '{') {
    $type1_data = json_decode($type1_raw, true);
} elseif (!empty($type1_raw)) {
    $cleaned = str_replace("\n", '', $type1_raw);
    if (!empty(trim($cleaned))) {
        $is_pipe = strpos($type1_raw, '|') !== false;
        $has_colon_key = preg_match('/^(구분|규격|종이종류|수량|주문방법)\s*[:：]/um', $type1_raw);

        if ($is_pipe) {
            // 스티커 파이프 패턴: "유포지 스티카|크기: 105 x 148mm|매수: 1000 매|사각"
            $parts = array_values(array_filter(array_map('trim', explode('|', $type1_raw)), function($v) { return $v !== ''; }));
            foreach ($parts as $p) {
                if (preg_match('/^크기\s*[:：]\s*(.+)/u', $p, $m)) {
                    $legacy_specs[] = ['label' => '크기', 'value' => trim($m[1])];
                } elseif (preg_match('/^매수\s*[:：]\s*(.+)/u', $p, $m)) {
                    $legacy_specs[] = ['label' => '매수', 'value' => trim($m[1])];
                } elseif (preg_match('/^(원형|사각|타원|자유형)/u', $p)) {
                    $legacy_specs[] = ['label' => '모양', 'value' => trim($p)];
                } else {
                    if (empty($legacy_specs)) {
                        $legacy_specs[] = ['label' => '재질', 'value' => trim($p)];
                    } else {
                        $legacy_specs[] = ['label' => '옵션', 'value' => trim($p)];
                    }
                }
            }
        } elseif ($has_colon_key) {
            // 키:값 패턴: "구분: 카다록\n규격: ...\n수량: 1000부"
            $lines = array_values(array_filter(array_map('trim', explode("\n", $type1_raw)), function($v) { return $v !== ''; }));
            foreach ($lines as $line) {
                if (preg_match('/^(.+?)\s*[:：]\s*(.+)/u', $line, $m)) {
                    $legacy_specs[] = ['label' => trim($m[1]), 'value' => trim($m[2])];
                }
            }
        } else {
            // 줄바꿈 구분 패턴 (품목별 라벨 매핑)
            $parts = explode("\n", $type1_raw);
            // 빈 줄도 위치 유지 (라벨 매핑 정확도 위해)
            $type_key = strtolower($order['Type'] ?? '');
            // 품목별 라벨 순서
            $label_map = [
                'inserted'        => ['인쇄유형', '용지', '사이즈', '인쇄면', '수량', '디자인'],
                '전단지'          => ['인쇄유형', '용지', '사이즈', '인쇄면', '수량', '디자인'],
                'namecard'        => ['재질', '코팅', '코팅유형', '인쇄면', '수량', '디자인'],
                '명함'            => ['재질', '코팅', '코팅유형', '인쇄면', '수량', '디자인'],
                'envelope'        => ['봉투종류', '규격상세', '규격', '인쇄방식', '수량', '디자인'],
                '봉투'            => ['봉투종류', '규격상세', '규격', '인쇄방식', '수량', '디자인'],
                'ncrflambeau'     => ['유형', '규격', '인쇄도수', '옵션', '수량', '디자인'],
                'littleprint'     => ['유형', '용지', '사이즈', '인쇄면', '수량', '디자인'],
                '포스터'          => ['유형', '용지', '사이즈', '인쇄면', '수량', '디자인'],
                'cadarok'         => ['유형', '규격', '용지', '옵션', '수량', '디자인'],
                '카다록'          => ['유형', '규격', '용지', '옵션', '수량', '디자인'],
                'merchandisebond' => ['유형', '규격', '인쇄유형', '인쇄면', '수량', '디자인'],
                '상품권'          => ['유형', '규격', '인쇄유형', '인쇄면', '수량', '디자인'],
            ];
            $labels = $label_map[$type_key] ?? ['항목1', '항목2', '항목3', '항목4', '수량', '디자인'];

            foreach ($parts as $i => $p) {
                $p = trim($p);
                if ($p === '') continue;
                $label = $labels[$i] ?? '기타';
                $legacy_specs[] = ['label' => $label, 'value' => $p];
            }
        }
    }
}

// 규격 정보 (정규화 컬럼 우선 → Type_1 JSON fallback)
$spec_type = $order['spec_type'] ?: ($type1_data['spec_type'] ?? '');
$spec_material = $order['spec_material'] ?: ($type1_data['spec_material'] ?? '');
$spec_size = $order['spec_size'] ?: ($type1_data['spec_size'] ?? '');
$spec_sides = $order['spec_sides'] ?: ($type1_data['spec_sides'] ?? '');
$spec_design = $order['spec_design'] ?: ($type1_data['spec_design'] ?? '');
$qty_display = $order['quantity_display'] ?: ($type1_data['quantity_display'] ?? '');
$qty_value = $order['quantity_value'] ?: ($type1_data['quantity_value'] ?? '');
$qty_unit = $order['quantity_unit'] ?: ($type1_data['quantity_unit'] ?? '');

// 정규화 컬럼도 Type_1 JSON도 없으면 레거시 파싱 결과 사용 여부
$use_legacy_specs = empty($spec_type) && empty($spec_material) && empty($spec_size) && !empty($legacy_specs);

// 금액 정보 (price_supply 우선, 없으면 money_4/money_5 fallback)
$price_supply = intval($order['price_supply'] ?: ($order['money_4'] ?? 0));
$price_vat = intval($order['price_vat'] ?: ($order['money_5'] ?? 0));
$price_vat_amount = intval($order['price_vat_amount'] ?? 0);
if ($price_vat_amount <= 0 && $price_supply > 0 && $price_vat > 0) {
    $price_vat_amount = $price_vat - $price_supply;
}

// 택배비 선불 (공급가액 + VAT 10%)
$logen_fee_type = $order['logen_fee_type'] ?? '';
$logen_delivery_fee = intval($order['logen_delivery_fee'] ?? 0);
$has_prepaid_shipping = ($logen_fee_type === '선불' && $logen_delivery_fee > 0);
$shipping_supply = $logen_delivery_fee;
$shipping_vat = round($shipping_supply * 0.1);
$shipping_total = $shipping_supply + $shipping_vat;

// 추가 옵션
$has_options = false;
$options = [];
if (!empty($order['coating_enabled'])) {
    $options[] = ['name' => '코팅', 'detail' => $order['coating_type'], 'price' => intval($order['coating_price'])];
    $has_options = true;
}
if (!empty($order['folding_enabled'])) {
    $options[] = ['name' => '접지', 'detail' => $order['folding_type'], 'price' => intval($order['folding_price'])];
    $has_options = true;
}
if (!empty($order['creasing_enabled'])) {
    $options[] = ['name' => '오시', 'detail' => $order['creasing_lines'] . '줄', 'price' => intval($order['creasing_price'])];
    $has_options = true;
}

// 원고파일 목록 (ImagePathResolver)
$file_result = ImagePathResolver::getFilesFromRow($order, false);
$order_files = $file_result['files'] ?? [];

// 같은 그룹 주문 조회
$group_orders = [];
if (!empty($order['order_group_id'])) {
    $gq = "SELECT no, Type, product_type, quantity_display, price_vat, OrderStyle FROM mlangorder_printauto WHERE order_group_id = ? AND no != ? ORDER BY order_group_seq";
    $gs = mysqli_prepare($db, $gq);
    mysqli_stmt_bind_param($gs, "si", $order['order_group_id'], $no);
    mysqli_stmt_execute($gs);
    $gr = mysqli_stmt_get_result($gs);
    while ($grow = mysqli_fetch_assoc($gr)) {
        $group_orders[] = $grow;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-1">
        <!-- 헤더 -->
        <div class="mb-2 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <a href="/dashboard/orders/" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-lg font-bold text-gray-900">주문 #<?php echo $order['no']; ?></h1>
                <span class="text-xs text-gray-400"><?php echo $order['date']; ?></span>
                <span class="px-2 py-0.5 text-xs font-semibold rounded-full <?php echo $status_info['bg'] . ' ' . $status_info['text']; ?>">
                    <?php echo $status_info['label']; ?>
                </span>
            </div>
            <a href="/admin/mlangprintauto/admin.php?mode=OrderView&no=<?php echo $order['no']; ?>" target="_blank"
               class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                기존 관리자
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-2">
            <!-- 왼쪽: 주문 정보 -->
            <div class="lg:col-span-2 space-y-2">
                <!-- 제품 규격 -->
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5 flex items-center gap-1.5">
                        <span class="w-1 h-3.5 bg-blue-600 rounded-full"></span>
                        제품 규격
                    </h3>
                    <div class="bg-gray-50 rounded p-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-bold text-gray-900"><?php echo $type_label; ?></span>
                            <?php if ($product_type): ?>
                            <span class="text-xs text-gray-400">(<?php echo htmlspecialchars($product_type); ?>)</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($qty_display): ?>
                        <div class="text-sm text-blue-700 font-medium"><?php echo htmlspecialchars($qty_display); ?></div>
                        <?php elseif (!empty($order['mesu'])): ?>
                        <div class="text-sm text-blue-700 font-medium"><?php echo htmlspecialchars($order['mesu']); ?>매</div>
                        <?php endif; ?>
                    </div>
                    <dl class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                        <?php if ($spec_type): ?>
                        <div class="bg-white border border-gray-100 rounded p-2">
                            <dt class="text-xs text-gray-400 mb-0.5">인쇄유형</dt>
                            <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($spec_type); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($spec_material): ?>
                        <div class="bg-white border border-gray-100 rounded p-2">
                            <dt class="text-xs text-gray-400 mb-0.5">용지</dt>
                            <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($spec_material); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($spec_size): ?>
                        <div class="bg-white border border-gray-100 rounded p-2">
                            <dt class="text-xs text-gray-400 mb-0.5">사이즈</dt>
                            <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($spec_size); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($spec_sides): ?>
                        <div class="bg-white border border-gray-100 rounded p-2">
                            <dt class="text-xs text-gray-400 mb-0.5">인쇄면</dt>
                            <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($spec_sides); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($spec_design): ?>
                        <div class="bg-white border border-gray-100 rounded p-2">
                            <dt class="text-xs text-gray-400 mb-0.5">디자인</dt>
                            <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($spec_design); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if ($qty_value): ?>
                        <div class="bg-white border border-gray-100 rounded p-2">
                            <dt class="text-xs text-gray-400 mb-0.5">수량</dt>
                            <dd class="font-medium text-gray-900">
                                <?php echo htmlspecialchars($qty_value . ($qty_unit ? $qty_unit : '')); ?>
                                <?php if (!empty($order['quantity_sheets'])): ?>
                                <span class="text-xs text-gray-400">(<?php echo number_format($order['quantity_sheets']); ?>매)</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php endif; ?>

                        <?php // 레거시 주문: 정규화 컬럼이 없을 때 Type_1 파싱 결과 표시 ?>
                        <?php if ($use_legacy_specs): ?>
                            <?php foreach ($legacy_specs as $ls): ?>
                            <div class="bg-white border border-gray-100 rounded p-2">
                                <dt class="text-xs text-gray-400 mb-0.5"><?php echo htmlspecialchars($ls['label']); ?></dt>
                                <dd class="font-medium text-gray-900"><?php echo htmlspecialchars($ls['value']); ?></dd>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </dl>

                    <?php if ($has_options): ?>
                    <div class="mt-2 pt-2 border-t border-gray-100">
                        <h4 class="text-xs font-medium text-gray-400 mb-1">추가 옵션</h4>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($options as $opt): ?>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-700 text-xs rounded-full">
                                <?php echo htmlspecialchars($opt['name']); ?>
                                <?php if ($opt['detail']): ?>
                                <span class="text-blue-500">(<?php echo htmlspecialchars($opt['detail']); ?>)</span>
                                <?php endif; ?>
                                <?php if ($opt['price'] > 0): ?>
                                <span class="text-blue-400">+<?php echo number_format($opt['price']); ?>원</span>
                                <?php endif; ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- 원고파일 -->
                <?php if (!empty($order_files)): ?>
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5 flex items-center gap-1.5">
                        <span class="w-1 h-3.5 bg-amber-500 rounded-full"></span>
                        원고파일 <span class="text-xs text-gray-400 font-normal">(<?php echo count($order_files); ?>개)</span>
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($order_files as $f):
                            $fname = $f['name'] ?? $f['saved_name'] ?? 'file';
                            $fsize = isset($f['size']) ? number_format($f['size'] / 1024, 1) . ' KB' : '';
                            $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));
                            $is_image = in_array($ext, ['jpg','jpeg','png','gif','bmp','tif','tiff']);
                            $download_path = $f['download_path'] ?? '';
                            if (empty($download_path)) {
                                $img_folder = $order['ImgFolder'] ?? '';
                                if (strpos($img_folder, '_MlangPrintAuto_') !== false) {
                                    $download_path = 'ImgFolder/' . $img_folder;
                                } elseif (strpos($img_folder, 'uploads/') === 0) {
                                    $download_path = $img_folder;
                                } else {
                                    $download_path = 'shop/data';
                                }
                            }
                            $dl_url = '/admin/mlangprintauto/download.php?downfile=' . urlencode($fname) . '&path=' . urlencode($download_path) . '&no=' . $no;
                        ?>
                        <div class="flex items-center justify-between p-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-lg flex-shrink-0"><?php echo $is_image ? '🖼️' : '📄'; ?></span>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-gray-900 truncate"><?php echo htmlspecialchars($fname); ?></div>
                                    <?php if ($fsize): ?>
                                    <div class="text-xs text-gray-400"><?php echo $fsize; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="<?php echo htmlspecialchars($dl_url); ?>"
                               class="flex-shrink-0 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100 transition-colors"
                               title="다운로드">
                                다운로드
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 금액 정보 -->
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-1.5">
                        <span class="w-1 h-3.5 bg-green-600 rounded-full"></span>
                        금액 정보
                    </h3>
                    <div class="text-sm">
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">공급가액</span>
                            <span class="text-gray-900"><?php echo number_format($price_supply); ?>원</span>
                        </div>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">부가세(VAT)</span>
                            <span class="text-gray-900"><?php echo number_format($price_vat_amount); ?>원</span>
                        </div>
                        <?php if ($has_options && intval($order['additional_options_total']) > 0): ?>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">추가옵션</span>
                            <span class="text-gray-900">+<?php echo number_format($order['additional_options_total']); ?>원</span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between py-0.5 border-t border-gray-200 mt-0.5">
                            <span class="font-semibold text-gray-900">총 결제금액</span>
                            <span class="text-base font-bold text-blue-600"><?php echo number_format($price_vat); ?>원</span>
                        </div>
                        <?php if ($has_prepaid_shipping): ?>
                        <div class="flex justify-between py-0.5">
                            <span class="text-gray-500">🚚 택배비 (선불)</span>
                            <span class="text-gray-900"><?php echo number_format($shipping_supply); ?>+VAT <?php echo number_format($shipping_vat); ?> = <?php echo number_format($shipping_total); ?>원</span>
                        </div>
                        <div class="flex justify-between py-0.5 border-t border-gray-200 mt-0.5">
                            <span class="font-semibold text-gray-900">택배비 포함 합계</span>
                            <span class="text-base font-bold text-red-600"><?php echo number_format($price_vat + $shipping_total); ?>원</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 고객 정보 -->
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5 flex items-center gap-1.5">
                        <span class="w-1 h-3.5 bg-purple-600 rounded-full"></span>
                        고객 정보
                    </h3>
                    <dl class="grid grid-cols-3 gap-x-3 gap-y-1 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400">이름</dt>
                            <dd class="text-gray-900 font-medium"><?php echo htmlspecialchars($order['name'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">전화</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['phone'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">휴대폰</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['Hendphone'] ?: '-'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">이메일</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['email'] ?: '-'); ?></dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-xs text-gray-400">배송</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['delivery'] ?: '-'); ?></dd>
                        </div>
                        <?php if (!empty($order['zip1'])): ?>
                        <div class="col-span-3">
                            <dt class="text-xs text-gray-400">주소</dt>
                            <dd class="text-gray-900"><?php if (!empty($order['zip'])): ?><span class="text-gray-400">[<?php echo htmlspecialchars($order['zip']); ?>]</span> <?php endif; ?><?php echo htmlspecialchars($order['zip1']); ?> <?php echo htmlspecialchars($order['zip2']); ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- 요청사항 -->
                <?php if (!empty($order['cont'])): ?>
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1 flex items-center gap-1.5">
                        <span class="w-1 h-3.5 bg-yellow-500 rounded-full"></span>
                        요청사항
                    </h3>
                    <div class="bg-gray-50 rounded p-2 text-xs text-gray-700 whitespace-pre-wrap max-h-32 overflow-y-auto"><?php echo htmlspecialchars($order['cont']); ?></div>
                </div>
                <?php endif; ?>

                <!-- 같은 그룹 주문 -->
                <?php if (!empty($group_orders)): ?>
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        <span class="w-1 h-4 bg-orange-500 rounded-full"></span>
                        함께 주문한 품목
                    </h3>
                    <div class="space-y-2">
                        <?php foreach ($group_orders as $go):
                            $go_label = $type_labels[$go['product_type'] ?? ''] ?? htmlspecialchars($go['Type']);
                            $go_status = $status_map[(string)$go['OrderStyle']] ?? ['label' => $go['OrderStyle'], 'bg' => 'bg-gray-100', 'text' => 'text-gray-800'];
                        ?>
                        <a href="?no=<?php echo $go['no']; ?>" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-medium text-gray-900">#<?php echo $go['no']; ?></span>
                                <span class="text-sm text-gray-600"><?php echo $go_label; ?></span>
                                <?php if ($go['quantity_display']): ?>
                                <span class="text-xs text-gray-400"><?php echo htmlspecialchars($go['quantity_display']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium"><?php echo number_format(intval($go['price_vat'])); ?>원</span>
                                <span class="px-2 py-0.5 text-xs rounded-full <?php echo $go_status['bg'] . ' ' . $go_status['text']; ?>"><?php echo $go_status['label']; ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- 오른쪽: 상태/결제/배송 -->
            <div class="space-y-2">
                <!-- 상태 관리 -->
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5">상태 관리</h3>
                    <form id="statusForm">
                        <input type="hidden" name="no" value="<?php echo $order['no']; ?>">
                        <div class="mb-1.5">
                            <select name="order_style" class="w-full px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <?php foreach ($status_map as $val => $info): ?>
                                <option value="<?php echo $val; ?>" <?php echo $os === (string)$val ? 'selected' : ''; ?>>
                                    <?php echo $info['label']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-4 py-1.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                            상태 변경
                        </button>
                    </form>
                </div>

                <!-- 결제 정보 -->
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5">결제 정보</h3>
                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">결제방법</dt>
                            <dd class="text-gray-900 font-medium"><?php echo htmlspecialchars($order['bank'] ?: '-'); ?></dd>
                        </div>
                        <?php if (!empty($order['bankname'])): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">입금자명</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['bankname']); ?></dd>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">총액</dt>
                            <dd class="font-bold text-blue-600"><?php echo number_format($price_vat); ?>원</dd>
                        </div>
                        <?php if ($has_prepaid_shipping): ?>
                        <div class="flex justify-between pt-1 border-t border-gray-100 mt-1">
                            <dt class="text-gray-500">🚚 택배비(선불)</dt>
                            <dd class="text-gray-900"><?php echo number_format($shipping_supply); ?>+VAT <?php echo number_format($shipping_vat); ?> = <?php echo number_format($shipping_total); ?>원</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="font-semibold text-gray-900">합계</dt>
                            <dd class="font-bold text-red-600"><?php echo number_format($price_vat + $shipping_total); ?>원</dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- 배송 정보 -->
                <?php if (!empty($order['waybill_no']) || !empty($order['logen_tracking_no'])): ?>
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5">배송 추적</h3>
                    <?php
                    $tracking = $order['waybill_no'] ?: $order['logen_tracking_no'];
                    $company = $order['delivery_company'] ?: '로젠택배';
                    ?>
                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">택배사</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($company); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">운송장번호</dt>
                            <dd class="text-gray-900 font-mono"><?php echo htmlspecialchars($tracking); ?></dd>
                        </div>
                        <?php if (!empty($order['waybill_date'])): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">발송일</dt>
                            <dd class="text-gray-900"><?php echo $order['waybill_date']; ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                    <a href="https://www.ilogen.com/web/personal/trace/<?php echo urlencode($tracking); ?>" target="_blank"
                       class="mt-3 block w-full text-center px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors">
                        배송 조회
                    </a>
                </div>
                <?php endif; ?>

                <!-- 담당자 -->
                <div class="bg-white rounded-lg shadow p-3">
                    <h3 class="text-sm font-semibold text-gray-900 mb-1.5">작업 정보</h3>
                    <dl class="space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">담당 디자이너</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['Designer'] ?: '미배정'); ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">교정 확인</dt>
                            <dd class="text-gray-900"><?php echo $order['proofreading_confirmed'] ? '확인완료' : '미확인'; ?></dd>
                        </div>
                        <?php if (!empty($order['proofreading_date'])): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">교정일시</dt>
                            <dd class="text-gray-900 text-xs"><?php echo $order['proofreading_date']; ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($order['quote_no'])): ?>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">견적번호</dt>
                            <dd class="text-gray-900"><?php echo htmlspecialchars($order['quote_no']); ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('statusForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    var formData = new FormData(this);
    formData.append('action', 'update');

    try {
        var response = await fetch('/dashboard/api/orders.php', {
            method: 'POST',
            body: formData
        });

        var result = await response.json();

        if (result.success) {
            alert('상태가 변경되었습니다.');
            location.reload();
        } else {
            alert('상태 변경 실패: ' + result.message);
        }
    } catch (error) {
        alert('상태 변경 중 오류가 발생했습니다.');
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
