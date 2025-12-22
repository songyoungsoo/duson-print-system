<?php
/**
 * 로젠택배 Excel 내보내기용 JSON API
 * PHP 7.4+ / mysqli 버전
 */
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../db.php';

// POST 선택 항목
$selected_nos = $_POST['selected_nos'] ?? '';

// 사용자 수정값
$custom_box_qty = [];
$custom_delivery_fee = [];
$custom_fee_type = [];

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
$search_name = trim($_GET['search_name'] ?? '');
$search_company = trim($_GET['search_company'] ?? '');
$search_date_start = trim($_GET['search_date_start'] ?? '');
$search_date_end = trim($_GET['search_date_end'] ?? '');
$search_no_start = trim($_GET['search_no_start'] ?? '');
$search_no_end = trim($_GET['search_no_end'] ?? '');

// WHERE 조건
$where_conditions = [];
$params = [];
$types = '';

if ($selected_nos !== '') {
    $nos_array = array_map('intval', explode(',', $selected_nos));
    $placeholders = implode(',', array_fill(0, count($nos_array), '?'));
    $where_conditions[] = "no IN ($placeholders)";
    foreach ($nos_array as $no) {
        $params[] = $no;
        $types .= 'i';
    }
} else {
    $where_conditions[] = "((zip1 LIKE '%구%') OR (zip2 LIKE '%-%'))";

    if ($search_name !== '') {
        $where_conditions[] = "name LIKE ?";
        $params[] = "%$search_name%";
        $types .= 's';
    }
    if ($search_company !== '') {
        $where_conditions[] = "company LIKE ?";
        $params[] = "%$search_company%";
        $types .= 's';
    }
    if ($search_date_start !== '' && $search_date_end !== '') {
        $where_conditions[] = "date >= ? AND date <= ?";
        $params[] = $search_date_start;
        $params[] = $search_date_end;
        $types .= 'ss';
    } elseif ($search_date_start !== '') {
        $where_conditions[] = "date >= ?";
        $params[] = $search_date_start;
        $types .= 's';
    } elseif ($search_date_end !== '') {
        $where_conditions[] = "date <= ?";
        $params[] = $search_date_end;
        $types .= 's';
    }
    if ($search_no_start !== '' && $search_no_end !== '') {
        $where_conditions[] = "no >= ? AND no <= ?";
        $params[] = intval($search_no_start);
        $params[] = intval($search_no_end);
        $types .= 'ii';
    } elseif ($search_no_start !== '') {
        $where_conditions[] = "no >= ?";
        $params[] = intval($search_no_start);
        $types .= 'i';
    } elseif ($search_no_end !== '') {
        $where_conditions[] = "no <= ?";
        $params[] = intval($search_no_end);
        $types .= 'i';
    }
}

$where_sql = "WHERE " . implode(' AND ', $where_conditions);
$query = "SELECT * FROM mlangorder_printauto $where_sql ORDER BY no DESC";

$stmt = mysqli_prepare($db, $query);
if ($stmt && !empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    // 파라미터 없는 경우 직접 실행
    $result = mysqli_query($db, $query);
}

