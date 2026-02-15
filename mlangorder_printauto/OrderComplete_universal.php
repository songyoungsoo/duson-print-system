<?php
/**
 * 🌟 통합 주문완료 시스템 - Universal OrderComplete
 * 모든 제품의 주문완료를 처리하는 공통 시스템
 * 경로: mlangorder_printauto/OrderComplete_universal.php
 *
 * 기능:
 * - 모든 제품 타입 지원 (sticker, namecard, envelope 등)
 * - 마지막 주문 제품으로 "계속 쇼핑하기" 이동
 * - 반응형 디자인 지원
 * - 다양한 주문 형태 지원 (단건/다건/장바구니)
 */

session_start();

// FIX: HTTP 헤더에서 UTF-8 명시 (브라우저 인코딩 깨짐 방지)
header('Content-Type: text/html; charset=UTF-8');

// 데이터베이스 연결 및 통합 인증 시스템
include "../db.php";
$connect = $db;

// FIX: 명시적으로 UTF-8 charset 설정 (인코딩 깨짐 방지)
mysqli_set_charset($connect, 'utf8mb4');

// 통합 인증 시스템 로드
include "../includes/auth.php";

// 추가 옵션 표시 클래스 포함
include "../includes/AdditionalOptionsDisplay.php";

// 수량 포맷팅 헬퍼
include "../includes/quantity_formatter.php";
include "../includes/ProductSpecFormatter.php";
include "../includes/SpecDisplayService.php";
$optionsDisplay = new AdditionalOptionsDisplay($connect);
$specFormatter = new ProductSpecFormatter($connect);
$specDisplayService = new SpecDisplayService($connect);

// ===========================================
// 🔧 공통 함수들
// ===========================================

/**
 * 카테고리 번호로 한글명 조회
 */
function getCategoryName($connect, $category_no) {
    if (!$category_no) return '';
    
    $query = "SELECT title FROM mlangprintauto_transactioncate WHERE no = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $query);
    if (!$stmt) return $category_no;
    
    mysqli_stmt_bind_param($stmt, 's', $category_no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return $row['title'];
    }
    
    mysqli_stmt_close($stmt);
    return $category_no;
}

/**
 * 마지막 주문 품목 페이지 URL 생성
 * 핵심 기능: 계속 쇼핑하기를 마지막 주문 제품으로 연결
 */
function getLastOrderProductUrl($order_list) {
    if (empty($order_list)) {
        return '../index.php';
    }

    // 가장 최근 주문 (첫 번째 주문)
    $latest_order = $order_list[0];
    $product_type_key = null;

    // 1순위: Type_1의 JSON 데이터에서 product_type 추출
    $type_data = $latest_order['Type_1'] ?? '';
    if (!empty($type_data)) {
        // "상품 정보: " 접두사 제거
        if (strpos($type_data, '상품 정보: ') === 0) {
            $type_data = substr($type_data, strlen('상품 정보: '));
        }

        $json_data = json_decode($type_data, true);
        if ($json_data && isset($json_data['product_type'])) {
            $product_type_key = $json_data['product_type'];
        }
    }

    // 2순위: Type 필드에서 상품 타입 추정
    if (empty($product_type_key)) {
        $product_type = $latest_order['Type'] ?? '';
        if (!empty($product_type)) {
            $product_type_key = detectProductType($product_type);
        }
    }

    // 3순위: ThingCate 필드 확인 (레거시 호환)
    if (empty($product_type_key) && !empty($latest_order['ThingCate'])) {
        $product_type_key = detectProductType($latest_order['ThingCate']);
    }

    // 상품 타입별 URL 매핑
    if (!empty($product_type_key)) {
        $product_urls = getProductUrlMapping();
        return $product_urls[$product_type_key] ?? '../index.php';
    }

    // 모든 방법 실패 시 메인 페이지로
    return '../index.php';
}

/**
 * 상품 타입 자동 감지 (개선된 버전)
 */
