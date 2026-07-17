<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید']);
    exit;
}

$code = strtoupper(trim($_POST['code'] ?? ''));
$cartTotal = getCartTotal();

$result = applyCoupon($code, $cartTotal);
echo json_encode($result);
?>