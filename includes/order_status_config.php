<?php
// OrderStyle 상태 매핑 SSOT — 변경 시 6개 파일에 영향:
// dashboard/proofs/index.php, dashboard/orders/index.php, dashboard/orders/view.php,
// mypage/index.php, mypage/orders.php, sub/checkboard.php

function getAdminStatusMap() {
    return [
        '0'  => ['label' => '미선택',     'bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'color' => '#6b7280'],
        '1'  => ['label' => '견적접수',   'bg' => 'bg-slate-100',  'text' => 'text-slate-700',  'color' => '#64748b'],
        '2'  => ['label' => '주문접수',   'bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'color' => '#d97706'],
        '3'  => ['label' => '접수완료',   'bg' => 'bg-amber-100',  'text' => 'text-amber-800',  'color' => '#d97706'],
        '4'  => ['label' => '입금대기',   'bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'color' => '#ea580c'],
        '5'  => ['label' => '시안제작중', 'bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'color' => '#4f46e5'],
        '6'  => ['label' => '시안',       'bg' => 'bg-violet-100', 'text' => 'text-violet-700', 'color' => '#7c3aed'],
        '7'  => ['label' => '교정',       'bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'color' => '#2563eb'],
        '8'  => ['label' => '작업완료',   'bg' => 'bg-green-100',  'text' => 'text-green-800',  'color' => '#16a34a'],
        '9'  => ['label' => '작업중',     'bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'color' => '#9333ea'],
        '10' => ['label' => '교정작업중', 'bg' => 'bg-cyan-100',   'text' => 'text-cyan-700',   'color' => '#0891b2'],
        '11' => ['label' => '카드결제',   'bg' => 'bg-pink-100',   'text' => 'text-pink-700',   'color' => '#be185d'],
        'deleted' => ['label' => '삭제됨', 'bg' => 'bg-red-100',   'text' => 'text-red-800',   'color' => '#dc2626'],
    ];
}

function getAdminStatusLabels() {
    $labels = [];
    foreach (getAdminStatusMap() as $key => $info) {
        $labels[$key] = $info['label'];
    }
    return $labels;
}

function getAdminStatusColors() {
    $colors = [];
    foreach (getAdminStatusMap() as $key => $info) {
        $sk = (string)$key;
        if ($sk === '0' || $sk === 'deleted') continue;
        $colors[$sk] = $info['color'];
    }
    return $colors;
}

function getPublicStatusMap() {
    return [
        '2'  => '접수중',
        '3'  => '접수완료',
        '4'  => '입금대기',
        '5'  => '시안제작중',
        '6'  => '시안완료',
        '7'  => '교정중',
        '8'  => '작업완료',
        '9'  => '작업중',
        '10' => '교정작업중',
    ];
}

function getCustomerStatus($orderStyle, $order = null) {
    $os = (string)$orderStyle;
    if ($order) {
        $tracking = ($order['waybill_no'] ?? '') ?: ($order['logen_tracking_no'] ?? '');
        if (!empty($tracking)) {
            return ['text' => '배송중', 'color' => '#28a745', 'group' => 'shipping'];
        }
    }
    switch ($os) {
        case '0': case '1': case '2':
            return ['text' => '주문접수', 'color' => '#6c757d', 'group' => 'received'];
        case '3': case '4':
            return ['text' => '접수완료', 'color' => '#17a2b8', 'group' => 'confirmed'];
        case '5': case '6': case '7': case '9': case '10':
            return ['text' => '작업중', 'color' => '#f59e0b', 'group' => 'working'];
        case '8':
            return ['text' => '작업완료', 'color' => '#10b981', 'group' => 'completed'];
        case 'deleted':
            return ['text' => '주문취소', 'color' => '#dc3545', 'group' => 'cancelled'];
        default:
            return ['text' => '주문접수', 'color' => '#6c757d', 'group' => 'received'];
    }
}

function getStatusFilterCondition($filterGroup) {
    switch ($filterGroup) {
        case 'received':  return "OrderStyle IN ('0','1','2')";
        case 'confirmed': return "OrderStyle IN ('3','4')";
        case 'working':   return "OrderStyle IN ('5','6','7','9','10')";
        case 'completed': return "OrderStyle = '8'";
        case 'shipping':  return "(waybill_no IS NOT NULL AND waybill_no != '' OR logen_tracking_no IS NOT NULL AND logen_tracking_no != '')";
        default: return '';
    }
}

function getAdminStatusMapAsJson() {
    return json_encode(getAdminStatusMap(), JSON_UNESCAPED_UNICODE);
}

function getAdminStatusOptionsAsJson() {
    $options = [];
    foreach (getAdminStatusMap() as $key => $info) {
        $sk = (string)$key;
        if ($sk === '0' || $sk === 'deleted') continue;
        $options[] = ['v' => $sk, 'l' => $info['label']];
    }
    return json_encode($options, JSON_UNESCAPED_UNICODE);
}

function getAdminStatusColorsAsJson() {
    return json_encode(getAdminStatusColors(), JSON_UNESCAPED_UNICODE);
}
