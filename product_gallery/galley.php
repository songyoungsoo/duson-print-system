<?php
// 1) PHP: img 폴더에서 최근 4개 이미지 가져오기
$dir = __DIR__ . '/img';
$files = array_filter(scandir($dir), function($f){
    return preg_match('/\.(jpe?g|png|gif)$/i', $f);
});
usort($files, function($a, $b) use ($dir){
    return filemtime("$dir/$b") - filemtime("$dir/$a");
});
$recent = array_slice($files, 0, 4);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>이미지 확대 갤러리</title>
  <style>
    .gallery { display: flex; flex-direction: column; align-items: center; }
    .main-container {
      width: 580px; height: 580px;
      overflow: hidden; position: relative;
      border: 1px solid #ccc;
    }
    .main-container img {
      width: 100%; height: 100%;
      object-fit: cover;
      transition: transform .2s ease;
      transform-origin: center center;
    }
    .thumbnails {
      margin-top: 16px;
      display: flex; gap: 8px;
    }
    .thumbnails img {
      width: 100px; height: 100px;
      object-fit: cover;
      cursor: pointer;
      border: 2px solid transparent;
      transition: border .2s;
    }
    .thumbnails img.active,
    .thumbnails img:hover {
      border-color: #0078d7;
    }
  </style>
</head>
<body>

<div class="gallery">
  <!-- 메인 이미지 -->
  <div class="main-container" id="zoomContainer">
    <img id="mainImage" src="img/<?= htmlspecialchars($recent[0]) ?>" alt="">
  </div>

  <!-- 썸네일 네비 -->
  <div class="thumbnails">
    <?php foreach($recent as $idx => $file): ?>
      <img 
        src="img/<?= htmlspecialchars($file) ?>" 
        data-full="img/<?= htmlspecialchars($file) ?>" 
        class="<?= $idx===0 ? 'active' : '' ?>"
      >
    <?php endforeach; ?>
  </div>
</div>

<script>
  const mainImg = document.getElementById('mainImage');
  const container = document.getElementById('zoomContainer');
  const thumbs = document.querySelectorAll('.thumbnails img');

  // 2) 썸네일 클릭 → 메인 변경
  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      // active 표시
      thumbs.forEach(t=>t.classList.remove('active'));
      thumb.classList.add('active');
      // 메인 변경
      mainImg.src = thumb.dataset.full;
    });
  });

  // 3) 마우스 이동에 따라 2배 확대 & 중심 이동
  container.addEventListener('mousemove', e => {
    const { width, height, left, top } = container.getBoundingClientRect();
    const x = e.clientX - left;
    const y = e.clientY - top;
    const xPct = x / width  * 100;
    const yPct = y / height * 100;
    mainImg.style.transformOrigin = `${xPct}% ${yPct}%`;
    mainImg.style.transform = 'scale(2)';
  });
  container.addEventListener('mouseleave', () => {
    mainImg.style.transformOrigin = 'center center';
    mainImg.style.transform = 'scale(1)';
  });
</script>

</body>
</html>
