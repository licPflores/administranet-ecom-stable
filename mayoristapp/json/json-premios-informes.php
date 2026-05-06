<?php
/*
 * JSON que puede no devolver json para los informes
 * de los premios 
 */

require_once '../sesion.inc.php';
/*
 * FUNCIONES
 * =============================================================================
 */
// HISTORIAL DE PUNTOS Y CANJES 
// trae 

function trae_historial_puntos($connV,$cliente,$arrFiltros){
    $where="";
    $whereC="";
    $colSpan=7;
    if($cliente){
        $where.=" AND histo.id_cliente=".$cliente;
        $whereC .=" AND ccanje.id_cliente=".$cliente;
    }
    if(isset($arrFiltros["fecha"])){
        $where.= " AND (histo.fecha BETWEEN '".$arrFiltros["fecha"]["desde"]."' ";
        $where .=" AND '".$arrFiltros["fecha"]["hasta"]."')";
        
        $whereC.= " AND (ccanje.fecha BETWEEN '".$arrFiltros["fecha"]["desde"]."' ";
        $whereC .=" AND '".$arrFiltros["fecha"]["hasta"]."')";
        
    }
    
    // buscar comprobantes que generan puntos
    
    $sqlPuntos="SELECT "
            . "histo.id_sp_movimiento_premios AS id,"
            . " DATE_FORMAT(histo.fecha,'%Y%m%d') AS fechaa,"
            . "DATE_FORMAT(histo.fecha,'%d/%m/%Y') AS fechab,"
            . "histo.tipo_comp,"
            . "histo.nro_comp,"
            . "histo.monto_neto,"
            . "histo.monto_final,"
            . "histo.puntos_acumulados,"
            . "CONCAT(cliente.nombre_cliente,' (id: ',cliente.id_manual_cli, ')' ) AS clienteh "            
            . "FROM sp_movimiento_premios AS histo "
            . "LEFT JOIN cliente ON cliente.Codigo=histo.id_cliente "
            . "WHERE histo.anulado='No' ".$where." "
            . "ORDER BY histo.fecha ASC,histo.id_sp_movimiento_premios ASC";
    $hacerP=mysqli_query($connV,$sqlPuntos) or die("error historial puntos ".mysqli_error($connV).' <pre>'.$sqlPuntos.'</pre>');
    $arrMov=array();
    while($d= mysqli_fetch_assoc($hacerP)){
        $key=$d["fechaa"];
        $arrMov[$key][]=$d;
        
    }
    
    // buscar comprobantes de canjes.
     $sqlCanjes="SELECT "           
                . "ccanje.id_sp_comprobante_canje AS id,"
                . "ccanje.id_sp_comprobante_canje AS nro_comp,"
                . "'CANJE' AS tipo_comp,"
                . "DATE_FORMAT(ccanje.fecha,'%Y%m%d') AS fechaa,"
                . "DATE_FORMAT(ccanje.fecha,'%d/%m/%Y') AS fechab,"
                . "ccanje.puntaje_consumido_total AS puntos,"
                . "ccanje.estado,"            
                . "CONCAT(cliente.nombre_cliente,' (id: ',cliente.id_manual_cli, ')' ) AS clienteh ,"
                . "COUNT(p.id_premios_canje) AS premios "           
            . "FROM sp_comprobante_canje AS ccanje "
            . "LEFT JOIN cliente ON cliente.Codigo=ccanje.id_cliente "
            . "LEFT JOIN sp_premios_canje AS p ON p.id_sp_comprobante_canje=ccanje.id_sp_comprobante_canje "            
           . " WHERE ccanje.anulado='No' ".$whereC." "
            . "GROUP BY ccanje.id_sp_comprobante_canje "
            . "ORDER BY ccanje.fecha ASC,ccanje.id_sp_comprobante_canje ASC";
    $hacerC= mysqli_query($connV,$sqlCanjes) or die('No se pueden recuperar los formularios de canje'.mysqli_error($connV).'<pre>'.$sqlCanjes.'</pre>');
    $arrCanjes=array();
    while($cc = mysqli_fetch_assoc($hacerC)){
        $key=$cc["fechaa"];
        $arrMov[$key][]= $cc;
    }
    
    //echo "<pre>";
//    print_r($arrMov);
//    echo "</pre>";
    
    
    $html ='<thead>';
    $html .='<tr>';
    $html .='<th>Fecha</th>';
    if(!$cliente){
        $html .='<th>Cliente</th>';
    }
    $html .='<th>Comp</th>';
    $html .='<th>Nro</th>';
    $html .='<th>Imp.Neto</th>';
    $html .='<th>Puntos</th>';
    $html .='<th>Estado</th>';
    $subtotalDinero=0;
    $subtotalPuntos=0;
    
    // armo el html de vuelta
    if(!empty($arrMov)){
        $html .='<tbody>';
       
        foreach($arrMov as $kf =>$fecha){
            // me fijo si soy nca ncb 
            //print_r($kf);
            //para cada fecha debo recorrer
            foreach($fecha AS $mov){
                //print_r($mov);
                $multi=1;
                if($mov["tipo_comp"]=='NCA' OR $mov["tipo_comp"]=='NCB' OR $mov["tipo_comp"]=='CANJE'){
                    $multi=-1;
                }
                // canjes
                if($mov["tipo_comp"]=='CANJE'){
                    $subtotalPuntos +=$mov["puntos"]*$multi;
                    //$subtotalDinero +=$mov["monto_neto"]*$multi;
                    $html .='<tr>';
                    $html .='<td data-order="'.$mov['fechaa'].'">'.$mov["fechab"].'</td>';
                    if(!$cliente){
                        $html .='<td>'.$mov["clienteh"].'</td>';
                    }
                    $html .='<td>'.$mov["tipo_comp"].'</td>';
                    $html .='<td>'.str_pad($mov["nro_comp"],8,'0',STR_PAD_LEFT ).'</td>';
                    $html .='<td class="dt-right" >0</td>';
                    $html .='<td class="dt-center">'.number_format($mov["puntos"]*$multi,0).'</td>';
                    $html .='<td>'.$mov["estado"].'</td>';
                    $html .='</tr>';
                }
                
                // comprobantes fiscales
                if($mov["tipo_comp"]!='CANJE'){
                    $subtotalPuntos +=$mov["puntos_acumulados"]*$multi;
                    $subtotalDinero +=$mov["monto_neto"]*$multi;
                    $html .='<tr>';
                    $html .='<td data-order="'.$mov['fechaa'].'">'.$mov["fechab"].'</td>';
                    if(!$cliente){
                        $html .='<td>'.$mov["clienteh"].'</td>';
                    }
                    $html .='<td>'.$mov["tipo_comp"].'</td>';
                    $html .='<td>'.$mov["nro_comp"].'</td>';
                    $html .='<td class="dt-right" >'.number_format($mov["monto_neto"]*$multi,2,',','.').'</td>';
                    $html .='<td class="dt-center">'.number_format($mov["puntos_acumulados"]*$multi,0).'</td>';
                    $html .='<td>Efectuado</td>';
                    $html .='</tr>';
                }
            }
            // revisar si hay canjes en esta fecha 
            
        }
        $html .='</tbody>';
        // footer 
        $html .='<tfooter>';
        $html .='<tr>';
        $html .='<td>Total</td>';
        $html .='<td></td>';
        $html .='<td></td>';
        if(!$cliente){
            $html .='<td></td>';
        }
        $html .='<td class="dt-right">'.number_format($subtotalDinero,2,',','.').'</td>';
        $html .='<td class="dt-center">'.number_format($subtotalPuntos,0).'</td>';
        $html .='<td></td>';
        $html .='</tr>';
        $html .='</tfooter>';
    }
    
    // no encontre resultados.
    if(empty($arrMov)){
        $html .='<tbody><tr><td colspan="'.$colSpan.'">No se encontraron movimentos.</td></tr></tbody>';
    }
    
    echo $html;
}

