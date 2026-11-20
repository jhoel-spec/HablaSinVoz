<?php

$tituloPagina = "HablaSinVoz - Gestión de Cursos";
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../layout/navbar.php';
?>

<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="content-title mb-1">Gestión de cursos</h1>
            <p class="text-muted mb-0">Administra docentes, contenido y actividad de estudiantes por curso.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="<?= BASE_URL ?>/index.php?c=curso&a=crear" class="btn btn-primary-custom">
                <i class="bi bi-plus-circle me-1"></i> Crear curso
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=aprobaciones" class="btn btn-outline-primary">
                <i class="bi bi-clipboard-check me-1"></i> Revisiones
            </a>
            <a href="<?= BASE_URL ?>/index.php?c=admin&a=usuarios" class="btn btn-outline-secondary">
                <i class="bi bi-people me-1"></i> Usuarios
            </a>
        </div>
    </div>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card border mb-4">
        <div class="card-header bg-light fw-bold">Cursos registrados</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Curso</th>
                        <th>Nivel</th>
                        <th>Profesor(es)</th>
                        <th>Lecciones</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cursos)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No hay cursos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($cursos as $curso): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($curso->getTitulo()) ?></td>
                                <td><?= htmlspecialchars($curso->getNivel()) ?></td>
                                <td><?= htmlspecialchars($curso->getNombreProfesor()) ?></td>
                                <td><?= (int)$curso->getTotalLecciones() ?> lecciones</td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($curso->getEstado()) ?></span></td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL ?>/index.php?c=curso&a=verContenido&id_curso=<?= (int)$curso->getIdCurso() ?>">
                                        Ver contenido
                                    </a>
                                    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/index.php?c=admin&a=cursos&id_curso=<?= (int)$curso->getIdCurso() ?>">
                                        Gestionar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($cursoSeleccionado): ?>
        <div class="card border mb-4">
            <div class="card-header bg-light">
                <h2 class="h5 mb-0">Editar curso: <?= htmlspecialchars($cursoSeleccionado->getTitulo()) ?></h2>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>/index.php?c=admin&a=guardarCurso" method="POST">
                    <input type="hidden" name="id_curso" value="<?= (int)$cursoSeleccionado->getIdCurso() ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="titulo">Título</label>
                            <input class="form-control" id="titulo" name="titulo" maxlength="150" required value="<?= htmlspecialchars($cursoSeleccionado->getTitulo()) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="nivel">Nivel</label>
                            <select class="form-select" id="nivel" name="nivel" required>
                                <?php foreach (['Básico 1', 'Básico 2', 'Intermedio', 'Avanzado'] as $nivel): ?>
                                    <option value="<?= htmlspecialchars($nivel) ?>" <?= $cursoSeleccionado->getNivel() === $nivel ? 'selected' : '' ?>><?= htmlspecialchars($nivel) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="estado">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <?php foreach (['Borrador', 'Revisión', 'Publicado'] as $estado): ?>
                                    <option value="<?= htmlspecialchars($estado) ?>" <?= $cursoSeleccionado->getEstado() === $estado ? 'selected' : '' ?>><?= htmlspecialchars($estado) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($cursoSeleccionado->getDescripcion()) ?></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary-custom">Guardar cambios del curso</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-6">
                <div class="card border h-100">
                    <div class="card-header bg-light fw-bold">Profesores asignados</div>
                    <div class="card-body">
                        <form action="<?= BASE_URL ?>/index.php?c=admin&a=asignarProfesoresCurso" method="POST">
                            <input type="hidden" name="id_curso" value="<?= (int)$cursoSeleccionado->getIdCurso() ?>">
                            <?php if (empty($profesoresActivos)): ?>
                                <p class="text-muted mb-0">No hay profesores activos para asignar.</p>
                            <?php else: ?>
                                <div class="row g-2">
                                    <?php foreach ($profesoresActivos as $profesor): ?>
                                        <?php
                                        $asignado = false;
                                        foreach ($profesoresCurso as $actual) {
                                            if ((int)$actual['id_usuario'] === (int)$profesor->getIdUsuario()) {
                                                $asignado = true;
                                                break;
                                            }
                                        }
                                        ?>
                                        <div class="col-md-6">
                                            <label class="form-check border rounded p-2 ps-5">
                                                <input class="form-check-input" type="checkbox" name="id_profesores[]" value="<?= (int)$profesor->getIdUsuario() ?>" <?= $asignado ? 'checked' : '' ?>>
                                                <span class="form-check-label"><?= htmlspecialchars($profesor->getNombre()) ?></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button type="submit" class="btn btn-outline-primary mt-3">Guardar profesores</button>
                            <?php endif; ?>
                        </form>
                        <?php
                        $profesoresInactivos = array_filter($profesoresCurso, function ($profesor) {
                            return ($profesor['estado'] ?? '') !== 'Activo';
                        });
                        ?>
                        <?php if (!empty($profesoresInactivos)): ?>
                            <div class="alert alert-warning small mt-3 mb-0">
                                Hay profesores asignados que están suspendidos. Al guardar la asignación, solo permanecerán los docentes activos seleccionados.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card border h-100">
                    <div class="card-header bg-light fw-bold">Estudiantes inscritos o con actividad</div>
                    <div class="card-body p-0">
                        <?php if (empty($estudiantesCurso)): ?>
                            <p class="text-muted p-3 mb-0">Todavía no hay estudiantes inscritos. Se registran al abrir un curso publicado.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Estudiante</th>
                                            <th>Correo</th>
                                            <th>Estado</th>
                                            <th>Inscripción</th>
                                            <th>Lecciones completadas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($estudiantesCurso as $estudiante): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($estudiante['nombre']) ?></td>
                                                <td><?= htmlspecialchars($estudiante['correo']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= ($estudiante['estado_usuario'] ?? '') === 'Activo' ? 'success' : 'secondary' ?>">
                                                        <?= htmlspecialchars($estudiante['estado_usuario'] ?? 'Desconocido') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($estudiante['inscrito'])): ?>
                                                        <?= !empty($estudiante['fecha_inscripcion']) ? htmlspecialchars(date('d/m/Y', strtotime($estudiante['fecha_inscripcion']))) : 'Inscrito' ?>
                                                    <?php else: ?>
                                                        Actividad registrada
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= (int)$estudiante['lecciones_completadas'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <span class="fw-bold">Materiales y lecciones del curso</span>
                <span class="badge bg-secondary"><?= count($leccionesCurso) ?> lecciones</span>
            </div>
            <div class="card-body">
                <?php if (empty($leccionesCurso)): ?>
                    <p class="text-muted mb-0">Este curso todavía no tiene lecciones.</p>
                <?php else: ?>
                    <div class="accordion" id="acordeonLecciones">
                        <?php foreach ($leccionesCurso as $indice => $leccion): ?>
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="encabezado-<?= (int)$leccion->getIdLeccion() ?>">
                                    <button class="accordion-button <?= $indice > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#leccion-<?= (int)$leccion->getIdLeccion() ?>">
                                        #<?= (int)$leccion->getOrden() ?> — <?= htmlspecialchars($leccion->getTitulo()) ?>
                                        <span class="badge bg-secondary ms-2"><?= htmlspecialchars($leccion->getEstado()) ?></span>
                                    </button>
                                </h3>
                                <div id="leccion-<?= (int)$leccion->getIdLeccion() ?>" class="accordion-collapse collapse <?= $indice === 0 ? 'show' : '' ?>" data-bs-parent="#acordeonLecciones">
                                    <div class="accordion-body">
                                        <form action="<?= BASE_URL ?>/index.php?c=admin&a=guardarLeccion" method="POST" class="border rounded p-3 mb-3">
                                            <input type="hidden" name="id_curso" value="<?= (int)$cursoSeleccionado->getIdCurso() ?>">
                                            <input type="hidden" name="id_leccion" value="<?= (int)$leccion->getIdLeccion() ?>">
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Título</label>
                                                    <input class="form-control" name="titulo" maxlength="150" required value="<?= htmlspecialchars($leccion->getTitulo()) ?>">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Orden</label>
                                                    <input class="form-control" name="orden" type="number" min="1" required value="<?= (int)$leccion->getOrden() ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Estado</label>
                                                    <select class="form-select" name="estado">
                                                        <?php foreach (['Pendiente', 'Publicada', 'Observada'] as $estado): ?>
                                                            <option value="<?= htmlspecialchars($estado) ?>" <?= $leccion->getEstado() === $estado ? 'selected' : '' ?>><?= htmlspecialchars($estado) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Descripción</label>
                                                    <textarea class="form-control" name="descripcion" rows="2"><?= htmlspecialchars($leccion->getDescripcion()) ?></textarea>
                                                </div>
                                                <div class="col-12 text-end">
                                                    <button type="submit" class="btn btn-sm btn-primary-custom">Guardar lección</button>
                                                </div>
                                            </div>
                                        </form>

                                        <h4 class="h6">Material multimedia</h4>
                                        <?php $materiales = $leccion->getMultimedia() ?? []; ?>
                                        <?php if (empty($materiales)): ?>
                                            <p class="text-muted small">Esta lección no tiene archivos ni enlaces adjuntos.</p>
                                        <?php else: ?>
                                            <ul class="list-group mb-3">
                                                <?php foreach ($materiales as $material): ?>
                                                    <?php
                                                    $rutaMaterial = $material->getRutaEnlace();
                                                    $etiquetaMaterial = parse_url($rutaMaterial, PHP_URL_HOST)
                                                        ?: basename(parse_url($rutaMaterial, PHP_URL_PATH) ?: $rutaMaterial);
                                                    $urlMaterial = preg_match('/\Ahttps?:\/\//i', $rutaMaterial)
                                                        ? $rutaMaterial
                                                        : BASE_URL . '/' . ltrim($rutaMaterial, '/');
                                                    ?>
                                                    <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                                                        <span><span class="badge bg-info text-dark me-2"><?= htmlspecialchars($material->getTipo()) ?></span><?= htmlspecialchars($etiquetaMaterial) ?></span>
                                                        <div class="d-flex gap-2">
                                                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($urlMaterial) ?>" target="_blank" rel="noopener noreferrer">Abrir</a>
                                                            <form action="<?= BASE_URL ?>/index.php?c=admin&a=eliminarMaterialCurso" method="POST" onsubmit="return confirm('¿Eliminar este archivo o enlace?')">
                                                                <input type="hidden" name="id_curso" value="<?= (int)$cursoSeleccionado->getIdCurso() ?>">
                                                                <input type="hidden" name="id_multimedia" value="<?= (int)$material->getIdMultimedia() ?>">
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                                            </form>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <form action="<?= BASE_URL ?>/index.php?c=admin&a=eliminarLeccion" method="POST" class="text-end" onsubmit="return confirm('Se eliminarán la lección, sus materiales, avances, reportes y evaluaciones relacionadas. ¿Continuar?')">
                                            <input type="hidden" name="id_curso" value="<?= (int)$cursoSeleccionado->getIdCurso() ?>">
                                            <input type="hidden" name="id_leccion" value="<?= (int)$leccion->getIdLeccion() ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Eliminar lección y contenido</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border border-danger mb-4">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="h5 text-danger mb-1">Eliminar curso</h2>
                    <p class="text-muted mb-0">Se borrarán también sus lecciones, materiales, avances, reportes y evaluaciones.</p>
                </div>
                <form action="<?= BASE_URL ?>/index.php?c=admin&a=eliminarCurso" method="POST" onsubmit="return confirm('Esta acción es permanente y eliminará también todo el contenido y actividad de estudiantes de este curso. ¿Continuar?')">
                    <input type="hidden" name="id_curso" value="<?= (int)$cursoSeleccionado->getIdCurso() ?>">
                    <button type="submit" class="btn btn-danger">Eliminar curso permanentemente</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
