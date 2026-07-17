<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$message = '';

// افزودن دسته‌بندی
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = sanitize($_POST['name']);
    $icon = sanitize($_POST['icon']);
    $description = sanitize($_POST['description']);
    $slug = generateSlug($name);
    
    $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $slug, $icon, $description]);
    logAdminAction('add_category', "دسته‌بندی '$name' افزوده شد");
    $message = 'دسته‌بندی جدید افزوده شد.';
}

// ویرایش دسته‌بندی
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = intval($_POST['id']);
    $name = sanitize($_POST['name']);
    $icon = sanitize($_POST['icon']);
    $description = sanitize($_POST['description']);
    $slug = generateSlug($name);
    
    $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, icon=?, description=? WHERE id=?");
    $stmt->execute([$name, $slug, $icon, $description, $id]);
    logAdminAction('edit_category', "دسته‌بندی #$id ویرایش شد");
    $message = 'دسته‌بندی ویرایش شد.';
}

// حذف دسته‌بندی
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // انتقال محصولات این دسته به دسته پیش‌فرض
    $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
    logAdminAction('delete_category', "دسته‌بندی #$id حذف شد");
    $message = 'دسته‌بندی حذف شد.';
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count FROM categories c ORDER BY sort_order")->fetchAll();
$editCategory = null;
if(isset($_GET['edit'])) {
    $editCategory = getCategoryById(intval($_GET['edit']));
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت دسته‌بندی‌ها</title>
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
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-sm { padding: 4px 10px; font-size: 10px; }
        .form-group { margin-bottom: 12px; }
        .form-group label { display: block; margin-bottom: 4px; font-size: 12px; color: var(--text-secondary); }
        .form-group input, .form-group textarea { width: 100%; padding: 8px 12px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; color: var(--text); font-family: 'Vazirmatn', sans-serif; font-size: 13px; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🔐 پنل مدیریت</h2>
        <a href="dashboard.php">📊 داشبورد</a>
        <a href="products.php">📦 محصولات</a>
        <a href="categories.php" class="active">📂 دسته‌بندی‌ها</a>
        <a href="orders.php">🛒 سفارشات</a>
        <a href="users.php">👥 کاربران</a>
        <a href="coupons.php">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <?php if($message): ?>
        <div class="alert">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 20px;">
            <!-- لیست -->
            <div class="card">
                <h3>📂 دسته‌بندی‌ها (<?php echo count($categories); ?>)</h3>
                <table>
                    <thead>
                        <tr><th>آیکون</th><th>نام</th><th>محصولات</th><th>عملیات</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $cat): ?>
                        <tr>
                            <td style="font-size:24px;"><?php echo $cat['icon']; ?></td>
                            <td><?php echo $cat['name']; ?></td>
                            <td><?php echo $cat['product_count']; ?> عدد</td>
                            <td>
                                <a href="?edit=<?php echo $cat['id']; ?>" class="btn btn-outline btn-sm">✏️</a>
                                <a href="?delete=<?php echo $cat['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('محصولات این دسته بدون دسته می‌شوند. مطمئنی؟')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- فرم -->
            <div class="card">
                <h3><?php echo $editCategory ? '✏️ ویرایش دسته‌بندی' : '➕ دسته‌بندی جدید'; ?></h3>
                <form method="POST">
                    <?php if($editCategory): ?>
                    <input type="hidden" name="edit_category" value="1">
                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                    <?php else: ?>
                    <input type="hidden" name="add_category" value="1">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>نام *</label>
                        <input type="text" name="name" required value="<?php echo $editCategory['name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>آیکون (ایموجی)</label>
                        <input type="text" name="icon" value="<?php echo $editCategory['icon'] ?? '📂'; ?>" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label>توضیحات</label>
                        <textarea name="description" rows="2"><?php echo $editCategory['description'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 ذخیره</button>
                    <?php if($editCategory): ?>
                    <a href="categories.php" class="btn btn-outline">انصراف</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>
</body>
</html>