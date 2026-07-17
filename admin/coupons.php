<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper($_POST['code']);
    $type = $_POST['type'];
    $value = intval($_POST['value']);
    $minOrder = intval($_POST['min_order']);
    $maxDiscount = intval($_POST['max_discount']);
    $usageLimit = intval($_POST['usage_limit']);
    $expireAt = !empty($_POST['expire_at']) ? $_POST['expire_at'] : null;
    
    $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_order, max_discount, usage_limit, expire_at) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$code, $type, $value, $minOrder, $maxDiscount, $usageLimit, $expireAt]);
    $message = 'کوپن جدید ایجاد شد.';
}

if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM coupons WHERE id = ?")->execute([intval($_GET['delete'])]);
    $message = 'کوپن حذف شد.';
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کوپن‌ها</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --surface: #1e293b; --border: #334155; --text: #e2e8f0; --text-secondary: #94a3b8; --primary: #6366f1; --danger: #ef4444; --success: #10b981; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--surface); border-left: 1px solid var(--border); padding: 20px; position: fixed; top: 0; right: 0; height: 100vh; overflow-y: auto; }
        .sidebar h2 { font-size: 18px; margin-bottom: 24px; text-align: center; color: var(--primary); }
        .sidebar a { display: block; padding: 10px 14px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(99,102,241,0.15); color: var(--text); }
        .main { flex: 1; margin-right: 250px; padding: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-sm { padding: 4px 10px; font-size: 10px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary); }
        .form-group input, .form-group select { width: 100%; padding: 8px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Vazirmatn', sans-serif; font-size: 13px; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
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
        <a href="coupons.php" class="active">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <?php if($message): ?>
        <div class="alert">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 20px;">
            <div class="card">
                <h3>🎫 کوپن‌های تخفیف</h3>
                <table>
                    <thead><tr><th>کد</th><th>نوع</th><th>مقدار</th><th>استفاده</th><th>انقضا</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach($coupons as $c): ?>
                        <tr>
                            <td><code style="background:var(--bg);padding:2px 8px;border-radius:4px;"><?php echo $c['code']; ?></code></td>
                            <td><?php echo $c['type'] === 'percent' ? 'درصدی' : 'ثابت'; ?></td>
                            <td><?php echo $c['type'] === 'percent' ? "٪{$c['value']}" : formatPrice($c['value']); ?></td>
                            <td><?php echo $c['used_count']; ?>/<?php echo $c['usage_limit'] ?: '∞'; ?></td>
                            <td><?php echo $c['expire_at'] ?? 'نامحدود'; ?></td>
                            <td><a href="?delete=<?php echo $c['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('حذف بشه؟')">🗑️</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3>➕ کوپن جدید</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>کد تخفیف *</label>
                        <input type="text" name="code" required placeholder="مثلاً: WELCOME50">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>نوع</label>
                            <select name="type">
                                <option value="percent">درصدی</option>
                                <option value="fixed">مبلغ ثابت</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>مقدار</label>
                            <input type="number" name="value" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>حداقل خرید</label>
                            <input type="number" name="min_order" value="0">
                        </div>
                        <div class="form-group">
                            <label>حداکثر تخفیف</label>
                            <input type="number" name="max_discount" value="0">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>محدودیت استفاده</label>
                            <input type="number" name="usage_limit" value="0" placeholder="۰ = نامحدود">
                        </div>
                        <div class="form-group">
                            <label>تاریخ انقضا</label>
                            <input type="datetime-local" name="expire_at">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 ایجاد کوپن</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>