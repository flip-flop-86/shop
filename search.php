<?php
require_once __DIR__ . '/includes/header.php';

$query = $_GET['q'] ?? '';
$pageTitle = 'جستجو: ' . $query;

$products = [];
if(!empty($query)) {
    $products = getProducts(['search' => $query, 'sort' => $_GET['sort'] ?? 'newest']);
}
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <span>جستجو</span>
    </div>

    <h1 class="section-title">🔍 نتایج جستجو برای "<?php echo sanitize($query); ?>"</h1>
    
    <?php if(!empty($query)): ?>
        <?php if(!empty($products)): ?>
        <p style="margin-bottom: 20px; color: var(--gray-500);"><?php echo count($products); ?> محصول یافت شد</p>
        <div class="products-grid">
            <?php foreach($products as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <h3>نتیجه‌ای یافت نشد</h3>
            <p>محصولی با این کلمه پیدا نکردیم. عبارت دیگری جستجو کنید.</p>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <h3>جستجو کنید</h3>
            <p>نام محصول یا کلمه مورد نظر را وارد کنید.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>