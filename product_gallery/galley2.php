<?php
// 1) img 폴더에서 최근 4개 이미지 파일 가져오기
$dir = __DIR__ . '/img';
$files = array_filter(scandir($dir), function($f){
    return preg_match('/\.(jpe?g|png|gif)$/i', $f);
});
usort($files, function($a, $b) use ($dir){
    return filemtime("$dir/$b") - filemtime("$dir/$a");
});
$recent = array_slice($files, 0, 4);
$firstImage = $recent[0] ?? '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>이미지 확대 갤러리</title>
  <style>
    .gallery {
      width: 580px;
      margin: 40px auto;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    /* 2) 확대 박스 */
    .zoom-box {
      width: 580px;
      height: 580px;
      border: 1px solid #ccc;
      background-repeat: no-repeat;
      background-position: center center;
      background-size: contain;              /* 전체가 보여지도록 */
      transition: background-size .2s ease, background-position .2s ease;
      cursor: crosshair;
    }
    /* 3) 썸네일 */
    .thumbnails {
      margin-top: 16px;
      display: flex;
      gap: 8px;
    }
    .thumbnails img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      cursor: pointer;
      border: 2px solid transparent;
      transition: border .2s;
    }
    .thumbnails img.active,
    .thumbnails img:hover {
      border-color: #0078D7;
    }
  </style>
</head>
<body>

<div class="gallery">
  <!-- 확대 박스: 최초 이미지 설정 -->
  <div class="zoom-box" id="zoomBox"
       style="background-image: url('img/<?= htmlspecialchars($firstImage) ?>');">
  </div>

  <!-- 썸네일 네비 -->
  <div class="thumbnails">
    <?php foreach ($recent as $idx => $img): ?>
      <img
        src="img/<?= htmlspecialchars($img) ?>"
        data-src="img/<?= htmlspecialchars($img) ?>"
        class="<?= $idx === 0 ? 'active' : '' ?>"
        alt="thumb<?= $idx ?>">
    <?php endforeach; ?>
  </div>
</div>

<script>
  const zoomBox = document.getElementById('zoomBox');
  const thumbs  = document.querySelectorAll('.thumbnails img');

  // 썸네일 클릭 → 확대 박스 배경 변경
  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      thumbs.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      zoomBox.style.backgroundImage  = `url('${thumb.dataset.src}')`;
      // 원상 복구
      zoomBox.style.backgroundSize    = 'contain';
      zoomBox.style.backgroundPosition= 'center center';
    });
  });

  // 마우스 움직임 → 2배 확대 & 위치 따라다님
  zoomBox.addEventListener('mousemove', e => {
    const { width, height, left, top } = zoomBox.getBoundingClientRect();
    const x = e.clientX - left;
    const y = e.clientY - top;
    const xPct = (x / width)  * 100;
    const yPct = (y / height) * 100;

    zoomBox.style.backgroundSize     = '200%';
    zoomBox.style.backgroundPosition = `${xPct}% ${yPct}%`;
  });

  // 마우스 벗어나면 원래대로
  zoomBox.addEventListener('mouseleave', () => {
    zoomBox.style.backgroundSize     = 'contain';
    zoomBox.style.backgroundPosition = 'center center';
  });
</script>

</body>
</html>
