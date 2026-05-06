<?php 
//session_start();
//
//session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
 require_once 'funciones-comunes.php';
//require_once 'sesion.inc.php';
if(!isset($_SESSION['cliente'])){
    header('Location:listado-clientes.php?cartel=1');
}
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas        = 1;
$uModal         = 1;
$uSlider        = 0;
$uGui           = 1;
$iconoDisabled  = 1;
$usaZoom        =0;
$soyMovil=0;
if($caminoDispo!=""){
        $soyMovil=1;
    }
?>

<!DOCTYPE HTML>
<html>
<head>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
    <title>administraNET e-com | Comprobantes no cancelados</title>
    <style>
    .table-responsive {
        overflow-x: auto;
        max-width: 100%;
    }

    .table-responsive table {
        width: 100%;
    }
    </style>
     <?php require_once 'cabecera.php';?>
    <?php 
//    echo '<pre>'.print_r($_POST).'</pre>';
        
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $comprobantes = array();
        $sqlComprobantes="SELECT 
                        DATE_FORMAT(recibo_factura.Fecha,'%d/%m/%Y') AS  FechaB,
                        recibo_factura.Fecha,
                        recibo_factura.TipoComprobante,
                        recibo_factura.Cancelado,
                        recibo_factura.NroComprobante,
                        recibo_factura.Importe,
                        recibo_factura.CondVenta,
                        recibo_factura.Saldo
                            
                    FROM 
                        recibo_factura           
                    WHERE 
                    recibo_factura.Saldo<>0              
                    AND recibo_factura.TipoComprobante<>'INIC'
                    AND recibo_factura.TipoComprobante<>'INID'
                    AND recibo_factura.Codigo =".$_SESSION['idcliente']."
                    AND recibo_factura.Anulado ='No'
                    AND recibo_factura.Estado = 'N/Canc'
                    ORDER BY recibo_factura.Fecha ASC";
        //{recibo_factura.TipoComprobante} <> 'INIC' And {recibo_factura.Saldo} <> 0 And {cliente.Codigo}= " & id_cliente & " And ({recibo_factura.Fecha} >= " & F1 & " and {recibo_factura.Fecha} <= " & F2 & ") And {recibo_factura.Anulado} = 'No' And {recibo_factura.Estado} = 'N/Canc'"
        $hacerComp = mysqli_query($connV,$sqlComprobantes) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlComprobantes);
//        echo "<pre>". print_R($sqlComprobantes)."</pre>";
        while($comprobante = mysqli_fetch_object($hacerComp)){
            $comprobantes[] = $comprobante;
        }
    ?>
