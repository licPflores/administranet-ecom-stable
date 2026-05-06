<?php 
//session_start();
//
//session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$usaZoom    = 0;
if (isset($_SESSION['cliente'])) {
    if (is_object($_SESSION['cliente'])) {
        $clienteObj = $_SESSION['cliente'];
    } else {
        $clienteObj = $_SESSION['cliente'][0];
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Premios historial de puntos | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    
    
<script>

  $(document).ready(function() { 
            
     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
        
     // para que se borren lo que tienen adentro las fechas   
//     $( "#fechaDesde" ).datepicker({ dateFormat: "dd/mm/yy" });
//     $( "#fechaHasta" ).datepicker({ dateFormat: "dd/mm/yy" });
//     
//     $('#fechaDesde').focus(function(){
//         $('#fechaDesde').val('');
//     });
//     $('#fechaHasta').focus(function(){
//         $('#fechaHasta').val('');             
//     });
     $('#campoBusca').change(function(){
         var valor = $(this).val();
         // voy a corroborar que div mostrar
//         $('#fechaDesde').val('dd/mm/aaaa'),
//         $('#fechaHasta').val('dd/mm/aaaa'),
         $('#numeroComp').val('');
         switch(valor){
             case 'Fecha':
//                 $('#buscaNumero').css({'display':'none'});
//                 $('#buscaFecha').css({'display':'block'});
                   $('#buscaNumero').hide();
                   $('#suggestions').hide();
                   $('#buscaFecha').show(400);
                   
                   
                 break;
             case 'NroComprobante':
                   $('#buscaFecha').hide();
                   $('#buscaNumero').show(400);
                   
//                 $('#buscaNumero').css({'display':'block'});
//                 $('#buscaFecha').css({'display':'none'});
                 
                 break;
         }
         
     });
     
     
     // boton para buscar coincidencias
     $('#botonBuscar').click(function(){
         var contienes = $('#myTable'), 
            campoBusca = $('#campoBusca').val(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            queInforme = $('#tipoInforme').val(),
            filtraCliente=$('#filtraCliente').val();
            
//            numeroComp = $('#numeroComp').val(),
           // tipoRemito = $('#tipoRemito').val(),
            //estadoPedido =  $('#estadoRecibo').val();
            //$('formBusca').submit();
            if(campoBusca==="Fecha"&&(fechaDesde===''||fechaHasta==='')){
                console.log("fechaDesde::: "+fechaDesde );
                console.log("fechaDesde::: "+fechaHasta );
                alert("Debe completar la fecha");
                $('#fechaDesde').focus();
                return false;
            }
        $.ajax({
                type: 'GET',
                url: 'json/json-premios-informes.php',
                data:{
                    "consultaHistorial" : 1,                    
                    "desde" : fechaDesde,
                    "hasta" : fechaHasta,
                    "cliente":filtraCliente,
                    "queInforme":queInforme
                    
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
//                    console.log(response);
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching: false,
                            responsive:false,
//                            ordering:false,
                            "language": {
                                
                            
                                "emptyTable":     "No data available in table",
                                "info":           "Viendo _START_ de _END_ de _TOTAL_ resultados",
                                "infoEmpty":      "Viendo 0 de 0 de 0 resultados",
                                "infoFiltered":   "(filtrado de _MAX_ resultados)",
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
                                
                            }, "order": [[ 0, "asc" ]],                           
                                dom: 'Bfrtip',
                                        buttons: [
                                            'excel', 
                                            {
                                                extend: 'pdf',
                                                orientation: 'landscape'
                                            }
                                        ]
                        });

                },
                error: function(x, e) {
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
      });
      
     //ventana modal para mostrar el contenido sin tener que usar una ventana abierta y se vea el codigo
      $(document).on('click', '.verComprobante', function(event) {
      
            var codigoMovimiento = $(this).attr('mov'),
                tipoComprobante = $(this).attr('comprobante');
            
        $.ajax({
                type:   'GET',
                url:    'ver_remito.php',
                data:{
                        "ajax":"true",
                        "codigomovimiento": codigoMovimiento,
                        "comprobante": tipoComprobante
                },
                success: function(response){
                    $('#basic-modal-content').empty();
            //                                                si hubiera una foto uno o foto dos
            //                                                lo correcto seria crear el objeto 
            //                                                y luego modificar el src a traves
            //                                                de ajax....
                    $('#basic-modal-content').html(response);
            //                                                $('#basic-modal-content').modal();
                    $('#basic-modal-content').modal({
                        //minHeight : 200,
                        maxWidth : 950,
                        minHeight: 700
                    });
                    $('#imprimir').click(function(){
                        $(this).hide();
                        window.print();
                    });    
                    return false;
                },
                error: function(x, e) {
                        var s = x.status, 
                                m = 'Ajax error: ' ; 
                        if (s === 0) {
                                m += 'Check your network connection.';
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
     
     });
     $('#verFiltros').on("click",function(){
         $(this).toggleClass('iconoAzul');
         $('#formBusca').toggle();
     });
      //$('#formBusca').hide();
      
 });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content" class="noPrint">
           
            <div class="buscador" id="barraBusca">
                 <label><i class="fas fa-sliders-h fa-lg iconoAzul" id="verFiltros"></i> Filtros</label> 
                <form id="formBusca" name="formBusca" method="POST" action="">
                    
                    <div class="control">
                        <label for="filtraCliente">Clientes:  
                            <select name="filtraCliente" id="filtraCliente">
                                <option value="todos" >- Todos -</option>
                                <?php if(isset($clienteObj)):?>
                                    <?php if($_SESSION["usa_id_manual"]=="Si"):?>
                                    <option selected="selected"  value="<?php echo $clienteObj->Codigo;?>"><?php echo $clienteObj->cliente.' (Cod:'.$clienteObj->id_manual_cli.')';?></option>
                                    <?php endif;?>
                                    <?php if($_SESSION["usa_id_manual"]!="Si"):?>
                                    <option selected="selected"  value="<?php echo $clienteObj->Codigo;?>"><?php echo $clienteObj->cliente.' (Cod:'.$clienteObj->Codigo.')';?></option>
                                    <?php endif;?>
                                <?php endif;?>
                            </select>
                        </label>
                    </div>
                  <div class="control">
                        <label for="tipoInforme">Tipo:
                            <select name="tipoInforme" id="tipoInforme">
                                <option value="historial" selected>Historial de canje</option>
                                <option value="listadoPuntos">Puntos x cliente</option>
                                
                                
                            </select>
                        </label>
                    </div>
                    <div class="control">
                        <label for="campoBusca">Buscar por:
                            <select name="campoBusca" id="campoBusca">
                                <option value="">-</option>
                                <option value="Fecha" selected="selected">Fecha</option>
                                
                                
                            </select>
                        </label>
                    </div>
                  
                    <div id="buscaFecha" style="display: block;" class="control">
                        <label for="fechaDesde">Desde: 
                            <input type="date" name="fechaDesde" id="fechaDesde" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d'). ' - 7 days'));?>">
                        </label>
                         <?php if($caminoDispo!=""):?>
                            <br>
                         <?php endif;?>
                        <label for="fechaHasta">Hasta: 
                           <input type="date" name="fechaHasta" id="fechaHasta" value="<?php echo date('Y-m-d');?>">
                        </label>
                    </div>
                    
                    
                    
                  
                    <div class="control">
                        <button  title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo grande azul">
                        <i class="fas fa-search fa-lg" ></i> buscar
                        </button>

                    </div>
                    
               </form>
            </div>
            
           <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla"  style="float:left;" >
           
                <h1>Historial de puntos 
<!--                    <a href="listado-clientes.php?frm=1" style="float:right;margin:3px">
                    <button class="botonNuevo grande azul">
                        <i class="fa fa-plus fa-lg"></i> 
                    </button>
                </a>-->
                </h1>
                
                <table class="display" cellspacing="1" id="myTable"></table>
           
                 
        
            </div>
        
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     <script >
                 // iniciar la primera lista de recibos.
                 
     </script>
    </body>
</html>
