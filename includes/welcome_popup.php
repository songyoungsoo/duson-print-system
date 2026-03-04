<!-- 홈페이지 리뉴얼 공지 팝업 (쿠키로 1회만 노출) -->
<?php if (!isset($_COOKIE['duson_welcome_seen'])): ?>
<style>
.welcome-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
    animation: welcomeFadeIn 0.3s ease;
}
@keyframes welcomeFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
.welcome-popup {
    background: #fff;
    border-radius: 16px;
    max-width: 460px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    overflow: hidden;
    animation: welcomeSlideUp 0.3s ease;
}
@keyframes welcomeSlideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.welcome-header {
    background: linear-gradient(135deg, #1a3a5c, #2d5a8e);
    color: #fff;
    padding: 28px 28px 20px;
    text-align: center;
}
.welcome-header h2 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 6px;
    line-height: 1.4;
    color: #fff;
}
.welcome-header p {
    font-size: 0.85rem;
    opacity: 0.85;
    margin: 0;
    color: #fff;
}
.welcome-body {
    padding: 24px 28px;
}
.welcome-section {
    margin-bottom: 18px;
}
.welcome-section:last-child {
    margin-bottom: 0;
}
.welcome-section-title {
    font-size: 0.9rem;
    font-weight: 700;
    color: #1a3a5c;
    margin: 0 0 8px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.welcome-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.welcome-section li {
    font-size: 0.85rem;
    color: #444;
    line-height: 1.6;
    padding-left: 18px;
    position: relative;
}
.welcome-section li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: 700;
}
.welcome-highlight {
    background: #fff8e1;
    border-left: 3px solid #f59e0b;
    border-radius: 0 8px 8px 0;
    padding: 12px 14px;
}
.welcome-highlight .welcome-section-title {
    color: #b45309;
}
.welcome-highlight li::before {
    color: #f59e0b;
    content: "🔒";
    font-size: 0.75rem;
}
.welcome-highlight li {
    padding-left: 22px;
}
.welcome-footer {
    padding: 0 28px 24px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.welcome-btn {
    display: block;
    width: 100%;
    padding: 13px;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    transition: background 0.2s;
}
.welcome-btn-primary {
    background: #1a3a5c;
    color: #fff;
}
.welcome-btn-primary:hover {
    background: #0d2640;
}
.welcome-btn-secondary {
    background: none;
    color: #888;
    font-size: 0.8rem;
    font-weight: 400;
    padding: 8px;
}
.welcome-btn-secondary:hover {
    color: #555;
}
@media (max-width: 480px) {
    .welcome-popup { border-radius: 12px; }
    .welcome-header { padding: 22px 20px 16px; }
    .welcome-header h2 { font-size: 1.15rem; }
    .welcome-body { padding: 18px 20px; }
    .welcome-footer { padding: 0 20px 20px; }
}
</style>

<div class="welcome-overlay" id="welcomePopup">
    <div class="welcome-popup">
        <div class="welcome-header">
            <h2>두손기획인쇄 홈페이지가<br>새 단장 했습니다!</h2>
            <p>더 편리한 서비스를 위해 홈페이지를 새롭게 개편하였습니다</p>
        </div>
        <div class="welcome-body">
            <div class="welcome-section">
                <div class="welcome-section-title">🔑 기존 회원님께</div>
                <ul>
                    <li>기존 아이디와 비밀번호 <strong>그대로 사용</strong> 가능합니다</li>
                    <li>주문 내역도 모두 보존되어 있습니다</li>
                </ul>
            </div>
            <div class="welcome-section welcome-highlight">
                <div class="welcome-section-title">교정 확인 방법 안내</div>
                <ul>
                    <li>교정(시안) 확인 시 <strong>본인 확인 절차</strong>가 추가되었습니다</li>
                    <li>주문 시 등록하신 <strong>전화번호 뒷자리 4자리</strong>를 입력하면 확인 가능합니다</li>
                </ul>
            </div>
        </div>
        <div class="welcome-footer">
            <button class="welcome-btn welcome-btn-primary" onclick="closeWelcomePopup(30)">확인</button>
            <button class="welcome-btn welcome-btn-secondary" onclick="closeWelcomePopup(1)">오늘 하루 보지 않기</button>
        </div>
    </div>
</div>

<script>
function closeWelcomePopup(days) {
    var d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = "duson_welcome_seen=1;expires=" + d.toUTCString() + ";path=/";
    var el = document.getElementById('welcomePopup');
    if (el) {
        el.style.opacity = '0';
        el.style.transition = 'opacity 0.2s';
        setTimeout(function() { el.remove(); }, 200);
    }
}
</script>
<?php endif; ?>