function detectProductType($product_type) {
    if (empty($product_type)) {
        return null;
    }

    $product_type_lower = strtolower($product_type);

    // 정확한 매칭 우선 (코드명으로 직접 매칭)
    $exact_matches = [
        'sticker' => 'sticker',
        'sticker_new' => 'sticker',
        'namecard' => 'namecard',
        'envelope' => 'envelope',
        'littleprint' => 'poster', // littleprint는 poster로 통일
        'poster' => 'poster',
        'inserted' => 'inserted',
        'leaflet' => 'inserted',
        'cadarok' => 'cadarok',
        'merchandisebond' => 'merchandisebond',
        'ncrflambeau' => 'ncrflambeau',
        'msticker' => 'msticker'
    ];

    // 정확한 매칭 시도
    if (isset($exact_matches[$product_type_lower])) {
        return $exact_matches[$product_type_lower];
    }

    // 키워드 기반 매칭 (우선순위 순서 중요)
    $type_mapping = [
        'msticker' => ['자석스티커', 'magnet', 'magnetic'],
        'sticker' => ['스티커', 'sticker'],
        'namecard' => ['명함', 'namecard', 'card'],
        'envelope' => ['봉투', 'envelope'],
        'poster' => ['포스터', 'poster', 'little', '소형인쇄'],
        'inserted' => ['전단', '전단지', 'leaflet', 'flyer', '리플렛', 'inserted'],
        'cadarok' => ['카다록', '카탈로그', 'catalog', 'cadarok'],
        'merchandisebond' => ['상품권', '쿠폰', 'bond', 'merchandise'],
        'ncrflambeau' => ['ncr', '전표', 'form', 'flambeau']
    ];

    // 키워드 매칭 (긴 키워드부터 검사)
    foreach ($type_mapping as $key => $keywords) {
        // 키워드를 길이 순으로 정렬 (긴 것부터)
        usort($keywords, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($keywords as $keyword) {
            if (strpos($product_type_lower, strtolower($keyword)) !== false) {
                return $key;
            }
        }
    }

    // 매칭 실패 시 null 반환 (기본값 사용 안 함)
    return null;
}

/**
 * 제품별 URL 매핑
 */
function getProductUrlMapping() {
    return [
        'sticker' => '../mlangprintauto/sticker_new/index.php',
        'namecard' => '../mlangprintauto/namecard/index.php',
        'envelope' => '../mlangprintauto/envelope/index.php',
        'littleprint' => '../mlangprintauto/littleprint/index.php',
        'poster' => '../mlangprintauto/littleprint/index.php', // 포스터 = littleprint
        'inserted' => '../mlangprintauto/inserted/index.php',
        'cadarok' => '../mlangprintauto/cadarok/index.php',
        'merchandisebond' => '../mlangprintauto/merchandisebond/index.php',
        'ncrflambeau' => '../mlangprintauto/ncrflambeau/index.php',
        'msticker' => '../mlangprintauto/msticker/index.php',
        'leaflet' => '../mlangprintauto/inserted/index.php'
    ];
}

/**
 * 제품 상세 정보 표시
 */
function displayProductDetails($connect, $order) {
    global $optionsDisplay, $specFormatter, $specDisplayService; // 전역 변수로 접근

    if (empty($order['Type_1'])) return '';

    $type_data = $order['Type_1'];

    // FIX: "상품 정보: " 접두사 제거 (기존 데이터 호환성)
    if (strpos($type_data, '상품 정보: ') === 0) {
        $type_data = substr($type_data, strlen('상품 정보: '));
    }

    $json_data = json_decode($type_data, true);

    // 2025-12-19: 테이블 대신 div 스타일로 변경 (OnlineOrder_unified.php 규격/옵션 스타일)
    $html = '<div class="specs-cell" style="line-height: 1.6;">';

    // JSON 파싱 실패 시 SpecDisplayService를 통해 레거시 텍스트 파싱 (2026-01-12)
    if (!$json_data && !empty($type_data)) {
        // SpecDisplayService.getDisplayData()가 레거시 텍스트 파싱 담당
        $displayData = $specDisplayService->getDisplayData($order);

        // line1, line2 표시 (타입/재질/사이즈/면수/디자인)
        if (!empty($displayData['line1'])) {
            $html .= '<div class="spec-item">' . htmlspecialchars($displayData['line1']) . '</div>';
        }
        if (!empty($displayData['line2'])) {
            $html .= '<div class="spec-item">' . htmlspecialchars($displayData['line2']) . '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    if ($json_data && is_array($json_data)) {
        // ✅ Phase 3: nested structure 문제 해결 (스티커의 order_details)
        if (isset($json_data['data_version']) && $json_data['data_version'] == 2) {
            // 신규 데이터: flat structure, 바로 사용
            $item = array_merge($order, $json_data);
        } else {
            // 레거시: nested structure 대응 (스티커만 order_details 중첩 구조)
            if (isset($json_data['order_details'])) {
                // 스티커 레거시 데이터: order_details 안의 데이터 추출
                $item = array_merge($order, $json_data['order_details']);
                $item['product_type'] = $json_data['product_type'] ?? '';
            } else {
                // 다른 제품: flat structure
                $item = array_merge($order, $json_data);
            }
        }

        $item['product_type'] = $order['product_type'] ?? $json_data['product_type'] ?? $item['product_type'] ?? '';

        // product_type이 없으면 데이터 구조로 추론
        if (empty($item['product_type'])) {
            if (isset($json_data['Section']) && isset($json_data['PN_type'])) {
                $item['product_type'] = 'littleprint';
            } elseif (isset($json_data['MY_Fsd']) && isset($json_data['PN_type'])) {
                $item['product_type'] = 'inserted';
            } elseif (isset($json_data['MY_type']) && isset($json_data['Section']) && isset($json_data['POtype'])) {
                // 봉투: MY_type + Section + POtype 조합
                $item['product_type'] = 'envelope';
            } elseif (isset($json_data['Section']) && !isset($json_data['PN_type'])) {
                $item['product_type'] = 'cadarok';
            }
        }

        $specs = $specFormatter->format($item);
        if (!empty($specs['line1'])) {
            $html .= '<div class="spec-item">' . htmlspecialchars($specs['line1']) . '</div>';
        }
        if (!empty($specs['line2'])) {
            $html .= '<div class="spec-item">' . htmlspecialchars($specs['line2']) . '</div>';
        }
        // 추가옵션은 별도 섹션에서 표시하므로 여기서는 생략
    } else {
        // 일반 텍스트 데이터 처리 (전단지 등)
        $lines = explode("\n", $type_data);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $html .= '<div class="spec-item">' . htmlspecialchars($line) . '</div>';
            }
        }
    }

    $html .= '</div>';
    
    // 추가 옵션 표시 (주문 데이터에서 추출)
    if ($optionsDisplay && !empty($order)) {
        // 주문 데이터에서 추가 옵션 정보 추출
        $optionData = [
            'coating_enabled' => $order['coating_enabled'] ?? 0,
            'coating_type' => $order['coating_type'] ?? '',
            'coating_price' => $order['coating_price'] ?? 0,
            'folding_enabled' => $order['folding_enabled'] ?? 0,
            'folding_type' => $order['folding_type'] ?? '',
            'folding_price' => $order['folding_price'] ?? 0,
            'creasing_enabled' => $order['creasing_enabled'] ?? 0,
            'creasing_lines' => $order['creasing_lines'] ?? 0,
            'creasing_price' => $order['creasing_price'] ?? 0,
            'additional_options_total' => $order['additional_options_total'] ?? 0,
            // 🆕 봉투 양면테이프 옵션 추가
            'envelope_tape_enabled' => $order['envelope_tape_enabled'] ?? 0,
            'envelope_tape_quantity' => $order['envelope_tape_quantity'] ?? 0,
            'envelope_tape_price' => $order['envelope_tape_price'] ?? 0,
            'envelope_additional_options_total' => $order['envelope_additional_options_total'] ?? 0
        ];

        $optionDetails = $optionsDisplay->getOrderDetails($optionData);
        if ($optionDetails['has_options']) {
            $html .= '<div style="margin-top: 8px; padding: 10px 10px 5px 10px; background: #e8f5e9; border-radius: 4px; border-left: 3px solid #4caf50; max-width: 100%; overflow: hidden; word-wrap: break-word;">';
            $html .= '<strong style="color: #2e7d32;">추가 옵션:</strong> ';

            foreach ($optionDetails['options'] as $option) {
                $html .= '<span class="option-item" style="background-color: #c8e6c9; color: #1b5e20; margin: 0 5px;">';
                $html .= $option['category'] . '(' . $option['name'] . ') ';
                $html .= '<strong>' . $option['formatted_price'] . '</strong>';
                $html .= '</span>';
            }

            $html .= '<div style="margin-top: 2.5px; font-size: 0.85rem; color: #2e7d32;">';
            $html .= '추가옵션 소계: <strong>' . number_format($optionDetails['total_price']) . '원</strong>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }

    // 🆕 프리미엄 옵션 표시 (명함용)
    if (!empty($order['premium_options']) && !empty($order['premium_options_total'])) {
        $premium_options = json_decode($order['premium_options'], true);
        if ($premium_options && $order['premium_options_total'] > 0) {
            $html .= '<div style="margin-top: 8px; padding: 10px 10px 5px 10px; background: #e8f5e9; border-radius: 4px; border-left: 3px solid #4caf50; max-width: 100%; overflow: hidden; word-wrap: break-word;">';
            $html .= '<strong style="color: #2e7d32;">✨ 프리미엄 옵션:</strong> ';

            $premium_option_names = [
                'foil' => ['name' => '박', 'types' => [
                    'gold_matte' => '금박무광',
                    'gold_gloss' => '금박유광',
                    'silver_matte' => '은박무광',
                    'silver_gloss' => '은박유광',
                    'blue_gloss' => '청박유광',
                    'red_gloss' => '적박유광',
                    'green_gloss' => '녹박유광',
                    'black_gloss' => '먹박유광'
                ]],
                'numbering' => ['name' => '넘버링', 'types' => ['single' => '1개', 'double' => '2개']],
                'perforation' => ['name' => '미싱', 'types' => ['horizontal' => '가로미싱', 'vertical' => '세로미싱', 'cross' => '십자미싱']],
                'rounding' => ['name' => '귀돌이', 'types' => ['4corners' => '네귀돌이', '2corners' => '두귀돌이']],
                'creasing' => ['name' => '오시', 'types' => ['single_crease' => '1줄오시', 'double_crease' => '2줄오시']]
            ];

            foreach ($premium_option_names as $option_key => $option_info) {
                if (!empty($premium_options[$option_key . '_enabled']) && $premium_options[$option_key . '_enabled'] == 1) {
                    $price = intval($premium_options[$option_key . '_price'] ?? 0);
                    if ($price > 0) {
                        $html .= '<span class="option-item" style="background-color: #c8e6c9; color: #1b5e20; margin: 0 5px;">';
                        $html .= $option_info['name'];

                        // 타입 표시
                        $option_type = $premium_options[$option_key . '_type'] ?? '';
                        if (!empty($option_type) && isset($option_info['types'][$option_type])) {
                            $html .= '(' . $option_info['types'][$option_type] . ')';
                        } elseif (empty($option_type)) {
                            $html .= '(타입미선택)';
                        }

                        $html .= ' <strong>' . number_format($price) . '원</strong>';
                        $html .= '</span>';
                    }
                }
            }

            $html .= '<div style="margin-top: 2.5px; font-size: 0.85rem; color: #2e7d32;">';
            $html .= '프리미엄 옵션 소계: <strong>' . number_format($order['premium_options_total']) . '원</strong>';
            $html .= '</div>';
            $html .= '</div>';
        }
    }
    
    // 요청사항 표시
    if (!empty($order['cont'])) {
        $html .= '<div class="request-note">';
        $html .= '<strong>💬 요청사항:</strong><br>';
        $html .= nl2br(htmlspecialchars($order['cont']));
        $html .= '</div>';
    }
    
    return $html;
}

// ===========================================
// 🎯 메인 로직 시작
// ===========================================

// GET 파라미터에서 데이터 가져오기
$orders = $_GET['orders'] ?? '';
$email = $_GET['email'] ?? '';
$name = $_GET['name'] ?? '';
$payment_status = $_GET['payment'] ?? ''; // payment=cancelled, failed, success

if (empty($orders)) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='../mlangorder_printauto/shop/cart.php';</script>";
    exit;
}

// 주문 번호들을 배열로 변환
$order_numbers = explode(',', $orders);
$order_list = [];
$total_amount = 0;
$total_amount_vat = 0;

// 각 주문 정보 조회
foreach ($order_numbers as $order_no) {
    $order_no = trim($order_no);
    if (!empty($order_no)) {
        $query = "SELECT * FROM mlangorder_printauto WHERE no = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $order_no);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $order_list[] = $row;
                $total_amount += $row['money_4'];
                $total_amount_vat += $row['money_5'];
            }
            mysqli_stmt_close($stmt);
        }
    }
}

