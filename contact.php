<?php
require_once __DIR__ . '/includes/header.php';

$pageTitle = 'تماس با ما';
$page = getPage('contact');
$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    if(empty($name) || empty($email) || empty($message)) {
        $error = 'لطفاً فیلدهای ضروری را پر کنید.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'ایمیل نامعتبر است.';
    } else {
        // ذخیره پیام در دیتابیس (جدول contact_messages)
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = 'پیام شما با موفقیت ارسال شد. در اسرع وقت پاسخ خواهیم داد.';
    }
}
?>

<div class="container">
    <div class="breadcrumb">
        <a href="/">خانه</a>
        <span>/</span>
        <span>تماس با ما</span>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Contact Info -->
        <div style="background: white; border-radius: var(--radius-lg); padding: 30px; box-shadow: var(--shadow-sm);">
            <?php if($page && $page['content']): ?>
                <?php echo $page['content']; ?>
            <?php else: ?>
                <h2>تماس با ما</h2>
                <p>محتوای این صفحه از پنل مدیریت قابل ویرایش است.</p>
            <?php endif; ?>

            <div style="margin-top: 24px; display: flex; flex-direction: column; gap: 16px;">
                <?php if(getSetting('contact_phone')): ?>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 24px;">📞</span>
                    <div>
                        <div style="font-weight: 700;">تلفن</div>
                        <div style="color: var(--gray-500);"><?php echo getSetting('contact_phone'); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(getSetting('contact_email')): ?>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 24px;">📧</span>
                    <div>
                        <div style="font-weight: 700;">ایمیل</div>
                        <div style="color: var(--gray-500);"><?php echo getSetting('contact_email'); ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if(getSetting('contact_address')): ?>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 24px;">📍</span>
                    <div>
                        <div style="font-weight: 700;">آدرس</div>
                        <div style="color: var(--gray-500);"><?php echo getSetting('contact_address'); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Form -->
        <div style="background: white; border-radius: var(--radius-lg); padding: 30px; box-shadow: var(--shadow-sm);">
            <h2 style="margin-bottom: 20px;">📝 فرم تماس</h2>

            <?php if($success): ?>
            <div style="background: var(--success-light); color: #065f46; padding: 12px; border-radius: var(--radius); margin-bottom: 16px;">
                ✅ <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
            <div style="background: var(--danger-light); color: #991b1b; padding: 12px; border-radius: var(--radius); margin-bottom: 16px;">
                ❌ <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">نام و نام خانوادگی</label>
                        <input type="text" name="name" required value="<?php echo $_POST['name'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="required">ایمیل</label>
                        <input type="email" name="email" required value="<?php echo $_POST['email'] ?? ''; ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>موضوع</label>
                    <input type="text" name="subject" value="<?php echo $_POST['subject'] ?? ''; ?>">
                </div>
                <div class="form-group">
                    <label class="required">پیام</label>
                    <textarea name="message" rows="5" required><?php echo $_POST['message'] ?? ''; ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">📤 ارسال پیام</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>