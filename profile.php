<?php
require_once __DIR__ . '/includes/header.php';
requireLogin();

$pageTitle = 'پروفایل کاربری';
$user = $_SESSION;
$userAddresses = getUserAddresses($_SESSION['user_id']);
$orders = getOrders($_SESSION['user_id']);
$wishlist = getWishlist($_SESSION['user_id']);
$activeTab = $_GET['tab'] ?? 'dashboard';

// بروزرسانی پروفایل
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullname = sanitize($_POST['fullname']);
    $email = sanitize($_POST['email']);
    
    $stmt = $pdo->prepare("UPDATE users SET fullname = ?, email = ? WHERE id = ?");
    $stmt->execute([$fullname, $email, $_SESSION['user_id']]);
    
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_email'] = $email;
    $success = 'پروفایل با موفقیت بروزرسانی شد.';
}

// تغییر رمز
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch();
    
    if(!password_verify($currentPassword, $userData['password'])) {
        $passwordError = 'رمز عبور فعلی اشتباه است.';
    } elseif(strlen($newPassword) < 8) {
        $passwordError = 'رمز عبور جدید باید حداقل ۸ کاراکتر باشد.';
    } elseif($newPassword !== $confirmPassword) {
        $passwordError = 'رمز عبور جدید و تکرار آن مطابقت ندارند.';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
        $passwordSuccess = 'رمز عبور با موفقیت تغییر کرد.';
    }
}
?>

