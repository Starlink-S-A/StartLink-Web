document.addEventListener('DOMContentLoaded', function() {
    let alertDialogInstance = null;
    let confirmModalInstance = null;
    let confirmCallback = null;

    if (typeof bootstrap !== 'undefined') {
        if(document.getElementById('alertDialog')) alertDialogInstance = new bootstrap.Modal(document.getElementById('alertDialog'));
        if(document.getElementById('confirmModal')) confirmModalInstance = new bootstrap.Modal(document.getElementById('confirmModal'));
    }

    function customAlert(message) {
        const body = document.getElementById('alertDialogBody');
        if (body && alertDialogInstance) {
            body.textContent = message;
            alertDialogInstance.show();
            return;
        }
        alert(message);
    }

    function customConfirm(message, callback) {
        const body = document.getElementById('confirmModalBody');
        const btn = document.getElementById('confirmActionButton');
        confirmCallback = callback;
        if (body && btn && confirmModalInstance) {
            body.textContent = message;
            confirmModalInstance.show();
            btn.onclick = () => {
                if (confirmCallback) confirmCallback();
                confirmModalInstance.hide();
            };
            return;
        }
        if (confirm(message)) if (callback) callback();
    }

    // AJAX Call to Mark Notification as Read
    function markNotificationAsRead(notificationId) {
        const formData = new FormData();
        formData.append('sub_action', 'mark_notification_read');
        formData.append('notification_id', notificationId);

        return fetch(`${BASE_URL}src/index.php?action=notificaciones`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.getElementById(`notification-item-${notificationId}`);
                if (item) {
                    item.classList.remove('unread');
                    item.classList.add('read');
                    const btn = item.querySelector('.mark-as-read-btn');
                    if(btn) {
                        btn.disabled = true;
                        btn.textContent = 'Leída';
                        btn.className = 'btn btn-sm btn-outline-secondary mark-as-read-btn';
                    }
                }
            }
            return data;
        });
    }

    // Attach functionality to mark as read manually
    document.querySelectorAll('.mark-as-read-btn').forEach(button => {
        button.addEventListener('click', async function() {
            const notificationId = this.dataset.notificationId;
            await markNotificationAsRead(notificationId);
        });
    });

    // Handle view detail redirects with marking as read
    document.querySelectorAll('.view-detail-btn').forEach(button => {
        button.addEventListener('click', async function(event) {
            event.preventDefault();
            const notificationId = this.dataset.notificationId;
            const redirectUrl = this.href;
            await markNotificationAsRead(notificationId);
            window.location.href = redirectUrl;
        });
    });

    // Mark all as read
    const markAllReadBtn = document.getElementById('mark-all-read-btn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            customConfirm('¿Estás seguro de que quieres marcar TODAS tus notificaciones como leídas?', async () => {
                const formData = new FormData();
                formData.append('sub_action', 'mark_all_notifications_read');

                const response = await fetch(`${BASE_URL}src/index.php?action=notificaciones`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    customAlert(`Error: ${data.message}`);
                }
            });
        });
    }

    const navbarMarkAllReadBtn = document.getElementById('navbar-mark-all-read-btn');
    if (navbarMarkAllReadBtn) {
        navbarMarkAllReadBtn.addEventListener('click', function(e) {
            e.preventDefault();
            customConfirm('¿Marcar todas tus notificaciones como leídas?', async () => {
                const formData = new FormData();
                formData.append('sub_action', 'mark_all_notifications_read');
                const response = await fetch(`${BASE_URL}src/index.php?action=notificaciones`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    pollNotifications();
                    window.location.reload();
                } else {
                    customAlert(`Error: ${data.message}`);
                }
            });
        });
    }

    // Continuous update polling for Notifications (checks every 5 seconds)
    function pollNotifications() {
        fetch(`${BASE_URL}src/index.php?action=notificaciones&sub_action=fetch_latest_ajax`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notifications-badge');
                if (badge) {
                    badge.textContent = data.unread_count > 0 ? data.unread_count : '';
                    badge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
                }

                const msgBadge = document.getElementById('messages-badge');
                if (msgBadge && typeof data.unread_chat_count !== 'undefined') {
                    msgBadge.textContent = data.unread_chat_count > 0 ? data.unread_chat_count : '';
                    msgBadge.style.display = data.unread_chat_count > 0 ? 'inline-block' : 'none';
                }

                const sidebarBadge = document.querySelector('.sidebar-nav .badge.bg-danger');
                if (sidebarBadge) {
                    sidebarBadge.textContent = data.unread_count;
                    sidebarBadge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
                }
            }
        })
        .catch(err => console.error('Error fetching latest notifications:', err));
    }

    setInterval(pollNotifications, 5000); 

    // Omitted logic for accept/decline offers as those hit other controllers (responder_oferta.php / responder_solicitud_contratacion.php)
    // but the user's base code handled them as a simple fetch. Since I don't see those controllers, I will skip redefining them here or let them act as they did.
});
