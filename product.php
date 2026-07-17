<?php
require_once __DIR__ . '/includes/header.php';

// دریافت محصول از URL
$slug = $_GET['slug'] ?? '';
if(empty($slug)) {
    header('Location: /products');
    exit;
}

$product = getProductBySlug($slug);
if(!$product) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = 'محصول یافت نشد';
    echo '<div class="container"><div class="empty-state"><div class="empty-state-icon">🔍</div><h3>محصول یافت نشد</h3><p>محصول مورد نظر در فروشگاه موجود نیست.</p><a href="/products" class="btn btn-primary">مشاهده محصولات</a></div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $product['name'];
$discountedPrice = getDiscountedPrice($product['price'], $product['discount_percent']);
$images = getProductImages($product['id']);
$specs = getProductSpecs($product['id']);
$reviews = getReviews($product['id']);
$ratingData = getAverageRating($product['id']);
$relatedProducts = getRelatedProducts($product['id'], $product['category_id'], 4);
$inWishlist = isLoggedIn() ? isInWishlist($_SESSION['user_id'], $product['id']) : false;
?>

<div class="container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <a href="/products">محصولات</a>
        <span>/</span>
        <?php if($product['category_slug']): ?>
        <a href="/category/<?php echo $product['category_slug']; ?>"><?php echo $product['category_name']; ?></a>
        <span>/</span>
        <?php endif; ?>
        <span><?php echo $product['name']; ?></span>
    </div>

    <!-- Product Detail -->
    <div class="product-detail">
        <!-- Gallery -->
        <div class="product-gallery">
            <div class="product-gallery-main" id="mainImage">
                <?php if($product['image_main']): ?>
                    <img src="<?php echo $product['image_main']; ?>" alt="<?php echo $product['name']; ?>" id="mainImg">
                <?php else: ?>
                    <span style="font-size: 120px;">📦</span>
                <?php endif; ?>
            </div>
            
            <?php if(!empty($images)): ?>
            <div class="product-gallery-thumbs">
                <?php foreach($images as $index => $img): ?>
                <div class="product-gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                     data-image="<?php echo $img['image_path']; ?>"
                     onclick="changeMainImage('<?php echo $img['image_path']; ?>', this)">
                    <img src="<?php echo $img['image_path']; ?>" alt="تصویر <?php echo $index + 1; ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="product-info">
            <h1><?php echo $product['name']; ?></h1>
            
            <div class="product-meta">
                <span>📂 <?php echo $product['category_name'] ?? 'عمومی'; ?></span>
                <?php if($product['sku']): ?>
                <span>🔢 کد: <?php echo $product['sku']; ?></span>
                <?php endif; ?>
                <?php if($product['sales_count'] > 0): ?>
                <span>🛒 <?php echo $product['sales_count']; ?> فروش</span>
                <?php endif; ?>
            </div>

            <?php if($ratingData['count'] > 0): ?>
            <div class="product-card-rating" style="font-size: 14px; margin-bottom: 15px;">
                <?php 
                $avg = round($ratingData['avg_rating'], 1);
                for($i = 1; $i <= 5; $i++) {
                    echo $i <= $avg ? '⭐' : '☆';
                }
                ?>
                <span><?php echo $avg; ?> (<?php echo $ratingData['count']; ?> نظر)</span>
            </div>
            <?php endif; ?>

            <?php if($product['short_description']): ?>
            <p style="color: var(--gray-500); margin-bottom: 15px; font-size: 14px;">
                <?php echo nl2br($product['short_description']); ?>
            </p>
            <?php endif; ?>

            <!-- Price -->
            <div class="product-price-section">
                <?php if($product['discount_percent'] > 0): ?>
                <span class="product-discount-badge">٪<?php echo $product['discount_percent']; ?> تخفیف</span>
                <?php endif; ?>
                
                <div class="product-price-large">
                    <?php echo formatPrice($discountedPrice); ?>
                </div>
                
                <?php if($product['discount_percent'] > 0): ?>
                <div style="margin-top: 5px;">
                    <span style="text-decoration: line-through; color: #999; font-size: 14px;">
                        <?php echo formatPrice($product['price']); ?>
                    </span>
                    <span style="color: var(--success); font-size: 13px; margin-right: 8px;">
                        <?php echo formatPrice($product['price'] - $discountedPrice); ?> صرفه‌جویی
                    </span>
                </div>
                <?php endif; ?>

                <div class="product-stock" style="margin-top: 10px;">
                    <?php if($product['stock'] > 10): ?>
                    <span class="in-stock">✅ موجود در انبار</span>
                    <?php elseif($product['stock'] > 0): ?>
                    <span class="in-stock">⚠️ فقط <?php echo $product['stock']; ?> عدد باقی مانده</span>
                    <?php else: ?>
                    <span class="out-of-stock">❌ ناموجود</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Actions -->
            <?php if($product['stock'] > 0): ?>
            <div class="product-actions">
                <div class="quantity-selector">
                    <button class="minus" type="button">−</button>
                    <input type="number" id="quantity-<?php echo $product['id']; ?>" 
                           value="1" min="1" max="<?php echo $product['stock']; ?>" 
                           readonly>
                    <button class="plus" type="button">+</button>
                </div>
                
                <button class="btn btn-primary btn-lg add-to-cart-btn" 
                        data-product-id="<?php echo $product['id']; ?>"
                        style="flex: 1;">
                    🛒 افزودن به سبد خرید
                </button>
                
                <?php if(isLoggedIn()): ?>
                <button class="btn btn-outline wishlist-btn <?php echo $inWishlist ? 'active' : ''; ?>" 
                        data-product-id="<?php echo $product['id']; ?>"
                        title="<?php echo $inWishlist ? 'حذف از علاقه‌مندی' : 'افزودن به علاقه‌مندی'; ?>">
                    <?php echo $inWishlist ? '❤️' : '🤍'; ?>
                </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Features -->
            <div class="product-features">
                <div class="product-feature">🚚 ارسال سریع</div>
                <div class="product-feature">✅ ضمانت اصالت</div>
                <div class="product-feature">🔄 ۷ روز بازگشت</div>
                <div class="product-feature">🔒 پرداخت امن</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="product-tabs">
        <div class="tab-buttons">
            <button class="tab-btn active" data-tab="description">📝 توضیحات</button>
            <?php if(!empty($specs)): ?>
            <button class="tab-btn" data-tab="specs">📋 مشخصات فنی</button>
            <?php endif; ?>
            <button class="tab-btn" data-tab="reviews">💬 نظرات (<?php echo $ratingData['count']; ?>)</button>
        </div>

        <!-- Description Tab -->
        <div class="tab-content active" id="description">
            <?php if($product['description']): ?>
                <?php echo nl2br($product['description']); ?>
            <?php else: ?>
                <p style="color: #999;">توضیحاتی برای این محصول ثبت نشده است.</p>
            <?php endif; ?>
        </div>

        <!-- Specs Tab -->
        <?php if(!empty($specs)): ?>
        <div class="tab-content" id="specs">
            <table class="specs-table">
                <tbody>
                    <?php foreach($specs as $spec): ?>
                    <tr>
                        <td><?php echo $spec['spec_key']; ?></td>
                        <td><?php echo $spec['spec_value']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Reviews Tab -->
        <div class="tab-content" id="reviews">
            <?php if(isLoggedIn()): ?>
            <div style="margin-bottom: 20px; padding: 16px; background: var(--gray-50); border-radius: var(--radius);">
                <h4 style="margin-bottom: 12px;">نظر خود را بنویسید</h4>
                <form id="reviewForm" onsubmit="submitReview(event, <?php echo $product['id']; ?>)">
                    <div style="margin-bottom: 10px;">
                        <label style="font-size: 13px; margin-bottom: 4px; display: block;">امتیاز:</label>
                        <div class="star-rating" id="starRating">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i === 5 ? 'checked' : ''; ?> hidden>
                            <label for="star<?php echo $i; ?>" style="cursor: pointer; font-size: 24px;" onmouseover="highlightStars(<?php echo $i; ?>)" onmouseout="resetStars()">☆</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" name="title" placeholder="عنوان نظر (اختیاری)" style="margin-bottom: 8px;">
                        <textarea name="comment" rows="3" placeholder="نظر خود را بنویسید..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">📤 ثبت نظر</button>
                </form>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 20px; color: #999;">
                برای ثبت نظر باید <a href="/login" style="color: var(--primary);">وارد شوید</a>
            </div>
            <?php endif; ?>

            <?php if(!empty($reviews)): ?>
                <?php foreach($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">
                            <?php echo mb_substr($review['fullname'] ?? '?', 0, 1); ?>
                        </div>
                        <div>
                            <strong><?php echo $review['fullname'] ?? 'کاربر'; ?></strong>
                            <div class="review-stars">
                                <?php echo str_repeat('⭐', $review['rating']); ?>
                            </div>
                        </div>
                        <span class="review-date"><?php echo $review['created_at']; ?></span>
                    </div>
                    <?php if($review['title']): ?>
                    <h4 style="margin-bottom: 6px;"><?php echo $review['title']; ?></h4>
                    <?php endif; ?>
                    <p class="review-text"><?php echo nl2br($review['comment']); ?></p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 20px;">هنوز نظری ثبت نشده است.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Products -->
    <?php if(!empty($relatedProducts)): ?>
    <section style="margin-bottom: 40px;">
        <h2 class="section-title">محصولات مرتبط</h2>
        <div class="products-grid">
            <?php foreach($relatedProducts as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
function changeMainImage(src, thumb) {
    const mainImg = document.getElementById('mainImg');
    if (mainImg) {
        mainImg.src = src;
    } else {
        document.getElementById('mainImage').innerHTML = `<img src="${src}" alt="Product">`;
    }
    
    document.querySelectorAll('.product-gallery-thumb').forEach(t => t.classList.remove('active'));
    thumb.classList.add('active');
}

function highlightStars(count) {
    const labels = document.querySelectorAll('#starRating label');
    labels.forEach((label, index) => {
        label.textContent = index >= (5 - count) ? '★' : '☆';
        label.style.color = index >= (5 - count) ? '#f59e0b' : '#ccc';
    });
}

function resetStars() {
    const checked = document.querySelector('#starRating input:checked');
    const count = checked ? parseInt(checked.value) : 5;
    highlightStars(count);
}

function submitReview(event, productId) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);
    formData.append('product_id', productId);
    
    fetch('/ajax/submit-review.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('✅ نظر شما ثبت شد و پس از تأیید نمایش داده می‌شود', 'success');
            form.reset();
        } else {
            showToast('❌ ' + data.message, 'error');
        }
    });
}

// Initialize stars
document.addEventListener('DOMContentLoaded', function() {
    resetStars();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>