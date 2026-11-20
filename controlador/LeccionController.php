<?php

require_once __DIR__ . '/../modelo/LeccionDao.php';
require_once __DIR__ . '/../modelo/CursoDao.php';
require_once __DIR__ . '/../modelo/ProgresoDao.php';
require_once __DIR__ . '/../modelo/CuestionarioDao.php';
require_once __DIR__ . '/AuthController.php';

class LeccionController {
    private $leccionDao;
    private $cursoDao;
    private $progresoDao;
    private $cuestionarioDao;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->leccionDao = new LeccionDao();
        $this->cursoDao = new CursoDao();
        $this->progresoDao = new ProgresoDao();
        $this->cuestionarioDao = new CuestionarioDao();
    }

    /**
     * Ver contenido de una lección (video, imagen, descripción de señas)
     */
    public function ver() {
        AuthController::requerirRol([1]);

        $id_leccion = (int)($_GET['id'] ?? 1);
        $id_usuario = $_SESSION['usuario_id'];

        $leccion = $this->leccionDao->obtenerPorId($id_leccion, $id_usuario);
        if (!$leccion) {
            header("Location: index.php?c=curso&a=misCursos&msg=recurso_no_disponible");
            exit();
        }
        $curso = $this->cursoDao->obtenerPorId($leccion->getIdCurso());
        if (!$curso) {
            header("Location: index.php?c=curso&a=misCursos&msg=recurso_no_disponible");
            exit();
        }
        $idCuestionario = $this->cuestionarioDao->obtenerIdPorCurso($leccion->getIdCurso());
        $todasLecciones = $this->leccionDao->obtenerPorCurso($leccion->getIdCurso(), true, $id_usuario);

        require_once __DIR__ . '/../vista/estudiantes/leccion.php';
    }

    /**
     * Marcar lección como completada
     */
    public function completar() {
        AuthController::requerirRol([1]);
        $id_leccion = (int)($_POST['id_leccion'] ?? $_GET['id'] ?? 1);
        $id_usuario = $_SESSION['usuario_id'];

        $this->progresoDao->marcarLeccion($id_usuario, $id_leccion, 1);

        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Lección completada']);
            exit();
        }

        header("Location: index.php?c=leccion&a=ver&id=" . $id_leccion . "&completada=1");
        exit();
    }
}