// canjes 

function trae_canjes_comprobantes($connV,$cliente,$arrFiltros){
    // Solicitado/Entregado/No entregado/Anulado
    $whereC="";
    $colSpan=7;
    if($cliente){
        
        $whereC .=" AND ccanje.id_cliente=".$cliente;
    }
    if(isset($arrFiltros["fecha"])){
        $whereC.= " AND (ccanje.fecha BETWEEN '".$arrFiltros["fecha"]["desde"]."' ";
        $whereC .=" AND '".$arrFiltros["fecha"]["hasta"]."')";
        
    }
    if(isset($arrFiltros["estado"])){
        $whereC .=" AND ccanje.estado='".$arrFiltros["estado"]."' ";
    }
    
    $sqlCanjes="SELECT "           
                . "ccanje.id_sp_comprobante_canje AS id,"
                . "ccanje.id_sp_comprobante_canje AS nro_comp,"
                . "'CANJE' AS tipo_comp,"
                . "DATE_FORMAT(ccanje.fecha,'%Y%m%d') AS fechaa,"
                . "DATE_FORMAT(ccanje.fecha,'%d/%m/%Y %h:%i') AS fechab,"
                . "ccanje.puntaje_consumido_total AS puntos,"
                . "ccanje.estado,"
                . "ccanje.tipo_canje,"            
                . "CONCAT(cliente.nombre_cliente,' (id: ',cliente.id_manual_cli, ')' ) AS clienteh ,"
                . "COUNT(p.id_premios_canje) AS premios, "
                . "CONCAT(usuarios.apellido_usuario ,' ',usuarios.nombre_usuario) AS vendedor "           
            . "FROM sp_comprobante_canje AS ccanje "
            . "LEFT JOIN cliente ON cliente.Codigo=ccanje.id_cliente "
            . "LEFT JOIN sp_premios_canje AS p ON p.id_sp_comprobante_canje=ccanje.id_sp_comprobante_canje "
            . "LEFT JOIN usuarios ON usuarios.id_usuario=ccanje.id_usuario"                      
           . " WHERE ccanje.anulado='No' ".$whereC." "
            . "GROUP BY ccanje.id_sp_comprobante_canje "
            . "ORDER BY ccanje.fecha ASC,ccanje.id_sp_comprobante_canje ASC";
    $hacerC= mysqli_query($connV,$sqlCanjes) or die('No se pueden recuperar los formularios de canje'.mysqli_error($connV).'<pre>'.$sqlCanjes.'</pre>');
    $arrMov=array();
    
    while($cc = mysqli_fetch_assoc($hacerC)){
        //$key=$cc["fechaa"];
        $arrMov[]= $cc;
    }
    $html ='<thead>';
    $html .='<tr>';
    $html .='<th>Fecha</th>';
    $html .='<th>Comp</th>';   
    $html .='<th>Nro</th>';
    if(!$cliente){
        $html .='<th>Cliente</th>';
    }
    $html .='<th>Puntos</th>';
    $html .='<th>Premios</th>';
    $html .='<th>Estado</th>';
    $html .='<th>Accion</th>';
    $html .='<th>Usuario</th>';
    $html .='<th>Anular</th>';
    $subtotalPuntos=0;
    $cantPremios=0;
    //die(print_r($arrMov));
    // armo el html de vuelta
    if(!empty($arrMov)){
       
        $html .='<tbody>';
       
        foreach($arrMov as $mov){
            // me fijo si soy nca ncb 
            
            $subtotalPuntos +=$mov["puntos"];
            $cantPremios +=$mov["premios"];
            $html .='<tr>';
            $html .='<td data-order="'.$mov['fechaa'].'">'.$mov["fechab"].'</td>'.PHP_EOL;
            
            $html .='<td>'.$mov["tipo_comp"].'</td>'.PHP_EOL;
            $html .='<td>'.str_pad($mov["nro_comp"],8,'0',STR_PAD_LEFT ).'</td>'.PHP_EOL;
            $numerito="'".str_pad($mov["nro_comp"],8,'0',STR_PAD_LEFT )."'";
            if(!$cliente){
                $html .='<td>'.$mov["clienteh"].'</td>'.PHP_EOL;
            }            
            $html .='<td class="dt-center">'.number_format($mov["puntos"],0).'</td>';
            $html .='<td class="dt-center"><a href="javascript:void(0)" onclick="ver_premios('.$mov["id"].');"> <strong>'.$mov["premios"].' <i class="fas fa-gift fa-lg fa-fw"></i></strong></a></td>';            
            $html .='<td>'.$mov["estado"].'</td>'.PHP_EOL; 
            // segun el estado
            if($mov["estado"]=='Solicitado'){
                $html .='<td>';
                $html .='<a href="javascript:void(0)" onclick="cambiar_estado('.$mov["id"].',0,'.$numerito.');"><i class="fas fa-check fa-lg fa-fw"></i> Entregar</a><br> ';
                $html .='<a href="javascript:void(0)" style=" white-space: nowrap;" onclick="cambiar_estado('.$mov["id"].',1,'.$numerito.');"> <i class="fas fa-times fa-lg fa-fw"></i>No Entregar</a><br>';
                $html .='<a href="javascript:void(0)" onclick="mandar_email_canje('.$mov["id"].');"> <i class="fas fa-envelope fa-lg fa-fw"></i>E-mail</a>';
                $html .='</td>'.PHP_EOL; 
            } 
            if($mov["estado"]!='Solicitado'){
                $html .='<td>';              
                $html .='</td>'.PHP_EOL; 
            }
            if($mov['tipo_canje']=='Vendedor'){
                $html .='<td>'.$mov["vendedor"].'</td>'.PHP_EOL;
            }
            if($mov['tipo_canje']!='Vendedor'){            
                $html .='<td></td>'.PHP_EOL;
            }
            if(1){
                $html .='<td>';
                $html .='<center><a style="text-decoration:none;" href="javascript:void(0)" title="Anular" onclick="anular('.$mov["id"].',\''.$mov['clienteh'].'\');"><i class="fas fa-trash fa-lg fa-fw"></i></a></center> ';
                
                $html .='</td>'.PHP_EOL;   
                            
            }
            
            $html .='</tr>'.PHP_EOL;
        }
        $html .='</tbody>';
        // footer 
        $html .='<tfooter>';
        $html .='<tr>';
        $html .='<td>Total</td>';
        $html .='<td></td>';
        $html .='<td></td>';
        if(!$cliente){
            $html .='<td></td>';
        }
       
        $html .='<td class="dt-center">'.number_format($subtotalPuntos,0).'</td>';
       $html .='<td class="dt-center">'.number_format($cantPremios,0).'<i class="fas fa-gift fa-lg fa-fw"></i></td>';
        $html .='<td></td>';
        $html .='<td></td>';
        $html .='<td></td>';
        $html .='</tr>';
        $html .='</tfooter>';
    }
    
    // no encontre resultados.
    if(empty($arrMov)){
        $html .='<tbody></tbody>';
    }
    
    echo $html;
}

