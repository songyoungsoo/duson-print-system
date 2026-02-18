<?php
/**
 * 우측 플로팅 메뉴 - 원형 아이콘 + 클릭 패널 방식
 * 2026-02-16 리디자인: 사이드바 → 플로팅 원형 아이콘
 */

// 표시 옵션 (기본값 모두 표시)
$show_contact = isset($show_contact) ? $show_contact : true;
$show_menu = isset($show_menu) ? $show_menu : true;
$show_bank = isset($show_bank) ? $show_bank : true;
?>

<!-- 플로팅 원형 메뉴 -->
<div class="floating-menu" id="floating-menu">

    <?php if($show_contact): ?>
    <div class="fm-item" data-panel="contact">
        <button class="fm-circle" title="고객센터">
            <span class="fm-icon">📞</span>
            <span class="fm-label">고객센터</span>
        </button>
        <div class="fm-panel">
            <div class="fm-panel-title">📞 고객센터</div>
            <div class="fm-panel-body">
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

    <?php if($show_bank): ?>
    <div class="fm-item" data-panel="bank">
        <button class="fm-circle" title="입금안내">
            <span class="fm-icon">🏦</span>
            <span class="fm-label">입금안내</span>
        </button>
        <div class="fm-panel">
            <div class="fm-panel-title">🏦 입금안내</div>
            <div class="fm-panel-body">
                <div class="fm-bank">
                    <span class="fm-bank-name">국민은행</span>
                    <span class="fm-bank-num">999-1688-2384</span>
                </div>
                <div class="fm-bank">
                    <span class="fm-bank-name">신한은행</span>
                    <span class="fm-bank-num">110-342-543507</span>
                </div>
                <div class="fm-bank">
                    <span class="fm-bank-name">농협</span>
                    <span class="fm-bank-num">301-2632-1830-11</span>
                </div>
                <div class="fm-bank-owner">예금주: 두손기획인쇄 차경선</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($show_menu): ?>
    <div class="fm-item" data-panel="quick">
        <button class="fm-circle" title="빠른메뉴">
            <span class="fm-icon">⚡</span>
            <span class="fm-label">빠른메뉴</span>
        </button>
        <div class="fm-panel">
            <div class="fm-panel-title">⚡ 빠른메뉴</div>
            <div class="fm-panel-body fm-links">
                <a href="/account/orders.php" class="fm-link">📋 주문내역</a>
                <a href="/shop/cart.php" class="fm-link">🛒 장바구니</a>
                <a href="mailto:dsp1830@naver.com" class="fm-link">✉️ 이메일문의</a>
                <a href="http://pf.kakao.com/_pEGhj/chat" target="_blank" class="fm-link fm-kakao">💬 카톡상담</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="fm-item" data-panel="time">
        <button class="fm-circle" title="운영시간">
            <span class="fm-icon">⏰</span>
            <span class="fm-label">운영시간</span>
        </button>
        <div class="fm-panel">
            <div class="fm-panel-title">⏰ 운영시간</div>
            <div class="fm-panel-body">
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
   플로팅 원형 메뉴 스타일
   ===================================================== */

.floating-menu {
    position: fixed;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    z-index: 9990;
    display: flex;
    flex-direction: column;
    gap: 8px;
    font-family: 'Noto Sans KR', -apple-system, sans-serif;
}

.floating-menu .fm-item {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: flex-end;
}

/* 원형 버튼 */
.floating-menu .fm-circle {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    background: #1E4E79;
    border: 2px solid rgba(255,255,255,0.3);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 3px;
    transition: all 0.25s ease;
    box-shadow: 0 3px 14px rgba(30,78,121,0.35);
    position: relative;
    z-index: 2;
    flex-shrink: 0;
}

.floating-menu .fm-circle:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 18px rgba(30,78,121,0.5);
    background: #2a6496;
}

.floating-menu .fm-circle:active {
    transform: scale(0.95);
}

.floating-menu .fm-item.active .fm-circle {
    background: #0d3a5e;
    box-shadow: 0 4px 18px rgba(30,78,121,0.55);
    border-color: rgba(255,255,255,0.5);
}

.floating-menu .fm-icon {
    font-size: 21px;
    line-height: 1;
    filter: grayscale(1) brightness(10);
}

.floating-menu .fm-label {
    font-size: 14px;
    color: rgba(255,255,255,0.9);
    font-weight: 700;
    letter-spacing: -0.3px;
    line-height: 1.1;
}

/* 패널 (슬라이드 아웃) */
.floating-menu .fm-panel {
    position: absolute;
    right: 98px;
    top: 50%;
    transform: translateY(-50%) translateX(20px);
    width: 240px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15), 0 2px 8px rgba(0,0,0,0.08);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease;
    z-index: 1;
    overflow: hidden;
}

