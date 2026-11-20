<?php

$tituloPagina = "HablaSinVoz - Panel del Profesor";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title mb-1">Panel del Profesor</h1>
            <p class="text-muted mb-0">Gestión de cursos y contenidos pedagógicos en Lengua de Señas Boliviana</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=crear" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle me-1"></i> Crear curso
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=subida&a=subir" class="btn btn-primary-custom">
                <i class="bi bi-cloud-upload me-1"></i> Subir material
            </a>
        </div>
    </div>

    <?php if (in_array($_GET['msg'] ?? '', ['curso_creado', 'curso_enviado_revision'], true)): ?>
        <div class="alert alert-success" role="alert">
            <?= ($_GET['msg'] ?? '') === 'curso_creado' ? 'El curso fue creado y enviado a revisión.' : 'El curso fue enviado a revisión.' ?>
        </div>
    <?php elseif (($_GET['msg'] ?? '') === 'curso_revision_error'): ?>
        <div class="alert alert-danger" role="alert">No se pudo enviar el curso a revisión. Actualiza la página e inténtalo nuevamente.</div>
    <?php elseif (($_GET['msg'] ?? '') === 'reporte_resuelto'): ?>
        <div class="alert alert-success" role="alert">La respuesta fue enviada al estudiante y el reporte quedó resuelto.</div>
    <?php elseif (($_GET['msg'] ?? '') === 'reporte_error'): ?>
        <div class="alert alert-danger" role="alert">No se pudo responder el reporte. Verifica tu respuesta y vuelve a intentarlo.</div>
    <?php endif; ?>
    <?php if (!empty($errorCursos)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorCursos) ?></div>
    <?php endif; ?>
    <?php if (!empty($errorEstudiantes)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorEstudiantes) ?></div>
    <?php endif; ?>
    <?php if (!empty($errorReportes)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($errorReportes) ?></div>
    <?php endif; ?>

    <section class="card border mb-4" id="reportes">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="bi bi-chat-left-text me-1"></i> Dudas y reportes de estudiantes</span>
            <span class="badge bg-<?= count(array_filter($reportes, function ($reporte) { return $reporte['estado'] === 'Pendiente'; })) ? 'warning text-dark' : 'success' ?>">
                <?= count(array_filter($reportes, function ($reporte) { return $reporte['estado'] === 'Pendiente'; })) ?> pendientes
            </span>
        </div>
        <?php if (empty($reportes)): ?>
            <div class="card-body text-muted">Todavía no hay dudas ni reportes en tus cursos.</div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($reportes as $reporte): ?>
                    <article class="list-group-item p-3">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <div>
                                <strong><?= htmlspecialchars($reporte['estudiante']) ?></strong>
                                <span class="text-muted">en <?= htmlspecialchars($reporte['curso']) ?> · <?= htmlspecialchars($reporte['leccion']) ?></span>
                            </div>
                            <span class="badge bg-<?= $reporte['estado'] === 'Pendiente' ? 'warning text-dark' : 'success' ?>">
                                <?= htmlspecialchars($reporte['estado']) ?>
                            </span>
                        </div>
                        <p class="mb-2"><?= nl2br(htmlspecialchars($reporte['comentario'])) ?></p>
                        <?php if (!empty($reporte['fecha_reporte'])): ?>
                            <small class="text-muted d-block mb-2">Enviado <?= htmlspecialchars(date('d/m/Y H:i', strtotime($reporte['fecha_reporte']))) ?></small>
                        <?php endif; ?>
                        <?php if ($reporte['estado'] === 'Pendiente'): ?>
                            <form action="<?= BASE_URL ?>/index.php?c=curso&a=responderReporte" method="POST" class="mt-3">
                                <input type="hidden" name="id_reporte" value="<?= (int)$reporte['id_reporte'] ?>">
                                <label for="respuesta-<?= (int)$reporte['id_reporte'] ?>" class="form-label fw-semibold">Responder y marcar como resuelto</label>
                                <textarea class="form-control mb-2" id="respuesta-<?= (int)$reporte['id_reporte'] ?>" name="respuesta" rows="2" maxlength="2000" required placeholder="Escribe una respuesta para el estudiante"></textarea>
                                <button type="submit" class="btn btn-sm btn-primary-custom">Enviar respuesta</button>
                            </form>
                        <?php elseif (!empty($reporte['respuesta'])): ?>
                            <div class="alert alert-success mb-0 mt-3">
                                <strong>Respuesta del profesor:</strong><br>
                                <?= nl2br(htmlspecialchars($reporte['respuesta'])) ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <div class="card border">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold">Mis cursos asignados</span>
            <span class="badge bg-secondary"><?= count($cursos) ?> cursos registrados</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Título del Curso</th>
                            <th>Nivel</th>
                            <th>Lecciones</th>
                            <th>Estudiantes</th>
                            <th>Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cursos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-journal-plus fs-2 text-muted d-block mb-2"></i>
                                    <div class="fw-semibold">Aún no tienes cursos</div>
                                    <div class="text-muted small mb-3">Crea tu primer curso para empezar a organizar tus lecciones.</div>
                                    <a href="<?= BASE_URL ?>/index.php?c=curso&a=crear" class="btn btn-sm btn-primary-custom">Crear curso</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cursos as $c): ?>
                                <tr>
                                    <td>#<?= $c->getIdCurso() ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($c->getTitulo()) ?></td>
                                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($c->getNivel()) ?></span></td>
                                    <td><?= $c->getTotalLecciones() ?> lecciones</td>
                                    <td>
                                        <?php $cantidadEstudiantes = count($estudiantesPorCurso[(int)$c->getIdCurso()] ?? []); ?>
                                        <a href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos&id_curso=<?= (int)$c->getIdCurso() ?>#estudiantes" class="btn btn-sm btn-outline-secondary">
                                            Ver (<?= $cantidadEstudiantes ?>)
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($c->getEstado() === 'Publicado'): ?>
                                            <span class="badge bg-success">Publicado</span>
                                        <?php elseif ($c->getEstado() === 'Revisión'): ?>
                                            <span class="badge bg-warning text-dark">En Revisión</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Borrador</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=verContenido&id_curso=<?= (int)$c->getIdCurso() ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-eye"></i> Ver contenido
                                            </a>
                                            <a href="<?= BASE_URL ?>/index.php?c=subida&a=subir&id_curso=<?= $c->getIdCurso() ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-cloud-upload"></i> Subir material
                                            </a>
                                            <?php if ($c->getEstado() === 'Borrador'): ?>
                                                <form action="<?= BASE_URL ?>/index.php?c=curso&a=enviarCursoRevision" method="POST" class="d-inline">
                                                    <input type="hidden" name="id_curso" value="<?= $c->getIdCurso() ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">Enviar a revisión</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($cursoSeleccionado): ?>
                <div class="card border mt-4" id="estudiantes">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Estudiantes de <?= htmlspecialchars($cursoSeleccionado->getTitulo()) ?></span>
                        <span class="badge bg-secondary"><?= count($estudiantesCurso) ?> participantes</span>
                    </div>
                    <?php if (empty($estudiantesCurso)): ?>
                        <div class="card-body text-muted">Aún no hay estudiantes inscritos o con actividad en este curso.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Correo</th>
                                        <th>Estado</th>
                                        <th>Inscripción</th>
                                        <th>Lecciones completadas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($estudiantesCurso as $estudiante): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($estudiante['nombre']) ?></td>
                                            <td><?= htmlspecialchars($estudiante['correo']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= ($estudiante['estado_usuario'] ?? '') === 'Activo' ? 'success' : 'secondary' ?>">
                                                    <?= htmlspecialchars($estudiante['estado_usuario'] ?? 'Desconocido') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($estudiante['inscrito'])): ?>
                                                    <?= !empty($estudiante['fecha_inscripcion']) ? htmlspecialchars(date('d/m/Y', strtotime($estudiante['fecha_inscripcion']))) : 'Inscrito' ?>
                                                <?php else: ?>
                                                    Actividad registrada
                                                <?php endif; ?>
                                            </td>
                                            <td><?= (int)$estudiante['lecciones_completadas'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
