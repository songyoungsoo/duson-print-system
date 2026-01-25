<header class="bg-white shadow-sm sticky top-0 z-50" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            
            <a href="<?= \App\Core\View::url('/') ?>" class="flex items-center space-x-2">
                <span class="text-2xl font-bold text-brand-navy">두손기획인쇄</span>
            </a>
            
            <nav class="hidden md:flex items-center space-x-8">
                <?php
                $products = require __DIR__ . '/../../config/products.php';
                $mainProducts = array_slice($products, 0, 6, true);
                foreach ($mainProducts as $key => $product): ?>
                <a href="<?= \App\Core\View::url("/product/{$key}") ?>" 
                   class="text-gray-600 hover:text-brand-navy transition-colors text-sm font-medium">
                    <?= $product['icon'] ?> <?= $product['name'] ?>
                </a>
                <?php endforeach; ?>
                
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-600 hover:text-brand-navy transition-colors text-sm font-medium flex items-center">
                        더보기
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50">
                        <?php
                        $moreProducts = array_slice($products, 6, null, true);
                        foreach ($moreProducts as $key => $product): ?>
                        <a href="<?= \App\Core\View::url("/product/{$key}") ?>" 
                           class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                            <?= $product['icon'] ?> <?= $product['name'] ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </nav>
            
            <div class="flex items-center space-x-4">
                <a href="<?= \App\Core\View::url('/cart') ?>" class="relative text-gray-600 hover:text-brand-navy">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span id="cart-count" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">0</span>
                </a>
                
                <?php if (\App\Core\Session::has('user_id')): ?>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-1 text-gray-600 hover:text-brand-navy">
                        <span class="text-sm"><?= \App\Core\View::escape(\App\Core\Session::get('user_name', '회원')) ?></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2">
                        <a href="<?= \App\Core\View::url('/mypage') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">마이페이지</a>
                        <a href="<?= \App\Core\View::url('/mypage/orders') ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">주문내역</a>
                        <hr class="my-2">
                        <a href="<?= \App\Core\View::url('/logout') ?>" class="block px-4 py-2 text-red-600 hover:bg-gray-100">로그아웃</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= \App\Core\View::url('/login') ?>" class="text-sm text-gray-600 hover:text-brand-navy">로그인</a>
                <?php endif; ?>
                
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden py-4 border-t">
            <?php foreach ($products as $key => $product): ?>
            <a href="<?= \App\Core\View::url("/product/{$key}") ?>" 
               class="block py-2 text-gray-600 hover:text-brand-navy">
                <?= $product['icon'] ?> <?= $product['name'] ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</header>
