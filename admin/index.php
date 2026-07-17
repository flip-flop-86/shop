<?php
session_start();

// اگر قبلاً لاگین کرده
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    // رمز مخفی پنل
    $adminPassword = 'Admin@1403#Shop!xY9';
    
    if($password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_time'] = time();
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'رمز عبور اشتباه است';
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', sans-serif;
            background: #0f172a;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-box {
            background: #1e293b;
            border-radius: 20px;
            padding: 40px;
            width: 380px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
            border: 1px solid #334155;
        }
        .login-box h1 {
            color: #e2e8f0;
            font-size: 24px;
            margin-bottom: 8px;
        }
        .login-box .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .login-box p {
            color: #94a3b8;
            font-size: 13px;
            margin-bottom: 24px;
        }
        .login-box input {
            width: 100%;
            padding: 12px 16px;
            background: #0f172a;
            border: 2px solid #334155;
            border-radius: 10px;
            color: #e2e8f0;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 14px;
            text-align: center;
            letter-spacing: 2px;
            transition: all 0.3s;
        }
        .login-box input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }
        .login-box button {
            width: 100%;
            padding: 12px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 10px;
            font-family: 'Vazirmatn', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 16px;
            transition: all 0.3s;
        }
        .login-box button:hover {
            background: #4f46e5;
            transform: translateY(-2px);
        }
        .error {
            color: #ef4444;
            font-size: 12px;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="icon">🔐</div>
        <h1>پنل مدیریت</h1>
        <p>رمز مخفی را وارد کنید</p>
        <form method="POST">
            <input type="password" name="password" placeholder="••••••••••••" autofocus>
            <?php if($error): ?>
            <div class="error">❌ <?php echo $error; ?></div>
            <?php endif; ?>
            <button type="submit">🚀 ورود</button>
        </form>
    </div>
</body>
</html>