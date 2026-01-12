<?php
require_once '../includes/config.php';

$action = $_GET['action'] ?? '';

// Get discussions by project
if ($action === 'list') {
    $project_id = (int)($_GET['project_id'] ?? 0);
    
    $result = $conn->query("
        SELECT d.*, u.name as user_name,
        COUNT(DISTINCT r.id) as replies_count,
        COUNT(DISTINCT l.id) as likes_count
        FROM discussions d
        LEFT JOIN users u ON d.user_id = u.id
        LEFT JOIN replies r ON d.id = r.discussion_id
        LEFT JOIN likes l ON d.id = l.discussion_id
        WHERE d.project_id = $project_id
        GROUP BY d.id
        ORDER BY d.created_at DESC
    ");
    
    $discussions = [];
    while ($row = $result->fetch_assoc()) {
        $discussions[] = $row;
    }
    
    json_response(true, '', $discussions);
}

// Get single discussion
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $result = $conn->query("
        SELECT d.*, u.name as user_name
        FROM discussions d
        LEFT JOIN users u ON d.user_id = u.id
        WHERE d.id = $id
    ");
    
    if ($result->num_rows === 0) {
        json_response(false, 'المناقشة غير موجودة');
    }
    
    json_response(true, '', $result->fetch_assoc());
}

// Create discussion
if ($action === 'create') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $project_id = (int)($_POST['project_id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($content)) {
        json_response(false, 'العنوان والمحتوى مطلوبان');
    }

    $sql = "INSERT INTO discussions (project_id, user_id, title, content) VALUES ($project_id, $user_id, '$title', '$content')";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم إنشاء المناقشة بنجاح', ['id' => $conn->insert_id]);
    } else {
        json_response(false, 'حدث خطأ في إنشاء المناقشة');
    }
}

// Update discussion
if ($action === 'update') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $id = (int)($_POST['id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Check ownership
    $result = $conn->query("SELECT user_id FROM discussions WHERE id = $id");
    if ($result->num_rows === 0 || $result->fetch_assoc()['user_id'] != $user_id) {
        json_response(false, 'ليس لديك صلاحية لتعديل هذه المناقشة');
    }

    $sql = "UPDATE discussions SET title = '$title', content = '$content' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم تحديث المناقشة بنجاح');
    } else {
        json_response(false, 'حدث خطأ في تحديث المناقشة');
    }
}

// Delete discussion
if ($action === 'delete') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $id = (int)($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Check ownership
    $result = $conn->query("SELECT user_id FROM discussions WHERE id = $id");
    if ($result->num_rows === 0 || $result->fetch_assoc()['user_id'] != $user_id) {
        json_response(false, 'ليس لديك صلاحية لحذف هذه المناقشة');
    }

    $sql = "DELETE FROM discussions WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم حذف المناقشة بنجاح');
    } else {
        json_response(false, 'حدث خطأ في حذف المناقشة');
    }
}

json_response(false, 'إجراء غير صحيح');
?>
