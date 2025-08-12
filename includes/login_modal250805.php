<?php
/**
 * 공통 로그인 모달 파일
 * 경로: includes/login_modal.php
 */
?>
<!-- 로그인 모달 -->
<div id="loginModal" class="login-modal">
    <div class="login-modal-content">
        <div class="login-modal-header">
            <h2>🔐 로그인 / 회원가입</h2>
            <span class="close-modal" onclick="hideLoginModal()">&times;</span>
        </div>
        <div class="login-modal-body">
            <?php if (!empty($login_message)): ?>
            <div class="login-message <?php echo (strpos($login_message, '성공') !== false || strpos($login_message, '완료') !== false) ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($login_message); ?>
            </div>
            <?php endif; ?>
            
            <div class="login-tabs">
                <button class="login-tab active" onclick="showLoginTab()">로그인</button>
                <button class="login-tab" onclick="showRegisterTab()">회원가입</button>
            </div>
            
            <!-- 로그인 폼 -->
            <form id="loginForm" class="login-form active" method="post">
                <div class="form-group">
                    <label for="username">아이디</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" name="login_action" class="form-submit">로그인</button>
            </form>
            
            <!-- 회원가입 폼 -->
            <form id="registerForm" class="login-form" method="post">
                <div class="form-group">
                    <label for="reg_username">아이디 *</label>
                    <input type="text" id="reg_username" name="reg_username" required>
                </div>
                <div class="form-group">
                    <label for="reg_password">비밀번호 * (6자 이상)</label>
                    <input type="password" id="reg_password" name="reg_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="reg_confirm_password">비밀번호 확인 *</label>
                    <input type="password" id="reg_confirm_password" name="reg_confirm_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="reg_name">이름 *</label>
                    <input type="text" id="reg_name" name="reg_name" required>
                </div>
                <div class="form-group">
                    <label for="reg_email">이메일</label>
                    <input type="email" id="reg_email" name="reg_email">
                </div>
                <div class="form-group">
                    <label for="reg_phone">전화번호</label>
                    <input type="tel" id="reg_phone" name="reg_phone">
                </div>
                <button type="submit" name="register_action" class="form-submit">회원가입</button>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 0.9rem; color: #6c757d;">
                <strong>테스트 계정:</strong><br>
                아이디: admin<br>
                비밀번호: admin123
            </div>
        </div>
    </div>
</div>

<script>
// 로그인 모달 관련 함수들
function showLoginModal() {
    document.getElementById('loginModal').style.display = 'block';
    document.body.style.overflow = 'hidden'; // 배경 스크롤 방지
}

function hideLoginModal() {
    document.getElementById('loginModal').style.display = 'none';
    document.body.style.overflow = 'auto'; // 배경 스크롤 복원
}

function showLoginTab() {
    document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('loginForm').classList.add('active');
}

function showRegisterTab() {
    document.querySelectorAll('.login-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));
    
    event.target.classList.add('active');
    document.getElementById('registerForm').classList.add('active');
}

// 모달 외부 클릭 시 닫기
window.onclick = function(event) {
    const modal = document.getElementById('loginModal');
    if (event.target == modal) {
        hideLoginModal();
    }
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideLoginModal();
    }
});
</script>