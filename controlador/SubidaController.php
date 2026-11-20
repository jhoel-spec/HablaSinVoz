<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../modelo/CursoDao.php';
require_once __DIR__ . '/../modelo/LeccionDao.php';
require_once __DIR__ . '/../modelo/MultimediaDao.php';
require_once __DIR__ . '/../modelo/ProfesorDao.php';
require_once __DIR__ . '/AuthController.php';

class SubidaController {
    private $cursoDao;
    private $leccionDao;
    private $multimediaDao;
    private $profesorDao;
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cursoDao = new CursoDao();
        $this->leccionDao = new LeccionDao();
        $this->multimediaDao = new MultimediaDao();
        $this->profesorDao = new ProfesorDao();
        $this->db = Database::getConnection();
    }

    /**
     * Muestra el formulario para subir contenido (profesor)
     */
    public function subir() {
        AuthController::requerirRol([2]);
        $id_profesor = $_SESSION['usuario_id'];
        $cursos = $this->cursoDao->obtenerPorProfesor($id_profesor);

        $mensaje = null;
        $error = null;
        $limiteMb = $this->profesorDao->obtenerLimiteSubidaMb();
        if ($limiteMb === null) {
            $error = "No se pudo consultar la configuración de carga. Ejecuta la actualización de la base de datos.";
        }

        $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $contenidoEnviado = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
        if ($metodo === 'POST' && empty($_POST) && empty($_FILES) && $contenidoEnviado > 0) {
            $error = "La solicitud supera el límite de carga del servidor. Aumenta post_max_size y upload_max_filesize en PHP.";
        } elseif ($metodo === 'POST') {
            if (($_POST['accion'] ?? '') === 'actualizar_limite') {
                $nuevoLimite = filter_var($_POST['limite_mb'] ?? null, FILTER_VALIDATE_INT);
                if ($nuevoLimite === false || $nuevoLimite < 1 || $nuevoLimite > 500) {
                    $error = "El límite debe ser un número entero entre 1 y 500 MB.";
                } elseif (!$this->profesorDao->actualizarLimiteSubidaMb($nuevoLimite)) {
                    $error = "No se pudo guardar el nuevo límite de carga.";
                } else {
                    $limiteMb = $nuevoLimite;
                    $mensaje = "El límite máximo por archivo se actualizó a {$limiteMb} MB.";
                }
            } elseif ($limiteMb === null) {
                $error = "No se pudo consultar la configuración de carga. Ejecuta la actualización de la base de datos.";
            } else {
                $id_curso = (int)($_POST['id_curso'] ?? 0);
                $titulo = trim($_POST['titulo'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');
                $orden = (int)($_POST['orden'] ?? 1);
                $enlaceExterno = trim($_POST['enlace_externo'] ?? '');
                $archivo = $_FILES['archivo'] ?? null;
                $archivoAdjunto = is_array($archivo) && (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
                $cursoPropio = false;
                foreach ($cursos as $curso) {
                    if ((int)$curso->getIdCurso() === $id_curso) {
                        $cursoPropio = true;
                        break;
                    }
                }

                if (preg_match('/\A.{1,150}\z/us', $titulo) !== 1) {
                    $error = "El título es obligatorio y no debe superar 150 caracteres.";
                } elseif (!$cursoPropio) {
                    $error = "Selecciona un curso que esté asignado a tu cuenta.";
                } elseif ($orden < 1) {
                    $error = "El número de orden debe ser mayor que cero.";
                } elseif ($archivoAdjunto && $enlaceExterno !== '') {
                    $error = "Adjunta un archivo o ingresa un enlace, pero no ambos.";
                } elseif (!$archivoAdjunto && $enlaceExterno === '') {
                    $error = "Adjunta un archivo multimedia o ingresa un enlace.";
                } elseif (!$archivoAdjunto && !$this->esEnlaceValido($enlaceExterno)) {
                    $error = "Ingresa un enlace válido que comience con http:// o https://.";
                } elseif (!$this->db) {
                    $error = "No hay conexión con la base de datos. No se guardó el material.";
                } else {
                    $material = $archivoAdjunto
                        ? $this->guardarArchivoMaterial($archivo, $error, $limiteMb)
                        : [
                            'ruta' => $enlaceExterno,
                            'tipo' => 'Enlace',
                            'tamano' => 0,
                            'ruta_fisica' => null
                        ];

                    if ($material !== false) {
                        try {
                            $this->db->beginTransaction();
                            $leccion = new Leccion(null, $id_curso, $titulo, $descripcion, $orden, 'Pendiente');
                            $id_leccion = $this->leccionDao->crear($leccion);
                            if (!$id_leccion) {
                                $this->db->rollBack();
                                $this->eliminarArchivoMaterial($material['ruta_fisica']);
                                $error = "No se pudo registrar la lección en la base de datos.";
                            } else {
                                $media = new Multimedia(null, $material['ruta'], $material['tipo'], $material['tamano'], $id_leccion);
                                if (!$this->multimediaDao->crear($media)) {
                                    $this->db->rollBack();
                                    $this->eliminarArchivoMaterial($material['ruta_fisica']);
                                    $error = "No se pudo guardar el material multimedia; no se registró la lección.";
                                } else {
                                    $this->db->commit();
                                    $mensaje = "Material enviado para revisión en el curso seleccionado.";
                                }
                            }
                        } catch (PDOException $e) {
                            if ($this->db->inTransaction()) {
                                $this->db->rollBack();
                            }
                            $this->eliminarArchivoMaterial($material['ruta_fisica']);
                            error_log("Error SubidaController::subir: " . $e->getMessage());
                            $error = "No se pudo guardar el material. Inténtalo nuevamente.";
                        }
                    }
                }
            }
        }

        require_once __DIR__ . '/../vista/profesor/subir_contenido.php';
    }

    private function esEnlaceValido($enlace) {
        if (!filter_var($enlace, FILTER_VALIDATE_URL)) {
            return false;
        }
        $esquema = strtolower(parse_url($enlace, PHP_URL_SCHEME) ?? '');
        return in_array($esquema, ['http', 'https'], true);
    }

    private function guardarArchivoMaterial($archivo, &$error, $limiteMb) {
        $codigoError = (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($codigoError !== UPLOAD_ERR_OK) {
            $error = in_array($codigoError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? "El archivo supera el límite permitido."
                : "No se pudo recibir el archivo. Inténtalo nuevamente.";
            return false;
        }

        $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));
        $mimesPermitidos = [
            'mp4' => ['video/mp4'],
            'webm' => ['video/webm'],
            'ogv' => ['video/ogg', 'application/ogg'],
            'mp3' => ['audio/mpeg', 'audio/mp3'],
            'wav' => ['audio/wav', 'audio/x-wav', 'audio/wave'],
            'ogg' => ['audio/ogg', 'application/ogg'],
            'oga' => ['audio/ogg', 'application/ogg'],
            'weba' => ['audio/webm', 'video/webm'],
            'flac' => ['audio/flac', 'audio/x-flac'],
            'm4a' => ['audio/mp4', 'audio/x-m4a', 'video/mp4'],
            'aac' => ['audio/aac', 'audio/x-aac'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'apng' => ['image/apng', 'image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'avif' => ['image/avif'],
            'bmp' => ['image/bmp', 'image/x-ms-bmp'],
            'ico' => ['image/vnd.microsoft.icon', 'image/x-icon']
        ];
        if (!isset($mimesPermitidos[$extension]) || !class_exists('finfo')) {
            $error = "Formato multimedia no permitido. Usa formatos web comunes como MP4, WebM, OGV, MP3, WAV, OGG, FLAC, M4A, AAC o imágenes JPG, PNG, GIF, WebP, AVIF, BMP e ICO.";
            return false;
        }

        $tamano = (int)($archivo['size'] ?? 0);
        $limite = (int)$limiteMb * 1024 * 1024;
        $temporal = $archivo['tmp_name'] ?? '';
        if ($tamano <= 0 || $tamano > $limite || !is_uploaded_file($temporal)) {
            $error = "El archivo debe ser válido y no superar el límite configurado de {$limiteMb} MB.";
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($temporal);
        if (!in_array($mime, $mimesPermitidos[$extension], true)) {
            $error = "El contenido del archivo no coincide con su extensión.";
            return false;
        }

        $directorio = __DIR__ . '/../uploads/';
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            $error = "No se pudo preparar la carpeta de archivos.";
            return false;
        }

        try {
            $nombre = bin2hex(random_bytes(16)) . '.' . $extension;
        } catch (Exception $e) {
            error_log("No se pudo generar el nombre del archivo multimedia: " . $e->getMessage());
            $error = "No se pudo preparar el archivo. Inténtalo nuevamente.";
            return false;
        }

        $destino = $directorio . $nombre;
        if (!move_uploaded_file($temporal, $destino)) {
            $error = "No se pudo guardar el archivo multimedia.";
            return false;
        }

        $extensionesVideo = ['mp4', 'webm', 'ogv'];
        $extensionesAudio = ['mp3', 'wav', 'ogg', 'oga', 'weba', 'flac', 'm4a', 'aac'];
        return [
            'ruta' => 'uploads/' . $nombre,
            'tipo' => in_array($extension, $extensionesVideo, true)
                ? 'Video'
                : (in_array($extension, $extensionesAudio, true) ? 'Audio' : 'Imagen'),
            'tamano' => $tamano,
            'ruta_fisica' => $destino
        ];
    }

    private function eliminarArchivoMaterial($ruta) {
        if ($ruta !== null && is_file($ruta) && !unlink($ruta)) {
            error_log("No se pudo eliminar un archivo multimedia cuyo registro falló.");
        }
    }
}