function trae_canjes_comprobantes_detallado($connV,$cliente,$arrFiltros){
    $whereC="";
    $colSpan=7;
    if($cliente){
        
        $whereC .=" AND ccanje.id_cliente=".$cliente;
    }
    if(isset($arrFiltros["fecha"])){
        $whereC.= " AND (ccanje.fecha BETWEEN '".$arrFiltros["fecha"]["desde"]."' ";
        $whereC .=" AND '".$arrFiltros["fecha"]["hasta"]."')";
        
    }
    if(isset($arrFiltros["estado"])){
        $whereC .=" AND ccanje.estado='".$arrFiltros["estado"]."' ";
    }
    
    $sqlCanjes="SELECT "           
                . "ccanje.id_sp_comprobante_canje AS id,"
                . "ccanje.id_sp_comprobante_canje AS nro_comp,"
                . "'CANJE' AS tipo_comp,"
                . "DATE_FORMAT(ccanje.fecha,'%Y%m%d') AS fechaa,"
                . "DATE_FORMAT(ccanje.fecha,'%d/%m/%Y %h:%i') AS fechab,"
                . "ccanje.puntaje_consumido_total AS puntos,"
                . "ccanje.estado,"
                . "ccanje.tipo_canje,"            
                . "CONCAT(cliente.nombre_cliente,' (id: ',cliente.id_manual_cli, ')' ) AS clienteh ,"
                . "p.id_abm_premios AS idpremio, "            
                . "pr.nombre_premios AS premio, "
                . "p.cantidad AS cantidad, "
                . "CONCAT(usuarios.apellido_usuario ,' ',usuarios.nombre_usuario) AS vendedor "           
            . "FROM sp_comprobante_canje AS ccanje "
            . "LEFT JOIN cliente ON cliente.Codigo=ccanje.id_cliente "
            . "LEFT JOIN sp_premios_canje AS p ON p.id_sp_comprobante_canje=ccanje.id_sp_comprobante_canje "
            . "LEFT JOIN sp_abm_premios AS pr ON pr.id_abm_premios=p.id_abm_premios "
            . "LEFT JOIN usuarios ON usuarios.id_usuario=ccanje.id_usuario "                      
           . " WHERE ccanje.anulado='No' ".$whereC." "
            
            . "ORDER BY ccanje.fecha ASC,ccanje.id_sp_comprobante_canje ASC";
    $hacerC= mysqli_query($connV,$sqlCanjes) or die('No se pueden recuperar los formularios de canje'.mysqli_error($connV).'<pre>'.$sqlCanjes.'</pre>');
    $arrMov=array();
    while($cc = mysqli_fetch_assoc($hacerC)){
        //$key=$cc["fechaa"];
        $arrMov[]= $cc;
    }
    $html ='<thead>';
    $html .='<tr>';
    $html .='<th>Fecha</th>';
    $html .='<th>Comp</th>';   
    $html .='<th>Nro</th>';
    if(!$cliente){
        $html .='<th>Cliente</th>';
    }
    $html .='<th>Puntos</th>';
    $html .='<th>IdPremio</th>';
    $html .='<th>Premio</th>';
    $html .='<th>Cantidad</th>';
    $html .='<th>Estado</th>';
//    $html .='<th>Accion</th>';
    $html .='<th>Usuario</th>';
    $subtotalPuntos=0;
    $cantPremios=0;
    
    // armo el html de vuelta
    if(!empty($arrMov)){
        $html .='<tbody>';
       
        foreach($arrMov as $mov){
            // me fijo si soy nca ncb 
            
            $subtotalPuntos +=$mov["puntos"];
//            $cantPremios +=$mov["premios"];
            $html .='<tr>';
            $html .='<td data-order="'.$mov['fechaa'].'">'.$mov["fechab"].'</td>';
            
            $html .='<td>'.$mov["tipo_comp"].'</td>';
            $html .='<td>'.str_pad($mov["nro_comp"],8,'0',STR_PAD_LEFT ).'</td>';
            $numerito="'".str_pad($mov["nro_comp"],8,'0',STR_PAD_LEFT )."'";
            if(!$cliente){
                $html .='<td>'.$mov["clienteh"].'</td>';
            }            
            $html .='<td class="dt-center">'.number_format($mov["puntos"],0).'</td>';
            $html .='<td class="dt-center">'.$mov["idpremio"].'</td>';            
            $html .='<td class="dt-left">'.$mov['premio'].'</td>';
            $html .='<td class="dt-center">'.number_format($mov["cantidad"],0).'</td>';                        
            $html .='<td>'.$mov["estado"].'</td>'; 
            // segun el estado
//            if($mov["estado"]=='Solicitado'){
//                $html .='<td>';
//                $html .='<a href="javascript:void(0)" onclick="cambiar_estado('.$mov["id"].',0,'.$numerito.');"><i class="fas fa-check fa-lg fa-fw"></i> Entregar</a><br> ';
//                $html .='<a href="javascript:void(0)" onclick="cambiar_estado('.$mov["id"].',1,'.$numerito.');"> <i class="fas fa-times fa-lg fa-fw"></i>No Entregar</a>';
//                $html .='</td>'; 
//            } 
//            if($mov["estado"]!='Solicitado'){
//                $html .='<td>';              
//                $html .='</td>'; 
//            }
            if($mov['tipo_canje']=='Vendedor'){
                $html .='<td>'.$mov["vendedor"].'</td>';
            }
            if($mov['tipo_canje']!='Vendedor'){            
                $html .='<td></td>';
                
            }
            
            $html .='</tr>';
        }
        $html .='</tbody>';
        // footer 
        $html .='<tfooter>';
        $html .='<tr>';
        $html .='<td>Total</td>';
        $html .='<td></td>';
        $html .='<td></td>';
        if(!$cliente){
            $html .='<td></td>';
        }
       
        $html .='<td class="dt-center">'.number_format($subtotalPuntos,0).'</td>';
       $html .='<td class="dt-center"></td>';
       $html .='<td class="dt-center"></td>';
       $html .='<td class="dt-left"></td>';
        $html .='<td></td>';
//        $html .='<td></td>';
        $html .='<td></td>';
        $html .='</tr>';
        $html .='</tfooter>';
    }
    
    // no encontre resultados.
    if(empty($arrMov)){
        $html .='<tbody></tbody>';
    }
    
    echo $html;
}

