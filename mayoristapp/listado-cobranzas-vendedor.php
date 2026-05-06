<?php require_once 'sesion.inc.php';?>
<?php 
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$usaZoom=0;

$codPuesto = $objVendedor->id_puesto; 
    // echo 'camino dispo',var_dump($caminoDispo);
    $whereViajante="";
    if($codPuesto!=1){
        $whereViajante= " AND viajantes.CodViajante=".$objVendedor->CodViajante.PHP_EOL;
    }
    // buscando vendedores rapido.
    $sql = "SELECT viajantes.CodViajante AS valor,"
                    . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
                    . " FROM viajantes"
                    . " WHERE viajantes.Anulado='No'" 
                    . $whereViajante               
                    . " ORDER BY texto ASC";
    
    $hacerVendedor = mysqli_query($connV,$sql);
    $arrVendedores = array();
    if($hacerVendedor){
        while($viajante = mysqli_fetch_assoc($hacerVendedor)){
            $arrVendedores[] = $viajante;
        }
    } 
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>cobranzas estadisticas | administraNET</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    
     <?php require_once 'cabecera.php';?>
    <?php 
      
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $pedidos = array();
        
    ?>
<script>
    

$.extend( $.fn.dataTable.defaults, {
    "language": {
        "emptyTable":     "No data available in table",
        "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
        "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
        "infoFiltered":   "(filtered from _MAX_ total entries)",
        "infoPostFix":    "",
        "thousands":      "",
        "lengthMenu":     "Ver _MENU_ entradas",
        "loadingRecords": "Loading...",
        "processing":     "Processing...",
        "search":         "Buscar:",
        "zeroRecords":    "No matching records found",
        "paginate": {
            "first":      "Primero",
            "last":       "Ultimo",
            "next":       "Siguiente",
            "previous":   "Anterior"
        },
        "aria": {
            "sortAscending":  ": activate to sort column ascending",
            "sortDescending": ": activate to sort column descending"
        }
    },
    "searching": true,
    "ordering": false,
    "paging": true,
    "responsive":false
} );

 $(document).ready(function(){

    var buttonCommon = {
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, ],
                    format: {
                        body: function(data, row, column, node) {
                            // Strip $ from salary column to make it numeric
                            //                    return column === 5 ?
                            //                        data.replace( /[$,]/g, '' ) :
                            //                        data;
                            //                    console.log({data});
                            //                    console.log({row}); 
                            //                    console.log({column});
                            if (!isNaN(data)) {
                                // console.log("data formateado: "+dinero.format(data));

                                data = dinero.format(data);
                                //console.log("data formateado: "+data);
                                //data=numerito.format(data);
                                //console.log("data formateado: "+data);

                                //b=a.replace(/ /g, "");
                                //                        data = '$ '+data;
                                //                        console.log("data sin el espacio del pesos"+data);

                            }
                            // if (column == 12) {
                            //     console.log('soy la columna del anulado', data, column);
                            //     var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                            //     //anulado = anulado.replace('</strong>,', '');
                            //     // console.log("data coma x punto: "+numero);
                            //     data = anulado;
                            // }
                            return data;
                        },
                        footer: function(data, row, column, node) {

                            if (!isNaN(data)) {
                                // console.log("data formateado: "+dinero.format(data));

                                const arrColumnasNumero = [5, 6, 7];
                                // console.log('soy la columna debo ingresar',arrColumnasNumero.includes(column),'columna:',column);
                                if (arrColumnasNumero.includes(column)) {
                                    data = dinero.format(data);

                                }

                            }
                            return data;
                        }
                    }
                }
            };
            var buttonCommonExcel = {
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7],
                    format: {
                        body: function(data, row, column, node) {

                            const arrColumnasNumero = [1,2, 3, 4,5,6,7];

                            if (arrColumnasNumero.includes(column)) {

                                var numero = data.replace(/[$.]/g, '');

                                numero = numero.replace(',', '.');
                                // console.log("data coma x punto: "+numero);
                                data = numero;


                            }
                            // console.log('soy la columna del anulado',data,column);
                            // if (column == 12) {
                            //     console.log('soy la columna del anulado', data, column);
                            //     var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                            //     //anulado = anulado.replace('</strong>,', '');
                            //     // console.log("data coma x punto: "+numero);
                            //     data = anulado;
                            // }
                            return data;
                        },
                        footer: function(data, row, column, node) {
                            // console.log('datos del pie',data,row,column,node);
                            const arrColumnasNumero = [1,2, 3, 4,5,6,7];

                            if (arrColumnasNumero.includes(row)) {

                                data = data.replace(/[$.]/g, '');
                                data = data.replace(',', '.');
                            }
                            return data;
                        }
                    }
                }
            };


     // funcion para las comas
     const today = new Date();

    // Establecer el día actual
    const day = String(today.getDate()).padStart(2, '0');
    const month = String(today.getMonth() + 1).padStart(2, '0'); // Enero es 0
    const year = today.getFullYear();

    // Crear la fecha en formato YYYY-MM-DD
    const currentDate = `${year}-${month}-${day}`;
    const firstOfMonth = `${year}-${month}-01`;

    // Asignar las fechas a los inputs
    $('#fechaDesde').val(firstOfMonth);
    $('#fechaHasta').val(currentDate);
        
                     
     // aca atacch a los eventos del spinner funcionando.
     var botonGuardar= $('#botonBuscar');
     var textoEspere = '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere...';
     var textoBuscar =  '<i class="fas fa-check fa-lg fa-fw"></i> Buscar';
      
        
     
        // boton para buscar coincidencias
     $('#botonBuscar').click(function(){
                        // console.log(botonGuardar); 
         var tablaCobranza = $('#myTableCobranza'),
            nombreCobranza    = "#myTableCobranza",            
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            viajante = $('#filtraVendedor').val(),
            viajanteTexto  = $('#filtraVendedor option:selected').text();
            datasetGrafico="",
            grafico = 0,
            tipo = $('#campoPeriodo').val();

        var tituloListado = "",
                    mensajeTop = "",
                    nombreInforme = "";
                     

        var datosLogin = obtenerUsuarioLogueado();       
            console.log(fechaDesde);
            console.log(fechaHasta);
            botonGuardar.prop('disabled',true);
            botonGuardar.html(textoEspere);
            
            if(tipo==""){
                alert("Debe seleccionar un período de resumen");
              return false;
               $('#campoPeriodo').focus();
            }
            if(fechaDesde==""){
                alert("Debe seleccionar una fecha desde primaria");
              return false;
              $('#fechaDesde').focus();
            }
            if(fechaHasta==""){
                alert("Debe seleccionar una fecha hasta secundaria");
              return false;
              $('#fechaHasta').focus();
            }
            var fecha = new Date();
            // antes de empezar voy a ir generando de a un informe a la vez
            var fechaFormateada = fecha.getFullYear() + '_' +
                ("0" + (fecha.getMonth() + 1)).slice(-2) + '_' +
                ("0" + fecha.getDate()).slice(-2) + '_' +
                ("0" + fecha.getHours()).slice(-2) + '_' +
                ("0" + fecha.getMinutes()).slice(-2) +
                ("0" + fecha.getSeconds()).slice(-2);
            const today = new Date();
            const day = String(today.getDate()).padStart(2, '0');
            const month = String(today.getMonth() + 1).padStart(2, '0'); // Los meses empiezan desde 0
            const year = today.getFullYear();

            const formattedDate = `${day}/${month}/${year}`;
         console.log('fecha formateada',fechaFormateada);
        $.ajax({
                type: 'GET',
                url: 'informes-json/cobranza_lista_vendedor_resumen.php',
                data:{
                    "traeCobranza" : 1,
                    "codViajante":viajante,                    
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,                   
                    "tipo" :  tipo                    
                    
                },
                success: function(response){ 
                    //console.log(response);
                    botonGuardar.prop('disabled',false);
                    botonGuardar.html(textoBuscar);
                    var respuesta  = response;
                    // console.log(respuesta);

                   if(respuesta.estado==="vacio"){
                        var trCampos="<tr><td>No se encontraron resultados</td></tr>";
                        tablaCobranza.find("tbody").empty();
                        tablaCobranza.find("tbody").append(trCampos);
                    }
                    if(respuesta.estado==="ok"){

                        // *tabla
                        if ( $.fn.dataTable.isDataTable(tablaCobranza)) {
                            tablaCobranza.DataTable().destroy();
                        }
                        var html = respuesta.tablaHtml;
                        var grafico = respuesta.jsonGrafico;

                        tablaCobranza.find("table").empty();
                        tablaCobranza.html(html);
                        // titulos de las tablas
                        // console.log('la respuesta',respuesta);
                        // console.log(respuesta.htmlTabla);
                        // console.log(html);
                        
//                        console.log("itotal:===>"+itotal);
//                        console.log(contienesVentas.html());
                            nombreInforme += "listado_cobranza_vendedores_" + fechaFormateada;
                            tituloListado += "Listado de Cobranza";
                            mensajeTop += " Vendedor: "+viajanteTexto+"\n";
                            mensajeTop += " Rango: " + fechaDesde + " al " + fechaHasta + "\n";
                            mensajeTop += " Emitido: " + formattedDate + "\n";
                            mensajeTop += " Generado por: " + datosLogin.vendedor.nombre_usuario + " " + datosLogin.vendedor.apellido_usuario + "\n";
                         var tt = tablaCobranza.DataTable({
                            "ordering":true,
                            
                           dom: 'lBfrtip',
                           buttons: [

                                $.extend(true, {}, buttonCommonExcel, {
                                    extend: 'excelHtml5',

                                    footer: true,
                                    title: tituloListado,
                                    filename: nombreInforme,
                                    messageTop: mensajeTop,
                                    pageSize: 'LEGAL',
                                    orientation: 'landscape'

                                }),



                                $.extend(true, {}, buttonCommon, {
                                    extend: 'pdfHtml5',
                                    title: tituloListado,
                                    filename: nombreInforme,
                                    messageTop: mensajeTop,
                                    footer: true,

                                    pageSize: 'A3',
                                    orientation: 'landscape'

                                })

                                ],
//                            "order":[[0,'asc'],[itotal,'desc']]
                           "order":[[0,'desc']]
                            
                        }); 
                        
                        // ordenamiento con la columna de indices
                        tt.on( 'order.dt search.dt', function () {
                            tt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                                cell.innerHTML = i+1;
                            } );
                            
                        } ).draw();
                        
                         
                        if(grafico==1){
                            /*
                             * GRAFICO BARRA
                             * **/
                             /*GRAFICO*/
                            drawChart(pepe.gdata,pepe.goption,"ColumnChart","graficoVentas"); 

                            /**
                             * GRAFICO DE TORTA
                             * */
                            /* parametros*/
                            //drawChart(pepe.gdataT,pepe.goptionT,"PieChart","graficoVentasRubroT"); 
                        }   
                    }
                },
                error: function(x, e) {
                    botonGuardar.prop('disabled',false);
                    botonGuardar.html(textoBuscar);
                        var s = x.status, 
                                m = 'Ajax error: ' ; 
                        if (s === 0) {
                                m += 'Check your network connection.' + x.status + e;
                        }
                        if (s === 404 || s === 500) {
                                m += s;
                        }
                        if (e === 'parsererror' || e === 'timeout') {
                                m += e;
                        }
                        alert(m);
                }
            });
