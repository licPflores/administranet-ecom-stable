<?php

ini_set("display_errors", 1);
if (!isset($mysqli))
    require_once 'preinclude.php';


function enviarMail($Correo = null, $mensaje = null, $adjunto = null, $MsgHtml = null, $Asunto = null, $CopiaAOtroMail = null) {
    global $mysqli;



    if ($mysqli->connect_errno) {

        echo "Error: Fallo al conectarse a MySQL debido a: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        die("Error: " . $mysqli->connect_error . "\n");
    }
    $mysqli->query("SET lc_time_names = 'es_AR'");


    $correo = $Correo;

//$vector = split('@',$correo);
    $vector = explode('@', $correo);
    $ncorreo = $vector[0];
    $ncorreo = '🎁 ' . $ncorreo;
    if (!filter_var($Correo, FILTER_VALIDATE_EMAIL)) {
        echo " Correo invalido";
        return false;
    }

    if (empty($mensaje)) {
        echo " No tengo Mensaje";
        return false;
    }


    If ($Asunto == "")
        $Asunto = "Solicitud canje de premio"; // Mensaje por defect

    $nombre = "Estimado Cliente";

    //mailera
    require_once '../../_lib/mail/PHPMailerAutoload.php';

    // $res = $mysqli->query("SELECT * FROM `correo_usr` WHERE id_usuario =1") or die ("No tengo datos del primer Usuario");
    $res = $mysqli->query("SELECT * FROM correo_usr where id_usuario = 1") or die("No tengo datos del primer Usuario");

	
	
    $contador = $res->num_rows;
    if ($contador < 1){
        die("No tengo Credenciales de correo para supervisor " . $contador);
        return false;
    }
    $cliente = $res->fetch_object();
	//echo '<pre>';
	//print_r($cliente).PHP_EOL;

    $mail = new PHPMailer;
    $mail->CharSet = 'UTF-8';

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
//$mail->Host = 'administranet.com.ar';  // Specify main and backup SMTP servers
    $mail->Host = $cliente->nombre_servidor_smtp;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $cliente->nombre_usuario;                 // SMTP username
    $mail->Password = $cliente->pass_usuario;    
	//$mail->SMTPDebug  = 2;
                       // SMTP password
    $tt = 'none';
    if ($cliente->ssl == 'Si')
        $tt = 'ssl';
    if (preg_match('/gmail/i', $cliente->nombre_servidor_smtp))
        $tt = 'tls';
    $mail->SMTPSecure = $tt;                            // Enable TLS encryption, `ssl` also accepted

    $mail->Port = $cliente->puerto_servidor_smtp;                                    // TCP port to connect to

    $mail->setFrom($cliente->nombre_usuario, 'La ibero club 🎁');
    $mail->addAddress($Correo, $ncorreo);     // Add a recipient

    if ($CopiaAOtroMail != null){
		$mail->addCC($CopiaAOtroMail);
	}
    $mail->AddEmbeddedImage('../style/check.png', 'check-png');
    $mail->addEmbeddedImage('../style/adm-logo.png', 'adm-logo-svg');
    $mail->isHTML(true);                                  // Set email format to HTML
    /* $mensaje=null,
      $adjunto=null,
      $MsgHtml=null,
      $Asunto=null,
      $CopiaAOtroMail=null */

    $mail->Subject = $Asunto;
    $mail->Body = $MsgHtml;
    $mail->AltBody = $mensaje;

    if (!$mail->send()) {


        $tql = "INSERT INTO error SET
                fecha_error=CURDATE(),
                detalle_error='" . $mail->ErrorInfo . "',
                ventana_error='envio de mail de canje',
                id_usuario=1,
                nro_error=1,
                hora_error=CURRENT_TIMESTAMP;";
        $mysqli->query($tql);
        // echo "<pre>" . $mail->ErrorInfo . "</pre>\n";
        // phpinfo();
        return false;
    } else {

        return true;
    }


    $resultado->close();
    $mysqli->close();





    //fin de funcion enviarMail
}

?>