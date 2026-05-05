// Helper function to get the correct cart key based on current channel
function getCartKey() {
    return window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b') ? 'chibo_wholesale_cart' : 'chibo_retail_cart';
}

// Make getCartKey globally accessible
window.getCartKey = getCartKey;

// Force clear cart if there are items but no recent order activity
// This is a fallback to ensure cart gets cleared
function forceClearCartIfNeeded() {
    const cartKey = getCartKey();
    const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');
    console.log('Checking cart for force clear:', { cartKey, cartLength: cart.length, path: window.location.pathname });

    if (cart.length > 0) {
        // Check if we're on a page that should have an empty cart (like dashboard or shop)
        if (window.location.pathname.includes('/dashboard') ||
            window.location.pathname.includes('/customer/dashboard') ||
            window.location.pathname.includes('/shop')) {
            console.log('Force clearing cart on page:', window.location.pathname);
            localStorage.removeItem(cartKey);
            updateCartDisplay();
        }
    }
}

// Call the force clear function
forceClearCartIfNeeded();

// Additional cart clearing for guest orders
// Check if we just came from an order placement
if (window.location.search.includes('order_placed') ||
    window.location.search.includes('success') ||
    document.referrer.includes('whatsapp')) {
    console.log('Detected order placement, clearing cart');
    localStorage.removeItem(getCartKey());
    updateCartDisplay();
}

// Manual cart clearing function for debugging
window.clearCartNow = function () {
    console.log('Manually clearing cart...');
    const cartKey = getCartKey();
    localStorage.removeItem(cartKey);
    updateCartDisplay();
    console.log('Cart cleared manually');
};

// Cart functionality
function updateCartDisplay() {
    const cartKey = getCartKey();

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }
    const counters = document.querySelectorAll('.cart-count');
    counters.forEach(el => el.textContent = cart.length);
}

// Saler WhatsApp number persistence
const SALER_WHATSAPP_KEY = 'chibo_saler_whatsapp';
const defaultWhatsapp = window.chiboConfig?.dynamicWhatsapp || '255655392319'; // This is the current WhatsApp (may be saler's)
const actualDefaultWhatsapp = '255655392319'; // This is the actual default (never changes)

// Function to get saler WhatsApp number from URL parameter
function getSalerFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const salerParam = urlParams.get('saler');
    if (salerParam) {
        // Clean the phone number
        let phone = salerParam.replace(/[^\d\+]/g, '');
        phone = phone.replace(/^\+/, '');
        if (phone && phone.length > 0) {
            return phone;
        }
    }
    return null;
}

// Function to get WhatsApp number (from localStorage or default)
function getWhatsAppNumber() {
    // First check localStorage for stored saler number
    const storedSaler = localStorage.getItem(SALER_WHATSAPP_KEY);
    if (storedSaler && storedSaler.length > 0) {
        return storedSaler;
    }
    // Fallback to PHP default
    return defaultWhatsapp;
}

// Function to get saler phone for order assignment (returns phone without +, or empty string)
function getSalerPhoneForOrder() {
    let salerPhone = '';
    const cleanedActualDefault = String(actualDefaultWhatsapp).replace(/^\+/, '').trim();

    console.log('getSalerPhoneForOrder: Starting search', {
        actualDefaultWhatsapp: actualDefaultWhatsapp,
        cleanedActualDefault: cleanedActualDefault
    });

    // PRIORITY 1: Check current URL parameter first (most reliable for current session)
    const urlSaler = (new URLSearchParams(window.location.search)).get('saler');
    console.log('getSalerPhoneForOrder: URL saler param:', urlSaler);
    if (urlSaler) {
        // Clean the phone number (remove + and non-digits)
        let phone = urlSaler.replace(/[^\d\+]/g, '');
        phone = phone.replace(/^\+/, '').trim();
        console.log('getSalerPhoneForOrder: Cleaned URL phone:', phone, 'length:', phone.length, 'isDefault:', phone === cleanedActualDefault);
        if (phone && phone.length > 5 && phone !== cleanedActualDefault) {
            salerPhone = phone;
            console.log('getSalerPhoneForOrder: ✅ Using URL saler phone:', salerPhone);
            return salerPhone;
        } else {
            console.log('getSalerPhoneForOrder: ❌ URL phone rejected (too short or is default)');
        }
    }

    // PRIORITY 2: Try to get from localStorage
    const storedSaler = localStorage.getItem(SALER_WHATSAPP_KEY);
    console.log('getSalerPhoneForOrder: localStorage value:', storedSaler);
    if (storedSaler && storedSaler.trim().length > 0) {
        // Clean stored saler (remove + prefix for consistency)
        let cleaned = storedSaler.replace(/^\+/, '').trim();
        console.log('getSalerPhoneForOrder: Cleaned stored phone:', cleaned, 'length:', cleaned.length, 'isDefault:', cleaned === cleanedActualDefault);
        if (cleaned && cleaned !== cleanedActualDefault && cleaned.length > 5) {
            salerPhone = cleaned;
            console.log('getSalerPhoneForOrder: ✅ Using stored saler phone:', salerPhone);
            return salerPhone;
        } else {
            console.log('getSalerPhoneForOrder: ❌ Stored phone rejected (too short or is default)');
        }
    } else {
        console.log('getSalerPhoneForOrder: ❌ No value in localStorage');
    }

    console.log('getSalerPhoneForOrder: ❌ No saler phone found', {
        storedSaler: storedSaler,
        urlSaler: urlSaler,
        defaultWhatsapp: defaultWhatsapp,
        actualDefaultWhatsapp: actualDefaultWhatsapp,
        cleanedActualDefault: cleanedActualDefault
    });
    return '';
}

