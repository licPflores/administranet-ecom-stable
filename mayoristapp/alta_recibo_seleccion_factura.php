<?php 
//error_reporting(E_ALL);
session_start();
$caminoDispo = $_SESSION['caminoDisp'];
session_write_close();
require_once 'sesion.inc.php';
//require_once $caminoDispo.'jcart/jcart.php';


/**
 * variables de configuracion para colocar los encabezados
 */
$usaZoom    =0;
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>administraNET e-com | Recibo WEB</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    
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
    "order": [[ 0, "desc" ]]
} );    
 $(document).ready(function(){
        
    $('#myTable').DataTable();  
     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
        
     // para que se borren lo que tienen adentro las fechas   
     $('#fechaDesde').focus(function(){
         $('#fechaDesde').val('');
     });
     $('#fechaHasta').focus(function(){
         $('#fechaHasta').val('');             
     });
     $('#campoBusca').change(function(){
         var valor = $(this).val();
         // voy a corroborar que div mostrar
         $('#fechaDesde').val('dd/mm/aaaa'),
         $('#fechaHasta').val('dd/mm/aaaa'),
         $('#numeroComp').val('');
         switch(valor){
             case 'Fecha':
                   $('#buscaNumero').hide();
                   $('#buscaTipo').hide();
                   $('#buscaFecha').show(400);
                 break;
             case 'NroComprobante':
                   $('#buscaFecha').hide();
                   $('#buscaTipo').hide();
                   $('#buscaNumero').show(400);
                 break;
             case '':
                $('#buscaTipo').hide();
                $('#buscaFecha').hide();
                $('#buscaNumero').hide();
                 break;
             case 'TipoPedido':
                $('#buscaTipo').show(400);
                $('#buscaFecha').hide();
                $('#buscaNumero').hide();
                 break;
         }
         
     });
     /*
      * Tipo de Recibo 
      * @returns {undefined}
      */
     $('#tipoRecibo').change(function(){
         var valor = $(this).val();
         // voy a corroborar que div mostrar
        
         switch(valor){
             case 'imputacion':
                  // busco las facturas y bloqueo el total
                   //$('#totalRecibo').prop("disabled",true);
                    $('#botonBuscar').click();
                 break;
             case 'acuenta':
                 //no busco y solo dejo el importe a poner a cuenta.
                 //$('#totalRecibo').prop("disabled",false);
                 var contienes = $('#myTable');
                 var h1=$("h1");
                  contienes.empty();
                    if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                        contienes.DataTable().destroy();
                    }
                    h1.text(" ");
                 break;
             
         }
         
     });
     
     
     // boton para buscar coincidencias
     $('#botonBuscar').click(function(){
         var contienes = $('#myTable'), 
            campoBusca = $('#campoBusca').val(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            numeroComp = $('#numeroComp').val(),
            tipoPedido = $('#tipoPedido').val(),
            estadoPedido =  $('#estadoPedido').val();
            //$('formBusca').submit();
         
        $.ajax({
                type: 'POST',
                url: 'relay_facturas_imputar.php',
                data:{
                    "ajax" : "true",
                    "vendedor"  : "false",
                    "campoBusca" : campoBusca,
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "numeroComp" : numeroComp,
                    "tipoPedido" : tipoPedido,
                    "estadoPedido" : estadoPedido
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
//                       console.log(response);  
                    contienes.empty();
                    if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                        contienes.DataTable().destroy();
                    }
                    contienes.html(response);
                    contienes.DataTable();
                    $('#myTable tbody').on("click","td i.tecompro", agregaComp);
                    $('#myTable tbody').on("click","td i.i-chk-factura", chkFactura);
                    $("#spinner").hide();
                         
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
      /*
       * VISUALIZAR COMPROBANTE
       */
    $(document).on('click', '.verComprobante', function(event) {
        //event.preventDefault();
        var 
            codigoMovimiento    = $(this).attr('mov'),
            tipoComprobante     = $(this).attr('comprobante');
//            alert($(this).attr('mov') + ' -  ' + $(this).attr('comprobante')+' - '+ este.attr('mov')+' - '+este.attr('comprobante'));
            
        $.ajax({
                type:   'POST',
                url:    'ver_pedido.php',
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
     /*
      * Click en Check FACTURA
      * @returns {undefined}
      * 
      */
     var chkFactura= 
             function(){
                 var claseChk='fa fa-check-square fa-2x i-chk-factura' , claseCuadro='fa fa-square fa-2x i-chk-factura';
                var codmov = $(this).attr('name');
                var cadena=$('#mi-fact-'+codmov).val();
                var inputMonto = $('#mi-cantidad-'+codmov);
                var monto = $('#mi-cantidad-'+codmov).val();
                var limiteMax=parseFloat(inputMonto.prop('max'));
                var labelSaldo = $('#mi-saldo-'+codmov);
                
                 if(monto>limiteMax){
                     alert("el monto Maximo a imputar por esta factura es de $"+limiteMax);
                     inputMonto.val(limiteMax);
                     return;
                 }
                //recupero el check
                
                var chk = $('#chk-factura-'+codmov);
               // console.log("chk-fact=>"+chk.prop('checked'));
                console.log("Mi limite=>" + inputMonto.prop('max'));
                if (chk.prop('checked')===true){
                    chk.prop('checked', false);
                    $(this).removeClass(claseChk).addClass( claseCuadro );
                    inputMonto.prop( "readonly", false );
                }else{
                    chk.prop('checked', true);
                    $(this).removeClass(claseCuadro).addClass( claseChk );
                    inputMonto.prop( "readonly", true );
                    
                }
                labelSaldo.text((limiteMax-monto).toFixed(2));
                // agregar que si se borra o deschequea se vuelve a
                // colocar el valor a imputar sugerido.
                subTotalFact();
             };
             
     /*
      * Calculo del Total a Sumarizar de Las Facturas
      * ==============================================
      * @returns {undefined}
      */     
    function subTotalFact(){
        var facturas = $('input[type="checkbox"]:checked');
        var total = $('#totalRecibo');
        var arrFact = jQuery.makeArray( facturas );
//        console.log("facturas=>"+arrFact);
        total.val(0);
        $.each( arrFact, function( key, value ) {
//            console.log( key + ": " + value );
//            console.log($(value));
            var montoTotal = parseFloat(total.val());
            var id = $(value).val();
            var miMonto = parseFloat($('#mi-cantidad-'+id).val());
            total.val(miMonto+montoTotal);
          });
          
//        $( 'input[type="checkbox"]:checked' ).each(function( index, element ) {
//            console.log(index+" : "+element);
//            //console.log("valores" + $(this).val());
//        });
    }          
     
     /* Alta de Importe a Cancelar de Facturas.
      * =========================================
      * */
    var agregaComp =
            function(){
                         
            //alert('aca anduvo cuatro');

            
          
         var codmov = $(this).attr('name');
         var cadena=$('#mi-fact-'+codmov).val();
         var monto = $('#mi-cantidad-'+codmov).val();
         
         
            //$('formBusca').submit();
         console.log("codmov=>"+codmov+" cadena:=>"+cadena+" Monto:=> "+monto);   
         
        $.ajax({
                type: 'POST',
                url: 'jrecibo.php',
                data:{
                    "ajax" : "true",
                    "cadena":cadena,
                    "monto":monto,
                    "accion":"alta"
                   
                   
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
                       console.log(response);  
//                        contienes.empty();
//                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
//                            contienes.DataTable().destroy();
//                        }
//                        contienes.html(response);
//                        contienes.DataTable();
                         $("#spinner").hide();
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
      }; 
     
     // tengo que hacer submit si o si para poder guardar las cosas y luego seguir.
     /*
      * SUBMIT
      */
     $( "#form-facturas" ).submit(function( event ) {
//         var contenido = $(this).serializeArray();
//        console.log(contenido);
//         var facturas = $('input[type="checkbox"]:checked');
//        var total = $('#totalRecibo');
//        var arrFact = jQuery.makeArray( facturas );
////        console.log("facturas=>"+arrFact);
//        var arrayT =[];
//        total.val(0);
//        $.each( arrFact, function( key, value ) {
////            console.log( key + ": " + value );
////            console.log($(value));
//            var montoTotal = parseFloat(total.val());
//            var id = $(value).val();
//            var miMonto = parseFloat($('#mi-cantidad-'+id).val());
//           console.log("datos a transmitr =>" + $('#mi-fact-'+id).val());
//           arrayT[key] = $('#mi-fact-'+id).val();
//          });
//          console.log("prueba =>" + arrayT );
        //event.preventDefault();
      });
     
    // Disparo el submit
    $('#botAceptar').click(function(){
        $('#form-facturas').submit();
    });
 });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content">
            <form name="form-facturas" id="form-facturas" action="jcart/jrecibo.php" method="POST"> 
            <div class="buscador">
                
                   <div class="control">
                        <label for="tipoRecibo">Tipo:  
                            <select name="tipoRecibo" id="tipoRecibo">
                                <option value="">-</option>
                                <option value="imputacion">Imputación</option>
                                <option value="acuenta">A cuenta</option>
                                
                            </select>
                        </label>
                    </div>
                    <div class="separador25px"></div>
                    <div class="control">
                        <label for="campoBusca">Buscar por:  
                            <select name="campoBusca" id="campoBusca">
                                <option value="">-</option>
                                <option value="Fecha">Fecha</option>
                                <option value="NroComprobante">Número</option>
<!--                                <option value="TipoPedido">Tipo Pedido</option>-->
                            </select>
                        </label>
                    </div>
                    <div class="separador10px"></div>
                    <div id="buscaFecha" style="display:none" class="control">
                        <label for="fechaDesde">Desde: <input type="text" name="fechaDesde" id="fechaDesde" class="inputFecha"></label>
                        <label for="fechaHasta">Hasta: <input type="text" name="fechaHasta" id="fechaHasta" class="inputFecha"></label>
                    </div>
                    
                    <div id="buscaNumero"  class="control" style="display:none">
                            <label for="numeroComp">Nº Comprob: 
                                <input type="text" name="numeroComp" id="numeroComp" >
                            </label>

                    </div>
                    
                   
                    <div class="separador10px"></div>
                    <div class="control">
                        <button  title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo chico azul">
                        <i class="fa fa-search fa-1x" ></i>
                        </button>
<!--                        <input type="button" name="botonBuscar" class="buttons" id="botonBuscar" value="Buscar">-->
                    </div>
                    
                    
               
            </div>
            
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>
             
            <div id="contiene-tabla"  > 
               <label for="totalRecibo">Total Imputado: 
                            <i class="fa fa-dollar fa-1x" ></i>
                            <input type="number" name="totalRecibo" id="totalRecibo" readonly="readonly">
                        </label>
               
                 <h1>Comprobantes a Cobrar</h1>
                 
                <table class="display" cellspacing="1" id="myTable">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th> 
                            <th>N°Comprob.</th>        
                            <th class="right">Importe</th>
                            <th class="right">Importe NC</th>
                            <th class="right">Cancelado</th>
                            <th class="right">Saldo</th>
                            <th>Imputado</th>
                            <th>&nbsp</th>
                        </tr>
                    </thead>		
                </table>
              
        
            </div>
                <div>
                <a href="#" name="botAceptar" id="botAceptar">
                            <button class="botonNuevo grande azul">
                                    <i class="fas fa-nex"></i> Siguiente >
                            </button>
                            </a>
                </div>    
            </form>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
     <div id="basic-modal-content" > </div>
    </body>
</html>