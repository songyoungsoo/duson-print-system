<?php
/**
 * 통합 제품 관리 페이지
 *
 * 9개 제품을 ProductConfig 기반으로 통합 관리합니다.
 * - 제품 선택 → 해당 제품 관리 UI 표시
 * - 리스트, 상세, 수정, 삭제 통합
 *
 * @author Claude Sonnet 4.5
 * @date 2025-12-25
 */

require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/includes/ProductConfig.php';

// 관리자 인증 필수
requireAdminAuth();

// 파라미터 받기
$product = $_GET['product'] ?? '';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 20;
$offset = ($page - 1) * $limit;

// 제품 선택 안 되어 있으면 선택 화면 표시
if (empty($product)) {
    include __DIR__ . '/views/product_selector.php';
    exit;
}

// 제품 유효성 검증
if (!ProductConfig::isValidProduct($product)) {
    die('잘못된 제품 코드입니다.');
}

$config = ProductConfig::getConfig($product);
$table = $config['table'];

// 액션 처리
switch ($action) {
    case 'list':
        include __DIR__ . '/views/product_list.php';
        break;

    case 'view':
        if (empty($id)) {
            header("Location: product_manager.php?product=$product");
            exit;
        }
        include __DIR__ . '/views/product_view.php';
        break;

    case 'edit':
        if (empty($id)) {
            header("Location: product_manager.php?product=$product");
            exit;
        }
        include __DIR__ . '/views/product_edit.php';
        break;

    case 'delete':
        if (!empty($id)) {
            $stmt = mysqli_prepare($db, "DELETE FROM {$table} WHERE no = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = '삭제되었습니다.';
            } else {
                $_SESSION['error'] = '삭제 실패: ' . mysqli_error($db);
            }
            mysqli_stmt_close($stmt);
        }
        header("Location: product_manager.php?product=$product");
        exit;

    case 'save':
        // POST 데이터로 저장/수정
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            include __DIR__ . '/handlers/product_save.php';
        }
        break;

    default:
        include __DIR__ . '/views/product_list.php';
}
?>