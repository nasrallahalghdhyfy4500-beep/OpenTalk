<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        json_response(false, 'جميع الحقول مطلوبة');
    }

    if (!validate_email($email)) {
        json_response(false, 'البريد الإلكتروني غير صحيح');
    }

    if (strlen($password) < 6) {
        json_response(false, 'كلمة المرور يجب أن تكون 6 أحرف على الأقل');
    }

    if ($password !== $confirm_password) {
        json_response(false, 'كلمات المرور غير متطابقة');
    }

    // Check if email already exists
    $result = $conn->query("SELECT id FROM users WHERE email = '$email'");
    if ($result->num_rows > 0) {
        json_response(false, 'البريد الإلكتروني مستخدم بالفعل');
    }

    // Hash password and insert user
    $hashed_password = hash_password($password);
    $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$hashed_password')";

    if ($conn->query($sql) === TRUE) {
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;
        
        json_response(true, 'تم التسجيل بنجاح', ['redirect' => 'index.php']);
    } else {
        json_response(false, 'حدث خطأ في التسجيل');
    }
}
?>
