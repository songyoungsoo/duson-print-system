<?php
/**
 * 로젠택배 Excel 내보내기용 JSON API
 * PHP 5.2 호환 버전 (dsp114.com용)
 */

// 출력 버퍼링으로 lib.php의 HTML 출력 캡처
ob_start();
include "lib.php";
$captured = ob_get_clean();

// lib.php 인증 실패 시 <script> 출력됨 (alert)
// 주의: <style>은 인증 성공해도 항상 출력되므로 체크하지 않음
if (strpos($captured, '<script>') !== false) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('success' => false, 'error' => iconv('EUC-KR', 'UTF-8', '로그인이 필요합니다')));
    exit;
}

// JSON은 UTF-8로 출력 (JavaScript에서 정상 파싱 위해)
header('Content-Type: application/json; charset=UTF-8');

$connect = dbconn();
if (!$connect) {
    echo json_encode(array('success' => false, 'error' => 'DB connection failed'));
    exit;
}

// EUC-KR -> UTF-8 변환 함수
function toUtf8($str) {
    if (empty($str)) return '';
    $converted = @iconv('EUC-KR', 'UTF-8//IGNORE', $str);
    return $converted !== false ? $converted : $str;
}

// POST 선택 항목
$selected_nos = isset($_POST['selected_nos']) ? $_POST['selected_nos'] : '';

// 사용자 수정값
$custom_box_qty = array();
$custom_delivery_fee = array();
$custom_fee_type = array();

if (!empty($_POST['box_qty_json'])) {
    $decoded = json_decode($_POST['box_qty_json'], true);
    if (is_array($decoded)) $custom_box_qty = $decoded;
}
if (!empty($_POST['delivery_fee_json'])) {
    $decoded = json_decode($_POST['delivery_fee_json'], true);
    if (is_array($decoded)) $custom_delivery_fee = $decoded;
}
if (!empty($_POST['fee_type_json'])) {
    $decoded = json_decode($_POST['fee_type_json'], true);
    if (is_array($decoded)) $custom_fee_type = $decoded;
}

// GET 검색 파라미터
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건
$where_conditions = array();

if ($selected_nos !== '') {
    $nos_array = array_map('intval', explode(',', $selected_nos));
    $nos_list = implode(',', $nos_array);
    $where_conditions[] = "no IN ($nos_list)";
} else {
    $where_conditions[] = "((zip1 LIKE '%구%') OR (zip2 LIKE '%-%'))";

    if ($search_name !== '') {
        $search_name_esc = mysql_real_escape_string($search_name);
        $where_conditions[] = "name LIKE '%$search_name_esc%'";
    }
    if ($search_company !== '') {
        $search_company_esc = mysql_real_escape_string($search_company);
        $where_conditions[] = "company LIKE '%$search_company_esc%'";
    }
    if ($search_date_start !== '' && $search_date_end !== '') {
        $search_date_start_esc = mysql_real_escape_string($search_date_start);
        $search_date_end_esc = mysql_real_escape_string($search_date_end);
        $where_conditions[] = "date >= '$search_date_start_esc' AND date <= '$search_date_end_esc'";
    } elseif ($search_date_start !== '') {
        $search_date_start_esc = mysql_real_escape_string($search_date_start);
        $where_conditions[] = "date >= '$search_date_start_esc'";
    } elseif ($search_date_end !== '') {
        $search_date_end_esc = mysql_real_escape_string($search_date_end);
        $where_conditions[] = "date <= '$search_date_end_esc'";
    }
    if ($search_no_start !== '' && $search_no_end !== '') {
        $where_conditions[] = "no >= " . intval($search_no_start) . " AND no <= " . intval($search_no_end);
    } elseif ($search_no_start !== '') {
        $where_conditions[] = "no >= " . intval($search_no_start);
    } elseif ($search_no_end !== '') {
        $where_conditions[] = "no <= " . intval($search_no_end);
    }
}

$where_sql = "WHERE " . implode(' AND ', $where_conditions);
$query = "SELECT * FROM MlangOrder_PrintAuto $where_sql ORDER BY no DESC";
$result = mysql_query($query);

if (!$result) {
    echo json_encode(array('success' => false, 'error' => 'Query Error: ' . mysql_error()));
    exit;
}

