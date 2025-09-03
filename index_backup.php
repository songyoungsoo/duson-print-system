<?php
// DB 연결 (보안 상수 임시 비활성화)
// include 'includes/db_constants.php';
include 'db.php';
$connect = $db; // auth.php expects $connect variable

// Debug: Check if database connection is valid
if (!$connect) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Include the auth system which handles login/logout processing
include 'includes/auth.php';

// $is_logged_in and $user_name variables are set by auth.php
// $login_message is also set by auth.php
?>
<?php
$page_title = '두손기획인쇄 - 명함 스티커 전단지 봉투 카다록 포스터 라벨 인쇄 전문';
$current_page = 'home';

// 공통 헤더 포함
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- DSP Gallery Layout CSS -->
<link rel="stylesheet" href="/css/style250801.css">
<link rel="stylesheet" href="/assets/css/layout.css">
<style>
:root { 
  --primary: #0ea5e9;
  --primary-dark: #0284c7;
  --secondary: #4f46e5;
  --accent: #6366f1;
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
  background: linear-gradient(135deg, #0ea5e9 0%, #4f46e5 100%);
  color: white;
  transition: all 0.3s ease;
}
.btn-primary:hover {
  background: linear-gradient(135deg, #0284c7 0%, #3730a3 100%);
  transform: translateY(-1px);
}
.text-gradient {
  background: linear-gradient(135deg, #0ea5e9 0%, #4f46e5 60%, #6366f1 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>

  <!-- DSP Gallery Slider -->
  <?php include 'includes/gallery.php'; ?>
  
  <!-- Enhanced Features Section -->
  <section class="py-16 bg-gradient-to-b from-sky-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-sky-200 rounded-full text-sky-700 text-sm font-medium mb-4 shadow-sm">
          <span class="w-2 h-2 bg-sky-500 rounded-full animate-pulse"></span>
          사무용에 최적화된 전문 서비스
        </div>
        <h2 class="text-3xl font-bold text-slate-800 mb-4">
          세련되고 효율적인 사무용 인쇄 솔루션
        </h2>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-4xl mx-auto">
        <div class="text-center group">
          <div class="w-16 h-16 mx-auto mb-4 bg-white border border-sky-200 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-105 transition-transform shadow-sm">
            ✓
          </div>
          <h3 class="font-semibold text-slate-800 mb-2">실시간 견적</h3>
          <p class="text-sm text-slate-600">즉시 확인하는 정확한 가격</p>
        </div>
        <div class="text-center group">
          <div class="w-16 h-16 mx-auto mb-4 bg-white border border-indigo-200 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-105 transition-transform shadow-sm">
            📁
          </div>
          <h3 class="font-semibold text-slate-800 mb-2">간편 업로드</h3>
          <p class="text-sm text-slate-600">드래그로 쉽게 파일 등록</p>
        </div>
        <div class="text-center group">
          <div class="w-16 h-16 mx-auto mb-4 bg-white border border-purple-200 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-105 transition-transform shadow-sm">
            🎨
          </div>
          <h3 class="font-semibold text-slate-800 mb-2">디자인 서비스</h3>
          <p class="text-sm text-slate-600">전문가의 완성도 높은 디자인</p>
        </div>
        <div class="text-center group">
          <div class="w-16 h-16 mx-auto mb-4 bg-white border border-orange-200 rounded-2xl flex items-center justify-center text-2xl group-hover:scale-105 transition-transform shadow-sm">
            🚚
          </div>
          <h3 class="font-semibold text-slate-800 mb-2">신속 배송</h3>
          <p class="text-sm text-slate-600">빠른 제작과 안전한 배송</p>
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
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/sticker_new/index.php'">
          <div class="h-24 bg-gradient-to-br from-green-400 to-emerald-500 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">스티커</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">스티커</h3>
            <p class="text-slate-600 mb-4">브랜드 홍보와 제품 라벨링에 최적. 다양한 재질과 사이즈로 맞춤 제작</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Flyers -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/inserted/index.php'">
          <div class="h-24 bg-gradient-to-br from-blue-400 to-sky-500 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">전단지</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">전단지</h3>
            <p class="text-slate-600 mb-4">마케팅 캠페인과 행사 홍보용. 소량부터 대량까지 합리적인 가격</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Magnetic Stickers -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/msticker/index.php'">
          <div class="h-24 bg-gradient-to-br from-blue-500 to-indigo-600 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">자석스티커</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">자석스티커</h3>
            <p class="text-slate-600 mb-4">냉장고나 철제 표면에 부착 가능. 생활 밀착형 광고 매체</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Business Cards -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/NameCard/index.php'">
          <div class="h-24 bg-gradient-to-br from-purple-500 to-violet-600 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">명함</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">명함</h3>
            <p class="text-slate-600 mb-4">비즈니스 첫인상을 좌우하는 필수 아이템. 프리미엄 용지와 후가공</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Coupons -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/MerchandiseBond/index.php?page=MerchandiseBond'">
          <div class="h-24 bg-gradient-to-br from-pink-500 to-red-500 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">상품권</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">상품권</h3>
            <p class="text-slate-600 mb-4">고객 충성도 프로그램과 프로모션용. 넘버링과 미싱 옵션 제공</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Envelopes -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/envelope/index.php?page=envelope'">
          <div class="h-24 bg-gradient-to-br from-amber-500 to-orange-500 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">봉투</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">봉투</h3>
            <p class="text-slate-600 mb-4">공식 문서와 우편물 발송용. 다양한 크기와 용지 옵션</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Forms -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/NcrFlambeau/index.php?page=NcrFlambeau'">
          <div class="h-24 bg-gradient-to-br from-slate-500 to-gray-600 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">양식지</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">양식지</h3>
            <p class="text-slate-600 mb-4">전표, 영수증, 거래명세서 등. NCR 복사지와 넘버링 지원</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Catalogs -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/cadarok/index.php'">
          <div class="h-24 bg-gradient-to-br from-emerald-500 to-teal-600 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">카다록</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">카다록</h3>
            <p class="text-slate-600 mb-4">제품 소개 및 회사 홍보용 책자. 다양한 제본 방식 선택</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
          </div>
        </div>
        
        <!-- Posters -->
        <div class="card-shadow bg-white rounded-2xl overflow-hidden group cursor-pointer" onclick="window.location.href='/MlangPrintAuto/LittlePrint/index_compact.php?page=LittlePrint'">
          <div class="h-24 bg-gradient-to-br from-red-500 to-pink-600 relative overflow-hidden">
            <div class="absolute inset-0 flex items-center justify-center">
              <h2 class="text-white text-3xl font-bold tracking-wide">포스터</h2>
            </div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-slate-800 mb-2">포스터</h3>
            <p class="text-slate-600 mb-4">행사, 전시, 인테리어용 대형 인쇄물. 고화질 출력으로 선명한 품질</p>
            <div class="text-sky-600 font-semibold flex items-center gap-2">
              견적받기 <span class="group-hover:translate-x-1 transition">→</span>
            </div>
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
            <li><a href="/MlangPrintAuto/sticker_new/index.php" class="hover:text-white transition">스티커</a></li>
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
            <?php if ($is_logged_in): ?>
              <li><span class="text-slate-400"><?php echo htmlspecialchars($user_name); ?>님</span></li>
            <?php else: ?>
              <li><button onclick="showLoginModal()" class="hover:text-white transition text-left">로그인</button></li>
            <?php endif; ?>
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
    
    // DSP Hero Slider is handled by external JS file
    
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
    
    // Lazy loading for images
    if ('IntersectionObserver' in window) {
      const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src || img.src;
            img.classList.remove('opacity-0');
            imageObserver.unobserve(img);
          }
        });
      });
      
      document.querySelectorAll('img[loading="lazy"]').forEach(img => {
        imageObserver.observe(img);
      });
    }
  </script>

  <!-- DSP Gallery Layout JavaScript -->
  <script src="/assets/js/layout.js"></script>
  
  <!-- Pure CSS Carousel - No JavaScript Logic -->
  <script>
    console.log('Pure CSS Carousel 활성화됨 - JavaScript 로직 없음');
  </script>

<?php
// 공통 푸터 포함
include 'includes/footer.php';
?>