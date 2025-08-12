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
  <title>부드러운 확대 갤러리</title>
  <style>
    .gallery {
      width: 580px;
      margin: 40px auto;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    /* 확대 박스: CSS 트랜지션 대신 will-change + JS 애니메이션으로 부드럽게 */
    .zoom-box {
      width: 580px;
      height: 580px;
      border: 1px solid #ccc;
      background-repeat: no-repeat;
      background-position: center center;
      background-size: 100%;
      will-change: background-position, background-size;
      cursor: crosshair;
    }
    /* 썸네일 */
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
  <!-- 확대 박스: 초기 배경 이미지 -->
  <div class="zoom-box" id="zoomBox"
       style="background-image: url('img/<?= htmlspecialchars($firstImage) ?>');">
  </div>

  <!-- 썸네일 -->
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

  // 썸네일 클릭 → 확대 박스의 이미지 변경
  thumbs.forEach(thumb => {
    thumb.addEventListener('click', () => {
      thumbs.forEach(t => t.classList.remove('active'));
      thumb.classList.add('active');
      zoomBox.style.backgroundImage = `url('${thumb.dataset.src}')`;
      // 타겟 상태 초기화
      targetSize = 100;
      targetX = 50; targetY = 50;
    });
  });

  // 애니메이션용 상태 변수
  let targetX = 50, targetY = 50;
  let currentX = 50, currentY = 50;
  let targetSize = 100, currentSize = 100;

  // mousemove → 목표 포지션 & 사이즈 설정
  zoomBox.addEventListener('mousemove', e => {
    const { width, height, left, top } = zoomBox.getBoundingClientRect();
    const xPct = (e.clientX - left) / width  * 100;
    const yPct = (e.clientY - top)  / height * 100;
    targetX = xPct;
    targetY = yPct;
    targetSize = 200; // 2배
  });

  // mouseleave → 원상태로 복원 목표값 설정
  zoomBox.addEventListener('mouseleave', () => {
    targetX = 50;
    targetY = 50;
    targetSize = 100;
  });

  // 부드러운 인터폴레이션 애니메이션 루프
  function animate() {
    // lerp 계수: 0.15 → 부드러운 추적
    currentX += (targetX - currentX) * 0.15;
    currentY += (targetY - currentY) * 0.15;
    currentSize += (targetSize - currentSize) * 0.15;

    zoomBox.style.backgroundPosition = `${currentX}% ${currentY}%`;
    zoomBox.style.backgroundSize     = `${currentSize}%`;

    requestAnimationFrame(animate);
  }
  animate();
</script>

</body>
</html>
