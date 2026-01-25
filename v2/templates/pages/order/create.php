<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="orderForm()">
    <nav class="flex mb-6 text-sm">
        <a href="<?= \App\Core\View::url('/') ?>" class="text-gray-500 hover:text-gray-700">홈</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="<?= \App\Core\View::url('/cart') ?>" class="text-gray-500 hover:text-gray-700">장바구니</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-900 font-medium">📦 주문하기</span>
    </nav>

    <h1 class="text-3xl font-bold text-gray-900 mb-8">📦 주문하기</h1>

    <form @submit.prevent="submitOrder()" class="space-y-8">
        <input type="hidden" name="_token" value="<?= \App\Core\CSRF::token() ?>">
        
        <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-brand-navy text-white rounded-full flex items-center justify-center text-sm mr-3">1</span>
                주문 상품 확인
            </h2>
            
            <div class="space-y-4">
                <?php foreach ($items as $index => $item): ?>
                <?php 
                $uploadedFiles = [];
                if (!empty($item['uploaded_files'])) {
                    $decoded = json_decode($item['uploaded_files'], true) ?: [];
                    $uploadedFiles = $decoded['files'] ?? (is_array($decoded) ? $decoded : []);
                }
                $fileCount = count($uploadedFiles);
                ?>
                <div class="p-4 bg-gray-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <span class="text-2xl"><?= $products[$item['product_type']]['icon'] ?? '📦' ?></span>
                            <div>
                                <p class="font-medium text-gray-900">
                                    <?= htmlspecialchars($products[$item['product_type']]['name'] ?? $item['product_type']) ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php
                                    $specs = array_filter([
                                        $item['spec_type'] ?: ($item['MY_type_name'] ?? ''),
                                        $item['spec_material'] ?: ($item['Section_name'] ?? ''),
                                        $item['quantity_display'] ?: ($item['MY_amount'] ?? ''),
                                    ]);
                                    echo htmlspecialchars(implode(' / ', $specs));
                                    ?>
                                </p>
                            </div>
                        </div>
                        <p class="font-bold text-brand-navy">
                            <?php
                            $price = $item['price_vat'] ?: $item['st_price_vat'] ?: $item['st_price'] ?: 0;
                            echo number_format((int)$price) . '원';
                            ?>
                        </p>
                    </div>
                    
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">디자인 파일:</span>
                            <?php if ($fileCount > 0): ?>
                            <div class="flex items-center gap-2">
                                <span class="text-green-600 font-medium">
                                    <?= $fileCount ?>개 업로드됨
                                </span>
                                <button type="button" 
                                        onclick="toggleFileList(<?= $item['no'] ?>)"
                                        class="text-brand-navy hover:underline text-xs">보기</button>
                            </div>
                            <?php else: ?>
                            <span class="text-amber-600">
                                미업로드 
                                <a href="<?= \App\Core\View::url('/cart') ?>" class="text-brand-navy hover:underline">(장바구니에서 추가)</a>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($fileCount > 0): ?>
                        <div id="file-list-<?= $item['no'] ?>" class="hidden mt-2 space-y-1">
                            <?php foreach ($uploadedFiles as $file): ?>
                            <div class="flex items-center gap-2 text-xs text-gray-600 bg-white px-2 py-1.5 rounded">
                                <span class="file-icon-mini <?= getFileIconClass($file['name'] ?? $file['original_name'] ?? '') ?>">
                                    <?= strtoupper(substr(pathinfo($file['name'] ?? $file['original_name'] ?? '', PATHINFO_EXTENSION), 0, 3)) ?>
                                </span>
                                <span class="truncate"><?= htmlspecialchars($file['name'] ?? $file['original_name'] ?? '') ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php
                function getFileIconClass($filename) {
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $classes = [
                        'ai' => 'file-icon-ai',
                        'psd' => 'file-icon-psd',
                        'pdf' => 'file-icon-pdf',
                        'jpg' => 'file-icon-image', 'jpeg' => 'file-icon-image',
                        'png' => 'file-icon-image', 'gif' => 'file-icon-image',
                        'tif' => 'file-icon-image', 'tiff' => 'file-icon-image',
                        'eps' => 'file-icon-eps',
                        'cdr' => 'file-icon-cdr',
                    ];
                    return $classes[$ext] ?? 'file-icon-default';
                }
                ?>
            </div>
            
            <div class="mt-4 pt-4 border-t border-gray-200 flex justify-between items-center">
                <span class="font-medium text-gray-700">총 결제금액</span>
                <span class="text-xl font-bold text-brand-navy"><?= number_format($totals['grand_total']) ?>원</span>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-brand-navy text-white rounded-full flex items-center justify-center text-sm mr-3">2</span>
                주문자 정보
            </h2>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        이름 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" x-model="form.name" required
                           value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="홍길동">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        연락처 <span class="text-red-500">*</span>
                    </label>
                    <input type="tel" name="phone" x-model="form.phone" required
                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="010-1234-5678">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">휴대폰</label>
                    <input type="tel" name="mobile" x-model="form.mobile"
                           value="<?= htmlspecialchars($user['mobile'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="010-1234-5678">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">이메일</label>
                    <input type="email" name="email" x-model="form.email"
                           value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="example@email.com">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">회사명</label>
                    <input type="text" name="company" x-model="form.company"
                           value="<?= htmlspecialchars($user['company'] ?? '') ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="(주)회사명">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">주문 비밀번호</label>
                    <input type="password" name="password" x-model="form.password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="비회원 주문조회용">
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-brand-navy text-white rounded-full flex items-center justify-center text-sm mr-3">3</span>
                배송 정보
            </h2>
            
            <div class="space-y-4">
                <div class="grid md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            우편번호 <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <input type="text" name="zip" x-model="form.zip" required readonly
                                   class="flex-1 px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
                                   placeholder="우편번호">
                            <button type="button" @click="searchAddress()"
                                    class="px-4 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors whitespace-nowrap">
                                주소검색
                            </button>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        주소 <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="address1" x-model="form.address1" required readonly
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50"
                           placeholder="기본 주소">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">상세주소</label>
                    <input type="text" name="address2" x-model="form.address2"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="상세 주소를 입력하세요">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">배송 요청사항</label>
                    <select name="delivery_memo" x-model="form.delivery_memo"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent">
                        <option value="">선택하세요</option>
                        <option value="문 앞에 놓아주세요">문 앞에 놓아주세요</option>
                        <option value="경비실에 맡겨주세요">경비실에 맡겨주세요</option>
                        <option value="배송 전 연락주세요">배송 전 연락주세요</option>
                        <option value="부재시 문 앞에 놓아주세요">부재시 문 앞에 놓아주세요</option>
                        <option value="직접입력">직접입력</option>
                    </select>
                </div>
                
                <div x-show="form.delivery_memo === '직접입력'" x-cloak>
                    <input type="text" name="delivery_memo_custom" x-model="form.delivery_memo_custom"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                           placeholder="배송 요청사항을 입력하세요">
                </div>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-brand-navy text-white rounded-full flex items-center justify-center text-sm mr-3">4</span>
                결제 방법
            </h2>
            
            <div class="grid md:grid-cols-2 gap-4">
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                       :class="form.payment_method === 'bank' ? 'border-brand-navy bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="payment_method" value="bank" x-model="form.payment_method"
                           class="w-5 h-5 text-brand-navy">
                    <span class="ml-3">
                        <span class="font-medium text-gray-900">무통장 입금</span>
                        <span class="block text-sm text-gray-500">주문 후 계좌 안내</span>
                    </span>
                </label>
                
                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                       :class="form.payment_method === 'card' ? 'border-brand-navy bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                    <input type="radio" name="payment_method" value="card" x-model="form.payment_method"
                           class="w-5 h-5 text-brand-navy">
                    <span class="ml-3">
                        <span class="font-medium text-gray-900">카드 결제</span>
                        <span class="block text-sm text-gray-500">신용/체크카드</span>
                    </span>
                </label>
            </div>
            
            <div x-show="form.payment_method === 'bank'" x-cloak class="mt-4 p-4 bg-yellow-50 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <strong>입금 계좌:</strong> 국민은행 123-456-789012 (예금주: 두손기획인쇄)<br>
                    주문 완료 후 3일 이내에 입금해 주세요. 미입금시 주문이 자동 취소됩니다.
                </p>
            </div>
        </section>

        <section class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <span class="w-8 h-8 bg-brand-navy text-white rounded-full flex items-center justify-center text-sm mr-3">5</span>
                추가 요청사항
            </h2>
            
            <textarea name="memo" x-model="form.memo" rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-navy focus:border-transparent"
                      placeholder="작업 관련 요청사항이 있으시면 입력해주세요."></textarea>
        </section>

        <div class="bg-gray-50 rounded-xl p-6">
            <div class="flex items-start mb-4">
                <input type="checkbox" id="agree_terms" x-model="form.agree_terms" required
                       class="w-5 h-5 text-brand-navy rounded mt-0.5">
                <label for="agree_terms" class="ml-3 text-sm text-gray-700">
                    <span class="font-medium">[필수]</span> 주문 내용을 확인하였으며, 
                    <a href="#" class="text-brand-navy underline">이용약관</a> 및 
                    <a href="#" class="text-brand-navy underline">개인정보 처리방침</a>에 동의합니다.
                </label>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="<?= \App\Core\View::url('/cart') ?>"
                   class="flex-1 py-4 border-2 border-gray-300 text-gray-700 text-center font-semibold rounded-lg hover:bg-gray-100 transition-colors">
                    ← 장바구니로 돌아가기
                </a>
                <button type="submit" :disabled="!form.agree_terms || loading"
                        :class="form.agree_terms && !loading ? 'bg-brand-navy hover:bg-blue-900' : 'bg-gray-300 cursor-not-allowed'"
                        class="flex-1 py-4 text-white font-semibold rounded-lg transition-colors flex items-center justify-center">
                    <span x-show="!loading">결제하기 (<?= number_format($totals['grand_total']) ?>원)</span>
                    <span x-show="loading" class="flex items-center">
                        <svg class="animate-spin h-5 w-5 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        처리중...
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

