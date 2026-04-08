<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/notifiacionesModel/notificacionesModel.php';

class NotificacionesController {
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
        $this->model = new NotificacionesModel();
    }

    public function index() {
        $action = $_GET['sub_action'] ?? ($_POST['sub_action'] ?? 'list');

        switch ($action) {
            case 'mark_notification_read':
                $this->markAsRead();
                break;
            case 'mark_all_notifications_read':
                $this->markAllAsRead();
                break;
            case 'delete_notification':
                $this->deleteNotification();
                break;
            case 'delete_all_notifications':
                $this->deleteAllNotifications();
                break;
            case 'redirect':
                $this->redirect();
                break;
            case 'fetch_latest_ajax':
                $this->fetchLatestAjax();
                break;
            case 'list':
            default:
                $this->showList();
                break;
        }
    }

    private function markAsRead() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationId = $_POST['notification_id'] ?? null;
            if ($notificationId && is_numeric($notificationId)) {
                $result = $this->model->markAsRead($notificationId, $this->userId);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo marcar la notificación como leída.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de notificación inválido.']);
            }
        }
        exit();
    }

    private function markAllAsRead() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->markAllAsRead($this->userId);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Todas las notificaciones han sido marcadas como leídas.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No hay notificaciones sin leer para marcar.']);
            }
        }
        exit();
    }

    private function deleteNotification() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $notificationId = $_POST['notification_id'] ?? null;
            if ($notificationId && is_numeric($notificationId)) {
                $result = $this->model->deleteNotification((int)$notificationId, $this->userId);
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Notificación eliminada.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se encontró la notificación o no tienes permiso.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'ID de notificación inválido.']);
            }
        }
        exit();
    }

    private function deleteAllNotifications() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->model->deleteAllNotifications($this->userId);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Todas las notificaciones han sido eliminadas.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No hay notificaciones para eliminar.']);
            }
        }
        exit();
    }

    private function fetchLatestAjax() {
        header('Content-Type: application/json');
        $unreadCount = $this->model->getUnreadCount($this->userId);
        $unreadChatCount = $this->model->getUnreadCountByType($this->userId, 'chat');
        $latest = $this->model->getNotificaciones($this->userId, 10);
        foreach ($latest as &$n) {
            $n = $this->processNotificationIcon($n);
        }
        echo json_encode([
            'success' => true,
            'unread_count' => $unreadCount,
            'unread_chat_count' => $unreadChatCount,
            'notifications' => $latest
        ]);
        exit();
    }

    public function redirect() {
        $notificationId = $_GET['notification_id'] ?? null;
        if ($notificationId) {
            $notification = $this->model->getNotificationById((int)$notificationId, $this->userId);
            if ($notification) {
                // Marcar como leída
                $this->model->markAsRead((int)$notificationId, $this->userId);
                
                // Redirigir si hay una URL válida, sino ir a la vista de notificaciones
                if (!empty($notification['url_redireccion']) && $notification['url_redireccion'] !== '#') {
                    $targetUrl = $notification['url_redireccion'];
                    
                    // Si es una URL interna (no empieza con http/https), anteponer BASE_URL
                    if (!preg_match('/^https?:\/\//i', $targetUrl)) {
                        // Limpiar posibles slashes duplicados
                        $targetUrl = BASE_URL . ltrim($targetUrl, '/');
                    }
                    
                    header("Location: " . $targetUrl);
                    exit();
                }
            }
        }
        
        // Redirección por defecto: SIEMPRE a la vista de notificaciones, NUNCA al dashboard
        header("Location: " . BASE_URL . "src/index.php?action=notificaciones");
        exit();
    }

    private function showList() {
        $notificationsPerPage = 10;
        $currentPage = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($currentPage - 1) * $notificationsPerPage;

        $totalNotifications = $this->model->getTotalNotificaciones($this->userId);
        $totalPages = ceil($totalNotifications / $notificationsPerPage);

        if ($currentPage > $totalPages && $totalPages > 0) {
            $currentPage = $totalPages;
            $offset = ($currentPage - 1) * $notificationsPerPage;
        } elseif ($currentPage < 1) {
            $currentPage = 1;
            $offset = 0;
        }

        $notifications = $this->model->getNotificacionesPaginated($this->userId, $notificationsPerPage, $offset);

        foreach ($notifications as &$notification) {
            $notification = $this->processNotificationIcon($notification);
        }
        unset($notification);

        $unread_notifications_count = $this->model->getUnreadCount($this->userId);

        require_once __DIR__ . '/../../views/notificacionesView/NotifiacacionesView.php';
    }

    private function processNotificationIcon($notification) {
        if (empty($notification['icono'])) {
            switch ($notification['tipo']) {
                case 'success': $notification['icono'] = 'fas fa-check-circle text-success'; break;
                case 'warning': $notification['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                case 'error': $notification['icono'] = 'fas fa-times-circle text-danger'; break;
                case 'contratacion': $notification['icono'] = 'fas fa-handshake text-info'; break;
                case 'danger': $notification['icono'] = 'fas fa-exclamation-circle text-danger'; break;
                default: $notification['icono'] = 'fas fa-info-circle text-info'; break;
            }
        } elseif (strpos($notification['icono'], 'text-') === false) {
            $notification['icono'] .= ' text-primary';
        }
        return $notification;
    }
}
