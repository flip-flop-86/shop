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

if($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'محصول نامعتبر']);
    exit;
}

$result = addToCart($_SESSION['user_id'], $productId, $quantity);
echo json_encode($result);
?>