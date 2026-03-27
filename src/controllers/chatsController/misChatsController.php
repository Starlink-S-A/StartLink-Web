<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/chatsModel/misChatsModel.php';

class MisChatsController {
    private $model;
    private $userId;

    public function __construct() {
        if (!isset($_SESSION["user_id"]) || $_SESSION["loggedin"] !== true) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit();
            }
            header("Location: " . BASE_URL . "bienvenida.php");
            exit();
        }
        $this->userId = $_SESSION["user_id"];
        $this->model = new MisChatsModel();
    }

    public function index() {
        $action = $_GET['sub_action'] ?? ($_POST['sub_action'] ?? 'list');

        switch ($action) {
            case 'update_active_chat':
                $this->updateActiveChat();
                break;
            case 'delete_chat':
                $this->deleteChat();
                break;
            case 'toggle_favorite':
                $this->toggleFavorite();
                break;
            case 'send_message':
                $this->sendMessage();
                break;
            case 'fetch_messages_ajax':
                $this->fetchMessagesAjax();
                break;
            case 'fetch_chats_ajax':
                $this->fetchChatsAjax();
                break;
            case 'list':
            default:
                $this->showList();
                break;
        }
    }

    private function deleteChat() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chatIdToDelete = $_POST['chat_id'] ?? null;
            if ($chatIdToDelete && is_numeric($chatIdToDelete)) {
                $result = $this->model->deleteChat($chatIdToDelete, $this->userId);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de chat inválido.']);
            }
        }
        exit();
    }

    private function updateActiveChat() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chatId = $_POST['chat_id'] ?? null;
            $this->model->updateUserActiveChat($this->userId, $chatId);
            echo json_encode(['success' => true]);
        }
        exit();
    }

    private function toggleFavorite() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $chatIdToToggle = $_POST['chat_id'] ?? null;
            $isFavorite = (int)filter_var($_POST['is_favorite'] ?? false, FILTER_VALIDATE_BOOLEAN); 
            if ($chatIdToToggle && is_numeric($chatIdToToggle)) {
                $result = $this->model->toggleFavorite($chatIdToToggle, $this->userId, $isFavorite);
                echo json_encode($result);
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de chat inválido.']);
            }
        }
        exit();
    }

    private function sendMessage() {
        header('Content-Type: application/json');
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $currentChatId = $_POST['chat_id'] ?? null;
            $messageContent = trim($_POST['message_content'] ?? '');
            
            if ($currentChatId && !empty($messageContent)) {
                if ($this->model->isParticipant($currentChatId, $this->userId)) {
                    $userData = $this->model->getUserData($this->userId);
                    $userName = $userData['nombre'] ?? 'Usuario';
                    $notificationUrl = BASE_URL . "index.php?action=mis_chats&chat_id=" . $currentChatId;
                    
                    $success = $this->model->sendMessage($currentChatId, $this->userId, $messageContent, $notificationUrl, $userName);
                    if ($success) {
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje.']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta conversación.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
            }
        }
        exit();
    }

    private function fetchMessagesAjax() {
        header('Content-Type: application/json');
        $currentChatId = $_GET['chat_id'] ?? null;
        if ($currentChatId && $this->model->isParticipant($currentChatId, $this->userId)) {
            $messages = $this->model->getMessages($currentChatId);
            
            // Enriquecer mensajes con la URL de la foto procesada
            foreach ($messages as &$msg) {
                if ($msg['remitente_foto']) {
                    $nombreArchivo = basename($msg['remitente_foto']);
                    $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
                    $rutaPublica  = rtrim(BASE_URL, '/') . 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;

                    if (file_exists($rutaAbsoluta)) {
                        $msg['remitente_foto'] = $rutaPublica;
                    } else {
                        $msg['remitente_foto'] = BASE_URL . 'images/default-profile.jpg';
                    }
                } else {
                    $msg['remitente_foto'] = BASE_URL . 'images/default-profile.jpg';
                }
            }
            unset($msg);

            echo json_encode(['success' => true, 'messages' => $messages, 'userId' => $this->userId, 'BASE_URL' => BASE_URL]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit();
    }

    private function fetchChatsAjax() {
        header('Content-Type: application/json');
        $searchQuery = $_GET['search_query'] ?? '';
        $conversations_raw = $this->model->getConversaciones($this->userId, $searchQuery);
        $conversations = $this->processConversations($conversations_raw);
        echo json_encode(['success' => true, 'chats' => $conversations]);
        exit();
    }

    private function showList() {
        $searchQuery = $_GET['search_query'] ?? '';
        $currentChatId = $_GET['chat_id'] ?? null;
        $userId = $this->userId;
        
        // Manejo de creación de chat privado
        if (isset($_GET['candidate_id']) && is_numeric($_GET['candidate_id'])) {
            $candidateId = (int)$_GET['candidate_id'];
            if ($candidateId == $this->userId) {
                $_SESSION['mensaje'] = "No puedes iniciar un chat privado contigo mismo.";
                $_SESSION['tipo'] = "warning";
                header("Location: " . BASE_URL . "dashboard.php");
                exit();
            }
            $newChatId = $this->model->createPrivateChat($this->userId, $candidateId);
            if ($newChatId) {
                header("Location: index.php?action=mis_chats&chat_id=" . $newChatId);
                exit();
            } else {
                $_SESSION['mensaje'] = "Error al iniciar el chat privado.";
                $_SESSION['tipo'] = "danger";
                header("Location: index.php?action=mis_chats");
                exit();
            }
        }

        // Manejo de chat de oferta (grupal o privado con reclutador)
        if (isset($_GET['id_oferta']) && is_numeric($_GET['id_oferta'])) {
            $offerId = (int)$_GET['id_oferta'];
            
            // Si es un chat privado con un usuario específico desde una oferta
            if (isset($_GET['id_usuario_privado']) && is_numeric($_GET['id_usuario_privado'])) {
                $targetUserId = (int)$_GET['id_usuario_privado'];
                $newChatId = $this->model->createPrivateChat($this->userId, $targetUserId);
            } else {
                // Si es el chat grupal de la oferta
                $newChatId = $this->model->getOrCreateOfferChat($offerId, $this->userId);
            }

            if ($newChatId) {
                header("Location: index.php?action=mis_chats&chat_id=" . $newChatId);
                exit();
            } else {
                $_SESSION['mensaje'] = "Error al acceder al chat de la oferta.";
                $_SESSION['tipo'] = "danger";
                header("Location: index.php?action=ofertas");
                exit();
            }
        }

        $conversations_raw = $this->model->getConversaciones($this->userId, $searchQuery);
        $conversations = $this->processConversations($conversations_raw);

        $messages = [];
        $currentChatData = null;
        
        $userData = $this->model->getUserData($this->userId);
        $currentUserPhoto = '';
        if ($userData && !empty($userData['foto_perfil'])) {
            $nombreArchivo = basename($userData['foto_perfil']);
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
            if (file_exists($rutaAbsoluta)) {
                $currentUserPhoto = BASE_URL . 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
            } else {
                $currentUserPhoto = BASE_URL . 'images/default-profile.jpg';
            }
        } else {
            $currentUserPhoto = BASE_URL . 'images/default-profile.jpg';
        }

        if ($currentChatId) {
            if ($this->model->isParticipant($currentChatId, $this->userId)) {
                $currentChatData = $this->model->getChatData($currentChatId);
                if ($currentChatData) {
                    $currentChatData['display_title'] = $this->getDisplayTitle($currentChatData);
                    $messages = $this->model->getMessages($currentChatId);
                }
            } else {
                $mensaje = "No tienes acceso a esta conversación.";
                $tipoMensaje = "danger";
                $currentChatId = null; 
            }
        }
        
        // Count unread notifications for sidebar
        require_once __DIR__ . '/../../models/notifiacionesModel/notificacionesModel.php';
        $notifModel = new NotificacionesModel();
        $unread_notifications_count = $notifModel->getUnreadCount($this->userId);
        
        require_once __DIR__ . '/../../views/chatsView/misChatsView.php';
    }

    private function processConversations($rawConversations) {
        $conversations = [];
        $defaultAvatar = 'https://static.thenounproject.com/png/4154905-200.png';

        foreach ($rawConversations as $conv) {
            $chatTitle = $conv['titulo_conversacion']; 
            $chatAvatar = $defaultAvatar; 

            if ($conv['tipo_conversacion'] === 'oferta_grupal' || $conv['tipo_conversacion'] === 'oferta_privada') {
                $oferta = $this->model->getOfertaTitle($conv['id_proyecto']);
                if ($oferta) {
                    $chatTitle = "Oferta: " . $oferta['titulo_oferta'];
                    // Intentar usar un icono de FontAwesome en el frontend o una imagen genérica
                    $chatAvatar = BASE_URL . 'assets/images/icon_job_offer.png'; 
                }
            } elseif ($conv['tipo_conversacion'] === 'perfil_publico') { 
                $otherParticipant = $this->model->getOtherParticipantInfo($conv['id_conversacion'], $this->userId);
                if ($otherParticipant) {
                    $chatTitle = $otherParticipant['nombre'];
                    if (!empty($otherParticipant['foto_perfil'])) {
                        $nombreArchivo = basename($otherParticipant['foto_perfil']);
                        $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
                        if (file_exists($rutaAbsoluta)) {
                            $chatAvatar = BASE_URL . 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                        }
                    }
                } else {
                    $chatTitle = "Chat Privado (Usuario Desconocido)";
                }
            } elseif ($conv['tipo_conversacion'] === 'empresa_interna') { 
                $chatTitle = $conv['titulo_conversacion'];
                $chatAvatar = BASE_URL . 'assets/images/icon_group_chat.png'; 
            }

            $conversations[] = [
                'id_conversacion' => $conv['id_conversacion'],
                'title' => $chatTitle,
                'avatar' => $chatAvatar,
                'last_message_content' => $conv['ultimo_mensaje_contenido'] ?? 'No hay mensajes.',
                'last_message_time' => $conv['ultimo_mensaje_fecha_envio'] ? (new DateTime($conv['ultimo_mensaje_fecha_envio']))->format('H:i') : '',
                'last_message_sender' => $conv['ultimo_mensaje_remitente_nombre'] ?? '',
                'type' => $conv['tipo_conversacion'],
                'is_favorite' => (bool)$conv['es_favorito'] 
            ];
        }
        return $conversations;
    }

    private function getDisplayTitle($currentChatData) {
        if ($currentChatData['tipo_conversacion'] === 'oferta_grupal' || $currentChatData['tipo_conversacion'] === 'oferta_privada') {
            $ofertaTitle = $this->model->getOfertaTitle($currentChatData['id_proyecto']);
            return "Oferta: " . ($ofertaTitle['titulo_oferta'] ?? 'Oferta Desconocida');
        } elseif ($currentChatData['tipo_conversacion'] === 'perfil_publico') { 
            $otherName = $this->model->getOtherParticipantInfo($currentChatData['id_conversacion'], $this->userId);
            return $otherName['nombre'] ?? 'Chat Privado';
        } elseif ($currentChatData['tipo_conversacion'] === 'empresa_interna') { 
            return $currentChatData['titulo_conversacion'];
        } else {
            return $currentChatData['titulo_conversacion'] ?? 'Chat';
        }
    }
}
