<?php
// INICIO DE PARAMETROS
//  =============================================================================
# TODO: generar el pdf del recibo o cualquier comprobante y luego enviarlo como adjunto por email
# TODO: uso de variables GET para el codmov, numero , fecha y tipo de comprobante.
require_once 'sesion.inc.php';
require_once '_scripts/php/funciones.php';
$uTablas    = 0;
$uModal     = 0;
$uSlider    = 0;
$uGui       = 0;
$iconoDisabled = 1;
$usaZoom = 0;
//phpinfo();
// configuracion del mail
//use _lib\mail\PHPMailer\PHPMailer\PHPMailer;
//use _lib\mail\PHPMailer\PHPMailer\Exception; 
//require '_lib/mail/PHPMailer/PHPMailer/PHPMailer.php';
//require '_lib/mail/PHPMailer/PHPMailer/Exception.php';
//require '_lib/mail/PHPMailer/PHPMailer/SMTP.php';

/* funcion para hacer el envio del mail 
 * ============================================================================
 */
function trae_html($destinatario, $url, $tipoComp, $nroComp, $empresa, $fecha, $vendedor, $total)
{
    $rcss = "_lib/mail/p-comp.css"; //ruta de archivo css
    $fcss = fopen($rcss, "r"); //abrir archivo css
    $scss = fread($fcss, filesize($rcss)); //leer contenido de css
    fclose($fcss); //cerrar archivo css
    $txtHtml = '';
    $txtHtml .= '<!DOCTYPE html>';
    $txtHtml .= '<head>';
    $txtHtml .= '<meta charset="UTF-8">';
    $txtHtml .= '<style>' . $scss . '</style>';
    $txtHtml .= '    <title>Envio de comprobantes electrónicos</title>';
    $txtHtml .= '</head>';
    $txtHtml .= '<body>';
    $txtHtml .= '    <div id="contenedor">';
    $txtHtml .= '        <div id="cabecera">Envio electrónico de comprobante</div>';
    $txtHtml .= '        <div id="cuerpo">';
    $txtHtml .= '            <p>Fecha: ' . $fecha . '</p>';
    $txtHtml .= '            <p>' . $destinatario . '</p>';
    $txtHtml .= '            <p>Descargue su comprobante aquí <a alt="' . $url . '" title="' . $url . '" href="' . $url . '" target="blank">' . $tipoComp . ' ' . $nroComp . ' $' . number_format($total, 2, ",", ".") . '</a></p>';
    $txtHtml .= '        </div>';
    $txtHtml .= '        <div id="firma">';
    $txtHtml .= '            <div id="logoFirma">';
    //        $txtHtml .='                <img src="'.$empresa["logo"].'"/>';
    $txtHtml .= '                <img src="cid:logoempresa"/>';

    $txtHtml .= '            </div>';
    $txtHtml .= '            <div id="textoFirma">';
    $txtHtml .= '                <label><strong>' . $vendedor . '</strong></label><br>';
    $txtHtml .= '                <label>' . $empresa["nombreempresa"] . '</label><br>';
    $txtHtml .= '                <label>' . $empresa["domicilioempresa"] . '</label><br>';
    $txtHtml .= '                <label>Tel: ' . $empresa["telefonoempresa"] . '</label><br>';
    $txtHtml .= '                <label><a href="' . $empresa["urlempresa"] . '" target="_blank">' . $empresa["urlempresa"] . '</a></label>';
    $txtHtml .= '            </div>';
    $txtHtml .= '        </div>';
    $txtHtml .= '        <div id="pie">';
    $txtHtml .= '            Mail generado por  <a href="https://www.administranet.com.ar" target="_blank">administraNET gestión e-commerce</a> <img src="cid:logoadministranet"/>';
    $txtHtml .= '        </div>';
    $txtHtml .= '    </div>';
    $txtHtml .= '</body>';
    $txtHtml .= '</html>';
    return $txtHtml;
}

