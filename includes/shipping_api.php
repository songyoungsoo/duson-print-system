<?php
/**
 * shipping_api.php — 배송 추정 AJAX API
 * 
 * Actions:
 *   estimate    — 장바구니 아이템으로 무게/박스 추정
 *   rates       — 현재 요금표 조회
 *   rates_save  — 요금표 수정 (관리자 전용)
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/ShippingCalculator.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'estimate':
        $cartJson = $_POST['cart_items'] ?? '';
        $cartItems = json_decode($cartJson, true);

        if (!$cartItems || !is_array($cartItems)) {
            echo json_encode(['success' => false, 'error' => 'cart_items required']);
            exit;
        }

        $result = ShippingCalculator::estimateFromCart($cartItems);
        echo json_encode(['success' => true, 'data' => $result]);
        break;

    case 'rates':
        $rates = ShippingCalculator::getRatesForDisplay($connect ?? null);
        echo json_encode(['success' => true, 'data' => $rates]);
        break;

    case 'order_estimate':
        // 관리자용: 주문번호로 배송 추정 + 기존 logen 데이터 반환
        $orderNo = intval($_GET['no'] ?? $_POST['no'] ?? 0);
        if (!$orderNo) {
            echo json_encode(['success' => false, 'error' => 'no required']);
            exit;
        }

        $stmt = mysqli_prepare($db, "SELECT * FROM mlangorder_printauto WHERE no = ?");
        mysqli_stmt_bind_param($stmt, "i", $orderNo);
        mysqli_stmt_execute($stmt);
        $orderResult = mysqli_stmt_get_result($stmt);
        $orderRow = mysqli_fetch_assoc($orderResult);
        mysqli_stmt_close($stmt);

        if (!$orderRow) {
            echo json_encode(['success' => false, 'error' => 'order not found']);
            exit;
        }

        $estimate = ShippingCalculator::estimateFromOrder($orderRow);

        echo json_encode([
            'success' => true,
            'data' => [
                'estimate' => $estimate,
                'delivery' => $orderRow['delivery'] ?? '',
                'logen_box_qty' => $orderRow['logen_box_qty'],
                'logen_delivery_fee' => $orderRow['logen_delivery_fee'],
                'logen_fee_type' => $orderRow['logen_fee_type'] ?? '',
                'logen_tracking_no' => $orderRow['logen_tracking_no'] ?? ''
            ]
        ]);
        break;

    case 'logen_save':
        // 관리자용: 배송 정보 저장
        $orderNo = intval($_POST['no'] ?? 0);
        if (!$orderNo) {
            echo json_encode(['success' => false, 'error' => 'no required']);
            exit;
        }

        $logenBoxQty = isset($_POST['logen_box_qty']) && $_POST['logen_box_qty'] !== '' ? intval($_POST['logen_box_qty']) : null;
        $logenDeliveryFee = isset($_POST['logen_delivery_fee']) && $_POST['logen_delivery_fee'] !== '' ? intval($_POST['logen_delivery_fee']) : null;
        $logenFeeType = $_POST['logen_fee_type'] ?? '';
        $logenTrackingNo = trim($_POST['logen_tracking_no'] ?? '');

        $updateQuery = "UPDATE mlangorder_printauto SET logen_box_qty = ?, logen_delivery_fee = ?, logen_fee_type = ?, logen_tracking_no = ? WHERE no = ?";
        $stmt = mysqli_prepare($db, $updateQuery);
        if (!$stmt) {
            echo json_encode(['success' => false, 'error' => mysqli_error($db)]);
            exit;
        }

        // bind_param 검증: 5 placeholders, "iissi", 5 vars
        $placeholder_count = substr_count($updateQuery, '?');  // 5
        $type_string_logen = "iissi";
        $type_count = strlen($type_string_logen);              // 5
        $var_count = 5;                                        // 5

        mysqli_stmt_bind_param($stmt, $type_string_logen, $logenBoxQty, $logenDeliveryFee, $logenFeeType, $logenTrackingNo, $orderNo);

        if (mysqli_stmt_execute($stmt)) {
            // === NEW: 택배비 선불 확정 시 고객 이메일 알림 ===
            if ($logenFeeType === '선불' && $logenDeliveryFee > 0) {
                $emailStmt = mysqli_prepare($db, "SELECT email, name, no, money_5 FROM mlangorder_printauto WHERE no = ?");
                mysqli_stmt_bind_param($emailStmt, "i", $orderNo);
                mysqli_stmt_execute($emailStmt);
                $emailResult = mysqli_stmt_get_result($emailStmt);
                $emailRow = mysqli_fetch_assoc($emailResult);
                mysqli_stmt_close($emailStmt);

                if ($emailRow && !empty($emailRow['email'])) {
                    $customerEmail = $emailRow['email'];
                    $customerName  = $emailRow['name'] ?? '고객';
                    $printAmount   = intval($emailRow['money_5'] ?? 0);

                    $shippingSupply = $logenDeliveryFee;
                    $shippingVat    = round($shippingSupply * 0.1);
                    $shippingTotal  = $shippingSupply + $shippingVat;
                    $grandTotal     = $printAmount + $shippingTotal;

                    $subject = "[두손기획인쇄] 주문 #{$orderNo} 택배비 안내";

                    $body = '<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:\'Malgun Gothic\',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:30px 0;">
  <tr><td align="center">
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
      <!-- Header -->
      <tr>
        <td style="background:#1E4E79;padding:24px 32px;">
          <p style="margin:0;color:#ffffff;font-size:20px;font-weight:bold;">두손기획인쇄</p>
          <p style="margin:4px 0 0;color:#a8c4e0;font-size:13px;">택배비 안내</p>
        </td>
      </tr>
      <!-- Body -->
      <tr>
        <td style="padding:32px;">
          <p style="margin:0 0 16px;font-size:15px;color:#333;">' . htmlspecialchars($customerName) . ' 고객님, 안녕하세요.</p>
          <p style="margin:0 0 24px;font-size:14px;color:#555;line-height:1.6;">
            주문 <strong>#' . $orderNo . '</strong>의 택배비(선불)가 확정되었습니다.<br>
            아래 내역을 확인하시고 입금해 주시기 바랍니다.
          </p>

          <!-- 택배비 내역 -->
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #2a6496;border-radius:6px;overflow:hidden;margin-bottom:20px;">
            <tr style="background:#1E4E79;">
              <td colspan="2" style="padding:10px 16px;color:#fff;font-size:13px;font-weight:bold;">택배비 내역</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:10px 16px;font-size:13px;color:#555;border-bottom:1px solid #cbd5e1;">공급가액</td>
              <td style="padding:10px 16px;font-size:13px;color:#333;text-align:right;border-bottom:1px solid #cbd5e1;">' . number_format($shippingSupply) . '원</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:10px 16px;font-size:13px;color:#555;border-bottom:1px solid #cbd5e1;">부가세 (10%)</td>
              <td style="padding:10px 16px;font-size:13px;color:#333;text-align:right;border-bottom:1px solid #cbd5e1;">+ ' . number_format($shippingVat) . '원</td>
            </tr>
            <tr style="background:#e8eff7;">
              <td style="padding:10px 16px;font-size:13px;font-weight:bold;color:#1E4E79;">택배비 합계</td>
              <td style="padding:10px 16px;font-size:13px;font-weight:bold;color:#1E4E79;text-align:right;">' . number_format($shippingTotal) . '원</td>
            </tr>
          </table>

          <!-- 입금 합계 -->
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #2a6496;border-radius:6px;overflow:hidden;margin-bottom:20px;">
            <tr style="background:#1E4E79;">
              <td colspan="2" style="padding:10px 16px;color:#fff;font-size:13px;font-weight:bold;">입금 안내</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:10px 16px;font-size:13px;color:#555;border-bottom:1px solid #cbd5e1;">인쇄 주문 금액</td>
              <td style="padding:10px 16px;font-size:13px;color:#333;text-align:right;border-bottom:1px solid #cbd5e1;">' . number_format($printAmount) . '원</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:10px 16px;font-size:13px;color:#555;border-bottom:1px solid #cbd5e1;">택배비 (선불)</td>
              <td style="padding:10px 16px;font-size:13px;color:#333;text-align:right;border-bottom:1px solid #cbd5e1;">' . number_format($shippingTotal) . '원</td>
            </tr>
            <tr style="background:#e8eff7;">
              <td style="padding:10px 16px;font-size:14px;font-weight:bold;color:#1E4E79;">총 입금액</td>
              <td style="padding:10px 16px;font-size:14px;font-weight:bold;color:#1E4E79;text-align:right;">' . number_format($grandTotal) . '원</td>
            </tr>
          </table>

          <!-- 계좌 정보 -->
          <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #94a3b8;border-radius:6px;overflow:hidden;margin-bottom:24px;">
            <tr style="background:#1E4E79;">
              <td colspan="2" style="padding:10px 16px;color:#fff;font-size:13px;font-weight:bold;">입금 계좌 안내</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:8px 16px;font-size:13px;color:#555;width:120px;border-bottom:1px solid #cbd5e1;">국민은행</td>
              <td style="padding:8px 16px;font-size:13px;color:#333;border-bottom:1px solid #cbd5e1;">999-1688-2384</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:8px 16px;font-size:13px;color:#555;border-bottom:1px solid #cbd5e1;">신한은행</td>
              <td style="padding:8px 16px;font-size:13px;color:#333;border-bottom:1px solid #cbd5e1;">110-342-543507</td>
            </tr>
            <tr style="background:#f8fafc;">
              <td style="padding:8px 16px;font-size:13px;color:#555;border-bottom:1px solid #cbd5e1;">농협</td>
              <td style="padding:8px 16px;font-size:13px;color:#333;border-bottom:1px solid #cbd5e1;">301-2632-1830-11</td>
            </tr>
            <tr style="background:#e8eff7;">
              <td style="padding:8px 16px;font-size:13px;font-weight:bold;color:#1E4E79;">예금주</td>
              <td style="padding:8px 16px;font-size:13px;font-weight:bold;color:#1E4E79;">두손기획인쇄 차경선</td>
            </tr>
          </table>

          <p style="margin:0 0 8px;font-size:13px;color:#555;">문의사항은 아래로 연락해 주세요.</p>
          <p style="margin:0;font-size:14px;font-weight:bold;color:#1E4E79;">📞 02-2632-1830</p>
        </td>
      </tr>
      <!-- Footer -->
      <tr>
        <td style="background:#f4f6f8;padding:16px 32px;border-top:1px solid #e2e8f0;">
          <p style="margin:0;font-size:12px;color:#888;text-align:center;">
            두손기획인쇄 | 서울특별시 영등포구 영등포로36길 9 1층
          </p>
        </td>
      </tr>
    </table>
  </td></tr>
</table>
</body>
</html>';

                    require_once $_SERVER['DOCUMENT_ROOT'] . '/mlangorder_printauto/mailer.lib.php';
                    ob_start();
                    $mail_result = mailer('두손기획인쇄', 'dsp1830@naver.com', $customerEmail, $subject, $body, 1, "");
                    ob_end_clean();
                    error_log("택배비 알림 이메일 발송 " . ($mail_result ? "성공" : "실패") . ": 주문#{$orderNo}");
                }
            }
            // === END: 택배비 이메일 알림 ===
            echo json_encode(['success' => true, 'message' => '배송 정보가 저장되었습니다.']);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_stmt_error($stmt)]);
        }
        mysqli_stmt_close($stmt);
        break;

    case 'rates_save':
        session_start();
        if (empty($_SESSION['is_admin'])) {
            echo json_encode(['success' => false, 'error' => 'admin_required']);
            exit;
        }

        $ratesJson = $_POST['rates'] ?? '';
        $ratesData = json_decode($ratesJson, true);

        if (!$ratesData || !is_array($ratesData)) {
            echo json_encode(['success' => false, 'error' => 'rates data required']);
            exit;
        }

        $db = $connect ?? null;
        if (!$db) {
            echo json_encode(['success' => false, 'error' => 'db_error']);
            exit;
        }

        mysqli_begin_transaction($db);
        try {
            mysqli_query($db, "DELETE FROM shipping_rates");

            $stmt = mysqli_prepare($db, "INSERT INTO shipping_rates (rate_group, label, max_kg, fee, sort_order, is_active) VALUES (?, ?, ?, ?, ?, 1)");

            $sortOrder = 0;
            foreach ($ratesData as $group => $items) {
                foreach ($items as $item) {
                    $sortOrder++;
                    $label = $item['label'] ?? '';
                    $maxKg = floatval($item['max_kg'] ?? 0);
                    $fee = intval($item['fee'] ?? 0);

                    if ($maxKg <= 0 || $fee <= 0) continue;

                    mysqli_stmt_bind_param($stmt, "ssdii", $group, $label, $maxKg, $fee, $sortOrder);
                    mysqli_stmt_execute($stmt);
                }
            }

            mysqli_commit($db);
            ShippingCalculator::$cachedRates = null;

            echo json_encode(['success' => true, 'message' => '요금표가 저장되었습니다.']);
        } catch (\Exception $e) {
            mysqli_rollback($db);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'unknown action']);
}
