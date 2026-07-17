<?php
require_once __DIR__ . '/includes/header.php';
requireLogin();

$pageTitle = 'تسویه حساب';
$cartItems = getCartItems($_SESSION['user_id']);

// اگر سبد خالیه، برگرد به سبد خرید
if(empty($cartItems)) {
    header('Location: /cart');
    exit;
}

$subtotal = 0;
foreach($cartItems as $item) {
    $itemPrice = getDiscountedPrice($item['price'], $item['discount_percent']);
    $subtotal += $itemPrice * $item['quantity'];
}

$freeShippingMin = intval(getSetting('free_shipping_min', 500000));
$shipping = $subtotal >= $freeShippingMin ? 0 : 50000;
$taxRate = intval(getSetting('tax_rate', 9));
$tax = floor($subtotal * $taxRate / 100);
$total = $subtotal + $shipping + $tax;

$userAddresses = getUserAddresses($_SESSION['user_id']);
?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <a href="/cart">سبد خرید</a>
        <span>/</span>
        <span>تسویه حساب</span>
    </div>

    <h1 class="section-title" style="margin-bottom: 24px;">🛍️ تکمیل سفارش</h1>

    <form id="checkoutForm" onsubmit="submitOrder(event)">
        <div class="checkout-page">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <!-- Receiver Info -->
                <h3>👤 اطلاعات گیرنده</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">نام و نام خانوادگی</label>
                        <input type="text" name="fullname" required value="<?php echo $_SESSION['user_fullname'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">شماره تماس</label>
                        <input type="tel" name="phone" required value="<?php echo $_SESSION['user_phone'] ?? ''; ?>" pattern="09[0-9]{9}" placeholder="۰۹۱۲۳۴۵۶۷۸۹">
                    </div>
                </div>

                <!-- Address Selection -->
                <h3 style="margin-top: 24px;">📍 آدرس تحویل</h3>
                
                <?php if(!empty($userAddresses)): ?>
                <div style="margin-bottom: 16px;">
                    <?php foreach($userAddresses as $addr): ?>
                    <div class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>" 
                         onclick="selectAddress(this, <?php echo $addr['id']; ?>)"
                         id="address-<?php echo $addr['id']; ?>">
                        <input type="radio" name="address_id" value="<?php echo $addr['id']; ?>" 
                               <?php echo $addr['is_default'] ? 'checked' : ''; ?> 
                               style="position: absolute; opacity: 0;">
                        <div class="address-title"><?php echo $addr['title']; ?></div>
                        <div class="address-detail">
                            <?php echo $addr['province'] . ' - ' . $addr['city']; ?><br>
                            <?php echo $addr['address']; ?><br>
                            کد پستی: <?php echo $addr['postal_code']; ?><br>
                            گیرنده: <?php echo $addr['receiver_name']; ?> | <?php echo $addr['receiver_phone']; ?>
                        </div>
                        <?php if($addr['is_default']): ?>
                        <span style="position: absolute; top: 12px; left: 12px; background: var(--primary); color: white; padding: 2px 10px; border-radius: var(--radius-full); font-size: 10px;">پیش‌فرض</span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- New Address -->
                <div style="margin-bottom: 16px;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleNewAddress()" id="newAddressBtn">
                        ➕ افزودن آدرس جدید
                    </button>
                </div>

                <div id="newAddressForm" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>عنوان آدرس</label>
                            <input type="text" name="new_address_title" placeholder="مثلاً: منزل">
                        </div>
                        <div class="form-group">
                            <label class="required">استان</label>
                            <input type="text" name="new_province" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">شهر</label>
                            <input type="text" name="new_city" required>
                        </div>
                        <div class="form-group">
                            <label>کد پستی</label>
                            <input type="text" name="new_postal_code" pattern="[0-9]{10}" placeholder="۱۰ رقمی">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="required">آدرس کامل</label>
                        <textarea name="new_address" required rows="2" placeholder="خیابان، کوچه، پلاک، طبقه..."></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="required">نام گیرنده</label>
                            <input type="text" name="new_receiver_name" required>
                        </div>
                        <div class="form-group">
                            <label class="required">شماره تماس گیرنده</label>
                            <input type="tel" name="new_receiver_phone" required pattern="09[0-9]{9}">
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <h3 style="margin-top: 24px;">📝 توضیحات سفارش (اختیاری)</h3>
                <div class="form-group">
                    <textarea name="notes" rows="2" placeholder="توضیحات اضافی برای سفارش..."></textarea>
                </div>

                <!-- Payment Method -->
                <h3 style="margin-top: 24px;">💳 روش پرداخت</h3>
                <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <label style="display: flex; align-items: center; gap: 8px; padding: 16px; border: 2px solid var(--gray-200); border-radius: var(--radius); cursor: pointer; flex: 1; min-width: 180px;" 
                           onclick="document.getElementById('paymentOnline').checked = true">
                        <input type="radio" name="payment_method" value="online" id="paymentOnline" checked>
                        <span>💳 پرداخت آنلاین</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; padding: 16px; border: 2px solid var(--gray-200); border-radius: var(--radius); cursor: pointer; flex: 1; min-width: 180px; opacity: 0.5;">
                        <input type="radio" name="payment_method" value="cod" disabled>
                        <span>💵 پرداخت در محل (بزودی)</span>
                    </label>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div>
                <div class="cart-summary">
                    <h3>📋 جزئیات سفارش</h3>
                    
                    <?php foreach($cartItems as $item): 
                        $itemPrice = getDiscountedPrice($item['price'], $item['discount_percent']);
                    ?>
                    <div style="display: flex; gap: 10px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid var(--gray-100);">
                        <div style="width: 50px; height: 50px; border-radius: var(--radius-sm); background: var(--gray-50); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">
                            <?php echo $item['image_main'] ? '<img src="'.$item['image_main'].'" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-sm);">' : '📦'; ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-size: 12px; font-weight: 600;"><?php echo $item['name']; ?></div>
                            <div style="font-size: 11px; color: var(--gray-500);"><?php echo $item['quantity']; ?> عدد</div>
                        </div>
                        <div style="font-weight: 700; font-size: 13px;"><?php echo formatPrice($itemPrice * $item['quantity']); ?></div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-row">
                        <span>جمع</span>
                        <span><?php echo formatPrice($subtotal); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>ارسال</span>
                        <span><?php echo $shipping === 0 ? 'رایگان' : formatPrice($shipping); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>مالیات</span>
                        <span><?php echo formatPrice($tax); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>قابل پرداخت</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: 16px;" id="submitOrderBtn">
                        🚀 ثبت و پرداخت سفارش
                    </button>
                    
                    <a href="/cart" class="btn btn-outline btn-block" style="margin-top: 8px;">← بازگشت به سبد خرید</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function toggleNewAddress() {
    const form = document.getElementById('newAddressForm');
    const btn = document.getElementById('newAddressBtn');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        btn.textContent = '✕ بستن فرم آدرس';
    } else {
        form.style.display = 'none';
        btn.textContent = '➕ افزودن آدرس جدید';
    }
}

function selectAddress(element, addressId) {
    document.querySelectorAll('.address-card').forEach(c => c.classList.remove('default'));
    element.classList.add('default');
    element.querySelector('input[type="radio"]').checked = true;
}

function submitOrder(event) {
    event.preventDefault();
    
    const btn = document.getElementById('submitOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '⏳ در حال ثبت سفارش...';
    
    const formData = new FormData(event.target);
    
    fetch('/ajax/submit-order.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/order-confirmation?id=' + data.order_id;
        } else {
            showToast('❌ ' + (data.message || 'خطا در ثبت سفارش'), 'error');
            btn.disabled = false;
            btn.innerHTML = '🚀 ثبت و پرداخت سفارش';
        }
    })
    .catch(() => {
        showToast('❌ خطا در ارتباط با سرور', 'error');
        btn.disabled = false;
        btn.innerHTML = '🚀 ثبت و پرداخت سفارش';
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>