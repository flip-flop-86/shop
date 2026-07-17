<?php
require_once __DIR__ . '/includes/header.php';

$featuredProducts = getFeaturedProducts(8);
$discountedProducts = getDiscountedProducts(8);
$newProducts = getNewProducts(8);
$bestsellers = getBestSellingProducts(4);
$categories = getCategories();
$showHero = getSetting('hero_show', '1') === '1';
?>

<?php if($showHero): ?>
<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1><?php echo getSetting('hero_title', 'به فروشگاه ما خوش آمدید'); ?></h1>
            <p><?php echo getSetting('hero_description', 'بهترین محصولات با بهترین قیمت'); ?></p>
            <a href="/products" class="btn btn-primary btn-lg">
                <?php echo getSetting('hero_button_text', 'مشاهده محصولات'); ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container">
    <!-- Categories -->
    <?php if(!empty($categories)): ?>
    <section style="margin-bottom: 40px;">
        <h2 class="section-title">
            📂 دسته‌بندی‌ها
            <a href="/products">مشاهده همه</a>
        </h2>
        <div class="categories-grid">
            <?php foreach($categories as $cat): ?>
            <a href="/category/<?php echo $cat['slug']; ?>" class="category-card">
                <div class="category-card-icon"><?php echo $cat['icon']; ?></div>
                <div class="category-card-title"><?php echo $cat['name']; ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Featured Products -->
    <?php if(!empty($featuredProducts)): ?>
    <section style="margin-bottom: 40px;">
        <h2 class="section-title">
            ⭐ محصولات ویژه
            <a href="/products?featured=1">مشاهده همه</a>
        </h2>
        <div class="products-grid">
            <?php foreach($featuredProducts as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Discount Banner -->
    <?php if(!empty($discountedProducts)): ?>
    <section style="margin-bottom: 40px;">
        <div style="background: linear-gradient(135deg, #ef4444, #f59e0b); border-radius: var(--radius-xl); padding: 30px; color: white; text-align: center;">
            <h2 style="font-size: 24px; margin-bottom: 8px;">🔥 تخفیف‌های ویژه</h2>
            <p style="margin-bottom: 15px; opacity: 0.9;">تا ۷۰٪ تخفیف روی محصولات منتخب</p>
            <a href="/discounts" class="btn" style="background: white; color: #ef4444; font-weight: 700;">مشاهده تخفیف‌ها</a>
        </div>
    </section>
    <?php endif; ?>

    <!-- Discounted Products -->
    <?php if(!empty($discountedProducts)): ?>
    <section style="margin-bottom: 40px;">
        <h2 class="section-title">
            🏷️ محصولات تخفیف‌دار
            <a href="/discounts">مشاهده همه</a>
        </h2>
        <div class="products-grid">
            <?php foreach($discountedProducts as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- New Products -->
    <?php if(!empty($newProducts)): ?>
    <section style="margin-bottom: 40px;">
        <h2 class="section-title">
            🆕 جدیدترین محصولات
            <a href="/products?sort=newest">مشاهده همه</a>
        </h2>
        <div class="products-grid">
            <?php foreach($newProducts as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Best Sellers -->
    <?php if(!empty($bestsellers)): ?>
    <section style="margin-bottom: 40px;">
        <h2 class="section-title">
            🏆 پرفروش‌ترین‌ها
            <a href="/products?sort=bestselling">مشاهده همه</a>
        </h2>
        <div class="products-grid">
            <?php foreach($bestsellers as $product): ?>
                <?php include __DIR__ . '/includes/product-card.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features -->
    <section style="margin-bottom: 40px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div style="background: white; border-radius: var(--radius-lg); padding: 24px; text-align: center; box-shadow: var(--shadow-sm);">
                <div style="font-size: 40px; margin-bottom: 10px;">🚚</div>
                <h4 style="margin-bottom: 6px;">ارسال سریع</h4>
                <p style="font-size: 13px; color: #666;">تحویل ۲۴ ساعته در تهران</p>
            </div>
            <div style="background: white; border-radius: var(--radius-lg); padding: 24px; text-align: center; box-shadow: var(--shadow-sm);">
                <div style="font-size: 40px; margin-bottom: 10px;">🔒</div>
                <h4 style="margin-bottom: 6px;">پرداخت امن</h4>
                <p style="font-size: 13px; color: #666;">درگاه پرداخت مطمئن</p>
            </div>
            <div style="background: white; border-radius: var(--radius-lg); padding: 24px; text-align: center; box-shadow: var(--shadow-sm);">
                <div style="font-size: 40px; margin-bottom: 10px;">✅</div>
                <h4 style="margin-bottom: 6px;">ضمانت اصالت</h4>
                <p style="font-size: 13px; color: #666;">تضمین کیفیت کالا</p>
            </div>
            <div style="background: white; border-radius: var(--radius-lg); padding: 24px; text-align: center; box-shadow: var(--shadow-sm);">
                <div style="font-size: 40px; margin-bottom: 10px;">🔄</div>
                <h4 style="margin-bottom: 6px;">بازگشت آسان</h4>
                <p style="font-size: 13px; color: #666;">۷ روز ضمانت بازگشت</p>
            </div>
        </div>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>