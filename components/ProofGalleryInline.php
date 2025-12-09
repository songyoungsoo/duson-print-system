<?php
/**
 * ProofGalleryInline.php
 * - 페이지 내 임베드용: 좌측 썸네일 4개, 우측 큰 미리보기(450x300), 호버 135% 줌
 * - 데이터 소스: mlangorder_printauto/upload/{order_no}/{image}
 * - 카테고리별 최신 업로드 4건 표시 (기본: 명함)
 *
 * 사용 예)
 *   $category = "명함"; // 전단지, 스티커, 명함, 상품권, 봉투, 양식지, 카다로그, 소량인쇄(포스터), 종이자석
 *   include __DIR__ . "/components/ProofGalleryInline.php";
 */

// 아키텍처 가이드 설정 사용
$productConfigs = include __DIR__ . '/product_config.php';
$currentProduct = $galleryConfig['category'] ?? 'namecard';
$config = $productConfigs[$currentProduct] ?? $productConfigs['namecard'];
$category = $config['title'];

$HomeDir = $_SERVER['DOCUMENT_ROOT'] . "/";
require_once $HomeDir . "db.php";

// 데이터베이스 연결 변수 확인 및 설정
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

/** 설정 */
$UPLOAD_DIR_ABS  = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload"; // 실제 파일 경로
$UPLOAD_DIR_URL  = "/mlangorder_printauto/upload"; // 브라우저 접근 경로
$THUMB_LIMIT     = 4;   // 최신 4개
$IMAGE_EXTS      = ['jpg','jpeg','png','webp','gif'];

/**
 * 프로젝트별(주문번호별) 업로드 폴더에서 대표 이미지 1개를 찾는다 (가장 최근 파일)
 */
function find_first_image_in_order($absOrderPath, $ex = ['jpg','jpeg','png','webp','gif']) {
  if (!is_dir($absOrderPath)) return null;
  $files = [];
  $dir = new DirectoryIterator($absOrderPath);
  foreach ($dir as $f) {
    if ($f->isDot() || !$f->isFile()) continue;
    $ext = strtolower(pathinfo($f->getFilename(), PATHINFO_EXTENSION));
    if (in_array($ext, $ex)) {
      $files[] = [$f->getFilename(), $f->getMTime()];
    }
  }
  if (empty($files)) return null;
  // 최신순
  usort($files, function($a,$b){ return $b[1] <=> $a[1]; });
  return $files[0][0]; // 최신 1개 파일명
}

/**
 * 카테고리별 최신 작업 n건의 대표 이미지를 만든다.
 * 실제 파일이 있는 완성된 주문만 가져옴 (WindowSian.php 방식)
 */
