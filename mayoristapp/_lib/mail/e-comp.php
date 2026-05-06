<?php
    set_time_limit(0);
    ignore_user_abort(true);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/PHPMailer/PHPMailer.php';
require 'PHPMailer/PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer/SMTP.php';
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
    function trae_html($destinatario,$url,$tipoComp,$nroComp,$empresa,$fecha,$vendedor,$total){
        $rcss = "p-comp.css";//ruta de archivo css
        $fcss = fopen ($rcss, "r");//abrir archivo css
        $scss = fread ($fcss, filesize ($rcss));//leer contenido de css
        fclose ($fcss);//cerrar archivo css
        $txtHtml='';    
        $txtHtml .='<!DOCTYPE html>';
        $txtHtml .='<head>';
        $txtHtml .='<meta charset="UTF-8">';
        $txtHtml .='<style>'.$scss.'</style>';
        $txtHtml .='    <title>Envio de comprobantes electrónicos</title>';
        $txtHtml .='</head>';
        $txtHtml .='<body>';
        $txtHtml .='    <div id="contenedor">';
        $txtHtml .='        <div id="cabecera">Envio electrónico de comprobante</div>';
        $txtHtml .='        <div id="cuerpo">';
        $txtHtml .='            <p>Fecha: '.$fecha.'</p>';
        $txtHtml .='            <p>'.$destinatario.'</p>';
        $txtHtml .='            <p>Descargue su comprobante aquí <a alt="'.$url.'" title="'.$url.'" href="'.$url.'" target="blank">'.$tipoComp .' '. $nroComp.' $'. number_format($total, 2, ",", ".").'</a></p>';
        $txtHtml .='        </div>';
        $txtHtml .='        <div id="firma">';
        $txtHtml .='            <div id="logoFirma">';
//        $txtHtml .='                <img src="'.$empresa["logo"].'"/>';
        $txtHtml .='                <img src="cid:logoempresa"/>';
        
        $txtHtml .='            </div>';
        $txtHtml .='            <div id="textoFirma">';
        $txtHtml .='                <label><strong>'.$vendedor.'</strong></label><br>';
        $txtHtml .='                <label>'.$empresa["nombreempresa"].'</label><br>';
        $txtHtml .='                <label>'.$empresa["domicilioempresa"].'</label><br>';
        $txtHtml .='                <label>Tel: '.$empresa["telefonoempresa"].'</label><br>';
        $txtHtml .='                <label><a href="'.$empresa["urlempresa"].'" target="_blank">'.$empresa["urlempresa"].'</a></label>';
        $txtHtml .='            </div>';
        $txtHtml .='        </div>';
        $txtHtml .='        <div id="pie">';
        $txtHtml .='            Mail generado por  <a href="http://www.administranet.com.ar" target="_blank">administraNET gestión e-commerce</a> <img src="cid:logoadministranet"/>';
        $txtHtml .='        </div>';
        $txtHtml .='    </div>';
        $txtHtml .='</body>';
        $txtHtml .='</html>';
    return $txtHtml;
    }
	
    function foto64($fotologo64){
       

        // remove the part that we don't need from the provided image and decode it
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $fotologo64));
        $nn=explode(" ",microtime());
        $nombre=$nn[1];
        $filepath = "logototal-".$nombre.".png"; // or image.jpg

        // Save the image in a defined path
        file_put_contents($filepath,$data);
        return $filepath;
    }


    $p = json_decode(file_get_contents("php://input"),TRUE);
    /*RECOGER VALORES ENVIADOS DESDE INDEX.PHP*/
    
//    $sDestino = $_POST['txtDestin'];
//    $sAsunto = $_POST['txtAsunto'];
//    $sMensaje = $_POST['txtMensa'];

    $url =$p["link"];
    $vendedor=$p["vendedor"];
    $empresa=$p["empresa"];
    $tipoComp = $p["tipoComp"];
    $nroComp=$p["comprobante"];
    $total= $p["total"];    
    $fecha=$p["fecha"];
    $cliente=$p["cliente"];
    $mailvendedor=$p["correo"]["nombre_usuario"];
    $fotoEmpresa= foto64($empresa["logo"]);
    
    //echo trae_html($cliente, $url, $tipoComp, $nroComp, $empresa, $fecha, $vendedor);
    
    //phpinfo();
    
    //echo '<pre>'.print_r($pp=json_decode(file_get_contents("php://input")),1).'</pre>';
    //echo trae_html($titulo, $destinatario, $link, $tipoComp, $nroComp, $empresa, $fecha)
    