.floating-menu .fm-panel::after {
    content: '';
    position: absolute;
    right: -6px;
    top: 50%;
    transform: translateY(-50%) rotate(45deg);
    width: 12px;
    height: 12px;
    background: #fff;
    box-shadow: 2px -2px 4px rgba(0,0,0,0.06);
}

.floating-menu .fm-item.active .fm-panel {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translateY(-50%) translateX(0);
}

.floating-menu .fm-panel-title {
    background: #1E4E79;
    color: #fff;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 700;
}

.floating-menu .fm-panel-body {
    padding: 12px 14px;
}

.floating-menu .fm-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 0;
    border-bottom: 1px dotted #e9ecef;
}

.floating-menu .fm-row:last-child {
    border-bottom: none;
}

.floating-menu .fm-key {
    font-size: 12px;
    color: #666;
    font-weight: 500;
}

.floating-menu .fm-val {
    font-size: 12px;
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
    font-size: 13px;
    font-weight: 700;
}

.floating-menu .fm-holiday .fm-val {
    color: #d32f2f;
}

.floating-menu .fm-bank {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px dotted #e9ecef;
}

.floating-menu .fm-bank:last-of-type {
    border-bottom: none;
}

.floating-menu .fm-bank-name {
    font-size: 12px;
    font-weight: 600;
    color: #333;
}

.floating-menu .fm-bank-num {
    font-size: 11px;
    font-weight: 700;
    color: #d32f2f;
    font-family: 'Consolas', 'Monaco', monospace;
    letter-spacing: -0.3px;
}

.floating-menu .fm-bank-owner {
    text-align: center;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e9ecef;
    font-size: 11px;
    color: #555;
    font-weight: 500;
}

.floating-menu .fm-links {
    padding: 8px 10px;
}

.floating-menu .fm-link {
    display: block;
    padding: 7px 10px;
    margin-bottom: 3px;
    font-size: 12px;
    font-weight: 500;
    color: #444;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.floating-menu .fm-link:hover {
    background: #e3f2fd;
    color: #1565c0;
    transform: translateX(-2px);
}

.floating-menu .fm-link.fm-kakao:hover {
    background: #fff9c4;
    color: #795548;
}

/* 태블릿 이하 */
@media (max-width: 1124px) {
    .floating-menu {
        right: 8px;
        gap: 5px;
    }

    .floating-menu .fm-circle {
        width: 72px;
        height: 72px;
    }

    .floating-menu .fm-icon {
        font-size: 18px;
    }

    .floating-menu .fm-label {
        font-size: 12px;
    }

    .floating-menu .fm-panel {
        right: 82px;
        width: 220px;
    }
}

/* 모바일 */
@media (max-width: 480px) {
    .floating-menu {
        right: 6px;
        gap: 4px;
    }

    .floating-menu .fm-circle {
        width: 58px;
        height: 58px;
    }

    .floating-menu .fm-icon {
        font-size: 15px;
    }

    .floating-menu .fm-label {
        font-size: 9px;
    }

    .floating-menu .fm-panel {
        position: fixed;
        right: 10px;
        left: 10px;
        top: auto;
        bottom: 80px;
        width: auto;
        transform: translateY(10px);
    }

    .floating-menu .fm-item.active .fm-panel {
        transform: translateY(0);
    }

    .floating-menu .fm-panel::after {
        display: none;
    }
}
</style>

<script>
(function() {
    'use strict';
    document.addEventListener('DOMContentLoaded', function() {
        var menu = document.getElementById('floating-menu');
        if (!menu) return;

        var items = menu.querySelectorAll('.fm-item[data-panel]');

        items.forEach(function(item) {
            var circle = item.querySelector('.fm-circle');
            if (!circle) return;

            item.addEventListener('mouseenter', function() {
                items.forEach(function(other) {
                    if (other !== item && !other.classList.contains('pinned')) {
                        other.classList.remove('active');
                    }
                });
                item.classList.add('active');
            });

            item.addEventListener('mouseleave', function() {
                if (!item.classList.contains('pinned')) {
                    item.classList.remove('active');
                }
            });

            circle.addEventListener('click', function(e) {
                e.stopPropagation();
                if (item.classList.contains('pinned')) {
                    item.classList.remove('pinned');
                    item.classList.remove('active');
                } else {
                    items.forEach(function(other) {
                        other.classList.remove('active');
                        other.classList.remove('pinned');
                    });
                    item.classList.add('active');
                    item.classList.add('pinned');
                }
            });
        });

        menu.querySelectorAll('.fm-panel').forEach(function(panel) {
            panel.addEventListener('click', function(e) { e.stopPropagation(); });
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