// funcion para traer de a requerimiento el detalle de canje
function trae_detalle_canje_json($connV,$idCanje){
    $vuelta=array();
    $sqlPremios = "SELECT"
                  ."  premio.nombre_premios AS nombre,"                  
                  ."  FORMAT(canje.puntos_premio,0) AS puntos,"
                  ."  canje.cantidad AS cantidad,"
                  ."  FORMAT(canje.puntos_consumido,0) AS total"
                  ."  FROM sp_premios_canje AS canje"
                  ."  LEFT JOIN sp_abm_premios AS premio ON premio.id_abm_premios=canje.id_abm_premios"
                  ."  WHERE canje.id_sp_comprobante_canje=".$idCanje.";";
    $hacerP= mysqli_query($connV, $sqlPremios);
    if($hacerP){
        $vuelta['msg']='ok';
        while($p= mysqli_fetch_assoc($hacerP)){
            $vuelta['premios'][]=$p;
        }
        $vuelta['comprobante']=str_pad($idCanje,8,'0',STR_PAD_LEFT );
    }
    
    if(!$hacerP){
        $vuelta['msg']='error';
        $vuelta['desc']='no puedo recuperar detalle canje de premios.'.mysqli_error($connV).'<pre>'.$sqlPremios.'</pre>';
    }
    print json_encode($vuelta);
}


