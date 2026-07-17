<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

// تغییر وضعیت کاربر
if(isset($_GET['toggle']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$id]);
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت کاربران</title>
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
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 4px 10px; border: none; border-radius: 4px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 10px; text-decoration: none; display: inline-block; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 10px; }
        .badge-success { background: rgba(16,185,129,0.2); color: var(--success); }
        .badge-danger { background: rgba(239,68,68,0.2); color: var(--danger); }
        .badge-info { background: rgba(99,102,241,0.2); color: var(--primary); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🔐 پنل مدیریت</h2>
        <a href="dashboard.php">📊 داشبورد</a>
        <a href="products.php">📦 محصولات</a>
        <a href="categories.php">📂 دسته‌بندی‌ها</a>
        <a href="orders.php">🛒 سفارشات</a>
        <a href="users.php" class="active">👥 کاربران</a>
        <a href="coupons.php">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <h2 style="margin-bottom: 20px;">👥 کاربران (<?php echo count($users); ?>)</h2>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>نام</th>
                        <th>شماره</th>
                        <th>ایمیل</th>
                        <th>نقش</th>
                        <th>وضعیت</th>
                        <th>تاریخ عضویت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td><?php echo $user['fullname'] ?: '-'; ?></td>
                        <td><?php echo $user['phone']; ?></td>
                        <td><?php echo $user['email'] ?: '-'; ?></td>
                        <td><span class="badge badge-<?php echo $user['role'] === 'admin' ? 'info' : 'success'; ?>"><?php echo $user['role']; ?></span></td>
                        <td><span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>"><?php echo $user['is_active'] ? 'فعال' : 'غیرفعال'; ?></span></td>
                        <td><?php echo $user['created_at']; ?></td>
                        <td>
                            <a href="?toggle=1&id=<?php echo $user['id']; ?>" class="btn <?php echo $user['is_active'] ? 'btn-danger' : 'btn-success'; ?>">
                                <?php echo $user['is_active'] ? 'غیرفعال' : 'فعال'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>