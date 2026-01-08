<?php
/**
 * E2E 테스트 검증용 API
 * 견적서-계산기 격리 검증을 위한 DB 조회
 *
 * Security: Read-only queries, no user input in SQL
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../../db.php';

$action = $_GET['action'] ?? '';
$sessionId = session_id();

$response = ['success' => false, 'data' => null];

try {
    switch ($action) {
        case 'shop_temp_count':
            // 장바구니(shop_temp) 품목 수
            $query = "SELECT COUNT(*) as count FROM shop_temp WHERE session_id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "s", $sessionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['data'] = ['count' => intval($row['count'])];
            mysqli_stmt_close($stmt);
            break;

        case 'quotation_temp_count':
            // 계산기(quotation_temp) 품목 수
            $query = "SELECT COUNT(*) as count FROM quotation_temp WHERE session_id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "s", $sessionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['data'] = ['count' => intval($row['count'])];
            mysqli_stmt_close($stmt);
            break;

        case 'shop_temp_latest':
            // 장바구니 최신 품목 상세
            $query = "SELECT product_type, st_price, st_price_vat, price_supply, price_vat, data_version
                      FROM shop_temp
                      WHERE session_id = ?
                      ORDER BY no DESC LIMIT 1";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "s", $sessionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['data'] = $row ?: [];
            mysqli_stmt_close($stmt);
            break;

        case 'quotation_temp_latest':
            // 계산기 최신 품목 상세
            $query = "SELECT product_type, st_price, st_price_vat, price_supply, price_vat, data_version
                      FROM quotation_temp
                      WHERE session_id = ?
                      ORDER BY regdate DESC LIMIT 1";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, "s", $sessionId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['data'] = $row ?: [];
            mysqli_stmt_close($stmt);
            break;

        case 'quote_items_latest':
            // 최근 견적서의 품목들
            $query = "SELECT qi.product_type, qi.source_type, qi.data_version,
                             qi.spec_type, qi.quantity_display,
                             qi.price_supply_phase3, qi.price_vat,
                             qi.supply_price, qi.total_price
                      FROM quote_items qi
                      WHERE qi.quote_id = (SELECT id FROM quotations ORDER BY created_at DESC LIMIT 1)
                      ORDER BY qi.item_no";
            $result = mysqli_query($db, $query);
            $items = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
            $response['success'] = true;
            $response['data'] = ['items' => $items, 'count' => count($items)];
            break;

        case 'latest_quote_total':
            // 최근 견적서의 총액
            $query = "SELECT supply_total, vat_total, grand_total
                      FROM quotations
                      ORDER BY created_at DESC LIMIT 1";
            $result = mysqli_query($db, $query);
            $row = mysqli_fetch_assoc($result);
            $response['success'] = true;
            $response['data'] = $row ?: [];
            break;

        case 'phase3_validation':
            // Phase 3 데이터 무결성 검증
            $query = "SELECT
                        qi.product_type,
                        qi.source_type,
                        qi.data_version,
                        qi.price_supply_phase3,
                        qi.price_vat,
                        CASE
                            WHEN qi.data_version = 2 AND qi.price_vat > 0 THEN 'VALID'
                            WHEN qi.data_version = 1 THEN 'LEGACY'
                            ELSE 'INVALID'
                        END as integrity_status
                      FROM quote_items qi
                      WHERE qi.quote_id = (SELECT id FROM quotations ORDER BY created_at DESC LIMIT 1)
                      ORDER BY qi.item_no";
            $result = mysqli_query($db, $query);
            $items = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $items[] = $row;
            }
            $response['success'] = true;
            $response['data'] = [
                'items' => $items,
                'has_invalid' => in_array('INVALID', array_column($items, 'integrity_status'))
            ];
            break;

        case 'clear_test_data':
            // 테스트 데이터 정리 (현재 세션만)
            mysqli_query($db, "DELETE FROM shop_temp WHERE session_id = '$sessionId'");
            mysqli_query($db, "DELETE FROM quotation_temp WHERE session_id = '$sessionId'");
            $response['success'] = true;
            $response['data'] = ['message' => 'Test data cleared for current session'];
            break;

        default:
            $response['success'] = false;
            $response['error'] = 'Invalid action';
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
mysqli_close($db);
?>
