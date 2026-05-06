<?php
//error_reporting('E_ALL');
require_once 'sesion.inc.php';
$permisoAltaCliente = $_SESSION["permiso_alta_cliente"];
// agregar un permiso de no edicion de nada
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";
/**
 * funciones php
 */

function devuelve_array($sql, $connV)
{

    $hacer = mysqli_query($connV, $sql) or die("error " . mysqli_error($connV) . " <pre>" . $sql . "</pre>");
    $vuelta = array();
    while ($r = mysqli_fetch_assoc($hacer)) {
        $vuelta[] = $r;
    }
    if (empty($vuelta)) {
        $vuelta = null;
    }
    return $vuelta;
}

function completa_select($arrValores, $idValue = null)
{
    $select = "";
    if (!empty($arrValores)) {
        foreach ($arrValores as $p) {
            if ($idValue && $idValue == $p["id"]) {
                $select .= '<option value="' . $p["id"] . '" selected="selected">' . $p["valor"] . '</option>';
            } else {
                $select .= '<option value="' . $p["id"] . '">' . $p["valor"] . '</option>';
            }
        }
    }
    return $select;
}



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
/*
 * Datos del cliente, provincia y todo lo demas.
 */
$idCliente = $_GET["id"];
$vuelta = "";
if (isset($_GET["vuelta"])) {
    $vuelta = $_GET["vuelta"];
}


$sqlCli = "SELECT * FROM cliente WHERE cliente.Codigo=" . $idCliente;
// datos del cliente
$hacer = mysqli_query($connV, $sqlCli) or die("error cliente:=> " . mysqli_error($connV) . " <pre>" . $sqlCli . "</pre>");
$cli = mysqli_fetch_assoc($hacer);

$sqlProvincia = "SELECT CodProvincia AS id,Provincia AS valor "
    . "FROM provincia "
    . "WHERE provincia.id_pais=1 "
    . "AND provincia.Anulado='No'";

$sqlDepartamento = "SELECT IDDepartamento AS id, NombreDepartamento AS valor "
    . "FROM departamento "
    . "WHERE departamento.Anulado='No'"
    . " AND departamento.CodProvincia=" . $cli["CodProvincia"];
$sqlDistrito = "SELECT IDDistrito AS id, NombreDistrito AS valor "
    . "FROM distrito "
    . "WHERE distrito.Anulado='No'"
    . " AND distrito.IDDepartamento=" . $cli["IDDepartamento"];

$sqlTipoCliente = "SELECT tipo_cliente.IDTipoCliente AS id, tipo_cliente.NombreTipoCliente as valor "
    . "FROM tipo_cliente "
    . "WHERE tipo_cliente.anulado='No'";

$sqlIva = "SELECT IDIva AS id, Abreviado AS valor "
    . "FROM contribuyentes ";


//echo "<pre>";
//print_r($cli);
//echo "</pre>";
$lista_prov = completa_select(devuelve_array($sqlProvincia, $connV), $cli["CodProvincia"]);
$lista_depto = completa_select(devuelve_array($sqlDepartamento, $connV), $cli["IDDepartamento"]);
$lista_dist = completa_select(devuelve_array($sqlDistrito, $connV), $cli["IDDistrito"]);
$lista_tcliente = completa_select(devuelve_array($sqlTipoCliente, $connV), $cli["TipoCliente"]);
$lista_iva = completa_select(devuelve_array($sqlIva, $connV), $cli["IDIva"]);
?>
<!DOCTYPE HTML>
<html>

