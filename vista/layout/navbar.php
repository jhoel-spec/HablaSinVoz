<?php

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}
$autenticado = isset($_SESSION['usuario_id']);
$rol = (int)($_SESSION['usuario_rol'] ?? 0);

$urlInicio = BASE_URL . '/index.php';
if ($autenticado) {
    if ($rol === 1) {
        $urlInicio = BASE_URL . '/index.php?c=curso&a=misCursos';
    } elseif ($rol === 2) {
        $urlInicio = BASE_URL . '/index.php?c=curso&a=profesorCursos';
    } elseif ($rol === 3) {
        $urlInicio = BASE_URL . '/index.php?c=admin&a=usuarios';
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container-fluid">
        <a class="navbar-brand navbar-brand-custom" href="<?= $urlInicio ?>">
            <span class="brand-mark"><i class="bi bi-hand-index-thumb-fill"></i></span>
            <span>HablaSinVoz<small>Lengua de Señas Boliviana</small></span>
        </a>
        <button class="navbar-toggler text-white border-white" type="button" data-bs-toggle="collapse" data-bs-target="#navContent">
            <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navContent">
            <div class="navbar-nav align-items-center">
                <?php if (!$autenticado): ?>
                    <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php">Inicio</a>
                <?php endif; ?>
                <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=curso&a=catalogo">Cursos</a>

                <?php if (!$autenticado): ?>
                    <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=auth&a=login">Iniciar sesión</a>
                    <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=auth&a=registro">Registrarse</a>
                <?php else: ?>
                    <?php if ($rol === 1): ?>
                        <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=curso&a=misCursos">Mis cursos</a>
                    <?php elseif ($rol === 2): ?>
                        <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=curso&a=profesorCursos">Panel Profesor</a>
                    <?php elseif ($rol === 3): ?>
                        <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones">Archivos por validar</a>
                        <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=admin&a=cursos">Gestionar cursos</a>
                        <a class="nav-link nav-link-custom" href="<?= BASE_URL ?>/index.php?c=admin&a=usuarios">Panel Admin</a>
                    <?php endif; ?>
                    
                    <a class="nav-link nav-link-custom text-white" href="<?= BASE_URL ?>/index.php?c=auth&a=perfil">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Mi Perfil') ?>
                    </a>
                    <a class="nav-link nav-link-custom text-danger" href="<?= BASE_URL ?>/index.php?c=auth&a=logout">Cerrar sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
