<?php
/**
 * 우측 플로팅 메뉴 - 원형 → 카드 변형 방식
 * 2026-02-18 리디자인: hover 시 원형이 제자리에서 카드로 변형
 *
 * 사용법: <?php include '../includes/sidebar.php'; ?>
 */

// 표시 옵션 (각 페이지에서 설정 가능)
$show_contact = isset($show_contact) ? $show_contact : true;
$show_menu = isset($show_menu) ? $show_menu : true;
$show_bank = isset($show_bank) ? $show_bank : true;
?>

<!-- 플로팅 원형 메뉴 -->
<div class="floating-menu" id="floating-menu">

    <!-- 카톡상담 -->
    <div class="fm-item fm-kakao-item">
        <a href="http://pf.kakao.com/_pEGhj/chat" target="_blank" class="fm-circle fm-kakao-circle" title="카톡상담">
            <img src="/TALK.svg" alt="카톡상담" class="fm-kakao-full">
        </a>
    </div>

    <?php if($show_contact): ?>
    <div class="fm-item" data-panel="contact">
        <div class="fm-card">
            <div class="fm-card-header">
                <span class="fm-icon">📞</span>
                <span class="fm-label">고객센터</span>
            </div>
            <div class="fm-card-content">
                <div class="fm-row">
                    <span class="fm-key">대표전화</span>
                    <a href="tel:16882384" class="fm-val fm-phone">1688-2384</a>
                </div>
                <div class="fm-row">
                    <span class="fm-key">직통</span>
                    <a href="tel:0226321830" class="fm-val">02-2632-1830</a>
                </div>
                <div class="fm-row">
                    <span class="fm-key">팩스</span>
                    <span class="fm-val">02-2632-1829</span>
                </div>
                <div class="fm-row">
                    <span class="fm-key">야간</span>
                    <a href="tel:01037121830" class="fm-val">010-3712-1830</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_menu): ?>
    <div class="fm-item" data-panel="file">
        <div class="fm-card">
            <div class="fm-card-header">
                <span class="fm-icon">📂</span>
                <span class="fm-label">파일전송</span>
            </div>
            <div class="fm-card-content fm-links">
                <a href="http://www.webhard.co.kr/" target="_blank" class="fm-link">
                    <span>웹하드 바로가기</span>
                    <span style="font-size:10px;color:#007bff;display:block;">ID: duson1830 / PW: 1830</span>
                </a>
                <a href="mailto:dsp1830@naver.com" class="fm-link">📧 dsp1830@naver.com</a>
            </div>
        </div>
    </div>

    <div class="fm-item" data-panel="guide">
        <div class="fm-card">
            <div class="fm-card-header">
                <span class="fm-icon">📋</span>
                <span class="fm-label">업무안내</span>
            </div>
            <div class="fm-card-content fm-links">
                <a href="/sub/attention.htm" class="fm-link">📝 작업시 유의사항</a>
                <a href="/sub/expense.htm" class="fm-link">💰 편집디자인비용</a>
                <a href="https://map.kakao.com/link/search/서울시 영등포구 영등포로 36길 9 송호빌딩" target="_blank" class="fm-link">🗺️ 오시는길</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_bank): ?>
    <div class="fm-item" data-panel="bank">
        <div class="fm-card">
            <div class="fm-card-header">
                <span class="fm-icon">🏦</span>
                <span class="fm-label">입금안내</span>
            </div>
            <div class="fm-card-content">
                <div class="fm-bank">
                    <span class="fm-bank-name">국민은행</span>
                    <span class="fm-bank-num" onclick="copyAccount(this)" title="클릭하여 복사">999-1688-2384</span>
                </div>
                <div class="fm-bank">
                    <span class="fm-bank-name">신한은행</span>
                    <span class="fm-bank-num" onclick="copyAccount(this)" title="클릭하여 복사">110-342-543507</span>
                </div>
                <div class="fm-bank">
                    <span class="fm-bank-name">농협</span>
                    <span class="fm-bank-num" onclick="copyAccount(this)" title="클릭하여 복사">301-2632-1830-11</span>
                </div>
                <div class="fm-bank-owner">예금주: 두손기획인쇄 차경선</div>
                <div class="fm-bank-card">💳 카드결제 가능</div>
                <div class="fm-bank-notice">⚠ 택배선불 고객은<br>반드시 전화 후 결제</div>
                <div class="fm-bank-hotline"><a href="tel:16882384">📞 1688-2384</a></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="fm-item" data-panel="time">
        <div class="fm-card">
            <div class="fm-card-header">
                <span class="fm-icon">⏰</span>
                <span class="fm-label">운영시간</span>
            </div>
            <div class="fm-card-content">
                <div class="fm-row">
                    <span class="fm-key">평일</span>
                    <span class="fm-val">09:00 ~ 18:00</span>
                </div>
                <div class="fm-row">
                    <span class="fm-key">토요일</span>
                    <span class="fm-val">09:00 ~ 13:00</span>
                </div>
                <div class="fm-row fm-holiday">
                    <span class="fm-key">일/공휴일</span>
                    <span class="fm-val">휴무</span>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* =====================================================
   플로팅 메뉴 - 원형 → 카드 변형
   ===================================================== */
