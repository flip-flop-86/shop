// ==========================================
// جاوااسکریپت اصلی فروشگاه
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    initQuantitySelectors();
    initTabs();
    initGallery();
    initAddToCart();
    initWishlist();
    initRemoveFromCart();
    initCouponApply();
});

// ==========================================
// Quantity Selector
// ==========================================
function initQuantitySelectors() {
    document.querySelectorAll('.quantity-selector').forEach(selector => {
        const input = selector.querySelector('input');
        const minusBtn = selector.querySelector('.minus');
        const plusBtn = selector.querySelector('.plus');
        
        if (!input || !minusBtn || !plusBtn) return;
        
        minusBtn.addEventListener('click', () => {
            let val = parseInt(input.value) || 1;
            if (val > 1) {
                input.value = val - 1;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        plusBtn.addEventListener('click', () => {
            let val = parseInt(input.value) || 1;
            const max = parseInt(input.getAttribute('max')) || 99;
            if (val < max) {
                input.value = val + 1;
                input.dispatchEvent(new Event('change'));
            }
        });
        
        input.addEventListener('change', () => {
            let val = parseInt(input.value) || 1;
            const min = parseInt(input.getAttribute('min')) || 1;
            const max = parseInt(input.getAttribute('max')) || 99;
            if (val < min) input.value = min;
            if (val > max) input.value = max;
        });
    });
}

// ==========================================
// Tabs
// ==========================================
function initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabGroup = this.closest('.product-tabs');
            if (!tabGroup) return;
            
            // Remove active from all buttons
            tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            // Add active to clicked button
            this.classList.add('active');
            
            // Hide all tab contents
            const targetId = this.getAttribute('data-tab');
            tabGroup.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Show target content
            const target = tabGroup.querySelector('#' + targetId);
            if (target) {
                target.classList.add('active');
            }
        });
    });
}

// ==========================================
// Image Gallery
// ==========================================
function initGallery() {
    const thumbs = document.querySelectorAll('.product-gallery-thumb');
    const main = document.querySelector('.product-gallery-main');
    
    if (!thumbs.length || !main) return;
    
    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const imgSrc = this.getAttribute('data-image');
            const icon = this.getAttribute('data-icon');
            
            if (imgSrc) {
                main.innerHTML = `<img src="${imgSrc}" alt="Product">`;
            } else if (icon) {
                main.innerHTML = icon;
            }
        });
    });
}

// ==========================================
// Add to Cart
// ==========================================
function initAddToCart() {
    document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            const quantity = document.querySelector(`#quantity-${productId}`)?.value || 1;
            const btnText = this.innerHTML;
            
            this.disabled = true;
            this.innerHTML = '⏳ ...';
            
            try {
                const response = await fetch('/ajax/add-to-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=${quantity}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateCartCount(data.count);
                    showToast('✅ محصول به سبد خرید اضافه شد', 'success');
                    this.innerHTML = '✓ اضافه شد';
                    setTimeout(() => {
                        this.innerHTML = btnText;
                        this.disabled = false;
                    }, 2000);
                } else {
                    showToast('❌ ' + (data.message || 'خطا در افزودن به سبد'), 'error');
                    this.innerHTML = btnText;
                    this.disabled = false;
                }
            } catch (error) {
                showToast('❌ خطا در ارتباط با سرور', 'error');
                this.innerHTML = btnText;
                this.disabled = false;
            }
        });
    });
}

// ==========================================
// Wishlist Toggle
// ==========================================
function initWishlist() {
    document.querySelectorAll('.wishlist-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const productId = this.getAttribute('data-product-id');
            
            try {
                const response = await fetch('/ajax/toggle-wishlist.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.action === 'added') {
                        this.classList.add('active');
                        this.innerHTML = '❤️';
                        showToast('✅ به علاقه‌مندی‌ها اضافه شد', 'success');
                    } else {
                        this.classList.remove('active');
                        this.innerHTML = '🤍';
                        showToast('از علاقه‌مندی‌ها حذف شد', 'info');
                    }
                    updateWishlistCount(data.count);
                }
            } catch (error) {
                showToast('❌ خطا در ارتباط با سرور', 'error');
            }
        });
    });
}

