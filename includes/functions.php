<?php
// ==========================================
// توابع عمومی
// ==========================================

function getCategories($onlyActive = true) {
    global $pdo;
    $sql = "SELECT * FROM categories";
    if ($onlyActive) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY sort_order ASC";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getCategoryById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getCategoryBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProducts($filters = []) {
    global $pdo;
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1";
    $params = [];
    
    if (isset($filters['category_id'])) {
        $sql .= " AND p.category_id = ?";
        $params[] = $filters['category_id'];
    }
    
    if (isset($filters['search'])) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.tags LIKE ?)";
        $search = '%' . $filters['search'] . '%';
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
    }
    
    if (isset($filters['min_price'])) {
        $sql .= " AND (p.price - (p.price * p.discount_percent / 100)) >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (isset($filters['max_price'])) {
        $sql .= " AND (p.price - (p.price * p.discount_percent / 100)) <= ?";
        $params[] = $filters['max_price'];
    }
    
    if (isset($filters['featured'])) {
        $sql .= " AND p.is_featured = 1";
    }
    
    if (isset($filters['discounted'])) {
        $sql .= " AND p.discount_percent > 0";
    }
    
    if (isset($filters['in_stock'])) {
        $sql .= " AND p.stock > 0";
    }
    
    // مرتب‌سازی
    $orderBy = $filters['sort'] ?? 'newest';
    switch($orderBy) {
        case 'price_asc':
            $sql .= " ORDER BY (p.price - (p.price * p.discount_percent / 100)) ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY (p.price - (p.price * p.discount_percent / 100)) DESC";
            break;
        case 'bestselling':
            $sql .= " ORDER BY p.sales_count DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY p.rating DESC";
            break;
        case 'newest':
        default:
            $sql .= " ORDER BY p.created_at DESC";
            break;
    }
    
    // محدودیت
    if (isset($filters['limit'])) {
        $sql .= " LIMIT " . intval($filters['limit']);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.slug = ? AND p.is_active = 1
    ");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProductImages($productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getProductSpecs($productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM product_specs WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getRelatedProducts($productId, $categoryId, $limit = 4) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT * FROM products 
        WHERE category_id = ? AND id != ? AND is_active = 1 
        ORDER BY RAND() 
        LIMIT ?
    ");
    $stmt->execute([$categoryId, $productId, $limit]);
    return $stmt->fetchAll();
}

function getFeaturedProducts($limit = 8) {
    return getProducts(['featured' => true, 'limit' => $limit, 'in_stock' => true]);
}

function getDiscountedProducts($limit = 8) {
    return getProducts(['discounted' => true, 'limit' => $limit, 'in_stock' => true]);
}

function getNewProducts($limit = 8) {
    return getProducts(['sort' => 'newest', 'limit' => $limit, 'in_stock' => true]);
}

function getBestSellingProducts($limit = 8) {
    return getProducts(['sort' => 'bestselling', 'limit' => $limit, 'in_stock' => true]);
}

function getCartItems($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ci.*, p.name, p.slug, p.price, p.discount_percent, p.image_main, p.stock, p.is_active
        FROM cart_items ci 
        JOIN products p ON ci.product_id = p.id 
        WHERE ci.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function addToCart($userId, $productId, $quantity = 1) {
    global $pdo;
    
    // چک موجودی
    $product = getProductById($productId);
    if (!$product || $product['stock'] <= 0 || !$product['is_active']) {
        return ['success' => false, 'message' => 'محصول ناموجود است'];
    }
    
    // چک اگر قبلاً در سبد هست
    $stmt = $pdo->prepare("SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $newQty = $existing['quantity'] + $quantity;
        if ($newQty > $product['stock']) {
            return ['success' => false, 'message' => 'موجودی کافی نیست'];
        }
        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        if ($quantity > $product['stock']) {
            return ['success' => false, 'message' => 'موجودی کافی نیست'];
        }
        $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $productId, $quantity]);
    }
    
    return ['success' => true, 'count' => getCartCount()];
}

function updateCartQuantity($userId, $productId, $quantity) {
    global $pdo;
    $product = getProductById($productId);
    
    if ($quantity <= 0) {
        return removeFromCart($userId, $productId);
    }
    
    if ($quantity > $product['stock']) {
        return ['success' => false, 'message' => 'موجودی کافی نیست'];
    }
    
    $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$quantity, $userId, $productId]);
    return ['success' => true];
}

function removeFromCart($userId, $productId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return ['success' => true];
}

function clearCart($userId) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
    $stmt->execute([$userId]);
}

function isInWishlist($userId, $productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    return $stmt->fetch() ? true : false;
}

function toggleWishlist($userId, $productId) {
    global $pdo;
    if (isInWishlist($userId, $productId)) {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return 'removed';
    } else {
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$userId, $productId]);
        return 'added';
    }
}

function getWishlist($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT w.*, p.name, p.slug, p.price, p.discount_percent, p.image_main 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        WHERE w.user_id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getReviews($productId, $limit = 10) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, u.fullname, u.avatar 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.product_id = ? AND r.is_approved = 1 
        ORDER BY r.created_at DESC 
        LIMIT ?
    ");
    $stmt->execute([$productId, $limit]);
    return $stmt->fetchAll();
}

function getAverageRating($productId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as count FROM reviews WHERE product_id = ? AND is_approved = 1");
    $stmt->execute([$productId]);
    return $stmt->fetch();
}

function getUserAddresses($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getOrders($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getOrderById($orderId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetch();
}

function getOrderItems($orderId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

function applyCoupon($code, $orderTotal) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expire_at IS NULL OR expire_at > NOW())");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    
    if (!$coupon) {
        return ['success' => false, 'message' => 'کد تخفیف نامعتبر است'];
    }
    
    if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
        return ['success' => false, 'message' => 'سقف استفاده از کد تخفیف تمام شده است'];
    }
    
    if ($orderTotal < $coupon['min_order']) {
        return ['success' => false, 'message' => 'حداقل خرید برای این کد تخفیف: ' . formatPrice($coupon['min_order'])];
    }
    
    $discount = 0;
    if ($coupon['type'] === 'percent') {
        $discount = $orderTotal * $coupon['value'] / 100;
        if ($coupon['max_discount'] > 0) {
            $discount = min($discount, $coupon['max_discount']);
        }
    } else {
        $discount = $coupon['value'];
    }
    
    return ['success' => true, 'discount' => $discount, 'coupon' => $coupon];
}

function getPage($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ? AND is_active = 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}
?>