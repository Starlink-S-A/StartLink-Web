<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class NotificacionesModel {
    private $link;

    public function __construct() {
        $this->link = getDbConnection();
    }

    public function getLink() {
        return $this->link;
    }

    public function getTotalNotificaciones($userId) {
        $stmtCount = $this->link->prepare("SELECT COUNT(*) FROM notificaciones WHERE user_id = ?");
        $stmtCount->execute([$userId]);
        return (int)$stmtCount->fetchColumn();
    }
    
    public function getUnreadCount($userId) {
        $stmtCount = $this->link->prepare("SELECT COUNT(*) FROM notificaciones WHERE user_id = ? AND leida = 0");
        $stmtCount->execute([$userId]);
        return (int)$stmtCount->fetchColumn();
    }

    public function getNotificacionesPaginated($userId, $limit, $offset) {
        $stmt = $this->link->prepare("
            SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
            FROM notificaciones
            WHERE user_id = ?
            ORDER BY fecha_creacion DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getNotificaciones($userId, $limit = 20) {
        $stmt = $this->link->prepare("
            SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
            FROM notificaciones
            WHERE user_id = ?
            ORDER BY fecha_creacion DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notificationId, $userId) {
        $stmtUpdate = $this->link->prepare("
            UPDATE notificaciones
            SET leida = 1
            WHERE id = ? AND user_id = ?
        ");
        $stmtUpdate->execute([$notificationId, $userId]);
        return $stmtUpdate->rowCount() > 0;
    }

    public function markAllAsRead($userId) {
        $stmtUpdateAll = $this->link->prepare("
            UPDATE notificaciones
            SET leida = 1
            WHERE user_id = ? AND leida = 0
        ");
        $stmtUpdateAll->execute([$userId]);
        return $stmtUpdateAll->rowCount() > 0;
    }

    /**
     * Crea una nueva notificación.
     * Si es de tipo 'chat', maneja la agrupación y el silenciado inteligente.
     */
    public function crearNotificacion($userId, $mensaje, $tipo, $icono, $url = null, $extraData = []) {
        // HU-Chat-01: Silenciado inteligente
        if ($tipo === 'chat' && isset($extraData['chat_id'])) {
            /* 
            Removido por causar falsos positivos cuando el usuario cierra el navegador 
            sin que el JS limpie current_chat_id:
            if ($this->isUserInChat($userId, $extraData['chat_id'])) {
                return false; 
            }
            */

            // Agrupación de mensajes del mismo remitente (HU-Chat-03)
            if (isset($extraData['sender_id'], $extraData['sender_name'])) {
                $existingNotif = $this->getUnreadChatNotification($userId, $extraData['chat_id'], $extraData['sender_id']);
                if ($existingNotif) {
                    $count = ($existingNotif['msg_count'] ?? 1) + 1;
                    $nuevoMensaje = "Tienes $count mensajes nuevos de " . $extraData['sender_name'];
                    return $this->updateChatNotification($existingNotif['id'], $nuevoMensaje, $count);
                }
            }
        }

        $stmt = $this->link->prepare("
            INSERT INTO notificaciones (user_id, mensaje, tipo, icono, url_redireccion, fecha_creacion, leida)
            VALUES (?, ?, ?, ?, ?, NOW(), 0)
        ");
        return $stmt->execute([$userId, $mensaje, $tipo, $icono, $url]);
    }

    private function isUserInChat($userId, $chatId) {
        $stmt = $this->link->prepare("SELECT current_chat_id FROM usuario WHERE id = ?");
        $stmt->execute([$userId]);
        $currentChat = $stmt->fetchColumn();
        return (int)$currentChat === (int)$chatId;
    }

    private function getUnreadChatNotification($userId, $chatId, $senderId) {
        // Busca una notificación de chat no leída de este remitente para este chat
        $urlPattern = "%chat_id=$chatId%";
        $stmt = $this->link->prepare("
            SELECT id, mensaje
            FROM notificaciones
            WHERE user_id = ? AND tipo = 'chat' AND leida = 0 AND url_redireccion LIKE ?
            ORDER BY fecha_creacion DESC LIMIT 1
        ");
        $stmt->execute([$userId, $urlPattern]);
        $notif = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($notif) {
            // Contar mensajes del remitente en esta conversación después de la última notificación
            $stmtCount = $this->link->prepare("SELECT COUNT(*) FROM mensaje WHERE id_conversacion = ? AND id_remitente = ?");
            $stmtCount->execute([$chatId, $senderId]);
            $notif['msg_count'] = (int)$stmtCount->fetchColumn();
        }
        
        return $notif;
    }

    private function updateChatNotification($id, $nuevoMensaje, $count) {
        $stmt = $this->link->prepare("UPDATE notificaciones SET mensaje = ?, fecha_creacion = NOW() WHERE id = ?");
        return $stmt->execute([$nuevoMensaje, $id]);
    }
}
