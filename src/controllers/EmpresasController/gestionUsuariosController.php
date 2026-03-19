<?php
require_once __DIR__ . '/../../config/configuracionInicial.php';
require_once __DIR__ . '/../../models/empresasModel/gestionUsuariosModel.php';

class GestionUsuariosController {
    private GestionUsuariosModel $model;

    public function __construct() {
        $this->model = new GestionUsuariosModel();
    }

    public function handle(int $empresaId, int $currentUserId, int $currentUserRolGlobal, ?int $currentUserRolEmpresa): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $canManageUsers = ($currentUserRolGlobal === 1) || in_array((int)$currentUserRolEmpresa, [1, 2], true);
        $canChangeRoles = ($currentUserRolGlobal === 1) || ((int)$currentUserRolEmpresa === 1);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formType = (string)($_POST['form_type'] ?? '');

            if (!$canManageUsers) {
                $_SESSION['mensaje'] = 'Acceso no autorizado para gestionar usuarios.';
                $_SESSION['tipo'] = 'danger';
                header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
                exit();
            }

            if ($formType === 'eliminar_usuario') {
                $this->handleEliminarUsuario($empresaId, $currentUserId, $currentUserRolGlobal, $currentUserRolEmpresa);
            }

            if ($formType === 'cambiar_rol') {
                $this->handleCambiarRol($empresaId, $currentUserId, $currentUserRolGlobal, $currentUserRolEmpresa, $canChangeRoles);
            }

