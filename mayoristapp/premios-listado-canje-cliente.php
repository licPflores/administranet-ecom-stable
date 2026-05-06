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
    <title>Premios Gestión de canjes | administraNET e-com </title>
    <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <?php require_once 'cabecera.php'; ?>


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
            $('#campoBusca').change(function() {
                var valor = $(this).val();
                // voy a corroborar que div mostrar
                //         $('#fechaDesde').val('dd/mm/aaaa'),
                //         $('#fechaHasta').val('dd/mm/aaaa'),
                $('#numeroComp').val('');
                switch (valor) {
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
            $('#botonBuscar').click(function() {
                var contienes = $('#myTable'),
                    campoBusca = $('#campoBusca').val(),
                    fechaDesde = $('#fechaDesde').val(),
                    fechaHasta = $('#fechaHasta').val(),
                    filtraCliente = $('#filtraCliente').val(),
                    queInforme = $('#tipoInforme').val();
                var estadoCanje = $('#estadoCanje').val();

                //            numeroComp = $('#numeroComp').val(),
                // tipoRemito = $('#tipoRemito').val(),
                //estadoPedido =  $('#estadoRecibo').val();
                //$('formBusca').submit();
                if (campoBusca === "Fecha" && (fechaDesde === '' || fechaHasta === '')) {
                    console.log("fechaDesde::: " + fechaDesde);
                    console.log("fechaDesde::: " + fechaHasta);
                    alert("Debe completar la fecha");
                    $('#fechaDesde').focus();
                    return false;
                }
                $.ajax({
                    type: 'GET',
                    url: 'json/json-premios-informes.php',
                    data: {
                        "consultaCanjes": 1,
                        "desde": fechaDesde,
                        "hasta": fechaHasta,
                        "cliente": filtraCliente,
                        "estadoCanje": estadoCanje,
                        "tipo": queInforme,


                    },
                    success: function(response) {
                        //                        // Refresh the cart display after a successful Ajax request
                        //                    console.log(response);
                        contienes.empty();
                        if ($.fn.dataTable.isDataTable('#myTable')) {
                            contienes.DataTable().destroy();
                        }
                        contienes.html(response);
                        contienes.DataTable({
                            searching: false,
                            responsive: false,
                            //                            ordering:false,
                            "language": {


                                "emptyTable": "No se encontraron resultados",
                                "info": "Viendo _START_ de _END_ de _TOTAL_ resultados",
                                "infoEmpty": "Viendo 0 de 0 de 0 resultados",
                                "infoFiltered": "(filtrado de _MAX_ resultados)",
                                "infoPostFix": "",
                                "thousands": "",
                                "lengthMenu": "Ver _MENU_ entradas",
                                "loadingRecords": "Loading...",
                                "processing": "Processing...",
                                "search": "Buscar:",
                                "zeroRecords": "No se encontraron coincidencias",
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

            //ventana modal para mostrar el contenido sin tener que usar una ventana abierta y se vea el codigo

            $('#verFiltros').on("click", function() {
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
                        <label for="filtraCliente">Cliente:
                            <select name="filtraCliente" id="filtraCliente">
                                <option value="todos">- Todos -</option>
                                <?php if (isset($clienteObj)) : ?>
                                    <?php if ($_SESSION["usa_id_manual"] == "Si") : ?>
                                        <option selected="selected" value="<?php echo $clienteObj->Codigo; ?>"><?php echo $clienteObj->cliente . '(Cod:' . $clienteObj->id_manual_cli . ')'; ?></option>
                                    <?php endif; ?>
                                    <?php if ($_SESSION["usa_id_manual"] != "Si") : ?>
                                        <option selected="selected" value="<?php echo $clienteObj->Codigo; ?>"><?php echo $clienteObj->cliente . '(Cod:' . $clienteObj->Codigo . ')'; ?></option>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </select>
                        </label>
                    </div>

                    <div class="control">
                        <label for="estadoCanje">Estado:
                            <select name="estadoCanje" id="estadoCanje">
                                <option value="todos"> -Todos- </option>
                                <option value="Solicitado" selected="selected">Solicitado</option>
                                <option value="Entregado">Entregado</option>
                                <option value="No Engregado">No Engregado</option>

                            </select>
                        </label>
                    </div>
                    <div class="control">
                        <label for="tipoInforme">Tipo:
                            <select name="tipoInforme" id="tipoInforme">
                                <option value="simple" selected>Simple-</option>
                                <option value="detallado">Detallado</option>

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
                            <input type="date" name="fechaDesde" id="fechaDesde" value="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . ' - 7 days')); ?>">
                        </label>
                        <?php if ($caminoDispo != "") : ?>
                            <br>
                        <?php endif; ?>
                        <label for="fechaHasta">Hasta:
                            <input type="date" name="fechaHasta" id="fechaHasta" value="<?php echo date('Y-m-d'); ?>">
                        </label>
                    </div>




                    <div class="control">
                        <button title="Buscar" alt="Buscar" type="button" id="botonBuscar" name="botonBuscar" class="botonNuevo grande azul">
                            <i class="fas fa-search fa-lg"></i> buscar
                        </button>
                        <!--                        <input type="button" name="botonBuscar" class="buttons" id="botonBuscar" value="Buscar">-->
                    </div>

                </form>
            </div>

            <div id="spinner" class="spinner" style="display:none;">
                <img src="_img/logo-administranet-ecommerce.png">
                <div class="texto">Procesando...</div>
            </div>
            <div id="contiene-tabla" style="float:left;">

                <h1>Listado de canjes
                    <!--                    <a href="listado-clientes.php?frm=1" style="float:right;margin:3px">
                    <button class="botonNuevo grande azul">
                        <i class="fa fa-plus fa-lg"></i> 
                    </button>
                </a>-->
                </h1>

                <table class="display" cellspacing="1" id="myTable"></table>



            </div>

        </div>

        <?php require_once 'footer.php'; ?>

    </div>
    <div id="modal-detalle-canje" style="display:none;">
        <div class="tituloVentana">Comprobantes no cancelados</div>
        <table id="tablaDetalleCanje" name="tablaDetalleCanje" cellspacing="1" style="width: 90%">
        </table>
        <div style="margin-left: 35%;margin-top: 30%;">
            <button id="cierroCanje" class="botonNuevo black grande"><i class="fas fa-times fa-lg fa-fw"></i> Cerrar</button>
        </div>
    </div>


    <script>
        // codigo para ver el los detalles.

        function ver_premios(idCanje) {
            // voy a buscar lo que hay en detale?
            $.ajax({
                type: 'GET',
                url: 'json/json-premios-informes.php',
                data: {
                    detalleCanje: 1,
                    idCanje: idCanje
                },
                dataType: 'json',
                success: function(data) {

                    //console.log(data);

                    if (data.msg === "ok") {
                        console.log({
                            data
                        });
                        // lleno el footer 
                        var texto = '';
                        var textoTabla = '<thead><tr>';
                        textoTabla += '<th>Premio</th>';
                        textoTabla += '<th>Canje</th>';
                        textoTabla += '<th>Puntos</th>';
                        textoTabla += '</tr></thead>';
                        textoTabla += '<tbody>';
                        var titulo = 'CANJE: #' + data.comprobante;

                        $(jQuery.parseJSON(JSON.stringify(data.premios))).each(function() {
                            texto += this.nombre + ' ' + this.cantidad + ' x ' + this.puntos + ' pts =' + this.total + ' pts\r\n';
                            textoTabla += '<tr>';
                            textoTabla += '<td class="dt-left">' + this.nombre + '</td>';
                            textoTabla += '<td class="dt-center"><strong>' + this.cantidad + '</strong> x ' + this.puntos + ' pts </td>';
                            textoTabla += '<td class="dt-center"><strong>' + this.total + '</strong></td>';
                            textoTabla += '</tr>';
                        });
                        textoTabla += '</tbody>';
                        //swal(titulo,texto);
                        var ventana = $('#modal-detalle-canje');
                        var tablita = $('#tablaDetalleCanje');
                        var tituloT = $('#modal-detalle-canje > .tituloVentana');
                        tituloT.text(titulo);
                        tablita.empty();
                        if ($.fn.dataTable.isDataTable('#tablaDetalleCanje')) {
                            tablita.DataTable().destroy();
                        }
                        tablita.html(textoTabla);
                        tablita.DataTable({
                            searching: false,
                            responsive: true,
                            paging: false,
                            info: false,
                            ordering: false
                        });
                        // modalita
                        wVentana = 290;
                        hVentana = 320;
                        ventana.modal({

                            minWidth: wVentana,

                            minHeight: hVentana,
                            maxHeight: hVentana,
                            close: false,
                            onShow: function() {
                                $('#cierroCanje').on("click", function(e) {
                                    e.preventDefault();
                                    //                                                var contienes2 = $('#tablaConsumos');

                                    $.modal.close();
                                    //                                                contienes2.DataTable().destroy();
                                });
                            }



                        });

                    }
                    if (data.msg === "error") {
                        console.log({
                            data
                        });
                    }

                }
            });



        }

        // function cambiar el estado. a un canje.

        function cambiar_estado(idCanje, estado, numero) {
            var estadoT, accion;
            if (estado == 0) {
                estadoT = 'Entregado';
                accion = 'Entregar';
            }
            if (estado == 1) {
                estadoT = 'No Entregado';
                accion = 'No Entregar';
            }
            swal({
                    title: "Está seguro?",
                    text: "El comprobante #" + numero + " cambiará a " + estadoT + " con todos sus premios!",
                    icon: "warning",
                    buttons: ["No", accion],
                    dangerMode: true,
                })
                .then((willDelete) => {


                    if (willDelete) {
                        console.log("aca iria el ajas?");
                        $.ajax({
                            type: 'GET',
                            url: 'json/json-premios-informes.php',
                            dataType: 'json',
                            data: {
                                "cambiaEstado": 1,
                                "idCanje": idCanje,
                                "estadoCanje": estadoT



                            },
                            success: function(data) {
                                console.log('que paso??');
                                console.log({
                                    data
                                });
                                if (data.msg === 'ok') {
                                    swal("Canje " + estadoT + "!", {
                                        icon: "success",
                                    });
                                }
                                if (data.msg === 'error') {
                                    console.log('error ');
                                    console.log({
                                        data
                                    });
                                    swal("Oops!", "ocurrio un inconveniente!" + data.desc, "error");
                                }
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
                                swal("Oop!", "ocurrio un error, intentalo nuevamente." + m, {
                                    icon: "error",
                                });
                            }
                        });

                    }
                });
            //        

        }
        // reenviar un email

        function mandar_email_canje(idCanje) {
            console.log("enviando el correo de un caje ---------");
            $.ajax({
                type: 'GET',
                url: 'p/json/enviar_mail_canje.php',
                dataType: 'json',
                data: {
                    "enviarMailCanje": 1,
                    "idCanje": idCanje
                },
                beforeSend: function() {
                    $('#spinner').show('fast');
                },
                success: function(data) {
                    console.log('estado del email------>');
                    console.log({
                        data
                    });
                    if (data.msg === 'ok') {
                        Swal.fire({
                            title: "Bien!",
                            text: "Email enviado con exito",
                            icon: "success"
                        });
                    }
                    if (data.msg === 'error') {
                        console.log('error ');
                        console.log({
                            data
                        });
                        Swal.fire({
                            title: "Oops!",
                            html: "ocurrio un inconveniente!<br>" + data.desc,
                            icon: "error"
                        });
                    }
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
                    Swal.fire({
                        title: "Oops!",
                        html: "ocurrio un inconveniente!<br>" + m,
                        icon: "error"
                    });
                },
                complete: function() {
                    $('#spinner').hide('fast');
                }
            });
        }

        function pad(num) {
            var s = num + "";
            while (s.length < 8) s = "0" + s;
            return s;
        }

        function anular(id,queCliente) {
            console.log('ANIULANDO::', id);
            var estadoT, accion;
            var comprobante;
            comprobante = pad(id);
            Swal.fire({
                    title: "Está seguro de anular este canje?",
                    text: "Se anulara el canje " + comprobante,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: 'Anular <i class="fas fa-check fa-lg"></i>',
                    cancelButtonText: 'Cancelar <i class="fas fa-times fa-lg"></i>'                        
                })
                .then((accion) => {


                    if (accion.isConfirmed) {
                        $.ajax({
                            type: 'GET',
                            url: 'p/json/anular_canje.php',
                            dataType: 'json',
                            data: {
                                'ide': id,
                                'queCliente': queCliente
                            },
                            success: function(data) {

                                if (data.msg) {
                                    swal("Resultado:\n" + data.msg, {
                                        icon: "success",
                                        buttons: false,
                                        timer: 13000
                                    });
                                }

                                location.reload(false);

                            },
                            error: function(x, e) {
                                Swal.fire("Oops!", "No se pudo Anular el premio", "error");

                            }





                        });

                    }
                });



        }
    </script>

</body>

</html>
<?php
