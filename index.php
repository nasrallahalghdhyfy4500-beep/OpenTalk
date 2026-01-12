<?php
require_once 'includes/config.php';
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>منصة مناقشة المشاريع</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Header -->
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
                    <a href="register.php" class="btn btn-sm btn-primary">إنشاء حساب</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Hero Section -->
        <div style="text-align: center; margin-bottom: 3rem;">
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;">منصة مناقشة المشاريع</h1>
            <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem;">
                تعاون وناقش أفكارك مع المجتمع
            </p>
            <?php if ($user): ?>
                <button class="btn btn-primary" onclick="openCreateProjectModal()">+ إنشاء مشروع جديد</button>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary">ابدأ الآن</a>
            <?php endif; ?>
        </div>

        <!-- Features -->
        <div class="grid grid-3" style="margin-bottom: 3rem;">
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">💬</div>
                    <h3>مناقشات غنية</h3>
                    <p style="color: var(--text-light);">شارك أفكارك وناقش التفاصيل مع الآخرين بسهولة</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">👥</div>
                    <h3>مجتمع نشط</h3>
                    <p style="color: var(--text-light);">تواصل مع مطورين وخبراء من مختلف المجالات</p>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">⚡</div>
                    <h3>سهل وسريع</h3>
                    <p style="color: var(--text-light);">واجهة بسيطة وسريعة للاستخدام</p>
                </div>
            </div>
        </div>

        <!-- Projects Section -->
        <h2 style="margin-bottom: 2rem;">المشاريع الأخيرة</h2>
        <div id="projectsList" class="grid grid-2"></div>
    </div>

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="modal">
        <div class="modal-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">إنشاء مشروع جديد</h2>
                </div>
                <div class="card-body">
                    <form id="createProjectForm">
                        <div class="form-group">
                            <label>عنوان المشروع</label>
                            <input type="text" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>الوصف</label>
                            <textarea name="description" required></textarea>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">إنشاء</button>
                            <button type="button" class="btn btn-secondary" onclick="UI.closeModal('createProjectModal')">إلغاء</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Load projects
        async function loadProjects() {
            const container = document.getElementById('projectsList');
            UI.showLoading(container);

            try {
                const response = await API.getProjects();
                if (response.success && response.data.length > 0) {
                    container.innerHTML = response.data.map(project => `
                        <div class="card">
                            <div class="card-body">
                                <h3>${escapeHtml(project.title)}</h3>
                                <p style="color: var(--text-light); margin: 0.5rem 0;">${escapeHtml(project.description)}</p>
                                <p style="font-size: 0.875rem; color: var(--text-light); margin-top: 1rem;">
                                    بواسطة: ${escapeHtml(project.owner_name)} | 
                                    مناقشات: ${project.discussions_count}
                                </p>
                            </div>
                            <div class="card-footer">
                                <a href="project.php?id=${project.id}" class="btn btn-primary btn-sm">عرض المشروع</a>
                            </div>
                        </div>
                    `).join('');
                } else {
                    UI.showEmpty(container, 'لا توجد مشاريع حتى الآن');
                }
            } catch (error) {
                console.error('Error:', error);
                UI.showAlert('حدث خطأ في تحميل المشاريع', 'error');
            }
        }

        // Create project
        document.getElementById('createProjectForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await API.createProject(
                    formData.get('title'),
                    formData.get('description')
                );
                
                if (response.success) {
                    UI.showAlert('تم إنشاء المشروع بنجاح');
                    UI.closeModal('createProjectModal');
                    e.target.reset();
                    loadProjects();
                } else {
                    UI.showAlert(response.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                UI.showAlert('حدث خطأ في إنشاء المشروع', 'error');
            }
        });

        function openCreateProjectModal() {
            UI.openModal('createProjectModal');
        }

        // Load projects on page load
        loadProjects();
    </script>
</body>
</html>