if (empty($order_list)) {
    echo "<script>alert('주문 정보를 찾을 수 없습니다.'); location.href='../mlangorder_printauto/shop/cart.php';</script>";
    exit;
}

// 첫 번째 주문의 고객 정보 사용
$first_order = $order_list[0];

// 페이지 설정
$page_title = '주문 완료 - Universal System';
$current_page = 'order_complete';

// 추가 CSS 연결
$additional_css = [
    '/css/common-styles.css',
    '/css/product-layout.css',
    '/css/excel-unified-style.css',
    '/css/table-design-system.css'
];

// 공통 헤더 포함
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 완료 - 두손기획인쇄</title>

    <!-- Google Fonts - Noto Sans KR -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@400;500;700&display=swap" rel="stylesheet">

<!-- Excel 스타일 OrderComplete -->
<style>
/* Excel Design System - 깔끔한 스프레드시트 스타일 */
:root {
    --primary-blue: #1E90FF;
    --dark-blue: #1873CC;
    --success-green: #28a745;
    --warning-orange: #f39c12;
    --error-red: #D9534F;
    --excel-gray: #F0F0F0;
    --excel-border: #CCCCCC;
    --text-primary: #333333;
    --text-secondary: #666666;
    --hover-blue: #E8F4FF;
}

.universal-container {
    max-width: 1100px;
    margin: 10px auto;
    padding: 20px;
    background: white;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-family: 'Noto Sans KR', sans-serif;
    font-size: 14px;
    color: #222;
    line-height: 1.6;
}

/* 📊 Excel 스타일 주문 테이블 */
.order-table {
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    background: white;
    border: 1px solid #ccc;
    table-layout: fixed;
}

.order-table thead th {
    background: #f3f3f3;
    color: #222;
    font-weight: bold;
    padding: 10px;
    text-align: center;
    font-size: 14px;
    border: 1px solid #ccc;
}

.order-table tbody tr {
    transition: background-color 0.2s ease;
    border-bottom: 1px solid #ccc;
}

.order-table tbody tr:nth-child(even) {
    background: #fafafa;
}

.order-table tbody tr:hover {
    background: #f5f5f5;
}

.order-table td {
    padding: 10px;
    vertical-align: top;
    font-size: 14px;
    border: 1px solid #ccc;
    color: #222;
    word-break: break-word;
}

/* 테이블 컬럼 스타일 (7컬럼: 주문번호, 품목, 규격/옵션, 수량, 단위, 공급가액, 상태) */
.col-order-no {
    width: 10%;
    text-align: center;
    font-weight: 600;
    color: var(--primary-blue);
    vertical-align: middle;
}

.col-product {
    width: 12%;
    font-weight: 600;
    color: var(--text-primary);
    vertical-align: middle;
    text-align: center;
}

.col-details {
    width: 38%;
    word-wrap: break-word;
    overflow-wrap: break-word;
    white-space: normal;
    vertical-align: top;
}

.col-quantity {
    width: 10%;
    text-align: center;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
    vertical-align: middle;
}

.col-unit {
    width: 8%;
    text-align: center;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 14px;
    vertical-align: middle;
}

.col-price {
    width: 12%;
    text-align: right;
    font-weight: 700;
    color: var(--error-red);
    font-size: 14px;
    vertical-align: middle;
}

.col-status {
    width: 10%;
    text-align: center;
    vertical-align: middle;
}

/* 주문 요약 섹션 (cart.php 스타일) */
.order-summary {
    margin-top: 20px;
    background-color: #F8F9FA;
    border-radius: 8px;
    padding: 16px;
    border: 1px solid #CCCCCC;
}

.summary-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.summary-title {
    color: #4a5568;
    font-weight: 600;
    font-size: 15px;
}

.summary-count {
    color: #718096;
    font-size: 13px;
}

.summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 12px;
    margin-bottom: 0;
}

.summary-box {
    text-align: center;
    padding: 12px;
    background-color: white;
    border-radius: 6px;
    border: 1px solid #CCCCCC;
}

.summary-box-label {
    color: #718096;
    font-size: 12px;
    margin-bottom: 4px;
}

.summary-box-value {
    color: #2d3748;
    font-weight: 600;
    font-size: 14px;
}

.summary-box.total {
    background-color: #1E90FF;
    color: white;
    border: 1px solid #1873CC;
}

.summary-box.total .summary-box-label {
    opacity: 0.9;
    color: white;
}

.summary-box.total .summary-box-value {
    font-weight: 700;
    font-size: 16px;
    color: white;
}

/* 결제 모달 스타일 */
.payment-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 1000;
    justify-content: center;
    align-items: center;
}

.payment-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 420px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.payment-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border-radius: 12px 12px 0 0;
}

.payment-modal-header .modal-brand {
    font-size: 1.2rem;
    font-weight: 700;
    color: white;
    margin: 0;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.payment-modal-header .modal-title {
    font-size: 0.95rem;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.9);
    margin: 0;
    width: 100%;
    order: 1;
}

.payment-modal-header .modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    font-size: 1.3rem;
    cursor: pointer;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.payment-modal-header .modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

.payment-modal-body {
    padding: 20px;
}

.payment-amount {
    text-align: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 15px;
    color: #333;
}

.payment-amount strong {
    font-size: 20px;
    color: #D9534F;
}

