<?php
/**
 * 관리자 견적서 작성 - Dashboard Tailwind Style
 */
require_once __DIR__ . '/../../../admin/includes/admin_auth.php';
requireAdminAuth();

require_once __DIR__ . '/../../../db.php';
require_once __DIR__ . '/../../../dashboard/includes/config.php';
require_once __DIR__ . '/includes/AdminQuoteManager.php';
require_once __DIR__ . '/includes/PriceHelper.php';

if (!$db) { die('DB 연결 실패'); }
mysqli_set_charset($db, 'utf8mb4');

// Auto-migration: ensure premium_options column exists (safe idempotent check)
$colCheck = mysqli_query($db, "SHOW COLUMNS FROM admin_quotation_temp LIKE 'premium_options'");
if ($colCheck && mysqli_num_rows($colCheck) === 0) {
    mysqli_query($db, "ALTER TABLE admin_quotation_temp ADD COLUMN premium_options TEXT AFTER additional_options");
}

$quoteManager = new AdminQuoteManager($db);
$adminSessionId = session_id();
$newQuoteNo = $quoteManager->generateQuoteNo();
$tempItems = $quoteManager->getTempItems($adminSessionId);
$unitOptions = ['매', '연', '부', '권', '개', '장', '식'];

// 팝업 모드 감지
$isPopup = !empty($_GET['popup']);

if (!$isPopup) {
    // 사이드바 active 표시를 위해 현재 경로 오버라이드
    $_SERVER['REQUEST_URI'] = '/dashboard/quotes/';
    include __DIR__ . '/../../../dashboard/includes/header.php';
    include __DIR__ . '/../../../dashboard/includes/sidebar.php';
}
?>

<?php if ($isPopup): ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>새 견적서 작성</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans KR', sans-serif; }</style>
</head>
<body class="bg-gray-50">
<?php endif; ?>

<main class="flex-1 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-2">
        <!-- 헤더 -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold text-gray-900">새 견적서</h1>
                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium"><?php echo htmlspecialchars($newQuoteNo); ?></span>
            </div>
            <div class="flex items-center gap-1.5">
                <a href="index.php" class="px-3 py-1 text-xs text-gray-600 border border-gray-300 rounded hover:bg-gray-100">← 취소</a>
                <button onclick="saveQuote(true)" class="px-3 py-1 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">임시저장</button>
                <button onclick="saveQuote(false)" class="px-3 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700">저장</button>
            </div>
        </div>

        <!-- 고객 정보 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">고객 정보</div>
            <div class="px-3 py-2">
                <div class="grid grid-cols-[70px_1fr_70px_1fr] gap-x-2 gap-y-1 items-center">
                    <label class="text-xs text-gray-500">회사명</label>
                    <input type="text" id="customer_company" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="회사명">
                    <label class="text-xs text-gray-500">담당자명 <span class="text-red-500">*</span></label>
                    <input type="text" id="customer_name" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="담당자명" required>
                    <label class="text-xs text-gray-500">연락처</label>
                    <input type="tel" id="customer_phone" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="010-0000-0000">
                    <label class="text-xs text-gray-500">이메일</label>
                    <input type="email" id="customer_email" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="email@example.com">
                </div>
                <div class="grid grid-cols-[70px_1fr] gap-x-2 gap-y-1 items-center mt-1">
                    <label class="text-xs text-gray-500">주소</label>
                    <input type="text" id="customer_address" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="배송 주소">
                </div>
            </div>
        </div>

        <!-- 품목 목록 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b rounded-t-lg flex items-center justify-between" style="background:#1E4E79;">
                <span class="font-semibold text-xs text-white">품목 목록</span>
                <div class="flex items-center gap-1.5">
                    <button onclick="openCalculatorSelect()" class="px-2.5 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700">계산기</button>
                    <button onclick="openManualModal()" class="px-2.5 py-1 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">+ 수동</button>
                </div>
            </div>
            <!-- 컬럼 헤더 -->
            <div class="grid items-center text-xs font-medium px-1 border-b border-gray-200" style="grid-template-columns:36px minmax(0,1fr) minmax(0,2fr) 70px 80px 90px 28px;background:#f9fafb;color:#6b7280;letter-spacing:0.025em;overflow:hidden;">
                <span class="py-1.5 text-center">NO</span>
                <span class="py-1.5 px-2">품목</span>
                <span class="py-1.5 px-2">규격/옵션</span>
                <span class="py-1.5 text-center">수량</span>
                <span class="py-1.5 text-right pr-2">단가</span>
                <span class="py-1.5 text-right pr-2">공급가액</span>
                <span></span>
            </div>
            <!-- 품목 리스트 -->
            <div id="itemsBody"></div>
            <!-- 택배비 입력 -->
            <div class="px-3 py-2 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs text-gray-500">🚚</span>
                        <span class="text-xs font-medium text-gray-700">택배비</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <input type="number" id="shipping_cost" class="w-28 px-2 py-1 text-xs text-right border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="0" min="0" value="0" oninput="updateTotals()">
                        <span class="text-xs text-gray-400">원</span>
                    </div>
                </div>
            </div>
            <!-- 합계 -->
            <div class="px-3 py-2 border-t border-gray-200 bg-gray-50/50 rounded-b-lg">
                <div class="flex justify-end gap-6 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">공급가액</span>
                        <span class="font-medium text-gray-700 w-20 text-right" id="supplyTotal">0</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-500">부가세</span>
                        <span class="font-medium text-gray-700 w-20 text-right" id="vatTotal">0</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-900 font-semibold">총액</span>
                        <span class="font-bold text-blue-700 w-24 text-right text-sm" id="grandTotal">0</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 메모 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">메모</div>
            <div class="px-3 py-2">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">고객 요청사항</label>
                        <textarea id="customer_memo" rows="5" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="고객이 요청한 내용"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">관리자 메모</label>
                        <textarea id="admin_memo" rows="5" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="내부 메모"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 수동 입력 모달 -->