function fetch_latest_orders_by_category($connect, $category, $limit = 4) {
  if (!$connect) return [];
  
  // 카테고리별 Type 매핑
  $type_mapping = [
    '명함' => 'NameCard',
    '전단지' => '전단지', 
    '스티커' => '스티커',
    '상품권' => '금액쿠폰',
    '봉투' => 'envelope',
    '양식지' => 'NCR 양식지',
    '카탈로그' => 'cadarok',
    '포스터' => 'LittlePrint'
  ];
  
  $db_type = $type_mapping[$category] ?? $category;
  
  // 완성된 주문 중에서 ThingCate가 있는 것만 (오래된 주문 우선)
  $sql = "SELECT No, ThingCate FROM mlangorder_printauto 
          WHERE OrderStyle IN ('6', '7', '8') 
          AND ThingCate IS NOT NULL 
          AND ThingCate != ''
          AND Type = ?
          ORDER BY 
            CASE 
                WHEN No < 70000 THEN 1
                WHEN No < 80000 THEN 2  
                WHEN No < 82000 THEN 3
                ELSE 4
            END,
            No DESC 
          LIMIT ?";
  
  $stmt = mysqli_prepare($connect, $sql);
  
  if ($stmt) {
    $search_limit = $limit * 3; // 변수로 저장해서 참조 전달
    mysqli_stmt_bind_param($stmt, "si", $db_type, $search_limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $valid_orders = [];
    $found = 0;
    
    while (($row = mysqli_fetch_assoc($result)) && $found < $limit) {
      // 행 데이터가 유효한지 확인
      if (!$row || !is_array($row) || !isset($row['No']) || !isset($row['ThingCate'])) {
        continue;
      }
      
      $order_no = $row['No'];
      $thing_cate = $row['ThingCate'];
      
      // 필수 데이터가 있는지 확인
      if (empty($order_no) || empty($thing_cate)) {
        continue;
      }
      
      // 실제 파일이 존재하는지 확인
      $file_path = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload/{$order_no}/{$thing_cate}";
      if (file_exists($file_path)) {
        $valid_orders[] = $order_no;
        $found++;
      }
    }
    
    mysqli_stmt_close($stmt);
    return $valid_orders;
  } else {
    // 대체 쿼리 (prepare 실패 시)
    $alt_sql = "SELECT No, ThingCate FROM mlangorder_printauto 
                WHERE OrderStyle IN ('6', '7', '8') 
                AND ThingCate IS NOT NULL 
                AND ThingCate != ''
                AND Type = '" . mysqli_real_escape_string($connect, $db_type) . "'
                ORDER BY No DESC 
                LIMIT " . ($limit * 3);
    $result = mysqli_query($connect, $alt_sql);
    
    $valid_orders = [];
    $found = 0;
    
    if ($result) {
      while (($row = mysqli_fetch_assoc($result)) && $found < $limit) {
        // 행 데이터가 유효한지 확인
        if (!$row || !is_array($row) || !isset($row['No']) || !isset($row['ThingCate'])) {
          continue;
        }
        
        $order_no = $row['No'];
        $thing_cate = $row['ThingCate'];
        
        // 필수 데이터가 있는지 확인
        if (empty($order_no) || empty($thing_cate)) {
          continue;
        }
        
        // 실제 파일이 존재하는지 확인
        $file_path = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload/{$order_no}/{$thing_cate}";
        if (file_exists($file_path)) {
          $valid_orders[] = $order_no;
          $found++;
        }
      }
    }
    return $valid_orders;
  }
}

/** 데이터 가져오기 */
if (isset($connect) && $connect) {
    $orderNos = fetch_latest_orders_by_category($connect, $category, $THUMB_LIMIT);
} else {
    $orderNos = [];
}

// 파일 경로 매핑 - ThingCate 기반으로 직접 이미지 가져오기
$items = []; // [ ['order_no'=>..., 'img_abs'=>..., 'img_url'=>...], ... ]

// 실제 파일이 있는 주문들의 ThingCate 정보 가져오기
if (!empty($orderNos)) {
  $order_nos_str = implode(',', array_map('intval', $orderNos));
  $detail_sql = "SELECT No, ThingCate FROM mlangorder_printauto 
                 WHERE No IN ($order_nos_str) 
                 AND ThingCate IS NOT NULL 
                 AND ThingCate != ''
                 ORDER BY FIELD(No, " . implode(',', array_map('intval', $orderNos)) . ")";
  
  $detail_result = mysqli_query($connect, $detail_sql);
  if ($detail_result) {
    while ($detail_row = mysqli_fetch_assoc($detail_result)) {
      // 행 데이터가 유효한지 확인
      if (!$detail_row || !is_array($detail_row) || !isset($detail_row['No']) || !isset($detail_row['ThingCate'])) {
        continue;
      }
      
      $ono = $detail_row['No'];
      $thing_cate = $detail_row['ThingCate'];
      
      // 필수 데이터가 있는지 확인
      if (empty($ono) || empty($thing_cate)) {
        continue;
      }
      
      // 실제 파일 경로 확인
      $img_abs = $UPLOAD_DIR_ABS . "/" . $ono . "/" . $thing_cate;
      if (file_exists($img_abs)) {
        $items[] = [
          'order_no' => $ono,
          'img_abs'  => $img_abs,
          'img_url'  => $UPLOAD_DIR_URL . "/" . $ono . "/" . rawurlencode($thing_cate),
        ];
      }
    }
  }
}

// 기본 이미지가 없을 경우 placeholder
if (empty($items)) {
  // 샘플 데이터 또는 placeholder 추가
  $items = [
    ['order_no' => 0, 'img_url' => 'https://via.placeholder.com/300x200?text=샘플+준비중'],
    ['order_no' => 0, 'img_url' => 'https://via.placeholder.com/300x200?text=샘플+준비중'],
    ['order_no' => 0, 'img_url' => 'https://via.placeholder.com/300x200?text=샘플+준비중'],
    ['order_no' => 0, 'img_url' => 'https://via.placeholder.com/300x200?text=샘플+준비중'],
  ];
}

$firstLarge = $items[0]['img_url'] ?? "https://via.placeholder.com/900x600?text=No+Image";
?>

<style>
/* 카드 타이틀 통일 스타일 */
.gallery-card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 700;
  color: #333;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-radius: 12px 12px 0 0;
  margin: -16px -16px 12px -16px; /* 카드 패딩 오버라이드 + 하단 12px 간격 */
  text-align: left;
}