// 데이터 수집
$rows = [];
while ($data = mysqli_fetch_assoc($result)) {
    $order_no = $data['no'];
    $type1_raw = $data['Type_1'] ?? '';

    // ========================================
    // 연 수량 추출 (3가지 형식 지원)
    // ========================================
    $yeon_num = 0;

    // 형식 1: JSON에서 MY_amount 추출
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data && isset($json_data['MY_amount'])) {
            $yeon_num = floatval($json_data['MY_amount']);
        }
    }
    // 형식 2: "X연" 패턴 매칭
    elseif (preg_match('/([\d.]+)\s*연/i', $type1_raw, $yeon_match)) {
        $yeon_num = floatval($yeon_match[1]);
    }
    // 형식 3: 줄바꿈 구분 텍스트에서 5번째 줄 추출
    else {
        $lines = preg_split('/[\r\n]+/', trim($type1_raw));
        if (count($lines) >= 5) {
            $fifth_line = trim($lines[4]);
            if (preg_match('/^([\d.]+)$/', $fifth_line, $line_match)) {
                $yeon_num = floatval($line_match[1]);
            }
        }
    }

    // ========================================
    // 박스수량/택배비 기본값 계산
    // ========================================
    $r = 1; $w = 3000;
    $product_type = $data['Type'] ?? '';

    // 제품별 기본 규칙
    if (preg_match("/NameCard/i", $product_type)) {
        $r = 1; $w = 2500;
    } elseif (preg_match("/MerchandiseBond/i", $product_type)) {
        $r = 1; $w = 2500;
    } elseif (preg_match("/sticker/i", $product_type)) {
        $r = 1; $w = 2500;
    } elseif (preg_match("/envelop/i", $product_type)) {
        $r = 1; $w = 3000;
    }
    // 전단지/리플렛 규격별 택배비
    elseif (preg_match("/inserted|전단지|leaflet|리플렛/i", $product_type)) {
        // 16절/B5 규격
        if (preg_match("/16절|b5/i", $type1_raw)) {
            $r = 2; $w = 3000;
        }
        // A4/A5 규격 - 연 수량 기반 계산
        elseif (preg_match("/a4|a5/i", $type1_raw)) {
            if ($yeon_num > 0 && $yeon_num <= 0.5) {
                // 0.5연 이하: 1박스, 3,500원
                $r = 1; $w = 3500;
            } elseif ($yeon_num >= 1) {
                // 1연 이상: 연 수량 = 박스 수량, 박스당 6,000원
                $r = ceil($yeon_num);
                $w = 6000 * $r;
            } else {
                // 기본값
                $r = 1; $w = 6000;
            }
        }
        // 기타 전단지
        else {
            $r = 1; $w = 3000;
        }
    }

    // DB 저장값 우선 적용 (logen_ 컬럼)
    if (!empty($data['logen_box_qty'])) {
        $r = intval($data['logen_box_qty']);
    }
    if (!empty($data['logen_delivery_fee'])) {
        $w = intval($data['logen_delivery_fee']);
    }
    $fee_type = !empty($data['logen_fee_type']) ? $data['logen_fee_type'] : '착불';

    // 사용자 수정값 적용 (저장 전 임시 변경값)
    if (!empty($custom_box_qty[$order_no])) {
        $r = intval($custom_box_qty[$order_no]);
    }
    if (!empty($custom_delivery_fee[$order_no])) {
        $w = intval($custom_delivery_fee[$order_no]);
    }
    if (!empty($custom_fee_type[$order_no])) {
        $fee_type = $custom_fee_type[$order_no];
    }

    // 필드 추출
    $name = trim($data['name'] ?? '');
    $zip = trim($data['zip'] ?? '');
    $zip1 = trim($data['zip1'] ?? '');
    $zip2 = trim($data['zip2'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $hendphone = trim($data['Hendphone'] ?? '');
    $type = trim($data['Type'] ?? '');
    $full_address = trim($zip1 . ' ' . $zip2);

    // Type_1 처리 - 품목명 간소화
    $type_1_display = $type1_raw;
    if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
        $json_data = json_decode($type1_raw, true);
        if ($json_data) {
            $formatted = $json_data['formatted_display'] ?? '';

            // 전단지 판별: 칼라인쇄(CMYK) + 아트지
            if (preg_match('/칼라인쇄\s*\(?\s*CMYK/i', $formatted) && preg_match('/아트지/i', $formatted)) {
                // 규격 추출 (A4, A5, B5, 16절 등)
                $size = '';
                if (preg_match('/규격[:\s]*([A-B]?\d+[절]?|\d+절)/i', $formatted, $m)) {
                    $size = $m[1];
                } elseif (preg_match('/(A4|A5|A3|B4|B5|16절|8절|4절)/i', $formatted, $m)) {
                    $size = $m[1];
                }

                // 수량 추출 (0.5연, 1연, 2연 등)
                $qty = '';
                if (preg_match('/수량[:\s]*([\d.]+\s*연|[\d,]+\s*매)/i', $formatted, $m)) {
                    $qty = $m[1];
                } elseif (preg_match('/([\d.]+\s*연)/i', $formatted, $m)) {
                    $qty = $m[1];
                }

                $type_1_display = '전단지 ' . $size . ' ' . $qty;
            } else {
                // 기타 제품: 품명 + 수량만 표시
                $product_name = '';
                $qty = '';

                // Type에서 제품명 추출
                if (preg_match('/NameCard/i', $type)) {
                    $product_name = '명함';
                } elseif (preg_match('/sticker|스티커|스티카/i', $type)) {
                    $product_name = '스티커';
                } elseif (preg_match('/envelop|봉투/i', $type)) {
                    // 봉투 세분화: 대봉투, 소봉투, 중봉투, 자켓봉투 등
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

                // 수량 추출
                if (preg_match('/수량[:\s]*([\d,]+\s*(매|장|부|연)?)/i', $formatted, $m)) {
                    $qty = $m[1];
                }

                $type_1_display = trim($product_name . ' ' . $qty);
            }
        }
    } else {
        // JSON이 아닌 경우 - Type_1 내용 기반으로 품명 추출 및 간소화
        $product_name = '';
        $qty = '';

        // ⚠️ 1순위: Type_1에서 "칼라인쇄(CMYK)" + "아트지" 패턴 → 전단지
        if (preg_match('/칼라인쇄\s*\(?\s*CMYK/i', $type1_raw) && preg_match('/아트지/i', $type1_raw)) {
            $product_name = '전단지';

            // 규격 추출 (A4, A5, B5, 16절 등)
            $size = '';
            if (preg_match('/(A4|A5|A3|B4|B5|16절|8절|4절)/i', $type1_raw, $m)) {
                $size = $m[1];
            }

            // 수량 추출 (여러 패턴 시도)
            if (preg_match('/([\d.]+\s*연)/i', $type1_raw, $m)) {
                $qty = $m[1];
            } elseif (preg_match('/(?:단면|양면)\s*[\r\n]+\s*([\d.]+)/i', $type1_raw, $m)) {
                // 단면/양면 다음 줄의 숫자 = 연 수량
                $qty = $m[1] . '연';
            } elseif (preg_match('/수량[:\s]*([\d,]+\s*(매|장)?)/i', $type1_raw, $m)) {
                $qty = $m[1];
            }

            $type_1_display = trim('전단지 ' . $size . ' ' . $qty);
        }
        // 2순위: Type에서 제품명 추출 (영문 + 한글 패턴 - 구체적인 것 먼저!)
        // ⚠️ 자석스티커를 스티커보다 먼저 체크해야 함 (자석스티커에 "스티커"가 포함됨)
        elseif (preg_match('/msticker|자석스티커|자석스티카/i', $type)) {
            $product_name = '자석스티커';
        } elseif (preg_match('/NameCard|명함/i', $type)) {
            $product_name = '명함';
        } elseif (preg_match('/sticker|스티커|스티카/i', $type)) {
            $product_name = '스티커';
        } elseif (preg_match('/envelop|봉투/i', $type)) {
            // 봉투 세분화: 대봉투, 소봉투, 중봉투, 자켓봉투 등
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

        // Type_1에서 수량 추출
        if (preg_match('/수량[:\s]*([\d,]+\s*(매|장|부|연)?)/i', $type1_raw, $m)) {
            $qty = $m[1];
        }

        // 품명이 추출되었으면 간소화된 형태로, 아니면 원본 유지
        if (!empty($product_name)) {
            $type_1_display = trim($product_name . ' ' . $qty);
        } else {
            $type_1_display = $type1_raw;
        }
    }

    $rows[] = [
        'name' => $name,
        'zip' => $zip,
        'address' => $full_address,
        'phone' => $phone,
        'hendphone' => $hendphone,
        'box_qty' => $r,
        'delivery_fee' => $w,
        'fee_type' => $fee_type,
        'product' => $type_1_display,
        'etc' => $order_no,  // 주문번호
        'message' => $type
    ];
}

echo json_encode(['success' => true, 'data' => $rows], JSON_UNESCAPED_UNICODE);
