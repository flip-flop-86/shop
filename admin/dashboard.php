<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// آمار
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$totalRevenue = $pdo->query("SELECT SUM(final_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?? 0;
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>داشبورد مدیریت</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --surface: #1e293b;
            --border: #334155;
            --text: #e2e8f0;
            --text-secondary: #94a3b8;
            --primary: #6366f1;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: var(--surface);
            border-left: 1px solid var(--border);
            padding: 20px;
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 24px;
            text-align: center;
            color: var(--primary);
        }
        .sidebar a {
            display: block;
            padding: 10px 14px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.3s;
            font-size: 14px;
        }
        .sidebar a:hover, .sidebar a.active {
            background: rgba(99,102,241,0.15);
            color: var(--text);
        }
        .main {
            flex: 1;
            margin-right: 250px;
            padding: 24px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 22px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-card .num {
            font-size: 28px;
            font-weight: 900;
            color: var(--primary);
        }
        .stat-card .lbl {
            font-size: 11px;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .card h3 {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: right;
            padding: 10px;
            font-size: 11px;
            color: var(--text-secondary);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 10px;
            font-size: 13px;
            border-bottom: 1px solid var(--border);
        }
        .btn {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 12px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .badge {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
        }
        .badge-success { background: rgba(16,185,129,0.2); color: var(--success); }
        .badge-warning { background: rgba(245,158,11,0.2); color: var(--warning); }
        .badge-danger { background: rgba(239,68,68,0.2); color: var(--danger); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🔐 پنل مدیریت</h2>
        <a href="dashboard.php" class="active">📊 داشبورد</a>
        <a href="products.php">📦 محصولات</a>
        <a href="categories.php">📂 دسته‌بندی‌ها</a>
        <a href="orders.php">🛒 سفارشات</a>
        <a href="users.php">👥 کاربران</a>
        <a href="coupons.php">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <div class="header">
            <h1>📊 داشبورد</h1>
            <a href="../" class="btn btn-outline" target="_blank">👁️ مشاهده سایت</a>
        </div>

        <div class="stats">
            <div class="stat-card"><div class="num"><?php echo $totalProducts; ?></div><div class="lbl">📦 محصولات</div></div>
            <div class="stat-card"><div class="num"><?php echo $totalOrders; ?></div><div class="lbl">🛒 سفارشات</div></div>
            <div class="stat-card"><div class="num"><?php echo $totalUsers; ?></div><div class="lbl">👥 کاربران</div></div>
            <div class="stat-card"><div class="num"><?php echo $totalCategories; ?></div><div class="lbl">📂 دسته‌بندی‌ها</div></div>
            <div class="stat-card"><div class="num"><?php echo $pendingOrders; ?></div><div class="lbl">⏳ در انتظار</div></div>
            <div class="stat-card"><div class="num"><?php echo formatPrice($totalRevenue); ?></div><div class="lbl">💰 درآمد کل</div></div>
        </div>

        <div class="card">
            <h3>🕐 آخرین سفارشات</h3>
            <table>
                <thead>
                    <tr>
                        <th>شماره</th>
                        <th>مشتری</th>
                        <th>مبلغ</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recentOrders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_number']; ?></td>
                        <td><?php 
                            $user = $pdo->query("SELECT fullname FROM users WHERE id = {$order['user_id']}")->fetch();
                            echo $user['fullname'] ?? 'کاربر';
                        ?></td>
                        <td><?php echo formatPrice($order['final_amount']); ?></td>
                        <td><span class="badge badge-<?php echo $order['status'] === 'pending' ? 'warning' : ($order['status'] === 'delivered' ? 'success' : 'danger'); ?>"><?php echo $order['status']; ?></span></td>
                        <td><?php echo $order['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>