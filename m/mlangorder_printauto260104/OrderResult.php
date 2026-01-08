<?php
session_start();
ini_set('display_errors', '0');

$HomeDir = "..";
$PageCode = "PrintAuto";
include "$HomeDir/db.php";

// GET 파라미터에서 데이터 가져오기
$OrderStyle = $_GET['OrderStyle'] ?? '';
$no = $_GET['no'] ?? '';
$username = $_GET['username'] ?? '';
$Type_1 = $_GET['Type_1'] ?? '';
$money4 = $_GET['money4'] ?? '';
$money5 = $_GET['money5'] ?? '';
$phone = $_GET['phone'] ?? '';
$Hendphone = $_GET['Hendphone'] ?? '';
$zip1 = $_GET['zip1'] ?? '';
$zip2 = $_GET['zip2'] ?? '';
$email = $_GET['email'] ?? '';
$date = $_GET['date'] ?? '';
$cont = $_GET['cont'] ?? '';
$standard = $_GET['standard'] ?? '';
$page = $_GET['page'] ?? '';
$PageSS = $_GET['PageSS'] ?? '';

// 이메일 발송
include_once('../shop/mailer.lib.php');

// 주문 내역 정리
$order_details = [
    'no' => $no,
    'username' => $username,
    'Type_1' => $Type_1,
    'money4' => number_format($money4),
    'money5' => number_format($money5),
    'phone' => $phone,
    'Hendphone' => $Hendphone,
    'zip1' => $zip1,
    'zip2' => $zip2,
    'email' => $email,
    'date' => $date,
    'cont' => $cont
];

// 이메일 내용 생성 및 발송
function generateEmailContent($details) {
    return "
    <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <h2>주문이 완료되었습니다</h2>
        <p>{$details['username']} 고객님, 주문해 주셔서 감사합니다.</p>
        
        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
            <tr style='background: #f8f9fa;'>
                <td style='padding: 12px; border: 1px solid #ddd; font-weight: bold;'>주문번호</td>
                <td style='padding: 12px; border: 1px solid #ddd;'>{$details['no']}</td>
            </tr>
            <tr>
                <td style='padding: 12px; border: 1px solid #ddd; font-weight: bold;'>주문내용</td>
                <td style='padding: 12px; border: 1px solid #ddd;'>{$details['Type_1']}</td>
            </tr>
            <tr style='background: #f8f9fa;'>
                <td style='padding: 12px; border: 1px solid #ddd; font-weight: bold;'>금액</td>
                <td style='padding: 12px; border: 1px solid #ddd;'>{$details['money4']}원 (부가세 포함: {$details['money5']}원)</td>
            </tr>
            <tr>
                <td style='padding: 12px; border: 1px solid #ddd; font-weight: bold;'>주문일시</td>
                <td style='padding: 12px; border: 1px solid #ddd;'>{$details['date']}</td>
            </tr>
        </table>
        
        <div style='background: #e8f4fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>
            <h3 style='color: #2c3e50; margin-top: 0;'>입금 안내</h3>
            <p><strong>은행:</strong> 국민은행</p>
            <p><strong>계좌번호:</strong> 999-1688-2384</p>
            <p><strong>예금주:</strong> 두손기획인쇄</p>
        </div>
        
        <div style='background: #f8f9fa; padding: 15px; border-radius: 8px;'>
            <p><strong>문의:</strong> 1688-2384, 02-2632-1830</p>
            <p><strong>주소:</strong> 서울특별시 영등포구 영등포로36길9 송호빌딩 1층</p>
            <p><strong>홈페이지:</strong> www.dsp1830.shop</p>
        </div>
    </div>";
}

$email_content = generateEmailContent($order_details);
if (!empty($email)) {
    $subject = "=?UTF-8?B?".base64_encode("$username 님의 주문 내역입니다.")."?=";
    mailer('', '', $email, $subject, $email_content, 1, '', '', '');
}

include $_SERVER['DOCUMENT_ROOT'] . "/mlangprintauto/mlangprintautotop.php";
?>