<div class="container">
    <div class="profile-page">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-sidebar-header">
                <div class="profile-avatar">
                    <?php echo mb_substr($user['user_fullname'] ?? '?', 0, 1); ?>
                </div>
                <div class="profile-name"><?php echo $user['user_fullname'] ?? 'کاربر'; ?></div>
                <div class="profile-phone"><?php echo $user['user_phone']; ?></div>
            </div>
            <div class="profile-menu">
                <a href="/profile?tab=dashboard" class="<?php echo $activeTab === 'dashboard' ? 'active' : ''; ?>">📊 داشبورد</a>
                <a href="/profile?tab=edit" class="<?php echo $activeTab === 'edit' ? 'active' : ''; ?>">👤 ویرایش پروفایل</a>
                <a href="/profile?tab=addresses" class="<?php echo $activeTab === 'addresses' ? 'active' : ''; ?>">📍 آدرس‌ها</a>
                <a href="/profile?tab=orders" class="<?php echo $activeTab === 'orders' ? 'active' : ''; ?>">📋 سفارشات</a>
                <a href="/profile?tab=wishlist" class="<?php echo $activeTab === 'wishlist' ? 'active' : ''; ?>">❤️ علاقه‌مندی‌ها</a>
                <a href="/profile?tab=password" class="<?php echo $activeTab === 'password' ? 'active' : ''; ?>">🔒 تغییر رمز</a>
                <a href="/logout" style="color: var(--danger);">🚪 خروج</a>
            </div>
        </aside>

        <!-- Content -->
        <div class="profile-content">
            <?php if(isset($success)): ?>
            <div style="background: var(--success-light); color: #065f46; padding: 12px; border-radius: var(--radius); margin-bottom: 16px;">✅ <?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if(isset($passwordError)): ?>
            <div style="background: var(--danger-light); color: #991b1b; padding: 12px; border-radius: var(--radius); margin-bottom: 16px;">❌ <?php echo $passwordError; ?></div>
            <?php endif; ?>
            
            <?php if(isset($passwordSuccess)): ?>
            <div style="background: var(--success-light); color: #065f46; padding: 12px; border-radius: var(--radius); margin-bottom: 16px;">✅ <?php echo $passwordSuccess; ?></div>
            <?php endif; ?>

            <?php
            switch($activeTab) {
                case 'dashboard':
                    ?>
                    <h3 style="margin-bottom: 20px;">📊 داشبورد</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 24px;">
                        <div style="background: var(--gray-50); padding: 20px; border-radius: var(--radius); text-align: center;">
                            <div style="font-size: 32px; font-weight: 900; color: var(--primary);"><?php echo count($orders); ?></div>
                            <div style="font-size: 12px; color: var(--gray-500);">سفارش</div>
                        </div>
                        <div style="background: var(--gray-50); padding: 20px; border-radius: var(--radius); text-align: center;">
                            <div style="font-size: 32px; font-weight: 900; color: var(--success);"><?php echo count($wishlist); ?></div>
                            <div style="font-size: 12px; color: var(--gray-500);">علاقه‌مندی</div>
                        </div>
                        <div style="background: var(--gray-50); padding: 20px; border-radius: var(--radius); text-align: center;">
                            <div style="font-size: 32px; font-weight: 900; color: var(--warning);"><?php echo count($userAddresses); ?></div>
                            <div style="font-size: 12px; color: var(--gray-500);">آدرس</div>
                        </div>
                    </div>
                    
                    <h4>🕐 آخرین سفارشات</h4>
                    <?php if(!empty($orders)): ?>
                        <?php foreach(array_slice($orders, 0, 3) as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-number"><?php echo $order['order_number']; ?></span>
                                    <span class="order-date"><?php echo $order['created_at']; ?></span>
                                </div>
                                <span class="order-status status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
                            </div>
                            <div class="order-total"><?php echo formatPrice($order['final_amount']); ?></div>
                        </div>
                        <?php endforeach; ?>
                        <a href="/profile?tab=orders" style="font-size: 13px; color: var(--primary);">مشاهده همه سفارشات →</a>
                    <?php else: ?>
                        <p style="color: var(--gray-500);">هنوز سفارشی ثبت نکردید.</p>
                    <?php endif; ?>
                    <?php
                    break;

                case 'edit':
                    ?>
                    <h3 style="margin-bottom: 20px;">👤 ویرایش پروفایل</h3>
                    <form method="POST">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="form-group">
                            <label>شماره تماس (غیرقابل تغییر)</label>
                            <input type="text" value="<?php echo $user['user_phone']; ?>" disabled style="background: var(--gray-50);">
                        </div>
                        <div class="form-group">
                            <label>نام و نام خانوادگی</label>
                            <input type="text" name="fullname" value="<?php echo $user['user_fullname'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>ایمیل</label>
                            <input type="email" name="email" value="<?php echo $user['user_email'] ?? ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">💾 ذخیره تغییرات</button>
                    </form>
                    <?php
                    break;

                case 'addresses':
                    ?>
                    <h3 style="margin-bottom: 20px;">📍 آدرس‌های من</h3>
                    <?php if(!empty($userAddresses)): ?>
                        <?php foreach($userAddresses as $addr): ?>
                        <div class="address-card <?php echo $addr['is_default'] ? 'default' : ''; ?>">
                            <div class="address-title"><?php echo $addr['title']; ?></div>
                            <div class="address-detail">
                                <?php echo $addr['province'] . ' - ' . $addr['city']; ?><br>
                                <?php echo $addr['address']; ?><br>
                                کد پستی: <?php echo $addr['postal_code']; ?><br>
                                گیرنده: <?php echo $addr['receiver_name']; ?> | <?php echo $addr['receiver_phone']; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--gray-500);">هنوز آدرسی ثبت نکردید.</p>
                    <?php endif; ?>
                    <?php
                    break;

                case 'orders':
                    ?>
                    <h3 style="margin-bottom: 20px;">📋 سفارشات من</h3>
                    <?php if(!empty($orders)): ?>
                        <?php foreach($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-number"><?php echo $order['order_number']; ?></span>
                                    <span class="order-date"><?php echo $order['created_at']; ?></span>
                                </div>
                                <span class="order-status status-<?php echo $order['status']; ?>"><?php echo $order['status']; ?></span>
                            </div>
                            <div class="order-total"><?php echo formatPrice($order['final_amount']); ?></div>
                            <a href="/order/<?php echo $order['id']; ?>" style="font-size: 12px; color: var(--primary);">مشاهده جزئیات</a>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--gray-500);">هنوز سفارشی ثبت نکردید.</p>
                    <?php endif; ?>
                    <?php
                    break;

                case 'wishlist':
                    ?>
                    <h3 style="margin-bottom: 20px;">❤️ علاقه‌مندی‌ها</h3>
                    <?php if(!empty($wishlist)): ?>
                    <div class="products-grid">
                        <?php foreach($wishlist as $item): ?>
                        <div class="product-card">
                            <div class="product-card-image">
                                <a href="/product/<?php echo $item['slug']; ?>">
                                    <?php echo $item['image_main'] ? '<img src="'.$item['image_main'].'" alt="'.$item['name'].'">' : '📦'; ?>
                                </a>
                            </div>
                            <div class="product-card-body">
                                <h3 class="product-card-title">
                                    <a href="/product/<?php echo $item['slug']; ?>"><?php echo $item['name']; ?></a>
                                </h3>
                                <div class="product-card-price">
                                    <span class="current-price"><?php echo formatPrice(getDiscountedPrice($item['price'], $item['discount_percent'])); ?></span>
                                </div>
                                <button class="btn btn-primary btn-sm btn-block add-to-cart-btn" data-product-id="<?php echo $item['product_id']; ?>">🛒 افزودن به سبد</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p style="color: var(--gray-500);">هنوز محصولی به علاقه‌مندی‌ها اضافه نکردید.</p>
                    <?php endif; ?>
                    <?php
                    break;

                case 'password':
                    ?>
                    <h3 style="margin-bottom: 20px;">🔒 تغییر رمز عبور</h3>
                    <form method="POST">
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-group">
                            <label class="required">رمز عبور فعلی</label>
                            <input type="password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label class="required">رمز عبور جدید</label>
                            <input type="password" name="new_password" required minlength="8" placeholder="حداقل ۸ کاراکتر">
                        </div>
                        <div class="form-group">
                            <label class="required">تکرار رمز عبور جدید</label>
                            <input type="password" name="confirm_password" required minlength="8">
                        </div>
                        <button type="submit" class="btn btn-primary">🔒 تغییر رمز</button>
                    </form>
                    <?php
                    break;
            }
            ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>