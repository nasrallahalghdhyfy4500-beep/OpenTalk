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
    <title>إنشاء حساب - منصة مناقشة المشاريع</title>
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
                <h2 class="card-title">إنشاء حساب جديد</h2>
            </div>
            <div class="card-body">
                <form id="registerForm">
                    <div class="form-group">
                        <label>الاسم</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>تأكيد كلمة المرور</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">إنشاء حساب</button>
                </form>
                <p style="text-align: center; margin-top: 1rem; color: var(--text-light);">
                    لديك حساب بالفعل؟ <a href="login.php" style="color: var(--primary-color);">تسجيل الدخول</a>
                </p>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('api/register.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    UI.showAlert('تم التسجيل بنجاح');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    UI.showAlert(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                UI.showAlert('حدث خطأ في التسجيل', 'error');
            }
        });
    </script>
</body>
</html>