function cambia_estado_canje($connV,$idCanje,$estado){
    $vuelta=array();
    $sql="UPDATE sp_premios_canje AS ccanje SET "
            . "ccanje.estado='".$estado."' "
            . "WHERE ccanje.id_sp_comprobante_canje='".$idCanje."';";
    $hacer= mysqli_query($connV,$sql);
    if($hacer){
        $vuelta['msg']="ok";
    }
    if(!$hacer){
        $vuelta['msg']='error';
        $vuelta['desc']='no puedo cambiar estado del canje.'.mysqli_error($connV).'<pre>'.$sql.'</pre>';
    }
    print json_encode($vuelta);
    
    
}

function trae_saldos_cliente($connV){
    
  
    
    // buscar comprobantes que generan puntos
    
    $sqlPuntos="SELECT "
            . "cliente.id_manual_cli As cod,"
            . "cliente.nombre_cliente AS nombre,"            
            . "saldoc.saldo_premios AS puntos "            
            . "FROM sp_saldo_cliente_premios AS saldoc "
            . "LEFT JOIN cliente ON cliente.Codigo=saldoc.id_cliente "            
            . "ORDER BY cliente.nombre_cliente ASC";
    $hacerP=mysqli_query($connV,$sqlPuntos) or die("error historial puntos ".mysqli_error($connV).' <pre>'.$sqlPuntos.'</pre>');
    $arrMov=array();
    while($d= mysqli_fetch_assoc($hacerP)){
       
        $arrMov[]=$d;
        
    }
    
    // buscar comprobantes de canjes.
    
    $html ='<thead>';
    $html .='<tr>';
    $html .='<th>Id</th>';    
    $html .='<th>Cliente</th>';
    $html .='<th>Puntos</th>';
    $subtotalPuntos=0;
    
    // armo el html de vuelta
    if(!empty($arrMov)){
        $html .='<tbody>';
       
        foreach($arrMov as $kf =>$p){
            // me fijo si soy nca ncb 
            //print_r($kf);
            //para cada fecha debo recorrer
           
            $subtotalPuntos +=$p["puntos"];
            //$subtotalDinero +=$mov["monto_neto"]*$multi;
            $html .='<tr>';
            $html .='<td>'.$p["cod"].'</td>';                    
            $html .='<td>'.$p["nombre"].'</td>';
            $html .='<td class="dt-center">'.number_format($p["puntos"],0,"",".").'</td>';                    
            $html .='</tr>';
        }
            // revisar si hay canjes en esta fecha 
        $html .='</tbody>';
        $html .='<tfooter>';
        $html .='<tr>';
        $html .='<td></td>';
        $html .='<td>Total</td>';        
        $html .='<td class="dt-center">'.number_format($subtotalPuntos,0,"",".").'</td>';        
        $html .='</tr>';
        $html .='</tfooter>';
            
    }
        
      
    
    echo $html;
        
    
}