.payment-cancelled-message {
    background: #fff3cd;
    border-left: 4px solid #f39c12;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 16px;
    color: #856404;
    text-align: center;
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.payment-option {
    display: flex;
    align-items: center;
    padding: 16px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.payment-option:hover {
    border-color: #1E90FF;
    background-color: #f8fbff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 144, 255, 0.15);
}

.option-icon {
    font-size: 28px;
    margin-right: 16px;
}

.option-info {
    flex: 1;
}

.option-title {
    font-weight: 600;
    font-size: 15px;
    color: #333;
    margin-bottom: 4px;
}

.option-desc {
    font-size: 13px;
    color: #666;
}

/* 무통장입금 섹션 */
.bank-transfer-section {
    margin-top: 20px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    border: 1px solid #dee2e6;
}

.bank-transfer-section h4 {
    margin: 0 0 16px 0;
    font-size: 16px;
    color: #333;
    display: flex;
    align-items: center;
    gap: 8px;
}

.bank-accounts {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.bank-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.bank-item-centered {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 14px 20px;
    background: white;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    margin-bottom: 10px;
}

.bank-item-centered .bank-name {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 4px;
}

.bank-item-centered .bank-account {
    font-size: 1.2rem;
    font-weight: 700;
}

.bank-name {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.bank-account {
    font-family: 'Consolas', monospace;
    font-size: 15px;
    color: #1E90FF;
    font-weight: 600;
    cursor: pointer;
}

.bank-account:hover {
    text-decoration: underline;
}

.bank-notice {
    margin-top: 16px;
    padding: 12px;
    background: #fff3cd;
    border-radius: 6px;
    font-size: 13px;
    color: #856404;
    line-height: 1.5;
}

.bank-notice strong {
    color: #533f03;
}

/* 결제하기 버튼 스타일 (빨간색으로 변경) */
.btn-pay {
    background-color: #D9534F !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(217, 83, 79, 0.3);
}

.btn-pay:hover {
    background-color: #C9302C !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(217, 83, 79, 0.4);
}

/* 반응형 */
@media (max-width: 768px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }

    .payment-modal-content {
        width: 95%;
        margin: 10px;
    }

    .bank-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }
}

.option-item {
    display: inline-block;
    margin: 2px 8px 2px 0;
    padding: 4px 8px;
    background-color: var(--excel-gray);
    border-radius: 4px;
    font-size: 0.8rem;
    color: var(--text-primary);
    font-weight: 500;
    border: 1px solid var(--excel-border);
}

/* 규격/옵션 셀 스타일 */
.specs-cell {
    max-width: 100%;
    overflow: hidden;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.spec-item {
    line-height: 1.5;
    margin-bottom: 4px;
    max-width: 100%;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* 요청사항 스타일 */
.request-note {
    margin-top: 8px;
    padding: 10px;
    background: #FFFCE6;
    border-left: 4px solid var(--warning-orange);
    border-radius: 4px;
    font-size: 0.85rem;
    color: #856404;
    max-width: 100%;
    overflow: hidden;
    word-wrap: break-word;
}

/* 정보 카드들 - Excel 스타일 */
.info-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin: 20px 0;
}

.info-card {
    background: white;
    border-radius: 4px;
    padding: 8px 10px;
    border: 1px solid var(--excel-border);
    line-height: 1.2;
}

.info-card h3 {
    margin: 0 0 4px 0;
    font-size: 0.95rem;
    color: var(--text-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1.2;
}

.info-row {
    display: flex;
    margin-bottom: 0;
    align-items: center;
    line-height: 1.2;
    padding: 1px 0;
}

/* 기존 중복 정의 제거됨 */

.info-value {
    flex: 1;
    color: var(--text-primary);
    font-weight: 500;
    font-size: 13px;
}

.info-label {
    width: 90px;
    font-weight: 600;
    color: var(--text-primary);
    font-size: 13px;
}

/* 인쇄용 스타일 */
@media print {
    /* 헤더, 푸터, 네비게이션 숨김 */
    header, footer, nav, .nav, .navbar, .header, .footer,
    .action-section {
        display: none !important;
    }
    
    /* 페이지 여백 최소화 */
    @page {
        margin: 0.5in;
        size: A4;
    }
    
    body {
        margin: 0;
        padding: 0;
        font-size: 12pt;
        line-height: 1.3;
        color: black !important;
        background: white !important;
    }
    
    .universal-container {
        box-shadow: none !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 10px !important;
        background: white !important;
    }
    
    /* 색상 제거 - 흑백 인쇄용 */
    .info-card {
        background: white !important;
        border: 1px solid #333 !important;
        border-radius: 4px !important;
        page-break-inside: avoid;
        margin-bottom: 15px !important;
    }
    
    .order-table {
        border: 1px solid #333 !important;
        background: white !important;
    }
    
    .order-table th {
        background: #f0f0f0 !important;
        color: black !important;
        border: 1px solid #333 !important;
    }
    
    .order-table td {
        border: 1px solid #333 !important;
        color: black !important;
    }
    
    /* 가격 강조 유지 */
    .price-supply span {
        font-size: 14pt !important;
        font-weight: bold !important;
    }
    
    /* 인쇄용 헤더 스타일 */
    .print-header {
        display: block !important;
        margin-bottom: 25px;
        padding-bottom: 20px;
        border-bottom: 3px solid #333;
    }
    
    .print-company-info {
        text-align: center;
        margin-bottom: 15px;
    }
    
    .print-company-info h1 {
        font-size: 24pt !important;
        font-weight: bold !important;
        margin: 0 0 8px 0 !important;
        color: black !important;
        letter-spacing: 2px;
    }
    
    .company-details p {
        margin: 2px 0 !important;
        font-size: 9pt !important;
        color: #666 !important;
    }
    
    .print-doc-title {
        text-align: center;
        margin: 15px 0;
        padding: 10px 0;
        border-top: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
    }
    
    .print-doc-title h2 {
        font-size: 18pt !important;
        font-weight: bold !important;
        margin: 0 0 5px 0 !important;
        color: black !important;
        letter-spacing: 1px;
    }
    
    .print-date {
        font-size: 10pt !important;
        color: #666 !important;
        margin: 0 !important;
    }
    
    .print-customer-info {
        margin: 15px 0;
    }
    
    .customer-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 1px solid #333 !important;
    }
    
    .customer-table td {
        padding: 8px 12px !important;
        border: 1px solid #666 !important;
        font-size: 10pt !important;
        color: black !important;
    }
    
    .customer-table strong {
        color: black !important;
        font-weight: bold !important;
    }
    
    .print-footer {
        display: block !important;
        page-break-inside: avoid;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 2px solid #333;
    }
    
    .print-payment-info {
        text-align: center;
    }
    
    .print-payment-info h3 {
        font-size: 14pt !important;
        font-weight: bold !important;
        margin: 0 0 10px 0 !important;
        color: black !important;
        letter-spacing: 1px;
    }
    
    .payment-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #333 !important;
        margin: 10px 0 !important;
    }
    
    .payment-table td {
        padding: 8px 12px !important;
        border: 1px solid #666 !important;
        font-size: 10pt !important;
        color: black !important;
        text-align: center !important;
    }
    
    .payment-table strong {
        color: black !important;
        font-weight: bold !important;
    }
    
    .print-contact-notice {
        text-align: center;
        margin-top: 15px;
        padding: 8px;
        border: 1px solid #999;
        background: #f5f5f5 !important;
    }
    
    .print-contact-notice p {
        font-size: 9pt !important;
        color: #333 !important;
        margin: 0 !important;
    }
}

/* 🎬 액션 버튼 구역 */
.action-section {
    background: white;
    border-radius: 4px;  /* Excel 스타일 */
    padding: 20px;
    text-align: center;
    margin: 20px 0;
}

.action-section h3 {
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    color: var(--text-primary);
}

.action-buttons {
    display: flex !important;
    justify-content: center !important;
    gap: 12px !important;
    flex-wrap: nowrap !important;
    grid-template-columns: none !important;
}

.action-buttons .btn-action {
    flex: 1 !important;
    max-width: 180px !important;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    min-width: auto;
    max-width: fit-content;
}

.btn-continue {
    background-color: #28a745 !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
}

.btn-print {
    background-color: #1E90FF !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(30, 144, 255, 0.3);
}

.btn-payment {
    background-color: #6f42c1 !important;
    color: white !important;
    box-shadow: 0 2px 8px rgba(111, 66, 193, 0.3);
}

.btn-payment:hover {
    background-color: #5a32a3 !important;
}

.btn-action:hover {
    transform: translateY(-1px);  /* Subtle hover effect */
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-continue:hover {
    background-color: #218838;  /* Darker green on hover */
}

.btn-print:hover {
    background-color: #1873CC;  /* Darker blue on hover */
}

/* 상태 배지 - Excel 스타일 */
.status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;  /* Excel 스타일 sharp corners */
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
}

.status-pending {
    background: #FFF3CD;  /* Light yellow */
    color: #856404;
    border: 1px solid var(--warning-orange);
}

.status-processing {
    background: #D6EBFF;  /* Light blue */
    color: var(--primary-blue);
    border: 1px solid var(--primary-blue);
}

.status-completed {
    background: #D4EDDA;  /* Light green */
    color: var(--success-green);
    border: 1px solid var(--success-green);
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .universal-container {
        margin: 10px;
        padding: 15px;
    }
    
    .success-header h1 {
        font-size: 1.8rem;
    }
    
    .success-stats {
        gap: 20px;
    }
    
    .order-table {
        font-size: 0.8rem;
    }
    
    .order-table td {
        padding: 10px 8px;
    }
    
    .action-buttons {
        flex-direction: row;
        align-items: center;
        gap: 10px;
    }

    .btn-action {
        min-width: auto;
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}

/* 세련된 인쇄 스타일 */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    body {
        font-family: 'Noto Sans KR', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif !important;
        color: #000 !important;
        background: white !important;
        margin: 0;
        padding: 15mm;
    }
    
    .action-section,
    .btn-action,
    .success-header {
        display: none !important;
    }
    
    .universal-container {
        box-shadow: none !important;
        padding: 0 !important;
        max-width: none !important;
        margin: 0 !important;
        background: white !important;
    }
    
    /* 회사 헤더 - 고급스러운 디자인 */
    .print-header {
        display: block !important;
        page-break-inside: avoid;
        margin-bottom: 30px;
        padding-bottom: 25px;
        border-bottom: 3px double #000;
        position: relative;
    }
    
    .print-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: #333;
    }
    
    .print-company-info {
        text-align: center;
        margin-bottom: 20px;
        position: relative;
    }
    
    .print-company-info h1 {
        font-size: 28pt !important;
        font-weight: 900 !important;
        margin: 10px 0 !important;
        color: #000 !important;
        letter-spacing: 3px;
        text-shadow: 1px 1px 0px #ccc;
        position: relative;
    }
    
    .print-company-info h1::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: #000;
    }
    
    .company-details {
        margin-top: 15px;
        padding: 10px;
        background: #f8f9fa !important;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    
    .company-details p {
        margin: 3px 0 !important;
        font-size: 10pt !important;
        color: #495057 !important;
        font-weight: 500;
    }
    
    /* 문서 제목 - 전문적인 스타일 */
    .print-doc-title {
        text-align: center;
        margin: 25px 0;
        padding: 15px 0;
        border: 2px solid #000;
        border-radius: 10px;
        background-color: #f8f9fa;
        position: relative;
    }
    
    .print-doc-title::before {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: #000;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16pt;
        font-weight: bold;
    }
    
    .print-doc-title h2 {
        font-size: 22pt !important;
        font-weight: 800 !important;
        margin: 0 0 8px 0 !important;
        color: #000 !important;
        letter-spacing: 2px;
        text-transform: uppercase;
    }
    
    .print-date {
        font-size: 11pt !important;
        color: #495057 !important;
        margin: 0 !important;
        font-weight: 600;
        background: #fff !important;
        padding: 3px 15px;
        border-radius: 15px;
        display: inline-block;
        border: 1px solid #dee2e6;
    }
    
    /* 고객 정보 - 세련된 테이블 */
    .print-customer-info {
        margin: 25px 0;
        page-break-inside: avoid;
    }
    
    .customer-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #000 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    }
    
    .customer-table td {
        padding: 12px 15px !important;
        border: 1px solid #495057 !important;
        font-size: 11pt !important;
        color: #000 !important;
        background: #ffffff !important;
        position: relative;
    }
    
    .customer-table td:first-child {
        background: #f8f9fa !important;
        font-weight: 700;
        border-right: 2px solid #000 !important;
    }
    
    .customer-table strong {
        color: #000 !important;
        font-weight: 800 !important;
    }
    
    /* 📊 주문 테이블 - 프로페셔널 디자인 */
    .order-table {
        display: table !important;
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #000 !important;
        margin: 20px 0 !important;
        page-break-inside: avoid;
    }
    
    .order-table thead {
        display: table-header-group !important;
        background: #000 !important;
    }
    
    .order-table thead th {
        padding: 15px 10px !important;
        border: 1px solid #fff !important;
        font-size: 11pt !important;
        font-weight: 800 !important;
        color: #fff !important;
        text-align: center !important;
        background: #000 !important;
    }
    
    .order-table tbody {
        display: table-row-group !important;
    }
    
    .order-table tbody tr {
        display: table-row !important;
        page-break-inside: avoid;
    }
    
    .order-table tbody td {
        display: table-cell !important;
        padding: 12px 10px !important;
        border: 1px solid #495057 !important;
        font-size: 10pt !important;
        color: #000 !important;
        background: #fff !important;
        vertical-align: top !important;
    }
    
    .order-row {
        display: table-row !important;
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
    
    .order-table .col-order-no {
        width: 10% !important;
        text-align: center !important;
        font-weight: 700 !important;
        background: #f8f9fa !important;
        vertical-align: middle !important;
    }

    .order-table .col-product {
        width: 12% !important;
        text-align: center !important;
        font-weight: 700 !important;
        color: #000 !important;
        vertical-align: middle !important;
    }

    .order-table .col-details {
        width: 38% !important;
        text-align: left !important;
        vertical-align: top !important;
        font-size: 9pt !important;
    }

    .order-table .col-quantity {
        width: 10% !important;
        text-align: center !important;
        font-weight: 700 !important;
        vertical-align: middle !important;
    }

    .order-table .col-unit {
        width: 8% !important;
        text-align: center !important;
        font-weight: 600 !important;
        vertical-align: middle !important;
    }

    .order-table .col-price {
        width: 12% !important;
        text-align: right !important;
        font-weight: 700 !important;
        vertical-align: middle !important;
    }

    .order-table .col-status {
        width: 10% !important;
        text-align: center !important;
        vertical-align: middle !important;
    }

    .status-badge {
        background: #000 !important;
        color: #fff !important;
        padding: 4px 8px !important;
        border-radius: 4px !important;
        font-size: 8pt !important;
        font-weight: 600 !important;
    }
    
    /* 결제 정보 푸터 - 우아한 디자인 */
    .print-footer {
        display: block !important;
        page-break-inside: avoid;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 3px double #000;
        position: relative;
    }
    
    .print-footer::before {
        content: '';
        position: absolute;
        top: -2px;
        left: 0;
        right: 0;
        height: 1px;
        background: #333;
    }
    
    .print-payment-info {
        text-align: center;
        position: relative;
    }
    
    .print-payment-info h3 {
        font-size: 16pt !important;
        font-weight: 800 !important;
        margin: 0 0 15px 0 !important;
        color: #000 !important;
        letter-spacing: 2px;
        position: relative;
        display: inline-block;
    }
    
    .print-payment-info h3::before,
    .print-payment-info h3::after {
        content: '◆';
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10pt;
        color: #495057;
    }
    
    .print-payment-info h3::before {
        left: -25px;
    }
    
    .print-payment-info h3::after {
        right: -25px;
    }
    
    .payment-table {
        width: 100% !important;
        border-collapse: collapse !important;
        border: 2px solid #000 !important;
        margin: 15px 0 !important;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1) !important;
    }
    
    .payment-table td {
        padding: 12px 15px !important;
        border: 1px solid #495057 !important;
        font-size: 11pt !important;
        color: #000 !important;
        text-align: center !important;
        background: #fff !important;
    }
    
    .payment-table td:first-child {
        background: #f8f9fa !important;
        font-weight: 800 !important;
        border-right: 2px solid #000 !important;
    }
    
    .payment-table strong {
        color: #000 !important;
        font-weight: 800 !important;
    }
    
    .print-contact-notice {
        text-align: center;
        margin-top: 20px;
        padding: 15px;
        border: 2px solid #495057;
        border-radius: 10px;
        background-color: #f8f9fa;
        position: relative;
    }
    
    .print-contact-notice::before {
        content: '';
        position: absolute;
        top: -15px;
        left: 50%;
        transform: translateX(-50%);
        background: #000;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12pt;
    }
    
    .print-contact-notice p {
        font-size: 10pt !important;
        color: #000 !important;
        margin: 5px 0 !important;
        font-weight: 600;
    }
}

/* ✨ 로딩 애니메이션 */
.order-row {
    opacity: 0;
    transform: translateY(20px);
    animation: slideInUp 0.5s ease forwards;
}

@keyframes slideInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- 헤더 스타일 (header-ui.php용) -->
<link rel="stylesheet" href="../css/common-styles.css">
</head>
<body>

<?php include "../includes/header-ui.php"; ?>

<div class="universal-container">
    <!-- 인쇄용 헤더 (화면에서는 숨김, 인쇄시에만 표시) -->
    <div class="print-header" style="display: none;">
        <div class="print-company-info">
            <h1>두손기획인쇄</h1>
            <div class="company-details">
                <p>서울 영등포구 영등포로36길 9, 송호빌딩 1층</p>
                <p>TEL: 02-2632-1830 | FAX: 02-2632-1831 | www.dsp114.co.kr</p>
            </div>
        </div>
        <div class="print-doc-title">
            <h2>주문 확인서</h2>
            <div class="print-date">발행일: <?php echo date('Y년 m월 d일'); ?></div>
        </div>
        <div class="print-customer-info">
            <table class="customer-table">
                <tr>
                    <td><strong>고객명:</strong> <?php echo htmlspecialchars($name ?: $first_order['name']); ?></td>
                    <td><strong>주문일:</strong> <?php echo htmlspecialchars($first_order['date'] ?? date('Y-m-d')); ?></td>
                </tr>
                <tr>
                    <td><strong>연락처:</strong> <?php echo htmlspecialchars($first_order['phone'] ?? $first_order['Hendphone'] ?? '정보없음'); ?></td>
                    <td><strong>이메일:</strong> <?php echo htmlspecialchars($email ?: $first_order['email'] ?: '정보없음'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 주문완료 제목 -->
    <h2 style="text-align: center; font-size: 22px; font-weight: bold; margin: 20px 0 10px; color: #2c3e50;">주문이 완료되었습니다</h2>

    <!-- 이메일 발송 안내 -->
    <div style="text-align: center; margin: 0 0 30px; padding: 12px 20px; background: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 4px; max-width: 600px; margin-left: auto; margin-right: auto;">
        <p style="margin: 0; color: #1565c0; font-size: 14px; font-weight: 500;">
            주문내용은 이메일로 발송됩니다
        </p>
    </div>

    <!-- 주문 테이블 (7컬럼: 주문번호, 품목, 규격/옵션, 수량, 단위, 공급가액, 상태) -->
    <table class="order-table">
        <thead>
            <tr>
                <th class="col-order-no">주문번호</th>
                <th class="col-product">품목</th>
                <th class="col-details">규격/옵션</th>
                <th class="col-quantity">수량</th>
                <th class="col-unit">단위</th>
                <th class="col-price">공급가액</th>
                <th class="col-status">상태</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_list as $index => $order):
            // ✅ Phase 2 통합: SpecDisplayService로 통합 출력 데이터 생성
            $displayData = $specDisplayService->getDisplayData($order);
            $product_details_html = displayProductDetails($connect, $order);
            // 전단지/리플렛 체크 (수량에 단위가 이미 포함됨)
            $is_flyer = in_array($order['product_type'] ?? '', ['inserted', 'leaflet']);
            ?>
            <tr class="order-row" style="animation-delay: <?php echo $index * 0.1; ?>s">
                <!-- 주문번호 -->
                <td class="col-order-no">
                    #<?php echo htmlspecialchars($order['no']); ?>
                </td>

                <!-- 품목 -->
                <td class="col-product">
                    <?php echo htmlspecialchars($order['Type']); ?>
                </td>

                <!-- 규격/옵션 -->
                <td class="col-details">
                    <?php echo $product_details_html; ?>
                </td>

                <!-- 수량 (통합) - 모든 품목 동일 구조 -->
                <td class="col-quantity">
                    <?php
                    $qty_val = $displayData['quantity_value'] ?? 0;
                    $qty_sheets = $displayData['quantity_sheets'] ?? 0;
                    $productType = $order['product_type'] ?? '';

                    // 수량 포맷팅
                    $formatted_qty = function_exists('formatQuantityNum')
                        ? formatQuantityNum($qty_val)
                        : number_format(floatval($qty_val));
                    echo $formatted_qty;

                    // ✅ 2026-01-16: 연/권 단위에 매수 표시 (전단지, NCR양식지)
                    $unit = $displayData['unit'] ?? '';
                    if ($qty_sheets > 0 && in_array($unit, ['연', '권'])): ?>
                        <br><span style="font-size: 11px; color: #1e88ff;">(<?php echo number_format($qty_sheets); ?>매)</span>
                    <?php endif; ?>
                </td>

                <!-- 단위 (통합) - 모든 품목 동일하게 단위 표시 -->
                <td class="col-unit">
                    <?php echo htmlspecialchars($displayData['unit'] ?? '매'); ?>
                </td>

                <!-- 공급가액 -->
                <td class="col-price">
                    <?php echo number_format($displayData['price_supply']); ?>원
                </td>

                <!-- 상태 -->
                <td class="col-status">
                    <span class="status-badge status-pending">입금대기</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 주문 요약 (cart.php 스타일) -->
    <div class="order-summary">
        <div class="summary-header">
            <div class="summary-title">결제 금액</div>
            <div class="summary-count">총 <?php echo count($order_list); ?>개 상품</div>
        </div>
        <div class="summary-grid">
            <div class="summary-box">
                <div class="summary-box-label">상품금액</div>
                <div class="summary-box-value"><span class="anim-number" data-target="<?php echo intval($total_amount); ?>">0</span>원</div>
            </div>
            <div class="summary-box">
                <div class="summary-box-label">부가세</div>
                <div class="summary-box-value"><span class="anim-number" data-target="<?php echo intval($total_amount_vat - $total_amount); ?>">0</span>원</div>
            </div>
            <div class="summary-box total">
                <div class="summary-box-label">총 결제금액</div>
                <div class="summary-box-value"><span class="anim-number" data-target="<?php echo intval($total_amount_vat); ?>">0</span>원</div>
            </div>
        </div>
        <!-- 버튼 영역 (결제 금액 바로 아래) -->
        <div class="action-buttons" style="margin-top: 16px;">
            <a href="<?php echo getLastOrderProductUrl($order_list); ?>" class="btn-action btn-continue">
                계속 쇼핑하기
            </a>
            <button onclick="openPaymentModal()" class="btn-action btn-pay">
                결제하기
            </button>
            <button onclick="openPrintWindow()" class="btn-action btn-print">
                주문서 인쇄
            </button>
        </div>
        <p style="margin-top: 12px; font-size: 0.9rem; color: var(--text-secondary); text-align: center;">
            결제 방법을 선택하여 진행해주세요. 궁금한 사항은 <strong>1688-2384</strong>로 연락주세요.
        </p>
    </div>

    <!-- 정보 카드들 -->
    <div class="info-cards">
        <!-- 고객 정보 -->
        <div class="info-card">
            <h3>고객 정보</h3>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">성명:</div>
                <div class="info-value"><?php echo htmlspecialchars($name ?: $first_order['name'] ?: '정보없음'); ?></div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">이메일:</div>
                <div class="info-value"><?php echo htmlspecialchars($email ?: $first_order['email'] ?: '정보없음'); ?></div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">연락처:</div>
                <div class="info-value">
                    <?php
                    // 휴대폰이 우선, 없으면 일반전화, 둘 다 없으면 정보없음
                    $phone_display = '';
                    if (!empty($first_order['Hendphone'])) {
                        $phone_display = $first_order['Hendphone'];
                    } elseif (!empty($first_order['phone'])) {
                        $phone_display = $first_order['phone'];
                    } else {
                        $phone_display = '연락처 정보 없음';
                    }
                    echo htmlspecialchars($phone_display);
                    ?>
                </div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">주소:</div>
                <div class="info-value">
                    <?php
                    $address_parts = [];

                    // 우편번호 추가
                    if (!empty($first_order['zip'])) {
                        $address_parts[] = '(' . $first_order['zip'] . ')';
                    }

                    // 주소1, 주소2 추가 (다양한 필드명 시도)
                    $address1 = $first_order['zip1'] ?? $first_order['addr1'] ?? $first_order['address1'] ?? '';
                    $address2 = $first_order['zip2'] ?? $first_order['addr2'] ?? $first_order['address2'] ?? '';

                    if (!empty($address1)) $address_parts[] = $address1;
                    if (!empty($address2)) $address_parts[] = $address2;

                    $address_display = !empty($address_parts) ? implode(' ', $address_parts) : '주소 정보 없음';
                    echo htmlspecialchars($address_display);
                    ?>
                </div>
            </div>
        </div>

        <!-- 입금 안내 -->
        <div class="info-card">
            <h3>입금 안내</h3>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">예금주:</div>
                <div class="info-value">두손기획인쇄 차경선</div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">국민은행:</div>
                <div class="info-value">999-1688-2384</div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">신한은행:</div>
                <div class="info-value">110-342-543507</div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">농협:</div>
                <div class="info-value">301-2632-1830-11</div>
            </div>
            <div class="info-row" style="margin-bottom: 5px;">
                <div class="info-label">카드결제:</div>
                <div class="info-value">1688-2384</div>
            </div>
            <div style="background: #fff3cd; padding: 8px; border-radius: 4px; margin-top: 10px; font-size: 13px; color: #856404;">
                <strong>입금자명을 주문자명(<?php echo htmlspecialchars($name ?: $first_order['name']); ?>)과 동일하게 해주세요</strong>
            </div>
        </div>
    </div>

    <!-- 무통장입금 계좌 안내 섹션 (숨김 상태) -->
    <div id="bankTransferSection" class="bank-transfer-section" style="display:none;">
        <h4>🏦 무통장입금 계좌 안내 <span style="font-size: 0.75rem; font-weight: normal; color: #6c757d;">(계좌번호를 클릭하면 복사)</span></h4>
        <div class="bank-accounts" style="text-align: center;">
            <div class="bank-item-centered">
                <span class="bank-name">국민은행</span>
                <span class="bank-account" onclick="copyToClipboard('999-1688-2384')">999-1688-2384</span>
            </div>
            <div class="bank-item-centered">
                <span class="bank-name">신한은행</span>
                <span class="bank-account" onclick="copyToClipboard('110-342-543507')">110-342-543507</span>
            </div>
            <div class="bank-item-centered">
                <span class="bank-name">농협</span>
                <span class="bank-account" onclick="copyToClipboard('301-2632-1830-11')">301-2632-1830-11</span>
            </div>
        </div>
        <p class="bank-notice">
            예금주: <strong>두손기획인쇄 차경선</strong><br>
            입금자명을 주문자명(<strong><?php echo htmlspecialchars($name ?: $first_order['name']); ?></strong>)과 동일하게 해주세요.<br>
            입금 확인 후 제작이 시작됩니다.
        </p>
    </div>

    <!-- 결제 방법 선택 모달 -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <div class="payment-modal-header">
                <h2 class="modal-brand">두손기획인쇄</h2>
                <button class="modal-close" onclick="closePaymentModal()">&times;</button>
                <h3 class="modal-title">결제 방법 선택</h3>
            </div>
            <div class="payment-modal-body">
                <?php if ($payment_status === 'cancelled'): ?>
                    <div class="payment-cancelled-message">
                        <strong>⚠️ 결제가 취소되었습니다</strong><br>
                        <span style="font-size: 13px; color: #666;">결제를 다시 시도하거나 무통장입금을 이용해주세요.</span>
                    </div>
                <?php endif; ?>

                <div class="payment-amount">
                    결제금액: <strong><?php echo number_format($total_amount_vat); ?>원</strong>
                </div>
                <div class="payment-options">
                    <!-- 옵션 1: 무통장입금 -->
                    <div class="payment-option" onclick="showBankTransfer()">
                        <div class="option-icon">🏦</div>
                        <div class="option-info">
                            <div class="option-title">무통장입금</div>
                            <div class="option-desc">계좌번호 확인 후 직접 입금</div>
                        </div>
                    </div>
                    <!-- 옵션 2: 신용카드 / 실시간 계좌이체 (이니시스) -->
                    <div class="payment-option" onclick="payWithInicis()">
                        <div class="option-icon">💳</div>
                        <div class="option-info">
                            <div class="option-title">신용카드 / 실시간 계좌이체</div>
                            <div class="option-desc">신용카드 또는 실시간 계좌이체로 즉시 결제</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- 인쇄용 푸터 (화면에서는 숨김, 인쇄시에만 표시) -->
    <div class="print-footer" style="display: none;">
        <div class="print-payment-info">
            <h3>입금 계좌 안내</h3>
            <table class="payment-table">
                <tr>
                    <td><strong>국민은행</strong></td>
                    <td>999-1688-2384</td>
                    <td rowspan="3" style="text-align: center; vertical-align: middle;">
                        <strong>예금주: 두손기획인쇄 차경선</strong><br>
                        <span style="font-size: 9pt; color: #666;">입금자명을 주문자명과 동일하게 해주세요</td></tr>
                    </td>
                </tr>
                <tr>
                    <td><strong>신한은행</strong></td>
                    <td>110-342-543507</td>
                </tr>
                <tr>
                    <td><strong>농협</strong></td>
                    <td>301-2632-1830-11</td>
                </tr>
            </table>
        </div>
        <div class="print-contact-notice">
            <p><strong>※ 입금 확인 후 제작이 시작됩니다.</strong></p>
            <p>궁금한 사항은 <strong>02-2632-1830</strong> 또는 <strong>1688-2384</strong>로 연락주세요.</p>
        </div>
    </div>
</div>

<!-- JavaScript (인쇄 및 애니메이션) -->
<script>
// 월스트리트 스타일 주문서 별도 창 열기
function openPrintWindow() {
    // JSON으로 안전하게 데이터 전달
    var orderData = <?php echo json_encode([
        'orders' => $orders ?? '',
        'email' => $email ?? '',
        'name' => $name ?? ''
    ], JSON_UNESCAPED_UNICODE); ?>;

    var printUrl = 'OrderFormPrint.php?orders=' + encodeURIComponent(orderData.orders) +
                   '&email=' + encodeURIComponent(orderData.email) +
                   '&name=' + encodeURIComponent(orderData.name);

    // 새 창으로 주문서 열기
    window.open(printUrl, 'orderPrint', 'width=800,height=900,scrollbars=yes,resizable=yes');
}

// 결제 모달 열기
function openPaymentModal() {
    document.getElementById('paymentModal').style.display = 'flex';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
}

// 결제 모달 닫기
function closePaymentModal() {
    document.getElementById('paymentModal').style.display = 'none';
    document.body.style.overflow = ''; // 스크롤 복원
}

// 이니시스 결제 (신용카드 / 실시간 계좌이체)
function payWithInicis() {
    var orderNo = <?php echo json_encode($first_order['no'] ?? ''); ?>;
    if (orderNo) {
        window.location.href = '/payment/inicis_request.php?order_no=' + encodeURIComponent(orderNo);
    } else {
        alert('주문 정보를 찾을 수 없습니다.');
    }
}

// 무통장입금 정보 표시
function showBankTransfer() {
    closePaymentModal();
    var bankSection = document.getElementById('bankTransferSection');
    bankSection.style.display = 'block';
    // 부드러운 스크롤
    bankSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// 모달 외부 클릭 시 닫기
document.addEventListener('click', function(e) {
    var modal = document.getElementById('paymentModal');
    if (e.target === modal) {
        closePaymentModal();
    }
});

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePaymentModal();
    }
});

