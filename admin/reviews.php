<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

// تأیید نظر
if(isset($_GET['approve'])) {
    $pdo->prepare("UPDATE reviews SET is_approved = 1 WHERE id = ?")->execute([intval($_GET['approve'])]);
}

// حذف نظر
if(isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([intval($_GET['delete'])]);
}

$reviews = $pdo->query("SELECT r.*, u.fullname, u.phone, p.name as product_name FROM reviews r JOIN users u ON r.user_id = u.id JOIN products p ON r.product_id = p.id ORDER BY r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت نظرات</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --surface: #1e293b; --border: #334155; --text: #e2e8f0; --text-secondary: #94a3b8; --primary: #6366f1; --danger: #ef4444; --success: #10b981; --warning: #f59e0b; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--surface); border-left: 1px solid var(--border); padding: 20px; position: fixed; top: 0; right: 0; height: 100vh; overflow-y: auto; }
        .sidebar h2 { font-size: 18px; margin-bottom: 24px; text-align: center; color: var(--primary); }
        .sidebar a { display: block; padding: 10px 14px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(99,102,241,0.15); color: var(--text); }
        .main { flex: 1; margin-right: 250px; padding: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 4px 10px; border: none; border-radius: 4px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 10px; text-decoration: none; display: inline-block; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-warning { background: var(--warning); color: black; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-success { background: rgba(16,185,129,0.2); color: var(--success); }
        .badge-warning { background: rgba(245,158,11,0.2); color: var(--warning); }
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
        <a href="reviews.php" class="active">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <h2 style="margin-bottom: 20px;">💬 نظرات</h2>
        <div class="card">
            <table>
                <thead>
                    <tr><th>کاربر</th><th>محصول</th><th>امتیاز</th><th>نظر</th><th>وضعیت</th><th>عملیات</th></tr>
                </thead>
                <tbody>
                    <?php foreach($reviews as $r): ?>
                    <tr>
                        <td><?php echo $r['fullname']; ?></td>
                        <td><?php echo $r['product_name']; ?></td>
                        <td><?php echo str_repeat('⭐', $r['rating']); ?></td>
                        <td><?php echo mb_substr($r['comment'], 0, 50) . '...'; ?></td>
                        <td><span class="badge badge-<?php echo $r['is_approved'] ? 'success' : 'warning'; ?>"><?php echo $r['is_approved'] ? 'تأیید شده' : 'در انتظار'; ?></span></td>
                        <td>
                            <?php if(!$r['is_approved']): ?>
                            <a href="?approve=<?php echo $r['id']; ?>" class="btn btn-success">✓ تأیید</a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $r['id']; ?>" class="btn btn-danger" onclick="return confirm('حذف بشه؟')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>