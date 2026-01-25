<div id="chat-widget" x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="mb-4 w-80 bg-white rounded-2xl shadow-2xl overflow-hidden">
        
        <div class="bg-brand-navy text-white p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        💬
                    </div>
                    <div>
                        <h4 class="font-semibold">상담 문의</h4>
                        <p class="text-xs text-white/70">평일 09:00 - 18:00</p>
                    </div>
                </div>
                <button @click="open = false" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        
        <div class="p-4 space-y-4">
            <div class="text-center text-gray-500 text-sm">
                빠른 상담을 원하시면 아래 채널을 이용해주세요.
            </div>
            
            <div class="space-y-2">
                <a href="tel:02-1234-5678" 
                   class="flex items-center justify-center space-x-2 w-full py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                    <span>📞</span>
                    <span>전화 상담</span>
                </a>
                
                <a href="https://pf.kakao.com/_xxxxxC" target="_blank"
                   class="flex items-center justify-center space-x-2 w-full py-3 bg-yellow-400 text-gray-900 rounded-lg hover:bg-yellow-500 transition-colors">
                    <span>💬</span>
                    <span>카카오톡 상담</span>
                </a>
                
                <a href="mailto:info@dsp1830.shop"
                   class="flex items-center justify-center space-x-2 w-full py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                    <span>✉️</span>
                    <span>이메일 문의</span>
                </a>
            </div>
        </div>
    </div>
    
    <button @click="open = !open"
            class="w-14 h-14 bg-brand-navy text-white rounded-full shadow-lg hover:shadow-xl transition-shadow flex items-center justify-center">
        <span x-show="!open" class="text-2xl">💬</span>
        <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
