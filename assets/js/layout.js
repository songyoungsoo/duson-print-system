/**
 * Modern Homepage Layout Enhancement
 * Sophisticated interactions and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Modern Layout 초기화...');
    
    // Initialize all layout enhancements
    initializeProductCards();
    initializeScrollAnimations();
    initializeParallaxEffects();
    initializeCTAButton();
    
    console.log('Modern Layout 초기화 완료');
});

/**
 * Product Cards Enhancement
 */
function initializeProductCards() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach((card, index) => {
        // Staggered animation on load
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
        
        // Enhanced hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-12px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Initial state for animation
    productCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';
    });
}

/**
 * Scroll-triggered Animations
 */
function initializeScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                
                // Stagger child animations
                const children = entry.target.querySelectorAll('.feature-card, .process-step');
                children.forEach((child, index) => {
                    setTimeout(() => {
                        child.classList.add('animate-in');
                    }, index * 150);
                });
            }
        });
    }, observerOptions);
    
    // Observe sections
    const sections = document.querySelectorAll('.features-section, .process-section, .about-section');
    sections.forEach(section => {
        observer.observe(section);
    });
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        .features-section,
        .process-section,
        .about-section {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s ease;
        }
        
        .features-section.animate-in,
        .process-section.animate-in,
        .about-section.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
        
        .feature-card,
        .process-step {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease;
        }
        
        .feature-card.animate-in,
        .process-step.animate-in {
            opacity: 1;
            transform: translateY(0);
        }
    `;
    document.head.appendChild(style);
}

/**
 * Parallax Effects for Enhanced Visuals
 */
function initializeParallaxEffects() {
    let ticking = false;
    
    function updateParallax() {
        const scrolled = window.pageYOffset;
        const rate = scrolled * -0.5;
        
        // Process section background
        const processSection = document.querySelector('.process-section');
        if (processSection) {
            processSection.style.backgroundPosition = `center ${rate}px`;
        }
        
        // About section subtle parallax
        const aboutImage = document.querySelector('.about-image');
        if (aboutImage) {
            const imageRate = scrolled * -0.2;
            aboutImage.style.transform = `translateY(${imageRate}px)`;
        }
        
        ticking = false;
    }
    
    function requestTick() {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }
    
    window.addEventListener('scroll', requestTick);
}

/**
 * Enhanced CTA Button Interactions
 */
function initializeCTAButton() {
    const ctaButtons = document.querySelectorAll('.btn-cta');
    
    ctaButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Create ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Add ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}