// ==========================================
// Remove from Cart
// ==========================================
function initRemoveFromCart() {
    document.querySelectorAll('.cart-remove').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('از حذف این محصول مطمئن هستید؟')) return;
            
            const productId = this.getAttribute('data-product-id');
            const row = this.closest('tr');
            
            try {
                const response = await fetch('/ajax/remove-from-cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(50px)';
                    row.style.transition = 'all 0.3s';
                    setTimeout(() => {
                        row.remove();
                        updateCartCount(data.count);
                        if (data.count === 0) {
                            location.reload();
                        } else {
                            updateCartTotals();
                        }
                    }, 300);
                    showToast('🗑️ محصول از سبد خرید حذف شد', 'info');
                }
            } catch (error) {
                showToast('❌ خطا در ارتباط با سرور', 'error');
            }
        });
    });
}

// ==========================================
// Quantity Update in Cart
// ==========================================
function updateCartItemQuantity(productId, quantity) {
    fetch('/ajax/update-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}&quantity=${quantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.count);
            updateCartTotals();
            // Update subtotal for this row
            const subtotalEl = document.querySelector(`#subtotal-${productId}`);
            if (subtotalEl && data.itemSubtotal) {
                subtotalEl.textContent = formatPrice(data.itemSubtotal);
            }
        } else {
            showToast('❌ ' + (data.message || 'خطا در بروزرسانی'), 'error');
        }
    })
    .catch(() => {
        showToast('❌ خطا در ارتباط با سرور', 'error');
    });
}

// ==========================================
// Coupon
// ==========================================
function initCouponApply() {
    const applyBtn = document.getElementById('applyCoupon');
    if (!applyBtn) return;
    
    applyBtn.addEventListener('click', async function() {
        const codeInput = document.getElementById('couponCode');
        const code = codeInput.value.trim();
        
        if (!code) {
            showToast('❌ کد تخفیف را وارد کنید', 'error');
            return;
        }
        
        this.disabled = true;
        this.textContent = '⏳ ...';
        
        try {
            const response = await fetch('/ajax/apply-coupon.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `code=${code}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                document.getElementById('couponDiscount').textContent = formatPrice(data.discount);
                document.getElementById('couponRow').style.display = 'flex';
                showToast('✅ کد تخفیف اعمال شد', 'success');
                updateCartTotals();
            } else {
                showToast('❌ ' + (data.message || 'کد تخفیف نامعتبر است'), 'error');
            }
        } catch (error) {
            showToast('❌ خطا در ارتباط با سرور', 'error');
        } finally {
            this.disabled = false;
            this.textContent = 'اعمال';
        }
    });
}

// ==========================================
// Update Cart Totals
// ==========================================
function updateCartTotals() {
    const rows = document.querySelectorAll('.cart-item-row');
    let subtotal = 0;
    
    rows.forEach(row => {
        const price = parseInt(row.getAttribute('data-price')) || 0;
        const quantity = parseInt(row.querySelector('.quantity-input')?.value) || 0;
        subtotal += price * quantity;
    });
    
    const shipping = subtotal >= <?php echo getSetting('free_shipping_min', 500000); ?> ? 0 : 50000;
    const tax = Math.floor(subtotal * <?php echo getSetting('tax_rate', 9); ?> / 100);
    const couponDiscount = parseInt(document.getElementById('couponDiscount')?.textContent?.replace(/[^0-9]/g, '')) || 0;
    const total = subtotal + shipping + tax - couponDiscount;
    
    document.getElementById('subtotalAmount').textContent = formatPrice(subtotal);
    document.getElementById('shippingAmount').textContent = shipping === 0 ? 'رایگان' : formatPrice(shipping);
    document.getElementById('taxAmount').textContent = formatPrice(tax);
    document.getElementById('totalAmount').textContent = formatPrice(Math.max(0, total));
}

// ==========================================
// Helper Functions
// ==========================================
function updateCartCount(count) {
    document.querySelectorAll('.cart-count').forEach(el => {
        el.textContent = count;
    });
}

function updateWishlistCount(count) {
    document.querySelectorAll('.wishlist-count').forEach(el => {
        el.textContent = count;
    });
}

function formatPrice(price) {
    return new Intl.NumberFormat('fa-IR').format(price) + ' تومان';
}

function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 2500);
}