            if ($formType === 'seguimiento_desempeno') {
                $this->handleSeguimientoDesempeno($empresaId, $currentUserId, $currentUserRolGlobal, $currentUserRolEmpresa);
            }
        }

        $usuarios = $this->model->getUsuariosEmpresa($empresaId);
        $rolesEmpresa = $this->model->getRolesEmpresa();

        return [
            'canManageUsers' => $canManageUsers,
            'canChangeRoles' => $canChangeRoles,
            'usuarios' => $usuarios,
            'rolesEmpresa' => $rolesEmpresa,
        ];
    }

    private function handleEliminarUsuario(int $empresaId, int $currentUserId, int $currentUserRolGlobal, ?int $currentUserRolEmpresa): void {
        $usuarioId = (int)($_POST['eliminar_usuario_id'] ?? 0);
        if ($usuarioId <= 0) {
            $_SESSION['mensaje'] = 'Usuario inválido.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        if ($usuarioId === $currentUserId && $currentUserRolGlobal !== 1) {
            $_SESSION['mensaje'] = 'No puedes eliminarte a ti mismo de la empresa.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        $rolObjetivo = $this->model->getRolEmpresaUsuario($usuarioId, $empresaId);
        if ($rolObjetivo === null) {
            $_SESSION['mensaje'] = 'El usuario no pertenece a esta empresa.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        if ($rolObjetivo === 1) {
            $_SESSION['mensaje'] = 'No puedes eliminar al Dueño de la empresa.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        $canRemove = ($currentUserRolGlobal === 1) || in_array((int)$currentUserRolEmpresa, [1, 2], true);
        if (!$canRemove) {
            $_SESSION['mensaje'] = 'No tienes permisos para eliminar usuarios.';
            $_SESSION['tipo'] = 'danger';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        $ok = $this->model->removeUsuarioEmpresa($usuarioId, $empresaId);
        if ($ok) {
            $this->model->updateRolGlobalAfterUnlink($usuarioId);
            $_SESSION['mensaje'] = 'Usuario eliminado correctamente de la empresa.';
            $_SESSION['tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'No se pudo eliminar el usuario.';
            $_SESSION['tipo'] = 'danger';
        }

        header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
        exit();
    }

    private function handleCambiarRol(
        int $empresaId,
        int $currentUserId,
        int $currentUserRolGlobal,
        ?int $currentUserRolEmpresa,
        bool $canChangeRoles
    ): void {
        $usuarioId = (int)($_POST['usuario_id'] ?? 0);
        $nuevoRol = (int)($_POST['nuevo_rol_empresa'] ?? 0);

        if ($usuarioId <= 0 || $nuevoRol <= 0) {
            $_SESSION['mensaje'] = 'Datos inválidos para cambiar rol.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        if (!$canChangeRoles) {
            $_SESSION['mensaje'] = 'No tienes permisos para cambiar roles.';
            $_SESSION['tipo'] = 'danger';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        $rolObjetivo = $this->model->getRolEmpresaUsuario($usuarioId, $empresaId);
        if ($rolObjetivo === null) {
            $_SESSION['mensaje'] = 'El usuario no pertenece a esta empresa.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        if ($rolObjetivo === 1) {
            $_SESSION['mensaje'] = 'No puedes cambiar el rol del Dueño.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        if ($usuarioId === $currentUserId && $currentUserRolGlobal !== 1) {
            $_SESSION['mensaje'] = 'No puedes cambiar tu propio rol.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        if (!in_array($nuevoRol, [2, 3], true)) {
            $_SESSION['mensaje'] = 'Solo se permite asignar roles Reclutador o Empleado.';
            $_SESSION['tipo'] = 'warning';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        $ok = $this->model->updateRolUsuarioEmpresa($usuarioId, $empresaId, $nuevoRol);
        if ($ok) {
            $_SESSION['mensaje'] = 'Rol actualizado correctamente.';
            $_SESSION['tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'No se pudo actualizar el rol.';
            $_SESSION['tipo'] = 'danger';
        }

        header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
        exit();
    }

    private function handleSeguimientoDesempeno(int $empresaId, int $currentUserId, int $currentUserRolGlobal, ?int $currentUserRolEmpresa): void {
        $usuarioId = (int)($_POST['id_usuario_evaluado'] ?? 0);
        $fecha = trim((string)($_POST['fecha_evaluacion'] ?? ''));
        $tipo = trim((string)($_POST['tipo_evaluacion'] ?? ''));
        $puntuacionRaw = trim((string)($_POST['puntuacion'] ?? ''));
        $comentarios = trim((string)($_POST['comentarios'] ?? ''));
        $objetivos = trim((string)($_POST['objetivos_logrados'] ?? ''));

        $errors = [];

        $canCreate = ($currentUserRolGlobal === 1) || in_array((int)$currentUserRolEmpresa, [1, 2], true);
        if (!$canCreate) {
            $errors[] = 'Acceso no autorizado para registrar desempeño.';
        }

        $rolObjetivo = $this->model->getRolEmpresaUsuario($usuarioId, $empresaId);
        if ($usuarioId <= 0 || $rolObjetivo === null) {
            $errors[] = 'Usuario evaluado inválido.';
        }

        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt || $dt->format('Y-m-d') !== $fecha) {
            $errors[] = 'Fecha de evaluación inválida.';
        }

        if ($tipo === '' || mb_strlen($tipo) > 50) {
            $errors[] = 'Tipo de evaluación inválido.';
        }

        $puntuacion = null;
        if ($puntuacionRaw !== '') {
            if (!is_numeric($puntuacionRaw)) {
                $errors[] = 'La puntuación debe ser numérica.';
            } else {
                $puntuacion = (float)$puntuacionRaw;
                if ($puntuacion < 0 || $puntuacion > 5) {
                    $errors[] = 'La puntuación debe estar entre 0 y 5.';
                }
            }
        }

        if ($comentarios !== '' && mb_strlen($comentarios) > 500) {
            $errors[] = 'Los comentarios exceden el límite de caracteres.';
        }
        if ($objetivos !== '' && mb_strlen($objetivos) > 500) {
            $errors[] = 'Los objetivos logrados exceden el límite de caracteres.';
        }

        if (!empty($errors)) {
            $_SESSION['mensaje'] = implode("\n", $errors);
            $_SESSION['tipo'] = 'danger';
            header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
            exit();
        }

        $ok = $this->model->addSeguimientoDesempeno(
            $usuarioId,
            $currentUserId,
            $fecha,
            $tipo,
            $puntuacion,
            $comentarios !== '' ? $comentarios : null,
            $objetivos !== '' ? $objetivos : null
        );

        if ($ok) {
            $_SESSION['mensaje'] = 'Seguimiento de desempeño guardado correctamente.';
            $_SESSION['tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'No se pudo guardar el seguimiento de desempeño.';
            $_SESSION['tipo'] = 'danger';
        }

        header('Location: ' . BASE_URL . 'mi_empresa?seccion=usuarios');
        exit();
    }
}
