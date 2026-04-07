<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Chats - TalentLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Estilos específicos -->
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/dashboard_styles.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>src/public/styles/chats.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Variable global BASE URL para JS -->
    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
        window.currentChatId = <?= json_encode($currentChatId) ?>;
        window.currentUserId = <?= json_encode($userId) ?>;
        window.DEFAULT_AVATAR = 'https://static.thenounproject.com/png/4154905-200.png';
    </script>
</head>
<body>
<?php include __DIR__ . '/../dashboardView/sidebar_View.php'; ?>

<div class="main-content">
<div class="container chat-container">
    <div class="conversations-sidebar">
        <h4>Chats Activos</h4>
        <div class="input-group mb-3">
            <input type="text" id="search-input" class="form-control" placeholder="Buscar chat por nombre..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button class="btn btn-outline-secondary" type="button" id="clear-search-btn">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div id="conversations-list">
            <?php if (empty($conversations)): ?>
                <div class="alert alert-info text-center mt-3">No tienes conversaciones activas.</div>
            <?php else: ?>
                <?php foreach ($conversations as $conv): ?>
                    <div class="conversation-item-wrapper <?= ($currentChatId == $conv['id_conversacion']) ? 'active' : '' ?>" data-favorite="<?= $conv['is_favorite'] ? 'true' : 'false' ?>">
                        <a href="<?= BASE_URL ?>src/index.php?action=mis_chats&chat_id=<?= htmlspecialchars($conv['id_conversacion']) ?>" class="conversation-content-link">
                            <img src="<?= htmlspecialchars($conv['avatar']) ?>" alt="Avatar" onerror="this.onerror=null;this.src='https://static.thenounproject.com/png/4154905-200.png';">
                            <div class="conversation-info">
                                <strong><?= htmlspecialchars($conv['title']) ?></strong>
                                <div class="last-message">
                                    <span>
                                        <?= htmlspecialchars($conv['last_message_sender']) ? htmlspecialchars($conv['last_message_sender']) . ': ' : '' ?>
                                        <?= htmlspecialchars($conv['last_message_content']) ?>
                                    </span>
                                    <?php if (!empty($conv['last_message_time'])): ?>
                                        <small><?= htmlspecialchars($conv['last_message_time']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item btn-action-delete" href="#" data-chat-id="<?= $conv['id_conversacion'] ?>" data-chat-title="<?= htmlspecialchars(addslashes($conv['title'])) ?>">Eliminar Chat</a></li>
                                <li>
                                    <a class="dropdown-item btn-action-favorite" href="#" data-chat-id="<?= $conv['id_conversacion'] ?>" data-chat-title="<?= htmlspecialchars(addslashes($conv['title'])) ?>" data-is-favorite="<?= $conv['is_favorite'] ? 'true' : 'false' ?>">
                                        <span class="favorite-text"><?= $conv['is_favorite'] ? 'Desmarcar Favorito' : 'Marcar como Favorito' ?></span>
                                        <i class="bi <?= $conv['is_favorite'] ? 'bi-star-fill' : 'bi-star' ?> ms-2"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="chat-main-content">
        <div class="chat-header" id="chat-header-title">
            <?php if ($currentChatData): ?>
                <?= htmlspecialchars($currentChatData['display_title']) ?>
            <?php else: ?>
                Selecciona un chat para empezar a conversar
            <?php endif; ?>
        </div>
        
        <div class="messages-display" id="messages-container">
            <?php if ($currentChatId && $currentChatData): ?>
                <?php if (empty($messages)): ?>
                    <div class="alert alert-info text-center mt-3 empty-msg-alert">No hay mensajes en esta conversación.</div>
                <?php else: ?>
                    <?php foreach ($messages as $message):
                        $isSent = ($message['id_remitente'] == $userId);
                        $messageClass = $isSent ? 'sent' : 'received';
                        $senderName = $isSent ? 'Tú' : htmlspecialchars($message['remitente_nombre']);
                        
                        $fotoDB = $message['remitente_foto'];
                        $senderPhotoUrl = empty($fotoDB) ? 'https://static.thenounproject.com/png/4154905-200.png' : BASE_URL . 'assets/images/Uploads/profile_pictures/' . basename($fotoDB);
                        $senderPhoto = $isSent ? $currentUserPhoto : $senderPhotoUrl;

                        // Calculate correct time in JS by outputting the ISO string
                        // DB saves in America/Bogota as per config
                        $dt = new DateTime($message['fecha_envio'], new DateTimeZone('America/Bogota'));
                        $isoTime = $dt->format('c');
                    ?>
                        <div class="message-wrapper <?= $messageClass ?>">
                            <?php if (!$isSent): ?>
                                <img src="<?= htmlspecialchars($senderPhoto) ?>" alt="Avatar" class="message-avatar" onerror="this.onerror=null;this.src='https://static.thenounproject.com/png/4154905-200.png';">
                            <?php endif; ?>
                            <div class="message-container">
                                <?php if (!$isSent): ?>
                                    <div class="message-sender-name"><?= $senderName ?></div>
                                <?php endif; ?>
                                <div class="message-bubble <?= $messageClass ?>">
                                    <?php if (!empty($message['metadata'])): 
                                        $meta = json_decode($message['metadata'], true);
                                        if ($meta && !empty($meta['attachmentUrl'])): ?>
                                        <a href="<?= BASE_URL . $meta['attachmentUrl'] ?>" target="_blank" class="d-block mb-2 p-2 rounded text-decoration-none d-flex align-items-center gap-2 <?= $isSent ? 'bg-white bg-opacity-25 text-white' : 'bg-light text-dark border' ?>" style="font-size: 0.9rem;">
                                            <i class="fas fa-file-download fa-lg"></i>
                                            <span class="text-truncate" style="max-width: 200px;"><?= htmlspecialchars($meta['fileName']) ?></span>
                                        </a>
                                    <?php endif; endif; ?>
                                    <?= nl2br(htmlspecialchars($message['contenido'])) ?>
                                </div>
                                <div class="message-info <?= $messageClass ?> js-local-time" data-iso="<?= htmlspecialchars($isoTime) ?>">
                                    <?= $dt->format('H:i') ?>
                                </div>
                            </div>
                            <?php if ($isSent): ?>
                                <img src="<?= htmlspecialchars($senderPhoto) ?>" alt="Avatar" class="message-avatar" onerror="this.onerror=null;this.src='https://static.thenounproject.com/png/4154905-200.png';">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-chat-selected">
                    No hay chat seleccionado.
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($currentChatId && $currentChatData): ?>
            <!-- CONTENEDOR PREVIEW ADJUNTO -->
            <div id="attachment_preview_container" class="d-none px-3 py-2 bg-white border-top border-bottom">
                <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background-color: #f1f5f9; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-2 text-truncate text-secondary fw-500" style="font-size: 0.9rem;">
                       <i class="fas fa-file-alt text-primary"></i> <span id="attachment_preview_name">archivo.pdf</span>
                    </div>
                    <button type="button" class="btn-close" style="font-size: 0.75rem;" id="remove_attachment_btn" aria-label="Close"></button>
                </div>
            </div>
            
            <form class="message-input-area d-flex align-items-end gap-2 p-3 bg-white" id="message-form" enctype="multipart/form-data">
                <input type="hidden" id="chat_id_input" value="<?= $currentChatId ?>">
                
                <label for="attachment_input" class="btn btn-light rounded-circle text-secondary m-0 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; cursor: pointer; background-color: #f1f5f9; border: none;">
                    <i class="fas fa-paperclip fs-5"></i>
                </label>
                <input type="file" id="attachment_input" name="attachment" class="d-none">
                
                <div class="flex-grow-1 position-relative">
                   <textarea id="message_content" class="form-control rounded-4 px-3 shadow-none bg-light" placeholder="Escribe un mensaje..." rows="1" style="resize: none; border: 1px solid #e2e8f0; padding-top: 12px; padding-bottom: 12px;"></textarea>
                </div>
                
                <button type="submit" class="btn text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 border-0" style="width: 45px; height: 45px; background-color: #00a680; transition: background-color 0.2s;">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        <?php else: ?>
            <div class="message-input-area d-flex gap-2 p-3">
                <textarea class="form-control rounded-4" placeholder="Selecciona un chat para escribir..." rows="1" style="resize: none;" disabled></textarea>
                <button class="btn btn-secondary rounded-circle" style="width: 45px; height: 45px;" disabled><i class="fas fa-paper-plane"></i></button>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="confirmModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmActionButton">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Alerta -->
<div class="modal fade" id="alertDialog" tabindex="-1" aria-labelledby="alertDialogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="alertDialogLabel">Mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="alertDialogBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>src/public/js/chats.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const timeElements = document.querySelectorAll('.js-local-time');
        timeElements.forEach(el => {
            const isoString = el.getAttribute('data-iso');
            if (isoString) {
                const dateObj = new Date(isoString);
                // format cleanly without seconds to exactly match the design
                el.textContent = dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
        });
    });
</script>
</body>
</html>
