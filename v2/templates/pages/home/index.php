<section class="bg-gradient-to-br from-brand-navy to-blue-900 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                기획에서 인쇄까지<br>
                <span class="text-brand-gold">원스톱 서비스</span>
            </h1>
            <p class="text-xl text-gray-300 mb-8 max-w-2xl mx-auto">
                스티커, 전단지, 명함 등 모든 인쇄물을<br>
                합리적인 가격으로 빠르게 제작해 드립니다.
            </p>
            <a href="#products" class="inline-flex items-center px-8 py-4 bg-brand-gold text-brand-navy font-semibold rounded-lg hover:bg-yellow-400 transition-colors">
                견적 확인하기
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>
</section>

<section id="products" class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">인쇄 품목</h2>
            <p class="text-gray-600">원하시는 품목을 선택하여 견적을 확인하세요</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
            <?php foreach ($products as $key => $product): ?>
            <a href="<?= \App\Core\View::url("/product/{$key}") ?>" class="product-card group p-6 text-center">
                <div class="product-card-icon mx-auto">
                    <?= $product['icon'] ?>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-brand-navy transition-colors mb-2">
                    <?= $product['name'] ?>
                </h3>
                <p class="text-sm text-gray-400 group-hover:text-brand-navy transition-colors">
                    견적 확인 <span class="inline-block transform group-hover:translate-x-1 transition-transform">→</span>
                </p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">왜 두손기획인쇄인가요?</h2>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl">⚡</span>
                </div>
                <h3 class="text-xl font-semibold mb-3">빠른 제작</h3>
                <p class="text-gray-600">주문 확인 후 신속하게 제작하여 빠른 배송을 약속드립니다.</p>
            </div>
            
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl">💰</span>
                </div>
                <h3 class="text-xl font-semibold mb-3">합리적 가격</h3>
                <p class="text-gray-600">공장 직영으로 중간 마진 없이 최저가로 제공합니다.</p>
            </div>
            
            <div class="bg-white rounded-2xl p-8 text-center shadow-sm">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-3xl">✨</span>
                </div>
                <h3 class="text-xl font-semibold mb-3">최상의 품질</h3>
                <p class="text-gray-600">최신 인쇄 장비와 숙련된 기술로 완벽한 품질을 보장합니다.</p>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-brand-navy text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-4">지금 바로 견적을 받아보세요</h2>
        <p class="text-gray-300 mb-8">간단한 정보 입력만으로 실시간 견적 확인이 가능합니다.</p>
        <a href="#products" class="inline-flex items-center px-8 py-4 bg-white text-brand-navy font-semibold rounded-lg hover:bg-gray-100 transition-colors">
            견적 시작하기
        </a>
    </div>
</section>
