<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/empresasModel/EmpresaInfoModel.php';

class EmpresaInfoController {
    private EmpresaInfoModel $model;

    public function __construct() {
        $this->model = new EmpresaInfoModel();
    }

    public function show() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['loggedin'] !== true) {
            header('Location: ' . BASE_URL);
            exit();
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $userRoleGlobal = (int)($_SESSION['id_rol'] ?? 2);
        $empresaId = isset($_SESSION['id_empresa']) ? (int)$_SESSION['id_empresa'] : null;
        $userRolEmpresa = isset($_SESSION['id_rol_empresa']) ? (int)$_SESSION['id_rol_empresa'] : null;

        if (!$empresaId) {
            $_SESSION['mensaje'] = 'Por favor, selecciona una empresa para gestionar.';
            header('Location: ' . BASE_URL . 'mis_empresas');
            exit();
        }

        $canEdit = ($userRoleGlobal === 1) || ($userRolEmpresa === 1);
        $seccion = (string)($_GET['seccion'] ?? 'informacion');
        if (!in_array($seccion, ['informacion', 'usuarios'], true)) {
            $seccion = 'informacion';
        }

        $errors = [];
        $fieldErrors = [];
        $mensaje = $_SESSION['mensaje'] ?? null;
        $tipoMensaje = $_SESSION['tipo'] ?? 'success';
        unset($_SESSION['mensaje'], $_SESSION['tipo']);

        $empresa = $this->model->getEmpresaWithAuditById($empresaId);
        if (!$empresa) {
            $_SESSION['mensaje'] = 'Empresa no encontrada.';
            $_SESSION['tipo'] = 'danger';
            header('Location: ' . BASE_URL . 'mis_empresas');
            exit();
        }

        if ($seccion === 'usuarios') {
            require_once __DIR__ . '/gestionUsuariosController.php';
            $gestionUsuariosController = new GestionUsuariosController();
            $gestionUsuariosData = $gestionUsuariosController->handle($empresaId, $userId, $userRoleGlobal, $userRolEmpresa);

            $canManageUsers = $gestionUsuariosData['canManageUsers'] ?? false;
            $canChangeRoles = $gestionUsuariosData['canChangeRoles'] ?? false;
            $usuarios = $gestionUsuariosData['usuarios'] ?? [];
            $rolesEmpresa = $gestionUsuariosData['rolesEmpresa'] ?? [];
        }

        $data = [
            'nombre_empresa' => $empresa['nombre_empresa'] ?? '',
            'descripcion' => $empresa['descripcion'] ?? '',
            'email_contacto' => $empresa['email_contacto'] ?? '',
            'telefono_contacto' => $empresa['telefono_contacto'] ?? '',
            'url_sitio_web' => $empresa['url_sitio_web'] ?? '',
        ];

        $uploadDir = rtrim(ROOT_PATH, '/\\') . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'Uploads' . DIRECTORY_SEPARATOR . 'logos_empresa' . DIRECTORY_SEPARATOR;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $seccion === 'informacion') {
            if (!$canEdit) {
                $errors[] = 'No tienes permisos para editar la información de la empresa.';
            }

            foreach ($data as $key => $value) {
                if (isset($_POST[$key])) {
                    $data[$key] = trim((string)$_POST[$key]);
                }
            }

            if ($data['nombre_empresa'] === '') {
                $fieldErrors['nombre_empresa'] = 'El nombre de la empresa es obligatorio.';
            } elseif (mb_strlen($data['nombre_empresa']) > 255) {
                $fieldErrors['nombre_empresa'] = 'El nombre de la empresa excede el límite de caracteres.';
            }

            if ($data['email_contacto'] === '') {
                $fieldErrors['email_contacto'] = 'El correo de contacto es obligatorio.';
            } elseif (!filter_var($data['email_contacto'], FILTER_VALIDATE_EMAIL)) {
                $fieldErrors['email_contacto'] = 'El correo de contacto no es válido.';
            } elseif (mb_strlen($data['email_contacto']) > 255) {
                $fieldErrors['email_contacto'] = 'El correo de contacto excede el límite de caracteres.';
            }

            if ($data['telefono_contacto'] !== '' && mb_strlen($data['telefono_contacto']) > 50) {
                $fieldErrors['telefono_contacto'] = 'El teléfono excede el límite de caracteres.';
            }
            if ($data['telefono_contacto'] !== '' && !preg_match('/^\d+$/', $data['telefono_contacto'])) {
                $fieldErrors['telefono_contacto'] = 'El teléfono solo debe contener números.';
            }

            if ($data['url_sitio_web'] !== '' && !filter_var($data['url_sitio_web'], FILTER_VALIDATE_URL)) {
                $fieldErrors['url_sitio_web'] = 'La URL del sitio web no es válida.';
            } elseif (mb_strlen($data['url_sitio_web']) > 255) {
                $fieldErrors['url_sitio_web'] = 'La URL del sitio web excede el límite de caracteres.';
            }

            $newLogoFileName = null;

            $shouldReplaceLogo = isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE;
            if (empty($errors) && empty($fieldErrors) && $shouldReplaceLogo) {
                if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                    $errors[] = 'Error al subir el logo.';
                } else {
                    $maxBytes = 5 * 1024 * 1024;
                    if ((int)$_FILES['logo']['size'] > $maxBytes) {
                        $errors[] = 'El logo excede el tamaño máximo permitido (5 MB).';
                    } else {
                        $fileExt = strtolower((string)pathinfo((string)$_FILES['logo']['name'], PATHINFO_EXTENSION));
                        $allowedExt = ['jpg', 'jpeg', 'png'];
                        if (!in_array($fileExt, $allowedExt, true)) {
                            $errors[] = 'Formato de logo no permitido. Usa JPG o PNG.';
                        } else {
                            $finfo = new finfo(FILEINFO_MIME_TYPE);
                            $mime = $finfo->file($_FILES['logo']['tmp_name']);
                            $allowedMime = ['image/jpeg', 'image/png'];
                            if (!in_array($mime, $allowedMime, true)) {
                                $errors[] = 'El archivo del logo no es una imagen JPG/PNG válida.';
                            } else {
                                if (!file_exists($uploadDir)) {
                                    mkdir($uploadDir, 0755, true);
                                }
                                $newLogoFileName = uniqid('logo_', true) . '.' . ($fileExt === 'jpeg' ? 'jpg' : $fileExt);
                                if (!move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $newLogoFileName)) {
                                    $errors[] = 'No se pudo guardar el logo en el servidor.';
                                    $newLogoFileName = null;
                                }
                            }
                        }
                    }
                }
            }

            if (empty($errors) && empty($fieldErrors)) {
                if ($this->model->companyExistsByNameExcludingId($data['nombre_empresa'], $empresaId)) {
                    $fieldErrors['nombre_empresa'] = 'Ya existe otra empresa registrada con ese nombre.';
                }
            }

            if (empty($errors) && empty($fieldErrors)) {
                $oldLogo = $empresa['logo_ruta'] ?? null;
                $ok = $this->model->updateEmpresa($empresaId, $data, $newLogoFileName, $userId);

                if ($ok) {
                    if ($newLogoFileName && $oldLogo && $oldLogo !== $newLogoFileName) {
                        $oldPath = $uploadDir . basename((string)$oldLogo);
                        if (file_exists($oldPath)) {
                            @unlink($oldPath);
                        }
                    }

                    $_SESSION['mensaje'] = 'Información de la empresa actualizada correctamente.';
                    $_SESSION['tipo'] = 'success';
                    header('Location: ' . BASE_URL . 'mi_empresa');
                    exit();
                }

                $errors[] = 'No se pudo actualizar la información. Intenta más tarde.';
            } else {
                if ($newLogoFileName) {
                    $newPath = $uploadDir . basename($newLogoFileName);
                    if (file_exists($newPath)) {
                        @unlink($newPath);
                    }
                }
            }
        }

        $empresa = $this->model->getEmpresaWithAuditById($empresaId);

        $logoUrl = null;
        if (!empty($empresa['logo_ruta'])) {
            $nombreArchivo = basename((string)$empresa['logo_ruta']);
            $rutaAbsoluta = $uploadDir . $nombreArchivo;
            $rutaPublica = rtrim(BASE_URL, '/') . '/assets/images/Uploads/logos_empresa/' . $nombreArchivo;
            if (file_exists($rutaAbsoluta)) {
                $logoUrl = $rutaPublica;
            }
        }

        $userName = $_SESSION['user_name'] ?? 'Usuario';
        $userRolEmpresaForNav = $_SESSION['id_rol_empresa'] ?? null;
        $esAdminEmpresa = in_array((int)$userRolEmpresaForNav, [1, 2], true);

        $defaultImage = 'https://static.thenounproject.com/png/4154905-200.png';
        $profileImage = $defaultImage;
        if (!empty($_SESSION['foto_perfil'])) {
            $rutaAbsoluta = ROOT_PATH . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            $rutaPublica = BASE_URL . 'assets/images/Uploads/profile_pictures/' . basename($_SESSION['foto_perfil']);
            if (file_exists($rutaAbsoluta)) {
                $profileImage = $rutaPublica;
            }
        }

        $unreadNotificationsCount = 0;
        $showPublishProfileLink = true;

        require_once __DIR__ . '/../../views/EmpresasView/empresa_info_view.php';
    }
}
