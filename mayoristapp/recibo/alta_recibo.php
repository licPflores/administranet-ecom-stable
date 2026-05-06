<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
/* 
 * RECIBO WEB CON EASY UI
 * programacion 100% mobil
 * por pasos con guia.
 * #TODO: buscar la tabla temporal con los recibos precargados si hay alguno finalizo el recibo elimino todo.
 * #TODO: guardar mal el cliente cuando finializa el recibo se asigna la cuenta a otro cliente.
 * #TODO: Medios de pago: cheque electronico, mercado pago ,etc
 * #TODO: input mode: numeric, decimal
 * #TODO: usar cookies para guardar el cliente o el json del recibo.
 * #TODO: guardar la sesion con el codigo de cliente como cabecera del recibo, tambioen guardar en cookie.
 * #TODO: general usar cookie para guardar el usuario e ingresar directamente. 
 * 
 * #TODO: controlar en fact_temporal no debe haber con el mismo cliente ni con el mismo usuario cargado nada. salir si es asi
 * #TODO: crear cookie del recibo con el cliente y el numero y se ira recuperando de ahi , problema con las sessiones en celulares. sobre todo al final.
 * #TODO: cambiar las sweet alert por sweet alert2 
 * #TODO: UNIFICAR cabeceras
 * #TODO: SACAR JAVASCRIPT A ARCHVIVOS
 * 
 */
require_once '../sesion.inc.php';

# sin cliente
if (!isset($_SESSION['cliente'])) {
    header("Location: ../listado-clientes.php");
    exit();
}



if (is_object($_SESSION['cliente'])) {
    $clienteObj = $_SESSION['cliente'];
} else {
    $clienteObj = $_SESSION['cliente'][0];
}

# control fact_temporal
// controlo que no exista alguien cargando un recibo de ese cliente.
$errorTemporal = 0;
$motivoError = '';
if (isset($clienteObj)) {
    $sqlTemporal = "SELECT 
                        fact_temporal.Codigo,
                        fact_temporal.id_fact_temporal 
                        FROM 
                        fact_temporal 
                        WHERE 
                        fact_temporal.Codigo=" . $clienteObj->Codigo;
    $hacerTemp = mysqli_query($connV, $sqlTemporal);
    if ($hacerTemp) {
        $cuantos = mysqli_num_rows($hacerTemp);
        if ($cuantos > 0) {
            $errorTemporal++;
            $motivoError = 'No puede generar el <strong>recibo</strong> <br> otro <strong>Usuario</strong> está generando recibos para ese cliente.<br> Intentelo más tarde';
        }
    }

    if (!$hacerTemp) {
        echo 'Error: ' . mysqli_error($connV) . '<pre>' . print_r($sqlTemporal) . '</pre>';
        $errorTemporal++;
        $motivoError = 'No puede generar el <strong>recibo</strong>, ocurrio un problema,<br> intentelo más tarde.';
    }
}

//
//echo "</pre>";
//print_r($_SESSION["puntos_de_venta_usr"]);
# control punto de ventas
$pvta = array();

if (isset($_SESSION["puntos_de_venta_usr"])) {
    $pvta = $_SESSION["puntos_de_venta_usr"];
}


