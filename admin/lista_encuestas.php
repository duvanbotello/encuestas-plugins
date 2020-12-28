<?php 
    global $wpdb;

    $tabla = "{$wpdb->prefix}encuestas";
    $tabla2 = "{$wpdb->prefix}preguntas";

    if(isset($_POST['btnactualizar'])){
        $txt_date1 = $_POST['txt_date1_01'];
        $nombre = $_POST['txtnombre_1'];
        $txt_date2 = $_POST['txt_date2_02'];
        $id_encuesta = $_POST['id_encuesta_1'];
        $datos = [
          'nombre' => $nombre,
          'fecha_inicio' => $txt_date1,
          'fecha_fin' => $txt_date2,
      ];
      $respuesta = $wpdb->update($tabla,$datos, array( 'idencuestas' => $id_encuesta ));
     
        $listapreguntas = $_POST['name'];
        $i = 0;

        foreach ($listapreguntas as $key => $value) {
             $tipo = $_POST['type'][$i];
             $id_pregunta = $_POST['preguntas'][$i];
              $datos2 = [
                 'descripcion' => $value,
                 'tipo_pregunta' => $tipo
             ];

             $wpdb->update($tabla2,$datos2, array( 'idpreguntas' => $id_pregunta ));

             $i++;
        }
     
      

    }

    if(isset($_POST['btnguardar'])){
        
      $txt_date1 = $_POST['txt_date1'];
      $nombre = $_POST['txtnombre'];
      $txt_date2 = $_POST['txt_date2'];
      $query = "SELECT idencuestas FROM $tabla ORDER BY idencuestas DESC limit 1";
      $resultado = $wpdb->get_results($query,ARRAY_A);
      $proximoId = $resultado[0]['idencuestas'] + 1;
      $shortcode = "[ENC id='$proximoId']";

      $datos = [
          'idencuestas' => null,
          'Nombre' => $nombre,
          'fecha_inicio' => $txt_date1,
          'fecha_fin' => $txt_date2,
          'ShortCode' => $shortcode
      ];
      $respuesta =  $wpdb->insert($tabla,$datos);

      if($respuesta){
        $listapreguntas = $_POST['name'];
        $i = 0;
        foreach ($listapreguntas as $key => $value) {
             $tipo = $_POST['type'][$i];
             $datos2 = [
                 'idpreguntas' => null,
                 'idencuestas' => $proximoId,
                 'descripcion' => $value,
                 'tipo_pregunta' => $tipo
             ];

             $wpdb->insert($tabla2,$datos2);

             $i++;
        }
     }
  }


    $query = "SELECT * FROM {$wpdb->prefix}encuestas";
    $lista_encuestas = $wpdb->get_results($query,ARRAY_A);
    if(empty($lista_encuestas)){
        $lista_encuestas = array();
    }



    $query_encuestas = "SELECT COUNT(*) as 'num' FROM {$wpdb->prefix}encuestas";
    $num_encuestas = $wpdb->get_results($query_encuestas,ARRAY_A);

    $query_preguntas = "SELECT COUNT(*) as 'num' FROM {$wpdb->prefix}preguntas";
    $num_preguntas = $wpdb->get_results($query_preguntas,ARRAY_A);

    $query_respuestas = "SELECT COUNT(*) as 'num' FROM {$wpdb->prefix}respuestas";
    $num_respuestas = $wpdb->get_results($query_respuestas,ARRAY_A);
    
?>
<div class="wrap">
    <?php
        echo "<h1>" . get_admin_page_title() . "</h1>"; 
    ?>
    <a id="btn_nuevo" class="page-title-action">Añadir nueva</a>
    <br><br><br>
    <table class="wp-list-table widefat fixed striped pages">
        <thead>

            <th>Nombre</th>
            <th>Fecha Inicio</th>
            <th>ShortCode</th>
            <th>Acciones</th>
        </thead>
        <tbody id="the-list">
            <?php 
                foreach ($lista_encuestas as $key => $value){
                    $id_encuesta = $value['idencuestas'];
                    $fecha_inicio = $value['fecha_inicio'];
                    $ShortCode = $value['ShortCode'];
                    $nombre_encuesta = $value['nombre'];
                    echo "
                    <tr>
              
                    <td> $nombre_encuesta </td>
                    <td>$fecha_inicio</td>
                    <td>$ShortCode</td>
                    <td> <a  onclick='estadisticas_encuesta($id_encuesta)' class='page-title-action'>Estadisticas</a> 
                    <a id='btn_modificar' onclick='editar($id_encuesta)'  class='page-title-action'>Editar</a>
                    <a id='btn_borrar' data-id='$id_encuesta' class='page-title-action'>Borrar</a></td>
                    </tr>
                    ";
                }
            ?>
        </tbody>
    </table>
