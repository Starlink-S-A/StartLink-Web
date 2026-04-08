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

    const BASE_URL = window.BASE_URL || '';

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

    const navbarDeleteAllBtn = document.getElementById('navbar-delete-all-btn');
    if (navbarDeleteAllBtn) {
        navbarDeleteAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            customConfirm('¿Borrar todas tus notificaciones?', async () => {
                const formData = new FormData();
                formData.append('sub_action', 'delete_all_notifications');
                const response = await fetch(`${BASE_URL}src/index.php?action=notificaciones`, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    pollNotifications();
                } else {
                    customAlert(`Error: ${data.message}`);
                }
            });
        });
    }

    document.addEventListener('click', async function(event) {
        const deleteButton = event.target.closest('.notif-delete-btn');
        if (!deleteButton) return;

        event.preventDefault();
        const notificationId = deleteButton.dataset.notificationId;
        if (!notificationId) return;

        if (!confirm('¿Eliminar esta notificación?')) return;

        const formData = new FormData();
        formData.append('sub_action', 'delete_notification');
        formData.append('notification_id', notificationId);

        try {
            const response = await fetch(`${BASE_URL}src/index.php?action=notificaciones`, {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                const item = document.getElementById(`notification-item-${notificationId}`);
                if (item) item.remove();
                pollNotifications();
            } else {
                customAlert(`Error: ${data.message}`);
            }
        } catch (error) {
            console.error('Error deleting notification:', error);
            customAlert('Error al eliminar la notificación. Intenta de nuevo.');
        }
    });

    // Continuous update polling for Notifications (checks every 5 seconds)
    function pollNotifications() {
        if (!BASE_URL && !window.location.pathname) {
            return;
        }

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

                updateNotificationsList(data.notifications);
            }
        })
        .catch(err => console.error('Error fetching latest notifications:', err));
    }

    function formatNotificationTime(rawDate) {
        if (!rawDate) return 'Hace un momento';
        const date = new Date(rawDate);
        if (Number.isNaN(date.getTime())) return 'Hace un momento';
        return date.toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
    }

    function buildRedirectUrl(url) {
        if (!url) return '#';
        if (/^https?:\/\//i.test(url)) {
            return url;
        }
        return `${BASE_URL}${url.startsWith('/') ? '' : '/'}${url}`;
    }

    function renderNotificationItem(notification) {
        const isUnread = notification.leida === 0 || notification.leida === '0' || notification.leida === false;
        const dotHtml = isUnread ? '<div class="bg-primary rounded-circle flex-shrink-0" style="width: 7px; height: 7px;"></div>' : '';
        const notificationType = notification.tipo ? notification.tipo.charAt(0).toUpperCase() + notification.tipo.slice(1) : 'Aviso';
        const iconClass = notification.icono || 'fas fa-info-circle text-info';
        const redirectUrl = buildRedirectUrl(notification.url_redireccion);
        const timeText = formatNotificationTime(notification.fecha_creacion);

        return `
            <div class="notif-item position-relative" id="notification-item-${notification.id}">
                <div class="notif-icon-circle" style="background-color: #e0f2fe; color: #0284c7;">
                    <i class="${iconClass}"></i>
                </div>
                <div class="flex-grow-1" style="min-width: 0;">
                    <div class="d-flex justify-content-between align-items-start">
                        <h6 class="mb-1 fw-600" style="font-size: 0.85rem;">${notificationType}</h6>
                        ${dotHtml}
                    </div>
                    <p class="mb-1 text-muted" style="font-size: 0.78rem; line-height: 1.4;">${notification.mensaje || ''}</p>
                    <small class="text-muted" style="font-size: 0.7rem;">${timeText}</small>
                </div>
                <a class="stretched-link" href="${redirectUrl}"></a>
                <button class="notif-delete-btn position-relative" style="z-index:2;" title="Eliminar" type="button"><i class="far fa-trash-alt"></i></button>
            </div>
        `;
    }

    function updateNotificationsList(notifications) {
        const container = document.getElementById('notifications-list');
        if (!container) return;

        if (!Array.isArray(notifications) || notifications.length === 0) {
            container.innerHTML = `
                <div class="p-4 text-center text-muted">
                    <i class="far fa-bell-slash d-block mb-2" style="font-size: 1.5rem;"></i>
                    No tienes notificaciones
                </div>
            `;
            return;
        }

        container.innerHTML = notifications.map(renderNotificationItem).join('');
    }

    pollNotifications();
    setInterval(pollNotifications, 5000); 

    // Omitted logic for accept/decline offers as those hit other controllers (responder_oferta.php / responder_solicitud_contratacion.php)
    // but the user's base code handled them as a simple fetch. Since I don't see those controllers, I will skip redefining them here or let them act as they did.
});
