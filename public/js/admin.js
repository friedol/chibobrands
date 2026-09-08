/**
  * Optimized Admin Framework JS
  * Consolidated for maximum performance
  */
(function () {
    const preloader = document.getElementById("preloader");
    
    // 1. Ultra-Fast Page Reveal
    const hideLoader = () => {
        if (preloader) {
            preloader.classList.add("fade-out");
            // Remove from layout once faded
            setTimeout(() => { preloader.style.display = 'none'; }, 100);
        }
    };

    if (document.readyState === "complete" || document.readyState === "interactive") {
        hideLoader();
    } else {
        document.addEventListener("DOMContentLoaded", hideLoader);
        window.addEventListener("load", hideLoader);
    }
    
    // Safety fallbacks - super fast reveal
    setTimeout(hideLoader, 150);
    setTimeout(hideLoader, 1000);

    // Instant reveal on Back/Forward browser navigation (bfcache)
    window.addEventListener("pageshow", hideLoader);
    window.addEventListener("popstate", hideLoader);

    document.addEventListener("DOMContentLoaded", function () {
        hideLoader();

        // 2. Navigation Preloader (Only for real navigations)
        document.body.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (!link || link.target === '_blank' || link.href.includes('#') || link.href.startsWith('javascript:') || link.hasAttribute('data-no-preloader')) return;
            
            // Only show if navigating to a different URL
            if (link.href && link.href !== window.location.href.split('#')[0]) {
                if (preloader) {
                    preloader.style.display = 'flex';
                    preloader.classList.remove("fade-out");
                    // Auto safety timeout to hide preloader if navigation is delayed or cancelled
                    setTimeout(hideLoader, 1500);
                }
            }
        });

        // 3. Instant Logout Handler
        const logoutForm = document.getElementById('logout-form');
        if (logoutForm) {
            logoutForm.addEventListener('submit', () => {
                const btn = logoutForm.querySelector('.logout-btn');
                if (btn) btn.disabled = true;
            });
        }


        // 5. Scroll Performance
        let ticking = false;
        const navbar = document.querySelector('.top-navbar');
        window.addEventListener('scroll', () => {
            if (!ticking) {
                window.requestAnimationFrame(() => {
                    const top = window.pageYOffset || document.documentElement.scrollTop;
                    navbar.classList.toggle('scrolled', top > 50);
                  
                    ticking = false;
                });
                ticking = true;
            }
        });
    });
})();
// Share Links Modal helper
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('shareLinksModal');
    if (!modalEl) return;
    const phoneWarning = modalEl.querySelector('#salerPhoneWarning');
    const copyButtons = modalEl.querySelectorAll('[data-copy-target]');
    copyButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = document.querySelector(this.getAttribute('data-copy-target'));
            if (target) {
                target.select();
                document.execCommand('copy');
                showSuccessToast('Link copied to clipboard');
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const sidebarNav = document.querySelector('.sidebar-nav') || sidebar;
    const submenuItems = document.querySelectorAll('[data-submenu-toggle]');
    const storageKey = 'admin_sidebar_state';

    // Desktop minimized sidebar (icon-only)
    const sidebarMinimizeToggle = document.getElementById('sidebarMinimizeToggle');
    const sidebarMinimizedIcon = document.getElementById('sidebarMinimizeIcon'); // optional (button may be removed)
    const minimizeStorageKey = 'admin_sidebar_minimized';

    function setSidebarMinimized(enabled) {
        // Only apply on desktop-like layouts
        if (!sidebar) return;
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('minimized');
            return;
        }

        sidebar.classList.toggle('minimized', !!enabled);
        localStorage.setItem(minimizeStorageKey, enabled ? '1' : '0');

        if (sidebarMinimizedIcon) {
            sidebarMinimizedIcon.classList.toggle('fa-angle-left', !enabled);
            sidebarMinimizedIcon.classList.toggle('fa-angle-right', enabled);
        }
    }

    // Restore minimized state (from saved setting)
    const savedMin = (localStorage.getItem(minimizeStorageKey) || '0') === '1';
    setSidebarMinimized(savedMin);

    // Use the existing top-bar red button to minimize on desktop.
    // (On mobile, the other sidebar handler still handles the slide-open behavior.)
    const topBarMinimizeButton = document.getElementById('mobileMenuToggle');
    if (topBarMinimizeButton) {
        topBarMinimizeButton.addEventListener('click', function () {
            if (!sidebar) return;
            if (window.innerWidth <= 992) return;
            const enabled = !sidebar.classList.contains('minimized');
            setSidebarMinimized(enabled);
        });
    }

    // Hover -> open submenu when minimized (desktop)
    // Very robust approach: position the submenu with `position: fixed` based on icon position.
    // This avoids any overflow/clipping issues from parent containers.
    const minimizedSubmenuItems = document.querySelectorAll('.sidebar .nav-item.has-submenu');
    const POP_WIDTH = 260;

    // Minimized mode: remove server-side `.active` submenu parents on reload.
    // This prevents unintended expansions when the current route marks items active.
    if (savedMin) {
        minimizedSubmenuItems.forEach(item => item.classList.remove('active'));
    }

    function forceHide(item) {
        if (!item) return;
        const submenu = item.querySelector('.nav-submenu');
        if (!submenu) return;
        submenu.style.opacity = '0';
        submenu.style.visibility = 'hidden';
        submenu.style.pointerEvents = 'none';
        item.classList.remove('active');
        delete item.dataset.popoutHover;
    }

    function showPopout(item, source = 'hover') {
        if (!item || !sidebar || !sidebar.classList.contains('minimized')) return;
        hideTooltip();
        const submenu = item.querySelector('.nav-submenu');
        if (!submenu) return;

        // Prevent duplication: when hovering a new icon, hide all other popouts.
        if (source === 'hover') {
            minimizedSubmenuItems.forEach(other => {
                if (other !== item) forceHide(other);
            });
        }

        const rect = item.getBoundingClientRect();
        submenu.style.position = 'fixed';
        // Keep the popout flush with the minimized sidebar (no visible gap).
        submenu.style.left = (rect.right + 0) + 'px';
        submenu.style.top = rect.top + 'px';
        submenu.style.width = POP_WIDTH + 'px';
        submenu.style.maxHeight = 'none';
        submenu.style.overflow = 'visible';
        submenu.style.opacity = '1';
        submenu.style.visibility = 'visible';
        submenu.style.pointerEvents = 'auto';
        submenu.style.zIndex = '30000';

        const wasActive = item.classList.contains('active');
        item.classList.add('active');

        // Mark only when the submenu is opened by hover (not when it was already open via click).
        if (source === 'hover' && !wasActive) {
            item.dataset.popoutHover = '1';
        } else {
            delete item.dataset.popoutHover;
        }
    }

    function hidePopout(item) {
        if (!item) return;
        const submenu = item.querySelector('.nav-submenu');
        if (!submenu) return;

        const openedByHover = item.dataset.popoutHover === '1';
        const isActive = item.classList.contains('active');

        // If currently open via click, don't hide on hover-leave.
        if (isActive && !openedByHover) return;

        submenu.style.opacity = '0';
        submenu.style.visibility = 'hidden';
        submenu.style.pointerEvents = 'none';
        if (openedByHover) {
            item.classList.remove('active');
        }
        delete item.dataset.popoutHover;
    }

    minimizedSubmenuItems.forEach(item => {
        let closeTimer = null;
        const submenu = item.querySelector('.nav-submenu');

        item.addEventListener('mouseenter', function () {
            if (!sidebar || !sidebar.classList.contains('minimized')) return;
            if (closeTimer) {
                clearTimeout(closeTimer);
                closeTimer = null;
            }
            showPopout(item, 'hover');
        });

        item.addEventListener('mouseleave', function () {
            if (!sidebar || !sidebar.classList.contains('minimized')) return;
            if (!submenu) return;

            // Delay close to allow cursor movement into submenu panel.
            closeTimer = setTimeout(() => {
                const submenuEl = item.querySelector('.nav-submenu');
                const keepOpen = item.matches(':hover') || (submenuEl && submenuEl.matches(':hover'));
                if (!keepOpen) hidePopout(item);
            }, 180);
        });

        if (submenu) {
            submenu.addEventListener('mouseenter', function () {
                if (!sidebar || !sidebar.classList.contains('minimized')) return;
                if (closeTimer) {
                    clearTimeout(closeTimer);
                    closeTimer = null;
                }
                showPopout(item, 'hover');
            });

            submenu.addEventListener('mouseleave', function () {
                if (!sidebar || !sidebar.classList.contains('minimized')) return;
                // Close after a short delay for stability
                closeTimer = setTimeout(() => {
                    if (!item.matches(':hover') && !submenu.matches(':hover')) hidePopout(item);
                }, 120);
            });
        }
    });

    // Avoid restoring multiple open popouts across pages.
    // (localStorage can remember several menus as active and then all become visible.)
    function closeAllPopoutsExcept(firstItem) {
        minimizedSubmenuItems.forEach(it => {
            if (it !== firstItem) forceHide(it);
        });
    }

    // Minimized mode: show a small popout/tooltip for items WITHOUT a submenu.
    let tooltipEl = null;
    let tooltipCloseTimer = null;

    function ensureTooltip() {
        if (tooltipEl) return;
        tooltipEl = document.createElement('div');
        tooltipEl.className = 'sidebar-minimized-tooltip';
        document.body.appendChild(tooltipEl);
    }

    function getNavLabelFromItem(item) {
        const link = item.querySelector('.nav-link');
        if (!link) return '';

        // Your markup usually has: <span class="d-flex ..."><i ...></i><span>Label</span></span>
        const labelSpan = link.querySelector('span.d-flex > span');
        const label = (labelSpan ? labelSpan.textContent : link.textContent).trim();
        // Clean up extra whitespace/newlines.
        return label.replace(/\s+/g, ' ');
    }

    function showTooltip(item) {
        if (!sidebar || !sidebar.classList.contains('minimized')) return;
        if (!item) return;
        ensureTooltip();

        const label = getNavLabelFromItem(item);
        if (!label) return;

        const rect = item.getBoundingClientRect();
        tooltipEl.textContent = label;
        tooltipEl.style.left = (rect.right + 10) + 'px';
        tooltipEl.style.top = rect.top + 'px';
        tooltipEl.style.opacity = '1';
        tooltipEl.style.visibility = 'visible';
        tooltipEl.style.pointerEvents = 'auto';
    }

    function hideTooltip() {
        if (!tooltipEl) return;
        tooltipEl.style.opacity = '0';
        tooltipEl.style.visibility = 'hidden';
        tooltipEl.style.pointerEvents = 'none';
        if (tooltipCloseTimer) {
            clearTimeout(tooltipCloseTimer);
            tooltipCloseTimer = null;
        }
    }

    // Attach events only to non-submenu top-level items (icon-only entries).
    // This avoids attaching tooltips inside submenu popouts.
    const minimizedSoloLinks = document.querySelectorAll(
        '.sidebar .sidebar-nav > .nav > .nav-item:not(.has-submenu) > .nav-link'
    );
    minimizedSoloLinks.forEach(link => {
        const item = link.closest('.nav-item');
        if (!item) return;

        link.addEventListener('mouseenter', function () {
            if (!sidebar || !sidebar.classList.contains('minimized')) return;
            if (tooltipCloseTimer) {
                clearTimeout(tooltipCloseTimer);
                tooltipCloseTimer = null;
            }
            showTooltip(item);
        });

        link.addEventListener('mouseleave', function () {
            if (!sidebar || !sidebar.classList.contains('minimized')) return;
            tooltipCloseTimer = setTimeout(() => {
                hideTooltip();
            }, 150);
        });

        link.addEventListener('click', function () {
            // Keep click stable: hide the tooltip immediately on navigation/click.
            hideTooltip();
            if (sidebar && sidebar.classList.contains('minimized')) {
                minimizedSubmenuItems.forEach(it => forceHide(it));
            }
        });
    });

    // When clicking actual submenu links (inside the popout), close everything for stability.
    // Guard: only run in minimized mode — in expanded mode this would incorrectly close the open submenu.
    const minimizedSubmenuLinks = document.querySelectorAll('.sidebar .nav-submenu .nav-link');
    minimizedSubmenuLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (!sidebar || !sidebar.classList.contains('minimized')) return;
            hideTooltip();
            minimizedSubmenuItems.forEach(it => forceHide(it));
        });
    });

    // If resized from mobile to desktop, re-apply minimized state
    window.addEventListener('resize', function () {
        if (!sidebar) return;
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('minimized');
            return;
        }
        const savedMin = (localStorage.getItem(minimizeStorageKey) || '0') === '1';
        setSidebarMinimized(savedMin);
    });

    // 1. Restore State
    const savedState = JSON.parse(localStorage.getItem(storageKey) || '{"openMenus":[], "scrollTop":0}');

    // Suppress submenu transitions during initial restore so the browser cannot
    // auto-scroll the sidebar to reveal the newly-visible active link after the animation.
    if (sidebar) sidebar.classList.add('sidebar-loading');

    // Restore Scroll Position
    if (sidebar && typeof savedState.scrollTop === 'number') {
        sidebarNav.scrollTop = savedState.scrollTop;
    }

    submenuItems.forEach(function (item, index) {
        const link = item.querySelector('.nav-link');
        const submenu = item.querySelector('.nav-submenu');
        // Use a unique identifier for the menu item (href of link or index)
        const menuId = link.getAttribute('href') || 'menu-' + index;
        item.dataset.menuId = menuId; // Store ID on element for saving later

        if (!submenu) return;

        // Check if submenu has active link on page load (Server Side Priority)
        const hasActiveSubmenu = submenu.querySelector('.nav-link.active');

        // Restore Open State (Client Side) OR Server Side Active.
        // In minimized mode we DO NOT auto-open popouts on reload (keeps UI stable),
        // popouts should appear only on hover/click handled above.
        const shouldAutoOpen = savedState.openMenus.includes(menuId) || hasActiveSubmenu;
        if (shouldAutoOpen) {
            if (!sidebar.classList.contains('minimized')) {
                item.classList.add('active');
            }
        }

        // Handle clicks on the main link (parent toggle)
        link.addEventListener('click', function (e) {
            // Only prevent default if this is the toggle link itself (not a submenu item)
            if (e.target.closest('.nav-link') === link) {
                e.preventDefault();
                e.stopPropagation();
                const prevScrollTop = sidebarNav ? sidebarNav.scrollTop : 0;
                item.classList.toggle('active');

            // Accordion behavior: Close other open menus
            if (item.classList.contains('active')) {
                submenuItems.forEach(function (otherItem) {
                    if (otherItem !== item) {
                        otherItem.classList.remove('active');
                        // Also force hide if minimized
                        if (window.innerWidth > 992 && sidebar && sidebar.classList.contains('minimized')) {
                            forceHide(otherItem);
                        }
                    }
                });
            }

                // When minimized, sync the fixed-position popout state with clicks.
                // This keeps "click to stay open" working even after hover-leave.
                delete item.dataset.popoutHover;
            }
            if (window.innerWidth > 992 && sidebar && sidebar.classList.contains('minimized')) {
                if (item.classList.contains('active')) {
                    showPopout(item, 'click');
                } else {
                    hidePopout(item);
                }
            }

            // In expanded mode, expanding/collapsing changes height and can cause the browser
            // to auto-scroll the sidebar. Preserve position for a stable UX.
            if (sidebar && !sidebar.classList.contains('minimized')) {
                requestAnimationFrame(() => {
                    sidebarNav.scrollTop = prevScrollTop;
                    saveSidebarState();
                });
            } else {
                saveSidebarState();
            }
        });
    });

    // If minimized is enabled and multiple submenu parents were marked active,
    // keep only the first open to avoid overlapping popouts after navigation.
    if (sidebar && sidebar.classList.contains('minimized')) {
        const activeParents = Array.from(submenuItems).filter(i => i.classList.contains('active'));
        if (activeParents.length > 1) {
            closeAllPopoutsExcept(activeParents[0]);
        }
    }

    // All submenus have been opened synchronously (no animation).
    // Re-lock scroll, then re-enable transitions in the next frame.
    if (sidebar) {
        if (typeof savedState.scrollTop === 'number') {
            sidebarNav.scrollTop = savedState.scrollTop;
        }
        requestAnimationFrame(() => {
            sidebar.classList.remove('sidebar-loading');
        });
    }

    // 2. Save State Function
    function saveSidebarState() {
        const openMenus = [];
        document.querySelectorAll('[data-submenu-toggle].active').forEach(item => {
            if (item.dataset.menuId) openMenus.push(item.dataset.menuId);
        });

        const state = {
            openMenus: openMenus,
            scrollTop: sidebarNav ? sidebarNav.scrollTop : 0
        };

        localStorage.setItem(storageKey, JSON.stringify(state));
    }

    // 3. Save Scroll Position on Scroll
    if (sidebar) {
        let scrollTimeout;
        sidebarNav.addEventListener('scroll', () => {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(saveSidebarState, 100);
        });

        // Instantly save sidebar scroll position when clicking any link inside the sidebar
        sidebar.addEventListener('click', (e) => {
            if (e.target.closest('.nav-link')) {
                saveSidebarState();
            }
        });
    }
});

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    // Toggle sidebar
    function toggleSidebar() {
        // Desktop: don't open the mobile sidebar. (Desktop minimize is handled separately.)
        if (window.innerWidth > 992) return;
        sidebar.classList.toggle('active');
        sidebarOverlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }

    // Open sidebar
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleSidebar);
    }

    // Close sidebar when clicking a nav link on mobile (exclude logout button and submenu toggles)
    const navLinks = document.querySelectorAll('.sidebar .nav-link:not(.logout-btn)');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // Only trigger for actual navigation links (ignore hashes/toggles/submenu parents)
            const isToggle = link.getAttribute('href') === '#'
                || link.hasAttribute('data-bs-toggle')
                || link.closest('[data-submenu-toggle]') !== null;
            
            if (window.innerWidth <= 992 && !isToggle) {
                // If the sidebar is active, toggle it off (hides overlay and restores scroll)
                if (sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            }
        });
    });

    // Close sidebar on window resize if screen becomes large
    window.addEventListener('resize', function () {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // Handle escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
});

