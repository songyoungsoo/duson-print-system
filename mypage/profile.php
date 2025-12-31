<?php
/**
 * 회원정보수정
 * 경로: /mypage/profile.php
 */

// 세션 및 인증 처리 (8시간 유지, 자동 로그인 30일)
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/auth.php';

// 로그인 확인
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('세션이 만료되었습니다. 다시 로그인해주세요.'); location.href='/member/login.php';</script>";
    exit;
}

// 데이터베이스 연결
require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// 폼 제출 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $current_password = $_POST['current_password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $business_number = trim($_POST['business_number'] ?? '');
    $business_owner = trim($_POST['business_owner'] ?? '');
    $business_address = trim($_POST['business_address'] ?? '');
    $business_type = trim($_POST['business_type'] ?? '');
    $business_item = trim($_POST['business_item'] ?? '');
    $tax_invoice_email = trim($_POST['tax_invoice_email'] ?? '');
    $zipcode = trim($_POST['zipcode'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $detail_address = trim($_POST['detail_address'] ?? '');

    // 현재 비밀번호 확인
    $query = "SELECT password FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    // 비밀번호 검증 (bcrypt 해시 또는 평문 모두 지원)
    $stored_password = $user['password'];
    $password_valid = false;

    // bcrypt 해시인 경우 ($2y$로 시작하고 60자)
    if (strlen($stored_password) === 60 && strpos($stored_password, '$2y$') === 0) {
        $password_valid = password_verify($current_password, $stored_password);
    } else {
        // 평문 비밀번호인 경우 직접 비교
        $password_valid = ($current_password === $stored_password);
    }

    if (!$password_valid) {
        $error = "현재 비밀번호가 일치하지 않습니다.";
    } else {
        // 회원정보 업데이트
        $update_query = "UPDATE users SET
                         name = ?,
                         email = ?,
                         phone = ?,
                         business_number = ?,
                         business_owner = ?,
                         business_address = ?,
                         business_type = ?,
                         business_item = ?,
                         tax_invoice_email = ?,
                         postcode = ?,
                         address = ?,
                         detail_address = ?
                         WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "ssssssssssssi",
            $name, $email, $phone,
            $business_number, $business_owner, $business_address,
            $business_type, $business_item, $tax_invoice_email,
            $zipcode, $address, $detail_address,
            $user_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $message = "회원정보가 성공적으로 수정되었습니다.";
            $_SESSION['user_name'] = $name; // 세션 업데이트
        } else {
            $error = "회원정보 수정 중 오류가 발생했습니다.";
        }
        mysqli_stmt_close($stmt);
    }
}

// 현재 회원정보 조회
$query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user_info = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

