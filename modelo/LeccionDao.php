<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Leccion.php';
require_once __DIR__ . '/MultimediaDao.php';

class LeccionDao {
    private $db;
    private $multimediaDao;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->multimediaDao = new MultimediaDao();
    }

    /**
     * Obtiene las lecciones de un curso
     */
    public function obtenerPorCurso($id_curso, $soloPublicadas = true, $id_usuario = null) {
        $lecciones = [];
        if ($this->db) {
            try {
                $condicion = $soloPublicadas ? " AND l.estado = 'Publicada'" : "";
                $sql = "SELECT l.*, 
                        (SELECT COUNT(*) FROM progreso p WHERE p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario AND p.completada = 1) AS completada
                        FROM lecciones l
                        WHERE l.id_curso = :id_curso $condicion
                        ORDER BY l.orden ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':id_curso' => $id_curso,
                    ':id_usuario' => $id_usuario ?: 0
                ]);
                while ($row = $stmt->fetch()) {
                    $media = $this->multimediaDao->obtenerPorLeccion($row['id_leccion']);
                    $lecciones[] = new Leccion(
                        $row['id_leccion'],
                        $row['id_curso'],
                        $row['titulo'],
                        $row['descripcion'],
                        (int)$row['orden'],
                        $row['estado'],
                        $media,
                        (bool)$row['completada']
                    );
                }
                return $lecciones;
            } catch (PDOException $e) {
                error_log("Error LeccionDao::obtenerPorCurso: " . $e->getMessage());
            }
        }

        return $lecciones;
    }

    /**
     * Obtiene una lección por su ID
     */
    public function obtenerPorId($id_leccion, $id_usuario = null) {
        if ($this->db) {
            try {
                $sql = "SELECT l.*,
                        (SELECT COUNT(*) FROM progreso p WHERE p.id_leccion = l.id_leccion AND p.id_usuario = :id_usuario AND p.completada = 1) AS completada
                        FROM lecciones l
                        WHERE l.id_leccion = :id LIMIT 1";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':id' => $id_leccion,
                    ':id_usuario' => $id_usuario ?: 0
                ]);
                $row = $stmt->fetch();
                if ($row) {
                    $media = $this->multimediaDao->obtenerPorLeccion($row['id_leccion']);
                    return new Leccion(
                        $row['id_leccion'],
                        $row['id_curso'],
                        $row['titulo'],
                        $row['descripcion'],
                        (int)$row['orden'],
                        $row['estado'],
                        $media,
                        (bool)$row['completada']
                    );
                }
            } catch (PDOException $e) {
                error_log("Error LeccionDao::obtenerPorId: " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Obtiene lecciones pendientes de aprobación para el Administrador
     */
    public function obtenerPendientes($id_curso = null) {
        if ($this->db) {
            try {
                $sql = "SELECT l.*, c.titulo AS nombre_curso, u.nombre AS nombre_profesor 
                        FROM lecciones l 
                        JOIN cursos c ON l.id_curso = c.id_curso 
                        JOIN usuarios u ON c.id_profesor = u.id_usuario 
                        WHERE l.estado = 'Pendiente'";
                $parametros = [];
                if ($id_curso) {
                    $sql .= " AND l.id_curso = :id_curso";
                    $parametros[':id_curso'] = $id_curso;
                }
                $sql .= " ORDER BY l.id_leccion ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($parametros);
                return $stmt->fetchAll();
            } catch (PDOException $e) {
                error_log("Error LeccionDao::obtenerPendientes: " . $e->getMessage());
            }
        }
        return [];
    }

    /**
     * Crea una lección
     */
    public function crear(Leccion $leccion) {
        if ($this->db) {
            try {
                $sql = "INSERT INTO lecciones (id_curso, titulo, descripcion, orden, estado) 
                        VALUES (:id_curso, :titulo, :descripcion, :orden, :estado)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':id_curso' => $leccion->getIdCurso(),
                    ':titulo' => $leccion->getTitulo(),
                    ':descripcion' => $leccion->getDescripcion(),
                    ':orden' => $leccion->getOrden(),
                    ':estado' => $leccion->getEstado()
                ]);
                return $this->db->lastInsertId();
            } catch (PDOException $e) {
                error_log("Error LeccionDao::crear: " . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Cambia el estado de una lección ('Pendiente', 'Publicada', 'Observada')
     */
    public function cambiarEstado($id_leccion, $estado) {
        if (!$this->db || (int)$id_leccion <= 0 || !in_array($estado, ['Pendiente', 'Publicada', 'Observada'], true)) {
            return false;
        }
        try {
            $sql = "UPDATE lecciones SET estado = :estado WHERE id_leccion = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':estado' => $estado, ':id' => (int)$id_leccion]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error LeccionDao::cambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    public function actualizar(Leccion $leccion, $id_curso = null) {
        if (
            !$this->db ||
            (int)$leccion->getIdLeccion() <= 0 ||
            (int)$leccion->getOrden() < 1 ||
            !in_array($leccion->getEstado(), ['Pendiente', 'Publicada', 'Observada'], true)
        ) {
            return false;
        }
        try {
            $sql = "UPDATE lecciones
                    SET titulo = :titulo, descripcion = :descripcion, orden = :orden, estado = :estado
                    WHERE id_leccion = :id_leccion";
            $parametros = [
                ':titulo' => $leccion->getTitulo(),
                ':descripcion' => $leccion->getDescripcion(),
                ':orden' => (int)$leccion->getOrden(),
                ':estado' => $leccion->getEstado(),
                ':id_leccion' => (int)$leccion->getIdLeccion()
            ];
            if ($id_curso !== null) {
                $sql .= " AND id_curso = :id_curso";
                $parametros[':id_curso'] = (int)$id_curso;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($parametros);
            if ($stmt->rowCount() > 0) {
                return true;
            }
            $sqlExiste = "SELECT 1 FROM lecciones WHERE id_leccion = :id_leccion";
            $parametrosExiste = [':id_leccion' => (int)$leccion->getIdLeccion()];
            if ($id_curso !== null) {
                $sqlExiste .= " AND id_curso = :id_curso";
                $parametrosExiste[':id_curso'] = (int)$id_curso;
            }
            $existe = $this->db->prepare($sqlExiste . " LIMIT 1");
            $existe->execute($parametrosExiste);
            return (bool)$existe->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error LeccionDao::actualizar: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar($id_leccion, $id_curso = null) {
        if (!$this->db || (int)$id_leccion <= 0) {
            return false;
        }
        $rutasMaterial = [];
        try {
            $this->db->beginTransaction();
            $sqlLeccion = "SELECT id_leccion FROM lecciones WHERE id_leccion = :id_leccion";
            $parametrosLeccion = [':id_leccion' => (int)$id_leccion];
            if ($id_curso !== null) {
                $sqlLeccion .= " AND id_curso = :id_curso";
                $parametrosLeccion[':id_curso'] = (int)$id_curso;
            }
            $stmt = $this->db->prepare($sqlLeccion . " FOR UPDATE");
            $stmt->execute($parametrosLeccion);
            if (!$stmt->fetch()) {
                $this->db->rollBack();
                return false;
            }

            $stmt = $this->db->prepare("SELECT ruta_enlace FROM multimedia WHERE id_leccion = :id_leccion");
            $stmt->execute([':id_leccion' => (int)$id_leccion]);
            $rutasMaterial = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $consultas = [
                "DELETE o FROM opciones o
                 JOIN preguntas p ON p.id_pregunta = o.id_pregunta
                 JOIN cuestionarios q ON q.id_cuestionario = p.id_cuestionario
                 WHERE q.id_leccion = :id_leccion",
                "DELETE p FROM preguntas p
                 JOIN cuestionarios q ON q.id_cuestionario = p.id_cuestionario
                 WHERE q.id_leccion = :id_leccion",
                "DELETE i FROM intentos i
                 JOIN cuestionarios q ON q.id_cuestionario = i.id_cuestionario
                 WHERE q.id_leccion = :id_leccion",
                "DELETE FROM cuestionarios WHERE id_leccion = :id_leccion",
                "DELETE FROM progreso WHERE id_leccion = :id_leccion",
                "DELETE FROM reportes WHERE id_leccion = :id_leccion",
                "DELETE FROM multimedia WHERE id_leccion = :id_leccion",
                "DELETE FROM lecciones WHERE id_leccion = :id_leccion"
            ];
            foreach ($consultas as $sql) {
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id_leccion' => (int)$id_leccion]);
            }
            $this->db->commit();
            MultimediaDao::eliminarArchivosFisicos($rutasMaterial);
            return true;
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error LeccionDao::eliminar: " . $e->getMessage());
            return false;
        }
    }
}
