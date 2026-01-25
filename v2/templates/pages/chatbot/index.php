<div class="max-w-2xl mx-auto px-4 py-8" x-data="chatbotApp()">
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">가격상담 챗봇</h1>
        <p class="text-gray-600">인쇄물 가격이 궁금하시면 물어보세요!</p>
    </div>

    <?php if (!$isConfigured): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <p class="text-yellow-800 text-sm">API 키가 설정되지 않았습니다.</p>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden flex flex-col" style="height: 650px;">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3 flex items-center gap-3">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-white font-semibold">두손 상담봇</h2>
                <p class="text-blue-100 text-xs">인쇄 가격 문의 전문</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
            <div class="flex gap-3">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                    </svg>
                </div>
                <div class="bg-gray-100 rounded-2xl rounded-tl-none px-4 py-3 max-w-[80%]">
                    <p class="text-gray-800 text-sm">안녕하세요! 두손기획인쇄 상담봇입니다. 😊<br><br>명함, 스티커, 전단지 등 인쇄물 가격이 궁금하시면 편하게 물어봐주세요!</p>
                </div>
            </div>

            <template x-for="(msg, index) in messages" :key="index">
                <div>
                    <div :class="msg.role === 'user' ? 'flex gap-3 justify-end' : 'flex gap-3'">
                        <template x-if="msg.role === 'assistant'">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                </svg>
                            </div>
                        </template>
                        <div :class="msg.role === 'user' 
                            ? 'bg-blue-600 text-white rounded-2xl rounded-tr-none px-4 py-3 max-w-[80%]' 
                            : 'bg-gray-100 rounded-2xl rounded-tl-none px-4 py-3 max-w-[80%]'">
                            <div class="text-sm whitespace-pre-wrap" x-html="formatMessageWithChoices(msg.content, msg.role)"></div>
                        </div>
                    </div>
                    
                    <template x-if="msg.paper_images && msg.paper_images.length > 0">
                        <div class="mt-3 ml-11">
                            <p class="text-xs text-gray-500 mb-2">📷 명함 용지 샘플 (클릭하면 질감을 크게 볼 수 있어요)</p>
                            <div class="flex gap-3 overflow-x-auto pb-2 pt-1">
                                <template x-for="(img, imgIdx) in msg.paper_images" :key="imgIdx">
                                    <div class="flex-shrink-0 cursor-pointer relative" 
                                        @click="showImageModal(img)"
                                        @mouseenter="$el.querySelector('img').style.transform = 'scale(2)'; $el.querySelector('img').style.zIndex = '50'"
                                        @mouseleave="$el.querySelector('img').style.transform = 'scale(1)'; $el.querySelector('img').style.zIndex = '1'">
                                        <img :src="img.url" :alt="img.name" 
                                            class="w-16 h-16 object-cover rounded-lg border-2 border-gray-200 hover:border-blue-500 shadow-sm hover:shadow-xl transition-all duration-200 origin-center"
                                            style="z-index: 1;">
                                        <p class="text-xs text-center text-gray-600 mt-1 w-16 truncate" x-text="img.name"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <template x-if="loading">
                <div class="flex gap-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                        </svg>
                    </div>
                    <div class="bg-gray-100 rounded-2xl rounded-tl-none px-4 py-3">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="error">
                <div class="flex gap-3">
                    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-red-700 text-sm w-full">
                        <p x-text="error"></p>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-4 border-t bg-gray-50">
            <div class="flex flex-wrap gap-2 mb-3">
                <button @click="sendQuickMessage('명함 가격 알려주세요')" 
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-100 transition-colors">
                    명함 가격
                </button>
                <button @click="sendQuickMessage('스티커 가격 알려주세요')" 
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-100 transition-colors">
                    스티커 가격
                </button>
                <button @click="sendQuickMessage('전단지 가격 알려주세요')" 
                    class="px-3 py-1.5 bg-white border border-gray-300 rounded-full text-xs text-gray-700 hover:bg-gray-100 transition-colors">
                    전단지 가격
                </button>
            </div>
            
            <form @submit.prevent="send" class="flex gap-2">
                <input 
                    type="text" 
                    x-model="input"
                    placeholder="가격이 궁금한 상품을 입력하세요..."
                    class="flex-1 px-4 py-2.5 border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    :disabled="loading"
                >
                <button 
                    type="submit"
                    :disabled="loading || !input.trim()"
                    class="px-4 py-2.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    
    <!-- 용지 질감 확대 모달 - 큰 이미지로 질감 확인 -->
    <div x-show="imageModal.show" x-transition 
        class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4"
        @click.self="imageModal.show = false"
        @keydown.escape.window="imageModal.show = false">
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl" style="max-width: 95vw;" @click.stop>
            <div class="px-6 py-4 border-b flex justify-between items-center bg-gradient-to-r from-blue-600 to-blue-700">
                <div>
                    <h3 class="font-bold text-white text-lg" x-text="imageModal.name"></h3>
                    <p class="text-blue-100 text-sm">용지 질감을 확인하세요</p>
                </div>
                <button @click="imageModal.show = false" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-4 bg-gray-100 flex items-center justify-center overflow-auto" style="max-height: 80vh;">
                <!-- 원본 크기 (1365x1024) 그대로 표시 -->
                <img :src="imageModal.url" :alt="imageModal.name" 
                    class="rounded-lg shadow-lg"
                    style="max-width: none; max-height: none;">
            </div>
            <div class="px-6 py-4 bg-white border-t flex justify-between items-center">
                <p class="text-gray-500 text-sm">💡 이 용지로 명함을 제작하시겠습니까?</p>
                <button @click="selectPaper(imageModal.name)" 
                    class="px-8 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition-colors font-semibold">
                    "<span x-text="imageModal.name"></span>" 선택하기
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function chatbotApp() {
    return {
        input: '',
        messages: [],
        loading: false,
        error: null,
        imageModal: {
            show: false,
            url: '',
            name: ''
        },
        
        async send() {
            if (!this.input.trim() || this.loading) return;
            
            const userMessage = this.input.trim();
            this.input = '';
            this.error = null;
            
            this.messages.push({
                role: 'user',
                content: userMessage
            });
            
            this.scrollToBottom();
            this.loading = true;
            
            const formData = new FormData();
            formData.append('message', userMessage);
            formData.append('history', JSON.stringify(this.messages.slice(0, -1)));
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            try {
                const response = await fetch('<?= \App\Core\View::url('/chatbot/chat') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    this.error = data.error;
                } else if (data.message) {
                    this.messages.push({
                        role: 'assistant',
                        content: data.message,
                        paper_images: data.paper_images || null
                    });
                }
            } catch (err) {
                this.error = '네트워크 오류가 발생했습니다.';
            } finally {
                this.loading = false;
                this.scrollToBottom();
            }
        },
        
        sendQuickMessage(msg) {
            this.input = msg;
            this.send();
        },
        
        showImageModal(img) {
            this.imageModal = {
                show: true,
                url: img.url,
                name: img.name
            };
        },
        
        selectPaper(name) {
            this.imageModal.show = false;
            this.input = name;
            this.send();
        },
        
        // 메시지 포맷팅 + 선택지에 체크 버튼 추가
        formatMessageWithChoices(content, role) {
            if (!content) return '';
            
            const lines = content.split('\n');
            let inChoiceBlock = false;  // "선택해주세요" 이후 목록인지
            let choiceBlockEnded = false;  // 선택 블록이 끝났는지
            
            const formattedLines = lines.map((line, idx) => {
                // HTML 이스케이프
                let escaped = line
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                
                // **bold** 처리
                escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong class="text-blue-600">$1</strong>');
                
                if (role !== 'assistant') return escaped;
                
                // "선택해주세요" 패턴 감지 → 다음 줄부터 선택 블록 시작
                if (line.match(/선택해\s*(주세요|보세요)|선택하세요|골라주세요/)) {
                    inChoiceBlock = true;
                    choiceBlockEnded = false;
                    return escaped;
                }
                
                // 선택 블록 안에서 번호 목록 처리
                if (inChoiceBlock && !choiceBlockEnded) {
                    // "1. 일반명함(쿠폰)" 또는 "- 단면" 패턴
                    const match = line.match(/^[\s]*(\d+[\.\)]\s*|[-•]\s*)(.+?)[\s]*$/);
                    if (match) {
                        const choiceText = match[2].trim();
                        // 제외 조건: 가격 정보, 설명 형식, 너무 긴 설명
                        const isExcluded = 
                            choiceText.includes('원)') || 
                            choiceText.includes('원 ') ||
                            choiceText.match(/:\s*\d/) ||
                            choiceText.match(/→|->/) ||  // 설명 화살표
                            choiceText.length > 50;  // 너무 긴 설명
                        
                        if (choiceText.length > 0 && !isExcluded) {
                            const prefix = match[1];
                            return `<span class="choice-line inline-flex items-center gap-1 cursor-pointer hover:bg-blue-100 rounded px-1 -mx-1 transition-colors" onclick="window.chatbotSelectChoice('${choiceText.replace(/'/g, "\\'")}')"><span class="text-blue-500">☑</span><span>${prefix}${choiceText}</span></span>`;
                        }
                    } else if (line.trim() === '') {
                        // 빈 줄이면 선택 블록 끝
                        choiceBlockEnded = true;
                    } else if (!line.match(/^\s/)) {
                        // 들여쓰기 없는 일반 텍스트면 선택 블록 끝
                        choiceBlockEnded = true;
                    }
                }
                
                return escaped;
            });
            
            return formattedLines.join('\n');
        },
        
        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('chat-messages');
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        }
    }
}

// 전역 함수: 선택지 클릭시 호출
window.chatbotSelectChoice = function(text) {
    // Alpine.js 컴포넌트 찾기
    const appEl = document.querySelector('[x-data="chatbotApp()"]');
    if (appEl && appEl.__x) {
        appEl.__x.$data.input = text;
        appEl.__x.$data.send();
    }
};
</script>