include $_SERVER['DOCUMENT_ROOT'] . '/includes/header-ui.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원정보수정 - 두손기획인쇄</title>
    <link rel="stylesheet" href="/css/common-styles.css">
    <style>
        body {
            background: #f5f5f5;
            font-size: 13px;
        }

        .mypage-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 20px;
        }

        .mypage-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 900px;
        }

        .page-title {
            margin: 0 0 20px 0;
            font-size: 24px;
            color: #ffffff;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 16px;
            color: #333;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #1466BA;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-label .required {
            color: #dc3545;
            margin-left: 4px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #1466BA;
        }

        .form-control:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .address-group {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4d8a;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 4px;
        }

        .password-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .password-section p {
            margin: 0;
            font-size: 13px;
            color: #856404;
        }

        /* ===== 사업자 정보 가로 배치 레이아웃 ===== */
        .business-info-horizontal {
            margin-bottom: 1rem;
        }

        .business-info-horizontal .info-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 0.8rem;
        }

        .business-info-horizontal .info-row-single {
            margin-bottom: 0.8rem;
        }

        .business-info-horizontal .info-field {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 5px;
            align-items: center;
        }

        /* 두 번째 필드 (대표자명, 종목) label 너비 조정 */
        .business-info-horizontal .info-row .info-field:nth-child(2) {
            grid-template-columns: 70px 1fr;
        }

        .business-info-horizontal .info-field-full {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 5px;
            align-items: start;
        }

        .business-info-horizontal .info-field label,
        .business-info-horizontal .info-field-full label {
            white-space: nowrap;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
            text-align: left;
        }

        .business-info-horizontal .info-field input,
        .business-info-horizontal .info-field-full input {
            width: 100%;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }

            .address-group {
                grid-template-columns: 100px 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="mypage-container">
        <!-- 사이드바 -->
        <?php include 'sidebar.php'; ?>

        <!-- 메인 컨텐츠 -->
        <div class="mypage-content">
            <h1 class="page-title">회원정보수정</h1>

            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <!-- 비밀번호 확인 -->
                <div class="password-section">
                    <p><strong>⚠️ 보안을 위해 현재 비밀번호를 입력해주세요.</strong></p>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        현재 비밀번호<span class="required">*</span>
                    </label>
                    <input type="password" name="current_password" class="form-control" required placeholder="현재 비밀번호를 입력하세요">
                </div>

                <!-- 기본 정보 -->
                <div class="form-section">
                    <h2 class="section-title">기본 정보</h2>

                    <div class="form-group">
                        <label class="form-label">
                            아이디
                        </label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user_info['username']); ?>" disabled>
                        <div class="help-text">아이디는 변경할 수 없습니다.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            이름<span class="required">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($user_info['name']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            이메일<span class="required">*</span>
                        </label>
                        <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($user_info['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            전화번호<span class="required">*</span>
                        </label>
                        <input type="tel" name="phone" class="form-control" required value="<?php echo htmlspecialchars($user_info['phone']); ?>" placeholder="010-1234-5678">
                    </div>
                </div>

                <!-- 주소 정보 -->
                <div class="form-section">
                    <h2 class="section-title">주소 (배송지) 정보</h2>

                    <div class="form-group">
                        <div class="address-group">
                            <input type="text" name="zipcode" class="form-control" value="<?php echo htmlspecialchars($user_info['postcode'] ?? ''); ?>" placeholder="우편번호" readonly>
                            <button type="button" class="btn btn-secondary" onclick="execDaumPostcode()">우편번호 찾기</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($user_info['address'] ?? ''); ?>" placeholder="주소" readonly>
                    </div>

                    <div class="form-group">
                        <input type="text" name="detail_address" class="form-control" value="<?php echo htmlspecialchars($user_info['detail_address'] ?? ''); ?>" placeholder="상세주소">
                    </div>
                </div>

                <!-- 사업자 정보 -->
                <div class="form-section">
                    <h2 class="section-title">사업자 정보 (선택)</h2>

                    <div class="business-info-horizontal">
                        <!-- 1줄: 사업자등록번호 + 대표자명 -->
                        <div class="info-row">
                            <div class="info-field">
                                <label>사업자등록번호</label>
                                <input type="text" name="business_number" class="form-control" value="<?php echo htmlspecialchars($user_info['business_number'] ?? ''); ?>" placeholder="000-00-00000" maxlength="12">
                            </div>
                            <div class="info-field">
                                <label>대표자명</label>
                                <input type="text" name="business_owner" class="form-control" value="<?php echo htmlspecialchars($user_info['business_owner'] ?? ''); ?>" placeholder="대표자 성명">
                            </div>
                        </div>

                        <!-- 2줄: 사업장 주소 -->
                        <div class="info-row-single">
                            <div style="display: grid; grid-template-columns: 110px 1fr; gap: 5px; align-items: start;">
                                <label style="white-space: nowrap; font-weight: 600; color: #2c3e50; margin: 0; padding-top: 8px;">사업장 주소</label>
                                <div>
                                    <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <input type="text" id="business_postcode" class="form-control" placeholder="우편번호" readonly style="width: 140px;">
                                        <button type="button" onclick="execBusinessDaumPostcode()" class="btn btn-secondary" style="white-space: nowrap;">
                                            우편번호 찾기
                                        </button>
                                    </div>
                                    <input type="text" id="business_address_display" class="form-control" placeholder="주소" readonly style="width: 100%; margin-bottom: 0.5rem;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                                        <input type="text" id="business_detailAddress" class="form-control" placeholder="상세주소" value="">
                                        <input type="text" id="business_extraAddress" class="form-control" placeholder="참고항목" value="">
                                    </div>
                                    <input type="hidden" name="business_address" id="business_address_hidden" value="<?php echo htmlspecialchars($user_info['business_address'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- 3줄: 업태 + 종목 -->
                        <div class="info-row">
                            <div class="info-field">
                                <label>업태</label>
                                <input type="text" name="business_type" class="form-control" value="<?php echo htmlspecialchars($user_info['business_type'] ?? ''); ?>" placeholder="제조업, 서비스업">
                            </div>
                            <div class="info-field">
                                <label>종목</label>
                                <input type="text" name="business_item" class="form-control" value="<?php echo htmlspecialchars($user_info['business_item'] ?? ''); ?>" placeholder="인쇄업, 광고업">
                            </div>
                        </div>

                        <!-- 4줄: 세금용 메일 -->
                        <div class="info-row-single">
                            <div class="info-field-full">
                                <label>세금용 메일</label>
                                <input type="email" name="tax_invoice_email" class="form-control" value="<?php echo htmlspecialchars($user_info['tax_invoice_email'] ?? ''); ?>" placeholder="세금계산서를 받을 이메일 주소를 입력하세요">
                            </div>
                        </div>
                    </div>

                    <div style="background: #e8f4fd; padding: 0.6rem; border-radius: 4px; margin-top: 0.8rem;">
                        <p style="margin: 0; font-size: 12px; color: #2c3e50;"><strong>안내:</strong></p>
                        <p style="margin: 0.2rem 0 0 0; font-size: 12px; color: #666;">• 세금계산서 발행을 원하시면 정확한 사업자 정보를 입력해주세요</p>
                        <p style="margin: 0.2rem 0 0 0; font-size: 12px; color: #666;">• 사업자등록번호는 하이픈(-) 포함하여 입력해주세요</p>
                    </div>
                </div>

                <!-- 버튼 -->
                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">수정하기</button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='/mypage/index.php'">취소</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 다음 우편번호 API -->
    <script src="//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
    <script>
        // 일반 배송 주소 검색
        function execDaumPostcode() {
            new daum.Postcode({
                oncomplete: function(data) {
                    var addr = data.userSelectedType === 'R' ? data.roadAddress : data.jibunAddress;
                    document.querySelector('input[name="zipcode"]').value = data.zonecode;
                    document.querySelector('input[name="address"]').value = addr;
                    document.querySelector('input[name="detail_address"]').focus();
                }
            }).open();
        }

        // 사업장 주소 검색
        function execBusinessDaumPostcode() {
            new daum.Postcode({
                oncomplete: function(data) {
                    var addr = '';
                    var extraAddr = '';

                    if (data.userSelectedType === 'R') {
                        addr = data.roadAddress;
                    } else {
                        addr = data.jibunAddress;
                    }

                    if(data.userSelectedType === 'R'){
                        if(data.bname !== '' && /[동|로|가]$/g.test(data.bname)){
                            extraAddr += data.bname;
                        }
                        if(data.buildingName !== '' && data.apartment === 'Y'){
                            extraAddr += (extraAddr !== '' ? ', ' + data.buildingName : data.buildingName);
                        }
                        if(extraAddr !== ''){
                            extraAddr = ' (' + extraAddr + ')';
                        }
                        document.getElementById("business_extraAddress").value = extraAddr;
                    } else {
                        document.getElementById("business_extraAddress").value = '';
                    }

                    document.getElementById('business_postcode').value = data.zonecode;
                    document.getElementById('business_address_display').value = addr;
                    document.getElementById("business_detailAddress").focus();

                    // hidden 필드 업데이트
                    updateBusinessAddress();
                }
            }).open();
        }

        // 사업장 주소 필드 변경 시 hidden 필드 업데이트
        function updateBusinessAddress() {
            const postcode = document.getElementById('business_postcode').value;
            const address = document.getElementById('business_address_display').value;
            const detailAddress = document.getElementById('business_detailAddress').value;
            const extraAddress = document.getElementById('business_extraAddress').value;

            let fullAddress = '';
            if (postcode) fullAddress += '[' + postcode + '] ';
            if (address) fullAddress += address;
            if (detailAddress) fullAddress += ' ' + detailAddress;
            if (extraAddress) fullAddress += ' ' + extraAddress;

            document.getElementById('business_address_hidden').value = fullAddress.trim();
        }

        // 페이지 로드 시 기존 사업장 주소 분리
        window.addEventListener('DOMContentLoaded', function() {
            const businessAddress = document.getElementById('business_address_hidden').value;
            if (businessAddress) {
                // [우편번호] 주소 상세주소 (참고항목) 형식 파싱
                const postcodeMatch = businessAddress.match(/\[(\d{5})\]/);
                if (postcodeMatch) {
                    document.getElementById('business_postcode').value = postcodeMatch[1];

                    // 우편번호 제거한 나머지 주소
                    let remaining = businessAddress.replace(/\[\d{5}\]\s*/, '');

                    // 참고항목 추출 (괄호로 감싸진 부분)
                    const extraMatch = remaining.match(/\(([^)]+)\)\s*$/);
                    if (extraMatch) {
                        document.getElementById('business_extraAddress').value = '(' + extraMatch[1] + ')';
                        remaining = remaining.replace(/\s*\([^)]+\)\s*$/, '');
                    }

                    // 남은 주소를 display에 표시
                    document.getElementById('business_address_display').value = remaining.trim();
                }
            }
        });

        // 상세주소/참고항목 입력 시 hidden 필드 업데이트
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('business_detailAddress').addEventListener('input', updateBusinessAddress);
            document.getElementById('business_extraAddress').addEventListener('input', updateBusinessAddress);
        });
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
