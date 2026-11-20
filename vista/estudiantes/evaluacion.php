<?php

$tituloPagina = "HablaSinVoz - Evaluación";
$paginaActiva = 'evaluacion';
if (!isset($cuestionario)) {
    require_once __DIR__ . '/../../modelo/CuestionarioDao.php';
    $cuestionarioDao = new CuestionarioDao();
    $cuestionario = $cuestionarioDao->obtenerPorId(1);
    $preguntas = $cuestionario->getPreguntas();
    $preguntaIndex = 2;
    $totalPreguntas = count($preguntas);
    $preguntaActual = $preguntas[$preguntaIndex - 1] ?? null;
}
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="dashboard-container">
    <!-- Sidebar ESTUDIANTE (Imagen 4) -->
    <?php require_once __DIR__ . '/../layout/sidebar_estudiante.php'; ?>

    <!-- Contenido Principal de la Evaluación (Imagen 4) -->
    <main class="dashboard-content">
        <!-- Encabezado con Título y Contador "Pregunta 2 de 5" -->
        <div class="evaluacion-header">
            <h1 class="evaluacion-title"><?= htmlspecialchars($cuestionario->getTitulo()) ?></h1>
            <span class="evaluacion-counter">Pregunta <?= $preguntaIndex ?> de <?= $totalPreguntas ?></span>
        </div>

        <form action="<?= BASE_URL ?>/index.php?c=evaluacion&a=calificar" method="POST" id="formEvaluacion">
            <input type="hidden" name="id_cuestionario" value="<?= $cuestionario->getIdCuestionario() ?>">
            <input type="hidden" name="pregunta_index" value="<?= $preguntaIndex ?>">

            <!-- Caja Principal de la Pregunta con [ imagen ] (Imagen 4) -->
            <div class="question-box">
                <div class="question-media-inner">
                    <?php if (!empty($preguntaActual['imagen'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($preguntaActual['imagen']) ?>" alt="Seña de la pregunta">
                    <?php else: ?>
                        [ imagen ]
                    <?php endif; ?>
                </div>
                <div class="question-text">
                    <?= htmlspecialchars($preguntaActual['texto_pregunta'] ?? '¿Qué letra representa esta seña?') ?>
                </div>
            </div>

            <!-- Cuadrícula 2x2 de Opciones (Imagen 4) -->
            <div class="options-grid">
                <?php 
                $opciones = $preguntaActual['opciones'] ?? [];
                // Nombres de etiquetas Opción A, B, C, D
                $letras = ['A', 'B', 'C', 'D'];
                foreach ($opciones as $idx => $op): 
                    $letra = $letras[$idx] ?? chr(65 + $idx);
                ?>
                    <label class="option-box" for="opcion_<?= $op['id_opcion'] ?>">
                        <input type="radio" 
                               name="opcion_seleccionada" 
                               id="opcion_<?= $op['id_opcion'] ?>" 
                               value="<?= $op['id_opcion'] ?>" 
                               class="option-radio" 
                               <?= ($idx === 1 && $preguntaIndex === 2) ? 'checked' : '' ?>>
                        <span class="option-label">Opción <?= $letra ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <!-- Botón Siguiente a la derecha (Imagen 4) -->
            <div class="clearfix">
                <button type="submit" class="btn btn-primary-custom btn-next-custom">
                    Siguiente ▶
                </button>
            </div>
        </form>
    </main>
</div>

<script>
// Interactividad para resaltar la caja seleccionada al hacer clic
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('.option-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.option-box').forEach(b => b.classList.remove('selected'));
            if (this.checked) {
                this.closest('.option-box').classList.add('selected');
            }
        });
        if (radio.checked) {
            radio.closest('.option-box').classList.add('selected');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
