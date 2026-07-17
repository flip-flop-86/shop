<?php
require_once __DIR__ . '/includes/header.php';

$slug = $_GET['slug'] ?? '';
if(empty($slug)) {
    header('Location: /products');
    exit;
}

$category = getCategoryBySlug($slug);
if(!$category) {
    header('HTTP/1.0 404 Not Found');
    echo '<div class="container"><div class="empty-state"><div class="empty-state-icon">🔍</div><h3>دسته‌بندی یافت نشد</h3></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $category['name'];

// فیلترها
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$sort = $_GET['sort'] ?? 'newest';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$inStock = $_GET['in_stock'] ?? '';

$filters = [
    'category_id' => $category['id'],
    'sort' => $sort
];

if($minPrice !== '') $filters['min_price'] = intval($minPrice);
if($maxPrice !== '') $filters['max_price'] = intval($maxPrice);
if($inStock === '1') $filters['in_stock'] = true;

$products = getProducts($filters);
$totalProducts = count($products);

// Pagination (simple)
$perPage = 12;
$totalPages = ceil($totalProducts / $perPage);
$offset = ($page - 1) * $perPage;
$filters['limit'] = $perPage;
$products = getProducts($filters);
?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <a href="/products">محصولات</a>
        <span>/</span>
        <span><?php echo $category['name']; ?></span>
    </div>

    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 24px;">
        <!-- Sidebar Filters -->
        <aside class="filters-sidebar">
            <h3 style="margin-bottom: 20px;">🔍 فیلترها</h3>
            
            <form method="GET" action="/category/<?php echo $slug; ?>">
                <!-- Sort -->
                <div class="filter-section">
                    <div class="filter-title">مرتب‌سازی</div>
                    <select name="sort" style="width: 100%; padding: 8px; border: 2px solid var(--gray-200); border-radius: var(--radius); font-family: var(--font);">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>جدیدترین</option>
                        <option value="bestselling" <?php echo $sort === 'bestselling' ? 'selected' : ''; ?>>پرفروش‌ترین</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>ارزان‌ترین</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>گران‌ترین</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>محبوب‌ترین</option>
                    </select>
                </div>

                <!-- Price Range -->
                <div class="filter-section">
                    <div class="filter-title">محدوده قیمت</div>
                    <div class="price-range-inputs">
                        <input type="number" name="min_price" placeholder="از" value="<?php echo $minPrice; ?>">
                        <input type="number" name="max_price" placeholder="تا" value="<?php echo $maxPrice; ?>">
                    </div>
                </div>

                <!-- Stock -->
                <div class="filter-section">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="in_stock" value="1" <?php echo $inStock === '1' ? 'checked' : ''; ?>>
                        <span>فقط کالاهای موجود</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-block">اعمال فیلترها</button>
                <a href="/category/<?php echo $slug; ?>" class="btn btn-outline btn-block" style="margin-top: 8px;">حذف فیلترها</a>
            </form>
        </aside>

        <!-- Products -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h2 style="font-size: 20px;"><?php echo $category['icon']; ?> <?php echo $category['name']; ?></h2>
                <span style="font-size: 13px; color: var(--gray-500);"><?php echo $totalProducts; ?> محصول</span>
            </div>

            <?php if(!empty($products)): ?>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php if($page > 1): ?>
                <a href="?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>&in_stock=<?php echo $inStock; ?>">«</a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>&in_stock=<?php echo $inStock; ?>" 
                   class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
                
                <?php if($page < $totalPages): ?>
                <a href="?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>&min_price=<?php echo $minPrice; ?>&max_price=<?php echo $maxPrice; ?>&in_stock=<?php echo $inStock; ?>">»</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h3>محصولی یافت نشد</h3>
                <p>در این دسته‌بندی محصولی وجود ندارد.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>