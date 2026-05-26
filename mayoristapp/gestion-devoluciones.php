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
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>administraNET e-com | Listado de devoluciones</title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />


    <?php require_once 'cabecera.php'; ?>


    <script>
        $.extend($.fn.dataTable.defaults, {
            searching: false,
            responsive: false,
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
                [1, "desc"]
            ],
            dom: 'Bfrtip',
            buttons: [
                'excel',
                {
                    extend: 'pdf',
                    orientation: 'landscape'
                }
            ]
        });
        $(document).ready(function() {



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
                //         $('#fechaDesde').val('<?php echo date('1/m/Y'); ?>'),
                //         $('#fechaHasta').val('<?php echo date('d/m/Y'); ?>'),
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
                    case '-':
                        $('#buscaFecha').hide();
                        $('#buscaNumero').hide();
                        break;

                }

            });

            // busqueda de cliente
            $('#filtrarPor').change(function() {
                var filtro = $(this).val(),
                    listado = $('#seleccionFiltro');
                //console.log(filtro);
                if (filtro == "") {
                    return false;
                }

                $.ajax({
                    type: 'GET',
                    url: 'relay-devoluciones.php',
                    data: {
                        "ajax": "true",
                        "tabla": filtro,
                        "queAccion": "seleccion"


                    },
                    success: function(response) {
                        console.log(response);
                        var listaVuelta = jQuery.parseJSON(response);
                        listado.val("");
                        //                    console.log(listaVuelta);
                        //                    listado.empty();
                        //                    listado.html('<option value="">- todos -</option>');
                        //                    listado.append(response);
                        $("#seleccionFiltro").autocomplete({
                            source: listaVuelta,
                            select: function(event, ui) {
                                event.preventDefault();
                                //                        console.log(ui.item.label);
                                //                        console.log($(this).attr("alt"));
                                $(this).attr("alt", ui.item.value);
                                $(this).val(ui.item.label);
                            }

                        });
                        listado.focus();
                    },
                    error: function(x, e) {
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
             * Agregar filtro a la lista
             * @param {type} event
             * @returns {undefined}
             */
            $('#addFiltro').on('click', function() {
                var listaFiltro = $('#listaFiltro'),
                    textFiltro = $('#filtroSelec'),
                    filtro = $('#filtrarPor').val(),
                    seleccion = $('#seleccionFiltro').attr("alt").split("|");
                var tFiltro = textFiltro.val();
                //agregar item a la lista
                var indiceLi = listaFiltro.children().length + 1;
                if (seleccion !== "" && seleccion[1] !== undefined) {
                    listaFiltro.append('<li id="' + indiceLi + '"> <i class="fas fa-check-square fa-lg "></i> ' + filtro + ' - ' + seleccion[1] + ' <a class="borrarLi" rel="listaFiltro|' + indiceLi + '" href="#" title="Eliminar de la lista"><i class="fas fa-trash fa-lg fa-fw"></i></a></li>');
                    tFiltro = tFiltro + filtro + '|' + seleccion[0] + '|' + seleccion[1] + '|' + indiceLi + '||';
                    textFiltro.val(tFiltro);

                    $('.borrarLi').on('click', function() {
                        var valorLi = $(this).attr("rel").split("|"),
                            textFiltro = $('#filtroSelec'),
                            arrFiltro = $('#filtroSelec').val().split("||");
                        var ul = valorLi[0],
                            li = valorLi[1] - 1,
                            liObj = valorLi[1],
                            textoFiltro = "";

                        for (var po in arrFiltro) {
                            if (arrFiltro[po] != "") {
                                var arrLinea = arrFiltro[po].split('|');
                                var iLi = arrLinea[3] - 1;
                                if (iLi != li) {

                                    textoFiltro = textoFiltro + arrFiltro[po] + "||";
                                }
                            }
                        }
                        textFiltro.val(textoFiltro);
                        $('#' + ul + ' #' + liObj).remove();
                    });
                }

                $('#seleccionFiltro').attr("alt", "");
                $('#seleccionFiltro').val("");
                // agregar al input una lista
            });

            // boton para buscar coincidencias
            $('#botonBuscar').click(function() {
                $('#spinner').show()

                let botonBusca = $(this)
                botonBusca.prop('disabled', true);
                botonBusca.html('<i class="fa-solid fa-circle-notch fa-spin"></i> Espere');
                var contienes = $('#myTable'),
                    campoBusca = $('#campoBusca').val(),
                    fechaDesde = $('#fechaDesde').val(),
                    fechaHasta = $('#fechaHasta').val(),
                    numeroComp = $('#numeroComp').val(),
                    estadoPedido = $('#estadoPedido').val(),
                    filtrarPor = $('#filtroSelec').val();
                //$('formBusca').submit();

                $.ajax({
                    type: 'POST',
                    url: 'relay-devoluciones.php',
                    data: {
                        "ajax": "true",
                        "vendedor": "false",
                        "campoBusca": campoBusca,
                        "fechaDesde": fechaDesde,
                        "fechaHasta": fechaHasta,
                        "numeroComp": numeroComp,
                        "queAccion": "listar",
                        "estadoPedido": estadoPedido,
                        "filtrarPor": filtrarPor

                    },
                    success: function(response) {
                        $('#spinner').hide()
                        botonBusca.prop('disabled', false);
                        botonBusca.html('<i class="fas fa-check fa-lg fa-fw"></i> Buscar');
                        //                        // Refresh the cart display after a successful Ajax request
                        ////                                    alert(response);  

                        contienes.empty();
                        if ($.fn.dataTable.isDataTable('#myTable')) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            "language": {
                                "thousand": ".",
                                "decimal": ","
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
                        //                         $('#myTable tbody').on("click","td i.tecompro", agregaComp);
                        $('#myTable tbody').on("click", "td i.i-chk-dev", chkFactura);
                        $("#spinner").hide();
                    },
                    error: function(x, e) {
                        $('#spinner').hide()
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
            var chkFactura =
                function() {
                    var claseChk = 'fa fa-check-square fa-2x i-chk-dev',
                        claseCuadro = 'fa fa-square fa-2x i-chk-dev';
                    var codmov = $(this).attr('name');

                    //var inputMonto = $('#mi-dev-'+codmov);


                    //recupero el check

                    var chk = $('#chk-dev-' + codmov);
                    // console.log("chk-fact=>"+chk.prop('checked'));
                    // console.log("Mi limite=>" + inputMonto.prop('max'));
                    if (chk.prop('checked') === true) {
                        chk.prop('checked', false);
                        $(this).removeClass(claseChk).addClass(claseCuadro);
                        //inputMonto.prop( "readonly", false );
                    } else {
                        chk.prop('checked', true);
                        $(this).removeClass(claseCuadro).addClass(claseChk);
                        ///inputMonto.prop( "readonly", true );

                    }
                    //labelSaldo.text((limiteMax-monto).toFixed(2));
                    // agregar que si se borra o deschequea se vuelve a
                    // colocar el valor a imputar sugerido.
                    subTotalFact();
                };

            /*
             * Calculo del Total a Sumarizar de Las Facturas
             * ==============================================
             * @returns {undefined}
             */
            function subTotalFact() {
                var devol = $('input[type="checkbox"]:checked');
                var totalNeto = $('#totalNeto');
                var totalIva = $('#totalIva');
                var totalTotal = $('#totalTotal');
                var arrDev = jQuery.makeArray(devol);
                //        console.log("facturas=>"+arrFact);
                totalNeto.val(0);
                totalIva.val(0);
                totalTotal.val(0);
                $.each(arrDev, function(key, value) {
                    //            console.log( key + ": " + value );
                    //            console.log($(value));
                    //sumarizar 
                    var id = $(value).val();
                    var cadena = $('#mi-dev-' + id).val().split("|");

                    var neto = parseFloat(cadena[0]);
                    var iva = parseFloat(cadena[1]);
                    var total = parseFloat(cadena[2]);

                    var montoNeto = parseFloat(totalNeto.val());
                    var montoIva = parseFloat(totalIva.val());
                    var montoTotalT = parseFloat(totalTotal.val());


                    totalNeto.val(montoNeto + neto);
                    totalIva.val(montoIva + iva);
                    totalTotal.val(montoTotalT + total);
                });
            }
            /* 
             * boton aceptar 
             * ==========================================================================
             * */
            $('#botAceptar').click(function() {
                var contienes = $('#myTable');
                var queHay = $('input[type="checkbox"]:checked').serializeArray();
                console.log(queHay);
                if (queHay.length > 0) {
                    $.ajax({
                        type: 'POST',
                        url: 'relay-devoluciones.php',
                        data: {
                            "ajax": "true",
                            "queAccion": "procesar",
                            "arrDev": queHay
                        },
                        success: function(response) {
                            //                        // Refresh the cart display after a successful Ajax request
                            ////                                    alert(response);  
                            console.log(response);
                            contienes.empty();
                            $('#basic-modal-content').html('');
                            $('#basic-modal-content').html(response);
                            $('#basic-modal-content').modal({
                                //minHeight : 200,
                                maxWidth: 320,
                                minHeight: 200
                            });
                            $('#myTable tbody').on("click", "td i.i-chk-dev", chkFactura);
                            $("#spinner").hide();
                        },
                        error: function(x, e) {
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
                } else {
                    $('#basic-modal-content').html('');
                    $('#basic-modal-content').html('<div id="alertas-formulario" class="alerta-error"> No hay devoluciones Seleccionadas</div>');
                    $('#basic-modal-content').modal({
                        //minHeight : 200,
                        maxWidth: 300,
                        minHeight: 100
                    });
                }


            });
            //esta es la forma de hacer que ande sin tanto complicaion    
            $('#verFiltros').on("click", function() {
                //$(this).find('i').toggleClass('fa-angle-up fa-angle-down'); // Alternar entre los íconos de flecha arriba y abajo
                if ($(this).hasClass('fa-angle-down') === true) {
                    // console.log('tenglo clase angle down----');
                    $(this).removeClass('fa-angle-down').addClass('fa-angle-up');

                } else {
                    // console.log('tenglo clase angle UP----');
                    $(this).removeClass('fa-angle-up').addClass('fa-angle-down');

                }
                $('#formBusca').toggle(); // Mostrar u ocultar el formulario con el id 'formBusca'
            });
            $('#formBusca').show();
        });
        // $('#parametrosInformes').on('click', function() {
        //                 // console.log('hago click en la busqueda avanzada---------');
        //                 var divAvanzado = $(".panelesBloqueInforme");
        //                 if ($(this).hasClass('fa-angle-down') === true) {
        //                     // console.log('tenglo clase angle down----');
        //                     $(this).removeClass('fa-angle-down').addClass('fa-angle-up');
        //                     divAvanzado.show();
        //                 } else {
        //                     // console.log('tenglo clase angle UP----');
        //                     $(this).removeClass('fa-angle-up').addClass('fa-angle-down');
        //                     divAvanzado.hide();
        //                 }

        //             });
    </script>
</head>

<body>
    <div id="wrapper">
        <?php
        require_once $barra;
        ?>

        <div class="paneles filtroInformes">




            <h1>Devoluciones <span><i class="fas fa-angle-up fa-lg fa-fw" style="cursor: pointer;" id="verFiltros"></i></span></h1>
            <form id="formBusca" name="formBusca" method="POST" action="">

                <div class='panelesBloqueInforme'>

                    <div class="titulo">
                        Parametros
                    </div>

                    <div class="control">
                        <label class="parametros" for="estadoPedido">Estado:</label>
                        <select name="estadoPedido" id="estadoPedido">
                            <option value="1">-</option>
                            <option value="Pendiente">Pendiente</option>
                            <option value="Computada">Computada</option>
                        </select>
                    </div>

                    <div class="control">
                        <label class="parametros" for="campoBusca">Buscar por:</label>
                        <select name="campoBusca" id="campoBusca">
                            <option value="1">-</option>
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

                    <div id="buscaNumero" class="control" style="display:none">
                        <label class="parametros" for="numeroComp">Nº Comprob:</label>
                        <input type="number" name="numeroComp" id="numeroComp">
                    </div>
                </div>

                <div class='panelesBloqueInforme'>

                    <div class="titulo">
                        Filtros
                    </div>

					<div class="panel-filtro-compuesto">
						<div class="control">
							<label class="parametros" for="filtrarPor">Tipo:</label>
							<select name="filtrarPor" id="filtrarPor">
								<option value="1"> - seleccionar -</option>
								<option value="cliente">Cliente</option>
								<!--<option value="tipocliente">Tipo Cliente</option>-->
								<option value="vendedor">Vendedor</option>
								<option value="articulo">Articulo</option>
								<option value="proveedor">Proveedor</option>
								<!--<option value="zona">Zona</option>-->
								<option value="rubro">Rubro</option>
								<option value="subrubro">Sub Rubro</option>
							</select>
						</div>

						<div class="control-con-boton">
							<!--<label class="parametros" for="seleccionFiltro">Valor: </label>-->
							<input id="seleccionFiltro" alt="" type="search" placeholder="Seleccione un valor...">
							<button name="addFiltro" id="addFiltro" class="botonNuevo chico azul" type="button"><i class="fa fa-check"></i> </button>
						</div>
					</div>

					<div class="subtitulo">Filtros aplicados:</div>

                    <div class="panelesBloqueInforme-interno contiene-lista-filtros en-bloque">
                        <!--<label class="parametros" for="listaFiltro">A filtrar:</label>-->
                        <ul name="listaFiltro" id="listaFiltro" class="listaSeleccionado"></ul>
                        <input type="hidden" name="filtroSelec" id="filtroSelec" value="" required="required">
                    </div>

                </div>

                <div class="panelesBloqueInformeAccion">
                    <span class="centro w100p">

                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo">
                            <i class="fas fa-search fa-lg fa-fw"></i> buscar
                        </button>
                        <!--                        <input type="button" name="botonBuscar" class="buttons" id="botonBuscar" value="Buscar">-->
                    </span>
                </div>

            </form>


        </div>


        <div class="paneles" >
            <div class="panelesBloqueInforme">
            

                <div class="titulo">Liquidacón de Devoluciones computadas</div>
                <div class="control">
                    <label class="parametros" for="totalNeto">Total Neto ($): 
                        <input  name="totalNeto" id="totalNeto" class="cajaTexto" readonly="readonly" >
                    </label>
                </div>
                
                <div class="control">
                    <label class="parametros" for="totalIva">Total Iva ($): 
                        <input type="number" class="cajaTexto" name="totalIva" id="totalIva" readonly="readonly">
                    </label>
                </div>
                
                <div class="control">
                <label class="parametros" for="totalTotal">Total Imputado ($):
                    <input type="number" name="totalTotal" id="totalTotal" class="cajaTexto" readonly="readonly">
                </label>
                </div>
            </div>    
        </div>

        <div class="paneles" id="contiene-tabla">

            <table class=" display" cellspacing="1" id="myTable"></table>

        </div>









            <div class="paneles panelesBloqueInformeAccion">
                <span class="centro w100p">
                    <button title="Aceptar" alt="Aceptar" type="button" id="botAceptar" name="botAceptar" class="botonNuevo grande azul">
                        <i class="fas fa-check fa-lg fa-fw"></i> Generar
                    </button>
                </span>
            </div>


            <?php require_once 'footer.php'; ?>

        </div>
        <div id="basic-modal-content"> </div>
        <!--spinner admNET-->
        <div id="spinner" class="spinnerAdm" style="display:none;">
            <div class="centro">
                <img src="_img/logo-administranet-ecommerce.png">
                <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
            </div>
        </div>
        <!--fin spinner-->
</body>

</html>