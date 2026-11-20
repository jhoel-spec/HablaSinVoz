<?php

$tituloPagina = "HablaSinVoz - " . htmlspecialchars($curso->getTitulo());
$paginaActiva = 'cursos';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<?php if (!empty($autenticado)): ?>
<div class="dashboard-container">
    <?php require_once __DIR__ . '/../layout/sidebar_estudiante.php'; ?>
    <main class="dashboard-content">
<?php else: ?>
<main class="container py-4">
<?php endif; ?>
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?c=<?= !empty($autenticado) ? 'curso&a=misCursos' : 'curso&a=catalogo' ?>"><?= !empty($autenticado) ? 'Mis cursos' : 'Catálogo' ?></a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($curso->getTitulo()) ?></li>
            </ol>
        </nav>

        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-body">
                <span class="badge bg-primary mb-2"><?= htmlspecialchars($curso->getNivel()) ?></span>
                <h1 class="content-title mb-2"><?= htmlspecialchars($curso->getTitulo()) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($curso->getDescripcion()) ?></p>
                <div class="text-secondary small">
                    <i class="bi bi-person-fill"></i> Profesor: <strong><?= htmlspecialchars($curso->getNombreProfesor()) ?></strong>
                </div>
            </div>
        </div>

        <h3 class="fw-bold mb-3">Lecciones del Curso</h3>
        <div class="list-group">
            <?php if (empty($lecciones)): ?>
                <div class="alert alert-info">
                    Este curso todavía no tiene lecciones publicadas. Vuelve pronto para continuar.
                </div>
            <?php else: ?>
            <?php foreach ($lecciones as $l): ?>
                <a href="<?= BASE_URL ?>/index.php?c=<?= !empty($autenticado) ? 'leccion&a=ver&id=' . (int)$l->getIdLeccion() : 'auth&a=login' ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                    <div>
                        <span class="badge bg-secondary me-2">#<?= $l->getOrden() ?></span>
                        <strong><?= htmlspecialchars($l->getTitulo()) ?></strong>
                        <p class="mb-0 text-muted small mt-1"><?= htmlspecialchars($l->getDescripcion()) ?></p>
                    </div>
                    <div>
                        <?php if (empty($autenticado)): ?>
                            <span class="badge bg-primary">Inicia sesión para ver</span>
                        <?php elseif ($l->isCompletada()): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Completada</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border">Pendiente</span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($autenticado) && !empty($lecciones) && $idCuestionario !== null): ?>
            <div class="mt-4">
                <a href="<?= BASE_URL ?>/index.php?c=evaluacion&a=ver&id=<?= $idCuestionario ?>&p=2" class="btn btn-primary-custom">
                    Ir a la Evaluación <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>
<?php if (!empty($autenticado)): ?>
    </main>
</div>
<?php else: ?>
</main>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
