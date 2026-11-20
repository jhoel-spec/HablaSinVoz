<?php

class Progreso {
    private $id_progreso;
    private $id_usuario;
    private $id_leccion;
    private $completada;
    private $fecha_completado;

    public function __construct($id_progreso = null, $id_usuario = null, $id_leccion = null, $completada = 1, $fecha_completado = null) {
        $this->id_progreso = $id_progreso;
        $this->id_usuario = $id_usuario;
        $this->id_leccion = $id_leccion;
        $this->completada = $completada;
        $this->fecha_completado = $fecha_completado;
    }

    public function getIdProgreso() { return $this->id_progreso; }
    public function setIdProgreso($id) { $this->id_progreso = $id; }

    public function getIdUsuario() { return $this->id_usuario; }
    public function setIdUsuario($id) { $this->id_usuario = $id; }

    public function getIdLeccion() { return $this->id_leccion; }
    public function setIdLeccion($id) { $this->id_leccion = $id; }

    public function isCompletada() { return (bool)$this->completada; }
    public function setCompletada($comp) { $this->completada = $comp ? 1 : 0; }

    public function getFechaCompletado() { return $this->fecha_completado; }
    public function setFechaCompletado($fecha) { $this->fecha_ado = $fecha; }
}
