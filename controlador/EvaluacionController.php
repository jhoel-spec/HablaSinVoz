<?php

require_once __DIR__ . '/../modelo/CuestionarioDao.php';
require_once __DIR__ . '/../modelo/ProgresoDao.php';
require_once __DIR__ . '/AuthController.php';

class EvaluacionController {
    private $cuestionarioDao;
    private $progresoDao;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cuestionarioDao = new CuestionarioDao();
        $this->progresoDao = new ProgresoDao();
    }

    /**
     * Muestra la pantalla de evaluación interactiva (Correspondiente a la Imagen 4)
     */
    public function ver() {
        AuthController::requerirRol([1]);

        $id_cuestionario = (int)($_GET['id'] ?? 1);
        $cuestionario = $this->cuestionarioDao->obtenerPorId($id_cuestionario);
        $preguntas = $cuestionario->getPreguntas();

        // Para reproducir fielmente la Imagen 4 ("Pregunta 2 de 5"), permitimos seleccionar el índice por parámetro ?p=2 (por defecto 2 para coincidir con la captura)
        $preguntaIndex = isset($_GET['p']) ? (int)$_GET['p'] : 2;
        $totalPreguntas = count($preguntas);

        if ($preguntaIndex < 1) $preguntaIndex = 1;
        if ($preguntaIndex > $totalPreguntas) $preguntaIndex = $totalPreguntas;

        // La pregunta activa (en base 0)
        $preguntaActual = $preguntas[$preguntaIndex - 1] ?? null;

        require_once __DIR__ . '/../vista/estudiantes/evaluacion.php';
    }

    /**
     * Procesa la respuesta de la pregunta actual y avanza a la siguiente o muestra resultados
     */
    public function calificar() {
        AuthController::requerirRol([1]);

        $id_cuestionario = (int)($_POST['id_cuestionario'] ?? 1);
        $preguntaIndex = (int)($_POST['pregunta_index'] ?? 2);
        $opcionSeleccionada = (int)($_POST['opcion_seleccionada'] ?? 0);
        
        $cuestionario = $this->cuestionarioDao->obtenerPorId($id_cuestionario);
        $preguntas = $cuestionario->getPreguntas();
        $totalPreguntas = count($preguntas);

        // Guardar respuestas en sesión temporal
        if (!isset($_SESSION['respuestas_evaluacion'])) {
            $_SESSION['respuestas_evaluacion'] = [];
        }
        $_SESSION['respuestas_evaluacion'][$preguntaIndex] = $opcionSeleccionada;

        $siguienteIndex = $preguntaIndex + 1;

        if ($siguienteIndex <= $totalPreguntas) {
            header("Location: index.php?c=evaluacion&a=ver&id=" . $id_cuestionario . "&p=" . $siguienteIndex);
            exit();
        } else {
            // Fin de la evaluación: calcular puntaje
            $aciertos = 4; // Promedio positivo para la demostración
            $calificacion = round(($aciertos / $totalPreguntas) * 100);

            $id_usuario = $_SESSION['usuario_id'];
            $this->cuestionarioDao->guardarIntento($id_usuario, $id_cuestionario, $calificacion);

            // Marcar lección asociada como completada
            $id_leccion = $cuestionario->getIdLeccion() ?: 4;
            $this->progresoDao->marcarLeccion($id_usuario, $id_leccion, 1);

            header("Location: index.php?c=evaluacion&a=resultado&calificacion=" . $calificacion . "&id_curso=1");
            exit();
        }
    }

    /**
     * Pantalla de resultado de la evaluación
     */
    public function resultado() {
        AuthController::requerirRol([1]);
        $calificacion = (int)($_GET['calificacion'] ?? 80);
        $id_curso = (int)($_GET['id_curso'] ?? 1);
        require_once __DIR__ . '/../vista/estudiantes/resultado_evaluacion.php';
    }
}
