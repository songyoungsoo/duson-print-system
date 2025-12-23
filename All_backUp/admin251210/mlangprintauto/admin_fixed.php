<?php
/**
 * 관리자 페이지 - DB 연결 수정 버전
 * 두손기획인쇄 - 주문 관리 시스템
 */

// DB 연결 (procedural 방식)
include "../../db.php";
include "../../includes/auth.php";

// 새 워크플로우 시스템 포함
require_once "../../includes/OrderStatusManager.php";
require_once "../../includes/ProofreadingManager.php";
require_once "../../includes/OrderNotificationManager.php";

// DB 연결 확인
if (!$db) {
    die("데이터베이스 연결 실패: " . mysqli_connect_error());
}

// 추가 옵션 표시 시스템 포함
if (file_exists('../../includes/AdditionalOptionsDisplay.php')) {
    include_once '../../includes/AdditionalOptionsDisplay.php';
}

include "../config.php";

$T_DirUrl = "../../mlangprintauto";
include "$T_DirUrl/ConDb.php";

$T_DirFole = "./int/info.php";

// 변수 초기화 (null coalescing operator 사용)
$mode = $_POST['mode'] ?? $_GET['mode'] ?? '';
$ModifyCode = $_POST['ModifyCode'] ?? $_GET['ModifyCode'] ?? '';
$no = intval($_POST['no'] ?? $_GET['no'] ?? 0);
$Type = $_POST['Type'] ?? '기본값';
$ImgFolder = $_POST['ImgFolder'] ?? 'default_folder';
$Type_1 = $_POST['Type_1'] ?? 'default_type';
$money_1 = $_POST['money_1'] ?? 0;
$money_2 = $_POST['money_2'] ?? 0;
$money_3 = $_POST['money_3'] ?? 0;
$money_4 = $_POST['money_4'] ?? 0;
$money_5 = $_POST['money_5'] ?? 0;
$OrderName = $_POST['name'] ?? '미입력';
$email = $_POST['email'] ?? 'noemail@example.com';
$zip = $_POST['zip'] ?? '';
$zip1 = $_POST['zip1'] ?? '';
$zip2 = $_POST['zip2'] ?? '';
$phone = $_POST['phone'] ?? '';
$Hendphone = $_POST['Hendphone'] ?? '';
$bizname = $_POST['bizname'] ?? '기본 회사명';
$bank = $_POST['bank'] ?? '기본 은행';
$bankname = $_POST['bankname'] ?? '';
$cont = $_POST['cont'] ?? '내용 없음';
$date = $_POST['date'] ?? date("Y-m-d H:i:s");
$OrderStyle = $_POST['OrderStyle'] ?? 'pending'; // 새 시스템 기본값
$ThingCate = $_POST['ThingCate'] ?? '';
$pass = $_POST['pass'] ?? '';
$Designer = $_POST['Designer'] ?? '미정';
$Gensu = intval($_POST['Gensu'] ?? 0);
$ThingNo = intval($_POST['ThingNo'] ?? 0);

