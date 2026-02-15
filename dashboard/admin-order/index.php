<?php
/**
 * 관리자 주문 등록 - Dashboard Tailwind Style
 * 전화/비회원 주문을 관리자가 직접 등록하는 폼
 * 
 * 패턴 참고: admin/mlangprintauto/quote/create.php
 * INSERT 대상: mlangorder_printauto 테이블
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../../db.php';

if (!$db) { die('DB 연결 실패'); }
mysqli_set_charset($db, 'utf8mb4');

$unitOptions = ['매', '연', '부', '권', '개', '장', '식'];

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<main class="flex-1 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-2">
        <!-- 헤더 -->
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-2">
                <h1 class="text-lg font-bold text-gray-900">관리자 주문 등록</h1>
                <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full font-medium">전화/비회원 주문</span>
            </div>
            <div class="flex items-center gap-1.5">
                <a href="/dashboard/orders/" class="px-3 py-1 text-xs text-gray-600 border border-gray-300 rounded hover:bg-gray-100">← 주문 목록</a>
                <button onclick="submitOrder()" class="px-4 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700 font-medium" id="submitBtn">주문 등록</button>
            </div>
        </div>

        <!-- 고객 정보 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">고객 정보</div>
            <div class="px-3 py-2">
                <div class="grid grid-cols-[70px_1fr_70px_1fr] gap-x-2 gap-y-1 items-center">
                    <label class="text-xs text-gray-500">성명 <span class="text-red-500">*</span></label>
                    <input type="text" id="customer_name" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="주문자 성명" required>
                    <label class="text-xs text-gray-500">전화번호 <span class="text-red-500">*</span></label>
                    <input type="tel" id="customer_phone" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="02-0000-0000">
                    <label class="text-xs text-gray-500">휴대폰</label>
                    <input type="tel" id="customer_mobile" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="010-0000-0000">
                    <label class="text-xs text-gray-500">이메일</label>
                    <input type="email" id="customer_email" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="email@example.com">
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
            <div class="grid items-center text-xs font-medium px-1 border-b border-gray-200" style="grid-template-columns:36px 1fr 2fr 70px 80px 90px 28px;background:#f9fafb;color:#6b7280;letter-spacing:0.025em;">
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

        <!-- 배송/결제 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">배송 / 결제</div>
            <div class="px-3 py-2">
                <div class="grid grid-cols-[70px_1fr_70px_1fr] gap-x-2 gap-y-1 items-center">
                    <label class="text-xs text-gray-500">수령방법</label>
                    <select id="delivery_method" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="택배">택배</option>
                        <option value="방문">방문 수령</option>
                        <option value="오토바이">오토바이 퀵</option>
                        <option value="다마스">다마스</option>
                    </select>
                    <label class="text-xs text-gray-500">결제방법</label>
                    <select id="payment_method" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="계좌이체">계좌이체</option>
                        <option value="카드결제">카드결제</option>
                        <option value="현금">현금</option>
                        <option value="기타">기타</option>
                    </select>
                </div>
                <!-- 주소 영역 (택배 선택 시) -->
                <div id="addressSection" class="mt-2">
                    <div class="grid grid-cols-[70px_1fr_auto] gap-x-2 gap-y-1 items-center">
                        <label class="text-xs text-gray-500">우편번호</label>
                        <input type="text" id="postcode" class="w-28 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="우편번호" readonly>
                        <button onclick="openPostcode()" class="px-2 py-1 text-xs text-white bg-gray-500 rounded hover:bg-gray-600">주소검색</button>
                    </div>
                    <div class="grid grid-cols-[70px_1fr] gap-x-2 gap-y-1 items-center mt-1">
                        <label class="text-xs text-gray-500">주소</label>
                        <input type="text" id="address" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="주소" readonly>
                        <label class="text-xs text-gray-500">상세주소</label>
                        <input type="text" id="detail_address" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="상세주소 입력">
                    </div>
                </div>
                <!-- 입금자명 (계좌이체 시) -->
                <div id="bankNameSection" class="mt-1">
                    <div class="grid grid-cols-[70px_1fr] gap-x-2 items-center">
                        <label class="text-xs text-gray-500">입금자명</label>
                        <input type="text" id="bankname" class="w-48 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="입금자명">
                    </div>
                </div>
            </div>
        </div>

        <!-- 사업자 정보 카드 (접이식) -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg flex items-center justify-between cursor-pointer" style="background:#1E4E79;color:#fff;" onclick="toggleBusinessInfo()">
                <span>사업자 정보 (선택)</span>
                <span id="bizToggleIcon" class="text-white/70 text-sm transition-transform">▼</span>
            </div>
            <div id="businessInfoSection" class="px-3 py-2 hidden">
                <div class="grid grid-cols-[80px_1fr_80px_1fr] gap-x-2 gap-y-1 items-center">
                    <label class="text-xs text-gray-500">상호</label>
                    <input type="text" id="biz_name" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="회사명">
                    <label class="text-xs text-gray-500">사업자번호</label>
                    <input type="text" id="biz_number" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="000-00-00000">
                    <label class="text-xs text-gray-500">대표자명</label>
                    <input type="text" id="biz_owner" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="대표자명">
                    <label class="text-xs text-gray-500">업태</label>
                    <input type="text" id="biz_type" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="업태">
                    <label class="text-xs text-gray-500">종목</label>
                    <input type="text" id="biz_item" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="종목">
                    <label class="text-xs text-gray-500">세금용 메일</label>
                    <input type="email" id="biz_tax_email" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="tax@example.com">
                </div>
            </div>
        </div>

        <!-- 교정 파일 업로드 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">교정 파일 (선택)</div>
            <div class="px-3 py-2">
                <div class="flex items-center gap-2">
                    <input type="file" id="proofFiles" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.ai,.psd,.zip" class="text-xs">
                    <span class="text-xs text-gray-400">JPG, PNG, PDF, AI, PSD, ZIP (20MB/파일)</span>
                </div>
                <div id="filePreview" class="flex flex-wrap gap-2 mt-2"></div>
            </div>
        </div>

        <!-- 메모 카드 -->
        <div class="bg-white rounded-lg shadow mb-2">
            <div class="px-3 py-1.5 border-b font-semibold text-xs rounded-t-lg" style="background:#1E4E79;color:#fff;">메모</div>
            <div class="px-3 py-2">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">고객 요청사항</label>
                        <textarea id="customer_memo" rows="4" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="고객이 요청한 내용"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-0.5">주문 상태</label>
                        <select id="order_status" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 mb-1">
                            <option value="2">주문접수</option>
                            <option value="3">접수완료</option>
                            <option value="5">시안제작중</option>
                        </select>
                        <label class="block text-xs text-gray-500 mb-0.5 mt-1">관리자 메모</label>
                        <textarea id="admin_memo" rows="2" class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500 resize-none" placeholder="내부 메모 (고객 미공개)"></textarea>
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
                <label class="block text-xs text-gray-500 mb-0.5">품목 유형</label>
                <select id="manual_product_type" class="w-full px-2 py-1 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">직접 입력</option>
                    <option value="sticker">스티커</option>
                    <option value="inserted">전단지</option>
                    <option value="namecard">명함</option>
                    <option value="envelope">봉투</option>
                    <option value="cadarok">카다록</option>
                    <option value="littleprint">포스터</option>
                    <option value="ncrflambeau">NCR양식지</option>
                    <option value="merchandisebond">상품권</option>
                    <option value="msticker">자석스티커</option>
                </select>
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

<!-- 계산기 선택 모달 (quote/create.php 동일) -->
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

<!-- Daum 주소검색 API -->
<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>

<script>
// ── 품목 데이터 (클라이언트) ──
let items = [];
let itemIdCounter = 0;

// 제품명 매핑
const PRODUCT_NAMES = {
    'sticker':'스티커','inserted':'전단지','namecard':'명함','envelope':'봉투',
    'cadarok':'카다록','littleprint':'포스터','ncrflambeau':'NCR양식지',
    'merchandisebond':'상품권','msticker':'자석스티커'
};

document.addEventListener('DOMContentLoaded', function() {
    renderItems();
    // 배송방법 변경 시 주소 영역 토글
    document.getElementById('delivery_method').addEventListener('change', toggleAddressSection);
    // 결제방법 변경 시 입금자명 토글
    document.getElementById('payment_method').addEventListener('change', toggleBankNameSection);
    // 파일 선택 시 미리보기
    document.getElementById('proofFiles').addEventListener('change', handleFilePreview);
});

// ── 품목 렌더링 (quote/create.php 동일 패턴) ──
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

    const colStyle = '36px 1fr 2fr 70px 80px 90px 28px';

    items.forEach((item, i) => {
        const unitPrice = item.unit_price > 0 ? Math.round(item.unit_price) : (item.quantity > 0 ? Math.round(item.supply_price / item.quantity) : 0);
        const qtyDisplay = item.quantity_display || (fmtNum(item.quantity) + item.unit);

        const row = document.createElement('div');
        row.className = 'grid items-center px-1 border-b border-gray-100 transition-colors';
        row.style.gridTemplateColumns = colStyle;
        row.style.height = '33px';
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
        nameEl.textContent = item.product_name;
        row.appendChild(nameEl);

        // 규격/옵션
        const specEl = document.createElement('span');
        specEl.className = 'py-1.5 px-2 text-xs text-gray-500 leading-snug';
        if (item.specification) {
            const parts = item.specification.split('\n');
            parts.forEach((part, pi) => {
                if (pi > 0) specEl.appendChild(document.createElement('br'));
                specEl.appendChild(document.createTextNode(part));
            });
        }
        row.appendChild(specEl);

        // 수량
        const qtyEl = document.createElement('span');
        qtyEl.className = 'py-1.5 text-center text-sm text-gray-700';
        qtyEl.textContent = qtyDisplay;
        row.appendChild(qtyEl);

        // 단가
        const upEl = document.createElement('span');
        upEl.className = 'py-1.5 text-right pr-2 text-sm text-gray-600';
        upEl.textContent = fmtNum(unitPrice);
        row.appendChild(upEl);

        // 공급가액
        const priceEl = document.createElement('span');
        priceEl.className = 'py-1.5 text-right pr-2 text-sm font-semibold text-gray-900';
        priceEl.textContent = fmtNum(item.supply_price);
        row.appendChild(priceEl);

        // 삭제
        const delWrap = document.createElement('span');
        delWrap.className = 'py-1.5 text-center';
        const delBtn = document.createElement('button');
        delBtn.className = 'text-gray-300 hover:text-red-500 text-sm leading-none transition-colors';
        delBtn.textContent = '\u00d7';
        delBtn.addEventListener('click', function() { deleteItem(item.id); });
        delWrap.appendChild(delBtn);
        row.appendChild(delWrap);

        container.appendChild(row);
    });
    updateTotals();
}

function updateTotals() {
    let supply = 0;
    items.forEach(item => supply += parseInt(item.supply_price) || 0);
    const vat = Math.round(supply * 0.1);
    document.getElementById('supplyTotal').textContent = fmtNum(supply);
    document.getElementById('vatTotal').textContent = fmtNum(vat);
    document.getElementById('grandTotal').textContent = fmtNum(supply + vat);
}

function fmtNum(n) { const v = parseFloat(n); return isNaN(v) ? '0' : v.toLocaleString('ko-KR', {maximumFractionDigits:1}); }

// ── 수동 입력 모달 ──
function openManualModal() {
    document.getElementById('manualModal').classList.remove('hidden');
    document.getElementById('manualModal').classList.add('flex');
    document.getElementById('manual_product_name').focus();
}
function closeManualModal() {
    document.getElementById('manualModal').classList.add('hidden');
    document.getElementById('manualModal').classList.remove('flex');
    document.getElementById('manual_product_name').value = '';
    document.getElementById('manual_product_type').value = '';
    document.getElementById('manual_specification').value = '';
    document.getElementById('manual_quantity').value = '1';
    document.getElementById('manual_unit').value = '매';
    document.getElementById('manual_supply_price').value = '';
}

function addManualItem() {
    const name = document.getElementById('manual_product_name').value.trim();
    const productType = document.getElementById('manual_product_type').value;
    const spec = document.getElementById('manual_specification').value.trim();
    const qty = parseFloat(document.getElementById('manual_quantity').value) || 1;
    const unit = document.getElementById('manual_unit').value;
    const price = parseInt(document.getElementById('manual_supply_price').value) || 0;

    if (!name) { alert('품목명을 입력해주세요.'); return; }
    if (price <= 0) { alert('공급가액을 입력해주세요.'); return; }

    items.push({
        id: ++itemIdCounter,
        source: 'manual',
        product_type: productType,
        product_name: name,
        specification: spec,
        quantity: qty,
        unit: unit,
        quantity_display: fmtNum(qty) + unit,
        unit_price: Math.round(price / qty),
        supply_price: price,
        calculator_data: null
    });
    renderItems();
    closeManualModal();
}

function deleteItem(id) {
    if (!confirm('삭제하시겠습니까?')) return;
    items = items.filter(x => x.id !== id);
    renderItems();
}

// ── 계산기 연동 (quote/create.php 동일) ──
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

// 계산기 postMessage 수신 (quote/create.php 동일 패턴)
window.addEventListener('message', function(e) {
    if(e.origin!==window.location.origin||!e.data||!e.data.type) return;
    if(e.data.type==='ADMIN_QUOTE_ITEM_ADDED' || e.data.type==='CALCULATOR_PRICE_DATA') {
        const payload = e.data.payload || {};
        if (payload.product_code && !payload.product_type) payload.product_type = payload.product_code;
        if (payload.quantity_unit && !payload.unit) payload.unit = payload.quantity_unit;
        if (payload.options && typeof payload.options === 'object') {
            Object.keys(payload.options).forEach(k => { if (!(k in payload)) payload[k] = payload.options[k]; });
        }

        const productType = payload.product_type || '';
        const productName = PRODUCT_NAMES[productType] || payload.product_name || productType;
        const supplyPrice = parseInt(payload.supply_price || payload.st_price || 0);
        const quantity = parseFloat(payload.quantity || payload.mesu || payload.MY_amount || 1);
        const unit = payload.unit || payload.quantity_unit || '매';
        const qtyDisplay = payload.quantity_display || (fmtNum(quantity) + unit);
        const specification = payload.specification || payload.spec_text || '';
        const unitPrice = quantity > 0 ? Math.round(supplyPrice / quantity) : 0;

        items.push({
            id: ++itemIdCounter,
            source: 'calculator',
            product_type: productType,
            product_name: productName,
            specification: specification,
            quantity: quantity,
            unit: unit,
            quantity_display: qtyDisplay,
            unit_price: unitPrice,
            supply_price: supplyPrice,
            calculator_data: payload
        });
        renderItems();
        closeCalculatorIframe();
    }
    if(e.data.type==='ADMIN_QUOTE_CLOSE_MODAL') closeCalculatorIframe();
});

// ── 배송/결제 토글 ──
function toggleAddressSection() {
    const method = document.getElementById('delivery_method').value;
    document.getElementById('addressSection').style.display = (method === '방문') ? 'none' : '';
}
function toggleBankNameSection() {
    const method = document.getElementById('payment_method').value;
    document.getElementById('bankNameSection').style.display = (method === '계좌이체') ? '' : 'none';
}

// ── 사업자 정보 토글 ──
function toggleBusinessInfo() {
    const section = document.getElementById('businessInfoSection');
    const icon = document.getElementById('bizToggleIcon');
    section.classList.toggle('hidden');
    icon.style.transform = section.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// ── Daum 주소검색 ──
function openPostcode() {
    new daum.Postcode({
        oncomplete: function(data) {
            document.getElementById('postcode').value = data.zonecode;
            document.getElementById('address').value = data.roadAddress || data.jibunAddress;
            document.getElementById('detail_address').focus();
        }
    }).open();
}

// ── 파일 미리보기 ──
function handleFilePreview() {
    const files = document.getElementById('proofFiles').files;
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';
    Array.from(files).forEach(file => {
        const tag = document.createElement('div');
        tag.className = 'flex items-center gap-1 px-2 py-1 bg-gray-100 rounded text-xs';
        tag.textContent = file.name + ' (' + (file.size / 1024 / 1024).toFixed(1) + 'MB)';
        preview.appendChild(tag);
    });
}

// ── 주문 등록 ──
function submitOrder() {
    const name = document.getElementById('customer_name').value.trim();
    const phone = document.getElementById('customer_phone').value.trim();
    if (!name) { alert('주문자 성명을 입력해주세요.'); document.getElementById('customer_name').focus(); return; }
    if (!phone) { alert('전화번호를 입력해주세요.'); document.getElementById('customer_phone').focus(); return; }
    if (items.length === 0) { alert('품목을 추가해주세요.'); return; }

    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = '등록 중...';

    // 사업자 정보 수집
    const bizName = document.getElementById('biz_name').value.trim();
    const bizNumber = document.getElementById('biz_number').value.trim();
    const bizOwner = document.getElementById('biz_owner').value.trim();
    const bizType = document.getElementById('biz_type').value.trim();
    const bizItem = document.getElementById('biz_item').value.trim();
    const bizTaxEmail = document.getElementById('biz_tax_email').value.trim();

    // bizname 컬럼 포맷: "상호 (사업자번호)"
    let bizname = '';
    if (bizName) {
        bizname = bizName;
        if (bizNumber) bizname += ' (' + bizNumber + ')';
    } else if (bizNumber) {
        bizname = bizNumber;
    }

    // 사업자 정보 텍스트 (cont에 추가)
    let bizText = '';
    if (bizNumber) {
        bizText = '\n\n=== 사업자 정보 ===\n';
        if (bizName) bizText += '상호(회사명): ' + bizName + '\n';
        bizText += '사업자등록번호: ' + bizNumber + '\n';
        if (bizOwner) bizText += '대표자명: ' + bizOwner + '\n';
        if (bizType) bizText += '업태: ' + bizType + '\n';
        if (bizItem) bizText += '종목: ' + bizItem + '\n';
        if (bizTaxEmail) bizText += '세금계산서 발행 이메일: ' + bizTaxEmail + '\n';
        bizText += '세금계산서 발행 요청';
    }

    const data = {
        customer_name: name,
        customer_phone: phone,
        customer_mobile: document.getElementById('customer_mobile').value.trim(),
        customer_email: document.getElementById('customer_email').value.trim(),
        delivery_method: document.getElementById('delivery_method').value,
        payment_method: document.getElementById('payment_method').value,
        bankname: document.getElementById('bankname').value.trim(),
        postcode: document.getElementById('postcode').value.trim(),
        address: document.getElementById('address').value.trim(),
        detail_address: document.getElementById('detail_address').value.trim(),
        bizname: bizname,
        biz_text: bizText,
        customer_memo: document.getElementById('customer_memo').value.trim(),
        admin_memo: document.getElementById('admin_memo').value.trim(),
        order_status: document.getElementById('order_status').value,
        items: items.map(x => ({
            source: x.source,
            product_type: x.product_type || '',
            product_name: x.product_name,
            specification: x.specification,
            quantity: x.quantity,
            unit: x.unit,
            quantity_display: x.quantity_display,
            unit_price: x.unit_price,
            supply_price: x.supply_price,
            calculator_data: x.calculator_data
        }))
    };

    // FormData로 파일과 JSON 데이터 함께 전송
    const formData = new FormData();
    formData.append('order_data', JSON.stringify(data));

    const fileInput = document.getElementById('proofFiles');
    if (fileInput.files.length > 0) {
        Array.from(fileInput.files).forEach(file => {
            formData.append('proof_files[]', file);
        });
    }

    fetch('/dashboard/api/admin-order.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('주문이 등록되었습니다.\n주문번호: ' + d.order_numbers.join(', '));
            location.href = '/dashboard/orders/';
        } else {
            alert('주문 등록 실패: ' + d.message);
        }
    })
    .catch(e => {
        alert('서버 오류: ' + e.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = '주문 등록';
    });
}
</script>

<?php
if (isset($db) && $db) { mysqli_close($db); }
include __DIR__ . '/../includes/footer.php';
?>