$usuario = $_SESSION["vendedor"];
//print_r($usuario);
?>
<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#395aa2">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>Nuevo Recibo</title>

    <!-- <link rel="stylesheet" type="text/css" href="https://www.jeasyui.com/easyui/themes/color.css"> -->
    <!-- <link rel="stylesheet" type="text/css" href="_lib/easyui/themes/black/easyui.css"> -->

    <!-- <link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/mobile.css"> 
    <link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/bootstrap/easyui.css"> 
    
    <link rel="stylesheet" type="text/css" href="../_lib/easyui/themes/icon.css">  
    <link rel="stylesheet" type="text/css" href="../_lib/easyui110/themes/bootstrap/easyui.css">
    <link rel="stylesheet" type="text/css" href="../_lib/easyui110/themes/mobile.css">
    <link rel="stylesheet" type="text/css" href="recibo.css">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous"> 
  

    <script type="text/javascript" src="../_lib/easyui/jquery.min.js"></script>  
    <script type="text/javascript" src="../_lib/easyui/jquery.easyui.min.js"></script> 
    <script type="text/javascript" src="../_lib/easyui/jquery.easyui.mobile.js"></script> 

    meter las swift si no joden..y permiten cuando sea necesario mostrarlas.----
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

     <style>
         .spinner {
            background-color:       #e8e8e8a8; 
/*            top:                    0%;
            left:                   0%;*/
            text-align:             center;
            z-index:                1234;
            overflow:               auto;
            width:                  100%; /* width of the spinner gif */
            height:                 100%; /*hight of the spinner gif +2px to fix IE8 issue */
           
            border:                 1px solid #DDDDDD;
            position:               absolute;
            padding:                10px;
            top:0;
        }
        .spinner div.centro{
            margin-top: 30%;
            background-color: #ffffff;
            height:150px;
            text-align: center;
            padding: 30px;
        }
        .spinner div.texto{
            margin-top: 30px;
            font-size: 20px;
        }
     </style> -->
    <?php include_once 'inc-header-recibo.php'; ?>
</head>

<body>
    <div class="easyui-navpanel titulo-recibo-gradiante" id="P1">
        <header class="titulo-recibo sin-borde">
            <div class="m-toolbar">
                <div class="m-title">Nuevo Recibo </div>
                <div class="m-left">

                    <a href="javascript:void(0)" class="easyui-linkbutton  titulo-recibo" data-options="plain:true" onclick="salida();"><i class="fas fa-sign-out-alt fa-lg fa-fw"></i> Salir </a>

                </div>
            </div>



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

        </header>

        <div class="contenedor-medios-cobro" style="height:100%">

            <div>
                <input type="hidden" name="idCliente" id="idCliente" value="<?php echo  $clienteObj->Codigo; ?>">
                <input type="hidden" name="saldoCliente" id="saldoCliente" value="<?php echo  $clienteObj->saldo; ?>">
                <input type="hidden" name="idPcCliente" id="idPcCliente" value="<?php echo  $clienteObj->id_pc; ?>">
                <div>
                    <!--<input class="easyui-numberbox" type="number"  inputmode="numeric" pattern="[0-9]*" id="puntoVenta" min="0" max="99999" required="true" missingMessage="Debe completar el punto de vta recibo"  style="width:100%" data-options="label: 'Punto venta',labelPosition:'top'" >-->
                    <?php if (!empty($pvta)) : ?>
                        <select class="easyui-combobox" title="Punto de venta" lines="true" id="puntoVenta" data-options="panelMaxHeight:'100px',labelWidth:'120px',label:'Punto de Venta:'" style="width:99%">
                            <?php foreach ($pvta as $pv) : ?>
                                <?php if ($pv["id_punto_venta"] == $usuario->id_punto_venta) : ?>
                                    <option value="<?php echo $pv["id_punto_venta"] . "|" . $pv["nro_punto_venta"]."|".$pv["cont"]; ?>" selected><?php echo str_pad($pv["nro_punto_venta"], 4, "0", STR_PAD_LEFT); ?></option>
                                <?php else : ?>
                                    <option value="<?php echo $pv["id_punto_venta"] . "|" . $pv["nro_punto_venta"]."|".$pv["cont"]; ?>"><?php echo str_pad($pv["nro_punto_venta"], 4, "0", STR_PAD_LEFT); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <div>Usuario sin punto de venta asigando, no puede continuar.</div>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="titulo-medios-cobro"> Tipo de recibo </h3>

                    <div class="m-buttongroup">

                        <a href="javascript:void(0)" class="easyui-linkbutton" id="sistema" data-options="toggle:true,group:'g1',selected:true" onclick="hide1('#items')" style="width:120px;height:30px"><i class="fa fa-check fa-fw fa-lg"></i>Sistema</a>
                        <a href="javascript:void(0)" class="easyui-linkbutton" id="talonario" data-options="toggle:true,group:'g1'" onclick="show1('#items')" style="width:120px;height:30px">Talonario</a>

                    </div>

                    <div id="items" style="padding:10px 10px;">
                        <!--<div style="margin-left: 2%;margin-bottom:10px; float:left;width:99%">-->
                        <input class="easyui-numberbox" type="text" inputmode="numeric" pattern="[0-9]*" id="nroTalonario" min="0" max="99999999" missingMessage="Debe completar numero de recibo" placeholder="nro recibo" style="width:90%;" data-options="label: 'Número:'">
                        <!--</div>-->


                    </div>

                </div>






            </div>
            
        </div>
        <footer>
                <?php if (!empty($pvta)) : ?>
                    <div style="padding:3px">
                        <a href="javascript:void(0)" id="crearRecibo" class="easyui-linkbutton primario" style="width:100%" onclick="crear_recibo()"> Siguiente <i class="fas fa-chevron-right fa-fw fa-lg"></i></a></p>
                    </div>
                <?php endif; ?>
            </footer>
        <div id="spinner" class="spinner" style="display:none;">
            <div class="centro">
                <img src="../_img/logo-administranet-ecommerce.png">
                <div class="texto"><i class="fas fa-circle-notch fa-spin"></i> Procesando...</div>
            </div>
        </div>

    </div>