<div class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" id="manualModal">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-2 border-b">
            <h3 class="text-sm font-semibold text-gray-900">수동 품목 추가</h3>
            <button onclick="closeManualModal()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="px-4 py-3 space-y-2.5">
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">품목명 <span class="text-red-500">*</span></label>
                <input type="text" id="manual_product_name" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="예: 스티커, 전단지, 택배비">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">규격/설명</label>
                <textarea id="manual_specification" rows="2" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="예: 아트지유광 / 60x50mm / 사각"></textarea>
            </div>
            <div class="grid grid-cols-[1fr_60px] gap-2">
                <div>
                    <label class="block text-xs text-gray-500 mb-0.5">수량 <span class="text-red-500">*</span></label>
                    <input type="number" id="manual_quantity" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" value="1" min="0.1" step="0.1">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-0.5">단위</label>
                    <select id="manual_unit" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <?php foreach ($unitOptions as $unit): ?>
                        <option value="<?php echo $unit; ?>"><?php echo $unit; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">공급가액 <span class="text-red-500">*</span></label>
                <input type="number" id="manual_supply_price" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="0" min="0">
            </div>
        </div>
        <div class="flex justify-end gap-2 px-4 py-2 border-t bg-gray-50 rounded-b-lg">
            <button onclick="closeManualModal()" class="px-3 py-1 text-xs text-gray-700 border border-gray-300 rounded hover:bg-gray-100">취소</button>
            <button onclick="addManualItem()" class="px-3 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700">추가</button>
        </div>
    </div>
</div>

