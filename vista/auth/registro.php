<?php

$tituloPagina = "HablaSinVoz - Registrarse";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="auth-card">
        <h2 class="auth-title">Crear cuenta</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 px-3 small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/index.php?c=auth&a=registro" method="POST">
            <div class="mb-3">
                <label for="nombre" class="form-label-custom">Nombre completo</label>
                <input type="text" class="form-input-custom" id="nombre" name="nombre" placeholder="Tu nombre y apellido" value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label-custom">Correo electrónico</label>
                <input type="email" class="form-input-custom" id="correo" name="correo" placeholder="correo@ejemplo.com" value="<?= htmlspecialchars($_POST['correo'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label-custom">Contraseña</label>
                <input type="password" class="form-input-custom" id="password" name="password" placeholder="Mínimo 6 caracteres" required>
            </div>

            <button type="submit" class="btn btn-primary-custom btn-auth-submit">
                Registrarse
            </button>

            <a href="<?= BASE_URL ?>/index.php?c=auth&a=login" class="auth-footer-link">
                ¿Ya tienes una cuenta? Inicia sesión
            </a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