// Store saler WhatsApp number if present in URL
function initSalerWhatsApp() {
    const salerFromUrl = getSalerFromUrl();
    if (salerFromUrl) {
        // Clean and store the saler phone
        const cleaned = salerFromUrl.replace(/^\+/, '').trim();
        // Only store if it's different from the actual default
        const cleanedActualDefault = String(actualDefaultWhatsapp).replace(/^\+/, '').trim();
        if (cleaned && cleaned.length > 5 && cleaned !== cleanedActualDefault) {
            localStorage.setItem(SALER_WHATSAPP_KEY, cleaned);
            console.log('Saler WhatsApp number stored in localStorage:', cleaned, 'from URL:', salerFromUrl, 'actualDefault:', cleanedActualDefault);
            // Update WhatsApp links dynamically
            updateWhatsAppLinks(cleaned);
        } else {
            console.log('Saler phone matches default or is invalid, not storing:', cleaned, 'default:', cleanedActualDefault);
            // Clear localStorage if it matches default
            if (cleaned === cleanedActualDefault) {
                localStorage.removeItem(SALER_WHATSAPP_KEY);
            }
        }
    } else {
        // Check if we have a stored saler number
        const storedSaler = localStorage.getItem(SALER_WHATSAPP_KEY);
        if (storedSaler) {
            const cleanedStored = storedSaler.replace(/^\+/, '').trim();
            const cleanedActualDefault = String(actualDefaultWhatsapp).replace(/^\+/, '').trim();
            // Only use if it's different from default
            if (cleanedStored !== cleanedActualDefault) {
                console.log('Using existing stored saler from localStorage:', storedSaler);
                updateWhatsAppLinks(storedSaler);
            } else {
                console.log('Stored saler matches default, clearing localStorage');
                localStorage.removeItem(SALER_WHATSAPP_KEY);
            }
        } else {
            console.log('No saler phone found in URL or localStorage');
        }
    }
}

// Update all WhatsApp links on the page
function updateWhatsAppLinks(whatsappNumber) {
    // Update floating WhatsApp button
    const whatsappFloat = document.querySelector('.whatsapp-float');
    if (whatsappFloat) {
        const baseUrl = `https://wa.me/${whatsappNumber}?text=Hello%20CHIBO%20BRAND%20👋,%20I%20would%20like%20to%20know%20more%20about%20your%20services.`;
        whatsappFloat.setAttribute('href', baseUrl);
    }

    // Update social media WhatsApp link
    const socialWhatsapp = document.querySelector('a[href*="wa.me"]:not(.whatsapp-float)');
    if (socialWhatsapp && !socialWhatsapp.classList.contains('whatsapp-float')) {
        socialWhatsapp.setAttribute('href', `https://wa.me/${whatsappNumber}`);
    }
}

// Preserve saler parameter in ALL internal links globally
function updateNavigationLinks() {
    const storedSaler = localStorage.getItem(SALER_WHATSAPP_KEY);
    if (!storedSaler || storedSaler === defaultWhatsapp) {
        return; // No saler link active
    }

    // Get ALL links on the page (more comprehensive approach)
    const allLinks = document.querySelectorAll('a[href]');

    allLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (!href) return;

        // Skip if it's an external link (not localhost), mailto, tel, javascript, or already has saler param
        if (href.startsWith('http://') && !href.includes('localhost')) return;
        if (href.startsWith('https://') && !href.includes('localhost') && !href.includes(window.location.hostname)) return;
        if (href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:') || href.startsWith('#')) return;
        if (href.includes('saler=')) return; // Already has saler param

        // Skip cart modal triggers
        if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('openCartModal')) return;
        // Skip dropdown toggles
        if (link.getAttribute('data-bs-toggle') === 'dropdown') return;
        // Skip links that open in new tabs (external links)
        if (link.getAttribute('target') === '_blank' && !href.includes(window.location.hostname)) return;
        // Skip WhatsApp links
        if (href.includes('wa.me') || href.includes('whatsapp.com')) return;

        // Add saler parameter to the link
        try {
            const url = new URL(href, window.location.origin);
            url.searchParams.set('saler', '+' + storedSaler);
            link.setAttribute('href', url.pathname + url.search + (url.hash || ''));
        } catch (e) {
            // If URL parsing fails, append manually
            const separator = href.includes('?') ? '&' : '?';
            link.setAttribute('href', href + separator + 'saler=+' + storedSaler);
        }
    });
}

// Intercept ALL link clicks globally to preserve saler parameter
function preserveSalerOnNavigation(e) {
    const link = e.target.closest('a');
    if (!link) return;

    const href = link.getAttribute('href');
    if (!href) return;

    // Skip external links (not same domain), mailto, tel, javascript, anchors
    if (href.startsWith('http://') && !href.includes('localhost') && !href.includes(window.location.hostname)) return;
    if (href.startsWith('https://') && !href.includes('localhost') && !href.includes(window.location.hostname)) return;
    if (href.startsWith('mailto:') || href.startsWith('tel:') || href.startsWith('javascript:') || href.startsWith('#')) return;
    // Skip cart modal triggers
    if (link.getAttribute('onclick') && link.getAttribute('onclick').includes('openCartModal')) return;
    // Skip dropdown toggles
    if (link.getAttribute('data-bs-toggle') === 'dropdown') return;
    // Skip external links in new tabs
    if (link.getAttribute('target') === '_blank' && !href.includes(window.location.hostname)) return;
    // Skip WhatsApp links
    if (href.includes('wa.me') || href.includes('whatsapp.com')) return;

    const storedSaler = localStorage.getItem(SALER_WHATSAPP_KEY);
    if (!storedSaler || storedSaler === defaultWhatsapp) {
        return; // No saler link active
    }

    // If link doesn't have saler param, add it
    if (!href.includes('saler=')) {
        e.preventDefault();
        e.stopPropagation();
        try {
            const url = new URL(href, window.location.origin);
            url.searchParams.set('saler', '+' + storedSaler);
            window.location.href = url.pathname + url.search + (url.hash || '');
        } catch (err) {
            // If URL parsing fails, append manually
            const separator = href.includes('?') ? '&' : '?';
            window.location.href = href + separator + 'saler=+' + storedSaler;
        }
        return false;
    }
}

// Initialize saler WhatsApp on page load (early)
initSalerWhatsApp();

// Set up global click interceptor EARLY (before DOMContentLoaded)
// This ensures we catch all navigation clicks
document.addEventListener('click', preserveSalerOnNavigation, true); // Use capture phase

