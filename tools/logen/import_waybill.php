<?php
/**
 * 로젠 운송장번호 일괄 등록 API
 *
 * logen_auto.js에서 호출하는 내부 전용 엔드포인트.
 * 주문번호(ordNo) 또는 수하인명(rcvNm)으로 매칭하여 운송장번호 등록.
 *
 * POST JSON:
 * {
 *   "items": [
 *     { "slipNo": "41234567890", "rcvNm": "홍길동", "ordNo": "123" },
 *     ...
 *   ]
 * }
 *
 * Response JSON:
 * {
 *   "success": true,
 *   "updated": 3,
 *   "failed": 1,
 *   "details": [
 *     { "slipNo": "41234567890", "rcvNm": "홍길동", "ordNo": "123", "status": "ok", "matchedBy": "ordNo", "dbNo": 123 },
 *     { "slipNo": "41234567891", "rcvNm": "김철수", "ordNo": "", "status": "ok", "matchedBy": "name", "dbNo": 456 },
 *     ...
 *   ]
 * }
 */

header('Content-Type: application/json; charset=utf-8');

// 인증: localhost는 통과, 외부 요청은 _eauth 토큰 필수
$allowed_ips = ['127.0.0.1', '::1', 'localhost'];
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remote, $allowed_ips)) {
    // _eauth 토큰 검증 (delivery_manager.php와 동일 패턴)
    $eauth = $_GET['_eauth'] ?? $_POST['_eauth'] ?? '';
    $expected = hash_hmac('sha256', '/tools/logen/import_waybill.php' . date('Y-m-d'), 'duson_embed_2026_secret');
    if (empty($eauth) || !hash_equals($expected, $eauth)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
}

// DB 연결
require_once __DIR__ . '/../../db.php';

// JSON 입력 파싱
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['items']) || !is_array($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input: items array required']);
    exit;
}

$items = $input['items'];
$updated = 0;
$failed = 0;
$details = [];

// 날짜 범위 (최근 30일 이내의 주문만 매칭)
$dateLimit = date('Y-m-d', strtotime('-30 days'));

