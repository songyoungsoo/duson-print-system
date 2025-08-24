<?php
/**
 * 품목별 갤러리 팝업 (단일 이미지 확대보기)
 * 특정 주문번호의 교정 시안을 크게 표시
 */

session_start();
require_once "../db.php";

// 데이터베이스 연결 확인
if (!isset($connect) && isset($db)) {
    $connect = $db;
}

// UTF-8 설정
if ($connect) {
    mysqli_set_charset($connect, "utf8");
}

$order_no = intval($_GET['no'] ?? 0);

if (!$order_no) {
    die("주문번호가 필요합니다.");
}

// 해당 주문 정보 가져오기
$sql = "SELECT No, ThingCate, Type, Item, name, standard, OrderName, Date, cont
        FROM MlangOrder_PrintAuto 
        WHERE No = ? AND OrderStyle = '8'";

$stmt = mysqli_prepare($connect, $sql);
$order_info = null;
$image_path = '';

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $order_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order_info = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

if (!$order_info) {
    die("주문 정보를 찾을 수 없습니다.");
}

// 이미지 파일 경로 찾기
$upload_base = $_SERVER['DOCUMENT_ROOT'] . "/MlangOrder_PrintAuto/upload";
$thing_cate = $order_info['ThingCate'];

if (!empty($thing_cate)) {
    // 새로운 구조 확인
    $new_path = "{$upload_base}/{$order_no}/{$thing_cate}";
    if (file_exists($new_path)) {
        $image_path = "/MlangOrder_PrintAuto/upload/{$order_no}/{$thing_cate}";
    } else {
        // 이전 구조 확인
        $date_dirs = glob($upload_base . "/0*", GLOB_ONLYDIR);
        foreach ($date_dirs as $date_dir) {
            $dir_name = basename($date_dir);
            $old_path = "{$upload_base}/{$dir_name}/{$order_no}/{$thing_cate}";
            if (file_exists($old_path)) {
                $image_path = "/MlangOrder_PrintAuto/upload/{$dir_name}/{$order_no}/{$thing_cate}";
                break;
            }
        }
    }
}

// 고객명 마스킹
$masked_name = '';
if (!empty($order_info['name'])) {
    $name_length = mb_strlen($order_info['name'], 'UTF-8');
    if ($name_length > 1) {
        $masked_name = mb_substr($order_info['name'], 0, 1, 'UTF-8') . str_repeat('*', $name_length - 1);
    } else {
        $masked_name = $order_info['name'];
    }
}

// 같은 카테고리의 다른 이미지들 (네비게이션용)
$nav_items = [];
$nav_sql = "SELECT No, ThingCate FROM MlangOrder_PrintAuto 
            WHERE OrderStyle = '8' AND Type = ? AND ThingCate != '' 
            ORDER BY No DESC LIMIT 20";

if ($nav_stmt = mysqli_prepare($connect, $nav_sql)) {
    $type = $order_info['Type'] ?? $order_info['Item'];
    mysqli_stmt_bind_param($nav_stmt, "s", $type);
    mysqli_stmt_execute($nav_stmt);
    $nav_result = mysqli_stmt_get_result($nav_stmt);
    while ($nav_row = mysqli_fetch_assoc($nav_result)) {
        $nav_items[] = $nav_row['No'];
    }
    mysqli_stmt_close($nav_stmt);
}

