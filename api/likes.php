<?php
require_once '../includes/config.php';

$action = $_GET['action'] ?? '';

// Toggle like on discussion
if ($action === 'toggle_discussion') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $discussion_id = (int)($_POST['discussion_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Check if already liked
    $result = $conn->query("SELECT id FROM likes WHERE user_id = $user_id AND discussion_id = $discussion_id");
    
    if ($result->num_rows > 0) {
        // Unlike
        $conn->query("DELETE FROM likes WHERE user_id = $user_id AND discussion_id = $discussion_id");
        json_response(true, 'تم إزالة الإعجاب', ['liked' => false]);
    } else {
        // Like
        $sql = "INSERT INTO likes (user_id, discussion_id) VALUES ($user_id, $discussion_id)";
        if ($conn->query($sql) === TRUE) {
            json_response(true, 'تم الإعجاب', ['liked' => true]);
        } else {
            json_response(false, 'حدث خطأ');
        }
    }
}

// Toggle like on reply
if ($action === 'toggle_reply') {
    if (!isLoggedIn()) {
        json_response(false, 'يجب تسجيل الدخول أولاً');
    }

    $reply_id = (int)($_POST['reply_id'] ?? 0);
    $user_id = $_SESSION['user_id'];

    // Check if already liked
    $result = $conn->query("SELECT id FROM likes WHERE user_id = $user_id AND reply_id = $reply_id");
    
    if ($result->num_rows > 0) {
        // Unlike
        $conn->query("DELETE FROM likes WHERE user_id = $user_id AND reply_id = $reply_id");
        json_response(true, 'تم إزالة الإعجاب', ['liked' => false]);
    } else {
        // Like
        $sql = "INSERT INTO likes (user_id, reply_id) VALUES ($user_id, $reply_id)";
        if ($conn->query($sql) === TRUE) {
            json_response(true, 'تم الإعجاب', ['liked' => true]);
        } else {
            json_response(false, 'حدث خطأ');
        }
    }
}

// Get likes count
if ($action === 'count') {
    $discussion_id = (int)($_GET['discussion_id'] ?? 0);
    $reply_id = (int)($_GET['reply_id'] ?? 0);

    if ($discussion_id > 0) {
        $result = $conn->query("SELECT COUNT(*) as count FROM likes WHERE discussion_id = $discussion_id");
    } else {
        $result = $conn->query("SELECT COUNT(*) as count FROM likes WHERE reply_id = $reply_id");
    }

    $row = $result->fetch_assoc();
    json_response(true, '', ['count' => $row['count']]);
}

json_response(false, 'إجراء غير صحيح');
?>
