<?php

$tituloPagina = "HablaSinVoz - Inicio";
if (!isset($cursosDestacados)) {
    require_once __DIR__ . '/../../modelo/CursoDao.php';
    $cursoDao = new CursoDao();
    $cursosPublicados = $cursoDao->obtenerPublicados();
    $cursosDestacados = array_slice($cursosPublicados, 0, 3);
    $hayMasCursos = count($cursosPublicados) > 3;
}
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<!-- Sección Hero (Imagen 1) -->
<section class="hero-section">
    <div class="container">
        <div class="hero-eyebrow"><i class="bi bi-hand-index-thumb"></i> Aprender nos conecta</div>
        <h1 class="hero-title">Aprende lengua de señas a tu ritmo</h1>
        <p class="hero-subtitle">Cursos y lecciones en video en una sola plataforma</p>
        <?php if (($rol ?? 0) === 3): ?>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=usuarios" class="btn btn-primary-custom">
                Ir al panel de administración
            </a>
        <?php elseif (($rol ?? 0) === 2): ?>
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos" class="btn btn-primary-custom">
                Ir al panel de profesor
            </a>
        <?php elseif (isset($_SESSION['usuario_id'])): ?>
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=misCursos" class="btn btn-primary-custom">
                Ir a mis cursos
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php?c=auth&a=registro" class="btn btn-primary-custom">
                Comenzar ahora
            </a>
        <?php endif; ?>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">Acceso público</span>
            <h2>Conoce nuestros primeros cursos</h2>
            <p>Explora los primeros cursos y revisa sus lecciones antes de crear una cuenta.</p>
        </div>
        <div class="row g-4">
            <?php if (empty($cursosDestacados)): ?>
                <div class="col-12">
                    <div class="alert alert-info mb-0">Todavía no hay cursos publicados. Vuelve pronto para explorar el catálogo.</div>
                </div>
            <?php else: ?>
                <?php foreach ($cursosDestacados as $curso): ?>
                    <div class="col-md-4">
                        <div class="course-card-custom">
                            <div class="course-image-box">
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($curso->getImagen()) ?>" alt="<?= htmlspecialchars($curso->getTitulo()) ?>" onerror="this.src='<?= BASE_URL ?>/assets/img/cursos/curso_1.svg'">
                            </div>
                            <span class="badge bg-primary mb-2 align-self-start"><?= htmlspecialchars($curso->getNivel()) ?></span>
                            <h3 class="course-title-custom"><?= htmlspecialchars($curso->getTitulo()) ?></h3>
                            <p class="text-muted small flex-grow-1"><?= htmlspecialchars($curso->getDescripcion()) ?></p>
                            <p class="small text-secondary mb-3">
                                <i class="bi bi-person"></i> <?= htmlspecialchars($curso->getNombreProfesor()) ?> • <?= (int)$curso->getTotalLecciones() ?> lecciones
                            </p>
                            <a href="<?= BASE_URL ?>/index.php?c=curso&a=detalle&id=<?= (int)$curso->getIdCurso() ?>" class="btn btn-primary-custom text-center w-100">
                                Ver curso
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if (!empty($hayMasCursos)): ?>
            <div class="text-center mt-4">
                <?php if (!empty($autenticado)): ?>
                    <p class="text-muted">Hay más cursos disponibles en el catálogo.</p>
                    <a href="<?= BASE_URL ?>/index.php?c=curso&a=catalogo" class="btn btn-primary-custom">Ver todos los cursos</a>
                <?php else: ?>
                    <p class="text-muted">Inicia sesión o crea una cuenta para acceder a los demás cursos.</p>
                    <a href="<?= BASE_URL ?>/index.php?c=auth&a=login" class="btn btn-outline-primary me-2">Iniciar sesión</a>
                    <a href="<?= BASE_URL ?>/index.php?c=auth&a=registro" class="btn btn-primary-custom">Crear cuenta</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="pb-5">
    <div class="container text-center">
        <a href="<?= BASE_URL ?>/index.php?c=leccion&a=ver&id=1" class="btn btn-outline-primary">
            <i class="bi bi-play-circle me-1"></i> Lecciones en video
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
