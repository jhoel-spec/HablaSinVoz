<?php

require_once __DIR__ . '/../modelo/CursoDao.php';
require_once __DIR__ . '/../modelo/LeccionDao.php';
require_once __DIR__ . '/../modelo/ProgresoDao.php';
require_once __DIR__ . '/../modelo/UsuarioDao.php';
require_once __DIR__ . '/../modelo/CuestionarioDao.php';
require_once __DIR__ . '/../modelo/ProfesorDao.php';
require_once __DIR__ . '/AuthController.php';

class CursoController {
    private $cursoDao;
    private $leccionDao;
    private $progresoDao;
    private $usuarioDao;
    private $cuestionarioDao;
    private $profesorDao;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cursoDao = new CursoDao();
        $this->leccionDao = new LeccionDao();
        $this->progresoDao = new ProgresoDao();
        $this->usuarioDao = new UsuarioDao();
        $this->cuestionarioDao = new CuestionarioDao();
        $this->profesorDao = new ProfesorDao();
    }

    /**
     * Página de Inicio Pública (Vista correspondiente a la Imagen 1)
     */
    public function inicio() {
        $cursosPublicados = $this->cursoDao->obtenerPublicados();
        $cursosDestacados = array_slice($cursosPublicados, 0, 3);
        $hayMasCursos = count($cursosPublicados) > 3;
        $autenticado = AuthController::estaAutenticado();
        require_once __DIR__ . '/../vista/public/index.php';
    }

    /**
     * Catálogo público de cursos
     */
    public function catalogo() {
        $autenticado = AuthController::estaAutenticado();
        $rol = $autenticado ? (int)($_SESSION['usuario_rol'] ?? 0) : 0;
        $cursosPublicados = $this->cursoDao->obtenerPublicados();
        $cursos = $autenticado ? $cursosPublicados : array_slice($cursosPublicados, 0, 3);
        $cursosRestringidos = max(0, count($cursosPublicados) - count($cursos));
        require_once __DIR__ . '/../vista/public/cursos.php';
    }

    /**
     * Panel de Cursos del Estudiante (Vista correspondiente a la Imagen 3)
     */
    public function misCursos() {
        AuthController::requerirSesion();

        $rol = (int)($_SESSION['usuario_rol'] ?? 0);
        if ($rol === 3) {
            header("Location: index.php?c=admin&a=aprobaciones");
            exit();
        } elseif ($rol === 2) {
            header("Location: index.php?c=curso&a=profesorCursos");
            exit();
        }

        $id_usuario = $_SESSION['usuario_id'];
        $cursos = $this->cursoDao->obtenerCursosEstudianteConProgreso($id_usuario);
        $errorCursos = $this->cursoDao->getLastError()
            ? 'No se pudieron cargar los cursos. Comprueba la conexión con la base de datos e inténtalo nuevamente.'
            : null;

        require_once __DIR__ . '/../vista/estudiantes/cursos.php';
    }

    /**
     * Continuar el curso activo más avanzado del estudiante
     */
    public function continuarCurso() {
        AuthController::requerirSesion();
        $rol = (int)($_SESSION['usuario_rol'] ?? 0);
        if ($rol === 3) {
            header("Location: index.php?c=admin&a=aprobaciones");
            exit();
        } elseif ($rol === 2) {
            header("Location: index.php?c=curso&a=profesorCursos");
            exit();
        }

        $cursos = $this->cursoDao->obtenerCursosEstudianteConProgreso((int)$_SESSION['usuario_id']);
        $cursoActivo = null;
        foreach ($cursos as $curso) {
            if (
                $curso->estaInscrito() &&
                ($cursoActivo === null || $curso->getPorcentajeProgreso() > $cursoActivo->getPorcentajeProgreso())
            ) {
                $cursoActivo = $curso;
            }
        }
        if ($cursoActivo === null) {
            header("Location: index.php?c=curso&a=misCursos");
            exit();
        }
        header("Location: index.php?c=curso&a=detalle&id=" . (int)$cursoActivo->getIdCurso());
        exit();
    }

    public function tomarCurso() {
        AuthController::requerirRol([1]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $curso = $idCurso > 0 ? $this->cursoDao->obtenerPorId($idCurso) : null;
        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' ||
            !$curso ||
            $curso->getEstado() !== 'Publicado'
        ) {
            header('Location: index.php?c=curso&a=misCursos&msg=inscripcion_error');
            exit();
        }

        $mensaje = $this->cursoDao->registrarEstudianteCurso($idCurso, (int)$_SESSION['usuario_id'])
            ? 'inscripcion_ok'
            : 'inscripcion_error';
        header('Location: index.php?c=curso&a=misCursos&msg=' . $mensaje);
        exit();
    }

    /**
     * Vista de Mi Progreso para el estudiante
     */
    public function miProgreso() {
        AuthController::requerirSesion();
        $rol = (int)($_SESSION['usuario_rol'] ?? 0);
        if ($rol === 3) {
            header("Location: index.php?c=admin&a=aprobaciones");
            exit();
        } elseif ($rol === 2) {
            header("Location: index.php?c=curso&a=profesorCursos");
            exit();
        }

        $id_usuario = $_SESSION['usuario_id'];
        $resumen = $this->progresoDao->obtenerResumenEstudiante($id_usuario);
        $cursos = $this->cursoDao->obtenerCursosEstudianteConProgreso($id_usuario);

        require_once __DIR__ . '/../vista/estudiantes/progreso.php';
    }

    /**
     * Panel de Cursos del Profesor
     */
    public function profesorCursos() {
        AuthController::requerirRol([2]);
        $id_profesor = $_SESSION['usuario_id'];
        $cursos = $this->cursoDao->obtenerPorProfesor($id_profesor);
        $errorCursos = $this->cursoDao->getLastError()
            ? 'No se pudieron cargar tus cursos. Comprueba la conexión con la base de datos e inténtalo nuevamente.'
            : null;
        $reportes = $this->profesorDao->obtenerReportes($id_profesor);
        $errorReportes = $this->profesorDao->getLastError()
            ? 'No se pudieron cargar las dudas y reportes. Ejecuta la actualización de la base de datos y vuelve a intentarlo.'
            : null;
        $estudiantesPorCurso = [];
        $idCursoSeleccionado = (int)($_GET['id_curso'] ?? 0);
        $cursoSeleccionado = null;
        $estudiantesCurso = [];
        $errorEstudiantes = null;
        foreach ($cursos as $curso) {
            $idCurso = (int)$curso->getIdCurso();
            $estudiantesPorCurso[$idCurso] = $this->cursoDao->obtenerEstudiantesConActividad($idCurso);
            if ($idCurso === $idCursoSeleccionado) {
                $cursoSeleccionado = $curso;
                $estudiantesCurso = $estudiantesPorCurso[$idCurso];
            }
        }
        if ($idCursoSeleccionado > 0 && !$cursoSeleccionado) {
            $errorEstudiantes = 'No tienes asignado ese curso o ya no está disponible.';
        }
        require_once __DIR__ . '/../vista/profesor/mis_cursos.php';
    }

    public function responderReporte() {
        AuthController::requerirRol([2]);
        $idReporteInput = $_POST['id_reporte'] ?? null;
        $respuestaInput = $_POST['respuesta'] ?? null;
        $idReporte = is_scalar($idReporteInput) ? (int)$idReporteInput : 0;
        $respuesta = is_string($respuestaInput) ? trim($respuestaInput) : '';
        $mensaje = 'reporte_error';

        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' &&
            preg_match('/\A.{1,2000}\z/us', $respuesta) === 1 &&
            $this->profesorDao->responderReporte($idReporte, (int)$_SESSION['usuario_id'], $respuesta)
        ) {
            $mensaje = 'reporte_resuelto';
        }

        header('Location: index.php?c=curso&a=profesorCursos&msg=' . $mensaje . '#reportes');
        exit();
    }

    public function crear() {
        AuthController::requerirRol([2, 3]);
        $rol = (int)$_SESSION['usuario_rol'];
        $profesores = $rol === 3 ? $this->usuarioDao->listarProfesoresActivos() : [];
        $error = null;

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $nivel = trim($_POST['nivel'] ?? '');
            $id_profesor = $rol === 2
                ? (int)$_SESSION['usuario_id']
                : (int)($_POST['id_profesor'] ?? 0);

            if (preg_match('/\A.{1,150}\z/us', $titulo) !== 1) {
                $error = "El título del curso es obligatorio y no debe superar 150 caracteres.";
            } elseif (!in_array($nivel, ['Básico 1', 'Básico 2', 'Intermedio', 'Avanzado'], true)) {
                $error = "Selecciona un nivel válido.";
            } elseif ($rol === 3) {
                $profesor = $this->usuarioDao->buscarPorId($id_profesor);
                if (!$profesor || !$profesor->esProfesor() || $profesor->getEstado() !== 'Activo') {
                    $error = "Selecciona un profesor activo para asignar el curso.";
                }
            }

            if ($error === null) {
                $estado = $rol === 3 ? 'Publicado' : 'Revisión';
                $curso = new Curso(null, $titulo, $descripcion, $nivel, $id_profesor, $estado);
                if ($this->cursoDao->crear($curso)) {
                    if ($rol === 3) {
                        header("Location: index.php?c=admin&a=usuarios&msg=curso_creado");
                    } else {
                        header("Location: index.php?c=curso&a=profesorCursos&msg=curso_creado");
                    }
                    exit();
                }
                $error = "No se pudo crear el curso. Verifica la conexión y los datos del formulario.";
            }
        }

        require_once __DIR__ . '/../vista/profesor/crear_curso.php';
    }

    public function enviarCursoRevision() {
        AuthController::requerirRol([2]);
        $id_curso = (int)($_POST['id_curso'] ?? 0);
        $curso = $this->cursoDao->obtenerPorId($id_curso);
        if (
            !$curso ||
            (int)$curso->getIdProfesor() !== (int)$_SESSION['usuario_id'] ||
            $curso->getEstado() !== 'Borrador'
        ) {
            header("Location: index.php?c=curso&a=profesorCursos&msg=curso_revision_error");
            exit();
        }

        $actualizado = $this->cursoDao->cambiarEstado($id_curso, 'Revisión');
        header("Location: index.php?c=curso&a=profesorCursos&msg=" . ($actualizado ? 'curso_enviado_revision' : 'curso_revision_error'));
        exit();
    }

    /**
     * Detalle de un curso con sus lecciones
     */
    public function detalle() {
        $autenticado = AuthController::estaAutenticado();
        $rol = $autenticado ? (int)($_SESSION['usuario_rol'] ?? 0) : 0;
        $id_curso = (int)($_GET['id'] ?? 0);
        if ($rol === 3) {
            header("Location: index.php?c=curso&a=verContenido&id_curso=" . $id_curso);
            exit();
        } elseif ($rol === 2) {
            header("Location: index.php?c=curso&a=verContenido&id_curso=" . $id_curso);
            exit();
        }

        $curso = $id_curso > 0 ? $this->cursoDao->obtenerPorId($id_curso) : null;
        $cursosPublicados = $this->cursoDao->obtenerPublicados();
        $indicePublicado = null;
        foreach ($cursosPublicados as $indice => $cursoPublicado) {
            if ((int)$cursoPublicado->getIdCurso() === $id_curso) {
                $indicePublicado = $indice;
                break;
            }
        }
        if (!$curso || $indicePublicado === null) {
            header("Location: index.php?c=curso&a=catalogo&msg=recurso_no_disponible");
            exit();
        }
        if (!$autenticado && $indicePublicado >= 3) {
            header("Location: index.php?c=auth&a=login");
            exit();
        }

        $id_usuario = $autenticado ? (int)$_SESSION['usuario_id'] : 0;
        $lecciones = $this->leccionDao->obtenerPorCurso($id_curso, true, $id_usuario);
        $idCuestionario = $this->cuestionarioDao->obtenerIdPorCurso($id_curso);

        require_once __DIR__ . '/../vista/estudiantes/detalle_curso.php';
    }

    public function verContenido() {
        AuthController::requerirRol([2, 3]);
        $idCurso = (int)($_GET['id_curso'] ?? 0);
        $curso = $idCurso > 0 ? $this->cursoDao->obtenerPorId($idCurso) : null;
        if (!$curso) {
            header("Location: index.php?c=curso&a=profesorCursos&msg=recurso_no_disponible");
            exit();
        }

        if ((int)$_SESSION['usuario_rol'] === 2) {
            $asignado = false;
            foreach ($this->cursoDao->obtenerPorProfesor((int)$_SESSION['usuario_id']) as $cursoAsignado) {
                if ((int)$cursoAsignado->getIdCurso() === $idCurso) {
                    $asignado = true;
                    break;
                }
            }
            if (!$asignado) {
                header("Location: index.php?c=curso&a=profesorCursos&msg=recurso_no_disponible");
                exit();
            }
        }

        $lecciones = $this->leccionDao->obtenerPorCurso($idCurso, false, 0);
        $urlRegreso = (int)$_SESSION['usuario_rol'] === 3
            ? BASE_URL . '/index.php?c=admin&a=cursos&id_curso=' . $idCurso
            : BASE_URL . '/index.php?c=curso&a=profesorCursos';
        require_once __DIR__ . '/../vista/profesor/contenido_curso.php';
    }
}