<script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script>
function toggleFileList(itemId) {
    const el = document.getElementById('file-list-' + itemId);
    if (el) {
        el.classList.toggle('hidden');
    }
}

function orderForm() {
    return {
        form: {
            name: '<?= addslashes($user['name'] ?? '') ?>',
            phone: '<?= addslashes($user['phone'] ?? '') ?>',
            mobile: '',
            email: '<?= addslashes($user['email'] ?? '') ?>',
            company: '',
            password: '',
            zip: '',
            address1: '',
            address2: '',
            delivery_memo: '',
            delivery_memo_custom: '',
            payment_method: 'bank',
            memo: '',
            agree_terms: false,
        },
        loading: false,
        
        searchAddress() {
            new daum.Postcode({
                oncomplete: (data) => {
                    this.form.zip = data.zonecode;
                    this.form.address1 = data.roadAddress || data.jibunAddress;
                }
            }).open();
        },
        
        async submitOrder() {
            if (!this.form.agree_terms) {
                DusonApp.showToast('이용약관에 동의해주세요.', 'error');
                return;
            }
            
            if (!this.form.name || !this.form.phone || !this.form.zip || !this.form.address1) {
                DusonApp.showToast('필수 정보를 입력해주세요.', 'error');
                return;
            }
            
            this.loading = true;
            
            try {
                const deliveryMemo = this.form.delivery_memo === '직접입력' 
                    ? this.form.delivery_memo_custom 
                    : this.form.delivery_memo;
                
                const response = await DusonApp.fetchAPI('/v2/public/order', {
                    method: 'POST',
                    body: JSON.stringify({
                        ...this.form,
                        delivery_memo: deliveryMemo,
                        _token: document.querySelector('input[name="_token"]').value,
                    }),
                });
                
                if (response.success) {
                    DusonApp.showToast('주문이 완료되었습니다!', 'success');
                    DusonApp.updateCartBadge(0);
                    
                    setTimeout(() => {
                        window.location.href = response.redirect;
                    }, 1000);
                } else {
                    DusonApp.showToast(response.message || '주문 처리에 실패했습니다.', 'error');
                }
            } catch (error) {
                console.error('주문 오류:', error);
                DusonApp.showToast('주문 처리 중 오류가 발생했습니다.', 'error');
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
