// API Helper
const API = {
    baseUrl: 'api/',
    
    async request(endpoint, method = 'GET', data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && method !== 'GET') {
            options.body = new FormData();
            for (let key in data) {
                options.body.append(key, data[key]);
            }
            options.headers = {};
        }

        const url = this.baseUrl + endpoint;
        const response = await fetch(url, options);
        return response.json();
    },

    // Projects
    getProjects() {
        return this.request('projects.php?action=list');
    },

    getProject(id) {
        return this.request(`projects.php?action=get&id=${id}`);
    },

    createProject(title, description) {
        return this.request('projects.php?action=create', 'POST', { title, description });
    },

    updateProject(id, title, description) {
        return this.request('projects.php?action=update', 'POST', { id, title, description });
    },

    deleteProject(id) {
        return this.request('projects.php?action=delete', 'POST', { id });
    },

    // Discussions
    getDiscussions(projectId) {
        return this.request(`discussions.php?action=list&project_id=${projectId}`);
    },

    getDiscussion(id) {
        return this.request(`discussions.php?action=get&id=${id}`);
    },

    createDiscussion(projectId, title, content) {
        return this.request('discussions.php?action=create', 'POST', { project_id: projectId, title, content });
    },

    updateDiscussion(id, title, content) {
        return this.request('discussions.php?action=update', 'POST', { id, title, content });
    },

    deleteDiscussion(id) {
        return this.request('discussions.php?action=delete', 'POST', { id });
    },

    // Replies
    getReplies(discussionId) {
        return this.request(`replies.php?action=list&discussion_id=${discussionId}`);
    },

    createReply(discussionId, content, parentReplyId = null) {
        const data = { discussion_id: discussionId, content };
        if (parentReplyId) data.parent_reply_id = parentReplyId;
        return this.request('replies.php?action=create', 'POST', data);
    },

    updateReply(id, content) {
        return this.request('replies.php?action=update', 'POST', { id, content });
    },

    deleteReply(id) {
        return this.request('replies.php?action=delete', 'POST', { id });
    },

    // Likes
    toggleDiscussionLike(discussionId) {
        return this.request('likes.php?action=toggle_discussion', 'POST', { discussion_id: discussionId });
    },

    toggleReplyLike(replyId) {
        return this.request('likes.php?action=toggle_reply', 'POST', { reply_id: replyId });
    },

    getLikesCount(discussionId = null, replyId = null) {
        const params = discussionId ? `discussion_id=${discussionId}` : `reply_id=${replyId}`;
        return this.request(`likes.php?action=count&${params}`);
    }
};

// UI Helper
const UI = {
    showAlert(message, type = 'success') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);
        
        setTimeout(() => alertDiv.remove(), 3000);
    },

    showLoading(element) {
        element.innerHTML = '<div class="loading"><div class="spinner"></div> جاري التحميل...</div>';
    },

    showEmpty(element, message = 'لا توجد بيانات') {
        element.innerHTML = `<div class="empty-state"><div class="empty-state-icon">📭</div><p>${message}</p></div>`;
    },

    openModal(modalId) {
        document.getElementById(modalId).classList.add('active');
    },

    closeModal(modalId) {
        document.getElementById(modalId).classList.remove('active');
    }
};

// Common Functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('ar-SA', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Close modal on outside click
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.classList.remove('active');
        }
    });

    // Close modal on close button
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) modal.classList.remove('active');
        });
    });
});
