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
                <button class="login-tab active" onclick="showLoginTab(event)">로그인</button>
                <button class="login-tab" onclick="showRegisterTab(event)">회원가입</button>
            </div>
            
            <form id="loginForm" class="login-form active" method="post" action="">
                <input type="hidden" name="login_action" value="1">
                <div class="form-group">
                    <label for="username">아이디</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group remember-me-group">
                    <label class="remember-me-label">
                        <input type="checkbox" name="remember_me" value="1" id="remember_me">
                        <span class="checkmark"></span>
                        <span class="remember-text">자동 로그인 (30일간 유지)</span>
                    </label>
                </div>
                <button type="submit" class="form-submit">로그인</button>
            </form>
            
            <form id="registerForm" class="login-form" method="post" action="">
                <input type="hidden" name="register_action" value="1">
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
                <button type="submit" class="form-submit">회원가입</button>
            </form>
        </div>
    </div>
</div>

<style>
.login-modal {
    display: none;
    position: fixed;
    z-index: 10000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.3);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.login-modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.login-modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px 8px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.login-modal-header h2 {
    margin: 0;
    font-size: 20px;
}

.close-modal {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
}

.close-modal:hover {
    transform: scale(1.2);
}

.login-modal-body {
    padding: 30px;
}

.login-tabs {
    display: flex;
    margin-bottom: 20px;
    border-bottom: 2px solid #eee;
}

.login-tab {
    flex: 1;
    padding: 10px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #2c3e50 !important; /* 헤더/푸터와 동일한 어두운 색상 */
    font-weight: 600 !important; /* 더 굵게 해서 가독성 향상 */
    transition: all 0.3s;
}

.login-tab.active {
    color: #1a365d !important; /* 더 어두운 색상으로 강조 */
    border-bottom: 2px solid #2c3e50;
    margin-bottom: -2px;
    font-weight: 700 !important; /* 활성 탭을 더 굵게 */
    background: rgba(44, 62, 80, 0.05); /* 살짝 배경 추가 */
}

.login-form {
    display: none;
}

.login-form.active {
    display: block;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50 !important; /* 헤더/푸터와 동일한 어두운 색상 */
    font-weight: 700 !important; /* 더 굵게 해서 가독성 향상 */
    font-size: 14px !important; /* 폰트 크기 명시 */
}

.form-group input {
    width: 100%;
    padding: 10px;
    border: 2px solid #bdc3c7 !important; /* 더 진한 테두리 */
    border-radius: 4px;
    font-size: 14px !important;
    color: #2c3e50 !important; /* 입력 텍스트 어두운 색상 */
    font-weight: 500 !important;
    transition: border-color 0.3s;
    background-color: #ffffff !important; /* 명확한 흰색 배경 */
}

.form-group input:focus {
    border-color: #2c3e50 !important; /* 헤더/푸터와 동일한 색상 */
    outline: none;
    box-shadow: 0 0 5px rgba(44, 62, 80, 0.3) !important; /* 포커스 효과 */
}

.form-submit {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%) !important; /* 헤더/푸터와 동일한 배경 */
    color: #ffffff !important; /* 선명한 흰색 */
    border: none;
    border-radius: 4px;
    font-size: 16px !important;
    font-weight: 700 !important; /* 더 굵게 */
    cursor: pointer;
    transition: all 0.3s;
}

.form-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 62, 80, 0.4) !important; /* 색상 통일 */
    background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%) !important; /* 호버 효과 */
}

/* 플레이스홀더 색상 개선 */
.form-group input::placeholder {
    color: #7f8c8d !important; /* 적당한 회색 */
    font-weight: 400 !important;
}

.login-message {
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 4px;
    text-align: center;
    font-weight: 600 !important; /* 메시지 텍스트 강조 */
}

.login-message.success {
    background-color: #d4edda;
    color: #0f5132 !important; /* 더 어둡고 선명한 초록색 */
    border: 2px solid #c3e6cb; /* 더 진한 테두리 */
    font-weight: 700 !important;
}

.login-message.error {
    background-color: #f8d7da;
    color: #58151c !important; /* 더 어둡고 선명한 빨간색 */
    border: 2px solid #f5c6cb; /* 더 진한 테두리 */
    font-weight: 700 !important;
}

/* 자동 로그인 체크박스 스타일 */
.remember-me-group {
    margin-bottom: 20px;
}

.remember-me-label {
    display: flex !important;
    align-items: center;
    cursor: pointer;
    font-size: 14px !important;
    color: #2c3e50 !important;
    font-weight: 500 !important;
}

.remember-me-label input[type="checkbox"] {
    width: 18px !important;
    height: 18px !important;
    margin-right: 10px !important;
    cursor: pointer;
    accent-color: #2c3e50;
}

.remember-text {
    color: #2c3e50 !important;
    font-weight: 500 !important;
}
</style>

<!-- 로그인 모달 JavaScript는 /js/common-auth.js에서 처리 -->