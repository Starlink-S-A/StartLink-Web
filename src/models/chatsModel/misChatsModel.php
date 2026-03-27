<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class MisChatsModel {
    private $link;

    public function __construct() {
        $this->link = getDbConnection();
    }

    public function getLink() {
        return $this->link;
    }

    public function updateUserActiveChat($userId, $chatId) {
        $chatId = !empty($chatId) ? $chatId : null;
        $stmt = $this->link->prepare("UPDATE usuario SET current_chat_id = ? WHERE id = ?");
        return $stmt->execute([$chatId, $userId]);
    }

    public function getUserData($userId) {
        $stmt = $this->link->prepare("SELECT nombre, foto_perfil FROM usuario WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getConversaciones($userId, $searchQuery = '') {
        $sql = "
            SELECT
                CP.id_conversacion,
                C.tipo_conversacion,
                C.id_proyecto,
                C.titulo_conversacion,
                C.fecha_creacion,
                C.ultimo_mensaje,
                M.contenido AS ultimo_mensaje_contenido,
                M.fecha_envio AS ultimo_mensaje_fecha_envio,
                U_REM.nombre AS ultimo_mensaje_remitente_nombre,
                U_REM.foto_perfil AS ultimo_mensaje_remitente_foto,
                CP.es_favorito
            FROM conversacion_participante CP
            JOIN conversacion C ON CP.id_conversacion = C.id_conversacion
            LEFT JOIN mensaje M ON C.id_conversacion = M.id_conversacion AND C.ultimo_mensaje = M.fecha_envio
            LEFT JOIN usuario U_REM ON M.id_remitente = U_REM.id
            WHERE CP.id_usuario = ?
        ";
        $params = [$userId];

        if (!empty($searchQuery)) {
            $sql .= " AND (C.titulo_conversacion LIKE ? OR EXISTS (SELECT 1 FROM conversacion_participante CP_OTHER JOIN usuario U_OTHER ON CP_OTHER.id_usuario = U_OTHER.id WHERE CP_OTHER.id_conversacion = C.id_conversacion AND CP_OTHER.id_usuario != ? AND U_OTHER.nombre LIKE ?))";
            $params[] = '%' . $searchQuery . '%';
            $params[] = $userId;
            $params[] = '%' . $searchQuery . '%';
        }

        $sql .= " ORDER BY CP.es_favorito DESC, C.ultimo_mensaje DESC, C.fecha_creacion DESC";

        $stmt = $this->link->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function isParticipant($chatId, $userId) {
        $stmt = $this->link->prepare("SELECT COUNT(*) FROM conversacion_participante WHERE id_conversacion = ? AND id_usuario = ?");
        $stmt->execute([$chatId, $userId]);
        return $stmt->fetchColumn() > 0;
    }

    public function getChatData($chatId) {
        $stmt = $this->link->prepare("
            SELECT C.id_conversacion, C.tipo_conversacion, C.id_proyecto, C.titulo_conversacion
            FROM conversacion C
            WHERE C.id_conversacion = ?
        ");
        $stmt->execute([$chatId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOfertaTitle($idProyecto) {
        $stmt = $this->link->prepare("SELECT titulo_oferta FROM oferta_trabajo WHERE id_oferta = ?");
        $stmt->execute([$idProyecto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getOtherParticipantInfo($chatId, $userId) {
        $stmt = $this->link->prepare("
            SELECT U.nombre, U.foto_perfil
            FROM conversacion_participante CP2
            JOIN usuario U ON CP2.id_usuario = U.id
            WHERE CP2.id_conversacion = ? AND CP2.id_usuario != ?
            LIMIT 1
        ");
        $stmt->execute([$chatId, $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMessages($chatId) {
        $stmt = $this->link->prepare("
            SELECT M.contenido, M.fecha_envio, M.id_remitente, U.nombre AS remitente_nombre, U.foto_perfil AS remitente_foto
            FROM mensaje M
            JOIN usuario U ON M.id_remitente = U.id
            WHERE M.id_conversacion = ?
            ORDER BY M.fecha_envio ASC
        ");
        $stmt->execute([$chatId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOtherParticipantsIds($chatId, $userId) {
        $stmt = $this->link->prepare("
            SELECT id_usuario FROM conversacion_participante
            WHERE id_conversacion = ? AND id_usuario != ?
        ");
        $stmt->execute([$chatId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function sendMessage($chatId, $userId, $messageContent, $notificationUrl, $currentUserName) {
        try {
            $this->link->beginTransaction();

            $now = date('Y-m-d H:i:s');

            // El campo se llama 'id_mensaje' (PK), 'id_conversacion', 'id_remitente', 'contenido', 'fecha_envio'
            $stmtInsertMessage = $this->link->prepare("
                INSERT INTO mensaje (id_conversacion, id_remitente, contenido, fecha_envio, tipo_mensaje)
                VALUES (?, ?, ?, ?, 'normal')
            ");
            if (!$stmtInsertMessage->execute([$chatId, $userId, $messageContent, $now])) {
                throw new Exception("Error al insertar mensaje en la base de datos.");
            }

            $stmtUpdateLastMessage = $this->link->prepare("
                UPDATE conversacion SET ultimo_mensaje = ? WHERE id_conversacion = ?
            ");
            if (!$stmtUpdateLastMessage->execute([$now, $chatId])) {
                throw new Exception("Error al actualizar último mensaje en conversación.");
            }

            $this->link->commit();

            try {
                $otherParticipants = $this->getOtherParticipantsIds($chatId, $userId);
                $notificationMessage = "Nuevo mensaje de " . $currentUserName;

                if (!empty($otherParticipants)) {
                    if (file_exists(__DIR__ . '/../notifiacionesModel/notificacionesModel.php')) {
                        require_once __DIR__ . '/../notifiacionesModel/notificacionesModel.php';
                        $notifModel = new NotificacionesModel();
                        
                        foreach ($otherParticipants as $participantId) {
                            $notifModel->crearNotificacion(
                                $participantId, 
                                $notificationMessage, 
                                'chat', 
                                'fas fa-comment-dots', 
                                $notificationUrl,
                                [
                                    'chat_id' => $chatId,
                                    'sender_id' => $userId,
                                    'sender_name' => $currentUserName
                                ]
                            );
                        }
                    }
                }
            } catch (Throwable $notifEx) {
                error_log("Error al enviar notificaciones de chat: " . $notifEx->getMessage());
            }

            return true;
        } catch (Throwable $e) {
            if ($this->link->inTransaction()) {
                $this->link->rollBack();
            }
            error_log("Error al enviar mensaje: " . $e->getMessage());
            return false;
        }
    }

    public function createPrivateChat($userId, $candidateId) {
        try {
            $stmtFindChat = $this->link->prepare("
                SELECT CP1.id_conversacion
                FROM conversacion_participante CP1
                JOIN conversacion_participante CP2 ON CP1.id_conversacion = CP2.id_conversacion
                JOIN conversacion C ON CP1.id_conversacion = C.id_conversacion
                WHERE CP1.id_usuario = ? AND CP2.id_usuario = ?
                AND C.tipo_conversacion = 'perfil_publico'
                GROUP BY CP1.id_conversacion
                HAVING COUNT(DISTINCT CP1.id_usuario) = 2
            ");
            $stmtFindChat->execute([$userId, $candidateId]);
            $foundChatId = $stmtFindChat->fetchColumn();

            if ($foundChatId) {
                return $foundChatId;
            }

            $this->link->beginTransaction();

            $stmtUserNames = $this->link->prepare("SELECT nombre FROM usuario WHERE id IN (?, ?)");
            $stmtUserNames->execute([$userId, $candidateId]);
            $names = $stmtUserNames->fetchAll(PDO::FETCH_COLUMN);
            $chatTitle = implode(" y ", $names);

            $stmtInsertConv = $this->link->prepare("
                INSERT INTO conversacion (tipo_conversacion, titulo_conversacion, fecha_creacion)
                VALUES ('perfil_publico', ?, NOW())
            ");
            $stmtInsertConv->execute([$chatTitle]);
            $newChatId = $this->link->lastInsertId();

            $stmtInsertParticipant = $this->link->prepare("
                INSERT INTO conversacion_participante (id_conversacion, id_usuario)
                VALUES (?, ?), (?, ?)
            ");
            $stmtInsertParticipant->execute([$newChatId, $userId, $newChatId, $candidateId]);

            $this->link->commit();
            return $newChatId;

        } catch (PDOException $e) {
            if ($this->link->inTransaction()) {
                $this->link->rollBack();
            }
            error_log("Error al crear chat privado: " . $e->getMessage());
            return false;
        }
    }

    public function getOrCreateOfferChat($offerId, $userId) {
        try {
            // Buscar si ya existe la conversación grupal para esta oferta
            $stmtFind = $this->link->prepare("SELECT id_conversacion FROM conversacion WHERE id_proyecto = ? AND tipo_conversacion = 'oferta_grupal' LIMIT 1");
            $stmtFind->execute([$offerId]);
            $chatId = $stmtFind->fetchColumn();

            if (!$chatId) {
                // Crear la conversación si no existe
                $stmtOffer = $this->link->prepare("SELECT titulo_oferta FROM oferta_trabajo WHERE id_oferta = ?");
                $stmtOffer->execute([$offerId]);
                $offerTitle = $stmtOffer->fetchColumn();

                $this->link->beginTransaction();
                $stmtInsert = $this->link->prepare("INSERT INTO conversacion (tipo_conversacion, id_proyecto, titulo_conversacion, fecha_creacion) VALUES ('oferta_grupal', ?, ?, NOW())");
                $stmtInsert->execute([$offerId, "Oferta: " . $offerTitle]);
                $chatId = $this->link->lastInsertId();
                $this->link->commit();
            }

            // Asegurarse de que el usuario sea participante
            if (!$this->isParticipant($chatId, $userId)) {
                $stmtJoin = $this->link->prepare("INSERT INTO conversacion_participante (id_conversacion, id_usuario, fecha_union) VALUES (?, ?, NOW())");
                $stmtJoin->execute([$chatId, $userId]);
            }

            return $chatId;
        } catch (PDOException $e) {
            if ($this->link->inTransaction()) $this->link->rollBack();
            error_log("Error en getOrCreateOfferChat: " . $e->getMessage());
            return false;
        }
    }

    public function deleteChat($chatId, $userId) {
        if (!$this->isParticipant($chatId, $userId)) {
            return ['success' => false, 'message' => 'No autorizado para eliminar este chat o el chat no existe.'];
        }

        try {
            $this->link->beginTransaction();
            $stmtDeleteConv = $this->link->prepare("DELETE FROM conversacion WHERE id_conversacion = ?");
            $stmtDeleteConv->execute([$chatId]);
            $this->link->commit();
            return ['success' => true, 'message' => 'Chat eliminado correctamente.'];
        } catch (PDOException $e) {
            $this->link->rollBack();
            error_log("Error al eliminar chat: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el chat: ' . $e->getMessage()];
        }
    }

    public function toggleFavorite($chatId, $userId, $isFavorite) {
        if (!$this->isParticipant($chatId, $userId)) {
            return ['success' => false, 'message' => 'No autorizado para modificar este chat.'];
        }

        try {
            $stmtUpdateFavorite = $this->link->prepare("
                UPDATE conversacion_participante
                SET es_favorito = ?
                WHERE id_conversacion = ? AND id_usuario = ?
            ");
            $stmtUpdateFavorite->execute([$isFavorite, $chatId, $userId]);
            return ['success' => true, 'message' => 'Estado de favorito actualizado.', 'is_favorite' => $isFavorite];
        } catch (PDOException $e) {
            error_log("Error al actualizar favorito: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el estado de favorito: ' . $e->getMessage()];
        }
    }
}
