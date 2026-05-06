<?
echo 'TEST'.PHP_EOL;
//require 'PHPMailerAutoload.php';
require_once '../_lib/mail/PHPMailerAutoload.php';
/*
 $mail->Username ='ventas.laiberoe@gmail.com';
	$mail->Password = 'hncvlddnmhwypgny';  
 */
$Correo = new PHPMailer();
  $Correo->IsSMTP();
  $Correo->CharSet = 'UTF-8';
  $Correo->SMTPAuth = true;
  $Correo->SMTPSecure = "none";
  $Correo->SMTPAutoTLS = true;
  $Correo->Debugoutput = 'html'; 
	$Correo->SMTPDebug = 3;
  $Correo->Host = "smtp.gmail.com";
  $Correo->Port = 587;
  $Correo->Username = "lcastelat@gmail.com";
  $Correo->Password = "oxvzbmfyarogbvzv";
  $Correo->SetFrom('lcastelat@gmail.com','De Yo');
  $Correo->FromName = "From";
  $Correo->AddAddress("lic.pflores@gmail.com");
  $Correo->Subject = "Prueba con PHPMailer PABLO";
  $Correo->Body = "<H3>Bienvenido! Esto Funciona! llegara llegara???</H3>";
  $Correo->IsHTML (true);
  if (!$Correo->Send())
  {
    echo "Error: $Correo->ErrorInfo";
  }
  else
  {
    echo "Message Sent!";
  }
  ?>