// 데이터 수집
$rows = array();
while ($data = mysql_fetch_assoc($result)) {
    $order_no = $data['no'];
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // 박스수량/택배비 기본값 계산
    $r = 1; $w = 3000;

    // 수량을 숫자로 추출 (쉼표 제거)
    $qty_num = 0;
    if (preg_match('/수량[:\s]*([\d,]+)/i', $type1_raw, $qty_match)) {
        $qty_num = intval(str_replace(',', '', $qty_match[1]));
    } elseif (preg_match('/([\d,]+)\s*매/i', $type1_raw, $qty_match)) {
        $qty_num = intval(str_replace(',', '', $qty_match[1]));
    }

    // 연 수량 추출 (0.5연, 1연 등)
    $yeon_num = 0;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data_price = json_decode($type1_raw, true);
        if ($json_data_price && isset($json_data_price['MY_amount'])) {
            $yeon_num = floatval($json_data_price['MY_amount']);
        }
    } elseif (preg_match('/([\d.]+)\s*연/i', $type1_raw, $yeon_match)) {
        $yeon_num = floatval($yeon_match[1]);
    } else {
        $lines = preg_split('/[\r\n]+/', trim($type1_raw));
        if (count($lines) >= 5) {
            $fifth_line = trim($lines[4]);
            if (preg_match('/^([\d.]+)$/', $fifth_line, $line_match)) {
                $yeon_num = floatval($line_match[1]);
            }
        }
    }

    // 제품별 택배비 계산
    $type = isset($data['Type']) ? $data['Type'] : '';
    if (preg_match("/NameCard|명함/i", $type)) {
        $r = 1;
        if ($qty_num >= 10000) { $w = 4000; }
        elseif ($qty_num >= 5000) { $w = 3500; }
        else { $w = 3000; }
    } elseif (preg_match("/MerchandiseBond|상품권|쿠폰/i", $type)) {
        $r = 1;
        if ($qty_num >= 10000) { $w = 6000; }
        elseif ($qty_num >= 5000) { $w = 4000; }
        else { $w = 3000; }
    } elseif (preg_match("/sticker|스티커|스티카/i", $type)) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/대봉투|각대봉투/i", $type)) {
        $r = 2; $w = 7000;
    } elseif (preg_match("/소봉투|중봉투|자켓봉투|창봉투|envelop|봉투/i", $type)) {
        $r = 1; $w = 3000;
    } elseif (preg_match("/16절/i", $type1_raw)) {
        $r = 2; $w = 7000;
    } elseif (preg_match("/a4|a5/i", $type1_raw)) {
        if ($yeon_num > 0 && $yeon_num <= 0.5) {
            $r = 1; $w = 3500;
        } else if ($yeon_num >= 1) {
            $r = ceil($yeon_num);
            $w = 6000 * $r;
        } else {
            $r = 1; $w = 6000;
        }
    }

    // DB 저장값 우선 적용
    if (!empty($data['logen_box_qty'])) {
        $r = intval($data['logen_box_qty']);
    }
    if (!empty($data['logen_delivery_fee'])) {
        $w = intval($data['logen_delivery_fee']);
    }
    $fee_type = !empty($data['logen_fee_type']) ? $data['logen_fee_type'] : '착불';

    // 사용자 수정값 적용
    if (!empty($custom_box_qty[$order_no])) {
        $r = intval($custom_box_qty[$order_no]);
    }
    if (!empty($custom_delivery_fee[$order_no])) {
        $w = intval($custom_delivery_fee[$order_no]);
    }
    if (!empty($custom_fee_type[$order_no])) {
        $fee_type = $custom_fee_type[$order_no];
    }

    // 필드 추출 (EUC-KR -> UTF-8 변환)
    $name = isset($data['name']) ? trim($data['name']) : '';
    $zip = isset($data['zip']) ? trim($data['zip']) : '';
    $zip1 = isset($data['zip1']) ? trim($data['zip1']) : '';
    $zip2 = isset($data['zip2']) ? trim($data['zip2']) : '';
    $phone = isset($data['phone']) ? trim($data['phone']) : '';
    $hendphone = isset($data['Hendphone']) ? trim($data['Hendphone']) : '';
    $full_address = trim($zip1 . ' ' . $zip2);

    // Type_1 처리 - 품목명 간소화
    $type_1_display = $type1_raw;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data) {
            $formatted = isset($json_data['formatted_display']) ? $json_data['formatted_display'] : '';

            if (preg_match('/칼라인쇄\s*\(?\s*CMYK/i', $formatted) && preg_match('/아트지/i', $formatted)) {
                $size = '';
                if (preg_match('/규격[:\s]*([A-B]?\d+[절]?|\d+절)/i', $formatted, $m)) {
                    $size = $m[1];
                } elseif (preg_match('/(A4|A5|A3|B4|B5|16절|8절|4절)/i', $formatted, $m)) {
                    $size = $m[1];
                }
                $qty = '';
                if (preg_match('/수량[:\s]*([\d.]+\s*연|[\d,]+\s*매)/i', $formatted, $m)) {
                    $qty = $m[1];
                } elseif (preg_match('/([\d.]+\s*연)/i', $formatted, $m)) {
                    $qty = $m[1];
                }
                $type_1_display = '전단지 ' . $size . ' ' . $qty;
            } else {
                $product_name = '';
                $qty = '';

                if (preg_match('/NameCard/i', $type)) {
                    $product_name = '명함';
                } elseif (preg_match('/sticker|스티커|스티카/i', $type)) {
                    $product_name = '스티커';
                } elseif (preg_match('/envelop|봉투/i', $type)) {
                    if (preg_match('/대봉투|각대봉투/i', $type)) {
                        $product_name = '대봉투';
                    } elseif (preg_match('/소봉투/i', $type)) {
                        $product_name = '소봉투';
                    } elseif (preg_match('/중봉투/i', $type)) {
                        $product_name = '중봉투';
                    } elseif (preg_match('/자켓봉투|자켓소봉투/i', $type)) {
                        $product_name = '자켓봉투';
                    } elseif (preg_match('/창봉투/i', $type)) {
                        $product_name = '창봉투';
                    } elseif (preg_match('/A4봉투/i', $type)) {
                        $product_name = 'A4봉투';
                    } else {
                        $product_name = '봉투';
                    }
                } elseif (preg_match('/cadarok|카다록|카탈로그/i', $type)) {
                    $product_name = '카다록';
                } elseif (preg_match('/leaflet|리플렛/i', $type)) {
                    $product_name = '리플렛';
                } elseif (preg_match('/poster|포스터|littleprint/i', $type)) {
                    $product_name = '포스터';
                } elseif (preg_match('/MerchandiseBond|상품권/i', $type)) {
                    $product_name = '상품권';
                } else {
                    $product_name = $type;
                }

                if (preg_match('/수량[:\s]*([\d,]+\s*(매|장|부|연)?)/i', $formatted, $m)) {
                    $qty = $m[1];
                }

                $type_1_display = trim($product_name . ' ' . $qty);
            }
        }
    } else {
        $product_name = '';
        $qty = '';

        if (preg_match('/칼라인쇄\s*\(?\s*CMYK/i', $type1_raw) && preg_match('/아트지/i', $type1_raw)) {
            $product_name = '전단지';
            $size = '';
            if (preg_match('/(A4|A5|A3|B4|B5|16절|8절|4절)/i', $type1_raw, $m)) {
                $size = $m[1];
            }
            if (preg_match('/([\d.]+\s*연)/i', $type1_raw, $m)) {
                $qty = $m[1];
            } elseif (preg_match('/(?:단면|양면)\s*[\r\n]+\s*([\d.]+)/i', $type1_raw, $m)) {
                $qty = $m[1] . '연';
            } elseif (preg_match('/수량[:\s]*([\d,]+\s*(매|장)?)/i', $type1_raw, $m)) {
                $qty = $m[1];
            }
            $type_1_display = trim('전단지 ' . $size . ' ' . $qty);
        }
        elseif (preg_match('/msticker|자석스티커|자석스티카/i', $type)) {
            $product_name = '자석스티커';
        } elseif (preg_match('/NameCard|명함/i', $type)) {
            $product_name = '명함';
        } elseif (preg_match('/sticker|스티커|스티카/i', $type)) {
            $product_name = '스티커';
        } elseif (preg_match('/envelop|봉투/i', $type)) {
            if (preg_match('/대봉투|각대봉투/i', $type)) {
                $product_name = '대봉투';
            } elseif (preg_match('/소봉투/i', $type)) {
                $product_name = '소봉투';
            } elseif (preg_match('/중봉투/i', $type)) {
                $product_name = '중봉투';
            } elseif (preg_match('/자켓봉투|자켓소봉투/i', $type)) {
                $product_name = '자켓봉투';
            } elseif (preg_match('/창봉투/i', $type)) {
                $product_name = '창봉투';
            } elseif (preg_match('/A4봉투/i', $type)) {
                $product_name = 'A4봉투';
            } else {
                $product_name = '봉투';
            }
        } elseif (preg_match('/cadarok|카다록|카탈로그/i', $type)) {
            $product_name = '카다록';
        } elseif (preg_match('/leaflet|리플렛/i', $type)) {
            $product_name = '리플렛';
        } elseif (preg_match('/poster|포스터|littleprint/i', $type)) {
            $product_name = '포스터';
        } elseif (preg_match('/MerchandiseBond|상품권|쿠폰/i', $type)) {
            $product_name = '상품권';
        } elseif (preg_match('/inserted|전단지/i', $type)) {
            $product_name = '전단지';
        } elseif (preg_match('/NCR|양식지|거래명세서/i', $type)) {
            $product_name = '양식지';
        }

        if (preg_match('/수량[:\s]*([\d,]+\s*(매|장|부|연)?)/i', $type1_raw, $m)) {
            $qty = $m[1];
        }

        if (!empty($product_name)) {
            $type_1_display = trim($product_name . ' ' . $qty);
        } else {
            $type_1_display = $type1_raw;
        }
    }

    // EUC-KR -> UTF-8 변환하여 JSON 배열에 추가
    $rows[] = array(
        'name' => toUtf8($name),
        'zip' => toUtf8($zip),
        'address' => toUtf8($full_address),
        'phone' => toUtf8($phone),
        'hendphone' => toUtf8($hendphone),
        'box_qty' => $r,
        'delivery_fee' => $w,
        'fee_type' => toUtf8($fee_type),
        'product' => toUtf8($type_1_display),
        'etc' => $order_no,
        'message' => toUtf8($type)
    );
}

echo json_encode(array('success' => true, 'data' => $rows));
