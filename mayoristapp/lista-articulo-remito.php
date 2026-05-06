<?php 
//session_start();
//
//session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';
$caminoDispo = $_SESSION['caminoDisp'];
$usaIdManual = $_SESSION["usa_id_manual"];
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$usaZoom    = 0;
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Articulos Remitados | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon"/>
     <?php require_once 'cabecera.php';?>
    <style media="print">
        .noPrint{ display: none; }
        .yesPrint{ display: block !important; }
    </style>
    <?php 
//    echo '<pre>'.print_r($_POST).'</pre>';
        
//        vamos a buscar los pedidos de acuerdo al cliente  y al vendedor 
        $condicion ="";    
        if(isset($_SESSION['vendedor'])){
                $objVendedor = $_SESSION['vendedor'];
                $condicion .= " AND comp_ped.idUsuario=".$objVendedor->id_usuario;
        }
        if(isset($_SESSION['idcliente'])){
            $condicion =" AND comp_ped.Codigo=" .$_SESSION['idcliente'];
        }
        
        $remitos = array();
        $sqlPedido="SELECT 
                            stock.id_manual,
                            stock.IDArt,
                            stock.Descripcion,
                            CONCAT('(',stock.id_manual,') ',stock.Descripcion) AS artManual,
                            CONCAT('(',stock.IDArt,') ',stock.Descripcion) AS artId,
                            stock.Cantidad,
                            comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                            DATE_FORMAT(comp_ped.Fecha,'%Y%m%d') AS Fecha,                            
                            comp_ped.NroComprobante,
                            comp_ped.CondVenta,
                            comp_ped.SubTotalGral,
                            cliente.nombre_cliente,
                            viajantes.Nombre,
                            DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                            comp_ped.FormaEntrega,
                            comp_ped.Estado,
                            comp_ped.TipoPedido,
                            comp_ped.Tipo,
                            comp_ped.autorizacion_sistema,
                            comp_ped.autorizacion_web,
                            comp_ped.Anulado,
                            (comp_ped.IVA1+
                            comp_ped.IVA2)AS IVA,
                            (comp_ped.SubTotalDesc+
                            comp_ped.IVA1+
                            comp_ped.IVA2) AS Total
                            
                    FROM 
                        stock
                        LEFT JOIN comp_ped  ON comp_ped.CodigoMovimiento = stock.CodigoMovimiento
                        LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo
                        LEFT JOIN viajantes ON viajantes.CodViajante = comp_ped.CodViajante
           
                    WHERE 
                    
                    comp_ped.TipoComprobante ='REM'
                    AND (comp_ped.TipoPedido ='Sistema' OR comp_ped.TipoPedido='Web') 
                    ".$condicion."
                       
                     
                    ORDER BY comp_ped.Fecha DESC LIMIT 60";
        $hacerPed = mysqli_query($connV,$sqlPedido) or die('No puedo recuperar el pedido'.mysqli_error($connV).'<br>'.$sqlPedido);
//        echo "<pre>";
//        print_r($sqlPedido);
//        echo "</br>";
        while($remito = mysqli_fetch_object($hacerPed)){
            $remitos[] = $remito;
        }
    ?>
