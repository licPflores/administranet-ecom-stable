<?php
// # INICIO DE PARAMETROS
//  =============================================================================

require_once 'sesion.inc.php';
include_once '_scripts/php/funciones.php';

# control que hay conexion.inc.php
if(!isset($connV)||!$connV){
    // sin conexion o session..
    header("Location: salida.inc.php");
    exit();
}



$uTablas    = 0;
$uModal     = 0;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom = 0;
$sinMail =0;
$codMovimiento=null;

$correoViajante = $_SESSION['correo'];


# control del mail de viajantes
    
if($correoViajante['nombre_usuario']==''||$correoViajante['pass_usuario']==''){
    header("Location: listado-clientes.php?cartel=7"); // error no possee email configurado el viajante
    
    exit();
}

# control que haya o post o get para hacer algo.
if(!isset($_POST)&&!isset($_GET['codigomovimiento'])){
    header('Location: listado-clientes.php?cartel=8&err=sin codigo get');// sin codigomovimiento.
    exit();
}

# POST Proceso envio de Email adjunto

if (isset($_POST['cualMail'])&&$_POST['cualMail']!='') {
    
    $mensaje = "";
    $codMovimiento = $_POST['codigoMovimientoPost'];
    $tipo=$_POST['tipoComprobantePost'];
    // recupero el comprobante
    $elComprobante = traer_info_comprobante($connV,$codMovimiento,$tipo);
    
    // no encontre el comprobante
    if(empty($elComprobante)){
        $urlVuelta = 'listado-clientes.php?cartel=8&error=Comprobante Vacio.';
        header('Location: ' . $urlVuelta);
        exit();
    }
    // echo "<pre>";
    // print_r($_POST);
    // echo "</pre>";
    // generar el pdf temporal a enviar. logo y generar ambos, luego on the fly

    # BUSCAR PDF 
    switch ($tipo) {
        case "PRE":
           

            break;
        case "PED":
            break;

        case "FA":
        case "FB":
        case "FC":
        case "FM":
        case "FE":
           
            break;
        case "REM":
           
            break;
        case "REC":
            $pdfComprobante = hacer_recibo_pdf($codMovimiento,$connV,'S');

            break;
        case "NCA":
        case "NCB":
        case "NCC":
        case "NCM":
        case "NCE":
            
            break;
    }


    

    $tipoComprobante= $elComprobante['TipoComprobante'];
    $numeroComprobante =$elComprobante['NroComprobante'];
    $totalComprobante = $elComprobante['totalComprobante'];
    $nombreCliente = $_POST['nombreClientePost'];
    $emailElegido = $_POST['cualMail'];
    $link=$tipoComprobante.'_'.$numeroComprobante;
    $vv = $_SESSION["vendedor"];
    $vendedor = $vv->apellido_usuario . " " . $vv->nombre_usuario;
    $urlComprobante =$tipoComprobante.'_'.$numeroComprobante.'.pdf';
    
    
    $empresa = array(
        "nombreempresa" => $_SESSION['nombre_empresa'],
        "telefonoempresa" => $_SESSION['telefono_empresa'],
        "cuitempresa" => $_SESSION['cuit_empresa'],
        "domicilioempresa" => $_SESSION['domicilio_empresa'],
        "emailempresa" => $_SESSION['email_empresa'],
        "urlempresa" => $_SESSION['direccion_web_empresa']
        
    );

    
    # array con datos para el Mail Final
    $param = array(
        "tipoComp" => $tipoComprobante,
        "comprobante" => $numeroComprobante,
        "total" => $totalComprobante,
        "emailCliente" => $emailElegido,
        "link" => $link,
        "fecha" => date('d/m/Y'),
        "cliente" => $nombreCliente,
        "vendedor" => $vendedor,
        "empresa" => $empresa,
        "correo" => $correoViajante,
        "urlComprobante"=>$urlComprobante    
        
    );

    
    # mandar el mail.
    $respuesta = enviar_mail_comprobante($connV,$param,$pdfComprobante);

    //var_dump($respuesta);    

    // exit();
    // mail enviado con EXITO
    if ($respuesta == 0) {
        switch ($tipoComprobante) {
            case "PRE":
                $urlVuelta = "lista-presupuestos-vendedor.php?cartel=6&pre=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);

                break;
            case "PED":
                break;

            case "FA":
            case "FB":
            case "FC":
            case "FM":
            case "FE":
                $urlVuelta = "lista_facturas_electronicas.php?cartel=6&fact=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REM":
                $urlVuelta = "lista-remitos.php?cartel=6&rem=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REC":
                //                $urlVuelta="lista-recibos.php?cartel=6&rec=".$comprobante;
                $urlVuelta = "listado-clientes.php?cartel=6&tipo=REC&comp=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "NCA":
            case "NCB":
            case "NCC":
            case "NCM":
            case "NCE":
                $urlVuelta = "lista_nota_credito.php?cartel=6&tipo=NC&comp=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
        }
    }
    // no se envio el mail x alguna razon.    
    if($respuesta!=0){
        //echo "No se pudo enviar correo";
        switch ($tipoComprobante) {
            case "PRE":
                $urlVuelta = "lista-presupuestos-vendedor.php?cartel=5&pre=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);

                break;
            case "PED":
                break;

            case "FA":
            case "FB":
            case "FC":
            case "FM":
            case "FE":
                $urlVuelta = "lista_facturas_electronicas.php?cartel=5&fact=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REM":
                $urlVuelta = "lista-remitos.php?cartel=5&rem=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REC":

                $urlVuelta = "listado-clientes.php?cartel=5&tipo=REC&comp=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "NCA":
            case "NCB":
            case "NCC":
            case "NCM":
            case "NCE":
                $urlVuelta = "lista_nota_credito.php?cartel=5&tipo=NC&comp=" . $numeroComprobante;
                header('Location: ' . $urlVuelta);
                break;
        }
    }
    // ya intente enviar el mail si hay error lo devuelvo.
    // pero debo eliminar la variable de sesion
    if (isset($_SESSION["datoMail"])) {
        unset($_SESSION["datoMail"]);
    }
}







