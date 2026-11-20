<?php

$tituloPagina = "Contenido del curso - " . htmlspecialchars($curso->getTitulo());
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
$urlMaterial = function ($ruta) {
    return preg_match('/\Ahttps?:\/\//i', $ruta)
        ? $ruta
        : BASE_URL . '/' . ltrim($ruta, '/');
};
?>

<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <a href="<?= htmlspecialchars($urlRegreso) ?>" class="small text-decoration-none">&larr; Volver al panel</a>
            <h1 class="content-title mb-1 mt-2"><?= htmlspecialchars($curso->getTitulo()) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($curso->getNivel()) ?> · <?= htmlspecialchars($curso->getEstado()) ?></p>
        </div>
    </div>

    <section class="card border mb-4">
        <div class="card-header bg-light fw-bold">Descripción del curso</div>
        <div class="card-body">
            <p class="mb-0"><?= nl2br(htmlspecialchars($curso->getDescripcion())) ?></p>
        </div>
    </section>

    <section class="card border">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold">Lecciones y materiales</span>
            <span class="badge bg-secondary"><?= count($lecciones) ?> lecciones</span>
        </div>
        <?php if (empty($lecciones)): ?>
            <div class="card-body text-muted">Este curso todavía no tiene lecciones.</div>
        <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($lecciones as $leccion): ?>
                    <article class="list-group-item p-3">
                        <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                            <h2 class="h5 mb-0">#<?= (int)$leccion->getOrden() ?> — <?= htmlspecialchars($leccion->getTitulo()) ?></h2>
                            <span class="badge bg-secondary align-self-start"><?= htmlspecialchars($leccion->getEstado()) ?></span>
                        </div>
                        <?php if ($leccion->getDescripcion() !== ''): ?>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($leccion->getDescripcion())) ?></p>
                        <?php endif; ?>

                        <?php $materiales = $leccion->getMultimedia() ?? []; ?>
                        <?php if (empty($materiales)): ?>
                            <p class="small text-muted mb-0">Sin materiales adjuntos.</p>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($materiales as $material): ?>
                                    <?php
                                    $ruta = $material->getRutaEnlace();
                                    $url = $urlMaterial($ruta);
                                    $extension = strtolower(pathinfo(parse_url($ruta, PHP_URL_PATH) ?? $ruta, PATHINFO_EXTENSION));
                                    ?>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <span class="badge bg-info text-dark mb-2"><?= htmlspecialchars($material->getTipo()) ?></span>
                                            <?php if ($material->getTipo() === 'Video'): ?>
                                                <video controls preload="metadata" class="w-100 rounded">
                                                    <source src="<?= htmlspecialchars($url) ?>" type="<?= htmlspecialchars([
                                                        'mp4' => 'video/mp4',
                                                        'webm' => 'video/webm',
                                                        'ogv' => 'video/ogg'
                                                    ][$extension] ?? '') ?>">
                                                    Tu navegador no puede reproducir este video.
                                                </video>
                                            <?php elseif ($material->getTipo() === 'Audio'): ?>
                                                <audio controls preload="metadata" class="w-100">
                                                    <source src="<?= htmlspecialchars($url) ?>">
                                                    Tu navegador no puede reproducir este audio.
                                                </audio>
                                            <?php elseif ($material->getTipo() === 'Imagen'): ?>
                                                <img src="<?= htmlspecialchars($url) ?>" alt="Material de <?= htmlspecialchars($leccion->getTitulo()) ?>" class="img-fluid rounded">
                                            <?php else: ?>
                                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener noreferrer">Abrir material</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
