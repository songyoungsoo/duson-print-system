<?php
/**
 * proof_gallery.php
 * - 카테고리별 교정 이미지 팝업 갤러리
 * - 페이지네이션 포함
 * - 썸네일 클릭 시 상단 라이트박스 뷰어
 */
header("Content-Type: text/html; charset=utf-8");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$HomeDir = $_SERVER['DOCUMENT_ROOT'] . "/";
require_once $HomeDir . "db.php";

// 관리자 인라인 편집 지원
$authFile = $_SERVER['DOCUMENT_ROOT'] . '/admin/includes/admin_auth.php';
if (file_exists($authFile)) { require_once $authFile; }
$isAdmin = function_exists('isAdminLoggedIn') && isAdminLoggedIn();

// 카테고리 → 제품코드 매핑 (관리자 API용)
$categoryToProduct = [
    '명함' => 'namecard', '스티커' => 'sticker', '봉투' => 'envelope',
    '전단지' => 'inserted', '포스터' => 'littleprint', '카탈로그' => 'cadarok',
    '상품권' => 'merchandisebond', '자석스티커' => 'msticker', '양식지' => 'ncrflambeau',
];

// 데이터베이스 연결 변수 확인 및 설정
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

$UPLOAD_DIR_ABS = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload";
$UPLOAD_DIR_URL = "/mlangorder_printauto/upload";
$IMAGE_EXTS     = ['jpg','jpeg','png','webp','gif'];

$cate   = $_GET['cate'] ?? '명함';
$page   = max(1, intval($_GET['page'] ?? 1));
$per    = 24; // 한 페이지 24개 이미지

// 데이터베이스 연결 확인
if (!$connect) {
    die("데이터베이스 연결 실패");
}

// ============================================
// 듀얼 소스: 안전갤러리 폴더 + 2022-2024 고객 주문 이미지
// 🔒 개인정보 보호: 명함, 봉투, 양식지는 갤러리 이미지만 사용
// ✅ 2026-02-13: 대시보드 안전갤러리(/ImgFolder/samplegallery/)와 경로 통일
// ============================================
$gallery_folders = [
    '명함' => ['/ImgFolder/sample/namecard/', '/ImgFolder/samplegallery/namecard/'],
    '스티커' => ['/ImgFolder/sample/sticker_new/', '/ImgFolder/samplegallery/sticker_new/'],
    '봉투' => ['/ImgFolder/sample/envelope/', '/ImgFolder/samplegallery/envelope/'],
    '전단지' => ['/ImgFolder/sample/inserted/', '/ImgFolder/samplegallery/inserted/'],
    '포스터' => ['/ImgFolder/sample/littleprint/', '/ImgFolder/samplegallery/littleprint/'],
    '카탈로그' => ['/ImgFolder/sample/cadarok/', '/ImgFolder/samplegallery/cadarok/'],
    '상품권' => ['/ImgFolder/sample/merchandisebond/', '/ImgFolder/samplegallery/merchandisebond/'],
    '자석스티커' => ['/ImgFolder/sample/msticker/', '/ImgFolder/samplegallery/msticker/'],
    '양식지' => ['/ImgFolder/sample/ncrflambeau/', '/ImgFolder/samplegallery/ncrflambeau/'],
];

// 통합 이미지 배열
$all_images = [];

