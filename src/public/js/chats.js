document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    const messageForm = document.getElementById('message-form');
    const messageInput = document.getElementById('message_content');
    const searchInput = document.getElementById('search-input');
    const clearSearchBtn = document.getElementById('clear-search-btn');
    const baseUrl = (typeof BASE_URL !== 'undefined' ? BASE_URL : (globalThis.BASE_URL || ''));
    const activeChatId = (typeof currentChatId !== 'undefined' ? currentChatId : (globalThis.currentChatId || null));

    let alertDialogInstance = null;
    let confirmModalInstance = null;
    let confirmCallback = null;

    try {
        if (document.getElementById('alertDialog')) alertDialogInstance = new bootstrap.Modal(document.getElementById('alertDialog'));
        if (document.getElementById('confirmModal')) confirmModalInstance = new bootstrap.Modal(document.getElementById('confirmModal'));
    } catch (e) { console.error('Bootstrap modals not loaded'); }

    function customAlert(message) {
        document.getElementById('alertDialogBody').textContent = message;
        if(alertDialogInstance) alertDialogInstance.show();
        else alert(message);
    }

    function customConfirm(message, callback) {
        document.getElementById('confirmModalBody').textContent = message;
        confirmCallback = callback;
        if(confirmModalInstance) {
            confirmModalInstance.show();
            document.getElementById('confirmActionButton').onclick = () => {
                if (confirmCallback) confirmCallback();
                confirmModalInstance.hide();
            };
        } else {
            if (confirm(message)) if (callback) callback();
        }
    }

    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // HU-Chat-01: Marcar chat como activo para silenciado inteligente
    if (activeChatId) {
        const formData = new FormData();
        formData.append('sub_action', 'update_active_chat');
        formData.append('chat_id', activeChatId);
        fetch(`${baseUrl}index.php?action=mis_chats`, {
            method: 'POST',
            body: formData
        });
    }

    // Limpiar chat activo al salir
    window.addEventListener('beforeunload', function() {
        const formData = new FormData();
        formData.append('sub_action', 'update_active_chat');
        formData.append('chat_id', '');
        navigator.sendBeacon(`${baseUrl}index.php?action=mis_chats`, formData);
    });

    // Autoajuste del textarea

    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        if (activeChatId) messageInput.focus();
    }

    function fetchJsonWithTimeout(url, options = {}, timeoutMs = 15000) {
        const controller = new AbortController();
        const timer = setTimeout(() => controller.abort(), timeoutMs);

        const headers = {
            ...(options.headers || {}),
            'X-Requested-With': 'XMLHttpRequest'
        };

        return fetch(url, { ...options, headers, signal: controller.signal })
            .then(async (res) => {
                const text = await res.text();
                let data;
                try {
                    data = text ? JSON.parse(text) : null;
                } catch (e) {
                    const snippet = (text || '').slice(0, 300);
                    throw new Error(`Respuesta inválida del servidor (HTTP ${res.status}). ${snippet}`);
                }

                if (!res.ok) {
                    const msg = data?.message || data?.debug || `HTTP ${res.status}`;
                    throw new Error(msg);
                }

                return data;
            })
            .finally(() => clearTimeout(timer));
    }

    // Polling function for real-time messages
    function fetchMessages() {
        if (!messagesContainer || !activeChatId) return;
        
        const url = `${baseUrl}index.php?action=mis_chats&sub_action=fetch_messages_ajax&chat_id=${activeChatId}`;
        
        fetchJsonWithTimeout(url, { method: 'GET' }, 15000)
            .then(data => {
                if(data.success) {
                    let newHtml = '';
                    if (data.messages.length === 0) {
                        newHtml = '<div class="alert alert-info text-center mt-3 empty-msg-alert">No hay mensajes en esta conversación.</div>';
                    } else {
                        data.messages.forEach(msg => {
                            const isSent = msg.id_remitente == data.userId;
                            const messageClass = isSent ? 'sent' : 'received';
                            const senderName = isSent ? 'Tú' : msg.remitente_nombre;
                            
                            // Las fotos ya vienen procesadas del controlador
                            const senderPhoto = msg.remitente_foto || (typeof DEFAULT_AVATAR !== 'undefined' ? DEFAULT_AVATAR : (baseUrl + 'images/default-profile.jpg'));
                            
                            const date = new Date(msg.fecha_envio);
                            const hours = date.getHours().toString().padStart(2, '0');
                            const mins = date.getMinutes().toString().padStart(2, '0');

                            let avatarHtml = `<img src="${senderPhoto}" alt="Avatar" class="message-avatar" onerror="this.onerror=null;this.src='${typeof DEFAULT_AVATAR !== 'undefined' ? DEFAULT_AVATAR : baseUrl + 'images/default-profile.jpg'}';">`;
                            
                            newHtml += `<div class="message-wrapper ${messageClass}">`;
                            if (!isSent) newHtml += avatarHtml;
                            newHtml += `<div class="message-container">`;
                            if (!isSent) newHtml += `<div class="message-sender-name">${senderName}</div>`;
                            newHtml += `<div class="message-bubble ${messageClass}">${msg.contenido}</div>`;
                            newHtml += `<div class="message-info ${messageClass}">${hours}:${mins}</div>`;
                            newHtml += `</div>`;
                            if (isSent) newHtml += avatarHtml;
                            newHtml += `</div>`;
                        });
                    }
                    
                    if (messagesContainer.innerHTML !== newHtml) {
                        const scrollPosition = messagesContainer.scrollTop;
                        const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.clientHeight <= scrollPosition + 10;
                        messagesContainer.innerHTML = newHtml;
                        if (wasAtBottom) messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                }
            })
            .catch(err => console.error('Error fetching messages:', err));
    }

    if (activeChatId) {
        setInterval(fetchMessages, 3000); // Polling every 3 seconds for continuous updates
    }

    // Send Message AJAX
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Usar el ID del chat actual de la variable global o del input
            const chatId = document.getElementById('chat_id_input')?.value || activeChatId;
            const content = messageInput.value.trim();
            
            if(!chatId || !content) {
                console.error('Falta ID de chat o contenido');
                return;
            }

            const formData = new FormData();
            formData.append('sub_action', 'send_message');
            formData.append('chat_id', chatId);
            formData.append('message_content', content);

            // Deshabilitar botón mientras se envía
            const submitBtn = messageForm.querySelector('button[type="submit"]');
            if(submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }

            fetchJsonWithTimeout(`${baseUrl}index.php?action=mis_chats`, {
                method: 'POST',
                body: formData
            }, 20000)
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    messageInput.style.height = 'auto';
                    fetchMessages(); // Actualizar inmediatamente
                } else {
                    customAlert(`Error: ${data.message || 'No se pudo enviar el mensaje'}`);
                }
            })
            .catch(err => {
                console.error('Error detallado enviando mensaje:', err);
                const msg = err?.name === 'AbortError'
                    ? 'Tiempo de espera agotado al enviar el mensaje. Revisa la conexión/servidor.'
                    : (err?.message || 'Error al enviar el mensaje.');
                customAlert(msg);
            })
            .finally(() => {
                if(submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar';
                }
            });
        });
    }

    document.querySelectorAll('.btn-action-delete').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault(); 
            const chatId = this.dataset.chatId;
            const chatTitle = this.dataset.chatTitle;

            customConfirm(`¿Estás seguro de que quieres eliminar el chat con "${chatTitle}"? Esta acción no se puede deshacer.`, () => {
                const formData = new FormData();
                formData.append('sub_action', 'delete_chat');
                formData.append('chat_id', chatId);

                fetch(`${baseUrl}index.php?action=mis_chats`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = `${baseUrl}index.php?action=mis_chats`;
                    } else customAlert(`Error: ${data.message}`);
                });
            });
        });
    });

    document.querySelectorAll('.btn-action-favorite').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault(); 
            const chatId = this.dataset.chatId;
            const currentIsFavorite = this.dataset.isFavorite === 'true'; 
            const newIsFavorite = !currentIsFavorite; 
            const chatTitle = this.dataset.chatTitle;
            const actionMessage = newIsFavorite ? `¿Estás seguro de que quieres marcar el chat con "${chatTitle}" como favorito?` : `¿Estás seguro de que quieres desmarcar el chat con "${chatTitle}" de favoritos?`;

            customConfirm(actionMessage, () => {
                const formData = new FormData();
                formData.append('sub_action', 'toggle_favorite');
                formData.append('chat_id', chatId);
                formData.append('is_favorite', newIsFavorite);

                fetch(`${baseUrl}index.php?action=mis_chats`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = `${baseUrl}index.php?action=mis_chats`;
                    } else customAlert(`Error: ${data.message}`);
                });
            });
        });
    });

    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const currentUrl = new URL(window.location.href);
                const query = this.value.trim();
                if (query) currentUrl.searchParams.set('search_query', query);
                else currentUrl.searchParams.delete('search_query');
                currentUrl.searchParams.delete('chat_id'); 
                window.location.href = currentUrl.toString();
            }, 500); 
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('search_query');
            currentUrl.searchParams.delete('chat_id'); 
            window.location.href = currentUrl.toString();
        });
    }
});
