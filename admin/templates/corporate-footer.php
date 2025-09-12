                        </div>
                        
                        <!-- Compact Footer (if needed) -->
                        <?php if (isset($footer_content) || isset($action_buttons)): ?>
                            <div class="compact-footer">
                                <?php if (isset($action_buttons) && !empty($action_buttons)): ?>
                                    <div class="btn-group">
                                        <?php foreach ($action_buttons as $button): ?>
                                            <?php
                                            $class = $button['class'] ?? 'btn-primary';
                                            $type = $button['type'] ?? 'button';
                                            $onclick = isset($button['onclick']) ? 'onclick="' . htmlspecialchars($button['onclick']) . '"' : '';
                                            $href = isset($button['href']) ? 'href="' . htmlspecialchars($button['href']) . '"' : '';
                                            ?>
                                            
                                            <?php if (isset($button['href'])): ?>
                                                <a <?php echo $href; ?> class="btn <?php echo htmlspecialchars($class); ?>" <?php echo $onclick; ?>>
                                                    <?php echo htmlspecialchars($button['text']); ?>
                                                </a>
                                            <?php else: ?>
                                                <button type="<?php echo htmlspecialchars($type); ?>" 
                                                        class="btn <?php echo htmlspecialchars($class); ?>" 
                                                        <?php echo $onclick; ?>>
                                                    <?php echo htmlspecialchars($button['text']); ?>
                                                </button>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (isset($footer_content)): ?>
                                    <div class="text-sm text-secondary">
                                        <?php echo $footer_content; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Page Statistics or Additional Info -->
                    <?php if (isset($page_stats)): ?>
                        <div class="mt-lg">
                            <div class="grid grid-auto">
                                <?php foreach ($page_stats as $stat): ?>
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="text-2xl font-bold text-primary">
                                                <?php echo htmlspecialchars($stat['value']); ?>
                                            </div>
                                            <div class="text-sm text-secondary">
                                                <?php echo htmlspecialchars($stat['label']); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- JavaScript for enhanced functionality -->
    <script>
        // Corporate Design System JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading states to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                if (button.type === 'submit' || button.classList.contains('btn-loading')) {
                    button.addEventListener('click', function() {
                        // Prevent double-clicking
                        if (this.classList.contains('loading')) return false;
                        
                        this.classList.add('loading');
                        const originalText = this.textContent;
                        this.textContent = '처리 중...';
                        this.disabled = true;
                        
                        // Reset after 3 seconds if form doesn't submit
                        setTimeout(() => {
                            if (this.classList.contains('loading')) {
                                this.classList.remove('loading');
                                this.textContent = originalText;
                                this.disabled = false;
                            }
                        }, 3000);
                    });
                }
            });
            
            // Auto-refresh functionality for specific pages
            const autoRefreshPages = ['admin.php', 'list.php'];
            const currentPage = window.location.pathname.split('/').pop();
            
            if (autoRefreshPages.some(page => currentPage.includes(page))) {
                // Add refresh indicator
                const header = document.querySelector('.corporate-header h1');
                if (header) {
                    const refreshIndicator = document.createElement('span');
                    refreshIndicator.className = 'text-xs text-tertiary ml-sm';
                    refreshIndicator.id = 'refresh-indicator';
                    header.appendChild(refreshIndicator);
                    
                    // Auto-refresh every 5 minutes for admin pages
                    let countdown = 300; // 5 minutes
                    const updateCountdown = () => {
                        const minutes = Math.floor(countdown / 60);
                        const seconds = countdown % 60;
                        refreshIndicator.textContent = `(자동 새로고침: ${minutes}:${seconds.toString().padStart(2, '0')})`;
                        
                        if (countdown <= 0) {
                            window.location.reload();
                        } else {
                            countdown--;
                            setTimeout(updateCountdown, 1000);
                        }
                    };
                    updateCountdown();
                }
            }
            
            // Table row click functionality
            const tableRows = document.querySelectorAll('.table tbody tr[data-href]');
            tableRows.forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function() {
                    window.location = this.dataset.href;
                });
            });
            
            // Form validation enhancement
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;
                    
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = 'var(--error-color)';
                            isValid = false;
                        } else {
                            field.style.borderColor = '';
                        }
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        alert('필수 입력 항목을 모두 입력해주세요.');
                    }
                });
            });
            
            // Sidebar collapse on mobile
            const toggleSidebar = () => {
                const sidebar = document.querySelector('.corporate-sidebar');
                if (window.innerWidth <= 768) {
                    sidebar.style.height = sidebar.style.height === 'auto' ? '60px' : 'auto';
                }
            };
            
            // Add mobile menu toggle
            if (window.innerWidth <= 768) {
                const header = document.querySelector('.corporate-header');
                const menuButton = document.createElement('button');
                menuButton.innerHTML = '☰';
                menuButton.className = 'btn btn-sm';
                menuButton.style.background = 'transparent';
                menuButton.style.border = '1px solid rgba(255,255,255,0.3)';
                menuButton.style.color = 'white';
                menuButton.onclick = toggleSidebar;
                
                header.insertBefore(menuButton, header.firstChild.nextSibling);
            }
            
            // Tooltip functionality for truncated text
            const addTooltips = () => {
                const elements = document.querySelectorAll('.table td, .card-title');
                elements.forEach(el => {
                    if (el.scrollWidth > el.clientWidth) {
                        el.title = el.textContent;
                    }
                });
            };
            addTooltips();
            
            // Search functionality enhancement
            const searchInputs = document.querySelectorAll('input[type="search"], input[name*="search"]');
            searchInputs.forEach(input => {
                let searchTimeout;
                input.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        // Add visual feedback for search
                        input.style.backgroundColor = '#f0f9ff';
                        setTimeout(() => {
                            input.style.backgroundColor = '';
                        }, 300);
                    }, 300);
                });
            });
        });
        
        // Utility functions for enhanced user experience
        window.CorporateUI = {
            showAlert: function(message, type = 'info') {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.textContent = message;
                alertDiv.style.position = 'fixed';
                alertDiv.style.top = '80px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '1000';
                alertDiv.style.minWidth = '300px';
                
                document.body.appendChild(alertDiv);
                
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            },
            
            confirmAction: function(message, callback) {
                if (confirm(message)) {
                    callback();
                }
            },
            
            formatNumber: function(num) {
                return new Intl.NumberFormat('ko-KR').format(num);
            },
            
            formatCurrency: function(amount) {
                return new Intl.NumberFormat('ko-KR', {
                    style: 'currency',
                    currency: 'KRW'
                }).format(amount);
            }
        };
    </script>
    
    <!-- Page-specific JavaScript -->
    <?php if (isset($additional_scripts)): ?>
        <?php foreach ($additional_scripts as $script): ?>
            <script src="<?php echo htmlspecialchars($script); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($inline_javascript)): ?>
        <script><?php echo $inline_javascript; ?></script>
    <?php endif; ?>
</body>
</html>