// 현재 이미지의 인덱스 찾기
$current_index = array_search($order_no, $nav_items);
$prev_no = ($current_index > 0) ? $nav_items[$current_index - 1] : null;
$next_no = ($current_index !== false && $current_index < count($nav_items) - 1) ? $nav_items[$current_index + 1] : null;
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문번호 <?= $order_no ?> - 작업물 보기</title>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Noto Sans KR', system-ui, -apple-system, sans-serif;
        background: #000;
        color: #fff;
        overflow: hidden;
    }

    .popup-container {
        position: relative;
        width: 100vw;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .popup-header {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        background: linear-gradient(180deg, rgba(0,0,0,0.8) 0%, transparent 100%);
        padding: 20px;
        z-index: 10;
    }

    .popup-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .popup-info {
        font-size: 14px;
        opacity: 0.8;
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .main-image {
        max-width: 90vw;
        max-height: 90vh;
        object-fit: contain;
        border-radius: 8px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        cursor: zoom-in;
        transition: transform 0.3s ease;
    }

    .main-image.zoomed {
        cursor: zoom-out;
        transform: scale(1.5);
    }

    .no-image {
        width: 600px;
        height: 400px;
        background: #333;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        color: #999;
        font-size: 18px;
    }

    .popup-controls {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 10;
    }

    .nav-buttons {
        display: flex;
        gap: 12px;
    }

    .nav-btn, .action-btn {
        background: rgba(255,255,255,0.2);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        padding: 10px 16px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    .nav-btn:hover, .action-btn:hover {
        background: rgba(255,255,255,0.3);
    }

    .nav-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .close-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(0,0,0,0.6);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        z-index: 15;
        transition: background 0.2s ease;
    }

    .close-btn:hover {
        background: rgba(0,0,0,0.8);
    }

    .loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #fff;
        font-size: 18px;
    }

    /* 키보드 단축키 안내 */
    .shortcuts {
        position: absolute;
        top: 80px;
        right: 20px;
        background: rgba(0,0,0,0.7);
        padding: 12px;
        border-radius: 8px;
        font-size: 12px;
        opacity: 0.7;
        z-index: 5;
    }

    .shortcuts div {
        margin-bottom: 4px;
    }

    @media (max-width: 768px) {
        .popup-header {
            padding: 15px;
        }
        
        .popup-title {
            font-size: 16px;
        }
        
        .popup-info {
            font-size: 12px;
            gap: 10px;
        }
        
        .main-image {
            max-width: 95vw;
            max-height: 80vh;
        }
        
        .shortcuts {
            display: none;
        }
        
        .nav-buttons {
            gap: 8px;
        }
        
        .nav-btn, .action-btn {
            padding: 8px 12px;
            font-size: 12px;
        }
    }
    </style>
</head>
<body>
    <div class="popup-container">
        <!-- 닫기 버튼 -->
        <button class="close-btn" onclick="window.close()" title="닫기 (ESC)">✕</button>

        <!-- 헤더 정보 -->
        <div class="popup-header">
            <div class="popup-title">
                주문번호 <?= htmlspecialchars($order_no) ?> - <?= htmlspecialchars($order_info['Type'] ?? $order_info['Item'] ?? '') ?>
            </div>
            <div class="popup-info">
                <span>고객: <?= htmlspecialchars($masked_name) ?></span>
                <?php if (!empty($order_info['standard'])): ?>
                    <span>규격: <?= htmlspecialchars($order_info['standard']) ?></span>
                <?php endif; ?>
                <?php if (!empty($order_info['Date'])): ?>
                    <span>일시: <?= htmlspecialchars($order_info['Date']) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 키보드 단축키 안내 -->
        <div class="shortcuts">
            <div><strong>단축키</strong></div>
            <div>ESC: 닫기</div>
            <div>←→: 이전/다음</div>
            <div>Space: 확대/축소</div>
        </div>

        <!-- 메인 이미지 -->
        <?php if (!empty($image_path)): ?>
            <img id="mainImage" 
                 class="main-image" 
                 src="<?= htmlspecialchars($image_path) ?>" 
                 alt="주문번호 <?= $order_no ?> 작업물"
                 onclick="toggleZoom()">
        <?php else: ?>
            <div class="no-image">
                <span>이미지를 찾을 수 없습니다</span>
            </div>
        <?php endif; ?>

        <!-- 하단 컨트롤 -->
        <div class="popup-controls">
            <div class="nav-buttons">
                <button class="nav-btn" 
                        onclick="navigate(<?= $prev_no ?: 'null' ?>)" 
                        <?= $prev_no ? '' : 'disabled' ?>>
                    ◀ 이전
                </button>
                <button class="nav-btn" 
                        onclick="navigate(<?= $next_no ?: 'null' ?>)" 
                        <?= $next_no ? '' : 'disabled' ?>>
                    다음 ▶
                </button>
            </div>
            
            <div class="nav-buttons">
                <button class="action-btn" onclick="openDetail()">
                    📄 상세보기
                </button>
                <button class="action-btn" onclick="downloadImage()">
                    💾 다운로드
                </button>
            </div>
        </div>
    </div>

    <script>
    let isZoomed = false;

    function toggleZoom() {
        const img = document.getElementById('mainImage');
        if (!img) return;
        
        isZoomed = !isZoomed;
        img.classList.toggle('zoomed', isZoomed);
    }

    function navigate(orderNo) {
        if (!orderNo) return;
        window.location.href = `?no=${orderNo}`;
    }

    function openDetail() {
        window.open('/MlangOrder_PrintAuto/WindowSian.php?mode=OrderView&no=<?= $order_no ?>', 
                    'detail_<?= $order_no ?>',
                    'width=1000,height=800,scrollbars=yes');
    }

    function downloadImage() {
        const img = document.getElementById('mainImage');
        if (!img || !img.src) return;
        
        const link = document.createElement('a');
        link.href = img.src;
        link.download = `주문번호_<?= $order_no ?>_작업물.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // 키보드 단축키
    document.addEventListener('keydown', function(e) {
        switch(e.key) {
            case 'Escape':
                window.close();
                break;
            case 'ArrowLeft':
                e.preventDefault();
                <?php if ($prev_no): ?>
                    navigate(<?= $prev_no ?>);
                <?php endif; ?>
                break;
            case 'ArrowRight':
                e.preventDefault();
                <?php if ($next_no): ?>
                    navigate(<?= $next_no ?>);
                <?php endif; ?>
                break;
            case ' ':
                e.preventDefault();
                toggleZoom();
                break;
        }
    });

    // 이미지 로딩 처리
    window.addEventListener('load', function() {
        const img = document.getElementById('mainImage');
        if (img) {
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.3s ease';
            
            if (img.complete) {
                img.style.opacity = '1';
            } else {
                img.onload = function() {
                    img.style.opacity = '1';
                };
            }
        }
    });
    </script>
</body>
</html>