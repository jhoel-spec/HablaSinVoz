<?php

class Multimedia {
    private $id_multimedia;
    private $ruta_enlace;
    private $tipo;
    private $tamano;
    private $id_leccion;

    public function __construct(
        $id_multimedia = null,
        $ruta_enlace = '',
        $tipo = 'Imagen',
        $tamano = 0,
        $id_leccion = null
    ) {
        $this->id_multimedia = $id_multimedia;
        $this->ruta_enlace = $ruta_enlace;
        $this->tipo = $tipo;
        $this->tamano = $tamano;
        $this->id_leccion = $id_leccion;
    }

    public function getIdMultimedia() { return $this->id_multimedia; }
    public function setIdMultimedia($id) { $this->id_multimedia = $id; }

    public function getRutaEnlace() { return $this->ruta_enlace; }
    public function setRutaEnlace($ruta) { $this->ruta_enlace = $ruta; }

    public function getTipo() { return $this->tipo; }
    public function setTipo($tipo) { $this->tipo = $tipo; }

    public function getTamano() { return $this->tamano; }
    public function setTamano($tamano) { $this->tamano = $tamano; }

    public function getIdLeccion() { return $this->id_leccion; }
    public function setIdLeccion($id) { $this->id_leccion = $id; }
}
