<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $title = $_POST['title'];
    $slug = $_POST['slug'];
    $content = $_POST['content'];
    $metaTitle = $_POST['meta_title'];
    $metaDescription = $_POST['meta_description'];
    
    if($id > 0) {
        $stmt = $pdo->prepare("UPDATE pages SET title=?, slug=?, content=?, meta_title=?, meta_description=? WHERE id=?");
        $stmt->execute([$title, $slug, $content, $metaTitle, $metaDescription, $id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO pages (title, slug, content, meta_title, meta_description) VALUES (?,?,?,?,?)");
        $stmt->execute([$title, $slug, $content, $metaTitle, $metaDescription]);
    }
    $message = 'صفحه ذخیره شد.';
}

$pages = $pdo->query("SELECT * FROM pages ORDER BY id")->fetchAll();
$editPage = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editPage = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت صفحات</title>
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
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 12px; text-decoration: none; display: inline-block; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-sm { padding: 4px 10px; font-size: 10px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 12px; color: var(--text-secondary); }
        .form-group input, .form-group textarea { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-family: 'Vazirmatn', sans-serif; font-size: 13px; }
        .form-group textarea { resize: vertical; min-height: 200px; }
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
        <a href="coupons.php">🎫 کوپن‌ها</a>
        <a href="reviews.php">💬 نظرات</a>
        <a href="pages.php" class="active">📄 صفحات</a>
        <a href="settings.php">⚙️ تنظیمات</a>
        <a href="logout.php" style="color: var(--danger);">🚪 خروج</a>
    </aside>

    <main class="main">
        <?php if($message): ?>
        <div class="alert">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h3>📄 صفحات موجود</h3>
                <table>
                    <thead><tr><th>عنوان</th><th>slug</th><th>عملیات</th></tr></thead>
                    <tbody>
                        <?php foreach($pages as $page): ?>
                        <tr>
                            <td><?php echo $page['title']; ?></td>
                            <td><code style="background:var(--bg);padding:2px 8px;border-radius:4px;"><?php echo $page['slug']; ?></code></td>
                            <td><a href="?edit=<?php echo $page['id']; ?>" class="btn btn-outline btn-sm">✏️ ویرایش</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card">
                <h3><?php echo $editPage ? '✏️ ویرایش صفحه' : '➕ صفحه جدید'; ?></h3>
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $editPage['id'] ?? 0; ?>">
                    <div class="form-group">
                        <label>عنوان *</label>
                        <input type="text" name="title" required value="<?php echo $editPage['title'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>slug *</label>
                        <input type="text" name="slug" required value="<?php echo $editPage['slug'] ?? ''; ?>" placeholder="about">
                    </div>
                    <div class="form-group">
                        <label>عنوان متا</label>
                        <input type="text" name="meta_title" value="<?php echo $editPage['meta_title'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>توضیحات متا</label>
                        <textarea name="meta_description" rows="2"><?php echo $editPage['meta_description'] ?? ''; ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>محتوا (HTML)</label>
                        <textarea name="content" rows="10"><?php echo $editPage['content'] ?? ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">💾 ذخیره</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>