<?php
// src/index.php

require_once __DIR__ . '/controllers/authController/AuthController.php';
require_once __DIR__ . '/controllers/dashboardController/sideBarController.php';
require_once __DIR__ . '/controllers/configuracionusuarioController/UserController.php';


$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $authController = new AuthController();
        $authController->login();
        break;

    case 'register':
        $authController = new AuthController();
        $authController->register();
        break;

    case 'forgotPassword':   // ✅ Solicitar enlace de recuperación
        $authController = new AuthController();
        $authController->forgotPassword();
        break;

    case 'resetPassword':    // ✅ Restablecer la contraseña con token
        $authController = new AuthController();
        $authController->resetPassword();
        break;

    case 'dashboard':
        $dashboardController = new DashboardController();
        $dashboardController->showDashboard();
        break;

    case 'configurar_perfil':
        $userController = new UserController();
        $userController->configureProfile();
        break;

    case 'logout':
        $authController = new AuthController();
        $authController->logout();
        break;

    case 'ofertas':
        $dashboardController = new DashboardController();
        $dashboardController->showOfertas();
        break;

    case 'crearEmpresa':
        require_once __DIR__ . '/controllers/empresasController/EmpresasController.php';
        $empresasController = new EmpresasController();
        $empresasController->create();
        break;

    case 'mis_empresas':
        require_once __DIR__ . '/controllers/EmpresasController/misEmpresasController.php';
        $misEmpresasController = new MisEmpresasController();
        $misEmpresasController->misEmpresas();
        break;

    case 'mi_empresa':
        require_once __DIR__ . '/controllers/EmpresasController/EmpresaInfoController.php';
        $empresaInfoController = new EmpresaInfoController();
        $empresaInfoController->show();
        break;

    case 'mis_equipos':
        require_once __DIR__ . '/controllers/EmpresasController/misEquiposController.php';
        $misEquiposController = new MisEquiposController();
        $misEquiposController->index();
        break;

    case 'mi_equipo':
        require_once __DIR__ . '/controllers/EmpresasController/miEquipoController.php';
        $miEquipoController = new MiEquipoController();
        $miEquipoController->show();
        break;

    case 'salir_oferta':
        require_once __DIR__ . '/controllers/ofertasController/ofertasController.php';
        require_once __DIR__ . '/models/ofertasModel/detallesOfertasModel.php';
        $ofertasController = new OfertasController();
        $ofertasController->salirOferta();
        break;

    case 'eliminar_oferta':
        require_once __DIR__ . '/controllers/ofertasController/ofertasController.php';
        $ofertasController = new OfertasController();
        $ofertasController->deleteOferta();
        break;

    case 'postular':
        require_once __DIR__ . '/controllers/ofertasController/ofertasController.php';
        $ofertasController = new OfertasController();
        $ofertasController->postular();
        break;

    case 'crear_oferta':
        require_once __DIR__ . '/controllers/ofertasController/ofertasController.php';
        $ofertasController = new OfertasController();
        $ofertasController->createOferta();
        break;

    case 'editar_oferta':
        require_once __DIR__ . '/controllers/ofertasController/ofertasController.php';
        $ofertasController = new OfertasController();
        $ofertasController->updateOferta();
        break;

    case 'detalle_oferta':
        require_once __DIR__ . '/controllers/ofertasController/detallesOfertasController.php';
        $db = getDbConnection();
        $controller = new DetallesOfertasController($db);
        $controller->index();
        break;

    // ─── Capacitaciones ───────────────────────────────────────
    case 'capacitaciones':
        require_once __DIR__ . '/controllers/capacitacionesController/capacitacionController.php';
        $capacitacionController = new CapacitacionController();
        $capacitacionController->showCapacitaciones();
        break;

    case 'crear_capacitacion':
        require_once __DIR__ . '/controllers/capacitacionesController/capacitacionController.php';
        $capacitacionController = new CapacitacionController();
        $capacitacionController->crearCapacitacion();
        break;

    case 'editar_capacitacion':
        require_once __DIR__ . '/controllers/capacitacionesController/capacitacionController.php';
        $capacitacionController = new CapacitacionController();
        $capacitacionController->editarCapacitacion();
        break;

    case 'eliminar_capacitacion':
        require_once __DIR__ . '/controllers/capacitacionesController/capacitacionController.php';
        $capacitacionController = new CapacitacionController();
        $capacitacionController->eliminarCapacitacion();
        break;

    case 'inscribir_capacitacion':
        require_once __DIR__ . '/controllers/capacitacionesController/capacitacionController.php';
        $capacitacionController = new CapacitacionController();
        $capacitacionController->gestionarInscripcion();
        break;

    case 'obtener_inscritos':
        require_once __DIR__ . '/controllers/capacitacionesController/capacitacionController.php';
        $capacitacionController = new CapacitacionController();
        $capacitacionController->obtenerInscritos();
        break;

    default:
        $authController = new AuthController();
        $authController->showWelcomePage();
        break;

} 
