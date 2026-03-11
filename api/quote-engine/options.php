<?php
/**
 * 견적엔진 API — 옵션 조회
 * GET /api/quote-engine/options.php
 *
 * 액션별 파라미터:
 *   ?action=products                          → 제품 목록
 *   ?action=dropdown&product=X&parent=0       → 최상위 드롭다운
 *   ?action=dropdown&product=X&parent=275     → 하위 드롭다운
 *   ?action=dropdown&product=X&parent=275&lookup=TreeNo → TreeNo 기준 조회
 *   ?action=quantities&product=X&style=Y&section=Z      → 수량 목록
 *   ?action=premium&product=X                 → 프리미엄 옵션
 */

header('Content-Type: application/json; charset=utf-8');
session_start();


if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../db.php';
mysqli_set_charset($db, 'utf8mb4');
require_once __DIR__ . '/../../includes/quote-engine/PriceCalculator.php';

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'GET만 허용됩니다']);
        exit;
    }

    $action = trim($_GET['action'] ?? '');
    $calculator = new QE_PriceCalculator($db);

    switch ($action) {
        // ────────────────────────────────────────────────
        // 제품 목록
        // ────────────────────────────────────────────────
        case 'products':
            $list = $calculator->getProductList();
            echo json_encode(['success' => true, 'data' => $list], JSON_UNESCAPED_UNICODE);
            break;

        // ────────────────────────────────────────────────
        // 캐스케이딩 드롭다운 옵션
        // ────────────────────────────────────────────────
        case 'dropdown':
            $product  = trim($_GET['product'] ?? '');
            $parentId = (int)($_GET['parent'] ?? 0);
            $lookup   = trim($_GET['lookup'] ?? 'BigNo');

            if ($product === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'product 파라미터가 필요합니다']);
                exit;
            }

            // lookup 화이트리스트 (BigNo 또는 no만 허용, 그 외 → BigNo)
            if (!in_array($lookup, ['BigNo', 'no', 'TreeNo'], true)) {
                $lookup = 'BigNo';
            }

            // TreeNo → no 컬럼으로 매핑 (transactioncate의 TreeNo는 no 기준 조회)
            $dbLookup = ($lookup === 'TreeNo') ? 'no' : $lookup;

            $options = $calculator->getOptions($product, $parentId, $dbLookup);
            echo json_encode(['success' => true, 'data' => $options], JSON_UNESCAPED_UNICODE);
            break;

        // ────────────────────────────────────────────────
        // 수량 목록
        // ────────────────────────────────────────────────
        case 'quantities':
            $product = trim($_GET['product'] ?? '');
            if ($product === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'product 파라미터가 필요합니다']);
                exit;
            }

            $filters = [];
            if (!empty($_GET['style']))        $filters['style']      = $_GET['style'];
            if (!empty($_GET['section']))       $filters['Section']    = $_GET['section'];
            if (!empty($_GET['Section']))       $filters['Section']    = $_GET['Section'];
            if (!empty($_GET['tree_select']))   $filters['TreeSelect'] = $_GET['tree_select'];
            if (!empty($_GET['TreeSelect']))    $filters['TreeSelect'] = $_GET['TreeSelect'];
            if (!empty($_GET['po_type']))       $filters['POtype']     = $_GET['po_type'];
            if (!empty($_GET['POtype']))        $filters['POtype']     = $_GET['POtype'];

            $quantities = $calculator->getQuantities($product, $filters);
            echo json_encode(['success' => true, 'data' => $quantities], JSON_UNESCAPED_UNICODE);
            break;

        // ────────────────────────────────────────────────
        // 프리미엄 옵션
        // ────────────────────────────────────────────────
        case 'premium':
            $product = trim($_GET['product'] ?? '');
            if ($product === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'product 파라미터가 필요합니다']);
                exit;
            }

            $premiumOptions = $calculator->getPremiumOptions($product);
            echo json_encode(['success' => true, 'data' => $premiumOptions], JSON_UNESCAPED_UNICODE);
            break;

        // ────────────────────────────────────────────────
        // 미지원 액션
        // ────────────────────────────────────────────────
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error'   => '지원하지 않는 action입니다. (products, dropdown, quantities, premium)',
            ], JSON_UNESCAPED_UNICODE);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