/*
 * CONTROLADOR
 * =============================================================================
 */

// inicial historial puntos
if(isset($_REQUEST["inicioHistorial"])&&$_REQUEST["inicioHistorial"]==1){
    $cliente=$_REQUEST["cliente"];
    $desde= date('Y-m-d', strtotime($Date. ' - 7 days'));
    $hasta=date('Y-m-d'); 
    $arrFiltros["fecha"]["desde"]=$desde;
    $arrFiltros["fecha"]["hasta"]=$hasta;
    //print_r($arrFiltros);
    trae_historial_puntos($connV, $cliente, $arrFiltros);
}

// consulta historial de puntos
if(isset($_REQUEST["consultaHistorial"])&& $_REQUEST["consultaHistorial"]==1){
    $cliente=null;
    if($_REQUEST["cliente"]!=="todos"){
        $cliente=$_REQUEST["cliente"];
    }
    $desde= $_REQUEST["desde"];
    $hasta=$_REQUEST["hasta"]; 
    $arrFiltros["fecha"]["desde"]=$desde;
    $arrFiltros["fecha"]["hasta"]=$hasta;
    if($_REQUEST["queInforme"]=="historial"){
        trae_historial_puntos($connV, $cliente, $arrFiltros);
    }
    if($_REQUEST["queInforme"]=="listadoPuntos"){
        trae_saldos_cliente($connV);
    }
}