//$vueltita = desencriptar($_POST["encriptado"]);
    
    //$jsonV= json_decode($_POST["param"],TRUE);
//    date_default_timezone_set('Etc/UTC');
    //echo "json param:<pre>";
    //echo print_r($p);
    //echo "</pre>";
    
//    require 'PHPMailer/PHPMailerAutoload.php';
    
    //$cuerpoMail = trae_html($titulo, $destinatario, $link, $tipoComp, $nroComp, $empresa, $fecha)
//    
//    
    /*CONFIGURACIÓN DE CLASE*/
        $mail = new PHPMailer;
        try{
            $mail->isSMTP(); //Indicar que se usará SMTP
            $mail->CharSet = 'UTF-8';//permitir envío de caracteres especiales (tildes y ñ)
        /*CONFIGURACIÓN DE DEBUG (DEPURACIÓN)*/
            $mail->SMTPDebug = 0; //Mensajes de debug; 0 = no mostrar (en producción), 1 = de cliente, 2 = de cliente y servidor
            $mail->Debugoutput = 'html'; //Mostrar mensajes (resultados) de depuración(debug) en html
        /*CONFIGURACIÓN DE PROVEEDOR DE CORREO QUE USARÁ EL EMISOR(GMAIL)*/
            $mail->Host = $p["correo"]["nombre_servidor_smtp"]; //'smtp.gmail.com'; //Nombre de host
            // $mail->Host = gethostbyname('smtp.gmail.com'); // Si su red no soporta SMTP sobre IPv6
            $mail->Port = 587; //Puerto SMTP, 587 para autenticado TLS
            $mail->SMTPSecure = 'tls'; //Sistema de encriptación - ssl (obsoleto) o tls
            $mail->SMTPAuth = true;//Usar autenticación SMTP
            $mail->SMTPOptions = array(
                'ssl' => array('verify_peer' => false,'verify_peer_name' => false,'allow_self_signed' => true)
            );//opciones para "saltarse" comprobación de certificados (hace posible del envío desde localhost)
        //CONFIGURACIÓN DEL EMISOR
            $mail->Username = $p["correo"]["nombre_usuario"];
            $mail->Password = $p["correo"]["pass_usuario"];
            $mail->setFrom($p["correo"]["nombre_usuario"], 'Notificador: '.$empresa["nombreempresa"]);

        //CONFIGURACIÓN DEL MENSAJE, EL CUERPO DEL MENSAJE SERA UNA PLANTILLA HTML QUE INCLUYE IMAGEN Y CSS
            $mail->Subject = $empresa["nombreempresa"]." " .$tipoComp." ".$nroComp. " Comprobante Electrónico"; //asunto del mensaje
            //incrustar imagen para cuerpo de mensaje(no confundir con Adjuntar)
            $mail->AddEmbeddedImage($fotoEmpresa, 'logoempresa'); //ruta de archivo de imagen
            $mail->AddEmbeddedImage('logo-administranet-ecommerce.png', 'logoadministranet'); //ruta de archivo de imagen
                
            $mail->isHTML(true); 
            $cuerpo = trae_html($cliente, $url, $tipoComp, $nroComp, $empresa, $fecha, $vendedor,$total);
            $mail->Body = $cuerpo; //cuerpo del mensaje
            $mail->AltBody = '---';//Mensaje de sólo texto si el receptor no acepta HTML

            $mail->addAddress($p["emailCliente"], 'Comprobante'); 
            //$mail->addAddress("pflores@administranet.com.ar", 'Comprobante'); 

        //CONFIGURACIÓN DE RECEPTORES
            
            $mail->send();
            echo "0";
            unlink($fotoEmpresa);
            //echo $cuerpo;
        //ENVIAR MENSAJE
        } catch (Exception $e) {
            //echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            echo "1";
        }
        
?>