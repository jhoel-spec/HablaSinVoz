<?php

$tituloPagina = "HablaSinVoz - Subir Contenido";
$cursoSeleccionado = (int)($_POST['id_curso'] ?? $_GET['id_curso'] ?? 0);
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border">
                <div class="card-header bg-light py-3">
                    <h2 class="fs-4 fw-bold mb-0 text-primary">
                        <i class="bi bi-cloud-arrow-up me-2"></i> Subir material al curso
                    </h2>
                    <small class="text-muted">La lección y su material quedarán <strong>Pendientes de revisión</strong> antes de publicarse.</small>
                </div>

                <div class="card-body p-4">
                    <?php if (!empty($mensaje)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($mensaje) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/index.php?c=subida&a=subir" method="POST" class="border rounded bg-light p-3 mb-4">
                        <input type="hidden" name="accion" value="actualizar_limite">
                        <label for="limite_mb" class="form-label fw-bold mb-1">Tamaño máximo por archivo</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="limite_mb" name="limite_mb" min="1" max="500" value="<?= htmlspecialchars((string)($limiteMb ?? 50)) ?>" required>
                            <span class="input-group-text">MB</span>
                            <button type="submit" class="btn btn-outline-primary">Guardar límite</button>
                        </div>
                        <div class="form-text">Puedes elegir entre 1 y 500 MB. El servidor también debe permitir ese tamaño en su configuración de PHP.</div>
                    </form>

                    <?php if (empty($cursos)): ?>
                        <div class="alert alert-info">
                            Primero debes crear un curso para poder agregarle material.
                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=crear" class="alert-link">Crear curso</a>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/index.php?c=subida&a=subir" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="id_curso" class="form-label-custom">Curso destino *</label>
                            <select name="id_curso" id="id_curso" class="form-select" required <?= empty($cursos) ? 'disabled' : '' ?>>
                                <option value="">-- Seleccionar curso --</option>
                                <?php foreach ($cursos as $cur): ?>
                                    <option value="<?= $cur->getIdCurso() ?>" <?= $cursoSeleccionado === (int)$cur->getIdCurso() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cur->getTitulo()) ?> (<?= htmlspecialchars($cur->getNivel()) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="titulo" class="form-label-custom">Título de la lección o seña *</label>
                            <input type="text" class="form-input-custom" id="titulo" name="titulo" maxlength="150" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" placeholder="Ej: Letras J, K y L o Saludo formal" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label-custom">Descripción y consejos de ejecución</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Explica la orientación de la palma, posición de los dedos y expresión facial..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="orden" class="form-label-custom">Número de orden</label>
                            <input type="number" class="form-input-custom" id="orden" name="orden" value="<?= htmlspecialchars($_POST['orden'] ?? '1') ?>" min="1" required>
                        </div>

                        <div class="mb-3">
                            <label for="enlace_externo" class="form-label-custom">Enlace multimedia (opcional)</label>
                            <input type="url" class="form-input-custom" id="enlace_externo" name="enlace_externo" value="<?= htmlspecialchars($_POST['enlace_externo'] ?? '') ?>" placeholder="https://...">
                        </div>

                        <div class="mb-4">
                            <label for="archivo" class="form-label-custom">Multimedia (opcional si ingresas un enlace)</label>
                            <input type="file" class="form-control" id="archivo" name="archivo" data-max-bytes="<?= (int)($limiteMb ?? 0) * 1024 * 1024 ?>" accept=".mp4,.webm,.ogv,.mp3,.wav,.ogg,.oga,.weba,.flac,.m4a,.aac,.jpg,.jpeg,.png,.apng,.gif,.webp,.avif,.bmp,.ico">
                            <small class="text-muted">Video, audio e imágenes hasta <?= (int)($limiteMb ?? 0) ?> MB por archivo. El navegador debe ser compatible con el formato para reproducirlo.</small>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos" class="btn btn-outline-secondary">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary-custom" <?= empty($cursos) ? 'disabled' : '' ?>>
                                <i class="bi bi-send me-1"></i> Guardar y enviar a revisión
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
