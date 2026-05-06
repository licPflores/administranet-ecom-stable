<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/* 
 * RECIBO WEB CON EASY UI
 * programacion 100% mobil
 * por pasos con guia.
 * 
 * 
 */
//require_once 'sesion.inc.php';
session_start();
// echo "<pre>";
// print_r($_SESSION["recibo"]['transferencia']);
// echo "</pre>";
if (is_object($_SESSION['cliente'])) {
    $clienteObj = $_SESSION['cliente'];
} else {
    $clienteObj = $_SESSION['cliente'][0];
}
//print_r($clienteObj);
$nroRecibo = $_SESSION["recibo"]["nroRecibo"];
$puntoVentaContable = $_SESSION["recibo"]["puntoVentaContable"];
$cuit = $clienteObj->CUIT;
$ocultar ="";
if($puntoVentaContable=='No'){
    $ocultar = ' style="display:none"';
}
//print_r($_SESSION["recibo"]["cheques"]["listado"]);
?>
<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Chrome, Firefox OS and Opera -->
    <meta name="theme-color" content="#395aa2">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Nuevo Recibo - medios de cobro -</title>

    <?php include_once 'inc-header-recibo.php'; ?>

</head>

<body>
    

    <!-- //* PANEL PRINCIPAL MEDIOS PANEL -->
    <div class="easyui-navpanel titulo-recibo-gradiante" id="panelMediosCobro">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title">Medios de cobro</div>
                <div class="m-left">
                    <a href="javascript:void(0)" class="easyui-linkbutton  titulo-recibo" data-options="plain:true"  onclick="salida();"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir</a>
                </div>

            </div>

        </header>

        <div class="contenedor-flex-cabecera">
            <div class="hijo-flex-cabecera ">
                <p class="importe-recibo"><i>$</i><span id="totalRecibo">0.00</span><br>
                    Total recibo</p>
            </div>
            <div class="hijo-flex-cabecera ">
                <p class="cliente-recibo"><span><?php echo $clienteObj->cliente; ?></span>
                    <br>Codigo: <?php echo  $clienteObj->Codigo; ?>
                    <br>Saldo: $<?php echo  number_format($clienteObj->saldo, 2, ",", "."); ?>
                </p>
            </div>

        </div>

        <div class="contenedor-medios-cobro-inicio">

            <div class="bloque-estado">

                <div class="hijo-estado linea">
                    <p class="texto-estado total-a-cubrir"> <i class="a-cubrir-alt">$</i><span id="totalAcubrir" class="a-cubrir-alt">0.00</span>
                        <br>
                        Imputado
                    </p>
                </div>
                <div class="hijo-estado linea">
                    <p class="texto-estado total-saldo"> <i>$</i> <span id="totalSaldo">0.00</span>
                        <br>
                        A cubrir
                    </p>
                </div>
                <div class="hijo-estado">
                    <p class="texto-estado total-a-cuenta"><i class="a-cuenta-alt">$</i><span id="totalAcuenta" class="a-cuenta-alt">0.00</span>
                        <br>
                        A cuenta
                    </p>
                </div>
            </div>
            <h3 class="titulo-medios-cobro">Medios de cobro</h3>
            <div class="bloque-medios-cobro" onclick="mostrar_panel_efectivo();" id="divMedioCobroEfectivo">

                <div class="flex-hijo-medios-cobro">
                    <div class="icono-mc pesos">
                        <i class="fa fa-money-bill fa-lg fa-fw"></i>
                    </div>
                </div>

                <div class="flex-hijo-medios-cobro texto-mc">
                    <p class="titulo-mc">Efectivo</p>
                    <!-- <p class="detalle-mc">pesos, moneda local.</span> -->
                </div>
                <div class="flex-hijo-medios-cobro total-mc">
                    <p class="importe-mc total-efectivo-pesos">
                        $<span id="totalEfectivoPesos">0.00</span>
                    </p>

                </div>

            </div>
            <div class="bloque-medios-cobro" onclick="mostrar_panel_dolar();" id="divMedioCobroEfectivoDolar">

                <div class="flex-hijo-medios-cobro">
                    <div class="icono-mc dolar">
                        <i class="fa fa-money-bill-alt fa-lg fa-fw"></i>
                    </div>
                </div>

                <div class="flex-hijo-medios-cobro texto-mc">
                    <p class="titulo-mc">Efectivo U$S</p>
                    <!-- <p class="detalle-mc">total dolar en pesos</p> -->
                </div>

                <div class="flex-hijo-medios-cobro total-mc">
                    <p class="importe-mc total-efectivo-dolar-pesos">
                        $<span id="totalEfectivoDolar ">0.00</span>
                    <p class="detalle-mc total-dolar" style="text-align:right">u$s <span id="totalDolar"></span></p>
                    </p>

                </div>

            </div>
            <div class="bloque-medios-cobro" onclick="mostrar_panel_cheques();" id="divMedioCobroCheque">
                <div class="flex-hijo-medios-cobro">
                    <div class="icono-mc cheque">
                        <i class="fa fa-money-check-alt fa-lg fa-fw"></i>
                    </div>

                </div>
                <div class="flex-hijo-medios-cobro texto-mc">
                    <p class="titulo-mc">Cheques</p>
                    <!-- <p class="detalle-mc">cant</span> -->
                </div>
                <div class="flex-hijo-medios-cobro total-mc">
                    <p class="importe-mc total-cheque">
                        $<span id="totalCheque">0.00</span>
                    </p>

                </div>

            </div>
            <div class="bloque-medios-cobro" onclick="mostrar_panel_transferencias();" id="divMedioCobroTransferencia">

                <div class="flex-hijo-medios-cobro">
                    <div class="icono-mc transferencia">
                        <i class="fa fa-landmark fa-lg fa-fw"></i>
                    </div>
                </div>
                <div class="flex-hijo-medios-cobro texto-mc">
                    <p class="titulo-mc">Transferencia Bancaria</p>
                    <p class="detalle-mc cantidad-transferencia"></p>

                </div>
                <div class="flex-hijo-medios-cobro total-mc">
                    <p class="importe-mc total-transferencia">
                        $<span id="totalTransferencia">0.00</span>
                    </p>
                </div>

            </div>
            <div class="bloque-medios-cobro" onclick="mostrar_panel_tarjetas();" id="divMedioCobroTarjeta">

                <div class="flex-hijo-medios-cobro">
                    <div class="icono-mc tarjeta">
                        <i class="far fa-credit-card fa-lg fa-fw"></i>
                    </div>
                </div>
                <div class="flex-hijo-medios-cobro texto-mc">
                    <p class="titulo-mc">Tarjetas credito/ debito</p>
                    <!-- <p class="detalle-mc">visa,master </p> -->
                </div>
                <div class="flex-hijo-medios-cobro total-mc">
                    <p class="importe-mc total-tarjeta">
                        $<span id="totalTarjeta">0.00</span>
                    </p>

                </div>

            </div>
        </div>

        <footer>


            <div style="padding:10px;text-align: center">
                <a href="javascript:void(0)" class="easyui-linkbutton primario" id="botonResumen" data-options="disabled:true" style="width:100%" onclick="trae_resumen_recibo();">Finalizar <i class="fas fa-angle-right fa-fw fa-lg"></i></a>
            </div>

        </footer>


    </div>
    <!-- FIN PANEL CONTRL MEDIOS  -->

    <!--FIN CUERPO EFECTIVO DOLAR-->
    <?php include_once('inc-medio-efectivo.php');?>


    <!--PANEL CUERPO CHEQUES-->
    <!--=================================================================-->
    <?php include_once('inc-medio-cheque.php');?>
    <!--FIN PANEL CUERPO CHEQUES-->

    <!-- PANEL CUERPO TARJETAS CREDITO
        ==================================================================== -->
        <?php include_once('inc-medio-tarjeta.php');?>
        <!-- FIN PNAEL CUERPO TARJETAS CREDITO -->

    <!-- CUERPO TRANSFERENCIA BANCARIA
        =============================================================== -->
        <?php include_once('inc-medio-transferencia.php');?>
    
    <!-- FIN PANEL TRANSFERENCIA BANCARIA -->

    <!-- PANEL RESUMEN RECIBO -->
    <!-- ====================================================================-->
    <div class="easyui-navpanel titulo-recibo-gradiante" id="panelResumen">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title">Resumen final</div>
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton titulo-recibo" data-options="plain:true"  onclick="$.mobile.go('#panelMediosCobro','fade','left');"><i class="fas fa-arrow-left fa-lg fa-fw"></i> Volver</a>


                </div>

            </div>

        </header>

        <div class="contenedor-medios-cobro">

            <div >
                <h3><span>Cliente: <?php echo $clienteObj->cliente; ?></span>
                    <br>Codigo: <?php echo  $clienteObj->Codigo; ?>
                    <br>Saldo: $<?php echo  number_format($clienteObj->saldo, 2, ",", "."); ?>
            </h3>

            </div>
           
            <div>

                <table id="tblResumenRecibo"></table>

                
                <h3 class="titulo-medios-cobro">Imputaciones</h3>
                <table id="tblResumenImputacion"></table>

                
                <h3 class="titulo-medios-cobro">Medios de cobro</h3>
                <!-- <table id="tblResumenMedios" data-options="header:'#hmedios'"></table> -->
                <table id="tblResumenMedios"></table>
            </div>



            <div>
                <!--<a href="javascript:void(0)" class="easyui-linkbutton" onclick="location.href='alta_recibo_medios_cobro.php'" style="width:100%"><i class="fas fa-angle-right fa-fw fa-lg"></i>Siguiente</a>-->
                <a href="javascript:void(0)" id="botonResumenFin" class="easyui-linkbutton primario" onclick="guardar_recibo();" style="width:49%"><i class="fas fa-check fa-fw fa-lg"></i> Generar recibo</a>


                <a href="javascript:void(0)" class="easyui-linkbutton secundario" id="botonEfectivo" onclick="$.mobile.go('#panelMediosCobro','fade','left');" style="width:49%"><i class="fas fa-times fa-lg fa-fw"></i> Cancelar</a>

            </div>

        </div>
    </div>


    <!-- == FIN PANEL RESUMEN RECIBO -->




    <!--== PANEL ENVIO EMAIL == -->




    <!--== FIN PANEL ENVIO == -->
    <!--DIALGO DE MENSAJES-->
    <div id="dlgMensaje" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Información'">
        <p id="mensajeDialog" style="text-align: center;">This is a message dialog.</p>
        <div class="dialog-button">
            <a href="javascript:void(0)" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensaje').dialog('close');">OK</a>
        </div>
    </div>
    <!--FIN DIALOGO MENSAJES-->

    <!--DIALGO DE CONFIRMACION ACEPTAR Y CANCELAR -->
    <div id="dlgMensajeOption" class="easyui-dialog" style="padding:20px 6px;width:80%;" data-options="inline:true,modal:true,closed:true,title:'Confirmación'">
        <p id="mensajeDialogOption" style="text-align: center;">This is a message dialog.</p>
        <!--            <div class="dialog-button">
                <a href="javascript:void(0)" id="aceptar" class="easyui-linkbutton" style="width:100%;height:35px" ><i class="fas fa-check"></i> Aceptar</a>
                <a href="javascript:void(0)" id="cancelar" class="easyui-linkbutton" style="width:100%;height:35px" onclick="$('#dlgMensajeOption').dialog('close');"><i class="fas fa-times"></i> Cancelar  </a>
            </div>-->
    </div>
    <!--FIN DIALOGO MENSAJES-->
    <div id="spinner" class="spinner" style="display:block;">
        <div class="centro">
            <img src="../_img/logo-administranet-ecommerce.png">
            <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
        </div>
    </div>

</body>
<script type="text/javascript" src="js/funciones-recibo.js"></script>
<script type="text/javascript" src="js/medios-efectivo.js"></script>
<script type="text/javascript" src="js/medios-dolar.js"></script>
<script type="text/javascript" src="js/medios-cheque.js"></script>
<script type="text/javascript" src="js/medios-transferencia.js"></script>
<script type="text/javascript" src="js/medios-tarjeta.js"></script>
<script type="text/javascript" src="js/retenciones.js"></script>
<script type="text/javascript" src="js/medios-subtotal.js"></script>
<script>    
     iniciar();
    //$('#efectivoCobro').focus();
    // $(document).ready(function() {
    //     var t = $('#chImporte');
    //     t.textbox('textbox').bind('keydown', function(e) {
    //         if (e.keyCode == 13) { // when press ENTER key, accept the inputed value.
    //             //t.textbox('setValue', $(this).val());
    //             console.info('hola soy un ENTER');
    //         }
    //     });
    // })
    // bloquear fomras de pago si viene con pv no contable.
</script>

</html>