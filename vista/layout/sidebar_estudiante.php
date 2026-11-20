<?php

$seccion = $paginaActiva ?? 'cursos';
?>
<aside class="dashboard-sidebar">
    <div class="sidebar-heading">ESTUDIANTE</div>
    <nav>
        <a href="<?= BASE_URL ?>/index.php?c=curso&a=misCursos" class="sidebar-btn <?= ($seccion === 'cursos' || $seccion === 'evaluacion') ? 'active' : '' ?>">
            Mis cursos
        </a>
        <a href="<?= BASE_URL ?>/index.php?c=curso&a=miProgreso" class="sidebar-btn <?= ($seccion === 'progreso') ? 'active' : '' ?>">
            Mi progreso
        </a>
        <a href="<?= BASE_URL ?>/index.php?c=auth&a=perfil" class="sidebar-btn <?= ($seccion === 'perfil') ? 'active' : '' ?>">
            Mi perfil
        </a>
        <a href="<?= BASE_URL ?>/index.php?c=auth&a=logout" class="sidebar-btn text-danger">
            Cerrar sesión
        </a>
    </nav>
</aside>
