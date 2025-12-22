<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>명함 샘플 갤러리</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body { 
  margin: 0; 
  font-family: 'Noto Sans KR', system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
  background: #f5f5f5;
}

.header {
  padding: 14px 18px; 
  font-weight: 700; 
  font-size: 18px; 
  color: #fff;
  background: linear-gradient(90deg, #22d3ee, #6366f1);
  display: flex; 
  align-items: center; 
  justify-content: space-between;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.grid {
  padding: 14px; 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); 
  gap: 12px;
}

@media (min-width: 768px) {
  .grid {
    grid-template-columns: repeat(6, 1fr);
  }
}

.card {
  border: 1px solid #e5e7eb; 
  border-radius: 12px; 
  overflow: hidden; 
  background: #fff; 
  cursor: pointer;
  display: flex; 
  align-items: center; 
  justify-content: center; 
  height: 140px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card img { 
  max-width: 100%; 
  max-height: 100%; 
  object-fit: contain; 
  display: block;
}

.pager { 
  padding: 10px 14px; 
  display: flex; 
  gap: 6px; 
  justify-content: center; 
  align-items: center;
  flex-wrap: wrap;
}

.pager a, .pager span {
  border: 1px solid #cbd5e1; 
  border-radius: 8px; 
  padding: 6px 10px; 
  text-decoration: none; 
  color: #334155; 
  background: #fff;
  transition: background 0.2s ease;
}

.pager a:hover {
  background: #f1f5f9;
}

.pager .current { 
  background: #334155; 
  color: #fff; 
  border-color: #334155;
}

.viewer {
  position: fixed; 
  inset: 0; 
  background: rgba(0,0,0,.85); 
  display: none; 
  align-items: center; 
  justify-content: center; 
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.viewer .box {
  width: 86vw; 
  height: 86vh; 
  max-width: 1200px; 
  max-height: 800px;
  background: #111; 
  border-radius: 16px; 
  overflow: hidden; 
  position: relative; 
  border: 1px solid #374151;
  box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}

.viewer img {
  width: 100%; 
  height: 100%; 
  object-fit: contain; 
  transition: transform .25s ease; 
  transform-origin: center center;
}

.viewer .close {
  position: absolute; 
  top: 12px; 
  right: 12px; 
  color: #fff; 
  background: rgba(0,0,0,0.7); 
  padding: 8px 14px; 
  border-radius: 8px; 
  cursor: pointer;
  border: 1px solid rgba(255,255,255,0.2);
  font-weight: 600;
  transition: background 0.2s ease;
}

.viewer .close:hover {
  background: rgba(0,0,0,0.9);
}

.no-data {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.no-data h3 {
  font-size: 24px;
  margin-bottom: 10px;
}
</style>
</head>
<body>
  <div class="header">
    <div>📁 명함 샘플 갤러리</div>
    <div style="opacity:.9;font-weight:500">57건</div>
  </div>

      <div class="grid">
              <div class="card" data-img="/ImgFolder/namecard/gallery/1048715.jpg">
          <img src="/ImgFolder/namecard/gallery/1048715.jpg" alt="1048715.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/15626555.jpg">
          <img src="/ImgFolder/namecard/gallery/15626555.jpg" alt="15626555.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16918119.jpg">
          <img src="/ImgFolder/namecard/gallery/16918119.jpg" alt="16918119.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16918120.jpg">
          <img src="/ImgFolder/namecard/gallery/16918120.jpg" alt="16918120.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16928981.jpg">
          <img src="/ImgFolder/namecard/gallery/16928981.jpg" alt="16928981.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16928982.jpg">
          <img src="/ImgFolder/namecard/gallery/16928982.jpg" alt="16928982.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16934160%20%EB%B3%B5%EC%82%AC.jpg">
          <img src="/ImgFolder/namecard/gallery/16934160%20%EB%B3%B5%EC%82%AC.jpg" alt="16934160 복사.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16934161.jpg">
          <img src="/ImgFolder/namecard/gallery/16934161.jpg" alt="16934161.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16934162%20%EB%B3%B5%EC%82%AC.jpg">
          <img src="/ImgFolder/namecard/gallery/16934162%20%EB%B3%B5%EC%82%AC.jpg" alt="16934162 복사.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16941694.jpg">
          <img src="/ImgFolder/namecard/gallery/16941694.jpg" alt="16941694.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16946839%20%281%29.jpg">
          <img src="/ImgFolder/namecard/gallery/16946839%20%281%29.jpg" alt="16946839 (1).jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16946839.jpg">
          <img src="/ImgFolder/namecard/gallery/16946839.jpg" alt="16946839.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16952599.jpg">
          <img src="/ImgFolder/namecard/gallery/16952599.jpg" alt="16952599.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16952602.jpg">
          <img src="/ImgFolder/namecard/gallery/16952602.jpg" alt="16952602.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16953595.jpg">
          <img src="/ImgFolder/namecard/gallery/16953595.jpg" alt="16953595.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16953598.jpg">
          <img src="/ImgFolder/namecard/gallery/16953598.jpg" alt="16953598.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16960656.jpg">
          <img src="/ImgFolder/namecard/gallery/16960656.jpg" alt="16960656.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16960657.jpg">
          <img src="/ImgFolder/namecard/gallery/16960657.jpg" alt="16960657.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16960659.jpg">
          <img src="/ImgFolder/namecard/gallery/16960659.jpg" alt="16960659.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16960660%20222.jpg">
          <img src="/ImgFolder/namecard/gallery/16960660%20222.jpg" alt="16960660 222.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16960660.jpg">
          <img src="/ImgFolder/namecard/gallery/16960660.jpg" alt="16960660.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16972761.jpg">
          <img src="/ImgFolder/namecard/gallery/16972761.jpg" alt="16972761.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16972762.jpg">
          <img src="/ImgFolder/namecard/gallery/16972762.jpg" alt="16972762.jpg">
        </div>
              <div class="card" data-img="/ImgFolder/namecard/gallery/16972763.jpg">
          <img src="/ImgFolder/namecard/gallery/16972763.jpg" alt="16972763.jpg">
        </div>
          </div>

    <div class="pager">
      <span class="current">1</span><a href="/popup/proof_gallery.php?cate=%EB%AA%85%ED%95%A8&page=2">2</a><a href="/popup/proof_gallery.php?cate=%EB%AA%85%ED%95%A8&page=3">3</a><a href="/popup/proof_gallery.php?cate=%EB%AA%85%ED%95%A8&page=2">다음 ▶</a>    </div>
  
  <!-- 라이트박스 뷰어 -->
  <div class="viewer" id="viewer" onclick="closeViewer(event)">
    <div class="box">
      <div class="close" onclick="closeViewer(event)">✕ 닫기</div>
      <img id="viewerImg" src="" alt="">
    </div>
  </div>

<script>
document.querySelectorAll('.card').forEach(function(el){
  el.addEventListener('click', function(){
    var url = el.getAttribute('data-img');
    var v = document.getElementById('viewer');
    var img = document.getElementById('viewerImg');
    img.style.transformOrigin = "center center";
    img.style.transform = "none";
    img.src = url;
    v.style.display = 'flex';
  });
});

function closeViewer(e){
  if (e && e.target && (e.target.id === 'viewer' || e.target.classList.contains('close'))) {
    document.getElementById('viewer').style.display = 'none';
  }
}

// ESC 키로 닫기
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.getElementById('viewer').style.display = 'none';
  }
});
</script>
</body>
</html>