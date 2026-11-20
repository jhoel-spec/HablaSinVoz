<?php

$tituloPagina = "HablaSinVoz - Mi perfil";
$paginaActiva = 'perfil';
$esEstudiante = (int)($_SESSION['usuario_rol'] ?? 0) === 1;
$esProfesor = (int)($_SESSION['usuario_rol'] ?? 0) === 2;
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="dashboard-container">
    <?php if ($esEstudiante): ?>
        <?php require __DIR__ . '/../layout/sidebar_estudiante.php'; ?>
    <?php endif; ?>

    <main class="dashboard-content">
        <h1 class="content-title">Mi Perfil</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="<?= $esEstudiante ? 'col-md-6' : 'col-md-12' ?>">
                <div class="card border p-3">
                    <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2"></i> Datos de la cuenta</h5>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Nombre completo:</label>
                        <div class="fs-6 fw-bold"><?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Correo electrónico:</label>
                        <div class="fs-6"><?= htmlspecialchars($_SESSION['usuario_correo'] ?? '') ?></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Rol en la plataforma:</label>
                        <div>
                            <span class="badge bg-primary fs-6"><?= htmlspecialchars($_SESSION['usuario_rol_nombre'] ?? 'Estudiante') ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($esEstudiante): ?>
                <div class="col-md-6">
                    <div class="card border p-3 bg-light">
                        <h5 class="fw-bold mb-2"><i class="bi bi-award me-2"></i> ¿Deseas enseñar LSB?</h5>
                        <p class="small text-muted">
                            Intérpretes, docentes y miembros de la comunidad sorda de Sacaba pueden postular para crear cursos y subir contenido propio.
                        </p>

                        <?php if (($solicitudProfesor['estado'] ?? '') === 'Pendiente'): ?>
                            <div class="alert alert-info mb-0">
                                Tu solicitud está pendiente de revisión por administración. Te avisaremos cuando sea validada.
                            </div>
                        <?php else: ?>
                        <?php if (($solicitudProfesor['estado'] ?? '') === 'Rechazada'): ?>
                            <div class="alert alert-warning">
                                Tu solicitud anterior fue rechazada. Puedes volver a postular con información y documentos actualizados.
                            </div>
                        <?php endif; ?>
                        <form action="<?= BASE_URL ?>/index.php?c=auth&a=perfil" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Describe tu experiencia con la Lengua de Señas Boliviana:</label>
                                <textarea class="form-control" name="experiencia" rows="3" placeholder="Ej: Intérprete certificado, docente de educación especial, persona sorda nativa..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="documento" class="form-label fw-bold small">Certificado o requisito de respaldo *</label>
                                <input class="form-control" type="file" id="documento" name="documento" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp" required>
                                <div class="form-text">Adjunta un archivo PDF, Word o imagen de hasta 10 MB. Solo administración podrá descargarlo.</div>
                            </div>
                            <button type="submit" class="btn btn-primary-custom btn-sm">
                                Enviar postulación como Profesor
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($esEstudiante): ?>
            <section class="card border mt-4">
                <div class="card-header bg-light fw-bold">Mis dudas y reportes</div>
                <?php if (empty($reportesEstudiante)): ?>
                    <div class="card-body text-muted">Aún no has enviado dudas o reportes.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($reportesEstudiante as $reporteEstudiante): ?>
                            <article class="list-group-item p-3">
                                <div class="d-flex flex-wrap justify-content-between gap-2">
                                    <strong><?= htmlspecialchars($reporteEstudiante['curso']) ?> · <?= htmlspecialchars($reporteEstudiante['leccion']) ?></strong>
                                    <span class="badge bg-<?= $reporteEstudiante['estado'] === 'Pendiente' ? 'warning text-dark' : 'success' ?>">
                                        <?= htmlspecialchars($reporteEstudiante['estado']) ?>
                                    </span>
                                </div>
                                <p class="mb-2 mt-2"><?= nl2br(htmlspecialchars($reporteEstudiante['comentario'])) ?></p>
                                <?php if (!empty($reporteEstudiante['respuesta'])): ?>
                                    <div class="alert alert-success mb-0">
                                        <strong>Respuesta del profesor:</strong><br>
                                        <?= nl2br(htmlspecialchars($reporteEstudiante['respuesta'])) ?>
                                    </div>
                                <?php else: ?>
                                    <small class="text-muted">El profesor responderá desde el panel del curso.</small>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if ($esProfesor): ?>
            <div class="row g-4 mt-1">
                <div class="col-12">
                    <h2 class="h4 fw-bold">Mi actividad como profesor</h2>
                </div>
                <div class="col-lg-4">
                    <section class="card border h-100">
                        <div class="card-header bg-light fw-bold">Cursos que imparto (<?= count($cursosProfesor) ?>)</div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($cursosProfesor)): ?>
                                <div class="list-group-item text-muted">Aún no tienes cursos asignados.</div>
                            <?php else: ?>
                                <?php foreach ($cursosProfesor as $cursoProfesor): ?>
                                    <div class="list-group-item">
                                        <strong><?= htmlspecialchars($cursoProfesor->getTitulo()) ?></strong>
                                        <span class="badge bg-secondary ms-1"><?= htmlspecialchars($cursoProfesor->getEstado()) ?></span>
                                        <small class="d-block text-muted"><?= htmlspecialchars($cursoProfesor->getNivel()) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
                <div class="col-lg-4">
                    <section class="card border h-100">
                        <div class="card-header bg-light fw-bold">Material subido (<?= count($materialesProfesor) ?>)</div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($materialesProfesor)): ?>
                                <div class="list-group-item text-muted">Aún no has subido material.</div>
                            <?php else: ?>
                                <?php foreach ($materialesProfesor as $materialProfesor): ?>
                                    <div class="list-group-item">
                                        <strong><?= htmlspecialchars($materialProfesor['leccion']) ?></strong>
                                        <small class="d-block text-muted">
                                            <?= htmlspecialchars($materialProfesor['curso']) ?> ·
                                            <?= htmlspecialchars($materialProfesor['tipo']) ?> ·
                                            <?= htmlspecialchars($materialProfesor['estado_leccion']) ?>
                                        </small>
                                        <a href="<?= htmlspecialchars(preg_match('/\Ahttps?:\/\//i', $materialProfesor['ruta_enlace']) ? $materialProfesor['ruta_enlace'] : BASE_URL . '/' . ltrim($materialProfesor['ruta_enlace'], '/')) ?>" target="_blank" rel="noopener noreferrer" class="small">Ver material</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
                <div class="col-lg-4">
                    <section class="card border h-100">
                        <div class="card-header bg-light fw-bold">Dudas pendientes (<?= count($reportesPendientesProfesor) ?>)</div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($reportesPendientesProfesor)): ?>
                                <div class="list-group-item text-muted">No tienes dudas pendientes de respuesta.</div>
                            <?php else: ?>
                                <?php foreach (array_slice($reportesPendientesProfesor, 0, 5) as $reportePendiente): ?>
                                    <div class="list-group-item">
                                        <strong><?= htmlspecialchars($reportePendiente['estudiante']) ?></strong>
                                        <small class="d-block text-muted"><?= htmlspecialchars($reportePendiente['curso']) ?> · <?= htmlspecialchars($reportePendiente['leccion']) ?></small>
                                        <p class="small mb-0 mt-1"><?= nl2br(htmlspecialchars($reportePendiente['comentario'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="card-body pt-2">
                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos#reportes" class="btn btn-sm btn-outline-primary">Gestionar dudas</a>
                        </div>
                    </section>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
