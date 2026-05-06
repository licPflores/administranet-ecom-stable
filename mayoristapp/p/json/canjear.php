<?php

require_once 'preinclude.php';
if (!isset($_SESSION)) {
    session_start();
}
if (isset($_SESSION)) {
    if (isset($_SESSION['carrito']) && isset($_SESSION['cantidad'])) {
        
        
        
        if (!isset($_SESSION['cliente'])){
            die("No tengo usuario");
        }
        if (is_object($_SESSION['cliente'])) {
            $clienteObj = $_SESSION['cliente'];
        } else {
            $clienteObj = $_SESSION['cliente'][0];
        }
        $idCliente = $clienteObj->Codigo;
        
        $articulos = $_SESSION['carrito'];
        $cantidaddeproductos = $_SESSION['cantidad'];
        $carrito = $_SESSION['carrito'];
        $cantidades = $_SESSION['cantidad'];
        $puntosPrem= $_SESSION['puntos'];
        $nombrePremio= $_SESSION['premios'];

        $idUsuarioCanje=$_SESSION['idusuario'];
        $vendedor=$_SESSION["vendedor"];
        $usuarioCanje = $vendedor->nombre_usuario . ' '. $vendedor->apellido_usuario; 
        $puntosCliente = Puntos(); //Puntos que tiene el cliente para canjear
        
        $saldo = 0;
        $arrPremios=array();
        foreach ($carrito as $idProducto => $valor) {
            $cantidad = $cantidades[$idProducto];
            $ptos = $puntosPrem[$idProducto];
            $subtotal = $ptos * $cantidad;
            //echo " Subtotal ".$subtotal."</p>\n";
            $saldo += $subtotal;
        }


        if ($puntosCliente < $saldo) {
            $mensaje = "Los puntos que tiene (" . $puntosCliente . ") no son suficientes para este canje (" . $saldo . ")";
            $estado="error";
        } else {
            $mensaje = "Usted tiene $puntosCliente puntos y se canjearan  " . $saldo . " puntos";
            $estado="ok";

            if ($mysqli->connect_errno) {

                echo "Error: Fallo al conectarse a MySQL debido a: \n";
                echo "Errno: " . $mysqli->connect_errno . "\n";
                die("Error: " . $mysqli->connect_error . "\n");
            }

// sp_comprobante_canje

            $sql = "INSERT INTO sp_comprobante_canje SET
                    fecha = CURDATE(),
                    id_cliente = " . $idCliente . ",
                    puntaje_consumido_total = " . $saldo . ",
                    estado = 'Solicitado',
                    id_usuario='".$idUsuarioCanje."',
                    fecha_entrega = date_add(CURDATE(), INTERVAL 1 YEAR),
                    anulado = 'No';";

            if (!$resultado = $mysqli->query($sql)) {

                echo "Error: La ejecución de la consulta falló debido a: \n";
                echo "Query: " . $sql . "\n";
                echo "Errno: " . $mysqli->errno . "\n";
                die("Error: " . $mysqli->error . "\n");
            }
            $id_sp_comprobante_canje = mysqli_insert_id($mysqli);

            $mensaje .= "\nComprobante de Canje: ".str_pad($id_sp_comprobante_canje,8,'0',STR_PAD_LEFT )."\n";
            $nroCanje=str_pad($id_sp_comprobante_canje,8,'0',STR_PAD_LEFT );

//sp_premios_canje
            $particulo = "<ol>\n";
            $arrPremios=array();
            foreach ($articulos as $indice => $value) {

                $sql = "INSERT INTO sp_premios_canje SET
                                id_abm_premios = '" . $indice . "',
                                puntos_consumido =" . $value . ",
                                id_sp_comprobante_canje = '" . $id_sp_comprobante_canje . "',
                                puntos_premio='" . $puntosPrem[$indice] . "',
                                estado='Solicitado',
                                cantidad='" . $cantidaddeproductos[$indice] . "'
                                ";
                // puntos_consumido = (" . $cantidaddeproductos[$indice] . "*" . PuntosdelProducto($indice) . "),
                //
                $particulo .= '<li> ' . $nombrePremio[$indice] . '  ' . $cantidaddeproductos[$indice] . ' x  ' . $puntosPrem[$indice] .' pts = <strong>' . $value . ' pts</strong> </li>' . PHP_EOL;
                $arrPremios[] = array('premio'=>$nombrePremio[$indice],'cantidad'=>$cantidaddeproductos[$indice],'puntos'=>$puntosPrem[$indice],'subtotal'=>$value);


                if (!$resultado = $mysqli->query($sql)) {

                    $mensaje .= "Error: La ejecución de la consulta falló debido a: \n"
                            . "Query: " . $sql . "\n"
                            . "Errno: " . $mysqli->errno . "\n"
                            . "Error: " . $mysqli->error . "\n";
                } else {

                    $mensaje .= "Premio: $nombrePremio[$indice] Cantidad: $cantidaddeproductos[$indice] \n";

                    //ACUTALIZO SALDO (cantidad de productos)

                    $sql = "UPDATE sp_abm_premios SET saldo_premios=saldo_premios-(" . $cantidaddeproductos[$indice] . ") WHERE id_abm_premios='" . $indice . "'";

                    if (!$resultado = $mysqli->query($sql)) {

                        $mensaje .= "Error: La ejecución de la consulta falló debido a: \n"
                                . "Query: " . $sql . "\n"
                                . "Errno: " . $mysqli->errno . "\n"
                                . "Error: " . $mysqli->error . "\n";
                    }


                    //borro carrito			
                }
            }
            $particulo .= "</ol>" . PHP_EOL;

            $sql = "UPDATE sp_saldo_cliente_premios SET  
					saldo_premios = saldo_premios-(" . $saldo . ")
					where id_cliente = " . $idCliente;

            if (!$resultado = $mysqli->query($sql)) {

                $mensaje .= "Error: La ejecución de la consulta falló debido a: \n"
                        . "Query: " . $sql . "\n"
                        . "Errno: " . $mysqli->errno . "\n"
                        . "Error: " . $mysqli->error . "\n";
            } else {
                
                require_once "mail.php";
                $mensaje=" Nro canje: <strong>".$nroCanje."</strong> generado.";
                $Manual = $clienteObj->id_manual_cli;
                $txt = "El cliente " . utf8_encode($clienteObj->cliente) . " (ID: " . $Manual . ') Ha Canjeado ' . $saldo .' puntos'. PHP_EOL . $particulo;
//                $html = "<h1>El cliente " . htmlentities($clienteObj->cliente) . " (id:" . $Manual . ")</h1>\n";
//                $html .= "<h2>Premios: </h2>" . PHP_EOL . $particulo;
//                $html .= '<h3>Ha Canjeado un total de  ' . $saldo . " puntos</h3>" . PHP_EOL;
//                $html .='<h3>Realizado por: '.$usuarioCanje.'</h3>'.PHP_EOL;
//                 $html .='<h5>generado por administraNET gestión.</h5>'.PHP_EOL;
                 $cliente=utf8_encode($clienteObj->cliente) . " (ID: " . $Manual . ")";
                 $arrDomicilios= $_SESSION["domicilios_cliente"];
                 $fecha=date('d/m/y H:i');
                 $html = mail_html($cliente, $usuarioCanje, $nroCanje, $fecha, $arrPremios, $saldo,$arrDomicilios);
                
                $mailOk=enviarMail('f.calderon@laibero.com.ar', $txt, null, $html, 'Se ha realizado un canje a ' . utf8_encode($clienteObj->cliente) . ' ' . date('d/m/y H:i'), 'd.nakamura@laibero.com.ar');
				//$mailOk=enviarMail('f.calderon@laibero.com.ar', $txt, null, $html, 'Se ha realizado un canje a ' . utf8_encode($clienteObj->cliente) . ' ' . date('d/m/y H:i'), 'lic.pflores@gmail.com);
		       
                if(!$mailOk){
                    $mensaje .="<br> NO se envio e-mail";
                }else{
                    $mensaje .="<br> Con envio de e-mail";
                }
            
                // voy a avisar si todo bien
                $estado="ok";
                
                unset($_SESSION['carrito']);
                unset($_SESSION['cantidad']);
                unset($_SESSION['puntos']);
                unset($_SESSION['premios']);
            }
        }

        echo json_encode(array("estado"=>$estado, "Mensaje" => $mensaje));
    } else {
        echo json_encode(array("estado"=>'error', "Mensaje" => "No hay premios para canjear"));
    }
}




