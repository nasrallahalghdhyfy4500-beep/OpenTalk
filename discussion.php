<?php
require_once 'includes/config.php';
$user = getCurrentUser();
$discussion_id = (int)($_GET['id'] ?? 0);

if ($discussion_id === 0) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المناقشة - منصة مناقشة المشاريع</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <a href="index.php" class="logo">💬 منصة مناقشة المشاريع</a>
            <nav class="nav">
                <?php if ($user): ?>
                    <span>مرحباً، <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="profile.php">ملفي الشخصي</a>
                    <a href="api/logout.php" class="btn btn-sm btn-danger">تسجيل الخروج</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-sm btn-outline">تسجيل الدخول</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <a href="javascript:history.back()" style="color: var(--primary-color); text-decoration: none; margin-bottom: 1rem;">← العودة</a>

        <!-- Discussion Details -->
        <div id="discussionDetails" class="card" style="margin-bottom: 2rem;">
            <div class="card-body">
                <div class="loading"><div class="spinner"></div> جاري التحميل...</div>
            </div>
        </div>

        <!-- Replies -->
        <h2 style="margin-bottom: 1rem;">الردود</h2>
        <div id="repliesList" class="grid"></div>

        <!-- Add Reply Form -->
        <?php if ($user): ?>
            <div class="card" style="margin-top: 2rem;">
                <div class="card-header">
                    <h3 class="card-title">أضف ردك</h3>
                </div>
                <div class="card-body">
                    <form id="createReplyForm">
                        <div class="form-group">
                            <textarea name="content" required placeholder="اكتب ردك هنا..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">إرسال الرد</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="margin-top: 2rem;">
                <div class="card-body" style="text-align: center;">
                    <p style="margin-bottom: 1rem;">يجب تسجيل الدخول لإضافة رد</p>
                    <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="js/main.js"></script>
    <script>
        const discussionId = <?php echo $discussion_id; ?>;

        async function loadDiscussion() {
            try {
                const response = await API.getDiscussion(discussionId);
                if (response.success) {
                    const discussion = response.data;
                    document.getElementById('discussionDetails').innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <h1>${escapeHtml(discussion.title)}</h1>
                                <p style="color: var(--text-light); margin: 1rem 0;">
                                    ${escapeHtml(discussion.content)}
                                </p>
                                <p style="font-size: 0.875rem; color: var(--text-light);">
                                    بواسطة: ${escapeHtml(discussion.user_name)} | 
                                    ${formatDate(discussion.created_at)}
                                </p>
                            </div>
                            <button class="btn btn-sm btn-outline" onclick="toggleDiscussionLike()">
                                <span id="likeBtn">❤️ إعجاب</span>
                            </button>
                        </div>
                    `;
                } else {
                    UI.showAlert('المناقشة غير موجودة', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function loadReplies() {
            const container = document.getElementById('repliesList');
            UI.showLoading(container);

            try {
                const response = await API.getReplies(discussionId);
                if (response.success && response.data.length > 0) {
                    container.innerHTML = response.data.map(reply => `
                        <div class="card">
                            <div class="card-body">
                                <p style="margin-bottom: 1rem;">${escapeHtml(reply.content)}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <p style="font-size: 0.875rem; color: var(--text-light);">
                                        ${escapeHtml(reply.user_name)} | ${formatDate(reply.created_at)}
                                    </p>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <button class="btn btn-sm btn-outline" onclick="toggleReplyLike(${reply.id})">
                                            ❤️ ${reply.likes_count}
                                        </button>
                                        ${<?php echo $user ? 'true' : 'false'; ?> && <?php echo isset($user) && $user['id'] ? 'true' : 'false'; ?> ? `
                                            <button class="btn btn-sm btn-danger" onclick="deleteReply(${reply.id})">حذف</button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    UI.showEmpty(container, 'لا توجد ردود حتى الآن');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function toggleDiscussionLike() {
            try {
                const response = await API.toggleDiscussionLike(discussionId);
                if (response.success) {
                    loadDiscussion();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function toggleReplyLike(replyId) {
            try {
                const response = await API.toggleReplyLike(replyId);
                if (response.success) {
                    loadReplies();
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteReply(replyId) {
            if (!confirm('هل أنت متأكد من حذف هذا الرد؟')) return;
            
            try {
                const response = await API.deleteReply(replyId);
                if (response.success) {
                    UI.showAlert('تم حذف الرد بنجاح');
                    loadReplies();
                } else {
                    UI.showAlert(response.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        document.getElementById('createReplyForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await API.createReply(
                    discussionId,
                    formData.get('content')
                );
                
                if (response.success) {
                    UI.showAlert('تم إضافة الرد بنجاح');
                    e.target.reset();
                    loadReplies();
                } else {
                    UI.showAlert(response.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                UI.showAlert('حدث خطأ في إضافة الرد', 'error');
            }
        });

        loadDiscussion();
        loadReplies();
    </script>
</body>
</html>
