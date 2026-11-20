<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/PrivateStorage.php';
require_once __DIR__ . '/Usuario.php';

class UsuarioDao {
    private $db;
    private $lastError = null;
    private static $estadoColumnReady = false;
    private static $documentoSolicitudColumnReady = false;

    public function __construct() {
        $this->db = Database::getConnection();
        if (!$this->db) {
            $this->lastError = Database::getLastError();
        } else {
            if (!self::$estadoColumnReady) {
                try {
                    $stmt = $this->db->query("SHOW COLUMNS FROM usuarios LIKE 'estado'");
                    if (!$stmt->fetch()) {
                        $this->db->exec("ALTER TABLE usuarios ADD COLUMN estado ENUM('Activo','Suspendido') NOT NULL DEFAULT 'Activo'");
                    }
                    self::$estadoColumnReady = true;
                } catch (PDOException $e) {
                    $this->lastError = $e->getMessage();
                    error_log("Error UsuarioDao::migrarEstado: " . $e->getMessage());
                }
            }
            if (!self::$documentoSolicitudColumnReady) {
                try {
                    $stmt = $this->db->query("SHOW COLUMNS FROM solicitudes_profesor LIKE 'documento'");
                    if (!$stmt->fetch()) {
                        $this->db->exec("ALTER TABLE solicitudes_profesor ADD COLUMN documento VARCHAR(255) DEFAULT NULL");
                    }
                    self::$documentoSolicitudColumnReady = true;
                } catch (PDOException $e) {
                    $this->lastError = $e->getMessage();
                    error_log("Error UsuarioDao::migrarDocumentoSolicitud: " . $e->getMessage());
                }
            }
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function listarProfesoresActivos() {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->query(
                "SELECT u.*, r.nombre_rol
                 FROM usuarios u
                 JOIN roles r ON r.id_rol = u.id_rol
                 WHERE u.id_rol = 2 AND u.estado = 'Activo'
                 ORDER BY u.nombre ASC"
            );
            $profesores = [];
            while ($row = $stmt->fetch()) {
                $profesores[] = new Usuario(
                    $row['id_usuario'],
                    $row['nombre'],
                    $row['correo'],
                    '',
                    $row['id_rol'],
                    $row['nombre_rol'],
                    $row['estado']
                );
            }
            return $profesores;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::listarProfesoresActivos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca un usuario por su correo electrónico
     */
    public function buscarPorCorreo($correo) {
        if ($this->db) {
            try {
                $sql = "SELECT u.*, r.nombre_rol 
                        FROM usuarios u 
                        JOIN roles r ON u.id_rol = r.id_rol 
                        WHERE u.correo = :correo LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':correo' => $correo]);
                $row = $stmt->fetch();
                if ($row) {
                    return new Usuario(
                        $row['id_usuario'],
                        $row['nombre'],
                        $row['correo'],
                        $row['password_hash'],
                        $row['id_rol'],
                        $row['nombre_rol'],
                        $row['estado'] ?? 'Activo'
                    );
                }
                return null;
            } catch (PDOException $e) {
                $this->lastError = $e->getMessage();
                error_log("Error UsuarioDao::buscarPorCorreo: " . $e->getMessage());
                return null;
            }
        }

        // Usuarios de demostración si MySQL aún no está disponible.
        $demoUsers = [
            'admin@hablasinvoz.bo' => [1, 'Administrador del Sistema', '$2y$10$ngpYu1nmNP2tPGZV.HTUROK3o6mmBBdJ2sw.re4hM3PwPLuerSHlu', 3, 'Administrador'],
            'profesor@hablasinvoz.bo' => [2, 'Prof. Carlos Mendoza', '$2y$10$s3pjU.HybMB9mynxWOWb1uqY/PIYLMahbFtD4R6ST3/jl5LajRA66', 2, 'Profesor'],
            'correo@ejemplo.com' => [3, 'Juan Pérez', '$2y$10$NiFb3aSOPBTKkuFE2rfPeOrC7GssIBWjkHeYZpCymTdchnWkUGzP2', 1, 'Estudiante']
        ];

        if (isset($demoUsers[$correo])) {
            $d = $demoUsers[$correo];
            return new Usuario($d[0], $d[1], $correo, $d[2], $d[3], $d[4]);
        }

        return null;
    }

    /**
     * Busca un usuario por ID
     */
    public function buscarPorId($id) {
        if ($this->db) {
            try {
                $sql = "SELECT u.*, r.nombre_rol 
                        FROM usuarios u 
                        JOIN roles r ON u.id_rol = r.id_rol 
                        WHERE u.id_usuario = :id LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id' => $id]);
                $row = $stmt->fetch();
                if ($row) {
                    return new Usuario(
                        $row['id_usuario'],
                        $row['nombre'],
                        $row['correo'],
                        $row['password_hash'],
                        $row['id_rol'],
                        $row['nombre_rol'],
                        $row['estado'] ?? 'Activo'
                    );
                }
                return null;
            } catch (PDOException $e) {
                error_log("Error UsuarioDao::buscarPorId: " . $e->getMessage());
                return null;
            }
        }

        if ($id == 1) return new Usuario(1, 'Administrador del Sistema', 'admin@hablasinvoz.bo', '', 3, 'Administrador');
        if ($id == 2) return new Usuario(2, 'Prof. Carlos Mendoza', 'profesor@hablasinvoz.bo', '', 2, 'Profesor');
        if ($id == 3) return new Usuario(3, 'Juan Pérez', 'correo@ejemplo.com', '', 1, 'Estudiante');

        return null;
    }

    /**
     * Registra un nuevo usuario en la base de datos
     */
    public function registrar(Usuario $usuario) {
        if ($this->db) {
            try {
                // Forzar el ID de rol: si viene vacío o menor a 1, asignar 1 (Estudiante)
                $id_rol = (int)$usuario->getIdRol();
                if ($id_rol <= 0) {
                    $id_rol = 1;
                }

                // Verificar que el rol exista en la tabla roles para prevenir error 1452
                $stmtCheck = $this->db->prepare("SELECT id_rol FROM roles WHERE id_rol = ?");
                $stmtCheck->execute([$id_rol]);
                if (!$stmtCheck->fetch()) {
                    $this->db->exec("INSERT INTO roles (id_rol, nombre_rol) VALUES 
                        (1, 'Estudiante'), 
                        (2, 'Profesor'), 
                        (3, 'Administrador')
                        ON DUPLICATE KEY UPDATE nombre_rol = VALUES(nombre_rol)");
                }

                $sql = "INSERT INTO usuarios (nombre, correo, password_hash, id_rol) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $usuario->getNombre(),
                    $usuario->getCorreo(),
                    $usuario->getPasswordHash(),
                    $id_rol
                ]);
                return $this->db->lastInsertId();
            } catch (PDOException $e) {
                $this->lastError = $e->getMessage();
                error_log("Error UsuarioDao::registrar: " . $e->getMessage());
                return false;
            }
        }
        return false;
    }

