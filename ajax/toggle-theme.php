<?php
require_once __DIR__ . '/../config/database.php';

$theme = $_POST['theme'] ?? 'light';
updateSetting('theme_mode', $theme);
echo json_encode(['success' => true]);
?>