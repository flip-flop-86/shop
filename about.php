<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'درباره ما';
$page = getPage('about');
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <span>درباره ما</span>
    </div>

    <div class="card" style="background: white; border-radius: var(--radius-lg); padding: 30px; box-shadow: var(--shadow-sm);">
        <?php if($page && $page['content']): ?>
            <?php echo $page['content']; ?>
        <?php else: ?>
            <h1>درباره ما</h1>
            <p>محتوای این صفحه از پنل مدیریت قابل ویرایش است.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>