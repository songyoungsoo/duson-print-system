<?php
/**
 * proof_gallery.php
 * - 카테고리별 교정 이미지 팝업 갤러리
 * - 페이지네이션 포함
 * - 썸네일 클릭 시 상단 라이트박스 뷰어
 */
header("Content-Type: text/html; charset=utf-8");

$HomeDir = $_SERVER['DOCUMENT_ROOT'] . "/";
require_once $HomeDir . "db.php";

// 데이터베이스 연결 변수 확인 및 설정
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

$UPLOAD_DIR_ABS = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload";
$UPLOAD_DIR_URL = "/MlangOrder_PrintAuto/upload";
$IMAGE_EXTS     = ['jpg','jpeg','png','webp','gif'];

$cate   = $_GET['cate'] ?? '명함';
$page   = max(1, intval($_GET['page'] ?? 1));
$per    = 24; // 한 페이지 24 주문(=24개 대표이미지)

// 데이터베이스 연결 확인
if (!$connect) {
    die("데이터베이스 연결 실패");
}

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

$db_type = $type_mapping[$cate] ?? $cate;
$offset = ($page - 1) * $per;

// API와 동일한 조건으로 주문 개수 구하기 (성공한 방식)
$count_sql = "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto 
              WHERE OrderStyle > '0'
              AND ThingCate IS NOT NULL 
              AND ThingCate != ''
              AND LENGTH(ThingCate) > 3
              AND ThingCate NOT LIKE '%test%'
              AND ThingCate NOT LIKE '%테스트%'
              AND date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
              AND Type = ?";
$count_stmt = mysqli_prepare($connect, $count_sql);

// 디버깅: 쿼리와 파라미터 확인
if (isset($_GET['debug'])) {
    echo "<!-- DEBUG: Category = $cate, DB Type = $db_type -->\n";
    echo "<!-- DEBUG: Count SQL = " . str_replace('?', "'$db_type'", $count_sql) . " -->\n";
}

