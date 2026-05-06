<?php


// funcion para reenviar el mail de un canje realizado.
function mandar_canje_email($idCanje,$conexion){
    // variables iniciales.
    $datosCanje=array();
    $arrPremiosCanje=array();
    $arrDomCliente=array();
    
    $mensaje="";
    $error=0;
    $errMensaje="";
    $particulo = "<ol>\n";

    // 1 buscar los datos del canje 
    $sqlCanje="SELECT 
                canje.id_sp_comprobante_canje,
                canje.fecha_control,
                canje.puntaje_consumido_total,
                cliente.nombre_cliente,
                cliente.Codigo,
                cliente.id_manual_cli,
                usuarios.nombre_usuario,
                usuarios.apellido_usuario
                FROM sp_comprobante_canje AS canje
                LEFT JOIN cliente ON cliente.Codigo=canje.id_cliente
                LEFT JOIN usuarios ON usuarios.id_usuario=canje.id_usuario
                WHERE
                canje.id_sp_comprobante_canje=".$idCanje.";";
    $hcanje=mysqli_query($conexion,$sqlCanje);
    if(!$hcanje){
        $errMensaje .='canje error sql: '.$sqlCanje.' descerror:'.mysqli_error($conexion).'<br>';
        $error++;
    }
    // encontre el canje guardo los datos.
    if($hcanje){
        $datosCanje=mysqli_fetch_assoc($hcanje);
    }            
    // 2 buscar premios del canje
    $sqlPremio="SELECT 
                premio.nombre_premios,
                pcanje.cantidad,
                pcanje.puntos_consumido,
                pcanje.puntos_premio
                FROM sp_premios_canje AS pcanje
                LEFT JOIN sp_abm_premios AS premio ON premio.id_abm_premios=pcanje.id_abm_premios 
                WHERE pcanje.id_sp_comprobante_canje=".$idCanje.";";
    $hpremio=mysqli_query($conexion,$sqlPremio);
    if(!$hpremio){
        $errMensaje .='premio error sql: '.$sqlPremio.' descerror:'.mysqli_error($conexion).'<br>';
        $error++;
    }
    // recupero los premios
    if($hpremio){
        while($premio=mysqli_fetch_assoc($hpremio)){

            $particulo .= '<li> ' . $premio["nombre_premios"] . '  ' . $premio["cantidad"] . ' x  ' . $premio["puntos_premio"] .' pts = <strong>' . $premio["puntos_consumido"] . ' pts</strong> </li>' . PHP_EOL;
            $arrPremiosCanje[] = array('premio'=>$premio["nombre_premios"],'cantidad'=>$premio["cantidad"],'puntos'=>$premio["puntos_premio"],'subtotal'=>$premio["puntos_consumido"]);
            //$arrPremiosCanje[]=$premio;
        }
        $particulo .= "</ol>" . PHP_EOL;
    }


    // si encontre el canje traigo los domicilios.
    if(!empty($datosCanje)){
        // 3 buscar los domicilios de entrega del cliente.
        $sqlDomicilios="SELECT
                        cliente_domicilio.Calle,
                        cliente_domicilio.NroCalle,
                        cliente_domicilio.Dpto,
                        provincia.Provincia,
                        departamento.NombreDepartamento,
                        distrito.NombreDistrito,
                        erp_zona.nombre_zona
                        FROM cliente_domicilio
                        LEFT JOIN provincia ON provincia.CodProvincia=cliente_domicilio.CodProvincia
                        LEFT JOIN departamento ON departamento.IDDepartamento=cliente_domicilio.IDDepartamento
                        LEFT JOIN distrito ON distrito.IDDistrito=cliente_domicilio.IDDistrito
                        LEFT JOIN erp_zona ON erp_zona.id_zona=cliente_domicilio.id_zona
                        WHERE
                        cliente_domicilio.id_cliente=".$datosCanje["Codigo"];
        $hdomi=mysqli_query($conexion,$sqlDomicilios);
        if(!$hdomi){
            $errMensaje .='domicilio error sql: '.$sqlDomicilios.' descerror: '.mysqli_error($conexion);
            $error++;
        }
        if($hdomi){
            while($domi=mysqli_fetch_assoc($hdomi)){
                $arrDomCliente[]=$domi;
            }
        }
    }
    // 4 armar datos para el cuerpo html
    if($error===0){
        $mensaje .= "\nComprobante de Canje: ".str_pad($idCanje,8,'0',STR_PAD_LEFT )."\n";
        $nroCanje=str_pad($idCanje,8,'0',STR_PAD_LEFT );
        $cliente=utf8_encode($datosCanje["nombre_cliente"]) . " (ID: " . $datosCanje["id_manual_cli"] . ")";
        $vendedor= $datosCanje["nombre_usuario"] .' '.$datosCanje["apellido_usuario"];
        $f= strtotime ( $datosCanje["fecha_control"] ); 
        $fechaControl =date ( 'd/m/Y H:i' ,$f);
        $txt = "El cliente " . utf8_encode($cliente) . " (ID: " . $datosCanje["id_manual_cli"] . ") Ha Canjeado " . $datosCanje["puntaje_consumido_total"] ." puntos". PHP_EOL . $particulo;
        // 5 armar el html
        $textoHtml=mail_html($cliente,$vendedor,$nroCanje,$fechaControl,$arrPremiosCanje,$datosCanje["puntaje_consumido_total"],$arrDomCliente);
        
        // 6 enviar el correo
        require_once 'mail.php';
        $mailOk=enviarMail('f.calderon@laibero.com.ar', $txt, null, $textoHtml, 'Se ha realizado un canje a ' . utf8_encode($cliente) . ' ' . $fechaControl, 'd.nakamura@laibero.com.ar');
		//$mailOk=enviarMail('f.calderon@laibero.com.ar', $txt, null, $textoHtml, 'Se ha realizado un canje a ' . utf8_encode($cliente) . ' ' . $fechaControl, 'lic.pflores@gmail.com');
		
        
        if(!$mailOk){
            $error++;
            $errMensaje .="<br> NO se envio e-mail";
        }else{
            $okMensaje ="Mail enviado con exito";
        }
    }


    if($error!=0){
        $vuelta=array('msg'=>'error','desc'=>$errMensaje);
    }else{
        $vuelta=array('msg'=>'ok','desc'=>$okMensaje);
    }

    print json_encode($vuelta);
    

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
    
    // ---------------- domicilios adicionales -------------------------------
	/*
    if(!empty($arrDomicilios)){
        $html .='<tr class="vendedor-pedido">';
        $html .='<td><p>Domicilio de entrega: </p>';
        foreach($arrDomicilios as $dom){
            $html .='<p><strong>'.$dom["Calle"].' '.$dom["NroCalle"].' '.$dom["Provincia"] .', '.$dom["NombreDepartamento"].', '.$dom["NombreDistrito"].' - '.$dom["nombre_zona"].'</strong></p>';
            
            
        }
        $html .='</td></tr>';
        
    }
    */
    
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
    $html .='	 	<tr class="administranet" bgcolor="#005aa0">';
    $html .='	 		<td  align="center" valign="middle">';
    $html .='	 			<p>Mail generado por <a href="https://www.administranet.com.ar" target="_blank">administraNET gestión e-commerce</a></p>';
    $html .='	 			<img style="width:30px;" src="cid:adm-logo-svg"/>';
    $html .='	 		</td>';
    $html .='	 	</tr>';

    $html .='	 </table>';         
    $html .='        </body>';
    $html .='        </html>';
    
    
    return $html;
}

// recibo parametros
require_once 'preinclude.php';
if(isset($_REQUEST["enviarMailCanje"])&&$_REQUEST["enviarMailCanje"]==1){
    $idCanje=$_REQUEST["idCanje"];
    mandar_canje_email($idCanje,$mysqli);
}
