<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once '../sesion.inc.php';
//echo "<pre>";
//print_r($_SESSION);
if (is_object($_SESSION['cliente'])) {
    $clienteObj = $_SESSION['cliente'];
} else {
    $clienteObj = $_SESSION['cliente'][0];
}
$rec = $_SESSION["recibo"];
?>
<!doctype html>
<html>

<head>
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#395aa2">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Descuentos y retenciones recibo.</title>
    <?php include_once 'inc-header-recibo.php'; ?>
</head>

<body>
    <div class="easyui-navpanel titulo-recibo-gradiante" id="P1">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <span class="m-title">Descuentos y retenciones</span>

                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true" onclick="salida()"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>

                </div>


            </div>

        </header>
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado"> <i class="titulo-recibo-alt">$</i><span id="totalImputadoDesc" class="titulo-recibo-alt">0.00</span>
                        <br>
                        Imputado
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado"><i class="positivo-alt">$</i><span id="totalDescuentoDesc" class="positivo-alt">0.00</span>
                        <br>
                        Descuento
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado"> <i class="tarjeta-alt">$</i><span id="totalRetencionesDesc" class="tarjeta-alt">0.00</span>
                        <br>
                        Retenciones
                    </p>
                </div>

                <div class="hijo-estado">
                    <p class="texto-estado"><i class="a-cubrir-alt">$</i><span id="totalAcubrirDesc" class="a-cubrir-alt">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>
            </div>
            <!-- <div style="text-align: center;font-size:x-small"><?php echo $clienteObj->cliente; ?></div> -->

            <div>

                <h3 class="titulo-medios-cobro">Descuentos</h3>

                <div>
                    <select class="easyui-combobox" name="listaDescuento" id="listaDescuento" data-options=" valueField: 'id',
                        textField: 'text',prompt:'0.00',panelMaxHeight:'50px',label:'Tipo:',labelWidth:'60px;'" style="width:68%;">

                    </select>
                    <input class="easyui-numberbox" id="montoDescuento" min="0" data-options="label:'$:',labelWidth:'15px;'" groupSeparator='.' decimalSeparator="," precision="2" prefix="$" prompt="0.00" style="width:30%">
                </div>
                <div style="text-align: left;margin-top:5px">
                    <a href="javascript:void(0)" class="easyui-linkbutton primario" id="descuento-ok" onclick="acepta_descuento_autom($(this))"><i class="fas fa-check fa-fw fa-lg"></i> Aplicar</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton secundario" id="descuento-cancel" disabled="true" onclick="borrar_descuento_autom($(this))"><i class="fas fa-times fa-fw fa-lg"></i> Borrar</a>
                </div>

            </div>

            <div style="height:30%;">
                <h3 class="titulo-medios-cobro">Retenciones</h3>
                <div style="margin-bottom:5px">
                    <a href="javascript:void(0)" class="easyui-linkbutton primario" onclick="agregar_retencion()"><i class="fas fa-plus fa-lg fa-fw"></i> Nueva</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton secundario" onclick="borrar_retencion()"><i class="fas fa-trash fa-lg fa-fw"></i> Borrar</a>
                    <a href="javascript:void(0)" class="easyui-linkbutton danger" onclick="vaciar_retenciones()"><i class="fas fa-trash-alt fa-lg fa-fw"></i> Vaciar</a>


                </div>

                <table id="tblRetenciones"></table>



            </div>


        </div>
        <footer>


            <div style="padding:3px;">
                <a href="javascript:void(0)" class="easyui-linkbutton primario" onclick="siguiente($(this));" style="width:100%">Siguiente <i class="fas fa-angle-right fa-fw fa-lg"></i></a>
            </div>

        </footer>
    </div>

    <!-- ALTA RETENCION -->
    <div id="dialogRetencion" class="easyui-dialog" style="padding:20px 6px;width:80%;top:0px" data-options="inline:true,modal:true,closed:true,title:'Alta de retención'">

        <div style="margin-bottom:5px">
            <select class="easyui-combobox" name="listaRetencion" id="listaRetencion" data-options=" valueField: 'id',
                    textField: 'text',prompt:'tipo de retencion...',label:'Tipo:',panelMaxHeight:'70px'" style="width:100%;"></select>


        </div>
        <div style="margin-bottom:5px">
            <input class="easyui-textbox" inputmode="numeric" type="number" pattern="[0-9]*" label="Certificado:" id="dlCertificado" prompt="000" style="width:100%">
        </div>


        <div style="margin-bottom:5px">
            <input class="easyui-textbox" inputmode="decimal" type="number" pattern="\d+(,\d{2})?" id="dlImporte" min="0" groupSeparator='.' decimalSeparator="," precision="2" required="true" missingMessage="Debe completar el monto" prefix='$' prompt="0.00" style="width:70%" label="Importe:">
        </div>
        <!--</div><input class="easyui-textbox" inputmode="numeric" type="text" pattern="\d+(,\d{2})?"  id="dlImporte" min="0" decimalSeparator="," precision="2" required="true" missingMessage="Debe completar el monto" prefix='$'   prompt="$ a retener" style="width:70%" label="Importe:">-->
        <div style="margin-bottom:5px">
            <input class="easyui-datebox" id="dlFechafCertificado" prompt="" data-options="editable:false,panelWidth:220,panelHeight:240,iconWidth:30,label:'Fecha:'" style="width:80%">
        </div>
        <div style="margin-bottom:5px">
            <input class="easyui-numberbox" id="dlPorcentaje" min="0" max="100" decimalSeparator="," precision="1" required="true" missingMessage="Debe completar %" suffix='%' prompt="0.00" style="width:70%" label="Porcentaje:" value="1">
        </div>


    </div>
    <!--FIN ALTA RETENCION -->


    <!--DIALGO DE MENSAJES-->
    <div id="dlgMensaje" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Information'">
        <p id="mensajeDialog">This is a message dialog.</p>
        <div class="dialog-button">
            <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensaje').dialog('close');">OK</a>
        </div>
    </div>
    <!--FIN DIALOGO MENSAJES-->

    <div id="spinner" class="spinner" style="display:none;">
        <div class="centro">
            <img src="../_img/logo-administranet-ecommerce.png">
            <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
        </div>
    </div>
