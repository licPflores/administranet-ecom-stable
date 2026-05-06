<?php
//error_reporting('E_ALL');
require_once 'sesion.inc.php';
//echo "<pre>";
//print_r($barra);
//echo "</pre>";
/**
 * variables de configuracion para colocar los encabezados
 */
$uTablas    = 0;
$uModal     = 1;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom = 0;
/**
 * elimino el carrito 
 **/
unset($_SESSION['jcart']);
if (isset($_GET["modifica"])) {
    $modifica = "si";
    $idCliente = $_GET["id"];
} else {
    $modifica = null;
    $idCliente = null;
}

//echo "<pre>";
//
//print_r($_SESSION);
//echo "</pre>";
//echo "<pre>";
//echo $formulario;
//echo $uFormulario;
//echo "</pre>";
// lista de precio por defecto quiero el array
$arrListasDePrecio = array();
if (isset($_SESSION["arr_lista_precio"]) && !empty($_SESSION["arr_lista_precio"])) {
    $arrListasDePrecio =  $_SESSION["arr_lista_precio"];
}

// echo '<pre>',print_r($arrListasDePrecio),'</pre>';
/*
 * Permiso para filtrar campos rapidos de cliente
 * si tengo permiso de modificar datos que sea solo de contacto
 * oculto los otros campos
 */
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>Nuevo Cliente | administraNET e-com </title>
    <?php require_once 'cabecera.php'; ?>
    <link rel='stylesheet' type='text/css' media='screen' href='_css/basic.css' />

    <script type='text/javascript' src='_lib/jquery.simplemodal.js'></script>
    <script>
        // agregar codigo jquery para visualizar las combos y hacer consulta via ajax. 
        // ver si se puede colocar el calendario...pero sabemos termina siendo engorroso
        /*
         * Modal 
         * @param {type} cuit
         * @returns {Boolean}
         */
        $.extend($.modal.defaults, {
            onClose: function() {
                $.modal.close();
                location.href = "listado-clientes.php";
            }
        });

        //$(document).ajaxStart(function(){
        //    $("#spinner").show();
        //});
        //$(document).ajaxComplete(function(){
        //  $("#spinner").hide();
        //});
        //$(document).ajaxError(function(){
        //    $("#spinner").hide();
        //});

        $(document).ready(function() {


            // * control de cuit de formato interno ya viene con el formato 00-00000000-0
            const validaCuit = function(cuit) {
                var status = 0;
                // valido el largo del cuit
                if (cuit.length !== 13) {
                    status++;
                    //console.log("mas largo :=>"+cuit.length);
                }
                //valido los primeros dos items
                //con este array me traigo los tres valores del cuite
                var arrCuit = cuit.split("-");
                var primera = arrCuit[0],
                    segunda = arrCuit[1],
                    tercera = arrCuit[2];
                //console.log("primera:=>"+primera+" segunda:=>"+segunda+" tecera:=>"+tercera);
                //valido primera parte
                var code = parseInt(primera, 10);
                var validTypes = [20, 23, 24, 27, 30, 33, 34];
                if (validTypes.indexOf(code) < 0) {
                    status++;
                    //console.log("no encontre el code:=>"+code+" resultado busqueda:=>"+ validTypes.indexOf(code));
                }
                //valida el checksum

                var sCUIT = String(primera + segunda + tercera);
                //console.log("sCUIT:=> "+sCUIT);
                var aCUIT = sCUIT.split('');

                var aMult = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
                var sum = 0;
                for (var i = 0; i <= 9; i++) {
                    sum += aCUIT[i] * aMult[i];
                }

                var diff = 11 - (sum % 11);
                var checksum = parseInt(aCUIT[10]);

                if (diff == 11) diff = 0; // do not consider diff == 10

                if (diff !== checksum) {
                    status++;

                }
                //console.log("status:=>"+status);
                return (status === 0);


            };

            // * solo valido que venga con el formato 00-000000000-0 
            const validaCuitFormato = function(cuit) {
                // Expresión regular para validar el formato 00-00000000-0
                const regex = /^[0-9]{2}-[0-9]{8}-[0-9]$/;
                return regex.test(cuit);
            }

            function resetFormulario() {

                var tipoC = $('#tipoCliente'),
                    nombreC = $('#nombreCliente'),
                    calleC = $('#calleCliente'),
                    numeroC = $('#numeroCliente'),
                    deptoC = $('#deptoCliente'),
                    provC = $('#provinciaCliente'),
                    departC = $('#departamentCliente'),
                    distritoC = $('#distritoCliente'),
                    telefonoC = $('#telefonoCliente'),
                    emailC = $('#emailCliente'),
                    faxC = $('#faxCliente'),
                    tipoIvaC = $('#ivaCliente'),
                    tipoDocC = $('#tipoDocCliente'),
                    nroDocC = $('#nroDocCliente'),
                    nroCuitC = $('#nroCuitCliente'),
                    listaPrecio = $('#listaPrecio');


                tipoC.val("");
                nombreC.val("");
                calleC.val("");
                numeroC.val("");
                deptoC.val("");
                provC.val("");
                departC.val("");
                distritoC.val("");
                telefonoC.val("");
                emailC.val("");
                faxC.val("");
                tipoIvaC.val("");
                tipoDocC.val("");
                nroDocC.val("0");
                nroCuitC.val("00-00000000-0");
                listaPrecio.val("");

            }
            var validarForm = function() {
                //validamos el formulario que no este vacio.
                var error = 0,
                    textoError = "";
                var divModal = $('.cartelCliente');
                // validar que no exista el cliente
                var tipoC = $('#tipoCliente'),
                    nombreC = $('#nombreCliente'),
                    calleC = $('#calleCliente'),
                    numeroC = $('#numeroCliente'),
                    provC = $('#provinciaCliente'),
                    departC = $('#departamentCliente'),
                    distritoC = $('#distritoCliente'),
                    telefonoC = $('#telefonoCliente'),
                    emailC = $('#emailCliente'),
                    tipoIvaC = $('#ivaCliente'),
                    tipoDocC = $('#tipoDocCliente'),
                    nroDocC = $('#nroDocCliente'),
                    listaPrecio = $('#listaPrecio'),
                    nroCuitC = $('#nroCuitCliente');

                textoError += '<div id="alertas-formulario" class="alerta-error">';
                textoError += '<strong>';
                textoError += '<i class="fa fa-warning"></i> Atención! </strong><br>';


                //$('#basic-modal-content').modal();
                if (tipoC.val() === "") {
                    //tipoC.focus();

                    textoError += '<span class="texto-alerta">Debe seleccionar <u>Tipo de Cliente</u></span><br>';
                    error++;
                }
                if (nombreC.val() === "") {
                    //nombreC.focus();
                    textoError += '<span class="texto-alerta">Debe Completar el <u>nombre de Cliente</u></span><br>';
                    error++;
                }
                if (calleC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe Completar la <u>calle del domicilio</u></span><br>';
                    error++;
                }
                if (numeroC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe Completar el <u>número de calle del domicilio</u></span><br>';
                    error++;
                }
                if (provC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe seleccionar la <u>provincia</u></span><br>';
                    error++;
                }
                if (departC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe seleccionar el <u>departamento</u></span><br>';
                    error++;
                }
                if (distritoC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe seleccionar el <u>distrito</u></span><br>';
                    error++;
                }

                // if(emailC.val()===""){
                //     //tipoC.focus();
                //     textoError +='<span class="texto-alerta">Debe Completar el <u>E-mail</u></span><br>';
                //     error++;
                // }

                if (telefonoC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe Completar el <u>telefono o movil</u></span><br>';
                    error++;
                }
                if (tipoIvaC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe seleccionar el <u>Tipo de IVA</u></span><br>';
                    error++;
                }
                if (tipoDocC.val() === "") {
                    textoError += '<span class="texto-alerta">Debe seleccionar el <u>Tipo de documento</u></span><br>';
                    error++;
                }

                if (tipoDocC.val() !== "CUIT" && nroDocC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe Completar el <u>número de documento</u>';
                    textoError += '<br>utilice CERO(0) si no lo conoce</span>';
                    error++;
                }
                // valido que el cuit no venga vacio    
                if (tipoDocC.val() === "CUIT" && nroCuitC.val() === "") {
                    //tipoC.focus();
                    textoError += '<span class="texto-alerta">Debe Completar el <u>CUIT</u>';
                    textoError += '<br>coloque 00-00000000-0 si desconoce</span><br>';
                    error++;
                }

                // valido que el cuit tenga los formatos necesarios. 
                if (tipoDocC.val() === "CUIT" && nroCuitC.val() !== "") {
                    let errorFormatoCuit = 0;
                    let elCuit = nroCuitC.val();

                    if (!validaCuitFormato(elCuit)) {
                        console.log('fallo el formato del cuit no vino como queria.')
                        errorFormatoCuit++;
                    }
                    if (!validaCuit(elCuit)) {
                        console.log('fallo la composicion del cuit no valido');
                        errorFormatoCuit++;
                    }
                    console.log('estado de error del cuit>', errorFormatoCuit);

                    if (errorFormatoCuit != 0) {
                        console.log('fallo el cuit>', errorFormatoCuit);
                        textoError += '<span class="texto-alerta"<u>CUIT</u> con Formato Inválido';
                        textoError += '<br>coloque 00-00000000-0 si desconoce</span><br>';
                        error++;
                    }

                }


                if (listaPrecio.val() === "") {
                    textoError += '<span class="texto-alerta">Debe seleccionar una <u>Lista de precios</u></span><br>';
                    error++;
                }

                if (error > 0) {
                    textoError += '</div>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Advertencia',
                        html: textoError


                    });
                    return false;
                } else {
                    //terminar la validacion agregar la validacion del cuit.
                    return true;
                }
            };

            //console.log("valida cuist:=>"+ validaCuit('20-27369904-3'));
            //console.log('formato de cuit',validaCuitFormato('00-000000-000'))  ;     

            $('#formCliente').on('submit', function() {
                event.preventDefault();
            });

            $('#altaClienteR').on("click", function() {
                let boton = $('#altaClienteR');
                let textoBoton = '<i class="fas fa-check fa-fw fa-lg"></i> Guardar'
                let textoEspere = '<i class="fa-solid fa-circle-notch fa-spin"></i> Espere...';
                boton.attr('disabled', true);
                boton.html(textoEspere);
                if (validarForm() === true) {
                    // todo bien con el formulario envio datos 
                    //                   var form=$('#formCliente').serializeArray();
                    var data = $('#formCliente').serializeArray();
                    data.push({
                        name: 'accion',
                        value: 'altaCliente'
                    });
                    //console.log("formulario:=> "+form);
                    $.ajax({
                        type: "POST",
                        url: "relay-cliente-rapido.php",
                        data,
                        beforeSend: function() {

                        },
                        success: function(response) {
                            //                            console.log("resultado no:?? "+response);
                            // console.log("fallo "+response.cartel);
                            boton.attr('disabled', false);
                            boton.html(textoBoton);
                            var a = jQuery.parseJSON(response);
                            var cartelito = "";
                            console.log([a]);
                            if (a.status === "error") {
                                //alert("todo
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    html: '<span class="texto-alerta">' + a.cartel + '</span>'

                                });


                            } else {

                                Swal.fire({
                                    position: 'top',
                                    icon: 'success',
                                    title: 'Muy bien!',
                                    html: a.cartel

                                }).then((result) => {
                                    if (result.value) {
                                        resetFormulario();
                                        window.location.href = "listado-clientes.php";
                                    }
                                });


                            }


                        },
                        complete: function() {
                            boton.attr('disabled', false);
                            boton.html(textoBoton);
                        }
                    });
                    //                   
                    //form.submit();
                }
            });


            //activar y desactivar el boton de busqueda rapida.

            $('#nroCuitCliente').hide();

            /* Provincia */
            var mando = {
                "accion": "inicio"
            };
            $.getJSON("relay-cliente-rapido.php", mando, function(data) {
                //                console.log("vuelta:"+JSON.toString(data));

                var items = [];
                var tCliente = $("#tipoCliente"),
                    tIva = $("#ivaCliente"),
                    tProvincia = $("#provinciaCliente");
                /*tipocliente*/
                $.each(data.tipoCliente, function(key, val) {
                    //console.log("val=>:"+$.json.values(val)); 
                    items.push("<option value='" + key + "'>" + val + "</option>");
                });
                var lista = items.join("");
                //console.log(lista);
                tCliente.append(lista);
                items = [];
                /* tipo de iva*/

                $.each(data.ivaCliente, function(key, val) {
                    //console.log("val=>:"+$.json.values(val)); 
                    items.push("<option value='" + key + "'>" + val + "</option>");
                });
                var lista = items.join("");
                //console.log(lista);
                tIva.append(lista);
                items = [];
                /* provincia*/
                items.push("<option value=''>- Provincia -</option>");
                $.each(data.provincia, function(key, val) {
                    //console.log("val=>:"+$.json.values(val)); 
                    items.push("<option value='" + key + "'>" + val + "</option>");
                });
                var lista = items.join("");
                //console.log(lista);
                tProvincia.append(lista);
            });

            /* cambio de provincia*/
            $("#provinciaCliente").on("change", function() {
                var idProvincia = $(this).val();

                if (idProvincia !== "") {
                    var mando = {
                        "accion": "departamento",
                        "idProvincia": idProvincia
                    };

                    $.getJSON("relay-cliente-rapido.php", mando, function(data) {
                        //console.log("vuelta:"+JSON.toString(data));

                        var items = [];
                        var tDepto = $("#departamentoCliente");
                        /*departamento del cliente*/
                        items.push("<option value=''>- Departamento -</option>");
                        $.each(data, function(key, val) {
                            //console.log("val=>:"+$.json.values(val)); 
                            items.push("<option value='" + key + "'>" + val + "</option>");
                        });
                        var lista = items.join("");
                        //console.log(lista);
                        tDepto.html("");
                        tDepto.append(lista);
                    });

                } else {
                    console.log("error: Debe seleccionar una provincia.");
                }
            });

            // cambio de depto traigo distritos.
            $("#departamentoCliente").on("change", function() {
                var idDepartamento = $(this).val();

                if (idDepartamento !== "") {
                    var mando = {
                        "accion": "distrito",
                        "idDepartamento": idDepartamento
                    };

                    $.getJSON("relay-cliente-rapido.php", mando, function(data) {
                        //console.log("vuelta:"+JSON.toString(data));

                        var items = [];
                        var tDist = $("#distritoCliente");
                        /*departamento del cliente*/
                        items.push("<option value=''>- Departamento -</option>");
                        $.each(data, function(key, val) {
                            //console.log("val=>:"+$.json.values(val)); 
                            items.push("<option value='" + key + "'>" + val + "</option>");
                        });
                        var lista = items.join("");
                        //console.log(lista);
                        tDist.html("");
                        tDist.append(lista);
                    });

                } else {
                    console.log("error: Debe seleccionar una Departamento.");
                }
            });

            /* cambio de dni*/
            $("#tipoDocCliente").on("change", function() {
                var td = $(this).val();
                var dni = $("#nroDocCliente"),
                    cuit = $("#nroCuitCliente");
                if (td === "CUIT") {
                    dni.val("");
                    cuit.show();
                    cuit.focus();
                    dni.hide();
                } else {
                    cuit.attr('placeholder', "00-00000000-0");
                    cuit.hide();
                    dni.show();
                    dni.focus();
                }
            });
            $("#botonVolver").on("click", function() {
                //alert("me fui!");
                location.href = "listado-clientes.php";
            });
            $('#tipoCliente').focus();
            $("#botonCancelar").on("click", function() {
                //alert("me fui!");
                location.href = "listado-clientes.php";
            });
            $('#tipoCliente').focus();

        });
    </script>
