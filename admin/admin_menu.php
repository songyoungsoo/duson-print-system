     <table border=0 align=center width=100% cellpadding=0 cellspacing=0 class='coolBar'>
       <tr>
         <td align=right width=100% height=28>
		 <font style='font-family:굴림; font-size: 12pt; color:#8F8F8F; font-weight:bold;'><i>
		 MlangWeb관리프로그램 &nbsp;
		 </i></font>
		 </td>
       </tr>
     </table>

<script language="JavaScript1.2" src="<?=($M123 === '..') ? '../js/MlangCoolmenus.js' : $M123 . '/js/MlangCoolmenus.js'?>"></script>
<script language="JavaScript1.2" src="<?=($M123 === '..') ? '../js/MlangMenu.php' : $M123 . '/js/MlangMenu.php'?>"></script>

<!-- 채팅 알림 배지 (모든 관리자 페이지 공통) -->
<div id="admin-chat-notif" onclick="window.open('/chat/admin.php','_blank')" title="고객 채팅" style="position:fixed; top:6px; right:16px; z-index:99999; cursor:pointer; display:flex; align-items:center; gap:4px; background:#fff; border:1px solid #ddd; border-radius:16px; padding:3px 10px 3px 8px; box-shadow:0 1px 4px rgba(0,0,0,0.12); font-family:굴림,sans-serif; transition:box-shadow .2s;">
    <span style="font-size:16px; line-height:1;">💬</span>
    <span id="admin-chat-badge" style="display:none; background:#ff3b30; color:#fff; font-size:10px; font-weight:bold; min-width:16px; height:16px; border-radius:8px; text-align:center; line-height:16px; padding:0 4px;">0</span>
    <span id="admin-chat-label" style="font-size:11px; color:#888;">채팅</span>
</div>
<script>
(function(){
    var prevCount = -1;
    var soundPlayed = false;

    function playNotifSound() {
        try {
            var ctx = new (window.AudioContext || window.webkitAudioContext)();
            // 첫 번째 비프
            var osc1 = ctx.createOscillator();
            var gain1 = ctx.createGain();
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.frequency.value = 880;
            osc1.type = 'sine';
            gain1.gain.setValueAtTime(0.25, ctx.currentTime);
            gain1.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
            osc1.start(ctx.currentTime);
            osc1.stop(ctx.currentTime + 0.15);
            // 두 번째 비프 (약간 높은 톤)
            var osc2 = ctx.createOscillator();
            var gain2 = ctx.createGain();
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.frequency.value = 1100;
            osc2.type = 'sine';
            gain2.gain.setValueAtTime(0.25, ctx.currentTime + 0.18);
            gain2.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
            osc2.start(ctx.currentTime + 0.18);
            osc2.stop(ctx.currentTime + 0.35);
        } catch(e) {}
    }

    function checkChat() {
        fetch('/chat/api.php?action=get_admin_unread_count')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d.success || !d.data) return;
                var count = d.data.count || 0;
                var badge = document.getElementById('admin-chat-badge');
                var label = document.getElementById('admin-chat-label');
                var notif = document.getElementById('admin-chat-notif');
                if (!badge) return;

                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                    label.textContent = count + '건';
                    notif.style.borderColor = '#ff3b30';
                    notif.title = '미읽은 고객 채팅 ' + count + '건 (클릭하여 열기)';
                    // 새 메시지 도착 시 알림음 (첫 로드 제외)
                    if (prevCount >= 0 && count > prevCount) {
                        playNotifSound();
                    }
                } else {
                    badge.style.display = 'none';
                    label.textContent = '채팅';
                    notif.style.borderColor = '#ddd';
                    notif.title = '고객 채팅 (클릭하여 열기)';
                }
                prevCount = count;
            })
            .catch(function() {});
    }

    // 페이지 로드 후 1초 뒤 첫 체크, 이후 15초 간격
    setTimeout(function() {
        checkChat();
        setInterval(checkChat, 15000);
    }, 1000);
})();
</script>