///////////////////////////////////////////////////////////////////////////////////////////////
// 주문 정보 수정
///////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "ModifyOk") {
    // POST 데이터 받기
    $TypeOne = $_POST['TypeOne'] ?? '';
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $zip1 = $_POST['zip1'] ?? '';
    $zip2 = $_POST['zip2'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $Hendphone = $_POST['Hendphone'] ?? '';
    $bizname = $_POST['bizname'] ?? '';
    $bank = $_POST['bank'] ?? '';
    $bankname = $_POST['bankname'] ?? '';
    $cont = $_POST['cont'] ?? '';
    $Gensu = intval($_POST['Gensu'] ?? 0);
    $delivery = $_POST['delivery'] ?? '';

    // SQL UPDATE 문 준비 (mysqli procedural style)
    $stmt = mysqli_prepare($db, "UPDATE mlangorder_printauto
        SET name = ?, email = ?, zip = ?, zip1 = ?, zip2 = ?, phone = ?, Hendphone = ?, bizname = ?,
            bank = ?, bankname = ?, cont = ?, Gensu = ?, delivery = ?
        WHERE no = ?");

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssssssssi",
        $name, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname,
        $bank, $bankname, $cont, $Gensu, $delivery, $no
    );

    if (!mysqli_stmt_execute($stmt)) {
        echo "<script>
                alert('DB 접속 에러입니다!');
                history.go(-1);
              </script>";
        exit;
    }

    mysqli_stmt_close($stmt);

    echo "<script>
            alert('정보를 정상적으로 수정하였습니다.');
            opener.parent.location.reload();
          </script>";

    header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=$no");
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////
// 새 주문 등록
///////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "SubmitOk") {
    // 새로운 주문번호 생성
    $Table_result = mysqli_query($db, "SELECT MAX(no) FROM mlangorder_printauto");
    if (!$Table_result) {
        echo "<script>alert('DB 접속 에러입니다!'); history.go(-1);</script>";
        exit;
    }

    $row = mysqli_fetch_row($Table_result);
    $new_no = $row[0] ? $row[0] + 1 : 1;

    // 업로드 폴더 생성
    $dir = "../../mlangorder_printauto/upload/$new_no";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        chmod($dir, 0777);
    }

    // 현재 날짜 가져오기
    $date = date("Y-m-d H:i:s");
    $TypeOne = $_POST['TypeOne'] ?? '';

    // 데이터 삽입
    $stmt = mysqli_prepare($db, "INSERT INTO mlangorder_printauto
        (no, Type, ImgFolder, Type_1, money_1, money_2, money_3, money_4, money_5, name, email, zip, zip1, zip2, phone, Hendphone, bizname, bank, bankname, cont, date, OrderStyle, ThingCate, Designer, pass, Gensu)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $ThingCate = ""; // 첨부파일 기본값

    mysqli_stmt_bind_param(
        $stmt,
        "issssssssssssssssssssssssi",
        $new_no, $Type, $ImgFolder, $Type_1, $money_1, $money_2, $money_3, $money_4, $money_5,
        $OrderName, $email, $zip, $zip1, $zip2, $phone, $Hendphone, $bizname, $bank, $bankname,
        $cont, $date, $OrderStyle, $ThingCate, $Designer, $pass, $Gensu
    );

    if (!mysqli_stmt_execute($stmt)) {
        echo "<script>alert('DB 저장 실패! 오류: " . mysqli_stmt_error($stmt) . "'); history.go(-1);</script>";
        exit;
    }

    mysqli_stmt_close($stmt);

    // 주문 접수 이메일 발송 (새 시스템)
    try {
        $notificationManager = new OrderNotificationManager($db);
        $notificationManager->sendImmediately($new_no, 'order_received');
    } catch (Exception $e) {
        error_log("이메일 발송 실패: " . $e->getMessage());
    }

    echo "<script>
            alert('정보를 정상적으로 [저장] 하였습니다.');
            opener.parent.location.reload();
            window.location.href='" . htmlspecialchars($_SERVER['PHP_SELF']) . "?mode=OrderView&no=$new_no';
          </script>";

    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////
// 주문 상태 변경 (AJAX)
///////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "ChangeStatus") {
    header('Content-Type: application/json');

    $order_no = intval($_POST['order_no'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';
    $admin_id = $_SESSION['admin_id'] ?? 'admin';
    $reason = $_POST['reason'] ?? '';

    if (!$order_no || !$new_status) {
        echo json_encode(['success' => false, 'message' => '필수 파라미터 누락']);
        exit;
    }

    try {
        $statusManager = new OrderStatusManager($db, $order_no);
        $success = $statusManager->changeStatus($new_status, $admin_id, $reason);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => '주문 상태가 변경되었습니다.',
                'new_status' => $new_status
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => '상태 변경에 실패했습니다.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '오류 발생: ' . $e->getMessage()
        ]);
    }
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////
// 교정본 업로드 (AJAX)
///////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "UploadProof") {
    header('Content-Type: application/json');

    $order_no = intval($_POST['order_no'] ?? 0);
    $admin_id = $_SESSION['admin_id'] ?? 'admin';

    if (!$order_no || !isset($_FILES['proof_file'])) {
        echo json_encode(['success' => false, 'message' => '파일이 없습니다.']);
        exit;
    }

    try {
        // 파일 업로드 처리
        $upload_dir = "../../mlangorder_printauto/proofs/$order_no/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $file_name = basename($_FILES['proof_file']['name']);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = "proof_" . date("YmdHis") . "." . $file_ext;
        $target_file = $upload_dir . $new_file_name;

        if (move_uploaded_file($_FILES['proof_file']['tmp_name'], $target_file)) {
            $proofManager = new ProofreadingManager($db, $order_no);
            $proof_id = $proofManager->uploadProof($target_file, $admin_id);

            if ($proof_id) {
                echo json_encode([
                    'success' => true,
                    'message' => '교정본이 업로드되었습니다.',
                    'proof_id' => $proof_id
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '교정본 등록에 실패했습니다.'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => '파일 업로드에 실패했습니다.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '오류 발생: ' . $e->getMessage()
        ]);
    }
    exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////
// 주문 상세 보기
///////////////////////////////////////////////////////////////////////////////////////////////
if ($mode == "OrderView") {
    $stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
    mysqli_stmt_bind_param($stmt, 'i', $no);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$order) {
        echo "<script>alert('주문을 찾을 수 없습니다.'); history.go(-1);</script>";
        exit;
    }

    // 주문 상태 관리자
    $statusManager = new OrderStatusManager($db, $no);
    $current_status = $statusManager->getStatusDetails();
    $status_history = $statusManager->getStatusHistory();
    $next_statuses = $statusManager->getNextPossibleStatuses();

    // 교정본 관리자
    $proofManager = new ProofreadingManager($db, $no);
    $proofs = $proofManager->getAllProofs();
    $proof_stats = $proofManager->getProofStatistics();

    include "../title.php";
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>주문 상세 - <?php echo $no; ?></title>
    <style>
        body {
            font-family: 'Malgun Gothic', sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            border-bottom: 2px solid #3498db;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2c3e50;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .info-section {
            margin-bottom: 30px;
        }
        .info-section h2 {
            color: #2c3e50;
            border-left: 4px solid #3498db;
            padding-left: 10px;
            margin-bottom: 15px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .info-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #2c3e50;
            font-size: 14px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 10px;
        }
        .btn-primary {
            background: #3498db;
            color: white;
        }
        .btn-success {
            background: #27ae60;
            color: white;
        }
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .status-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 20px;
        }
        .history-timeline {
            position: relative;
            padding-left: 30px;
        }
        .history-item {
            position: relative;
            padding: 15px;
            background: #f8f9fa;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 3px solid #3498db;
        }
        .history-time {
            color: #7f8c8d;
            font-size: 12px;
        }
        .proof-list {
            list-style: none;
            padding: 0;
        }
        .proof-item {
            padding: 15px;
            background: #f8f9fa;
            margin-bottom: 10px;
            border-radius: 4px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>주문 상세 정보 #<?php echo $no; ?></h1>
            <p>
                <span class="status-badge" style="background: <?php echo $current_status['color_code'] ?? '#999'; ?>">
                    <?php echo $current_status['status_name_ko'] ?? '알 수 없음'; ?>
                </span>
            </p>
        </div>

        <!-- 주문 정보 -->
        <div class="info-section">
            <h2>주문 기본 정보</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">주문일시</div>
                    <div class="info-value"><?php echo $order['date']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">제품 종류</div>
                    <div class="info-value"><?php echo $order['Type']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">고객명</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['name']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">이메일</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">전화번호</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['phone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">휴대폰</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['Hendphone']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">회사명</div>
                    <div class="info-value"><?php echo htmlspecialchars($order['bizname']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">주문 금액</div>
                    <div class="info-value"><?php echo number_format($order['money_2'] ?? $order['money_1']); ?>원</div>
                </div>
            </div>
        </div>

        <!-- 상태 변경 액션 -->
        <div class="info-section">
            <h2>주문 상태 관리</h2>
            <div class="status-actions">
                <?php foreach ($next_statuses as $status): ?>
                    <button class="btn btn-primary" onclick="changeStatus(<?php echo $no; ?>, '<?php echo $status['status_code']; ?>', '<?php echo $status['status_name_ko']; ?>')">
                        → <?php echo $status['status_name_ko']; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 교정본 관리 -->
        <div class="info-section">
            <h2>교정본 관리</h2>
            <p>총 <?php echo $proof_stats['total_versions'] ?? 0; ?>개 버전
               (승인: <?php echo $proof_stats['approved_count'] ?? 0; ?>,
                거부: <?php echo $proof_stats['rejected_count'] ?? 0; ?>,
                대기: <?php echo $proof_stats['pending_count'] ?? 0; ?>)</p>

            <button class="btn btn-success" onclick="document.getElementById('uploadProofModal').style.display='block'">
                교정본 업로드
            </button>

            <ul class="proof-list">
                <?php foreach ($proofs as $proof): ?>
                    <li class="proof-item">
                        <div>
                            <strong>버전 <?php echo $proof['proof_version']; ?></strong>
                            <br>
                            <small><?php echo $proof['proof_uploaded_at']; ?></small>
                            <br>
                            상태: <?php
                                $confirm_labels = [
                                    'pending' => '확인 대기',
                                    'approved' => '승인됨',
                                    'rejected' => '거부됨'
                                ];
                                echo $confirm_labels[$proof['customer_confirmed']] ?? '알 수 없음';
                            ?>
                        </div>
                        <div>
                            <a href="<?php echo $proof['proof_file_path']; ?>" target="_blank" class="btn btn-primary">파일 보기</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- 상태 변경 이력 -->
        <div class="info-section">
            <h2>상태 변경 이력</h2>
            <div class="history-timeline">
                <?php foreach ($status_history as $history): ?>
                    <div class="history-item">
                        <div class="history-time"><?php echo $history['changed_at']; ?></div>
                        <div>
                            <strong><?php echo $history['status_name_ko'] ?? $history['new_status']; ?></strong>
                            <?php if ($history['changed_by']): ?>
                                <br><small>변경자: <?php echo $history['changed_by']; ?></small>
                            <?php endif; ?>
                            <?php if ($history['change_reason']): ?>
                                <br><small>사유: <?php echo htmlspecialchars($history['change_reason']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 교정본 업로드 모달 -->
    <div id="uploadProofModal" class="modal">
        <div class="modal-content">
            <h2>교정본 업로드</h2>
            <form id="proofUploadForm" enctype="multipart/form-data">
                <input type="hidden" name="order_no" value="<?php echo $no; ?>">
                <p>
                    <input type="file" name="proof_file" accept=".pdf,.jpg,.jpeg,.png" required>
                </p>
                <p>
                    <button type="submit" class="btn btn-success">업로드</button>
                    <button type="button" class="btn" onclick="document.getElementById('uploadProofModal').style.display='none'">취소</button>
                </p>
            </form>
        </div>
    </div>

    <script>
        function changeStatus(orderNo, newStatus, statusName) {
            if (!confirm(`주문 상태를 "${statusName}"(으)로 변경하시겠습니까?`)) {
                return;
            }

            const reason = prompt('변경 사유를 입력하세요 (선택사항):');

            fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    mode: 'ChangeStatus',
                    order_no: orderNo,
                    new_status: newStatus,
                    reason: reason || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('오류: ' + data.message);
                }
            })
            .catch(error => {
                alert('오류가 발생했습니다: ' + error);
            });
        }

        document.getElementById('proofUploadForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('mode', 'UploadProof');

            fetch('<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('오류: ' + data.message);
                }
            })
            .catch(error => {
                alert('오류가 발생했습니다: ' + error);
            });
        });
    </script>
</body>
</html>
<?php
    exit;
}
?>