</div>

<div class="jumbotron wrap">
  <h1 class="display-4">Estadisticas Generales!</h1>
  <p class="lead">Total de Encuestas: <?php  echo $num_encuestas[0]['num'] ?> </p>
  <p class="lead">Total de Preguntas: <?php  echo $num_preguntas[0]['num'] ?></p>
  <p class="lead">Total de respuestas: <?php  echo $num_respuestas[0]['num'] ?></p>
</div>

<!-- Modal -->
<div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Nueva Encuesta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post">

<div class="modal-body">
    
          <div class="form-group">
            <label for="txtnombre" class="col-sm-4 col-form-label">Nombre de la encuesta</label>
            <div class="col-sm-8">
                <input type="text" id="txtnombre" name="txtnombre" style="width:100%">
            </div>
            <label for="txtnombre" class="col-sm-4 col-form-label">Fecha Inicio</label>
            <div class="col-sm-8">
                <input type="date" id="txt_date1" name="txt_date1" style="width:100%">
            </div>
            <label for="txtnombre" class="col-sm-4 col-form-label">Fecha Fin</label>
            <div class="col-sm-8">
                <input type="date" id="txt_date2" name="txt_date2" style="width:100%">
            </div>
          </div>
          <br>
          <hr>
          <h4> Preguntas</h4>
          <hr>
          <br>
          <table id="camposdinamicos">
            <tr>  
                <td>
                   <label for="txtnombre" class="col-form-label" style="margin-right:5px">Pregunta 1</label>
                </td>
                <td>
                    <input type="text" name="name[]" id="name" class="form-control name_list">
                </td>
                <td>
                  <select name="type[]" id="type" class="form-control type_list"  style="margin-left:5px">
                        <option value="1">SI - NO</option>
                        <option value="2"> Rango 0 - 5</option>
                        <option value="3"> Respuesta breve</option>
                  </select>
                
                </td>
                <td>
                    <button name="add" id="add" class="btn btn-success" style="margin-left:15px">Agregar mas</button>
                </td>
            </tr>
          </table>


</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
  <button type="submit" class="btn btn-primary" name="btnguardar" id="btnguardar">Guardar</button>
</div>
</form>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="modaleditar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Modificar Encuesta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post">

<div class="modal-body">
    
          <div class="form-group">
            <label for="txtnombre_1" class="col-sm-4 col-form-label">Nombre de la encuesta</label>
            <div class="col-sm-8">
                <input type="text" id="txtnombre_1" name="txtnombre_1" style="width:100%">
                <input type="text" id="id_encuesta_1" name="id_encuesta_1" style="width:100%" hidden>
            </div>
            <label for="txtnombre" class="col-sm-4 col-form-label">Fecha Inicio</label>
            <div class="col-sm-8">
                <input type="date" id="txt_date1_01" name="txt_date1_01" style="width:100%">
            </div>
            <label for="txtnombre" class="col-sm-4 col-form-label">Fecha Fin</label>
            <div class="col-sm-8">
                <input type="date" id="txt_date2_02" name="txt_date2_02" style="width:100%">
            </div>
          </div>
          <br>
          <hr>
          <h4> Preguntas</h4>
          <hr>
          <br>
          <table id="camposdinamicos2">
    
          </table>


</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
  <button type="submit" class="btn btn-primary" name="btnactualizar" id="btnactualizar">Actualizar</button>
</div>
</form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modal_grafica" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Estadisticas de Encuesta</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post">

<div id="canvas_4" name="canvas_4" class="modal-body">

</div>
<div class="modal-footer">
  <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
</div>
</form>
    </div>
  </div>
</div>