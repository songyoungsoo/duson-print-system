<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="text-center mb-8">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">주문이 완료되었습니다!</h1>
        <p class="text-gray-600">감사합니다. 주문 내역을 확인해주세요.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="border-b border-gray-200 pb-4 mb-4">
            <p class="text-sm text-gray-500 mb-1">주문번호</p>
            <p class="text-2xl font-bold text-brand-navy">#<?= htmlspecialchars($order['no']) ?></p>
        </div>
        
        <div class="space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-500">주문일시</span>
                <span class="font-medium"><?= date('Y년 m월 d일 H:i', strtotime($order['date'])) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">주문자</span>
                <span class="font-medium"><?= htmlspecialchars($order['name']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">연락처</span>
                <span class="font-medium"><?= htmlspecialchars($order['phone']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">배송지</span>
                <span class="font-medium text-right">
                    <?= htmlspecialchars($order['zip1'] . ' ' . $order['zip2']) ?>
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 mb-4">주문 상품</h3>
        
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-gray-700 whitespace-pre-line"><?= nl2br(htmlspecialchars($order['Type_1'])) ?></p>
        </div>
        
        <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-500">공급가액</span>
                <span class="font-medium"><?= number_format($order['money_1']) ?>원</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">부가세</span>
                <span class="font-medium"><?= number_format($order['money_4']) ?>원</span>
            </div>
            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                <span>총 결제금액</span>
                <span class="text-brand-navy"><?= number_format($order['money_2']) ?>원</span>
            </div>
        </div>
    </div>

    <?php if ($order['bank'] === 'bank'): ?>
    <div class="bg-yellow-50 rounded-xl p-6 mb-6">
        <h3 class="font-semibold text-yellow-800 mb-2">입금 안내</h3>
        <p class="text-yellow-700 text-sm mb-3">아래 계좌로 입금해 주시면 확인 후 제작이 시작됩니다.</p>
        <div class="bg-white rounded-lg p-4">
            <p class="font-medium text-gray-900">국민은행 123-456-789012</p>
            <p class="text-gray-600">예금주: 두손기획인쇄</p>
            <p class="text-lg font-bold text-brand-navy mt-2"><?= number_format($order['money_2']) ?>원</p>
        </div>
        <p class="text-xs text-yellow-600 mt-3">
            * 주문자명과 입금자명이 다를 경우 고객센터로 연락해 주세요.<br>
            * 3일 이내 미입금시 주문이 자동 취소됩니다.
        </p>
    </div>
    <?php endif; ?>

    <div class="bg-blue-50 rounded-xl p-6 mb-6">
        <h3 class="font-semibold text-blue-800 mb-2">주문 진행 안내</h3>
        <div class="flex items-center justify-between text-sm">
            <div class="text-center">
                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center mx-auto mb-1">1</div>
                <p class="text-blue-700">주문완료</p>
            </div>
            <div class="flex-1 h-0.5 bg-blue-200 mx-2"></div>
            <div class="text-center">
                <div class="w-10 h-10 bg-blue-200 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-1">2</div>
                <p class="text-blue-400">입금확인</p>
            </div>
            <div class="flex-1 h-0.5 bg-blue-200 mx-2"></div>
            <div class="text-center">
                <div class="w-10 h-10 bg-blue-200 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-1">3</div>
                <p class="text-blue-400">제작중</p>
            </div>
            <div class="flex-1 h-0.5 bg-blue-200 mx-2"></div>
            <div class="text-center">
                <div class="w-10 h-10 bg-blue-200 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-1">4</div>
                <p class="text-blue-400">발송완료</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-4">
        <a href="<?= \App\Core\View::url('/') ?>"
           class="flex-1 py-4 border-2 border-gray-300 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-100 transition-colors">
            홈으로 돌아가기
        </a>
        <a href="/mlangorder_printauto/session/orderhistory.php"
           class="flex-1 py-4 bg-brand-navy text-white text-center font-semibold rounded-lg hover:bg-blue-900 transition-colors">
            주문내역 확인하기
        </a>
    </div>

    <div class="mt-8 text-center text-sm text-gray-500">
        <p>문의사항은 고객센터 <strong class="text-gray-700">031-123-4567</strong>로 연락해 주세요.</p>
        <p>운영시간: 평일 09:00 ~ 18:00 (주말/공휴일 휴무)</p>
    </div>
</div>
