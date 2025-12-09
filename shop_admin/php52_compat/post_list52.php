<?php
// PHP 5.2 호환 버전 (dsp114.com용)
include "lib.php";
$connect = dbconn();  // DB 연결 필수!

$DbDir="..";
$GGTABLE="mlangprintauto_transactionCate";
$l[1] = "주문접수";
$l[2] = "입금확인";
$l[3] = "작업중";
$l[4] = "배송중";
$l[0] = "주문취소";

$start = isset($_GET['start']) ? $_GET['start'] : 1;
if(!$start) $start = 1;
$PHP_SELF = $_SERVER['PHP_SELF'];

// 검색 파라미터 받기 (PHP 5.2)
$search_name = isset($_GET['search_name']) ? trim($_GET['search_name']) : '';
$search_company = isset($_GET['search_company']) ? trim($_GET['search_company']) : '';
$search_date_start = isset($_GET['search_date_start']) ? trim($_GET['search_date_start']) : '';
$search_date_end = isset($_GET['search_date_end']) ? trim($_GET['search_date_end']) : '';
$search_no_start = isset($_GET['search_no_start']) ? trim($_GET['search_no_start']) : '';
$search_no_end = isset($_GET['search_no_end']) ? trim($_GET['search_no_end']) : '';

// WHERE 조건 생성
$where_conditions = array();
$where_conditions[] = "(zip1 like '%구%' ) or (zip2 like '%-%')";

if($search_name != '') {
  $search_name_esc = mysql_real_escape_string($search_name);
  $where_conditions[] = "name like '%$search_name_esc%'";
}

if($search_company != '') {
  $search_company_esc = mysql_real_escape_string($search_company);
  $where_conditions[] = "company like '%$search_company_esc%'";
}

if($search_date_start != '' && $search_date_end != '') {
  $search_date_start_esc = mysql_real_escape_string($search_date_start);
  $search_date_end_esc = mysql_real_escape_string($search_date_end);
  $where_conditions[] = "date >= '$search_date_start_esc' and date <= '$search_date_end_esc'";
} else if($search_date_start != '') {
  $search_date_start_esc = mysql_real_escape_string($search_date_start);
  $where_conditions[] = "date >= '$search_date_start_esc'";
} else if($search_date_end != '') {
  $search_date_end_esc = mysql_real_escape_string($search_date_end);
  $where_conditions[] = "date <= '$search_date_end_esc'";
}

// 주문번호 범위 검색 추가
if($search_no_start != '' && $search_no_end != '') {
  $search_no_start = intval($search_no_start);
  $search_no_end = intval($search_no_end);
  $where_conditions[] = "no >= $search_no_start and no <= $search_no_end";
} else if($search_no_start != '') {
  $search_no_start = intval($search_no_start);
  $where_conditions[] = "no >= $search_no_start";
} else if($search_no_end != '') {
  $search_no_end = intval($search_no_end);
  $where_conditions[] = "no <= $search_no_end";
}

$where_sql = implode(' and ', $where_conditions);

// 전체 페이지 구하기
$query = "select count(*) from MlangOrder_PrintAuto where $where_sql";
$result = mysql_query($query);
if (!$result) {
    die("Query Error: " . mysql_error() . "<br>Query: " . $query);
}
$data = mysql_fetch_array($result);
$total = $data[0];

// 한화면에 표시될 페이지수
$pagenum = 20;

// 총페이지수
$pages = round($total / $pagenum);

// 시작변수
$s = $pagenum * ($start-1);

// 검색 파라미터를 URL에 추가하기 위한 변수
$search_params = '';
if($search_name != '') $search_params .= "&search_name=" . urlencode($search_name);
if($search_company != '') $search_params .= "&search_company=" . urlencode($search_company);
if($search_date_start != '') $search_params .= "&search_date_start=" . urlencode($search_date_start);
if($search_date_end != '') $search_params .= "&search_date_end=" . urlencode($search_date_end);
if($search_no_start != '') $search_params .= "&search_no_start=" . urlencode($search_no_start);
if($search_no_end != '') $search_params .= "&search_no_end=" . urlencode($search_no_end);

