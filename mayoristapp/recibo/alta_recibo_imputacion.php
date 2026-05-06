<?php
require_once '../sesion.inc.php';

if (is_object($_SESSION['cliente'])) {
    $clienteObj = $_SESSION['cliente'];
} else {
    $clienteObj = $_SESSION['cliente'][0];
}

$nroRecibo = "";
$desde = date("d/m/Y", strtotime("-1 year"));
$hasta = date("d/m/Y");


$rec = $_SESSION["recibo"];
//echo "<pre>";
//print_r($_SESSION["recibo"]);
?>
<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#395aa2">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Facturas a imputar - Nuevo recibo</title>

    <?php include_once 'inc-header-recibo.php'; ?>
    
</head>

<body>

    <div class="easyui-navpanel titulo-recibo-gradiante" id="panelImputacion">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <span class="m-title"><i class="fas fa-file-invoice"></i> Imputacion de facturas</span>
               
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true" onclick="salida()"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>

                </div>
            </div>
           
        </header>

        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">
                <div class="hijo-estado linea">
                    <p class="texto-estado total-recibo"><i class="titulo-recibo-alt">$</i><span id="totalSaldoClienteFactura" class="titulo-recibo-alt">0.00</span>
                        <br>
                        Saldo cliente
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i class="positivo-alt">$</i><span id="totalImputadoFactura" class="positivo-alt">0.00</span>
                        <br>
                        Imputado
                    </p>
                </div>

                <div class="hijo-estado">
                    <p class="texto-estado total-tarjeta"><i class="a-cubrir-alt">$</i><span id="totalAcuentaFactura" class="a-cubrir-alt">0.00</span>
                        <br>
                        A cuenta
                    </p>
                </div>
            </div>
            <!-- <div style="text-align: center;font-size:x-small"><?php echo $clienteObj->cliente; ?></div> -->
            <h3 class="titulo-medios-cobro">Facturas a imputar</h3>
            <div id="dl" data-options="
                fit: true,
                border: false,
                lines: true
                ">
            </div>
        </div>
        <footer>
            
            <div style="padding:3px;">
                <a id="btn" href="javascript:void(0)" class="easyui-linkbutton primario" style="width:100%" onclick="finalizar_imputacion()">Siguiente <i class='fas fa-chevron-right fa-fw fa-lg'></i></a>
            </div>
        </footer>


    </div>

    <div id="dlgImputacion" class="easyui-dialog" style="padding:20px 6px;width:80%;top:0px" data-options="inline:true,modal:true,closed:true,title:'Imputar'">
        <div style="margin-bottom:10px">
            <!--<input class="easyui-textbox" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" id="montoImputar" min="0" decimalSeparator="," precision="2" required="true" prefix="$" missingMessage="Debe completar el monto a impútar"   prompt="monto efectivo " style="width:90%" label="Monto:">-->
            <input class="easyui-textbox" inputmode="decimal" type="number" pattern="[-+]?[0-9]*[.,]?[0-9]+" id="montoImputar" min="0" required="true" decimalSeparator="," prefix="$" missingMessage="Debe completar el monto a impútar" prompt="0.00" style="width:90%" label="<strong>Monto:</strong>">
        </div>
        <div style="margin-bottom:10px" id="cuerpoDlgImputacion">
        </div>

    </div>

    <div id="dlgNoImputa" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Desimputar'">
        <p id="mensajeDialogNo">¿Esta seguro que desea desimputar la factura?</p>
    </div>

    <div id="dlgMensaje" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Información'">
        <p id="mensajeDialog" style="text-align: center;">This is a message dialog.</p>
        <div class="dialog-button">
            <a href="javascript:void(0)" class="easyui-linkbutton primario" style="width:100%;height:35px" onclick="$('#dlgMensaje').dialog('close');"><i class="fas fa-check fa-lg fa-fw"></i> OK</a>
        </div>
    </div>



    <!-- FIN resumen de lo imputado -->
    <div class="easyui-navpanel titulo-recibo-gradiante" id="panelFin" data-options="openDuration:1000">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <span class="m-title">Resumen</span>
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton  titulo-recibo" data-options="plain:true"  onclick="$.mobile.go('#panelImputacion','slide','right');"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>


                </div>
            </div>
        </header>
        <div class="contenedor-medios-cobro">
            <div class="bloque-estado">

                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i class="positivo-alt">$</i><span id="totalImputadoFacturaResumen" class="positivo-alt">0.00</span>
                        <br>
                        Total imputado
                    </p>
                </div>


            </div>
            <div >
                <h3 class="titulo-medios-cobro"><p style="font-size:smaller">Cliente: <strong><?php echo $clienteObj->cliente; ?></strong> 
                    (Id: <strong><?php echo  $clienteObj->Codigo; ?></strong>)
                    <br>Saldo en cuenta: <strong class="a-cubrir-alt">$<?php echo  number_format($clienteObj->saldo, 2, ",", "."); ?></strong></p>
                </h3>

            </div>
            <h3 class="titulo-medios-cobro">Facturas imputadas</h3>
            <div id="dlFin" data-options="
                fit: true,
                border: false,
                lines: true,                
                ">
            </div>
        </div>
        <footer>
            <div style="text-align:center;padding:5px">


                <a href="javascript:void(0);" class="easyui-linkbutton secundario" style="width:49%" onclick="$.mobile.go('#panelImputacion','slide','right');"><i class="fas fa-times fa-lg fa-fw"></i> Cancelar</a>
                <a href="javascript:void(0);" class="easyui-linkbutton primario" style="width:49%" onclick="confirmar_imputacion()"> <i class="fas fa-check fa-lg fa-fw"></i> Confimar </a>

            </div>
            <!--            <div class="m-toolbar">-->
            <!-- <div style="padding:10px">
                    <input class="easyui-numberbox" id="totalImputado" min="0" groupSeparator='.'  decimalSeparator="," precision="2"   data-options="readonly:true,prefix:'$'"   prompt="0" style="width:90%" label="Imputado:">
                    
                </div> -->
            <!--</div>-->
        </footer>

    </div>
    <div id="spinner" class="spinner" style="display:none;">
        <div class="centro">
            <img src="../_img/logo-administranet-ecommerce.png">
            <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
        </div>
    </div>

    <script>
        var varTotalImputado = 0.00; // monto que voy imputando de cada factura
        var varTotalSaldo = 0.00; // sumatoria de las facturas
        var varTotalaCuenta = 0.00; // si hay dinero a cuenta a modo informativo.
        var objTotalImputado = $('#totalImputadoFactura');
        var objTotalSaldoCliente = $('#totalSaldoClienteFactura');
        var objTotalAcuenta = $('#totalAcuentaFactura');
        var objTotalImputadoResumen = $('#totalImputadoFacturaResumen');


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
            return (d < 10 ? ('0' + d) : d) + '/' + (m < 10 ? ('0' + m) : m) + '/' + y;
        };


        $.fn.datebox.defaults.parser = function(s) {
            if (!s) return new Date();
            var ss = s.split('/');
            var y = parseInt(ss[2], 10);
            var m = parseInt(ss[1], 10);
            var d = parseInt(ss[0], 10);
            if (!isNaN(y) && !isNaN(m) && !isNaN(d)) {
                return new Date(y, m - 1, d);
            } else {
                return new Date();
            }
        };

        function buscar() {
            console.log('debo volver a pasar por esto');
            $('#dl').datalist('load', {
                name: 'easyui',
                subject: 'datagrid',
                listarFacturas: 1,
                //                    desde: fechaReves($('#fDesde').datebox('getValue')),
                //                    hasta: fechaReves($('#fHasta').datebox('getValue')),
                cliente: <?php echo $objCliente->Codigo; ?>
            });
        }

        function fechaReves(s) {
            var ss = s.split('/');
            var y = parseInt(ss[2], 10);
            var m = parseInt(ss[1], 10);
            var d = parseInt(ss[0], 10);
            return y + '-' + m + '-' + d;
        }

        // total de imputacion

        function total_imputado() {


            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    totalReciboCheque: 1
                },
                dataType: 'json',
                success: function(data) {
                    console.log("total_imputado =>", data);
                    console.log(data);

                    if (data.msg === "ok") {
                        console.log("dentro del ok del imputado total::" + data.total);
                        // lleno el footer 

                        //$('#montoImputarTotal').numberbox('setValue',data.total);
                        varTotalImputado = data.total;
                        objTotalImputado.text(numerito.format(data.total));
                        objTotalImputadoResumen.text(numerito.format(data.total));


                    }

                }
            });

        }

        // calculo de la deuda de facturas.

        function calculo_deuda() {
            console.log("calculando la deuda ");
            var deuda = 0,
                totalDeuda = $('#montoDeudaTotal');
            var rows = $('#dl').datalist('getRows');
            console.log({
                rows
            });
            for (var i = 0; i < rows.length; i++) {
                console.log(rows[i].Saldo);
                deuda += parseFloat(rows[i].Saldo);
            }
            //totalDeuda.numberbox('setValue',deuda);
            varTotalSaldo = deuda;
            objTotalSaldoCliente.text(numerito.format(deuda));
        }

        // finalizar de imputacion.

        function finalizar_imputacion() {
            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    finImputacion: 1
                },
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.msg === "fallo") {
                        $('#mensajeDialog').text("Debe imputar facturas");
                        $('#dlgMensaje').dialog('open').dialog('center');
                    }
                    if (data.msg === "ok") {
                        // hacer algo con el row.
                        var fact = data.resumen;

                        $('#dlFin').datalist({
                            data: fact,
                            textField: 'factura',


                            textFormatter: function(value, row) {
                                //                    console.log(row);
                                return '<p class="renglon-factura"><i class="far fa-check-square fa-lg fa-fw"></i>' + value + ' Imputado: <strong>' + dinero.format(row.imputado) + '</strong>'+ '<br>  Saldo: ' + dinero.format(row.saldo) +'</p>';
                            }
                        });

                        //$('#totalImputado').numberbox('setValue',data.total);
                        varTotalImputado = data.total;
                        objTotalImputado.text(numerito.format(data.total));

                        $.mobile.go('#panelFin', 'slide', 'right');

                    }
                }
            });
        }

        function confirmar_imputacion() {
            // $('#spinner').show();
            location.href = "alta_recibo_descuento.php";

        }

        // funcion para traer lo que hay a cuenta.

        function traer_recibos_cuenta() {
            var montoaCuenta = $('#montoAcuenta');

            $.ajax({
                type: 'GET',
                url: 'ajax/json_recibo.php',
                data: {
                    traeAcuenta: 1
                },
                dataType: 'json',
                success: function(data) {
                    console.log('traer recibo a cuenta=>', data);

                    if (data.msg === "ok") {
                        //console.log({data});
                        //montoaCuenta.numberbox('setValue',data.acuenta);
                        varTotalaCuenta = data.acuenta;
                        objTotalAcuenta.text(data.acuenta);
                    }
                    // error
                    if (data.msg === "error") {
                        // colocar en el titulo que no se pudo cargar el recibo.
                        // poner el mensaje de error y anular el boton de nuevo recibo
                        // pedir solo salir.
                        // alert('hubo un error');
                        //console.log(data);
                        //montoaCuenta.numberbox('setValue',0);
                        varTotalaCuenta = 0
                        objTotalAcuenta.numerito.format(0);


                    }
                }
            });


        }

        $(function() {
            var imputadoFactura = 0;
            // rango de fechas 

            $('#dl').datalist({
                //data: data,
                url: 'ajax/json_recibo.php',
                queryParams: {
                    name: 'easyui',
                    subject: 'datagrid',
                    listarFacturas: 1
                    //                    desde: fechaReves($('#fDesde').datebox('getValue')),
                    //                    hasta: fechaReves($('#fHasta').datebox('getValue')),

                },
                emptyMsg: 'No se encontraron resultados',
                textField: 'item',
                valueField: 'id',
                checkbox: false,
                checkOnSelect: false,
                singleSelect: false,
                textFormatter: function(value, row, index) {
                    
                    var linea = ' <a href="javascript:void(0)" class="datalist-link renglon-factura" ><i class="far fa-square fa-lg fa-fw"></i> ' + value + ' <br><span>Fecha: ' + row.FechaB + ' Saldo a imputar: <strong>$' + numerito.format(row.Saldo) + '</strong></a>';
                    
                    // estoy imputado pinto.
                    if(parseFloat(row.imputado)>0.00){
                        let saldoTexto=' Saldo a imputar: <strong>$'+numerito.format(row.Saldo)+'</strong>';
                        if(row.Saldo==0){
                            // saldoTexto = ' Saldo a imputar: -';
                            saldoTexto = '';
                        
                        }


                        linea = '<a href="javascript:void(0)" class="datalist-link renglon-factura-imputada" ><i class="far fa-check-square fa-lg fa-fw"></i> ' + value + ' - Imputado: <strong>$' +numerito.format(row.imputado)+ '</strong>'+saldoTexto+'</a>';
                    }
                    // sin imputacion.
                    if(parseFloat(row.imputado)==0.00){
                        linea = ' <a href="javascript:void(0)" class="datalist-link renglon-factura" ><i class="far fa-square fa-lg fa-fw"></i> ' + value + '  <p class="detalle-factura">Fecha: ' + row.FechaB + ' - Saldo a imputar: <strong>$' + numerito.format(row.Saldo) + '</strong></p></a>';
                    }


                    
                    return linea;
                },
                onLoadSuccess: function() {
                    calculo_deuda();
                    total_imputado();
                    
                    // verificar si hubo imputaciones ya cargadas.
                    
                },
                //onClickRow
                onSelect: function(index, row) {
                    imputadoFactura =0.00;
                   // si tengo valor imputado me salgo 
                //    if(parseFloat(row.imputado)!=0){
                //        return;
                //    }  
                   if(parseFloat(row.imputado)==0){
                   
                    // console.info('vengo desde el row y si soy imputado no tengo que abrir de nuevo.',row.imputado);
                    $('#dlgImputacion').dialog({
                        'title': row.item,
                        'buttons': [{
                                id:'botonImputarDialog',
                                text: '<i class="fas fa-check fa-lg fa-fw"></i> Imputar',

                                handler: function() {
                                    console.log('soy el handler del boton imputar');
                                    console.log('total a imputarcion?', imputadoFactura);
                                    var monto = parseFloat($('#montoImputar').numberbox('getValue'));
                                    var maximo = parseFloat(row.Saldo);
                                    var todoBien = 0;
                                    // guardar el importe en el json.
                                    console.log($('#montoImputar').numberbox('getValue'));
                                    console.log('monto:' + monto);
                                    console.log('maximo:' + maximo);
                                    console.log(monto > maximo);
                                    console.log('que resultado');
                                    if (monto > maximo) {
                                        todoBien++;
                                        //$('#mensajeDialog').html("El monto $"+ monto +" a imputar supera el máximo <strong>$"+row.Saldo+"</strong>");

                                        //$('#dlgMensaje').dialog('open').dialog('center');
                                        //$('#montoImputar').numberbox('clear').numberbox('textbox').focus();
                                        $('#montoImputar').numberbox('setValue', 0);

                                        console.log("NO PODES SEGUIR");
                                    }
                                    if (monto <= 0) {
                                        todoBien++;
                                        //$('#mensajeDialog').text("El monto debe ser mayor a cero y positivo ");
                                        //$('#dlgMensaje').dialog('open').dialog('center');
                                        Swal.fire({
                                            html: 'El monto debe ser mayor a <strong>cero</strong> y <strong>valor positivo</strong>',
                                            icon: 'warning',
                                            confirmButtonColor: '#395aa2'

                                        });
                                    }
                                    //console.log(row);
                                    // ajax de la imputacion 
                                    if (todoBien == 0) {
                                        imputadoFactura = monto;

                                        $.ajax({
                                            type: 'GET',
                                            url: 'ajax/json_recibo.php',
                                            data: {
                                                imputarFactura: 1,
                                                idrecibofactura: row.id_recibo_factura,
                                                codmodfact: row.CodigoMovimiento,
                                                fecha: row.Fecha,
                                                nrofactura: row.NroComprobante,
                                                importe: row.Importe,
                                                cancelado: row.Cancelado,
                                                saldo: row.Saldo,
                                                tipocomprobante: row.TipoComprobante,
                                                vencimiento: row.Vencimiento,
                                                condventa: row.CondVenta,
                                                aimputar: monto

                                            },
                                            dataType: 'json',
                                            success: function(data) {
                                                console.log(data);
                                                if (data.msg === "ok") {
                                                    // hacer algo con el row.
                                                    row.Saldo = row.Saldo - monto;
                                                    row.imputado = row.imputado+monto;

                                                    //console.log(row.Saldo);
                                                    // llamar arecalculo del recibo 
                                                    // y marcar la factura de alguna forma como lista.
                                                    // aviso que todo bien
                                                    //$(this).datalist.refreshRow(index);
                                                    $('#dlgImputacion').dialog('close');
                                                    $('#dl').datalist('refreshRow', index);
                                                    total_imputado();
                                                    calculo_deuda();

                                                    //$('#mensajeDialog').html('<p class="detalle-mc">Se imputó <strong>$' + monto + '</strong> <br>a factura: <strong>' + row.item + '</strong>');

                                                    //$('#dlgMensaje').dialog('open').dialog('center');
                                                    Swal.fire({
                                                        title:'Muy bien!',
                                                        position: 'top',
                                                        html:'Se imputó <strong>$' + monto + '</strong> <br>a factura: <strong>' + row.item + '</strong>',
                                                        icon:'success',
                                                        confirmButtonColor:'#395aa2' 
                                                    });

                                                }
                                            }
                                        });

                                    }


                                }
                            },
                            {
                                id:'botonCancelarDialog',    
                                text: '<i class="fas fa-times fa-fw fa-lg"></i> Cancelar',
                                handler: function() {
                                    console.log('soy el handerl del cancelar de la fascturas.');
                                    // preguntar primero si fui seleccionado antes.
                                    var seleccionados = $('#dl').datalist('getSelections');
                                    console.log('click boton cancelar imputacion');
                                    console.log('hay seleccionados?', seleccionados);
                                    $('#dl').datalist('unselectRow', index);
                                    $('#dlgImputacion').dialog('close');
                                }

                            }
                        ]

                    });
                    // continuo creando la dialog


                   // pruebaImputacion(row, index);
                    $('#dlFecha').textbox('setValue', row.FechaB);
                    $('#dlImporte').textbox('setValue', row.Importe);
                    // $('#dlCancelado').textbox('setValue',row.Cancelado);
                    $('#dlaCancelar').textbox('setValue', row.Saldo);
                    var cuerpoImp = "";
                    cuerpoImp += '<div class="div-linea-dialog texto-izquierda">Saldo a imputar:</div>';
                    cuerpoImp += '<div class="div-linea-dialog texto-izquierda"><strong  class="primario">' + dinero.format(row.Saldo) + '</strong></div>';
                    cuerpoImp += '<div class="div-linea-dialog texto-izquierda">Fecha:</div>'; 
                    cuerpoImp +=' <div class="div-linea-dialog texto-izquierda"> <strong>' + row.FechaB + '</strong></div>';
                    cuerpoImp += '<div class="div-linea-dialog texto-izquierda">Importe: </div>';
                    cuerpoImp += '<div class="div-linea-dialog texto-izquierda"><strong>' + dinero.format(row.Importe) + '</strong></div>';
                    
                    $('#cuerpoDlgImputacion').html(cuerpoImp);


                    //                    $('#montoImputar').numberbox('setValue',row.Saldo);
                    $('#montoImputar').textbox('setValue', row.Saldo);
                    //$('#montoImputar').val(row.Saldo);

                    // bloq


                    $('#dlgImputacion').dialog('open').dialog('hcenter');
                    $('#montoImputar').numberbox('textbox').focus();
                    //                    $('#p2-title').html(row.item);
                    //                    $.mobile.go('#p2');
                    //                console.log(row);.}
                }
                },

                onBeforeUnselect: function(index, row) {
                    //alert('me hicieron before un select unselect');  
                    //console.log('before unselect'+{row});
                    // console.log('soy la linea del unselect', row);
                    $('#dlgNoImputa').dialog({
                        'title': row.item,
                        'buttons': [{
                                id: 'botonDesimputarDialog',
                                text: '<i class="fas fa-trash fa-fw fa-lg"></i> Desimputar',
                                handler: function() {
                                    // ajax de la imputacion 
                                    $.ajax({
                                        type: 'GET',
                                        url: 'ajax/json_recibo.php',
                                        data: {
                                            desimputarFactura: 1,
                                            idrecibofactura: row.id_recibo_factura


                                        },
                                        dataType: 'json',
                                        success: function(data) {
                                            console.log('que regresa al desimputara=?',data);
                                            if (data.msg === "ok") {
                                                // hacer algo con el row.
                                                console.log(" Desimputar saldoantes::::" + row.Saldo);
                                                var nuSaldo = data.saldoNuevo;
                                                var monto = nuSaldo - row.Saldo;
                                                row.Saldo = nuSaldo;
                                                row.imputado = 0.00;
                                                console.log("Saldo nuevo::" + row.Saldo);

                                                // llamar arecalculo del recibo 
                                                // y marcar la factura de alguna forma como lista.
                                                // aviso que todo bien
                                                $('#dlgNoImputa').dialog('close');

                                                // $('#mensajeDialog').html("Se desimputó <strong>" + dinero.format(monto) + "</strong> <br>a factura: <strong>" + row.item + "</strong>");
                                                // $('#dlgMensaje').dialog('open').dialog('center');

                                                $('#dl').datalist('refreshRow', index);
                                                
                                                total_imputado();
                                                calculo_deuda();
                                                Swal.fire({
                                                    icon:'success',
                                                    html:'Se desimputó <strong>' + dinero.format(monto) + '</strong> <br>a factura: <strong>' + row.item + '</strong>',
                                                    confirmButtonColor:'#395aa2'
                                                });
                                            }
                                        }
                                    });
                                }
                            },
                            {
                                id:'botonCancelarDialog',
                                text: '<i class="fas fa-times fa-lg fa-fw"></i>Cancelar',
                                handler: function() {
                                    $('#dlgNoImputa').dialog('close');
                                    return false;
                                }
                            }
                        ]

                    });
                    // que no se abra si esta sin imputar nada.
                    console.log('cancele la imputacion que hago');
                    console.log('imputado por la factura=>', imputadoFactura);
                    console.log('valor de imputaicon en campo', $('#montoImputar').numberbox('getValue'));
                    //console.log(row);
                    //var monti=parseFloat($('#montoImputar').numberbox('getValue'));
                    let monti = imputadoFactura;
                    console.log(monti);
                    if (monti !== 0) {
                        $('#dlgNoImputa').dialog('open').dialog('center');
                    }
                    //return false;
                }




            });
            // consultar si hay plata a favor
            //           var facturitasD=  $('#dl').datalist('getRows');
            //           console.log("facturitasD");
            //           console.log({facturitasD});
            traer_recibos_cuenta();
        });

        

        function pruebaImputacion(linea, indice) {
            console.log('soy la linea de facturas=>=>=>', linea);
            console.log('soy el indice de la linea que hice click==>=>', indice);
        }

        function desimputar(row, index) {
            console.log("hola vengo a desimputar");
            console.log({
                row
            });
            console.log({
                index
            });
            console.log("index a desimputar::" + index);

        }

        function salida() {
            // $('#mGeneralEf').menu('hide', true);
            //$('#mGeneralCh').menu('hide', true);
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
    </script>
</body>

</html>