</head>

<body>
    <div id="wrapper">
        <?php require_once $barra; ?>
        <div class="cartelCliente" id="cartelNuevo"></div>

        <div id="content">
            <div class="divFormularios">


                <div id="titulo" class="formulario">

                    <h1> Nuevo Cliente </h1>

                </div>
                <div id="cuerpo" class="cuerpo-formulario">
                    <form method="post" action="" id="formCliente" name="formCliente">



                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="tipoCliente">
                                    Tipo<em>*</em></label>
                                <select name="tipoCliente" id="tipoCliente" tabindex="1">
                                    <option value="">- tipo de cliente -</option>
                                </select>
                            </div>
                            <div class="bloque-renglon">
                                <label for="nombreCliente">Nombre<em>*</em></label>
                                <input type="text" id="nombreCliente" name="nombreCliente" placeholder="nombre y apellido o razon social..." tabindex="2">
                            </div>
                        </div>

                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="calleCliente">Calle<em>*</em></label>
                                    <input type="text" id="calleCliente" name="calleCliente" placeholder="calle..." tabindex="3">
                                
                            </div>
                            <div class="bloque-renglon">
                                <label for="numeroCliente">Nro<em>*</em></label>
                                    <input type="text" id="numeroCliente" name="numeroCliente" placeholder="numero de calle..." tabindex="4">
                                
                            </div>
                            <div class="bloque-renglon">
                                <label for="deptoCliente">Depto</label>
                                    <input type="text" id="deptoCliente" name="deptoCliente" placeholder="numero o letra departamento..." tabindex="5">
                                
                            </div>
                        </div>

                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="provinciaCliente">Provincia<em>*</em></label>
                                    <select name="provinciaCliente" id="provinciaCliente" tabindex="6">
                                    </select>
                                
                            </div>
                        </div>

                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="departamentoCliente">Depto/Localidad<em>*</em></label>
                                    <select name="departamentoCliente" id="departamentoCliente" tabindex="7">
                                    </select>
                                
                            </div>
                            <div class="bloque-renglon">
                                <label for="distritoCliente">Distrito/Partido<em>*</em></label>
                                    <select name="distritoCliente" id="distritoCliente" tabindex="8">
                                    </select>
                                
                            </div>
                        </div>
                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="telefonoCliente">Telefono<em>*</em></label>
                                    <input type="tel" id="telefonoCliente" name="telefonoCliente" placeholder="telefono - movil..." tabindex="9">
                                
                            </div>
                            <div class="bloque-renglon">
                                <label for="emailCliente">E-mail</label>
                                    <input type="email" id="emailCliente" name="emailCliente" placeholder="e-mail..." tabindex="10">
                                
                            </div>
                        </div>

                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="faxCliente">Fax</label>
                                    <input type="text" id="faxCliente" name="faxCliente" placeholder="fax..." tabindex="11">
                                
                            </div>
                            <div class="bloque-renglon">
                                <label for="ivaCliente">IVA<em>*</em></label>
                                    <select name="ivaCliente" id="ivaCliente" tabindex="12">
                                        <option value="">- tipo de IVA -</option>
                                    </select>
                                
                            </div>
                        </div>

                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="tipoDocCliente">Tipo Doc<em>*</em></label>
                                    <select name="tipoDocCliente" id="tipoDocCliente" tabindex="13">
                                        <option value="">- tipo documento -</option>
                                        <option value="CUIT">CUIT</option>
                                        <option value="DNI">DNI</option>
                                        <option value="LE">LE</option>
                                        <option value="LC">LC</option>
                                        <option value="CIE">CIE</option>
                                        <option value="PAS">PAS</option>
                                        <option value="NOID">NOID</option>
                                    </select>
                                
                            </div>
                            <div class="bloque-renglon">
                                <label for="nroDocCliente">Nro<em>*</em></label>
                                    <input type="number" id="nroDocCliente" name="nroDocCliente" placeholder="documento de identidad" tabindex="14">
                                    <input type="text" id="nroCuitCliente" name="nroCuitCliente" placeholder="cuit 00-0000000-0" size="13" tabindex="15">

                                
                            </div>
                        </div>

                        <div class="renglonForm">
                            <div class="bloque-renglon">
                                <label for="listaPrecio">Lista de precio<em>*</em></label>
                                    <select name="listaPrecio" id="listaPrecio" tabindex="16">
                                        <option value="">- lista de precio -</option>
                                        <?php
                                        foreach ($arrListasDePrecio as $lista) {
                                            $option = '<option ';
                                            $option .= ' value="' . $lista["texto"] . '" ';
                                            if ($lista["defecto"] == "si") {
                                                $option .= ' selected="selected" ';
                                            }
                                            $option .= '>';
                                            $option .= $lista["texto"] . ' - ' . $lista["nombre"];
                                            $option .= '</option>' . PHP_EOL;
                                            echo $option;
                                        }

                                        ?>


                                    </select>
                                
                            </div>
                        </div>

                        <div class="renglonForm renglonBotones" style="text-align: center; width: 97%; padding:1%;">

                            <button id="botonVolver" class="botonNuevo grande botonVolver" type="button">
                                <i class="fas fa-arrow-left fa-fw fa-lg"></i> Volver
                            </button>


                            <button id="botonCancelar" class="botonNuevo grande botonCancelar" type="button" tabindex="16">
                                <i class="fas fa-times-circle fa-fw fa-lg"></i> Cancelar
                            </button>

                            <button class="botonNuevo grande azul" type="button" id="altaClienteR" tabindex="17"><i class="fas fa-check fa-fw fa-lg"></i> Guardar</button>
                        </div>

                    </form>
                </div>


                <div id="spinner" class="spinner" style="display:none;">
                    <img src="_img/logo-administranet-ecommerce.png" />
                </div>


            </div>




        </div>
        <?php require_once 'footer.php'; ?>

</body>

</html>