$query = "select * from MlangOrder_PrintAuto where $where_sql order by no desc";
$query .= " limit $s, $pagenum ";
$result = mysql_query($query);
if (!$result) {
    die("<br>Query Error: " . mysql_error() . "<br>Query: " . $query);
}

$next = $start + 1;
$prev = $start - 1;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="EUC-KR">
<title>로젠 주소 추출 (PHP 5.2)</title>
<style>
body { margin-left: 0px; }
td { font-size: 9pt; }
a { text-decoration: none; color: #333; }
a:hover { color: #0066FF; }
input[type=text], select { font-size: 9pt; }
.search-row td { padding: 3px; }
table { border-collapse: collapse; }
table td, table th { border: 1px solid #ccc; }
.btn-logen {
    background-color: #03C75A;
    color: white;
    border: none;
    padding: 5px 15px;
    border-radius: 4px;
    cursor: pointer;
    margin-left: 5px;
}
.btn-logen:hover {
    background-color: #02a849;
}
</style>
<!-- SheetJS CDN for real xlsx export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>

<li> 총 게시물수 : <?php echo $total ?>

<!-- 검색 폼 추가 -->
<form method="get" action="<?php echo $PHP_SELF?>" id="searchForm">
<table border="1" cellpadding="5" cellspacing="0" style="margin-bottom:10px;">
  <tr class="search-row">
    <td>이름:</td>
    <td><input type="text" name="search_name" value="<?php echo htmlspecialchars($search_name)?>" size="10"></td>
    <td>상호:</td>
    <td><input type="text" name="search_company" value="<?php echo htmlspecialchars($search_company)?>" size="15"></td>
    <td>날짜:</td>
    <td>
      <input type="text" name="search_date_start" value="<?php echo htmlspecialchars($search_date_start)?>" size="10" placeholder="YYYY-MM-DD">
      ~
      <input type="text" name="search_date_end" value="<?php echo htmlspecialchars($search_date_end)?>" size="10" placeholder="YYYY-MM-DD">
    </td>
    <td>주문번호:</td>
    <td>
      <input type="text" name="search_no_start" value="<?php echo htmlspecialchars($search_no_start)?>" size="8" placeholder="시작">
      ~
      <input type="text" name="search_no_end" value="<?php echo htmlspecialchars($search_no_end)?>" size="8" placeholder="끝">
    </td>
    <td><input type="submit" value="검색"></td>
    <td><input type="button" value="초기화" onclick="location.href='<?php echo $PHP_SELF?>'"></td>
  </tr>
</table>
</form>

<script type="text/javascript">
function toggleAll(source) {
  var checkboxes = document.getElementsByName('selected_no[]');
  for(var i=0; i<checkboxes.length; i++) {
    checkboxes[i].checked = source.checked;
  }
}

// 로젠택배 엑셀 양식 다운로드 함수 (SheetJS)
function exportSelectedToLogenExcel() {
  var checkboxes = document.getElementsByName('selected_no[]');
  var selected = [];
  var boxQty = {};
  var deliveryFee = {};
  var feeType = {};

  for(var i=0; i<checkboxes.length; i++) {
    if(checkboxes[i].checked) {
      var no = checkboxes[i].value;
      selected.push(no);
      var qtyInput = document.getElementsByName('box_qty[' + no + ']')[0];
      var feeInput = document.getElementsByName('delivery_fee[' + no + ']')[0];
      var typeSelect = document.getElementsByName('fee_type[' + no + ']')[0];
      if(qtyInput) boxQty[no] = qtyInput.value;
      if(feeInput) deliveryFee[no] = feeInput.value;
      if(typeSelect) feeType[no] = typeSelect.value;
    }
  }

  if(selected.length === 0) {
    alert('다운로드할 항목을 선택해주세요.');
    return;
  }

  // JSON API 호출하여 데이터 가져오기
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'export_logen_json.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if(xhr.readyState === 4 && xhr.status === 200) {
      try {
        var response = JSON.parse(xhr.responseText);
        if(response.success && response.data) {
          generateXlsx(response.data);
        } else {
          alert('데이터를 가져오는데 실패했습니다.');
        }
      } catch(e) {
        alert('응답 파싱 오류: ' + e.message);
      }
    }
  };
  var postData = 'selected_nos=' + encodeURIComponent(selected.join(','));
  postData += '&box_qty_json=' + encodeURIComponent(jsonStringify(boxQty));
  postData += '&delivery_fee_json=' + encodeURIComponent(jsonStringify(deliveryFee));
  postData += '&fee_type_json=' + encodeURIComponent(jsonStringify(feeType));
  xhr.send(postData);
}

function exportAllToLogenExcel() {
  var searchParams = window.location.search;
  var xhr = new XMLHttpRequest();
  xhr.open('GET', 'export_logen_json.php' + searchParams, true);
  xhr.onreadystatechange = function() {
    if(xhr.readyState === 4 && xhr.status === 200) {
      try {
        var response = JSON.parse(xhr.responseText);
        if(response.success && response.data) {
          generateXlsx(response.data);
        } else {
          alert('데이터를 가져오는데 실패했습니다.');
        }
      } catch(e) {
        alert('응답 파싱 오류: ' + e.message);
      }
    }
  };
  xhr.send();
}

// SheetJS로 실제 xlsx 파일 생성
function generateXlsx(data) {
  var headers = ['수하인명', '우편번호', '주소', '전화', '핸드폰', '박스수량', '택배비', '운임구분', '품목명', '기타', '배송메세지'];
  var wsData = [headers];
  for(var i=0; i<data.length; i++) {
    var row = data[i];
    wsData.push([
      row.name || '',
      row.zip || '',
      row.address || '',
      row.phone || '',
      row.hendphone || '',
      parseInt(row.box_qty) || 1,
      parseInt(row.delivery_fee) || 3000,
      row.fee_type || '착불',
      row.product || '',
      row.etc || '',
      row.message || ''
    ]);
  }

  var ws = XLSX.utils.aoa_to_sheet(wsData);
  var wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');

  var now = new Date();
  var filename = 'logen_' + now.getFullYear() +
    ('0' + (now.getMonth()+1)).slice(-2) +
    ('0' + now.getDate()).slice(-2) + '_' +
    ('0' + now.getHours()).slice(-2) +
    ('0' + now.getMinutes()).slice(-2) + '.xlsx';

  XLSX.writeFile(wb, filename);
}

// 간단한 JSON.stringify 대체
function jsonStringify(obj) {
  var parts = [];
  for (var key in obj) {
    if (obj.hasOwnProperty(key)) {
      parts.push('"' + key + '":"' + obj[key] + '"');
    }
  }
  return '{' + parts.join(',') + '}';
}

function getTimestamp() {
  var d = new Date();
  var yyyy = d.getFullYear();
  var mm = ('0' + (d.getMonth() + 1)).slice(-2);
  var dd = ('0' + d.getDate()).slice(-2);
  var hh = ('0' + d.getHours()).slice(-2);
  var mi = ('0' + d.getMinutes()).slice(-2);
  var ss = ('0' + d.getSeconds()).slice(-2);
  return yyyy + mm + dd + '_' + hh + mi + ss;
}

// 로젠 설정값 자동 저장 (박스수량, 택배비, 운임구분)
function saveLogenSetting(orderNo, field, value) {
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'save_logen_settings.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        try {
          var response = JSON.parse(xhr.responseText);
          if (response.success) {
            var inputName = field === 'fee_type'
              ? 'select[name="fee_type[' + orderNo + ']"]'
              : 'input[name="' + field + '[' + orderNo + ']"]';
            var element = document.querySelector(inputName);
            if (element) {
              element.style.backgroundColor = '#90EE90';
              setTimeout(function() {
                element.style.backgroundColor = '';
              }, 500);
            }
          } else {
            alert('저장 실패: ' + (response.error || '알 수 없는 오류'));
          }
        } catch(e) {
          // JSON 파싱 오류 무시
        }
      }
    }
  };

  var postData = 'order_no=' + encodeURIComponent(orderNo);
  postData += '&field=' + encodeURIComponent(field);
  postData += '&value=' + encodeURIComponent(value);
  xhr.send(postData);
}
</script>

