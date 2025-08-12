<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>두손기획인쇄 — 사무용 인쇄 전문 서비스</title>
  <meta name="description" content="명함, 전단지, 스티커, 봉투, 카다록 등 사무용 인쇄물 전문 제작. 실시간 견적과 빠른 주문 시스템." />
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'cafe': {
              50: '#f8fafc',
              100: '#f1f5f9',
              200: '#e2e8f0',
              300: '#cbd5e1',
              400: '#94a3b8',
              500: '#64748b',
              600: '#475569',
              700: '#334155',
              800: '#1e293b',
              900: '#0f172a',
            }
          }
        }
      }
    }
  </script>
  <style>
    :root { 
      --primary: #0ea5e9;
      --primary-dark: #0284c7;
      --secondary: #6366f1;
      --accent: #8b5cf6;
      --neutral: #64748b;
    }
    html { scroll-behavior: smooth; }
    body { 
      font-family: 'Noto Sans KR', system-ui, -apple-system, sans-serif;
      background: #fafbfc;
      color: #334155;
    }
    .gradient-bg {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 25%, #f8fafc 100%);
    }
    .card-shadow {
      box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
      transition: all 0.3s ease;
    }
    .card-shadow:hover {
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
      transform: translateY(-2px);
    }
    .btn-primary {
      background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
      color: white;
      transition: all 0.3s ease;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #0284c7 0%, #4f46e5 100%);
      transform: translateY(-1px);
    }
    .text-gradient {
      background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 60%, #8b5cf6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-slate-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg">
            D
          </div>
          <div>
            <div class="text-xl font-bold text-slate-800">두손기획인쇄</div>
            <div class="text-xs text-slate-500 -mt-1">Professional Print Service</div>
          </div>
        </div>
        
        <nav class="hidden md:flex items-center gap-8">
          <a href="#products" class="text-slate-600 hover:text-sky-600 font-medium transition">제품</a>
          <a href="#process" class="text-slate-600 hover:text-sky-600 font-medium transition">주문과정</a>
          <a href="#about" class="text-slate-600 hover:text-sky-600 font-medium transition">회사소개</a>
          <a href="#contact" class="text-slate-600 hover:text-sky-600 font-medium transition">문의</a>
        </nav>
        
        <div class="flex items-center gap-3">
          <a href="/member/login.php" class="hidden sm:block px-4 py-2 text-slate-600 hover:text-slate-800 transition">로그인</a>
          <a href="/MlangOrder_PrintAuto/OnlineOrder_unified.php" class="btn-primary px-6 py-2 rounded-lg text-sm font-semibold">바로 주문</a>
        </div>
        
        <!-- Mobile menu button -->
        <button id="mobile-menu-btn" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
      
      <!-- Mobile menu -->
      <div id="mobile-menu" class="md:hidden hidden py-4 border-t border-slate-200">
        <div class="flex flex-col gap-4">
          <a href="#products" class="text-slate-600 hover:text-sky-600 font-medium">제품</a>
          <a href="#process" class="text-slate-600 hover:text-sky-600 font-medium">주문과정</a>
          <a href="#about" class="text-slate-600 hover:text-sky-600 font-medium">회사소개</a>
          <a href="#contact" class="text-slate-600 hover:text-sky-600 font-medium">문의</a>
          <div class="flex gap-2 pt-2">
            <a href="/member/login.php" class="flex-1 px-4 py-2 text-center border border-slate-300 rounded-lg text-slate-600">로그인</a>
            <a href="/MlangOrder_PrintAuto/OnlineOrder_unified.php" class="flex-1 btn-primary px-4 py-2 rounded-lg text-center text-sm font-semibold">바로 주문</a>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="gradient-bg py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-sky-50 border border-sky-200 rounded-full text-sky-700 text-sm font-medium mb-6">
          <span class="w-2 h-2 bg-sky-500 rounded-full"></span>
          사무용 인쇄물 전문 서비스
        </div>
        
        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-slate-800 mb-6">
          세련되고 전문적인
          <span class="block text-gradient">사무용 인쇄 솔루션</span>
        </h1>
        
        <p class="text-xl text-slate-600 max-w-3xl mx-auto mb-10 leading-relaxed">
          명함부터 카다록까지, 비즈니스에 필요한 모든 인쇄물을 
          <strong class="text-slate-700">실시간 견적</strong>과 함께 간편하게 주문하세요.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-12">
          <a href="#products" class="btn-primary px-8 py-4 rounded-xl text-lg font-semibold">
            📋 제품 둘러보기
          </a>
          <a href="/MlangOrder_PrintAuto/OnlineOrder_unified.php" class="px-8 py-4 rounded-xl border-2 border-slate-300 text-slate-700 font-semibold hover:border-sky-400 hover:text-sky-600 transition">
            ⚡ 즉시 주문
          </a>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-2xl mx-auto text-center">
          <div class="flex flex-col items-center gap-2">
            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-green-600 text-xl">✓</div>
            <span class="text-sm text-slate-600 font-medium">실시간 견적</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 text-xl">📁</div>
            <span class="text-sm text-slate-600 font-medium">파일 업로드</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600 text-xl">🎨</div>
            <span class="text-sm text-slate-600 font-medium">디자인 서비스</span>
          </div>
          <div class="flex flex-col items-center gap-2">
            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 text-xl">🚚</div>
            <span class="text-sm text-slate-600 font-medium">빠른 배송</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Products Section -->
  <section id="products" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl font-bold text-slate-800 mb-4">비즈니스 필수 인쇄물</h2>
        <p class="text-xl text-slate-600 max-w-2xl mx-auto">
          사무실에서 필요한 모든 인쇄물을 한 곳에서 주문하세요
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
        <!-- Stickers -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/shop/view_modern.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-red-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">🏷️</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">스티커</h3>
          <p class="text-slate-600 mb-4">브랜드 홍보와 제품 라벨링에 최적. 다양한 재질과 사이즈로 맞춤 제작</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Flyers -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/inserted/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">📄</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">전단지</h3>
          <p class="text-slate-600 mb-4">마케팅 캠페인과 행사 홍보용. 소량부터 대량까지 합리적인 가격</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Magnetic Stickers -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/msticker/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">🧲</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">종이자석</h3>
          <p class="text-slate-600 mb-4">냉장고나 철제 표면에 부착 가능. 생활 밀착형 광고 매체</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Business Cards -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/NameCard/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">💼</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">명함</h3>
          <p class="text-slate-600 mb-4">비즈니스 첫인상을 좌우하는 필수 아이템. 프리미엄 용지와 후가공</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Coupons -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/MerchandiseBond/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">🎟️</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">쿠폰</h3>
          <p class="text-slate-600 mb-4">고객 충성도 프로그램과 프로모션용. 넘버링과 미싱 옵션 제공</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Envelopes -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/envelope/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">✉️</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">봉투</h3>
          <p class="text-slate-600 mb-4">공식 문서와 우편물 발송용. 다양한 크기와 용지 옵션</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Forms -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/NcrFlambeau/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-teal-100 to-teal-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">📋</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">양식지</h3>
          <p class="text-slate-600 mb-4">전표, 영수증, 거래명세서 등. NCR 복사지와 넘버링 지원</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Catalogs -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/cadarok/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-orange-100 to-orange-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">📚</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">카다록</h3>
          <p class="text-slate-600 mb-4">제품 소개 및 회사 홍보용 책자. 다양한 제본 방식 선택</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
        
        <!-- Posters -->
        <div class="card-shadow bg-white rounded-2xl p-6 group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/LittlePrint/index.php'">
          <div class="w-16 h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-2xl flex items-center justify-center text-2xl mb-4 group-hover:scale-110 transition">🖼️</div>
          <h3 class="text-xl font-bold text-slate-800 mb-2">포스터</h3>
          <p class="text-slate-600 mb-4">행사, 전시, 인테리어용 대형 인쇄물. 고화질 출력으로 선명한 품질</p>
          <div class="text-sky-600 font-semibold flex items-center gap-2">
            견적받기 <span class="group-hover:translate-x-1 transition">→</span>
          </div>
        </div>
      </div>
      
      <div class="text-center">
        <a href="/shop/cart.php" class="btn-primary px-8 py-4 rounded-xl text-lg font-semibold inline-flex items-center gap-3">
          🛒 장바구니에서 일괄 주문
        </a>
      </div>
    </div>
  </section>

  <!-- Process Section -->
  <section id="process" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-16">
        <h2 class="text-3xl font-bold text-slate-800 mb-4">간단한 주문 과정</h2>
        <p class="text-xl text-slate-600">4단계로 완성되는 전문적인 인쇄 서비스</p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div class="text-center">
          <div class="w-20 h-20 bg-sky-100 rounded-full flex items-center justify-center text-2xl text-sky-600 mx-auto mb-4">1️⃣</div>
          <h3 class="text-lg font-bold text-slate-800 mb-2">제품 선택</h3>
          <p class="text-slate-600">필요한 인쇄물 선택하고 옵션 설정. 실시간으로 가격 확인</p>
        </div>
        
        <div class="text-center">
          <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center text-2xl text-indigo-600 mx-auto mb-4">2️⃣</div>
          <h3 class="text-lg font-bold text-slate-800 mb-2">파일 업로드</h3>
          <p class="text-slate-600">완성된 디자인 파일 업로드 또는 디자인 의뢰</p>
        </div>
        
        <div class="text-center">
          <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center text-2xl text-purple-600 mx-auto mb-4">3️⃣</div>
          <h3 class="text-lg font-bold text-slate-800 mb-2">검수 & 교정</h3>
          <p class="text-slate-600">전문 관리자 검수 후 교정안 확인 및 최종 승인</p>
        </div>
        
        <div class="text-center">
          <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-2xl text-green-600 mx-auto mb-4">4️⃣</div>
          <h3 class="text-lg font-bold text-slate-800 mb-2">제작 & 배송</h3>
          <p class="text-slate-600">품질 검사 후 안전 포장하여 지정 장소로 배송</p>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div>
          <h2 class="text-3xl font-bold text-slate-800 mb-6">
            사무용에 최적화된<br>
            <span class="text-gradient">전문 인쇄 서비스</span>
          </h2>
          <p class="text-lg text-slate-600 mb-8 leading-relaxed">
            두손기획인쇄는 20년 이상의 경험으로 기업과 개인 고객에게 
            최고 품질의 인쇄 서비스를 제공합니다. 
            사무용 인쇄물에 특화된 시스템으로 효율적이고 세련된 결과물을 보장합니다.
          </p>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center text-green-600">✓</div>
              <span class="font-medium text-slate-700">실시간 견적 시스템</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">✓</div>
              <span class="font-medium text-slate-700">전문 디자인 서비스</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">✓</div>
              <span class="font-medium text-slate-700">빠른 제작 & 배송</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600">✓</div>
              <span class="font-medium text-slate-700">사후 관리 서비스</span>
            </div>
          </div>
          
          <a href="#contact" class="btn-primary px-6 py-3 rounded-lg font-semibold">상담 문의하기</a>
        </div>
        
        <div class="grid grid-cols-2 gap-6">
          <div class="space-y-6">
            <div class="card-shadow bg-white rounded-2xl p-6 text-center">
              <div class="text-3xl font-bold text-sky-600 mb-2">20+</div>
              <div class="text-slate-600 font-medium">경험 년수</div>
            </div>
            <div class="card-shadow bg-white rounded-2xl p-6 text-center">
              <div class="text-3xl font-bold text-indigo-600 mb-2">9</div>
              <div class="text-slate-600 font-medium">주요 제품군</div>
            </div>
          </div>
          <div class="space-y-6 mt-8">
            <div class="card-shadow bg-white rounded-2xl p-6 text-center">
              <div class="text-3xl font-bold text-purple-600 mb-2">1000+</div>
              <div class="text-slate-600 font-medium">월 주문건수</div>
            </div>
            <div class="card-shadow bg-white rounded-2xl p-6 text-center">
              <div class="text-3xl font-bold text-green-600 mb-2">99%</div>
              <div class="text-slate-600 font-medium">고객 만족도</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="py-20 bg-slate-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-slate-800 mb-4">문의 및 상담</h2>
        <p class="text-xl text-slate-600">프로젝트 문의나 견적 요청을 남겨주세요</p>
      </div>
      
      <div class="card-shadow bg-white rounded-2xl p-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Contact Info -->
          <div>
            <h3 class="text-xl font-bold text-slate-800 mb-6">연락처 정보</h3>
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-100 rounded-lg flex items-center justify-center text-sky-600">📞</div>
                <div>
                  <div class="font-semibold text-slate-800">전화</div>
                  <div class="text-slate-600">02-2632-1830, 1688-2384</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">📧</div>
                <div>
                  <div class="font-semibold text-slate-800">이메일</div>
                  <div class="text-slate-600">dsp1830@naver.com</div>
                </div>
              </div>
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">📍</div>
                <div>
                  <div class="font-semibold text-slate-800">주소</div>
                  <div class="text-slate-600">서울 영등포구 영등포로 36길 9<br>송호빌딩 1F</div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Contact Form -->
          <div>
            <form action="/contact.php" method="post" class="space-y-4">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input 
                  name="name" 
                  placeholder="성함" 
                  required
                  class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none"
                />
                <input 
                  name="company" 
                  placeholder="회사명" 
                  class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none"
                />
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <input 
                  name="email" 
                  type="email"
                  placeholder="이메일" 
                  required
                  class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none"
                />
                <input 
                  name="phone" 
                  placeholder="연락처" 
                  class="px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none"
                />
              </div>
              <textarea 
                name="message" 
                placeholder="문의 내용 (필요한 제품, 수량, 납기 등을 자세히 적어주세요)"
                rows="5"
                required
                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none resize-vertical"
              ></textarea>
              <button type="submit" class="w-full btn-primary py-3 rounded-lg font-semibold">
                문의 보내기
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-slate-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 bg-gradient-to-br from-sky-500 to-indigo-600 rounded-lg flex items-center justify-center text-white font-bold">D</div>
            <span class="text-lg font-bold">두손기획인쇄</span>
          </div>
          <p class="text-slate-400 text-sm leading-relaxed">
            전문적이고 세련된 사무용 인쇄 솔루션을 제공하는 두손기획인쇄입니다.
          </p>
        </div>
        
        <div>
          <h4 class="font-semibold mb-4">주요 서비스</h4>
          <ul class="space-y-2 text-sm text-slate-400">
            <li><a href="/MlangPrintAuto/shop/view_modern.php" class="hover:text-white transition">스티커</a></li>
            <li><a href="/MlangPrintAuto/inserted/index.php" class="hover:text-white transition">전단지</a></li>
            <li><a href="/MlangPrintAuto/NameCard/index.php" class="hover:text-white transition">명함</a></li>
            <li><a href="/MlangPrintAuto/cadarok/index.php" class="hover:text-white transition">카다록</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-semibold mb-4">고객 지원</h4>
          <ul class="space-y-2 text-sm text-slate-400">
            <li><a href="/bbs/" class="hover:text-white transition">고객센터</a></li>
            <li><a href="/shop/cart.php" class="hover:text-white transition">장바구니</a></li>
            <li><a href="/member/login.php" class="hover:text-white transition">로그인</a></li>
            <li><a href="#" class="hover:text-white transition">이용약관</a></li>
          </ul>
        </div>
        
        <div>
          <h4 class="font-semibold mb-4">연락처</h4>
          <div class="space-y-2 text-sm text-slate-400">
            <div>📞 02-2632-1830</div>
            <div>📱 1688-2384</div>
            <div>📧 dsp1830@naver.com</div>
            <div>🌐 www.dsp114.com</div>
          </div>
        </div>
      </div>
      
      <hr class="border-slate-700 my-8">
      
      <div class="flex flex-col md:flex-row justify-between items-center text-sm text-slate-400">
        <div>
          © <span id="currentYear"></span> 두손기획인쇄. All rights reserved.
        </div>
        <div class="flex gap-6 mt-4 md:mt-0">
          <a href="#" class="hover:text-white transition">개인정보처리방침</a>
          <a href="#" class="hover:text-white transition">이용약관</a>
        </div>
      </div>
    </div>
  </footer>

  <script>
    // Set current year
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    mobileMenuBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
    
    // Close mobile menu when clicking on links
    document.querySelectorAll('#mobile-menu a').forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
      });
    });
  </script>
</body>
</html>