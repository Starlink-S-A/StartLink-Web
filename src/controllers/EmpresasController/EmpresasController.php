<?php
// src/controllers/empresasController/EmpresasController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/empresasModel/crearEmpresaModel.php';

class EmpresasController {
    private $model;

    public function __construct() {
        try {
            $this->model = new EmpresasModel();
        } catch (Exception $e) {
            error_log("Error al inicializar EmpresasController: " . $e->getMessage());
        }
    }

    /**
     * Mostrar formulario de creación y procesar envío POST.
     */
    public function create() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // verificar inicio de sesión
        if (!isset($_SESSION['user_id']) || $_SESSION['loggedin'] !== true) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $data = [
            'nombre_empresa' => '',
            'descripcion' => '',
            'email_contacto' => '',
            'telefono_contacto' => '',
            'pais' => '',
            'departamento' => '',
            'ciudad' => '',
            'url_sitio_web' => '',
            'logo_ruta' => ''
        ];

        $errors = [];
        $message = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // recolectar y sanear entradas
            foreach ($data as $key => $value) {
                if (isset($_POST[$key])) {
                    $data[$key] = trim($_POST[$key]);
                }
            }

            // validaciones básicas
            if (empty($data['nombre_empresa']) || empty($data['email_contacto'])) {
                $errors[] = 'El nombre de la empresa y el correo de contacto son obligatorios.';
            } elseif (!filter_var($data['email_contacto'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'El correo de contacto no es válido.';
            } elseif (strlen($data['nombre_empresa']) > 255 || strlen($data['email_contacto']) > 255) {
                $errors[] = 'El nombre o correo exceden el límite de caracteres permitido.';
            }

            // comprobar nombre existente
            if (empty($errors) && $this->model->companyExistsByName($data['nombre_empresa'])) {
                $errors[] = 'Ya existe una empresa registrada con ese nombre.';
            }

            // manejar logo
            if (empty($errors) && isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = ROOT_PATH . 'assets/images/Uploads/logos_empresa/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $fileExt = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('logo_') . '.' . strtolower($fileExt);
                $allowed = ['jpg','jpeg','png','gif'];
                if (in_array(strtolower($fileExt), $allowed)) {
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $fileName)) {
                        $data['logo_ruta'] = $fileName;
                    } else {
                        $errors[] = 'Error al subir el archivo del logo.';
                    }
                } else {
                    $errors[] = 'Formato de archivo no permitido. Use JPG, PNG o GIF.';
                }
            }

            if (empty($errors)) {
                try {
                    $empresaId = $this->model->createCompany($data);
                    if ($empresaId) {
                        // vincular usuario y actualizar roles
                        $userId = $_SESSION['user_id'];
                        $this->model->linkUserToCompany($userId, $empresaId, 1);
                        $this->model->updateUserRoleGlobal($userId, 3);

                        $_SESSION['id_empresa'] = $empresaId;
                        $_SESSION['id_rol_empresa'] = 1;
                        $_SESSION['id_rol'] = 3;
                        $_SESSION['mensaje_empresa'] = 'Empresa registrada exitosamente.';

                        // HU-17: Registro exitoso de Empresa -> Notificar al Usuario Creador
                        require_once __DIR__ . '/../../models/notifiacionesModel/notificacionesModel.php';
                        $notifModel = new NotificacionesModel();
                        $notifModel->crearNotificacion(
                            $userId,
                            "¡Empresa " . $data['nombre_empresa'] . " registrada con éxito!",
                            'Sistema',
                            'fas fa-building',
                            BASE_URL . "index.php?action=dashboard"
                        );

                        header('Location: ' . BASE_URL . 'index.php?action=dashboard&empresa_creada=1');
                        exit();
                    } else {
                        $errors[] = 'No se pudo crear la empresa en la base de datos.';
                    }
                } catch (Exception $e) {
                    error_log('Error al crear empresa: ' . $e->getMessage());
                    $errors[] = 'Error al registrar empresa. Intenta más tarde.';
                }
            }
        }

        // preparar variables para vista
        $esContratador = false; // se calcula en vista si es necesario
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRoleGlobal = $_SESSION['id_rol'] ?? 2;
        $userRolEmpresa = $_SESSION['id_rol_empresa'] ?? null;
        
        // Determinar si es administrador de empresa para el navbar
        $esAdminEmpresa = in_array($userRolEmpresa, [1, 2]);

        $defaultImage = 'https://static.thenounproject.com/png/4154905-200.png';
        $profileImage = $defaultImage;
        if (!empty($_SESSION['foto_perfil'])) {
            $rutaAbsoluta = ROOT_PATH . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            $rutaPublica  = BASE_URL . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
            }
        }

        $unreadNotificationsCount = 0;

        require_once __DIR__ . '/../../views/EmpresasView/crearEmpresa_view.php';
    }


}
?>