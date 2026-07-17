<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید']);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

$result = updateCartQuantity($_SESSION['user_id'], $productId, $quantity);

if($result['success']) {
    $item = getCartItems($_SESSION['user_id']);
    $foundItem = null;
    foreach($item as $i) {
        if($i['product_id'] == $productId) {
            $foundItem = $i;
            break;
        }
    }
    if($foundItem) {
        $price = getDiscountedPrice($foundItem['price'], $foundItem['discount_percent']);
        $result['itemSubtotal'] = $price * $quantity;
    }
    $result['count'] = getCartCount();
}

echo json_encode($result);
?>