// Auto-hide alerts (only for temporary auto-dismiss banners)
setTimeout(function () {
    const alerts = document.querySelectorAll('.alert-auto-dismiss');
    alerts.forEach(alert => {
        if (alert.parentNode) {
            alert.remove();
        }
    });
}, 5000);


// Removed global button loading state handler to prevent stuck 'Processing...' state

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]:not([href="#"])').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Add hover effects to cards
document.querySelectorAll('.card').forEach(card => {
    card.addEventListener('mouseenter', function () {
        this.style.transform = 'translateY(-5px)';
    });

    card.addEventListener('mouseleave', function () {
        this.style.transform = 'translateY(0)';
    });
});

// Smart navbar - hide/show on scroll
let lastScrollTop = 0;
const navbar = document.querySelector('.top-navbar');

window.addEventListener('scroll', function () {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (scrollTop > 100) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }

    // Hide navbar when scrolling down, show when scrolling up
    /*
    if (scrollTop > lastScrollTop && scrollTop > 200) {
        navbar.style.transform = 'translateY(-100%)';
    } else {
        navbar.style.transform = 'translateY(0)';
    }
    */

    lastScrollTop = scrollTop;
});
function showNotification(message, type = 'success', title = null, duration = 5000) {
    const container = document.getElementById('notificationContainer');
    if (!container) return;

    // Set default titles
    const titles = {
        success: title || 'Success',
        error: title || 'Error',
        warning: title || 'Warning',
        info: title || 'Message'
    };

    // Set icons
    const icons = {
        success: '<i class="fas fa-check"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `custom-notification ${type}`;
    notification.innerHTML = `
        <div class="notification-icon ${type}">
            ${icons[type]}
        </div>
        <div class="notification-content">
            <div class="notification-title">${titles[type]}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" aria-label="Close notification">
            <i class="fas fa-times"></i>
        </button>
        <div class="notification-progress"></div>
    `;

    container.appendChild(notification);

    // Initial progress bar animation
    const progressBar = notification.querySelector('.notification-progress');
    progressBar.style.animationDuration = `${duration}ms`;
    progressBar.classList.add('notification-progress-run');

    const closeFn = () => {
        if (notification.classList.contains('hiding')) return;
        notification.classList.add('hiding');
        setTimeout(() => notification.remove(), 400);
    };

    // Auto remove
    const autoClose = setTimeout(closeFn, duration);

    // Manual close
    notification.querySelector('.notification-close').addEventListener('click', (e) => {
        clearTimeout(autoClose);
        closeFn();
    });
}

// Session messages are handled in the Blade template
// Use window.showSessionNotifications() to display them

// Modern Confirmation Modal System
window.modernConfirm = function (message, onConfirm, options = {}) {
    const defaults = {
        title: 'Confirm Action',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        type: 'warning', // warning, danger, success, info
        icon: 'fa-exclamation-triangle'
    };

    const settings = { ...defaults, ...options };

    // Create modal HTML
    const modalHTML = `
                <div class="modern-confirm-overlay" id="modernConfirmModal">
                    <div class="modern-confirm-modal">
                        <div class="modern-confirm-icon ${settings.type}">
                            <i class="fas ${settings.icon}"></i>
                        </div>
                        <h3 class="modern-confirm-title">${settings.title}</h3>
                        <p class="modern-confirm-message">${message}</p>
                        <div class="modern-confirm-actions">
                            <button class="modern-confirm-btn cancel" onclick="closeModernConfirm()">
                                <i class="fas fa-times me-2"></i>${settings.cancelText}
                            </button>
                            <button class="modern-confirm-btn confirm ${settings.type}" id="modernConfirmBtn">
                                <i class="fas fa-check me-2"></i>${settings.confirmText}
                            </button>
                        </div>
                    </div>
                </div>
            `;

    // Add to body
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Add event listeners
    document.getElementById('modernConfirmBtn').addEventListener('click', function () {
        closeModernConfirm();
        onConfirm();
    });

    // Close on overlay click
    document.getElementById('modernConfirmModal').addEventListener('click', function (e) {
        if (e.target === this) {
            closeModernConfirm();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function escapeHandler(e) {
        if (e.key === 'Escape') {
            closeModernConfirm();
            document.removeEventListener('keydown', escapeHandler);
        }
    });

    // Animate in
    setTimeout(() => {
        document.getElementById('modernConfirmModal').classList.add('show');
    }, 10);

    return false; // Prevent default form submission
};

window.closeModernConfirm = function () {
    const modal = document.getElementById('modernConfirmModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.remove();
        }, 300);
    }
};

// Toast notification function for admin
function showSuccessToast(message) {
    // Check if toast container exists, if not create it
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
                <div id="${toastId}" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-check-circle me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);

    // Initialize and show toast
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });

    toast.show();

    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', function () {
        toastElement.remove();
    });
}