function foto64($fotologo64)
{


    // remove the part that we don't need from the provided image and decode it
    $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotologo64));
    $nn = explode(" ", microtime());
    $nombre = $nn[1];
    $filepath = "_img/logototal.png"; // or image.jpg

    // Save the image in a defined path
    file_put_contents($filepath, $data);
    return $filepath;
}


// proceso y envio el mail
//=========================================================================

function enviar_mail_comprobante($datos)
{
    require '_lib/mail/PHPMailerAutoload.php';

    //        echo "<pre>";
    //        var_dump($datos);
    //        echo "</pre>";
    $p = json_decode($datos, true);
    //        echo "<pre>";
    //        var_dump($p);
    //        echo "</pre>";

    //$p = json_decode(file_get_contents("php://input"),TRUE);
    /*RECOGER VALORES ENVIADOS DESDE INDEX.PHP*/

    //    $sDestino = $_POST['txtDestin'];
    //    $sAsunto = $_POST['txtAsunto'];
    //    $sMensaje = $_POST['txtMensa'];

    $url = $p["link"];
    $vendedor = $p["vendedor"];
    $empresa = $p["empresa"];
    $tipoComp = $p["tipoComp"];
    $nroComp = $p["comprobante"];
    $total = $p["total"];
    $fecha = $p["fecha"];
    $cliente = $p["cliente"];
    // pasar el codigo de movimiento.
    $mailvendedor = $p["correo"]["nombre_usuario"];
    $fotoEmpresa = foto64($empresa["logo"]);

    
    /*CONFIGURACIÓN DE CLASE*/
    $mail = new PHPMailer;
    try {
        $mail->isSMTP(); //Indicar que se usará SMTP
        $mail->CharSet = 'UTF-8'; //permitir envío de caracteres especiales (tildes y ñ)
        /*CONFIGURACIÓN DE DEBUG (DEPURACIÓN)*/
        $mail->SMTPDebug = 0; //Mensajes de debug; 0 = no mostrar (en producción), 1 = de cliente, 2 = de cliente y servidor
        $mail->Debugoutput = 'html'; //Mostrar mensajes (resultados) de depuración(debug) en html
        /*CONFIGURACIÓN DE PROVEEDOR DE CORREO QUE USARÁ EL EMISOR(GMAIL)*/
        $mail->Host = $p["correo"]["nombre_servidor_smtp"]; //'smtp.gmail.com'; //Nombre de host
        // $mail->Host = gethostbyname('smtp.gmail.com'); // Si su red no soporta SMTP sobre IPv6
        $mail->Port = 587; //Puerto SMTP, 587 para autenticado TLS
        $mail->SMTPSecure = 'tls'; //Sistema de encriptación - ssl (obsoleto) o tls
        $mail->SMTPAuth = true; //Usar autenticación SMTP
        $mail->SMTPOptions = array(
            'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
        ); //opciones para "saltarse" comprobación de certificados (hace posible del envío desde localhost)
        //CONFIGURACIÓN DEL EMISOR
        $mail->Username = $p["correo"]["nombre_usuario"];
        $mail->Password = $p["correo"]["pass_usuario"];
        //            echo "pppp:". $p["correo"]["pass_usuario"];
        //            echo " nombre:". $p["correo"]["nombre_usuario"];

        $mail->setFrom($p["correo"]["nombre_usuario"], 'Notificador: ' . $empresa["nombreempresa"]);

        //CONFIGURACIÓN DEL MENSAJE, EL CUERPO DEL MENSAJE SERA UNA PLANTILLA HTML QUE INCLUYE IMAGEN Y CSS
        $mail->Subject = $empresa["nombreempresa"] . " " . $tipoComp . " " . $nroComp . " Comprobante Electrónico"; //asunto del mensaje
        //incrustar imagen para cuerpo de mensaje(no confundir con Adjuntar)
        $mail->AddEmbeddedImage($fotoEmpresa, 'logoempresa'); //ruta de archivo de imagen
        $mail->AddEmbeddedImage('_lib/mail/logo-administranet-ecommerce.png', 'logoadministranet'); //ruta de archivo de imagen

        $mail->isHTML(true);
        $cuerpo = trae_html_mail($cliente, $url, $tipoComp, $nroComp, $empresa, $fecha, $vendedor, $total);
        //$cuerpo = trae_html_($cliente, $url, $tipoComp, $nroComp, $empresa, $fecha, $vendedor, $total);
        $mail->Body = $cuerpo; //cuerpo del mensaje
        $mail->AltBody = '---'; //Mensaje de sólo texto si el receptor no acepta HTML

        // $mail->addAddress($p["emailCliente"], 'Comprobante'); 
        $mail->addAddress("pflores@administranet.com.ar", 'Comprobante');
        //$mail->Debugoutput();
        //CONFIGURACIÓN DE RECEPTORES
        file_put_contents('log/mail-comprobantes-' . date('Y-m-d_h_i') . '.html', $cuerpo);
        $mail->send();
        // echo "todo bien";
        //$mail->Debugoutput();
        unlink($fotoEmpresa);
        return 0;

        //ENVIAR MENSAJE
    } catch (Exception $e) {
        //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
        file_put_contents('log/err-mail-comprobantes-' . date('Y-m-d_h_i') . '.txt', $mail->ErrorInfo);
        return 1;
    }
}




