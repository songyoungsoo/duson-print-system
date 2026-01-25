<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="cartPage()">
    <nav class="flex mb-6 text-sm">
        <a href="<?= \App\Core\View::url('/') ?>" class="text-gray-500 hover:text-gray-700">홈</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-900 font-medium">🛒 장바구니</span>
    </nav>

    <h1 class="text-3xl font-bold text-gray-900 mb-8">🛒 장바구니</h1>

    <?php if (empty($items)): ?>
    <div class="text-center py-16">
        <div class="text-6xl mb-4">🛒</div>
        <h2 class="text-xl font-semibold text-gray-700 mb-2">장바구니가 비어있습니다</h2>
        <p class="text-gray-500 mb-6">원하시는 상품을 담아보세요.</p>
        <a href="<?= \App\Core\View::url('/') ?>" 
           class="inline-flex items-center px-6 py-3 bg-brand-navy text-white font-medium rounded-lg hover:bg-blue-900 transition-colors">
            상품 둘러보기 →
        </a>
    </div>
    <?php else: ?>
    
    <div class="grid lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($items as $item): ?>
            <div class="cart-item bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                 data-item-id="<?= $item['no'] ?>">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-3">
                            <span class="text-2xl">
                                <?= $products[$item['product_type']]['icon'] ?? '📦' ?>
                            </span>
                            <h3 class="text-lg font-semibold text-gray-900">
                                <?= htmlspecialchars($products[$item['product_type']]['name'] ?? $item['product_type']) ?>
                            </h3>
                        </div>
                        
                        <div class="text-sm text-gray-600 space-y-1 mb-4">
                            <?php if (!empty($item['spec_type']) || !empty($item['MY_type_name'])): ?>
                            <p>
                                <span class="text-gray-400">종류:</span>
                                <?= htmlspecialchars($item['spec_type'] ?: $item['MY_type_name'] ?? '-') ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['spec_material']) || !empty($item['Section_name'])): ?>
                            <p>
                                <span class="text-gray-400">재질:</span>
                                <?= htmlspecialchars($item['spec_material'] ?: $item['Section_name'] ?? '-') ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['spec_size'])): ?>
                            <p>
                                <span class="text-gray-400">사이즈:</span>
                                <?= htmlspecialchars($item['spec_size']) ?>
                            </p>
                            <?php endif; ?>
                            
                            <?php if (!empty($item['spec_sides']) || !empty($item['POtype_name'])): ?>
                            <p>
                                <span class="text-gray-400">인쇄:</span>
                                <?= htmlspecialchars($item['spec_sides'] ?: $item['POtype_name'] ?? '-') ?>
                            </p>
                            <?php endif; ?>
                            
                            <p>
                                <span class="text-gray-400">수량:</span>
                                <span class="font-medium text-gray-900">
                                    <?= htmlspecialchars($item['quantity_display'] ?: ($item['MY_amount'] ?? '-')) ?>
                                </span>
                            </p>
                        </div>
                        
                        <?php if (!empty($item['work_memo'])): ?>
                        <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-3">
                            <span class="text-gray-400">메모:</span>
                            <?= nl2br(htmlspecialchars($item['work_memo'])) ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php 
                        $uploadedFiles = [];
                        if (!empty($item['uploaded_files'])) {
                            $uploadedFiles = json_decode($item['uploaded_files'], true) ?: [];
                            if (isset($uploadedFiles['files'])) {
                                $uploadedFiles = $uploadedFiles['files'];
                            }
                        }
                        ?>
                        <div class="cart-item-files mt-4" x-data="cartItemFiles(<?= $item['no'] ?>, <?= htmlspecialchars(json_encode($uploadedFiles)) ?>)">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-500">
                                    <span class="text-gray-400">디자인 파일:</span>
                                    <span x-text="files.length > 0 ? files.length + '개 업로드됨' : '미업로드'" 
                                          :class="files.length > 0 ? 'text-green-600 font-medium' : 'text-amber-600'"></span>
                                </span>
                                <button @click="showUploader = !showUploader"
                                        class="text-xs text-brand-navy hover:underline">
                                    <span x-text="showUploader ? '닫기' : (files.length > 0 ? '파일 관리' : '+ 파일 추가')"></span>
                                </button>
                            </div>
                            
                            <div x-show="files.length > 0 && !showUploader" class="flex flex-wrap gap-1.5">
                                <template x-for="(file, index) in files.slice(0, 3)" :key="index">
                                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 rounded text-xs text-gray-600">
                                        <span x-text="file.name || file.original_name" class="truncate max-w-[100px]"></span>
                                    </span>
                                </template>
                                <span x-show="files.length > 3" class="text-xs text-gray-400">+<span x-text="files.length - 3"></span>개</span>
                            </div>
                            
                            <div x-show="showUploader" x-transition class="mt-3 p-4 bg-gray-50 rounded-lg">
                                <template x-if="files.length > 0">
                                    <div class="mb-3 space-y-2">
                                        <template x-for="(file, index) in files" :key="index">
                                            <div class="flex items-center justify-between bg-white p-2 rounded border">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <span class="file-icon-mini" :class="getFileIconClass(file.name || file.original_name)" x-text="getFileExt(file.name || file.original_name)"></span>
                                                    <span class="text-sm text-gray-700 truncate" x-text="file.name || file.original_name"></span>
                                                </div>
                                                <button @click="removeFile(index)" class="text-gray-400 hover:text-red-500 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                
                                <div class="cart-file-dropzone border-2 border-dashed border-gray-300 rounded-lg p-4 text-center cursor-pointer hover:border-brand-navy transition-colors"
                                     @click="$refs.fileInput<?= $item['no'] ?>.click()"
                                     @dragover.prevent="dragover = true"
                                     @dragleave.prevent="dragover = false"
                                     @drop.prevent="handleDrop($event)"
                                     :class="{'border-brand-navy bg-blue-50': dragover}">
                                    <input type="file" x-ref="fileInput<?= $item['no'] ?>" multiple 
                                           accept=".ai,.psd,.pdf,.jpg,.jpeg,.png,.gif,.tif,.tiff,.eps,.cdr"
                                           @change="handleFiles($event)" class="hidden">
                                    <p class="text-sm text-gray-600">파일을 드래그하거나 클릭하여 추가</p>
                                    <p class="text-xs text-gray-400 mt-1">AI, PSD, PDF, JPG 등 (최대 50MB)</p>
                                </div>
                                
                                <div x-show="newFiles.length > 0" class="mt-3">
                                    <button @click="uploadFiles()" 
                                            :disabled="uploading"
                                            class="w-full py-2 bg-brand-navy text-white text-sm font-medium rounded-lg hover:bg-blue-900 disabled:opacity-50 transition-colors">
                                        <span x-show="!uploading">새 파일 <span x-text="newFiles.length"></span>개 업로드</span>
                                        <span x-show="uploading">업로드 중...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-right ml-6">
                        <p class="text-xl font-bold text-brand-navy">
                            <?php
                            $price = $item['price_vat'] ?: $item['st_price_vat'] ?: $item['st_price'] ?: 0;
                            echo number_format((int)$price) . '원';
                            ?>
                        </p>
                        <p class="text-xs text-gray-400">(VAT 포함)</p>
                        
                        <button @click="removeItem(<?= $item['no'] ?>)"
                                class="mt-4 text-sm text-red-500 hover:text-red-700 transition-colors">
                            삭제
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">주문 요약</h3>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-gray-600">
                        <span>상품 수</span>
                        <span x-text="itemCount + '건'"><?= $totals['item_count'] ?>건</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>공급가액</span>
                        <span x-text="formatPrice(supplyTotal)"><?= number_format($totals['supply_total']) ?>원</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>부가세</span>
                        <span x-text="formatPrice(vatTotal)"><?= number_format($totals['vat_total']) ?>원</span>
                    </div>
                    <hr class="border-gray-200">
                    <div class="flex justify-between text-lg font-bold">
                        <span>총 결제금액</span>
                        <span class="text-brand-navy" x-text="formatPrice(grandTotal)">
                            <?= number_format($totals['grand_total']) ?>원
                        </span>
                    </div>
                </div>
                
                <div class="space-y-3">
                    <button @click="downloadQuote()"
                            class="w-full py-3 border-2 border-brand-navy text-brand-navy font-semibold rounded-lg hover:bg-brand-navy hover:text-white transition-colors">
                        📄 견적서 다운로드
                    </button>
                    
                    <a href="<?= \App\Core\View::url('/order') ?>"
                       class="block w-full py-4 bg-brand-navy text-white text-center font-semibold rounded-lg hover:bg-blue-900 transition-colors">
                        📦 주문하기
                    </a>
                </div>
                
                <p class="mt-4 text-xs text-gray-400 text-center">
                    주문 전 파일 업로드 및 배송 정보를 입력해 주세요.
                </p>
            </div>
        </div>
    </div>
    
    <?php endif; ?>
