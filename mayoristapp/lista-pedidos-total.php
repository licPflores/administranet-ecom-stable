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
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>administraNET e-com | Listado de pedidos</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <?php require_once 'cabecera.php'; ?>


    <script>
        $.extend($.fn.dataTable.defaults, {
            searching: true,
            responsive: false,
            ordering: false,
            "language": {
                "emptyTable": "No se encontraron resultados",
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
							if(column!=11){
                                data = dinero.format(data);
							}
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

                                const arrColumnasNumero = [5];
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

                            const arrColumnasNumero = [5];

                            if (arrColumnasNumero.includes(column)) {

                                var numero = data.replace(/[$.]/g, '');

                                numero = numero.replace(',', '.');
                                // console.log("data coma x punto: "+numero);
                                data = numero;


                            }
                            // console.log('soy la columna del anulado',data,column);
                            if (column == 12) {
                                // console.log('soy la columna del anulado', data, column);
                                var anulado = data.replace(/<\/?[^>]+(>|$)/g, "");


                                //anulado = anulado.replace('</strong>,', '');
                                // console.log("data coma x punto: "+numero);
								// console.log('como va el anulado?¿',anulado);
                                data = anulado;
                            }
                            return data;
                        },
                        footer: function(data, row, column, node) {
                            // console.log('datos del pie',data,row,column,node);
                            const arrColumnasNumero = [6];

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

            function datosFiltrosActuales() {
                return {
                    "vendedor": "true",
                    "campoBusca": $('#campoBusca').val(),
                    "campoBuscaTexto": $('#campoBusca option:selected').text(),
                    "fechaDesde": $('#fechaDesde').val(),
                    "fechaHasta": $('#fechaHasta').val(),
                    "numeroComp": $('#numeroComp').val(),
                    "tipoPedido": $('#tipoPedido').val(),
                    "tipoPedidoTexto": $('#tipoPedido option:selected').text(),
                    "estadoPedido": $('#estadoPedido').val(),
                    "estadoPedidoTexto": $('#estadoPedido option:selected').text(),
                    "filtraVendedor": $('#filtraVendedor').val(),
                    "filtraVendedorTexto": $('#filtraVendedor option:selected').text(),
                    "tipoInforme": $('#tipoInforme').val(),
                    "tipoInformeTexto": $('#tipoInforme option:selected').text(),
                    "listaPed": $('#listaTodos').val(),
                    "listaPedTexto": $('#listaTodos option:selected').text()
                };
            }

            function setActionButtonLoading($btn, texto) {
                $btn.html('<i class="fa-solid fa-circle-notch fa-spin"></i> ' + texto);
                //$btn.prop('disabled', true);
                $('#botonBuscar, #botonExportPdf, #botonExportExcel').prop('disabled', true);
                
            }

            function resetActionButton($btn, htmlOriginal) {
                $('#botonBuscar, #botonExportPdf, #botonExportExcel').prop('disabled', false);
                $btn.prop('disabled', false);
                $btn.html(htmlOriginal);
            }

            function htmlBotonDetalle(visible) {
                if (visible) {
                    return '<i class="fa fa-chevron-up barrita fa-lg fa-2x"></i>';
                }

                return '<i class="fa fa-list-ul barrita fa-lg fa-2x"></i>';
            }

            function descargarInforme(formato) {
                var filtros = datosFiltrosActuales();
                var form = $('<form>', {
                    method: 'POST',
                    action: 'relay-pedidos.php'
                });

                form.append($('<input>', { type: 'hidden', name: 'exportarArchivo', value: '1' }));
                form.append($('<input>', { type: 'hidden', name: 'formato', value: formato }));

                $.each(filtros, function(clave, valor) {
                    form.append($('<input>', { type: 'hidden', name: clave, value: valor }));
                });

                $('body').append(form);
                form.submit();
                form.remove();
            }

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

            // aca atacch a los eventos del spinner funcionando.


            // para que se borren lo que tienen adentro las fechas   
            $('#fechaDesde').focus(function() {
                $('#fechaDesde').val('');
            });
            $('#fechaHasta').focus(function() {
                $('#fechaHasta').val('');
            });
            $('#campoBusca').change(function() {
                var valor = $(this).val();
                // voy a corroborar que div mostrar
                $('#fechaDesde').val('dd/mm/aaaa'),
                    $('#fechaHasta').val('dd/mm/aaaa'),
                    $('#numeroComp').val('');
                switch (valor) {
                    case 'Fecha':
                        $('#buscaNumero').hide();
                        //    $('#buscaTipo').hide();
                        $('#buscaFecha').show(400);
                        break;
                    case 'NroComprobante':
                        $('#buscaFecha').hide();
                        //    $('#buscaTipo').hide();
                        $('#buscaNumero').show(400);
                        break;
                    case '':
                        // $('#buscaTipo').hide();
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;
                    case 'TipoPedido':
                        // $('#buscaTipo').show(400);
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;
                }

            });
            // boton para buscar coincidencias
            $('#botonBuscar').click(function() {
                let botonBusca = $(this);
                var htmlBuscarOriginal = '<i class="fas fa-check fa-lg fa-fw"></i> Buscar';

                var contienes = $('#myTable');
                var filtros = datosFiltrosActuales();
                var campoBusca = filtros.campoBusca,
                    fechaDesde = filtros.fechaDesde,
                    fechaHasta = filtros.fechaHasta,
                    numeroComp = filtros.numeroComp,
                    tipoPedido = filtros.tipoPedido,
                    estadoPedido = filtros.estadoPedido,
                    filtraVendedor = filtros.filtraVendedor,
                    tipoInforme = filtros.tipoInforme,
                    listaPed = filtros.listaPed;
                var tituloListado = "",
                    mensajeTop = "",
                    nombreInforme = "";
                var clienteTexto, tipoPedidoTexto, estadoPedidoTexto, vendedorTexto;

                tipoPedidoTexto = $('#tipoPedido option:selected').text();
                estadoPedidoTexto = $('#estadoPedido option:selected').text();
                vendedorTexto = $('#filtraVendedor option:selected').text();
                // console.log('datos a pasra al titulo',clienteTexto,tipoPedidoTexto,estadoPedidoTexto,vendedorTexto);
                var datosLogin = obtenerUsuarioLogueado();
                // console.log('usuario Session:',datosLogin );
                // buscar cliente seleccionado.

                $.ajax({
                    type: 'GET',
                    url: 'relay-clientes.php',
                    data: {
                        "traeDatosClienteSeleccionado": 1,

                    },
                    success: function(response) {
                        console.log('objeto cliente seleccionado', response.length);
                        let resultado = response.length;
                        if (resultado != 0) {
                            clienteTexto = response[0].cliente + ' (Cod: ' + response[0].Codigo + ')';
                        }
                        // cero si viene vacio.

                        // console.log('cliente',response[0].cliente);
                        // console.log('codigo',response[0].Codigo);

                    },
                    error: function(x, e) {
                        resetActionButton(botonBusca, htmlBuscarOriginal);
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


                //$('formBusca').submit();
                setActionButtonLoading(botonBusca, 'Espere...');
                $.ajax({
                    type: 'POST',
                    url: 'relay-pedidos.php',
                    data: {
                        "ajax": "true",
                        "vendedor": filtros.vendedor,
                        "campoBusca": filtros.campoBusca,
                        "fechaDesde": filtros.fechaDesde,
                        "fechaHasta": filtros.fechaHasta,
                        "numeroComp": filtros.numeroComp,
                        "tipoPedido": filtros.tipoPedido,
                        "estadoPedido": filtros.estadoPedido,
                        "filtraVendedor": filtros.filtraVendedor,
                        "tipoInforme": filtros.tipoInforme,
                        "listaPed": filtros.listaPed

                    },
                    success: function(response) {
                        resetActionButton(botonBusca, htmlBuscarOriginal);
                        var fecha = new Date();
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
                        console.log(formattedDate);
                        let simbIzq = "$";

                        // nombre archivo 
                        nombreInforme += "listado_pedidos_" + fechaFormateada;
                        tituloListado += "Listado de Pedidos";
                        // fecha
                        if (campoBusca == "Fecha" && fechaDesde != "" && fechaHasta != "") {

                            mensajeTop += " Rango: " + fechaDesde + " al " + fechaHasta + "\n";
                        }
                        // por numero de comprobante
                        if (campoBusca == "NroComprobante" && numeroComp != "") {
                            mensajeTop += " N° Comprobante: " + numeroComp + "\n";
                        }
                        // clientes
                        if (listaPed != "todos" && clienteTexto != "") {
                            // soy cliente seleccionado pero puedo no tener un cliente seleccionado.

                            mensajeTop += " Cliente: " + clienteTexto + "\n";
                        }

                        if (tipoPedido != "") {
                            mensajeTop += " Tipo: " + tipoPedidoTexto + "\n";
                        }
                        if (estadoPedido != "") {
                            mensajeTop += " Estado: " + estadoPedidoTexto + "\n";
                        }

                        // vendedor
                        mensajeTop +=" Vendedor: "+vendedorTexto +"\n";

                        mensajeTop += " Emitido: " + formattedDate + "\n";
						console.log('datos LOgin: ',datosLogin);
						if(datosLogin!=undefined){
							mensajeTop += " Generado por: " + datosLogin.vendedor.nombre_usuario + " " + datosLogin.vendedor.apellido_usuario + "\n";
						}
                        if ($.fn.dataTable.isDataTable('#myTable')) {
                            contienes.DataTable().clear().destroy();
                        }
                        contienes.html(response);
                        var tablaDataTables = contienes.DataTable({
                            "pageLength": 5, // Define el número de filas por página
                            "lengthMenu": [
                                [5, 10, 25, 50, 100, -1],
                                [5, 10, 25, 50, 100, "Todos"]
                            ],

                            "orderCellsTop": true,
                            "columnDefs": [
                                {
                                    "targets": 0, // Esta será la columna para el número de fila (se crea de manera dinámica)
                                    "orderable": false, // No queremos que sea ordenable
                                    "searchable": false, // No queremos que sea parte de la búsqueda
                                    "render": function(data, type, row, meta) {
                                        return meta.row + 1; // Calcula el índice (meta.row devuelve el número de fila 0-indexado)
                                    }
                                },
                                {
                                    "targets": [6],
                                    "render": $.fn.dataTable.render.number('.', ',', 2, simbIzq)
                                }

                            ],
                            // fixedColumns:   {
                            //     leftColumns: 1,
                            //     rightColumns: 1
                            // },
                            "footerCallback": function(row, data, start, end, display) {
                                var api = this.api();

                                // Loop through each column you want to update in the footer
                                api.columns([6]).every(function() {
                                    var column = this;
                                    // Use reduce to sum the data
                                    var sum = column
                                        .data()
                                        .reduce(function(a, b) {
                                            return parseFloat(a) + parseFloat(b);
                                        }, 0);

                                    // Update footer with formatted number
                                    $(column.footer()).html(
                                        $.fn.dataTable.render.number('.', ',', 2, simbIzq).display(sum)
                                    );
                                });
                            },
                            "createdRow": function(row, data, dataIndex) {
                                if (data[11] === '<strong>Si</strong>') {
                                    // Apply red color to the entire row
                                    $(row).css('color', 'red');
                                }
                            },
                            dom: 'lfrtip',



                        });


                        // $("#spinner").hide();
                    },
                    error: function(x, e) {
                        resetActionButton(botonBusca, htmlBuscarOriginal);
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
            /*
             * VISUALIZAR COMPROBANTE
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

            $(document).on('click', '.toggleDetallePedido', function(event) {
                event.preventDefault();
                var btn = $(this);
                var tr = btn.closest('tr');
                var tabla = $('#myTable').DataTable();
                var row = tabla.row(tr);
                var codigoMovimiento = btn.attr('mov');
                var detalleHtml = tr.data('detalleHtml');

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    btn.html(htmlBotonDetalle(false));
                    btn.attr('title', 'Ver detalle');
                    return;
                }

                if (detalleHtml) {
                    row.child('<div class="detalle-child-row">' + detalleHtml + '</div>').show();
                    tr.addClass('shown');
                    btn.html(htmlBotonDetalle(true));
                    btn.attr('title', 'Ocultar detalle');
                    return;
                }

                btn.html('<i class="fa-solid fa-circle-notch fa-spin fa-lg fa-fw"></i>');
                btn.attr('title', 'Cargando detalle');

                $.ajax({
                    type: 'POST',
                    url: 'relay-pedidos.php',
                    data: {
                        "ajaxDetallePedido": 1,
                        "codMovPedido": codigoMovimiento
                    },
                    success: function(response) {
                        tr.data('detalleHtml', response);
                        row.child('<div class="detalle-child-row">' + response + '</div>').show();
                        tr.addClass('shown');
                        btn.html(htmlBotonDetalle(true));
                        btn.attr('title', 'Ocultar detalle');
                    },
                    error: function() {
                        btn.html(htmlBotonDetalle(false));
                        btn.attr('title', 'Ver detalle');
                        alert('No se pudo cargar el detalle del pedido.');
                    }
                });
            });

            $('#botonExportPdf').on('click', function() {
                var $btn = $(this);
                var htmlOriginal = '<i class="fas fa-file-pdf fa-lg fa-fw"></i> Descargar PDF';
                setActionButtonLoading($btn, 'Espere...');
                descargarInforme('pdf');
                setTimeout(function() {
                    resetActionButton($btn, htmlOriginal);
                }, 2000);
            });

            $('#botonExportExcel').on('click', function() {
                var $btn = $(this);
                var htmlOriginal = '<i class="fas fa-file-excel fa-lg fa-fw"></i> Descargar Excel';
                setActionButtonLoading($btn, 'Espere...');
                descargarInforme('excel');
                setTimeout(function() {
                    resetActionButton($btn, htmlOriginal);
                }, 2000);
            });

        });
    </script>
    <style>
        .detalle-child-row {
            padding: 12px 14px;
            background: linear-gradient(180deg, #f7f9fe 0%, #eef2fb 100%);
            border-left: 4px solid #2a3e72;
        }

        .detalle-card {
            box-shadow: 0 6px 18px rgba(42, 62, 114, 0.08);
        }

        .tabla-detalle-pedido {
            font-size: 12px;
        }

        .tabla-detalle-pedido th {
            text-transform: uppercase;
            font-size: 10.5px;
            letter-spacing: 0.35px;
        }

        .tabla-detalle-pedido td {
            vertical-align: middle;
        }

        .tabla-detalle-pedido tbody tr:nth-child(even) td {
            background: #f9fbff;
        }

        .tabla-detalle-pedido tbody tr:hover td {
            background: #eef3ff;
        }

        .detalle-cantidad {
            min-width: 128px;
        }

        .detalle-cantidad-numero {
            font-size: 14px;
        }

        .detalle-cantidad-badge {
            box-shadow: inset 0 -1px 0 rgba(42, 62, 114, 0.12);
        }

        .detalle-cantidad-meta {
            letter-spacing: 0.2px;
        }

        .pedido-gestion {
            min-width: 120px;
            line-height: 1.15;
        }

        .pedido-gestion-tipo {
            color: #20335f;
            font-weight: 600;
        }

        .pedido-gestion-estado {
            margin-top: 3px;
            font-weight: 700;
        }

        .pedido-gestion-estado.facturado {
            color: var(--facturado);
        }

        .pedido-gestion-estado.pendiente {
            color: var(--pendiente);
        }

        .pedido-gestion-estado.parcial {
            color: var(--parcial);
        }

        .pedido-gestion-estado.cerrado {
            color: var(--cerrado);
        }

        .pedido-gestion-estado.preparado {
            color: var(--preparado);
        }

        .pedido-gestion-estado.pendienteRemito {
            color: var(--pendiente-remito);
        }

        .pedido-gestion-estado.promocion {
            color: var(--producto-promocion);
        }

        .pedido-gestion-autorizado {
            margin-top: 3px;
            color: #5f6b85;
            font-size: 11px;
        }

        .toggleDetallePedido {
            color: inherit;
        }

        .toggleDetallePedido:hover {
            text-decoration: none;
        }
    </style>
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
                        <label for="estadoPedido">Clientes: </label>
                        <select name="listaTodos" id="listaTodos">
                            <option value="cliente">Seleccionado</option>
                            <option selected value="todos">Todos </option>
                        </select>
                    </div>

                    <div class="control">
                        <label for="filtraVendedor">Vendedor / Viajante: </label>
						<select name="filtraVendedor" id="filtraVendedor">

							<?php if ($codPuesto == 1): ?>
								<option value="todos">- Todos -</option>
								<?php
								if (!empty($arrVendedores)) {
									foreach ($arrVendedores as $vendedor) {
										echo '<option value="' . $vendedor['valor'] . '">' . $vendedor['texto'] . '</option>' . PHP_EOL;
									}
								}
								?>
							<?php endif; ?>
							<?php if ($codPuesto != 1): ?>
								<?php
								if (!empty($arrVendedores)) {
									foreach ($arrVendedores as $vendedor) {
										echo '<option selected="selected"  value="' . $vendedor['valor'] . '">' . $vendedor['texto'] . '</option>' . PHP_EOL;
									}
								}
								?>
							<?php endif; ?>

						</select>
                    </div>

                    <div class="control">
                        <label for="estadoPedido" class="parametros">Estado:</label>
						<select name="estadoPedido" id="estadoPedido">
							<option value="">Todos</option>
							<option value="En Remito">En Remito</option>
							<option value="Facturado">Facturado</option>
							<option value="Pendiente">Pendiente</option>

							<option value="Imput manual">Imput manual</option>
							<option value="Aprobado">Aprobado</option>
							<option value="Completo">Completo</option>
							<option value="Parcial">Parcial</option>
							<option value="Cerrado">Cerrado</option>
							<option value="En preparación">En preparación</option>
							<option value="Preparado">Preparado</option>
						</select>
                        
                    </div>
                    <div id="buscaTipo" class="control">
                        <label for="tipoPedido" class="parametros">Tipo:</label>
						<select id="tipoPedido" name="tipoPedido">
							<option value="">Todos</option>
							<option value="Sistema">Sistema</option>
							<option value="Web">Web Viajante</option>
							<option value="Web Cliente">Web Propío</option>
						</select>
                        

                    </div>

                </div>
                <div class='panelesBloqueInforme'>

                    <div class="control">
                        <label for="campoBusca" class="parametros">Buscar por:</label>
						<select name="campoBusca" id="campoBusca">
							<option value=""> </option>
							<option value="Fecha" selected="selected">Fecha</option>
							<option value="NroComprobante">Número</option>
							<option value="TipoPedido">Tipo Pedido</option>
						</select>
                    </div>

                    <div id="buscaFecha" class="control-fechas grid-column-0">
						<div class="control">
                        	<label for="fechaDesde" class="parametros">Desde: </label>
							<input type="date" name="fechaDesde" id="fechaDesde" value="<?php echo date('Y-m-d');?>">
							
                        </div>
						<div class="control">
							<label for="fechaHasta" class="parametros">Hasta: </label>
							<input type="date" name="fechaHasta" id="fechaHasta" value="<?php echo date('Y-m-d');?>">
							
						</div>
					</div>

                    <div id="buscaNumero" class="control" style="display:none">
                        <label for="numeroComp" class="parametros">Nº Comprob:</label>
                        <input type="text" name="numeroComp" id="numeroComp">
                    </div>
                </div>

                <div class='panelesBloqueInforme'>
                    <div class="control">
                        <label for="tipoInforme" class="parametros">Tipo de Informe:</label>
						<select name="tipoInforme" id="tipoInforme">
							<option value="resumen">Resumen </option>
							<option value="detallado">Detallado </option>
						</select>
                    </div>
                   
                </div>

                <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                            <i class="fas fa-check fa-lg fa-fw"></i> Buscar
                        </button>
                        <button title="Exportar PDF" alt="Exportar PDF" type="button" id="botonExportPdf" name="botonExportPdf" class="botonNuevo">
                            <i class="fas fa-file-pdf fa-lg fa-fw"></i> Descargar PDF
                        </button>
                        <button title="Exportar Excel" alt="Exportar Excel" type="button" id="botonExportExcel" name="botonExportExcel" class="botonNuevo">
                            <i class="fas fa-file-excel fa-lg fa-fw"></i> Descargar Excel
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
            <h1>Listado de pedidos</h1>
            <table class="display" id="myTable" data-page-length='10'></table>

        </div>
    </div>

    <?php require_once 'footer.php'; ?>

    </div>
    <div id="basic-modal-content"> </div>
</body>

</html>