function Puntos() {
    global $idCliente;
    global $mysqli;


    if ($mysqli->connect_errno) {

        echo "Error: Fallo al conectarse a MySQL debido a: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        die("Error: " . $mysqli->connect_error . "\n");
    }
    $sql = "select id_cliente,saldo_premios,
            date_format(vencimiento,'%d/%m/%Y') as vencimiento
            from sp_saldo_cliente_premios where id_cliente = " . $idCliente . " limit 1;";
    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    }
    $Registro = mysqli_fetch_object($resultado);
    $saldo_premios = $Registro->saldo_premios;
    return $Registro->saldo_premios;
}




function PuntosdelProducto($producto) {

    global $mysqli;


    if ($mysqli->connect_errno) {

        echo "Error: Fallo al conectarse a MySQL debido a: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        die("Error: " . $mysqli->connect_error . "\n");
    }
    $sql = "select  round(
                if(s.anulado='No',
                if(s.vigencia_premios>=CURDATE(), s.puntos_premios,0)
                 ,0)
                 ,0) 
                as pto, s.* from sp_abm_premios s 
                WHERE s.id_abm_premios = " . $producto . " limit 1";
    if (!$resultado = $mysqli->query($sql)) {

        echo "Error: La ejecución de la consulta falló debido a: \n";
        echo "Query: " . $sql . "\n";
        echo "Errno: " . $mysqli->errno . "\n";
        die("Error: " . $mysqli->error . "\n");
    }
    $Registro = mysqli_fetch_object($resultado);
    $puntosdlcliente = $Registro->pto;
    return $puntosdlcliente;
}

