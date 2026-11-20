<?php

$tituloPagina = "HablaSinVoz - Gestión de Usuarios";
$solicitudesPendientes = array_filter($solicitudes, function ($solicitud) {
    return $solicitud['estado'] === 'Pendiente';
});
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="content-title mb-1">Gestión de Usuarios y Roles</h1>
            <p class="text-muted mb-0">Control de acceso y asignación de permisos (Estudiante, Profesor, Administrador)</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=crear" class="btn btn-primary-custom me-2">
                <i class="bi bi-plus-circle me-1"></i> Crear curso
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones" class="btn btn-outline-primary">
                <i class="bi bi-clipboard-check me-1"></i> Ver revisiones de contenido
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=cursos" class="btn btn-outline-primary">
                <i class="bi bi-journal-text me-1"></i> Gestionar cursos
            </a>
        </div>
    </div>

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

    <!-- Solicitudes de Nuevos Profesores -->
    <?php if (!empty($solicitudesPendientes)): ?>
        <div class="card border border-warning mb-4">
            <div class="card-header bg-warning bg-opacity-25 fw-bold">
                <i class="bi bi-person-plus-fill me-2"></i> Solicitudes para ser profesor
                <span class="badge bg-warning text-dark ms-2"><?= count($solicitudesPendientes) ?> pendientes</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Postulante</th>
                                <th>Correo</th>
                                <th>Experiencia declarada en LSB</th>
                                <th>Documento</th>
                                <th>Estado</th>
                                <th class="text-end">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($solicitudesPendientes as $s): ?>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($s['nombre']) ?></td>
                                    <td><?= htmlspecialchars($s['correo']) ?></td>
                                    <td><small><?= htmlspecialchars($s['experiencia']) ?></small></td>
                                    <td>
                                        <?php if (!empty($s['documento'])): ?>
                                            <a href="<?= BASE_URL ?>/index.php?c=admin&a=verDocumentoSolicitud&id=<?= (int)$s['id_solicitud'] ?>" class="btn btn-sm btn-outline-primary">
                                                Descargar respaldo
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Sin documento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= ($s['estado'] === 'Aprobada') ? 'success' : (($s['estado'] === 'Rechazada') ? 'danger' : 'warning text-dark') ?>">
                                            <?= htmlspecialchars($s['estado']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($s['estado'] === 'Pendiente'): ?>
                                            <?php if (!empty($s['documento'])): ?>
                                                <form action="<?= BASE_URL ?>/index.php?c=admin&a=resolverSolicitud" method="POST" class="d-inline">
                                                    <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">
                                                    <input type="hidden" name="estado" value="Aprobada">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        Validar y aprobar
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form action="<?= BASE_URL ?>/index.php?c=admin&a=resolverSolicitud" method="POST" class="d-inline">
                                                <input type="hidden" name="id_solicitud" value="<?= $s['id_solicitud'] ?>">
                                                <input type="hidden" name="estado" value="Rechazada">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Rechazar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tabla Principal de Usuarios -->
    <div class="card border">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <span class="fw-bold">Listado de usuarios registrados</span>
            <span class="badge bg-secondary"><?= count($usuarios) ?> usuarios</span>
        </div>
        <div class="card-body py-2 border-bottom">
            <small class="text-muted">Las cuentas con cursos o actividad asociada no se eliminan para conservar sus datos; puedes suspenderlas en su lugar.</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Correo Electrónico</th>
                            <th>Rol Actual</th>
                            <th>Estado</th>
                            <th class="text-end">Cambiar Rol</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>#<?= $u->getIdUsuario() ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($u->getNombre()) ?></td>
                                <td><?= htmlspecialchars($u->getCorreo()) ?></td>
                                <td>
                                    <?php if ($u->esAdmin()): ?>
                                        <span class="badge bg-danger">Administrador</span>
                                    <?php elseif ($u->esProfesor()): ?>
                                        <span class="badge bg-primary">Profesor</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Estudiante</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $u->getEstado() === 'Activo' ? 'success' : 'secondary' ?>">
                                        <?= htmlspecialchars($u->getEstado()) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <form action="<?= BASE_URL ?>/index.php?c=admin&a=usuarios" method="POST" class="d-inline-flex justify-content-end align-items-center gap-2">
                                        <input type="hidden" name="accion" value="actualizar_rol">
                                        <input type="hidden" name="id_usuario" value="<?= $u->getIdUsuario() ?>">
                                        <select name="id_rol" class="form-select form-select-sm" style="width: 140px;" <?= (int)$u->getIdUsuario() === (int)$_SESSION['usuario_id'] ? 'disabled' : '' ?>>
                                            <option value="1" <?= $u->getIdRol() == 1 ? 'selected' : '' ?>>Estudiante</option>
                                            <option value="2" <?= $u->getIdRol() == 2 ? 'selected' : '' ?>>Profesor</option>
                                            <option value="3" <?= $u->getIdRol() == 3 ? 'selected' : '' ?>>Administrador</option>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-dark" <?= (int)$u->getIdUsuario() === (int)$_SESSION['usuario_id'] ? 'disabled' : '' ?>>
                                            Actualizar
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php $esUsuarioActual = (int)$u->getIdUsuario() === (int)$_SESSION['usuario_id']; ?>
                                    <form action="<?= BASE_URL ?>/index.php?c=admin&a=usuarios" method="POST" class="d-inline">
                                        <input type="hidden" name="id_usuario" value="<?= $u->getIdUsuario() ?>">
                                        <input type="hidden" name="accion" value="<?= $u->getEstado() === 'Activo' ? 'suspender' : 'activar' ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-<?= $u->getEstado() === 'Activo' ? 'warning' : 'success' ?>" <?= $esUsuarioActual ? 'disabled' : '' ?>>
                                            <?= $u->getEstado() === 'Activo' ? 'Suspender' : 'Reactivar' ?>
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/index.php?c=admin&a=usuarios" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar permanentemente este usuario y sus solicitudes de profesor? Esta acción no se puede deshacer.')">
                                        <input type="hidden" name="id_usuario" value="<?= $u->getIdUsuario() ?>">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" <?= $esUsuarioActual ? 'disabled' : '' ?>>
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
