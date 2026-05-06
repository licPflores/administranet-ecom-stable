<?php
//error_reporting(E_ALL);
session_start();
$caminoDispo = $_SESSION['caminoDisp'];
session_write_close();
//require_once $caminoDispo.'jcart/jcart.php';
require_once 'sesion.inc.php';

/**
 * variables de configuracion para colocar los encabezados
 */
$usaZoom    = 0;
$uTablas    = 1;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 1;
$iconoDisabled = 1;
$codPuesto = $objVendedor->id_puesto;
// echo 'camino dispo',var_dump($caminoDispo);
$whereViajante = "";
if ($codPuesto != 1) {
    $whereViajante = " AND viajantes.CodViajante=" . $objVendedor->CodViajante . PHP_EOL;
}
// buscando vendedores rapido.
$sql = "SELECT viajantes.CodViajante AS valor,"
    . " CONCAT(viajantes.Nombre,' (cod:',viajantes.CodViajante,')') AS texto "
    . " FROM viajantes"
    . " WHERE viajantes.Anulado='No'"
    . $whereViajante
    . " ORDER BY texto ASC";

$hacerVendedor = mysqli_query($connV, $sql);
$arrVendedores = array();
if ($hacerVendedor) {
    while ($viajante = mysqli_fetch_assoc($hacerVendedor)) {
        $arrVendedores[] = $viajante;
    }
}

// motivos de No entrega
$motivos_no_entrega = array(
    "No se encuentra en domicilio",
    "Error de facturación",
    "Error de mercadería",
    "Mercadería defectuosa"
);


?>
<!DOCTYPE HTML>
<html>

<head>
    <title>administraNET e-com | comprobantes en rutas</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />



    <?php require_once 'cabecera.php'; ?>
    <!-- switchery -->
    <!-- <link rel="stylesheet" href="dist/switchery.css" /> -->
    <!-- <script src="dist/switchery.js"></script> -->
    <!-- select con buscador -->
    <!-- ...existing code... -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <!-- ...existing code... -->
