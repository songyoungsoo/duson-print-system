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
</body>
</html>
