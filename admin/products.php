<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// حذف محصول
if(isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("SELECT image_main FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if($product && $product['image_main']) {
        deleteImage($product['image_main']);
    }
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    logAdminAction('delete_product', "محصول #$id حذف شد");
    $message = 'محصول با موفقیت حذف شد.';
    $action = 'list';
}

// ذخیره محصول (جدید یا ویرایش)
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $data = [
        'category_id' => intval($_POST['category_id']),
        'name' => sanitize($_POST['name']),
        'slug' => generateSlug($_POST['name']),
        'sku' => sanitize($_POST['sku']),
        'price' => intval($_POST['price']),
        'discount_percent' => intval($_POST['discount_percent']),
        'stock' => intval($_POST['stock']),
        'weight' => floatval($_POST['weight']),
        'short_description' => sanitize($_POST['short_description']),
        'description' => $_POST['description'],
        'tags' => sanitize($_POST['tags']),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'is_special' => isset($_POST['is_special']) ? 1 : 0,
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'meta_title' => sanitize($_POST['meta_title']),
        'meta_description' => sanitize($_POST['meta_description']),
    ];

    // آپلود تصویر اصلی
    if(isset($_FILES['image_main']) && $_FILES['image_main']['size'] > 0) {
        $upload = uploadImage($_FILES['image_main'], 'products');
        if($upload['success']) {
            // حذف تصویر قبلی
            if($id > 0) {
                $old = $pdo->query("SELECT image_main FROM products WHERE id = $id")->fetch();
                if($old && $old['image_main']) deleteImage($old['image_main']);
            }
            $data['image_main'] = $upload['path'];
        }
    }

    if($id > 0) {
        // ویرایش
        $stmt = $pdo->prepare("UPDATE products SET category_id=?, name=?, slug=?, sku=?, price=?, discount_percent=?, stock=?, weight=?, short_description=?, description=?, tags=?, is_featured=?, is_special=?, is_active=?, meta_title=?, meta_description=?, image_main=COALESCE(?, image_main), updated_at=NOW() WHERE id=?");
        $stmt->execute([
            $data['category_id'], $data['name'], $data['slug'], $data['sku'],
            $data['price'], $data['discount_percent'], $data['stock'], $data['weight'],
            $data['short_description'], $data['description'], $data['tags'],
            $data['is_featured'], $data['is_special'], $data['is_active'],
            $data['meta_title'], $data['meta_description'],
            $data['image_main'] ?? null, $id
        ]);
        logAdminAction('edit_product', "محصول #$id ویرایش شد");
        $message = 'محصول با موفقیت ویرایش شد.';
    } else {
        // افزودن جدید
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, sku, price, discount_percent, stock, weight, short_description, description, tags, is_featured, is_special, is_active, meta_title, meta_description, image_main) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['category_id'], $data['name'], $data['slug'], $data['sku'],
            $data['price'], $data['discount_percent'], $data['stock'], $data['weight'],
            $data['short_description'], $data['description'], $data['tags'],
            $data['is_featured'], $data['is_special'], $data['is_active'],
            $data['meta_title'], $data['meta_description'],
            $data['image_main'] ?? null
        ]);
        $newId = $pdo->lastInsertId();
        logAdminAction('add_product', "محصول #$newId افزوده شد");
        $message = 'محصول جدید با موفقیت افزوده شد.';
    }

    // ذخیره مشخصات فنی
    if(isset($_POST['spec_keys']) && is_array($_POST['spec_keys'])) {
        if($id > 0 || isset($newId)) {
            $productId = $id > 0 ? $id : $newId;
            $pdo->prepare("DELETE FROM product_specs WHERE product_id = ?")->execute([$productId]);
            
            $specKeys = $_POST['spec_keys'];
            $specValues = $_POST['spec_values'];
            
            $stmt = $pdo->prepare("INSERT INTO product_specs (product_id, spec_key, spec_value) VALUES (?, ?, ?)");
            foreach($specKeys as $i => $key) {
                if(!empty($key) && !empty($specValues[$i])) {
                    $stmt->execute([$productId, sanitize($key), sanitize($specValues[$i])]);
                }
            }
        }
    }

    // آپلود تصاویر گالری
    if(isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        if($id > 0 || isset($newId)) {
            $productId = $id > 0 ? $id : $newId;
            $files = $_FILES['gallery_images'];
            
            for($i = 0; $i < count($files['name']); $i++) {
                if($files['size'][$i] > 0) {
                    $file = [
                        'name' => $files['name'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'size' => $files['size'][$i],
                        'error' => $files['error'][$i]
                    ];
                    $upload = uploadImage($file, 'products');
                    if($upload['success']) {
                        $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_path, sort_order) VALUES (?, ?, ?)");
                        $stmt->execute([$productId, $upload['path'], $i]);
                    }
                }
            }
        }
    }

    $action = 'list';
}