if ($count_stmt) {
    mysqli_stmt_bind_param($count_stmt, "s", $db_type);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    $total = $count_row ? $count_row['total'] : 0;
    mysqli_stmt_close($count_stmt);
    
    // 디버깅: 결과 확인
    if (isset($_GET['debug'])) {
        echo "<!-- DEBUG: Total found = $total -->\n";
    }
} else {
    // API와 동일한 조건의 대체 쿼리 (성공한 방식)
    $escaped_type = mysqli_real_escape_string($connect, $db_type);
    $count_result = mysqli_query($connect, "SELECT COUNT(*) as total FROM MlangOrder_PrintAuto 
                                           WHERE OrderStyle > '0'
                                           AND ThingCate IS NOT NULL 
                                           AND ThingCate != ''
                                           AND LENGTH(ThingCate) > 3
                                           AND ThingCate NOT LIKE '%test%'
                                           AND ThingCate NOT LIKE '%테스트%'
                                           AND date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
                                           AND Type = '" . $escaped_type . "'");
    if ($count_result) {
        $count_row = mysqli_fetch_assoc($count_result);
        $total = $count_row ? $count_row['total'] : 0;
    } else {
        $total = 0;
    }
}

// API와 동일한 조건으로 데이터 가져오기 (성공한 방식)
$sql = "SELECT No, ThingCate FROM MlangOrder_PrintAuto 
        WHERE OrderStyle > '0'
        AND ThingCate IS NOT NULL 
        AND ThingCate != ''
        AND LENGTH(ThingCate) > 3
        AND ThingCate NOT LIKE '%test%'
        AND ThingCate NOT LIKE '%테스트%'
        AND date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
        AND Type = ?
        ORDER BY 
          CASE 
              WHEN No < 70000 THEN 1
              WHEN No < 80000 THEN 2  
              WHEN No < 82000 THEN 3
              ELSE 4
          END,
          No DESC 
        LIMIT ?, ?";
$stmt = mysqli_prepare($connect, $sql);

$orderNos = [];
if ($stmt) {
    $search_limit = $per * 2; // 변수로 저장해서 참조 전달
    mysqli_stmt_bind_param($stmt, "sii", $db_type, $offset, $search_limit);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    $found = 0;
    while (($row = mysqli_fetch_assoc($res)) && $found < $per) {
        // 행 데이터가 유효한지 확인
        if (!$row || !is_array($row) || !isset($row['No']) || !isset($row['ThingCate'])) {
            continue;
        }
        
        $order_no = $row['No'];
        $thing_cate = $row['ThingCate'];
        
        // 실제 파일이 존재하는지 확인
        $file_path = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
        if (file_exists($file_path)) {
            $orderNos[] = $order_no;
            $found++;
        }
    }
    mysqli_stmt_close($stmt);
} else {
    // API와 동일한 조건의 대체 쿼리 (성공한 방식)
    $escaped_type = mysqli_real_escape_string($connect, $db_type);
    $alt_sql = "SELECT No, ThingCate FROM MlangOrder_PrintAuto 
                WHERE OrderStyle > '0'
                AND ThingCate IS NOT NULL 
                AND ThingCate != ''
                AND LENGTH(ThingCate) > 3
                AND ThingCate NOT LIKE '%test%'
                AND ThingCate NOT LIKE '%테스트%'
                AND date >= DATE_SUB(NOW(), INTERVAL 2 YEAR)
                AND Type = '" . $escaped_type . "'
                ORDER BY No DESC 
                LIMIT {$offset}, " . ($per * 2);
    $res = mysqli_query($connect, $alt_sql);
    if ($res) {
        $found = 0;
        while (($row = mysqli_fetch_assoc($res)) && $found < $per) {
            // 행 데이터가 유효한지 확인
            if (!$row || !is_array($row) || !isset($row['No']) || !isset($row['ThingCate'])) {
                continue;
            }
            
            $order_no = $row['No'];
            $thing_cate = $row['ThingCate'];
            
            // 실제 파일이 존재하는지 확인
            $file_path = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
            if (file_exists($file_path)) {
                $orderNos[] = $order_no;
                $found++;
            }
        }
    }
}

$pages = max(1, ceil($total / $per));

function get_image_from_thingcate($orderNo, $absBase, $urlBase, $connect){
  // 주문번호의 ThingCate 필드에서 이미지 파일명 가져오기
  $sql = "SELECT ThingCate FROM MlangOrder_PrintAuto WHERE No = ? AND ThingCate IS NOT NULL AND ThingCate != ''";
  $stmt = mysqli_prepare($connect, $sql);
  
  if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $orderNo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if ($row && isset($row['ThingCate'])) {
      $thing_cate = $row['ThingCate'];
      $file_path = $absBase . "/" . $orderNo . "/" . $thing_cate;
      
      if (file_exists($file_path)) {
        return $urlBase . "/" . $orderNo . "/" . rawurlencode($thing_cate);
      }
    }
  }
  
  return null;
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($cate) ?> 샘플 갤러리</title>
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
    <div>📁 <?= htmlspecialchars($cate) ?> 샘플 갤러리</div>
    <div style="opacity:.9;font-weight:500"><?= number_format($total) ?>건</div>
  </div>

  <?php if (empty($orderNos)): ?>
    <div class="no-data">
      <h3>아직 샘플이 준비되지 않았습니다</h3>
      <p>곧 업데이트 예정입니다.</p>
    </div>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($orderNos as $ono):
        $img = get_image_from_thingcate($ono, $UPLOAD_DIR_ABS, $UPLOAD_DIR_URL, $connect);
        if (!$img) {
          $img = 'https://via.placeholder.com/300x200?text=이미지+준비중';
        }
      ?>
        <div class="card" data-img="<?= htmlspecialchars($img) ?>">
          <img src="<?= htmlspecialchars($img) ?>" alt="sample <?= (int)$ono ?>">
        </div>
      <?php endforeach; ?>
    </div>

    <div class="pager">
      <?php
      $base = "/popup/proof_gallery.php?cate=" . urlencode($cate) . "&page=";
      
      // 이전 버튼
      if ($page > 1) {
        echo '<a href="'.$base.($page-1).'">◀ 이전</a>';
      }
      
      // 페이지 번호
      $window = 7; // 표시 범위
      $start = max(1, $page - floor($window/2));
      $end   = min($pages, $start + $window - 1);
      if ($end - $start + 1 < $window) {
        $start = max(1, $end - $window + 1);
      }
      
      for ($p = $start; $p <= $end; $p++) {
        if ($p == $page) {
          echo '<span class="current">'.$p.'</span>';
        } else {
          echo '<a href="'.$base.$p.'">'.$p.'</a>';
        }
      }
      
      // 다음 버튼
      if ($page < $pages) {
        echo '<a href="'.$base.($page+1).'">다음 ▶</a>';
      }
      ?>
    </div>
  <?php endif; ?>

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