// 페이지 로드 애니메이션
document.addEventListener('DOMContentLoaded', function() {
    // 결제 취소/실패 시 자동으로 결제 모달 열기
    var paymentStatus = <?php echo json_encode($payment_status); ?>;
    if (paymentStatus === 'cancelled' || paymentStatus === 'failed') {
        setTimeout(function() {
            openPaymentModal();
        }, 500);

        // URL에서 payment 파라미터 제거 (새로고침 시 메시지 다시 표시 방지)
        if (window.history.replaceState) {
            var newUrl = window.location.pathname + window.location.search.replace(/[?&]payment=[^&]*/, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, newUrl);
        }
    }
    
    // 최초 주문 완료 시에만 자동 이메일 발송 (결제 취소/실패가 아닐 때)
    if (!paymentStatus || (paymentStatus !== 'cancelled' && paymentStatus !== 'failed')) {
        sendOrderEmail();
    }

    // 테이블 행들에 순차적 애니메이션
    const rows = document.querySelectorAll('.order-row');
    rows.forEach((row, index) => {
        setTimeout(() => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // 성공 헤더 펄스 효과
    const header = document.querySelector('.success-header');
    if (header) {
        setTimeout(() => {
            header.style.transform = 'scale(1.02)';
            setTimeout(() => {
                header.style.transform = 'scale(1)';
            }, 200);
        }, 500);
    }
});

// 주문 완료 이메일 자동 발송
function sendOrderEmail() {
    var orderData = {
        orders: '<?php echo addslashes($orders ?? ''); ?>',
        email: '<?php echo addslashes($email ?? ($first_order['email'] ?? '')); ?>',
        name: '<?php echo addslashes($name ?? ($first_order['name'] ?? '')); ?>',
        orderList: <?php echo json_encode($order_list, JSON_UNESCAPED_UNICODE); ?>,
        totalAmount: <?php echo $total_amount ?? 0; ?>,
        totalAmountVat: <?php echo $total_amount_vat ?? 0; ?>
    };
    
    // 이메일이 없으면 발송하지 않음
    if (!orderData.email || orderData.email.indexOf('@') === -1) {
        console.log('이메일 주소가 없어 발송을 건너뜁니다.');
        return;
    }
    
    // 이미 발송된 주문인지 체크 (sessionStorage 사용)
    var emailSentKey = 'email_sent_' + orderData.orders;
    if (sessionStorage.getItem(emailSentKey)) {
        console.log('이미 이메일이 발송된 주문입니다.');
        return;
    }
    
    fetch('send_order_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(orderData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('주문 확인 이메일 발송 완료:', data.message);
            sessionStorage.setItem(emailSentKey, 'true');
        } else {
            console.error('이메일 발송 실패:', data.message);
        }
    })
    .catch(error => {
        console.error('이메일 발송 오류:', error);
    });
}

// 복사 기능 (계좌번호 등)
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('복사되었습니다: ' + text);
    });
}

