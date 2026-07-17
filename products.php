<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'همه محصولات';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$sort = $_GET['sort'] ?? 'newest';
$category = $_GET['category'] ?? '';
$featured = $_GET['featured'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$inStock = $_GET['in_stock'] ?? '';

$filters = ['sort' => $sort];
if($category) $filters['category_id'] = $category;
if($featured) $filters['featured'] = true;
if($minPrice !== '') $filters['min_price'] = intval($minPrice);
if($maxPrice !== '') $filters['max_price'] = intval($maxPrice);
if($inStock === '1') $filters['in_stock'] = true;

$allProducts = getProducts($filters);
$totalProducts = count($allProducts);
$perPage = 12;
$totalPages = ceil($totalProducts / $perPage);
$filters['limit'] = $perPage;
$products = getProducts($filters);

$categories = getCategories();
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <span>محصولات</span>
    </div>

    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 24px;">
        <aside class="filters-sidebar">
            <h3 style="margin-bottom: 20px;">🔍 فیلترها</h3>
            <form method="GET" action="/products">
                <div class="filter-section">
                    <div class="filter-title">دسته‌بندی</div>
                    <select name="category" style="width: 100%; padding: 8px; border: 2px solid var(--gray-200); border-radius: var(--radius); font-family: var(--font);">
                        <option value="">همه</option>
                        <?php foreach($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['icon'] . ' ' . $cat['name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
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
                <div class="filter-section">
                    <div class="filter-title">محدوده قیمت</div>
                    <div class="price-range-inputs">
                        <input type="number" name="min_price" placeholder="از" value="<?php echo $minPrice; ?>">
                        <input type="number" name="max_price" placeholder="تا" value="<?php echo $maxPrice; ?>">
                    </div>
                </div>
                <div class="filter-section">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="in_stock" value="1" <?php echo $inStock === '1' ? 'checked' : ''; ?>>
                        <span>فقط کالاهای موجود</span>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-block">اعمال فیلترها</button>
                <a href="/products" class="btn btn-outline btn-block" style="margin-top: 8px;">حذف فیلترها</a>
            </form>
        </aside>

        <div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2>📦 همه محصولات</h2>
                <span style="font-size: 13px; color: var(--gray-500);"><?php echo $totalProducts; ?> محصول</span>
            </div>

            <?php if(!empty($products)): ?>
            <div class="products-grid">
                <?php foreach($products as $product): ?>
                    <?php include __DIR__ . '/includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php if($totalPages > 1): ?>
            <div class="pagination">
                <?php 
                $queryParams = $_GET;
                for($i = 1; $i <= $totalPages; $i++): 
                    $queryParams['page'] = $i;
                    $url = '/products?' . http_build_query($queryParams);
                ?>
                <a href="<?php echo $url; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📦</div>
                <h3>محصولی یافت نشد</h3>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>