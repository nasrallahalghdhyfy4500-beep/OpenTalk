<?php
require_once 'includes/config.php';
$user = getCurrentUser();

if (!$user) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ملفي الشخصي - منصة مناقشة المشاريع</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <a href="index.php" class="logo">💬 منصة مناقشة المشاريع</a>
            <nav class="nav">
                <span>مرحباً، <?php echo htmlspecialchars($user['name']); ?></span>
                <a href="api/logout.php" class="btn btn-sm btn-danger">تسجيل الخروج</a>
            </nav>
        </div>
    </header>

    <div class="container">
        <a href="index.php" style="color: var(--primary-color); text-decoration: none; margin-bottom: 1rem;">← العودة</a>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Profile Info -->
            <div class="card">
                <div class="card-body" style="text-align: center;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                    <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                    <p style="color: var(--text-light); margin: 0.5rem 0;">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    <p style="font-size: 0.875rem; color: var(--text-light); margin-top: 1rem;">
                        عضو منذ: <?php echo formatDate($user['created_at']); ?>
                    </p>
                </div>
            </div>

            <!-- User's Projects -->
            <div>
                <h2 style="margin-bottom: 1rem;">مشاريعي</h2>
                <div id="userProjects" class="grid"></div>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        async function loadUserProjects() {
            const container = document.getElementById('userProjects');
            UI.showLoading(container);

            try {
                const response = await API.getProjects();
                if (response.success) {
                    const userId = <?php echo $user['id']; ?>;
                    const userProjects = response.data.filter(p => p.owner_id === userId);
                    
                    if (userProjects.length > 0) {
                        container.innerHTML = userProjects.map(project => `
                            <div class="card">
                                <div class="card-body">
                                    <h3>${escapeHtml(project.title)}</h3>
                                    <p style="color: var(--text-light); margin: 0.5rem 0;">
                                        ${escapeHtml(project.description)}
                                    </p>
                                    <p style="font-size: 0.875rem; color: var(--text-light); margin-top: 1rem;">
                                        مناقشات: ${project.discussions_count}
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <a href="project.php?id=${project.id}" class="btn btn-primary btn-sm">عرض</a>
                                    <button class="btn btn-danger btn-sm" onclick="deleteProject(${project.id})">حذف</button>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        UI.showEmpty(container, 'لم تنشئ أي مشاريع حتى الآن');
                    }
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        async function deleteProject(projectId) {
            if (!confirm('هل أنت متأكد من حذف هذا المشروع؟')) return;
            
            try {
                const response = await API.deleteProject(projectId);
                if (response.success) {
                    UI.showAlert('تم حذف المشروع بنجاح');
                    loadUserProjects();
                } else {
                    UI.showAlert(response.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }

        loadUserProjects();
    </script>
</body>
</html>
