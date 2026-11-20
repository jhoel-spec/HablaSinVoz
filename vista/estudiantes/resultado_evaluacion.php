<?php

$tituloPagina = "HablaSinVoz - Resultado de la Evaluación";
$paginaActiva = 'evaluacion';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="dashboard-container">
    <?php require_once __DIR__ . '/../layout/sidebar_estudiante.php'; ?>

    <main class="dashboard-content">
        <div class="card border p-4 text-center mx-auto" style="max-width: 580px;">
            <div class="mb-3">
                <i class="bi bi-patch-check-fill text-success" style="font-size: 4rem;"></i>
            </div>
            <h2 class="fw-bold text-primary mb-2">¡Evaluación Finalizada!</h2>
            <p class="text-muted">Has completado el cuestionario de Alfabeto dactilológico en LSB.</p>

            <div class="display-4 fw-bold text-success my-3">
                <?= $calificacion ?>%
            </div>
            <p class="fw-semibold text-secondary">
                <?= ($calificacion >= 60) ? '¡Excelente trabajo! Has demostrado un gran dominio de las señas.' : 'Sigue practicando las posiciones y la orientación de la mano.' ?>
            </p>

            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="<?= BASE_URL ?>/index.php?c=curso&a=misCursos" class="btn btn-primary-custom">
                    Volver a mis cursos
                </a>
                <a href="<?= BASE_URL ?>/index.php?c=evaluacion&a=ver&id=1&p=1" class="btn btn-outline-secondary">
                    Repetir evaluación
                </a>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