// ============================================
// 1단계: 갤러리 폴더 이미지 로드
// ============================================
if (isset($gallery_folders[$cate])) {
    $folders = $gallery_folders[$cate];

    foreach ($folders as $folder_path) {
        $gallery_path = $_SERVER['DOCUMENT_ROOT'] . $folder_path;
        $gallery_url = $folder_path;

        if (is_dir($gallery_path)) {
            // glob() + filemtime desc — 대시보드 갤러리 API와 동일한 방식
            $files = glob($gallery_path . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
            usort($files, function($a, $b) { return filemtime($b) - filemtime($a); });

            foreach ($files as $filepath) {
                $file = basename($filepath);
                $all_images[] = [
                    'type' => 'gallery',
                    'url' => $gallery_url . rawurlencode($file),
                    'filename' => $file
                ];
            }
        }
    }
}

// ============================================
// 2단계: DB에서 2022-2024 고객 주문 이미지 로드
// ============================================

// 🔒 개인정보 보호: 명함, 봉투, 양식지는 갤러리 이미지만 사용 (고객 파일 제외)
// 🚧 임시 제한 (2026-02-12): 스티커, 전단지도 DB 쿼리 비활성화 (갤러리 이미지만 사용)
$privacy_protected_categories = ['명함', '봉투', '양식지', '스티커', '전단지'];
$skip_db_query = in_array($cate, $privacy_protected_categories);

if ($skip_db_query) {
    // 개인정보 보호 카테고리: DB 쿼리 건너뜀
    if (isset($_GET['debug'])) {
        echo "<!-- DEBUG: Privacy protected category '{$cate}' - skipping DB query -->\n";
    }
} else {
    // 일반 카테고리: DB에서 고객 주문 이미지 로드

// 카테고리별 Type 매핑 (배열로 여러 타입 지원)
$type_mapping = [
    '명함' => ['NameCard'],
    '전단지' => ['전단지'],
    '스티커' => 'LIKE', // 스티커는 LIKE 검색 사용 (투명스티커, 유포지스티커 등 모든 변형 대응)
    '상품권' => ['쿠폰', '상품권', '금액쿠폰'],
    '봉투' => ['봉투', '소봉투', '대봉투', '자켓봉투', '자켓소봉투', '중봉투', '창봉투'], // 주요 봉투 타입
    '양식지' => ['NCR 양식지', '양식지', '거래명세서'],
    '카탈로그' => ['카다록', '카다로그', 'leaflet', 'cadarok'], // 카다록/리플렛 타입
    '포스터' => ['포스터', 'LittlePrint', 'littleprint', 'poster', 'Poster'], // 포스터 타입
    '자석스티커' => 'LIKE' // 자석스티커는 LIKE 검색 사용 (37가지 변형 대응)
];

$db_types = $type_mapping[$cate] ?? [$cate];

// 여러 타입을 지원하는 WHERE 조건 생성
$type_conditions = [];
$type_params = [];

// LIKE 검색이 필요한 카테고리 (자석스티커, 스티커)
if ($db_types === 'LIKE') {
    if ($cate === '자석스티커') {
        $type_where = "(Type LIKE '%자석%')";
    } elseif ($cate === '스티커') {
        // 스티커 + 스티카 모두 포함, 자석 제외 (자석스티커는 별도 카테고리)
        $type_where = "((Type LIKE '%스티커%' OR Type LIKE '%스티카%') AND Type NOT LIKE '%자석%')";
    } else {
        $type_where = "(Type LIKE '%{$cate}%')";
    }
} else {
    foreach ($db_types as $type) {
        $type_conditions[] = "Type = ?";
        $type_params[] = $type;
    }
    $type_where = '(' . implode(' OR ', $type_conditions) . ')';
}

// 2022-2024 고정 기간 필터 (3년간 데이터)
$date_filter = "date >= '2022-01-01' AND date <= '2024-12-31'";

// DB에서 모든 주문 이미지 가져오기 (페이지네이션 전)
$data_sql = "SELECT No, ThingCate FROM mlangorder_printauto
            WHERE OrderStyle > '0'
            AND ThingCate IS NOT NULL
            AND ThingCate != ''
            AND LENGTH(ThingCate) > 3
            AND ThingCate NOT LIKE '%test%'
            AND ThingCate NOT LIKE '%테스트%'
            AND " . $date_filter . "
            AND " . $type_where . "
            ORDER BY
              CASE
                  WHEN No < 70000 THEN 1
                  WHEN No < 80000 THEN 2
                  WHEN No < 82000 THEN 3
                  ELSE 4
              END,
              No DESC";

// 디버깅
if (isset($_GET['debug'])) {
    echo "<!-- DEBUG: Category = $cate, Type WHERE = $type_where -->\n";
}

// 데이터 쿼리 실행
if ($db_types === 'LIKE') {
    // LIKE 검색은 직접 실행
    $res = mysqli_query($connect, $data_sql);
} else {
    // Prepared statement 사용
    $data_stmt = mysqli_prepare($connect, $data_sql);
    if ($data_stmt) {
        if (!empty($type_params)) {
            $types = str_repeat('s', count($type_params));
            mysqli_stmt_bind_param($data_stmt, $types, ...$type_params);
        }
        mysqli_stmt_execute($data_stmt);
        $res = mysqli_stmt_get_result($data_stmt);
    } else {
        $res = false;
    }
}

if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        // 행 데이터가 유효한지 확인
        if (!$row || !is_array($row) || !isset($row['No']) || !isset($row['ThingCate'])) {
            continue;
        }

        $order_no = $row['No'];
        $thing_cate = $row['ThingCate'];

        // 실제 파일이 존재하는지 확인
        $file_path = $_SERVER['DOCUMENT_ROOT'] . "/mlangorder_printauto/upload/{$order_no}/{$thing_cate}";
        if (file_exists($file_path)) {
            $all_images[] = [
                'type' => 'order',
                'order_no' => $order_no,
                'url' => $UPLOAD_DIR_URL . "/" . $order_no . "/" . rawurlencode($thing_cate)
            ];
        }
    }

    // Statement 정리
    if (isset($data_stmt)) {
        mysqli_stmt_close($data_stmt);
    }
}

} // end of else block for privacy protection