.floating-menu {
    position: fixed;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 9990;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
    font-family: 'Noto Sans KR', -apple-system, sans-serif;
}

.floating-menu .fm-item {
    display: flex;
    justify-content: flex-end;
}

/* === 카드 (원형 ↔ 사각형 변형) === */
.floating-menu .fm-card {
    width: 88px;
    max-height: 88px;
    border-radius: 44px;
    background: #1E4E79;
    overflow: hidden;
    cursor: pointer;
    box-shadow: 0 3px 14px rgba(30,78,121,0.35);
    transition: width 0.3s ease, max-height 0.35s ease, border-radius 0.3s ease, box-shadow 0.3s ease;
}

.floating-menu .fm-card:hover {
    box-shadow: 0 4px 18px rgba(30,78,121,0.5);
}

/* 확장 상태 */
.floating-menu .fm-item.active .fm-card {
    width: 210px;
    max-height: 400px;
    border-radius: 14px;
    box-shadow: 0 8px 32px rgba(30,78,121,0.4);
}

/* === 카드 헤더 (원형일 때 아이콘+라벨) === */
.floating-menu .fm-card-header {
    width: 88px;
    height: 88px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    flex-shrink: 0;
    transition: width 0.3s ease, height 0.3s ease, padding 0.3s ease;
}

.floating-menu .fm-item.active .fm-card-header {
    width: 100%;
    height: 44px;
    flex-direction: row;
    justify-content: flex-start;
    gap: 6px;
    padding: 0 14px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.floating-menu .fm-icon {
    font-size: 21px;
    line-height: 1;
    filter: grayscale(1) brightness(10);
    transition: font-size 0.3s ease;
}

.floating-menu .fm-item.active .fm-icon {
    font-size: 15px;
}

.floating-menu .fm-label {
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    font-weight: 700;
    letter-spacing: -0.3px;
    line-height: 1.1;
    white-space: nowrap;
    transition: font-size 0.3s ease;
}

.floating-menu .fm-item.active .fm-label {
    font-size: 13px;
}

/* === 카드 콘텐츠 (확장 시 표시) === */
.floating-menu .fm-card-content {
    background: #fff;
    padding: 0 12px;
    max-height: 0;
    opacity: 0;
    transition: opacity 0.25s ease 0.1s, padding 0.3s ease, max-height 0.35s ease;
    overflow: hidden;
}

.floating-menu .fm-item.active .fm-card-content {
    padding: 10px 12px;
    max-height: 300px;
    opacity: 1;
}

/* === 내부 행 스타일 === */
.floating-menu .fm-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px dotted #e9ecef;
}

.floating-menu .fm-row:last-child {
    border-bottom: none;
}

.floating-menu .fm-key {
    font-size: 11px;
    color: #666;
    font-weight: 500;
}

.floating-menu .fm-val {
    font-size: 11px;
    color: #222;
    font-weight: 600;
    text-decoration: none;
}

.floating-menu a.fm-val:hover {
    color: #1E4E79;
    text-decoration: underline;
}

.floating-menu .fm-phone {
    color: #d32f2f;
    font-size: 12px;
    font-weight: 700;
}

.floating-menu .fm-holiday .fm-val {
    color: #d32f2f;
}

/* === 은행 정보 === */
.floating-menu .fm-bank {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px dotted #e9ecef;
}

.floating-menu .fm-bank:last-of-type {
    border-bottom: none;
}

.floating-menu .fm-bank-name {
    font-size: 11px;
    font-weight: 600;
    color: #333;
}

.floating-menu .fm-bank-num {
    font-size: 13px;
    font-weight: 700;
    color: #d32f2f;
    font-family: 'Consolas', 'Monaco', monospace;
    letter-spacing: -0.5px;
    cursor: pointer;
    transition: color 0.2s;
}

.floating-menu .fm-bank-num:hover {
    color: #b71c1c;
    text-decoration: underline;
}

.floating-menu .fm-bank-owner {
    text-align: center;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px solid #e9ecef;
    font-size: 10px;
    color: #555;
    font-weight: 500;
}

.floating-menu .fm-bank-card {
    text-align: center;
    margin-top: 5px;
    padding: 4px 0;
    font-size: 11px;
    font-weight: 600;
    color: #1E4E79;
    border-top: 1px dotted #e9ecef;
}

