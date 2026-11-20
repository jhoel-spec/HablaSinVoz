<?php

$tituloPagina = "HablaSinVoz - Mi progreso";
$paginaActiva = 'progreso';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="dashboard-container">
    <?php require_once __DIR__ . '/../layout/sidebar_estudiante.php'; ?>

    <main class="dashboard-content">
        <h1 class="content-title">Mi Progreso Académico</h1>

        <!-- Métricas Rápidas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 border rounded text-center bg-light">
                    <div class="fs-2 fw-bold text-primary"><?= $resumen['lecciones_completadas'] ?? 0 ?></div>
                    <div class="text-muted small">Lecciones completadas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded text-center bg-light">
                    <div class="fs-2 fw-bold text-success"><?= count($cursos) ?></div>
                    <div class="text-muted small">Cursos en avance</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded text-center bg-light">
                    <div class="fs-2 fw-bold text-info"><?= $resumen['evaluaciones_realizadas'] ?? 0 ?></div>
                    <div class="text-muted small">Evaluaciones rendidas</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded text-center bg-light">
                    <div class="fs-2 fw-bold text-warning"><?= round($resumen['promedio_calificaciones'] ?? 0) ?>%</div>
                    <div class="text-muted small">Promedio de aprobación</div>
                </div>
            </div>
        </div>

        <h3 class="fw-bold mb-3">Avance por curso</h3>
        <div class="card border mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Curso</th>
                                <th>Nivel</th>
                                <th>Progreso</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($cursos)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        Aún no hay cursos publicados para mostrar tu progreso.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($cursos as $c): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($c->getTitulo()) ?></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($c->getNivel()) ?></span></td>
                                        <td style="min-width: 200px;">
                                            <div class="progress-bar-container mb-1">
                                                <div class="progress-bar-fill" style="width: <?= $c->getPorcentajeProgreso() ?>%;"></div>
                                            </div>
                                            <small class="text-muted fw-bold"><?= $c->getPorcentajeProgreso() ?>% completado</small>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=detalle&id=<?= $c->getIdCurso() ?>" class="btn btn-sm btn-outline-primary">
                                                Continuar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
