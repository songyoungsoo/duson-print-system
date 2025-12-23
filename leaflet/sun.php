<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>세븐스타에너지 전단지</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #f3f4f6;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        /* A4 Paper Specification */
        .a4-page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        @media print {
            body {
                background: none;
                margin: 0;
            }
            .a4-page {
                margin: 0;
                width: 100%;
                height: 100%;
                box-shadow: none;
                page-break-after: always;
            }
            .no-print {
                display: none !important;
            }
        }

        .highlight-gradient {
            background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        }
        
        .accent-text {
            color: #d97706;
        }

        .price-tag {
            background-color: #fffbeb;
            border: 2px solid #d97706;
            color: #b45309;
            transform: rotate(-2deg);
        }
    </style>
</head>
<body>

    <!-- Control Buttons (Hidden when printing) -->
    <div class="no-print fixed top-4 right-4 z-50 flex gap-2">
        <button onclick="window.print()" class="bg-gray-600 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2">
            <i class="fas fa-print"></i> 인쇄 (Ctrl+P)
        </button>
        <button onclick="downloadPDF()" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded shadow-lg flex items-center gap-2">
            <i class="fas fa-file-pdf"></i> PDF 다운로드
        </button>
    </div>

    <!-- Main A4 Page -->
    <div id="flyer-content" class="a4-page">
        
        <!-- Header Section -->
        <header class="relative w-full h-64 overflow-hidden">
            <!-- Background Image Container -->
            <div class="absolute inset-0 bg-gray-200">
                <!-- User Image: Fallback to placeholder if fails -->
                <img src="i_8474ce3fe885.jpg" 
                     onerror="this.src='https://images.unsplash.com/photo-1513694203232-719a280e022f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1000&q=80'" 
                     alt="세븐스타에너지 시공 사례" 
                     class="w-full h-full object-cover opacity-90">
                <div class="absolute inset-0 bg-gradient-to-r from-black/70 to-transparent"></div>
            </div>

            <div class="relative z-10 p-8 h-full flex flex-col justify-center text-white">
                <div class="bg-yellow-500 text-black font-bold text-xs px-2 py-1 inline-block w-max mb-2 rounded">
                    세븐스타에너지 혁신 솔루션
                </div>
                <h1 class="text-4xl font-black leading-tight mb-2 drop-shadow-md">
                    우리 집 전기 요금,<br>
                    <span class="text-yellow-400">정말 이 가격이 가능할까요?</span>
                </h1>
                <p class="text-lg font-medium opacity-90">
                    세븐스타에너지의 시공을 통해<br>혁신적인 에너지 절감을 경험해 보세요.
                </p>
            </div>
        </header>

        <!-- Shocking Price Badge Section -->
        <section class="bg-teal-700 text-white py-4 px-8 flex items-center justify-between shadow-md relative z-20">
            <div>
                <h2 class="text-xl font-bold">실제 사례 <span class="text-sm font-normal opacity-80">(30평형 가정집)</span></h2>
                <p class="text-sm">놀라운 에너지 절감 효과를 확인하세요!</p>
            </div>
            <div class="bg-white text-teal-800 px-6 py-2 rounded-lg font-black text-2xl shadow-lg border-2 border-yellow-400 animate-pulse">
                월 청구 금액 1,140원 / 1,510원 실현!
            </div>
        </section>

        <!-- Main Content Grid -->
        <main class="flex-grow p-8 space-y-6 bg-white">
            
            <!-- Grid Layout for 3 Key Features -->
            <div class="grid grid-cols-1 gap-5">
                
                <!-- Section 1 -->
                <div class="flex gap-4 border-b border-gray-100 pb-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center text-teal-700 text-2xl">
                        <i class="fas fa-solar-panel"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">1. 핵심 에너지 생산 및 절감 시스템</h3>
                        <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                            <li><span class="font-bold text-teal-700">6kW 가정용 태양광:</span> 월 600kW 이상 생산. 가스/기름 불필요. <span class="text-xs bg-gray-100 px-1 rounded text-gray-500">단독주택 전용</span></li>
                            <li><span class="font-bold text-teal-700">온유관 보일러:</span> 보일러 본체 및 고장 걱정 NO. 동파 염려가 없고 반영구적 사용으로 경제성 탁월.</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 2 -->
                <div class="flex gap-4 border-b border-gray-100 pb-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 text-2xl">
                        <i class="fas fa-plug"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">2. 스마트하고 편리한 생활 가전</h3>
                        <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                            <li><span class="font-bold text-orange-600">3구 인덕션:</span> 가스 없는 전기 작동으로 화재 위험 획기적 감소.</li>
                            <li><span class="font-bold text-orange-600">올 스테인리스 온수기:</span> 샤워기, 싱크대 온수 원활 공급. 고장이 거의 없는 내구성.</li>
                        </ul>
                    </div>
                </div>

                <!-- Section 3 -->
                <div class="flex gap-4">
                    <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-2xl">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 mb-1">3. 맞춤형 스마트 컨트롤 시스템</h3>
                        <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                            <li><span class="font-bold text-blue-600">와이파이 조절기:</span> 외출 중 원격 온도 조절로 귀가 시 쾌적함 유지.</li>
                            <li><span class="font-bold text-blue-600">각방 조절기:</span> 방마다 개별 온도 조절 가능. 기존 보일러 단점 개선.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Metaphor Box -->
            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 mt-2">
                <div class="flex items-start gap-3">
                    <i class="fas fa-lightbulb text-yellow-500 text-xl mt-1"></i>
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm mb-1">쉽게 이해하기</h4>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            세븐스타에너지의 솔루션은 <span class="bg-yellow-100 px-1 font-bold text-gray-800">'집안에 작은 발전소와 똑똑한 관리사'</span>를 두는 것과 같습니다. 스스로 에너지를 만들고(태양광), 낭비 없이 배분하여(스마트 조절기) 가계 경제에 큰 보탬을 줍니다.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Why Us Grid -->
            <div class="mt-4 pt-2">
                <h3 class="text-center font-bold text-gray-800 mb-3 text-lg border-b-2 border-teal-500 inline-block px-4 mx-auto block w-max">왜 세븐스타에너지인가요?</h3>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-teal-50 p-3 rounded-lg">
                        <i class="fas fa-coins text-teal-600 text-2xl mb-2"></i>
                        <h4 class="font-bold text-sm text-gray-800">확실한 비용 절감</h4>
                        <p class="text-xs text-gray-500 mt-1">저렴한 전기요금 유지</p>
                    </div>
                    <div class="bg-teal-50 p-3 rounded-lg">
                        <i class="fas fa-leaf text-teal-600 text-2xl mb-2"></i>
                        <h4 class="font-bold text-sm text-gray-800">미래형 솔루션</h4>
                        <p class="text-xs text-gray-500 mt-1">태양광 냉·난방 구축</p>
                    </div>
                    <div class="bg-teal-50 p-3 rounded-lg">
                        <i class="fas fa-shield-alt text-teal-600 text-2xl mb-2"></i>
                        <h4 class="font-bold text-sm text-gray-800">안전과 내구성</h4>
                        <p class="text-xs text-gray-500 mt-1">화재 위험 감소</p>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer Contact -->
        <footer class="bg-gray-800 text-white p-6 mt-auto">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-400 text-xs mb-1">상담 및 문의</p>
                    <h2 class="text-2xl font-black text-yellow-400 tracking-wider">세븐스타에너지</h2>
                </div>
                <div class="text-right">
                    <p class="text-lg font-bold flex items-center justify-end gap-2">
                        <i class="fas fa-phone-alt text-teal-400"></i> 055-747-7999
                    </p>
                    <p class="text-lg font-bold flex items-center justify-end gap-2">
                        <i class="fas fa-mobile-alt text-teal-400"></i> 010-2141-3233
                    </p>
                </div>
            </div>
        </footer>

    </div>

    <script>
        function downloadPDF() {
            // PDF 다운로드 시작 알림 (선택 사항)
            const btn = document.querySelector('button[onclick="downloadPDF()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> 변환 중...';
            btn.disabled = true;

            const element = document.getElementById('flyer-content');
            const opt = {
                margin:       0,
                filename:     '세븐스타에너지_전단지.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true }, // 고화질 설정 및 이미지 로딩 허용
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };

            // PDF 생성 및 저장
            html2pdf().set(opt).from(element).save().then(() => {
                // 완료 후 버튼 복구
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                alert('PDF 생성 중 오류가 발생했습니다. 브라우저 인쇄(Ctrl+P)를 시도해주세요.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>
</body>
</html>