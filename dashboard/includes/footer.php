    </div> <!-- End Layout Container -->

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Common JavaScript -->
    <script>
        // Number formatting helper
        function formatNumber(num) {
            return new Intl.NumberFormat('ko-KR').format(num);
        }
        
        // Currency formatting helper
        function formatCurrency(num) {
            if (num >= 100000000) {
                return (num / 100000000).toFixed(1) + '억';
            } else if (num >= 10000) {
                return (num / 10000).toFixed(0) + '만';
            }
            return formatNumber(num) + '원';
        }
        
        // Date formatting helper
        function formatDate(dateString) {
            const date = new Date(dateString);
            return `${date.getMonth()+1}/${date.getDate()} ${date.getHours()}:${String(date.getMinutes()).padStart(2,'0')}`;
        }
        
        // Toast notification helper
        function showToast(message, type = 'info') {
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            
            const toast = document.createElement('div');
            toast.className = `fixed top-14 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transition-opacity duration-300`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>

    <!-- Chat Notification Polling -->
    <script>
    (function() {
        // /dashboard/chat/ 페이지에서는 폴링 생략
        if (location.pathname.indexOf('/dashboard/chat') === 0) return;

        let prevTotal = -1;
        let chatPopup = null;
        let audioCtx = null;

        function escapeHtml(str) {
            var div = document.createElement('div');
            div.textContent = str;
            return div.textContent;
        }

        function playNotificationSound() {
            try {
                if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                var osc = audioCtx.createOscillator();
                var gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                osc.frequency.setValueAtTime(660, audioCtx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.3);
                osc.start(audioCtx.currentTime);
                osc.stop(audioCtx.currentTime + 0.3);
            } catch(e) {}
        }

        function openChatPopup() {
            if (chatPopup && !chatPopup.closed) {
                chatPopup.focus();
                return;
            }
            chatPopup = window.open(
                '/chat/admin_floating.php',
                'admin_chat_popup',
                'width=420,height=650,scrollbars=yes,resizable=yes'
            );
        }

        function showChatToast(room) {
            document.querySelectorAll('.chat-notify-toast').forEach(function(el) { el.remove(); });

            var name = escapeHtml(room.customer_name || '고객');
            var msg = escapeHtml(room.last_message || '');
            var preview = msg.length > 30 ? msg.substring(0, 30) + '...' : msg;

            var toast = document.createElement('div');
            toast.className = 'chat-notify-toast';
            toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;background:#1e293b;color:#fff;border-radius:12px;padding:14px 18px;box-shadow:0 8px 32px rgba(0,0,0,0.25);max-width:340px;font-family:"Noto Sans KR",sans-serif;animation:chatSlideUp .3s ease-out;cursor:default;';

            // 안전한 DOM 조작
            var row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:flex-start;gap:10px;';

            var icon = document.createElement('span');
            icon.style.cssText = 'font-size:22px;line-height:1;';
            icon.textContent = '\uD83D\uDCAC';

            var body = document.createElement('div');
            body.style.cssText = 'flex:1;min-width:0;';
            var titleEl = document.createElement('div');
            titleEl.style.cssText = 'font-size:13px;font-weight:700;margin-bottom:3px;';
            titleEl.textContent = '새 채팅: ' + name;
            var previewEl = document.createElement('div');
            previewEl.style.cssText = 'font-size:12px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
            previewEl.textContent = preview;
            body.appendChild(titleEl);
            body.appendChild(previewEl);

            var closeBtn = document.createElement('button');
            closeBtn.style.cssText = 'background:none;border:none;color:#64748b;font-size:16px;cursor:pointer;padding:0;line-height:1;';
            closeBtn.textContent = '\u00D7';
            closeBtn.addEventListener('click', function() { toast.remove(); });

            row.appendChild(icon);
            row.appendChild(body);
            row.appendChild(closeBtn);
            toast.appendChild(row);

            var replyBtn = document.createElement('button');
            replyBtn.style.cssText = 'display:block;width:100%;margin-top:10px;padding:7px;background:#3b82f6;color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;';
            replyBtn.textContent = '답변하기';
            replyBtn.addEventListener('click', function() { openChatPopup(); toast.remove(); });
            toast.appendChild(replyBtn);

            document.body.appendChild(toast);

            setTimeout(function() {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transition = 'opacity .3s';
                    setTimeout(function() { toast.remove(); }, 300);
                }
            }, 8000);
        }

        function updateBadge(count) {
            var badge = document.getElementById('chat-badge-count');
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        }

        function pollUnreadRooms() {
            fetch('/chat/api.php?action=get_unread_rooms')
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (!res.success) return;
                    var data = res.data;
                    var total = data.total_unread || 0;

                    updateBadge(total);

                    if (prevTotal >= 0 && total > prevTotal && data.rooms.length > 0) {
                        showChatToast(data.rooms[0]);
                        playNotificationSound();
                    }

                    prevTotal = total;
                })
                .catch(function() {});
        }

        window._openChatPopup = openChatPopup;

        var style = document.createElement('style');
        style.textContent = '@keyframes chatSlideUp{from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1}}';
        document.head.appendChild(style);

        pollUnreadRooms();
        setInterval(pollUnreadRooms, 10000);
    })();
    </script>
</body>
</html>
