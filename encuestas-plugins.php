<?php
/*
Plugin Name: Encuestas Plugins V1 - Duvan Botello
Plugin URI: http://tesis-wordpress.dadojeans.com/
Author: Duvan Botello
Description: Plugin creado para realizar las pruebas necesarias con el fin de sacar datos teoricos que sirve como soporte para trabajo de grado de duvan botello.
Version: 0.0.1
*/

require_once dirname(__FILE__). '/clases/codigocorto.class.php';

function Activar(){
    global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}encuestas (
        `idencuestas` INT NOT NULL AUTO_INCREMENT,
        `nombre` VARCHAR(45) NOT NULL,
        `estado` INT NULL,
        `fecha_inicio` DATE NOT NULL,
        `fecha_fin` DATE NOT NULL,
        `num_vistas` INT NULL,
        `ShortCode` VARCHAR(45) NULL,
        PRIMARY KEY (`idencuestas`),
        UNIQUE INDEX `idencuestas_UNIQUE` (`idencuestas` ASC),
        UNIQUE INDEX `ShortCode_UNIQUE` (`ShortCode` ASC));
      ";

    $wpdb->query($sql);

    $sq2 ="CREATE TABLE IF NOT EXISTS {$wpdb->prefix}preguntas (
        `idpreguntas` INT NOT NULL AUTO_INCREMENT,
        `descripcion` VARCHAR(45) NULL,
        `idencuestas` INT NOT NULL,
        `tipo_pregunta` INT NOT NULL,
        PRIMARY KEY (`idpreguntas`),
        UNIQUE INDEX `idpreguntas_UNIQUE` (`idpreguntas` ASC) ,
        INDEX `fk_preguntas_encuestas1_idx` (`idencuestas` ASC),
        CONSTRAINT `fk_preguntas_encuestas1`
          FOREIGN KEY (`idencuestas`)
          REFERENCES {$wpdb->prefix}encuestas(`idencuestas`)
          ON DELETE NO ACTION
          ON UPDATE NO ACTION);";
    
    $wpdb->query($sq2);

    $sq3 ="CREATE TABLE IF NOT EXISTS {$wpdb->prefix}respuestas (
        `idrespuestas` INT NOT NULL AUTO_INCREMENT,
        `descripcion` VARCHAR(45) NULL,
        `idpreguntas` INT NOT NULL,
        PRIMARY KEY (`idrespuestas`),
        UNIQUE INDEX `idrespuestas_UNIQUE` (`idrespuestas` ASC),
        INDEX `fk_respuestas_preguntas1_idx` (`idpreguntas` ASC),
        CONSTRAINT `fk_respuestas_preguntas1`
          FOREIGN KEY (`idpreguntas`)
          REFERENCES {$wpdb->prefix}preguntas (`idpreguntas`)
          ON DELETE NO ACTION
          ON UPDATE NO ACTION);
      ";

    $wpdb->query($sq3);

    $sq4 ="CREATE TABLE IF NOT EXISTS {$wpdb->prefix}votacion (
        `idvotacion` INT NOT NULL AUTO_INCREMENT,
        `idpreguntas` INT NOT NULL,
        `idrespuestas` INT NOT NULL,
        PRIMARY KEY (`idvotacion`),
        UNIQUE INDEX `idvotacion_UNIQUE` (`idvotacion` ASC),
        INDEX `fk_votacion_preguntas_idx` (`idpreguntas` ASC) ,
        INDEX `fk_votacion_respuestas1_idx` (`idrespuestas` ASC) ,
        CONSTRAINT `fk_votacion_preguntas`
          FOREIGN KEY (`idpreguntas`)
          REFERENCES {$wpdb->prefix}preguntas (`idpreguntas`)
          ON DELETE NO ACTION
          ON UPDATE NO ACTION,
        CONSTRAINT `fk_votacion_respuestas1`
          FOREIGN KEY (`idrespuestas`)
          REFERENCES {$wpdb->prefix}respuestas (`idrespuestas`)
          ON DELETE NO ACTION
          ON UPDATE NO ACTION);";
    
    $wpdb->query($sq4);
} 