// Update cart display on page load
document.addEventListener('DOMContentLoaded', function () {
    updateCartDisplay();
    // Initialize saler WhatsApp after DOM is loaded (to update links)
    initSalerWhatsApp();
    // Update ALL links to preserve saler parameter (global update)
    updateNavigationLinks();

    // Watch for dynamically added links and update them aggressively
    const observer = new MutationObserver(function (mutations) {
        let shouldUpdate = false;
        mutations.forEach(function (mutation) {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.tagName === 'A' || (node.querySelectorAll && node.querySelectorAll('a').length > 0)) {
                            shouldUpdate = true;
                        }
                    }
                });
            }
            // Also check if attributes changed (like href)
            if (mutation.type === 'attributes' && mutation.attributeName === 'href') {
                shouldUpdate = true;
            }
        });
        if (shouldUpdate) {
            // Update immediately and also after a small delay
            updateNavigationLinks();
            setTimeout(updateNavigationLinks, 100);
        }
    });

    // Start observing the document body for added nodes and attribute changes
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['href']
    });

    // Update links multiple times to catch all content (late-loading content, AJAX, etc.)
    setTimeout(updateNavigationLinks, 300);
    setTimeout(updateNavigationLinks, 800);
    setTimeout(updateNavigationLinks, 1500);

    // Also listen for cross-tab cart updates
    window.addEventListener('storage', function (e) {
        if (e.key === 'chibo_retail_cart' || e.key === 'chibo_wholesale_cart') updateCartDisplay();
        // Also update WhatsApp links if saler number changes in another tab
        if (e.key === SALER_WHATSAPP_KEY) {
            const storedSaler = localStorage.getItem(SALER_WHATSAPP_KEY);
            if (storedSaler) {
                updateWhatsAppLinks(storedSaler);
                updateNavigationLinks(); // Update links when saler changes
            }
        }
    });
    // Listen for in-page custom events when pages update the cart
    document.addEventListener('chibo_cart_updated', updateCartDisplay);
});

// Show alert function
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
    document.body.appendChild(alertDiv);
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Loading state
function showLoading(element) {
    if (element) {
        element.classList.add('loading', 'show');
    }
}

function hideLoading(element) {
    if (element) {
        element.classList.remove('loading', 'show');
    }
}

// Toast notification functions
function showToast(message, type = 'success') {
    const toast = document.getElementById('cartToast');
    const toastMessage = document.getElementById('toastMessage');
    const toastHeader = toast.querySelector('.toast-header');

    // Update message
    toastMessage.textContent = message;

    // Update icon and color based on type
    const icon = toastHeader.querySelector('i');
    if (type === 'success') {
        icon.className = 'fas fa-check-circle text-success me-2';
        toastHeader.querySelector('strong').textContent = 'Success';
    } else if (type === 'error') {
        icon.className = 'fas fa-exclamation-circle text-danger me-2';
        toastHeader.querySelector('strong').textContent = 'Error';
    } else if (type === 'warning') {
        icon.className = 'fas fa-exclamation-triangle text-warning me-2';
        toastHeader.querySelector('strong').textContent = 'Warning';
    } else if (type === 'info') {
        icon.className = 'fas fa-info-circle text-info me-2';
        toastHeader.querySelector('strong').textContent = 'Info';
    }

    // Show toast
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Mobile sidebar functions - Fixed
function toggleMobileSidebar() {
    console.log('Toggle mobile sidebar clicked');
    const sidebar = document.getElementById('mobileSidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
        console.log('Sidebar toggled, classes:', sidebar.className);
    }
}

function closeMobileSidebar() {
    console.log('Close mobile sidebar called');
    const sidebar = document.getElementById('mobileSidebar');
    if (sidebar) {
        sidebar.classList.remove('open');
        console.log('Sidebar closed, classes:', sidebar.className);
    }
}

// Close sidebar when clicking outside
document.addEventListener('click', function (e) {
    const sidebar = document.getElementById('mobileSidebar');
    if (!sidebar || !sidebar.classList.contains('open')) return;

    // Check if click is on toggle button
    const toggleBtn = e.target.closest('button[onclick*="toggleMobileSidebar"]');
    if (toggleBtn) return; // Don't close if clicking toggle button

    // Check if click is inside sidebar content
    const sidebarContent = e.target.closest('.mobile-sidebar-content');
    if (sidebarContent) return; // Don't close if clicking inside sidebar

    // Close sidebar if clicking outside
    console.log('Clicking outside sidebar, closing...');
    closeMobileSidebar();
});

// Close sidebar on escape key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeMobileSidebar();
    }
});

// Close sidebar when clicking overlay
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('mobile-sidebar-overlay')) {
        closeMobileSidebar();
    }
});

// Cart modal functions
function openCartModal() {
    const modal = new bootstrap.Modal(document.getElementById('cartModal'));
    updateCartModal();
    modal.show();
}

