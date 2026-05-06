<?php 
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

//session_start();
//   
//    session_write_close();
//    require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
require_once 'funciones-comunes.php';
if(!isset($_SESSION['cliente'])){
    header('Location:listado-clientes.php?cartel=1');
}

/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$usaZoom    = 0;
$iconoDisabled = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>administraNET e-com | Cuenta corriente</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    <?php 
//    echo '<pre>'.print_r($_POST).'</pre>';
        $hoy = date("Y-m-d");
        $time = strtotime("-1 year", time());
        $fdesde = date("Y-m-d", $time);
//        vamos a buscar los pedidos de acuerdo al cliente y al estado 
        $ctaCte = array();
        $sqlCtaCte="SELECT 
                            DATE_FORMAT(cuentacliente.Fecha,'%d/%m/%Y') AS FechaB,
                            DATE_FORMAT(cuentacliente.Fecha,'%Y%m%d') AS Fecha,
                            cuentacliente.TipoComprobante,
                            cuentacliente.NroComprobante,
                            cuentacliente.CondVenta,
                            cuentacliente.ImporteVenta AS Debito,
                            cuentacliente.ImporteCobro AS Credito,
                            cuentacliente.saldo,
                            DATE_FORMAT(cuentacliente.Vencimiento,'%d/%m/%Y') AS Vencimiento,
                            cuentacliente.Vencido,
                            cuentacliente.Estado,
                            cuentacliente.Anulado,
                            cuentacliente.Recibo,
                            cuentacliente.NroFactura,
                            cuentacliente.Detalle
                            
                    FROM 
                        cuentacliente
           
                    WHERE 
                    cuentacliente.`Codigo`=".$_SESSION['idcliente']."
                    ORDER BY cuentacliente.id_cuentacliente DESC ";
        $hacerCta = mysqli_query($connV,$sqlCtaCte) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlCtaCte);
//        echo $sqlPedido.'<br>';
        while($ctaCteObj = mysqli_fetch_object($hacerCta)){
            $ctaCte[] = $ctaCteObj;
        }
    ?>
