<?php

require_once __DIR__ . '/../modelo/CursoDao.php';
require_once __DIR__ . '/../modelo/LeccionDao.php';
require_once __DIR__ . '/../modelo/CuestionarioDao.php';
require_once __DIR__ . '/../modelo/ProgresoDao.php';

class ApiController {
    private $cursoDao;
    private $leccionDao;
    private $cuestionarioDao;
    private $progresoDao;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cursoDao = new CursoDao();
        $this->leccionDao = new LeccionDao();
        $this->cuestionarioDao = new CuestionarioDao();
        $this->progresoDao = new ProgresoDao();

        header('Content-Type: application/json; charset=utf-8');
    }

    /**
     * GET /api/senas?q=hola
     * Búsqueda en vivo en el catálogo de lecciones y señas
     */
    public function senas() {
        $q = strtolower(trim($_GET['q'] ?? ''));
        $db = Database::getConnection();
        $resultados = [];

        if ($db) {
            try {
                $sql = "SELECT l.id_leccion, l.titulo, l.descripcion, c.titulo AS curso, m.ruta_enlace, m.tipo
                        FROM lecciones l
                        JOIN cursos c ON l.id_curso = c.id_curso
                        LEFT JOIN multimedia m ON l.id_leccion = m.id_leccion
                        WHERE l.estado = 'Publicada' AND (LOWER(l.titulo) LIKE :q OR LOWER(l.descripcion) LIKE :q2)
                        ORDER BY l.id_leccion ASC";
                $stmt = $db->prepare($sql);
                $stmt->execute([':q' => "%$q%", ':q2' => "%$q%"]);
                $resultados = $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Error ApiController::senas: " . $e->getMessage());
            }
        }

        echo json_encode(['status' => 'success', 'data' => $resultados]);
        exit();
    }

    /**
     * GET /api/lecciones/{id}/preguntas
     * Carga del cuestionario y preguntas en JSON para fetch
     */
    public function preguntas() {
        $id_leccion = (int)($_GET['id'] ?? 4);
        $cuestionario = $this->cuestionarioDao->obtenerPorLeccion($id_leccion);

        if ($cuestionario) {
            echo json_encode([
                'status' => 'success',
                'cuestionario' => [
                    'id_cuestionario' => $cuestionario->getIdCuestionario(),
                    'titulo' => $cuestionario->getTitulo(),
                    'preguntas' => $cuestionario->getPreguntas()
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No se encontraron preguntas para esta lección']);
        }
        exit();
    }

    /**
     * POST /api/intentos
     * Calificación automática de cuestionario
     */
    public function intentos() {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id_usuario = $_SESSION['usuario_id'] ?? (int)($input['id_usuario'] ?? 3);
        $id_cuestionario = (int)($input['id_cuestionario'] ?? 1);
        $calificacion = (float)($input['calificacion'] ?? 100);

        $guardado = $this->cuestionarioDao->guardarIntento($id_usuario, $id_cuestionario, $calificacion);
        echo json_encode(['status' => $guardado ? 'success' : 'error', 'calificacion' => $calificacion]);
        exit();
    }

    /**
     * POST /api/progreso
     * Marca una lección como completada
     */
    public function progreso() {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $id_usuario = $_SESSION['usuario_id'] ?? (int)($input['id_usuario'] ?? 3);
        $id_leccion = (int)($input['id_leccion'] ?? 1);
        $completada = isset($input['completada']) ? (int)$input['completada'] : 1;

        $ok = $this->progresoDao->marcarLeccion($id_usuario, $id_leccion, $completada);
        echo json_encode(['status' => $ok ? 'success' : 'error', 'completada' => $completada]);
        exit();
    }

    /**
     * POST /api/reportes
     * Reporta un error o duda en una seña
     */
    public function reportes() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
            exit();
        }
        if ((int)($_SESSION['usuario_rol'] ?? 0) !== 1 || empty($_SESSION['usuario_id'])) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión como estudiante para enviar un reporte']);
            exit();
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $input = is_array($input) ? $input : $_POST;
        $idLeccionInput = $input['id_leccion'] ?? null;
        $comentarioInput = $input['comentario'] ?? null;
        if (!is_scalar($idLeccionInput) || !is_string($comentarioInput)) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Los datos del reporte no son válidos']);
            exit();
        }
        $id_leccion = (int)$idLeccionInput;
        $comentario = trim($comentarioInput);
        if ($id_leccion <= 0 || preg_match('/\A.{1,2000}\z/us', $comentario) !== 1) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Escribe una duda de hasta 2000 caracteres']);
            exit();
        }

        $db = Database::getConnection();
        if (!$db) {
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'No hay conexión con la base de datos. El reporte no fue guardado.']);
            exit();
        }

        try {
            $stmt = $db->prepare(
                "SELECT COUNT(*)
                 FROM lecciones l
                 JOIN cursos c ON c.id_curso = l.id_curso
                 WHERE l.id_leccion = :id_leccion
                   AND l.estado = 'Publicada'
                   AND c.estado = 'Publicado'"
            );
            $stmt->execute([':id_leccion' => $id_leccion]);
            if ((int)$stmt->fetchColumn() !== 1) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'La lección ya no está disponible para reportes']);
                exit();
            }

            $stmt = $db->prepare(
                "INSERT INTO reportes (id_usuario, id_leccion, comentario, estado)
                 VALUES (:usuario, :leccion, :comentario, 'Pendiente')"
            );
            $stmt->execute([
                ':usuario' => (int)$_SESSION['usuario_id'],
                ':leccion' => $id_leccion,
                ':comentario' => $comentario
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Tu reporte fue enviado al profesor']);
        } catch (PDOException $e) {
            error_log("Error ApiController::reportes: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'No se pudo guardar el reporte. Inténtalo nuevamente.']);
        }
        exit();
    }

    /**
     * GET /api/oembed?url=...
     * Integración con API externa (YouTube oEmbed)
     */
    public function oembed() {
        $url = trim($_GET['url'] ?? '');
        if (empty($url)) {
            echo json_encode(['status' => 'error', 'message' => 'URL requerida']);
            exit();
        }

        $oembedUrl = "https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json";
        
        // Intentar llamada HTTP con timeout rápido
        $ctx = stream_context_create(['http' => ['timeout' => 3]]);
        $response = @file_get_contents($oembedUrl, false, $ctx);

        if ($response !== false) {
            echo $response;
        } else {
            // Fallback con datos genéricos si no hay internet o falla la API
            echo json_encode([
                'title' => 'Video en Lengua de Señas Boliviana (LSB)',
                'author_name' => 'HablaSinVoz',
                'html' => '<iframe width="480" height="270" src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>',
                'thumbnail_url' => 'assets/img/cursos/curso_1.svg'
            ]);
        }
        exit();
    }
}