function updateCartModal() {
    const body = document.getElementById('cartModalBody');
    const totalEl = document.getElementById('cartModalTotal');
    const itemsCountEl = document.getElementById('cartItemsCount');
    const subtotalEl = document.getElementById('modalSubtotal');
    const subtotalAmountEl = document.getElementById('modalSubtotalAmount');
    const vatRowEl = document.getElementById('modalVatRow');
    const vatAmountEl = document.getElementById('modalVatAmount');

    const cartKey = getCartKey();

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }

    if (!cart.length) {
        body.innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-shopping-cart fa-3x mb-3"></i><p>Your cart is empty</p></div>';
        totalEl.textContent = '0 TZS';
        itemsCountEl.textContent = '0';
        subtotalEl.style.display = 'none';
        vatRowEl.style.display = 'none';
        return;
    }

    let subtotal = 0;
    body.innerHTML = `
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="font-size: 0.8rem;">Product</th>
                                <th style="width:100px; font-size: 0.8rem;" class="text-center">Qty</th>
                                <th class="text-end" style="font-size: 0.8rem;">Unit Price</th>
                                <th class="text-end" style="font-size: 0.8rem;">Total</th>
                                <th class="text-center" style="font-size: 0.8rem; width:60px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="cartRows"></tbody>
                    </table>
                </div>`;
    const rows = document.getElementById('cartRows');

    cart.forEach((item, idx) => {
        // Handle different field name formats
        const unit = parseFloat(item.unitPrice || 0);
        const minQty = parseInt(item.minQuantity || 1);
        const qty = Math.max(minQty, parseInt(item.quantity || item.qty || minQty));
        const sub = item.totalPrice || item.total || (unit * qty);
        subtotal += sub;

        // Handle different variant field names and fix [object Object] issue
        const variantData = item.variant || item.variants || item.variations || {};
        let variantsText = '';

        if (variantData && typeof variantData === 'object') {
            if (Array.isArray(variantData)) {
                // Handle array format - filter out null/empty values
                variantsText = variantData
                    .filter(v => v && v.name && v.option_value && v.option_value !== 'N/A' && v.option_value !== 'null' && v.option_value !== 'undefined')
                    .map(v => `${v.name}: ${v.option_value}`)
                    .join(', ');
            } else {
                // Handle object format - filter out null/empty values
                variantsText = Object.entries(variantData)
                    .filter(([k, v]) => {
                        // Filter out null, undefined, empty strings, 'N/A', and invalid objects
                        if (!v || v === 'N/A' || v === 'null' || v === 'undefined' || v === '[object Object]') {
                            return false;
                        }
                        // For objects, check if they have valid name/value
                        if (typeof v === 'object' && v !== null) {
                            return !!(v.name || v.value || v.label);
                        }
                        return true;
                    })
                    .map(([k, v]) => {
                        // Handle nested objects
                        if (typeof v === 'object' && v !== null) {
                            if (v.name) return `${k}: ${v.name}`;
                            if (v.value) return `${k}: ${v.value}`;
                            if (v.label) return `${k}: ${v.label}`;
                            return null; // Skip invalid objects
                        }
                        return `${k}: ${v}`;
                    })
                    .filter(v => v !== null && v !== '') // Remove null/empty entries
                    .join(', ');
            }
        }

        const tr = document.createElement('tr');
        tr.innerHTML = `
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="${item.image || (window.chiboConfig?.defaultImage || '/images/default.webp')}" onerror="this.src='${window.chiboConfig?.defaultImage || '/images/default.webp'}'" class="rounded" style="width:35px;height:35px;object-fit:cover;">
                            <div style="min-width: 0; flex: 1;">
                                <div class="fw-semibold" style="font-size: 0.7rem; line-height: 1.2;">${item.name}</div>
                                ${variantsText ? `<div class="text-muted" style="font-size: 0.6rem; line-height: 1.1; word-break: break-word;">${variantsText}</div>` : ''}
                                <div class="text-muted small" style="font-size: 0.55rem;">${(item.customerType || item.channel) === 'wholesale' ? 'Wholesale' : 'Retail'}</div>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="input-group justify-content-center cart-qty-input-group" style="width:120px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="cartQtyChange(${idx}, -1)" style="font-size: 0.9rem; padding: 0.4rem 0.6rem; font-weight: 600;"><i class="fas fa-minus"></i></button>
                            <input type="number" class="form-control text-center cart-qty-input" min="${minQty}" value="${qty}" onchange="cartQtyInput(${idx}, this.value)" style="font-size: 0.9rem; padding: 0.35rem 0.25rem; font-weight: 700; color: #212529; border: 2px solid #dee2e6;">
                            <button class="btn btn-outline-secondary" type="button" onclick="cartQtyChange(${idx}, 1)" style="font-size: 0.9rem; padding: 0.4rem 0.6rem; font-weight: 600;"><i class="fas fa-plus"></i></button>
                        </div>
                    </td>
                    <td class="text-end" style="font-size: 0.65rem;">${unit.toLocaleString()} TZS</td>
                    <td class="text-end" style="font-size: 0.65rem; font-weight: bold;">${sub.toLocaleString()} TZS</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger" onclick="removeCartItem(${idx})" title="Remove item" style="font-size: 0.65rem; padding: 0.2rem 0.35rem;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>`;
        rows.appendChild(tr);
    });

    // Update totals display
    updateModalTotals(subtotal);
    itemsCountEl.textContent = String(cart.length);
}

// Make updateCartModal globally accessible
window.updateCartModal = updateCartModal;

function updateModalTotals(subtotal) {
    const vatCheckbox = document.getElementById('modalVatReceipt');
    const totalEl = document.getElementById('cartModalTotal');
    const subtotalEl = document.getElementById('modalSubtotal');
    const subtotalAmountEl = document.getElementById('modalSubtotalAmount');
    const vatRowEl = document.getElementById('modalVatRow');
    const vatAmountEl = document.getElementById('modalVatAmount');

    if (vatCheckbox && vatCheckbox.checked) {
        const vatAmount = Math.round(subtotal * 0.18);
        const total = subtotal + vatAmount;

        subtotalEl.style.display = 'flex';
        vatRowEl.style.display = 'flex';
        subtotalAmountEl.textContent = subtotal.toLocaleString() + ' TZS';
        vatAmountEl.textContent = vatAmount.toLocaleString() + ' TZS';
        totalEl.textContent = total.toLocaleString() + ' TZS';
    } else {
        subtotalEl.style.display = 'none';
        vatRowEl.style.display = 'none';
        totalEl.textContent = subtotal.toLocaleString() + ' TZS';
    }
}

function toggleModalVATReceipt() {
    const vatCheckbox = document.getElementById('modalVatReceipt');
    const cartKey = getCartKey();
    const cart = JSON.parse(localStorage.getItem(cartKey) || '[]');

    let subtotal = 0;
    cart.forEach(item => {
        const unit = parseFloat(item.unitPrice || 0);
        const qty = parseInt(item.quantity || item.qty || 1);
        const sub = item.totalPrice || item.total || (unit * qty);
        subtotal += sub;
    });

    updateModalTotals(subtotal);
}

function persistCart(cart) {
    const cartKey = getCartKey();
    localStorage.setItem(cartKey, JSON.stringify(cart));
    updateCartDisplay();
}

// Public API for pages to add items to cart and refresh count
window.addToChiboCart = function (item) {
    const cartKey = getCartKey();

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }
    cart.push(item);
    persistCart(cart);
}

function cartQtyChange(index, delta) {
    const cartKey = getCartKey();

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }
    if (!cart[index]) return;

    // Handle different field names and enforce per-item minimum
    const minQty = parseInt(cart[index].minQuantity || 1);
    const currentQty = cart[index].quantity || cart[index].qty || minQty;
    const next = Math.max(minQty, parseInt(currentQty) + delta);

    // Recalculate unit price using stored pricing metadata (volume discounts)
    let unitPrice = parseFloat(cart[index].unitPrice || 0);
    if (cart[index].pricing && Array.isArray(cart[index].pricing.priceTiers)) {
        const tiers = cart[index].pricing.priceTiers;
        let baseUnit = 0;
        tiers.forEach(tier => {
            const minQty = parseInt(tier.min_quantity);
            const maxQty = tier.max_quantity ? parseInt(tier.max_quantity) : 999999;
            if (next >= minQty && next <= maxQty) {
                baseUnit = parseFloat(tier.price_per_unit);
            }
        });
        if (!baseUnit && cart[index].pricing.basePrice) {
            baseUnit = parseFloat(cart[index].pricing.basePrice);
        }
        const variantExtra = parseFloat(cart[index].variantExtra || 0);
        if (baseUnit) {
            unitPrice = baseUnit + variantExtra;
            cart[index].unitPrice = unitPrice;
        }
    }

    // Normalize field names
    cart[index].quantity = next;
    cart[index].qty = next; // Keep both for compatibility
    cart[index].totalPrice = unitPrice * next;
    cart[index].total = cart[index].totalPrice; // Keep both for compatibility

    persistCart(cart);
    updateCartModal();
}