// ============================================
// 3단계: 병합된 이미지 배열에 페이지네이션 적용
// ============================================
$total = count($all_images);
$pages = max(1, ceil($total / $per));

// 페이지네이션 적용
$offset = ($page - 1) * $per;
$paged_images = array_slice($all_images, $offset, $per);

if (isset($_GET['debug'])) {
    echo "<!-- DEBUG: Gallery images = " . count(array_filter($all_images, function($i) { return $i['type'] === 'gallery'; })) . " -->\n";
    echo "<!-- DEBUG: Order images = " . count(array_filter($all_images, function($i) { return $i['type'] === 'order'; })) . " -->\n";
    echo "<!-- DEBUG: Total = $total, Pages = $pages -->\n";
}

function get_image_from_thingcate($orderNo, $absBase, $urlBase, $connect){
  // 주문번호의 ThingCate 필드에서 이미지 파일명 가져오기
  $sql = "SELECT ThingCate FROM mlangorder_printauto WHERE No = ? AND ThingCate IS NOT NULL AND ThingCate != ''";
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
<title>샘플 갤러리</title>
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

.pager .dots {
  border: none;
  background: none;
  padding: 6px 4px;
  color: #94a3b8;
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

/* 관리자 인라인 편집 스타일 */
.admin-editable { position: relative; }
.admin-card-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center; gap: 6px;
  opacity: 0; transition: opacity .25s ease;
  border-radius: 12px;
}
.admin-editable:hover .admin-card-overlay { opacity: 1; }
.admin-card-btn {
  padding: 6px 14px; border: none; border-radius: 6px;
  font-size: 12px; font-weight: 600; cursor: pointer;
  transition: transform .15s ease, filter .15s ease;
  white-space: nowrap;
}
.admin-card-btn:hover { transform: scale(1.08); filter: brightness(1.1); }
.admin-card-replace { background: #2563eb; color: #fff; }
.admin-card-delete { background: #dc2626; color: #fff; }
.admin-order-badge {
  position: absolute; top: 4px; right: 4px;
  background: rgba(0,0,0,0.5); color: #fff;
  font-size: 10px; padding: 2px 6px; border-radius: 4px;
  pointer-events: none;
}
</style>
</head>
<body>
  <div class="header">
    <div>📁 <?= htmlspecialchars($cate) ?> 샘플 갤러리<?php if ($isAdmin): ?> <span style="background:rgba(255,255,255,0.25);padding:2px 8px;border-radius:4px;font-size:12px;margin-left:8px">🔧 관리자</span><?php endif; ?></div>
    <div style="display:flex;align-items:center;gap:10px">
      <?php if ($isAdmin): ?>
      <button onclick="adminUpload()" style="background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.4);color:#fff;padding:5px 12px;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;transition:background .2s" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">+ 이미지 추가</button>
      <?php endif; ?>
      <span style="opacity:.9;font-weight:500"><?= number_format($total) ?>건</span>
    </div>
  </div>

  <?php if (empty($paged_images)): ?>
    <div class="no-data">
      <h3>아직 샘플이 준비되지 않았습니다</h3>
      <p>곧 업데이트 예정입니다.</p>
    </div>
  <?php else: ?>
    <div class="grid">
      <?php
      // 통합 이미지 배열 렌더링 (갤러리 + 주문 이미지)
      foreach ($paged_images as $idx => $imgData):
        $img_url = $imgData['url'];
        $isGallery = ($imgData['type'] === 'gallery');

        // Alt 텍스트 생성
        if ($isGallery) {
          $alt = htmlspecialchars($imgData['filename'] ?? 'gallery_' . $idx);
        } else {
          $alt = 'order_' . htmlspecialchars($imgData['order_no'] ?? $idx);
        }

        // 관리자용 data 속성
        $adminAttrs = '';
        if ($isAdmin && $isGallery) {
          $fn = htmlspecialchars($imgData['filename'] ?? '');
          $src = (strpos($img_url, '/samplegallery/') !== false) ? 'safegallery' : 'sample';
          $adminAttrs = ' data-editable="true" data-filename="' . $fn . '" data-source="' . $src . '"';
        }
      ?>
        <div class="card<?php echo ($isAdmin && $isGallery) ? ' admin-editable' : ''; ?>" data-img="<?= htmlspecialchars($img_url) ?>"<?= $adminAttrs ?>>
          <img src="<?= htmlspecialchars($img_url) ?>" alt="<?= $alt ?>" loading="lazy" onerror="this.parentElement.style.display='none'">
          <?php if ($isAdmin && $isGallery): ?>
          <div class="admin-card-overlay">
            <button class="admin-card-btn admin-card-replace" title="이미지 교체" onclick="event.stopPropagation(); adminReplace(this.closest('.card'))">✏️ 교체</button>
            <button class="admin-card-btn admin-card-delete" title="이미지 삭제" onclick="event.stopPropagation(); adminDelete(this.closest('.card'))">✕ 삭제</button>
          </div>
          <?php elseif ($isAdmin && !$isGallery): ?>
          <div class="admin-order-badge">🔒 주문</div>
          <?php endif; ?>
        </div>
      <?php
      endforeach;
      ?>
    </div>

    <div class="pager">
      <?php
      $cate_encoded = urlencode($cate);
      $base = "/popup/proof_gallery.php?cate={$cate_encoded}&page=";
      $range = 2; // 현재 페이지 좌우 표시 개수

      // « 첫페이지
      if ($page > 1) {
        echo "<a href=\"{$base}1\">«</a>";
        echo "<a href=\"{$base}" . ($page - 1) . "\">‹</a>";
      }

      // 페이지 번호
      $start = max(1, $page - $range);
      $end = min($pages, $page + $range);

      if ($start > 1) {
        echo "<a href=\"{$base}1\">1</a>";
        if ($start > 2) echo "<span class=\"dots\">…</span>";
      }

      for ($p = $start; $p <= $end; $p++) {
        if ($p == $page) {
          echo "<span class=\"current\">{$p}</span>";
        } else {
          echo "<a href=\"{$base}{$p}\">{$p}</a>";
        }
      }

      if ($end < $pages) {
        if ($end < $pages - 1) echo "<span class=\"dots\">…</span>";
        echo "<a href=\"{$base}{$pages}\">{$pages}</a>";
      }

      // › 다음, » 마지막
      if ($page < $pages) {
        echo "<a href=\"{$base}" . ($page + 1) . "\">›</a>";
        echo "<a href=\"{$base}{$pages}\">»</a>";
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
  // 배경, X버튼, 이미지, box 클릭 모두 닫기
  document.getElementById('viewer').style.display = 'none';
}

// ESC 키로 닫기
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    document.getElementById('viewer').style.display = 'none';
  }
});
</script>

