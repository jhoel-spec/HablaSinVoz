<?php

class Usuario {
    private $id_usuario;
    private $nombre;
    private $correo;
    private $password_hash;
    private $id_rol;
    private $nombre_rol;
    private $estado;

    public function __construct($id_usuario = null, $nombre = '', $correo = '', $password_hash = '', $id_rol = 1, $nombre_rol = 'Estudiante', $estado = 'Activo') {
        $this->id_usuario = $id_usuario;
        $this->nombre = $nombre;
        $this->correo = $correo;
        $this->password_hash = $password_hash;
        $this->id_rol = $id_rol;
        $this->nombre_rol = $nombre_rol;
        $this->estado = $estado;
    }

    public function getIdUsuario() { return $this->id_usuario; }
    public function setIdUsuario($id) { $this->id_usuario = $id; }

    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }

    public function getCorreo() { return $this->correo; }
    public function setCorreo($correo) { $this->correo = $correo; }

    public function getPasswordHash() { return $this->password_hash; }
    public function setPasswordHash($hash) { $this->password_hash = $hash; }

    public function getIdRol() { return $this->id_rol; }
    public function setIdRol($rol) { $this->id_rol = $rol; }

    public function getNombreRol() { return $this->nombre_rol; }
    public function setNombreRol($nombre_rol) { $this->nombre_rol = $nombre_rol; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

    public function esEstudiante() { return (int)$this->id_rol === 1; }
    public function esProfesor() { return (int)$this->id_rol === 2; }
    public function esAdmin() { return (int)$this->id_rol === 3; }
}
