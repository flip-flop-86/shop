<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$result = removeFromCart($_SESSION['user_id'], $productId);
$result['count'] = getCartCount();

echo json_encode($result);
?>