<head>
    <title>Modificar cliente | administraNET e-com </title>
    <link rel='stylesheet' type='text/css' media='screen' href='_css/formulario.css' />
    <?php require_once 'cabecera.php'; ?>
    
    <script type='text/javascript' src='_lib/jquery.simplemodal.js'></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mailcheck/1.1.2/mailcheck.min.js"></script>
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

        $(document).ready(function() {

            // email check.
            // =============================================================================
            var domains = ['hotmail.com', 'gmail.com', 'aol.com'];
            var topLevelDomains = ["com", "net", "org", "com.ar"];
            var $email = $('#emailCliente');
            var $hint = $("#hint");

            $email.on('blur', function(event) {
                console.log("event ", event);
                console.log("this ", $(this));
                $hint.css('display', 'none').empty();
                $(this).mailcheck({
                    domains: domains, // optional
                    topLevelDomains: topLevelDomains, // optional
                    suggested: function(element, suggestion) {
                        // callback code
                        console.log("suggestion ", suggestion.full);
                        //$('#suggestion').html("quiso poner?<b><i>" + suggestion.full + "</b></i>?");
                        if (!$hint.html()) {
                            // First error - fill in/show entire hint element
                            var suggestion = "Ups! quiso decir <span class='suggestion'>" +
                                "<span class='address'>" + suggestion.address + "</span>" +
                                "@<a href='#' class='domain'>" + suggestion.domain +
                                "</a></span>?";

                            $hint.html(suggestion).fadeIn(150);
                        } else {
                            // Subsequent errors
                            $(".address").html(suggestion.address);
                            $(".domain").html(suggestion.domain);
                        }
                    },
                    empty: function(element) {
                        // callback code
                        $('#suggestion').html('Sin sugerencias :(');
                    }
                });
            });

            $hint.on('click', '.domain', function() {
                // On click, fill in the field with the suggestion and remove the hint
                console.log("click en hint");
                $email.val($(".suggestion").text());
                $hint.fadeOut(200, function() {
                    $(this).empty();
                });
                return false;
            });






            /* control de cuit.
             * ****************************************************************
             * */

            var validaCuit = function(cuit) {
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

                return (status === 0);

            };

            var validarForm = function() {
                //validamos el formulario que no este vacio.
                var error = 0,
                    textoError = "";
                var divModal = $('.cartelCliente');
                var todosCampos = $("#completo").val();
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
                    tipoIvaC = $('#tipoIvaCliente'),
                    tipoDocC = $('#tipoDocCliente'),
                    nroDocC = $('#nroDocCliente'),
                    nroCuitC = $('#nroCuitCliente');
                /*
                textoError += '<div id="alertas-formulario" class="alerta-error">';
                textoError += '<strong>';
                textoError += '<i class="fa fa-warning"></i> Atención! </strong><br>';
                */
                //alta reducida!
                if (todosCampos === "no") {
                    //$('#basic-modal-content').modal();

                    if (calleC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar <strong> la calle del domicilio</strong></span>';
                        error++;
                    }
                    if (numeroC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>número de calle del domicilio</strong></span>';
                        error++;
                    }
                    if (provC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar<strong> la provincia </strong></span>';
                        error++;
                    }
                    if (departC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar <strong>el departamento</strong></span>';
                        error++;
                    }
                    if (distritoC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar <strong>el distrito</strong></span>';
                        error++;
                    }
                    if (emailC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong> E-mail </strong></span>';
                        error++;
                    }
                    if (telefonoC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>telefono o movil</strong></span>';
                        error++;
                    }


                } else {
                    //$('#basic-modal-content').modal();
                    if (tipoC.val() === "") {
                        //tipoC.focus();

                        textoError += '<span class="texto-alerta">Debe seleccionar<strong> Tipo de Cliente</strong></span>';
                        error++;
                    }
                    if (nombreC.val() === "") {
                        //nombreC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>nombre de Cliente</strong></span>';
                        error++;
                    }
                    if (calleC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar <strong> la calle del domicilio</strong></span>';
                        error++;
                    }
                    if (numeroC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>número de calle del domicilio</strong></span>';
                        error++;
                    }
                    if (provC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar<strong> la provincia </strong></span>';
                        error++;
                    }
                    if (departC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar <strong>el departamento</strong></span>';
                        error++;
                    }
                    if (distritoC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar <strong>el distrito</strong></span>';
                        error++;
                    }
                    if (emailC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong> E-mail </strong></span>';
                        error++;
                    }
                    if (telefonoC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>telefono o movil</strong></span>';
                        error++;
                    }
                    if (tipoIvaC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe seleccionar el <strong>Tipo de IVA</strong></span>';
                        error++;
                    }
                    if (tipoDocC.val() === "") {
                        textoError += '<span class="texto-alerta">Debe seleccionar el <strong>Tipo de documento</strong></span>';
                        error++;
                    }

                    if (tipoDocC.val() !== "CUIT" && nroDocC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>número de documento</strong>';
                        textoError += '<br>utilice CERO(0) si no lo conoce</span>';
                        error++;
                    }
                    if (tipoDocC.val() === "CUIT" && nroCuitC.val() === "") {
                        //tipoC.focus();
                        textoError += '<span class="texto-alerta">Debe Completar el <strong>CUIT</strong>';
                        textoError += '<br>coloque 00-00000000-0 si desconoce</span>';
                        error++;
                    }
                }
                if (error > 0) {
                    /*
                    textoError += '</div>';

                    divModal.addClass("renglonFormLargo");
                    divModal.html(textoError);
                    divModal.show();
                    */
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        html: textoError
                        
                        });
                    return false;
                } else {
                    //terminar la validacion agregar la validacion del cuit.
                    return true;
                }
            };

            // # Edicion de Datos Rapido de cliente.
            $('#editarClienteR').on("click", function() {
                var divModal = $('.cartelCliente');

                if (validarForm() === true) {
                    // todo bien con el formulario envio datos 
                    //                   var form=$('#formCliente').serializeArray();
                    var data = $('#formCliente').serializeArray();
                    data.push({
                        name: 'accion',
                        value: 'editaCliente'
                    });
                    //console.log("formulario:=> "+form);
                    $.ajax({
                        type: "POST",
                        url: "relay-cliente-rapido.php",
                        data,
                        success: function(response) {
                            //console.log("resultado no:?? "+response);
                            console.log("revolvio?=", response);
                            var a = jQuery.parseJSON(response);
                            var cartelito = "";
                            divModal.addClass("renglonFormLargo");
                            //                            console.log("a:=>"+a.cartel);
                            if (a.estado === "error") {
                                /*
                                // error sin alerts
                                cartelito += '<div id="alertas-formulario" class="alerta-error">';
                                cartelito += '<strong>';
                                cartelito += '<i class="fa fa-warning"></i> Atención! </strong><br>';
                                cartelito += '<span class="texto-alerta">' + a.cartel + '</span>';
                                cartelito += '</div>';
                                divModal.html(cartelito);
                                divModal.show();
                                */
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    html: a.cartel                                    
                                    });

                                //$('#basic-modal-content').modal();
                            } else {
                                ///alert("tudo bien");
                                /*
                                cartelito += '<div id="alertas-formulario" class="alerta-exito">';
                                cartelito += '<strong>';
                                cartelito += '<i class="fa fa-check-circle fa-lg"></i> ';
                                cartelito += '<span class="texto-alerta"> Datos Editados con exito!</span>';
                                cartelito += '</strong>';

                                cartelito += '</div>';
                                divModal.html(cartelito);

                                divModal.show();*/
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Excelente!',
                                    text: 'Datos Editados con exito!'                                    
                                    });
                                //$('#basic-modal-content').modal();

                            }


                        }
                    });
                    //                   
                    //form.submit();
                }
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
                    dni.hide();
                } else {
                    cuit.attr('placeholder', "00-00000000-0");
                    cuit.hide();
                    dni.show();
                }
            });

            /*
             *   EDICION DE CLIENTe
             */
            $("#botonVolver").on("click", function() {
                //alert("me fui!");
                location.href = "listado-clientes.php";
            });

            $("#botonMail").on("click", function() {
                //alert("me fui!");

                // llamar a retomar los datos del cliente cambiados.
                var codigoCliente = $("#codCliente").val();
                //    $("#botonVolver").hide();    
                //                                        alert(codigoCliente);
                $.ajax({
                    type: 'post',
                    url: 'seleccionar-cliente.php',
                    data: {
                        "ajax": "true",
                        "codCliente": codigoCliente

                    },
                    success: function(response) {
                        console.log(response);
                        //                 $("#clienteOk").show();
                        location.href = "fin-comprobante.php";


                    }
                });


            });
            //test
            // var   tipoC=$('#tipoCliente');
            // console.log(tipoC.val());
        });
    </script>
