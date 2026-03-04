<?php
/**
 * OrderGroupHelper - 주문 그룹/건수 표시 공유 헬퍼
 *
 * order_group_id 기반으로 그룹을 분석하고
 * 건수/그룹 표시에 필요한 정보를 제공합니다.
 *
 * 사용처:
 *   - OrderComplete_universal.php (주문완료)
 *   - mypage/orders.php (고객 주문 목록)
 *   - admin/order_manager.php (관리자 주문 관리)
 *   - admin/orderlist.php (관리자 주문 목록) — 자체 인라인 구현 있음
 *
 * @author Claude
 * @date 2026-03-04
 */

class OrderGroupHelper
{
    /**
     * 주문 배열에서 그룹 정보 추출
     *
     * @param array $orders 주문 배열 (각 요소에 order_group_id, Type, money_5 필요)
     * @return array [
     *   'groups' => [gid => [orders]],
     *   'ungrouped' => [orders],
     *   'group_info' => [gid => ['total', 'spec_counts', 'is_multi_spec']]
     * ]
     */
    public static function analyzeGroups(array $orders): array
    {
        $group_map = [];
        $ungrouped = [];

        foreach ($orders as $o) {
            $gid = $o['order_group_id'] ?? null;
            if (!empty($gid)) {
                $group_map[$gid][] = $o;
            } else {
                $ungrouped[] = $o;
            }
        }

        $group_info = [];
        foreach ($group_map as $gid => $gorders) {
            $spec_counts = [];
            foreach ($gorders as $go) {
                $specKey = ($go['Type'] ?? '') . '|' . ($go['money_5'] ?? '');
                $spec_counts[$specKey] = ($spec_counts[$specKey] ?? 0) + 1;
            }
            $group_info[$gid] = [
                'total' => count($gorders),
                'spec_counts' => $spec_counts,
                'is_multi_spec' => count($spec_counts) > 1,
            ];
        }

        return [
            'groups' => $group_map,
            'ungrouped' => $ungrouped,
            'group_info' => $group_info,
        ];
    }

    /**
     * 같은 사양 주문끼리 축약 (주문완료 페이지용)
     * Type + money_5가 같으면 하나의 그룹으로 합침
     *
     * @param array $orders 주문 배열
     * @return array [
     *   ['representative' => order, 'count' => N, 'order_nos' => [nos], 'total_price' => sum],
     *   ...
     * ]
     */
    public static function collapseIdentical(array $orders): array
    {
        $spec_groups = [];

        foreach ($orders as $o) {
            $specKey = ($o['Type'] ?? '') . '|' . ($o['money_5'] ?? '');
            if (!isset($spec_groups[$specKey])) {
                $spec_groups[$specKey] = [
                    'representative' => $o,
                    'count' => 0,
                    'order_nos' => [],
                    'total_supply' => 0,
                    'total_vat' => 0,
                ];
            }
            $spec_groups[$specKey]['count']++;
            $spec_groups[$specKey]['order_nos'][] = $o['no'];
            $spec_groups[$specKey]['total_supply'] += intval($o['money_4'] ?? 0);
            $spec_groups[$specKey]['total_vat'] += intval($o['money_5'] ?? 0);
        }

        return array_values($spec_groups);
    }

    /**
     * xN건 배지 HTML 생성
     */
    public static function countBadge(int $count, string $size = 'normal'): string
    {
        if ($count <= 1) return '';

        $styles = [
            'small' => 'font-size:10px;padding:1px 6px;',
            'normal' => 'font-size:11px;padding:1px 8px;',
            'large' => 'font-size:12px;padding:2px 10px;',
        ];
        $style = $styles[$size] ?? $styles['normal'];

        return '<span style="display:inline-block;background:#e74c3c;color:white;' . $style
             . 'border-radius:10px;margin-left:4px;font-weight:bold;">'
             . "\xC3\x97" . $count . '건</span>';
    }

    /**
     * 그룹 순서 라벨 (건 1/3)
     */
    public static function groupSeqLabel(int $seq, int $total): string
    {
        return '<span style="display:inline-block;font-size:10px;color:#666;margin-left:4px;">'
             . '건 ' . $seq . '/' . $total . '</span>';
    }

    /**
     * 합산 금액 표시 HTML (공급가액 기준)
     */
    public static function sumPriceHtml(int $unit_price, int $count): string
    {
        if ($count <= 1) return number_format($unit_price) . '원';
        $total = $unit_price * $count;
        return number_format($unit_price) . '원 × ' . $count . '건 = <strong>' . number_format($total) . '원</strong>';
    }

    /**
     * 리스트 페이지용: 현재 페이지의 주문 배열에서 각 주문의 그룹 건수를 계산
     * DB 서브쿼리 없이, 현재 페이지 데이터만으로 판단
     *
     * @param array $orders 현재 페이지의 주문 배열
     * @return array [order_no => ['group_total' => N, 'group_seq' => M, 'is_first' => bool]]
     */
    public static function getPageGroupInfo(array $orders): array
    {
        $analysis = self::analyzeGroups($orders);
        $result = [];

        foreach ($analysis['groups'] as $gid => $gorders) {
            $total = count($gorders);
            if ($total <= 1) continue; // 단건은 스킵

            foreach ($gorders as $idx => $o) {
                $result[$o['no']] = [
                    'group_id' => $gid,
                    'group_total' => $total,
                    'group_seq' => $idx + 1,
                    'is_first' => ($idx === 0),
                    'is_multi_spec' => $analysis['group_info'][$gid]['is_multi_spec'],
                ];
            }
        }

        return $result;
    }

    /**
     * DB에서 order_group_id로 그룹 총 건수 조회 (페이지네이션 환경용)
     *
     * @param mysqli $db
     * @param string $group_id
     * @return int
     */
    public static function getGroupCountFromDB($db, string $group_id): int
    {
        if (empty($group_id)) return 1;

        $stmt = mysqli_prepare($db, "SELECT COUNT(*) as cnt FROM mlangorder_printauto WHERE order_group_id = ?");
        if (!$stmt) return 1;

        mysqli_stmt_bind_param($stmt, 's', $group_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return intval($row['cnt'] ?? 1);
    }
}