$products = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
$categories = getCategories();
$editProduct = null;

if($action === 'edit' && isset($_GET['id'])) {
    $editId = intval($_GET['id']);
    $editProduct = getProductById($editId);
    if($editProduct) {
        $editProduct['specs'] = getProductSpecs($editId);
        $editProduct['images'] = getProductImages($editId);
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت محصولات</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a; --surface: #1e293b; --border: #334155;
            --text: #e2e8f0; --text-secondary: #94a3b8; --primary: #6366f1;
            --success: #10b981; --danger: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Vazirmatn', sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; }
        .sidebar { width: 250px; background: var(--surface); border-left: 1px solid var(--border); padding: 20px; position: fixed; top: 0; right: 0; height: 100vh; overflow-y: auto; }
        .sidebar h2 { font-size: 18px; margin-bottom: 24px; text-align: center; color: var(--primary); }
        .sidebar a { display: block; padding: 10px 14px; color: var(--text-secondary); text-decoration: none; border-radius: 8px; margin-bottom: 4px; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(99,102,241,0.15); color: var(--text); }
        .main { flex: 1; margin-right: 250px; padding: 24px; }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 24px; margin-bottom: 20px; }
        .card h3 { margin-bottom: 20px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; padding: 10px; font-size: 11px; color: var(--text-secondary); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        td { padding: 10px; font-size: 13px; border-bottom: 1px solid var(--border); }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-family: 'Vazirmatn', sans-serif; font-size: 12px; text-decoration: none; display: inline-block; transition: all 0.3s; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { opacity: 0.9; }
        .btn-danger { background: var(--danger); color: white; }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-sm { padding: 4px 10px; font-size: 10px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 12px; color: var(--text-secondary); }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; color: var(--text); font-family: 'Vazirmatn', sans-serif; font-size: 13px; }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
        .alert-success { background: rgba(16,185,129,0.15); color: var(--success); border: 1px solid rgba(16,185,129,0.3); }
        .spec-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
        .spec-row input { flex: 1; }
        .image-preview { width: 80px; height: 80px; border-radius: 8px; object-fit: cover; background: var(--bg); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <h2>🔐 پنل مدیریت</h2>
        <a href="dashboard.php">📊 داشبورد</a>
        <a href="products.php" class="active">📦 محصولات</a>
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
        <?php if($message): ?>
        <div class="alert alert-success">✅ <?php echo $message; ?></div>
        <?php endif; ?>

        <?php if($action === 'add' || $action === 'edit'): ?>
        <!-- فرم افزودن/ویرایش -->
        <div class="card">
            <h3><?php echo $action === 'edit' ? '✏️ ویرایش محصول' : '➕ افزودن محصول جدید'; ?></h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $editProduct['id'] ?? 0; ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>نام محصول *</label>
                        <input type="text" name="name" required value="<?php echo $editProduct['name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>دسته‌بندی *</label>
                        <select name="category_id" required>
                            <option value="">انتخاب کنید...</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($editProduct['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label>قیمت (تومان) *</label>
                        <input type="number" name="price" required value="<?php echo $editProduct['price'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>تخفیف (%)</label>
                        <input type="number" name="discount_percent" value="<?php echo $editProduct['discount_percent'] ?? 0; ?>" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <label>موجودی</label>
                        <input type="number" name="stock" value="<?php echo $editProduct['stock'] ?? 0; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>کد محصول (SKU)</label>
                        <input type="text" name="sku" value="<?php echo $editProduct['sku'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label>وزن (گرم)</label>
                        <input type="number" name="weight" value="<?php echo $editProduct['weight'] ?? 0; ?>" step="0.01">
                    </div>
                </div>

                <div class="form-group">
                    <label>توضیح کوتاه</label>
                    <textarea name="short_description" rows="2"><?php echo $editProduct['short_description'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>توضیحات کامل</label>
                    <textarea name="description" rows="5"><?php echo $editProduct['description'] ?? ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label>برچسب‌ها (با کاما جدا کنید)</label>
                    <input type="text" name="tags" value="<?php echo $editProduct['tags'] ?? ''; ?>" placeholder="مثلاً: پرفروش,جدید,تخفیف">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>تصویر اصلی</label>
                        <input type="file" name="image_main" accept="image/*">
                        <?php if(!empty($editProduct['image_main'])): ?>
                        <img src="<?php echo $editProduct['image_main']; ?>" class="image-preview" style="margin-top:8px;">
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label>تصاویر گالری</label>
                        <input type="file" name="gallery_images[]" accept="image/*" multiple>
                        <?php if(!empty($editProduct['images'])): ?>
                        <div style="display:flex;gap:8px;margin-top:8px;">
                            <?php foreach($editProduct['images'] as $img): ?>
                            <img src="<?php echo $img['image_path']; ?>" class="image-preview">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- مشخصات فنی -->
                <div class="form-group">
                    <label>مشخصات فنی</label>
                    <div id="specsContainer">
                        <?php if(!empty($editProduct['specs'])): ?>
                            <?php foreach($editProduct['specs'] as $spec): ?>
                            <div class="spec-row">
                                <input type="text" name="spec_keys[]" placeholder="عنوان" value="<?php echo $spec['spec_key']; ?>">
                                <input type="text" name="spec_values[]" placeholder="مقدار" value="<?php echo $spec['spec_value']; ?>">
                                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <div class="spec-row">
                            <input type="text" name="spec_keys[]" placeholder="عنوان">
                            <input type="text" name="spec_values[]" placeholder="مقدار">
                            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="addSpecRow()" style="margin-top:8px;">➕ افزودن مشخصه</button>
                </div>

                <div class="form-row-3">
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_featured" <?php echo ($editProduct['is_featured'] ?? false) ? 'checked' : ''; ?>>
                            محصول ویژه
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                            <input type="checkbox" name="is_active" <?php echo ($editProduct['is_active'] ?? true) ? 'checked' : ''; ?>>
                            فعال
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>عنوان متا (SEO)</label>
                    <input type="text" name="meta_title" value="<?php echo $editProduct['meta_title'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label>توضیحات متا (SEO)</label>
                    <textarea name="meta_description" rows="2"><?php echo $editProduct['meta_description'] ?? ''; ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">💾 ذخیره محصول</button>
                <a href="products.php" class="btn btn-outline">انصراف</a>
            </form>
        </div>

        <?php else: ?>
        <!-- لیست محصولات -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>📦 محصولات (<?php echo count($products); ?>)</h2>
            <a href="?action=add" class="btn btn-primary">➕ افزودن محصول جدید</a>
        </div>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>تصویر</th>
                        <th>نام</th>
                        <th>دسته</th>
                        <th>قیمت</th>
                        <th>تخفیف</th>
                        <th>موجودی</th>
                        <th>ویژه</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): ?>
                    <tr>
                        <td>#<?php echo $p['id']; ?></td>
                        <td>
                            <?php if($p['image_main']): ?>
                            <img src="<?php echo $p['image_main']; ?>" class="image-preview" style="width:40px;height:40px;">
                            <?php else: ?>
                            📦
                            <?php endif; ?>
                        </td>
                        <td><?php echo $p['name']; ?></td>
                        <td><?php echo $p['category_name'] ?? '-'; ?></td>
                        <td><?php echo formatPrice($p['price']); ?></td>
                        <td><?php echo $p['discount_percent'] > 0 ? "٪{$p['discount_percent']}" : '-'; ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><?php echo $p['is_featured'] ? '⭐' : '-'; ?></td>
                        <td><?php echo $p['is_active'] ? '✅' : '❌'; ?></td>
                        <td>
                            <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-outline btn-sm">✏️</a>
                            <a href="?delete=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('مطمئنی؟')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>

    <script>
    function addSpecRow() {
        const container = document.getElementById('specsContainer');
        const row = document.createElement('div');
        row.className = 'spec-row';
        row.innerHTML = `
            <input type="text" name="spec_keys[]" placeholder="عنوان">
            <input type="text" name="spec_values[]" placeholder="مقدار">
            <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
        `;
        container.appendChild(row);
    }
    </script>
</body>
</html>