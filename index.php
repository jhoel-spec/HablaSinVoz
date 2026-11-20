<?php

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');
// 1. Iniciar sesión global si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Definir ruta base global
if (!defined('BASE_URL')) {
    define('BASE_URL', rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/'));
}

// 3. Cargar la configuración de la base de datos
require_once 'config/Database.php';

// 4. Cargar todos los controladores
require_once 'controlador/AuthController.php';
require_once 'controlador/CursoController.php';
require_once 'controlador/LeccionController.php';
require_once 'controlador/EvaluacionController.php';
require_once 'controlador/SubidaController.php';
require_once 'controlador/AdminController.php';
require_once 'controlador/ApiController.php';

// 5. Capturar los parámetros enviados por la URL
// Compatible tanto con ?c=auth&a=login como con ?controller=auth&action=login
$controlador = $_GET['c'] ?? $_GET['controller'] ?? 'curso';
$controlador = strtolower(trim($controlador));

$accion = $_GET['a'] ?? $_GET['action'] ?? 'inicio';
$accion = trim($accion);

// 6. Instanciar el controlador correspondiente
switch ($controlador) {
    case 'auth':
        $ctrl = new AuthController();
        break;
    case 'leccion':
        $ctrl = new LeccionController();
        break;
    case 'evaluacion':
        $ctrl = new EvaluacionController();
        break;
    case 'subida':
        $ctrl = new SubidaController();
        break;
    case 'admin':
        $ctrl = new AdminController();
        break;
    case 'api':
        $ctrl = new ApiController();
        break;
    case 'curso':
    default:
        // El controlador por defecto si escriben algo incorrecto
        $ctrl = new CursoController();
        break;
}

// 7. Ejecutar el método (acción) solicitado
if (method_exists($ctrl, $accion)) {
    $ctrl->$accion();
} else {
    // Fallback de seguridad: si piden una acción que no existe
    if (method_exists($ctrl, 'inicio')) {
        $ctrl->inicio();
    } else {
        header("Location: " . BASE_URL . "/index.php");
        exit();
    }
}
?>