<form id="listForm">
<table width="100%" border="1" cellpadding="2" cellspacing="0">
  <tr bgcolor="#99CCFF">
    <td><input type="checkbox" onclick="toggleAll(this)"></td>
    <td> 주문번호
    <td> 날짜
    <td> 이름
    <td> 우편번호
    <td> 주소
    <td> 전화
    <td> 핸드폰
    <td> 박스수량
    <td> 택배비
    <td> 운임구분
    <td> 품목
    <td> 기타
    <td> Type
  </tr>

<?php
  $row_count = 0;
  while($data = mysql_fetch_array($result)){
    // Type_1이 JSON인지 확인하고 파싱
    $type1_display = isset($data['Type_1']) ? $data['Type_1'] : '';
    $type1_raw = isset($data['Type_1']) ? $data['Type_1'] : '';

    // 품목명 간소화 처리
    $type = isset($data['Type']) ? trim($data['Type']) : '';

    if (!empty($data['Type_1']) && substr(trim($data['Type_1']), 0, 1) === '{') {
        $json_data = json_decode($data['Type_1'], true);
        if ($json_data) {
            $formatted = isset($json_data['formatted_display']) ? $json_data['formatted_display'] : '';

            // 전단지 판별: 칼라인쇄(CMYK) + 아트지
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
                $type1_display = htmlspecialchars('전단지 ' . $size . ' ' . $qty);
            } else {
                // 기타 제품: 품명 + 수량만 표시
                $product_name = '';
                $qty = '';

                // Type에서 제품명 추출
                if (preg_match('/NameCard/i', $type)) {
                    $product_name = '명함';
                } elseif (preg_match('/msticker|자석스티커|자석스티카/i', $type)) {
                    $product_name = '자석스티커';
                } elseif (preg_match('/sticker|스티커|스티카/i', $type)) {
                    $product_name = '스티커';
                } elseif (preg_match('/envelop|봉투/i', $type)) {
                    // 봉투 세분화
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

                $type1_display = htmlspecialchars(trim($product_name . ' ' . $qty));
            }
        }
    } else {
        // JSON이 아닌 경우
        $product_name = '';
        $qty = '';

        // 1순위: Type_1에서 "칼라인쇄(CMYK)" + "아트지" 패턴 → 전단지
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
            $type1_display = htmlspecialchars(trim('전단지 ' . $size . ' ' . $qty));
        }
        // 2순위: Type에서 제품명 추출
        elseif (preg_match('/msticker|자석스티커|자석스티카/i', $type)) {
            $product_name = '자석스티커';
        } elseif (preg_match('/NameCard|명함/i', $type)) {
            $product_name = '명함';
        } elseif (preg_match('/sticker|스티커|스티카/i', $type)) {
            $product_name = '스티커';
        } elseif (preg_match('/envelop|봉투/i', $type)) {
            // 봉투 세분화
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
        if (empty($qty) && preg_match('/수량[:\s]*([\d,]+\s*(매|장|부|연)?)/i', $type1_raw, $m)) {
            $qty = $m[1];
        }

        // 품명이 추출되었으면 간소화된 형태로
        if (!empty($product_name) && empty($type1_display)) {
            $type1_display = htmlspecialchars(trim($product_name . ' ' . $qty));
        } elseif (empty($type1_display)) {
            $type1_display = htmlspecialchars($type1_raw);
        }
    }
?>
<?php
// 박스수량/택배비/운임구분 - DB 저장값 우선, 없으면 자동 계산
$r = 1; $w = 3000; $ft = '착불'; // 기본값

// 수량을 숫자로 추출 (쉼표 제거)
$qty_num = 0;
if (preg_match('/수량[:\s]*([\d,]+)/i', $type1_raw, $qty_match)) {
    $qty_num = intval(str_replace(',', '', $qty_match[1]));
} elseif (preg_match('/([\d,]+)\s*매/i', $type1_raw, $qty_match)) {
    $qty_num = intval(str_replace(',', '', $qty_match[1]));
}

// 연 수량 추출 (0.5연, 1연 등)
$yeon_num = 0;
// JSON인 경우 MY_amount에서 직접 추출
if (!empty($type1_raw) && substr(trim($type1_raw), 0, 1) === '{') {
    $json_data = json_decode($type1_raw, true);
    if ($json_data && isset($json_data['MY_amount'])) {
        $yeon_num = floatval($json_data['MY_amount']);
    }
} elseif (preg_match('/([\d.]+)\s*연/i', $type1_raw, $yeon_match)) {
    // "6연" 형식에서 추출
    $yeon_num = floatval($yeon_match[1]);
} else {
    // 줄바꿈 구분 텍스트에서 5번째 줄 (연 수량) 추출
    // 형식: 인쇄방식 / 용지 / 규격 / 단면양면 / 연수량 / 주문타입
    $lines = preg_split('/[\r\n]+/', trim($type1_raw));
    if (count($lines) >= 5) {
        $fifth_line = trim($lines[4]);
        // 숫자(소수점 포함)만 추출
        if (preg_match('/^([\d.]+)$/', $fifth_line, $line_match)) {
            $yeon_num = floatval($line_match[1]);
        }
    }
}

// 제품별 택배비 계산 (2025-12-05 업데이트)
if (preg_match("/NameCard|명함/i", isset($data['Type']) ? $data['Type'] : '')) {
    // 명함: 1박스, 기본 3000원 (5000매 3500원, 10000매 4000원)
    $r = 1;
    if ($qty_num >= 10000) {
        $w = 4000;
    } elseif ($qty_num >= 5000) {
        $w = 3500;
    } else {
        $w = 3000;
    }
} elseif (preg_match("/MerchandiseBond|상품권|쿠폰/i", isset($data['Type']) ? $data['Type'] : '')) {
    // 상품권: 1박스, 기본 3000원 (5000매 4000원, 10000매 6000원)
    $r = 1;
    if ($qty_num >= 10000) {
        $w = 6000;
    } elseif ($qty_num >= 5000) {
        $w = 4000;
    } else {
        $w = 3000;
    }
} elseif (preg_match("/sticker|스티커|스티카/i", isset($data['Type']) ? $data['Type'] : '')) {
    // 스티커: 1박스, 3000원
    $r = 1; $w = 3000;
} elseif (preg_match("/대봉투|각대봉투/i", isset($data['Type']) ? $data['Type'] : '')) {
    // 대봉투: 2박스, 7000원
    $r = 2; $w = 7000;
} elseif (preg_match("/소봉투|중봉투|자켓봉투|창봉투|envelop|봉투/i", isset($data['Type']) ? $data['Type'] : '')) {
    // 소봉투류: 1박스, 3000원
    $r = 1; $w = 3000;
} elseif (preg_match("/16절/i", $type1_raw)) {
    // 16절: 2박스, 7000원
    $r = 2; $w = 7000;
} elseif (preg_match("/a4|a5/i", $type1_raw)) {
    // A4/A5: 0.5연 이하 1박스 3500원, 1연 당 1박스 6000원
    if ($yeon_num > 0 && $yeon_num <= 0.5) {
        $r = 1;
        $w = 3500;
    } else if ($yeon_num >= 1) {
        $r = ceil($yeon_num);  // 연 수량 = 박스 수량
        $w = 6000 * $r;        // 박스당 6000원
    } else {
        $r = 1;
        $w = 6000;
    }
}

// DB 저장값이 있으면 덮어쓰기
if (!empty($data['logen_box_qty'])) {
    $r = intval($data['logen_box_qty']);
}
if (!empty($data['logen_delivery_fee'])) {
    $w = intval($data['logen_delivery_fee']);
}
if (!empty($data['logen_fee_type'])) {
    $ft = $data['logen_fee_type'];
}
?>
  <tr>
    <td><input type="checkbox" name="selected_no[]" value="<?php echo $data['no']?>"></td>
    <td><?php echo htmlspecialchars(isset($data['no']) ? $data['no'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['date']) ? $data['date'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['name']) ? $data['name'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['zip']) ? $data['zip'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['zip1']) ? $data['zip1'] : '')?> <?php echo htmlspecialchars(isset($data['zip2']) ? $data['zip2'] : '')?></td>
    <td><?php echo htmlspecialchars(isset($data['phone']) ? $data['phone'] : '')?></td>
    <td width="120"><a href="http://www.webhard.co.kr/webII/page/sms/main_sms.php"><?php echo htmlspecialchars(isset($data['Hendphone']) ? $data['Hendphone'] : '')?></a></td>
    <td align='center'><input type="text" name="box_qty[<?php echo $data['no']?>]" value="<?php echo $r; ?>" size="2" style="text-align:center;" onchange="saveLogenSetting(<?php echo $data['no']?>, 'box_qty', this.value)"></td>
    <td><input type="text" name="delivery_fee[<?php echo $data['no']?>]" value="<?php echo $w; ?>" size="5" onchange="saveLogenSetting(<?php echo $data['no']?>, 'delivery_fee', this.value)"></td>
    <td><select name="fee_type[<?php echo $data['no']?>]" style="font-size:9pt;" onchange="saveLogenSetting(<?php echo $data['no']?>, 'fee_type', this.value)">
      <option value="착불"<?php if($ft=='착불') echo ' selected';?>>착불</option>
      <option value="신용"<?php if($ft=='신용') echo ' selected';?>>신용</option>
      <option value="퀵"<?php if($ft=='퀵') echo ' selected';?>>퀵</option>
    </select></td>
    <td><?php echo $type1_display?></td>
    <td>&nbsp;</td>
    <td><?php echo htmlspecialchars(isset($data['Type']) ? $data['Type'] : '')?></td>
  </tr>
  <?php

 } ?>
</table>
</form>

<div style="margin:10px 0;">
  <button type="button" class="btn-logen" onclick="exportSelectedToLogenExcel()">선택항목 로젠 엑셀 다운로드</button>
  <button type="button" class="btn-logen" onclick="exportAllToLogenExcel()">전체 로젠 엑셀 다운로드</button>
</div>

맨앞
<a href="<?php echo $PHP_SELF?>?start=1<?php echo $search_params?>">맨앞</a>

<?php if($start>1){ ?>
<a href="<?php echo $PHP_SELF?>?start=<?php echo $prev?><?php echo $search_params?>">[이전]</a>
<?php } ?>

<?php
for($i=$start; $i<=$pages; $i++){
  if($i<($start+10))
  echo "<a href=\"".$PHP_SELF."?start=".$i.$search_params."\">[".$i."]</a> ";
}
?>

<?php if($next!=$pages){ ?>
<a href="<?php echo $PHP_SELF?>?start=<?php echo $next?><?php echo $search_params?>">[다음]</a>
<?php } ?>

<a href="<?php echo $PHP_SELF?>?start=<?php echo $pages?><?php echo $search_params?>">맨끝</a>

</body>
</html>
