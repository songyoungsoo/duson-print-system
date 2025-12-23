<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>타지역 전화 서비스 - 사장님을 위한 성공 파트너</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;700;900&display=swap" rel="stylesheet">
    <script type="module">
        import { GoogleGenerativeAI } from "https://esm.run/@google/generative-ai";

        // Gemini API Configuration
        const apiKey = ""; // API Key is handled by the environment
        
        // Define generateAdCopy globally so it can be called from HTML
        window.generateAdCopy = async function() {
            const industry = document.getElementById('industry').value;
            const region = document.getElementById('region').value;
            const strength = document.getElementById('strength').value;
            const resultContainer = document.getElementById('ai-result');
            const loadingSpinner = document.getElementById('loading-spinner');
            const generateBtn = document.getElementById('generate-btn');

            if (!industry || !region) {
                alert("업종과 희망 지역을 입력해주세요!");
                return;
            }

            // UI State: Loading
            resultContainer.innerHTML = '';
            resultContainer.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');
            generateBtn.disabled = true;
            generateBtn.classList.add('opacity-50', 'cursor-not-allowed');

            try {
                const genAI = new GoogleGenerativeAI(apiKey);
                const model = genAI.getGenerativeModel({ model: "gemini-2.5-flash-preview-09-2025" });

                const prompt = `
                    당신은 한국의 소상공인 마케팅 전문가입니다. 
                    다음 정보를 바탕으로 고객을 사로잡을 수 있는 매력적인 광고 카피(헤드라인 + 설명) 3가지를 만들어주세요.
                    이 광고는 '타지역 전화 서비스(070이 아닌 일반 지역번호 사용)'를 이용하여 해당 지역에 진출하는 상황을 가정합니다.
                    
                    입력 정보:
                    - 업종: ${industry}
                    - 타겟 지역: ${region}
                    - 강점: ${strength || '친절하고 신속한 서비스'}

                    응답 형식(JSON):
                    [
                        { "icon": "fa-bullhorn", "title": "광고 카피 1 제목", "desc": "설명..." },
                        { "icon": "fa-star", "title": "광고 카피 2 제목", "desc": "설명..." },
                        { "icon": "fa-thumbs-up", "title": "광고 카피 3 제목", "desc": "설명..." }
                    ]
                    
                    반드시 JSON 형식만 출력해주세요. 마크다운 태그 없이 순수 JSON 문자열만 주세요.
                `;

                const result = await model.generateContent(prompt);
                const response = await result.response;
                let text = response.text();
                
                // Remove Markdown code blocks if present
                text = text.replace(/```json/g, '').replace(/```/g, '').trim();
                
                const suggestions = JSON.parse(text);

                // Render Results
                let htmlContent = '<div class="grid grid-cols-1 md:grid-cols-3 gap-6">';
                suggestions.forEach((item, index) => {
                    htmlContent += `
                        <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-blue-500 hover:transform hover:-translate-y-1 transition duration-300">
                            <div class="text-blue-500 text-3xl mb-4"><i class="fas ${item.icon}"></i></div>
                            <h4 class="text-xl font-bold mb-2 text-gray-800">${item.title}</h4>
                            <p class="text-gray-600 text-sm leading-relaxed">${item.desc}</p>
                            <button class="mt-4 text-blue-600 font-bold text-sm hover:underline" onclick="navigator.clipboard.writeText('${item.title}\\n${item.desc}'); alert('복사되었습니다!');">
                                <i class="far fa-copy"></i> 복사하기
                            </button>
                        </div>
                    `;
                });
                htmlContent += '</div>';

                resultContainer.innerHTML = htmlContent;
                resultContainer.classList.remove('hidden');

            } catch (error) {
                console.error("Gemini API Error:", error);
                resultContainer.innerHTML = `
                    <div class="bg-red-50 text-red-600 p-4 rounded-lg text-center w-full">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        오류가 발생했습니다. 잠시 후 다시 시도해주세요.
                    </div>
                `;
                resultContainer.classList.remove('hidden');
            } finally {
                loadingSpinner.classList.add('hidden');
                generateBtn.disabled = false;
                generateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        };
    </script>
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
        }
        .hero-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
        }
        .text-shadow {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .service-card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        /* Loading Spinner Animation */
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .sparkle-btn {
            background: linear-gradient(45deg, #2563eb, #9333ea);
            background-size: 200% 200%;
            animation: rainbow 3s ease infinite;
        }
        @keyframes rainbow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <!-- Header / Hero Section -->
    <header class="hero-bg h-screen min-h-[600px] flex items-center justify-center text-white relative">
        <div class="container mx-auto px-6 text-center z-10">
            <span class="inline-block py-1 px-3 rounded-full bg-blue-600 text-sm font-bold mb-4 animate-bounce">사장님 필수 아이템</span>
            <h1 class="text-4xl md:text-6xl font-black mb-6 leading-tight text-shadow">
                월 1,000원으로 확장하는<br>
                <span class="text-blue-400">전국구 비즈니스</span>
            </h1>
            <p class="text-xl md:text-2xl mb-10 font-light text-gray-200">
                원하는 지역 번호로 고객의 전화를 놓치지 마세요.<br>
                가장 저렴하고 확실한 영업 확장의 기회입니다.
            </p>
            <div class="flex flex-col md:flex-row gap-4 justify-center">
                <button onclick="document.getElementById('ai-section').scrollIntoView({behavior: 'smooth'})" class="sparkle-btn text-white font-bold py-4 px-10 rounded-lg text-lg shadow-lg transition duration-300 transform hover:scale-105">
                    <i class="fas fa-magic mr-2"></i> AI 광고 카피 무료 체험
                </button>
                <button class="bg-transparent border-2 border-white hover:bg-white hover:text-blue-900 text-white font-bold py-4 px-10 rounded-lg text-lg transition duration-300">
                    서비스 더 알아보기
                </button>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-10 w-full text-center animate-pulse">
            <i class="fas fa-chevron-down text-3xl"></i>
        </div>
    </header>

    <!-- Key Benefits Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">왜 '타지역 전화 서비스'인가요?</h2>
                <p class="text-gray-500 text-lg">사장님의 고민, 저희가 해결해 드립니다.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
                <!-- Benefit 1 -->
                <div class="service-card bg-gray-50 p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="fas fa-won-sign"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">압도적인 비용 절감</h3>
                    <p class="text-gray-600 leading-relaxed">
                        비싼 광고비는 이제 그만!<br>
                        <span class="text-blue-600 font-bold">1회선 당 월 1,000원</span>의<br>
                        저렴한 비용으로 시작하세요.
                    </p>
                </div>
                <!-- Benefit 2 -->
                <div class="service-card bg-gray-50 p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">지역 확장 효과</h3>
                    <p class="text-gray-600 leading-relaxed">
                        사무실 이전 없이,<br>
                        <span class="text-green-600 font-bold">원하는 지역 번호를 개통</span>하여<br>
                        영업 지역을 넓힐 수 있습니다.
                    </p>
                </div>
                <!-- Benefit 3 -->
                <div class="service-card bg-gray-50 p-8 rounded-2xl shadow-sm border border-gray-100 text-center">
                    <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">확실한 홍보 효과</h3>
                    <p class="text-gray-600 leading-relaxed">
                        지역 번호를 통한 신뢰도 상승!<br>
                        타겟 지역 고객에게<br>
                        <span class="text-red-600 font-bold">자연스러운 노출</span>이 가능합니다.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Gemini AI Feature Section -->
    <section id="ai-section" class="py-20 bg-gradient-to-br from-indigo-50 to-blue-100 relative overflow-hidden">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-blue-200 rounded-full opacity-30 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-purple-200 rounded-full opacity-30 blur-3xl"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-12">
                <span class="inline-block py-1 px-3 rounded-full bg-indigo-100 text-indigo-700 text-sm font-bold mb-3 border border-indigo-200">
                    <i class="fas fa-magic mr-1"></i> Gemini AI Powered
                </span>
                <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900">
                    AI 스마트 광고 카피 생성기
                </h2>
                <p class="text-gray-600 text-lg">
                    확장하고 싶은 지역에 딱 맞는 홍보 문구를 AI가 무료로 만들어 드립니다.<br>
                    지금 바로 비즈니스 아이디어를 테스트해보세요!
                </p>
            </div>

            <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="p-8 md:p-10">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div>
                            <label for="industry" class="block text-sm font-bold text-gray-700 mb-2">업종 <span class="text-red-500">*</span></label>
                            <input type="text" id="industry" placeholder="예: 이삿짐 센터, 에어컨 청소" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label for="region" class="block text-sm font-bold text-gray-700 mb-2">희망 확장 지역 <span class="text-red-500">*</span></label>
                            <input type="text" id="region" placeholder="예: 부산 해운대구, 대구 수성구" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                        <div>
                            <label for="strength" class="block text-sm font-bold text-gray-700 mb-2">우리 가게 장점</label>
                            <input type="text" id="strength" placeholder="예: 10년 경력, 친절함, 최저가" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                        </div>
                    </div>

                    <div class="text-center mb-8">
                        <button id="generate-btn" onclick="generateAdCopy()" class="sparkle-btn w-full md:w-auto text-white font-bold py-4 px-12 rounded-full text-xl shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition duration-300 flex items-center justify-center mx-auto">
                            <i class="fas fa-wand-magic-sparkles mr-2"></i> AI 광고 문구 생성하기
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div id="loading-spinner" class="hidden text-center py-10">
                        <div class="loader mb-4"></div>
                        <p class="text-gray-500 animate-pulse">Gemini AI가 사장님을 위한 최적의 카피를 고민 중입니다...</p>
                    </div>

                    <!-- Result Area -->
                    <div id="ai-result" class="hidden animate-fade-in-up">
                        <!-- Results will be injected here via JS -->
                    </div>
                </div>
                <div class="bg-gray-50 px-8 py-4 text-center text-sm text-gray-500 border-t border-gray-100">
                    <i class="fas fa-info-circle mr-1"></i> 생성된 문구는 자유롭게 사용하실 수 있습니다. 타지역 전화 서비스와 함께라면 효과 200%!
                </div>
            </div>
        </div>
    </section>

    <!-- Recommendation Section -->
    <section class="py-20 bg-gray-100">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2">
                    <img src="https://images.unsplash.com/photo-1556761175-5973dc0f32e7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1600&q=80" alt="비즈니스 미팅" class="rounded-lg shadow-2xl">
                </div>
                <div class="md:w-1/2">
                    <h2 class="text-3xl md:text-4xl font-bold mb-8 text-gray-900">
                        이런 사장님께<br>
                        <span class="text-blue-600">강력 추천</span> 드립니다!
                    </h2>
                    
                    <div class="space-y-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-check-circle text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-gray-800">저비용 고효율 마케팅을 찾는 분</h4>
                                <p class="text-gray-600 mt-1">전단지보다 저렴하지만 효과는 확실합니다.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-check-circle text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-gray-800">사업 확장을 계획 중인 사장님</h4>
                                <p class="text-gray-600 mt-1">점포를 새로 낼 필요 없이 전화번호 하나로 지역을 선점하세요.</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <i class="fas fa-check-circle text-blue-500 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-bold text-gray-800">출장 및 현장 서비스 전문가</h4>
                                <p class="text-gray-600 mt-1">이동이 잦고 넓은 지역을 커버해야 하는 업종에 최적화되어 있습니다.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Industries List Section -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-4">다양한 업종에서 이미 활용 중입니다</h2>
            <p class="text-gray-500 mb-12">현장 서비스부터 전문 기술직까지, 모든 비즈니스에 열려있습니다.</p>
            
            <div class="max-w-4xl mx-auto">
                <div class="flex flex-wrap justify-center gap-3">
                    <!-- Construction & Heavy -->
                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium border border-blue-100">#이삿짐센터</span>
                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium border border-blue-100">#인력사무실</span>
                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium border border-blue-100">#철거업체</span>
                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium border border-blue-100">#폐기물처리</span>
                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium border border-blue-100">#사다리차</span>
                    <span class="px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-medium border border-blue-100">#중장비</span>
                    
                    <!-- Repair & Install -->
                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">#에어컨설치</span>
                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">#보일러수리</span>
                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">#설비배관</span>
                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">#태양광</span>
                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">#컴퓨터수리</span>
                    <span class="px-4 py-2 bg-green-50 text-green-700 rounded-full font-medium border border-green-100">#창호/방충망</span>
                    
                    <!-- Service & Life -->
                    <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-full font-medium border border-yellow-100">#퀵서비스</span>
                    <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-full font-medium border border-yellow-100">#열쇠/도어락</span>
                    <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-full font-medium border border-yellow-100">#심부름센터</span>
                    <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-full font-medium border border-yellow-100">#입주청소</span>
                    <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-full font-medium border border-yellow-100">#유품정리</span>
                    <span class="px-4 py-2 bg-yellow-50 text-yellow-700 rounded-full font-medium border border-yellow-100">#출장세차</span>
                    
                    <!-- Others -->
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium border border-gray-200">#LPG가스</span>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium border border-gray-200">#폐차장</span>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium border border-gray-200">#운전학원</span>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium border border-gray-200">#간판</span>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium border border-gray-200">#렌터카</span>
                    <span class="px-4 py-2 bg-gray-100 text-gray-700 rounded-full font-medium border border-gray-200">#상조/장례</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Footer -->
    <footer class="bg-blue-900 text-white py-16">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-3xl font-bold mb-6">성공적인 비즈니스의 시작, 지금 바로 문의하세요</h2>
            <p class="text-blue-200 mb-8 text-lg">망설이는 순간에도 경쟁업체는 지역을 넓혀가고 있습니다.</p>
            
            <div class="flex flex-col md:flex-row justify-center items-center gap-6">
                <a href="#" class="flex items-center gap-3 bg-white text-blue-900 font-bold py-4 px-10 rounded-full text-xl hover:bg-gray-100 transition duration-300 shadow-lg">
                    <i class="fas fa-phone-alt animate-pulse"></i>
                    010-XXXX-XXXX
                </a>
                <button class="border-2 border-white text-white font-bold py-4 px-10 rounded-full text-xl hover:bg-blue-800 transition duration-300">
                    온라인 상담 신청
                </button>
            </div>
            <p class="mt-8 text-sm text-blue-300 opacity-70">
                © 2024 타지역 전화 서비스. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>