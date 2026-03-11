<!-- LOGIN MODAL v3 - 2026-03-10 23:00 -->
<div id="loginModal" class="login-modal">
    <div id="loginModalBox">
        <span id="loginModalClose" onclick="hideLoginModal()">&times;</span>
        <div id="loginModalLogo">
            <img src="/ImgFolder/duson_02.png" alt="두손기획인쇄">
            <div>두손기획인쇄</div>
        </div>

        <form method="post" action="/member/login_unified.php">
            <input type="hidden" name="mode" value="member_login">
            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">

            <span class="lm-badge lm-badge-blue">개인 / 사업자 회원</span>

            <label class="lm-label">아이디</label>
            <input type="text" name="id" class="lm-input" placeholder="아이디" required>

            <label class="lm-label">비밀번호</label>
            <input type="password" name="pass" class="lm-input" placeholder="비밀번호" required>

            <div class="lm-check-row">
                <label><input type="checkbox" name="remember_me" value="1"> 자동 로그인</label>
            </div>

            <button type="submit" class="lm-btn-login">로그인</button>

            <div class="lm-links">
                <a href="/member/join.php">회원가입</a>
                <span>|</span>
                <a href="/member/password_reset_simple.php">비밀번호 찾기</a>
            </div>
        </form>

        <span class="lm-badge lm-badge-green">SNS 회원 가입자 전용</span>

        <div class="lm-naver-row">
            <div class="lm-naver-desc">
                🔧 네이버 로그인은 현재 준비 중입니다.
            </div>
            <a href="#" class="lm-btn-naver" style="opacity:0.5; pointer-events:none;">
                <b>N</b> 네이버 로그인
            </a>
        </div>

        <div class="lm-warning">
            주의 : 기존 개인 / 사업자 회원은 중복가입 하지 마시고,<br>상단 개인 / 사업자 회원 로그인을 이용 바랍니다.
        </div>
    </div>
</div>

<style>
#loginModal {
    display: none;
    position: fixed;
    z-index: 10000;
    inset: 0;
    background: rgba(0,0,0,0.35);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}
#loginModalBox {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%,-50%);
    background: #fff;
    border-radius: 10px;
    width: 300px;
    padding: 18px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.25);
    font-family: 'Noto Sans KR', 'Pretendard Variable', sans-serif;
    font-size: 13px;
    color: #333;
}
#loginModalClose {
    position: absolute;
    top: 10px; right: 14px;
    font-size: 22px;
    color: #999;
    cursor: pointer;
    line-height: 1;
}
#loginModalClose:hover { color: #333; }

#loginModalLogo {
    text-align: center;
    margin-bottom: 14px;
}
#loginModalLogo img {
    width: 48px; height: 48px;
    border-radius: 50%;
    object-fit: contain;
    margin-bottom: 4px;
}
#loginModalLogo div {
    font-size: 16px;
    font-weight: 800;
    color: #1a365d;
}

#loginModalBox .lm-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 3px;
    margin-bottom: 8px;
    color: #fff;
}
#loginModalBox .lm-badge-blue { background: #2c5282; }
#loginModalBox .lm-badge-green { background: #03C75A; }

#loginModalBox .lm-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #555;
    margin-bottom: 3px;
}
#loginModalBox .lm-input {
    width: 100%;
    padding: 8px 10px;
    border: 1.5px solid #d0d5dd;
    border-radius: 5px;
    font-size: 13px;
    background: #f0f4fa;
    margin-bottom: 8px;
    box-sizing: border-box;
    font-family: inherit;
}
#loginModalBox .lm-input:focus {
    outline: none;
    border-color: #2196f3;
    background: #fff;
    box-shadow: none;
}

#loginModalBox .lm-check-row {
    margin-bottom: 10px;
}
#loginModalBox .lm-check-row label {
    font-size: 12px;
    color: #666;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
#loginModalBox .lm-check-row input[type="checkbox"] {
    width: 14px; height: 14px;
    accent-color: #2c5282;
    padding: 0;
}

#loginModalBox .lm-btn-login {
    width: 100%;
    padding: 9px;
    background: linear-gradient(135deg, #1565d8, #2196f3);
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
}
#loginModalBox .lm-btn-login:hover {
    background: linear-gradient(135deg, #2196f3, #42a5f5);
}

#loginModalBox .lm-links {
    text-align: center;
    margin: 10px 0 14px;
    font-size: 12px;
    color: #999;
}
#loginModalBox .lm-links a {
    color: #555;
    text-decoration: none;
}
#loginModalBox .lm-links a:hover { text-decoration: underline; }
#loginModalBox .lm-links span { margin: 0 6px; }

#loginModalBox .lm-naver-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
#loginModalBox .lm-naver-desc {
    flex: 1;
    font-size: 11px;
    color: #e65100;
    line-height: 1.4;
}
#loginModalBox .lm-btn-naver {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    background: #03C75A;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
}
#loginModalBox .lm-btn-naver b {
    font-size: 15px;
}
#loginModalBox .lm-warning {
    font-size: 11px;
    color: #c53030;
    line-height: 1.5;
    padding: 8px;
    background: #fff5f5;
    border: 1px solid #feb2b2;
    border-radius: 4px;
}
</style>

<!-- 로그인 모달 JavaScript는 /js/common-auth.js에서 처리 -->