<script>
   
 // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
 // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
 $(document).ready(function(){ 
         
        var contienes = $('#myTable');
        
        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
            contienes.DataTable().destroy();
        }
       
        contienes.DataTable({
            searching:false,
            ordering:false,
            responsive:true,
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
             
            "dom": 'lBfrtip',
            buttons: [
                'excel', 
                {
                    extend: 'pdf',
                    orientation: 'landscape'
                }
            ]
        });
        
        
     // aca atacch a los eventos del spinner funcionando.
      $("#spinner").bind("ajaxSend", function() {
            $(this).show();
        }).bind("ajaxStop", function() {
            $(this).hide();
        }).bind("ajaxError", function() {
            $(this).hide();
        });
        
           // para que se borren lo que tienen adentro las fechas   
    // $( "#fechaDesde" ).datepicker({ dateFormat: "dd/mm/yy" });
     //$( "#fechaHasta" ).datepicker({ dateFormat: "dd/mm/yy" });  
     // para que se borren lo que tienen adentro las fechas   
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
//         $('#spinner').toggle();
         var contienes = $('#myTable'), 
            campoBusca = $('#campoBusca').val(),
            fechaDesde = $('#fechaDesde').val(),
            fechaHasta = $('#fechaHasta').val(),
            numeroComp = $('#numeroComp').val();
//            estadoPedido =  $('#estadoPedido').val();
            //$('formBusca').submit();
         console.log("fecha desde{"+fechaDesde+"}");
         console.log("fecha desde{"+fechaHasta+"}");
        $.ajax({
                type: 'POST',
                url: 'relay-ctacte.php',
                data:{
                    "ajax" : "true",
                    "campoBusca" : campoBusca,
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "numeroComp" : numeroComp
//                    "estadoPedido" : estadoPedido
                    
                },
                success: function(response) {
//                        // Refresh the cart display after a successful Ajax request
//                                    alert(response);  
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        //contienes.tablesorter({widthFixed: false, widgets: ['zebra']});
                        //contienes.tablesorterPager({container: $("#pager")});
                        contienes.DataTable({
                            searching:false,
                            responsive: true,
                             ordering:true,
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
                                },
                               
                                dom: 'lBfrtip',
                                        buttons: [
                                            'excel', 
                                            {
                                                extend: 'pdf',
                                                orientation: 'landscape'
                                            }
                                        ]
                            }
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
//    $('#verFiltros').on("click",function(){
//         $(this).toggleClass('iconoAzul');
//         $('#formBusca').toggle();
//     });
//    $('#formBusca').hide();
        $("#formBusca").on("submit",function(){
            console.log("entre en el formulario");
            event.preventDefault();
        });
    });
// function ver_pedido(cual,nombre){
//      vuelta=window.showModalDialog("ver_pedido.php?codigomovimiento="+cual+"&nropedido="+nombre,"","help:no;status:no;dialogHeight:660px;dialogWidth:770px;");
//   }
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
                                            <!--                    <div class="control" >
                                                                    Estado: 
                                                                        <select name="estadoPedido" id="estadoPedido">
                                                                            <option value="">-</option>
                                                                            <option value="En Remito">En Remito</option>
                                                                            <option value="Pendiente">Pendiente</option>
                                                                        </select>
                                                                        selected="selected"
                                                                    
                                                                </div>-->
                                                                
                                                    <div class='panelesBloqueInforme'>
                                                            <div class="control">
                                                                <label class="parametros" for="campoBusca">Buscar por: </label> 
                                                                    <select name="campoBusca" id="campoBusca">
                                                                        <option value="-">-</option>
                                                                        <option value="Fecha" selected="selected">Fecha</option>
                                                                        <option value="NroComprobante">Número</option>
                                                                    </select>
                                                                
                                                            </div>
                                                            
                                                            <div id="buscaFecha"  class="control">
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
                                                                    <button title="Buscar" alt="Buscar" type="submit" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                                                                        <i class="fas fa-search fa-lg fa-fw"></i> Buscar
                                                                    </button>
                                                                </span>
                                                            </div>

                                                                
                                                        </form>
                                    </div>
            
            <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div>
             <div id="contiene-tabla"  style="float:left;" >
                 <h1>Cuenta Corriente</h1>
           <?php if(!empty($ctaCte)):?>
            <table class="display" cellspacing="1" id="myTable" data-page-length='10'>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Comp</th>
                        <th>Número</th>
                        <th>Cond.Vta</th>
                        <th class="dt-right">Debitos</th>
                        <th class="dt-right">Creditos</th>
                        <th class="dt-right">Saldo</th>
                        <th>Venc.</th>
                        <th>Vencido</th>
                        <th>Estado</th>
                        <th>Anul.</th>
                        <th>Recibo</th>
                        <th>Factura Nd/NC</th>

                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ctaCte as $renglon):?>
                        <tr>
                            <td class="dt-nowrap" data-order="<?php echo $renglon->Fecha;?>">
                                
                                <?php echo $renglon->FechaB;?>
                            </td>
                            <td><?php echo $renglon->TipoComprobante;?></td>
                            <td class="dt-nowrap comprobante"><?php echo $renglon->NroComprobante;?></td>
                            <td><?php echo $renglon->CondVenta;?></td>
                            <td class="importe">$<?php echo $renglon->Debito+0.00;?></td>
                            <td class="importe">$<?php echo $renglon->Credito+0.00;?></td>
                            <td class="importe">$<?php echo $renglon->saldo+0.00;?></td>
                            <td><?php echo $renglon->Vencimiento;?></td>
                            <td><?php echo $renglon->Vencido;?></td>
                            <td><?php echo $renglon->Estado;?></td>
                            <td><?php echo $renglon->Anulado;?></td>
                            <td class="dt-nowrap comprobante"><?php echo $renglon->Recibo;?></td>
                            <td class=" dt-nowrap comprobante"><?php echo $renglon->NroFactura;?></td>
     
                        </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

            <?php endif;?>
            </div>
        </div>
 
        <?php require_once 'footer.php';?>   
    
    </div>
    </body>
</html>