//           
    
      });
});
     
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div class="paneles filtroInformes">
            <h1>Parametros <span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
                    
                <form id="formBusca" name="formBusca" method="POST" action="">
                <div class='panelesBloqueInforme'>
                <div class="control">
                        <label for="filtraVendedor">Vendedor / Viajante:  
                            <select name="filtraVendedor" id="filtraVendedor">
                                
                                <?php if($codPuesto==1):?>
                                    <option value="todos" >- Todos -</option>
                                    <?php 
                                    if(!empty($arrVendedores)){
                                        foreach($arrVendedores as $vendedor){
                                        echo '<option value="'.$vendedor['valor'].'">'.$vendedor['texto'].'</option>'.PHP_EOL;

                                        }
                                    }
                                    ?>    
                                <?php endif;?>    
                                <?php if($codPuesto!=1):?>
                                <?php    
                                        if(!empty($arrVendedores)){
                                            foreach($arrVendedores as $vendedor){
                                                echo '<option selected="selected"  value="'.$vendedor['valor'].'">'.$vendedor['texto'].'</option>'.PHP_EOL;

                                            }
                                        }
                                    ?>
                                <?php endif;?>    
                                
                            </select>
                        </label>
                    </div>
                    <div class="control">
                        <label for="campoPeriodo" class="parametros">Periodo:  
                            <select name="campoPeriodo" id="campoPeriodo" class="param">
                                
                                <option selected="selected" value="0">Mensual</option>
                                <!-- <option value="1">Totalizado</option> -->
                                
                            </select>
                        </label>
                    </div>
                    <div id="buscaFecha" class="control">
                        <label for="fechaDesde" class="parametros">Desde: <input type="date" name="fechaDesde" id="fechaDesde"></label>
                        <label for="fechaHasta" class="parametros">Hasta: <input type="date" name="fechaHasta" id="fechaHasta"></label>
                    </div>
                    
                    
                    
                    
                    
                   
<!--                   <div class="control">
                         
                         <input type="checkbox" name="aceptaGrafico" id="aceptaGrafico" value="si">
                         <label for="aceptaGrafico">  Ver gráficos <i class="fa fa-bar-chart fa-1x" ></i> </label>
                    </div>-->
                    <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                            <i class="fas fa-check fa-lg fa-fw"></i> Buscar
                        </button>
                    </span>
                </div>
                        </div> 
               </form>
            </div>
            
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla"  > 
                <h1>Cobranzas por periodo</h1>
                <!-- <h2 class="alignLeft"><?php echo $objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario;?></h2> -->
                <table class="display" cellspacing="1" id="myTableCobranza" style="width:99%">
                    <thead></thead>
                    <tbody></tbody>
                    <tfoot></tfoot>
                </table>
                <h3 class="alignLeft">Gráfico</h3>
                <div id="graficoCobranzas"></div>
                                
                

            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     <div id="basic-modal-content"> </div>
    </body>
</html>