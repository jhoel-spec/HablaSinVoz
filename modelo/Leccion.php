<?php

class Leccion {
    private $id_leccion;
    private $id_curso;
    private $titulo;
    private $descripcion;
    private $orden;
    private $estado;
    private $multimedia;
    private $completada;

    public function __construct(
        $id_leccion = null,
        $id_curso = null,
        $titulo = '',
        $descripcion = '',
        $orden = 1,
        $estado = 'Pendiente',
        $multimedia = [],
        $completada = false
    ) {
        $this->id_leccion = $id_leccion;
        $this->id_curso = $id_curso;
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->orden = $orden;
        $this->estado = $estado;
        $this->multimedia = $multimedia;
        $this->completada = $completada;
    }

    public function getIdLeccion() { return $this->id_leccion; }
    public function setIdLeccion($id) { $this->id_leccion = $id; }

    public function getIdCurso() { return $this->id_curso; }
    public function setIdCurso($id) { $this->id_curso = $id; }

    public function getTitulo() { return $this->titulo; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }

    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($desc) { $this->descripcion = $desc; }

    public function getOrden() { return $this->orden; }
    public function setOrden($orden) { $this->orden = $orden; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

    public function getMultimedia() { return $this->multimedia; }
    public function setMultimedia($media) { $this->multimedia = $media; }

    public function isCompletada() { return $this->completada; }
    public function setCompletada($completada) { $this->completada = $completada; }
}
