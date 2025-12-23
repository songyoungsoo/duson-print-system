/**
 * 공통 인증 관련 JavaScript
 * 로그인 모달, 사용자 메뉴 등
 */

// 로그인 모달 표시
function showLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

// 로그인 모달 숨김
function hideLoginModal() {
    const modal = document.getElementById('loginModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// 로그인 탭 표시
function showLoginTab(event) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm && registerForm) {
        loginForm.classList.add('active');
        loginForm.classList.remove('login-form');
        loginForm.classList.add('login-form');
        
        registerForm.classList.remove('active');
        
        // 활성 탭 변경
        document.querySelectorAll('.login-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        if (event && event.target) {
            event.target.classList.add('active');
        }
    }
}

// 회원가입 탭 표시
function showRegisterTab(event) {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginForm && registerForm) {
        loginForm.classList.remove('active');
        
        registerForm.classList.add('active');
        registerForm.classList.remove('login-form');
        registerForm.classList.add('login-form');
        
        // 활성 탭 변경
        document.querySelectorAll('.login-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        if (event && event.target) {
            event.target.classList.add('active');
        }
    }
}

// 사용자 메뉴 토글
function toggleUserMenu() {
    const menuItems = document.getElementById('userMenuItems');
    if (menuItems) {
        menuItems.classList.toggle('show');
    }
}

// DOM 로드 완료 시 이벤트 바인딩
document.addEventListener('DOMContentLoaded', function() {
    // 모달 외부 클릭 시 닫기
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        loginModal.addEventListener('click', function(event) {
            if (event.target === loginModal) {
                hideLoginModal();
            }
        });
    }
    
    // 로그인 성공 시 서버에서 리다이렉트 처리하므로 여기서는 제거
    
    // 로그인/회원가입 에러 시 모달 자동 표시
    const errorMessage = document.querySelector('.login-message.error');
    if (errorMessage) {
        showLoginModal();
    }
    
    // 사용자 메뉴 외부 클릭 시 닫기
    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu-dropdown');
        const menuItems = document.getElementById('userMenuItems');
        
        if (userMenu && menuItems && !userMenu.contains(event.target)) {
            menuItems.classList.remove('show');
        }
    });
    
    // ESC 키로 모달 및 메뉴 닫기
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            hideLoginModal();
            
            const menuItems = document.getElementById('userMenuItems');
            if (menuItems) {
                menuItems.classList.remove('show');
            }
        }
    });
    
    // 회원가입 폼 비밀번호 확인 검증
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const password = document.getElementById('reg_password');
            const confirmPassword = document.getElementById('reg_confirm_password');
            
            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    event.preventDefault();
                    alert('비밀번호가 일치하지 않습니다.');
                    confirmPassword.focus();
                    return false;
                }
            }
        });
    }
});