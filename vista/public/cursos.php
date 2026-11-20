<?php

$tituloPagina = "HablaSinVoz - Catálogo de Cursos";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-5">
    <?php if (($_GET['msg'] ?? '') === 'recurso_no_disponible'): ?>
        <div class="alert alert-info" role="alert">El curso que intentaste abrir ya no está disponible.</div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title mb-1">Catálogo de Cursos de LSB</h1>
            <p class="text-muted">Aprende Lengua de Señas Boliviana paso a paso por niveles</p>
        </div>
        <?php if (($rol ?? 0) === 3): ?>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones" class="btn btn-primary-custom">
                Ver archivos por validar
            </a>
        <?php elseif (($rol ?? 0) === 2): ?>
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos" class="btn btn-primary-custom">
                Ir a mi panel de profesor
            </a>
        <?php elseif (!empty($autenticado)): ?>
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=misCursos" class="btn btn-primary-custom">
                Ir a mis cursos
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php?c=auth&a=login" class="btn btn-primary-custom">
                Iniciar sesión
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($cursosRestringidos)): ?>
        <div class="alert alert-info" role="status">
            Se muestran los primeros tres cursos. Inicia sesión o crea una cuenta para acceder a los otros <?= (int)$cursosRestringidos ?>.
            <a href="<?= BASE_URL ?>/index.php?c=auth&a=registro" class="alert-link">Crear cuenta</a>
            o <a href="<?= BASE_URL ?>/index.php?c=auth&a=login" class="alert-link">iniciar sesión</a>.
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (empty($cursos)): ?>
            <div class="col-12">
                <div class="alert alert-info mb-0">Todavía no hay cursos publicados. Vuelve pronto para explorar el catálogo.</div>
            </div>
        <?php else: ?>
        <?php foreach ($cursos as $c): ?>
            <div class="col-md-4">
                <div class="course-card-custom">
                    <div class="course-image-box">
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($c->getImagen()) ?>" alt="<?= htmlspecialchars($c->getTitulo()) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/cursos/curso_1.svg'">
                    </div>
                    <span class="badge bg-primary mb-2 align-self-start"><?= htmlspecialchars($c->getNivel()) ?></span>
                    <h3 class="course-title-custom"><?= htmlspecialchars($c->getTitulo()) ?></h3>
                    <p class="text-muted small flex-grow-1"><?= htmlspecialchars($c->getDescripcion()) ?></p>
                    <p class="small text-secondary mb-3">
                        <i class="bi bi-person"></i> <?= htmlspecialchars($c->getNombreProfesor()) ?> • <?= $c->getTotalLecciones() ?> lecciones
                    </p>
                    <?php if (($rol ?? 0) === 3): ?>
                        <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones&id_curso=<?= $c->getIdCurso() ?>" class="btn btn-primary-custom text-center w-100">
                            Ver archivos por validar
                        </a>
                    <?php elseif (($rol ?? 0) === 2): ?>
                        <a href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos" class="btn btn-primary-custom text-center w-100">
                            Gestionar mis cursos
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/index.php?c=curso&a=detalle&id=<?= $c->getIdCurso() ?>" class="btn btn-primary-custom text-center w-100">
                            Ver Lecciones
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