.proof-gallery {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

.proof-large {
  width: 100%; 
  height: 300px; /* 큰 미리보기 영역 */
}

/* 포스터 방식: backgroundImage 기반 호버 확대 */
.lightbox-viewer {
  width: 100%; 
  height: 100%;
  border-radius: 16px; 
  overflow: hidden; 
  border: 1px solid #ddd; 
  background: #f9f9f9;
  position: relative;
  background-size: contain;
  background-repeat: no-repeat;
  background-position: 50% 50%;
  cursor: zoom-in;
}

.proof-thumbs {
  display: grid; 
  grid-template-columns: repeat(4, 1fr); 
  gap: 10px;
  width: 100%;
}

.proof-thumbs .thumb {
  width: 100%; 
  height: 80px; 
  border-radius: 12px; 
  overflow: hidden; 
  border: 2px solid #ddd; 
  cursor: pointer;
  background: #f7f7f7;
  display: flex; 
  align-items: center; 
  justify-content: center;
  transition: border-color 0.3s ease, transform 0.2s ease;
}

.proof-thumbs .thumb:hover {
  border-color: #007bff;
  transform: translateY(-2px);
}

.proof-thumbs .thumb.active {
  border-color: #007bff;
  box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}

.proof-thumbs .thumb img {
  max-width: 100%; 
  max-height: 100%; 
  object-fit: contain; 
  display: block;
}

/* 통일된 Primary 버튼 스타일 */
.btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  height: 48px;
  padding: 0 20px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(90deg, #22d3ee, #6366f1);
  color: #fff;
  font-size: 16px;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: transform 0.2s ease, filter 0.2s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  filter: brightness(0.95);
}

.btn-primary:active {
  transform: translateY(0);
}

/* 반응형 스타일 */
@media (max-width: 640px) {
  .gallery-card-title {
    padding: 12px 16px;
    font-size: 16px;
    gap: 6px;
  }
  
  .btn-primary {
    height: 44px;
    font-size: 15px;
    padding: 0 16px;
  }
  
  .proof-thumbs {
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }
  
  .proof-thumbs .thumb {
    height: 70px;
  }
}
</style>

<div class="proof-gallery" role="region" aria-label="<?= htmlspecialchars($category) ?> 샘플 갤러리">
  <!-- 큰 이미지가 상단에 (포스터 방식 backgroundImage) -->
  <div class="proof-large">
    <div class="lightbox-viewer" id="posterZoomBox" role="img" aria-label="선택된 <?= htmlspecialchars($category) ?> 샘플 이미지">
    </div>
  </div>

  <!-- 썸네일이 하단에 (4개 나란히) -->
  <div class="proof-thumbs" role="list" aria-label="<?= htmlspecialchars($category) ?> 썸네일 목록">
    <?php foreach ($items as $idx => $it): ?>
      <div class="thumb <?= $idx === 0 ? 'active' : '' ?>" 
           data-img="<?= htmlspecialchars($it['img_url']) ?>" 
           data-index="<?= $idx ?>"
           role="listitem"
           tabindex="0"
           aria-label="<?= htmlspecialchars($category) ?> 샘플 <?= $idx + 1 ?>"
           aria-selected="<?= $idx === 0 ? 'true' : 'false' ?>">
        <img src="<?= htmlspecialchars($it['img_url']) ?>" alt="<?= htmlspecialchars($category) ?> 샘플 <?= $idx + 1 ?>">
      </div>
    <?php endforeach; ?>
  </div>

  <!-- 더 많은 샘플 보기 버튼 -->
  <button 
    class="btn-primary btn-more-gallery"
    onclick="openUnifiedModal('<?= htmlspecialchars($config['title']) ?>', '<?= htmlspecialchars($config['icon']) ?>')"
    aria-label="더 많은 샘플 보기"
  >
    <span aria-hidden="true"><?= $config['icon'] ?></span> 더 많은 샘플 보기
  </button>
</div>

<script>
// 카테고리별 아이콘 반환 함수
function getIconForCategory(category) {
    const icons = {
        '전단지': '📄',
        '스티커': '🏷️',
        '명함': '💳',
        '봉투': '✉️',
        '포스터': '🖼️',
        '카탈로그': '📚',
        '상품권': '🎁',
        '자석스티커': '🧲',
        '양식지': '📋'
    };
    return icons[category] || '📂';
}

(function(){
  // 포스터 방식 호버링 변수들
  var currentX = 50, currentY = 50, currentSize = 100;
  var targetX = 50, targetY = 50, targetSize = 100;
  var originalBackgroundSize = 'contain';
  var animationId = null;

  // 썸네일 선택 함수 (포스터 방식으로 수정)
  function selectThumb(th) {
    // 모든 썸네일에서 active 클래스 및 aria-selected 제거
    document.querySelectorAll('.proof-thumbs .thumb').forEach(function(item){
      item.classList.remove('active');
      item.setAttribute('aria-selected', 'false');
    });
    
    // 선택한 썸네일에 active 클래스 및 aria-selected 추가
    th.classList.add('active');
    th.setAttribute('aria-selected', 'true');
    
    // 포스터 방식: backgroundImage로 교체
    var url = th.getAttribute('data-img');
    var zoomBox = document.getElementById('posterZoomBox');
    zoomBox.style.backgroundImage = 'url("' + url + '")';
    zoomBox.style.backgroundSize = 'contain';
    zoomBox.style.backgroundPosition = '50% 50%';
    
    // 변수 초기화
    currentX = targetX = 50;
    currentY = targetY = 50;
    currentSize = targetSize = 100;
    originalBackgroundSize = 'contain';
    
    console.log('🖼️ 이미지 교체:', url);
  }
  
  // 썸네일 클릭 및 키보드 이벤트 처리
  document.querySelectorAll('.proof-thumbs .thumb').forEach(function(th){
    // 클릭 이벤트
    th.addEventListener('click', function(){
      selectThumb(th);
    });
    
    // 키보드 이벤트 (Enter, Space)
    th.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        selectThumb(th);
      }
    });
  });

  // 포스터 방식 호버링 시스템 초기화
  function initializePosterHover() {
    var zoomBox = document.getElementById('posterZoomBox');
    if (!zoomBox) return;
    
    // 첫 번째 이미지로 초기화
    var firstThumb = document.querySelector('.proof-thumbs .thumb.active');
    if (firstThumb) {
      var firstUrl = firstThumb.getAttribute('data-img');
      zoomBox.style.backgroundImage = 'url("' + firstUrl + '")';
      zoomBox.style.backgroundSize = 'contain';
      zoomBox.style.backgroundPosition = '50% 50%';
    }
    
    // 마우스 움직임 추적 (포스터 방식 동일)
    zoomBox.addEventListener('mousemove', function(e) {
      var rect = zoomBox.getBoundingClientRect();
      var x = ((e.clientX - rect.left) / rect.width) * 100;
      var y = ((e.clientY - rect.top) / rect.height) * 100;
      
      targetX = x;
      targetY = y;
      targetSize = 135; // 1.35배 확대
    });
    
    // 마우스 벗어날 때 초기화 (핵심!)
    zoomBox.addEventListener('mouseleave', function() {
      targetX = 50;
      targetY = 50;
      targetSize = 100;
      console.log('👋 포스터 방식 호버 초기화');
    });
    
    // 부드러운 애니메이션 시작
    startSmoothAnimation();
    
    console.log('✅ 포스터 방식 호버링 초기화 완료');
  }
  
  // 포스터 방식 부드러운 애니메이션
  function startSmoothAnimation() {
    if (animationId) {
      cancelAnimationFrame(animationId);
    }
    
    function animate() {
      var zoomBox = document.getElementById('posterZoomBox');
      if (!zoomBox) return;
      
      // 포스터와 동일한 부드러운 보간 (0.08 lerp 계수)
      currentX += (targetX - currentX) * 0.08;
      currentY += (targetY - currentY) * 0.08;
      currentSize += (targetSize - currentSize) * 0.08;
      
      zoomBox.style.backgroundPosition = currentX + '% ' + currentY + '%';
      
      if (currentSize > 100.1) {
        zoomBox.style.backgroundSize = currentSize + '%';
      } else {
        zoomBox.style.backgroundSize = originalBackgroundSize;
      }
      
      animationId = requestAnimationFrame(animate);
    }
    
    animate();
  }
  
  // DOM 로드 후 초기화
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePosterHover);
  } else {
    initializePosterHover();
  }
})();

function openProofPopup(category){
  // 팝업 갤러리 오픈 (페이지네이션 포함)
  var w = 980, h = 720;
  var left = (window.screen.width - w)/2;
  var top  = (window.screen.height - h)/2;
  window.open(
    "/popup/proof_gallery.php?cate=" + encodeURIComponent(category),
    "proof_popup",
    "width="+w+",height="+h+",left="+left+",top="+top+",resizable=yes,scrollbars=yes"
  );
}
</script>