function cartQtyInput(index, value) {
    const cartKey = getCartKey();

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }
    if (!cart[index]) return;
    const minQty = parseInt(cart[index].minQuantity || 1);
    let qty = parseInt(value || String(minQty));
    if (isNaN(qty) || qty < minQty) qty = minQty;

    // Recalculate unit price using stored pricing metadata (volume discounts)
    let unitPrice = parseFloat(cart[index].unitPrice || 0);
    if (cart[index].pricing && Array.isArray(cart[index].pricing.priceTiers)) {
        const tiers = cart[index].pricing.priceTiers;
        let baseUnit = 0;
        tiers.forEach(tier => {
            const minQty = parseInt(tier.min_quantity);
            const maxQty = tier.max_quantity ? parseInt(tier.max_quantity) : 999999;
            if (qty >= minQty && qty <= maxQty) {
                baseUnit = parseFloat(tier.price_per_unit);
            }
        });
        if (!baseUnit && cart[index].pricing.basePrice) {
            baseUnit = parseFloat(cart[index].pricing.basePrice);
        }
        const variantExtra = parseFloat(cart[index].variantExtra || 0);
        if (baseUnit) {
            unitPrice = baseUnit + variantExtra;
            cart[index].unitPrice = unitPrice;
        }
    }

    // Normalize field names
    cart[index].quantity = qty;
    cart[index].qty = qty; // Keep both for compatibility
    cart[index].totalPrice = unitPrice * qty;
    cart[index].total = cart[index].totalPrice; // Keep both for compatibility

    persistCart(cart);
    updateCartModal();
}

function removeFromCart(index) {
    const cartKey = getCartKey();

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }
    if (index >= 0 && index < cart.length) {
        cart.splice(index, 1);
        persistCart(cart);
        updateCartModal();
        showToast('Item removed from cart', 'success');
    }
}

// Global alias for removeFromCart
window.removeCartItem = function (index) {
    removeFromCart(index);
};

function sendOrderToWhatsApp() {
    console.log('=== SEND ORDER TO WHATSAPP CLICKED ===');
    const cartKey = getCartKey();
    console.log('Cart key:', cartKey);

    let cart = [];
    try { cart = JSON.parse(localStorage.getItem(cartKey) || '[]'); } catch (e) { cart = []; }
    console.log('Cart from localStorage:', cart);

    if (cart.length === 0) {
        console.log('Cart is empty, showing error');
        showToast('Your cart is empty', 'error');
        return;
    }

    console.log('Creating order directly and sending to WhatsApp...');

    // Check if VAT receipt is requested (read it BEFORE closing modal)
    let includeVatReceipt = false;
    const vatCheckbox = document.getElementById('modalVatReceipt');
    if (vatCheckbox) {
        includeVatReceipt = vatCheckbox.checked;
        console.log('VAT Receipt checkbox checked:', includeVatReceipt);
    } else {
        console.log('VAT Receipt checkbox not found');
    }

    // Close modal after reading checkbox state
    const modalEl = document.getElementById('cartModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();

    // Create order directly via API
    createOrderFromCart(cart).then((orderData) => {
        console.log('Order created successfully:', orderData);
        console.log('Including VAT Receipt in message:', includeVatReceipt);

        // Generate WhatsApp message with VAT information
        const message = generateWhatsAppMessage(orderData, cart, includeVatReceipt);
        // Use stored saler WhatsApp number or fallback to default
        const whatsappNumber = getWhatsAppNumber();
        const whatsappUrl = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message);

        console.log('Redirecting to WhatsApp:', whatsappUrl);

        // Clear cart after successful order creation
        localStorage.removeItem(cartKey);
        updateCartModal();

        // Redirect to WhatsApp
        window.location.href = whatsappUrl;
    }).catch(error => {
        console.error('Failed to create order:', error);
        showToast('Failed to create order. Please try again.', 'error');
    });
}

function createOrderFromCart(cart) {
    return new Promise((resolve, reject) => {
        console.log('=== CREATE ORDER FROM CART STARTED ===');
        console.log('Cart data:', cart);

        // Convert cart items to the format expected by the server
        const cartData = cart.map(item => {
            const productId = item.product_id || item.id || item.barcode;
            const quantity = item.quantity || item.qty || 1;

            return {
                product_id: productId,
                quantity: quantity,
                variations: item.variations || item.variant || {},
                addons: item.addons || [],
                custom_inputs: item.custom_inputs || []
            };
        });

        console.log('Converted cart data for order creation:', cartData);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        console.log('CSRF Token:', csrfToken);

        // Create order via API
        // Get saler phone using dedicated function
        const salerPhone = getSalerPhoneForOrder();
        console.log('=== ORDER CREATION DEBUG ===');
        console.log('Saler phone for order assignment:', salerPhone);
        console.log('localStorage.getItem(SALER_WHATSAPP_KEY):', localStorage.getItem(SALER_WHATSAPP_KEY));
        console.log('URL saler param:', (new URLSearchParams(window.location.search)).get('saler'));
        console.log('actualDefaultWhatsapp:', actualDefaultWhatsapp);
        console.log('===========================');

        // Detect if this is a B2B/wholesale store
        // Check pathname, hostname, or referrer for B2B indicators
        const pathname = window.location.pathname;
        const hostname = window.location.hostname;
        const isWholesale = pathname.includes('/b2b/')
            || pathname.startsWith('/b2b')
            || pathname === '/b2b'
            || hostname.includes('b2b.');

        // Include saler phone in both header and body (body as backup)
        const requestBody = {
            cart: cartData,
            saler_phone: salerPhone && salerPhone.length > 0 ? salerPhone : null,
            is_wholesale: isWholesale
        };

        console.log('Store type detection:', {
            pathname: window.location.pathname,
            isWholesale: isWholesale
        });

        fetch('/api/orders/create-from-cart-data', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                // Pass saler phone from stored value or URL param so backend can assign order
                'X-Saler-Phone': salerPhone && salerPhone.length > 0 ? salerPhone : ''
            },
            body: JSON.stringify(requestBody)
        })
            .then(response => {
                console.log('Order creation response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Order creation response data:', data);
                if (data.success) {
                    resolve(data);
                } else {
                    reject(new Error(data.message || 'Failed to create order'));
                }
            })
            .catch(error => {
                console.error('Order creation fetch error:', error);
                reject(error);
            });
    });
}

