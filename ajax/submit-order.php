<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید']);
    exit;
}

$userId = $_SESSION['user_id'];
$cartItems = getCartItems($userId);

if(empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'سبد خرید خالی است']);
    exit;
}

// محاسبه مبالغ
$subtotal = 0;
foreach($cartItems as $item) {
    $price = getDiscountedPrice($item['price'], $item['discount_percent']);
    $subtotal += $price * $item['quantity'];
}

$shipping = $subtotal >= intval(getSetting('free_shipping_min', 500000)) ? 0 : 50000;
$tax = floor($subtotal * intval(getSetting('tax_rate', 9)) / 100);
$total = $subtotal + $shipping + $tax;

// آدرس
$addressId = intval($_POST['address_id'] ?? 0);
$useNewAddress = !empty($_POST['new_province']);

if($useNewAddress) {
    // ذخیره آدرس جدید
    $stmt = $pdo->prepare("INSERT INTO user_addresses (user_id, title, province, city, address, postal_code, receiver_name, receiver_phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $_POST['new_address_title'] ?? 'آدرس من',
        $_POST['new_province'],
        $_POST['new_city'],
        $_POST['new_address'],
        $_POST['new_postal_code'] ?? '',
        $_POST['new_receiver_name'],
        $_POST['new_receiver_phone']
    ]);
    $addressId = $pdo->lastInsertId();
}

if($addressId <= 0) {
    echo json_encode(['success' => false, 'message' => 'آدرس را انتخاب کنید']);
    exit;
}

// ایجاد سفارش
$orderNumber = generateOrderNumber();
$notes = sanitize($_POST['notes'] ?? '');
$paymentMethod = $_POST['payment_method'] ?? 'online';

$stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, status, total_amount, shipping_amount, tax_amount, final_amount, payment_method, notes) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)");
$stmt->execute([$orderNumber, $userId, $subtotal, $shipping, $tax, $total, $paymentMethod, $notes]);
$orderId = $pdo->lastInsertId();

// ذخیره آیتم‌های سفارش
foreach($cartItems as $item) {
    $price = getDiscountedPrice($item['price'], $item['discount_percent']);
    $itemSubtotal = $price * $item['quantity'];
    
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$orderId, $item['product_id'], $item['name'], $price, $item['quantity'], $itemSubtotal]);
    
    // کاهش موجودی
    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ?, sales_count = sales_count + ? WHERE id = ?");
    $stmt->execute([$item['quantity'], $item['quantity'], $item['product_id']]);
}

// خالی کردن سبد
clearCart($userId);

echo json_encode(['success' => true, 'order_id' => $orderId]);
?>