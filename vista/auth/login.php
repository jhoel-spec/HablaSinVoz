<?php

$tituloPagina = "HablaSinVoz - Iniciar sesión";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="auth-card">
        <h2 class="auth-title">Iniciar sesión</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?c=auth&a=login" method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label-custom">Correo electrónico</label>
                <input type="email" class="form-input-custom" id="correo" name="correo" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label-custom">Contraseña</label>
                <input type="password" class="form-input-custom" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary-custom btn-auth-submit">
                Ingresar
            </button>

            <a href="<?= BASE_URL ?>/index.php?c=auth&a=registro" class="auth-footer-link">
                ¿No tienes cuenta? Regístrate
            </a>
        </form>

    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
