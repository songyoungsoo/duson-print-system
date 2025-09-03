<?php
/**
 * 주문 내역 CSV 내보내기 API 엔드포인트
 * 경로: /api/orders/export.php
 */

session_start();
include "../../db.php";
include "../../includes/functions.php";

// 로그인 체크
if (!isset($_SESSION['user_id'])) {
    header('Location: /member/login.php');
    exit;
}

$user_name = $_SESSION['user_name'] ?? '';

// 필터 파라미터 (GET 요청에서)
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$product = $_GET['product'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

try {
    // 기본 쿼리 (orders.php와 동일한 로직)
    $where_conditions = ["customer_name = ?"];
    $params = [$user_name];
    $param_types = "s";
    
    // 검색 조건 추가
    if (!empty($search)) {
        $where_conditions[] = "(order_no LIKE ? OR product_name LIKE ? OR recv_name LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
        $param_types .= "sss";
    }
    
    if (!empty($status)) {
        $where_conditions[] = "status = ?";
        $params[] = $status;
        $param_types .= "s";
    }
    
    if (!empty($product)) {
        $where_conditions[] = "product_code LIKE ?";
        $params[] = "%$product%";
        $param_types .= "s";
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(created_at) >= ?";
        $params[] = $date_from;
        $param_types .= "s";
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(created_at) <= ?";
        $params[] = $date_to;
        $param_types .= "s";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // 전체 주문 데이터 조회 (페이지네이션 없이)
    $query = "SELECT 
        order_no as '주문번호',
        product_name as '상품명',
        options_summary as '옵션',
        qty as '수량',
        unit_price as '단가',
        total_price as '총금액',
        status as '상태',
        DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as '주문일시',
        customer_name as '주문자',
        recv_name as '수취인',
        recv_phone as '수취인전화',
        recv_addr as '배송주소',
        memo as '요청사항'
    FROM MlangOrder_PrintAuto 
    WHERE $where_clause 
    ORDER BY created_at DESC";
    
    $stmt = mysqli_prepare($db, $query);
    if ($stmt && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $orders = [];
    }
    
    // CSV 파일명 생성
    $filename = "주문내역_" . $user_name . "_" . date('Y-m-d_H-i-s') . ".csv";
    
    // CSV 헤더 설정
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    // BOM 추가 (엑셀에서 한글 깨짐 방지)
    echo "\xEF\xBB\xBF";
    
    // CSV 파일 생성
    $output = fopen('php://output', 'w');
    
    if (!empty($orders)) {
        // 헤더 행 출력
        $headers = array_keys($orders[0]);
        fputcsv($output, $headers);
        
        // 데이터 행 출력
        foreach ($orders as $order) {
            // 숫자 형식 정리
            if (isset($order['단가'])) {
                $order['단가'] = number_format($order['단가']) . '원';
            }
            if (isset($order['총금액'])) {
                $order['총금액'] = number_format($order['총금액']) . '원';
            }
            
            // 옵션 정리 (너무 길면 축약)
            if (isset($order['옵션']) && strlen($order['옵션']) > 50) {
                $order['옵션'] = substr($order['옵션'], 0, 47) . '...';
            }
            
            fputcsv($output, $order);
        }
    } else {
        // 데이터가 없을 때
        fputcsv($output, ['주문번호', '상품명', '옵션', '수량', '단가', '총금액', '상태', '주문일시', '주문자', '수취인', '수취인전화', '배송주소', '요청사항']);
        fputcsv($output, ['데이터가 없습니다.', '', '', '', '', '', '', '', '', '', '', '', '']);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    error_log("CSV 내보내기 오류: " . $e->getMessage());
    
    // 오류 발생 시 일반 HTML 응답
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>오류</title></head><body>";
    echo "<script>alert('CSV 내보내기 중 오류가 발생했습니다.'); history.back();</script>";
    echo "</body></html>";
}

// 데이터베이스 연결 종료
if (isset($stmt)) mysqli_stmt_close($stmt);
mysqli_close($db);
?>