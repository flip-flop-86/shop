<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'لطفاً وارد شوید']);
    exit;
}

$productId = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 5);
$title = sanitize($_POST['title'] ?? '');
$comment = sanitize($_POST['comment'] ?? '');

if(empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'متن نظر را وارد کنید']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO reviews (user_id, product_id, rating, title, comment) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$_SESSION['user_id'], $productId, $rating, $title, $comment]);

echo json_encode(['success' => true, 'message' => 'نظر شما ثبت شد و پس از تأیید نمایش داده می‌شود']);
?>