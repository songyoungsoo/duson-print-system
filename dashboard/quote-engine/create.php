<?php
/**
 * 견적엔진 — 견적서 작성/수정
 * /dashboard/quote-engine/create.php
 * ?id=N 으로 기존 견적 수정 모드
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../includes/quote-engine/QuoteEngine.php';
require_once __DIR__ . '/../../includes/quote-engine/CustomerManager.php';

$_SERVER['REQUEST_URI'] = '/dashboard/quote-engine/';

$engine = new QE_QuoteEngine($db);
$editId = intval($_GET['id'] ?? 0);
$editQuote = null;
$editItems = [];
$quoteNo = '';
$pageTitle = '새 견적서';

if ($editId) {
    $editQuote = $engine->getQuote($editId);
    if ($editQuote) {
        $editItems = $engine->getItems($editId);
        $quoteNo = $editQuote['quote_no'];
        $pageTitle = '견적서 수정';
    } else {
        $editId = 0;
    }
}

if (!$quoteNo) {
    $quoteNo = $engine->generateQuoteNo('quotation');
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 min-h-0 bg-gray-50 overflow-y-auto">
<div class="max-w-5xl mx-auto px-4 py-2">

<!-- 헤더 -->
<div class="flex items-center gap-2 mb-2">
    <a href="index.php" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-lg font-bold text-gray-900"><?php echo $pageTitle; ?></h1>
    <span class="px-2 py-0.5 text-xs font-mono font-bold rounded bg-gray-100 text-gray-600"><?php echo htmlspecialchars($quoteNo); ?></span>
    <div class="ml-auto flex gap-2">
        <button onclick="saveQuote(true)" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50 transition-colors">임시저장</button>
        <button onclick="saveQuote(false)" class="px-3 py-1 text-xs text-white rounded hover:opacity-90 transition-colors" style="background:#1E4E79;">저장</button>
    </div>
</div>

<!-- 고객 정보 -->
<div class="bg-white rounded-lg shadow mb-2">
    <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">고객 정보</div>
    <div class="px-3 py-2">
        <div class="relative mb-2">
            <input type="text" id="customerSearch" placeholder="거래처 검색 (회사명, 이름, 전화번호)"
                   class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   autocomplete="off">
            <div id="customerDropdown" class="absolute z-40 w-full bg-white border border-gray-200 rounded-b shadow-lg hidden max-h-48 overflow-y-auto"></div>
        </div>
        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16 flex-shrink-0">회사명</label>
                <input type="text" id="customer_company" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16 flex-shrink-0">담당자</label>
                <input type="text" id="customer_name" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16 flex-shrink-0">연락처</label>
                <input type="text" id="customer_phone" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16 flex-shrink-0">이메일</label>
                <input type="text" id="customer_email" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16 flex-shrink-0">주소</label>
                <input type="text" id="customer_address" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16 flex-shrink-0">사업자번호</label>
                <input type="text" id="customer_biz_no" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
        </div>
        <input type="hidden" id="customer_id" value="">
    </div>
</div>

<!-- 품목 목록 -->
<div class="bg-white rounded-lg shadow mb-2">
    <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg flex items-center justify-between" style="background:#1E4E79;color:#fff;">
        <span>품목 목록</span>
        <div class="flex gap-1">
            <button onclick="openCalcSelect()" class="px-2 py-0.5 text-[10px] bg-white/20 hover:bg-white/30 rounded transition-colors">🔢 계산기</button>
            <button onclick="openManualModal()" class="px-2 py-0.5 text-[10px] bg-white/20 hover:bg-white/30 rounded transition-colors">✏️ 수동</button>
            <button onclick="openExtraModal()" class="px-2 py-0.5 text-[10px] bg-white/20 hover:bg-white/30 rounded transition-colors">📦 부가항목</button>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 py-1.5 text-center text-[10px] font-medium text-gray-500 w-8">NO</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500 w-20">유형</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500">품목</th>
                    <th class="px-2 py-1.5 text-left text-[10px] font-medium text-gray-500">규격/옵션</th>
                    <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 w-16">수량</th>
                    <th class="px-2 py-1.5 text-center text-[10px] font-medium text-gray-500 w-10">단위</th>
                    <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 w-20">단가</th>
                    <th class="px-2 py-1.5 text-right text-[10px] font-medium text-gray-500 w-24">공급가</th>
                    <th class="px-2 py-1.5 text-center text-[10px] font-medium text-gray-500 w-8"></th>
                </tr>
            </thead>
            <tbody id="itemsBody" class="bg-white divide-y divide-gray-200">
                <tr id="emptyRow">
                    <td colspan="9" class="px-4 py-6 text-center text-xs text-gray-400">
                        품목을 추가하세요. 위 버튼을 클릭하거나 아래 추가 버튼을 이용할 수 있습니다.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- 추가 버튼 (테이블 아래) -->
    <div class="px-3 py-1.5 border-t border-gray-100 flex gap-2">
        <button onclick="openCalcSelect()" class="px-2 py-1 text-xs text-blue-600 hover:bg-blue-50 rounded transition-colors">+ 계산기로 추가</button>
        <button onclick="openManualModal()" class="px-2 py-1 text-xs text-green-600 hover:bg-green-50 rounded transition-colors">+ 수동 추가</button>
        <button onclick="openExtraModal()" class="px-2 py-1 text-xs text-orange-600 hover:bg-orange-50 rounded transition-colors">+ 부가항목</button>
    </div>
    <!-- 합계 -->
    <div class="px-3 py-2 border-t bg-gray-50 rounded-b-lg">
        <div class="grid grid-cols-2 gap-2">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-10">할인</label>
                <input type="number" id="discount_amount" value="0" min="0" oninput="updateTotals()"
                       class="w-28 px-2 py-1 text-xs text-right border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
                <span class="text-xs text-gray-400">원</span>
                <input type="text" id="discount_reason" placeholder="할인 사유"
                       class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="text-right space-y-0.5">
                <div class="text-xs text-gray-500">공급가액: <span id="totalSupply" class="font-semibold text-gray-800">0</span>원</div>
                <div class="text-xs text-gray-500">부가세: <span id="totalVat" class="font-semibold text-gray-800">0</span>원</div>
                <div class="text-xs text-gray-500">할인: <span id="totalDiscount" class="text-red-500 font-semibold">0</span>원</div>
                <div class="text-sm font-bold text-gray-900">합계: <span id="grandTotal" class="text-blue-700">0</span>원</div>
            </div>
        </div>
    </div>
</div>

<!-- 메모/조건 -->
<div class="bg-white rounded-lg shadow mb-4">
    <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">견적 조건</div>
    <div class="px-3 py-2 grid grid-cols-2 gap-x-4 gap-y-1.5">
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-500 w-16 flex-shrink-0">유효기간</label>
            <input type="number" id="valid_days" value="7" min="1" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
            <span class="text-xs text-gray-400">일</span>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-xs text-gray-500 w-16 flex-shrink-0">결제조건</label>
            <input type="text" id="payment_terms" value="발행일로부터 7일" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500">
        </div>
        <div class="flex items-start gap-2">
            <label class="text-xs text-gray-500 w-16 flex-shrink-0 mt-1">고객메모</label>
            <textarea id="customer_memo" rows="2" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
        </div>
        <div class="flex items-start gap-2">
            <label class="text-xs text-gray-500 w-16 flex-shrink-0 mt-1">관리자메모</label>
            <textarea id="admin_memo" rows="2" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 resize-none"></textarea>
        </div>
    </div>
</div>

</div>
</main>

<!-- ═══════════════════════════════════════════ -->
<!-- 계산기 선택 모달 (9개 제품 카드) -->
<!-- ═══════════════════════════════════════════ -->
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
            <button onclick="closeModal('calcSelectModal')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="px-4 py-4">
            <div class="grid grid-cols-3 gap-3">
                <button class="qc-card" style="--cg:#3b82f6" onclick="openCalculator('sticker')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#3b82f6,#2563eb)"><div><div class="qc-title">스티커</div><div class="qc-sub">맞춤형 스티커</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>방수 소재</li><li>자유 형태</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/sticker_new_s.png" alt="스티커"></div></div>
                </button>
                <button class="qc-card" style="--cg:#10b981" onclick="openCalculator('inserted')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#10b981,#059669)"><div><div class="qc-title">전단지</div><div class="qc-sub">홍보용 전단지</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>고해상도</li><li>빠른 제작</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/inserted_s.png" alt="전단지"></div></div>
                </button>
                <button class="qc-card" style="--cg:#8b5cf6" onclick="openCalculator('namecard')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed)"><div><div class="qc-title">명함</div><div class="qc-sub">전문 명함</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>UV 코팅</li><li>당일 제작</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/namecard_s.png" alt="명함"></div></div>
                </button>
                <button class="qc-card" style="--cg:#e11d48" onclick="openCalculator('envelope')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#e11d48,#be123c)"><div><div class="qc-title">봉투</div><div class="qc-sub">각종 봉투</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>창봉투</li><li>대량 주문</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/envelop_s.png" alt="봉투"></div></div>
                </button>
                <button class="qc-card" style="--cg:#06b6d4" onclick="openCalculator('cadarok')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#06b6d4,#0891b2)"><div><div class="qc-title">카다록</div><div class="qc-sub">제품 카탈로그</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>풀컬러</li><li>전문 편집</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/catalogue_s.png" alt="카다록"></div></div>
                </button>
                <button class="qc-card" style="--cg:#f97316" onclick="openCalculator('littleprint')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#f97316,#ea580c)"><div><div class="qc-title">포스터</div><div class="qc-sub">대형 포스터</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>대형 사이즈</li><li>고화질 출력</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/poster_s.png" alt="포스터"></div></div>
                </button>
                <button class="qc-card" style="--cg:#84cc16" onclick="openCalculator('ncrflambeau')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#84cc16,#65a30d)"><div><div class="qc-title">NCR양식</div><div class="qc-sub">양식지 제작</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>2~4연</li><li>무탄소 용지</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/ncr_s.png" alt="NCR양식"></div></div>
                </button>
                <button class="qc-card" style="--cg:#d946ef" onclick="openCalculator('merchandisebond')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#d946ef,#c026d3)"><div><div class="qc-title">상품권</div><div class="qc-sub">쿠폰/상품권</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>위조 방지</li><li>번호 인쇄</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/merchandise_s.png" alt="상품권"></div></div>
                </button>
                <button class="qc-card" style="--cg:#ef4444" onclick="openCalculator('msticker')">
                    <div class="qc-head" style="background:linear-gradient(135deg,#ef4444,#dc2626)"><div><div class="qc-title">자석스티커</div><div class="qc-sub">마그네틱 스티커</div></div></div>
                    <div class="qc-body"><ul class="qc-feats"><li>강력 자석</li><li>차량용 최적</li></ul><div class="qc-img"><img src="/ImgFolder/gate_picto/m_sticker_s.png" alt="자석스티커"></div></div>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- 계산기 폼 모달 (DB제품 + 스티커) -->
<!-- ═══════════════════════════════════════════ -->
<div class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" id="calcFormModal">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between px-4 py-2 border-b">
            <h3 id="calcFormTitle" class="text-sm font-semibold text-gray-900">계산기</h3>
            <button onclick="closeModal('calcFormModal')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="px-4 py-3" id="calcFormContent">
            <!-- DB 제품 폼 -->
            <div id="dbCalcForm" class="hidden space-y-2">
                <div id="dropdownContainer"></div>
                <div id="premiumContainer" class="hidden">
                    <p class="text-xs font-semibold text-gray-600 mb-1">추가 옵션</p>
                    <div id="premiumOptions" class="space-y-1"></div>
                </div>
            </div>
            <!-- 스티커 폼 -->
            <div id="stickerCalcForm" class="hidden space-y-2">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 w-14">재질</label>
                    <select id="stk_jong" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="calcStickerPrice()">
                        <option value="">선택</option>
                        <option value="jil 아트유광코팅">아트유광코팅</option>
                        <option value="jil 아트무광코팅">아트무광코팅</option>
                        <option value="jil 아트비코팅">아트비코팅</option>
                        <option value="jka 강접아트유광코팅">강접아트유광코팅</option>
                        <option value="cka 초강접아트코팅">초강접아트코팅</option>
                        <option value="cka 초강접아트비코팅">초강접아트비코팅</option>
                        <option value="jsp 유포지">유포지</option>
                        <option value="jsp 은데드롱">은데드롱</option>
                        <option value="jsp 투명스티커">투명스티커</option>
                        <option value="jil 모조비코팅">모조비코팅</option>
                        <option value="jsp 크라프트지">크라프트지</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 w-14">사이즈</label>
                    <input type="number" id="stk_garo" placeholder="가로(mm)" min="10" max="590" class="w-24 px-2 py-1 text-xs border border-gray-300 rounded" oninput="calcStickerPrice()">
                    <span class="text-xs text-gray-400">×</span>
                    <input type="number" id="stk_sero" placeholder="세로(mm)" min="10" max="590" class="w-24 px-2 py-1 text-xs border border-gray-300 rounded" oninput="calcStickerPrice()">
                    <span class="text-xs text-gray-400">mm</span>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 w-14">매수</label>
                    <select id="stk_mesu" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="calcStickerPrice()">
                        <option value="">선택</option>
                        <option value="500">500매</option><option value="1000">1,000매</option>
                        <option value="2000">2,000매</option><option value="3000">3,000매</option>
                        <option value="4000">4,000매</option><option value="5000">5,000매</option>
                        <option value="6000">6,000매</option><option value="7000">7,000매</option>
                        <option value="8000">8,000매</option><option value="9000">9,000매</option>
                        <option value="10000">10,000매</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 w-14">모양</label>
                    <select id="stk_domusong" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="calcStickerPrice()">
                        <option value="00000 사각">기본사각</option>
                        <option value="08000 사각도무송">사각도무송 (+8,000)</option>
                        <option value="08000 귀돌">귀돌이 (+8,000)</option>
                        <option value="08000 원형">원형 (+8,000)</option>
                        <option value="08000 타원">타원형 (+8,000)</option>
                        <option value="19000 복잡">모양도무송 (+19,000)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 w-14">디자인</label>
                    <select id="stk_uhyung" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="calcStickerPrice()">
                        <option value="0">없음</option>
                        <option value="10000">기본 (+10,000)</option>
                        <option value="30000">복잡 (+30,000)</option>
                    </select>
                </div>
            </div>
            <!-- 가격 결과 -->
            <div id="calcResult" class="mt-3 p-2 bg-blue-50 rounded hidden">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-600">공급가액</span>
                    <span class="text-sm font-bold text-blue-700" id="calcSupplyPrice">0원</span>
                </div>
                <div class="flex items-center justify-between mt-0.5">
                    <span class="text-[10px] text-gray-400">VAT 포함</span>
                    <span class="text-xs text-gray-500" id="calcTotalPrice">0원</span>
                </div>
            </div>
        </div>
        <div class="px-4 py-2 border-t flex justify-end gap-2">
            <button onclick="closeModal('calcFormModal')" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50">취소</button>
            <button onclick="addCalcItem()" id="calcAddBtn" disabled class="px-3 py-1 text-xs text-white rounded disabled:opacity-50" style="background:#1E4E79;">품목에 추가</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- 수동 입력 모달 -->
<!-- ═══════════════════════════════════════════ -->
<div class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" id="manualModal">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-4 py-2 border-b">
            <h3 class="text-sm font-semibold text-gray-900">수동 품목 추가</h3>
            <button onclick="closeModal('manualModal')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16">품목명</label>
                <input type="text" id="man_name" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" placeholder="예: 명함 (특수)">
            </div>
            <div class="flex items-start gap-2">
                <label class="text-xs text-gray-500 w-16 mt-1">규격/설명</label>
                <textarea id="man_spec" rows="2" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded resize-none" placeholder="옵션, 사이즈 등"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16">수량</label>
                <input type="number" id="man_qty" min="1" value="1" class="w-20 px-2 py-1 text-xs border border-gray-300 rounded text-right" oninput="calcManualPrice()">
                <select id="man_unit" class="px-2 py-1 text-xs border border-gray-300 rounded">
                    <option>매</option><option>연</option><option>부</option><option>권</option><option>건</option><option>개</option><option>세트</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16">단가</label>
                <input type="number" id="man_unit_price" min="0" value="0" class="w-28 px-2 py-1 text-xs border border-gray-300 rounded text-right" oninput="calcManualPrice()">
                <span class="text-xs text-gray-400">원</span>
                <span class="text-xs text-gray-400 ml-2">= 공급가: <strong id="man_supply">0</strong>원</span>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-16">비고</label>
                <input type="text" id="man_note" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded">
            </div>
        </div>
        <div class="px-4 py-2 border-t flex justify-end gap-2">
            <button onclick="closeModal('manualModal')" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50">취소</button>
            <button onclick="addManualItem()" class="px-3 py-1 text-xs text-white rounded" style="background:#1E4E79;">추가</button>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- 부가항목 모달 -->
<!-- ═══════════════════════════════════════════ -->
<div class="fixed inset-0 bg-black/40 z-50 hidden items-center justify-center" id="extraModal">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-sm mx-4">
        <div class="flex items-center justify-between px-4 py-2 border-b">
            <h3 class="text-sm font-semibold text-gray-900">부가항목 추가</h3>
            <button onclick="closeModal('extraModal')" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-12">종류</label>
                <select id="ext_category" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="onExtraCategoryChange()">
                    <option value="shipping">🚚 택배비</option>
                    <option value="design">🎨 디자인비</option>
                    <option value="rush">⚡ 급행료</option>
                    <option value="processing">🔧 가공비</option>
                    <option value="packing">📦 포장비</option>
                    <option value="other">기타</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-12">금액</label>
                <input type="number" id="ext_price" min="0" value="0" class="w-28 px-2 py-1 text-xs border border-gray-300 rounded text-right">
                <span class="text-xs text-gray-400">원</span>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 w-12">비고</label>
                <input type="text" id="ext_note" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded">
            </div>
        </div>
        <div class="px-4 py-2 border-t flex justify-end gap-2">
            <button onclick="closeModal('extraModal')" class="px-3 py-1 text-xs border border-gray-300 rounded text-gray-600 hover:bg-gray-50">취소</button>
            <button onclick="addExtraItem()" class="px-3 py-1 text-xs text-white rounded" style="background:#1E4E79;">추가</button>
        </div>
    </div>
</div>

<!-- 토스트 -->
<div id="toast" class="fixed top-4 right-4 z-[9999] hidden">
    <div id="toastInner" class="px-4 py-2 rounded-lg shadow-lg text-sm font-medium text-white"></div>
</div>

<script>
// ═══════════════════════════════════════════════
// 상태 관리
// ═══════════════════════════════════════════════
var quoteItems = [];
var selectedCustomerId = null;
var currentCalcProduct = null;
var currentCalcResult = null;
var editId = <?php echo $editId; ?>;
var searchTimer = null;

var EXTRA_LABELS = {
    shipping: '택배비', design: '디자인비', rush: '급행료',
    processing: '가공비', packing: '포장비', other: '기타'
};

var ITEM_TYPE_BADGES = {
    product: '<span class="px-1 py-0.5 text-[9px] rounded bg-blue-50 text-blue-600">계산</span>',
    manual: '<span class="px-1 py-0.5 text-[9px] rounded bg-green-50 text-green-600">수동</span>',
    extra: '<span class="px-1 py-0.5 text-[9px] rounded bg-orange-50 text-orange-600">부가</span>'
};

// ═══════════════════════════════════════════════
// 품목 렌더링
// ═══════════════════════════════════════════════
function renderItems() {
    var tbody = document.getElementById('itemsBody');
    if (quoteItems.length === 0) {
        tbody.innerHTML = '<tr id="emptyRow"><td colspan="9" class="px-4 py-6 text-center text-xs text-gray-400">품목을 추가하세요.</td></tr>';
        return;
    }
    var html = '';
    for (var i = 0; i < quoteItems.length; i++) {
        var it = quoteItems[i];
        html += '<tr class="hover:bg-gray-50">';
        html += '<td class="px-2 py-1 text-center text-xs text-gray-400">' + (i + 1) + '</td>';
        html += '<td class="px-2 py-1">' + (ITEM_TYPE_BADGES[it.item_type] || '') + '</td>';
        html += '<td class="px-2 py-1 text-xs font-medium text-gray-800">' + esc(it.product_name) + '</td>';
        html += '<td class="px-2 py-1 text-xs text-gray-500" title="' + esc(it.specification) + '">' + truncate(it.specification, 40) + '</td>';
        html += '<td class="px-2 py-1 text-xs text-right text-gray-700">' + fmt(it.quantity) + '</td>';
        html += '<td class="px-2 py-1 text-xs text-center text-gray-500">' + esc(it.unit) + '</td>';
        html += '<td class="px-2 py-1 text-xs text-right text-gray-600">' + fmt(it.unit_price) + '</td>';
        html += '<td class="px-2 py-1 text-xs text-right font-semibold text-gray-800">' + fmt(it.supply_price) + '</td>';
        html += '<td class="px-2 py-1 text-center"><button onclick="removeItem(' + i + ')" class="p-0.5 text-red-400 hover:text-red-600"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button></td>';
        html += '</tr>';
    }
    tbody.innerHTML = html;
}

function addItem(item) {
    quoteItems.push(item);
    renderItems();
    updateTotals();
}

function removeItem(idx) {
    quoteItems.splice(idx, 1);
    renderItems();
    updateTotals();
}

function updateTotals() {
    var supply = 0;
    for (var i = 0; i < quoteItems.length; i++) {
        supply += parseInt(quoteItems[i].supply_price) || 0;
    }
    var vat = Math.floor(supply * 0.1);
    var discount = parseInt(document.getElementById('discount_amount').value) || 0;
    var grand = supply + vat - discount;

    document.getElementById('totalSupply').textContent = fmt(supply);
    document.getElementById('totalVat').textContent = fmt(vat);
    document.getElementById('totalDiscount').textContent = fmt(discount);
    document.getElementById('grandTotal').textContent = fmt(grand);
}

// ═══════════════════════════════════════════════
// 모달 유틸
// ═══════════════════════════════════════════════
function openModal(id) {
    var el = document.getElementById(id);
    el.classList.remove('hidden');
    el.classList.add('flex');
}
function closeModal(id) {
    var el = document.getElementById(id);
    el.classList.add('hidden');
    el.classList.remove('flex');
}

// ═══════════════════════════════════════════════
// 계산기 선택
// ═══════════════════════════════════════════════
function openCalcSelect() { openModal('calcSelectModal'); }

function openCalculator(product) {
    closeModal('calcSelectModal');
    currentCalcProduct = product;
    currentCalcResult = null;
    document.getElementById('calcAddBtn').disabled = true;
    document.getElementById('calcResult').classList.add('hidden');

    if (product === 'sticker') {
        document.getElementById('calcFormTitle').textContent = '스티커 계산기';
        document.getElementById('dbCalcForm').classList.add('hidden');
        document.getElementById('stickerCalcForm').classList.remove('hidden');
        resetStickerForm();
    } else {
        var names = {namecard:'명함',inserted:'전단지',envelope:'봉투',littleprint:'포스터',merchandisebond:'상품권',cadarok:'카다록',ncrflambeau:'NCR양식',msticker:'자석스티커'};
        document.getElementById('calcFormTitle').textContent = (names[product] || product) + ' 계산기';
        document.getElementById('stickerCalcForm').classList.add('hidden');
        document.getElementById('dbCalcForm').classList.remove('hidden');
        loadDbCalcDropdowns(product);
    }
    openModal('calcFormModal');
}

// ═══════════════════════════════════════════════
// DB 제품 캐스케이딩 드롭다운
// ═══════════════════════════════════════════════
var dbCalcState = { dropdowns: [], selectedValues: {} };

function loadDbCalcDropdowns(product) {
    var container = document.getElementById('dropdownContainer');
    container.innerHTML = '<p class="text-xs text-gray-400">로딩중...</p>';
    dbCalcState = { dropdowns: [], selectedValues: {}, product: product };

    fetch('/api/quote-engine/options.php?action=dropdown&product=' + product + '&parent=0')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success || !data.data || data.data.length === 0) {
                container.innerHTML = '<p class="text-xs text-red-500">옵션을 불러올 수 없습니다.</p>';
                return;
            }
            container.innerHTML = '';
            addDropdownLevel(container, product, data.data, 0, '종류');
            loadPremiumOptions(product);
        })
        .catch(function() { container.innerHTML = '<p class="text-xs text-red-500">API 오류</p>'; });
}

function addDropdownLevel(container, product, options, level, labelText) {
    var id = 'dbDrop_' + level;
    var div = document.createElement('div');
    div.className = 'flex items-center gap-2 mb-1.5';
    div.id = 'dbDropRow_' + level;
    div.innerHTML = '<label class="text-xs text-gray-500 w-14">' + labelText + '</label>' +
        '<select id="' + id + '" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="onDbDropdownChange(' + level + ')">' +
        '<option value="">선택</option></select>';
    container.appendChild(div);

    var sel = document.getElementById(id);
    for (var i = 0; i < options.length; i++) {
        var opt = document.createElement('option');
        opt.value = options[i].no || options[i].id || '';
        opt.textContent = options[i].title || options[i].name || options[i].label || '';
        opt.dataset.bigno = options[i].BigNo || '';
        sel.appendChild(opt);
    }
    dbCalcState.dropdowns[level] = { el: sel, options: options };
}

function onDbDropdownChange(level) {
    var sel = dbCalcState.dropdowns[level].el;
    var val = sel.value;
    var product = dbCalcState.product;

    // 하위 드롭다운 제거
    for (var i = level + 1; i < 10; i++) {
        var row = document.getElementById('dbDropRow_' + i);
        if (row) row.remove();
        delete dbCalcState.dropdowns[i];
    }
    // 수량 드롭다운 제거
    var qtyRow = document.getElementById('dbDropRow_qty');
    if (qtyRow) qtyRow.remove();
    var poRow = document.getElementById('dbDropRow_po');
    if (poRow) poRow.remove();

    dbCalcState.selectedValues['level_' + level] = val;
    currentCalcResult = null;
    document.getElementById('calcAddBtn').disabled = true;
    document.getElementById('calcResult').classList.add('hidden');

    if (!val) return;

    // 하위 옵션 로드
    fetch('/api/quote-engine/options.php?action=dropdown&product=' + product + '&parent=' + val)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data && data.data.length > 0) {
                var labels = ['종류', '재질', '세부', '기타'];
                addDropdownLevel(document.getElementById('dropdownContainer'), product, data.data, level + 1, labels[level + 1] || '옵션');
            } else {
                loadQuantities(product, level);
            }
        });
}

function loadQuantities(product, lastLevel) {
    var container = document.getElementById('dropdownContainer');
    var filters = {};
    if (dbCalcState.selectedValues['level_0']) filters.style = dbCalcState.selectedValues['level_0'];
    if (dbCalcState.selectedValues['level_1']) filters.section = dbCalcState.selectedValues['level_1'];
    if (dbCalcState.selectedValues['level_2']) filters.tree_select = dbCalcState.selectedValues['level_2'];

    var qs = 'action=quantities&product=' + product;
    for (var k in filters) { qs += '&' + k + '=' + encodeURIComponent(filters[k]); }

    fetch('/api/quote-engine/options.php?' + qs)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data && data.data.length > 0) {
                var div = document.createElement('div');
                div.className = 'flex items-center gap-2 mb-1.5';
                div.id = 'dbDropRow_qty';
                div.innerHTML = '<label class="text-xs text-gray-500 w-14">수량</label>' +
                    '<select id="dbDrop_qty" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="onQtyChange()"><option value="">선택</option></select>';
                container.appendChild(div);

                var sel = document.getElementById('dbDrop_qty');
                for (var i = 0; i < data.data.length; i++) {
                    var opt = document.createElement('option');
                    opt.value = data.data[i].value;
                    opt.textContent = data.data[i].label;
                    sel.appendChild(opt);
                }
            }
        });
}

function onQtyChange() {
    var qty = document.getElementById('dbDrop_qty').value;
    if (!qty) { currentCalcResult = null; document.getElementById('calcAddBtn').disabled = true; document.getElementById('calcResult').classList.add('hidden'); return; }
    calculateDbPrice();
}

function calculateDbPrice() {
    var params = { product: dbCalcState.product };
    if (dbCalcState.selectedValues['level_0']) params.style = dbCalcState.selectedValues['level_0'];
    if (dbCalcState.selectedValues['level_1']) params.section = dbCalcState.selectedValues['level_1'];
    if (dbCalcState.selectedValues['level_2']) params.tree_select = dbCalcState.selectedValues['level_2'];
    var qtyEl = document.getElementById('dbDrop_qty');
    if (qtyEl) params.quantity = qtyEl.value;

    // 프리미엄 옵션
    var poSelects = document.querySelectorAll('[data-premium-option]');
    var premiumOpts = {};
    poSelects.forEach(function(sel) {
        if (sel.value) premiumOpts[sel.dataset.premiumOption] = parseInt(sel.value);
    });
    if (Object.keys(premiumOpts).length > 0) params.premium_options = premiumOpts;

    fetch('/api/quote-engine/calculate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(params)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            currentCalcResult = data;
            document.getElementById('calcSupplyPrice').textContent = fmt(data.supply_price) + '원';
            document.getElementById('calcTotalPrice').textContent = fmt(data.total) + '원';
            document.getElementById('calcResult').classList.remove('hidden');
            document.getElementById('calcAddBtn').disabled = false;
        } else {
            showToast(data.error || '계산 실패', 'error');
        }
    });
}

// ═══════════════════════════════════════════════
// 프리미엄 옵션 로드
// ═══════════════════════════════════════════════
function loadPremiumOptions(product) {
    fetch('/api/quote-engine/options.php?action=premium&product=' + product)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var container = document.getElementById('premiumOptions');
            var wrapper = document.getElementById('premiumContainer');
            container.innerHTML = '';
            if (!data.success || !data.data || data.data.length === 0) { wrapper.classList.add('hidden'); return; }
            wrapper.classList.remove('hidden');
            data.data.forEach(function(opt) {
                var div = document.createElement('div');
                div.className = 'flex items-center gap-2';
                var html = '<label class="text-xs text-gray-500 w-14">' + esc(opt.option_name) + '</label>';
                html += '<select data-premium-option="' + esc(opt.option_name) + '" class="flex-1 px-2 py-1 text-xs border border-gray-300 rounded" onchange="onPremiumChange()">';
                html += '<option value="">선택안함</option>';
                opt.variants.forEach(function(v) {
                    var priceLabel = '';
                    if (v.pricing_config && v.pricing_config.fixed) priceLabel = ' (+' + fmt(v.pricing_config.fixed) + '원)';
                    html += '<option value="' + v.variant_id + '">' + esc(v.variant_name) + priceLabel + '</option>';
                });
                html += '</select>';
                div.innerHTML = html;
                container.appendChild(div);
            });
        });
}

function onPremiumChange() {
    if (document.getElementById('dbDrop_qty') && document.getElementById('dbDrop_qty').value) {
        calculateDbPrice();
    }
}

// ═══════════════════════════════════════════════
// 스티커 가격 계산
// ═══════════════════════════════════════════════
function resetStickerForm() {
    document.getElementById('stk_jong').value = '';
    document.getElementById('stk_garo').value = '';
    document.getElementById('stk_sero').value = '';
    document.getElementById('stk_mesu').value = '';
    document.getElementById('stk_domusong').value = '00000 사각';
    document.getElementById('stk_uhyung').value = '0';
}

function calcStickerPrice() {
    var jong = document.getElementById('stk_jong').value;
    var garo = parseInt(document.getElementById('stk_garo').value) || 0;
    var sero = parseInt(document.getElementById('stk_sero').value) || 0;
    var mesu = parseInt(document.getElementById('stk_mesu').value) || 0;
    if (!jong || !garo || !sero || !mesu) {
        currentCalcResult = null;
        document.getElementById('calcAddBtn').disabled = true;
        document.getElementById('calcResult').classList.add('hidden');
        return;
    }

    fetch('/api/quote-engine/calculate.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            product: 'sticker',
            jong: jong,
            garo: garo,
            sero: sero,
            mesu: mesu,
            domusong: document.getElementById('stk_domusong').value,
            uhyung: parseInt(document.getElementById('stk_uhyung').value) || 0
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            currentCalcResult = data;
            document.getElementById('calcSupplyPrice').textContent = fmt(data.supply_price) + '원';
            document.getElementById('calcTotalPrice').textContent = fmt(data.total) + '원';
            document.getElementById('calcResult').classList.remove('hidden');
            document.getElementById('calcAddBtn').disabled = false;
        }
    });
}

// ═══════════════════════════════════════════════
// 계산기 결과 품목 추가
// ═══════════════════════════════════════════════
function addCalcItem() {
    if (!currentCalcResult || !currentCalcResult.success) return;
    var r = currentCalcResult;
    addItem({
        item_type: 'product',
        product_type: currentCalcProduct,
        product_name: r.product_name || currentCalcProduct,
        specification: r.specification || '',
        quantity: r.quantity || 0,
        unit: r.unit || '매',
        unit_price: r.unit_price || 0,
        supply_price: r.supply_price || 0,
        extra_category: null,
        note: '',
        source_data: r.source_data || null
    });
    closeModal('calcFormModal');
    showToast('품목이 추가되었습니다.', 'success');
}

// ═══════════════════════════════════════════════
// 수동 입력
// ═══════════════════════════════════════════════
function openManualModal() {
    document.getElementById('man_name').value = '';
    document.getElementById('man_spec').value = '';
    document.getElementById('man_qty').value = '1';
    document.getElementById('man_unit_price').value = '0';
    document.getElementById('man_unit').value = '매';
    document.getElementById('man_note').value = '';
    document.getElementById('man_supply').textContent = '0';
    openModal('manualModal');
}

function calcManualPrice() {
    var qty = parseInt(document.getElementById('man_qty').value) || 0;
    var up = parseInt(document.getElementById('man_unit_price').value) || 0;
    document.getElementById('man_supply').textContent = fmt(qty * up);
}

function addManualItem() {
    var name = document.getElementById('man_name').value.trim();
    if (!name) { showToast('품목명을 입력하세요.', 'error'); return; }
    var qty = parseInt(document.getElementById('man_qty').value) || 1;
    var up = parseInt(document.getElementById('man_unit_price').value) || 0;
    addItem({
        item_type: 'manual',
        product_type: null,
        product_name: name,
        specification: document.getElementById('man_spec').value.trim(),
        quantity: qty,
        unit: document.getElementById('man_unit').value,
        unit_price: up,
        supply_price: qty * up,
        extra_category: null,
        note: document.getElementById('man_note').value.trim(),
        source_data: null
    });
    closeModal('manualModal');
    showToast('수동 품목이 추가되었습니다.', 'success');
}

// ═══════════════════════════════════════════════
// 부가항목
// ═══════════════════════════════════════════════
function openExtraModal() {
    document.getElementById('ext_category').value = 'shipping';
    document.getElementById('ext_price').value = '0';
    document.getElementById('ext_note').value = '';
    openModal('extraModal');
}

function onExtraCategoryChange() {
    var cat = document.getElementById('ext_category').value;
    var defaults = { shipping: 4000, design: 30000, rush: 20000, processing: 0, packing: 0, other: 0 };
    document.getElementById('ext_price').value = defaults[cat] || 0;
}

function addExtraItem() {
    var cat = document.getElementById('ext_category').value;
    var price = parseInt(document.getElementById('ext_price').value) || 0;
    if (price <= 0) { showToast('금액을 입력하세요.', 'error'); return; }
    addItem({
        item_type: 'extra',
        product_type: null,
        product_name: EXTRA_LABELS[cat] || cat,
        specification: '',
        quantity: 1,
        unit: '건',
        unit_price: price,
        supply_price: price,
        extra_category: cat,
        note: document.getElementById('ext_note').value.trim(),
        source_data: null
    });
    closeModal('extraModal');
    showToast('부가항목이 추가되었습니다.', 'success');
}

// ═══════════════════════════════════════════════
// 고객 검색
// ═══════════════════════════════════════════════
document.getElementById('customerSearch').addEventListener('input', function() {
    var q = this.value.trim();
    clearTimeout(searchTimer);
    if (q.length < 1) { document.getElementById('customerDropdown').classList.add('hidden'); return; }
    searchTimer = setTimeout(function() {
        fetch('/api/quote-engine/customers.php?action=search&q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var dd = document.getElementById('customerDropdown');
                if (!data.success || !data.data || data.data.length === 0) {
                    dd.innerHTML = '<div class="px-3 py-2 text-xs text-gray-400">결과 없음</div>';
                    dd.classList.remove('hidden');
                    return;
                }
                var html = '';
                data.data.forEach(function(c) {
                    html += '<div class="px-3 py-1.5 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0" onclick="selectCustomer(' + c.id + ',this)">';
                    html += '<div class="text-xs font-medium text-gray-800">' + esc(c.company || '') + '</div>';
                    html += '<div class="text-[10px] text-gray-500">' + esc(c.name || '') + ' · ' + esc(c.phone || '') + '</div>';
                    html += '<input type="hidden" data-c=\'' + JSON.stringify(c).replace(/'/g, '&#39;') + '\'>';
                    html += '</div>';
                });
                dd.innerHTML = html;
                dd.classList.remove('hidden');
            });
    }, 300);
});

document.getElementById('customerSearch').addEventListener('blur', function() {
    setTimeout(function() { document.getElementById('customerDropdown').classList.add('hidden'); }, 200);
});

function selectCustomer(id, el) {
    var dataEl = el.querySelector('input[data-c]');
    var c = JSON.parse(dataEl.dataset.c);
    selectedCustomerId = c.id;
    document.getElementById('customer_id').value = c.id;
    document.getElementById('customer_company').value = c.company || '';
    document.getElementById('customer_name').value = c.name || '';
    document.getElementById('customer_phone').value = c.phone || '';
    document.getElementById('customer_email').value = c.email || '';
    document.getElementById('customer_address').value = c.address || '';
    document.getElementById('customer_biz_no').value = c.business_number || '';
    document.getElementById('customerSearch').value = c.company || c.name || '';
    document.getElementById('customerDropdown').classList.add('hidden');
}

// ═══════════════════════════════════════════════
// 저장
// ═══════════════════════════════════════════════
function saveQuote(isDraft) {
    if (quoteItems.length === 0) { showToast('품목을 1개 이상 추가하세요.', 'error'); return; }

    var payload = {
        id: editId || null,
        doc_type: 'quotation',
        customer_id: selectedCustomerId || document.getElementById('customer_id').value || null,
        customer_company: gv('customer_company'),
        customer_name: gv('customer_name'),
        customer_phone: gv('customer_phone'),
        customer_email: gv('customer_email'),
        customer_address: gv('customer_address'),
        customer_biz_no: gv('customer_biz_no'),
        valid_days: parseInt(gv('valid_days')) || 7,
        payment_terms: gv('payment_terms'),
        customer_memo: gv('customer_memo'),
        admin_memo: gv('admin_memo'),
        discount_amount: parseInt(gv('discount_amount')) || 0,
        discount_reason: gv('discount_reason'),
        is_draft: isDraft,
        items: quoteItems
    };

    fetch('/api/quote-engine/save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast((isDraft ? '임시저장' : '저장') + ' 완료: ' + (data.quote_no || ''), 'success');
            setTimeout(function() { location.href = 'index.php'; }, 800);
        } else {
            showToast(data.error || '저장 실패', 'error');
        }
    })
    .catch(function() { showToast('서버 오류', 'error'); });
}

// ═══════════════════════════════════════════════
// 유틸
// ═══════════════════════════════════════════════
function gv(id) { var el = document.getElementById(id); return el ? el.value.trim() : ''; }
function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
function fmt(n) { return (parseInt(n) || 0).toLocaleString(); }
function truncate(s, n) { if (!s) return ''; return s.length > n ? s.substring(0, n) + '…' : s; }

function showToast(msg, type) {
    var t = document.getElementById('toast');
    var inner = document.getElementById('toastInner');
    inner.textContent = msg;
    inner.className = 'px-4 py-2 rounded-lg shadow-lg text-sm font-medium text-white ' + (type === 'error' ? 'bg-red-500' : 'bg-green-500');
    t.classList.remove('hidden');
    setTimeout(function() { t.classList.add('hidden'); }, 2500);
}

// ═══════════════════════════════════════════════
// 수정 모드 초기화
// ═══════════════════════════════════════════════
<?php if ($editQuote): ?>
(function() {
    var q = <?php echo json_encode($editQuote, JSON_UNESCAPED_UNICODE); ?>;
    var items = <?php echo json_encode($editItems, JSON_UNESCAPED_UNICODE); ?>;

    if (q.customer_id) { selectedCustomerId = q.customer_id; document.getElementById('customer_id').value = q.customer_id; }
    document.getElementById('customer_company').value = q.customer_company || '';
    document.getElementById('customer_name').value = q.customer_name || '';
    document.getElementById('customer_phone').value = q.customer_phone || '';
    document.getElementById('customer_email').value = q.customer_email || '';
    document.getElementById('customer_address').value = q.customer_address || '';
    document.getElementById('customer_biz_no').value = q.customer_biz_no || '';
    document.getElementById('valid_days').value = q.valid_days || 7;
    document.getElementById('payment_terms').value = q.payment_terms || '';
    document.getElementById('customer_memo').value = q.customer_memo || '';
    document.getElementById('admin_memo').value = q.admin_memo || '';
    document.getElementById('discount_amount').value = q.discount_amount || 0;
    document.getElementById('discount_reason').value = q.discount_reason || '';

    for (var i = 0; i < items.length; i++) {
        var it = items[i];
        quoteItems.push({
            item_type: it.item_type || 'manual',
            product_type: it.product_type || null,
            product_name: it.product_name || '',
            specification: it.specification || '',
            quantity: parseFloat(it.quantity) || 0,
            unit: it.unit || '',
            unit_price: parseInt(it.unit_price) || 0,
            supply_price: parseInt(it.supply_price) || 0,
            extra_category: it.extra_category || null,
            note: it.note || '',
            source_data: it.source_data ? (typeof it.source_data === 'string' ? JSON.parse(it.source_data) : it.source_data) : null
        });
    }
    renderItems();
    updateTotals();
})();
<?php endif; ?>
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