function syncCartToServer(cart) {
    return new Promise((resolve, reject) => {
        console.log('=== SYNC CART TO SERVER STARTED ===');
        console.log('Original cart:', cart);

        // Convert cart items to the format expected by the server
        const cartData = cart.map(item => {
            // Handle different field name formats
            const productId = item.product_id || item.id || item.barcode;
            const quantity = item.quantity || item.qty || 1;

            console.log('Processing item:', item, '-> productId:', productId, 'quantity:', quantity);

            return {
                product_id: productId,
                quantity: quantity,
                variations: item.variations || item.variant || {},
                addons: item.addons || [],
                custom_inputs: item.custom_inputs || []
            };
        });

        console.log('Converted cart data for server:', cartData);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        console.log('CSRF Token:', csrfToken);

        // Send cart data to server
        fetch('/cart/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ cart: cartData })
        })
            .then(response => {
                console.log('Cart sync response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Cart sync response data:', data);
                if (data.success) {
                    resolve(data);
                } else {
                    reject(new Error(data.message || 'Failed to sync cart'));
                }
            })
            .catch(error => {
                console.error('Cart sync fetch error:', error);
                reject(error);
            });
    });
}

function generateWhatsAppMessage(orderData, cart, includeVatReceipt = false) {
    console.log('generateWhatsAppMessage called with includeVatReceipt:', includeVatReceipt);
    const isWholesale = window.location.hostname.includes('b2b') || window.location.pathname.includes('/b2b');
    const channel = isWholesale ? 'B2B' : 'RETAIL';

    let message = `🛒 *NEW ORDER REQUEST* - ${channel} STORE\n\n`;
    message += `📋 *Order Code:* ${orderData.order_code}\n`;
    message += `📅 *Date:* ${new Date().toLocaleDateString()}\n\n`;

    message += `📦 *ORDER ITEMS:*\n`;
    let totalAmount = 0;

    cart.forEach((item, index) => {
        const price = item.price || item.unitPrice || 0;
        const quantity = item.quantity || item.qty || 1;
        const subtotal = price * quantity;
        totalAmount += subtotal;

        message += `${index + 1}. *${item.name}*\n`;

        // Add variants if any
        const variantData = item.variant || item.variants || item.variations || {};
        if (variantData && typeof variantData === 'object') {
            let variantsText = '';
            if (Array.isArray(variantData)) {
                // Handle array format - filter out null/empty values
                variantsText = variantData
                    .filter(v => v && v.name && v.option_value && v.option_value !== 'N/A' && v.option_value !== 'null' && v.option_value !== 'undefined')
                    .map(v => `${v.name}: ${v.option_value}`)
                    .join(', ');
            } else {
                // Handle object format - filter out null/empty values
                variantsText = Object.entries(variantData)
                    .filter(([k, v]) => {
                        // Filter out null, undefined, empty strings, 'N/A', and invalid objects
                        if (!v || v === 'N/A' || v === 'null' || v === 'undefined' || v === '[object Object]') {
                            return false;
                        }
                        // For objects, check if they have valid name/value
                        if (typeof v === 'object' && v !== null) {
                            return !!(v.name || v.value || v.label);
                        }
                        return true;
                    })
                    .map(([k, v]) => {
                        // Handle nested objects
                        if (typeof v === 'object' && v !== null) {
                            if (v.name) return `${k}: ${v.name}`;
                            if (v.value) return `${k}: ${v.value}`;
                            if (v.label) return `${k}: ${v.label}`;
                            return null; // Skip invalid objects
                        }
                        return `${k}: ${v}`;
                    })
                    .filter(v => v !== null && v !== '') // Remove null/empty entries
                    .join(', ');
            }
            if (variantsText) {
                message += `   • Variants: ${variantsText}\n`;
            }
        }

        message += `   • Quantity: ${quantity} pieces\n`;
        message += `   • Unit Price: TZS ${price.toLocaleString()}\n`;
        message += `   • Subtotal: TZS ${subtotal.toLocaleString()}\n\n`;
    });

    // Add VAT information if requested
    message += `💰 *ORDER SUMMARY:*\n`;
    message += `   • Subtotal: TZS ${totalAmount.toLocaleString()}\n`;

    console.log('Processing VAT - includeVatReceipt:', includeVatReceipt, 'totalAmount:', totalAmount);
    if (includeVatReceipt) {
        const vatAmount = Math.round(totalAmount * 0.18);
        const totalWithVAT = totalAmount + vatAmount;
        console.log('Adding VAT to message - VAT Amount:', vatAmount, 'Total with VAT:', totalWithVAT);
        message += `   • VAT (18%): TZS ${vatAmount.toLocaleString()}\n`;
        message += `   • *Total (with VAT): TZS ${totalWithVAT.toLocaleString()}*\n\n`;
        message += `🧾 *VAT Receipt: Yes (18% included)*\n`;
    } else {
        console.log('VAT not included in message');
        message += `   • *Total: TZS ${totalAmount.toLocaleString()}*\n\n`;
    }

    message += `📱 *Order placed via WhatsApp from ${channel} store*\n`;
    message += `🌐 *Website:* ${window.location.hostname}\n\n`;
    message += `Please confirm this order and provide delivery details.`;

    return message;
}

function generateOrderNumber() {
    const now = new Date();
    const dateStr = now.getFullYear().toString() +
        (now.getMonth() + 1).toString().padStart(2, '0') +
        now.getDate().toString().padStart(2, '0');
    const randomNum = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
    return `CHB-${dateStr}-${randomNum}`;
}

