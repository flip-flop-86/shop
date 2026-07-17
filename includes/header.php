<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$categories = getCategories();
$cartCount = isLoggedIn() ? getCartCount() : 0;
$wishlistCount = isLoggedIn() ? getWishlistCount() : 0;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo getSetting('site_description', 'فروشگاه آنلاین'); ?>">
    <meta name="theme-color" content="<?php echo getSetting('primary_color', '#4f46e5'); ?>">
    <title><?php echo $pageTitle ?? SITE_NAME; ?> | <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'><?php echo getSetting('site_favicon', '🛒'); ?></text></svg>">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
    <?php if(getSetting('theme_mode') === 'dark'): ?>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/dark.css">
    <?php endif; ?>
    
    <!-- Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
</head>
<body class="<?php echo getSetting('theme_mode', 'light') === 'dark' ? 'dark-mode' : ''; ?>">
    
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-left">
                <a href="tel:<?php echo getSetting('contact_phone', ''); ?>">
                    📞 <?php echo getSetting('contact_phone', ''); ?>
                </a>
                <span class="separator">|</span>
                <span>🚚 ارسال رایگان بالای <?php echo formatPrice(getSetting('free_shipping_min', 500000)); ?></span>
            </div>
            <div class="top-bar-right">
                <?php if(isLoggedIn()): ?>
                    <a href="/profile">👤 <?php echo sanitize($_SESSION['user_fullname'] ?? 'کاربر'); ?></a>
                    <span class="separator">|</span>
                    <a href="/orders">📋 سفارشات</a>
                    <span class="separator">|</span>
                    <a href="/logout">🚪 خروج</a>
                <?php else: ?>
                    <a href="/login">🔐 ورود / ثبت‌نام</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <button class="mobile-menu-btn" id="mobileMenuBtn">☰</button>
            
            <a href="/" class="logo">
                <span class="logo-icon"><?php echo getSetting('site_logo', '🛒'); ?></span>
                <span class="logo-text"><?php echo SITE_NAME; ?></span>
            </a>
            
            <div class="search-box">
                <form action="/search" method="GET">
                    <input type="text" name="q" placeholder="جستجو در محصولات..." value="<?php echo isset($_GET['q']) ? sanitize($_GET['q']) : ''; ?>">
                    <button type="submit">🔍</button>
                </form>
            </div>
            
            <div class="header-actions">
                <a href="/cart" class="header-action cart-action">
                    🛒
                    <span class="badge cart-count"><?php echo $cartCount; ?></span>
                </a>
                <?php if(isLoggedIn()): ?>
                <a href="/wishlist" class="header-action">
                    ❤️
                    <span class="badge wishlist-count"><?php echo $wishlistCount; ?></span>
                </a>
                <?php endif; ?>
                <button class="theme-toggle" id="themeToggle" title="تغییر تم">
                    <?php echo getSetting('theme_mode', 'light') === 'dark' ? '☀️' : '🌙'; ?>
                </button>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav" id="mainNav">
        <div class="container">
            <ul class="nav-list">
                <li><a href="/" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>">🏠 خانه</a></li>
                <li><a href="/products" class="<?php echo $currentPage == 'products.php' ? 'active' : ''; ?>">📦 همه محصولات</a></li>
                
                <?php foreach($categories as $cat): ?>
                <li><a href="/category/<?php echo $cat['slug']; ?>"><?php echo $cat['icon'] . ' ' . $cat['name']; ?></a></li>
                <?php endforeach; ?>
                
                <li><a href="/discounts">🏷️ تخفیف‌ها</a></li>
            </ul>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-header">
            <span>منو</span>
            <button id="closeMobileMenu">✕</button>
        </div>
        <ul>
            <li><a href="/">🏠 خانه</a></li>
            <li><a href="/products">📦 محصولات</a></li>
            <?php foreach($categories as $cat): ?>
            <li><a href="/category/<?php echo $cat['slug']; ?>"><?php echo $cat['icon'] . ' ' . $cat['name']; ?></a></li>
            <?php endforeach; ?>
            <li><a href="/discounts">🏷️ تخفیف‌ها</a></li>
            <li><a href="/about">📄 درباره ما</a></li>
            <li><a href="/contact">📧 تماس با ما</a></li>
        </ul>
    </div>

    <!-- Main Content Wrapper -->
    <main class="main-content">