<!-- 계산기 선택 모달 (대문 카드 축소 스타일) -->
<style>
.qc-card{display:flex;flex-direction:column;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);cursor:pointer;transition:all .25s ease;background:#fff;border:none;padding:0;text-align:left;}
.qc-card:hover{transform:translateY(-3px);box-shadow:0 6px 16px rgba(0,0,0,0.15);}
.qc-head{display:flex;align-items:center;gap:6px;padding:6px 10px;color:#fff;}
.qc-head .qc-title{font-size:12px;font-weight:700;line-height:1.2;}
.qc-head .qc-sub{font-size:9px;opacity:0.7;font-weight:500;}
.qc-body{display:grid;grid-template-columns:1fr 56px;gap:4px;padding:6px 10px 8px;flex:1;align-items:end;}
.qc-feats{list-style:none;margin:0;padding:0;font-size:10px;color:#4b5563;}
.qc-feats li::before{content:"✓ ";color:var(--cg);font-weight:700;}
.qc-feats li{margin-bottom:2px;line-height:1.4;}
.qc-img{width:56px;height:56px;border-radius:6px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.1);}
.qc-img img{width:100%;height:100%;object-fit:cover;}
</style>
<div class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" id="calcSelectModal">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4">
        <div class="flex items-center justify-between px-5 py-3 border-b">
            <h3 class="text-base font-bold text-gray-900">품목 계산기 선택</h3>
            <button onclick="closeCalculatorSelect()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-4 py-4">
            <div class="grid grid-cols-3 gap-3">
                <button class="qc-card" style="--cg:#3b82f6" onclick="openCalculator('sticker')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#3b82f6,#2563eb)">
                        <div><div class="qc-title">스티커</div><div class="qc-sub">맞춤형 스티커</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>방수 소재</li><li>자유 형태</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/sticker_new_s.png" alt="스티커"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#10b981" onclick="openCalculator('inserted')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#10b981,#059669)">
                        <div><div class="qc-title">전단지</div><div class="qc-sub">홍보용 전단지</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>고해상도</li><li>빠른 제작</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/inserted_s.png" alt="전단지"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#8b5cf6" onclick="openCalculator('namecard')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)">
                        <div><div class="qc-title">명함</div><div class="qc-sub">전문 명함</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>UV 코팅</li><li>당일 제작</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/namecard_s.png" alt="명함"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#e11d48" onclick="openCalculator('envelope')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#e11d48,#be123c)">
                        <div><div class="qc-title">봉투</div><div class="qc-sub">각종 봉투</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>창봉투</li><li>대량 주문</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/envelop_s.png" alt="봉투"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#06b6d4" onclick="openCalculator('cadarok')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#06b6d4,#0891b2)">
                        <div><div class="qc-title">카다록</div><div class="qc-sub">제품 카탈로그</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>풀컬러</li><li>전문 편집</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/catalogue_s.png" alt="카다록"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#f97316" onclick="openCalculator('littleprint')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#f97316,#ea580c)">
                        <div><div class="qc-title">포스터</div><div class="qc-sub">대형 포스터</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>대형 사이즈</li><li>고화질 출력</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/poster_s.png" alt="포스터"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#84cc16" onclick="openCalculator('ncrflambeau')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#84cc16,#65a30d)">
                        <div><div class="qc-title">NCR양식</div><div class="qc-sub">양식지 제작</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>2~4연</li><li>무탄소 용지</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/ncr_s.png" alt="NCR양식"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#d946ef" onclick="openCalculator('merchandisebond')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#d946ef,#c026d3)">
                        <div><div class="qc-title">상품권</div><div class="qc-sub">쿠폰/상품권</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>위조 방지</li><li>번호 인쇄</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/merchandise_s.png" alt="상품권"></div>
                    </div>
                </button>
                <button class="qc-card" style="--cg:#ef4444" onclick="openCalculator('msticker')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#ef4444,#dc2626)">
                        <div><div class="qc-title">자석스티커</div><div class="qc-sub">마그네틱 스티커</div></div>
                    </div>
                    <div class="qc-body">
                        <ul class="qc-feats"><li>강력 자석</li><li>차량용 최적</li></ul>
                        <div class="qc-img"><img src="/ImgFolder/gate_picto/m_sticker_s.png" alt="자석스티커"></div>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 계산기 iframe 모달 -->
<div class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" id="calcIframeModal">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl mx-4 flex flex-col" style="max-height:90vh;">
        <div class="flex items-center justify-between px-4 py-2 border-b flex-shrink-0">
            <h3 id="calcModalTitle" class="text-sm font-semibold text-gray-900">계산기</h3>
            <button onclick="closeCalculatorIframe()" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="flex-1 overflow-hidden">
            <iframe id="calcIframe" src="about:blank" class="w-full h-full border-0" style="min-height:500px;"></iframe>
        </div>
        <div class="px-4 py-1.5 border-t bg-gray-50 text-center text-xs text-gray-500 flex-shrink-0 rounded-b-lg">
            계산기에서 옵션 선택 후 <strong class="text-gray-700">견적서에 적용</strong> 버튼 클릭
        </div>
    </div>
</div>

<script>
let items = <?php echo json_encode(array_map(function($item) {
    $isManual = !empty($item['is_manual']);
    return [
        'no' => $item['no'],
        'is_manual' => $isManual ? 1 : 0,
        'product_name' => $isManual ? ($item['manual_product_name'] ?? '') : PriceHelper::getProductTypeName($item['product_type'] ?? ''),
        'specification' => $isManual ? ($item['manual_specification'] ?? '') : ($item['specification'] ?? ''),
        'quantity' => $isManual ? floatval($item['manual_quantity'] ?? 1) : floatval($item['mesu'] ?? $item['MY_amount'] ?? 1),
        'unit' => $isManual ? ($item['manual_unit'] ?? '개') : PriceHelper::getDefaultUnit($item['product_type'] ?? ''),
        'quantity_display' => $item['quantity_display'] ?? '',
        'unit_price' => floatval($item['unit_price'] ?? 0),
        'supply_price' => $isManual ? intval($item['manual_supply_price'] ?? 0) : intval($item['st_price'] ?? 0),
        'product_type' => $item['product_type'] ?? '',
        'source_data' => $item
    ];
}, $tempItems), JSON_UNESCAPED_UNICODE); ?>;

const quoteNo = '<?php echo addslashes($newQuoteNo); ?>';

document.addEventListener('DOMContentLoaded', renderItems);

function renderItems() {
    const container = document.getElementById('itemsBody');
    while (container.firstChild) container.removeChild(container.firstChild);

    if (items.length === 0) {
        const empty = document.createElement('div');
        empty.className = 'py-6 text-center text-xs text-gray-400';
        empty.textContent = '품목을 추가해주세요.';
        container.appendChild(empty);
        updateTotals();
        return;
    }

    const colStyle = '36px minmax(0,1fr) minmax(0,2fr) 70px 80px 90px 28px';

    items.forEach((item, i) => {
        const unitPrice = item.unit_price > 0 ? Math.round(item.unit_price) : (item.quantity > 0 ? Math.round(item.supply_price / item.quantity) : 0);
        const qtyDisplay = item.quantity_display || (formatNumber(item.quantity) + item.unit);

        const row = document.createElement('div');
        row.className = 'grid items-center px-1 border-b border-gray-100 transition-colors';
        row.style.gridTemplateColumns = colStyle;
        row.style.minHeight = '33px';
        row.style.overflow = 'hidden';
        row.style.backgroundColor = i % 2 === 1 ? '#e6f7ff' : '#fff';
        row.onmouseenter = function(){ this.style.backgroundColor='#dbeafe'; };
        row.onmouseleave = function(){ this.style.backgroundColor = i % 2 === 1 ? '#e6f7ff' : '#fff'; };

        // NO
        const noEl = document.createElement('span');
        noEl.className = 'py-1.5 text-center text-xs text-gray-400';
        noEl.textContent = i + 1;
        row.appendChild(noEl);

        // 품목
        const nameEl = document.createElement('span');
        nameEl.className = 'py-1.5 px-2 text-sm font-medium text-gray-900 truncate';
        nameEl.style.minWidth = '0';
        nameEl.style.overflow = 'hidden';
        nameEl.textContent = item.product_name;
        row.appendChild(nameEl);

        // 규격/옵션
        const specEl = document.createElement('span');
        specEl.className = 'py-1.5 px-2 text-xs text-gray-500 leading-snug';
        specEl.style.minWidth = '0';
        specEl.style.overflow = 'hidden';
        specEl.style.wordBreak = 'break-all';
        if (item.specification) {
            const specParts = item.specification.split('\n');
            specParts.forEach((part, pi) => {
                if (pi > 0) specEl.appendChild(document.createElement('br'));
                specEl.appendChild(document.createTextNode(part));
            });
        }
        row.appendChild(specEl);

        // 수량 (전단지: 연 + 매수 2줄)
        const qtyEl = document.createElement('span');
        qtyEl.className = 'py-1.5 text-center text-sm text-gray-700 leading-tight';
        const parenMatch = qtyDisplay.match(/^(.+?)\s*(\(.+\))$/);
        if (parenMatch) {
            qtyEl.appendChild(document.createTextNode(parenMatch[1]));
            qtyEl.appendChild(document.createElement('br'));
            const sub = document.createElement('span');
            sub.className = 'text-xs text-gray-400';
            sub.textContent = parenMatch[2];
            qtyEl.appendChild(sub);
        } else if (item.product_type === 'inserted') {
            qtyEl.appendChild(document.createTextNode(qtyDisplay));
            const sheets = item.source_data?.quantityTwo || item.source_data?.quantity_two || item.source_data?.sheets || 0;
            if (parseInt(sheets) > 0) {
                qtyEl.appendChild(document.createElement('br'));
                const sub = document.createElement('span');
                sub.className = 'text-xs text-gray-400';
                sub.textContent = '(' + formatNumber(sheets) + '\ub9e4)';
                qtyEl.appendChild(sub);
            }
        } else {
            qtyEl.textContent = qtyDisplay;
        }
        row.appendChild(qtyEl);

        // 단가
        const upEl = document.createElement('span');
        upEl.className = 'py-1.5 text-right pr-2 text-sm text-gray-600';
        upEl.textContent = formatNumber(unitPrice);
        row.appendChild(upEl);

        // 공급가액
        const priceEl = document.createElement('span');
        priceEl.className = 'py-1.5 text-right pr-2 text-sm font-semibold text-gray-900';
        priceEl.textContent = formatNumber(item.supply_price);
        row.appendChild(priceEl);

        // 삭제
        const delWrap = document.createElement('span');
        delWrap.className = 'py-1.5 text-center';
        const delBtn = document.createElement('button');
        delBtn.className = 'text-gray-300 hover:text-red-500 text-sm leading-none transition-colors';
        delBtn.textContent = '\u00d7';
        delBtn.addEventListener('click', function() { deleteItem(item.no); });
        delWrap.appendChild(delBtn);
        row.appendChild(delWrap);

        container.appendChild(row);
    });
    updateTotals();
}

function updateTotals() {
    let supply = 0;
    items.forEach(item => supply += parseInt(item.supply_price) || 0);
    const shipping = parseInt(document.getElementById('shipping_cost').value) || 0;
    supply += shipping;
    const vat = Math.round(supply * 0.1);
    document.getElementById('supplyTotal').textContent = formatNumber(supply);
    document.getElementById('vatTotal').textContent = formatNumber(vat);
    document.getElementById('grandTotal').textContent = formatNumber(supply + vat);
}

function openManualModal() { document.getElementById('manualModal').classList.remove('hidden'); document.getElementById('manualModal').classList.add('flex'); document.getElementById('manual_product_name').focus(); }
function closeManualModal() {
    document.getElementById('manualModal').classList.add('hidden');
    document.getElementById('manualModal').classList.remove('flex');
    document.getElementById('manual_product_name').value = '';
    document.getElementById('manual_specification').value = '';
    document.getElementById('manual_quantity').value = '1';
    document.getElementById('manual_unit').value = '개';
    document.getElementById('manual_supply_price').value = '';
}

function addManualItem() {
    const name = document.getElementById('manual_product_name').value.trim();
    const spec = document.getElementById('manual_specification').value.trim();
    const qty = parseFloat(document.getElementById('manual_quantity').value) || 1;
    const unit = document.getElementById('manual_unit').value;
    const price = parseInt(document.getElementById('manual_supply_price').value) || 0;

    if (!name) { alert('품목명을 입력해주세요.'); return; }
    if (price <= 0) { alert('공급가액을 입력해주세요.'); return; }

    fetch('api/add_manual_item.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({is_manual:true, product_name:name, specification:spec, quantity:qty, unit:unit, supply_price:price})
    }).then(r=>r.json()).then(d=>{
        if(d.success) {
            items.push({no:d.item_no, is_manual:1, product_name:name, specification:spec, quantity:qty, unit:unit, quantity_display:formatNumber(qty)+unit, unit_price:Math.round(price/qty), supply_price:price, product_type:'', source_data:null});
            renderItems(); closeManualModal();
        } else alert('실패: '+d.message);
    }).catch(e=>alert('오류: '+e.message));
}

