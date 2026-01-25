<div class="max-w-4xl mx-auto px-4 py-8" x-data="copywriterApp()">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">카피라이터</h1>
        <p class="text-gray-600">AI가 만드는 헤드카피 & 서브카피</p>
    </div>

    <?php if (!$isConfigured): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-center gap-2 text-yellow-800">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span class="font-medium">API 키가 설정되지 않았습니다.</span>
        </div>
        <p class="mt-2 text-sm text-yellow-700">.env 파일에 GEMINI_API_KEY를 설정해주세요. <a href="https://aistudio.google.com/apikey" target="_blank" class="underline text-yellow-800 font-medium">무료 발급 →</a></p>
    </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-2 gap-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-blue-600 text-white rounded-full text-sm flex items-center justify-center">1</span>
                정보 입력
            </h2>
            
            <form @submit.prevent="generate" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        제품/서비스명 <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="form.product"
                        placeholder="예: 프리미엄 명함 인쇄"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        타겟 고객
                    </label>
                    <input 
                        type="text" 
                        x-model="form.target"
                        placeholder="예: 스타트업 대표, 프리랜서 디자이너"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        핵심 키워드
                    </label>
                    <input 
                        type="text" 
                        x-model="form.keywords"
                        placeholder="예: 고급, 빠른배송, 합리적 가격"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            톤앤매너
                        </label>
                        <select 
                            x-model="form.tone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="전문적">전문적</option>
                            <option value="친근함">친근함</option>
                            <option value="유머러스">유머러스</option>
                            <option value="감성적">감성적</option>
                            <option value="고급스러움">고급스러움</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            업종
                        </label>
                        <select 
                            x-model="form.category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option value="인쇄/출판">인쇄/출판</option>
                            <option value="식품/요식업">식품/요식업</option>
                            <option value="뷰티/패션">뷰티/패션</option>
                            <option value="IT/테크">IT/테크</option>
                            <option value="교육">교육</option>
                            <option value="건강/의료">건강/의료</option>
                            <option value="부동산">부동산</option>
                            <option value="금융">금융</option>
                            <option value="일반">일반</option>
                        </select>
                    </div>
                </div>
                
                <button 
                    type="submit"
                    :disabled="loading || !form.product"
                    class="w-full py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex items-center justify-center gap-2"
                >
                    <template x-if="loading">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </template>
                    <span x-text="loading ? '생성 중...' : '카피 생성하기'"></span>
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-6 h-6 bg-green-600 text-white rounded-full text-sm flex items-center justify-center">2</span>
                생성 결과
            </h2>
            
            <template x-if="error">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
                    <p x-text="error"></p>
                </div>
            </template>
            
            <template x-if="!copies.length && !error && !loading">
                <div class="text-center py-12 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <p>정보를 입력하고 버튼을 누르면<br>AI가 카피를 생성합니다.</p>
                </div>
            </template>
            
            <template x-if="copies.length">
                <div class="space-y-4">
                    <template x-for="(copy, index) in copies" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:bg-blue-50/30 transition-colors group">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1">
                                    <p class="text-xl font-bold text-gray-900 mb-1" x-text="copy.head"></p>
                                    <p class="text-gray-600" x-text="copy.sub"></p>
                                </div>
                                <button 
                                    @click="copyToClipboard(copy)"
                                    class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-100 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                                    title="복사하기"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    
                    <button 
                        @click="generate"
                        :disabled="loading"
                        class="w-full py-2 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-50 disabled:opacity-50 transition-colors"
                    >
                        다시 생성하기
                    </button>
                </div>
            </template>
        </div>
    </div>

    <div x-show="copied" x-transition class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg">
        클립보드에 복사되었습니다!
    </div>
</div>

<script>
function copywriterApp() {
    return {
        form: {
            product: '',
            target: '',
            keywords: '',
            tone: '전문적',
            category: '인쇄/출판'
        },
        loading: false,
        error: null,
        copies: [],
        copied: false,
        
        async generate() {
            this.loading = true;
            this.error = null;
            
            const formData = new FormData();
            formData.append('product', this.form.product);
            formData.append('target', this.form.target);
            formData.append('keywords', this.form.keywords);
            formData.append('tone', this.form.tone);
            formData.append('category', this.form.category);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }
            
            try {
                const response = await fetch('<?= \App\Core\View::url('/copywriter/generate') ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.error) {
                    this.error = data.error;
                } else if (data.copies) {
                    this.copies = data.copies;
                }
            } catch (err) {
                this.error = '네트워크 오류가 발생했습니다.';
            } finally {
                this.loading = false;
            }
        },
        
        async copyToClipboard(copy) {
            const text = `${copy.head}\n${copy.sub}`;
            try {
                await navigator.clipboard.writeText(text);
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            } catch (err) {
                alert('복사에 실패했습니다.');
            }
        }
    }
}
</script>
