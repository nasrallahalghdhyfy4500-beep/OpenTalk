<?php
require_once 'includes/config.php';
if (isLoggedIn()) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - منصة مناقشة المشاريع</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <a href="index.php" class="logo">💬 منصة مناقشة المشاريع</a>
        </div>
    </header>

    <div class="container" style="max-width: 400px; margin-top: 3rem;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">تسجيل الدخول</h2>
            </div>
            <div class="card-body">
                <form id="loginForm">
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">تسجيل الدخول</button>
                </form>
                <p style="text-align: center; margin-top: 1rem; color: var(--text-light);">
                    ليس لديك حساب؟ <a href="register.php" style="color: var(--primary-color);">إنشاء حساب</a>
                </p>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    UI.showAlert('تم تسجيل الدخول بنجاح');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    UI.showAlert(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                UI.showAlert('حدث خطأ في تسجيل الدخول', 'error');
            }
        });
    </script>
</body>
</html>
