<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// تغییر وضعیت سفارش
if(isset($_POST['update_status'])) {
    $orderId = intval($_POST['order_id']);
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $orderId]);
    logAdminAction('update_order', "سفارش #$orderId به وضعیت $status تغییر کرد");
}

$statusFilter = $_GET['status'] ?? '';
$sql = "SELECT o.*, u.fullname, u.phone FROM orders o JOIN users u ON o.user_id = u.id";
if($statusFilter) {
    $sql .= " WHERE o.status = ?";
    $stmt = $pdo->prepare($sql . " ORDER BY o.created_at DESC");
    $stmt->execute([$statusFilter]);
} else {
    $stmt = $pdo->query($sql . " ORDER BY o.created_at DESC");
}
$orders = $stmt->fetchAll();

$statuses = ['pending' => 'در انتظار', 'confirmed' => 'تأیید شده', 'processing' => 'در حال پردازش', 'shipped' => 'ارسال شده', 'delivered' => 'تحویل شده', 'cancelled' => 'لغو شده'];
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت سفارشات</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root { --bg: #0f172a; --surface: #1e293b; --border: #334155; --text: #e2e8f0; --text-secondary: #94a3b8; --primary: #6366f1; --success: #10b981; --warning: #f59e0b; --danger: #ef4444; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--surface); border-left: 1px solid var(--border); padding: 20px; position: fixed; top: 0; right: 0; height: 100vh; overflow-y: auto; }
        .sidebar h2 { font-size: 18px; margin-bottom: 24px; text-align: center; color: var(--primary); }
        .sidebar a { display: block; padding: 10px 14px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(99,102,241,0.15); color: var(--text); }
        .main { flex: 1; margin-right: 250px; padding: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 12px; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-sm { padding: 4px 10px; font-size: 10px; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .badge { padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; }
        .badge-warning { background: rgba(245,158,11,0.2); color: var(--warning); }
        .badge-success { background: rgba(16,185,129,0.2); color: var(--success); }
        .badge-info { background: rgba(99,102,241,0.2); color: var(--primary); }
        .badge-danger { background: rgba(239,68,68,0.2); color: var(--danger); }
        .filters { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .filters a { padding: 6px 14px; border-radius: 20px; font-size: 12px; text-decoration: none; border: 1px solid var(--border); color: var(--text-secondary); }
        .filters a.active { background: var(--primary); color: white; border-color: var(--primary); }
        select { padding: 6px 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Vazirmatn', sans-serif; font-size: 11px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🔐 پنل مدیریت</h2>
        <a href="dashboard.php">📊 داشبورد</a>
        <a href="products.php">📦 محصولات</a>
        <a href="categories.php">📂 دسته‌بندی‌ها</a>
        <a href="orders.php" class="active">🛒 سفارشات</a>
        <a href="users.php">👥 کاربران</a>
        <a href="coupons.php">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <h2 style="margin-bottom: 20px;">🛒 سفارشات</h2>

        <div class="filters">
            <a href="orders.php" class="<?php echo !$statusFilter ? 'active' : ''; ?>">همه</a>
            <?php foreach($statuses as $key => $label): ?>
            <a href="?status=<?php echo $key; ?>" class="<?php echo $statusFilter === $key ? 'active' : ''; ?>"><?php echo $label; ?></a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>شماره سفارش</th>
                        <th>مشتری</th>
                        <th>مبلغ</th>
                        <th>پرداخت</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                        <th>تغییر وضعیت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo $order['fullname']; ?><br><small style="color:var(--text-secondary);"><?php echo $order['phone']; ?></small></td>
                        <td><?php echo formatPrice($order['final_amount']); ?></td>
                        <td><span class="badge badge-<?php echo $order['payment_status'] === 'paid' ? 'success' : 'warning'; ?>"><?php echo $order['payment_status']; ?></span></td>
                        <td><span class="badge badge-<?php echo $order['status'] === 'delivered' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : ($order['status'] === 'pending' ? 'warning' : 'info')); ?>"><?php echo $statuses[$order['status']] ?? $order['status']; ?></span></td>
                        <td><?php echo $order['created_at']; ?></td>
                        <td>
                            <form method="POST" style="display: flex; gap: 4px;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <input type="hidden" name="update_status" value="1">
                                <select name="status" onchange="this.form.submit()">
                                    <?php foreach($statuses as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>