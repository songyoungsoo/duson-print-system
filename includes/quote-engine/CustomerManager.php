<?php
/**
 * QE_CustomerManager — 견적엔진 거래처/고객 관리
 * 경로: /includes/quote-engine/CustomerManager.php
 *
 * 테이블: qe_customers
 * 모든 SQL은 prepared statement + bind_param 3단계 검증.
 */

class QE_CustomerManager
{
    /** @var mysqli */
    private $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    // ═══════════════════════════════════════════════════════════
    //  CREATE
    // ═══════════════════════════════════════════════════════════

    /**
     * 거래처 저장
     *
     * @param array $data company, name(필수), phone, email, address, business_number, memo
     * @return array ['success'=>bool, 'id'=>int]
     */
    public function save(array $data): array
    {
        if (empty($data['name'])) {
            return ['success' => false, 'error' => '담당자명은 필수입니다'];
        }

        $sql = "INSERT INTO qe_customers (company, name, phone, email, address, business_number, memo)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            return ['success' => false, 'error' => 'INSERT 준비 실패: ' . mysqli_error($this->db)];
        }

        $company  = $data['company'] ?? null;
        $name     = $data['name'];
        $phone    = $data['phone'] ?? null;
        $email    = $data['email'] ?? null;
        $address  = $data['address'] ?? null;
        $bizNo    = $data['business_number'] ?? null;
        $memo     = $data['memo'] ?? null;

        // bind_param 검증: ? = 7, 타입 = 7, 변수 = 7 ✓
        mysqli_stmt_bind_param($stmt, 'sssssss',
            $company, $name, $phone, $email, $address, $bizNo, $memo
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return ['success' => false, 'error' => 'INSERT 실패: ' . mysqli_stmt_error($stmt)];
        }

        $id = (int)mysqli_insert_id($this->db);
        mysqli_stmt_close($stmt);