</head>

<body>
    <div id="wrapper">

<?php 
            require_once $barra;
        ?>
      
        <div id="content">
            <div  class="headerFormulario">
                <h1 class="titulo">Editar Cliente</h1>
            </div>
            <div class="divFormulario">
            
                <div class="cartelCliente" id="cartelNuevo"></div>
                <form method="post" action="" id="formCliente" name="formCliente">
                    <?php if ($permisoAltaCliente == "No") : ?>
                        <!-- FORMULARIO CLIENTE CORTO-->
                        <div class="renglonFormularioCliente">
                            <label class="labels" for="telefonoCliente">Telefono<em>*</em></label>
                                <input class="inputs"  type="tel" id="telefonoCliente" name="telefonoCliente" value="<?php echo $cli["telefono"] ?>" placeholder="telefono - movil...">
                            
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="emailCliente">E-mail<em>*</em></label>
                                <input  class="inputs" type="email" id="emailCliente" name="emailCliente" value="<?php echo $cli["Email"] ?>" placeholder="E-mail...">
                           
                            <div id="hint"></div>
                        </div>

                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="calleCliente">Calle<em>*</em></label>
                                <input  class="inputs" type="text" id="calleCliente" name="calleCliente" value="<?php echo $cli["Calle"] ?>" placeholder="Calle...">
                            
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="numeroCliente">Nro<em>*</em></label>
                                <input  class="inputs" type="text" id="numeroCliente" name="numeroCliente" value="<?php echo $cli["NroCalle"] ?>" placeholder="Numero de calle...">
                           
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="deptoCliente">Depto<em>*</em></label>
                                <input  class="inputs" type="text" id="deptoCliente" name="deptoCliente" value="<?php echo $cli["Dpto"] ?>" placeholder="Numero o letra departamento...">
                           
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="provinciaCliente">Provincia<em>*</em></label>
                                <select class="selectCustomizado" name="provinciaCliente" id="provinciaCliente">
                                    <?php echo $lista_prov; ?>
                                </select>
                           
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="departamentoCliente">Depto/Localidad<em>*</em></label>
                                <select class="selectCustomizado" name="departamentoCliente" id="departamentoCliente">
                                    <?php echo $lista_depto;   ?>
                                </select>
                           
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="distrito">Distrito/Partido<em>*</em></label>
                                <select class="selectCustomizado" name="distritoCliente" id="distritoCliente">
                                    <?php echo $lista_dist; ?>
                                </select>
                         
                        </div>



                    <?php else : ?>
                        <!-- FORMULARIO CLIENTE COMPLETO-->
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="tipoCliente">
                                Tipo<em>*</em></label>
                                <select class="selectCustomizado" name="tipoCliente" id="tipoCliente">
                                    <option value="">- tipo de cliente -</option>
                                    <?php echo $lista_tcliente; ?>
                                </select>

                           
                        </div>

                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="nombreCliente">Nombre<em>*</em></label>
                                <input  class="inputs" type="text" id="nombreCliente" name="nombreCliente" <?php if ($permisoAltaCliente == "No") {
                                                                                                echo 'readonly="readonly"';
                                                                                            } ?> value="<?php echo $cli["nombre_cliente"] ?>" placeholder="Nombre de Cliente">
                          
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="calleCliente">Calle<em>*</em></label>
                                <input  class="inputs" type="text" id="calleCliente" name="calleCliente" value="<?php echo $cli["Calle"] ?>" placeholder="Calle...">
                           
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="numeroCliente">Nro<em>*</em></label>
                                <input  class="inputs" type="text" id="numeroCliente" name="numeroCliente" value="<?php echo $cli["NroCalle"] ?>" placeholder="Numero de calle...">
                           
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="deptoCliente">Depto<em>*</em></label>
                                <input  class="inputs" type="text" id="deptoCliente" name="deptoCliente" value="<?php echo $cli["Dpto"] ?>" placeholder="Numero o letra departamento...">
                           
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="provinciaCliente">Provincia<em>*</em></label>
                                <select class="selectCustomizado" name="provinciaCliente" id="provinciaCliente">
                                    <?php echo $lista_prov; ?>
                                </select>
                          
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="departamentoCliente">Depto/Localidad<em>*</em></label>
                                <select class="selectCustomizado" name="departamentoCliente" id="departamentoCliente">
                                    <?php echo $lista_depto;   ?>
                                </select>
                            
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="distrito">Distrito/Partido<em>*</em></label>
                                <select class="selectCustomizado" name="distritoCliente" id="distritoCliente">
                                    <?php echo $lista_dist; ?>
                                </select>
                            
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="telefonoCliente">Telefono<em>*</em></label>
                                <input class="inputs"  type="tel" id="telefonoCliente" name="telefonoCliente" value="<?php echo $cli["telefono"] ?>" placeholder="telefono - movil...">
                           
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="emailCliente">E-mail<em>*</em></label>
                                <input class="inputs"  type="email" id="emailCliente" name="emailCliente" value="<?php echo $cli["Email"] ?>" placeholder="E-mail...">
                           
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="faxCliente">Fax</label>
                                <input  class="inputs" type="text" id="faxCliente" name="faxCliente" value="<?php echo $cli["Fax"] ?>" placeholder="Fax...">
                            
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="ivaCliente">IVA<em>*</em></label>
                                <select class="selectCustomizado" name="ivaCliente" id="ivaCliente">
                                    <option value="">- tipo de IVA -</option>
                                    <?php echo $lista_iva; ?>
                                </select>
                            
                        </div>
                        <div class="renglonFormularioCliente divSelect">
                            <label  class="labels" for="tipoDocCliente">Tipo Doc<em>*</em></label>
                                <select class="selectCustomizado" name="tipoDocCliente" id="tipoDocCliente">
                                    <option value="">- tipo documento -</option>

                                    <option <?php if ($cli["tipo_doc"] == "CUIT") {
                                                echo 'selected="selected"';
                                            } ?> value="CUIT">CUIT</option>
                                    <option <?php if ($cli["tipo_doc"] == "DNI") {
                                                echo 'selected="selected"';
                                            } ?> value="DNI">DNI</option>
                                    <option <?php if ($cli["tipo_doc"] == "LE") {
                                                echo 'selected="selected"';
                                            } ?>value="LE">LE</option>
                                    <option <?php if ($cli["tipo_doc"] == "LC") {
                                                echo 'selected="selected"';
                                            } ?>value="LC">LC</option>
                                    <option <?php if ($cli["tipo_doc"] == "CIE") {
                                                echo 'selected="selected"';
                                            } ?>value="CIE">CIE</option>
                                    <option <?php if ($cli["tipo_doc"] == "PAS") {
                                                echo 'selected="selected"';
                                            } ?>value="PAS">PAS</option>
                                    <option <?php if ($cli["tipo_doc"] == "NOID") {
                                                echo 'selected="selected"';
                                            } ?>value="NOID">NOID</option>
                                </select>
                           
                        </div>
                        <div class="renglonFormularioCliente">
                            <label  class="labels" for="nroDocCliente">Nro<em>*</em></label>
                                <?php if ($cli["tipo_doc"] != "CUIT") : ?>
                                    <input class="inputs"  type="number" id="nroDocCliente" <?php if ($permisoAltaCliente == "No") {
                                                                                echo 'readonly="readonly"';
                                                                            } ?> name="nroDocCliente" value="<?php echo $cli["CUIT"]; ?>" placeholder="documento...">
                                    <input  class="inputs" type="text" id="nroCuitCliente" <?php if ($permisoAltaCliente == "No") {
                                                                                echo 'readonly="readonly"';
                                                                            } ?> name="nroCuitCliente" placeholder="00-0000000-0" size="13" style="display:none">

                                <?php else : ?>
                                    <input  class="inputs" type="number" id="nroDocCliente" <?php if ($permisoAltaCliente == "No") {
                                                                                echo 'readonly="readonly"';
                                                                            } ?> name="nroDocCliente" placeholder="documento..." style="display:none">
                                    <input  class="inputs" type="text" id="nroCuitCliente" <?php if ($permisoAltaCliente == "No") {
                                                                                echo 'readonly="readonly"';
                                                                            } ?> name="nroCuitCliente" value="<?php echo $cli["CUIT"]; ?>" placeholder="00-0000000-0" size="13">

                                <?php endif; ?>
                            
                        </div>
                    <?php endif; ?>

                    <input type="hidden" id="codCliente" name="codCliente" value="<?php echo $idCliente; ?>">
                    <input type="hidden" id="vuelta" name="vuelta" value="<?php echo $vuelta; ?>">
                    <input type="hidden" id="completo" name="completo" value="<?php echo $permisoAltaCliente ?>">

                </form>

            </div>


            <div id="spinner" class="spinner" style="display:none;">
                <img src="_img/logo-administranet-ecommerce.png" />
            </div>


                                                        
                <div class="botones">



                
                    <?php if ($vuelta == "") : ?>
                        
                    <button id="botonVolver" type="button" class="botonConfirmar">
                        <i class="fa fa-arrow-left fa-lg" aria-hidden="true"></i>
                        Volver
                    </button>
                        <!-- <i class="fa fa-chevron-circle-left fa-2x fa-lg floatLeft" id="botonVolver" title="volver a listado de clientes"> </i> -->
                    <?php else : ?>
                        
                     <button id="botonMail" type="button" class="botonConfirmar">
                        <i class="fa fa-envelope fa-2x fa-lg" aria-hidden="true"></i>
                        Enviar Mail
                    </button>
                        <!-- <i class="fa fa-envelope fa-2x fa-lg floatLeft" id="botonMail" title="Enviar Mail"> </i> -->
                    <?php endif; ?>
   







                    <button id="editarClienteR" type="button" class="botonConfirmar">
                        <i class="fa fa-check fa-lg" aria-hidden="true"></i>
                        Confirmar
                    </button>
                    
                    
                </div>
                    <!-- <i  class="fa fa-check-circle fa-2x fa-lg floatRight"></i> -->
            

        </div>
        <?php require_once 'footer.php';?>   
      
    </div>
   

</body>

</html>