<style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            /* Fondo oscuro con opacidad */
            z-index: 1000;
            justify-content: center;
            align-items: center;
            font-family: 'Arial', sans-serif;
            /* Fuente limpia y profesional */
        }

        .modal-content {
            background-color: #ffffff;
            /* Fondo blanco */
            width: 90%;
            max-width: 500px;
            padding: 20px;
            border-radius: 10px;
            /* Bordes redondeados */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            /* Sombra para destacar */
            border: 1px solid #e0e0e0;
            /* Borde gris claro */
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e0e0e0;
            /* Línea divisoria */
            padding-bottom: 10px;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 18px;
            color: #2A3E72;
            font-weight: bold;
            /* Texto oscuro */
        }

        .modal-titulo {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 5px;
            /* border: 1px solid #2A3E72; */
            /* Línea divisoria */
            /* border-radius: 10px; */
            background-color: #f8f9fa;
            /* Fondo gris claro */
            color: #2A3E72;
            /* Texto blanco */
            font-size: 0.675rem;
            margin-top: 10px;

        }

        .modal-titulo span {
            display: block;
            margin: 2px;

        }

        .close-btn {
            cursor: pointer;
            font-size: 20px;
            color: #ff4d4d;
            /* Rojo suave para el botón de cerrar */
            transition: color 0.3s ease;
        }

        .close-btn:hover {
            color: #ff0000;
            /* Rojo más intenso al pasar el mouse */
        }

        .modal-body {
            margin-top: 20px;
        }

        .modal-body label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555555;
            /* Texto gris oscuro */
        }

        .modal-body select,
        .modal-body textarea,
        .modal-body input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #cccccc;
            /* Borde gris claro */
            border-radius: 5px;
            font-size: 14px;
            color: #333333;
            /* Texto oscuro */
            background-color: #f9f9f9;
            /* Fondo gris claro */
            transition: border-color 0.3s ease;
        }

        .modal-body select:focus,
        .modal-body textarea:focus,
        .modal-body input:focus {
            border-color: #007bff;
            /* Azul para el borde al enfocar */
            outline: none;
        }

        .modal-footer {
            text-align: right;
            margin-top: 20px;
        }

        .modal-footer button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .modal-footer .save-btn {
            background-color: #007bff;
            /* Azul principal */
            color: #ffffff;
            /* Texto blanco */
        }

        .modal-footer .save-btn:hover {
            background-color: #0056b3;
            /* Azul más oscuro al pasar el mouse */
        }

        .modal-footer .cancel-btn {
            background-color: #6c757d;
            /* Gris oscuro */
            color: #ffffff;
            /* Texto blanco */
            margin-right: 10px;
        }

        .modal-footer .cancel-btn:hover {
            background-color: #5a6268;
            /* Gris más oscuro al pasar el mouse */
        }

        @media (max-width: 768px) {
            .modal-content {
                width: 95%;
            }

            .modal-header h2 {
                font-size: 16px;
            }

            .modal-footer button {
                font-size: 12px;
                padding: 8px 16px;
            }
        }

        /* Personalización del menú de autocomplete para que combine con la modal */
        .ui-autocomplete {
            background: #fff;
            border: 1px solid #007bff;
            border-radius: 8px;
            max-height: 220px;
            overflow-y: auto;
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            z-index: 2000 !important;
            box-shadow: 0 8px 16px rgba(0, 123, 255, 0.08);
        }

        .ui-menu-item {
            padding: 8px 16px;
            color: #333;
            cursor: pointer;
            transition: background 0.2s;
        }

        .ui-menu-item-wrapper {
            padding: 4px 0;
        }

        .ui-state-active,
        .ui-menu-item:hover {
            background: #007bff !important;
            color: #fff !important;
            border-radius: 6px;
        }

        .ui-autocomplete strong {
            color: #007bff;
            font-weight: bold;
            background: none;
        }

        /* Para destacar la opción seleccionada en el select */
        #selectEntregado option[selected], #selectEntregado option:checked {
            font-weight: bold;
        }

        /* Iconos y estilos para el select personalizado */
        .select-entregado {
            position: relative;
            padding-left: 28px !important;
        }
        .select-entregado .fa-check-circle {
            position: absolute;
            left: 6px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
        }

        .select-entregado.si {
            background-color: #e6f9ed !important;
            border: 2px solid #28a745 !important;
            color: #28a745 !important;
        }
        .select-entregado.no {
            background-color: #fdeaea !important;
            border: 2px solid #dc3545 !important;
            color: #dc3545 !important;
        }

        /* Para los divs de motivo */
        .motivo-entrega {
            border: 2px solid #28a745 !important;
            background: #e6f9ed !important;
            border-radius: 8px;
            padding: 10px;
        }
        .motivo-no-entrega {
            border: 2px solid #dc3545 !important;
            background: #fdeaea !important;
            border-radius: 8px;
            padding: 10px;
        }

        /* Moderno y mobile friendly */
        .estado-entrega {
            display: flex;
            align-items: center;
            /* gap: 10px; */
            font-size: 1.1em;
            margin-bottom: 12px;
            border-radius: 8px;
            padding: 10px;
            font-weight: bold;
            flex-direction: column;
            align-items: baseline;
        }
        .estado-entrega.entregado {
            background: #e6f9ed;
            color: #28a745;
            border: 2px solid #28a745;
        }
        .estado-entrega.no-entregado {
            background: #fdeaea;
            color: #dc3545;
            border: 2px solid #dc3545;
        }
        .estado-entrega i {
            font-size: 1.5em;
        }
        .titulo-estado {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin-bottom: 6px;
            font-size: 1.1em;
        }
        .detalle-estado {
            display: flex;
            flex-direction: column;
            font-size: 0.75em;
            margin-left: 10px;
            color: #444;
            font-weight: normal;
        }
        .detalle-estado span {
            margin: 5px 0;
        }
        .detalle-estado span i.entregado{
            color: #28a745;
        }
        .detalle-estado span i.no-entregado{
            color: #dc3545;
        }

        .seccion {
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 10px 12px;
            box-shadow: 0 2px 8px rgba(44,62,80,0.04);
        }
        .seccion h3 {
            font-size: 1em;
            margin: 0 0 6px 0;
            color: #2A3E72;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
            float: none
        }
        .traza-item, .ruta-item, .prep-item {
            font-size: 0.78em;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .no-corta{
            white-space: nowrap;
        }
        .traza-item .fecha {
            margin-left: auto;
            /* color: #888; */
            /* font-size: 0.78em; */
        }
        @media (max-width: 600px) {
            .modal-content, .seccion {
                padding: 8px !important;
            }
            .estado-entrega {
                font-size: 1em;
                padding: 8px;
            }
            .seccion h3 {
                font-size: 0.98em;
            }
        }
        
    </style>
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
            "order": [
                [0, "desc"]
            ]
        });

        $(document).ready(function() {

            var buttonCommon = {
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
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
                            if (column == 12) {
                                console.log('soy la columna del anulado', data, column);
                                var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                                //anulado = anulado.replace('</strong>,', '');
                                // console.log("data coma x punto: "+numero);
                                data = anulado;
                            }
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
                    columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
                    format: {
                        body: function(data, row, column, node) {

                            const arrColumnasNumero = [5, 6, 7];

                            if (arrColumnasNumero.includes(column)) {

                                var numero = data.replace(/[$.]/g, '');

                                numero = numero.replace(',', '.');
                                // console.log("data coma x punto: "+numero);
                                data = numero;


                            }
                            // console.log('soy la columna del anulado',data,column);
                            if (column == 12) {
                                console.log('soy la columna del anulado', data, column);
                                var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                                //anulado = anulado.replace('</strong>,', '');
                                // console.log("data coma x punto: "+numero);
                                data = anulado;
                            }
                            return data;
                        },
                        footer: function(data, row, column, node) {
                            // console.log('datos del pie',data,row,column,node);
                            const arrColumnasNumero = [6, 7, 8];

                            if (arrColumnasNumero.includes(row)) {

                                data = data.replace(/[$.]/g, '');
                                data = data.replace(',', '.');
                            }
                            return data;
                        }
                    }
                }
            };
            // funcion para cambiar la fecha.
            function fechaReverso(date) {
                console.log(date);
                var a = date.split('-');
                var d = new Number(a[2]);
                var m = new Number(a[1]);
                var y = new Number(a[0]);
                var dd = new Date(y, m - 1, d);
                var y = dd.getFullYear();
                var m = dd.getMonth() + 1;
                var d = dd.getDate();
                return (d < 10 ? '0' + d : d) + '/' + (m < 10 ? '0' + m : m) + '/' + y;
                //return dd;
            }

             // Autocomplete para Clientes
    $("#inputClientes").autocomplete({
    source: function(request, response) {
        $.ajax({
            url: "relay-logistica-comprobantes.php?action=obtenerClientes",
            dataType: "json",
            data: { q: request.term },
            success: function(data) {
                response($.map(data.results.slice(0, 7), function(item) {
                    return {
                        label: item.text,
                        value: item.text,
                        id: item.id
                    };
                }));
            }
        });
    },
    minLength: 2,
    select: function(event, ui) {
        $("#inputClientesId").val(ui.item.id);
    },
    change: function(event, ui) {
        if (!ui.item) {
            $("#inputClientesId").val('');
        }
    }
});

    // // Autocomplete para Choferes
    // $("#inputChoferes").autocomplete({
    //     source: function(request, response) {
    //         $.ajax({
    //             url: "relay-logistica-comprobantes.php?action=obtenerChoferes",
    //             dataType: "json",
    //             data: { q: request.term },
    //             success: function(data) {
    //             response($.map(data.results.slice(0, 7), function(item) {
    //                 return {
    //                     label: item.text,
    //                     value: item.text,
    //                     id: item.id
    //                 };
    //             }));
    //         }
    //         });
    //     },
    //     minLength: 2,
    //     select: function(event, ui) {
    //         $("#inputChoferesId").val(ui.item.id);
    //     },
    //     change: function(event, ui) {
    //         if (!ui.item) {
    //             $("#inputChoferesId").val('');
    //         }
    //     }
    // });

    // Autocomplete para Rutas
    $("#inputRutas").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "relay-logistica-comprobantes.php?action=obtenerHojasRuta",
                dataType: "json",
                data: { q: request.term },
                success: function(data) {
                response($.map(data.results.slice(0, 7), function(item) {
                    return {
                        label: item.text,
                        value: item.text,
                        id: item.id
                    };
                }));
            }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $("#inputRutasId").val(ui.item.id);
        },
        change: function(event, ui) {
            if (!ui.item) {
                $("#inputRutasId").val('');
            }
        }
    });

    $.ui.autocomplete.prototype._renderItem = function(ul, item) {
        var term = this.term.trim();
        var regex = new RegExp('(' + $.ui.autocomplete.escapeRegex(term) + ')', 'gi');
        var label = item.label.replace(regex, "<strong>$1</strong>");
        return $("<li>")
            .append("<div>" + label + "</div>")
            .appendTo(ul);
    };

            $('#parametrosInformes').on('click', function() {
                var divAvanzado = $(".panelesBloqueInforme");
                if ($(this).hasClass('fa-angle-down') === true) {
                    $(this).removeClass('fa-angle-down').addClass('fa-angle-up');
                    divAvanzado.show();
                } else {
                    $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
                    divAvanzado.hide();
                }
            });

            $('#fechaDesde').focus(function() {
                $('#fechaDesde').val('');
            });
            $('#fechaHasta').focus(function() {
                $('#fechaHasta').val('');
            });

            $('#campoBusca').change(function() {
                var valor = $(this).val();
                $('#fechaDesde').val('dd/mm/aaaa'),
                    $('#fechaHasta').val('dd/mm/aaaa'),
                    $('#numeroComp').val('');
                switch (valor) {
                    case 'Fecha':
                        $('#buscaNumero').hide();
                        $('#buscaFecha').show(400);
                        break;
                    case 'NroComprobante':
                        $('#buscaFecha').hide();
                        $('#buscaNumero').show(400);
                        break;
                    case '':
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;
                    case 'TipoPedido':
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;
                }
            });


            $('#botonBuscar').click(function() {
                let botonBusca = $(this);

                var contienes = $('#myTable'),
                    campoBusca = $('#campoBusca').val(),
                    fechaDesde = $('#fechaDesde').val(),
                    fechaHasta = $('#fechaHasta').val(),
                    numeroComp = $('#numeroComp').val(),
                    
                    estado = $('#entregadoRemito').val(),
                    clienteNombre = $('#inputClientes').val(),
                    clienteId = $('#inputClientesId').val(),
                    rutaNombre = $('#inputRutas').val(),
                    rutaId = $('#inputRutasId').val();


                var tituloListado = "",
                    mensajeTop = "",
                    nombreInforme = "";
                var clienteTexto, tipoPedidoTexto, estadoPedidoTexto, vendedorTexto;

                tipoPedidoTexto = $('#tipoPedido option:selected').text();
                estadoPedidoTexto = $('#estadoPedido option:selected').text();
                vendedorTexto = $('#filtraVendedor option:selected').text();

                botonBusca.prop('disabled', true);
                botonBusca.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere...');
                $.ajax({
                    type: 'GET',
                    url: 'relay-logistica-comprobantes.php',
                    data: {
                        "listarComprobantes": 1,
                        //filtros
                        fechaDesde: fechaDesde,
                        fechaHasta: fechaHasta,
                        numeroComp: numeroComp,
                        estado: estado,
                        idCliente: clienteId,
                        idRuta: rutaId

                    },
                    success: function(response) {
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar');
                        // console.log('tabla:',response.html);
                        var fecha = new Date();
                        var fechaFormateada = fecha.getFullYear() + '_' +
                            ("0" + (fecha.getMonth() + 1)).slice(-2) + '_' +
                            ("0" + fecha.getDate()).slice(-2) + '_' +
                            ("0" + fecha.getHours()).slice(-2) + '_' +
                            ("0" + fecha.getMinutes()).slice(-2) +
                            ("0" + fecha.getSeconds()).slice(-2);
                        const today = new Date();
                        const day = String(today.getDate()).padStart(2, '0');
                        const month = String(today.getMonth() + 1).padStart(2, '0');
                        const year = today.getFullYear();

                        const formattedDate = `${day}/${month}/${year}`;
                        let simbIzq = "$";

                        if (response.msg == 'error') {
                            Swal.fire({
                                title: "Error",
                                text: response.error,
                                icon: "error",
                                confirmButtonColor: '#395aa2',
                            });
                            return;
                        }
                        if (response.msg == 'vacio') {
                            // Swal.fire({
                            //     title: "Atención",
                            //     text: response.error,
                            //     icon: "warning",
                            //     confirmButtonColor: '#395aa2',
                            // });
                           let laTabla = response.html;
                            contienes.empty();
                            if ($.fn.dataTable.isDataTable('#myTable')) {
                                contienes.DataTable().destroy();
                            }
                            contienes.html(laTabla);
                            return;
                        }
                        if (response.msg == 'ok') {
                            tituloListado = "Comprobantes en ruta" + " - " + vendedorTexto + " - " + tipoPedidoTexto + " - " + estadoPedidoTexto;
                            mensajeTop = "Comprobantes en ruta" + " - " + vendedorTexto + " - " + tipoPedidoTexto + " - " + estadoPedidoTexto;
                            nombreInforme = 'Comprobantes en ruta' + '-' + vendedorTexto + '-' + tipoPedidoTexto + '-' + estadoPedidoTexto;
                            let laTabla = response.html;
                            contienes.empty();
                            if ($.fn.dataTable.isDataTable('#myTable')) {
                                contienes.DataTable().destroy();
                            }
                            contienes.html(laTabla);
                            var tablaDataTables = contienes.DataTable({
                                "pageLength": 5,
                                "lengthMenu": [
                                    [5, 10, 25, 50, 100, -1],
                                    [5, 10, 25, 50, 100, "Todos"]
                                ],

                                "orderCellsTop": true,
                                "columnDefs": [
                                    // {
                                    //     "targets": 0,
                                    //     "orderable": false,
                                    //     "searchable": false,
                                    //     "render": function(data, type, row, meta) {
                                    //         return meta.row + 1;
                                    //     }
                                    // },
                                    // habilitar solo si soy desktop.
                                    // {
                                    //     "targets": [3],
                                    //     "render": $.fn.dataTable.render.number('.', ',', 2, simbIzq)
                                    // }

                                ],
                                // sin footer
                                // "footerCallback": function(row, data, start, end, display) {
                                //     var api = this.api();

                                //     api.columns([3]).every(function() {
                                //         var column = this;
                                //         var sum = column
                                //             .data()
                                //             .reduce(function(a, b) {
                                //                 return parseFloat(a) + parseFloat(b);
                                //             }, 0);

                                //         $(column.footer()).html(
                                //             $.fn.dataTable.render.number('.', ',', 2, simbIzq).display(sum)
                                //         );
                                //     });
                                // },
                                dom: 'frtip',
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



                            });
                        }
                    },
                    error: function(x, e) {
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar');
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
        });

        function renderDetallesRemito(data) {
            //* jsn que recibo
            // "fechaRemito": "2025-05-05",
            // "FechaRemitoB": "05\/05\/2025",
            // "nroRemito": "0014-00000021",
            // "codMovRemito": "805878",
            // "nroPedido": "0014-00000021",
            // "codMovPedido": "805858",
            // "FechaPedidoB": "29\/04\/2025",
            // "nroFactura": null,
            // "codMovFactura": null,
            // "FechaFacturaB": null,
            // "entregado": "Si",
            // "id_usuario_no_entrega": null,
            // "motivo_no_entrega": null,
            // "detalle_no_entrega": null,
            // "fecha_hora_entrega": "2025-05-18T16:44",
            // "fechaHoraEntregaB": "18\/05\/2025 16:44",
            // "cliente": "BERTOLO ALEJANDRA (11758)",
            // "nombre_chofer": null,
            // "nombreUsuarioEntrega": null,
            // "nombreChoferEntrega": null,
            // "desc_ruta": null,
            // "estado_ruta": null,
            // "totalRemito": "241821.61",
            // "totalPedido": "241821.61",
            // "totalFactura": null
            console.log('detalles:', data);
            // Limpia el div
            const detalles = document.getElementById('modal-detalles');
            detalles.innerHTML = '';
            const info = data.data;

            // Sección Estado de Entrega
            let estadoEntrega = '';
            if (info.entregado === 'Si') {
                estadoEntrega = `
                    <div class="estado-entrega entregado">
                        <div class="titulo-estado"> <i class="fas fa-check-circle"></i> <span>Entregado</span></div>
                        <div class="detalle-estado">
                            <span><i class="far fa-clock  fa-lg fa-fw entregado"></i> ${info.fechaHoraEntregaB || '-'}</span>
                            
                        </div>
                    </div>
                `;
            } else {
                //si entregado es no y nombreUsuarioNoEntrega es null va estado de entrega sino es sin datos
                if(info.nombreUsuarioNoEntrega == null){
                    estadoEntrega = `
                    <div class="estado-entrega">
                        <span>Sin datos</span>
                        
                    </div>
                `;
                }
                else{
                estadoEntrega = `
                    <div class="estado-entrega no-entregado">
                        <div class="titulo-estado"><i class="fas fa-times-circle"></i> <span>No Entregado</span></div>
                        <div class="detalle-estado">
                            <span><i class="fas fa-exclamation-triangle  fa-lg fa-fw no-entregado"></i> Motivo: <strong>${info.motivo_no_entrega || '-'}</strong></span>
                            <span><i class="fas fa-user  fa-lg fa-fw no-entregado"></i> ${info.nombreUsuarioNoEntrega || '-'}</span>
                            <span><i class="fas fa-truck  fa-lg fa-fw no-entregado"></i> ${info.nombre_chofer || '-'}</span>
                            <span><i class="fas fa-comment-dots  fa-lg fa-fw no-entregado"></i> ${info.detalle_no_entrega || '-'}</span>
                        </div>
                    </div>
                `;
                }
            }

            // Sección Trazabilidad 

            let trazabilidad = `
                <div class="seccion seccion-trazabilidad">
                    <h3><i class="fas fa-link fa-lg"></i> Trazabilidad</h3>
                    ${info.nroPedido ? `
                        <div class="traza-item">
                            <i class="fas fa-clipboard-list fa-lg"></i>
                            <span> Pedido: <strong class="no-corta">${info.nroPedido}</strong></span>
                            <span class="fecha">Fecha: <strong>${info.FechaPedidoB || '-'}</strong></span>
                            <span>Total: <strong>$${Number(info.totalPedido).toLocaleString('es-AR', {minimumFractionDigits:2})}</strong></span>
                        </div>
                    ` : ''}
                    ${info.nroFactura ? `
                        <div class="traza-item">
                            <i class="fas fa-file-invoice fa-lg"></i>
                            <span>Factura: <strong class="no-corta">${info.nroFactura}</strong></span>
                            <span class="fecha">Fecha: <strong>${info.FechaFacturaB || '-'}</strong></span>
                            <span>Total: <strong>$${Number(info.totalFactura).toLocaleString('es-AR', {minimumFractionDigits:2})}</strong></span>
                        </div>
                    ` : ''}
                </div>
            `;

            // Sección Ruta y Chofer
            let rutaChofer = `
                <div class="seccion seccion-ruta">
                    <h3><i class="fas fa-route "></i> Ruta y Chofer</h3>
                    <div class="ruta-item">
                        <i class="fas fa-map-signs"></i> Ruta: <strong>${info.desc_ruta || '-'}</strong>
                    </div>
                    <div class="ruta-item">
                        <i class="fas fa-flag-checkered"></i> Estado Ruta: <strong>${info.estado_ruta || '-'}</strong>
                    </div>
                    <div class="ruta-item">
                        <i class="fas fa-truck"></i> Chofer: <strong>${info.nombre_chofer || '-'}</strong>
                    </div>
                </div>
            `;

            // Sección Preparación
            let preparacion = `
                <div class="seccion seccion-preparacion">
                    <h3><i class="fas fa-box-open fa-lg"></i> Preparación</h3>
                    <div class="prep-item">
                        Cliente: <strong>${info.cliente || '-'}</strong>
                    </div>
                    <div class="prep-item">
                        Total Remito: <strong>$${Number(info.totalRemito).toLocaleString('es-AR', {minimumFractionDigits:2}) || '-'}</strong>
                    </div>
                    
                </div>
            `;

            // Renderiza todo
            detalles.innerHTML = `
                ${estadoEntrega}
                ${trazabilidad}
                ${rutaChofer}
                ${preparacion}
            `;
        }
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
                        <label for="inputClientes"> Cliente:</label>
                        <input type="text" id="inputClientes" placeholder="Buscar cliente..."  style="width: 100%;">
                        <input type="hidden" id="inputClientesId" name="inputClientesId">
                    <!-- </div> -->
                    <!-- <div class="control">
                        <label for="inputChoferes">Chofer:</label>
                        <input type="text" id="inputChoferes" placeholder="Buscar chofer...">
                        <input type="hidden" id="inputChoferesId" name="inputChoferesId">
                    </div> -->
                    <!-- <div class="control"> -->
                        <label for="inputRutas">Ruta:</label>
                        <input type="text" id="inputRutas" placeholder="Buscar hoja de ruta..." style="width: 100%;">
                        <input type="hidden" id="inputRutasId" name="inputRutasId">
                    </div>
                </div>
                <div class='panelesBloqueInforme'>
                    <div class="control">
                        <label for="entregadoRemito" class="parametros">Estado:
                            <select name="entregadoRemito" id="entregadoRemito">
                                <option value="">Todos</option>
                                <option value="SinDatos">Sin datos</option>                                
                                <option value="Si">Entregado </option>
                                <option value="No">No Entregado</option>

                            </select>
                        </label>
                    </div>

                    <div class="control">
                        <label for="campoBusca" class="parametros">Buscar por:
                            <select name="campoBusca" id="campoBusca">
                                <option value=""> </option>
                                <option value="Fecha" selected="selected">Fecha</option>
                                <option value="NroComprobante">Número</option>
                                <!-- <option value="TipoPedido">Tipo </option> -->
                            </select>
                        </label>
                    </div>
                    <div id="buscaFecha" class="control">
                        <label for="fechaDesde" class="parametros">Desde: <input type="date" name="fechaDesde" id="fechaDesde"></label>
                        <label for="fechaHasta" class="parametros">Hasta: <input type="date" name="fechaHasta" id="fechaHasta"></label>
                    </div>

                    <div id="buscaNumero" class="control" style="display:none">
                        <label for="numeroComp" class="parametros">Nº Comprob:
                            <input type="text" name="numeroComp" id="numeroComp">
                        </label>

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

        <div id="spinner" class="spinner" style="display:none;">
            <img src="_img/logo-administranet-ecommerce.png">
            <div class="texto">Procesando...</div>
        </div>

        <div class="paneles" id="contiene-tabla">
            <h1>Comprobantes en ruta</h1>
            <table class="display" id="myTable" data-page-length='10'></table>

        </div>
        <!-- Modales -->
        <div id="modal-actualiza" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Actualizar Comprobante </h2>
                    <span class="close-btn" onclick="cerrarModal()">X</span>
                    
                </div>
                 <div id="modal-titulo" class="modal-titulo"></div>
                <div class="modal-body">
                    <form id="form-actualizar" action="" method="POST">
                       

                        <label for="selectEntregado">Estado de entrega:</label>
                        <select name="selectEntregado" id="selectEntregado" required>
                            <option value="">Seleccionar</option>
                            <option value="Si">Entregado</option>
                            <option value="No">No Entregado</option>
                        </select>

                        <!-- <div id="motivo-entrega" > -->
                        <!-- <div id="motivo-entrega" style="display: none;" class="motivo-entrega">
                            <label for="fechaHoraEntrega">Fecha y Hora de Entrega:</label>
                            <input type="datetime-local" name="fechaHoraEntrega" id="fechaHoraEntrega">
                        </div> -->

                        <!-- <div id="motivo-no-entrega"> -->
                        <div id="motivo-no-entrega" style="display: none;" class="motivo-no-entrega">


                            <label for="selectMotivo">Motivo:</label>
                            <select name="selectMotivo" id="selectMotivo">
                                <option value="">Seleccione un motivo</option>
                                <?php foreach ($motivos_no_entrega as $motivo): ?>
                                    <option value="<?= $motivo ?>"><?= $motivo ?></option>
                                <?php endforeach; ?>
                            </select>
                            <!-- <label for="selectChoferModal">Chofer|Usuario</label>
                            <select name="selectChoferModal" id="selectChoferModal">
                                <option value="">Seleccione un chofer-usuario</option>

                            </select> -->
                            <label for="detalleNoEntrega">Detalle:</label>
                            <textarea name="detalleNoEntrega" id="detalleNoEntrega" rows="3"></textarea>
                        </div>
                        <input type="hidden" name="codigoMovimientoRemito" id="codigoMovimientoRemito">
                        <input type="hidden" name="codigoMovimientoPedido" id="codigoMovimientoPedido">
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="boton gris" onclick="cerrarModal()">Cancelar</button>
                    <button class="boton azul" onclick="guardarDatos()">Guardar</button>
                </div>
            </div>
        </div>
        <!-- Modal para ver mas informacion solo boton cerrar o cancelar -->
        <div id="modal-ver-mas" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Detalles del Comprobante</h2>
                    <span class="close-btn" onclick="cerrarModalVerMas()">X</span>
                </div>
                <div id="modal-titulo-ver-mas" class="modal-titulo"></div>
                <div class="modal-body">
                    <div id="modal-detalles" class="modal-detalles"></div>      
                </div>

                <div class="modal-footer">
                    <button class="boton gris" onclick="cerrarModalVerMas()">Cerrar</button>  
                </div>
            </div>
        </div>
                                    






<!-- fin modales -->
    </div>

    <?php require_once 'footer.php'; ?>

    </div>
    <script>
        function abrirModal(comprobante) {
            //  function abrirModal(codMovRemito, codMovPedido, nroRemito) {    
            //console.log('comprobante',comprobante);
            let datosRemito = "";
            document.getElementById('modal-actualiza').style.display = 'flex';
            const codMovRemito = comprobante.getAttribute('data-codmov-remito');
            const codMovPedido = comprobante.getAttribute('data-codmov-pedido');
            const nroRemito = comprobante.getAttribute('data-nro-remito');
            const fechaRemito = comprobante.getAttribute('data-fecha-remito');
            const totalRemito = comprobante.getAttribute('data-total-remito');
            const cliente = comprobante.getAttribute('data-cliente');
            document.getElementById('codigoMovimientoRemito').value = codMovRemito;
            document.getElementById('codigoMovimientoPedido').value = codMovPedido;

            datosRemito += "<span>Fecha: <strong>" + fechaRemito + "</strong></span>";
            datosRemito += "<span>Remito: <strong>" + nroRemito + "</strong></span>";
           
            datosRemito += "<span>Total: <strong>" + totalRemito + "</strong></span>";
            datosRemito += "<span>Cliente: <strong>" + cliente + "</strong></span>";

            document.getElementById('modal-titulo').innerHTML = datosRemito;
            obtenerChoferesTodos();

        }

        function obtenerInfoRemito(codMovRemito, callback) {
            fetch('relay-logistica-comprobantes.php?obtenerInfo=1&codMovRemito=' + encodeURIComponent(codMovRemito))
                .then(response => response.json())
                .then(data => callback(data))
                .catch(error => {
                    Swal.fire("Error", "No se pudo obtener la información del remito.", "error");
                });
        }

        // 2. Función para abrir la modal y armar el HTML
        function abrirVerMas(comprobante) {
             let cabeceraInfo = "";
            document.getElementById('modal-ver-mas').style.display = 'flex';
            const codMovRemito = comprobante.getAttribute('data-codmov-remito');
            const nroRemito = comprobante.getAttribute('data-nro-remito');
            const fechaRemito = comprobante.getAttribute('data-fecha-remito');
            const totalRemito = comprobante.getAttribute('data-total-remito');
            //const cliente = comprobante.getAttribute('data-cliente');
            // cabecera
            cabeceraInfo += "<span>Fecha: <strong>" + fechaRemito + "</strong></span>";
            cabeceraInfo += "<span>Remito: <strong>" + nroRemito + "</strong></span>";
            // cabeceraInfo += "<span>Total: <strong>" + totalRemito + "</strong></span>";
            //cabeceraInfo += "<span>Cliente: <strong>" + cliente + "</strong></span>";

            document.getElementById('modal-titulo-ver-mas').innerHTML = cabeceraInfo;
            // Llama a la función AJAX
            obtenerInfoRemito(codMovRemito, function(data) {
                // Aquí armas el HTML con los datos recibidos
                 
                renderDetallesRemito(data);
            });
        }


        

        function obtenerChoferesTodos() {
            const selectChoferModal = document.getElementById('selectChoferModal');
            const url = 'relay-logistica-comprobantes.php?action=obtenerChoferesTodos';
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    selectChoferModal.innerHTML = '<option value="">Seleccione un chofer</option>';
                    if (data.results && Array.isArray(data.results)) {
                        data.results.forEach(chofer => {
                            const option = document.createElement('option');
                            option.value = chofer.id;
                            option.textContent = chofer.text;
                            selectChoferModal.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function cerrarModal() {
            const form = document.getElementById('form-actualizar');
            form.reset();
            limpiarSelectEntregado();
            document.getElementById('modal-actualiza').style.display     = 'none';
        }

        function cerrarModalVerMas() {
            document.getElementById('modal-ver-mas').style.display = 'none';
        }

        function limpiarSelectEntregado() {
            const select = document.getElementById('selectEntregado');
            select.classList.remove('si', 'no', 'select-entregado');
            // Elimina cualquier icono insertado
            select.querySelectorAll('.fa-check-circle').forEach(el => el.remove());
            // Opcional: resetea el valor
            select.value = '';

            // Oculta los motivos
            const motivoSectionNo = document.getElementById('motivo-no-entrega');
            const motivoSectionSi = document.getElementById('motivo-entrega');
            if (motivoSectionNo) motivoSectionNo.style.display = 'none';
            if (motivoSectionSi) motivoSectionSi.style.display = 'none';
        }

        function validarDatosEntrega() {
            const entregado = document.getElementById('selectEntregado').value;
            const motivo = document.getElementById('selectMotivo').value;
           // const chofer = document.getElementById('selectChoferModal').value;
            //const fechaHoraEntrega = document.getElementById('fechaHoraEntrega').value;

            if (entregado === 'No' && motivo === '' ) {
                Swal.fire({
                    title: "Error",
                    text: "Si no está entregado, debe completar el motivo y elegir un chofer.",
                    icon: "error",
                    confirmButtonColor: '#395aa2',
                });
                return false;
            }

            // if (entregado === 'Si' && fechaHoraEntrega === '') {
            //     Swal.fire({
            //         title: "Error",
            //         text: "Si está entregado, debe completar la fecha y hora de entrega.",
            //         icon: "error",
            //         confirmButtonColor: '#395aa2',
            //     });
            //     return false;
            // }

            return true;
        }

        function guardarDatos() {
            // validar si es entregado si , tiene que guardar la fecha hora obligado

            // validar que si es entregado en no, tiene que completar el motivo, elegir un chofer y un detalle el detall no es obligatorios
            if (!validarDatosEntrega()) {
                return;
            }
            const form = document.getElementById('form-actualizar');
            const formData = new FormData(form);
            formData.append('guardarDatosEntrega', 1);
            const url = 'relay-logistica-comprobantes.php';
            // const spinner = document.getElementById('spinner');
            // spinner.style.display = 'flex';

            fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // spinner.style.display = 'none';
                    if (data.msg === 'ok') {
                        Swal.fire({
                            title: "Éxito",
                            text: data.mensaje,
                            icon: "success",
                            confirmButtonColor: '#395aa2',
                        });
                        // Aquí puedes actualizar la tabla o realizar otras acciones necesarias
                        const botonBusca = document.getElementById('botonBuscar');
                        botonBusca.click(); // Simula un clic en el botón de búsqueda para actualizar la tabla
                        cerrarModal();
                        // Actualizar la tabla o realizar otras acciones necesarias
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: data.error,
                            icon: "error",
                            confirmButtonColor: '#395aa2',
                        });
                    }
                })
                .catch(error => {
                    // spinner.style.display = 'none';
                    console.error('Error:', error);
                    Swal.fire({
                        title: "Error",
                        text: "Ocurrió un error al guardar los datos.",
                        icon: "error",
                        confirmButtonColor: '#395aa2',
                    });
                });
        }

        document.getElementById('selectEntregado').addEventListener('change', function() {
            const select = this;
            const motivoSectionNo = document.getElementById('motivo-no-entrega');
            const motivoSectionSi = document.getElementById('motivo-entrega');

            // Limpiar clases previas
            select.classList.remove('si', 'no');
            select.classList.remove('select-entregado');
            // Quitar iconos previos
            select.querySelectorAll('.fa-check-circle').forEach(el => el.remove());

            // Aplicar estilos y agregar icono según selección
            if (select.value === 'Si') {
                select.classList.add('si', 'select-entregado');
                // Agregar icono verde
                select.insertAdjacentHTML('afterbegin', '<i class="fas fa-check-circle" style="color:#28a745"></i>');
            } else if (select.value === 'No') {
                select.classList.add('no', 'select-entregado');
                // Agregar icono rojo
                select.insertAdjacentHTML('afterbegin', '<i class="fas fa-check-circle" style="color:#dc3545"></i>');
            }

            // Mostrar/ocultar motivos y aplicar estilos
            motivoSectionNo.style.display = select.value === 'No' ? 'block' : 'none';
            motivoSectionSi.style.display = select.value === 'Si' ? 'block' : 'none';
        });
    </script>
</body>

</html>