# GET variables 
//if(isset($_GET['codigomovimiento'])){
if(isset($_GET['p'])){
    $arrGet = unserialize(base64url_decode($_GET['p']));
    //echo "BASE<pre>";
    ////echo base64url_decode($_GET['p']);
    //echo "<br>GET<pre>";
    //print_r($arrGet);
    //echo "</pre>";

    // $tipoComprobante = $_GET['tipocomprobante'];
    // $codigoMovimiento = $_GET['codigomovimiento']; 
    // $comprobante = $_GET['numerocomprobante'];
    // $codigoCliente = $_GET['codigo'];

    $tipoComprobante = $arrGet['tipocomprobante'];
    $codigoMovimiento = $arrGet['codigomovimiento']; 
    $comprobante = $arrGet['numerocomprobante'];
    $codigoCliente = $arrGet['codigo'];


    $arrCliente = datos_cliente_para_envio($connV,$codigoCliente);
    // error no encontre cliente
    if(empty($arrCliente)){
       // header('Location: listado-clientes.php?cartel=8&error=fallo consulta clientes');// sin fallo el cliente
        exit();
    }
    // datos de cliente
    if(!empty($arrCliente)){
   
        $emailContacto =$arrCliente['emailcontacto'];
        $emailCli =$arrCliente['email'];
        $emailUsuario =$arrCliente['emailUsuarioWeb'];
        $nombreCliente =$arrCliente['cliente'];

    }

    if (($emailCli == "" && $emailContacto == ""&& $emailUsuario == "") || ($emailCli == null && $emailContacto == null&& $emailUsuario == "")) {
        $sinMail++;
    }

    // no hay mail del cliente
    if ($sinMail == 1) {
        // echo "sin mail";
        header("Location: mod-cliente-rapido.php?modifica=si&id=" . $codigoCliente . "&vuelta=fincomprobante|".$codigoMovimiento."|".$tipoComprobante."|".$comprobante."|".$codigoCliente);
        exit();
    }

}





