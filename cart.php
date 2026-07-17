<?php
require_once __DIR__ . '/includes/header.php';
requireLogin();

$pageTitle = 'سبد خرید';
$cartItems = getCartItems($_SESSION['user_id']);
$subtotal = 0;
$shipping = 0;
$tax = 0;
$total = 0;

foreach($cartItems as $item) {
    $itemPrice = getDiscountedPrice($item['price'], $item['discount_percent']);
    $itemSubtotal = $itemPrice * $item['quantity'];
    $subtotal += $itemSubtotal;
}

$freeShippingMin = intval(getSetting('free_shipping_min', 500000));
$shipping = $subtotal >= $freeShippingMin ? 0 : 50000;
$taxRate = intval(getSetting('tax_rate', 9));
$tax = floor($subtotal * $taxRate / 100);
$total = $subtotal + $shipping + $tax;
?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <span>سبد خرید</span>
    </div>

    <h1 class="section-title" style="margin-bottom: 24px;">🛒 سبد خرید</h1>

    <?php if(!empty($cartItems)): ?>
    <div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; align-items: start;">
        <!-- Cart Items -->
        <div class="cart-page">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>محصول</th>
                        <th>قیمت</th>
                        <th>تعداد</th>
                        <th>جمع</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cartItems as $item): 
                        $itemPrice = getDiscountedPrice($item['price'], $item['discount_percent']);
                        $itemSubtotal = $itemPrice * $item['quantity'];
                    ?>
                    <tr class="cart-item-row" data-price="<?php echo $itemPrice; ?>">
                        <td>
                            <div class="cart-product">
                                <div class="cart-product-img">
                                    <?php if($item['image_main']): ?>
                                        <img src="<?php echo $item['image_main']; ?>" alt="<?php echo $item['name']; ?>">
                                    <?php else: ?>
                                        📦
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div class="cart-product-name">
                                        <a href="/product/<?php echo $item['slug']; ?>"><?php echo $item['name']; ?></a>
                                    </div>
                                    <?php if($item['discount_percent'] > 0): ?>
                                    <span style="font-size: 11px; color: var(--danger);">٪<?php echo $item['discount_percent']; ?> تخفیف</span>
                                    <?php endif; ?>
                                    <?php if(!$item['is_active'] || $item['stock'] <= 0): ?>
                                    <span style="color: var(--danger); font-size: 12px;">❌ ناموجود</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span style="font-weight: 700;"><?php echo formatPrice($itemPrice); ?></span>
                                <?php if($item['discount_percent'] > 0): ?>
                                <br><span style="text-decoration: line-through; color: #999; font-size: 11px;"><?php echo formatPrice($item['price']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="quantity-selector" style="width: 120px;">
                                <button class="minus" onclick="updateCartQty(<?php echo $item['product_id']; ?>, parseInt(document.getElementById('qty-<?php echo $item['product_id']; ?>').value) - 1)">−</button>
                                <input type="number" id="qty-<?php echo $item['product_id']; ?>" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1" max="<?php echo $item['stock']; ?>" 
                                       onchange="updateCartQty(<?php echo $item['product_id']; ?>, this.value)"
                                       class="quantity-input">
                                <button class="plus" onclick="updateCartQty(<?php echo $item['product_id']; ?>, parseInt(document.getElementById('qty-<?php echo $item['product_id']; ?>').value) + 1)">+</button>
                            </div>
                        </td>
                        <td>
                            <strong id="subtotal-<?php echo $item['product_id']; ?>"><?php echo formatPrice($itemSubtotal); ?></strong>
                        </td>
                        <td>
                            <button class="cart-remove" data-product-id="<?php echo $item['product_id']; ?>" title="حذف">🗑️</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Cart Summary -->
        <div>
            <div class="cart-summary">
                <h3>📋 خلاصه سفارش</h3>
                
                <div class="summary-row">
                    <span>جمع سبد خرید</span>
                    <span id="subtotalAmount"><?php echo formatPrice($subtotal); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>هزینه ارسال</span>
                    <span id="shippingAmount" style="<?php echo $shipping === 0 ? 'color: var(--success);' : ''; ?>">
                        <?php echo $shipping === 0 ? 'رایگان 🎉' : formatPrice($shipping); ?>
                    </span>
                </div>
                
                <?php if($shipping > 0): ?>
                <div style="font-size: 11px; color: var(--gray-500); margin-bottom: 8px;">
                    ارسال رایگان برای خریدهای بالای <?php echo formatPrice($freeShippingMin); ?>
                    <?php if($subtotal < $freeShippingMin): ?>
                    <br>فقط <?php echo formatPrice($freeShippingMin - $subtotal); ?> تا ارسال رایگان
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="summary-row">
                    <span>مالیات (<?php echo $taxRate; ?>٪)</span>
                    <span id="taxAmount"><?php echo formatPrice($tax); ?></span>
                </div>

                <!-- Coupon -->
                <div style="margin: 12px 0; padding: 12px 0; border-top: 1px solid var(--gray-100); border-bottom: 1px solid var(--gray-100);">
                    <div class="coupon-input">
                        <input type="text" id="couponCode" placeholder="کد تخفیف دارید؟" style="flex: 1; padding: 8px 12px; border: 2px solid var(--gray-200); border-radius: var(--radius); font-family: var(--font);">
                        <button class="btn btn-outline btn-sm" id="applyCoupon">اعمال</button>
                    </div>
                    <div class="summary-row" id="couponRow" style="display: none;">
                        <span>تخفیف کوپن</span>
                        <span id="couponDiscount" style="color: var(--success);">0</span>
                    </div>
                </div>
                
                <div class="summary-row total">
                    <span>مبلغ قابل پرداخت</span>
                    <span id="totalAmount"><?php echo formatPrice($total); ?></span>
                </div>
                
                <a href="/checkout" class="btn btn-primary btn-block btn-lg" style="margin-top: 16px;">
                    🛍️ ادامه فرآیند خرید
                </a>
                
                <a href="/products" class="btn btn-outline btn-block" style="margin-top: 8px;">
                    📦 ادامه خرید
                </a>
            </div>

            <!-- Trust Info -->
            <div style="background: var(--white); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--shadow-sm); margin-top: 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <span>🔒</span>
                    <span style="font-size: 13px; font-weight: 600;">پرداخت امن</span>
                </div>
                <p style="font-size: 11px; color: var(--gray-500);">اطلاعات شما محفوظ بوده و پرداخت از طریق درگاه امن انجام می‌شود.</p>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- Empty Cart -->
    <div class="empty-state">
        <div class="empty-state-icon">🛒</div>
        <h3>سبد خرید شما خالی است</h3>
        <p>محصولات جذاب ما رو ببینید و به سبد خرید اضافه کنید.</p>
        <a href="/products" class="btn btn-primary">🏬 رفتن به فروشگاه</a>
    </div>
    <?php endif; ?>
</div>

<script>
function updateCartQty(productId, quantity) {
    quantity = parseInt(quantity);
    if (isNaN(quantity) || quantity < 1) quantity = 1;
    
    const input = document.getElementById('qty-' + productId);
    const max = parseInt(input.getAttribute('max'));
    if (quantity > max) {
        showToast('❌ موجودی کافی نیست', 'error');
        input.value = max;
        quantity = max;
    }
    
    input.value = quantity;
    
    fetch('/ajax/update-cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId + '&quantity=' + quantity
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.count);
            location.reload();
        } else {
            showToast('❌ ' + (data.message || 'خطا'), 'error');
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>