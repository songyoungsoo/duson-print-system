<footer class="bg-gray-900 text-gray-300 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <div class="col-span-1 md:col-span-2">
                <h3 class="text-xl font-bold text-white mb-4">두손기획인쇄</h3>
                <p class="text-sm leading-relaxed mb-4">
                    기획에서 인쇄까지 원스톱 서비스<br>
                    스티커, 전단지, 명함 등 모든 인쇄물 전문
                </p>
                <div class="space-y-2 text-sm">
                    <p>📍 주소: 서울시 영등포구 영등포로 36길 9 송호빌딩 1층</p>
                    <p>📞 전화: 02-2632-1830</p>
                    <p>📧 이메일: dsp1830@naver.com</p>
                    <p>🕐 영업시간: 평일 09:00 - 18:00</p>
                </div>
            </div>
            
            <div>
                <h4 class="text-white font-semibold mb-4">인쇄 품목</h4>
                <ul class="space-y-2 text-sm">
                    <?php
                    $products = require __DIR__ . '/../../config/products.php';
                    foreach ($products as $key => $product): ?>
                    <li>
                        <a href="<?= \App\Core\View::url("/product/{$key}") ?>" class="hover:text-white transition-colors">
                            <?= $product['name'] ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div>
                <h4 class="text-white font-semibold mb-4">고객 서비스</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= \App\Core\View::url('/faq') ?>" class="hover:text-white transition-colors">자주 묻는 질문</a></li>
                    <li><a href="<?= \App\Core\View::url('/guide') ?>" class="hover:text-white transition-colors">주문 가이드</a></li>
                    <li><a href="<?= \App\Core\View::url('/terms') ?>" class="hover:text-white transition-colors">이용약관</a></li>
                    <li><a href="<?= \App\Core\View::url('/privacy') ?>" class="hover:text-white transition-colors">개인정보처리방침</a></li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
            <p>&copy; <?= date('Y') ?> 두손기획인쇄. All rights reserved.</p>
        </div>
    </div>
</footer>
