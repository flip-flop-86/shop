<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => $_POST['site_name'],
        'site_description' => $_POST['site_description'],
        'site_logo' => $_POST['site_logo'],
        'site_favicon' => $_POST['site_favicon'],
        'currency' => $_POST['currency'],
        'free_shipping_min' => $_POST['free_shipping_min'],
        'tax_rate' => $_POST['tax_rate'],
        'contact_phone' => $_POST['contact_phone'],
        'contact_email' => $_POST['contact_email'],
        'contact_address' => $_POST['contact_address'],
        'footer_text' => $_POST['footer_text'],
        'hero_title' => $_POST['hero_title'],
        'hero_description' => $_POST['hero_description'],
        'hero_button_text' => $_POST['hero_button_text'],
        'hero_show' => isset($_POST['hero_show']) ? '1' : '0',
        'primary_color' => $_POST['primary_color'],
        'shipping_methods' => $_POST['shipping_methods'],
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
    ];
    
    foreach($settings as $key => $value) {
        updateSetting($key, $value);
    }
    
    logAdminAction('update_settings', 'تنظیمات سایت بروزرسانی شد');
    $message = 'تنظیمات با موفقیت ذخیره شد.';
}

$allSettings = getAllSettings();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تنظیمات سایت</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --surface: #1e293b; --border: #334155; --text: #e2e8f0; --text-secondary: #94a3b8; --primary: #6366f1; --success: #10b981; --danger: #ef4444; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--surface); border-left: 1px solid var(--border); padding: 20px; position: fixed; top: 0; right: 0; height: 100vh; overflow-y: auto; }
        .sidebar h2 { font-size: 18px; margin-bottom: 24px; text-align: center; color: var(--primary); }
        .sidebar a { display: block; padding: 10px 14px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(99,102,241,0.15); color: var(--text); }
        .main { flex: 1; margin-right: 250px; padding: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        .card h3 { margin-bottom: 20px; font-size: 16px; border-bottom: 1px solid var(--border); padding-bottom: 12px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 12px; color: var(--text-secondary); }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-family: 'Vazirmatn', sans-serif; font-size: 13px; }
        .form-group textarea { resize: vertical; min-height: 60px; }
        .btn { padding: 10px 24px; border: none; border-radius: 8px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 14px; background: var(--primary); color: white; font-weight: 600; }
        .btn:hover { opacity: 0.9; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .color-input { display: flex; align-items: center; gap: 8px; }
        .color-input input[type="color"] { width: 50px; height: 40px; padding: 2px; cursor: pointer; }
        .color-input input[type="text"] { flex: 1; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🔐 پنل مدیریت</h2>
        <a href="dashboard.php">📊 داشبورد</a>
        <a href="products.php">📦 محصولات</a>
        <a href="categories.php">📂 دسته‌بندی‌ها</a>
        <a href="orders.php">🛒 سفارشات</a>
        <a href="users.php">👥 کاربران</a>
        <a href="coupons.php">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php" class="active">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <h2 style="margin-bottom: 20px;">⚙️ تنظیمات سایت</h2>

        <?php if($message): ?>
        <div class="alert">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- عمومی -->
            <div class="card">
                <h3>🌐 تنظیمات عمومی</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>نام فروشگاه</label>
                        <input type="text" name="site_name" value="<?php echo $allSettings['site_name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>توضیحات سایت</label>
                        <input type="text" name="site_description" value="<?php echo $allSettings['site_description'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>لوگو (ایموجی)</label>
                        <input type="text" name="site_logo" value="<?php echo $allSettings['site_logo'] ?? '🛒'; ?>" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label>Favicon (ایموجی)</label>
                        <input type="text" name="site_favicon" value="<?php echo $allSettings['site_favicon'] ?? '🛒'; ?>" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label>واحد پول</label>
                        <input type="text" name="currency" value="<?php echo $allSettings['currency'] ?? 'تومان'; ?>">
                    </div>
                    <div class="form-group">
                        <label>حداقل خرید برای ارسال رایگان</label>
                        <input type="number" name="free_shipping_min" value="<?php echo $allSettings['free_shipping_min'] ?? 500000; ?>">
                    </div>
                    <div class="form-group">
                        <label>نرخ مالیات (%)</label>
                        <input type="number" name="tax_rate" value="<?php echo $allSettings['tax_rate'] ?? 9; ?>">
                    </div>
                    <div class="form-group">
                        <label>رنگ اصلی</label>
                        <div class="color-input">
                            <input type="color" name="primary_color" value="<?php echo $allSettings['primary_color'] ?? '#4f46e5'; ?>">
                            <input type="text" name="primary_color" value="<?php echo $allSettings['primary_color'] ?? '#4f46e5'; ?>">
                        </div>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="maintenance_mode" <?php echo ($allSettings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                        حالت تعمیرات (فقط ادمین سایت رو ببینه)
                    </label>
                </div>
            </div>

            <!-- تماس -->
            <div class="card">
                <h3>📞 اطلاعات تماس</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>تلفن</label>
                        <input type="text" name="contact_phone" value="<?php echo $allSettings['contact_phone'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>ایمیل</label>
                        <input type="email" name="contact_email" value="<?php echo $allSettings['contact_email'] ?? ''; ?>">
                    </div>
                    <div class="form-group" style="grid-column: 1/-1;">
                        <label>آدرس</label>
                        <textarea name="contact_address" rows="2"><?php echo $allSettings['contact_address'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>

            <!-- صفحه اصلی -->
            <div class="card">
                <h3>🏠 صفحه اصلی (Hero)</h3>
                <div class="form-group">
                    <label>عنوان</label>
                    <input type="text" name="hero_title" value="<?php echo $allSettings['hero_title'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>توضیحات</label>
                    <textarea name="hero_description" rows="2"><?php echo $allSettings['hero_description'] ?? ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>متن دکمه</label>
                    <input type="text" name="hero_button_text" value="<?php echo $allSettings['hero_button_text'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="hero_show" <?php echo ($allSettings['hero_show'] ?? '1') === '1' ? 'checked' : ''; ?>>
                        نمایش بخش Hero
                    </label>
                </div>
            </div>

            <!-- فوتر -->
            <div class="card">
                <h3>📄 فوتر</h3>
                <div class="form-group">
                    <label>متن کپی‌رایت</label>
                    <input type="text" name="footer_text" value="<?php echo $allSettings['footer_text'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>روش‌های ارسال (با | جدا کنید)</label>
                    <input type="text" name="shipping_methods" value="<?php echo $allSettings['shipping_methods'] ?? 'پست پیشتاز|پیک موتوری|تیپاکس'; ?>">
                </div>
            </div>

            <button type="submit" class="btn">💾 ذخیره تمام تنظیمات</button>
        </form>
    </main>
</body>
</html>