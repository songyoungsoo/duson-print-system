<?php
/**
 * 사업자등록증 관리
 * 경로: /mypage/business_certificate.php
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

// 파일 업로드 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_certificate'])) {
    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/business_certificates/';

    if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['certificate_file'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // 허용된 확장자
        $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];

        if (!in_array($file_ext, $allowed_ext)) {
            $error = "허용되지 않는 파일 형식입니다. (jpg, jpeg, png, pdf만 가능)";
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB
            $error = "파일 크기는 5MB를 초과할 수 없습니다.";
        } else {
            // 안전한 파일명 생성
            $safe_filename = $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = $upload_dir . $safe_filename;
            $db_path = '/uploads/business_certificates/' . $safe_filename;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // 기존 파일 삭제
                $query = "SELECT business_cert_path FROM users WHERE id = ?";
                $stmt = mysqli_prepare($db, $query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $old_data = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);

                if ($old_data['business_cert_path']) {
                    $old_file = $_SERVER['DOCUMENT_ROOT'] . $old_data['business_cert_path'];
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }

                // 데이터베이스 업데이트
                $update_query = "UPDATE users SET business_cert_path = ? WHERE id = ?";
                $stmt = mysqli_prepare($db, $update_query);
                mysqli_stmt_bind_param($stmt, "si", $db_path, $user_id);

                if (mysqli_stmt_execute($stmt)) {
                    $message = "사업자등록증이 성공적으로 업로드되었습니다.";
                } else {
                    $error = "파일 업로드 중 오류가 발생했습니다.";
                    unlink($upload_path); // 업로드된 파일 삭제
                }
                mysqli_stmt_close($stmt);
            } else {
                $error = "파일 업로드에 실패했습니다.";
            }
        }
    } else {
        $error = "파일이 선택되지 않았습니다.";
    }
}

// 파일 삭제 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_certificate'])) {
    $query = "SELECT business_cert_path FROM users WHERE id = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($data['business_cert_path']) {
        $file_path = $_SERVER['DOCUMENT_ROOT'] . $data['business_cert_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        $update_query = "UPDATE users SET business_cert_path = NULL WHERE id = ?";
        $stmt = mysqli_prepare($db, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "사업자등록증이 삭제되었습니다.";
        } else {
            $error = "삭제 중 오류가 발생했습니다.";
        }
        mysqli_stmt_close($stmt);
    }
}

// 현재 사업자등록증 정보 조회
$query = "SELECT business_cert_path, business_number, business_name FROM users WHERE id = ?";
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
    <title>사업자등록증 관리 - 두손기획인쇄</title>
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

        .info-box {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #1466BA;
        }

        .info-box h3 {
            margin: 0 0 12px 0;
            font-size: 16px;
            color: #1466BA;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #333;
        }

        .info-box li {
            margin-bottom: 6px;
        }

        .business-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .business-info-item {
            display: flex;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .business-info-label {
            width: 150px;
            font-weight: 600;
            color: #666;
        }

        .business-info-value {
            flex: 1;
            color: #333;
        }

        .upload-section {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 25px;
            transition: all 0.3s;
        }

        .upload-section:hover {
            border-color: #1466BA;
            background: #f8f9fa;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .upload-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .file-input-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 15px;
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: inline-block;
            padding: 10px 24px;
            background: #1466BA;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .file-label:hover {
            background: #0d4d8a;
        }

        .file-name {
            display: block;
            margin-top: 10px;
            font-size: 13px;
            color: #666;
        }

        .current-file {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .current-file h3 {
            margin: 0 0 15px 0;
            font-size: 16px;
            color: #333;
        }

        .file-preview {
            text-align: center;
            margin-bottom: 15px;
        }

        .file-preview img {
            max-width: 100%;
            max-height: 400px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .file-preview-pdf {
            background: #f8f9fa;
            padding: 40px;
            border-radius: 8px;
            text-align: center;
        }

        .file-preview-pdf .icon {
            font-size: 64px;
            margin-bottom: 15px;
        }

        .file-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #1466BA;
            color: white;
        }

        .btn-primary:hover {
            background: #0d4d8a;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        @media (max-width: 768px) {
            .mypage-container {
                grid-template-columns: 1fr;
            }

            .file-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
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
            <h1 class="page-title">사업자등록증 관리</h1>

            <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- 안내 메시지 -->
            <div class="info-box">
                <h3>📄 사업자등록증 등록 안내</h3>
                <ul>
                    <li>세금계산서 발행 시 사업자등록증이 필요합니다.</li>
                    <li>파일 형식: JPG, PNG, PDF</li>
                    <li>최대 파일 크기: 5MB</li>
                    <li>사업자등록증은 안전하게 보관됩니다.</li>
                </ul>
            </div>

            <!-- 사업자 정보 -->
            <?php if ($user_info['business_number'] || $user_info['business_name']): ?>
            <div class="business-info">
                <h3 style="margin: 0 0 15px 0; font-size: 16px;">사업자 정보</h3>
                <?php if ($user_info['business_number']): ?>
                <div class="business-info-item">
                    <div class="business-info-label">사업자등록번호</div>
                    <div class="business-info-value"><?php echo htmlspecialchars($user_info['business_number']); ?></div>
                </div>
                <?php endif; ?>
                <?php if ($user_info['business_name']): ?>
                <div class="business-info-item">
                    <div class="business-info-label">상호명</div>
                    <div class="business-info-value"><?php echo htmlspecialchars($user_info['business_name']); ?></div>
                </div>
                <?php endif; ?>
                <div style="margin-top: 15px;">
                    <a href="/mypage/profile.php" class="btn btn-secondary btn-sm">회원정보에서 수정</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- 현재 등록된 파일 -->
            <?php if ($user_info['business_cert_path']): ?>
            <div class="current-file">
                <h3>📎 등록된 사업자등록증</h3>

                <?php
                $file_ext = strtolower(pathinfo($user_info['business_cert_path'], PATHINFO_EXTENSION));
                ?>

                <div class="file-preview">
                    <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png'])): ?>
                        <img src="<?php echo htmlspecialchars($user_info['business_cert_path']); ?>" alt="사업자등록증">
                    <?php else: ?>
                        <div class="file-preview-pdf">
                            <div class="icon">📄</div>
                            <div>PDF 파일</div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="file-actions">
                    <a href="<?php echo htmlspecialchars($user_info['business_cert_path']); ?>" download class="btn btn-success">💾 다운로드</a>
                    <a href="<?php echo htmlspecialchars($user_info['business_cert_path']); ?>" target="_blank" class="btn btn-primary">🔍 새 창에서 보기</a>
                    <form method="post" style="display: inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                        <button type="submit" name="delete_certificate" class="btn btn-danger">🗑️ 삭제</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- 파일 업로드 -->
            <form method="post" enctype="multipart/form-data">
                <div class="upload-section">
                    <div class="upload-icon">📤</div>
                    <div class="upload-text">
                        <?php if ($user_info['business_cert_path']): ?>
                            새 파일로 교체하기
                        <?php else: ?>
                            사업자등록증을 업로드하세요
                        <?php endif; ?>
                    </div>

                    <div class="file-input-wrapper">
                        <input type="file" name="certificate_file" id="certificateFile" class="file-input" accept=".jpg,.jpeg,.png,.pdf" required>
                        <label for="certificateFile" class="file-label">📁 파일 선택</label>
                        <span class="file-name" id="fileName"></span>
                    </div>

                    <div>
                        <button type="submit" name="upload_certificate" class="btn btn-success">업로드</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 파일 선택 시 파일명 표시
        document.getElementById('certificateFile').addEventListener('change', function() {
            const fileName = this.files[0] ? this.files[0].name : '';
            document.getElementById('fileName').textContent = fileName ? '선택된 파일: ' + fileName : '';
        });
    </script>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
</body>
</html>
<?php
mysqli_close($db);
?>