.floating-menu .fm-bank-notice {
    text-align: center;
    margin-top: 4px;
    padding: 5px 6px;
    font-size: 14px;
    font-weight: 700;
    color: #d32f2f;
    background: #fff5f5;
    border-radius: 4px;
    line-height: 1.4;
}

.floating-menu .fm-bank-hotline {
    text-align: center;
    margin-top: 4px;
}

.floating-menu .fm-bank-hotline a {
    font-size: 12px;
    font-weight: 700;
    color: #1E4E79;
    text-decoration: none;
    letter-spacing: -0.3px;
}

.floating-menu .fm-bank-hotline a:hover {
    text-decoration: underline;
}

/* === 링크 목록 === */
.floating-menu .fm-links {
    padding: 0 8px;
}

.floating-menu .fm-item.active .fm-links {
    padding: 8px;
}

.floating-menu .fm-link {
    display: block;
    padding: 6px 8px;
    margin-bottom: 2px;
    font-size: 11px;
    font-weight: 500;
    color: #444;
    text-decoration: none;
    border-radius: 6px;
    transition: background 0.2s ease, color 0.2s ease;
}

.floating-menu .fm-link:hover {
    background: #e3f2fd;
    color: #1565c0;
}

/* === 카카오톡 === */
.floating-menu .fm-kakao-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.floating-menu .fm-circle {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: all 0.25s ease;
    flex-shrink: 0;
}

.floating-menu .fm-kakao-circle {
    background: none;
    border: none;
    border-radius: 13%;
    box-shadow: 0 3px 14px rgba(0,0,0,0.15);
    padding: 0;
    overflow: hidden;
}

.floating-menu .fm-kakao-circle:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 18px rgba(0,0,0,0.25);
}

.floating-menu .fm-kakao-full {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* === 복사 토스트 === */
.fm-copy-toast {
    position: fixed;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: rgba(0,0,0,0.8);
    color: #fff;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    z-index: 99999;
    opacity: 0;
    transition: opacity 0.3s, transform 0.3s;
    pointer-events: none;
    font-family: 'Noto Sans KR', sans-serif;
}

.fm-copy-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* === 반응형 === */
@media (max-width: 768px) {
    /* 모바일 스타일 */
}

@media (max-width: 1024px) {
    .floating-menu {
        display: none;
    }
}
</style>

<div class="fm-copy-toast" id="fm-copy-toast"></div>

<script>
function copyAccount(el) {
    var num = el.textContent.trim();
    if (navigator.clipboard) {
        navigator.clipboard.writeText(num).then(function() { showCopyToast(num); });
    } else {
        var ta = document.createElement('textarea');
        ta.value = num;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showCopyToast(num);
    }
}
function showCopyToast(num) {
    var t = document.getElementById('fm-copy-toast');
    t.textContent = num + ' 복사되었습니다';
    t.classList.add('show');
    clearTimeout(t._tid);
    t._tid = setTimeout(function() { t.classList.remove('show'); }, 2000);
}

(function() {
    'use strict';
    document.addEventListener('DOMContentLoaded', function() {
        var menu = document.getElementById('floating-menu');
        if (!menu) return;

        var items = menu.querySelectorAll('.fm-item[data-panel]');

        items.forEach(function(item) {
            var card = item.querySelector('.fm-card');
            if (!card) return;
            item._hideTimer = null;

            item.addEventListener('mouseenter', function() {
                clearTimeout(item._hideTimer);
                items.forEach(function(other) {
                    if (other !== item && !other.classList.contains('pinned')) {
                        clearTimeout(other._hideTimer);
                        other.classList.remove('active');
                    }
                });
                item.classList.add('active');
            });

            item.addEventListener('mouseleave', function() {
                if (!item.classList.contains('pinned')) {
                    item._hideTimer = setTimeout(function() {
                        item.classList.remove('active');
                    }, 300);
                }
            });

            card.addEventListener('click', function(e) {
                e.stopPropagation();
                clearTimeout(item._hideTimer);
                if (item.classList.contains('pinned')) {
                    item.classList.remove('pinned');
                    item.classList.remove('active');
                } else {
                    items.forEach(function(other) {
                        clearTimeout(other._hideTimer);
                        other.classList.remove('active');
                        other.classList.remove('pinned');
                    });
                    item.classList.add('active');
                    item.classList.add('pinned');
                }
            });
        });

        menu.querySelectorAll('.fm-card-content').forEach(function(content) {
            content.addEventListener('click', function(e) { e.stopPropagation(); });
        });

        document.addEventListener('click', function() {
            items.forEach(function(item) {
                item.classList.remove('active');
                item.classList.remove('pinned');
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                items.forEach(function(item) {
                    item.classList.remove('active');
                    item.classList.remove('pinned');
                });
            }
        });
    });
})();
</script>
