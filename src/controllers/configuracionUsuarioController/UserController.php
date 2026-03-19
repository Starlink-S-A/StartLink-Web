<?php
// src/controllers/UserController.php

require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/userModel/User.php';

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function configureProfile() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === null) {
            header('Location: ' . BASE_URL . 'bienvenida.php');
            exit();
        }

        $message = $_SESSION['message'] ?? '';
        $message_type = $_SESSION['message_type'] ?? '';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);

        $userId = $_SESSION['user_id'];
        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRole = $_SESSION['id_rol'] ?? null;

        $pdo = getDbConnection();
        $currentStep = $_GET['step'] ?? 'personal';

        // Manejar eliminación de habilidades por GET
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_skill']) && $currentStep == 'skills') {
            $skillToDelete = trim($_GET['delete_skill']);
            $errors = [];
            
            if (empty($skillToDelete)) {
                $errors[] = 'Habilidad no especificada.';
            } elseif (strlen($skillToDelete) > 100) {
                $errors[] = 'El nombre de la habilidad es demasiado largo.';
            } else {
                try {
                    // Buscar id_habilidad
                    $stmt = $pdo->prepare("SELECT id_habilidad FROM habilidad WHERE nombre_habilidad = :nombre_habilidad");
                    $stmt->execute([':nombre_habilidad' => $skillToDelete]);
                    $habilidadId = $stmt->fetchColumn();

                    if ($habilidadId) {
                        $stmt = $pdo->prepare("
                            DELETE FROM usuario_habilidad 
                            WHERE id_usuario = :id_usuario AND id_habilidad = :id_habilidad
                        ");
                        $stmt->execute([
                            ':id_usuario' => $userId,
                            ':id_habilidad' => $habilidadId
                        ]);

                        if ($stmt->rowCount() > 0) {
                            $_SESSION['message'] = 'Habilidad eliminada correctamente.';
                            $_SESSION['message_type'] = 'success';
                        } else {
                            $errors[] = 'La habilidad no está asociada a tu perfil.';
                        }
                    } else {
                        $errors[] = 'La habilidad no existe en la base de datos.';
                    }
                } catch (PDOException $e) {
                    error_log('Error al eliminar habilidad: ' . $e->getMessage());
                    $errors[] = 'Error al eliminar la habilidad: ' . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                $_SESSION['message'] = implode(' ', $errors);
                $_SESSION['message_type'] = 'danger';
            }
            
            header("Location: " . BASE_URL . "configurar_perfil?step=skills");
            exit();
        }

        // Cargar datos del perfil
        $perfilData = [];
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE id = :id_usuario");
            $stmt->bindParam(':id_usuario', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $perfilData = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$perfilData) {
                $perfilData = [
                    'id' => $userId,
                    'nombre' => '',
                    'email' => '',
                    'genero' => '',
                    'dni' => '',
                    'telefono' => '',
                    'ciudad' => '',
                    'departamento' => '',
                    'pais' => '',
                    'foto_perfil' => '',
                ];
            }
        } catch (PDOException $e) {
            error_log('Error al cargar datos del perfil para usuario ' . $userId . ': ' . $e->getMessage());
            $message = 'Error al cargar tu información de perfil. Inténtalo de nuevo más tarde.';
            $message_type = 'danger';
        }

        // Cargar datos adicionales
        $experiencias = [];
        $estudios = [];
        $habilidades = [];

        try {
            // Experiencias laborales
            $stmt = $pdo->prepare("SELECT id_experiencia, id_usuario, titulo_puesto, empresa_nombre, descripcion, fecha_inicio, fecha_fin FROM experiencia_laboral WHERE id_usuario = :id_usuario ORDER BY fecha_inicio DESC");
            $stmt->execute([':id_usuario' => $userId]);
            $experiencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Estudios académicos
            $stmt = $pdo->prepare("SELECT id_estudio, id_usuario, titulo_grado, institucion, fecha_inicio, fecha_fin, descripcion FROM estudio WHERE id_usuario = :id_usuario ORDER BY fecha_inicio DESC");
            $stmt->execute([':id_usuario' => $userId]);
            $estudios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Habilidades
            $stmt = $pdo->prepare("
                SELECT h.nombre_habilidad 
                FROM usuario_habilidad uh 
                JOIN habilidad h ON uh.id_habilidad = h.id_habilidad 
                WHERE uh.id_usuario = :id_usuario
            ");
            $stmt->execute([':id_usuario' => $userId]);
            $habilidades = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nombre_habilidad');
        } catch (PDOException $e) {
            error_log('Error al cargar datos adicionales: ' . $e->getMessage());
            if ($e->getCode() == '42S02') {
                $message = 'La tabla de estudios no existe en la base de datos. Contacta al administrador.';
            } else {
                $message = 'Error al cargar datos adicionales. Inténtalo de nuevo.';
            }
            $message_type = 'danger';
        }

        // Variables para navbar
        $esAdminEmpresa = false;
        $esTrabajadorActivo = false;
        $showPublishProfileLink = false;
        $unreadNotificationsCount = 0;
        $latestNotifications = [];

        // --- LOGICA DE RESOLUCION DE IMAGEN DE PERFIL (Consistente con misEmpresasController) ---
        $profileImage = 'https://static.thenounproject.com/png/4154905-200.png';

        // 1. Prioridad: Sesión | 2. Fallback: Base de Datos
        $fotoPath = !empty($_SESSION['foto_perfil']) ? $_SESSION['foto_perfil'] : ($perfilData['foto_perfil'] ?? null);

        if (!empty($fotoPath)) {
            $nombreArchivo = basename($fotoPath);
            // Normalizar rutas usando DIRECTORY_SEPARATOR para file_exists
            $rutaAbsoluta = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'profile_pictures' . DIRECTORY_SEPARATOR . $nombreArchivo;
            $rutaPublica  = rtrim(BASE_URL, '/') . '/assets/images/Uploads/profile_pictures/' . $nombreArchivo;

            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
                // Sincronizar sesión si vino de la BD
                if (empty($_SESSION['foto_perfil'])) {
                    $_SESSION['foto_perfil'] = 'assets/images/Uploads/profile_pictures/' . $nombreArchivo;
                }
            }
        }

        try {
            // Obtener ID rol TRABAJADOR
            $stmtRolTrabajador = $pdo->prepare("SELECT id FROM rol WHERE nombre_rol = 'TRABAJADOR'");
            $stmtRolTrabajador->execute();
            $rolTrabajadorId = $stmtRolTrabajador->fetchColumn();

            // Rol global del usuario
            $stmtUserGlobalRole = $pdo->prepare("SELECT id_rol FROM usuario WHERE id = ?");
            $stmtUserGlobalRole->execute([$userId]);
            $currentUserGlobalRole = $stmtUserGlobalRole->fetchColumn();

            if ($currentUserGlobalRole != $rolTrabajadorId) {
                $showPublishProfileLink = true;
            }

            // Lógica de empresa
            if (isset($_SESSION['id_empresa']) && isset($_SESSION['id_rol_empresa'])) {
                $userCompanyRole = $_SESSION['id_rol_empresa'];
                if (in_array($userCompanyRole, [1, 2])) {
                    $esAdminEmpresa = true;
                }
                if (in_array($userCompanyRole, [2, 3])) {
                    $esTrabajadorActivo = true;
                }
            } else {
                $stmtCompanyAssociation = $pdo->prepare("
                    SELECT id_empresa, id_rol_empresa 
                    FROM usuario_empresa 
                    WHERE id_usuario = ? 
                    ORDER BY id_rol_empresa ASC 
                    LIMIT 1
                ");
                $stmtCompanyAssociation->execute([$userId]);
                $userCompanyAssociation = $stmtCompanyAssociation->fetch(PDO::FETCH_ASSOC);

                if ($userCompanyAssociation) {
                    $_SESSION['id_empresa'] = $userCompanyAssociation['id_empresa'];
                    $_SESSION['id_rol_empresa'] = $userCompanyAssociation['id_rol_empresa'];
                    $userCompanyRole = $userCompanyAssociation['id_rol_empresa'];
                    if (in_array($userCompanyRole, [1, 2])) {
                        $esAdminEmpresa = true;
                    }
                    if (in_array($userCompanyRole, [2, 3])) {
                        $esTrabajadorActivo = true;
                    }
                }
            }

            // Respaldo para trabajador global
            if ($userRole == $rolTrabajadorId) {
                $esTrabajadorActivo = true;
            }

            // Notificaciones
            $stmtNotifications = $pdo->prepare("
                SELECT id, mensaje, tipo, icono, fecha_creacion, leida, url_redireccion, postulacion_id, solicitud_contratacion_id
                FROM notificaciones
                WHERE user_id = ?
                ORDER BY fecha_creacion DESC
                LIMIT 5
            ");
            $stmtNotifications->execute([$userId]);
            $latestNotifications = $stmtNotifications->fetchAll(PDO::FETCH_ASSOC);

            foreach ($latestNotifications as &$notification) {
                if (empty($notification['icono'])) {
                    switch ($notification['tipo']) {
                        case 'success': $notification['icono'] = 'fas fa-check-circle text-success'; break;
                        case 'warning': $notification['icono'] = 'fas fa-exclamation-triangle text-warning'; break;
                        case 'error': $notification['icono'] = 'fas fa-times-circle text-danger'; break;
                        default: $notification['icono'] = 'fas fa-info-circle text-info'; break;
                    }
                } elseif (strpos($notification['icono'], 'text-') === false) {
                    $notification['icono'] .= ' text-primary';
                }
                if (!$notification['leida']) {
                    $unreadNotificationsCount++;
                }
            }

        } catch (PDOException $e) {
            error_log("Error en UserController (navbar vars): " . $e->getMessage());
            $esTrabajadorActivo = ($userRole == 3 || in_array($_SESSION['id_rol_empresa'] ?? null, [2, 3]));
        }

        // Procesar formulario POST
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $errors = [];
            $formType = $_POST['form_type'] ?? '';

            switch ($formType) {
                case 'personal_info':
                    $nombre = trim($_POST['nombre'] ?? '');
                    $email = trim($_POST['email'] ?? '');
                    $genero = trim($_POST['genero'] ?? '');
                    $telefono = trim($_POST['telefono'] ?? '');
                    $ciudad = trim($_POST['ciudad'] ?? '');
                    $departamento = trim($_POST['departamento'] ?? '');
                    $pais = trim($_POST['pais'] ?? '');
                    $dni = trim($_POST['dni'] ?? '');
                    $fotoPerfilPath = $perfilData['foto_perfil'] ?? null;

                    // Validaciones
                    if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';
                    if (empty($email)) $errors[] = 'El correo electrónico es obligatorio.';
                    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'El correo electrónico no es válido.';
                    if (empty($genero)) $errors[] = 'El género es obligatorio.';
                    if (!empty($telefono) && !preg_match('/^[0-9+\-\(\) ]{7,15}$/', $telefono)) $errors[] = 'El teléfono no es válido.';
                    if (!empty($dni) && !preg_match('/^[0-9]{8,12}$/', $dni)) $errors[] = 'El DNI no es válido.';
                    if (empty($ciudad)) $errors[] = 'La ciudad es obligatoria.';
                    if (empty($pais)) $errors[] = 'El país es obligatorio.';

                    // Verificar unicidad del email
                    try {
                        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE email = :email AND id != :id_usuario");
                        $stmt->execute([':email' => $email, ':id_usuario' => $userId]);
                        if ($stmt->fetch()) {
                            $errors[] = 'El correo electrónico ya está registrado para otro usuario.';
                        }
                    } catch (PDOException $e) {
                        error_log('Error al verificar unicidad del email: ' . $e->getMessage());
                        $errors[] = 'Error al verificar el correo electrónico. Inténtalo de nuevo.';
                    }

                    // Verificar unicidad del DNI
                    if (!empty($dni)) {
                        try {
                            $stmt = $pdo->prepare("SELECT id FROM usuario WHERE dni = :dni AND id != :id_usuario");
                            $stmt->execute([':dni' => $dni, ':id_usuario' => $userId]);
                            if ($stmt->fetch()) {
                                $errors[] = 'El DNI ya está registrado para otro usuario.';
                            }
                        } catch (PDOException $e) {
                            error_log('Error al verificar unicidad del DNI: ' . $e->getMessage());
                            $errors[] = 'Error al verificar el DNI. Inténtalo de nuevo.';
                        }
                    }

                    // Manejo de la foto de perfil
                    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == UPLOAD_ERR_OK) {
                        $fileTmpPath = $_FILES['foto_perfil']['tmp_name'];
                        $fileName = $_FILES['foto_perfil']['name'];
                        $fileSize = $_FILES['foto_perfil']['size'];
                        $fileType = $_FILES['foto_perfil']['type'];
                        $fileNameCmps = explode(".", $fileName);
                        $fileExtension = strtolower(end($fileNameCmps));

                        $allowedFileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        $maxFileSize = 5 * 1024 * 1024; // 5 MB

                        if (in_array($fileExtension, $allowedFileExtensions)) {
                            if ($fileSize <= $maxFileSize) {
                                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                                $uploadFileDir = ROOT_PATH . '/assets/images/Uploads/profile_pictures/';
                                $destPath = $uploadFileDir . $newFileName;

                                if (!is_dir($uploadFileDir)) {
                                    mkdir($uploadFileDir, 0755, true);
                                }

                                if (move_uploaded_file($fileTmpPath, $destPath)) {
                                    $fotoPerfilPath = 'assets/images/Uploads/profile_pictures/' . $newFileName;
                                    if (!empty($perfilData['foto_perfil']) && $perfilData['foto_perfil'] != 'default.jpg' && file_exists(ROOT_PATH . '/' . $perfilData['foto_perfil'])) {
                                        unlink(ROOT_PATH . '/' . $perfilData['foto_perfil']);
                                    }
                                    $_SESSION['foto_perfil'] = $fotoPerfilPath;
                                } else {
                                    $errors[] = 'Error al mover la foto de perfil. Verifica permisos de escritura.';
                                }
                            } else {
                                $errors[] = 'La foto de perfil excede el tamaño máximo (5MB).';
                            }
                        } else {
                            $errors[] = 'Formato de imagen no permitido. Usa JPG, JPEG, PNG o GIF.';
                        }
                    } elseif (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] != UPLOAD_ERR_NO_FILE) {
                        $errors[] = 'Error al subir la foto de perfil.';
                    }

                    // Actualizar base de datos
                    if (empty($errors)) {
                        try {
                            $stmt = $pdo->prepare("
                                UPDATE usuario SET 
                                    nombre = :nombre,
                                    email = :email,
                                    genero = :genero,
                                    dni = :dni,
                                    telefono = :telefono,
                                    ciudad = :ciudad,
                                    departamento = :departamento,
                                    pais = :pais,
                                    foto_perfil = :foto_perfil
                                WHERE id = :id_usuario
                            ");
                            $stmt->execute([
                                ':nombre' => $nombre,
                                ':email' => $email,
                                ':genero' => $genero,
                                ':dni' => $dni ?: null,
                                ':telefono' => $telefono ?: null,
                                ':ciudad' => $ciudad,
                                ':departamento' => $departamento ?: null,
                                ':pais' => $pais,
                                ':foto_perfil' => $fotoPerfilPath,
                                ':id_usuario' => $userId
                            ]);

                            $_SESSION['user_name'] = $nombre;
                            $_SESSION['user_email'] = $email;

                            $_SESSION['message'] = 'Información personal actualizada correctamente.';
                            $_SESSION['message_type'] = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=personal");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al actualizar perfil: ' . $e->getMessage());
                            if ($e->getCode() == '23000') {
                                $errors[] = 'El DNI o el correo electrónico ya está registrado.';
                            } else {
                                $errors[] = 'Error al guardar la información personal: ' . $e->getMessage();
                            }
                        }
                    }
                    break;

                case 'change_password':
                    $newPassword = trim($_POST['new_password'] ?? '');
                    $confirmPassword = trim($_POST['confirm_password'] ?? '');

                    if (empty($newPassword) || empty($confirmPassword)) {
                        $errors[] = 'Los campos de contraseña son obligatorios.';
                    }
                    if ($newPassword !== $confirmPassword) {
                        $errors[] = 'Las contraseñas no coinciden.';
                    }
                    if (strlen($newPassword) < 8) {
                        $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
                    }

                    if (empty($errors)) {
                        try {
                            $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("UPDATE usuario SET contrasena_hash = :contrasena_hash WHERE id = :id_usuario");
                            $stmt->execute([
                                ':contrasena_hash' => $passwordHash,
                                ':id_usuario' => $userId
                            ]);

                            $_SESSION['message'] = 'Contraseña actualizada correctamente.';
                            $_SESSION['message_type'] = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=personal");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al cambiar contraseña: ' . $e->getMessage());
                            $errors[] = 'Error al cambiar la contraseña: ' . $e->getMessage();
                        }
                    }
                    break;

                case 'add_experience':
                    $cargo = trim($_POST['cargo'] ?? '');
                    $empresa = trim($_POST['empresa'] ?? '');
                    $descripcion = trim($_POST['descripcion'] ?? '');
                    $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
                    $fechaFin = trim($_POST['fecha_fin'] ?? '');

                    // Validaciones
                    if (empty($cargo)) $errors[] = 'El cargo es obligatorio.';
                    if (empty($empresa)) $errors[] = 'La empresa es obligatoria.';
                    if (empty($fechaInicio)) $errors[] = 'La fecha de inicio es obligatoria.';
                    if (strlen($cargo) > 255) $errors[] = 'El cargo no puede exceder los 255 caracteres.';
                    if (strlen($empresa) > 255) $errors[] = 'La empresa no puede exceder los 255 caracteres.';
                    if (!empty($fechaInicio) && !DateTime::createFromFormat('Y-m-d', $fechaInicio)) {
                        $errors[] = 'La fecha de inicio no tiene un formato válido.';
                    }
                    if (!empty($fechaFin) && !DateTime::createFromFormat('Y-m-d', $fechaFin)) {
                        $errors[] = 'La fecha de fin no tiene un formato válido.';
                    }
                    if (!empty($fechaFin) && strtotime($fechaFin) < strtotime($fechaInicio)) {
                        $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
                    }

                    // Verificar que el usuario existe
                    try {
                        $stmt = $pdo->prepare("SELECT id FROM usuario WHERE id = :id_usuario");
                        $stmt->execute([':id_usuario' => $userId]);
                        if (!$stmt->fetch()) {
                            $errors[] = 'Usuario no encontrado en la base de datos.';
                        }
                    } catch (PDOException $e) {
                        error_log('Error al verificar usuario: ' . $e->getMessage());
                        $errors[] = 'Error al verificar el usuario: ' . $e->getMessage();
                    }

                    if (empty($errors)) {
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO experiencia_laboral (id_usuario, titulo_puesto, empresa_nombre, descripcion, fecha_inicio, fecha_fin)
                                VALUES (:id_usuario, :titulo_puesto, :empresa_nombre, :descripcion, :fecha_inicio, :fecha_fin)
                            ");
                            $stmt->execute([
                                ':id_usuario' => $userId,
                                ':titulo_puesto' => $cargo,
                                ':empresa_nombre' => $empresa,
                                ':descripcion' => $descripcion,
                                ':fecha_inicio' => $fechaInicio,
                                ':fecha_fin' => $fechaFin ?: null
                            ]);

                            $message = 'Experiencia laboral añadida correctamente.';
                            $message_type = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=experience");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al añadir experiencia: ' . $e->getMessage());
                            $errors[] = 'Error al añadir la experiencia laboral: ' . $e->getMessage();
                        }
                    }
                    break;

                case 'delete_experience':
                    $experienceId = $_POST['experience_id'] ?? '';
                    if (empty($experienceId)) $errors[] = 'ID de experiencia no proporcionado.';

                    if (empty($errors)) {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM experiencia_laboral WHERE id_experiencia = :id_experiencia AND id_usuario = :id_usuario");
                            $stmt->execute([':id_experiencia' => $experienceId, ':id_usuario' => $userId]);

                            $message = 'Experiencia laboral eliminada correctamente.';
                            $message_type = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=experience");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al eliminar experiencia: ' . $e->getMessage());
                            $errors[] = 'Error al eliminar la experiencia laboral: ' . $e->getMessage();
                        }
                    }
                    break;

                case 'add_education':
                    $titulo = trim($_POST['titulo'] ?? '');
                    $institucion = trim($_POST['institucion'] ?? '');
                    $fechaInicio = trim($_POST['fecha_inicio'] ?? '');
                    $fechaFin = trim($_POST['fecha_fin'] ?? '');

                    if (empty($titulo)) $errors[] = 'El título es obligatorio.';
                    if (empty($institucion)) $errors[] = 'La institución es obligatoria.';
                    if (empty($fechaInicio)) $errors[] = 'La fecha de inicio es obligatoria.';
                    if (!empty($fechaFin) && strtotime($fechaFin) < strtotime($fechaInicio)) {
                        $errors[] = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
                    }

                    if (empty($errors)) {
                        try {
                            $stmt = $pdo->prepare("
                                INSERT INTO estudio (id_usuario, titulo_grado, institucion, fecha_inicio, fecha_fin)
                                VALUES (:id_usuario, :titulo_grado, :institucion, :fecha_inicio, :fecha_fin)
                            ");
                            $stmt->execute([
                                ':id_usuario' => $userId,
                                ':titulo_grado' => $titulo,
                                ':institucion' => $institucion,
                                ':fecha_inicio' => $fechaInicio,
                                ':fecha_fin' => $fechaFin ?: null
                            ]);

                            $message = 'Estudio académico añadido correctamente.';
                            $message_type = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=education");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al añadir estudio: ' . $e->getMessage());
                            $errors[] = 'Error al añadir el estudio académico: ' . $e->getMessage();
                        }
                    }
                    break;

                case 'delete_education':
                    $educationId = $_POST['education_id'] ?? '';
                    if (empty($educationId)) $errors[] = 'ID de estudio no proporcionado.';

                    if (empty($errors)) {
                        try {
                            $stmt = $pdo->prepare("DELETE FROM estudio WHERE id_estudio = :id_estudio AND id_usuario = :id_usuario");
                            $stmt->execute([':id_estudio' => $educationId, ':id_usuario' => $userId]);

                            $message = 'Estudio académico eliminado correctamente.';
                            $message_type = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=education");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al eliminar estudio: ' . $e->getMessage());
                            $errors[] = 'Error al eliminar el estudio académico: ' . $e->getMessage();
                        }
                    }
                    break;

                case 'add_skill':
                    $newSkill = trim($_POST['new_skill'] ?? '');
                    if (empty($newSkill)) $errors[] = 'La habilidad no puede estar vacía.';
                    if (in_array($newSkill, $habilidades)) $errors[] = 'La habilidad ya está en tu lista.';
                    if (strlen($newSkill) > 100) $errors[] = 'La habilidad no puede exceder los 100 caracteres.';

                    if (empty($errors)) {
                        try {
                            // Verificar si la habilidad existe en HABILIDAD
                            $stmt = $pdo->prepare("SELECT id_habilidad FROM habilidad WHERE nombre_habilidad = :nombre_habilidad");
                            $stmt->execute([':nombre_habilidad' => $newSkill]);
                            $habilidadId = $stmt->fetchColumn();

                            if (!$habilidadId) {
                                // Insertar nueva habilidad en HABILIDAD
                                $stmt = $pdo->prepare("INSERT INTO habilidad (nombre_habilidad) VALUES (:nombre_habilidad)");
                                $stmt->execute([':nombre_habilidad' => $newSkill]);
                                $habilidadId = $pdo->lastInsertId();
                            }

                            // Asociar habilidad al usuario en USUARIO_HABILIDAD
                            $stmt = $pdo->prepare("
                                INSERT INTO usuario_habilidad (id_usuario, id_habilidad, nivel_dominio)
                                VALUES (:id_usuario, :id_habilidad, :nivel_dominio)
                            ");
                            $stmt->execute([
                                ':id_usuario' => $userId,
                                ':id_habilidad' => $habilidadId,
                                ':nivel_dominio' => 'Intermedio' // Valor por defecto
                            ]);

                            $message = 'Habilidad añadida correctamente.';
                            $message_type = 'success';
                            header("Location: " . BASE_URL . "configurar_perfil?step=skills");
                            exit();
                        } catch (PDOException $e) {
                            error_log('Error al añadir habilidad: ' . $e->getMessage());
                            $errors[] = 'Error al añadir la habilidad: ' . $e->getMessage();
                        }
                    }
                    break;

                case 'delete_skill':
                    $skillToDelete = trim($_GET['delete_skill'] ?? '');
                    if (empty($skillToDelete)) {
                        $errors[] = 'Habilidad no especificada.';
                    } elseif (strlen($skillToDelete) > 100) {
                        $errors[] = 'El nombre de la habilidad es demasiado largo.';
                    } else {
                        error_log("Intentando eliminar habilidad: '$skillToDelete' para usuario ID: $userId");
                        try {
                            // Buscar id_habilidad
                            $stmt = $pdo->prepare("SELECT id_habilidad FROM habilidad WHERE nombre_habilidad = :nombre_habilidad");
                            $stmt->execute([':nombre_habilidad' => $skillToDelete]);
                            $habilidadId = $stmt->fetchColumn();

                            error_log("Consulta ejecutada: SELECT id_habilidad FROM habilidad WHERE nombre_habilidad = '$skillToDelete'; Resultado: " . ($habilidadId ? $habilidadId : 'No encontrado'));

                            if ($habilidadId) {
                                $stmt = $pdo->prepare("
                                    DELETE FROM usuario_habilidad 
                                    WHERE id_usuario = :id_usuario AND id_habilidad = :id_habilidad
                                ");
                                $stmt->execute([
                                    ':id_usuario' => $userId,
                                    ':id_habilidad' => $habilidadId
                                ]);

                                error_log("Consulta ejecutada: DELETE FROM usuario_habilidad WHERE id_usuario = $userId AND id_habilidad = $habilidadId; Filas afectadas: " . $stmt->rowCount());

                                if ($stmt->rowCount() > 0) {
                                    $message = 'Habilidad eliminada correctamente.';
                                    $message_type = 'success';
                                } else {
                                    $errors[] = 'La habilidad no está asociada a tu perfil.';
                                    error_log("No se eliminó ninguna fila en USUARIO_HABILIDAD para habilidad ID: $habilidadId, usuario ID: $userId");
                                }
                            } else {
                                $errors[] = 'La habilidad no existe en la base de datos.';
                                error_log("Habilidad '$skillToDelete' no encontrada en la tabla HABILIDAD");
                            }
                        } catch (PDOException $e) {
                            error_log('Error al eliminar habilidad: ' . $e->getMessage());
                            $errors[] = 'Error al eliminar la habilidad: ' . $e->getMessage();
                        }
                    }

                    if (empty($errors)) {
                        header("Location: " . BASE_URL . "configurar_perfil?step=skills");
                        exit();
                    }
                    break;
            }

            if (!empty($errors)) {
                $message = implode(' ', $errors);
                $message_type = 'danger';
                $_SESSION['message'] = $message;
                $_SESSION['message_type'] = $message_type;
                header("Location: " . BASE_URL . "configurar_perfil?step=" . $currentStep);
                exit();
            }
        }

        error_log("Session in UserController: " . print_r($_SESSION, true));
        require_once __DIR__ . '/../../views/userView/configurar_perfil_view.php';
    }
}
?>