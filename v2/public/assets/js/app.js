document.addEventListener('DOMContentLoaded', function() {
    initCSRF();
    initCartCount();
});

function initCSRF() {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (token) {
        window.csrfToken = token;
    }
}

function initCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    updateCartBadge(cart.length);
}

function updateCartBadge(count) {
    const badge = document.getElementById('cart-count');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

async function fetchAPI(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.csrfToken || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    };
    
    const mergedOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers,
        },
    };
    
    const response = await fetch(url, mergedOptions);
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideIn 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function formatPrice(price) {
    return new Intl.NumberFormat('ko-KR').format(price);
}

function formatQuantity(quantity, unitName) {
    return `${formatPrice(quantity)}${unitName}`;
}

window.DusonApp = {
    fetchAPI,
    showToast,
    formatPrice,
    formatQuantity,
    updateCartBadge,
};
