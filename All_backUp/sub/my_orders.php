<?php
session_start();

// Check authentication
if (!isset($_SESSION['customer_authenticated']) || $_SESSION['customer_authenticated'] !== true) {
    header('Location: my_orders_auth.php');
    exit;
}

// Check session timeout (2 hours for customers)
if (isset($_SESSION['customer_auth_timestamp']) && (time() - $_SESSION['customer_auth_timestamp']) > 7200) {
    session_destroy();
    header('Location: my_orders_auth.php?timeout=1');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: my_orders_auth.php?logout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['customer_auth_timestamp'] = time();

$customer_name = $_SESSION['customer_name'];
$customer_phone = $_SESSION['customer_phone'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>두손기획인쇄 - 내 주문 조회</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+KR:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .user-info {
            flex: 1;
        }
        
        .user-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .user-details {
            font-size: 0.9rem;
            color: #666;
        }
        
        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .session-info {
            font-size: 0.8rem;
            color: #666;
            text-align: right;
        }
        
        .logout-btn {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .logout-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .orders-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            overflow: hidden;
        }
        
        .orders-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            text-align: center;
        }
        
        .orders-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .orders-subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
            font-size: 0.9rem;
        }
        
        .orders-table td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        
        .orders-table tr:hover {
            background: #f9fafb;
        }
        
        .order-number {
            font-weight: 600;
            color: #667eea;
        }
        
        .order-type {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .type-leaflet { background: #dbeafe; color: #1d4ed8; }
        .type-sticker { background: #dcfce7; color: #166534; }
        .type-namecard { background: #fef3c7; color: #92400e; }
        .type-envelope { background: #f3e8ff; color: #7c2d12; }
        .type-poster { background: #fed7e2; color: #be185d; }
        .type-catalog { background: #e0e7ff; color: #3730a3; }
        .type-coupon { background: #fce7f3; color: #be185d; }
        .type-default { background: #f1f5f9; color: #475569; }
        
        .order-status {
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-received { background: #dbeafe; color: #1e40af; }
        .status-design { background: #fef3c7; color: #92400e; }
        .status-correction { background: #fed7e2; color: #be185d; }
        .status-printing { background: #dcfce7; color: #166534; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-working { background: #e0e7ff; color: #3730a3; }
        .status-default { background: #f1f5f9; color: #475569; }
        
        .view-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .view-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-text {
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .footer {
            margin-top: 2rem;
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
        }
        
        .footer-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }
        
        .contact-info {
            color: #666;
            font-size: 0.9rem;
        }
        
        .contact-phone {
            color: #667eea;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .orders-table {
                font-size: 0.8rem;
            }
            
            .orders-table th,
            .orders-table td {
                padding: 0.5rem;
            }
            
            .user-name {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($customer_name) ?>님의 주문 내역</div>
                    <div class="user-details">
                        📞 <?= htmlspecialchars($customer_phone) ?> | 
                        🔒 개인 정보 보호 모드
                    </div>
                </div>
                <div class="header-actions">
                    <div class="session-info">
                        세션 시작: <?= date('Y-m-d H:i', $_SESSION['customer_auth_timestamp']) ?><br>
                        자동 로그아웃: 2시간 후
                    </div>
                    <a href="?logout=1" class="logout-btn">
                        🚪 로그아웃
                    </a>
                </div>
            </div>
        </div>
        
        <div class="orders-container">
            <div class="orders-header">
                <div class="orders-title">내 주문 조회 결과</div>
                <div class="orders-subtitle">본인이 주문한 내역만 표시됩니다</div>
            </div>
            
            <div class="table-container">
                <?php
                include "../db.php";
                
                // 본인의 주문만 조회 (이름과 전화번호가 일치하는 경우)
                $stmt = $db->prepare("
                    SELECT * FROM mlangorder_printauto 
                    WHERE LOWER(name) = LOWER(?) 
                    AND (phone LIKE ? OR phone LIKE ? OR phone LIKE ?) 
                    ORDER BY no DESC
                ");
                
                $phone_patterns = [
                    '%' . $customer_phone . '%',
                    $customer_phone,
                    str_replace('-', '', $customer_phone)
                ];
                
                $stmt->bind_param("ssss", $customer_name, $phone_patterns[0], $phone_patterns[1], $phone_patterns[2]);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>주문번호</th>
                            <th>상품종류</th>
                            <th>주문날짜</th>
                            <th>처리상태</th>
                            <th>담당자</th>
                            <th>시안보기</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><span class="order-number"><?= $row['no'] ?></span></td>
                            <td>
                                <?php
                                $type_class = 'type-default';
                                $type_text = $row['Type'];
                                
                                switch($row['Type']) {
                                    case 'inserted':
                                        $type_class = 'type-leaflet';
                                        $type_text = '전단지';
                                        break;
                                    case 'sticker':
                                        $type_class = 'type-sticker';
                                        $type_text = '스티커';
                                        break;
                                    case 'NameCard':
                                        $type_class = 'type-namecard';
                                        $type_text = '명함';
                                        break;
                                    case 'envelope':
                                        $type_class = 'type-envelope';
                                        $type_text = '봉투';
                                        break;
                                    case 'LittlePrint':
                                        $type_class = 'type-poster';
                                        $type_text = '소량인쇄';
                                        break;
                                    case 'cadarok':
                                        $type_class = 'type-catalog';
                                        $type_text = '카다로그';
                                        break;
                                    case 'MerchandiseBond':
                                        $type_class = 'type-coupon';
                                        $type_text = '상품권';
                                        break;
                                    case 'NcrFlambeau':
                                        $type_text = '양식지';
                                        break;
                                }
                                ?>
                                <span class="order-type <?= $type_class ?>"><?= $type_text ?></span>
                            </td>
                            <td><?= date('Y-m-d', strtotime($row['date'])) ?></td>
                            <td>
                                <?php
                                $status_class = 'status-default';
                                $status_text = '알수없음';
                                
                                switch($row['OrderStyle']) {
                                    case '2':
                                        $status_class = 'status-received';
                                        $status_text = '접수중';
                                        break;
                                    case '3':
                                        $status_class = 'status-received';
                                        $status_text = '접수완료';
                                        break;
                                    case '4':
                                        $status_class = 'status-design';
                                        $status_text = '입금대기';
                                        break;
                                    case '5':
                                        $status_class = 'status-design';
                                        $status_text = '시안제작중';
                                        break;
                                    case '6':
                                        $status_class = 'status-design';
                                        $status_text = '시안';
                                        break;
                                    case '7':
                                        $status_class = 'status-correction';
                                        $status_text = '교정';
                                        break;
                                    case '8':
                                        $status_class = 'status-completed';
                                        $status_text = '작업완료';
                                        break;
                                    case '9':
                                        $status_class = 'status-working';
                                        $status_text = '작업중';
                                        break;
                                    case '10':
                                        $status_class = 'status-correction';
                                        $status_text = '교정작업중';
                                        break;
                                }
                                ?>
                                <span class="order-status <?= $status_class ?>"><?= $status_text ?></span>
                            </td>
                            <td><?= htmlspecialchars($row['Designer'] ?? '미배정') ?></td>
                            <td>
                                <button class="view-btn" onclick="openOrderView(<?= $row['no'] ?>)">
                                    👁️ 시안보기
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php
                } else {
                ?>
                <div class="empty-state">
                    <div class="empty-icon">📋</div>
                    <div class="empty-title">주문 내역이 없습니다</div>
                    <div class="empty-text">
                        아직 주문하신 내역이 없거나,<br>
                        입력하신 정보와 일치하는 주문을 찾을 수 없습니다.<br><br>
                        문의사항이 있으시면 고객센터로 연락주세요.
                    </div>
                </div>
                <?php
                }
                
                $stmt->close();
                $db->close();
                ?>
            </div>
        </div>
        
        <div class="footer">
            <div class="footer-title">🏢 두손기획인쇄</div>
            <div class="contact-info">
                <strong>고객센터:</strong> 
                📞 <span class="contact-phone">02-2632-1830</span> | 
                📞 <span class="contact-phone">1688-2384</span><br>
                <strong>주소:</strong> 서울 영등포구 영등포로 36길 9, 송호빌딩 1F
            </div>
        </div>
    </div>
    
    <script>
        function openOrderView(orderNo) {
            const popup = window.open(
                '/mlangorder_printauto/WindowSian.php?mode=OrderView&no=' + orderNo,
                'OrderView',
                'width=900,height=600,top=50,left=50,menubar=no,resizable=yes,statusbar=no,scrollbars=yes,toolbar=no'
            );
            popup.focus();
        }
        
        // 세션 만료 경고 (1시간 50분 후)
        setTimeout(function() {
            if (confirm('세션이 곧 만료됩니다. 계속 사용하시겠습니까?')) {
                location.reload();
            }
        }, 6600000); // 1시간 50분
        
        // 자동 새로고침 방지를 위한 사용자 활동 감지
        let lastActivity = Date.now();
        
        document.addEventListener('click', function() {
            lastActivity = Date.now();
        });
        
        document.addEventListener('keypress', function() {
            lastActivity = Date.now();
        });
    </script>
</body>
</html>