function mail_html($cliente,$vendedor,$comprobante,$fecha,$arrPremios,$totalPuntos,$arrDomicilios){
    $html='';
    $rcss = "../style/mail-premio.css";//ruta de archivo css
    $fcss = fopen ($rcss, "r");//abrir archivo css
    $scss = fread ($fcss, filesize ($rcss));//leer contenido de css
    fclose ($fcss);//cerrar archivo css
    $html .='    <!DOCTYPE html>';
    $html .='<html>';
    $html .='    <head>';
    $html .='        <title>La Ibero Club , nuevo canje del vendedor</title>';
    $html .='        <meta charset="UTF-8">';
    $html .='        <meta name="viewport" content="width=device-width, initial-scale=1.0">';
    $html .='        <meta http-equiv="X-UA-Compatible" content="IE=edge">';
    $html .='       <style type="text/css">'.$scss.'</style>';
    $html .='    </head>';
    $html .='    <body>';
    $html .='      <table cellpadding="0" cellspacing="0" border="0" width="540px" style="margin: 11px;">';	

    $html .='	 	<tr class="encabezado">';	 		
    $html .='	 		<td width="540px">';
    $html .='	 			<h1>Se ha realizado un canje 🎁!</h1>';
    $html .='	 		</td>';
    $html .='	 	</tr>';	

    $html .='	 	<tr class="estado-pedido">';
    $html .='	 		<td  align="center" valign="middle">';
    $html .='	 			<img width="50px" src="cid:check-png"/>';
    $html .='	 		</td>';
    $html .='	 	</tr>';
    $html .='	 	<tr class="estado-pedido">';
    $html .='	 		<td >';
    $html .='	 			<h2>'.$cliente.'</h2>';
    $html .='	 		</td>';
    $html .='	 	</tr>';

    $html .='	 	<tr class="datos-pedido">';
    $html .='	 		<td  align="center" width="540px">';

    $html .='	 			<table cellpadding="0" cellspacing="0" border="0" width="540px">';

    $html .='	 				<tr>';
    $html .='	 					<td class="titulo-tabla-pedido" colspan="4">';
    $html .='	 						<h2>Canje: '.$comprobante.'</h2>';
    $html .='	 					</td>';
    $html .='	 				</tr>';

    $html .='	 				<tr>';
    $html .='	 					<td style="width: 60%; border-bottom: 1px solid #333333;">';
    $html .='	 						<h5>Premio</h5>';
    $html .='	 					</td>';
    $html .='                                                <td style="width: 10%; text-align: center; border-bottom: 1px solid #333333;">';
    $html .='	 						<h5>Cant</h5>';
    $html .='	 					</td>';
    $html .='                                                <td style="width: 10%;text-align: center; border-bottom: 1px solid #333333;">';
    $html .='	 						<h5>Ptos</h5>';
    $html .='	 					</td>';
    $html .='                                                <td style="width: 20%; text-align: right; border-bottom: 1px solid #333333;">';
    $html .='	 						<h5>SubTotal</h5>';
    $html .='	 					</td>';

    $html .='	 				</tr>';
    foreach($arrPremios AS $p){
        $html .='	 				<tr>';
        $html .='	 					<td style="width: 60%;">';
        $html .='	 						<p>'.$p["premio"].'</p>';
        $html .='	 					</td>';
        $html .='                                                 <td style="width: 10%; ">';
        $html .='	 						<p style="text-align: center;">'.$p["cantidad"].'</p>';
        $html .='	 					</td>';
        $html .='                                                <td style="width: 10%;">';
        $html .='	 						<p style="text-align: center;">'.number_format($p["puntos"],0,",",".").'</p>';
        $html .='	 					</td>';
        $html .='                                                 <td style="width: 20%;">';
        $html .='	 						<p style="text-align: right;">'.number_format($p["subtotal"],0,",",".").'</p>';
        $html .='	 					</td>';
        $html .='	 				</tr>';

    }
    $html .='                                        <tr>';
    $html .='	 					<td  colspan="2" style="border-top:1px solid black;">';
    $html .='	 						<h5>Total de puntos</h5>';
    $html .='	 					</td>';
    $html .='                                                 <td colspan="2" style="border-top:1px solid black;">';
    $html .='	 						<p style="text-align: right;" class="puntos">'.number_format($totalPuntos,0,",",".").'</p>';
    $html .='	 					</td>';
    $html .='	 				</tr>';
    $html .='	 			</table>';
    $html .='	 		</td>';
    $html .='	 	</tr>';
    
       
    
    // ---------------------- fin domicilios ---------------------------------

    $html .=' 	<tr class="vendedor-pedido">';	 		
    $html .='	 		<td align="center" valign="middle">';
    $html .='	 			<p>Realizado por: <strong>'.$vendedor.'</strong></p>';
    $html .='	 		</td>';
    $html .='	 	</tr>';
    $html .='                <tr class="fecha-pedido">';	 		
    $html .='	 		<td  align="center" valign="middle">';
    $html .='	 			<p> Generado el '.$fecha.'</p>';
    $html .='	 		</td>';
    $html .='	 	</tr>';

    $html .='	 	<tr class="footer">';
    $html .='	  		<td >';
    $html .='	 			<table cellpadding="0" cellspacing="0" border="0" bgcolor="#f7a832" >';
    $html .='	 				<tr>	 					';
    $html .='                                            <td align="center" valign="middle">';
    $html .='                                                <h4>La Ibero Club 🎁. La Ibero Española <a href="http://www.laiberoespanola.com.ar" target="_blank">www.laiberoespanola.com.ar</a></h4>';
    $html .='                                            </td>';
    $html .='	 				</tr>'; 				
    $html .='	 			</table>';

    $html .='	 		</td>';
    $html .='	 	</tr>';

    $html .='	 	<tr class="administranet" bgcolor="#005aa0">';
    $html .='	 		<td  align="center" valign="middle">';
    $html .='	 			<p>Mail generado por <a href="https://www.administranet.com.ar" target="_blank">administraNET gestión e-commerce</a></p>';
    $html .='	 			<img style="width:30px;" src="cid:adm-logo-svg"/>';
    $html .='	 		</td>';
    $html .='	 	</tr>';
	// ---------------- domicilios adicionales -------------------------------
    if(!empty($arrDomicilios)){
		$html .='<tr class="vendedor-pedido">';
		$html .='<td >';
		$html .='	 <h2>'.$cliente.'</h2>';
		$html .='</td>';
		$html .='</tr>';
        $html .='<tr class="vendedor-pedido">';
        $html .='<td><p>Domicilio de entrega: </p>';
        foreach($arrDomicilios as $dom){
            $html .='<p><strong>'.$dom["Calle"].' '.$dom["NroCalle"].' '.$dom["Provincia"] .', '.$dom["NombreDepartamento"].', '.$dom["NombreDistrito"].' - '.$dom["nombre_zona"].'</strong></p>';
            
            
        }
        $html .='</td></tr>';
        
    }
	// ---------------- domicilios adicionales -------------------------------
    if(!empty($arrDomicilios)){
		$html .='<tr class="vendedor-pedido">';
		$html .='<td >';
		$html .='	 <h2>'.$cliente.'</h2>';
		$html .='</td>';
		$html .='</tr>';
        $html .='<tr class="vendedor-pedido">';
        $html .='<td><p>Domicilio de entrega: </p>';
        foreach($arrDomicilios as $dom){
            $html .='<p><strong>'.$dom["Calle"].' '.$dom["NroCalle"].' '.$dom["Provincia"] .', '.$dom["NombreDepartamento"].', '.$dom["NombreDistrito"].' - '.$dom["nombre_zona"].'</strong></p>';
            
            
        }
        $html .='</td></tr>';
        
    }

    $html .='	 </table>';         
    $html .='        </body>';
    $html .='        </html>';
    
    
    return $html;
}