<?php

$esAdmin = (int)($_SESSION['usuario_rol'] ?? 0) === 3;
$tituloPagina = "HablaSinVoz - Crear curso";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<main class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="content-title mb-1">Crear un curso</h1>
            <p class="text-muted mb-0">
                <?= $esAdmin ? 'Asigna el curso a un profesor activo.' : 'Completa los datos para enviar el curso a revisión.' ?>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/index.php?c=<?= $esAdmin ? 'admin&a=usuarios' : 'curso&a=profesorCursos' ?>" class="btn btn-outline-secondary">
            Volver al panel
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($esAdmin && empty($profesores)): ?>
        <div class="alert alert-warning" role="alert">
            No hay profesores activos para asignar el curso. Activa o registra un profesor antes de continuar.
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="<?= BASE_URL ?>/index.php?c=curso&a=crear" method="POST">
                <?php if ($esAdmin): ?>
                    <div class="mb-3">
                        <label for="id_profesor" class="form-label fw-semibold">Profesor responsable *</label>
                        <select name="id_profesor" id="id_profesor" class="form-select" required <?= empty($profesores) ? 'disabled' : '' ?>>
                            <option value="">Selecciona un profesor</option>
                            <?php foreach ($profesores as $profesor): ?>
                                <option value="<?= $profesor->getIdUsuario() ?>" <?= (int)($_POST['id_profesor'] ?? 0) === (int)$profesor->getIdUsuario() ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($profesor->getNombre()) ?> — <?= htmlspecialchars($profesor->getCorreo()) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="titulo" class="form-label fw-semibold">Nombre del curso *</label>
                    <input type="text" name="titulo" id="titulo" class="form-control" maxlength="150" value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>" required>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="4" placeholder="Describe qué aprenderán los estudiantes."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label for="nivel" class="form-label fw-semibold">Nivel *</label>
                    <select name="nivel" id="nivel" class="form-select" required>
                        <option value="">Selecciona el nivel</option>
                        <?php foreach (['Básico 1', 'Básico 2', 'Intermedio', 'Avanzado'] as $nivel): ?>
                            <option value="<?= htmlspecialchars($nivel) ?>" <?= ($_POST['nivel'] ?? '') === $nivel ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nivel) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!$esAdmin): ?>
                    <div class="alert alert-info">
                        El curso quedará en revisión. Cuando sea aprobado por administración, aparecerá en el catálogo.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success">
                        Los cursos creados por administración se publican de inmediato.
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary-custom" <?= $esAdmin && empty($profesores) ? 'disabled' : '' ?>>
                        Crear curso
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