</div>

<script>
function cartItemFiles(itemId, initialFiles) {
    return {
        itemId: itemId,
        files: initialFiles || [],
        newFiles: [],
        showUploader: false,
        uploading: false,
        dragover: false,
        
        handleFiles(event) {
            const files = Array.from(event.target.files);
            this.addNewFiles(files);
            event.target.value = '';
        },
        
        handleDrop(event) {
            this.dragover = false;
            const files = Array.from(event.dataTransfer.files);
            this.addNewFiles(files);
        },
        
        addNewFiles(files) {
            const validExtensions = ['ai', 'psd', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'eps', 'cdr'];
            const maxSize = 50 * 1024 * 1024;
            
            for (const file of files) {
                const ext = file.name.split('.').pop().toLowerCase();
                if (!validExtensions.includes(ext)) {
                    DusonApp.showToast(`지원하지 않는 파일 형식: ${file.name}`, 'error');
                    continue;
                }
                if (file.size > maxSize) {
                    DusonApp.showToast(`파일 크기 초과 (50MB): ${file.name}`, 'error');
                    continue;
                }
                if (!this.newFiles.some(f => f.name === file.name && f.size === file.size)) {
                    this.newFiles.push(file);
                }
            }
        },
        
        async removeFile(index) {
            if (index < this.files.length) {
                this.files.splice(index, 1);
                await this.saveFilesToServer();
            } else {
                this.newFiles.splice(index - this.files.length, 1);
            }
        },
        
        async uploadFiles() {
            if (this.newFiles.length === 0 || this.uploading) return;
            
            this.uploading = true;
            
            try {
                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '<?= \App\Core\CSRF::token() ?>');
                formData.append('item_id', this.itemId);
                
                for (const file of this.newFiles) {
                    formData.append('file[]', file);
                }
                
                const response = await fetch('/v2/public/cart/upload-files', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.files = data.files || [];
                    this.newFiles = [];
                    DusonApp.showToast('파일이 업로드되었습니다.', 'success');
                } else {
                    throw new Error(data.message || '업로드 실패');
                }
            } catch (error) {
                console.error('Upload error:', error);
                DusonApp.showToast(error.message || '파일 업로드 중 오류가 발생했습니다.', 'error');
            } finally {
                this.uploading = false;
            }
        },
        
        async saveFilesToServer() {
            try {
                const response = await DusonApp.fetchAPI('/v2/public/cart/update-files', {
                    method: 'POST',
                    body: JSON.stringify({
                        _token: '<?= \App\Core\CSRF::token() ?>',
                        item_id: this.itemId,
                        files: this.files,
                    }),
                });
                
                if (!response.success) {
                    throw new Error(response.message);
                }
            } catch (error) {
                console.error('Save files error:', error);
            }
        },
        
        getFileExt(filename) {
            return (filename || '').split('.').pop().toUpperCase().substring(0, 4);
        },
        
        getFileIconClass(filename) {
            const ext = (filename || '').split('.').pop().toLowerCase();
            const classes = {
                'ai': 'file-icon-ai',
                'psd': 'file-icon-psd',
                'pdf': 'file-icon-pdf',
                'jpg': 'file-icon-image', 'jpeg': 'file-icon-image',
                'png': 'file-icon-image', 'gif': 'file-icon-image',
                'tif': 'file-icon-image', 'tiff': 'file-icon-image',
                'eps': 'file-icon-eps',
                'cdr': 'file-icon-cdr',
            };
            return classes[ext] || 'file-icon-default';
        }
    };
}