function extractVariantValue(value) {
    if (!value) return '';

    if (typeof value === 'string') {
        return value;
    } else if (typeof value === 'object' && value !== null) {
        // Handle object values - extract name or value property
        if (value.name) {
            return value.name;
        } else if (value.value) {
            return value.value;
        } else if (value.label) {
            return value.label;
        } else if (value.text) {
            return value.text;
        } else {
            // If it's an object with other properties, try to extract meaningful data
            const keys = Object.keys(value);
            if (keys.length > 0) {
                // Return the first meaningful property
                for (let key of keys) {
                    if (key !== 'price' && key !== 'id' && value[key]) {
                        return value[key];
                    }
                }
            }
            return JSON.stringify(value);
        }
    } else {
        return String(value);
    }
}

function saveOrderToSystem(cart, orderNumber) {
    // Filter valid cart items
    const validItems = cart.filter(item => item && item.name && item.id && item.quantity && item.unitPrice);

    if (validItems.length === 0) {
        console.log('No valid items in cart');
        return;
    }

    // Calculate total amount
    const totalAmount = calculateCartTotal(validItems);
    console.log('Calculated total amount:', totalAmount);

    // Prepare order data
    const orderData = {
        order_code: orderNumber,
        total_amount: totalAmount,
        payment_status: 'pending',
        approval_status: 'requested',
        notes: `Order placed via WhatsApp from ${window.location.hostname.includes('b2b') ? 'WHOLESALE' : 'RETAIL'} store`,
        items: validItems.map((item, index) => {
            console.log(`Processing item ${index}:`, item);

            // Ensure all required fields are present and valid
            const quantity = parseInt(item.quantity) || 1;
            const unitPrice = parseFloat(item.unitPrice) || 0;
            const subtotal = parseFloat(item.totalPrice) || (quantity * unitPrice);

            const processedItem = {
                product_name: item.name || 'Unknown Product',
                product_barcode: item.id || 'UNKNOWN',
                quantity: quantity,
                unit_price: unitPrice,
                subtotal: subtotal,
                variants: item.variant || {},
                channel: item.customerType || 'retail'
            };

            console.log(`Processed item ${index}:`, processedItem);
            return processedItem;
        }),
        customer_info: {
            channel: window.location.hostname.includes('b2b') ? 'wholesale' : 'retail',
            source: 'whatsapp_cart'
        }
    };

    // Validate order data before sending
    if (!orderData.total_amount || orderData.total_amount <= 0) {
        console.log('Invalid total amount:', orderData.total_amount);
        showToast('Invalid order total. Please try again.', 'error');
        return;
    }

    if (!orderData.items || orderData.items.length === 0) {
        console.log('No items in order data');
        showToast('No items in order. Please try again.', 'error');
        return;
    }

    // Validate each item
    for (let i = 0; i < orderData.items.length; i++) {
        const item = orderData.items[i];
        if (!item.quantity || item.quantity <= 0) {
            console.log(`Invalid quantity for item ${i}:`, item);
            showToast(`Invalid quantity for item ${i + 1}. Please try again.`, 'error');
            return;
        }
        if (!item.unit_price || item.unit_price <= 0) {
            console.log(`Invalid unit price for item ${i}:`, item);
            showToast(`Invalid price for item ${i + 1}. Please try again.`, 'error');
            return;
        }
        if (!item.subtotal || item.subtotal <= 0) {
            console.log(`Invalid subtotal for item ${i}:`, item);
            showToast(`Invalid subtotal for item ${i + 1}. Please try again.`, 'error');
            return;
        }
    }

    // Debug: Log the data being sent
    console.log('Sending order data:', orderData);
    console.log('Cart items:', validItems);

    // Send to backend (non-blocking)
    fetch('/api/orders/create-from-cart', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify(orderData)
    })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                console.log('Order saved successfully:', data.order_id);
                // Clear cart after successful order
                if (data.clear_cart) {
                    localStorage.removeItem(getCartKey());
                    updateCartDisplay();
                    console.log('Cart cleared after order creation');
                }
            } else {
                console.log('Order save failed:', data.message);
                if (data.errors) {
                    console.log('Validation errors:', data.errors);
                }
            }
        })
        .catch(error => {
            console.log('Order save error:', error);
            // Don't show error to user - WhatsApp still works
        });
}

function calculateCartTotal(cart) {
    return cart.reduce((total, item) => {
        return total + (item.totalPrice || (item.quantity * item.unitPrice));
    }, 0);
}

