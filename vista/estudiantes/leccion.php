<?php

$tituloPagina = "HablaSinVoz - " . htmlspecialchars($leccion->getTitulo());
$paginaActiva = 'cursos';
$materiales = $leccion->getMultimedia() ?? [];
$urlMaterial = function ($ruta) {
    return preg_match('/\Ahttps?:\/\//i', $ruta)
        ? $ruta
        : BASE_URL . '/' . ltrim($ruta, '/');
};
$tipoMime = function ($ruta) {
    $tipos = [
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'oga' => 'audio/ogg',
        'weba' => 'audio/webm',
        'flac' => 'audio/flac',
        'm4a' => 'audio/mp4',
        'aac' => 'audio/aac',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'apng' => 'image/apng',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon'
    ];
    $extension = strtolower(pathinfo(parse_url($ruta, PHP_URL_PATH) ?? $ruta, PATHINFO_EXTENSION));
    return $tipos[$extension] ?? '';
};
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="dashboard-container">
    <?php require_once __DIR__ . '/../layout/sidebar_estudiante.php'; ?>

    <main class="dashboard-content">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?c=curso&a=misCursos">Mis cursos</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php?c=curso&a=detalle&id=<?= $curso->getIdCurso() ?>"><?= htmlspecialchars($curso->getTitulo()) ?></a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($leccion->getTitulo()) ?></li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="content-title mb-1"><?= htmlspecialchars($leccion->getTitulo()) ?></h1>
                <span class="badge bg-secondary">Lección <?= $leccion->getOrden() ?> • <?= htmlspecialchars($curso->getTitulo()) ?></span>
            </div>
            <div>
                <?php if ($leccion->isCompletada()): ?>
                    <span class="badge bg-success p-2 fs-6"><i class="bi bi-check-circle-fill"></i> Completada</span>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/index.php?c=leccion&a=completar" method="POST" class="d-inline">
                        <input type="hidden" name="id_leccion" value="<?= $leccion->getIdLeccion() ?>">
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-check2"></i> Marcar como completada
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="border rounded p-3 bg-light">
                    <h5 class="fw-bold mb-3"><i class="bi bi-collection-play me-1"></i> Material de la lección</h5>
                    <?php if (empty($materiales)): ?>
                        <p class="text-muted mb-0">No hay material publicado para esta lección.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($materiales as $material): ?>
                                <?php
                                    $rutaMaterial = $material->getRutaEnlace();
                                    $url = $urlMaterial($rutaMaterial);
                                    $mime = $tipoMime($rutaMaterial);
                                ?>
                                <div class="col-md-6">
                                    <div class="bg-white border rounded p-3 h-100">
                                        <?php if ($material->getTipo() === 'Video'): ?>
                                            <video controls preload="metadata" class="w-100 rounded">
                                                <source src="<?= htmlspecialchars($url) ?>" type="<?= htmlspecialchars($mime) ?>">
                                                Tu navegador no puede reproducir este video.
                                            </video>
                                        <?php elseif ($material->getTipo() === 'Audio'): ?>
                                            <audio controls preload="metadata" class="w-100">
                                                <source src="<?= htmlspecialchars($url) ?>" type="<?= htmlspecialchars($mime) ?>">
                                                Tu navegador no puede reproducir este audio.
                                            </audio>
                                        <?php elseif ($material->getTipo() === 'Imagen'): ?>
                                            <img src="<?= htmlspecialchars($url) ?>" alt="Material visual de <?= htmlspecialchars($leccion->getTitulo()) ?>" class="img-fluid rounded" style="max-height: 360px;">
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($url) ?>" class="btn btn-outline-primary" target="_blank" rel="noopener noreferrer">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Abrir material externo
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($material->getTipo() === 'Video' || $material->getTipo() === 'Audio'): ?>
                                            <a href="<?= htmlspecialchars($url) ?>" class="small d-inline-block mt-2" download>Descargar archivo</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12">
                <div class="border rounded p-3 bg-white">
                    <h5 class="fw-bold mb-2 text-primary"><i class="bi bi-hand-index me-1"></i> Instrucciones de ejecución</h5>
                    <p class="text-muted mb-0"><?= nl2br(htmlspecialchars($leccion->getDescripcion())) ?></p>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalReporte">
                <i class="bi bi-flag"></i> Reportar duda o error
            </button>

            <?php if ($idCuestionario !== null): ?>
                <a href="<?= BASE_URL ?>/index.php?c=evaluacion&a=ver&id=<?= $idCuestionario ?>&p=2" class="btn btn-primary-custom">
                    Realizar Evaluación del Curso <i class="bi bi-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Modal para Reportar errores en la lección -->
<div class="modal fade" id="modalReporte" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formReporte">
                <div class="modal-header">
                    <h5 class="modal-title">Reportar duda o error en la seña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Tus comentarios ayudan a los profesores de LSB y administradores de Sacaba a mejorar el contenido pedagógico.</p>
                    <div id="reporteError" class="alert alert-danger d-none" role="alert"></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Comentario u observación:</label>
                        <textarea class="form-control" name="comentario" rows="3" maxlength="2000" placeholder="Describe qué parte del video o seña no se entiende bien..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-custom">Enviar reporte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('formReporte').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const boton = form.querySelector('button[type="submit"]');
    const alerta = document.getElementById('reporteError');
    const comentario = form.querySelector('textarea[name="comentario"]').value.trim();
    alerta.classList.add('d-none');
    boton.disabled = true;
    fetch('<?= BASE_URL ?>/index.php?c=api&a=reportes', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            id_leccion: <?= $leccion->getIdLeccion() ?>,
            comentario: comentario
        })
    }).then(async response => {
        const data = await response.json();
        if (!response.ok || data.status !== 'success') {
            throw new Error(data.message || 'No se pudo enviar el reporte.');
        }
        alert(data.message);
        form.reset();
        bootstrap.Modal.getInstance(document.getElementById('modalReporte')).hide();
    }).catch(error => {
        alerta.textContent = error.message || 'No se pudo enviar el reporte.';
        alerta.classList.remove('d-none');
    }).finally(() => {
        boton.disabled = false;
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