    /**
     * Lista todos los usuarios para el panel de administración
     */
    public function listarTodos() {
        $usuarios = [];
        if ($this->db) {
            try {
                $sql = "SELECT u.*, r.nombre_rol 
                        FROM usuarios u 
                        JOIN roles r ON u.id_rol = r.id_rol 
                        ORDER BY u.id_usuario ASC";
                $stmt = $this->db->query($sql);
                while ($row = $stmt->fetch()) {
                    $usuarios[] = new Usuario(
                        $row['id_usuario'],
                        $row['nombre'],
                        $row['correo'],
                        $row['password_hash'],
                        $row['id_rol'],
                        $row['nombre_rol'],
                        $row['estado'] ?? 'Activo'
                    );
                }
                return $usuarios;
            } catch (PDOException $e) {
                $this->lastError = $e->getMessage();
                error_log("Error UsuarioDao::listarTodos: " . $e->getMessage());
                return [];
            }
        }

        // Demo fallback
        $usuarios[] = new Usuario(1, 'Administrador del Sistema', 'admin@hablasinvoz.bo', '', 3, 'Administrador');
        $usuarios[] = new Usuario(2, 'Prof. Carlos Mendoza', 'profesor@hablasinvoz.bo', '', 2, 'Profesor');
        $usuarios[] = new Usuario(3, 'Juan Pérez', 'correo@ejemplo.com', '', 1, 'Estudiante');
        return $usuarios;
    }

