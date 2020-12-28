jQuery(document).ready(function($){

    $("#btn_nuevo").click(function(){
        $("#modalnuevo").modal("show");
    });

    
    var i = 1;
    $("#add").click(function(){
        i++;add
        $("#camposdinamicos").append('<tr id="row'+i+'"><td><label for="txtnombre" class="col-form-label" style="margin-right:5px">Pregunta '+i+'</label></td><td> <input type="text" name="name[]" id="name" class="form-control name_list"></td><td><select name="type[]" id="type" class="form-control type_list"  style="margin-left:5px"><option value="1" selected>SI - NO</option><option value="2"> Rango 0 - 5</option></select></td><td><button name="remove" id="'+i+'" class="btn btn-danger btn_remove" style="margin-left:15px">X</button></td></tr>');
        return false;
    });

    $(document).on('click','.btn_remove',function(){
        var button_id = $(this).attr('id');
        $("#row" +button_id+"").remove();
        return false;
    });



    $(document).on('click',"a[data-id]",function(){
        var id = this.dataset.id;
        var url = SolicitudesAjax.url;
        $.ajax({
            type: "POST",
            url: url,
            data:{
                action : "peticioneliminar",
                nonce : SolicitudesAjax.seguridad,
                id: id,
            },
            success:function(){
                alert("Datos borrados");
                location.reload();
            }
        });
});




});


var estadisticas_encuesta = (id)=>{

    var url = SolicitudesAjax.url;
    jQuery(document).ready(function($){
        $("#canvas_4").empty();
        $.ajax({
            type: "POST",
            url: url,
            data:{
                action : "obtenerestadisticas",
                nonce : SolicitudesAjax.seguridad,
                id: id,
            },
            success:function(data){
                var label = []
                var datos_grafico = []
                console.log(data)
                for (let step = 0; step < data.length; step++) {
                    $("#canvas_4").append('<canvas id="migrafica'+step+'" width="100" height="50"></canvas>')
                    var ctx = document.getElementById('migrafica'+step);
                    if (data[step].tipo_pregunta == 1){
                        label = ["SI", "NO"]
                        datos_grafico = [data[step].votos_si, data[step].votos_no]
                    }

                    if (data[step].tipo_pregunta == 2){
                        label = ["0", "1", "2", "3", "4", "5"]
                        datos_grafico = [data[step].votos_0, data[step].votos_1, data[step].votos_2, data[step].votos_3, data[step].votos_4, data[step].votos_5]
                    }


                    var myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: label,
                            datasets: [{
                                label: data[step].preguntas,
                                data: datos_grafico,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                   
                }
           
            }
        }); 

    $("#modal_grafica").modal("show");}); 
}

var editar = (id)=>{
    
    var url = SolicitudesAjax.url;
    jQuery(document).ready(function($){
    $.ajax({
        type: "POST",
        url: url,
        data:{
            action : "obtenerencuesta",
            nonce : SolicitudesAjax.seguridad,
            id: id,
        },
        success:function(data){
            console.log(data)
            $("#camposdinamicos2").empty();
            $("#modaleditar").modal("show");
            console.log(data.datos_encuesta[0].idencuestas)
            $("#id_encuesta_1").val(data.datos_encuesta[0].idencuestas);
            $("#txtnombre_1").val(data.datos_encuesta[0].nombre);
            $("#txt_date1_01").val(data.datos_encuesta[0].fecha_inicio);
            $("#txt_date2_02").val(data.datos_encuesta[0].fecha_fin);
            var i = 0;
            for (let step = 0; step < data.datos_pregunta.length; step++) {
                console.log('uno')
                    i++;add
                    if (data.datos_pregunta[step].tipo_pregunta == 1){
                        $("#camposdinamicos2").append('<tr id="row'+i+'"><td><label for="txtnombre" class="col-form-label" style="margin-right:5px">Pregunta '+i+'</label></td><td> <input type="text" name="preguntas[]" id="preguntas" value="'+ data.datos_pregunta[step].idpreguntas +'" class="form-control name_list" hidden><input type="text" name="name[]" id="name" value="'+ data.datos_pregunta[step].descripcion +'" class="form-control name_list"></td><td><select name="type[]" id="type" class="form-control type_list"  style="margin-left:5px"><option value="1" selected>SI - NO</option><option value="2"> Rango 0 - 5</option></select></td></tr>');
                    }

                    if(data.datos_pregunta[step].tipo_pregunta == 2){
                        $("#camposdinamicos2").append('<tr id="row'+i+'"><td><label for="txtnombre" class="col-form-label" style="margin-right:5px">Pregunta '+i+'</label></td><td><input type="text" name="preguntas[]" id="preguntas" value="'+ data.datos_pregunta[step].idpreguntas +'" class="form-control name_list" hidden> <input type="text" name="name[]" id="name" value="'+ data.datos_pregunta[step].descripcion +'" class="form-control name_list"></td><td><select name="type[]" id="type" class="form-control type_list"  style="margin-left:5px"><option value="2" selected> Rango 0 - 5</option><option value="1">SI - NO</option></select></td></tr>');
                    }
              }


        }
    }); 

});

}