</body>
<script>
    $(document).ready(function() {

        $('#items').hide();
        <?php if ($errorTemporal != 0) : ?>
            Swal.fire({
                title: 'Oop, algo pasó',
                icon: 'error',
                html: '<?php echo $motivoError; ?>',
                confirmButtonText: 'Aceptar',

            }).then((result) => {

                location.replace('../listado-clientes.php');

            });
        <?php endif; ?>


    });

    // botones de opcion sistema o talonario.

    $('a.easyui-linkbutton').on('click', function() {
        //console.log('click en el boton',$(this).linkbutton('options'));
        $('a.easyui-linkbutton').each(function() {

            let misOpciones = $(this).linkbutton('options');
            if (misOpciones.group == 'g1') {
                let textito = misOpciones.id;
                if (misOpciones.selected == true) {
                    // me sacan el selected
                    $(this).linkbutton({
                        'text': textito.charAt(0).toUpperCase() + textito.slice(1)
                    });
                    //$(this).linkbutton({'text':'<i class="fa fa-check fa-fw fa-lg"></i> '+});
                }
                if (misOpciones.selected == false) {
                    // soy el proximo checkbox
                    $(this).linkbutton({
                        'text': '<i class="fa fa-check fa-fw fa-lg"></i> ' + textito.charAt(0).toUpperCase() + textito.slice(1)
                    });
                }
            }
        });
        // si tngo g1 y estoy selected, me voy a deselecccionar.
    });

    function show1(id) {
        //$('div.m-item').hide();
        $(id).show();
    }

    function hide1(id) {
        $(id).hide();
    }

    function crear_recibo() {
        //$('#spinner').show();
        var botonCrearRecibo = $('#botonCrearRecibo');
        botonCrearRecibo.linkbutton({
            'text': '<i class="fas fa-circle-notch fa-spin"></i> Espere...'
        });
        botonCrearRecibo.attr('disabled', true);
        var tipoRecibo = "sistema";
        var nroPventaT = "";
        var nroReciboT = "";
        var idCliente = $('#idCliente').val();
        var saldoCliente = $('#saldoCliente').val();
        var idPcCliente = $('#idPcCliente').val();
        var error = 0;
        // que hacer si es x sistema o talonario
        $('a.easyui-linkbutton').each(function() {
            var opts = $(this).linkbutton('options');

            if (opts.selected && opts.group == "g1") {
                console.log('crear recibo que opcion elegi', opts);
                console.log({
                    opts
                });
                if (opts.id == "talonario") {
                    tipoRecibo = "Talonario";
                    // validar que no puede quedar vacio el talionario y numero
                    var pv = $('#puntoVenta'),
                        nro = $('#nroTalonario');
                    if (pv.val() === '') {
                        //pv.numberbox('clear').numberbox('textbox').focus();
                        error++;


                    } else {
                        nroPventaT = pv.combobox('getValue');
                    }

                    if (nro.val() === '') {
                        nro.numberbox('clear').numberbox('textbox').focus();
                        error++;
                    } else {
                        nroReciboT = nro.val();
                    }
                    //console.log(pv.val()=='');
                    console.log(nro.val() == '');

                }
                if (opts.id == "sistema") {
                    var pv = $('#puntoVenta');

                    if (pv.val() === '') {
                        //pv.numberbox('clear').numberbox('textbox').focus();
                        error++;


                    } else {
                        nroPventaT = pv.combobox('getValue');;
                    }
                }
            }

        });

        // ver si es recibo a cuenta o imputacion
        var claseRecibo = "imputacion";
        /*
        $('a.easyui-linkbutton.cRecibo').each(function(){
            var opts = $(this).linkbutton('options');
            
            if (opts.selected){
                console.log($(this).prop('id'));
                claseRecibo=$(this).prop('id');
                
            }
        });
        */
        // inicio del recibo consultar si esta seguro de seguir.

        if (error == 0) {
            /// preguntar si continua y ahi seguir
            var nomCliente = $('#nombreCliente').text();
            var nomCliente = '<?php echo $clienteObj->cliente; ?>';
            var mensajeFin = "Recibo para: <strong>" + nomCliente + "</strong>";
            mensajeFin += " <br>con saldo: <strong>$" + saldoCliente + "</strong>.";
            //var divMensaje=document.createElement('div');
            //    divMensaje.innerHTML = mensajeFin;
            Swal.fire({
                title: '¿Continuamos?',
                html: mensajeFin,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#395aa2',
                cancelButtonColor: '#cddfff',
                cancelButtonText: '<i class="fas fa-times"></i> No',
                position: 'top',

                confirmButtonText: '<i class="fas fa-check"></i> Si !'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('acepte hacer el recibio---');
                    console.log("todo ok");
                    $.ajax({
                        type: 'GET',
                        url: 'ajax/json_recibo.php',
                        data: {
                            altaRecibo: 1,
                            cliente: idCliente,
                            saldoCliente: saldoCliente,
                            idPcCliente: idPcCliente,
                            tipoNro: tipoRecibo,
                            nroPv: nroPventaT,
                            nroRec: nroReciboT
                        },
                        dataType: 'json',
                        beforeSend: function() {
                            $('#spinner').show();
                        },
                        success: function(data) {
                            console.log([data]);
                            console.log(claseRecibo);
                            if (data.msg === "ok") {
                                if (claseRecibo == "aCuenta") {
                                    $('#nroRecibo').text('Rec: ' + data.numero);
                                    $.mobile.go('#mCuenta');
                                }
                                if (claseRecibo == "imputacion") {
                                    location.href = "alta_recibo_imputacion.php";
                                }
                            }

                            if (data.msg == 'error') {
                                Swal.fire("Ooops!", data.desc, "warning");
                            }

                        },
                        complete: function() {
                            $('#spinner').hide();
                        }
                    });
                }

                if (result.isCanceled) {
                    $('#spinner').hide();
                }
            });



        }

        if (error > 0) {
            console.error("todo mal");

            $('#spinner').hide();
        }

        botonCrearRecibo.linkbutton({
            'text': 'Siguiente <i class="fas fa-chevron-right fa-fw fa-lg"></i>'
        });
        botonCrearRecibo.attr('disabled', false);
    }

    // no hya plata a cuenta hay que hacer medios de cobro 
    // directamente
    function salida() {
        // $('#mGeneralEf').menu('hide', true);
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
                            console.log({
                                data
                            });

                            if (data.msg === "ok") {
                                console.log({
                                    data
                                });

                                location.replace('../listado-clientes.php');


                            }
                            // error
                            if (data.msg === "error") {
                                // colocar en el titulo que no se pudo cargar el recibo.
                                // poner el mensaje de error y anular el boton de nuevo recibo
                                // pedir solo salir.
                                // alert('hubo un error');
                                location.replace('../listado-clientes.php');


                            }
                        }
                    });
                }
            });
    }
</script>

</html>