<script>

  $(document).ready(function() { 
      $('#myTable').DataTable({
          searching: false,
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
               
           }
           ,
           "order": [[ 0, "desc" ]]
           ,
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
            case '-':
                   $('#buscaFecha').hide();
                   $('#buscaNumero').hide();
                 
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
            numeroComp = $('#numeroComp').val(),
            tipoRemito = $('#tipoRemito').val(),
            estadoPedido =  $('#estadoPedido').val();
            //$('formBusca').submit();
         
        $.ajax({
                type: 'POST',
                url: 'relay-articulo-remito.php',
                data:{
                    "ajax" : "true",
                    "campoBusca" : campoBusca,
                    "fechaDesde" : fechaDesde,
                    "fechaHasta" : fechaHasta,
                    "numeroComp" : numeroComp,
                    "tipoRemito" : tipoRemito,
                    "estadoPedido" : estadoPedido
                    
                },
                success: function(response) {
                    $('#spinner').hide()
//                        // Refresh the cart display after a successful Ajax request
 
                        contienes.empty();
                        if ( $.fn.dataTable.isDataTable( '#myTable' ) ) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching: false,
                            responsive:true,
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
                                
                            }, "order": [[ 0, "desc" ]],                           
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
//      $('#verFiltros').on("click",function(){
//          $(this).toggleClass('iconoAzul');
//          $('#formBusca').toggle();
//      });
//       $('#formBusca').hide();


$('#parametrosInformes').on('click', function() {
                // console.log('hago click en la busqueda avanzada---------');
                var divAvanzado = $(".panelesBloqueInforme");
                if ($(this).hasClass('fa-angle-down') === true) {
                    // console.log('tenglo clase angle down----');
                    $(this).removeClass('fa-angle-down').addClass('fa-angle-up');
                    divAvanzado.show();
                } else {
                    // console.log('tenglo clase angle UP----');
                    $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
                    divAvanzado.hide();
                }

            });


      
  });
 
</script>
</head>
<body>
    <div id="wrapper">
        <?php 
            require_once $barra;
        ?>
        
        <div id="content" class="noPrint">
           
                            <div class="paneles filtroInformes" id="barraBusca">
                                             <h1>Filtros <span ><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
                                                <form id="formBusca" name="formBusca" method="POST" action="">

                                                    <div class='panelesBloqueInforme'>
                                                            <div class="control">
                                                                <label class="parametros" for="estadoPedido">Estado:</label>  
                                                                    <select name="estadoPedido" id="estadoPedido">
                                                                        <option value="1">-</option>                             
                                                                        <option value="Facturado">Facturado</option>
                                                                        <option value="Pendiente">Pendiente</option>
                                                                    </select>
                                                                
                                                            </div>


                                                            <div class="control" >
                                                                <label class="parametros" for="tipoRemito">Tipo: </label>
                                                                    <select id="tipoRemito" name="tipoRemito" >
                                                                        <option value="1">-</option>
                                                                        <option value="Sistema">Sistema</option>
                                                                        <option value="Web">Web</option>
                                                                        <option value="Talonario">Talonario</option>
                                                                    </select>
                                                                
                                                                
                                                            </div>

                                                            
                                                            <div class="control">
                                                                <label class="parametros" for="campoBusca">Buscar por: </label> 
                                                                    <select name="campoBusca" id="campoBusca">
                                                                        <option value="-">-</option>
                                                                        <option value="Fecha">Fecha</option>
                                                                        <option value="NroComprobante">Número</option>
                                                                    </select>
                                                                
                                                            </div>
                                                            
                                                            <div id="buscaFecha" style="display:none" class="control">
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

                                                    
            <div id="basic-modal-content" > </div>
                <!--spinner admNET-->
                <div id="spinner" class="spinnerAdm" style="display:none;">
                    <div class="centro">
                        <img src="_img/logo-administranet-ecommerce.png">
                        <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
                    </div>
                </div>
                <!--fin spinner-->
                                                                        
                                            </form>
                            </div>
                            
           <!-- <div id="spinner" class="spinner" style="display:none;">
               <img src="_img/logo-administranet-ecommerce.png">   
               <div class="texto">Procesando...</div>
            </div> -->
            <div id="contiene-tabla"  style="float:left;" >
            <?php if(isset($_REQUEST['cartel'])&&($_REQUEST['cartel']=='5' || $_REQUEST['cartel']=='6' )):?>
                        <?php
                            $textoCartel = '<div id="alertas-formulario" class="alerta-exito">'
                                    . 'Se ha generado:<br>';
                            if(isset($_GET['rem'])){ $textoCartel .='Remito: <strong>'.$_GET['rem'].' <i class="fa fa-check-circle"></i></strong><br>';}
                            if($_GET['cartel']=='6'){ $textoCartel .='Email enviado: <strong> <i class="fa fa-check-circle"></i></strong><br>';}
                            if($_GET['cartel']=='5'){ $textoCartel .='Email NO enviado: <strong> <i class="fa fa-times-circle"></i></strong><br>';}
                            $textoCartel .='<div style="text-align:center">'

                                    . '</div></div>';
                         ?>
                        <div id="basic-modal-content" class="cartelCliente"><?php echo $textoCartel;?>

                        </div>
                    <?php endif;?>
                <h1>Artículos remitados
                    <a href="listado-clientes.php?frm=1" style="float:right;margin:3px">
                    <button class="botonNuevo grande azul">
                        <i class="fa fa-plus fa-lg"></i> 
                    </button>
                </a></h1>
                
           <?php if(!empty($remitos)):?>
               
                <!--soy WEB-->
                    <table class="display" cellspacing="1" id="myTable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Articulo</th>
                                <th>Entregado</th>
                                <th>N°Comp.</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th>Tipo</th>                               
                                <th>Estado</th>                               
                                <th>Anul.</th>
                                <th>&nbsp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($remitos as $remito):?>
                                <tr>
                                    <td class="dt-nowrap" data-order="<?php echo $remito->Fecha;?>">                                       
                                        <?php echo $remito->FechaB;?>
                                    </td>
                                    <?php if($usaIdManual=="Si"):?>
                                    <td class="dt-nowrap" data-order="<?php echo $remito->id_manual;?>"><?php echo $remito->artManual;?></td>
                                    <?php else:?>
                                    <td class="dt-nowrap" data-order="<?php echo $remito->IDArt;?>"><?php echo $remito->artId;?></td>
                                    <?php endif;?>
                                    <td class="dt-center" ><?php echo $remito->Cantidad;?></td>
                                    <td class="dt-nowrap" ><?php echo $remito->NroComprobante;?></td>

                                    <td><?php echo $remito->nombre_cliente;?></td>
                                    <td><?php echo $remito->Nombre;?></td>
                                    <td><?php echo $remito->TipoPedido;?></td>
                                  
                                    <?php 

                                        switch($remito->Estado){
                                            case 'Facturado':
                                                $claseEstado = 'facturado';
                                                break;

                                            case 'En Remito':
                                                $claseEstado = 'pendienteRemito';
                                                break;
                                            default:
                                                $claseEstado = 'promocion';
                                            break;
                                        }
                                    ?>
                                    <td class="<?php echo $claseEstado;?>"><?php echo $remito->Estado;?></td>                                                                        
                                    <td><?php echo $remito->Anulado;?></td>
                                    <td>
                                        <a  target="_blank" href="ver_remito-movil.php?codigomovimiento=<?php echo $remito->CodigoMovimiento;?>&tipocomprobante=REM"  title="Visualizar comprobante" alt="Visualizar comprobante" mov="<?php echo $remito->CodigoMovimiento;?>" comprobante="REM">
                                        <i class="fa fa-file-pdf barrita fa-lg fa-2x"></i>    
                                        </a>
                                        <a  href="relay-comprobante-a-mail.php?codMov=<?php echo $remito->CodigoMovimiento;?>&tipocomprobante=0"  title="mandar Email" alt="enviar email" mov="<?php echo $remito->CodigoMovimiento;?>" comprobante="REM">
                                        <i class="fa fa-envelope barrita fa-lg fa-2x"></i>    
                                        </a>
                                        </td>

                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
            <?php endif; ?>
                 
        
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
                var wPantalla=$(document).width();  
                var wVentana=0, hVentana=0;    
                            if(wPantalla>320){
                               wVentana=400;
                               hVentana=100;
                            }else{
                                wVentana=300;
                                hVentana=200;
                            }
                console.log(wPantalla);
                
                    $('#basic-modal-content').modal({
                            minWidth : wVentana,
                            minHeight: hVentana
                            
                        });
                
            });
        </script>
    <?php endif;?>
    </body>
</html>