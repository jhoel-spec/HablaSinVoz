<?php

require_once __DIR__ . '/../modelo/UsuarioDao.php';
require_once __DIR__ . '/../modelo/CursoDao.php';
require_once __DIR__ . '/../modelo/LeccionDao.php';
require_once __DIR__ . '/../modelo/MultimediaDao.php';
require_once __DIR__ . '/../config/PrivateStorage.php';
require_once __DIR__ . '/AuthController.php';

class AdminController {
    private $usuarioDao;
    private $cursoDao;
    private $leccionDao;
    private $multimediaDao;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->usuarioDao = new UsuarioDao();
        $this->cursoDao = new CursoDao();
        $this->leccionDao = new LeccionDao();
        $this->multimediaDao = new MultimediaDao();
    }

    /**
     * Gestión de Usuarios y Roles
     */
    public function usuarios() {
        AuthController::requerirRol([3]);
        $mensaje = null;
        $error = null;

        $mensajes = [
            'curso_creado' => 'El curso fue creado y publicado correctamente.',
            'solicitud_aprobada' => 'La solicitud fue aprobada y el usuario ahora es profesor.',
            'solicitud_rechazada' => 'La solicitud fue rechazada.',
            'solicitud_error' => 'No se pudo resolver la solicitud; puede que ya haya sido revisada o que la cuenta esté suspendida.',
            'solicitud_documento' => 'No se puede aprobar la solicitud porque falta el documento de respaldo.'
        ];
        $mensaje = $_SESSION['admin_flash_usuarios'] ?? null;
        unset($_SESSION['admin_flash_usuarios']);
        if ($mensaje === null && isset($mensajes[$_GET['msg'] ?? ''])) {
            $mensaje = $mensajes[$_GET['msg']];
            $_SESSION['admin_flash_usuarios'] = $mensaje;
            header('Location: index.php?c=admin&a=usuarios');
            exit();
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $accion = $_POST['accion'] ?? '';
            $id_u = (int)($_POST['id_usuario'] ?? 0);
            $usuario = $id_u > 0 ? $this->usuarioDao->buscarPorId($id_u) : null;

            if (!$usuario) {
                $error = "No se encontró el usuario solicitado.";
            } elseif ($id_u === (int)$_SESSION['usuario_id']) {
                $error = "No puedes cambiar el estado, rol o eliminar tu propia cuenta.";
            } elseif ($accion === 'actualizar_rol') {
                $id_r = (int)($_POST['id_rol'] ?? 0);
                if (!in_array($id_r, [1, 2, 3], true)) {
                    $error = "El rol seleccionado no es válido.";
                } elseif ($id_r !== 3 && $this->bloquearRetiroAdminActivo($usuario)) {
                    $error = "No se puede continuar: debe permanecer al menos un administrador activo.";
                } elseif ($this->usuarioDao->actualizarRol($id_u, $id_r)) {
                    $mensaje = "Rol de usuario actualizado correctamente.";
                } else {
                    $error = $this->usuarioDao->getLastError() === 'profesor_asignado_a_cursos'
                        ? "Reasigna primero sus cursos desde Gestión de cursos antes de cambiar su rol."
                        : "No se pudo actualizar el rol del usuario.";
                }
            } elseif ($accion === 'suspender' || $accion === 'activar') {
                $nuevoEstado = $accion === 'suspender' ? 'Suspendido' : 'Activo';
                if ($nuevoEstado === 'Suspendido' && $this->bloquearRetiroAdminActivo($usuario)) {
                    $error = "No se puede continuar: debe permanecer al menos un administrador activo.";
                } elseif ($this->usuarioDao->cambiarEstadoUsuario($id_u, $nuevoEstado)) {
                    $mensaje = $nuevoEstado === 'Suspendido' ? "Usuario suspendido correctamente." : "Usuario reactivado correctamente.";
                } else {
                    $error = "No se pudo cambiar el estado del usuario.";
                }
            } elseif ($accion === 'eliminar') {
                if ($this->bloquearRetiroAdminActivo($usuario)) {
                    $error = "No se puede continuar: debe permanecer al menos un administrador activo.";
                } elseif ($this->usuarioDao->eliminarUsuario($id_u)) {
                    $mensaje = "Usuario eliminado correctamente.";
                } else {
                    $error = $this->usuarioDao->getLastError() === 'El usuario tiene cursos o actividad asociada.'
                        ? "El usuario tiene cursos o actividad asociada; suspéndelo para conservar esos datos."
                        : "No se pudo eliminar al usuario por un error de base de datos.";
                }
            } else {
                $error = "La acción solicitada no es válida.";
            }
        }

        $usuarios = $this->usuarioDao->listarTodos();
        $solicitudes = $this->usuarioDao->listarSolicitudes();
        if ($error === null && $this->usuarioDao->getLastError() !== null) {
            $error = "No se pudo consultar la base de datos; algunas acciones de administración no estarán disponibles.";
        }

        require_once __DIR__ . '/../vista/admin/usuarios.php';
    }

    private function bloquearRetiroAdminActivo($usuario) {
        if (!$usuario->esAdmin() || $usuario->getEstado() !== 'Activo') {
            return false;
        }

        $administradoresActivos = $this->usuarioDao->contarAdministradoresActivos();
        return $administradoresActivos === false || $administradoresActivos <= 1;
    }

    /**
     * Aprobaciones de Lecciones y Cursos en Revisión
     */
    public function aprobaciones() {
        AuthController::requerirRol([3]);
        $mensaje = null;

        if (isset($_GET['aprobar_curso'])) {
            $id_curso = (int)$_GET['aprobar_curso'];
            $mensaje = $this->cursoDao->cambiarEstado($id_curso, 'Publicado')
                ? "El curso #$id_curso fue aprobado y publicado."
                : "No se pudo publicar el curso #$id_curso.";
        } elseif (isset($_GET['observar_curso'])) {
            $id_curso = (int)$_GET['observar_curso'];
            $mensaje = $this->cursoDao->cambiarEstado($id_curso, 'Borrador')
                ? "El curso #$id_curso fue devuelto a borrador para su corrección."
                : "No se pudo devolver el curso #$id_curso a borrador.";
        } elseif (isset($_GET['aprobar_leccion'])) {
            $id_l = (int)$_GET['aprobar_leccion'];
            $mensaje = $this->leccionDao->cambiarEstado($id_l, 'Publicada')
                ? "La lección #$id_l ha sido aprobada y publicada para los estudiantes."
                : "No se pudo publicar la lección #$id_l.";
        } elseif (isset($_GET['observar_leccion'])) {
            $id_l = (int)$_GET['observar_leccion'];
            $mensaje = $this->leccionDao->cambiarEstado($id_l, 'Observada')
                ? "La lección #$id_l ha sido devuelta con observaciones al profesor."
                : "No se pudo observar la lección #$id_l.";
        }

        $idCursoFiltro = (int)($_GET['id_curso'] ?? 0);
        if ($mensaje !== null) {
            $_SESSION['admin_flash_aprobaciones'] = $mensaje;
            $url = 'index.php?c=admin&a=aprobaciones';
            if ($idCursoFiltro > 0) {
                $url .= '&id_curso=' . $idCursoFiltro;
            }
            header('Location: ' . $url);
            exit();
        }
        $mensaje = $_SESSION['admin_flash_aprobaciones'] ?? null;
        unset($_SESSION['admin_flash_aprobaciones']);

        $pendientes = $this->leccionDao->obtenerPendientes($idCursoFiltro);
        $todosCursos = $this->cursoDao->obtenerTodos();
        $cursosEnRevision = array_values(array_filter($todosCursos, function ($curso) {
            return $curso->getEstado() === 'Revisión';
        }));

        require_once __DIR__ . '/../vista/admin/aprobaciones.php';
    }

    /**
     * Resolver solicitudes de postulantes a Profesor
     */
    public function resolverSolicitud() {
        AuthController::requerirRol([3]);
        $id_solicitud = (int)($_POST['id_solicitud'] ?? 0);
        $estado = $_POST['estado'] ?? '';

        $resuelta = $this->usuarioDao->resolverSolicitud($id_solicitud, $estado);
        if ($resuelta) {
            $mensaje = $estado === 'Aprobada' ? 'solicitud_aprobada' : 'solicitud_rechazada';
        } else {
            $mensaje = $this->usuarioDao->getLastError() === 'missing_document'
                ? 'solicitud_documento'
                : 'solicitud_error';
        }
        $mensajes = [
            'solicitud_aprobada' => 'La solicitud fue aprobada y el usuario ahora es profesor.',
            'solicitud_rechazada' => 'La solicitud fue rechazada.',
            'solicitud_error' => 'No se pudo resolver la solicitud; puede que ya haya sido revisada o que la cuenta esté suspendida.',
            'solicitud_documento' => 'No se puede aprobar la solicitud porque falta el documento de respaldo.'
        ];
        $_SESSION['admin_flash_usuarios'] = $mensajes[$mensaje];
        header('Location: index.php?c=admin&a=usuarios');
        exit();
    }

    public function verDocumentoSolicitud() {
        AuthController::requerirRol([3]);
        $id_solicitud = (int)($_GET['id'] ?? 0);
        $documento = $this->usuarioDao->obtenerDocumentoSolicitud($id_solicitud);
        $ruta = $documento
            ? PrivateStorage::solicitudPath($documento['documento'] ?? '')
            : null;

        if ($ruta === null || !is_readable($ruta)) {
            http_response_code(404);
            echo "El documento no está disponible.";
            exit();
        }

        $extension = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="requisito_solicitud_' . $id_solicitud . '.' . $extension . '"');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store, max-age=0');
        header('Pragma: no-cache');
        header('Content-Length: ' . filesize($ruta));
        if (readfile($ruta) === false) {
            error_log("No se pudo leer el documento de solicitud #$id_solicitud.");
        }
        exit();
    }

    public function cursos() {
        AuthController::requerirRol([3]);
        $mensajes = [
            'curso_actualizado' => 'Los datos del curso fueron actualizados.',
            'curso_error' => 'No se pudo actualizar el curso. Revisa los datos e inténtalo nuevamente.',
            'profesores_actualizados' => 'Los profesores asignados al curso fueron actualizados.',
            'profesores_error' => 'No se pudo cambiar la asignación. Selecciona al menos un profesor activo.',
            'curso_eliminado' => 'El curso y todo su contenido asociado fueron eliminados.',
            'curso_eliminar_error' => 'No se pudo eliminar el curso y sus datos asociados.',
            'leccion_actualizada' => 'La lección fue actualizada.',
            'leccion_error' => 'No se pudo actualizar la lección.',
            'leccion_eliminada' => 'La lección y sus materiales asociados fueron eliminados.',
            'leccion_eliminar_error' => 'No se pudo eliminar la lección.',
            'material_eliminado' => 'El material fue eliminado.',
            'material_error' => 'No se pudo eliminar el material.'
        ];
        $mensajeFlash = $_SESSION['admin_flash_cursos'] ?? null;
        unset($_SESSION['admin_flash_cursos']);
        $mensaje = $mensajes[$mensajeFlash ?? ($_GET['msg'] ?? '')] ?? null;
        if ($mensajeFlash === null && isset($mensajes[$_GET['msg'] ?? ''])) {
            $url = 'index.php?c=admin&a=cursos';
            $idCurso = (int)($_GET['id_curso'] ?? 0);
            if ($idCurso > 0) {
                $url .= '&id_curso=' . $idCurso;
            }
            $_SESSION['admin_flash_cursos'] = $_GET['msg'];
            header('Location: ' . $url);
            exit();
        }
        $error = $this->cursoDao->getLastError() !== null
            ? 'No se pudo completar la consulta de cursos. Revisa la conexión y la estructura de la base de datos.'
            : null;
        $cursos = $this->cursoDao->obtenerTodos();
        $idCursoSeleccionado = (int)($_GET['id_curso'] ?? 0);
        $cursoSeleccionado = $idCursoSeleccionado > 0
            ? $this->cursoDao->obtenerPorId($idCursoSeleccionado)
            : null;
        $profesoresActivos = $this->usuarioDao->listarProfesoresActivos();
        $profesoresCurso = $cursoSeleccionado
            ? $this->cursoDao->obtenerProfesoresCurso($idCursoSeleccionado)
            : [];
        $leccionesCurso = $cursoSeleccionado
            ? $this->leccionDao->obtenerPorCurso($idCursoSeleccionado, false, 0)
            : [];
        $estudiantesCurso = $cursoSeleccionado
            ? $this->cursoDao->obtenerEstudiantesConActividad($idCursoSeleccionado)
            : [];

        require_once __DIR__ . '/../vista/admin/cursos.php';
    }

    public function guardarCurso() {
        AuthController::requerirRol([3]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $cursoActual = $idCurso > 0 ? $this->cursoDao->obtenerPorId($idCurso) : null;
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $nivel = trim($_POST['nivel'] ?? '');
        $estado = $_POST['estado'] ?? '';
        if (
            !$cursoActual ||
            preg_match('/\A.{1,150}\z/us', $titulo) !== 1 ||
            !in_array($nivel, ['Básico 1', 'Básico 2', 'Intermedio', 'Avanzado'], true) ||
            !in_array($estado, ['Borrador', 'Revisión', 'Publicado'], true)
        ) {
            $this->redirigirGestionCursos($idCurso, 'curso_error');
        }

        $curso = new Curso(
            $idCurso,
            $titulo,
            $descripcion,
            $nivel,
            $cursoActual->getIdProfesor(),
            $estado
        );
        $this->redirigirGestionCursos(
            $idCurso,
            $this->cursoDao->actualizar($curso) ? 'curso_actualizado' : 'curso_error'
        );
    }

    public function asignarProfesoresCurso() {
        AuthController::requerirRol([3]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $idsProfesores = $_POST['id_profesores'] ?? [];
        if (!is_array($idsProfesores)) {
            $idsProfesores = [];
        }
        $actualizado = $this->cursoDao->asignarProfesores($idCurso, $idsProfesores);
        $this->redirigirGestionCursos(
            $idCurso,
            $actualizado ? 'profesores_actualizados' : 'profesores_error'
        );
    }

    public function eliminarCurso() {
        AuthController::requerirRol([3]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $eliminado = $this->cursoDao->eliminar($idCurso);
        $this->redirigirGestionCursos(
            0,
            $eliminado ? 'curso_eliminado' : 'curso_eliminar_error'
        );
    }

    public function guardarLeccion() {
        AuthController::requerirRol([3]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $idLeccion = (int)($_POST['id_leccion'] ?? 0);
        $leccionActual = $this->leccionDao->obtenerPorId($idLeccion);
        $titulo = trim($_POST['titulo'] ?? '');
        $orden = (int)($_POST['orden'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        if (
            !$leccionActual ||
            (int)$leccionActual->getIdCurso() !== $idCurso ||
            preg_match('/\A.{1,150}\z/us', $titulo) !== 1 ||
            $orden < 1 ||
            !in_array($estado, ['Pendiente', 'Publicada', 'Observada'], true)
        ) {
            $this->redirigirGestionCursos($idCurso, 'leccion_error');
        }

        $leccion = new Leccion(
            $idLeccion,
            $idCurso,
            $titulo,
            trim($_POST['descripcion'] ?? ''),
            $orden,
            $estado
        );
        $this->redirigirGestionCursos(
            $idCurso,
            $this->leccionDao->actualizar($leccion, $idCurso) ? 'leccion_actualizada' : 'leccion_error'
        );
    }

    public function eliminarLeccion() {
        AuthController::requerirRol([3]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $idLeccion = (int)($_POST['id_leccion'] ?? 0);
        $eliminada = $this->leccionDao->eliminar($idLeccion, $idCurso);
        $this->redirigirGestionCursos(
            $idCurso,
            $eliminada ? 'leccion_eliminada' : 'leccion_eliminar_error'
        );
    }

    public function eliminarMaterialCurso() {
        AuthController::requerirRol([3]);
        $idCurso = (int)($_POST['id_curso'] ?? 0);
        $idMaterial = (int)($_POST['id_multimedia'] ?? 0);
        $eliminado = $this->multimediaDao->eliminar($idMaterial, $idCurso);
        $this->redirigirGestionCursos(
            $idCurso,
            $eliminado ? 'material_eliminado' : 'material_error'
        );
    }

    private function redirigirGestionCursos($idCurso, $mensaje) {
        $_SESSION['admin_flash_cursos'] = $mensaje;
        $url = 'index.php?c=admin&a=cursos';
        if ((int)$idCurso > 0) {
            $url .= '&id_curso=' . (int)$idCurso;
        }
        header('Location: ' . $url);
        exit();
    }
}