foreach ($items as $item) {
    $slipNo = trim($item['slipNo'] ?? '');
    $rcvNm = trim($item['rcvNm'] ?? '');
    $ordNo = trim($item['ordNo'] ?? '');

    // 운송장번호 필수 (4로 시작하는 11자리)
    if (empty($slipNo) || !preg_match('/^4\d{10}$/', $slipNo)) {
        $details[] = [
            'slipNo' => $slipNo,
            'rcvNm' => $rcvNm,
            'ordNo' => $ordNo,
            'status' => 'skip',
            'reason' => 'invalid waybill number',
        ];
        continue;
    }

    // 이미 등록된 운송장번호인지 체크
    $checkStmt = mysqli_prepare($db,
        "SELECT no FROM mlangorder_printauto WHERE waybill_no = ? LIMIT 1");
    mysqli_stmt_bind_param($checkStmt, "s", $slipNo);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    if (mysqli_fetch_assoc($checkResult)) {
        $details[] = [
            'slipNo' => $slipNo,
            'rcvNm' => $rcvNm,
            'ordNo' => $ordNo,
            'status' => 'skip',
            'reason' => 'already registered',
        ];
        mysqli_stmt_close($checkStmt);
        continue;
    }
    mysqli_stmt_close($checkStmt);

    $matched = false;
    $matchedBy = '';
    $dbNo = 0;

    // 방법 1: ordNo로 매칭 (주문번호가 숫자인 경우)
    $cleanOrdNo = preg_replace('/^dsno/i', '', $ordNo);
    if (!empty($cleanOrdNo) && is_numeric($cleanOrdNo)) {
        $stmt = mysqli_prepare($db,
            "UPDATE mlangorder_printauto
             SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
             WHERE no = ? AND (waybill_no IS NULL OR waybill_no = '')");
        $intOrdNo = intval($cleanOrdNo);
        mysqli_stmt_bind_param($stmt, "si", $slipNo, $intOrdNo);
        mysqli_stmt_execute($stmt);
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $matched = true;
            $matchedBy = 'ordNo';
            $dbNo = $intOrdNo;
        }
        mysqli_stmt_close($stmt);
    }

    // 방법 2: 수하인명으로 매칭 (ordNo 매칭 실패 시)
    if (!$matched && !empty($rcvNm)) {
        $nameStmt = mysqli_prepare($db,
            "SELECT no, name FROM mlangorder_printauto
             WHERE (waybill_no IS NULL OR waybill_no = '')
               AND date >= ?
               AND name = ?
             ORDER BY date DESC
             LIMIT 1");
        mysqli_stmt_bind_param($nameStmt, "ss", $dateLimit, $rcvNm);
        mysqli_stmt_execute($nameStmt);
        $nameResult = mysqli_stmt_get_result($nameStmt);
        $nameRow = mysqli_fetch_assoc($nameResult);
        mysqli_stmt_close($nameStmt);

        if ($nameRow) {
            $updateStmt = mysqli_prepare($db,
                "UPDATE mlangorder_printauto
                 SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                 WHERE no = ?");
            $matchNo = intval($nameRow['no']);
            mysqli_stmt_bind_param($updateStmt, "si", $slipNo, $matchNo);
            mysqli_stmt_execute($updateStmt);
            if (mysqli_stmt_affected_rows($updateStmt) > 0) {
                $matched = true;
                $matchedBy = 'name';
                $dbNo = $matchNo;
            }
            mysqli_stmt_close($updateStmt);
        }
    }

    // 방법 3: 전화번호로 매칭 (이름 매칭 실패 시)
    if (!$matched) {
        $rcvTel = trim($item['rcvTel'] ?? '');
        $rcvHp = trim($item['rcvHp'] ?? '');
        // 전화번호 정규화 (숫자만 추출)
        $phones = [];
        if (!empty($rcvHp)) $phones[] = preg_replace('/[^0-9]/', '', $rcvHp);
        if (!empty($rcvTel) && $rcvTel !== $rcvHp) $phones[] = preg_replace('/[^0-9]/', '', $rcvTel);

        foreach ($phones as $phone) {
            if (strlen($phone) < 9) continue;
            // phone 또는 Hendphone 컬럼에서 매칭
            $phoneStmt = mysqli_prepare($db,
                "SELECT no, name FROM mlangorder_printauto
                 WHERE (waybill_no IS NULL OR waybill_no = '')
                   AND date >= ?
                   AND (REPLACE(REPLACE(phone, '-', ''), ' ', '') = ?
                     OR REPLACE(REPLACE(Hendphone, '-', ''), ' ', '') = ?)
                 ORDER BY date DESC
                 LIMIT 1");
            mysqli_stmt_bind_param($phoneStmt, "sss", $dateLimit, $phone, $phone);
            mysqli_stmt_execute($phoneStmt);
            $phoneResult = mysqli_stmt_get_result($phoneStmt);
            $phoneRow = mysqli_fetch_assoc($phoneResult);
            mysqli_stmt_close($phoneStmt);

            if ($phoneRow) {
                $updateStmt = mysqli_prepare($db,
                    "UPDATE mlangorder_printauto
                     SET waybill_no = ?, waybill_date = NOW(), delivery_company = '로젠'
                     WHERE no = ?");
                $matchNo = intval($phoneRow['no']);
                mysqli_stmt_bind_param($updateStmt, "si", $slipNo, $matchNo);
                mysqli_stmt_execute($updateStmt);
                if (mysqli_stmt_affected_rows($updateStmt) > 0) {
                    $matched = true;
                    $matchedBy = 'phone';
                    $dbNo = $matchNo;
                }
                mysqli_stmt_close($updateStmt);
                break;
            }
        }
    }

    if ($matched) {
        $updated++;
        $details[] = [
            'slipNo' => $slipNo,
            'rcvNm' => $rcvNm,
            'ordNo' => $ordNo,
            'status' => 'ok',
            'matchedBy' => $matchedBy,
            'dbNo' => $dbNo,
        ];
    } else {
        $failed++;
        $details[] = [
            'slipNo' => $slipNo,
            'rcvNm' => $rcvNm,
            'ordNo' => $ordNo,
            'status' => 'fail',
            'reason' => 'no matching order found',
        ];
    }
}

echo json_encode([
    'success' => true,
    'updated' => $updated,
    'failed' => $failed,
    'total' => count($items),
    'details' => $details,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