function Desactivar(){

}


register_activation_hook( __FILE__, 'Activar'); //Vinculando boton activar con la funcion
register_deactivation_hook(__FILE__, 'Desactivar');

add_action('admin_menu','CrearMenu');

function CrearMenu(){
    add_menu_page(
        'Encuestas V1',//Titulo de la pagina
        'Encuestas V1 Menu',// Titulo del menu
        'manage_options', // Capability
         plugin_dir_path(__FILE__).'admin/lista_encuestas.php', //slug
         null, //function del contenido
         plugin_dir_url(__FILE__).'admin/img/icon.png',//icono
         '1' //priority
    );

 
}

function EncolarBosstrapJs($hook){
    //echo "<script>console.log('$hook')</script>";
    if($hook != "encuestas-plugins/admin/lista_encuestas.php"){
      return ;
    }
    wp_enqueue_script('bootstrapJs',plugins_url('admin/boostrap/js/bootstrap.min.js',__FILE__),array('jquery'));
}

add_action('admin_enqueue_scripts','EncolarBosstrapJs');

function EncolarBosstrapCSS($hook){

  if($hook != "encuestas-plugins/admin/lista_encuestas.php"){
    return ;
  }
  wp_enqueue_style('bootstrapCSS',plugins_url('admin/boostrap/css/bootstrap.min.css',__FILE__));
}

add_action('admin_enqueue_scripts','EncolarBosstrapCSS');


//encolar js propio

function EncolarJS($hook){
  if($hook != "encuestas-plugins/admin/lista_encuestas.php"){
      return ;
  }
  wp_enqueue_script('JsGraficar',plugins_url('admin/js/Chart.js',__FILE__));
  wp_enqueue_script('JsExterno',plugins_url('admin/js/lista_encuestas.js',__FILE__),array('jquery'));
  wp_localize_script('JsExterno','SolicitudesAjax',[
      'url' => admin_url('admin-ajax.php'),
      'seguridad' => wp_create_nonce('seg')
  ]);
}
add_action('admin_enqueue_scripts','EncolarJS');

function EliminarEncuesta(){
  $nonce = $_POST['nonce'];
  if(!wp_verify_nonce($nonce, 'seg')){
      die('No tiene permisos para ejecutar Ajax');
  }

  $id = $_POST['id'];
  global $wpdb;
  $tabla = "{$wpdb->prefix}encuestas";
  $tabla2 = "{$wpdb->prefix}preguntas";
  $wpdb->delete($tabla2,array('idencuestas' =>$id));
  $wpdb->delete($tabla,array('idencuestas' =>$id));
   return true;  

}

add_action('wp_ajax_peticioneliminar','EliminarEncuesta');


function ObtenerEncuesta(){
  $nonce = $_POST['nonce'];
  if(!wp_verify_nonce($nonce, 'seg')){
      die('No tiene permisos para ejecutar Ajax');
  }

  $id = $_POST['id'];
  global $wpdb;
  $tabla = "{$wpdb->prefix}encuestas";
  $tabla2 = "{$wpdb->prefix}preguntas";
  $query = "SELECT * FROM $tabla WHERE idencuestas = '$id'";
  $query2 = "SELECT * FROM $tabla2 WHERE idencuestas = '$id'";
  $datos = $wpdb->get_results($query, ARRAY_A);
  $datos2 = $wpdb->get_results($query2, ARRAY_A);
  $respuesta = [
    'datos_encuesta' => $datos,
    'datos_pregunta' => $datos2,
    ];
  if(empty($respuesta)){
      $respuesta = array();
      wp_send_json($respuesta);
  }else{
    wp_send_json($respuesta);
  }
}

add_action('wp_ajax_obtenerencuesta','ObtenerEncuesta');

