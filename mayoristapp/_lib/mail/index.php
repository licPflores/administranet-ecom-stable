<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer/PHPMailer.php';
require 'PHPMailer/PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer/SMTP.php';
//Load composer's autoloader
//require 'vendor/autoload.php';


$mensaje = 'No tengo contenido';
if(isset($argv[1]))$mensaje=$argv[1];
if(isset($_REQUEST['mensaje']))$mensaje=$_REQUEST['mensaje'];
$idcliente = 0;
if(isset($_REQUEST['idcliente']))$idcliente=$_REQUEST['idcliente'];
$mailr = 'soporte@administranet.com.ar';
$titulo ='';
$pie = '';
$mails = "soporte@administranet.com.ar";
if(isset($_REQUEST['Correo']))
if(!empty($_REQUEST['Correo']))$mails = $_REQUEST['Correo'];
	


        $mysqli = new mysqli('localhost', 'admin', '$AES-128-CBC$wjc/51J4MDafnoVaFLgAeg==$uawlTLYnK+u98IIZ6CdOXA==', 'administranet_interno');

if ($mysqli->connect_errno) {
    die('Error de Conexion: ' .  $mysqli->connect_error);
}
$sql = 'select * from gestion_cliente WHERE id_cliente = '.$idcliente;
$resultado = $mysqli->query($sql);

if ($resultado = $mysqli->query($sql)) {

    /* obtener el array de objetos */
    while ($obj = $resultado->fetch_object()) {
        //printf ("%s (%s)\n", $obj->Name, $obj->CountryCode);
$mailr = $obj->email_cliente;
$titulo = $obj->nombre_cliente;
    }

    /* liberar el conjunto de resultados */
    $resultado->close();
}
if ( $idcliente==0) $pie = "\n \t Sin ID Cliente";
$mail = new PHPMailer();                              // Passing `true` enables exceptions
try {
    //Server settings
    $mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'localhost';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'lfalcon@administranet.com.ar';                 // SMTP username
    $mail->Password = 'Q1w2e3R$';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    //Recipients
    $mail->setFrom('soporte@administranet.com.ar', 'Notificador');
    $mail->addAddress($mails, 'Alerta');     // Add a recipient
//    $mail->addAddress('ellen@example.com');               // Name is optional
   $mail->addReplyTo($mailr, $titulo);
  //  $mail->addCC('cc@example.com');
   // $mail->addBCC('bcc@example.com');

    //Attachments
   // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Mensaje de Notificacion '.$titulo;
    $mail->Body    = nl2br($mensaje.$pie).'<img src="http://administranet.com.ar/images/logo.png">';
    $mail->AltBody = $mensaje;

    $mail->send();
 //   echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}
