<?php
// 보안 상수 정의 후 데이터베이스 연결
include "../../includes/db_constants.php";
include "../../db.php";

// 추가 옵션 표시 시스템 포함
if (file_exists('../../includes/AdditionalOptionsDisplay.php')) {
    include_once '../../includes/AdditionalOptionsDisplay.php';
}

// db.php에서 생성된 $db 연결을 사용
$mysqli = $db;
if (!$mysqli) {
    die("Connection failed: Database connection not established");
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? null;
$mode = isset($_REQUEST['mode']) ? $_REQUEST['mode'] : '';
$check = isset($_REQUEST['check']) ? $_REQUEST['check'] : [];
$no = isset($_REQUEST['no']) ? $_REQUEST['no'] : '';
$YearOne = isset($_REQUEST['YearOne']) ? $_REQUEST['YearOne'] : '';
$YearTwo = isset($_REQUEST['YearTwo']) ? $_REQUEST['YearTwo'] : '';
$Type = isset($_REQUEST['Type']) ? $_REQUEST['Type'] : '';
$Cate = isset($_REQUEST['Cate']) ? $_REQUEST['Cate'] : '';
$TDsearchValue = isset($_REQUEST['TDsearchValue']) ? $_REQUEST['TDsearchValue'] : '';
$offset = isset($_REQUEST['offset']) ? intval($_REQUEST['offset']) : 0;
$CountWW = isset($_REQUEST['CountWW']) ? $_REQUEST['CountWW'] : '';
$ModifyCode = isset($_REQUEST['ModifyCode']) ? $_REQUEST['ModifyCode'] : ''; // ModifyCode 변수 추가
$JK = isset($_GET['JK']) ? $_GET['JK'] : ''; // GET 방식으로 전달되는 경우
$PHP_SELF   = $_SERVER['PHP_SELF'] ?? '';

if ($mode === "ChickBoxAll") {
    $check = $_POST['check'] ?? [];

    if (empty($check)) {
        echo "<script>
            alert('삭제 [처리]할 체크항목이 없습니다.\\n\\n[삭제] 처리할 것을 체크하여 주십시요.');
            history.go(-1);
        </script>";
        exit;
    }

    foreach ($check as $id) {
        $id = intval($id);
        $stmt = $mysqli->prepare("DELETE FROM mlangorder_printauto WHERE no = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    $mysqli->close();

    echo "<script>
        alert('체크한 항목을 정상적으로 [삭제] 처리 하였습니다.');
        location.href = '$PHP_SELF';
    </script>";
    exit;
}

// 반송 입력 폼
if ($mode === "sendback") {
    $no = intval($_GET['no'] ?? 0);
    ?>
    <head>
    <script src="../js/coolbar.js"></script>
    <script>
        window.moveTo(screen.width / 5, screen.height / 5);
        function MemberCheckField() {
            const f = document.FrmUserInfo;
            if (f.cont.value.trim() === "") {
                alert("반송이유를 입력해 주세요.");
                f.cont.focus();
                return false;
            }
            return true;
        }
    </script>
    </head>

    <body class='coolBar'>
    <form name='FrmUserInfo' method='post' onsubmit='return MemberCheckField()' action='<?php echo  $PHP_SELF ?>'>
        <input type='hidden' name='mode' value='sendback_ok'>
        <input type='hidden' name='no' value='<?php echo  $no ?>'>
        <table align='center' cellpadding='10' cellspacing='5' width='100%'>
            <tr><td bgcolor='#336699'>
                <font style='font-size:11pt; color:#fff;'>
                    반송 이유(송장번호 등)를 입력해 주세요.<br>
                    <span style='font-size:9pt; color:red;'>* 반송 처리 시 회원 적립금에서 자동 차감됩니다.</span>
                </font>
            </td></tr>
            <tr><td>
                <input type='text' name='cont' size='50'>
                <input type='submit' value='처리하기'>
            </td></tr>
        </table>
    </form>
    </body>
    </html>
    <?php
    exit;
}

// 반송 처리 실행
if ($mode === "sendback_ok") {
    $no = intval($_POST['no'] ?? 0);
    $cont = trim($_POST['cont'] ?? '');
    $date = date("Y-m-d H:i:s");

    if (!$no || $cont === '') {
        echo "<script>alert('잘못된 접근입니다.'); window.close();</script>";
        exit;
    }

    // 주문 정보 확인
    $stmt = $mysqli->prepare("SELECT PMmember FROM mlangorder_printauto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        echo "<script>alert('주문 정보를 찾을 수 없습니다.'); window.close();</script>";
        exit;
    }
    $pmMember = $row['PMmember'];
    $stmt->close();

    // 적립금 정보 조회
    $stmt = $mysqli->prepare("SELECT no, TotalMoney FROM MlangPM_MemberTotalMoney WHERE id = ? ORDER BY no DESC LIMIT 1");
    $stmt->bind_param("s", $pmMember);
    $stmt->execute();
    $result = $stmt->get_result();
    $memberTotal = $result->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT Money_2 FROM MlangPM_MemberMoney WHERE PMThingOrderNo = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $result = $stmt->get_result();
    $memberMoney = $result->fetch_assoc();
    $stmt->close();

    if (!$memberTotal || !$memberMoney) {
        echo "<script>alert('적립금 정보를 확인할 수 없습니다.'); window.close();</script>";
        exit;
    }

    $newTotal = $memberTotal['TotalMoney'] - $memberMoney['Money_2'];

    // 적립금 차감
    $stmt = $mysqli->prepare("UPDATE MlangPM_MemberTotalMoney SET TotalMoney = ? WHERE no = ?");
    $stmt->bind_param("di", $newTotal, $memberTotal['no']);
    $stmt->execute();
    $stmt->close();

    // 주문 반송 처리
    $stmt = $mysqli->prepare("UPDATE mlangorder_printauto SET OrderStyle = 6 WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();

    // 적립금 로그 기록
    $stmt = $mysqli->prepare("UPDATE MlangPM_MemberMoney SET TakingStyle = '반송', sendback = ?, sendback_date = ? WHERE PMThingOrderNo = ?");
    $stmt->bind_param("ssi", $cont, $date, $no);
    $stmt->execute();
    $stmt->close();

    $mysqli->close();

    echo "<script>
        alert('$no 번의 자료를 정상적으로 반송 처리하였습니다.');
        opener.parent.location.reload();
        window.close();
    </script>";
    exit;
}
?>
<?php

$mode = $_GET['mode'] ?? $_POST['mode'] ?? '';
$no = $_GET['no'] ?? $_POST['no'] ?? '';
$JK = $_GET['JK'] ?? $_POST['JK'] ?? '';  // ✅ GET 우선 (URL 파라미터)

include "../../db.php";

if ($mode === "delete") {
    $no = intval($no);
    $stmt = $mysqli->prepare("DELETE FROM mlangorder_printauto WHERE no = ?");
    $stmt->bind_param("i", $no);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('$no 번의 자료를 정상적으로 삭제하였습니다.');opener.parent.location.reload();window.close();</script>";
    exit;
}

if ($mode === "OrderStyleModify") {
    $no = intval($no);
    $JK = strval(intval($JK));  // 정수로 변환 후 문자열로 (VARCHAR 컬럼이므로)

    // ✅ JK 값 검증: 0이거나 유효하지 않은 값이면 오류 처리
    $validOrderStyles = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11];
    if (!in_array(intval($JK), $validOrderStyles)) {
        echo "<script>
            alert('⚠️ 오류: 유효하지 않은 진행상태 값입니다.\\n\\n전달된 값: $JK\\n\\n페이지를 새로고침하고 다시 시도해주세요.');
            history.back();
        </script>";
        exit;
    }

    // 디버깅: 변경 전 상태 확인
    $check_stmt = $mysqli->prepare("SELECT OrderStyle FROM mlangorder_printauto WHERE no=?");
    $check_stmt->bind_param("i", $no);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $before = $check_result->fetch_assoc();
    $check_stmt->close();

    // UPDATE 실행 (OrderStyle은 VARCHAR이므로 문자열로 바인딩)
    $stmt = $mysqli->prepare("UPDATE mlangorder_printauto SET OrderStyle=? WHERE no=?");
    $stmt->bind_param("si", $JK, $no);  // string(OrderStyle), integer(no)
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    // 디버깅: 변경 후 상태 확인
    $check_stmt = $mysqli->prepare("SELECT OrderStyle FROM mlangorder_printauto WHERE no=?");
    $check_stmt->bind_param("i", $no);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $after = $check_result->fetch_assoc();
    $check_stmt->close();

    // 쿼리 파라미터 제거한 깨끗한 URL로 리다이렉트
    $cleanUrl = strtok($PHP_SELF, '?');
    echo "<script>
        window.location.href = '$cleanUrl';
    </script>";
    exit;
}

$M123 = "..";
include "../top.php";
?>

<!-- head 태그 제거: top.php에 이미 포함되어 있음 -->
<link rel="stylesheet" href="css/order-list-modern.css">
<script>
function popUp(L, e) {
    if (document.layers) {
        var barron = document.layers[L];
        barron.left = e.pageX;
        barron.top = e.pageY + 5;
        barron.visibility = "visible";
    } else if (document.all) {
        var barron = document.all[L];
        barron.style.left = event.clientX + document.body.scrollLeft + 'px';
        barron.style.top = event.clientY + document.body.scrollTop + 5 + 'px';
        barron.style.visibility = "visible";
    }
}

function popDown(L) {
    if (document.layers) document.layers[L].visibility = "hidden";
    else if (document.all) document.all[L].style.visibility = "hidden";
}

function allcheck(form) {
    for (var i = 0; i < form.elements.length; i++) {
        var check = form.elements[i];
        if (check.type === 'checkbox') check.checked = true;
    }
}

function uncheck(form) {
    for (var i = 0; i < form.elements.length; i++) {
        var check = form.elements[i];
        if (check.type === 'checkbox') check.checked = false;
    }
}

function DelGCheckField() {
    if (confirm('자료를 삭제처리 하시겠습니까?\n\n한번 삭제한 자료는 복구되지 않으니 신중히 결정해 주세요.')) {
        document.MemoPlusecheckForm.action = "<?php echo  $PHP_SELF ?>";
        document.MemoPlusecheckForm.submit();
    }
}
</script>
<script src='../js/exchange.js'></script>
<!-- /head 태그 제거: top.php에 이미 포함되어 있음 -->

<div class="order-list-container">
<!-- 페이지 헤더 -->
<div class="order-header">
    <div class="order-header-content">
        <h1 class="order-title">📋 주문 관리</h1>
        <button class="btn btn--primary" onClick="window.open('admin.php?mode=OrderView', 'MViertWSubmitr','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no');">
            ➕ 신규 주문 입력
        </button>
    </div>
    <div class="order-notices">
        <p class="notice-item">💡 주문정보를 보시면 자동으로 접수완료로 처리 됩니다.</p>
        <p class="notice-item">💡 시안제출 을 누르시면 시안 자료를 직접 올리실수 있습니다.</p>
        <p class="notice-item">💡 날짜로 검색시 - 을 넣어주셔야 합니다. ( 예: 2005-03-03 ~ 2006-11-21 )</p>
    </div>
</div>

<!-- 필터 영역 -->
<div class="order-filters">
    <form method='post' name='TDsearch' onsubmit='return TDsearchCheckField()' action='<?php echo  $PHP_SELF ?>' class="filters-form">
        <div class="filter-row">
            <div class="filter-group">
                <label class="filter-label">제품 분류</label>
                <select name='Type' class="select">
                    <option value='total'>전체</option>
                    <option value='inserted' <?php echo $Type == "inserted" ? "selected" : "" ?>>전단지</option>
                    <option value='sticker' <?php echo $Type == "sticker" ? "selected" : "" ?>>스티카</option>
                    <option value='namecard' <?php echo $Type == "namecard" ? "selected" : "" ?>>명함</option>
                    <option value='merchandisebond' <?php echo $Type == "merchandisebond" ? "selected" : "" ?>>상품권</option>
                    <option value='envelope' <?php echo $Type == "envelope" ? "selected" : "" ?>>봉투</option>
                    <option value='ncrflambeau' <?php echo $Type == "ncrflambeau" ? "selected" : "" ?>>양식지</option>
                    <option value='cadarok' <?php echo $Type == "cadarok" ? "selected" : "" ?>>리플렛</option>
                    <option value='cadarokTwo' <?php echo $Type == "cadarokTwo" ? "selected" : "" ?>>카다로그</option>
                    <option value='littleprint' <?php echo $Type == "littleprint" ? "selected" : "" ?>>소량인쇄</option>
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">검색 필드</label>
                <select name='Cate' class="select">
                    <option value='no' <?php echo $Cate == "no" ? "selected" : "" ?>>번호</option>
                    <option value='name' <?php echo $Cate == "name" ? "selected" : "" ?>>상호/성명</option>
                    <option value='phone' <?php echo $Cate == "phone" ? "selected" : "" ?>>전화번호</option>
                    <option value='Hendphone' <?php echo $Cate == "Hendphone" ? "selected" : "" ?>>휴대폰</option>
                    <option value='bizname' <?php echo $Cate == "bizname" ? "selected" : "" ?>>인쇄내용</option>
                    <option value='OrderStyle' <?php echo $Cate == "OrderStyle" ? "selected" : "" ?>>진행상태</option>
                </select>
            </div>

            <div class="filter-group filter-group--date">
                <label class="filter-label">날짜 검색</label>
                <div class="date-range-inputs">
                    <input type='text' name='YearOne' class="input input--date" placeholder="시작일 (YYYY-MM-DD)" onclick="Calendar(this);">
                    <span class="date-separator">~</span>
                    <input type='text' name='YearTwo' class="input input--date" placeholder="종료일 (YYYY-MM-DD)" onclick="Calendar(this);">
                </div>
            </div>

            <div class="filter-group filter-group--search">
                <label class="filter-label">검색어</label>
                <input type='text' name='TDsearchValue' class="input input--search" placeholder="검색어를 입력하세요">
            </div>

            <div class="filter-actions">
                <button type='submit' class="btn btn--primary">🔍 검색</button>
                <?php if ($Type) { ?>
                <button type='button' onClick="window.location='<?php echo $PHP_SELF ?>';" class="btn btn--secondary">🔄 초기화</button>
                <?php } ?>
            </div>
        </div>
    </form>
</div>

<!-- 주문 목록 테이블 -->
<div class="order-table-wrapper">
<form method='post' name='MemoPlusecheckForm'>
<input type='hidden' name='mode' value='ChickBoxAll'>
<input type='hidden' name='Ttable' value='<?php echo $TIO_CODE?>'>
<input type='hidden' name='Cate' value='<?php echo $Cate?>'>
<input type='hidden' name='Type' value='<?php echo $Type?>'>
<input type='hidden' name='YearOne' value='<?php echo $YearOne?>'>
<input type='hidden' name='YearTwo' value='<?php echo $YearTwo?>'>

<table class="order-table">
<thead>
<tr>
<th class="order-table-th order-table-th--checkbox">
    <input type="checkbox" class="checkbox" onclick="javascript:allcheck(MemoPlusecheckForm);" title="전체 선택">
</th>
<th class="order-table-th order-table-th--number">번호</th>
<th class="order-table-th">분야</th>
<th class="order-table-th">주문인</th>
<th class="order-table-th">주문날짜</th>
<th class="order-table-th">추가옵션</th>
<th class="order-table-th">배송</th>
<th class="order-table-th">진행상태</th>
<th class="order-table-th">시안</th>
<th class="order-table-th order-table-th--actions">주문정보</th>
</tr>
</thead>
<tbody>

<?php
function Error($msg) {
  echo ("<script language=javascript>
  window.alert('$msg');
  history.go(-1);
  </script>");
  exit;
}
?>
<?php
$offset = $_GET['offset'] ?? $_POST['offset'] ?? 0;	
include "../../db.php";
$table = "mlangorder_printauto";

if ($Type) {
  if ($YearOne && !$YearTwo) {
    $msg = "날짜 검색을 하시려면  ~ 이전 의 값을 입력해 주셔야 합니다.";
    Error($msg);
  }
  if ($YearTwo && !$YearOne) {
    $msg = "날짜 검색을 하시려면  ~ 이후 의 값을 입력해 주셔야 합니다.";
    Error($msg);
  }

  $TypeOk = ($Type == "total") ? "" : "and Type='$Type'";

  // 번호(no) 검색은 정확히 일치, 나머지는 LIKE 검색
  if ($Cate === 'no' && $TDsearchValue !== '') {
    $searchCondition = "no = " . intval($TDsearchValue);
  } elseif ($Cate === 'no' && $TDsearchValue === '') {
    $searchCondition = "1=1"; // 검색어 없으면 전체
  } else {
    $searchCondition = "$Cate like '%$TDsearchValue%'";
  }

  if ($YearOne || $YearTwo) {
    $YearOneOk = $YearOne . " 00:00:00";
    $YearTwoOk = $YearTwo . " 00:00:00";
    $Mlang_query = "select * from $table where date > '$YearOneOk' and date < '$YearTwoOk' $TypeOk and $searchCondition";
  } else {
    $Mlang_query = "select * from $table where $searchCondition $TypeOk";
  }
} else {
  $Mlang_query = "select * from $table";
}

$query = mysqli_query($db, "$Mlang_query");
$recordsu = mysqli_num_rows($query);
$total = mysqli_affected_rows($db);

$listcut = 20;
if (!$offset) $offset = 0;

if ($CountWW) {
  $result = mysqli_query($db, "$Mlang_query order by $CountWW $s limit $offset,$listcut");
} else {
  $result = mysqli_query($db, "$Mlang_query order by NO desc limit $offset,$listcut");
}

$rows = mysqli_num_rows($result);
if ($rows) {
  while ($row = mysqli_fetch_array($result)) {
    // 제품 타입 라벨
    $productTypeLabels = [
        "inserted" => "전단지",
        "sticker" => "스티카",
        "namecard" => "명함",
        "merchandisebond" => "상품권",
        "envelope" => "봉투",
        "ncrflambeau" => "양식지",
        "cadarok" => "리플렛",
        "cadarokTwo" => "카다로그",
        "littleprint" => "소량인쇄"
    ];
    $productLabel = $productTypeLabels[$row["Type"]] ?? $row["Type"];

    // 진행 상태 배지 클래스
    $statusBadgeClass = [
        "1" => "badge--info", "2" => "badge--warning", "3" => "badge--success",
        "4" => "badge--warning", "5" => "badge--primary", "6" => "badge--info",
        "7" => "badge--warning", "8" => "badge--success", "9" => "badge--primary",
        "10" => "badge--warning", "11" => "badge--danger"
    ];
    $badgeClass = $statusBadgeClass[$row["OrderStyle"]] ?? "badge--secondary";
?>
<tr class="order-table-row">
<td class="order-table-td order-table-td--checkbox">
<?php if ($row["OrderStyle"] != "5") { ?>
<input type="checkbox" name="check[]" value="<?php echo $row["no"] ?>" class="checkbox">
<?php } ?>
</td>
<td class="order-table-td order-table-td--number">
    <strong><?php echo $row["no"] ?></strong>
</td>
<td class="order-table-td">
    <span class="badge badge--outline"><?php echo $productLabel ?></span>
</td>
<td class="order-table-td">
    <?php
    // 주문인 이름 표시 (0이나 빈값이면 이메일에서 추출하거나 기본값 표시)
    $display_name = $row["name"];
    if (empty($display_name) || $display_name === '0') {
        // 이메일에서 @ 앞부분 추출 시도
        if (!empty($row["email"])) {
            $email_parts = explode('@', $row["email"]);
            $display_name = $email_parts[0];
        } else {
            $display_name = '주문자';
        }
    }
    echo htmlspecialchars($display_name);
    ?>
</td>
<td class="order-table-td order-table-td--date">
    <?php echo htmlspecialchars($row["date"]) ?>
</td>
<td class="order-table-td">
<?php
// 추가 옵션 표시
if (class_exists('AdditionalOptionsDisplay')) {
    $optionsDisplay = new AdditionalOptionsDisplay($db);
    $optionData = [
        'coating_enabled' => $row['coating_enabled'] ?? 0,
        'coating_type' => $row['coating_type'] ?? '',
        'coating_price' => $row['coating_price'] ?? 0,
        'folding_enabled' => $row['folding_enabled'] ?? 0,
        'folding_type' => $row['folding_type'] ?? '',
        'folding_price' => $row['folding_price'] ?? 0,
        'creasing_enabled' => $row['creasing_enabled'] ?? 0,
        'creasing_lines' => $row['creasing_lines'] ?? '',
        'creasing_price' => $row['creasing_price'] ?? 0,
        'additional_options_total' => $row['additional_options_total'] ?? 0,
        'premium_options' => $row['premium_options'] ?? '',
        'premium_options_total' => $row['premium_options_total'] ?? 0,
        // 🔧 봉투 양면테이프 옵션 추가
        'envelope_tape_enabled' => $row['envelope_tape_enabled'] ?? 0,
        'envelope_tape_quantity' => $row['envelope_tape_quantity'] ?? 0,
        'envelope_tape_price' => $row['envelope_tape_price'] ?? 0,
        'envelope_additional_options_total' => $row['envelope_additional_options_total'] ?? 0
    ];
    $summary = $optionsDisplay->getCartSummary($optionData);
    if ($summary === '옵션 없음') {
        echo "<span class='text-muted'>옵션없음</span>";
    } else {
        echo "<span class='badge badge--success'>" . htmlspecialchars($summary) . "</span>";
    }
} else {
    echo "<span class='text-muted'>-</span>";
}
?>
</td>
<td class="order-table-td">
<?php
// 배송 배지 렌더링
$deliveryValue = trim($row['delivery'] ?? '');
$logenFeeType = $row['logen_fee_type'] ?? '';
$logenTrackingNo = $row['logen_tracking_no'] ?? '';

if ($deliveryValue === '택배') {
    $badgeExtra = '';
    if ($logenFeeType === '선불') {
        $badgeExtra = ' 선불';
    } elseif ($logenFeeType === '착불') {
        $badgeExtra = ' 착불';
    }
    $trackingIcon = !empty($logenTrackingNo) ? ' ✓' : '';
    echo "<button type='button' class='badge badge--shipping badge--shipping-parcel' onclick='openShippingModal({$row['no']})'>";
    echo "🚚 택배{$badgeExtra}{$trackingIcon}";
    echo "</button>";
} elseif ($deliveryValue === '방문') {
    echo "<span class='badge badge--shipping badge--shipping-visit'>🏢 방문</span>";
} elseif ($deliveryValue === '퀵' || $deliveryValue === '오토바이') {
    echo "<button type='button' class='badge badge--shipping badge--shipping-quick' onclick='openShippingModal({$row['no']})'>";
    echo "🏍 퀵";
    echo "</button>";
} elseif ($deliveryValue === '다마스') {
    echo "<button type='button' class='badge badge--shipping badge--shipping-quick' onclick='openShippingModal({$row['no']})'>";
    echo "🚐 다마스";
    echo "</button>";
} elseif (empty($deliveryValue)) {
    echo "<span class='text-muted'>-</span>";
} else {
    echo "<button type='button' class='badge badge--shipping badge--shipping-other' onclick='openShippingModal({$row['no']})'>";
    echo htmlspecialchars($deliveryValue);
    echo "</button>";
}
?>
</td>
<td class="order-table-td">
<?php
$orderStyles = [
  1 => "견적접수", 2 => "주문접수", 3 => "접수완료", 4 => "입금대기",
  5 => "시안제작중", 6 => "시안", 7 => "교정", 8 => "작업완료",
  9 => "작업중", 10 => "교정작업중", 11 => "카드결제"
];
// 현재 OrderStyle 값 (빈 문자열이나 NULL이면 1로 기본값 설정, 0은 유효한 값으로 처리)
$currentStatus = ($row["OrderStyle"] === '' || $row["OrderStyle"] === null) ? 1 : intval($row["OrderStyle"]);
?>
<select onchange="handleStatusChange_<?php echo $row['no']; ?>(this)" class="select select--status" id="status_<?php echo $row['no']; ?>" data-original-index="<?php echo array_search($currentStatus, array_keys($orderStyles)); ?>">
<?php
foreach ($orderStyles as $key => $label) {
  $selected = ($currentStatus == $key) ? "selected" : "";
  echo "<option value='$PHP_SELF?mode=OrderStyleModify&JK=$key&no={$row['no']}' $selected>$label</option>";
}
?>
</select>
<script>
function handleStatusChange_<?php echo $row['no']; ?>(select) {
    location.href = select.value;
}
</script>
</td>
<td class="order-table-td">
<button type="button" class="btn btn--sm btn--secondary" onclick="openSinModal(<?php echo $row['no'] ?>, <?php echo $row['ThingCate'] ? 'true' : 'false' ?>)">
    <?php if ($row['ThingCate']) { ?>📝 시안수정<?php } else { ?>➕ 시안등록<?php } ?>
</button>
</td>
<td class="order-table-td order-table-td--actions">
<button type="button" class="btn btn--sm btn--primary" onClick="javascript:popup=window.open('admin.php?mode=OrderView&no=<?php echo $row['no'] ?>', 'MViertW','width=650,height=600,top=0,left=0,menubar=no,resizable=no,statusbar=no,scrollbars=yes,toolbar=no'); popup.focus();">
    📋 주문정보
</button>
</td>
</tr>
<?php
$i = 0;
$i = $i + 1;
  }
} else {
  // 검색 결과 없음 메시지
  $emptyMessage = "등록 자료없음";
  if ($TDsearchValue) {
    $emptyMessage = "$Cate 로 검색되는 '$TDsearchValue' - 관련 검색 자료없음";
  } elseif ($OrderCate) {
    $emptyMessage = "$cate 로 검색되는 - 관련 검색 자료없음";
  }
?>
<tr class="order-table-row order-table-row--empty">
<td colspan="10" class="order-table-td--empty">
    <div class="empty-state">
        <div class="empty-state-icon">📭</div>
        <p class="empty-state-message"><?php echo $emptyMessage ?></p>
    </div>
</td>
</tr>
<?php
}
?>

</tbody>
</table>

<!-- 테이블 하단 액션 버튼 -->
<div class="table-actions">
    <div class="table-actions-left">
        <button type='button' class="btn btn--outline" onClick="javascript:allcheck(MemoPlusecheckForm);">
            ☑️ 전체 선택
        </button>
        <button type='button' class="btn btn--outline" onClick="javascript:uncheck(MemoPlusecheckForm);">
            ☐ 선택 해제
        </button>
        <button type='button' class="btn btn--danger" onClick="javascript:DelGCheckField();">
            🗑️ 선택 항목 삭제
        </button>
    </div>
</div>

</form>
</div>

<!-- 페이지네이션 -->
<?php
$mlang_pagego = isset($_POST['mlang_pagego']) ? $_POST['mlang_pagego'] : '';
$OrderCate = isset($_POST['OrderCate']) ? $_POST['OrderCate'] : '';
$OrderStyleYU9OK = isset($_POST['OrderStyleYU9OK']) ? $_POST['OrderStyleYU9OK'] : '';
if($rows){

if($TDsearchValue){
$mlang_pagego="Cate=$Cate&TDsearchValue=$TDsearchValue";
}else if($OrderStyleYU9OK){
$mlang_pagego="OrderStyleYU9OK=$OrderStyleYU9OK";
}else if($OrderCate){
$mlang_pagego="OrderCate=$OrderCate";
}else{}

$pagecut= 7;
$one_bbs= $listcut*$pagecut;
$start_offset= intval($offset/$one_bbs)*$one_bbs;
$end_offset= intval($recordsu/$one_bbs)*$one_bbs;
$start_page= intval($start_offset/$listcut)+1;
$end_page= ($recordsu%$listcut>0)? intval($recordsu/$listcut)+1: intval($recordsu/$listcut);
?>

<div class="pagination-wrapper">
    <div class="pagination">
        <?php if($start_offset!= 0) {
            $apoffset= $start_offset- $one_bbs;
        ?>
        <a href='<?php echo $PHP_SELF ?>?offset=<?php echo $apoffset ?>&<?php echo $mlang_pagego ?>' class="pagination-link pagination-link--prev">
            ‹ 이전
        </a>
        <?php } ?>

        <?php
        for($i= $start_page; $i< $start_page+$pagecut; $i++) {
            $newoffset= ($i-1)*$listcut;
            if($offset!= $newoffset){
        ?>
        <a href='<?php echo $PHP_SELF ?>?offset=<?php echo $newoffset ?>&<?php echo $mlang_pagego ?>' class="pagination-link">
            <?php echo $i ?>
        </a>
        <?php
            } else {
        ?>
        <span class="pagination-link pagination-link--active"><?php echo $i ?></span>
        <?php
            }
            if($i==$end_page) break;
        }
        ?>

        <?php if($start_offset!= $end_offset) {
            $nextoffset= $start_offset+ $one_bbs;
        ?>
        <a href='<?php echo $PHP_SELF ?>?offset=<?php echo $nextoffset ?>&<?php echo $mlang_pagego ?>' class="pagination-link pagination-link--next">
            다음 ›
        </a>
        <?php } ?>
    </div>

    <div class="pagination-info">
        총 <strong><?php echo $end_page ?></strong>개의 주문
    </div>
</div>

<?php
}
mysqli_close($db);
?>

</div><!-- .order-list-container -->

<!-- 배송정보 모달 -->
<div id="shippingModal" class="shipping-modal" style="display:none;">
    <div class="shipping-modal-overlay" onclick="closeShippingModal()"></div>
    <div class="shipping-modal-content">
        <div class="shipping-modal-header">
            <h3>📦 배송 정보 <span id="shippingModalOrderNo"></span></h3>
            <button type="button" class="shipping-modal-close" onclick="closeShippingModal()">&times;</button>
        </div>
        <div class="shipping-modal-body">
            <div id="shippingEstimateSection">
                <div class="shipping-info-row">
                    <label>추정 무게</label>
                    <span id="shippingEstWeight">-</span>
                </div>
                <p class="shipping-estimate-notice">⚠ 추정치이며 실제와 다를 수 있습니다.</p>
            </div>
            <hr class="shipping-divider">
            <div class="shipping-form-group">
                <label for="shippingFeeType">운임구분</label>
                <select id="shippingFeeType" class="select">
                    <option value="">미지정</option>
                    <option value="착불">착불</option>
                    <option value="선불">선불</option>
                </select>
            </div>
            <div class="shipping-form-group">
                <label for="shippingBoxQty">박스 수량</label>
                <input type="number" id="shippingBoxQty" class="input" min="0" placeholder="박스 수">
            </div>
            <div class="shipping-form-group">
                <label for="shippingDeliveryFee">택배비 (원)</label>
                <input type="number" id="shippingDeliveryFee" class="input" min="0" step="100" placeholder="택배비 입력">
            </div>
            <div class="shipping-form-group">
                <label for="shippingTrackingNo">송장번호</label>
                <input type="text" id="shippingTrackingNo" class="input" placeholder="송장번호 입력">
            </div>
        </div>
        <div class="shipping-modal-footer">
            <button type="button" class="btn btn--outline btn--sm" onclick="closeShippingModal()">취소</button>
            <button type="button" class="btn btn--primary btn--sm" onclick="saveShippingInfo()">💾 저장</button>
        </div>
    </div>
</div>

<!-- 시안수정 모달 -->
<div id="sinModal" class="sin-modal" style="display:none;">
    <div class="sin-modal-overlay" onclick="closeSinModal()"></div>
    <div class="sin-modal-content">
        <iframe id="sinModalIframe" src="" frameborder="0"></iframe>
    </div>
</div>

<style>
.sin-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}
.sin-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}
.sin-modal-content {
    position: relative;
    width: 390px;
    height: 365px;
    background: transparent;
    border-radius: 12px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    animation: modalSlideIn 0.3s ease-out;
}
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
#sinModalIframe {
    width: 100%;
    height: 100%;
    border: none;
}
</style>

<script>
function openSinModal(orderNo, hasThingCate) {
    var url = 'admin.php?mode=SinForm&coe&no=' + orderNo + '&modal=1';
    if (hasThingCate) {
        url += '&ModifyCode=ok';
    }
    document.getElementById('sinModalIframe').src = url;
    document.getElementById('sinModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeSinModal() {
    document.getElementById('sinModal').style.display = 'none';
    document.getElementById('sinModalIframe').src = '';
    document.body.style.overflow = '';
}

window.addEventListener('message', function(e) {
    if (e.data === 'closeSinModal') {
        closeSinModal();
        location.reload();
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSinModal();
        closeShippingModal();
    }
});
</script>

<script>
var currentShippingOrderNo = null;

function openShippingModal(orderNo) {
    currentShippingOrderNo = orderNo;
    document.getElementById('shippingModalOrderNo').textContent = '#' + orderNo;
    document.getElementById('shippingEstWeight').textContent = '로딩중...';
    document.getElementById('shippingFeeType').value = '';
    document.getElementById('shippingBoxQty').value = '';
    document.getElementById('shippingDeliveryFee').value = '';
    document.getElementById('shippingTrackingNo').value = '';
    document.getElementById('shippingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';

    fetch('../../includes/shipping_api.php?action=order_estimate&no=' + orderNo)
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                var d = res.data;
                var est = d.estimate;
                if (est.calculable) {
                    var w = parseFloat(est.weight_kg); document.getElementById('shippingEstWeight').textContent = w <= 3 ? est.weight_kg + 'kg 이하' : '약 ' + est.weight_kg + 'kg';
                } else {
                    document.getElementById('shippingEstWeight').textContent = '계산 불가';
                }
                if (d.logen_fee_type) document.getElementById('shippingFeeType').value = d.logen_fee_type;
                if (d.logen_box_qty !== null && d.logen_box_qty !== '') document.getElementById('shippingBoxQty').value = d.logen_box_qty;
                if (d.logen_delivery_fee !== null && d.logen_delivery_fee !== '') document.getElementById('shippingDeliveryFee').value = d.logen_delivery_fee;
                if (d.logen_tracking_no) document.getElementById('shippingTrackingNo').value = d.logen_tracking_no;
            } else {
                document.getElementById('shippingEstWeight').textContent = '오류';
            }
        })
        .catch(function() {
            document.getElementById('shippingEstWeight').textContent = '오류';
        });
}

function closeShippingModal() {
    document.getElementById('shippingModal').style.display = 'none';
    document.body.style.overflow = '';
    currentShippingOrderNo = null;
}

function saveShippingInfo() {
    if (!currentShippingOrderNo) return;

    var formData = new FormData();
    formData.append('action', 'logen_save');
    formData.append('no', currentShippingOrderNo);
    formData.append('logen_fee_type', document.getElementById('shippingFeeType').value);
    formData.append('logen_box_qty', document.getElementById('shippingBoxQty').value);
    formData.append('logen_delivery_fee', document.getElementById('shippingDeliveryFee').value);
    formData.append('logen_tracking_no', document.getElementById('shippingTrackingNo').value);

    fetch('../../includes/shipping_api.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            closeShippingModal();
            location.reload();
        } else {
            alert('저장 실패: ' + (res.error || '알 수 없는 오류'));
        }
    })
    .catch(function() {
        alert('네트워크 오류가 발생했습니다.');
    });
}
</script>

<?php
include "../down.php";
?>