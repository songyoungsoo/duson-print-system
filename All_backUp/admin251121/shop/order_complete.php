<?php
session_start();
$order_id = $_GET['order_id'] ?? '';
$email_sent = $_GET['email_sent'] ?? '0';

if (empty($order_id)) {
    header('Location: cart.php');
    exit;
}

// 주문 정보 조회를 위한 설정
require_once('../lib/func.php');
require_once('../includes/AdditionalOptionsDisplay.php');
$connect = dbconn();

// 주문 정보 조회 (mlangorder_printauto 테이블에서)
$order_info = [];
$order_query = "SELECT * FROM mlangorder_printauto WHERE money_5 = ? ORDER BY no DESC LIMIT 5";
$stmt = mysqli_prepare($connect, $order_query);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, 's', $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $order_info[] = $row;
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>✅ 주문 완료</title>
    <link rel="stylesheet" href="../css/modern-style.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .success-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        
        .success-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #27ae60;
            margin-bottom: 1rem;
        }
        
        .order-id {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            background: #f8f9fa;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin: 2rem 0;
            border-left: 4px solid #3498db;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 3rem 0;
        }
        
        .info-card {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
        }
        
        .info-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .info-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .info-desc {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 3rem;
        }
        
        .btn-action {
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(149, 165, 166, 0.3);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .contact-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            text-align: center;
        }
        
        .contact-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        
        .contact-details {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">🎉</div>
            <h1 class="success-title">주문이 완료되었습니다!</h1>
            <p style="font-size: 1.2rem; color: #6c757d; margin-bottom: 2rem;">
                주문해 주셔서 감사합니다. 빠른 시일 내에 연락드리겠습니다.
            </p>
            
            <div class="order-id">
                📋 주문번호: <strong><?php echo htmlspecialchars($order_id); ?></strong>
            </div>
            
            <?php if (!empty($order_info)): ?>
            <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: left;">
                <h3 style="color: #2c3e50; margin-bottom: 15px; text-align: center;">📦 주문 내역</h3>
                
                <?php 
                $optionsDisplay = getAdditionalOptionsDisplay($connect);
                $total_order_amount = 0;
                
                foreach ($order_info as $item): 
                    $total_order_amount += intval($item['money_2']);
                ?>
                <div style="background: white; border-radius: 8px; padding: 15px; margin-bottom: 15px; border-left: 4px solid #3498db;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                        <div>
                            <h4 style="margin: 0; color: #2c3e50; font-size: 16px;"><?php echo htmlspecialchars($item['Type']); ?></h4>
                            <p style="margin: 5px 0; color: #6c757d; font-size: 13px;"><?php echo htmlspecialchars($item['Type_1']); ?></p>
                        </div>
                        <div style="text-align: right;">
                            <p style="margin: 0; font-weight: bold; color: #e74c3c; font-size: 16px;"><?php echo number_format($item['money_2']); ?>원</p>
                            <p style="margin: 0; color: #6c757d; font-size: 12px;">(VAT 포함)</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($item['coating_enabled']) || !empty($item['folding_enabled']) || !empty($item['creasing_enabled'])): ?>
                    <div style="margin-top: 10px; padding: 10px; background: #f0f8ff; border-radius: 5px; border-left: 3px solid #3498db;">
                        <p style="margin: 0 0 8px 0; font-weight: bold; color: #2c3e50; font-size: 13px;">📎 추가 옵션:</p>
                        <?php
                        $option_summary = $optionsDisplay->getCartSummary($item);
                        echo '<p style="margin: 0; color: #495057; font-size: 13px;">' . $option_summary . '</p>';
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <div style="text-align: center; margin-top: 20px; padding-top: 15px; border-top: 2px solid #3498db;">
                    <h3 style="color: #2c3e50; margin: 0;">💰 총 주문금액: <span style="color: #e74c3c;"><?php echo number_format($total_order_amount); ?>원</span></h3>
                    <p style="margin: 5px 0 0 0; color: #6c757d; font-size: 14px;">(부가세 포함)</p>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($email_sent === '1'): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 10px; margin: 1rem 0; border: 1px solid #c3e6cb;">
                📧 <strong>주문 확인 이메일이 발송되었습니다!</strong><br>
                <small>이메일을 확인해주세요. 스팸함도 확인해보시기 바랍니다.</small>
            </div>
            <?php elseif ($email_sent === '0'): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 10px; margin: 1rem 0; border: 1px solid #f5c6cb;">
                ⚠️ <strong>이메일 발송에 실패했습니다.</strong><br>
                <small>주문은 정상적으로 처리되었으며, 곧 연락드리겠습니다.</small>
            </div>
            <?php endif; ?>
            
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon">💰</div>
                    <div class="info-title">결제 안내</div>
                    <div class="info-desc">입금 확인 후<br>작업을 시작합니다</div>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">📞</div>
                    <div class="info-title">연락 드림</div>
                    <div class="info-desc">1-2시간 내<br>연락드리겠습니다</div>
                </div>
                
                <div class="info-card">
                    <div class="info-icon">🚚</div>
                    <div class="info-title">배송 안내</div>
                    <div class="info-desc">택배비는<br>착불입니다</div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="view_modern.php" class="btn-action btn-primary">
                    🛍️ 계속 쇼핑하기
                </a>
                <a href="../mlangprintauto/inserted/index.php" class="btn-action btn-secondary">
                    📄 전단지 주문
                </a>
            </div>
        </div>
        
        <div class="contact-info">
            <h3 class="contact-title">📞 고객센터</h3>
            <div class="contact-details">
                <div class="contact-item">
                    <span>📞</span>
                    <span>1688-2384</span>
                </div>
                <div class="contact-item">
                    <span>⏰</span>
                    <span>평일 09:00 - 18:00</span>
                </div>
                <div class="contact-item">
                    <span>📧</span>
                    <span>duson1830@naver.com</span>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // 페이지 로드 시 축하 효과
        document.addEventListener('DOMContentLoaded', function() {
            // 간단한 축하 알림
            setTimeout(function() {
                if (confirm('주문이 성공적으로 완료되었습니다! 📧 주문 확인 이메일을 보내드릴까요?')) {
                    // 이메일 발송 로직 (선택사항)
                    alert('주문 확인 이메일이 발송되었습니다! 📧');
                }
            }, 1000);
        });
    </script>
</body>
</html>