/**
 * elimino el carrito 
 **/
if(isset($_SESSION['jcart'])){
    unset($_SESSION['jcart']);
}

// el post lo hago aca mismo, envio con session.
// si no hay mails, lo mando a modificar el cliente rapido.
// hacer una funcion de mandar mail desde listado de comprobante.
//// total es hacer una sesion nomas.
//echo "<pre>";
//print_r($_SESSION);
//echo "</pre>";

//$foto = traeLogo($connV,"_lib/mail");
$foto = traeLogo64($connV, "_lib/mail");
//$logo64= ;
//// total es hacer una sesion nomas.
//echo "<img src=".$foto.">";

if (isset($_SESSION["datoMail"])) {
    $dd = $_SESSION["datoMail"];
    //    echo "<pre>";
    //    print_r($dd);
    //    echo "</pre>";
    $comprobante = $_SESSION["nroComprobante"];
    $tipoComprobante = $_SESSION["tipoComprobante"];
    $totalComprobante = $_SESSION["totalComprobante"];
    $nombreCliente = $dd["cliente"];
    $emailCli = $dd["email"];
    $emailContacto = $dd["emailContacto"];
    $codCliente = $dd["Codigo"];
} else {
    // inicion de variables session, 

    $cliente = $_SESSION["cliente"][0];
    //echo "<pre>";
    //print_r($cliente);
    //echo "</pre>";
    $comprobante = $_SESSION["nroComprobante"];
    $tipoComprobante = $_SESSION["tipoComprobante"];
    $totalComprobante = $_SESSION["totalComprobante"];
    $nombreCliente = $cliente->cliente;
    $emailCli = $cliente->email;
    $emailContacto = $cliente->emailcontacto;
    $codCliente = $cliente->Codigo;
}

$vv = $_SESSION["vendedor"];
$vendedor = $vv->apellido_usuario . " " . $vv->nombre_usuario;


$empresa = array(
    "nombreempresa" => $_SESSION['nombre_empresa'],
    "telefonoempresa" => $_SESSION['telefono_empresa'],
    "cuitempresa" => $_SESSION['cuit_empresa'],
    "domicilioempresa" => $_SESSION['domicilio_empresa'],
    "emailempresa" => $_SESSION['email_empresa'],
    "urlempresa" => $_SESSION['direccion_web_empresa'],
    "logo" => $foto
);
// hay mail 
// url ya viene en la sesion.

//echo var_dump($_SESSION["correo"]);
$correo = $_SESSION["correo"];

$sinMail = 0;

if (($emailCli == "" && $emailContacto == "") || ($emailCli == null && $emailContacto == null)) {
    $sinMail++;
}

// ya elegi el correo deberia hacer el envio desde aca.

