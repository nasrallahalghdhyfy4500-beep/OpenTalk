<?php
require_once '../includes/config.php';

$action = $_GET['action'] ?? '';

// Get replies by discussion
if ($action === 'list') {
    $discussion_id = (int)($_GET['discussion_id'] ?? 0);
    
    $result = $conn->query("
        SELECT r.*, u.name as user_name,
        COUNT(DISTINCT l.id) as likes_count
        FROM replies r
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN likes l ON r.id = l.reply_id
        WHERE r.discussion_id = $discussion_id
        GROUP BY r.id
        ORDER BY r.created_at ASC
    ");
    
    $replies = [];
    while ($row = $result->fetch_assoc()) {
        $replies[] = $row;
    }
    
    json_response(true, '', $replies);
}

// Create reply
if ($action === 'create') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $discussion_id = (int)($_POST['discussion_id'] ?? 0);
    $content = sanitize($_POST['content'] ?? '');
    $parent_reply_id = (int)($_POST['parent_reply_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    if (empty($content)) {
        json_response(false, 'المحتوى مطلوب');
    }

    $parent_id = $parent_reply_id > 0 ? $parent_reply_id : 'NULL';
    $sql = "INSERT INTO replies (discussion_id, user_id, parent_reply_id, content) VALUES ($discussion_id, $user_id, $parent_id, '$content')";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم إضافة الرد بنجاح', ['id' => $conn->insert_id]);
    } else {
        json_response(false, 'حدث خطأ في إضافة الرد');
    }
}

// Update reply
if ($action === 'update') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $id = (int)($_POST['id'] ?? 0);
    $content = sanitize($_POST['content'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Check ownership
    $result = $conn->query("SELECT user_id FROM replies WHERE id = $id");
    if ($result->num_rows === 0 || $result->fetch_assoc()['user_id'] != $user_id) {
        json_response(false, 'ليس لديك صلاحية لتعديل هذا الرد');
    }

    $sql = "UPDATE replies SET content = '$content' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم تحديث الرد بنجاح');
    } else {
        json_response(false, 'حدث خطأ في تحديث الرد');
    }
}

// Delete reply
if ($action === 'delete') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $id = (int)($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Check ownership
    $result = $conn->query("SELECT user_id FROM replies WHERE id = $id");
    if ($result->num_rows === 0 || $result->fetch_assoc()['user_id'] != $user_id) {
        json_response(false, 'ليس لديك صلاحية لحذف هذا الرد');
    }

    $sql = "DELETE FROM replies WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم حذف الرد بنجاح');
    } else {
        json_response(false, 'حدث خطأ في حذف الرد');
    }
}

json_response(false, 'إجراء غير صحيح');
?>
