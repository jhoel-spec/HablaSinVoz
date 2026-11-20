<?php

$tituloPagina = "HablaSinVoz - Aprobación de Contenido";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title mb-1">Moderación y Aprobación de Contenido</h1>
            <p class="text-muted mb-0">Revisión pedagógica de lecciones y videos de LSB antes de su publicación general</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=crear" class="btn btn-primary-custom me-2">
                <i class="bi bi-plus-circle me-1"></i> Crear curso
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=cursos" class="btn btn-outline-primary me-2">
                <i class="bi bi-journal-text me-1"></i> Gestionar cursos
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i> Ir a usuarios y roles
            </a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card border mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-journal-check me-1 text-primary"></i> Cursos pendientes de revisión</span>
            <span class="badge bg-warning text-dark"><?= count($cursosEnRevision) ?> pendientes</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Curso</th>
                            <th>Nivel</th>
                            <th>Profesor</th>
                            <th>Lecciones</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cursosEnRevision)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    No hay cursos pendientes de revisión.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cursosEnRevision as $cursoRevision): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($cursoRevision->getTitulo()) ?></td>
                                    <td><?= htmlspecialchars($cursoRevision->getNivel()) ?></td>
                                    <td><?= htmlspecialchars($cursoRevision->getNombreProfesor()) ?></td>
                                    <td><?= $cursoRevision->getTotalLecciones() ?></td>
                                    <td class="text-end text-nowrap">
                                        <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones&aprobar_curso=<?= $cursoRevision->getIdCurso() ?>" class="btn btn-sm btn-success">
                                            Aprobar y publicar
                                        </a>
                                        <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones&observar_curso=<?= $cursoRevision->getIdCurso() ?>" class="btn btn-sm btn-outline-danger">
                                            Devolver a borrador
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

    <!-- Lecciones Pendientes de Aprobación -->
    <div class="card border mb-4">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-hourglass-split me-1 text-warning"></i> Lecciones pendientes de revisión</span>
            <span class="badge bg-warning text-dark"><?= count($pendientes) ?> pendientes</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lección</th>
                            <th>Curso</th>
                            <th>Profesor</th>
                            <th>Descripción</th>
                            <th class="text-end">Acción de moderación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendientes)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-check-all text-success fs-4 d-block mb-1"></i>
                                    No hay lecciones pendientes de revisión en este momento.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendientes as $p): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($p['titulo']) ?></td>
                                    <td><?= htmlspecialchars($p['nombre_curso']) ?></td>
                                    <td><?= htmlspecialchars($p['nombre_profesor']) ?></td>
                                    <td><small class="text-muted"><?= htmlspecialchars($p['descripcion']) ?></small></td>
                                    <td class="text-end">
                                        <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones&aprobar_leccion=<?= $p['id_leccion'] ?><?= $idCursoFiltro ? '&id_curso=' . $idCursoFiltro : '' ?>" class="btn btn-sm btn-success me-1">
                                            <i class="bi bi-check-lg"></i> Publicar
                                        </a>
                                        <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones&observar_leccion=<?= $p['id_leccion'] ?><?= $idCursoFiltro ? '&id_curso=' . $idCursoFiltro : '' ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-x-lg"></i> Observar
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

    <!-- Resumen de Todos los Cursos -->
    <div class="card border">
        <div class="card-header bg-light fw-bold">
            <i class="bi bi-journal-text me-1"></i> Cursos registrados en la plataforma
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Curso</th>
                            <th>Nivel</th>
                            <th>Profesor</th>
                            <th>Total Lecciones</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($todosCursos)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay cursos registrados.</td>
                            </tr>
                        <?php else: ?>
                        <?php foreach ($todosCursos as $cur): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($cur->getTitulo()) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($cur->getNivel()) ?></span></td>
                                <td><?= htmlspecialchars($cur->getNombreProfesor()) ?></td>
                                <td><?= $cur->getTotalLecciones() ?></td>
                                <td>
                                    <span class="badge bg-<?= ($cur->getEstado() === 'Publicado') ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($cur->getEstado()) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