function generateOrderMessage(cart, orderNumber) {
    // Filter valid cart items
    const validItems = cart.filter(item => item && item.name && item.id && item.quantity && item.unitPrice);
    const currentDate = new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    let message = `🛍️ *NEW ORDER REQUEST* 🛍️\n\n`;
    message += `🔢 *Order Number:* ${orderNumber}\n`;
    message += `📅 *Date:* ${currentDate}\n`;
    message += `🏪 *From:* CHIBO BRAND ${window.location.hostname.includes('b2b') ? 'WHOLESALE' : 'RETAIL'} STORE\n\n`;
    message += `📋 *ORDER DETAILS:*\n`;
    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n`;

    let totalAmount = 0;
    let totalItems = 0;

    validItems.forEach((item, index) => {
        const quantity = parseInt(item.quantity || 1);
        const unitPrice = parseFloat(item.unitPrice || 0);
        const subtotal = item.totalPrice || (quantity * unitPrice);
        totalAmount += subtotal;
        totalItems += quantity;

        message += `📦 *Item ${index + 1}:* ${item.name}\n`;
        message += `   • Barcode: ${item.id || 'N/A'}\n`;
        message += `   • Quantity: ${quantity} pieces\n`;
        message += `   • Unit Price: ${unitPrice.toLocaleString()} TZS\n`;
        message += `   • Subtotal: ${subtotal.toLocaleString()} TZS\n`;

        // Add variants if any
        const variantData = item.variant || item.variants || {};
        if (variantData && Object.keys(variantData).length > 0) {
            message += `   • Variants:\n`;
            Object.entries(variantData).forEach(([key, value]) => {
                if (value) {
                    const displayValue = extractVariantValue(value);
                    if (displayValue) {
                        message += `     - ${key}: ${displayValue}\n`;
                    }
                }
            });
        }

        // Add channel info
        if (item.customerType) {
            message += `   • Channel: ${item.customerType === 'wholesale' ? 'Wholesale' : 'Retail'}\n`;
        }

        message += `\n`;
    });

    message += `━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n`;
    message += `📊 *ORDER SUMMARY:*\n`;
    message += `   • Order Number: ${orderNumber}\n`;
    message += `   • Total Items: ${totalItems} pieces\n`;

    // Check if VAT is enabled
    const vatCheckbox = document.getElementById('modalVatReceipt');
    if (vatCheckbox && vatCheckbox.checked) {
        const vatAmount = Math.round(totalAmount * 0.18);
        const totalWithVAT = totalAmount + vatAmount;
        message += `   • Subtotal: ${totalAmount.toLocaleString()} TZS\n`;
        message += `   • VAT (18%): ${vatAmount.toLocaleString()} TZS\n`;
        message += `   • Total Amount: *${totalWithVAT.toLocaleString()} TZS*\n`;
        message += `   • VAT Receipt: Yes (18% included)\n`;
    } else {
        message += `   • Total Amount: *${totalAmount.toLocaleString()} TZS*\n`;
    }

    message += `\n💬 *Please confirm this order and provide delivery details.*\n\n`;
    message += `Thank you for choosing CHIBO BRAND! 🙏`;

    return message;
}

// iOS Safari navbar fix
function isIOS() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
}

function fixIOSNavbar() {
    if (isIOS()) {
        const navbar = document.querySelector('.navbar');
        const body = document.body;

        if (navbar) {
            // Force fixed positioning on iOS
            navbar.style.position = 'fixed';
            navbar.style.top = '0';
            navbar.style.left = '0';
            navbar.style.right = '0';
            navbar.style.width = '100%';
            navbar.style.zIndex = '1030';

            // Add padding to body to account for fixed navbar
            const navbarHeight = navbar.offsetHeight;
            body.style.paddingTop = navbarHeight + 'px';

            // Handle orientation change
            window.addEventListener('orientationchange', function () {
                setTimeout(() => {
                    const newHeight = navbar.offsetHeight;
                    body.style.paddingTop = newHeight + 'px';
                }, 100);
            });

            // Handle scroll events to ensure navbar stays visible
            let lastScrollTop = 0;
            window.addEventListener('scroll', function () {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    // Scrolling down
                    navbar.style.transform = 'translateY(0)';
                } else {
                    // Scrolling up
                    navbar.style.transform = 'translateY(0)';
                }

                lastScrollTop = scrollTop;
            });
        }
    }
}

// Accelerated Preloader Script
function hidePreloader() {
    const preloader = document.getElementById("preloader");
    if (!preloader || preloader.style.display === "none") return;

    preloader.classList.add("fade-out");
    setTimeout(() => {
        preloader.style.display = "none";
    }, 200);
}

if (document.readyState === "complete" || document.readyState === "interactive") {
    hidePreloader();
} else {
    window.addEventListener("DOMContentLoaded", hidePreloader);
}
// Fallback to ensure preloader is hidden even if DOMContentLoaded is delayed
setTimeout(hidePreloader, 1500);

// Show preloader on page navigation
document.addEventListener("DOMContentLoaded", function () {
    // Fix iOS navbar immediately on DOM ready
    fixIOSNavbar();

    // Only show preloader on form submissions (excluding logout)
    document.querySelectorAll('form:not([action*="logout"])').forEach(form => {
        form.addEventListener('submit', function () {
            const preloader = document.getElementById("preloader");
            if (preloader) {
                preloader.style.display = "flex";
                preloader.classList.remove("fade-out");
            }
        });
    });
});

// Instant public logout - no processing states
document.addEventListener("DOMContentLoaded", function () {
    const publicLogoutForm = document.getElementById('public-logout-form');
    const publicLogoutBtn = document.querySelector('.public-logout-btn');

    if (publicLogoutForm && publicLogoutBtn) {
        publicLogoutForm.addEventListener('submit', function (e) {
            // Simple visual feedback without processing states
            publicLogoutBtn.style.opacity = '0.7';
            publicLogoutBtn.disabled = true;

            // Submit immediately - no delays or processing indicators
        });
    }
});
// Global Search (App-bar) - Live Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchWrappers = document.querySelectorAll('.search-wrapper');
    
    searchWrappers.forEach(wrapper => {
        const input = wrapper.querySelector('.search-input');
        const results = wrapper.querySelector('.search-results-dropdown');
        
        if (!input || !results) return;
        
        let searchController = null;
        let searchTimeout = null;
        
        // Determine channel and endpoint based on current URL
        const currentPath = window.location.pathname;
        let searchEndpoint;
        if (currentPath.startsWith('/b2b/') || window.location.hostname.includes('b2b.')) {
            searchEndpoint = '/b2b/products/autocomplete';
        } else {
            searchEndpoint = '/products/autocomplete';
        }
        
        function renderResults(items) {
            if (!items || items.length === 0) {
                results.innerHTML = `
                    <div class="search-no-results p-3 text-center text-muted">
                        <i class="fas fa-search mb-2 d-block"></i>
                        <div>No products found</div>
                    </div>
                `;
                results.classList.add('show');
                return;
            }
            
            results.innerHTML = items.map(item => `
                <a href="${item.url}" class="search-result-item d-flex align-items-center p-2 text-decoration-none text-dark border-bottom">
                    <img src="${item.image}" alt="${item.name}" class="search-result-img rounded me-3" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='/images/default.webp'">
                    <div class="search-result-info flex-grow-1">
                        <div class="search-result-name fw-bold" style="font-size: 0.85rem;">${item.name}</div>
                        <div class="search-result-meta text-muted" style="font-size: 0.75rem;">${item.barcode} • ${item.category || 'Uncategorized'}</div>
                    </div>
                    <div class="search-result-price fw-bold text-danger" style="font-size: 0.85rem;">TSH ${Number(item.price || 0).toLocaleString()}</div>
                </a>
            `).join('');
            
            results.classList.add('show');
        }
        
        async function performSearch(query) {
            if (searchController) {
                searchController.abort();
            }
            
            searchController = new AbortController();
            
            try {
                const url = `${searchEndpoint}?q=${encodeURIComponent(query)}`;
                const response = await fetch(url, {
                    signal: searchController.signal
                });
                
                const data = await response.json();
                renderResults(data.items || []);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    console.error('Search error:', error);
                }
            }
        }
        
        input.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            if (query.length === 0) {
                results.classList.remove('show');
                results.innerHTML = '';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 100);
        });
        
        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !results.contains(e.target)) {
                results.classList.remove('show');
            }
        });
        
        input.addEventListener('focus', function() {
            if (this.value.trim().length > 0 && results.innerHTML) {
                results.classList.add('show');
            }
        });
    });
});
