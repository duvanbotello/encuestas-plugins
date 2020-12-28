<?php

class codigocorto{

    public function ObtenerEncuesta($encuestaid){
        global $wpdb;
        $tabla = "{$wpdb->prefix}encuestas";
        $query = "SELECT * FROM $tabla WHERE idencuestas = '$encuestaid'";
        $datos = $wpdb->get_results($query,ARRAY_A);
        if(empty($datos)){
            $datos = array();
        }
        return $datos[0];
    }

    public function ObtenerEncuestaDetalle($encuestaid){
        global $wpdb;
        $tabla = "{$wpdb->prefix}preguntas";
        $query = "SELECT * FROM $tabla WHERE idencuestas = '$encuestaid'";
        $datos = $wpdb->get_results($query,ARRAY_A);
        if(empty($datos)){
            $datos = array();
        }
        return $datos;
    }

    public function formOpen($titulo){
        $html = "
            <div class='wrap'>
                <h4> $titulo</h4>
                <br>
                <form method='POST'>
        ";

        return $html;
    }

    public function formClose(){
        $html = "
              <br>
                 <input type='submit' id='btnguardar' name='btnguardar' class='page-title-action' value='enviar'>
            </form>
          </div>  
        ";

        return $html;
    }

    function fromInput($idpreguntas,$descripcion,$tipo_pregunta){
        $html="";
        if($tipo_pregunta == 1){
            $html="
                <diV class='from-group'>
                    <p><b>$descripcion</b></p>
                  <div class='col-sm-8'>  
                        <select class='from-control' id='$idpreguntas' name='$idpreguntas'>
                                <option value='SI'>SI</option>
                                <option value='No'>NO</option>
                        </select>
                  </div>
            
            ";
        }elseif ($tipo_pregunta == 2) {
            $html="
                <diV class='from-group'>
                    <p><b>$descripcion</b></p>
                  <div class='col-sm-8'>  
                        <select class='from-control' id='$idpreguntas' name='$idpreguntas'>
                                <option value='0'>0</option>
                                <option value='1'>1</option>
                                <option value='2'>2</option>
                                <option value='3'>3</option>
                                <option value='4'>4</option>
                                <option value='5'>5</option>
                        </select>
                  </div>
            
            ";
        }else{

        }
        return $html;
    }

    function Armador($encuestaid){
        $enc = $this->ObtenerEncuesta($encuestaid);
         $nombre = $enc['nombre'];
        //obtener todas las preguntas
        $preguntas = "";
        $listapregutas = $this->ObtenerEncuestaDetalle($encuestaid);
        foreach ($listapregutas as $key => $value) {
            $idpreguntas = $value['idpreguntas'];
            $descripcion = $value['descripcion'];
            $tipo_pregunta =$value['tipo_pregunta'];
            $encid = $value['idencuestas'];

            if($encid == $encuestaid){
                $preguntas .= $this->fromInput($idpreguntas,$descripcion,$tipo_pregunta);
            }
        }

        $html = $this->formOpen($nombre);
        $html .= $preguntas;
        $html .= $this->formClose();

        return $html;

    }


    function GuardarDetalle($datos){
        global $wpdb;
        $tabla = "{$wpdb->prefix}respuestas"; 
        return  $wpdb->insert($tabla,$datos);
    }




}