if (isset($_POST['cualMail'])&&$_POST['cualMail']!='') {
    $mensaje = "";


    $emailElegido = $_POST['cualMail'];
    // cadena a enviar
    $cad[] = array(
        "db" => $_SESSION["id_bd"],
        "codmov" => $_SESSION["codigoMovimiento"],
        "tipocomp" => $tipoComprobante
    );
    $jsonT = json_encode($cad);
    $aVer = urlencode(encriptar_comprobante($jsonT));

    $link = "http://" . $_SERVER["HTTP_HOST"] . "/administraweb/cmp.php?id=" . $aVer;

    //prueba de curl para pasar las variables por post.
    $param = array(
        "tipoComp" => $tipoComprobante,
        "comprobante" => $comprobante,
        "total" => $totalComprobante,
        "emailCliente" => $emailElegido,
        "link" => $link,
        "fecha" => date('d/m/Y'),
        "cliente" => $nombreCliente,
        "vendedor" => $vendedor,
        "empresa" => $empresa,
        "correo" => $correo
    );
    $encriptado = json_encode($param);

    
    if ($correo == NULL) {
        // echo "NO HAY MAIL DEL VIAJANTE NO MANDO NADA";
        $respuesta = 1;
    } else {
        $respuesta = enviar_mail_comprobante($encriptado);
    }

    //var_dump($respuesta);    

    //exit();

    if ($respuesta == 0) {
        switch ($tipoComprobante) {
            case "PRE":
                $urlVuelta = "lista-presupuestos-vendedor.php?cartel=6&pre=" . $comprobante;
                header('Location: ' . $urlVuelta);

                break;
            case "PED":
                break;

            case "FA":
            case "FB":
            case "FC":
            case "FM":
            case "FE":
                $urlVuelta = "lista_facturas_electronicas.php?cartel=6&fact=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REM":
                $urlVuelta = "lista-remitos.php?cartel=6&rem=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REC":
                //                $urlVuelta="lista-recibos.php?cartel=6&rec=".$comprobante;
                $urlVuelta = "listado-clientes.php?cartel=6&tipo=REC&comp=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "NCA":
            case "NCB":
            case "NCC":
            case "NCM":
            case "NCE":
                $urlVuelta = "lista_nota_credito.php?cartel=6&tipo=NC&comp=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
        }
    } else {
        //echo "No se pudo enviar correo";
        switch ($tipoComprobante) {
            case "PRE":
                $urlVuelta = "lista-presupuestos-vendedor.php?cartel=5&pre=" . $comprobante;
                header('Location: ' . $urlVuelta);

                break;
            case "PED":
                break;

            case "FA":
            case "FB":
            case "FC":
            case "FM":
            case "FE":
                $urlVuelta = "lista_facturas_electronicas.php?cartel=5&fact=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REM":
                $urlVuelta = "lista-remitos.php?cartel=5&rem=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "REC":

                $urlVuelta = "listado-clientes.php?cartel=5&tipo=REC&comp=" . $comprobante;
                header('Location: ' . $urlVuelta);
                break;
            case "NCA":
            case "NCB":
            case "NCC":
            case "NCM":
            case "NCE":
                $urlVuelta = "lista_nota_credito.php?cartel=5&tipo=NC&comp=" . $comprobante;
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

if ($sinMail == 1) :
    // echo "sin mail";
    header("Location: mod-cliente-rapido.php?modifica=si&id=" . $codCliente . "&vuelta=fincomprobante");
else :
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
                                </select>
                            </label>
                        </div>
                        <div class="renglonForm">

                            <button type='button' id="cancelarMail" class="botonNuevo grande white"><i class="fas fa-times fa-2x fa-lg" title="Enviar Correo"></i> Cancelar </button>
                            <button type='button' id="mandarCorreo" class="botonNuevo grande azul"><i class="fa fa-envelope fa-2x fa-lg" title="Enviar Correo"></i> Enviar correo </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </body>
<?php endif; ?>