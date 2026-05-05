// Wholesale Categories Page - Interactive Features

document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize AOS (Animate on Scroll)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-out',
            once: true,
            offset: 100
        });
    }
    
    // Search Functionality
    const searchInput = document.getElementById('categorySearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const categoryCards = document.querySelectorAll('.category-card-modern');
            
            categoryCards.forEach(card => {
                const categoryName = card.querySelector('.category-name').textContent.toLowerCase();
                const categoryDesc = card.querySelector('.category-description')?.textContent.toLowerCase() || '';
                
                if (categoryName.includes(searchTerm) || categoryDesc.includes(searchTerm)) {
                    card.style.display = 'flex';
                    card.classList.add('aos-animate');
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Show/hide empty state
            const visibleCards = document.querySelectorAll('.category-card-modern[style="display: flex;"]');
            const emptyState = document.getElementById('emptySearchState');
            if (emptyState) {
                emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
            }
        });
    }
    
    // Filter Functionality
    const filterSelect = document.getElementById('categoryFilter');
    if (filterSelect) {
        filterSelect.addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const categoryCards = document.querySelectorAll('.category-card-modern');
            
            categoryCards.forEach(card => {
                if (filterValue === 'all') {
                    card.style.display = 'flex';
                } else {
                    const productCount = parseInt(card.querySelector('.product-count-badge').textContent);
                    
                    if (filterValue === 'popular' && productCount > 5) {
                        card.style.display = 'flex';
                    } else if (filterValue === 'new' && productCount < 5) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                }
            });
        });
    }
    
    // Parallax Effect on Hero
    window.addEventListener('scroll', function() {
        const hero = document.querySelector('.categories-hero');
        if (hero) {
            const scrolled = window.pageYOffset;
            const parallax = scrolled * 0.5;
            hero.style.transform = `translateY(${parallax}px)`;
        }
    });
    
    // Card Hover 3D Effect
    const cards = document.querySelectorAll('.category-card-modern');
    cards.forEach(card => {
        card.addEventListener('mousemove', function(e) {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 20;
            const rotateY = (centerX - x) / 20;
            
            card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-8px)`;
        });
        
        card.addEventListener('mouseleave', function() {
            card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
        });
    });
    
    // Button Ripple Effect
    const buttons = document.querySelectorAll('.view-products-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
    
    // Smooth Scroll for Breadcrumb
    const breadcrumbLinks = document.querySelectorAll('.categories-breadcrumb a');
    breadcrumbLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Count Animation for Product Count Badges
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const badge = entry.target;
                const finalCount = parseInt(badge.getAttribute('data-count'));
                let currentCount = 0;
                const increment = Math.ceil(finalCount / 30);
                
                const counter = setInterval(() => {
                    currentCount += increment;
                    if (currentCount >= finalCount) {
                        badge.textContent = finalCount + ' products';
                        clearInterval(counter);
                    } else {
                        badge.textContent = currentCount + ' products';
                    }
                }, 30);
                
                observer.unobserve(badge);
            }
        });
    }, observerOptions);
    
    const badges = document.querySelectorAll('.product-count-badge');
    badges.forEach(badge => {
        const count = badge.textContent.match(/\d+/)[0];
        badge.setAttribute('data-count', count);
        observer.observe(badge);
    });
    
    // Floating Icons Animation Enhancement
    const floatingIcons = document.querySelectorAll('.floating-icon');
    floatingIcons.forEach((icon, index) => {
        icon.style.animationDelay = `${index * 2}s`;
        icon.style.animationDuration = `${15 + Math.random() * 10}s`;
    });
    
    // WhatsApp Button Tooltip
    const whatsappBtn = document.querySelector('.whatsapp-float-categories');
    if (whatsappBtn) {
        whatsappBtn.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'whatsapp-tooltip';
            tooltip.textContent = 'Chat with us!';
            tooltip.style.cssText = `
                position: absolute;
                right: 70px;
                top: 50%;
                transform: translateY(-50%);
                background: #25D366;
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 8px;
                white-space: nowrap;
                font-size: 0.9rem;
                font-weight: 600;
                box-shadow: 0 4px 10px rgba(0,0,0,0.2);
                animation: slideInRight 0.3s ease-out;
            `;
            this.appendChild(tooltip);
        });
        
        whatsappBtn.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.whatsapp-tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    }
    
    // Lazy Loading for Images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        observer.unobserve(img);
                    }
                }
            });
        });
        
        const lazyImages = document.querySelectorAll('img[data-src]');
        lazyImages.forEach(img => imageObserver.observe(img));
    }
    
    console.log('Wholesale Categories Page - Interactive features loaded successfully!');
});
