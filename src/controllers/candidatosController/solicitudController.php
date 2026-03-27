<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';

class SolicitudController {
    public function verSolicitud() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php');
            exit();
        }
        
        $solicitudId = $_GET['id'] ?? 0;
        $userId = $_SESSION['user_id'];
        
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT sc.*, e.nombre_empresa, e.descripcion, e.url_sitio_web, e.logo_ruta
            FROM solicitud_contratacion sc
            JOIN empresa e ON sc.id_empresa = e.id_empresa
            WHERE sc.id = ? AND sc.id_candidato = ?
        ");
        $stmt->execute([$solicitudId, $userId]);
        $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$solicitud) {
            die("Solicitud no encontrada o no tienes permisos.");
        }
        
        require_once __DIR__ . '/../../views/candidatosView/verSolicitudView.php';
    }

    public function responderSolicitud() {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'No session'], 401);
            return;
        }
        
        $solicitudId = $_POST['id'] ?? 0;
        $respuesta = $_POST['respuesta'] ?? '';
        $userId = $_SESSION['user_id'];
        
        if (!in_array($respuesta, ['aceptada', 'rechazada'])) {
            $this->json(['success' => false, 'message' => 'Respuesta inválida'], 400);
            return;
        }
        
        $pdo = getDbConnection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT * FROM solicitud_contratacion WHERE id = ? AND id_candidato = ? FOR UPDATE");
            $stmt->execute([$solicitudId, $userId]);
            $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$solicitud) {
                throw new Exception("Solicitud no encontrada.");
            }
            if ($solicitud['estado'] !== 'pendiente') {
                throw new Exception("Esta solicitud ya ha sido respondida.");
            }
            
            // Actualizar estado
            $stmtUpdate = $pdo->prepare("UPDATE solicitud_contratacion SET estado = ? WHERE id = ?");
            $stmtUpdate->execute([$respuesta, $solicitudId]);
            
            if ($respuesta === 'aceptada') {
                // Insertar en usuario_empresa si no existe
                $stmtCheck = $pdo->prepare("SELECT 1 FROM usuario_empresa WHERE id_usuario = ? AND id_empresa = ?");
                $stmtCheck->execute([$userId, $solicitud['id_empresa']]);
                if (!$stmtCheck->fetchColumn()) {
                    $stmtIns = $pdo->prepare("
                        INSERT INTO usuario_empresa (id_usuario, id_empresa, id_rol_empresa, horas_semanales_estandar)
                        VALUES (?, ?, 3, ?)
                    ");
                    $stmtIns->execute([$userId, $solicitud['id_empresa'], $solicitud['horas_semanales_estandar']]);
                    
                    if ($solicitud['salario_base'] !== null) {
                        $stmtSal = $pdo->prepare("UPDATE usuario SET salario_base = ? WHERE id = ?");
                        $stmtSal->execute([$solicitud['salario_base'], $userId]);
                    }
                    $stmtStart = $pdo->prepare("UPDATE usuario SET fecha_ingreso = COALESCE(fecha_ingreso, CURDATE()) WHERE id = ?");
                    $stmtStart->execute([$userId]);
                    
                    $stmtHide = $pdo->prepare("UPDATE perfil_busqueda_empleo SET esta_disponible = 0 WHERE id_usuario = ?");
                    $stmtHide->execute([$userId]);
                }
            }
            
            $pdo->commit();
            $this->json(['success' => true, 'message' => 'Solicitud ' . $respuesta . ' exitosamente.']);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
    
    private function json(array $data, int $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
