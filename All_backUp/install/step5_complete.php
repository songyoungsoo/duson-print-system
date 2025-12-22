<?php
/**
 * Step 5: 설치 완료
 */

$admin_id = $_SESSION['admin_id'] ?? 'admin';
$company_name = $_SESSION['config']['company']['name'] ?? '인쇄몰';
?>

<div style="text-align: center; padding: 40px 0;">
    <div style="font-size: 80px; color: #2E7D32; margin-bottom: 20px;">✓</div>

    <h2 class="step-title" style="border: none; text-align: center; background: transparent;">설치가 완료되었습니다!</h2>

    <p style="font-size: 16px; color: #666; margin-bottom: 40px;">
        <?php echo htmlspecialchars($company_name); ?> 인쇄몰이 성공적으로 설치되었습니다.
    </p>

    <div style="background: #FFF9E6; padding: 30px; border: 1px solid #C8D6C8; max-width: 500px; margin: 0 auto 40px; text-align: left;">
        <h3 style="margin-bottom: 20px; color: #4A6741; font-weight: 600;">설치 정보</h3>

        <div style="margin-bottom: 15px;">
            <strong>관리자 ID:</strong>
            <span style="color: #1565C0;"><?php echo htmlspecialchars($admin_id); ?></span>
        </div>

        <div style="margin-bottom: 15px;">
            <strong>사이트 URL:</strong>
            <span style="color: #1565C0;"><?php echo 'http://' . $_SERVER['HTTP_HOST']; ?></span>
        </div>

        <div style="margin-bottom: 15px;">
            <strong>관리자 URL:</strong>
            <span style="color: #1565C0;"><?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>/admin/</span>
        </div>
    </div>

    <div class="alert alert-warning" style="max-width: 500px; margin: 0 auto 30px;">
        <strong>보안 권장:</strong> 설치가 완료되면 <code>/install/</code> 폴더를 삭제하거나 접근을 제한하세요.
    </div>

    <div style="display: flex; gap: 20px; justify-content: center;">
        <a href="../" class="btn btn-primary" style="padding: 15px 40px;">
            🏠 사이트 바로가기
        </a>
        <a href="../admin/" class="btn btn-success" style="padding: 15px 40px;">
            ⚙️ 관리자 페이지
        </a>
    </div>
</div>

<style>
    .next-steps {
        margin-top: 50px;
        padding-top: 30px;
        border-top: 2px solid #C8D6C8;
    }
    .next-steps h3 {
        margin-bottom: 20px;
        color: #4A6741;
        font-weight: 600;
    }
    .step-list {
        list-style: none;
        padding: 0;
    }
    .step-list li {
        padding: 12px 0;
        border-bottom: 1px solid #E8F4E8;
        display: flex;
        align-items: center;
        font-size: 13px;
    }
    .step-list li:last-child {
        border-bottom: none;
    }
    .step-list .num {
        width: 28px;
        height: 28px;
        background: #81C784;
        color: white;
        border-radius: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-weight: bold;
        font-size: 13px;
    }
</style>

<div class="next-steps">
    <h3>다음 단계</h3>
    <ul class="step-list">
        <li>
            <span class="num">1</span>
            관리자 페이지에서 제품 가격표를 설정하세요
        </li>
        <li>
            <span class="num">2</span>
            샘플 이미지를 ImgFolder에 업로드하세요
        </li>
        <li>
            <span class="num">3</span>
            이메일 발송을 테스트하세요
        </li>
        <li>
            <span class="num">4</span>
            SSL 인증서를 설치하세요 (권장)
        </li>
        <li>
            <span class="num">5</span>
            /install/ 폴더를 삭제하세요
        </li>
    </ul>
</div>
