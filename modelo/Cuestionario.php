<?php

class Cuestionario {
    private $id_cuestionario;
    private $id_leccion;
    private $titulo;
    private $preguntas;

    public function __construct($id_cuestionario = null, $id_leccion = null, $titulo = '', $preguntas = []) {
        $this->id_cuestionario = $id_cuestionario;
        $this->id_leccion = $id_leccion;
        $this->titulo = $titulo;
        $this->preguntas = $preguntas;
    }

    public function getIdCuestionario() { return $this->id_cuestionario; }
    public function setIdCuestionario($id) { $this->id_cuestionario = $id; }

    public function getIdLeccion() { return $this->id_leccion; }
    public function setIdLeccion($id) { $this->id_leccion = $id; }

    public function getTitulo() { return $this->titulo; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }

    public function getPreguntas() { return $this->preguntas; }
    public function setPreguntas($preguntas) { $this->preguntas = $preguntas; }
}