function ObtenerEstadisticas(){
  $nonce = $_POST['nonce'];
  if(!wp_verify_nonce($nonce, 'seg')){
      die('No tiene permisos para ejecutar Ajax');
  }

  $id = $_POST['id'];
  global $wpdb;
  $tabla = "{$wpdb->prefix}encuestas";
  $tabla2 = "{$wpdb->prefix}preguntas";
  $tabla3 = "{$wpdb->prefix}respuestas";

  $query = "SELECT * FROM $tabla2 WHERE idencuestas = '$id'";

  $datos = $wpdb->get_results($query, ARRAY_A);
  $array_respuestas_votos = array();
  foreach ($datos as $key => $value) {
    $respuesta = array();
    $id_pregunta = $value['idpreguntas'];
     $query2 = "SELECT * FROM $tabla2 WHERE idpreguntas = '$id_pregunta'";
     $datos2 = $wpdb->get_results($query2, ARRAY_A);
     if($datos2[0]['tipo_pregunta'] == 1){
       $query_estadisticas = "SELECT wp_preguntas.descripcion as 'preguntas', (SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = 'SI') AS 'votos_si', (SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = 'No') AS 'votos_no' FROM wp_preguntas
       WHERE wp_preguntas.idpreguntas = $id_pregunta";
       $resultado_grafica = $wpdb->get_results($query_estadisticas, ARRAY_A);
       $respuesta = [
        'preguntas' => $resultado_grafica[0]['preguntas'],
        'votos_no' => $resultado_grafica[0]['votos_no'],
        'votos_si' => $resultado_grafica[0]['votos_si'],
        'tipo_pregunta' => 1
      ];
       
     }else{
      $query_estadisticas = "SELECT wp_preguntas.descripcion as 'preguntas',(SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = '0') as 'voto_0', (SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = '1') as 'voto_1',(SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = '2') as 'voto_2',(SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = '3') as 'voto_3',(SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = '4') as 'voto_4',(SELECT COUNT(*) FROM wp_respuestas WHERE wp_respuestas.idpreguntas = wp_preguntas.idpreguntas and wp_respuestas.descripcion = '5') as 'voto_5' FROM wp_preguntas
      WHERE wp_preguntas.idpreguntas = $id_pregunta";
       $resultado_grafica = $wpdb->get_results($query_estadisticas, ARRAY_A);
       $respuesta = [
        'preguntas' => $resultado_grafica[0]['preguntas'],
        'votos_0' => $resultado_grafica[0]['voto_0'],
        'votos_1' => $resultado_grafica[0]['voto_1'],
        'votos_2' => $resultado_grafica[0]['voto_2'],
        'votos_3' => $resultado_grafica[0]['voto_3'],
        'votos_4' => $resultado_grafica[0]['voto_4'],
        'votos_5' => $resultado_grafica[0]['voto_5'],
        'tipo_pregunta' => 2
      ];
     }
     array_push($array_respuestas_votos, $respuesta); 
  }
  
  wp_send_json($array_respuestas_votos);

}

add_action('wp_ajax_obtenerestadisticas','ObtenerEstadisticas');

//shortcode

function imprimirshortcode($atts){
  $_short = new codigocorto;
  //obtener el id por parametro
  $id= $atts['id'];
  //Programar las acciones del boton
  if(isset($_POST['btnguardar'])){
      $listadePreguntas = $_short->ObtenerEncuestaDetalle($id);
      foreach ($listadePreguntas as $key => $value) {
         $idpregunta = $value['idpreguntas'];
         if(isset($_POST[$idpregunta])){
             $valortxt = $_POST[$idpregunta];
             $datos = [
                 'idpreguntas' => $idpregunta,
                 'descripcion' => $valortxt
             ];
             $_short->GuardarDetalle($datos);
         }
      }
      return " Encuesta enviada exitosamente";
  }
  //Imprimir el formulario
  $html = $_short->Armador($id);
  return $html;
}


add_shortcode("ENC","imprimirshortcode");