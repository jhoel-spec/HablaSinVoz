<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Multimedia.php';

class MultimediaDao {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los recursos multimedia de una lección
     */
    public function obtenerPorLeccion($id_leccion) {
        $recursos = [];
        if ($this->db) {
            try {
                $sql = "SELECT * FROM multimedia WHERE id_leccion = :id_leccion ORDER BY id_multimedia ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':id_leccion' => $id_leccion]);
                while ($row = $stmt->fetch()) {
                    $recursos[] = new Multimedia(
                        $row['id_multimedia'],
                        $row['ruta_enlace'],
                        $row['tipo'],
                        (int)$row['tamano'],
                        $row['id_leccion']
                    );
                }
                return $recursos;
            } catch (PDOException $e) {
                error_log("Error MultimediaDao::obtenerPorLeccion: " . $e->getMessage());
                return [];
            }
        }

        return $recursos;
    }

    /**
     * Guarda un nuevo archivo o enlace multimedia
     */
    public function crear(Multimedia $media) {
        if ($this->db) {
            try {
                $sql = "INSERT INTO multimedia (ruta_enlace, tipo, tamano, id_leccion) 
                        VALUES (:ruta, :tipo, :tamano, :id_leccion)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    ':ruta' => $media->getRutaEnlace(),
                    ':tipo' => $media->getTipo(),
                    ':tamano' => $media->getTamano(),
                    ':id_leccion' => $media->getIdLeccion()
                ]);
                return $this->db->lastInsertId();
            } catch (PDOException $e) {
                error_log("Error MultimediaDao::crear: " . $e->getMessage());
            }
        }
        return false;
    }

    /**
     * Elimina un registro multimedia
     */
    public function eliminar($id_multimedia, $id_curso = null) {
        if (!$this->db || (int)$id_multimedia <= 0) {
            return false;
        }
        try {
            $sqlRuta = "SELECT m.ruta_enlace
                        FROM multimedia m
                        JOIN lecciones l ON l.id_leccion = m.id_leccion
                        WHERE m.id_multimedia = :id_multimedia";
            $parametros = [':id_multimedia' => (int)$id_multimedia];
            if ($id_curso !== null) {
                $sqlRuta .= " AND l.id_curso = :id_curso";
                $parametros[':id_curso'] = (int)$id_curso;
            }
            $stmt = $this->db->prepare($sqlRuta);
            $stmt->execute($parametros);
            $ruta = $stmt->fetchColumn();
            if ($ruta === false) {
                return false;
            }

            if ($id_curso !== null) {
                $stmt = $this->db->prepare(
                    "DELETE m FROM multimedia m
                     JOIN lecciones l ON l.id_leccion = m.id_leccion
                     WHERE m.id_multimedia = :id_multimedia AND l.id_curso = :id_curso"
                );
                $stmt->execute([
                    ':id_multimedia' => (int)$id_multimedia,
                    ':id_curso' => (int)$id_curso
                ]);
            } else {
                $stmt = $this->db->prepare("DELETE FROM multimedia WHERE id_multimedia = :id_multimedia");
                $stmt->execute([':id_multimedia' => (int)$id_multimedia]);
            }
            if ($stmt->rowCount() !== 1) {
                return false;
            }
            self::eliminarArchivosFisicos([$ruta]);
            return true;
        } catch (PDOException $e) {
            error_log("Error MultimediaDao::eliminar: " . $e->getMessage());
            return false;
        }
    }

    public static function eliminarArchivosFisicos(array $rutas) {
        $directorio = realpath(__DIR__ . '/../uploads');
        if ($directorio === false) {
            return;
        }
        foreach ($rutas as $ruta) {
            if (!is_string($ruta) || preg_match('/\Auploads\/[a-f0-9]{32}\.(?:mp4|webm|ogv|mp3|wav|ogg|oga|weba|flac|m4a|aac|jpe?g|png|apng|gif|webp|avif|bmp|ico)\z/i', $ruta) !== 1) {
                continue;
            }
            $archivo = realpath($directorio . DIRECTORY_SEPARATOR . substr($ruta, strlen('uploads/')));
            if (
                $archivo !== false &&
                strpos($archivo, $directorio . DIRECTORY_SEPARATOR) === 0 &&
                is_file($archivo) &&
                !unlink($archivo)
            ) {
                error_log("No se pudo eliminar el archivo multimedia: " . basename($archivo));
            }
        }
    }
}
