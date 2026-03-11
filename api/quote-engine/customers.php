<?php
/**
 * 견적엔진 API — 거래처 관리
 * GET/POST /api/quote-engine/customers.php
 *
 * GET 액션:
 *   ?action=search&q=검색어              → 자동완성 검색
 *   ?action=recent                       → 최근 사용 거래처 5건
 *   ?action=get&id=1                     → 단건 조회
 *   ?action=list&page=1&search=검색어    → 페이지네이션 목록
 *
 * POST 액션:
 *   ?action=save   Body: { company, name, phone, email, address, business_number, memo }
 *   ?action=update Body: { id, company, name, ... }
 *   ?action=delete Body: { id }
 */

header('Content-Type: application/json; charset=utf-8');
session_start();


if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../../db.php';
mysqli_set_charset($db, 'utf8mb4');
require_once __DIR__ . '/../../includes/quote-engine/CustomerManager.php';

try {
    $action  = trim($_GET['action'] ?? $_POST['action'] ?? '');
    $method  = $_SERVER['REQUEST_METHOD'];
    $manager = new QE_CustomerManager($db);

    // ════════════════════════════════════════════════════
    //  GET 요청
    // ════════════════════════════════════════════════════
    if ($method === 'GET') {
        switch ($action) {
            // ── 자동완성 검색 ──
            case 'search':
                $q = trim($_GET['q'] ?? '');
                if ($q === '') {
                    echo json_encode(['success' => true, 'data' => []], JSON_UNESCAPED_UNICODE);
                    exit;
                }
                $limit = min((int)($_GET['limit'] ?? 10), 50);
                $results = $manager->search($q, $limit);
                echo json_encode(['success' => true, 'data' => $results], JSON_UNESCAPED_UNICODE);
                break;

            // ── 최근 사용 거래처 ──
            case 'recent':
                $limit = min((int)($_GET['limit'] ?? 5), 20);
                $results = $manager->getRecent($limit);
                echo json_encode(['success' => true, 'data' => $results], JSON_UNESCAPED_UNICODE);
                break;

            // ── 단건 조회 ──
            case 'get':
                $id = (int)($_GET['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'id 파라미터가 필요합니다']);
                    exit;
                }
                $customer = $manager->get($id);
                if (!$customer) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => '거래처를 찾을 수 없습니다']);
                    exit;
                }
                echo json_encode(['success' => true, 'data' => $customer], JSON_UNESCAPED_UNICODE);
                break;

            // ── 목록 (페이지네이션) ──
            case 'list':
                $page    = max(1, (int)($_GET['page'] ?? 1));
                $perPage = min(max(1, (int)($_GET['per_page'] ?? 20)), 100);
                $search  = trim($_GET['search'] ?? '');
                $result  = $manager->listAll($page, $perPage, $search);
                echo json_encode(['success' => true, 'data' => $result], JSON_UNESCAPED_UNICODE);
                break;

            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => 'GET: 지원하지 않는 action입니다 (search, recent, get, list)',
                ], JSON_UNESCAPED_UNICODE);
                break;
        }
        exit;
    }

    // ════════════════════════════════════════════════════
    //  POST 요청
    // ════════════════════════════════════════════════════
    if ($method === 'POST') {
        // JSON body 파싱
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => '잘못된 JSON 형식입니다']);
                exit;
            }
        } else {
            $input = $_POST;
        }

        // action이 GET param에 있을 수도 있음
        if ($action === '') {
            $action = $input['action'] ?? '';
        }

        switch ($action) {
            // ── 거래처 등록 ──
            case 'save':
                $result = $manager->save([
                    'company'         => $input['company'] ?? null,
                    'name'            => $input['name'] ?? '',
                    'phone'           => $input['phone'] ?? null,
                    'email'           => $input['email'] ?? null,
                    'address'         => $input['address'] ?? null,
                    'business_number' => $input['business_number'] ?? null,
                    'memo'            => $input['memo'] ?? null,
                ]);

                if (!$result['success']) {
                    http_response_code(400);
                }
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                break;

            // ── 거래처 수정 ──
            case 'update':
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'id가 필요합니다']);
                    exit;
                }

                $result = $manager->update($id, [
                    'company'         => $input['company'] ?? null,
                    'name'            => $input['name'] ?? '',
                    'phone'           => $input['phone'] ?? null,
                    'email'           => $input['email'] ?? null,
                    'address'         => $input['address'] ?? null,
                    'business_number' => $input['business_number'] ?? null,
                    'memo'            => $input['memo'] ?? null,
                ]);

                if (!$result['success']) {
                    http_response_code(400);
                }
                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                break;

            // ── 거래처 삭제 ──
            case 'delete':
                $id = (int)($input['id'] ?? 0);
                if ($id <= 0) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'id가 필요합니다']);
                    exit;
                }

                $ok = $manager->delete($id);
                if (!$ok) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => '거래처를 찾을 수 없거나 삭제 실패']);
                    exit;
                }
                echo json_encode(['success' => true]);
                break;

            default:
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error'   => 'POST: 지원하지 않는 action입니다 (save, update, delete)',
                ], JSON_UNESCAPED_UNICODE);
                break;
        }
        exit;
    }

    // 그 외 메서드
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'GET 또는 POST만 허용됩니다']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