<script>
   
 // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
 // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
 $(function(){
    var contienes = $('#myTable');
        
        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
            contienes.DataTable().destroy();
        } 
    contienes.DataTable({
            searching: false,
        ordering: false,
        responsive: true,
        paging: false,
        language: {
            "emptyTable":     "No hay datos disponibles en la tabla",
            "info":           "Mostrando _START_ de _END_ de _TOTAL_ resultados",
            "infoEmpty":      "Mostrando 0 de 0 de 0 resultados",
            "infoFiltered":   "(filtrado de _MAX_ resultados)",
            "infoPostFix":    "",
            "thousands":      ",",
            "lengthMenu":     "Ver _MENU_ entradas",
            "loadingRecords": "Cargando...",
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "zeroRecords":    "No se encontraron registros coincidentes",
            "paginate": {
                "first":      "Primero",
                "last":       "Último",
                "next":       "Siguiente",
                "previous":   "Anterior"
            },
            "aria": {
                "sortAscending":  ": activar para ordenar la columna ascendente",
                "sortDescending": ": activar para ordenar la columna descendente"
            }
        },
        dom: 'Bfrtip',
        buttons: [
            'excel', 
            {
                extend: 'pdf',
                orientation: 'landscape'
            }
        ]
    });
     
     
     // aca atacch a los eventos del spinner funcionando.
    //   $("#spinner").bind("ajaxSend", function() {
    //         $(this).show();
    //     }).bind("ajaxStop", function() {
    //         $(this).hide();
    //     }).bind("ajaxError", function() {
    //         $(this).hide();
    //     });
        
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
            case '-':
                   $('#buscaFecha').hide();
                   $('#buscaNumero').hide();
                   
//                 $('#buscaNumero').css({'display':'block'});
//                 $('#buscaFecha').css({'display':'none'});
                 
                 break;
         }
         
     });
     // boton para buscar coincidencias
     $('#botonBuscar').click(function(){

        $('#spinner').show()
         var contienes = $('#myTable'), 
            campoBusca = $('#campoBusca').val(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            numeroComp = $('#numeroComp').val()
            //estadoPedido =  $('#estadoPedido').val();
            //$('formBusca').submit();

         
        $.ajax({
                type: 'POST',
                url: 'relay-comprobantes-ncancelados.php',
                data:{
                    "ajax" : "true",
                    "campoBusca" : campoBusca,
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "numeroComp" : numeroComp
                    //"estadoPedido" : estadoPedido
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
////                                    alert(response);  
$('#spinner').hide()
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            responsive:true,
                            searching:false,
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
                                
                            },
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
                    $('#spinner').hide()
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
 
// $('#verFiltros').on("click",function(){
//         $(this).toggleClass('iconoAzul');
//         $('#formBusca').toggle();
//     });
//      $('#formBusca').hide();
 
 });
 function ver_pedido(cual,nombre){
      vuelta=window.showModalDialog("ver_remito.php?codigomovimiento="+cual+"&nropedido="+nombre,"","help:no;status:no;dialogHeight:560px;dialogWidth:770px;");
   }
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content" class="noPrint">
                           
                    <div class="paneles filtroInformes">   
                                    
                                    <form id="formBusca" name="formBusca" method="POST" action="">

                                        <div class='panelesBloqueInforme'>
                                   
                                                <div class="control">
                                                    <label class="parametros" for="campoBusca">Buscar por: </label> 
                                                        <select name="campoBusca" id="campoBusca">
                                                            <option value="-">-</option>
                                                            <option value="Fecha" selected="selected">Fecha</option>
                                                            <!-- <option value="NroComprobante">Número</option> -->
                                                        </select>
                                                    
                                                </div>
                                                
                                                <div id="buscaFecha" class="control">
                                                    <label class="parametros" for="fechaDesde">Desde: </label>
                                                        <input type="date" name="fechaDesde" id="fechaDesde">
                                                        
                                                    <label class="parametros" for="fechaHasta">Hasta: </label>
                                                        <input type="date" name="fechaHasta" id="fechaHasta">
                                                </div>
                                                
                                                <div id="buscaNumero"  class="control" style="display:none">
                                                    <label for="numeroComp">Nº Comp.: </label>
                                                        <input type="text" name="numeroComp" id="numeroComp" >
                                                    
                                                </div>
                                                        
                                    </div>
                                    <div class="panelesBloqueInformeAccion">


                                    <span class="centro w100p">
                                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                                            <i class="fas fa-search fa-lg fa-fw"></i> Buscar
                                        </button>
                                    </span>

                                                    
                                    </div>
                                                        
                                </form>
                    </div>
<!--             
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div> -->

            <div id="basic-modal-content" > </div>
                <!--spinner admNET-->
                <div id="spinner" class="spinnerAdm" style="display:none;">
                    <div class="centro">
                        <img src="_img/logo-administranet-ecommerce.png">
                        <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
                    </div>
                </div>
                <!--fin spinner-->
            <div id="contiene-tabla" class="table-responsive"  >
                <h1>Comprobantes No Cancelados</h1>
           <?php if(!empty($comprobantes)):?>
               
                <!--No soy Movil -->
                <table  id="myTable" class="display" cellspacing="1" data-page-length='10'>
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Comp</th>
                                <th>N°Comprobante</th>
                                <th class="dt-right">Cancelado</th>
                                <th class="dt-right">Importe</th>
                                <th>Cond.Venta</th>
                                <th class="dt-right">Saldo</th>
                                <th class="dt-right">Saldo Acum</th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $saldoAcum =0;
                            foreach($comprobantes as $comprobante):
                                $saldo = 0;
                                switch($comprobante->TipoComprobante){
                                    case 'AJC':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;

                                    case 'REC':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;
                                    case 'NCA':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;
                                    case 'NCB':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;
                                    case 'NCC':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;
                                    case 'NCM':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;
                                    case 'NCE':
                                        $saldo = $comprobante->Saldo * (-1);
                                        break;
                                   default:
                                            $saldo = $comprobante->Saldo;
                                        break;
                                }
                                $saldoAcum +=  $saldo;
                                //if($comprobante->TipoComprobante !='REC'):
                                ?>
                                <tr>
                                    <td>
                                        <span style="display:none"><?php echo $comprobante->Fecha;?></span>
                                        <?php echo $comprobante->FechaB;?>
                                    </td>

                                    <td><?php echo $comprobante->TipoComprobante;?></td>
                                    <td><?php echo $comprobante->NroComprobante;?></td>
                                    <td class="importe"><i class="fa fa-dollar"></i><?php echo $comprobante->Cancelado;?></td>
                                    <td class="importe"><i class="fa fa-dollar"></i><?php echo $comprobante->Importe;?></td>
                                    <td ><?php echo $comprobante->CondVenta;?></td>
                                    <td class="importe"><i class="fa fa-dollar"></i><?php echo $saldo;?></td>
                                    <td class="importe"><i class="fa fa-dollar"></i><?php echo $saldoAcum; ?></td>                            
                                </tr>
                                <?php //endif;?>
                            <?php endforeach;?>
                        </tbody>
                        <tfooter>
                            <tr>
                                <td colspan="7" class="dt-right"><strong>Saldo al <?php echo date('d/m/Y');?></strong></td>
                                <td class="importe"><strong> <i class="fa fa-dollar"></i><?php echo $saldoAcum?></strong></td>
                            </tr>
                         </tfooter>   
                    </table>
               <?php endif;?>
            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
    </body>
</html>