<?php if ($isAdmin): ?>
<script>
// 관리자 갤러리 편집 기능
var GALLERY_API = '/dashboard/api/gallery.php';
var ADMIN_PRODUCT = <?= json_encode($categoryToProduct[$cate] ?? '') ?>;

function adminReplace(card) {
  var filename = card.dataset.filename;
  var source = card.dataset.source;
  if (!filename || !source) return;
  var input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/jpeg,image/png,image/gif,image/webp';
  input.style.display = 'none';
  document.body.appendChild(input);
  input.addEventListener('change', function() {
    if (!input.files || !input.files[0]) { document.body.removeChild(input); return; }
    var fd = new FormData();
    fd.append('action', 'replace');
    fd.append('product', ADMIN_PRODUCT);
    fd.append('source', source);
    fd.append('old_filename', filename);
    fd.append('file', input.files[0]);
    fetch(GALLERY_API, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.success) { alert('교체 완료'); window.location.reload(); }
        else { alert(res.message || '교체 실패'); }
      })
      .catch(function() { alert('서버 오류'); });
    document.body.removeChild(input);
  });
  input.click();
}

function adminDelete(card) {
  var filename = card.dataset.filename;
  var source = card.dataset.source;
  if (!filename || !source) return;
  if (!confirm('이 이미지를 삭제하시겠습니까?\n\n파일명: ' + filename + '\n\n⚠️ 삭제하면 복구할 수 없습니다.')) return;
  var fd = new FormData();
  fd.append('action', 'delete');
  fd.append('product', ADMIN_PRODUCT);
  fd.append('filename', filename);
  fd.append('source', source);
  fetch(GALLERY_API, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(res) {
      if (res.success) { alert('삭제 완료'); window.location.reload(); }
      else { alert(res.message || '삭제 실패'); }
    })
    .catch(function() { alert('서버 오류'); });
}

function adminUpload() {
  var input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/jpeg,image/png,image/gif,image/webp';
  input.multiple = true;
  input.style.display = 'none';
  document.body.appendChild(input);
  input.addEventListener('change', function() {
    if (!input.files || input.files.length === 0) { document.body.removeChild(input); return; }
    var fd = new FormData();
    fd.append('action', 'upload');
    fd.append('product', ADMIN_PRODUCT);
    fd.append('target', 'sample');
    for (var i = 0; i < input.files.length; i++) { fd.append('files[]', input.files[i]); }
    fetch(GALLERY_API, { method: 'POST', body: fd })
      .then(function(r) { return r.json(); })
      .then(function(res) {
        if (res.success) { alert(res.message || '업로드 완료'); window.location.reload(); }
        else { alert(res.message || '업로드 실패'); }
      })
      .catch(function() { alert('서버 오류'); });
    document.body.removeChild(input);
  });
  input.click();
}
</script>
<?php endif; ?>
</body>
</html>
