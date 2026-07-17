<?php
require_once __DIR__ . '/includes/header.php';

// اگر قبلاً لاگین کرده، برگرد به صفحه اصلی
if(isLoggedIn()) {
    header('Location: /profile');
    exit;
}

$pageTitle = 'ورود / ثبت‌نام';
$mode = $_GET['mode'] ?? 'login'; // login or register
$error = '';
$success = '';

// پردازش فرم
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    $action = $_POST['action'] ?? '';
    
    if($action === 'login') {
        // لاگین
        $password = $_POST['password'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        
        if($user && password_verify($password, $user['password'])) {
            if($user['is_active']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_phone'] = $user['phone'];
                $_SESSION['user_fullname'] = $user['fullname'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                
                // بروزرسانی آخرین لاگین
                $stmt = $pdo->prepare("UPDATE users SET last_login = NOW(), login_attempts = 0 WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                $redirect = $_SESSION['redirect_after_login'] ?? '/profile';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'حساب کاربری شما غیرفعال شده است.';
            }
        } else {
            $error = 'شماره تماس یا رمز عبور اشتباه است.';
            // افزایش تعداد تلاش ناموفق
            if($user) {
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?");
                $stmt->execute([$user['id']]);
            }
        }
    } elseif($action === 'register') {
        // ثبت‌نام
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $fullname = $_POST['fullname'] ?? '';
        
        // اعتبارسنجی
        if(!preg_match('/^09[0-9]{9}$/', $phone)) {
            $error = 'شماره تماس نامعتبر است.';
        } elseif(strlen($password) < 8) {
            $error = 'رمز عبور باید حداقل ۸ کاراکتر باشد.';
        } elseif($password !== $confirmPassword) {
            $error = 'رمز عبور و تکرار آن مطابقت ندارند.';
        } else {
            // چک تکراری نبودن
            $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ?");
            $stmt->execute([$phone]);
            if($stmt->fetch()) {
                $error = 'این شماره تماس قبلاً ثبت شده است.';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                $stmt = $pdo->prepare("INSERT INTO users (phone, password, fullname, role) VALUES (?, ?, ?, 'user')");
                $stmt->execute([$phone, $hashedPassword, $fullname]);
                
                $userId = $pdo->lastInsertId();
                
                // لاگین خودکار
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_phone'] = $phone;
                $_SESSION['user_fullname'] = $fullname;
                $_SESSION['user_role'] = 'user';
                
                $redirect = $_SESSION['redirect_after_login'] ?? '/profile';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit;
            }
        }
    }
}
?>

<div class="container">
    <div class="auth-page">
        <div class="auth-card">
            <h2><?php echo $mode === 'register' ? '📝 ثبت‌نام' : '🔐 ورود'; ?></h2>
            <p><?php echo $mode === 'register' ? 'حساب کاربری جدید بسازید' : 'به فروشگاه خوش آمدید'; ?></p>
            
            <?php if($error): ?>
            <div style="background: var(--danger-light); color: #991b1b; padding: 12px; border-radius: var(--radius); margin-bottom: 16px; font-size: 13px;">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if(isset($_GET['registered'])): ?>
            <div style="background: var(--success-light); color: #065f46; padding: 12px; border-radius: var(--radius); margin-bottom: 16px; font-size: 13px;">
                ✅ ثبت‌نام با موفقیت انجام شد. وارد شوید.
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $mode; ?>">
                
                <div class="form-group">
                    <label class="required">شماره تماس</label>
                    <input type="tel" name="phone" required pattern="09[0-9]{9}" 
                           placeholder="۰۹۱۲۳۴۵۶۷۸۹" 
                           value="<?php echo $_POST['phone'] ?? ''; ?>"
                           style="text-align: center; font-size: 16px; letter-spacing: 2px;">
                </div>
                
                <?php if($mode === 'register'): ?>
                <div class="form-group">
                    <label>نام و نام خانوادگی</label>
                    <input type="text" name="fullname" value="<?php echo $_POST['fullname'] ?? ''; ?>">
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="required">رمز عبور</label>
                    <input type="password" name="password" required minlength="8" 
                           placeholder="حداقل ۸ کاراکتر">
                </div>
                
                <?php if($mode === 'register'): ?>
                <div class="form-group">
                    <label class="required">تکرار رمز عبور</label>
                    <input type="password" name="confirm_password" required minlength="8" 
                           placeholder="رمز عبور را دوباره وارد کنید">
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">
                    <?php echo $mode === 'register' ? '📝 ثبت‌نام' : '🔐 ورود'; ?>
                </button>
            </form>
            
            <div class="auth-footer">
                <?php if($mode === 'login'): ?>
                حساب کاربری ندارید؟ <a href="/login?mode=register">ثبت‌نام کنید</a>
                <?php else: ?>
                قبلاً ثبت‌نام کردید؟ <a href="/login">وارد شوید</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>