function cartPage() {
    return {
        itemCount: <?= $totals['item_count'] ?? 0 ?>,
        supplyTotal: <?= $totals['supply_total'] ?? 0 ?>,
        vatTotal: <?= $totals['vat_total'] ?? 0 ?>,
        grandTotal: <?= $totals['grand_total'] ?? 0 ?>,
        
        formatPrice(value) {
            return new Intl.NumberFormat('ko-KR').format(value) + '원';
        },
        
        async removeItem(itemId) {
            if (!confirm('이 상품을 장바구니에서 삭제하시겠습니까?')) {
                return;
            }
            
            try {
                const response = await DusonApp.fetchAPI('/v2/public/cart/remove', {
                    method: 'POST',
                    body: JSON.stringify({
                        item_id: itemId,
                        _token: document.querySelector('meta[name="csrf-token"]')?.content || '',
                    }),
                });
                
                if (response.success) {
                    const itemEl = document.querySelector(`[data-item-id="${itemId}"]`);
                    if (itemEl) {
                        itemEl.style.opacity = '0';
                        itemEl.style.transform = 'translateX(-20px)';
                        setTimeout(() => itemEl.remove(), 300);
                    }
                    
                    this.itemCount = response.cart_count;
                    this.supplyTotal = response.totals.supply_total;
                    this.vatTotal = response.totals.vat_total;
                    this.grandTotal = response.totals.grand_total;
                    
                    DusonApp.updateCartBadge(response.cart_count);
                    DusonApp.showToast('삭제되었습니다.', 'success');
                    
                    if (response.cart_count === 0) {
                        location.reload();
                    }
                } else {
                    DusonApp.showToast(response.message || '삭제에 실패했습니다.', 'error');
                }
            } catch (error) {
                console.error('삭제 오류:', error);
                DusonApp.showToast('삭제 중 오류가 발생했습니다.', 'error');
            }
        },
        
        async downloadQuote() {
            try {
                const response = await DusonApp.fetchAPI('/v2/public/cart/quote', {
                    method: 'GET',
                });
                
                if (response.success) {
                    this.generateQuotePDF(response.quote);
                } else {
                    DusonApp.showToast(response.message || '견적서 생성에 실패했습니다.', 'error');
                }
            } catch (error) {
                console.error('견적서 오류:', error);
                DusonApp.showToast('견적서 생성 중 오류가 발생했습니다.', 'error');
            }
        },
        
        generateQuotePDF(quote) {
            let html = `
                <html>
                <head>
                    <title>견적서 - ${quote.quote_no}</title>
                    <style>
                        body { font-family: 'Malgun Gothic', sans-serif; padding: 40px; }
                        h1 { text-align: center; margin-bottom: 30px; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                        th { background: #f5f5f5; }
                        .total { font-size: 18px; font-weight: bold; text-align: right; margin-top: 20px; }
                        .company { text-align: center; margin-bottom: 40px; }
                    </style>
                </head>
                <body>
                    <div class="company">
                        <h1>견 적 서</h1>
                        <p>두손기획인쇄</p>
                    </div>
                    <p>견적번호: ${quote.quote_no}</p>
                    <p>발행일: ${quote.date}</p>
                    <table>
                        <thead>
                            <tr>
                                <th>상품명</th>
                                <th>사양</th>
                                <th>수량</th>
                                <th>금액</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${quote.items.map(item => `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td>${item.spec}</td>
                                    <td>${item.quantity}</td>
                                    <td>${new Intl.NumberFormat('ko-KR').format(item.price)}원</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    <div class="total">
                        <p>공급가액: ${new Intl.NumberFormat('ko-KR').format(quote.totals.supply_total)}원</p>
                        <p>부가세: ${new Intl.NumberFormat('ko-KR').format(quote.totals.vat_total)}원</p>
                        <p style="font-size: 20px;">총액: ${new Intl.NumberFormat('ko-KR').format(quote.totals.grand_total)}원</p>
                    </div>
                </body>
                </html>
            `;
            
            const printWindow = window.open('', '_blank');
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.print();
        }
    };
}
</script>

<style>
.cart-item {
    transition: opacity 0.3s ease, transform 0.3s ease;
}
</style>
