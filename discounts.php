<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'تخفیف‌های ویژه';
$products = getProducts(['discounted' => true, 'sort' => 'newest', 'in_stock' => true]);
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <span>تخفیف‌ها</span>
    </div>

    <div style="background: linear-gradient(135deg, #ef4444, #f59e0b); border-radius: var(--radius-xl); padding: 30px; color: white; text-align: center; margin-bottom: 30px;">
        <h1 style="font-size: 28px;">🔥 تخفیف‌های ویژه</h1>
        <p style="opacity: 0.9;">فرصت عالی برای خرید با بهترین قیمت</p>
    </div>

    <?php if(!empty($products)): ?>
    <div class="products-grid">
        <?php foreach($products as $product): ?>
            <?php include __DIR__ . '/includes/product-card.php'; ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-state-icon">🏷️</div>
        <h3>تخفیفی فعال نیست</h3>
        <p>در حال حاضر محصول تخفیف‌داری موجود نیست.</p>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>