//consulta canjes de premios.
if(isset($_REQUEST["consultaCanjes"])&& $_REQUEST["consultaCanjes"]==1){
    $cliente=null;
    if($_REQUEST["cliente"]!=="todos"){
        $cliente=$_REQUEST["cliente"];
    }
    $desde= $_REQUEST["desde"];
    $hasta=$_REQUEST["hasta"]; 
    if($_REQUEST["estadoCanje"]!=="todos"){
        $arrFiltros["estado"] = $_REQUEST["estadoCanje"];
    }
    $arrFiltros["fecha"]["desde"]=$desde;
    $arrFiltros["fecha"]["hasta"]=$hasta;

    if($_REQUEST["tipo"]=="simple"){
        trae_canjes_comprobantes($connV, $cliente, $arrFiltros);
    }
    if($_REQUEST["tipo"]=="detallado"){
        trae_canjes_comprobantes_detallado($connV, $cliente, $arrFiltros);
    }
    
}


// consulta detalle del canje

if(isset($_REQUEST["detalleCanje"])&&$_REQUEST["detalleCanje"]==1){
    $idCanje=$_REQUEST["idCanje"];
    trae_detalle_canje_json($connV, $idCanje);
    
}


// cambiar el estado de un canje
 if (isset($_REQUEST["cambiaEstado"])&& $_REQUEST["cambiaEstado"]==1){
     $idCanje=$_REQUEST["idCanje"];
     $estado=$_REQUEST["estadoCanje"];
     cambia_estado_canje($connV, $idCanje, $estado);
     
     
 }
 
