<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید']);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$action = toggleWishlist($_SESSION['user_id'], $productId);

echo json_encode([
    'success' => true,
    'action' => $action,
    'count' => getWishlistCount()
]);
?>