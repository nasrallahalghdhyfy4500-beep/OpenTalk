<?php
require_once 'includes/config.php';
$user = getCurrentUser();
$project_id = (int)($_GET['id'] ?? 0);

if ($project_id === 0) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفاصيل المشروع - منصة مناقشة المشاريع</title>
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
        <a href="index.php" style="color: var(--primary-color); text-decoration: none; margin-bottom: 1rem;">← العودة</a>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <!-- Project Details -->
            <div>
                <div id="projectDetails" class="card">
                    <div class="card-body">
                        <div class="loading"><div class="spinner"></div> جاري التحميل...</div>
                    </div>
                </div>

                <!-- Discussions -->
                <h2 style="margin-top: 2rem; margin-bottom: 1rem;">المناقشات</h2>
                <div id="discussionsList" class="grid"></div>
            </div>

            <!-- Sidebar -->
            <div>
                <?php if ($user): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">إضافة مناقشة جديدة</h3>
                        </div>
                        <div class="card-body">
                            <form id="createDiscussionForm">
                                <div class="form-group">
                                    <label>العنوان</label>
                                    <input type="text" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label>المحتوى</label>
                                    <textarea name="content" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" style="width: 100%;">إنشاء مناقشة</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card">
                        <div class="card-body" style="text-align: center;">
                            <p style="margin-bottom: 1rem;">يجب تسجيل الدخول لإضافة مناقشة</p>
                            <a href="login.php" class="btn btn-primary">تسجيل الدخول</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        const projectId = <?php echo $project_id; ?>;

        async function loadProject() {
            try {
                const response = await API.getProject(projectId);
                if (response.success) {
                    const project = response.data;
                    document.getElementById('projectDetails').innerHTML = `
                        <h1>${escapeHtml(project.title)}</h1>
                        <p style="color: var(--text-light); margin: 1rem 0;">${escapeHtml(project.description)}</p>
                        <p style="font-size: 0.875rem; color: var(--text-light);">
                            بواسطة: ${escapeHtml(project.owner_name)}
                        </p>
                    `;
                } else {
                    UI.showAlert('المشروع غير موجود', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function loadDiscussions() {
            const container = document.getElementById('discussionsList');
            UI.showLoading(container);

            try {
                const response = await API.getDiscussions(projectId);
                if (response.success && response.data.length > 0) {
                    container.innerHTML = response.data.map(discussion => `
                        <div class="card">
                            <div class="card-body">
                                <h3 style="margin-bottom: 0.5rem;">
                                    <a href="discussion.php?id=${discussion.id}" style="color: var(--text-dark); text-decoration: none;">
                                        ${escapeHtml(discussion.title)}
                                    </a>
                                </h3>
                                <p style="color: var(--text-light); font-size: 0.875rem;">
                                    بواسطة: ${escapeHtml(discussion.user_name)} | 
                                    ردود: ${discussion.replies_count} | 
                                    إعجابات: ${discussion.likes_count}
                                </p>
                            </div>
                            <div class="card-footer">
                                <a href="discussion.php?id=${discussion.id}" class="btn btn-primary btn-sm">عرض المناقشة</a>
                            </div>
                        </div>
                    `).join('');
                } else {
                    UI.showEmpty(container, 'لا توجد مناقشات حتى الآن');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        document.getElementById('createDiscussionForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await API.createDiscussion(
                    projectId,
                    formData.get('title'),
                    formData.get('content')
                );
                
                if (response.success) {
                    UI.showAlert('تم إنشاء المناقشة بنجاح');
                    e.target.reset();
                    loadDiscussions();
                } else {
                    UI.showAlert(response.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                UI.showAlert('حدث خطأ في إنشاء المناقشة', 'error');
            }
        });

        loadProject();
        loadDiscussions();
    </script>
</body>
</html>
