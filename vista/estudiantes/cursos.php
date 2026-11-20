<?php

$tituloPagina = "HablaSinVoz - Mis cursos";
$paginaActiva = 'cursos';
if (!isset($cursos)) {
    require_once __DIR__ . '/../../modelo/CursoDao.php';
    $cursoDao = new CursoDao();
    $cursos = $cursoDao->obtenerCursosEstudianteConProgreso(3);
}
$cursosInscritos = array_values(array_filter($cursos, function ($curso) {
    return $curso->estaInscrito();
}));
$cursosDisponibles = array_values(array_filter($cursos, function ($curso) {
    return !$curso->estaInscrito();
}));
$mensajesInscripcion = [
    'inscripcion_ok' => 'Te inscribiste al curso. Ya puedes comenzar con sus lecciones.',
    'inscripcion_error' => 'No se pudo completar la inscripción. Verifica que el curso esté publicado e inténtalo nuevamente.'
];
$mensajeInscripcion = $mensajesInscripcion[$_GET['msg'] ?? ''] ?? null;
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="dashboard-container">
    <!-- Sidebar ESTUDIANTE (Imagen 3) -->
    <?php require_once __DIR__ . '/../layout/sidebar_estudiante.php'; ?>

    <!-- Contenido Principal (Imagen 3) -->
    <main class="dashboard-content">
        <h1 class="content-title">Mis cursos</h1>
        <?php if (!empty($errorCursos)): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorCursos) ?></div>
        <?php endif; ?>
        <?php if (($_GET['msg'] ?? '') === 'recurso_no_disponible'): ?>
            <div class="alert alert-info" role="alert">El curso o material que intentaste abrir ya no está disponible.</div>
        <?php endif; ?>
        <?php if ($mensajeInscripcion): ?>
            <div class="alert <?= ($_GET['msg'] ?? '') === 'inscripcion_ok' ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?= htmlspecialchars($mensajeInscripcion) ?>
            </div>
        <?php endif; ?>

        <section class="mb-5">
            <h2 class="h4 fw-bold mb-3">Cursos inscritos</h2>
            <div class="row g-4">
            <?php if (empty($cursosInscritos)): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm text-center p-5">
                        <i class="bi bi-journal-plus fs-1 text-primary mb-3"></i>
                        <h3 class="h5 fw-bold">Aún no estás inscrito en ningún curso</h3>
                        <p class="text-muted mb-0">Elige un curso disponible para agregarlo a tu lista de aprendizaje.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($cursosInscritos as $c): ?>
                    <div class="col-md-4">
                        <div class="course-card-custom">
                            <div class="course-image-box">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($c->getImagen()) ?>" alt="<?= htmlspecialchars($c->getTitulo()) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/cursos/curso_1.svg'">
                            </div>
                            <h2 class="course-title-custom"><?= htmlspecialchars($c->getTitulo()) ?></h2>
                            <div class="progress-bar-container" role="progressbar" aria-valuenow="<?= $c->getPorcentajeProgreso() ?>" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar-fill" style="width: <?= $c->getPorcentajeProgreso() ?>%;"></div>
                            </div>
                            <div class="progress-text mb-3">
                                <?= $c->getPorcentajeProgreso() ?>% completado
                            </div>
                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=detalle&id=<?= (int)$c->getIdCurso() ?>" class="btn btn-outline-primary mt-auto">
                                Continuar aprendiendo
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </section>

        <section>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h4 fw-bold mb-0">Cursos disponibles</h2>
                <a href="<?= BASE_URL ?>/index.php?c=curso&a=catalogo" class="small">Ver catálogo</a>
            </div>
            <div class="row g-4">
                <?php if (empty($cursos)): ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">No hay cursos publicados disponibles en este momento.</div>
                    </div>
                <?php elseif (empty($cursosDisponibles)): ?>
                    <div class="col-12">
                        <div class="alert alert-info mb-0">Ya estás inscrito en todos los cursos publicados.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($cursosDisponibles as $c): ?>
                        <div class="col-md-4">
                            <div class="course-card-custom">
                                <div class="course-image-box">
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($c->getImagen()) ?>" alt="<?= htmlspecialchars($c->getTitulo()) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/cursos/curso_1.svg'">
                                </div>
                                <span class="badge bg-primary mb-2 align-self-start"><?= htmlspecialchars($c->getNivel()) ?></span>
                                <h3 class="course-title-custom"><?= htmlspecialchars($c->getTitulo()) ?></h3>
                                <p class="text-muted small flex-grow-1"><?= htmlspecialchars($c->getDescripcion()) ?></p>
                                <p class="small text-secondary"><?= htmlspecialchars($c->getNombreProfesor()) ?> • <?= (int)$c->getTotalLecciones() ?> lecciones</p>
                                <form action="<?= BASE_URL ?>/index.php?c=curso&a=tomarCurso" method="POST" class="mt-auto">
                                    <input type="hidden" name="id_curso" value="<?= (int)$c->getIdCurso() ?>">
                                    <button type="submit" class="btn btn-primary-custom w-100">
                                        <i class="bi bi-plus-circle me-1"></i> Tomar curso
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <?php if (!empty($cursosInscritos)): ?>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/index.php?c=curso&a=continuarCurso" class="btn btn-primary-custom">
                    Continuar curso
                </a>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
