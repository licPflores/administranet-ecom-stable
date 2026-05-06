<?php require_once 'sesion.inc.php'; ?>
<?php
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$usaZoom       = 0;
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>Pedidos del vendedor | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <?php require_once 'cabecera.php'; ?>

    <?php
    //    echo '<pre>'.print_r($_SESSION).'</pre>';
    $vendedor = $_SESSION['tipousuario'];
    $deQuien = "";
    if ($vendedor == "vendedor") {
        // vendedor
        //echo "VENDEDOR";
        //permiso para ver todos los clientes o no.
        if ($_SESSION["todos_clientes"] == "No") {
            $deQuien = " AND comp_ped.CodViajante=" . $objVendedor->CodViajante;
        }

        if (isset($_REQUEST["listaPed"]) && $_REQUEST["listaPed"] == "cliente") {
            if (isset($_SESSION['idcliente']) && $_SESSION['idcliente'] != "") {
                $deQuien .= " AND comp_ped.Codigo=" . $_SESSION['idcliente'];
            }
        }
    } else {
        //cliente
        $deQuien .= " AND comp_ped.Codigo=" . $_SESSION['idcliente'];
    }
    $usaIdManual = $_SESSION["usa_id_manual"];
    //        vamos a buscar los pedidos de acuerdo al cliente y al estado 
    $pedidos = array();
    $sqlPedido = "SELECT 
                            comp_ped.CodigoMovimiento,
                            comp_ped.id_comp_ped AS id,
                            DATE_FORMAT(comp_ped.Fecha,'%d/%m/%Y') AS FechaB,
                            comp_ped.Fecha,
                            comp_ped.NroComprobante,
                            comp_ped.SubTotalDesc,
                            comp_ped.IVA1,
                            comp_ped.IVA2,
                            comp_ped.Exento,
                            comp_ped.CondVenta,
                            DATE_FORMAT(comp_ped.FechaEntrega,'%d/%m/%Y') AS FechaEntrega,
                            comp_ped.FormaEntrega,
                            comp_ped.Estado,
                            cliente.nombre_cliente,
                            cliente.Codigo,
                            cliente.id_manual_cli,
                            viajantes.Nombre,
                            comp_ped.TipoPedido,
                            comp_ped.autorizacion_sistema,
                            comp_ped.autorizacion_web,
                            comp_ped.Anulado,
                            (comp_ped.IVA1+
                            comp_ped.IVA2)AS IVA,
                            (comp_ped.SubTotalDesc+
                            comp_ped.IVA1+
                            comp_ped.IVA2) AS Total
                            
                    FROM 
                        comp_ped
                    LEFT JOIN cliente ON cliente.Codigo = comp_ped.Codigo
                    LEFT JOIN viajantes ON viajantes.CodViajante= comp_ped.CodViajante
                    WHERE 
                    
                    comp_ped.TipoComprobante ='PED'
                    {$deQuien}
                       
                     
                    ORDER BY comp_ped.Fecha DESC LIMIT 60";
    $hacerPed = mysqli_query($connV, $sqlPedido) or die('No puedo recuperar el pedido' . mysqli_error($connV) . '<br>' . $sqlPedido);
    //        echo $sqlPedido.'<br>';
    while ($pedido = mysqli_fetch_object($hacerPed)) {
        $pedidos[] = $pedido;
    }
    ?>
    <script>
        $.extend($.fn.dataTable.defaults, {
            searching: false,
            responsive: false,
            ordering: false,
            "language": {
                "emptyTable": "No data available in table",
                "info": "Viendo _START_ de _END_ de _TOTAL_ resultados",
                "infoEmpty": "Viendo 0 de 0 de 0 resultados",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "infoPostFix": "",
                "thousands": "",
                "lengthMenu": "Ver _MENU_ entradas",
                "loadingRecords": "Loading...",
                "processing": "Processing...",
                "search": "Buscar:",
                "zeroRecords": "No matching records found",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activate to sort column ascending",
                    "sortDescending": ": activate to sort column descending"
                }

            },
            errMode: 'none'
        });
        // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
        // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
        $(document).ready(function() {

            $('#myTable').DataTable({
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

            // para que se borren lo que tienen adentro las fechas   

            $('#campoBusca').change(function() {
                var valor = $(this).val();
                // voy a corroborar que div mostrar
                $('#fechaDesde').val('dd/mm/aaaa'),
                    $('#fechaHasta').val('dd/mm/aaaa'),
                    $('#numeroComp').val('');
                switch (valor) {
                    case 'Fecha':
                        $('#buscaNumero').hide();
                        $('#buscaTipo').hide();
                        $('#suggestions').hide();
                        $('#buscaFecha').show(400);


                        break;
                    case 'NroComprobante':
                        $('#buscaFecha').hide();
                        $('#buscaTipo').hide();
                        $('#buscaNumero').show(400);
                        break;
                    case 'TipoPedido':
                        $('#buscaFecha').hide();
                        $('#buscaTipo').show(400);
                        $('#buscaNumero').hide();
                        break;
                    case '-':
                        $('#buscaFecha').hide();
                        $('#buscaTipo').hide();
                        $('#buscaNumero').hide();
                        break;
                }

            });
            // boton para buscar coincidencias
            $('#botonBuscar').on("click", function(event) {

                $('#spinner').show()
                let botonBusca = $(this);
                botonBusca.prop('disabled', true);
                botonBusca.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere');
                var contienes = $('#myTable'),
                    campoBusca = $('#campoBusca').val(),
                    fechaDesde = $('#fechaDesde').val(),
                    fechaHasta = $('#fechaHasta').val(),
                    numeroComp = $('#numeroComp').val(),
                    estadoPedido = $('#estadoPedido').val(),
                    tipoPedido = $('#tipoPedido').val(),
                    listaPed = $('#listaTodos').val();
                //$('formBusca').submit();

                $.ajax({
                    type: 'POST',
                    url: 'relay-pedidos.php',
                    data: {
                        "ajax": "true",
                        "vendedor": "true",
                        "campoBusca": campoBusca,
                        "fechaDesde": fechaDesde,
                        "fechaHasta": fechaHasta,
                        "numeroComp": numeroComp,
                        "estadoPedido": estadoPedido,
                        "tipoPedido": tipoPedido,
                        "listaPed": listaPed
                    },
                    success: function(response) {
                        $('#spinner').hide()//                        // Refresh the cart display after a successful Ajax request
                        //alert(response);  
                        //contienes.html('');    
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Generar');
                        contienes.empty();
                        if ($.fn.dataTable.isDataTable('#myTable')) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        //console.log(contienes[0].rows[1].innerText);
                        //if(contienes[0].rows[1].innerText==="No se encontaron resultados"){
                        contienes.DataTable({
                            "orderCellsTop": true,
                            dom: 'Bfrtip',
                            buttons: [
                                'excel',
                                {
                                    extend: 'pdf',
                                    orientation: 'landscape'
                                }
                            ]

                        });
                        //}
                        $("#spinner").hide();

                    },
                    error: function(x, e) {
                        $('#spinner').hide()
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Generar');
                        var s = x.status,
                            m = 'Ajax error: ';
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
            <?php if ($caminoDispo == "") : ?>
                /*
                 * VISUALIZAR COMPROBANTE PC
                 * @param {type} event
                 * @returns {undefined}
                 */
                $(document).on('click', '.verComprobante', function(event) {
                    //event.preventDefault();
                    var
                        codigoMovimiento = $(this).attr('mov'),
                        tipoComprobante = $(this).attr('comprobante');
                    //            alert($(this).attr('mov') + ' -  ' + $(this).attr('comprobante')+' - '+ este.attr('mov')+' - '+este.attr('comprobante'));

                    $.ajax({
                        type: 'POST',
                        url: 'ver_pedido.php',
                        data: {
                            "ajax": "true",
                            "codigomovimiento": codigoMovimiento,
                            "comprobante": tipoComprobante
                        },
                        success: function(response) {
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
                                maxWidth: 950,
                                minHeight: 700
                            });
                            $('#imprimir').click(function() {
                                $(this).hide();
                                window.print();
                            });
                            //return false;
                        },
                        error: function(x, e) {
                            var s = x.status,
                                m = 'Ajax error: ';
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
            <?php else : ?>
                /*
                 * VISUALIZAR COMPROBANTE MOVIL
                 * @param {type} event
                 * @returns {undefined}
                 */
                $(document).on('click', '.verComprobante', function(event) {
                    //event.preventDefault();
                    var
                        codigoMovimiento = $(this).attr('mov'),
                        tipoComprobante = $(this).attr('comprobante');
                    //            alert($(this).attr('mov') + ' -  ' + $(this).attr('comprobante')+' - '+ este.attr('mov')+' - '+este.attr('comprobante'));

                    $.ajax({
                        type: 'POST',
                        url: 'ver_pedido-movil.php',
                        data: {
                            "ajax": "true",
                            "codigomovimiento": codigoMovimiento,
                            "comprobante": tipoComprobante
                        },
                        success: function(response) {
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
                                maxWidth: 950,
                                minHeight: 700
                            });
                            $('#imprimir').click(function() {
                                $(this).hide();
                                window.print();
                            });
                            //return false;
                        },
                        error: function(x, e) {
                            var s = x.status,
                                m = 'Ajax error: ';
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
            <?php endif; ?>
            //esta es la forma de hacer que ande sin tanto complicaion    

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


$(document).on('click', '.anularPedido', function(event) {
        event.preventDefault();
// sweet alert 2 preguntar si quiero anular el pedido
        //alert('Anular pedido');
        var 
            codigoMovimiento    = $(this).attr('mov'),
            tipoComprobante     = $(this).attr('comprobante');
            numeroComprobante     = $(this).attr('nrocomprobante');
        Swal.fire({
                title: "¿Está seguro que desea anular ?",
                text: "Anulará "+tipoComprobante+" N° "+numeroComprobante,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#395aa2',

            })
            .then((resultado) => {
                if (resultado.isConfirmed) {
                    console.log('dentro del si quiero anular carajo');
                    $.ajax({
                        type:   'POST',
                        url:    'relay-pedidos.php',
                        data:{
                                "anularPedido":1,
                                "codMovPedido": codigoMovimiento,
                            
                        },
                        success: function(response){
                            if(response.msg=="ok"){
                                
                                Swal.fire({
                                    title: "Pedido anulado",
                                    text: "El pedido ha sido anulado correctamente",
                                    icon: "success",
                                    confirmButtonColor: '#395aa2',
                                }).then((value) => {
                                    window.location.reload(); 
                                });
                                
                            }
                            if(response.msg=="error"){
                                Swal.fire({
                                    title: "Error",
                                    text: "El pedido no se ha podido anular"+response.error,
                                    icon: "error",
                                    confirmButtonColor: '#395aa2',
                                });
                            }
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
                    //alert('Anular pedido');
                    //window.location.href = 'lista-pedidos-vendedor.php?cartel=1';
                    //window.location.reload();
                    //location.reload();
                } else {
                    console.log('no se anulo');
                }
            });


        
            
        
     
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
            <h1>Filtros <span><i id="parametrosInformes" class="fas fa-angle-up fa-lg fa-fw"></i></span></h1>
            <form id="formBusca" name="formBusca" method="POST" action="">
                <div class='panelesBloqueInforme'>
                    <!-- <label>Filtros:</label> <i class="fa fa-filter fa-lg " id="verFiltros"></i> -->

                    <div class="control">
                        <label class="parametros" for="estadoPedido">Clientes: </label>
                        <select name="listaTodos" id="listaTodos">
                            <option value="cliente">Seleccionado</option>
                            <option selected value="todos">Todos </option>
                        </select>

                    </div>
                    <div class="control">
                        <label class="parametros" for="estadoPedido">Estado:</label>
                        <select name="estadoPedido" id="estadoPedido">
                            <option value="1">-</option>

                            <option value="En Remito">En Remito</option>
                            <option value="Facturado">Facturado</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="En Pedido">En Pedido</option>
                            <option value="Imput manual">Imput manual</option>
                            <option value="Aprobado">Aprobado</option>
                            <option value="Completo">Completo</option>
                            <option value="Parcial">Parcial</option>
                            <option value="Cerrado">Cerrado</option>
                            <option value="En preparación">En preparación</option>
                            <option value="Preparado">Preparado</option>
                        </select>

                    </div>

                    <div class="control">
                        <label class="parametros" for="campoBusca">Buscar por: </label>
                        <select name="campoBusca" id="campoBusca">
                            <option value="-">-</option>
                            <option value="Fecha">Fecha</option>
                            <option value="NroComprobante">Número</option>
                            <option value="TipoPedido">Tipo Pedido</option>
                        </select>

                    </div>

                    <div id="buscaFecha" style="display:none" class="control">
                        <label class="parametros" for="fechaDesde">Desde: </label>
                        <input type="date" name="fechaDesde" id="fechaDesde">

                        <label class="parametros" for="fechaHasta">Hasta: </label>
                        <input type="date" name="fechaHasta" id="fechaHasta">
                    </div>

                    <div id="buscaNumero" class="control" style="display:none">
                        <label for="numeroComp">Nº Comp.: </label>
                        <input type="text" name="numeroComp" id="numeroComp">

                    </div>
                    <div id="buscaTipo" class="control" style="display:none">
                        <label class="parametros" for="tipoPedido">Tipo de pedido: </label>
                        <select id="tipoPedido" name="tipoPedido">
                            <option value="1">-</option>
                            <option value="Sistema">Sistema</option>
                            <option value="Web">Web Vendedor</option>
                            <option value="Web cliente">Web Cliente</option>
                        </select>


                    </div>
                </div>

                <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                            <i class="fas fa-check fa-lg fa-fw"></i> Generar
                        </button>
                    </span>
                </div>

            </form>
        </div>

       


    

    <div class="paneles" id="contiene-tabla">


        <?php if (isset($_REQUEST['cartel']) && ($_REQUEST['cartel'] == '5' || $_REQUEST['cartel'] == '6')) : ?>
            <?php
            $textoCartel = '<div id="alertas-formulario" class="alerta-exito">'
                . 'Se ha generado:<br>';
            if (isset($_GET['ped'])) {
                $textoCartel .= 'Pedido: <strong>' . $_GET['ped'] . ' <i class="fa fa-check-circle"></i></strong><br>';
            }
            if ($_GET['cartel'] == '6') {
                $textoCartel .= 'Email enviado: <strong> <i class="fa fa-check-circle"></i></strong><br>';
            }
            if ($_GET['cartel'] == '5') {
                $textoCartel .= 'Email NO enviado: <strong> <i class="fa fa-times-circle"></i></strong><br>';
            }
            $textoCartel .= '<div style="text-align:center">'

                . '</div></div>';
            ?>
            <div id="basic-modal-content" class="cartelCliente"><?php echo $textoCartel; ?>

            </div>
        <?php endif; ?>
        <h1>Listado de pedidos</h1>
        <?php if (!empty($pedidos)) : ?>

            <!--SOY WEB-->
            <table class="display" id="myTable" cellspacing="1">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>N°Comprob.</th>
                        <th>Cliente</th>
                        <th>Cond.Vta</th>
                        <th class="right">SubTotal</th>
                        <th class="right">Iva</th>
                        <th class="right">Total</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Autorizado</th>
                        <th>Entrega</th>
                        <th>Anul.</th>
                        <th>&nbsp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido) : ?>
                        <tr>
                            <td class="dt-nowrap">
                                <?php echo $pedido->FechaB; ?></td>
                            <td class="dt-nowrap"><?php echo $pedido->NroComprobante; ?></td>
                            <td><?php
                                if ($usaIdManual == "si") :
                                    echo $pedido->id_manual_cli . " - " . $pedido->nombre_cliente;
                                else :
                                    echo $pedido->Codigo . " - " . $pedido->nombre_cliente;
                                endif;
                                ?></td>
                            <td><?php echo $pedido->CondVenta; ?></td>
                            <td class="importe">$<?php echo $pedido->SubTotalDesc; ?></td>
                            <td class="importe">$<?php echo $pedido->IVA; ?></td>
                            <td class="importe">$<?php echo $pedido->Total; ?></td>
                            <td><?php echo $pedido->TipoPedido; ?></td>
                            <?php

                            switch ($pedido->Estado) {
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
                            <td class="<?php echo $claseEstado; ?>"><?php echo $pedido->Estado; ?></td>
                            <td><?php echo $pedido->autorizacion_sistema; ?></td>
                            <!--<td style="width:80px; "><?php echo $pedido->FechaEntrega; ?></td>-->
                            <td><?php echo $pedido->FormaEntrega; ?></td>
                            <td><?php echo $pedido->Anulado; ?></td>
                            <td >
                                <span class="acciones">
                                    <a target="blank" href="ver_pedido-movil.php?codigomovimiento=<?php echo $pedido->CodigoMovimiento; ?>&tipocomprobante=PED" title="Visualizar comprobante" alt="Visualizar comprobante" mov="<?php echo $pedido->CodigoMovimiento; ?>" comprobante="PED">
                                        <i class="fa fa-file-pdf barrita fa-lg fa-2x"></i>
                                    </a>
                                </span>
                                <span class="acciones">
                                    <a href="relay-comprobante-a-mail.php?codMov=<?php echo $pedido->CodigoMovimiento; ?>&tipocomprobante=0" title="mandar Email" alt="enviar email" mov="<?php echo $pedido->CodigoMovimiento; ?>" comprobante="PED">
                                        <i class="fa fa-envelope barrita fa-lg fa-2x"></i>
                                    </a>
                                </span>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>


        <?php endif; ?>
    </div>
    <?php require_once 'footer.php'; ?>

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
    <?php if (isset($_REQUEST['cartel'])) : ?>
        <script>
            $(document).ready(function() {
                //        $('#basic-modal-content').empty();
                //        $('#basic-modal-content').html(response);
                //$('#basic-modal-content').maxHeight = 200,
                var wPantalla = $(document).width();
                var wVentana = 0,
                    hVentana = 0;
                if (wPantalla > 320) {
                    wVentana = 400;
                    hVentana = 100;
                } else {
                    wVentana = 300;
                    hVentana = 200;
                }
                console.log(wPantalla);

                $('#basic-modal-content').modal({
                    minWidth: wVentana,
                    minHeight: hVentana

                });

            });
        </script>
    <?php endif; ?>
    <div id="basic-modal-content"> </div>
</body>

</html>