    /**
     * Actualiza el rol de un usuario
     */
    public function actualizarRol($id_usuario, $id_rol) {
        if (!$this->db || (int)$id_usuario <= 0 || !in_array((int)$id_rol, [1, 2, 3], true)) {
            return false;
        }
        try {
            $stmt = $this->db->prepare("SELECT id_rol FROM usuarios WHERE id_usuario = :id_usuario");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            $rolActual = $stmt->fetchColumn();
            if ($rolActual === false) {
                return false;
            }
            if ((int)$rolActual === (int)$id_rol) {
                return true;
            }
            if ((int)$rolActual === 2 && (int)$id_rol !== 2) {
                $stmt = $this->db->prepare(
                    "SELECT
                        (SELECT COUNT(*) FROM cursos WHERE id_profesor = :profesor) +
                        (SELECT COUNT(*) FROM curso_profesores WHERE id_profesor = :asignado)"
                );
                $stmt->execute([
                    ':profesor' => (int)$id_usuario,
                    ':asignado' => (int)$id_usuario
                ]);
                if ((int)$stmt->fetchColumn() > 0) {
                    $this->lastError = 'profesor_asignado_a_cursos';
                    return false;
                }
            }

            $stmt = $this->db->prepare("UPDATE usuarios SET id_rol = :id_rol WHERE id_usuario = :id_usuario");
            $stmt->execute([':id_rol' => (int)$id_rol, ':id_usuario' => (int)$id_usuario]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::actualizarRol: " . $e->getMessage());
            return false;
        }
    }

    public function contarAdministradoresActivos() {
        if (!$this->db) {
            return false;
        }
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = 3 AND estado = 'Activo'");
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::contarAdministradoresActivos: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstadoUsuario($id_usuario, $estado) {
        if (!$this->db || (int)$id_usuario <= 0 || !in_array($estado, ['Activo', 'Suspendido'], true)) {
            return false;
        }
        try {
            $stmt = $this->db->prepare("SELECT estado FROM usuarios WHERE id_usuario = :id_usuario");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            $estadoActual = $stmt->fetchColumn();
            if ($estadoActual === false) {
                return false;
            }
            if ($estadoActual === $estado) {
                return true;
            }

            $stmt = $this->db->prepare("UPDATE usuarios SET estado = :estado WHERE id_usuario = :id_usuario");
            $stmt->execute([':estado' => $estado, ':id_usuario' => (int)$id_usuario]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::cambiarEstadoUsuario: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarUsuario($id_usuario) {
        $this->lastError = null;
        if (!$this->db || (int)$id_usuario <= 0) {
            return false;
        }
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = :id_usuario FOR UPDATE");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            if (!$stmt->fetch()) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare(
                "SELECT
                    (SELECT COUNT(*) FROM cursos WHERE id_profesor = :profesor) +
                    (SELECT COUNT(*) FROM curso_profesores WHERE id_profesor = :asignaciones) +
                    (SELECT COUNT(*) FROM curso_estudiantes WHERE id_usuario = :inscripciones) +
                    (SELECT COUNT(*) FROM intentos WHERE id_usuario = :intentos) +
                    (SELECT COUNT(*) FROM progreso WHERE id_usuario = :progreso) +
                    (SELECT COUNT(*) FROM reportes WHERE id_usuario = :reportes)"
            );
            $stmt->execute([
                ':profesor' => (int)$id_usuario,
                ':asignaciones' => (int)$id_usuario,
                ':inscripciones' => (int)$id_usuario,
                ':intentos' => (int)$id_usuario,
                ':progreso' => (int)$id_usuario,
                ':reportes' => (int)$id_usuario
            ]);
            if ((int)$stmt->fetchColumn() > 0) {
                $this->lastError = 'El usuario tiene cursos o actividad asociada.';
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare(
                "SELECT documento
                 FROM solicitudes_profesor
                 WHERE id_usuario = :id_usuario
                 FOR UPDATE"
            );
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            $documentosSolicitud = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $stmt = $this->db->prepare("DELETE FROM solicitudes_profesor WHERE id_usuario = :id_usuario");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);

            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }
            $this->db->commit();

            foreach ($documentosSolicitud as $documento) {
                $rutaDocumento = PrivateStorage::solicitudPath($documento);
                if ($rutaDocumento !== null && is_file($rutaDocumento) && !unlink($rutaDocumento)) {
                    error_log("No se pudo eliminar el documento de solicitud: " . basename($rutaDocumento));
                }
            }

            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::eliminarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Guarda una solicitud para ser profesor
     */
    public function solicitarProfesor($id_usuario, $experiencia, $documento) {
        $this->lastError = null;
        if (
            !$this->db ||
            (int)$id_usuario <= 0 ||
            !preg_match('/\A[a-f0-9]{32}\.(pdf|doc|docx|jpe?g|png|webp)\z/i', $documento)
        ) {
            return false;
        }
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare("SELECT estado, id_rol FROM usuarios WHERE id_usuario = :id_usuario FOR UPDATE");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            $usuario = $stmt->fetch();
            if (!$usuario || $usuario['estado'] !== 'Activo' || (int)$usuario['id_rol'] !== 1) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("SELECT 1 FROM solicitudes_profesor WHERE id_usuario = :id_usuario AND estado = 'Pendiente' LIMIT 1");
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            if ($stmt->fetchColumn()) {
                $this->lastError = 'pending_request';
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare(
                "INSERT INTO solicitudes_profesor (id_usuario, experiencia, estado, documento)
                 VALUES (:id_usuario, :experiencia, 'Pendiente', :documento)"
            );
            $stmt->execute([
                ':id_usuario' => (int)$id_usuario,
                ':experiencia' => $experiencia,
                ':documento' => $documento
            ]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::solicitarProfesor: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene las solicitudes de profesor para el admin
     */
    public function listarSolicitudes() {
        if ($this->db) {
            try {
                $sql = "SELECT s.*, u.nombre, u.correo 
                        FROM solicitudes_profesor s 
                        JOIN usuarios u ON s.id_usuario = u.id_usuario 
                        ORDER BY (s.estado = 'Pendiente') DESC, s.id_solicitud DESC";
                return $this->db->query($sql)->fetchAll();
            } catch (PDOException $e) {
                $this->lastError = $e->getMessage();
                error_log("Error UsuarioDao::listarSolicitudes: " . $e->getMessage());
            }
        }
        return [];
    }

    public function obtenerUltimaSolicitudProfesor($id_usuario) {
        if (!$this->db || (int)$id_usuario <= 0) {
            return null;
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT estado
                 FROM solicitudes_profesor
                 WHERE id_usuario = :id_usuario
                 ORDER BY id_solicitud DESC
                 LIMIT 1"
            );
            $stmt->execute([':id_usuario' => (int)$id_usuario]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::obtenerUltimaSolicitudProfesor: " . $e->getMessage());
            return null;
        }
    }

    public function obtenerDocumentoSolicitud($id_solicitud) {
        if (!$this->db || (int)$id_solicitud <= 0) {
            return null;
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT id_solicitud, documento
                 FROM solicitudes_profesor
                 WHERE id_solicitud = :id_solicitud
                 LIMIT 1"
            );
            $stmt->execute([':id_solicitud' => (int)$id_solicitud]);
            return $stmt->fetch() ?: null;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::obtenerDocumentoSolicitud: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Cambia el estado de una solicitud de profesor
     */
    public function resolverSolicitud($id_solicitud, $estado) {
        $this->lastError = null;
        if (!$this->db || (int)$id_solicitud <= 0 || !in_array($estado, ['Aprobada', 'Rechazada'], true)) {
            return false;
        }
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "SELECT s.id_usuario, s.estado, s.documento, u.estado AS estado_usuario
                 FROM solicitudes_profesor s
                 JOIN usuarios u ON u.id_usuario = s.id_usuario
                 WHERE s.id_solicitud = :id_solicitud
                 LIMIT 1
                 FOR UPDATE"
            );
            $stmt->execute([':id_solicitud' => (int)$id_solicitud]);
            $solicitud = $stmt->fetch();
            if (!$solicitud || $solicitud['estado'] !== 'Pendiente' || $solicitud['estado_usuario'] !== 'Activo') {
                $this->db->rollBack();
                return false;
            }
            if ($estado === 'Aprobada' && empty($solicitud['documento'])) {
                $this->lastError = 'missing_document';
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("UPDATE solicitudes_profesor SET estado = :estado WHERE id_solicitud = :id_solicitud AND estado = 'Pendiente'");
            $stmt->execute([':estado' => $estado, ':id_solicitud' => (int)$id_solicitud]);
            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return false;
            }

            if ($estado === 'Aprobada') {
                $stmt = $this->db->prepare("UPDATE usuarios SET id_rol = 2 WHERE id_usuario = :id_usuario");
                $stmt->execute([':id_usuario' => $solicitud['id_usuario']]);
            }
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->lastError = $e->getMessage();
            error_log("Error UsuarioDao::resolverSolicitud: " . $e->getMessage());
            return false;
        }
    }
}