?>
    <!DOCTYPE HTML>
    <html>

    <head>
        <title>administraNET e-com | Comprobantes</title>
        <link href="favicon.ico" rel="shortcut icon" type="image/x-icon" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <?php require_once 'cabecera.php'; ?>
        <link rel='stylesheet' type='text/css' media='screen' href='_css/basic.css' />
        <script type='text/javascript' src='_lib/jquery.simplemodal.js'></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {

                $('#mandarCorreo').on("click", function() {
                    var mail = $("#cualMail").val();
                    var error = 0,
                        textoError = "";
                    var divModal = $('.cartelCliente');

                    if (mail !== "") {
                        $("#formCliente").submit();
                    } else {
                        //                    textoError+='<div id="alertas-formulario" class="alerta-error">';
                        //                    textoError+='<strong>';
                        //                    textoError+='<i class="fa fa-warning"></i> Atención! </strong><br>' ;
                        //
                        //                     textoError +='<span class="texto-alerta">Debe seleccionar <u>Un Mail</u></span><br>';
                        //                     textoError+='</div>';
                        //                     divModal.html(textoError);
                        //                     divModal.show();

                        Swal.fire("Atención!", "Debe seleccionar un E-mail.", "warning");

                    }
                });

                $('#botonVolver').on("click", function() {
                    location.href = "listado-clientes.php";
                });
                $('#cancelarMail').on("click", function() {
                    location.href = "listado-clientes.php";
                });
            });
        </script>
    </head>

    <body>
        <div id="wrapper">
            <?php //require_once $barra;
            ?>
            <div id="content">
                <div class="divFormularios">
                    <div id="titulo" class="formulario">
                        <i class="fa fa-chevron-circle-left fa-2x fa-lg floatLeft" id="botonVolver"> </i>
                        <div class="floatLeft" style="text-align: center;width:70%"><?php echo "<strong>" . $tipoComprobante . "</strong> " . $comprobante; ?></div>

                    </div>
                    <div class="cartelCliente" id="cartelNuevo"></div>
                    <form method="post" action="" id="formCliente" name="formCliente">


                        <div class="renglonForm">
                            <label for="cualMail">Seleccionar:
                                <select name="cualMail" id="cualMail">
                                    <option value="">- E Mail -</option>
                                    <?php if ($emailCli != "") : ?>
                                        <option value="<?php echo $emailCli; ?>">Cliente: <?php echo $emailCli; ?></option>
                                    <?php endif; ?>
                                    <?php if ($emailContacto != "") : ?>
                                        <option value="<?php echo $emailContacto; ?>">Contacto: <?php echo $emailContacto; ?></option>
                                    <?php endif; ?>
                                    <?php if ($emailUsuario != "") : ?>
                                        <option value="<?php echo $emailUsuario; ?>">Usuario: <?php echo $emailUsuario; ?></option>
                                    <?php endif; ?>
                                </select>
                            </label>
                        </div>
                        <div class="renglonForm">

                            <button type='button' id="cancelarMail" class="botonNuevo grande white"><i class="fas fa-times fa-2x fa-lg" title="Enviar Correo"></i> Cancelar </button>
                            <button type='button' id="mandarCorreo" class="botonNuevo grande azul"><i class="fa fa-envelope fa-2x fa-lg" title="Enviar Correo"></i> Enviar correo </button>
                            <input type="hidden" name="tipoComprobantePost" id="tipoComprobantePost" value="<?php echo $tipoComprobante;?>">
                            <input type="hidden" name="numeroComprobantePost" id="numeroComprobantePost" value="<?php echo $comprobante;?>">
                            <input type="hidden" name="codigoMovimientoPost" id="codigoMovimientoPost" value="<?php echo $codigoMovimiento;?>">
                            <input type="hidden" name="codigoClientePost" id="codigoClientePost" value="<?php echo $codigoCliente;?>">
                            <input type="hidden" name="nombreClientePost" id="nombreClientePost" value="<?php echo $nombreCliente;?>">

                        </div>

                    </form>
                </div>
            </div>
        </div>

    </body>
