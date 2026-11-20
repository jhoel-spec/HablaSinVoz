<?php

class Curso {
    private $id_curso;
    private $titulo;
    private $descripcion;
    private $nivel;
    private $id_profesor;
    private $estado;
    private $nombre_profesor;
    private $total_lecciones;
    private $porcentaje_progreso;
    private $imagen;
    private $inscrito;

    public function __construct(
        $id_curso = null,
        $titulo = '',
        $descripcion = '',
        $nivel = 'Básico 1',
        $id_profesor = null,
        $estado = 'Borrador',
        $nombre_profesor = '',
        $total_lecciones = 0,
        $porcentaje_progreso = 0,
        $imagen = '',
        $inscrito = false
    ) {
        $this->id_curso = $id_curso;
        $this->titulo = $titulo;
        $this->descripcion = $descripcion;
        $this->nivel = $nivel;
        $this->id_profesor = $id_profesor;
        $this->estado = $estado;
        $this->nombre_profesor = $nombre_profesor;
        $this->total_lecciones = $total_lecciones;
        $this->porcentaje_progreso = $porcentaje_progreso;
        $this->imagen = $imagen;
        $this->inscrito = (bool)$inscrito;
    }

    public function getIdCurso() { return $this->id_curso; }
    public function setIdCurso($id) { $this->id_curso = $id; }

    public function getTitulo() { return $this->titulo; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }

    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }

    public function getNivel() { return $this->nivel; }
    public function setNivel($nivel) { $this->nivel = $nivel; }

    public function getIdProfesor() { return $this->id_profesor; }
    public function setIdProfesor($id) { $this->id_profesor = $id; }

    public function getEstado() { return $this->estado; }
    public function setEstado($estado) { $this->estado = $estado; }

    public function getNombreProfesor() { return $this->nombre_profesor; }
    public function setNombreProfesor($nombre) { $this->nombre_profesor = $nombre; }

    public function getTotalLecciones() { return $this->total_lecciones; }
    public function setTotalLecciones($total) { $this->total_lecciones = $total; }

    public function getPorcentajeProgreso() { return $this->porcentaje_progreso; }
    public function setPorcentajeProgreso($porcentaje) { $this->porcentaje_progreso = $porcentaje; }

    public function getImagen() { return $this->imagen; }
    public function setImagen($img) { $this->imagen = $img; }

    public function estaInscrito() { return $this->inscrito; }
    public function setInscrito($inscrito) { $this->inscrito = (bool)$inscrito; }
}