function deleteItem(itemNo) {
    if(!confirm('삭제하시겠습니까?')) return;
    fetch('api/delete_temp_item.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({item_no:itemNo})})
    .then(r=>r.json()).then(d=>{
        if(d.success) { items = items.filter(x=>x.no!==itemNo); renderItems(); }
        else alert('삭제 실패: '+d.message);
    }).catch(e=>alert('오류: '+e.message));
}

function saveQuote(isDraft) {
    const name = document.getElementById('customer_name').value.trim();
    if(!name) { alert('담당자명을 입력해주세요.'); document.getElementById('customer_name').focus(); return; }
    if(items.length===0) { alert('품목을 추가해주세요.'); return; }

    const data = {
        quote_no: quoteNo,
        customer_company: document.getElementById('customer_company').value.trim(),
        customer_name: name,
        customer_phone: document.getElementById('customer_phone').value.trim(),
        customer_email: document.getElementById('customer_email').value.trim(),
        customer_address: document.getElementById('customer_address').value.trim(),
        customer_memo: document.getElementById('customer_memo').value.trim(),
        admin_memo: document.getElementById('admin_memo').value.trim(),
        shipping_cost: parseInt(document.getElementById('shipping_cost').value) || 0,
        is_draft: isDraft,
        items: items.map(x=>({source_type:x.is_manual?'manual':'calculator', product_type:x.product_type||'', product_name:x.product_name, specification:x.specification, quantity:x.quantity, unit:x.unit, quantity_display:x.quantity_display, unit_price:x.unit_price, supply_price:x.supply_price, source_data:x.source_data}))
    };

    fetch('api/save.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(data)})
    .then(r=>r.json()).then(d=>{
        if(d.success) { alert(isDraft?'임시저장됨':'저장됨'); location.href='detail.php?id='+d.quote_id; }
        else alert('저장 실패: '+d.message);
    }).catch(e=>alert('오류: '+e.message));
}

function formatNumber(n) { const v=parseFloat(n); return isNaN(v)?'0':v.toLocaleString('ko-KR',{maximumFractionDigits:1}); }

// 계산기 연동
const CALC_CFG = {
    'sticker':{name:'스티커',url:'/admin/mlangprintauto/quote/widgets/sticker.php'},
    'inserted':{name:'전단지',url:'/admin/mlangprintauto/quote/widgets/inserted.php'},
    'namecard':{name:'명함',url:'/admin/mlangprintauto/quote/widgets/namecard.php'},
    'envelope':{name:'봉투',url:'/admin/mlangprintauto/quote/widgets/envelope.php'},
    'ncrflambeau':{name:'NCR양식',url:'/admin/mlangprintauto/quote/widgets/ncrflambeau.php'},
    'cadarok':{name:'카다록',url:'/admin/mlangprintauto/quote/widgets/cadarok.php'},
    'littleprint':{name:'포스터',url:'/admin/mlangprintauto/quote/widgets/littleprint.php'},
    'msticker':{name:'자석스티커',url:'/admin/mlangprintauto/quote/widgets/msticker.php'},
    'merchandisebond':{name:'상품권',url:'/admin/mlangprintauto/quote/widgets/merchandisebond.php'}
};

function openCalculatorSelect() { document.getElementById('calcSelectModal').classList.remove('hidden'); document.getElementById('calcSelectModal').classList.add('flex'); }
function closeCalculatorSelect() { document.getElementById('calcSelectModal').classList.add('hidden'); document.getElementById('calcSelectModal').classList.remove('flex'); }
function openCalculator(type) {
    const c = CALC_CFG[type]; if(!c){alert('알 수 없는 품목');return;}
    closeCalculatorSelect();
    document.getElementById('calcModalTitle').textContent = c.name+' 계산기';
    document.getElementById('calcIframe').src = c.url;
    document.getElementById('calcIframeModal').classList.remove('hidden');
    document.getElementById('calcIframeModal').classList.add('flex');
    document.body.style.overflow='hidden';
}
function closeCalculatorIframe() {
    document.getElementById('calcIframeModal').classList.add('hidden');
    document.getElementById('calcIframeModal').classList.remove('flex');
    document.getElementById('calcIframe').src='about:blank';
    document.body.style.overflow='';
}

window.addEventListener('message', function(e) {
    if(e.origin!==window.location.origin||!e.data||!e.data.type) return;
    if(e.data.type==='ADMIN_QUOTE_ITEM_ADDED' || e.data.type==='CALCULATOR_PRICE_DATA') {
        const payload = e.data.payload || {};
        if (payload.product_code && !payload.product_type) payload.product_type = payload.product_code;
        if (payload.quantity_unit && !payload.unit) payload.unit = payload.quantity_unit;
        if (payload.options && typeof payload.options === 'object') {
            Object.keys(payload.options).forEach(k => { if (!(k in payload)) payload[k] = payload.options[k]; });
        }
        const addBtn = document.querySelector('#calcIframeModal button');
        if(addBtn) addBtn.disabled = true;
        fetch('api/add_calculator_item.php', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(payload)})
        .then(r=>r.json()).then(d=>{
            if(d.success) {
                items.push({no:d.item_no, is_manual:0, product_name:d.item.product_name, specification:d.item.specification, quantity:d.item.quantity, unit:d.item.unit, quantity_display:d.item.quantity_display, unit_price:d.item.unit_price, supply_price:d.item.supply_price, product_type:d.item.product_type, source_data:payload});
                renderItems();
                closeCalculatorIframe();
            } else {
                alert('품목 추가 실패: '+d.message);
            }
        }).catch(err=>{
            alert('서버 오류: '+err.message+'\n다시 시도해주세요.');
        }).finally(()=>{
            if(addBtn) addBtn.disabled = false;
        });
    }
    if(e.data.type==='ADMIN_QUOTE_CLOSE_MODAL') closeCalculatorIframe();
});
</script>
<script>
(function() {
    if (!window.opener) return;
    function fitWindow() {
        var body = document.body, html = document.documentElement;
        var contentH = Math.max(body.scrollHeight, body.offsetHeight, html.scrollHeight);
        var contentW = Math.max(body.scrollWidth, body.offsetWidth, html.scrollWidth);
        var chromeH = window.outerHeight - window.innerHeight;
        var chromeW = window.outerWidth - window.innerWidth;
        var targetW = Math.min(Math.max(contentW + chromeW + 60, 900), screen.availWidth - 40);
        var targetH = Math.min(contentH + chromeH + 60, screen.availHeight - 40);
        window.resizeTo(targetW, targetH);
        var left = Math.round((screen.availWidth - targetW) / 2);
        var top = Math.round((screen.availHeight - targetH) / 2);
        window.moveTo(Math.max(0, left), Math.max(0, top));
    }
    if (document.readyState === 'complete') fitWindow();
    else window.addEventListener('load', fitWindow);
})();
</script>

<?php if ($isPopup): ?>
</body>
</html>
<?php else: ?>
<?php include __DIR__ . '/../../../dashboard/includes/footer.php'; ?>
<?php endif; ?>
