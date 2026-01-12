<?php
require_once '../includes/config.php';

$action = $_GET['action'] ?? '';

// Get all projects
if ($action === 'list') {
    $result = $conn->query("
        SELECT p.*, u.name as owner_name,
        COUNT(DISTINCT d.id) as discussions_count,
        COUNT(DISTINCT r.id) as replies_count
        FROM projects p
        LEFT JOIN users u ON p.owner_id = u.id
        LEFT JOIN discussions d ON p.id = d.project_id
        LEFT JOIN replies r ON d.id = r.discussion_id
        WHERE p.status = 'active'
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    
    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    json_response(true, '', $projects);
}

// Get single project
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $result = $conn->query("
        SELECT p.*, u.name as owner_name
        FROM projects p
        LEFT JOIN users u ON p.owner_id = u.id
        WHERE p.id = $id
    ");
    
    if ($result->num_rows === 0) {
        json_response(false, 'المشروع غير موجود');
    }
    
    json_response(true, '', $result->fetch_assoc());
}

// Create project
if ($action === 'create') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (empty($title)) {
        json_response(false, 'عنوان المشروع مطلوب');
    }

    $sql = "INSERT INTO projects (title, description, owner_id) VALUES ('$title', '$description', $user_id)";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم إنشاء المشروع بنجاح', ['id' => $conn->insert_id]);
    } else {
        json_response(false, 'حدث خطأ في إنشاء المشروع');
    }
}

// Update project
if ($action === 'update') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $id = (int)($_POST['id'] ?? 0);
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Check ownership
    $result = $conn->query("SELECT owner_id FROM projects WHERE id = $id");
    if ($result->num_rows === 0 || $result->fetch_assoc()['owner_id'] != $user_id) {
        json_response(false, 'ليس لديك صلاحية لتعديل هذا المشروع');
    }

    $sql = "UPDATE projects SET title = '$title', description = '$description' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم تحديث المشروع بنجاح');
    } else {
        json_response(false, 'حدث خطأ في تحديث المشروع');
    }
}

// Delete project
if ($action === 'delete') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $id = (int)($_POST['id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Check ownership
    $result = $conn->query("SELECT owner_id FROM projects WHERE id = $id");
    if ($result->num_rows === 0 || $result->fetch_assoc()['owner_id'] != $user_id) {
        json_response(false, 'ليس لديك صلاحية لحذف هذا المشروع');
    }

    $sql = "DELETE FROM projects WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        json_response(true, 'تم حذف المشروع بنجاح');
    } else {
        json_response(false, 'حدث خطأ في حذف المشروع');
    }
}

json_response(false, 'إجراء غير صحيح');
?>