// 주문 상세 정보 토글
function toggleOrderDetails(orderNo) {
    const details = document.querySelector(`#details_${orderNo}`);
    if (details) {
        details.style.display = details.style.display === 'none' ? 'block' : 'none';
    }
}

console.log('🌟 Universal OrderComplete System Loaded');
console.log('📊 Order Count:', <?php echo count($order_list); ?>);
console.log('Total Amount:', <?php echo $total_amount_vat; ?>);
console.log('🔗 Continue Shopping URL:', '<?php echo addslashes(getLastOrderProductUrl($order_list)); ?>');
<?php
// 디버깅: 마지막 주문 데이터 출력
if (!empty($order_list)) {
    $latest = $order_list[0];
    echo "console.log('Latest Order Type:', '" . addslashes($latest['Type'] ?? 'N/A') . "');";
    if (!empty($latest['Type_1'])) {
        $type1_preview = substr($latest['Type_1'], 0, 200);
        echo "console.log('Type_1 Preview:', '" . addslashes($type1_preview) . "...');";
    }
}
?>
</script>
<script>
(function() {
    function animateNum(el, target, dur) {
        if (!target) { el.textContent = '0'; return; }
        var start = null;
        function ease(t) { return t === 1 ? 1 : 1 - Math.pow(2, -10 * t); }
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            el.textContent = Math.round(ease(p) * target).toLocaleString('ko-KR');
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    document.querySelectorAll('.anim-number').forEach(function(el) {
        animateNum(el, parseInt(el.dataset.target) || 0, 800);
    });
})();
</script>

<?php
// 공통 푸터 포함
include "../includes/footer.php";
?>
