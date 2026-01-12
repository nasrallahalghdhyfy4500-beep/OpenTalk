<?php
require_once '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        json_response(false, 'البريد الإلكتروني وكلمة المرور مطلوبة');
    }

    // Get user
    $result = $conn->query("SELECT id, name, email, password FROM users WHERE email = '$email'");
    
    if ($result->num_rows === 0) {
        json_response(false, 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!verify_password($password, $user['password'])) {
        json_response(false, 'البريد الإلكتروني أو كلمة المرور غير صحيحة');
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    json_response(true, 'تم تسجيل الدخول بنجاح', ['redirect' => 'index.php']);
}
?>
