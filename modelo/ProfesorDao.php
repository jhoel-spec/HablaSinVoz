<?php

require_once __DIR__ . '/../config/Database.php';

class ProfesorDao {
    private $db;
    private $lastError = null;

    public function __construct() {
        $this->db = Database::getConnection();
        if (!$this->db) {
            $this->lastError = Database::getLastError();
        }
    }

    public function getLastError() {
        return $this->lastError;
    }

    public function obtenerReportes($idProfesor, $soloPendientes = false) {
        if (!$this->db) {
            return [];
        }
        try {
            $sql = "SELECT r.id_reporte, r.comentario, r.respuesta, r.estado, r.fecha_reporte,
                           u.nombre AS estudiante, l.id_leccion, l.titulo AS leccion,
                           c.id_curso, c.titulo AS curso
                    FROM reportes r
                    JOIN usuarios u ON u.id_usuario = r.id_usuario
                    JOIN lecciones l ON l.id_leccion = r.id_leccion
                    JOIN cursos c ON c.id_curso = l.id_curso
                    WHERE (c.id_profesor = :profesor_principal OR EXISTS (
                        SELECT 1 FROM curso_profesores cp
                        WHERE cp.id_curso = c.id_curso AND cp.id_profesor = :profesor_asignado
                    ))";
            if ($soloPendientes) {
                $sql .= " AND r.estado = 'Pendiente'";
            }
            $sql .= " ORDER BY (r.estado = 'Pendiente') DESC, r.fecha_reporte DESC, r.id_reporte DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':profesor_principal' => (int)$idProfesor,
                ':profesor_asignado' => (int)$idProfesor
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error ProfesorDao::obtenerReportes: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerReportesEstudiante($idUsuario) {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT r.comentario, r.respuesta, r.estado, r.fecha_reporte, r.fecha_respuesta,
                        l.titulo AS leccion, c.titulo AS curso
                 FROM reportes r
                 JOIN lecciones l ON l.id_leccion = r.id_leccion
                 JOIN cursos c ON c.id_curso = l.id_curso
                 WHERE r.id_usuario = :usuario
                 ORDER BY r.fecha_reporte DESC, r.id_reporte DESC"
            );
            $stmt->execute([':usuario' => (int)$idUsuario]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error ProfesorDao::obtenerReportesEstudiante: " . $e->getMessage());
            return [];
        }
    }

    public function responderReporte($idReporte, $idProfesor, $respuesta) {
        if (!$this->db || (int)$idReporte <= 0 || (int)$idProfesor <= 0) {
            return false;
        }
        try {
            $sql = "UPDATE reportes r
                    JOIN lecciones l ON l.id_leccion = r.id_leccion
                    JOIN cursos c ON c.id_curso = l.id_curso
                    SET r.respuesta = :respuesta, r.estado = 'Resuelto',
                        r.id_profesor_respuesta = :profesor_actualiza,
                        r.fecha_respuesta = CURRENT_TIMESTAMP
                    WHERE r.id_reporte = :id_reporte
                      AND r.estado = 'Pendiente'
                      AND (c.id_profesor = :profesor_principal OR EXISTS (
                          SELECT 1 FROM curso_profesores cp
                          WHERE cp.id_curso = c.id_curso AND cp.id_profesor = :profesor_asignado
                      ))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':respuesta' => $respuesta,
                ':profesor_actualiza' => (int)$idProfesor,
                ':id_reporte' => (int)$idReporte,
                ':profesor_principal' => (int)$idProfesor,
                ':profesor_asignado' => (int)$idProfesor
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error ProfesorDao::responderReporte: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerMateriales($idProfesor) {
        if (!$this->db) {
            return [];
        }
        try {
            $stmt = $this->db->prepare(
                "SELECT m.ruta_enlace, m.tipo, m.tamano, l.id_leccion, l.titulo AS leccion,
                        l.estado AS estado_leccion, c.id_curso, c.titulo AS curso
                 FROM multimedia m
                 JOIN lecciones l ON l.id_leccion = m.id_leccion
                 JOIN cursos c ON c.id_curso = l.id_curso
                 WHERE c.id_profesor = :profesor_principal OR EXISTS (
                     SELECT 1 FROM curso_profesores cp
                     WHERE cp.id_curso = c.id_curso AND cp.id_profesor = :profesor_asignado
                 )
                 ORDER BY c.titulo, l.orden, m.id_multimedia"
            );
            $stmt->execute([
                ':profesor_principal' => (int)$idProfesor,
                ':profesor_asignado' => (int)$idProfesor
            ]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error ProfesorDao::obtenerMateriales: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerLimiteSubidaMb() {
        if (!$this->db) {
            return null;
        }
        try {
            $stmt = $this->db->query("SELECT limite_mb FROM configuracion_subidas WHERE id_config = 1");
            $limite = $stmt->fetchColumn();
            return $limite === false ? null : (int)$limite;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error ProfesorDao::obtenerLimiteSubidaMb: " . $e->getMessage());
            return null;
        }
    }

    public function actualizarLimiteSubidaMb($limiteMb) {
        if (!$this->db || (int)$limiteMb < 1 || (int)$limiteMb > 500) {
            return false;
        }
        try {
            $stmt = $this->db->prepare(
                "UPDATE configuracion_subidas SET limite_mb = :limite WHERE id_config = 1"
            );
            $stmt->execute([':limite' => (int)$limiteMb]);
            return $stmt->rowCount() > 0 || $this->obtenerLimiteSubidaMb() === (int)$limiteMb;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error ProfesorDao::actualizarLimiteSubidaMb: " . $e->getMessage());
            return false;
        }
    }
}