        return ['success' => true, 'id' => $id];
    }

    // ═══════════════════════════════════════════════════════════
    //  READ
    // ═══════════════════════════════════════════════════════════

    /**
     * 단건 조회
     */
    public function get(int $id): ?array
    {
        $stmt = mysqli_prepare($this->db, "SELECT * FROM qe_customers WHERE id = ?");
        if (!$stmt) return null;

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $row ?: null;
    }

    /**
     * 검색 (자동완성용)
     * company, name, phone, email 에서 LIKE 검색
     *
     * @param string $query 검색어
     * @param int    $limit 최대 결과 수
     * @return array
     */
    public function search(string $query, int $limit = 10): array
    {
        $query = trim($query);
        if ($query === '') return [];

        $like = '%' . $query . '%';

        $sql = "SELECT * FROM qe_customers
                WHERE company LIKE ? OR name LIKE ? OR phone LIKE ? OR email LIKE ?
                ORDER BY use_count DESC, last_used_at DESC
                LIMIT ?";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];

        // bind_param 검증: ? = 5, 타입 = 5, 변수 = 5 ✓
        mysqli_stmt_bind_param($stmt, 'ssssi', $like, $like, $like, $like, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $rows;
    }

    /**
     * 목록 조회 (페이지네이션)
     *
     * @param int    $page
     * @param int    $perPage
     * @param string $search  검색어 (선택)
     * @return array ['items'=>[...], 'total'=>N, 'page'=>N, 'pages'=>N]
     */
    public function listAll(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $conditions = [];
        $types = '';
        $values = [];

        if ($search !== '') {
            $like = '%' . $search . '%';
            $conditions[] = '(company LIKE ? OR name LIKE ? OR phone LIKE ? OR email LIKE ?)';
            $types .= 'ssss';
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
            $values[] = $like;
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

        // ── COUNT ──
        $countSql = "SELECT COUNT(*) AS cnt FROM qe_customers {$where}";
        $countStmt = mysqli_prepare($this->db, $countSql);

        if ($types !== '' && count($values) > 0) {
            mysqli_stmt_bind_param($countStmt, $types, ...$values);
        }
        mysqli_stmt_execute($countStmt);
        $total = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($countStmt))['cnt'] ?? 0);
        mysqli_stmt_close($countStmt);

        $pages = ($perPage > 0) ? (int)ceil($total / $perPage) : 1;
        $page  = max(1, min($page, $pages ?: 1));
        $offset = ($page - 1) * $perPage;

        // ── SELECT ──
        $dataSql = "SELECT * FROM qe_customers {$where}
                    ORDER BY use_count DESC, last_used_at DESC, id DESC
                    LIMIT ? OFFSET ?";

        $dataTypes  = $types . 'ii';
        $dataValues = array_merge($values, [$perPage, $offset]);

        $dataStmt = mysqli_prepare($this->db, $dataSql);

        // bind_param 검증
        $placeholders = substr_count($dataSql, '?');
        $typeLen = strlen($dataTypes);
        $valLen  = count($dataValues);
        if ($placeholders !== $typeLen || $typeLen !== $valLen) {
            return ['items' => [], 'total' => 0, 'page' => 1, 'pages' => 0];
        }

        mysqli_stmt_bind_param($dataStmt, $dataTypes, ...$dataValues);
        mysqli_stmt_execute($dataStmt);
        $result = mysqli_stmt_get_result($dataStmt);

        $items = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
        mysqli_stmt_close($dataStmt);

        return [
            'items' => $items,
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ];
    }

    /**
     * 최근 사용 거래처 조회
     */
    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT * FROM qe_customers
                WHERE last_used_at IS NOT NULL
                ORDER BY last_used_at DESC
                LIMIT ?";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) return [];

        mysqli_stmt_bind_param($stmt, 'i', $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $rows;
    }

    // ═══════════════════════════════════════════════════════════
    //  UPDATE
    // ═══════════════════════════════════════════════════════════

    /**
     * 거래처 수정
     */
    public function update(int $id, array $data): array
    {
        // 존재 확인
        if (!$this->get($id)) {
            return ['success' => false, 'error' => '거래처를 찾을 수 없습니다'];
        }

        if (empty($data['name'])) {
            return ['success' => false, 'error' => '담당자명은 필수입니다'];
        }

        $sql = "UPDATE qe_customers SET
                    company = ?, name = ?, phone = ?, email = ?,
                    address = ?, business_number = ?, memo = ?
                WHERE id = ?";

        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            return ['success' => false, 'error' => 'UPDATE 준비 실패'];
        }

        $company  = $data['company'] ?? null;
        $name     = $data['name'];
        $phone    = $data['phone'] ?? null;
        $email    = $data['email'] ?? null;
        $address  = $data['address'] ?? null;
        $bizNo    = $data['business_number'] ?? null;
        $memo     = $data['memo'] ?? null;

        // bind_param 검증: ? = 8, 타입 = 8, 변수 = 8 ✓
        mysqli_stmt_bind_param($stmt, 'sssssssi',
            $company, $name, $phone, $email, $address, $bizNo, $memo, $id
        );

        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return ['success' => false, 'error' => 'UPDATE 실패'];
        }
        mysqli_stmt_close($stmt);

        return ['success' => true, 'id' => $id];
    }

    /**
     * 사용 횟수 증가 + 마지막 사용 시간 갱신
     * (견적서에서 이 거래처 사용 시 호출)
     */
    public function markUsed(int $id): void
    {
        $stmt = mysqli_prepare($this->db,
            "UPDATE qe_customers SET use_count = use_count + 1, last_used_at = NOW() WHERE id = ?"
        );
        if (!$stmt) return;

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // ═══════════════════════════════════════════════════════════
    //  DELETE
    // ═══════════════════════════════════════════════════════════

    /**
     * 거래처 삭제
     */
    public function delete(int $id): bool
    {
        $stmt = mysqli_prepare($this->db, "DELETE FROM qe_customers WHERE id = ?");
        if (!$stmt) return false;

        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);

        return $ok && $affected > 0;
    }
}