// Smart Notification Features
(function () {
    // Mark notification as read
    window.markNotificationRead = function (notificationId) {
        fetch(`/admin/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                // Remove notification from list
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    notificationItem.closest('li').style.opacity = '0.5';
                    setTimeout(() => {
                        notificationItem.closest('li').remove();
                        updateNotificationCount();
                    }, 300);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
                // Fallback: redirect to notification page
                window.location.href = `/admin/notifications/${notificationId}`;
            });
    };

    // Mark all notifications as read
    const markAllReadBtn = document.querySelector('.mark-all-read-btn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            fetch(window.adminConfig?.routes?.notificationsMarkAllRead || '/admin/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    if (response.ok) {
                        // Clear all notifications from dropdown
                        if (notificationList) {
                            notificationList.innerHTML = `
                                <div class="p-5 text-center">
                                    <div class="mb-3">
                                        <i class="fas fa-check-circle text-success" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                    </div>
                                    <p class="text-muted small mb-0">You're all caught up!</p>
                                </div>
                            `;
                        }
                        updateNotificationCount();
                        // Close dropdown
                        const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationDropdown'));
                        if (dropdown) {
                            dropdown.hide();
                        }
                    } else {
                        // Fallback: reload page
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                    window.location.href = window.adminConfig?.routes?.notificationsIndex || '/admin/notifications';
                });
        });
    }

    // Update notification count badge
    function updateNotificationCount() {
        const notificationList = document.getElementById('notificationList');
        const remainingNotifications = notificationList.querySelectorAll('.notification-item').length;
        const badge = document.querySelector('#notificationDropdown .badge');
        const headerBadge = document.querySelector('.notification-dropdown .dropdown-header .badge');

        if (remainingNotifications === 0) {
            if (badge) badge.remove();
        } else {
            if (badge) badge.textContent = remainingNotifications > 99 ? '99+' : remainingNotifications;
        }
    }

    // Optimized Auto-refresh with Visibility Check & Sound Alerts
    let notificationRefreshInterval;
    let lastKnownNotificationId = null;
    let isInitialLoad = true;

    function startNotificationRefresh() {
        if (!window.location.pathname.startsWith('/admin')) return;

        const refresh = () => {
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('notificationDropdown'));
            // Still refresh if dropdown is closed to update badge and play sound
            if (document.visibilityState === 'visible' && (!dropdown || !dropdown._isShown())) {
                refreshNotifications();
            }
        };

        notificationRefreshInterval = setInterval(refresh, 30000); // 30 seconds for better responsiveness
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'visible') refresh();
        });

        // Initial check
        setTimeout(refresh, 1000);
    }

    // Refresh notifications count and list
    function refreshNotifications() {
        fetch(window.adminConfig?.routes?.notificationsRefreshCount || '/admin/notifications/refresh-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('#notificationDropdown .badge');
                const sidebarBadge = document.querySelector('.sidebar .nav-link[href*="notifications"] .badge');

                // Update badge in top bar
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                    } else {
                        const icon = document.querySelector('#notificationDropdown i');
                        if (icon) {
                            const newBadge = document.createElement('span');
                            newBadge.className = 'position-absolute translate-middle badge rounded-pill bg-danger';
                            newBadge.style = 'top: 8px; right: -5px; border: 2px solid #fff; font-size: 0.65rem; min-width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; padding: 0;';
                            newBadge.textContent = data.count > 99 ? '99+' : data.count;
                            icon.parentElement.appendChild(newBadge);
                        }
                    }

                    // Update sidebar badge if it exists
                    if (sidebarBadge) {
                        sidebarBadge.textContent = data.count;
                        sidebarBadge.style.display = 'inline-block';
                    }
                } else {
                    if (badge) badge.remove();
                    if (sidebarBadge) sidebarBadge.style.display = 'none';
                }


                // Check for new notification
                if (data.latest && data.latest.id !== lastKnownNotificationId) {
                    // Play sound for new notifications (including on initial load if unread exists)
                    playNotificationSound(data.latest.id);

                    // Only show toast if not initial load (to avoid spam on login)
                    if (!isInitialLoad) {
                        showNotification(
                            `<strong>${data.latest.title}</strong><br>${data.latest.message}<br><a href="${data.latest.url}" class="btn btn-sm btn-light mt-2 p-1 px-2" style="font-size: 0.7rem;">View Details</a>`,
                            'info',
                            'New Activity',
                            10000
                        );
                    }
                    lastKnownNotificationId = data.latest.id;
                }
                isInitialLoad = false;
            })
            .catch(error => console.error('Error refreshing notifications:', error));
    }

    function playNotificationSound(notificationId) {
        // Check if we already played sound for this notification today
        const lastPlayed = JSON.parse(localStorage.getItem('notification_sound_log') || '{}');
        const today = new Date().toDateString();

        if (lastPlayed.id === notificationId && lastPlayed.date === today) {
            return; // Already played for this notification today
        }

        const sound = document.getElementById('notificationSound');
        if (sound) {
            sound.currentTime = 0;
            sound.play()
                .then(() => {
                    // Log that we played sound for this notification
                    localStorage.setItem('notification_sound_log', JSON.stringify({
                        id: notificationId,
                        date: today
                    }));
                })
                .catch(e => console.log('Audio playback blocked:', e));
        }
    }

    // Refresh full list when opening dropdown
    document.getElementById('notificationDropdown')?.addEventListener('show.bs.dropdown', function () {
        const list = document.getElementById('notificationList');
        if (list) {
            list.style.opacity = '0.7';

            refreshNotifications();
            setTimeout(() => list.style.opacity = '1', 200);
        }
    });

    // Start auto-refresh on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startNotificationRefresh);
    } else {
        startNotificationRefresh();
    }

    // Clean up interval on page unload
    window.addEventListener('beforeunload', function () {
        if (notificationRefreshInterval) {
            clearInterval(notificationRefreshInterval);
        }
    });

    // Handle responsive dropdown positioning
    function adjustDropdownPosition() {
        if (window.innerWidth <= 768) {
            const dropdowns = document.querySelectorAll('.notification-dropdown, .message-dropdown');
            dropdowns.forEach(dropdown => {
                dropdown.style.position = 'fixed';
                dropdown.style.right = '1rem';
                dropdown.style.left = 'auto';
            });
        }
    }

    window.addEventListener('resize', adjustDropdownPosition);

    // Enable audio playback on first user interaction (required by browsers)
    // Enable audio playback on first user interaction (required by browsers)
    function unlockAudio() {
        const sound = document.getElementById('notificationSound');
        if (sound) {
            // Mute, play, then pause and unmute to unlock without sound
            sound.muted = true;
            sound.play().then(() => {
                sound.pause();
                sound.currentTime = 0;
                sound.muted = false; // Unmute for future real notifications
            }).catch(() => { });
        }
        document.removeEventListener('click', unlockAudio);
        document.removeEventListener('keydown', unlockAudio);
    }
    document.addEventListener('click', unlockAudio, { once: true });
    document.addEventListener('keydown', unlockAudio, { once: true });
    adjustDropdownPosition();
})();