</body>
<script>
    const numerito = new Intl.NumberFormat('es-AR', {
        style: 'decimal',
        minimumFractionDigits: 2
    });

    const dinero = new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
        minimumFractionDigits: 2

    });
    $.fn.datebox.defaults.formatter = function(date) {
        var y = date.getFullYear();
        var m = date.getMonth() + 1;
        var d = date.getDate();
        return (d < 10 ? '0' + d : d) + '/' + (m < 10 ? '0' + m : m) + '/' + y;
    };

    $.fn.datebox.defaults.parser = function(s) {
        if (s) {
            var a = s.split('/');
            var d = new Number(a[0]);
            var m = new Number(a[1]);
            var y = new Number(a[2]);
            var dd = new Date(y, m - 1, d);
            return dd;
        } else {
            return new Date();
        }
    };
    //* variables totales globales
    var varTotalImputado = 0.00;
    var varTotalDescuento = 0.00;
    var varTotalRetenciones = 0.00;
    var varTotalAcubrir = 0.00;

    // objetos con subTotales
    var objTotalImputado = $('#totalImputadoDesc'),
        objTotaldescuento = $('#totalDescuentoDesc'),
        objTotalRetencion = $('#totalRetencionesDesc'),
        objTotalAcubrir = $('#totalAcubrirDesc');

    // salida de los descuentos

    function siguiente(este) {
        este.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> Espere..'
        });
        location.href = "alta_recibo_medios_cobro.php";
    }

    // al cambiar el porcentaje
    $('#listaDescuento').combobox({
        onSelect: function(row) {
            var porciento = row.id;
            var objSaldo, vSaldo, descuento;

            objSaldo = $('#totalSaldo');

            // vSaldo = objSaldo.numberbox('getValue');
            vSaldo = varTotalAcubrir;
            descuento = vSaldo * porciento / 100;
            $('#montoDescuento').numberbox('setValue', descuento);
            console.log("vSaldo::" + vSaldo);
            console.log("descuento::" + descuento);
            // obtener el campo del monto y total del recibo.
        }
    });

    function iniciar() {

        traer_descuentos();
        trae_tipo_retencion_cli();
        trae_retenciones();
        trae_totales();

    }

    function trae_totales() {
        let botonAltaDescuento,
            inputImporteDescuento,
            botonBorraDescuento;
        botonAltaDescuento = $('#descuento-ok');
        botonBorraDescuento = $('#descuento-cancel');
        inputImporteDescuento = $('#montoDescuento');

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                totalRecibo: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log('trae totales de descuento.', data);


                if (data.msg === "ok") {
                    objTotalImputado.text(numerito.format(data.total));
                    objTotaldescuento.text(numerito.format(data.descuento));
                    objTotalRetencion.text(numerito.format(data.retencion));
                    objTotalAcubrir.text(numerito.format(data.saldo));
                    varTotalImputado = data.total;
                    varTotalDescuento = data.descuento;
                    varTotalRetenciones = data.retencion;
                    varTotalAcubrir = data.saldo;
                    if (data.descuento !== 0) {
                        // hay descuento 
                        botonAltaDescuento.linkbutton('disable');
                        botonBorraDescuento.linkbutton('enable');
                        inputImporteDescuento.numberbox('setValue', data.descuento);

                    }


                }

            }
        });
    }

    function acepta_descuento_autom(quien) {
        quien.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> Espere..'
        });
        quien.linkbutton('disable');
        var lPorcentaje = $('#listaDescuento').combobox('getValue');
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                altaDescuento: 1,
                porcentaje: lPorcentaje
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);

                if (data.msg === "ok") {
                    // recalcular 
                    quien.linkbutton('disable');
                    quien.linkbutton({
                        'text': '<i class="fas fa-check fa-fw fa-lg"></i> Aplicar'
                    });
                    // bloqueo el porcentaje y dejo el importe en el campo 
                    //$('#listaDescuento').combobox('disabled');
                    $('#descuento-cancel').linkbutton('enable');
                    $('#listaDescuento').prop("disabled", true);
                    trae_totales();
                    Swal.fire({
                        icon: 'success',
                        text: 'Muy bien, se generó el descuento!',
                        align: 'top',
                        confirmButtonColor: '#395aa2'
                    });
                } else {
                    // error 
                    //$('#mensajeDialog').text("hubo un inconveniente con el descuento, vuelva a intentar.");
                    //$('#dlgMensaje').dialog('open').dialog('center');
                    $('#listaDescuento').combobox('textbox').focus();
                    Swal.fire({
                        icon: 'warning',
                        text: 'hubo un inconveniente con el descuento, vuelva a intentar.',
                        confirmButtonColor: '#395aa2'
                    });
                }

            },
            complete: function() {
                $('#spinner').hide();
            }
        });
    }

    function borrar_descuento_autom(quien) {
        // $('#spinner').show();
        quien.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> "Espere..'
        });

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                bajaDescuento: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);

                if (data.msg === "ok") {
                    // recalcular 
                    quien.linkbutton('disable');
                    quien.linkbutton({
                        'text': '<i class="fas fa-times fa-fw fa-lg"></i> Borrar'
                    });

                    $('#descuento-ok').linkbutton('enable');
                    $('#montoDescuento').numberbox('setValue', 0);
                    $('#listaDescuento').combobox('unselect');
                    $('#listaDescuento').combobox('clear');
                    $('#listaDescuento').prop("disabled", true);
                    trae_totales();
                } else {
                    // error 
                    // $('#mensajeDialog').text("hubo un inconveniente eliminacion de descuento, vuelva a intentar.");
                    // $('#dlgMensaje').dialog('open').dialog('center');

                    $('#listaDescuento').combobox('textbox').focus();
                    Swal.fire({
                        icon: 'error',
                        text: 'Hubo un inconveniente al borrar el descuento,vuelva a intentarlo',
                        confirmButtonColor: '#395aa2'
                    });
                }

            }
        });

    }

    function show1(id) {
        //$('div.m-item').hide();
        $(id).show();
    }

    function hide1(id) {
        $(id).hide();
    }




    function fechaReves(s) {
        console.log("fechaVacio");
        console.log(s);
        var ss = s.split('/');
        var y = parseInt(ss[2], 10);
        var m = parseInt(ss[1], 10);
        var d = parseInt(ss[0], 10);
        return y + '-' + m + '-' + d;
    }

    function traer_descuentos() {
        // solo los presento y si hay movimientos 

        var selDescuento = $('#listaDescuento');
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeDescuentos: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);

                if (data.msg === "ok") {
                    var opt = data.descuentos;
                    selDescuento.combobox({
                        data: opt
                    });
                    selDescuento.combobox('textbox').focus();

                } else {
                    // error 
                }

            }
        });
    }

    function trae_tipo_retencion_cli() {
        var selRet = $('#listaRetencion');
        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                traeRetencionCli: 1
            },
            dataType: 'json',
            success: function(data) {
                console.log(data);

                if (data.msg === "ok") {
                    var opt = data.tipoRetencion;
                    selRet.combobox({
                        data: opt
                    });

                } else {
                    // error 
                }

            }
        });

    }
    var indiceTabla = undefined;

    // agrego una retencion 

    function agregar_retencion() {

        var ventana = $('#dialogRetencion');
        console.log('agregar_retencion');
        ventana.dialog({
            'title': 'Alta retención',
            'buttons': [{
                    id: 'botonAceptarDialog',
                    text: '<i class="fas fa-check fa-lg fa-fw"></i> Aceptar',

                    handler: function() {
                        //$('#spinner').show();
                        console.log("click en aceptar");
                        $('#botonAceptarDialog').linkbutton({
                            'text': '<i class="fas fa-circle-notch fa-spin"></i> Espere..'
                        });
                        var codRetencion = $('#listaRetencion').combobox('getValue'),
                            tipoRetencion = $('#listaRetencion').combobox('getText'),
                            fecha = fechaReves($('#dlFechafCertificado').datebox('getValue')),
                            certificado = $('#dlCertificado').textbox('getValue'),
                            porcentaje = $('#dlPorcentaje').numberbox('getValue'),
                            monto = $('#dlImporte').numberbox('getValue');
                        //                                    console.log("codRetencion::"+codRetencion);
                        //                                    console.log("retencion::"+tipoRetencion);
                        console.log("fecha::" + fecha);
                        //                                    console.log("certificado::"+certificado);
                        //                                    console.log("porcentaje::"+porcentaje);
                        //                                    console.log("monto::"+monto);
                        // guardar el importe en el json.

                        if (monto <= 0) {
                            // $('#spinner').hide();
                            $('#botonAceptarDialog').linkbutton({
                                'text': '<i class="fas fa-check fa-lg fa-fw"></i> Aceptar'
                            });
                            //$('#mensajeDialog').text("El monto debe ser mayor a cero y positivo ");
                            //$('#dlgMensaje').dialog('open').dialog('center');
                            return false;
                            Swal.fire({
                                icon: 'warning',
                                text: 'El monto debe ser mayor a cero y positivo',
                                confirmButtonColor: '#395aa2'
                            });
                        }

                        // ajax de la imputacion 
                        $.ajax({
                            type: 'GET',
                            url: 'ajax/json_recibo.php',
                            data: {
                                altaRetencion: 1,
                                cod: codRetencion,
                                tipo: tipoRetencion,
                                fecha: fecha,
                                certificado: certificado,
                                porcentaje: porcentaje,
                                monto: monto
                            },
                            dataType: 'json',
                            success: function(data) {
                                console.log(data);
                                if (data.msg === "ok") {
                                    // hacer algo con el row.

                                    // llamar arecalculo del recibo 
                                    // y marcar la factura de alguna forma como lista.
                                    // aviso que todo bien
                                    //$('#mensajeDialog').text("Se imputo $"+monto+ " a factura: "+row.item+"" );
                                    //$('#dlgMensaje').dialog('open').dialog('center');
                                    //vacio todo al guardar
                                    $('#listaRetencion').combobox('setText', '');

                                    $('#dlFechafCertificado').datebox('clear');
                                    $('#dlCertificado').textbox('clear');
                                    //$('#dlPorcentaje').textbox('clear');
                                    $('#dlImporte').textbox('clear');
                                    $('#dialogRetencion').dialog('close');
                                    trae_retenciones();
                                    trae_totales();
                                    Swal.fire({
                                        icon: 'success',
                                        text: 'Se generó la retención!',
                                        confirmButtonColor: '#395aa2'
                                    });
                                }
                            },
                            complete: function() {
                                $('#spinner').hide();
                            }
                        });




                    }
                },
                {
                    text: '<i class="fas fa-times fa-lg fa-fw"></i> Cancelar',
                    id: 'botonCancelarDialog',
                    handler: function() {

                        $('#dialogRetencion').dialog('close');
                    }

                }
            ]

        });
        ventana.dialog('open');
    }

    function borrar_retencion() {
        //        onsole.log("borrando un dato.");    
        //            $('#tblRetenciones').datagrid('cancelEdit', indiceTabla)
        // obtener el renglon
        var grid = $('#tblRetenciones');
        if (grid.hasClass('easyui-grid') == true) {
            console.log('soy grid:', grid.hasClass('easyui-grid'));


            var row = $('#tblRetenciones').datagrid('getRowIndex');
            var select = $('#tblRetenciones').datagrid('getSelected');
            console.log("que row:" + JSON.stringify(row));
            console.log("que selected :" + JSON.stringify(select));
            //               alert("no puedo borrar");
            console.log(select.key);
            var key = select.key;


            Swal.fire({
                    title: "¿Está seguro borrar?",
                    text: "Si acepta, se eliminará la retención <strong>" + select.certificado + "</strong> de $" + select.monto,
                    icon: "warning",
                    //buttons: ["Cancelar", "Borrar"],
                    confirmButtonText: 'Borrar!',
                    confirmButtonColor: '#395aa2',
                    cancelButtonText: 'Cancelar',
                    showCancelButton: true,

                })
                .then((willDelete) => {
                    if (willDelete.isConfirmed) {
                        console.log('dentro del willdelete');
                        $.ajax({
                            type: 'GET',
                            url: 'ajax/json_recibo.php',
                            data: {
                                bajaRetencion: 1,
                                key: key
                            },
                            dataType: 'json',

                            success: function(data) {
                                console.log({
                                    data
                                });
                                trae_retenciones();
                                trae_totales();
                            }
                        });
                    }
                });
        }

        if (grid.hasClass('easyui-grid') == false) {
            Swal.fire({
                icon: 'error',
                text: 'No ha seleccionado una retención',
                confirmButtonColor: '#395aa2'
            });
        }

    }

    function vaciar_retenciones() {
        let grid = $('#tblRetenciones');
        if (grid.hasClass('easyui-grid') == true) {

            Swal.fire({
                    title: "¿Está seguro de vaciar todo?",
                    text: "Si acepta, se eliminarán todas las retenciones!",
                    icon: "warning",
                    //buttons: ["Cancelar", "Vaciar"],
                    //dangerMode: true,
                    confirmButtonText: 'Vaciar!',
                    confirmButtonColor: '#395aa2',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                })
                .then((willDelete) => {
                    if (willDelete.isConfirmed) {
                        console.log('dentro del willdelete');
                        $.ajax({
                            type: 'GET',
                            url: 'json_recibo.php',
                            data: {
                                vaciarRetencion: 1
                            },
                            dataType: 'json',

                            success: function(data) {
                                trae_retenciones();
                                trae_totales();
                            }
                        });
                    }
                });
        }
        if (grid.hasClass('easyui-grid') == false) {
            Swal.fire({
                icon: 'error',
                text: 'No existen retenciones!',
                confirmButtonColor: '#395aa2'
            });
        }

    }

    // buscar las retenciones     
    function trae_retenciones() {

        //$('#spinner').show();
        var tabla = $('#tblRetenciones');

        $.ajax({
            type: 'GET',
            url: 'ajax/json_recibo.php',
            data: {
                listaRetencion: 1
            },
            dataType: 'json',
            beforeSend: function() {
                $('#spinner').show();
            },
            success: function(data) {
                //console.log(data);

                if (data.msg === "ok") {
                    var opt = data.retencion;
                    // console.log(opt);
                    tabla.datagrid({

                        singleSelect: true,
                        fit: false,
                        fitColumns: true,
                        border: true,
                        scrollbarSize: 0,

                        columns: [
                            [{
                                    field: 'key',
                                    title: 'Key',
                                    width: 120,
                                    hidden: true
                                },
                                {
                                    field: 'cod',
                                    title: 'Cod',
                                    width: 120,
                                    hidden: true
                                },
                                {
                                    field: 'retencion',
                                    title: 'Retencion',
                                    width: 120
                                },
                                {
                                    field: 'certificado',
                                    title: 'Cert',
                                    width: 80
                                },
                                {
                                    field: 'porcentaje',
                                    title: '%',
                                    width: 50,
                                    align: 'right'
                                },
                                {
                                    field: 'monto',
                                    title: 'Monto',
                                    width: 100,
                                    align: 'right'
                                }
                            ]
                        ],
                        // data:[{retencion: "Ganancias", certificado: "8825", porcentaje: "5.0", monto: "145.00"}]  
                        data: opt
                    });

                }
                if (data.msg === "no") {
                    if (tabla.data('datagrid')) {
                        // no initialization
                        tabla.datagrid('loadData', []);
                    }


                }

            },
            complete: function() {
                $('#spinner').hide();
            }
        });
    }

    // no hya plata a cuenta hay que hacer medios de cobro 
    // directamente
    // descuento
    function salida() {

        Swal.fire({
                title: "¿Está seguro que desea salir?",
                text: "Si acepta se eliminará el recibo en curso!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#395aa2',
                cancelButtonColor: '#cddfff',
            })
            .then((resultado) => {
                if (resultado.isConfirmed) {
                    console.log('dentro del willdelete');
                    $.ajax({
                        type: 'GET',
                        url: 'ajax/json_recibo.php',
                        data: {
                            salirRecibo: 1
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log(data);

                            if (data.msg === "ok") {
                                console.log({
                                    data
                                });

                                location.href = '../listado-clientes.php';


                            }
                            // error
                            if (data.msg === "error") {
                                // colocar en el titulo que no se pudo cargar el recibo.
                                // poner el mensaje de error y anular el boton de nuevo recibo
                                // pedir solo salir.
                                // alert('hubo un error');
                                location.href = '../listado-clientes.php';


                            }
                        }
                    });
                }
            });
    }
    iniciar();
</script>

</html>