<!-- 주문 완료 메인 콘텐츠 -->
<div style="padding: 2rem;">
    <!-- 성공 메시지 -->
    <div style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 2rem; border-radius: 15px; text-align: center; margin-bottom: 2rem;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">✅</div>
        <h2 style="font-size: 1.8rem; font-weight: 700; margin-bottom: 0.5rem;">주문이 완료되었습니다!</h2>
        <p style="font-size: 1.1rem; opacity: 0.9;"><?php echo htmlspecialchars($username); ?> 고객님, 주문해 주셔서 감사합니다.</p>
    </div>

    <!-- 주문 정보 카드 -->
    <div style="background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 2rem; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 1.5rem;">
            <h3 style="font-size: 1.5rem; font-weight: 700; margin: 0;">📋 주문 정보</h3>
        </div>
        <div style="padding: 2rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50; width: 150px;">🔢 주문번호</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;"><?php echo htmlspecialchars($no); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50;">📦 주문내용</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;"><?php echo htmlspecialchars($Type_1); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50;">💰 금액</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;">
                        <span style="font-size: 1.2rem; font-weight: 700; color: #e74c3c;"><?php echo number_format($money4); ?>원</span>
                        <span style="color: #6c757d; margin-left: 10px;">(부가세 포함: <?php echo number_format($money5); ?>원)</span>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px; font-weight: 600; color: #2c3e50;">📅 주문일시</td>
                    <td style="padding: 12px; color: #495057;"><?php echo htmlspecialchars($date); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 고객 정보 카드 -->
    <div style="background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 2rem; overflow: hidden;">
        <div style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%); color: white; padding: 1.5rem;">
            <h3 style="font-size: 1.5rem; font-weight: 700; margin: 0;">👤 고객 정보</h3>
        </div>
        <div style="padding: 2rem;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50; width: 150px;">👤 이름</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;"><?php echo htmlspecialchars($username); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50;">📞 연락처</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;">
                        <?php if($phone): ?>전화: <?php echo htmlspecialchars($phone); ?><?php endif; ?>
                        <?php if($phone && $Hendphone): ?> / <?php endif; ?>
                        <?php if($Hendphone): ?>휴대폰: <?php echo htmlspecialchars($Hendphone); ?><?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50;">🏠 주소</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;"><?php echo htmlspecialchars($zip1 . ' ' . $zip2); ?></td>
                </tr>
                <tr>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; font-weight: 600; color: #2c3e50;">📧 이메일</td>
                    <td style="padding: 12px; border-bottom: 1px solid #f1f3f4; color: #495057;"><?php echo htmlspecialchars($email); ?></td>
                </tr>
                <?php if($cont): ?>
                <tr>
                    <td style="padding: 12px; font-weight: 600; color: #2c3e50;">📝 요청사항</td>
                    <td style="padding: 12px; color: #495057;"><?php echo nl2br(htmlspecialchars($cont)); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <!-- 입금 안내 카드 -->
    <div style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem;">
        <h3 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px;">
            💳 입금 안내
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;">🏦 은행</div>
                <div style="font-size: 1.1rem;">국민은행</div>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;">💳 계좌번호</div>
                <div style="font-size: 1.1rem; font-weight: 700;">999-1688-2384</div>
            </div>
            <div>
                <div style="font-weight: 600; margin-bottom: 5px;">👤 예금주</div>
                <div style="font-size: 1.1rem;">두손기획인쇄</div>
            </div>
        </div>
    </div>

    <!-- 액션 버튼들 -->
    <div style="text-align: center; margin: 2rem 0;">
        <a href="/mlangprintauto/cadarok/index.php" style="display: inline-block; background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: 700; margin: 0 10px; box-shadow: 0 6px 20px rgba(52, 152, 219, 0.3);">
            🛍️ 계속 쇼핑하기
        </a>
        <a href="/shop/cart.php" style="display: inline-block; background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 15px 30px; border-radius: 25px; text-decoration: none; font-weight: 700; margin: 0 10px; box-shadow: 0 6px 20px rgba(39, 174, 96, 0.3);">
            🛒 장바구니 보기
        </a>
    </div>

    <!-- 카드결제 폼 -->
    <div style="background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 2rem; text-align: center;">
        <h3 style="color: #2c3e50; margin-bottom: 1rem;">💳 카드결제</h3>
        <form method="post" action="../stdpay/INIStdPaySample/INIStdPayRequest.php">
            <input type="hidden" name="no" value="<?php echo htmlspecialchars($no); ?>">
            <button type="submit" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); color: white; border: none; padding: 15px 30px; border-radius: 25px; font-weight: 700; cursor: pointer; box-shadow: 0 6px 20px rgba(231, 76, 60, 0.3);">
                💳 카드결제 하기
            </button>
            <p style="color: #6c757d; margin-top: 10px; font-size: 0.9rem;">카드결제 문의: 1688-2384</p>
        </form>
    </div>
</div>

<!-- 하단 정보 -->
<div style="background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%); color: white; padding: 2rem; text-align: center;">
    <h3 style="margin-bottom: 1rem;">📞 문의 안내</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; text-align: left;">
        <div>
            <div style="font-weight: 600; margin-bottom: 5px;">📞 고객센터</div>
            <div>1688-2384, 02-2632-1830</div>
        </div>
        <div>
            <div style="font-weight: 600; margin-bottom: 5px;">🏢 주소</div>
            <div>서울특별시 영등포구 영등포로36길9 송호빌딩 1층</div>
        </div>
        <div>
            <div style="font-weight: 600; margin-bottom: 5px;">🌐 홈페이지</div>
            <div>www.dsp1830.shop</div>
        </div>
    </div>
</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . "/mlangprintauto/MlangPrintAutoDown.php"; ?>