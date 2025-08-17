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
  <link rel="stylesheet" href="../css/gallery-common.css">
  <style>
    .gallery {
      width: 580px;
      margin: 40px auto;
    }
  </style>
  <script src="../includes/js/GalleryLightbox.js"></script>
</head>
<body>

<div class="gallery">
  <div class="image-gallery-section">
    <h4>🖼️ 이미지 갤러리</h4>
    <div id="galleryContainer"></div>
  </div>
</div>

<script>
  // PHP에서 가져온 이미지 데이터를 JavaScript 배열로 변환
  const phpImages = [
    <?php foreach ($recent as $idx => $img): ?>
      {
        id: <?= $idx ?>,
        title: '<?= htmlspecialchars($img) ?>',
        path: 'img/<?= htmlspecialchars($img) ?>',
        thumbnail: 'img/<?= htmlspecialchars($img) ?>'
      }<?= $idx < count($recent) - 1 ? ',' : '' ?>
    <?php endforeach; ?>
  ];

  // 갤러리 초기화
  document.addEventListener('DOMContentLoaded', function() {
    const gallery = new GalleryLightbox('galleryContainer', {
      productType: 'local_gallery',
      autoLoad: false,
      zoomEnabled: true
    });
    
    gallery.init();
    gallery.setImages(phpImages);
    
    // 로딩 상태 숨기기
    gallery.showLoading(false);
    
    console.log('로컬 갤러리 초기화 완료:', phpImages.length + '개 이미지');
  });
</script>

</body>
</html>
