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
$usaZoom       =0;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Notas de Crédito del vendedor | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    
    
<script>
 $.extend( $.fn.dataTable.defaults, {
     searching:true,
     responsive:true,
     ordering:false,
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
    }
    
} );      
 // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
 // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
 $(document).ready(function(){ 
    
//       $('#myTable').DataTable({dom: 'Bfrtip',
//                                        buttons: [
//                                            'excel', 
//                                            {
//                                                extend: 'pdf',
//                                                orientation: 'landscape'
//                                            }
//                                        ]});  
       
     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).hide();
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
           // para que se borren lo que tienen adentro las fechas   
     
     $('#campoBusca').change(function(){
         var valor = $(this).val();
         // voy a corroborar que div mostrar
         $('#fechaDesde').val('dd/mm/aaaa'),
         $('#fechaHasta').val('dd/mm/aaaa'),
         $('#numeroComp').val('');
         switch(valor){
             case 'NroComprobante':
                   $('#buscaTipo').hide();
                   $('#buscaNumero').show(400);
                 break;
             case 'TipoPedido':
                   $('#buscaTipo').show(400);
                   $('#buscaNumero').hide();                    
                 break;
            case '1':
                   $('#buscaTipo').hide();
                   $('#buscaNumero').hide();                    
                 break;
         }
         
     });
     // boton para buscar coincidencias
     $('#botonBuscar').on("click",function(event){
        let botonBusca =$(this);
        botonBusca.prop('disabled', true);
        botonBusca.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere');
         var contienes      = $('#myTable'), 
            campoBusca      = $('#campoBusca').val(),
            fechaDesde      = $('#fechaDesde').val(),
            fechaHasta      = $('#fechaHasta').val(),
            numeroComp      = $('#numeroComp').val(),
            estadoFact    = $('#estadoFactura').val(),
            tipoPedido      = $('#tipoPedido').val(),           
            listaFact        = $('#listaTodos').val();
         
        $.ajax({
                type: 'POST',
                url: 'relay_nota_credito.php',
                data:{
                    "ajax"          : "true",
                    "vendedor"      : "true",
                    "campoBusca"    : campoBusca,
                    "fechaDesde"    : fechaDesde,
                    "fechaHasta"    : fechaHasta,
                    "numeroComp"    : numeroComp,
                    "estadoFact"  : estadoFact,
                    "tipoFact"    : tipoPedido,
                    "listaFact"      : listaFact
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
                                    //alert(response);  
                        //contienes.html('');    
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar');
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            dom: 'Bfrtip',
                                        buttons: [
                                            'excel', 
                                            {
                                                extend: 'pdf',
                                                orientation: 'landscape'
                                            }
                                        ]
                        });
                         $("#spinner").hide();
                },
                error: function(x, e) {
                    botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar');
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
       
    /*
       * VISUALIZAR COMPROBANTE MOVIL
       * @param {type} event
       * @returns {undefined}
       */
     $(document).on('click', '.verComprobante', function(event) {
        //event.preventDefault();
        var 
            codigoMovimiento    = $(this).attr('mov'),
            tipoComprobante     = $(this).attr('comprobante');
//            alert($(this).attr('mov') + ' -  ' + $(this).attr('comprobante')+' - '+ este.attr('mov')+' - '+este.attr('comprobante'));
            
        $.ajax({
                type:   'POST',
                url:    'ver_pedido-movil.php',
                data:{
                        "ajax":"true",
                        "codigomovimiento": codigoMovimiento,
                        "comprobante": tipoComprobante
                },
                success: function(response){
//                    alert(response);
                    $('#basic-modal-content').html('');
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
                    //return false;
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
     
     //esta es la forma de hacer que ande sin tanto complicaion    
//    $('#verFiltros').on("click",function(){
//         $(this).toggleClass('iconoAzul');
//         $('#formBusca').toggle();
//     });
//    $('#formBusca').hide(); 
<?php if($_SESSION['tipousuario']=="cliente"):?>
     $('#dlistatodos').hide();
      
      // seleccionar la o
<?php endif;?>      
 });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content">
        

            <div class="paneles filtroInformes">          
               <h1>Filtros <span ><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
                <form id="formBusca" name="formBusca" method="POST" action="">
            <div class='panelesBloqueInforme'>
                <!-- <label>Filtros:</label> <i class="fa fa-filter fa-lg " id="verFiltros"></i> -->
                
                    <div class="control">
                        <label class="parametros" for="estadoPedido">Clientes:   </label>
                            <select name="listaTodos" id="listaTodos">
                            <?php if($_SESSION['tipousuario']=="vendedor"):?>
                                <option value="cliente">Seleccionado</option>
                                <option  selected value="todos">Todos </option>
                                <?php else:?>
                                    <option  selected value="cliente">Seleccionado</option>
                                    <option value="todos">Todos </option>
                                <?php endif;?>   
                            </select>
                       
                    </div>
                    
                    
                    <div id="buscaFecha"  class="control">
                        <label class="parametros" for="fechaDesde">Desde: </label>
                            <input type="date" name="fechaDesde" id="fechaDesde">
                            
                        <label class="parametros" for="fechaHasta">Hasta: </label>
                            <input type="date" name="fechaHasta" id="fechaHasta">
                    </div>

                   <div class="control">
                        <label class="parametros" for="estadoFactura">Estado:</label>  
                            <select name="estadoFactura" id="estadoFactura">
                                <option value="">-</option>
                                <option value="Canc">Cancelada</option>
                                <option value="N/Canc">No Cancelada</option>
                                
                            </select>
                        
                    </div>
                    
                    <div class="control">
                        <label class="parametros" for="campoBusca">Buscar por: </label> 
                            <select name="campoBusca" id="campoBusca">
                            <option value="1">-</option>
                                <option value="NroComprobante">Número</option>
                                <option value="TipoPedido">Tipo Comprobante</option>
                            </select>
                        
                    </div>
                    

                    
                    <div class="control">
                    <div id="buscaNumero"  class="control" style="display:none">
                        <label for="numeroComp">Nº Comp.: </label>
                            <input type="text" name="numeroComp" id="numeroComp" >
                        
                    </div>
                    </div>
                        

                    <div class="control">
                    <div id="buscaTipo" class="control" style="display:none">
                        <label for="tipoPedido">NC: 
                            <select id="tipoPedido" name="tipoPedido" >
                                <option value="-">-</option>
                                <option value="NCA">NCA</option>
                                <option value="NCB">NCB</option>
                                <option value="NCC">NCC</option>
                                <option value="NCM">NCM</option>
                                <option value="NCE">NCE</option>
                            </select>
                        </label>
                        
                    </div>
                    </div>
</div>
                 
                <div class="panelesBloqueInformeAccion">
                        <span class="centro w100p">
                            <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                                <i class="fas fa-check fa-lg fa-fw"></i> Buscar
                            </button>
                        </span>
                    </div>

                    
               </form>
            </div>
            
            <!-- <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div> -->
            <div id="contiene-tabla" >
                <h1>Lista de Notas de Crédito</h1>        
            
                <table class="display" cellspacing="1" id="myTable"></table>
        </div>
    </div>
    <?php require_once 'footer.php';?>   
    
    </div>
    <?php if(isset($_REQUEST['cartel'])):?>
        <script>
            $(document).ready(function(){
        //        $('#basic-modal-content').empty();
        //        $('#basic-modal-content').html(response);
                //$('#basic-modal-content').maxHeight = 200,
                $('#basic-modal-content').modal({
                    minHeight:  300,
                    minWidth:   300
                });
            });
        </script>
    <?php endif;?>
    <div id="basic-modal-content" > </div>
    </body>
</html>