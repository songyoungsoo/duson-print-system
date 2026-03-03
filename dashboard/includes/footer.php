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

    <!-- Chat Sound (global, shared with mini-chat) -->
    <script>
    (function() {
        var audioCtx = null;
        window.playNotificationSound = function() {
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
        };

        // Legacy chat popup (dashboard/chat/index.php에서 사용)
        var chatPopup = null;
        window._openChatPopup = function() {
            if (chatPopup && !chatPopup.closed) { chatPopup.focus(); return; }
            chatPopup = window.open('/chat/admin_floating.php', 'admin_chat_popup', 'width=420,height=650,scrollbars=yes,resizable=yes');
        };
    })();
    </script>

    <!-- Mini-Chat Widget (replaces old toast notifications) -->
    <?php include __DIR__ . '/mini-chat.php'; ?>
    <!-- 전화번호 자동 포맷팅 (010-1234-5678) -->
    <script>
    (function() {
        function formatKoreanPhone(v) {
            var d = v.replace(/\D/g, '');
            if (d.length === 0) return '';
            if (d.substring(0, 2) === '02') {
                if (d.length <= 2) return d;
                if (d.length <= 5) return d.substring(0,2) + '-' + d.substring(2);
                if (d.length <= 9) return d.substring(0,2) + '-' + d.substring(2, d.length-4) + '-' + d.substring(d.length-4);
                return d.substring(0,2) + '-' + d.substring(2,6) + '-' + d.substring(6,10);
            }
            if (d.length <= 3) return d;
            if (d.length <= 7) return d.substring(0,3) + '-' + d.substring(3);
            if (d.length <= 11) return d.substring(0,3) + '-' + d.substring(3, d.length-4) + '-' + d.substring(d.length-4);
            return d.substring(0,3) + '-' + d.substring(3,7) + '-' + d.substring(7,11);
        }
        function applyPhoneFormat(input) {
            input.addEventListener('input', function() {
                var pos = this.selectionStart;
                var before = this.value;
                var formatted = formatKoreanPhone(before);
                if (formatted !== before) {
                    this.value = formatted;
                    var diff = formatted.length - before.length;
                    this.setSelectionRange(pos + diff, pos + diff);
                }
            });
            if (input.value && /^\d{9,11}$/.test(input.value.replace(/\D/g, ''))) {
                input.value = formatKoreanPhone(input.value);
            }
        }
        document.querySelectorAll('input[type="tel"], input[name="phone"], input[name="Hendphone"]').forEach(applyPhoneFormat);
        ['customer_phone', 'customer_mobile', 'qfm-phone'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) applyPhoneFormat(el);
        });
        window.formatKoreanPhone = formatKoreanPhone;
        window.applyPhoneFormat = applyPhoneFormat;
    })();
    </script>

</body>
</html>
