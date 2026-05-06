<?php
require_once 'sesion.inc.php';
require_once 'jcart/numero_a_letra.php';
/* 
 * Configuracion del Recibo.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

// datos del cliente para no usar en javascript

if(is_object($_SESSION['cliente'])){
    $clienteObj = $_SESSION['cliente'];
}else{
    $clienteObj = $_SESSION['cliente'][0];
}


    // estructura del recibo
    
 // funcion para evitar el peso por las dudas
function tofloat($num) {
    $dotPos = strrpos($num, '.');
    $commaPos = strrpos($num, ',');
    $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : 
        ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);
   
    if (!$sep) {
        return floatval(preg_replace("/[^0-9]/", "", $num));
    } 

    return floatval(
        preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
        preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
    );
}

   
// trabajar con funciones de sesion como uso leopi

// creo el nuevo recibo 
function nuevo_recibo($tipo,$idPv,$pv,$nro,$cliente,$saldoCliente,$idPcCliente,$connV){
   // no hay recibo lo doy de alta.
//    echo "<pre>";
//    $variables=array($tipo,$pv,$nro,$cliente);
//    print_r($variables);
    $todoBien=1;
    $error="";
    $vector=array();
    if(is_object($_SESSION['cliente'])){
        $clienteObj = $_SESSION['cliente'];
    }else{
        $clienteObj = $_SESSION['cliente'][0];
    }
    
    // el cliente que tengo y el cliente seleccionado no coinciden
    if($clienteObj->Codigo!=$cliente && $clienteObj->Saldo!=$saldoCliente){
        $todoBien++;
        $error .="No coincide cliente actual con cliente de recibo.";
    }
    
    
    
    
 
    
    if(!isset($_SESSION['recibo'])){
        $_SESSION["recibo"]=array();
        $_SESSION["recibo"]["tipo"]=$tipo;
        $_SESSION["recibo"]["nroCompBusq"] = 0;
        $_SESSION["recibo"]["codCliente"] = $cliente;
        $_SESSION["recibo"]["saldoCliente"] = $saldoCliente;
        $_SESSION["recibo"]["idPcCliente"] = $idPcCliente;
        
        $_SESSION["recibo"]["total"]='';
        $nroReciboCompleto="";  
    //    $nroReciboCompleto=0;
        
        //$todoBien = trae_codmov($connV);
        $_SESSION["recibo"]["idPv"]=$idPv;
        
        if($tipo=="Talonario"){
            $okTalonario= verifica_nro_talonario($connV, $pv, $nro);
            if($okTalonario){
                $nroReciboCompleto = str_pad($pv,4,'0',STR_PAD_LEFT);
                $nroReciboCompleto .='-';
                $nroReciboCompleto .=str_pad($nro,8,'0',STR_PAD_LEFT);
                $_SESSION["recibo"]["nroRecibo"] = $nroReciboCompleto;
                $_SESSION["recibo"]["nroCompBusq"] = $nro;
                $_SESSION["recibo"]["nroCompBusq"] = $nro;
                $todoBien=0;
            }else{
                $todoBien=1;
                $error .="Ya existe un recibo con ese punto venta y talonario\n";
            }
        }
    
        if($tipo=="sistema"){
            $_SESSION["recibo"]["nroRecibo"] = '0-0';
            $_SESSION["recibo"]["nroCompBusq"] = 0;
          // $todoBien= numero_recibo_sistema($connV);
           //print_R($_SESSION["recibo"]);
        }
        
        $nroReciboCompleto=$_SESSION["recibo"]["nroRecibo"];
    }
    
    if(isset($_SESSION["recibo"])){
        if(!isset($_SESSION["recibo"]["nroRecibo"])){
            $todoBien++;
            $error .="no se inicio numero de recibo\n";
            unset($_SESSION["recibo"]);
        }
        if(isset($_SESSION["recibo"]["nroRecibo"])){    
            $nroReciboCompleto=$_SESSION["recibo"]["nroRecibo"];
            $todoBien=0;
        }
    }
    
    
    if($todoBien==0){
        $vector['msg']="ok";
        $vector['numero']=$nroReciboCompleto;
    }
    
    if($todoBien>0){
        //fallo reviento la sesion???
        $vector['msg']="fallo";
        $vector['desc']=$error;
        
       
    }
    
    
    print json_encode($vector);
    
}
function verifica_nro_talonario($connV,$nroPv,$nroForm){
//    echo "que recibi???<pre>";
//    print_r($nroPv);
    $nroFormRec = str_pad($nroPv, 4, '0', STR_PAD_LEFT) . "-" . str_pad($nroForm, 8, '0', STR_PAD_LEFT);
    $nroCompBusqForm = $nroForm;

    $sqlControlNum = "SELECT 
                        cuentacliente.CodigoMovimiento 
                      FROM cuentacliente                      
                      WHERE 
                        cuentacliente.tiporec='Talonario'
                        AND cuentacliente.TipoComprobante = 'REC'
                        AND cuentacliente.Anulado='No'
                        AND cuentacliente.NroComprobante ='{$nroFormRec}'
                        AND cuentacliente.NroCompBusq='{$nroForm}'";


    $hacerControl = mysqli_query($connV,$sqlControlNum) or die('No puedo consultar el talonario');
    $resControl = mysqli_num_rows($hacerControl);
    if ($resControl > 0) {
        return false;
    }else{
        return true;
    }
}



function trae_codmov($connV){
    $errores=0;
    $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV));

        $buscoCod=0;
        // recupero el codigo de movimiento
        // es un bucle que deberia evitar o ejecutarse hasta que sea el mismo codigomov
        // en caso de que se cambie de cod mov antes no se pueda pisar.
        while($buscoCod==0){
            $sqlMovi = "SELECT CodigoMovimiento + 1 as CodigoMovNew,CodigoMovimiento FROM codmov WHERE codigo = 1";
            $resultado = mysqli_query($connV,$sqlMovi) or die('No puedo recuperar el codigo de movimiento'.mysqli_error($connV));
            if(!$resultado){
                $errores++;
            }
            // recupero el nuevo codigo de movimiento
            $codMovResult = mysqli_fetch_assoc($resultado);
            $codMov = $codMovResult["CodigoMovNew"];
            $codMovViejo=$codMovResult["CodigoMovimiento"];
            // actualizo el codigo de movimiento en la tabla codigo de movimiento.
            $sqlMoviUp  = "UPDATE codmov 
                            SET CodigoMovimiento=" . $codMov. " 
                            WHERE codmov.codigo=1 AND codmov.CodigoMovimiento=".$codMovViejo.";";
            $resultado = mysqli_query($connV,$sqlMoviUp) or die('No puedo modificar el codigo de movimiento'.mysql_error($connV));
            if(!$resultado){
                $errores++;
            }
            $buscoCod= mysqli_affected_rows($connV);
            // cierro la transaccion.
            if($errores == 0 && $buscoCod!=0){
                $sqlTotal= "COMMIT;";
                $resultado = mysqli_query($connV,$sqlTotal);
                $_SESSION["recibo"]["codmov"]=$codMov;
                //echo "todo bien";
            }else{
                $sqlTotal = "ROLLBACK;";
                $resultado = mysqli_query($connV,$sqlTotal);
                //echo "todo mal";
            }
        }

        // reinicio la transaccion
        return $errores;
}


function numero_recibo_sistema($connV){
    //require_once 'conexion-general.inc.php';
     $usuario = $_SESSION["vendedor"];
    //$codViajante = $usuario->CodViajante;
    $errores=0;
    
//    $idPuntoVenta = $usuario->id_punto_venta;
    $idPuntoVenta =$_SESSION["recibo"]["idPv"];
    
        $sqlTotal = "SET AUTOCOMMIT =0;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
        $sqlTotal = "BEGIN;";
        $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV)); 
        // obtengo el numero de comprobante del pedido

        $sqlTalon = "SELECT * 
                        FROM talonarios 
                        WHERE id_punto_venta = '".$idPuntoVenta."' 
                        AND TipoComprobante = 'REC'";
        $resultado = mysqli_query($connV,$sqlTalon) or die('No puedo recuperar el talonario' . mysqli_error($connV) );
        if(!$resultado){
            $errores++;
        }

        $objTalonario = mysqli_fetch_assoc($resultado);

        if(!empty($objTalonario)){
            $numeroRecibo = str_pad($objTalonario["PV"],4,"0",STR_PAD_LEFT) . "-" . str_pad($objTalonario["Nro"], 8, '0',STR_PAD_LEFT);
            $nroCompBusqRecibo= $objTalonario["Nro"];

            // actualizo el talonario
            $sqlTalonUp = "UPDATE talonarios 
                                SET Nro = ".$objTalonario["Nro"]."+1 
                                WHERE id_punto_venta = '".$idPuntoVenta."' 
                                AND TipoComprobante = 'REC'"; 
            $resultado = mysqli_query($connV,$sqlTalonUp) or die('No puedo actualizar el talonario' . mysqli_error($connV)."<p>".$sqlTalonUp."</p>");
            if(!$resultado){
                $errores++;
            }
        }else{
            $errores++;
        }
        if($errores == 0 ){
                $sqlTotal= "COMMIT;";
                $resultado = mysqli_query($connV,$sqlTotal);
                $_SESSION["recibo"]["nroRecibo"] = $numeroRecibo;
                $_SESSION["recibo"]["nroCompBusq"] = $nroCompBusqRecibo;
                //echo "todo bien";
            }else{
                $sqlTotal = "ROLLBACK;";
                $resultado = mysqli_query($connV,$sqlTotal);
                //echo "todo mal";
        }
    return $errores;    
}
// asigno el tipo de recibo. si imputacion o acuenta.

// saldo a favor o a cuenta si hubiera

function trae_recibos_a_cuenta($connV,$codCliente=null){
    
  //$codCliente=$_SESSION["recibo"]["codCliente"];
  $sqlACuenta="SELECT "
                . "rc.Codigo,"
                . "rc.Estado,"
                . "rc.Saldo,"
                . "rc.TipoComprobante,"
                . "rc.Anulado, "
                . "SUM(rc.Saldo) AS aCuenta "
                . " FROM recibo_factura AS rc "
                . "WHERE "
                .  "rc.Codigo=".$codCliente." "
                .  "AND rc.Estado = 'N/Canc' "
                .  "AND rc.Saldo <> 0 "
                .  "AND (rc.TipoComprobante = 'REC' Or " 
                .  "rc.TipoComprobante = 'NCA' Or " 
                .  "rc.TipoComprobante = 'NCM' Or " 
                .  "rc.TipoComprobante = 'NCE' Or " 
                .  "rc.TipoComprobante = 'NCC' Or " 
                .  "rc.TipoComprobante = 'NCB' Or " 
                .  "rc.TipoComprobante = 'AJC' Or " 
                .  "rc.TipoComprobante = 'INIC') "
                .  "AND rc.Anulado = 'No' "
                . "GROUP BY rc.Codigo ORDER BY rc.NroComprobante"; 
    $hacer = mysqli_query($connV,$sqlACuenta);
    
    if(mysqli_errno($connV)!=0){
        $vector['msg']='error';
        $vector['desc']='No puedo recuperar saldo a cuenta. <pre>'.mysqli_error($connV)."<br>". $sqlACuenta.'/<pre>';
    }else{
            
    
        $rec= mysqli_fetch_array($hacer);

        $saldo=0;
        if(isset($rec["aCuenta"])){
            $saldo +=$rec["aCuenta"];
        }   
        $vector['msg']='ok';

        $vector['acuenta'] = $saldo;
    }
    print json_encode($vector);
 
}


// imputacion busca facturas
function listar_facturas($connV,$desde=null,$hasta=null){
    // si no hay rango para buscar traigo las 5 mas antiguas.
   $codCliente=$_SESSION["recibo"]["codCliente"];
   
    $deQuien = " AND rf.Codigo={$codCliente} ";
    $consulta="";
    if($desde!=null && $hasta!=null){
        //$fed = $_REQUEST['fechaDesde'];
                //$fechaDesde = $fed[2].'/'. $fed[1].'/'.$fed[0];
                //$feh = explode("-",$_REQUEST['fechaHasta']);
        //        $feh =$_REQUEST['fechaHasta'];
                //$fechaHasta = $feh[2].'/'. $feh[1].'/'.$feh[0];
                $fechaDesde = $desde;
                $fechaHasta = $hasta;
                
               // $consulta .=" AND rf.Fecha BETWEEN '".$fechaDesde . "' AND '". $fechaHasta."'";
    }
    
    $sqlFacturas = "SELECT     
                    rf.id_recibo_factura,
                    rf.id_recibo_factura AS id,
                    CONCAT(rf.TipoComprobante,' ',rf.NroComprobante) AS item,
                    rf.TipoComprobante,
                    rf.Fecha,
                    DATE_FORMAT(rf.Fecha,'%d/%m/%Y') AS FechaB,
                    rf.Vencimiento,
                    rf.NroComprobante,
                    rf.CodigoMovimiento,
                    rf.CondVenta,
                    rf.Importe,
                    rf.ImporteNC,
                    rf.Cancelado,
                    rf.Saldo,
                    rf.Neto
                    
                    FROM 
                      recibo_factura AS rf
                    WHERE 
                    rf.Estado = 'N/Canc' AND 
                    (rf.TipoComprobante = 'FA' Or             
                        rf.TipoComprobante = 'FB' Or 
                        rf.TipoComprobante = 'FM' Or 
                        rf.TipoComprobante = 'FC' Or 
                        rf.TipoComprobante = 'FE' Or 
                        rf.TipoComprobante = 'NDA' Or 
                        rf.TipoComprobante = 'NDM' Or 
                        rf.TipoComprobante = 'NDC' Or 
                        rf.TipoComprobante = 'NDE' Or 
                        rf.TipoComprobante = 'NDB' Or             
			rf.TipoComprobante = 'AJD' Or 
                         rf.TipoComprobante = 'INID') 
			AND rf.Saldo <> 0 AND  rf.Anulado = 'No'
                    
                    {$deQuien}
                    {$consulta}
                     
                    ORDER BY rf.Fecha ASC";
    
    //print_r($sqlFacturas);
    $hacer = mysqli_query($connV,$sqlFacturas) or die('No puedo consultar las facturas de imputacion. '.$sqlFacturas);
    $facturas = array();
    if ($hacer) {

        while ($f = mysqli_fetch_assoc($hacer)) {
            $facturas[] = $f;
        }
    }
    print json_encode($facturas);
    
}
// imputacion agrega factura
function imputar_factura($arrImp){
    if(!isset($_SESSION["recibo"]["clase"])){
        $_SESSION["recibo"]["clase"]="imputacion";
    }
    $saldoN=$arrImp["saldo"] - $arrImp["aimputar"];
    $_SESSION["recibo"]["facturas"][ $arrImp["idrecibofactura"]]=$arrImp;
    $_SESSION["recibo"]["facturas"][ $arrImp["idrecibofactura"]]["saldoN"]= $saldoN;
    
    $vector['msg']="ok";
        
    
    print json_encode($vector);
    
    
}

// desimputar una factura

function desimputar_factura($idfactura){
    $vector=array();
    // obtener el array 
    $rec=$_SESSION["recibo"];
    $facturita= $rec["facturas"][$idfactura];
    $vector['msg']='ok';
    $vector['saldoNuevo']= $facturita['saldo'];
    unset($_SESSION["recibo"]["facturas"][$idfactura]);
      print json_encode($vector);
}


// actualiza Valor Recibo
// deuvelve el json
function actualiza_total(){
    
   // unset($_SESSION['recibo']['descuento']);
   $recibo= $_SESSION["recibo"];
//   print_r($recibo);
   $totalRecibo =0;
   $vector['descuento'] = 0; 
   $vector['retencion'] = 0;
   $totalRetencion=0;
   $vector['saldo'] = 0; 
   
   if(isset($recibo["facturas"])){
       foreach($recibo["facturas"] as $f){
          
               //print_r($f);
               $totalRecibo +=$f["aimputar"];
           
           
       }
   }
   if(isset($recibo["clase"])){
       
   }
   // descuento
    if(isset($recibo["descuento"])){
       $vector['descuento']=$recibo["descuento"]["total"]; 
    }
// retenciones
   if(isset($recibo["retencion"])){
       foreach($recibo["retencion"]["lista"] as $k=>$ret){
           $totalRetencion +=$ret['monto'];
       }
       $_SESSION['retencion']['total']=$totalRetencion;
       $vector['retencion'] = $totalRetencion;
   }
    
    $vector['msg']="ok";
    $vector['total']=$totalRecibo;
    $vector['saldo']=$vector['total']-$vector['descuento']-$vector['retencion']; 
    
    print json_encode($vector);
    
}

// devuelve un array
function actualiza_total_array(){
   // unset($_SESSION['recibo']['descuento']);
   $recibo= $_SESSION["recibo"];
   $totalRecibo =0;
   $vector['descuento'] = 0; 
   $vector['retencion'] = 0; 
   
   $vector['saldo'] = 0; 
   
   if(isset($recibo["facturas"])){
       foreach($recibo["facturas"] as $f){
          
               //print_r($f);
               $totalRecibo +=$f["aimputar"];
           
           
       }
   }
   if(isset($recibo["clase"])){
       
   }
   // descuento
    if(isset($recibo["descuento"])){
       $vector['descuento']=$recibo["descuento"]["total"]; 
    }
// retenciones
   if(isset($recibo["retencion"])){
       $vector['retencion'] = $recibo["retencion"]["total"];
   }
    
    $vector['msg']="ok";
    $vector['total']=$totalRecibo;
    $vector['saldo']=$vector['total']-$vector['descuento']-$vector['retencion']; 
    
    return $vector;
    
}

function resumen_facturas_listas(){
    $recibo= $_SESSION["recibo"];
   
   $vector=array();
   $vector['total'] = 0; 
   if(isset($recibo["facturas"])){
       foreach($recibo["facturas"] as $k=>$f){
           //print_R($f);
          
            $vector['resumen'][]= array("factura"=>$f["nrofactura"],
                             "imputado"=>$f["aimputar"],
                             "saldo"=>round($f["saldoN"],2)
                
                );   
            $vector['total'] +=$f["aimputar"];
          
           
       }
   }
   
   
   if(isset($vector['resumen'])){
       $vector['msg']="ok";
   }else{
       $vector['msg']="fallo";
   }
   print json_encode($vector);
   
}

// traer tipos de descuentos
function trae_descuentos($connV){
    $errores=0;
     $vector=array();
    $sql="SELECT porcentaje AS id, CONCAT(porcentaje,'% ',nombreDescuento) AS text FROM descuento_rec WHERE  porcentaje>0 ORDER BY CodDescuento ";
    $hacer=mysqli_query($connV,$sql) or die("no puedo encontrar los descuentos en recibo<pre>".mysqli_error($connV));
    $descuentos=array();
    if($hacer){
        while($d= mysqli_fetch_assoc($hacer)){
            $descuentos[] =$d;
        }
        
    }
    
    //print_r($vector);
    
    if(!$hacer){
        $errores++;
    }
    
    if($errores==0){
        
        $vector['msg']="ok";
        $vector['descuentos']=$descuentos;
    }else{
        $vector['msg']="fallo";
    }
    
    print json_encode($vector);
}

// lista retenciones del cliente
function trae_retencion_cli($connV){
    $errores=0;
     $vector=array();
    $sql="SELECT  rc.CodRetencion AS id, rc.NombreRetencion  AS text FROM tipo_retencion_cli AS rc WHERE rc.Anulado='No' ORDER BY rc.NombreRetencion ASC ";
    $hacer=mysqli_query($connV,$sql) or die("no puedo encontrar las retenciones del en recibo<pre>".mysqli_error($connV));
    $descuentos=array();
    if($hacer){
        while($d= mysqli_fetch_assoc($hacer)){
            $descuentos[] =$d;
        }
        
    }
    
    //print_r($vector);
    
    if(!$hacer){
        $errores++;
    }
    
    if($errores==0){
        
        $vector['msg']="ok";
        $vector['tipoRetencion']=$descuentos;
    }else{
        $vector['msg']="fallo";
    }
    
    print json_encode($vector);
}



// alta de retencion
function alta_retencion($arrRet){
    // agregar la retencion a los datos que hay del recibo
    // preguntar si existe el total y si no sumarle.
    
//    if (!isset($_SESSION['recibo']['retencion']['total'])){
//        $_SESSION['recibo']['retencion']['total']=0;
//    }
   $key=$arrRet["cod"]."-".$arrRet["certificado"];
    $_SESSION['recibo']['retencion']['lista'][$key]=$arrRet;
    
    // recalcular el total de las retenciones.
    $totalRetencion=0;
    $listaRet=$_SESSION['recibo']['retencion']['lista'];
    foreach($listaRet as $kr=> $r){
        $totalRetencion +=$r['monto'];
    }
    $_SESSION['recibo']['retencion']['total']=$totalRetencion;
    $vector['msg']="ok";
    print json_encode($vector);
}

// borrar retenciones
function borrar_retencion($key){
    // averiguo si existe 
    if(isset($_SESSION['recibo']['retencion']['lista'][$key])){
        //existe la elimino.
        UNSET($_SESSION['recibo']['retencion']['lista'][$key]);
    }
    $vector['msg']="ok";
    print json_encode($vector);
}

// vaciar retenciones

function vaciar_retenciones(){
    if(isset($_SESSION['recibo']['retencion'])){
        UNSET($_SESSION['recibo']['retencion']);
    }
    $vector['msg']="ok";
    print json_encode($vector);
}


function borrar_descuento(){
    unset($_SESSION["recibo"]["descuento"]);
    $vector['msg']="ok";
    print json_encode($vector);
}

function lista_retencion(){
    
    $vector=array();
    if(isset($_SESSION['recibo']['retencion'])){
        $retenciones=$_SESSION['recibo']['retencion'];
        
        foreach($retenciones['lista'] as $k => $r){
            
            $vector["retencion"][]=array(
                            'key'=>$k,
                            'cod'=>$r['cod'],
                            'retencion'=>$r['tipo'],
                            'certificado'=>$r['certificado'],
                            'porcentaje'=>$r['porcentaje'],
                            'monto'=>$r['monto']);
        }
        $vector["msg"]="ok";
        
    }else{
        $vector["msg"]="no";
    }
    print json_encode($vector);
}
// bajar retencion


function alta_descuento($porcentaje){
    
    $arrRecibo = actualiza_total_array();
    
    
    $total = ($arrRecibo["saldo"]*$porcentaje/100);
    
    $_SESSION["recibo"]["descuento"]=array('porcentaje'=>$porcentaje,'total'=>$total);
    $vector['msg']="ok";
    print json_encode($vector);
}

// funciones de EFECTIVO
//==============================================================================

// trae la caja efectivo
// -----------------------------------------------------------------------------
function trae_caja_efectivo($connV){
   $vector=array();
   $errores=0;
    $idCaja=$_SESSION["id_caja_efectivo_usr"];      
    $sqlCaja="SELECT cj.id_caja AS id,cj.nombre_caja AS text,'true' AS selected "
            . "FROM caja_abm AS cj "           
            . "WHERE  "
            . "cj.id_caja = {$idCaja} "
            . "ORDER BY nombre_caja";
    $hacer=mysqli_query($connV,$sqlCaja) or die("no puedo encontrar la caja efectivo<pre>".mysqli_error($connV));
    
    $cajas=array();
    if($hacer){
        while($d= mysqli_fetch_assoc($hacer)){
            $cajas[] =$d;
        }
        
    }
    
    //print_r($vector);
    
    if(!$hacer){
        $errores++;
    }
    
    if($errores==0){
        
        $vector['msg']="ok";
        $vector['caja']=$cajas;
    }else{
        $vector['msg']="fallo";
    }
    
    print json_encode($vector);
}
// trae la cotizacion del dolar
// -----------------------------------------------------------------------------
function trae_coti_dolar($connV){
    $vector=array();
    $errores=0;
    $sql="SELECT cotizacion.ValorPesos AS valor "
            . "FROM cotizacion";
    $hacer=mysqli_query($connV,$sql) or die("no puedo encontrar la cotizacuion del dolar<pre>".mysqli_error($connV));
    
    if($hacer){
        while($d= mysqli_fetch_assoc($hacer)){
            $dolar =$d["valor"];
        }
        
    }
    
    //print_r($vector);
    
    if(!$hacer){
        $errores++;
    }
    
    if($errores==0){
        
        $vector['msg']="ok";
        $vector['cotizacion']=$dolar;
    }else{
        $vector['msg']="fallo";
    }
    
    print json_encode($vector);
    
}


// trae el total inicial del recibo para efectivo

//function total_recibo_efectivo() {
//
//    $recibo = $_SESSION["recibo"];
//    //print_r($recibo);
//    $arrRecibo = actualiza_total_array();
//    //print_r($arrRecibo);
//
//    $vector['total'] = 0;
//    $vector['efectivo'] = 0;
//    $vector['cheque']=0;
//    $vector['saldo'] = 0;
//    $totalRecibo = $arrRecibo["saldo"];
//    if (isset($recibo["efectivo"]["total"])) {
//        $vector['efectivo'] = $recibo["efectivo"]["total"];
//    }
//    
//    if(isset($recibo["cheque"]["total"])){
//        $vector['cheque'] = $recibo["cheque"]["total"];
//    }
//
//    $vector['msg'] = "ok";
//    $vector['total'] = $totalRecibo;
//    $vector['saldo'] = $vector['total'] - ($vector['efectivo']+$vector['cheque']);
//
//    print json_encode($vector);
//}

// guardar el cobro efectivo 
// ----------------------------------------------------------------------------
function alta_efectivo($arrEfe){
    // guardar datos de la caja
    $vector=array();
    
    $_SESSION['recibo']['efectivo']['idCaja'] = $arrEfe["idcaja"];
    $_SESSION['recibo']['efectivo']['pesos']=0;
    $_SESSION['recibo']['efectivo']['dolar']=0;
    $_SESSION['recibo']['efectivo']['cotizacion']=1;
    $totalEfectivo=0;    
    // veamos si hay plata pesos
    if(isset($arrEfe["efectivo"])){
        $_SESSION['recibo']['efectivo']['pesos'] = floatval($arrEfe["efectivo"]);
        $totalEfectivo +=floatval($arrEfe["efectivo"]);
    }
    // veamos si hay plata dolares
    
    if(isset($arrEfe["dolar"])){
        $_SESSION['recibo']['efectivo']['dolar']=$arrEfe["dolar"];
        $_SESSION['recibo']['efectivo']['cotizacion']=$arrEfe["coti"];
        $totalEfectivo +=floatval($arrEfe["dolar"]*$arrEfe["coti"]);
    }
    
    $_SESSION['recibo']['efectivo']['total']= $totalEfectivo;
    $vector['msg']="ok";
    print json_encode($vector);
    
}

function borrar_efectivo($tipo){
    $vector=array();
    
    
    if(isset($_SESSION['recibo']['efectivo'])){
        $totalEfectivo =$_SESSION['recibo']['efectivo']['total'];
        if($tipo=="dolar"){
            $totalEfectivo = $totalEfectivo -($_SESSION['recibo']['efectivo']['dolar'] * $_SESSION['recibo']['efectivo']['cotizacion']);
            unset($_SESSION['recibo']['efectivo']['dolar']);
            unset($_SESSION['recibo']['efectivo']['cotizacion']);
        }
        if($tipo=="pesos"){
            $totalEfectivo = $totalEfectivo-$_SESSION['recibo']['efectivo']['pesos'];
            unset($_SESSION['recibo']['efectivo']['pesos']);
        }
        if($totalEfectivo==0){       
        
            unset( $_SESSION['recibo']['efectivo']);
        }else{
             $_SESSION['recibo']['efectivo']['total']= $totalEfectivo;
        }
    }
    
    $vector['msg']="ok";
    print json_encode($vector);
}

// funciones de CHEQUE
// =============================================================================

// trae la caja de cheques
// -----------------------------------------------------------------------------

function trae_caja_cheque($connV){
    $vector=array();
    $errores=0;
    $idCaja=$_SESSION["id_caja_cheque_usr"];      
    $sqlCaja="SELECT cj.id_caja AS id,"
            . "cj.nombre_caja AS text,'true' AS selected "
            . "FROM caja_abm AS cj "            
            . "WHERE  "
            . "cj.id_caja = {$idCaja} "
            . "ORDER BY cj.nombre_caja";
    $hacer=mysqli_query($connV,$sqlCaja) or die("no puedo encontrar la caja de cheques<pre>".mysqli_error($connV));
    $cajas=array();
    if($hacer){
        while($d= mysqli_fetch_assoc($hacer)){
            $cajas[] =$d;
        }
        
    }
    
    //print_r($vector);
    
    if(!$hacer){
        $errores++;
    }
    
    if($errores==0){
        
        $vector['msg']="ok";
        $vector['caja']=$cajas;
    }else{
        $vector['msg']="fallo";
    }
    
    print json_encode($vector);
}

// trae BANCOS 
// ----------------------------------------------------------------------------
function trae_bancos($connV){
   $vector=array();
   $errores=0;
    
    $sqlBanco="SELECT b.CodBanco AS id,b.Nombre AS text,b.CUIT AS cuit "
            . "FROM banco AS b "           
            . "WHERE  "
            . "b.anulado='No' "
            . "AND b.CodBanco>1 "
            . "ORDER BY b.Nombre ASC";
    $hacer=mysqli_query($connV,$sqlBanco) or die("no puedo encontrar bancos <pre>".mysqli_error($connV).$sqlBanco);
    
    $bancos=array();
    if($hacer){
        while($d= mysqli_fetch_assoc($hacer)){
            $bancos[] =$d;
        }
        
    }
    
    //print_r($vector);
    
    if(!$hacer){
        $errores++;
    }
    
    if($errores==0){
        
        $vector['msg']="ok";
        $vector['banco']=$bancos;
    }else{
        $vector['msg']="fallo";
    }
    
    print json_encode($vector);
}

// funcion control existencia de cheques.

function control_cheque_existe($arrCh){
    // no hay cheques cargados
    $control= 0;// hay error
    
    // hay cheque cargados debo controlar
    if(isset($_SESSION['recibo']['cheque']['total'])){
       $listaC=$_SESSION['recibo']['cheque']['listado'];
        $claveBusco=$arrCh['codbanco'].'c'.$arrCh['numero'];
       foreach($listaC as $key=>$ch){
           //if($ch['codbanco']==$arrCh['codbanco']&&$ch['numero']==$arrCh['numero']&&$ch['importe']==$arrCh['importe']){
             
           //}
           if($key==$claveBusco){
               $control=1;
           }
       }
    }
    
    if ($control===0){
        // ingresarlo
        return true;
    } 
    if($control!==0){
        // no dar de alta
        return false;
    }
    
}

// funcion alta de cheques
//-----------------------------------------------------------------------------
function alta_cheque($arrCh){
    
    // verificar que no exista el cheque actual 
    // el importe del cheque no puede superar el saldo.
    $control= control_cheque_existe($arrCh);
    
    
    if($control==true){
        
        // si no es el primer cheque aumento el importe total
        if(isset($_SESSION['recibo']['cheques']['total'])){
            $_SESSION['recibo']['cheques']['total'] +=$arrCh['importe'];
        }else{
            // primer cheque
            $_SESSION['recibo']['cheques']['total']=$arrCh['importe'];
        }
        // date create from format y ver como viene el cobro y ahi sumarselo
        $vencimiento=date('Y-m-d',strtotime($arrCh['cobro'].' +30 days'));   
        $clave=$arrCh['codbanco'].'c'.$arrCh['numero'];
        $_SESSION["recibo"]["cheques"]["listado"][$clave]= array(
                                    'codbanco'=>$arrCh['codbanco'], 
                                    'banco'=>$arrCh['banco'], 
                                    'cuitbanco'=>$arrCh['cuitbanco'], 
                                    'librador'=>$arrCh['librador'], 
                                    'cuitlibrador'=>$arrCh['cuitlibrador'], 
                                    'numero'=>$arrCh['numero'], 
                                    'importe'=>$arrCh['importe'], 
                                    'emision'=>$arrCh['emison'], 
                                    'vencimiento'=>$vencimiento,
                                    'cobro'=>$arrCh['cobro'],
                                    'tipo'=>$arrCh['tipo']
                                    );
    }
    calcula_total_cheques();
    $vector=array();
    $vector['msg']="ok";
   // echo print_r($_SESSION["recibo"]["cheques"]["listado"]);
     print json_encode($vector);
}

// total de los cheques pero ademas debo traer caja y todo junto.
// ----------------------------------------------------------------------------
function total_recibo_cheque(){
    $recibo = $_SESSION["recibo"];
//    print_r($recibo);
    $arrRecibo = actualiza_total_array();
//    print_r($arrRecibo);

    $vector['total'] = 0;
    $vector['efectivo'] = 0;
    $vector['cheque']=0;
    $vector['saldo'] = 0;
    
    $totalRecibo = $arrRecibo["saldo"]*1.00;
    $totalEfectivo=0;
    $totalCheque=0;
    $totalSaldo=0;
    if (isset($recibo["efectivo"]["total"])) {
        $totalEfectivo= $recibo["efectivo"]["total"]*1.00;
    }
    // recalculo de los cheques por las dudas haya habido algun error.
    if(isset($recibo["cheques"]["total"])){
        $totalCheque= $recibo["cheques"]["total"]*1.00;
    }
    
    $totalSaldo=floatVal(bcsub($totalRecibo,($totalEfectivo+$totalCheque),2));
//    echo var_dump($totalRecibo);
//    echo var_dump($totalSaldo);
//    echo var_dump($totalRecibo-$totalSaldo);
//    echo var_dump(bcsub($totalRecibo, $totalSaldo));
    $vector['msg'] = "ok";
    $vector['efectivo']=$totalEfectivo;
    $vector['cheque'] = $totalCheque;
    $vector['total'] = $totalRecibo;
    $vector['saldo'] = $totalSaldo;
//    print_r($vector);

    print json_encode($vector);
}
// funcion actualiza total de cheques 
// -----------------------------------------------------------------------------
function calcula_total_cheques(){
     $recibo = $_SESSION["recibo"];
     $arrayCheques=array();
     if(isset($_SESSION["recibo"]["cheques"]["listado"])){
        $arrCheques=$_SESSION["recibo"]["cheques"]["listado"];
        $totalCheques=0;
        if(!empty($arrCheques)){
            $vector['msg']="ok";
            foreach($arrCheques as $k=>$d){
                $totalCheques +=$d["importe"];
            }
        }
    
        $_SESSION['recibo']['cheques']['total'] =$totalCheques;
     }
     
    
    return;
     
}


// funcion listado de cheques 
// -----------------------------------------------------------------------------
function lista_cheques($idCajaCheque){
    $vector=array();
    // los traigo solo cuando haya algo 
    $arrCheques=$_SESSION["recibo"]["cheques"]["listado"];
    
    if(!isset($_SESSION["recibo"]["cheques"]["idCajaCheque"])){
        $_SESSION["recibo"]["cheques"]["idCajaCheque"]= $idCajaCheque;
    }
    
    if(!empty($arrCheques)){
        $vector['msg']="ok";
        foreach($arrCheques as $k=>$d){
//                echo "<pre>";
//            print_r($d);
//            echo "</pre>";
            $vector['cheques'][]=array('cod'=>$k,'numero'=>$d["numero"],'banco'=>$d["banco"],'importe'=>'$'.number_format($d["importe"],2,",","."));
        }
    }else{
        // vector vacio
        $vector['msg']="vacio";
    }
    print json_encode($vector);
}

// borrar un cheque 
function borrar_cheque($arrCh){
    // el array trae el key , el numero y el valor.
    // primero busco la key 
//    echo "<pre>";
//    print_r($_SESSION['recibo']);
//    echo  "</pre>";
    $vector=array();
    $clave=$arrCh["cod"];
    $numero=$arrCh["numero"];
    $importe= tofloat($arrCh["importe"]);
    $cheques=$_SESSION["recibo"]["cheques"]["listado"];
    $total=$_SESSION["recibo"]["cheques"]["total"];
//    echo "<pre>";
//    print_r($cheques);
//    echo  "</pre>";
    if(isset($cheques[$clave])){
//        echo "exite clave<pre>";
//        print_r($importe);
//        var_dump($cheques[$clave]["importe"]==$importe&& $cheques[$clave]["numero"]==$numero);
//        print_r($cheques[$clave]);
//        echo "</pre>";
        if($cheques[$clave]["importe"]==$importe&& $cheques[$clave]["numero"]==$numero){
            // hay que borrar la session.
            $total=bcsub($total , $importe,2);
            unset($_SESSION["recibo"]["cheques"]["listado"][$clave]);
            $_SESSION["recibo"]["cheques"]["total"] = $total;
            $vector["msg"]="ok";
        }
    }else{
        $vector["msg"]="error";
    }
     print json_encode($vector);
}

function vaciar_cheques(){
    if(isset($_SESSION["recibo"]["cheques"])){
        UNSET($_SESSION["recibo"]["cheques"]);
    }
    $vector['msg']="ok";
    print json_encode($vector);
}


// RECIBO RESUMEN 
// ============================================================================


function trae_resumen_recibo(){
    $recibo=$_SESSION["recibo"];
    $vector=array();
    $vector['msg']="ok";
    $vector['tiporec']=$recibo["clase"];
    $vector['resumen'][]=array('campo'=>'cajaCheques:','valor'=> $recibo["cheques"]["idCajaCheque"]);
    
    $vector['resumen'][]=array('campo'=>'Tipo:','valor'=>$recibo["clase"]);
    
    /// analizar aca si hay dinero a cuenta
    print json_encode($vector);
    
    
}

function trae_resumen_imputacion(){
    $vector= array();
    $totalDesc=0;
    $totalRet=0;
     $recibo=$_SESSION["recibo"];
    // con las imputaciones debo traer descuentos y retenciones      
     if(isset($recibo["facturas"])){
        $vector['msg']='ok';
         $cuantas = sizeof($recibo["facturas"]);
         $total = 0;
         foreach($recibo["facturas"] as $c=>$f){
             $total+=$f["aimputar"];
         }
       // facturas  
       $vector['imputacion'][]=array('campo'=>'Facturas:',
                                    'cantidad'=>$cuantas,
                                    'valor'=>'<strong>$'.number_format($total,2,",",".").'</strong>'
           );  
      
       
       
//       // total del recibo general 
//       $vector['imputacion'][]= array('campo'=>'Total',
//                                        'cantidad'=>'',
//                                        'valor'=>'<strong>$'.number_format($total -($totalDesc + $totalRet),2,",",".").'</strong>'
//               );  
       
     }else{
         $vector['msg']="vacio";
     }
    print json_encode($vector);
     
     
}

function trae_resumen_medios(){
    $recibo= $_SESSION["recibo"];
    $totalAcobrar=0;
    $vector=array();
    $vector['msg']='ok';
    // hay plata en efectivo
    if(isset($recibo["efectivo"])){
        // cuanto hay pesos y dolares 
        if(isset($recibo["efectivo"]["pesos"])){
            $vector["medios"][]=array(
                "campo"=>"Pesos:",
                "cantidad"=>"",
                "valor"=>"<strong>$".number_format($recibo["efectivo"]["pesos"],2,",",".").'</strong>'
            );
        }
        // dolar 
        if(isset($recibo["efectivo"]["dolar"])&&is_numeric($recibo["efectivo"]["dolar"])){
            $vector["medios"][]=array(
                "campo"=>"Dólares:",
                "cantidad"=>"",
                "valor"=>'<strong>u$s'.number_format($recibo["efectivo"]["dolar"],2,",",".") .' ($'.number_format(($recibo["efectivo"]["dolar"]*$recibo["efectivo"]["cotizacion"]),2,",",".").')</strong>'
            );
//            $vector["medios"][]=array(
//                "campo"=>"SubTotal:",
//                "cantidad"=>"dolar a peso",
//                "valor"=>'$'.($recibo["efectivo"]["dolar"]*$recibo["efectivo"]["cotizacion"]  )
//            );
        }
        
        $vector["medios"][]=array(
                "campo"=>"<strong>SubTotal Efec:<strong>",
                "cantidad"=>"",
                "valor"=>'<strong>$'.number_format($recibo["efectivo"]["total"],2,",",".").'</strong>'
            );
        $totalAcobrar +=$recibo["efectivo"]["total"];
    }
    
    //cheques    
//    echo "<pre>";
//    print_r($recibo);
    
    if(isset($recibo["cheques"]["listado"])){
        // contar cuantos hay y poner el total
//        recalcular lista de cheques.
        $totalCheques=0;
        foreach($recibo["cheques"]["listado"] as $ch){
            $totalCheques +=$ch["importe"];
        }
        
        $cuantos = count($recibo["cheques"]["listado"]);
        $vector["medios"][]=array(
                "campo"=>"Cheques:",
                "cantidad"=>$cuantos,
                "valor"=>'<strong>$'.number_format($recibo["cheques"]["total"],2,",",".").'</strong>'
            );
        $totalAcobrar +=$recibo["cheques"]["total"];
    }
    
    // retenciones
    // y si hay retenciones
       if(isset($recibo["retencion"])){
           $totalAcobrar+=$recibo["retencion"]["total"];
            $vector["medios"][]=array('campo'=>'Retenciones:',
                                        'cantidad'=>'-',
                                        'valor'=>'<strong>$'.number_format($recibo["retencion"]["total"],2,",",".").'</strong>'
               );  
       }
    
    // descuentos si hay 
       $totalDesc=0;
       if(isset($recibo["descuento"])){
           $totalDesc=$recibo["descuento"]["total"];
           $vector["medios"][]=array('campo'=>'Descuentos:',
                                        'cantidad'=>$recibo["descuento"]["porcentaje"].'%',
                                        'valor'=>'<strong>$'.number_format($recibo["descuento"]["total"]*-1,2,",",".").'</strong>'
               );  
       }
          
       
    // subtotal de cobro.
     $vector["medios"][]=array(
                "campo"=>"Total Recibo:",
                "cantidad"=>"",
                "valor"=>'$'.number_format($totalAcobrar-$totalDesc,2,",",".").''
            );
    // evaluar si hare un recibo a cuenta....
    $a= total_recibo_resumen();
//    echo "<pre>";
//    print_r($a);
//    print_r($recibo);
    
    $plataAcuenta=0;
    foreach($a as $ac){
        if(isset($ac["aCuenta"])){
            $plataAcuenta+=$ac["aCuenta"];
        }
    }
    
    if($plataAcuenta>0){
        $vector["medios"][]=array(
                "campo"=>"A Cuenta:",
                "cantidad"=>"",
                "valor"=>'<strong>$'.number_format($plataAcuenta,2,",",".").'</strong>'
            );
    }
    print json_encode($vector);
}

// devuelvo el total del recibo pero lo guardo en su campo
// total y mando valor a cuenta si hiciera falta. 

function total_recibo_resumen(){
    $recibo=$_SESSION["recibo"];
    $vuelta=array();
    $impu= actualiza_total_array();
    $_SESSION["recibo"]["totalImputado"] = $impu["total"];
//    echo "<pre>";
//    print_r($impu);
    $totalRecibo=0;
    $aCuenta = $impu["saldo"];
    
    if(isset($recibo["efectivo"]["total"])){
//        echo "dentro del efecito<br>";
        $aCuenta= floatval(bcsub($aCuenta,$recibo["efectivo"]["total"],2));
        $totalRecibo+=$recibo["efectivo"]["total"];
    }
    if(isset($recibo["cheques"]["total"])){
//        echo "dentro del cheque<br>";
        $aCuenta= floatval(bcsub($aCuenta,$recibo["cheques"]["total"],2));
        $totalRecibo+=$recibo["cheques"]["total"];
    }

    
    
    if($aCuenta<0){
//        echo "hay plata a cuenta<br>";
        // acuenta negativo debo hacer x imputacion e informarlo.
        $_SESSION["recibo"]["aCuenta"] = ($aCuenta * -1);
        $vuelta[] = array('aCuenta'=>($aCuenta * -1));
    } 
//    echo " total del recibo==>".$totalRecibo;
    $_SESSION["recibo"]["totalAcobrar"] = $impu["saldo"];
    // agregar las retenciones al total del recibo.
    if(isset($recibo["retencion"])){
        $totalRecibo +=$recibo["retencion"]["total"];
    }
    
    $_SESSION["recibo"]["total"]=$totalRecibo;
    $vuelta[] = array('total'=>$impu["total"]);
    return $vuelta;
}

function control_final_recibo(){
    
    $recibo=$_SESSION["recibo"];
//    echo "<pre>";
//    print_r($recibo);
    $vuelta=array();
    // por las dudas voy a sumar de nuevo las facturas a imputar...
    $totalRecibo=$recibo["total"];
    $totalaCobrar=$recibo["totalImputado"];
    $diferencia=floatVal(bcsub($totalaCobrar,$totalRecibo,2));
        // si diferencia positiva y mayor a cero es porque me falta cubrir
        // si la diferencia=0 perfecto he cubierto.
        // si la diferencia me da negativo , hay plata a cuenta todo bien
    if($diferencia >0){
    
        // 
        $vuelta['msg']='error';
        $vuelta['deuda']=$diferencia;
    }else{
        $vuelta['msg'] = 'ok';
         $vuelta['saldo']=$diferencia;
    }
    print json_encode($vuelta);
    
    
}


// FIN DE RECIBO 
function guardar_recibo($connV){
    //ob_start();
if(!isset($_SESSION["recibo"])){
    echo "sin sesion retorno";
    exit();
}

/* TRAER Y GESTIONAR EL CODIGO MOVIMIENTO SOLO ACA.*/
    $todoBien = trae_codmov($connV);
    if($_SESSION["recibo"]["tipo"]=="sistema"){
           $todoBien= numero_recibo_sistema($connV);
    }


//    echo "<pre>";
//    echo "1.INICIANDO RECIBO============><br>";
    $rec=$_SESSION['recibo'];
     $vector=array();
      $vector['msg']='ok';
     $usuario = $_SESSION["vendedor"];
     
//     if(is_object($_SESSION['cliente'])){
//        $cliente = $_SESSION['cliente'];
//    }else{
//        $cliente = $_SESSION['cliente'][0];
//    }
    $codViajante = $usuario->CodViajante;
    $errores=0;
    $idPuntoVenta = $usuario->id_punto_venta; 
    //$saldoCliente = $cliente->saldo;
   $saldoCliente = $rec["saldoCliente"];
   $saldoClienteNuevo= $rec["saldoCliente"];
    //$saldoClienteNuevo= $cliente->saldo;
    // instancio el recibo 
    
    
    if($rec["clase"]=="imputacion"){$clase="Imputacion";}
    if($rec["tipo"]=="sistema"){ $tipo="Sistema";      
    }else{$tipo="Talonario";}
    
    // inicio de totales en CERO para no tener problemas.
    $cotiDolar=1;
    $totalEfectivoP=0;
    $totalEfectivoD=0;
    $totalPago =0;
    $totalCheque=0;
    $totalImputacionRec=0;
    $netoImput=0;
    $totalPagoRec=0;
    $totalRecibo=0;
    $totalDescuento=0;
    $totalRetenciones=0;
    $totalTarjeta=0;
    $totalMedcob=0;
    $totalIngreso=0;
    
// hay plata efectivo
    if(isset($rec["efectivo"])){
        $cotiDolar=$rec["efectivo"]["cotizacion"];
        $totalEfectivoP=$rec["efectivo"]["pesos"];
        $totalEfectivoD=$rec["efectivo"]["dolar"];
        $totalPago += $rec["efectivo"]["total"];
    
    }
    // cheques 
    if( isset($rec["cheques"]["total"])){
        $totalCheque +=$rec["cheques"]["total"];
    }
    // totales y subtotales
    if(isset($rec["facturas"])){
        $totalImputacionRec+=$rec["totalImputado"];
    }
    // descuento
    if(isset($rec["descuento"])){
        
        $netoImput=$totalImputacionRec -$rec["descuento"]["total"]; 
        $totalDescuento=$rec["descuento"]["total"]; 
    }
    // retencion
    if(isset($rec["retencion"])){
        $totalRetenciones+=$rec["retencion"]["total"];
    }
    
    
    $totalMedcob += ($totalPago+$totalCheque);
    $totalPagoRec += ($totalPago+$totalCheque);
    // el total del recibo es el total de lo que se paga.
    $totalRecibo+=$totalPagoRec + $totalRetenciones;
//    $totalRecibo = ($totalImputacionRec - $totalDescuento);
    $saldoClienteNuevo = ($saldoCliente-$totalRecibo);
    
//    echo "2.INICIANDO TRANSACCION============><br>";
    //echo var_dump($connV);
    // INICIO TRANSACCIONES
    // ========================================================================
    $sqlTotal = "SET AUTOCOMMIT =0;";
    $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo iniciar autocommit '.mysqli_error($connV));
    $sqlTotal = "BEGIN;";
    $resultado = mysqli_query($connV,$sqlTotal) or die('No puedo hacer Begin '.mysqli_error($connV));
    $errorT=0;
    $arrError=array();
    $hacer=null;
    // INSERTO CUENTA CLIENTE DEL RECIBO
    // =========================================================================
    
    $sqlCuCliente = "INSERT INTO cuentacliente SET "
                    ."Fecha='".date('Y/m/d')."', "
                    ."TipoComprobante = 'REC', "   
                    ."NroComprobante='".$rec["nroRecibo"]."',"
                    ."NroCompBusq='".$rec["nroCompBusq"]."',"
                    ."id_pv='".$idPuntoVenta."',"
                    ."TipoREC = '".$tipo."',"
                    ."ImporteVentaL='".num2letras($rec["total"])."',"
                    ."Detalle='WEB',"
                    ."anulado='No',"
                    ."ReciboMov=0,"
                    ."ImporteCobro='".$rec["total"]."',"
                    ."ImporteVenta=NULL,"            
                    ."CondVenta= '-',"                    
                    ."idUsuario='".$usuario->id_usuario."',"            
                    ."codSucursal ='".$usuario->id_sucursal."',"            
                    ."Codigo='".$rec["codCliente"]."', " 
                    ."CodigoMovimiento='".$rec["codmov"]."',"
                    ."TipoRecibo='".$clase."',"             
                    ."CotiDolar ='".$cotiDolar."', "            
                    ."ReciboPesos = '".$totalEfectivoP."',"
                    ."ReciboDolar ='".$totalEfectivoD."',"
                    ."TotalPago ='".$totalPagoRec."', "            
                    ."TotalEfectivoP ='".$totalEfectivoP."', "
                    ."TotalEfectivoD ='".$totalEfectivoD."', "
                    ."TotalCheque = '".$totalCheque."', "
                    ."TotalImputacionRec ='".$totalImputacionRec."', "
                    ."NetoImputacionRec ='".$totalImputacionRec."', "
                    ."TotalPagoRec ='".$totalPagoRec."', "
                    ."TotalRecibo = '".$totalRecibo."',"
                    ."TotalDescRec = '".$totalDescuento."',"
                    ."TotalRetencion ='".$totalRetenciones."',"
                    ."Total_Tarjeta = '".$totalTarjeta."',"
                    ."Total_MC = 0,"
                    ."Total_Ingreso = '".$totalIngreso."',"
                    ."CodViajante = '".$codViajante."',"
                    ."Saldo ='".$saldoClienteNuevo."';";
//    echo "3.EXEC $sqlCuCliente ============><br>";
    $hacer= mysqli_query($connV,$sqlCuCliente);
    if(!$hacer){
        $errorT++;
        $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlCuCliente', 'sql'=>$sqlCuCliente);
        
    }
    // actualizo el saldo del cliente        
    $sqlCliente = "UPDATE cliente "
                    . " SET cliente.saldo='".$saldoClienteNuevo."' "
                    . " WHERE cliente.Codigo=".$rec["codCliente"].";";     
//    echo "4.EXEC $sqlCliente ============><br>";
    $hacer=mysqli_query($connV,$sqlCliente);
    if(!$hacer){
        $errorT++;
        $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlCliente', 'sql'=>$sqlCliente);
        
    }        
    
    
    // EFECTIVO
    //==========================================================================
    if(isset($rec["efectivo"])){
       $cajaP=$rec["efectivo"]["idCaja"];
            
        // traigo el saldo de la caja. traigo las dos cajas despues veo cual actualizo.
        $sqlSaldoCajaP="SELECT * FROM caja_saldo WHERE id_caja = ". $cajaP.";";
//         echo "5.EXEC $sqlSaldoCajaP ============><br>";
        $trae= mysqli_query($connV,$sqlSaldoCajaP)or die("no puedo recuperar las caajas".mysqli_error($connV)."<pre>".$sqlSaldoCajaP."</pre>");
        $arrCajasP=array();
        while($c= mysqli_fetch_assoc($trae)){
             $arrCajasP[$c["moneda"]]=$c;
        }   
        
        // pesos caja
        if($totalEfectivoP<>0){
            $Mon = "Pesos";
            // nuevo saldo de la caja en pesos
            $saldoCajaP=$arrCajasP[$Mon]["saldo"]+$totalEfectivoP;            
            
            // sql para alta de la caja.
            $sqlAltaCaja="INSERT INTO caja SET "
                    . "Fecha='".date('Y/m/d')."', "           
                    . "tipo_comprobante = 'REC',"
                    . "Tipo = 'Cobranza Efectivo', "
                    . "nro_comprobante ='".$rec["nroRecibo"]."',"
                    . "nro_comp_busq = '".$rec["nroCompBusq"]."',"
                    . "egreso = 0,"
                    . "id_usuario ='". $usuario->id_usuario."', "
                    . "cod_sucursal ='".$usuario->id_sucursal."', "
                    . "Moneda = 'Pesos', "
                    . "ingreso = ".$totalEfectivoP.", "
                    . "Detalle = '', "
                    . "Codigo_Movimiento = ".$rec["codmov"].", "
                    . "Codigo_Cliente = ".$rec["codCliente"].", "
                    . "codigo_prov = 1, "
                    . "tipo_cp = 'Cliente', "
                    . "anulado = 'No', "
                    . "Saldo = ".$saldoCajaP.", "
                    . "id_caja_abm_origen = ".$cajaP."; ";
//             echo "6.EXEC $sqlAltaCaja ============><br>";
            $hacer=mysqli_query($connV,$sqlAltaCaja);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlAltaCaja', 'sql'=>$sqlAltaCaja);
                
            }   
            
            $sqlEditaCajaP="UPDATE caja_saldo "
                    . "SET caja_saldo.saldo= ".$saldoCajaP.", "
                    . " caja_saldo.id_usuario=".$usuario->id_usuario." "
                    . "WHERE caja_saldo.id_caja_saldo=".$arrCajasP[$Mon]["id_caja_saldo"].";";      
//               echo "7.EXEC $sqlEditaCajaP ============><br>";
            $hacer=mysqli_query($connV,$sqlEditaCajaP);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlEditaCajaP', 'sql'=>$sqlEditaCajaP);
                
            }           
        }
        
        // dolares caja
        if($totalEfectivoD<>0){
            $Mon = "Dolar";
            // nuevo saldo de la caja en pesos
            $saldoCajaD=$arrCajasP[$Mon]["saldo"]+$totalEfectivoD;            
            
            // sql para alta de la caja.
            $sqlAltaCaja="INSERT INTO caja SET "
                    . "Fecha='".date('Y/m/d')."', "           
                    . "tipo_comprobante = 'REC',"
                    . "Tipo = 'Cobranza Efectivo', "
                    . "nro_comprobante ='".$rec["nroRecibo"]."',"
                    . "nro_comp_busq = '".$rec["nroCompBusq"]."',"
                    . "egreso = 0,"
                    . "id_usuario ='". $usuario->id_usuario."', "
                    . "cod_sucursal ='".$usuario->id_sucursal."', "
                    . "Moneda = 'Dolar', "
                    . "ingreso = ".$totalEfectivoD.", "
                    . "Detalle = '', "
                    . "Codigo_Movimiento = ".$rec["codmov"].", "
                    . "Codigo_Cliente = ".$rec["codCliente"].", "
                    . "codigo_prov = 1, "
                    . "tipo_cp = 'Cliente', "
                    . "anulado = 'No', "
                    . "Saldo = ".$saldoCajaD.", "
                    . "id_caja_abm_origen = ".$cajaP."; ";
            
//            echo "8.EXEC $sqlAltaCaja ============><br>";
            $hacer=mysqli_query($connV,$sqlAltaCaja);
            
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlAltaCaja', 'sql'=>$sqlAltaCaja);
                
            }  
            
            $sqlEditaCajaD="UPDATE caja_saldo "
                    . "SET caja_saldo.saldo= ".$saldoCajaD.", "
                    . " caja_saldo.id_usuario=".$usuario->id_usuario." "
                    . "WHERE caja_saldo.id_caja_saldo=".$arrCajasP[$Mon]["id_caja_saldo"].";"; 
//             echo "9.EXEC $sqlEditaCajaD ============><br>";
            
            $hacer=mysqli_query($connV,$sqlEditaCajaD);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlEditaCajaD', 'sql'=>$sqlEditaCajaD);
                
            }  
        }
       
        
        
    }
    
    // IMPUTACION DE FACTURAS
    //=========================================================================
    
    if($rec["clase"]=="imputacion"){
        //print_r($rec);
        foreach($rec["facturas"] as $i=>$f){
            // facturas canceladas total
            // averigo si la factura queda totalmente cancelada.
            
            $estado="N/Canc";
            $saldo=0;
            $canceladoActual=0;
            $canceladoTotal=0;
            $modificado="No";
            // soy cancelada
            if($f["saldoN"]==0){

                // cancelar la factura en su estado.  
                $estado="Canc";
                $modificado="Si";
                $sqlUpdCanc="UPDATE cuentacliente SET "
                        . " estado='Canc',"
                        . " ReciboMov=".$rec["codmov"].", "
                        . " Recibo='".$rec["nroRecibo"]."' "
                        . "WHERE cuentacliente.CodigoMovimiento=".$f["codmovFact"].";"; 
//                echo "10.EXEC $sqlUpdCanc ============><br>";
                $hacer=mysqli_query($connV,$sqlUpdCanc);
                if(!$hacer){
                    $errorT++;
                    $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlUpdCanc', 'sql'=>$sqlUpdCanc);
                    
                }
            }
            
            //$canceladoActual +=$f["cancelado"];
            $canceladoActual=$f["aimputar"];
            $cancelado=$canceladoActual+$f['cancelado'];
            // actualizo la recibo factura 
            $sqlUpdRecFact="UPDATE recibo_factura SET "
                    . "cancelado=".$cancelado.", "
                    . "Saldo=".$f["saldoN"].", "
                    . "estado='".$estado."', "
                    . "Imp='Si', "
                    . "ReciboMov=".$rec["codmov"].", "
                    . "Recibo='".$rec["nroRecibo"]."', "
                    . "CodViajante=".$codViajante." "
                    . " WHERE recibo_factura.id_recibo_factura=".$f["idrecibofactura"].";";
//            echo "11.EXEC $sqlUpdRecFact ============><br>";
            $hacer=mysqli_query($connV,$sqlUpdRecFact);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlUpdRecFact', 'sql'=>$sqlUpdRecFact);
                
            }
            
            // alta imputacion 
            $sqlInsImp= "INSERT INTO imputacion SET "
                    . "fecha_fac_nd='".$f["fecha"]."',"
                    . "tipo_comp_fac_nd='".$f["tipocomprobante"]."',"
                    . "nro_comp_fac_nd='".$f["nrofactura"]."',"
                    . "codmov_fac_nd='".$f["codmovFact"]."',"
                    . "fecha_nc_rec='".date('Y/m/d')."',"
                    . "tipo_comp_nc_rec='REC',"
                    . "nro_comp_nc_rec='".$rec["nroRecibo"]."',"
                    . "codmov_nc_rec=".$rec["codmov"].", "
                    . "Tipo='Imputación',"
                    . "importe_fac_nd=".$f["importe"].","
                    . "importe_cancelado_fac_nd=".$canceladoActual.", "
                    . "importe_saldo_fac_nd=".$f["saldoN"].","
                    . "estado_fac_nd='".$estado."' ,"
                    . "importe_nc_rec=".$rec["total"].","
                    . "importe_cancelado_nc_rec=".$canceladoActual.","
                    . "importe_saldo_nc_rec=0,"
                    . "estado_nc_rec='Canc',"
                    . "id_usuario=".$usuario->id_usuario.","
                    . "id_cliente=".$rec["codCliente"].";";
            $hacer=mysqli_query($connV,$sqlInsImp);
//            echo "12.EXEC $sqlInsImp ============><br>";
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsImp', 'sql'=>$sqlInsImp);
                
            }
           // $cancelado=$canceladoActual+$f['cancelado'];
            // alta recibo factura par
            $sqlInsRecFactPar="INSERT INTO recibo_factura_par SET "
                    . "cancelado=".$cancelado.", "
                    . "CanceladoActual=".$canceladoActual.", "
                    . "Saldo=".$f["saldoN"].", "
                    . "estado='".$estado."', "
                    . "Imp='Si', "
                    . "ReciboMov=".$rec["codmov"].", "
                    . "Recibo='".$rec["nroRecibo"]."', "
                    . "Fecha='".$f["fecha"]."', "
                    . "TipoComprobante='".$f["tipocomprobante"]."', "
                    . "Importe=".$f["importe"].", "
                    . "NroComprobante='".$f["nrofactura"]."', "
                    . "Vencimiento='".$f["vencimiento"]."', "
                    . "CodigoMovimiento=".$f["codmovFact"].", "
                    . "Codigo=".$rec["codCliente"].", "
                    . "CondVenta='".$f["condventa"]."', "
                    . "ImporteNC=".$f["importe"].", "
                    . "seleccionado='Si',"
                    . "ACuenta=NULL;"; 
//             echo "13.EXEC $sqlInsRecFactPar ============><br>";
            $hacer=mysqli_query($connV,$sqlInsRecFactPar);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsRecFactPar', 'sql'=>$sqlInsRecFactPar);
                
            }               
                                
        }
        
        // verificar si hay plata a cuenta
       // echo "que hay a cuenta::".var_dump($rec["aCuenta"]);
        if(isset($rec["aCuenta"])){
            // recibo factura
            
            $sqlInsRecFactCuenta="INSERT INTO recibo_factura SET "
                    . "Fecha='".date('Y/m/d')."', "
                    . "TipoComprobante='REC', "
                    . "Importe=".$rec["aCuenta"].", "
                    . "cancelado=0,"
                    . "Saldo=".$rec["aCuenta"].", "
                    . "ImporteNC=".$rec["aCuenta"].", "
                    . "NroComprobante='".$rec["nroRecibo"]."', "
                    . "estado='N/Canc',"
                    . "CodigoMovimiento=".$rec["codmov"].", "
                    . "Codigo=".$rec["codCliente"].", "
                    . "Imp='No', "
                    . "anulado='No', "
                    . "Modificado='No', "
                    . "Tipo='Cliente',"
                    . "CodViajante=".$codViajante.";";
//              echo "14.EXEC $sqlInsRecFactCuenta ============><br>";
            $hacer=mysqli_query($connV,$sqlInsRecFactCuenta);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsRecFactCuenta', 'sql'=>$sqlInsRecFactCuenta);
                
            }
            
            // recibo factura par
            $sqlInsRecFactParCunta="INSERT INTO recibo_factura_par SET "
                    . "cancelado=0,"
                    . "CanceladoActual=0,"
                    . "Saldo=".$rec["aCuenta"].", "
                    . "estado='N/Canc', "
                    . "Imp='Si', "
                    . "ReciboMov=".$rec["codmov"].", "
                    . "Recibo='".$rec["nroRecibo"]."',"
                    . "Fecha='".date('Y/m/d')."', "
                    . "TipoComprobante='REC',"
                    . "Importe=".$rec["aCuenta"].", "
                    . "NroComprobante='".$rec["nroRecibo"]."',"
                    . "CodigoMovimiento=".$rec["codmov"].", "
                    . "Codigo=".$rec["codCliente"].", "
                    . "ImporteNC=".$rec["aCuenta"].", "
                    . "anulado='No',"
                    . "Modificado='No',"
                    . "seleccionado='No';";
//            echo "15.EXEC $sqlInsRecFactParCunta ============><br>";
            $hacer=mysqli_query($connV,$sqlInsRecFactParCunta);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsRecFactParCunta', 'sql'=>$sqlInsRecFactParCunta);
               
            }
            
           
        }
        
        // CHEQUES DE TERCEROS
        // =====================================================================
        if(isset($rec["cheques"]["listado"])){
            
            // buscar el saldo de la caja 
            $sqlTraeSaldoCajaParcial = "SELECT * FROM caja_saldo WHERE caja_saldo.id_caja=".$rec["cheques"]["idCajaCheque"].";";
//             echo "16.EXEC $sqlTraeSaldoCajaParcial ============><br>";
            $hsa=mysqli_query($connV,$sqlTraeSaldoCajaParcial); 
            if(!$hsa){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlTraeSaldoCajaParcial Cheques', 'sql'=>$sqlTraeSaldoCajaParcial);
               
            }        
            //print_r($rec);
            
            //$errorT++;
                    
            $cj= mysqli_fetch_assoc($hsa);
            //$arrError[]=array('error'=>mysqli_error($connV),'variable'=>'saldo caja cheques no valido', 'sql'=>$cj["saldo"].' sql::'.$sqlTraeSaldoCajaParcial.' arr:: '.json_encode($cj));
            // no traje un saldo valido
            if(!is_numeric($cj["saldo"])){
                 $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'saldo caja cheques no valido', 'sql'=>$cj["saldo"].' sql::'.$sqlTraeSaldoCajaParcial.' arr:: '.json_encode($cj));
            }
            $saldoCajaCh=$cj["saldo"];
        
        
            foreach($rec["cheques"]["listado"] as $c){
            // insertar cheques en chequetercero
                //print_r($c);
                
                
                $saldoCajaCh +=$c["importe"];

                $sqlInsChequeTerc="INSERT INTO chequetercero SET "
                        . "NroCheque='".$c["numero"]."', "
                        . "CodBanco=".$c["codbanco"].", "
                        . "CodCliente=".$rec["codCliente"].", "
                        . "Librador='".$c["librador"]."',"
                        . "fechaEmision='".$c["emision"]."',"
                        . "fechaVto='".$c["vencimiento"]."', "
                        . "fechaCobro='".$c["cobro"]."', "
                        . "Importe=".$c["importe"].", "
                        . "anulado='No',"
                        . "Encartera='Si',"
                        . "Entregado='No',"
                        . "Rechazado='No',"
                        . "Depositado='No',"
                        . "NroCompREC='".$rec["nroRecibo"]."',"
                        . "CodigoMovimientoREC=".$rec["codmov"].", "
                        . "CUITLibrador='".$c["cuitlibrador"]."',"
                        . "tipo_cheque='".$c["tipo"]."';"; 
//                echo "17.EXEC $sqlInsChequeTerc ============><br>";
                $hacer=mysqli_query($connV,$sqlInsChequeTerc)or die('error'.mysqli_error($connV).' variable=>'.'$sqlInsChequeTerc'. 'sql'.$sqlInsChequeTerc);
                
                if(!$hacer){
                    $errorT++;
                    $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsChequeTerc', 'sql'=>$sqlInsChequeTerc);
                    
                }
                //$errorT++;
                  //  $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsChequeTerc', 'sql'=>$sqlInsChequeTerc);
                // recuperar el insert id
                $idCheque= mysqli_insert_id($connV); 
                if(mysqli_errno($connV)){
                    $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsChequeTerc', 'sql'=>$sqlInsChequeTerc);
                    
                
                }
               
                // insertar en caja

                $sqlInsCajaCheque="INSERT INTO caja SET "
                        . "Fecha='".date('Y/m/d')."', "
                        . "tipo_comprobante='CHEQ', "
                        . "Tipo='Cheque', "
                        . "nro_comprobante='".$c["numero"]."', "
                        . "nro_comp_busq='".$c["numero"]."', "
                        . "egreso=0,"
                        . "id_usuario=".$usuario->id_usuario.", "
                        . "cod_sucursal=".$usuario->id_sucursal.", "
                        . "Moneda='No',"
                        . "ingreso=".$c["importe"].", "
                        . "Detalle ='Cheque Nro: ".$c["numero"]." - Banco: ".$c["banco"]." - Librador: ".$c["librador"]." - CUIT: ". $c["cuitlibrador"] . " - Fecha Cob: ".$c["cobro"]."', "
                        . "Codigo_Movimiento=".$rec["codmov"].", "
                        . "Codigo_Cliente=".$rec["codCliente"].", "
                        . "codigo_prov=1,"
                        . "tipo_cp='Cliente', "
                        . "anulado='No', "
                        . "Saldo=".$saldoCajaCh.","
                        . "id_caja_abm_origen=".$rec["cheques"]["idCajaCheque"].", "
                        . "id_chequetercero=".$idCheque.", "
                        . "nro_comp_cheq='".$rec["nroRecibo"]."',"
                        . "tipo_comp_cheq='REC';";
//                echo "18.EXEC $sqlInsCajaCheque ============><br>";
                $hacer=mysqli_query($connV,$sqlInsCajaCheque)or die('error'.mysqli_error($connV).' variable=>'.'$sqlInsCajaCheque'. 'sql'.$sqlInsCajaCheque); 
                
                if(!$hacer){
                    $errorT++;
                    $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsCajaCheque', 'sql'=>$sqlInsCajaCheque);
                    
        
                }                     
          
                
                // insertar en chequeterc_rec
                $sqlInsCheqRec = "INSERT INTO chequeterc_rec SET "
                        . "NroCheque='".$c["numero"]."',"
                        . "CodBanco=".$c["codbanco"].", "
                        . "CodCliente=".$rec["codCliente"].", "
                        . "Librador='".$c["librador"]."', "
                        . "fechaEmision='".$c["emision"]."', "
                        . "fechaVto='".$c["vencimiento"]."', "
                        . "fechaCobro='".$c["cobro"]."', "
                        . "Importe=".$c["importe"].", "
                        . "anulado='No',"
                        . "NroCompREC='".$rec["nroRecibo"]."', "
                        . "CUITLibrador='".$c["cuitlibrador"]."', "
                        . "CodigoMovimientoREC=".$rec["codmov"].","
                        . "tipo_cheque='".$c["tipo"]."';";
//                 echo "19.EXEC $sqlInsCheqRec ============><br>";
                $hacer=mysqli_query($connV,$sqlInsCheqRec)or die('error'.mysqli_error($connV).' variable=>'.'$sqlInsCheqRec'. 'sql'.$sqlInsCheqRec);
                if(!$hacer){
                    $errorT++;
                    $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsCheqRec', 'sql'=>$sqlInsCheqRec);
                    
                }
            
                
            }
            // dentro de los cheques actualizo la caja
            // actualizar la caja_saldo
            $sqlUpdCajaCheque="UPDATE caja_saldo SET "
                    . "saldo=".$saldoCajaCh.", "
                    . "id_usuario=".$usuario->id_usuario.", "
                    . "cod_sucursal=".$usuario->id_sucursal." "
                    . " WHERE caja_saldo.id_caja=".$rec["cheques"]["idCajaCheque"].";";
//            echo "20.EXEC $sqlUpdCajaCheque ============><br>";
            $hacer=mysqli_query($connV,$sqlUpdCajaCheque)or die('error'.mysqli_error($connV).' variable=>'.'$sqlUpdCajaCheque'. 'sql'.$sqlUpdCajaCheque);
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlUpdCajaCheque', 'sql'=>$sqlUpdCajaCheque);
                
            }            
        }
        
        
        
    }
    // RETENCIONES 
    // =========================================================================
    
    if(isset($rec["retencion"])){
        foreach($rec["retencion"]["lista"] as $l=>$rt){
            $sqlInsRet="INSERT INTO retenciones SET "
                    . "NroCertificado='".$rt["certificado"]."',"
                    . "CodCliente=".$rec["codCliente"].","
                    . "Fecha='".$rt["fecha"]."', "
                    . "Porcentaje=".$rt["porcentaje"].", "
                    . "Importe=".$rt["monto"].", "
                    . "NroRec='".$rec["nroRecibo"]."', "
                    . "CodRetencion=".$rt["cod"].","
                    . "CodAgentRet=1,"
                    . "anulado='No',"
                    . "Codigo_Movimiento=".$rec["codmov"].", "
                    . "CodBanco=1;";
//             echo "21.EXEC $sqlInsRet ============><br>";
            $hacer=mysqli_query($connV,$sqlInsRet)or die('error'.mysqli_error($connV).' variable=>'.'$sqlInsRet'. 'sql'.$sqlInsRet);
            
            if(!$hacer){
                $errorT++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsRet', 'sql'=>$sqlInsRet);
               
        
            }  
        }
    }
    
    // DESCUENTOS 
    //=========================================================================
    if(isset($rec["descuento"])){
        $sqlInsDesc="INSERT INTO descuento_rec_nc SET "
                . "CodDescuento=1,"
                . "Fecha='".date('Y/m/d')."',"
                . "NroRec='".$rec["nroRecibo"]."',"
                . "CodigoMovimiento=".$rec["codmov"].","
                . "Importe=".$rec["descuento"]["total"].", "
                . "Porcentaje=".$rec["descuento"]["porcentaje"].", "
                . "CodCliente=".$rec["codCliente"].", "
                . "Computado='No',"
                . "anulado='No';";
//        echo "22.EXEC $sqlInsDesc ============><br>";
        $hacer=mysqli_query($connV,$sqlInsDesc)or die('error'.mysqli_error($connV).' variable=>'.'$sqlInsDesc'. 'sql'.$sqlInsDesc);
        if(!$hacer){
            $errorT++;
            $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsRet', 'sql'=>$sqlInsDesc);
        //    print_r($arrError);
        
        }  
        
    } 
    
    //Finalizar la transaccion porque debo ir a otro codigo
    
    // ASIENTO CONTABLE
    // =========================================================================
    // ========================================================================
    // averiguar si el punto de venta es contable.
    // si esta activada la contabilidad SELECT activ_contabilidad from configuracion
    $sqlSelPvta="SELECT * FROM punto_venta WHERE id_punto_venta=".$idPuntoVenta.";";
//      echo "23.EXEC $sqlSelPvta ============><br>";
    $hpv=mysqli_query($connV,$sqlSelPvta)or die('no puedo traer el punto de venta.'.mysqli_error($connV).'<pre>'.$sqlSelPvta.'</pre>');
    
    $pvc= mysqli_fetch_assoc($hpv);
//    print_r($pvc);
    $sqlSelContab="SELECT activ_contabilidad FROM configuracion;";
//    echo "24.EXEC $sqlSelContab ============><br>";
    $hcconf= mysqli_query($connV, $sqlSelContab) or die('no puedo buscar el permiso en confi'.mysqli_error($connV).'<pre>'.$sqlSelContab.'</pre>');
    $pconf=mysqli_fetch_assoc($hcconf);
    
    if($errorT==0){
    
        if($pvc["cont"]=="Si"&&$pconf["activ_contabilidad"]=="Si"){
            // genero el asiento contable.
            $asiento= generar_asiento_cont($connV, $rec["codmov"]);
            $as=null;
            // devuelve un error que habria que agregarlo para saber que todo bin.
            if($asiento["estado"]=="error"){
                //echo"que devuelve el asiento?<pre>";
                // echo var_dump($asiento);
                /// echo "</pre>";
                $errorT++;
                $arrError[]=array('error'=>'fallo contabilidad','variable'=>$asiento['variable'],'sql'=>$asiento['sql']);
            }
            //se genero la contabilidad
            if($asiento["estado"]=="ok"){
                $as=$asiento["asiento"];
            }
        }
    }
    
     if($errorT == 0){
            $sqlTotal= "COMMIT;";
            $resultado = mysqli_query($connV,$sqlTotal);
            if(isset($as)&&$as!=null){
                // hay contabilidad y asiento
                $vector=array('msg'=>'ok','nroRecibo'=>$rec["nroRecibo"],'importe'=>number_format($rec["total"],2,",","."),'asiento'=>$as,'codcliente'=>$rec["codCliente"]);
            }
            if(!isset($as)){
                //sin asiento
                $vector=array('msg'=>'ok','nroRecibo'=>$rec["nroRecibo"],'importe'=>number_format($rec["total"],2,",","."),'asiento'=>'no','codcliente'=>$rec["codCliente"]);
            }
            // creo las variables de sesion para 
            $_SESSION["tipoComprobante"] = "REC";
            $_SESSION["nroComprobante"]=$rec["nroRecibo"];
            $_SESSION["totalComprobante"]=$rec["total"];
            $_SESSION["codigoMovimiento"]=$rec["codmov"];
    
    
            // ELIMINAR LA SESSION DEL RECIBO
            unset($_SESSION["recibo"]); 
            //echo "todo bien";
        }else{
//            echo "25.ERROR";
//            print_r($arrError);
            $sqlTotal = "ROLLBACK;";
            $resultado = mysqli_query($connV,$sqlTotal);
            $vector=array('msg'=>'error','desc'=>$arrError);
            //unset($_SESSION["recibo"]); 
            //echo "todo mal";
        }
//    $out = ob_get_clean();
//    echo "<pre>";
//    echo print_r($out);
     print json_encode($vector);
} 

function generar_asiento_cont($connV,$codMov){
//    ob_start();





   // echo "<pre>";
//    echo "1.INICIANDO ASIENTO============><br>";
    $rec=$_SESSION["recibo"];
//    if(is_object($_SESSION['cliente'])){
//        $cliente = $_SESSION['cliente'];
//    }else{
//        $cliente = $_SESSION['cliente'][0];
//    }
    
    
    
    $usuario = $_SESSION['vendedor'];
            
    $cotiDolar=1;
    $totalEfectivoP=0;
    $totalEfectivoD=0;
    $totalPago =0;
    $totalCheque=0;
    $totalImputacionRec=0;
    $netoImput=0;
    $totalPagoRec=0;
    $totalRecibo=0;
    $totalDescuento=0;
    $totalRetenciones=0;
    $totalTarjeta=0;
    $totalMedcob=0;
    $totalIngreso=0;
    $errorConta=0;
    $arrError=array();
// hay plata efectivo
   // print_r($rec);
    if(isset($rec["efectivo"])){
        $cotiDolar=$rec["efectivo"]["cotizacion"];
        $totalEfectivoP=$rec["efectivo"]["pesos"];
        $totalEfectivoD=$rec["efectivo"]["total"]-$rec["efectivo"]["pesos"];
        $totalPago += $rec["efectivo"]["total"];
    
    }
    // cheques 
    if( isset($rec["cheques"]["total"])){
        $totalCheque +=$rec["cheques"]["total"];
    }
    // totales y subtotales
    if(isset($rec["facturas"])){
        $totalImputacionRec+=$rec["totalImputado"];
    }
    // descuento
    if(isset($rec["descuento"])){
        
        $netoImput=$totalImputacionRec -$rec["descuento"]["total"]; 
        $totalDescuento=$rec["descuento"]["total"]; 
    }
    // retencion
    if(isset($rec["retencion"])){
        $totalRetenciones+=$rec["retencion"]["total"];
    }
    
    
    $totalMedcob += ($totalPago+$totalCheque);
    $totalPagoRec += ($totalPago+$totalCheque);
    
    $totalRecibo = $totalPagoRec+$totalRetenciones;
    
    $idPuntoVenta = $usuario->id_punto_venta; 
    $saldoCliente = $rec["saldoCliente"];
    $idPcCliente= $rec["idPcCliente"];
    //$saldoCliente = $cliente->saldo;
    
    $saldoClienteNuevo = ($saldoCliente-$totalPagoRec);
    //$saldoClienteNuevo = ($saldoCliente-$totalPagoRec);
    //empezar la contabilidad
    // vamos a tener que crear un array
//    echo "MedCob::<pre>";
//    print_r($totalMedcob);
//    echo "<br>TotalPagoRec:.";
//    print_r($totalPagoRec);
//    echo "<br>Total recibo::";
//    print_r($totalRecibo);
//    print_r($cliente);
    $matAsiento=array();
    $i=0;
    
    // CAJA
    //=============================================
    if(isset($rec["efectivo"]["total"])&&isset($totalPago)){
       
    
        $cajaP = $rec["efectivo"]["idCaja"];
        $sqlSaldoCajaP = "SELECT * FROM caja_abm WHERE id_caja = " . $cajaP;
//        echo "1.EXEC $sqlSaldoCajaP ============><br>";
        $hcaj = mysqli_query($connV, $sqlSaldoCajaP) or die("no puedo recuperar la caja abm" . mysqli_error($connV) . "<pre>" . $sqlSaldoCajaP . "</pre>");
        if(!$hcaj){
//            exit();
        }
        $arrCajap = mysqli_fetch_assoc($hcaj);

        // pesos

        if ($totalEfectivoP != 0) {
            // traigo el saldo de la caja. traigo las dos cajas despues veo cual actualizo.            
            $i++;
            $matAsiento[$i] = array($arrCajap["id_pc"], $totalEfectivoP, 0);
            
        }

        // dolares

        if ($totalEfectivoD != 0) {           
            $i++;
            $matAsiento[$i] = array($arrCajap["id_pc"], $totalEfectivoD, 0);
        }
    }
    
    // CHEQUES
    //========================================================================
    
    if(isset($rec["cheques"]["total"])){
       $i++;
        $idCajaCheque= $rec["cheques"]["idCajaCheque"];
        $sqlSaldoCajaC= "SELECT * FROM caja_abm WHERE id_caja = " . $idCajaCheque;
//         echo "2.EXEC $sqlSaldoCajaC ============><br>";
        $hcaj = mysqli_query($connV, $sqlSaldoCajaC) or die("no puedo recuperar la caja abm" . mysqli_error($connV) . "<pre>" . $sqlSaldoCajaC . "</pre>");
        if(!$hcaj){
//            exit();
        }
        $arrCajaC = mysqli_fetch_assoc($hcaj);
        $matAsiento[$i] = array($arrCajaC["id_pc"], $totalCheque, 0);
    
    }
            
    // RETENCIONES
    if(isset($rec["retencion"])){
        foreach($rec["retencion"]["lista"] as $ki=>$ret){
            $i++;
            $sqlPcRet="SELECT * FROM tipo_retencion_cli WHERE CodRetencion=".$ret["cod"];
//            echo "3.EXEC $sqlPcRet ============><br>";
            $hret= mysqli_query($connV,$sqlPcRet)or die("No puedo recuperar la retencion ".mysqli_error($connV).'<pre>'.$sqlPcRet.'</pre>');
           if(!$hcaj){
//            exit();
        }
            $datoRet= mysqli_fetch_assoc($hret);
            $matAsiento[$i] = array($datoRet["id_pc"], $ret["monto"], 0);
            
            
        }
    }
        
    // DEUDORES X VENTA
    // ========================================================================
    $i++;
    // al ser por imputacion traigo las facturas imputacion menos descuento.
    // al ser x imputacion siempre tengo que traer la plata porque al traer de mas.
    $matAsiento[$i]=array($idPcCliente,0,$totalRecibo);
//$idPcCliente
    // ANALIZAR BALANCEO
    $debe=0;
    $haber=0;
    //print_r($rec);
    //echo "asiento desbalanceado<pre>";
//    print_r($matAsiento);
    
    foreach($matAsiento as  $ii => $a){
        $debe+=$a[1];
        $haber+=$a[2];
    }
//    $errorConta++;
//         $arrError[]=array('error'=>'Asiento desbalanceado','variable'=>'$debe <> $haber','sql'=>'debe:'.$debe.' - haber:'.$haber);
        // salir de la function retornar error
    if($debe<>$haber){
        $errorConta++;
         $arrError[]=array('error'=>'Asiento desbalanceado','variable'=>'$debe <> $haber','sql'=>array('debe:'.$debe.' - haber:'.$haber, 'matriz:'=>$matAsiento));
        // salir de la function retornar error
        //asiento desbalanceado salid.
        
    }
     $idEjercicio = 0;
     $idPeriodo = 0;    
        
   // ejercicio.
    
    $sqlEjercicio="SELECT * FROM cont_ejercicio WHERE activo_ejercicio='Si'";
//    echo "4.EXEC $sqlEjercicio ============><br>";
    $hej= mysqli_query($connV,$sqlEjercicio)or die('No puedo recuperar el ejercicio contable'.mysqli_error($connV).'<pre>'.$sqlEjercicio.'</pre>');
    $ej=mysqli_fetch_assoc($hej);
    if($ej==NULL){
        // no habia ejerccio abierto
        $errorConta++;
         $arrError[]=array('error'=>'No hay ejercicio contable activo','variable'=>'$sqlEjercicio','sql'=>$sqlEjercicio);
//         exit();
     //   salir
    }else{
        
        $idEjercicio = $ej["id_ejercicio"];
        
    }
    // periodo activo
    $sqlPeriodo="SELECT * FROM cont_periodo WHERE activo_periodo = 'Si' ";
//    echo "5.EXEC $sqlPeriodo ============><br>";
    $hper=mysqli_query($connV,$sqlPeriodo)or die('No puedo recuperar el periodo contable'.mysqli_error($connV).'<pre>'.$sqlPeriodo.'</pre>');
    if(!$hper){
//        exit();
    }
    $pper= mysqli_fetch_assoc($hper);
//    echo "pper=>";
//    print_r($pper);
//    var_dump($pper);
    if($pper==NULL){
        // Sin periodo ocntable SAlir.
//        $errorConta++;
//        $arrError[]=array('error'=>'No hay periodo contable activo','variable'=>'$sqlPeriodo','sql'=>$sqlPeriodo);
        
    }else{
        
        $idPeriodo=$pper["id_periodo"];   
    
    }
    
    // verifico si esta cerrado el ejercicio   
        
        
    if($idPeriodo!=0){
        $sqlEjCerrado="SELECT cerrado FROM cont_periodo WHERE id_periodo = ".$idPeriodo ." AND id_ejercicio = ".$idEjercicio;
    }else{
        $sqlEjCerrado="SELECT cerrado FROM cont_ejercicio WHERE id_ejercicio = ".$idEjercicio;
        $idPeriodo=1;
    }
//    echo "6.EXEC $sqlEjCerrado ============><br>";
    $hcerrado=mysqli_query($connV,$sqlEjCerrado)or die('No puedo ver el ejercicio contable cerrado '.myqli_error($connV).'<pre>'.$sqlEjCerrado.'</pre>');
    if(!$hcerrado){
        
    }
    $cerrado= mysqli_fetch_assoc($hcerrado);
    if($cerrado["cerrado"]=='Si'){
        // error de periodo contable CERRAD
        $errorConta++;
         $arrError[]=array('error'=>'Periodo contable cerrado','variable'=>'$sqlEjCerrado','sql'=>$sqlEjCerrado);
         
    }
     
    // evaluar si hay error conta , hacer vector de salida y salir con return.
    
    // insertar el asiento.
      
    // numero de asiento contable 
    $sqlContEjer="SELECT * FROM cont_ejercicio WHERE id_ejercicio = ". $idEjercicio;
//   echo "7.EXEC $sqlContEjer ============><br>";
     $hasi=mysqli_query($connV,$sqlContEjer) or die ('no puedo buscar el cont asiento para numero'.mysqli_error($connV).'<pre>'.$sqlContEjer.'</pre>');
     if(!$hasi){
//          exit();
     }
     
     $asiento= mysqli_fetch_assoc($hasi);
//     print_r($asiento);
     $nroAsientoCont=$asiento["nro_asiento_ejercicio"];
     $contadorasiento = $asiento["nro_asiento_ejercicio"] +1;
     // actualizo el otro asiento
     $sqlNumAsiento="UPDATE cont_ejercicio SET Nro_asiento_ejercicio=".$contadorasiento." WHERE id_ejercicio=".$idEjercicio;
//      echo "8.EXEC $sqlNumAsiento ============><br>";
     $upd=mysqli_query($connV,$sqlNumAsiento);
     if(!$upd){
         $errorConta++;
         $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlNumAsiento','sql'=>$sqlNumAsiento);
//         exit();
     }
             
           
     
     
     // FECHA
     // ====================================================================
        $desdeEj= new DateTime($ej["fecdesde_ejercicio"]);
        $hastaEj= new DateTime($ej["fechasta_ejercicio"]);
        $hoy = new DateTime(date('Y-m-d'));       
        $fecha=  date('Y-m-d');
        // si hay period
        if($pper){
            $desdePer = new DateTime ( $pper["fecdesde_periodo"]); 
            $hastaPer = new DateTime ( $pper["fechasta_periodo"]); 
        }
        // dentro del ejercicio 
        if($hoy>=$desdeEj&&$hoy<=$hastaEj){
            if($pper!=NULL){
                if($hoy<=$desdePer && $hoy>=$hastaPer){
                    $errorConta++;
                    $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$hoy<=$desdePer && $hoy>=$hastaPer','sql'=>var_dump($hoy<=$desdePer && $hoy>=$hastaPer));


                }
            }
        }else{
            $errorConta++;
            $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'Fecha de Recibo fuera del Ejercicio contable desde:'.$ej["fecdesde_ejercicio"].' - hasta:'.$ej["fechasta_ejercicio"],'sql'=>$hoy->format('d/m/Y').'>='.$desdeEj->format('d/m/Y').'&&'.$hoy->format('d/m/Y').'<='.$hastaEj->format('d/m/Y'));
        }
        
        // evaluo la fecha del ejerccio si hay error
       
        // concepto del asiento
        $sqlConcepto="SELECT desc_concepto_asiento FROM cont_concepto_asiento WHERE id_concepto_asiento = 5 ";
//        echo "9.EXEC $sqlConcepto ============><br>";
        $hcon=mysqli_query($connV,$sqlConcepto) or die('No puedo recuperar el concepto del asiento '.mysqli_error($connV).'<pre>'.$sqlConcepto.'</pre>');
       if(!$hcon){
//           exit();
       }
        $concepto= mysqli_fetch_assoc($hcon);
        
     // alta de asiento con el array
//     print_r($matAsiento);
     
     foreach($matAsiento AS $asientoC){
         // la cuenta
//          print_r($asiento);
     
         $sqlInfoCuenta="SELECT * FROM cont_pc WHERE id_pc=".$asientoC[0];
//          echo "10.EXEC $sqlInfoCuenta ============><br>";
         $hcuenta=mysqli_query($connV,$sqlInfoCuenta) or die('no puedo recuperar la cuenta'.mysqli_error($connV).'<pre>'.$sqlInfoCuenta.'</pre>');
         if(!$hcuenta){
//             exitC();
         }
         $cuenta= mysqli_fetch_assoc($hcuenta);
         
         
         // EJERCICIO SALDO
         // ====================================================================
         $sqlSaldoCuenta ="SELECT * FROM cont_ejercicio_saldo_cta "
                    . "WHERE id_pc = ".$asientoC[0]. " AND id_ejercicio = ".$idEjercicio; 
//          echo "11.EXEC $sqlSaldoCuenta ============><br>";
         $has=mysqli_query($connV,$sqlSaldoCuenta)or die('No puedo recuperar el saldo de la cuenta del asiento'.mysqli_error($connV).'<pre>'.$sqlSaldoCuenta.'</pre>');
         if(!$has){
//             exit();
         }
         $saldoEjer= mysqli_fetch_assoc($has);
         
         // actualizando saldo EJERCICIO
         $saldoCuenta=0;
         if($cuenta["saldo_pc"]=="Deudor"){
             $saldoCuenta= $saldoEjer["saldo_ejercicio_cta"]+($asientoC[1] - $asientoC[2]);
             $sqlUpdSaldoCuenta="UPDATE cont_ejercicio_saldo_cta SET "
                     . "saldo_ejercicio_cta=".$saldoCuenta." "
                     . "WHERE id_pc = ". $asientoC[0] ." "
                     . "AND id_ejercicio = ".$idEjercicio;
         }
         if($cuenta["saldo_pc"]=="Acreedor"){
             $saldoCuenta= $saldoEjer["saldo_ejercicio_cta"] - ($asientoC[1] + $asientoC[2]);
             $sqlUpdSaldoCuenta="UPDATE cont_ejercicio_saldo_cta SET "
                     . "saldo_ejercicio_cta=".$saldoCuenta." "
                     . "WHERE id_pc = ". $asientoC[0] ." "
                     . "AND id_ejercicio = ".$idEjercicio;
         } 
//         echo "12.EXEC $sqlUpdSaldoCuenta ============><br>";
         $h=mysqli_query($connV,$sqlUpdSaldoCuenta);
         if(!$h){
             $errorConta++;
             $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlUpdSaldoCuenta','sql'=>$sqlUpdSaldoCuenta);
             
         }
         
         // PERIODO
         //=====================================================================
        
         $sqlSaldoPer="SELECT * FROM cont_periodo_saldo_cta "
                 . "WHERE  id_pc = ". $asientoC[0]." "
                 . "AND id_ejercicio = ".$idEjercicio. " "
                 . "AND id_periodo = ".$idPeriodo;
//          echo "13.EXEC $sqlSaldoPer ============><br>";
         $hp=mysqli_query($connV,$sqlSaldoPer)or die('No puedo recuperar el saldo del periodo '.mysqli_error($connV).'<pre>'.$sqlSaldoPer.'</pre>');
        
         if(!$hp){
             $errorConta++;
             $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlSaldoPer','sql'=>$sqlSaldoPer);
         }
         $saldoPer=mysqli_fetch_assoc($hp);
          $saldoCuentaP=0;
         if($saldoPer!=NULL){
           
            if($cuenta["saldo_pc"]=="Deudor"){
                $saldoCuentaP= $saldoPer["saldo_periodo_cta"]+($asientoC[1] - $asientoC[2]);
                $sqlUpdSaldoCuentaP="UPDATE cont_periodo_saldo_cta SET "
                        . "saldo_periodo_cta=".$saldoCuentaP." "
                        . " WHERE id_pc = ". $asientoC[0] ." "
                        . " AND id_ejercicio = ".$idEjercicio
                        . " AND id_periodo = ".$idPeriodo;
                        
            }
            if($cuenta["saldo_pc"]=="Acreedor"){
                $saldoCuentaP= $saldoPer["saldo_periodo_cta"] - ($asientoC[1] + $asientoC[2]);
                $sqlUpdSaldoCuentaP="UPDATE cont_periodo_saldo_cta SET "
                        . "saldo_periodo_cta=".$saldoCuentaP." "
                        . " WHERE id_pc = ". $asientoC[0] ." "
                        . " AND id_ejercicio = ".$idEjercicio
                        . " AND id_periodo = ".$idPeriodo;
                        
            } 
//            echo "14.EXEC $sqlUpdSaldoCuentaP ============><br>";
            $h=mysqli_query($connV,$sqlUpdSaldoCuentaP);
            if(!$h){
                $errorConta++;
                $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlUpdSaldoCuentaP','sql'=>$sqlUpdSaldoCuentaP);
               
            }
         }
         
         
         
         // ALTA FINAL DE CADA ASIENTO 
         // 
         
         $sqlInsAsiento="INSERT INTO cont_asiento SET "
                 . "codigo_movimiento =".$codMov.","
                 . "nro_asiento ='".$nroAsientoCont."',"
                 . "id_periodo = ".$idPeriodo.","
                 . "id_ejercicio = ".$idEjercicio.","
                 . "saldo_asiento=".$saldoCuenta.","
                 . "fecha_asiento='".$fecha."',"
                 . "debe_asiento =".$asientoC[1].","
                 . "haber_asiento = ".$asientoC[2].","
                 . "id_pc =".$asientoC[0].","
                 . "desc_concepto_asiento ='".$concepto["desc_concepto_asiento"]."',"
                 . "id_concepto_asiento = 5,"
                 . "balanceado_asiento='Si',"
                 . "id_usuario=".$usuario->id_usuario.", "                 
                 . "desc_asiento = 'Recibo por ".$rec["clase"]. " - Nro Comp. REC - ". $rec["nroRecibo"]. "';";
         
//          echo "15.EXEC $sqlInsAsiento ============><br>";        
         $hasiento=mysqli_query($connV,$sqlInsAsiento);
         
         if(!$hasiento){
             $errorConta++;
             $arrError[]=array('error'=>mysqli_error($connV),'variable'=>'$sqlInsAsiento','sql'=>$sqlInsAsiento);
//             print_r($arrError);
//             exit();
         }
         
    }
     // evaluar las transaccion 
    $vector=array();
    // armar vector de vuelta con exito.
    if($errorConta>0){
        $variable='';
        $sqle='';
        //echo" viendo si hay error contabilidad<pre>";
        //print_r($arrError);
        foreach($arrError as $k=> $error){
            
            $variable .=$error['error'].'=>'.$error['variable'];
            $sqle .=$error['sql'].'\n';
        }
        $vector=array('estado'=>'error','variable'=>$variable,'sql'=>$sqle);
    }else{
        $vector=array('estado'=>'ok','asiento'=>$nroAsientoCont);
    }   
//    $out = ob_get_clean();
//    echo "<pre>";
//    echo print_r($out);
    return $vector;
}

function salir_recibo(){
    
    $vector=array('msg'=>'ok');
    if(isset($_SESSION["recibo"])){
        unset($_SESSION["recibo"]);
    }
    print json_encode($vector);
    
}


//==============================================================================



/*
 * CONTROLADORES
 * =============================================================================
 */

//echo "<pre>";
//print_r($_SESSION["recibo"]);
//echo "</pre>";
 header('Content-Type: application/json'); 

// alta de recibo
if(isset($_REQUEST["altaRecibo"])&&$_REQUEST["altaRecibo"]==1){
    $tipo=$_REQUEST["tipoNro"];
    $arrPv=explode("|",$_REQUEST["nroPv"]);
    $pv=$arrPv[1];
    $idPv=$arrPv[0];
    $nro=$_REQUEST["nroRec"];
    $cliente=$_REQUEST["cliente"];
    $saldoCliente=$_REQUEST["saldoCliente"];
    $idPcCliente=$_REQUEST["idPcCliente"];
   //$cliente= $objCliente->Codigo;
    nuevo_recibo($tipo,$idPv, $pv, $nro, $cliente,$saldoCliente,$idPcCliente,$connV);
    
}    
// listar facturas a imputar.
if(isset($_REQUEST["listarFacturas"])&&$_REQUEST["listarFacturas"]==1){
//    $cliente= $_REQUEST["cliente"];
    //$cliente =  $objCliente->Codigo;
    $desde=date("Y-m-d", strtotime("-1 year"));
    $hasta=date("Y-m-d");
    if(isset($_REQUEST["desde"])&&$_REQUEST["desde"]!==1){
        $desde=$_REQUEST["desde"];
    }
    
    if(isset($_REQUEST["hasta"])&&$_REQUEST["hasta"]!==1){
        $hasta=$_REQUEST["hasta"];
    }
    
    listar_facturas($connV,$desde,$hasta);
      
        
}

// traer el total a cuenta del cliente

if(isset($_REQUEST["traeAcuenta"])&& $_REQUEST["traeAcuenta"]==1){
//    $cliente= $objCliente->Codigo;
    $cliente=$_SESSION["recibo"]["codCliente"];
    trae_recibos_a_cuenta($connV, $cliente);
}


// imputar una factura
if(isset($_REQUEST["imputarFactura"])&&$_REQUEST["imputarFactura"]==1){
    
    $arrF["idrecibofactura"]=$_REQUEST["idrecibofactura"];
    $arrF["codmovFact"]=$_REQUEST["codmodfact"];
    $arrF["fecha"]=$_REQUEST["fecha"];
    $arrF["nrofactura"]=$_REQUEST["nrofactura"];
    $arrF["importe"]=$_REQUEST["importe"];
    $arrF["cancelado"]=$_REQUEST["cancelado"];
    $arrF["saldo"]=$_REQUEST["saldo"];
    $arrF["aimputar"]=$_REQUEST["aimputar"];
    // agregar el tipo de factura y el numero de factura 
    $arrF['tipocomprobante']=$_REQUEST["tipocomprobante"];
    $arrF['vencimiento']=$_REQUEST["vencimiento"];
    $arrF['condventa']=$_REQUEST["condventa"];
    
    imputar_factura($arrF);
    
        
    
}

// desimputar factura

if(isset($_REQUEST['desimputarFactura'])&&$_REQUEST['desimputarFactura']==1){
    $idrecibofactura = $_REQUEST["idrecibofactura"];
    desimputar_factura($idrecibofactura);
}


// total recibo 
if(isset($_REQUEST["totalRecibo"])&&$_REQUEST["totalRecibo"]==1){
   actualiza_total();
   
}

// resumen de facturas

if(isset($_REQUEST["finImputacion"])&&$_REQUEST["finImputacion"]==1){
    resumen_facturas_listas();
}   


// alta de retencion
if(isset($_REQUEST["altaRetencion"])&&$_REQUEST["altaRetencion"]==1){
    $arrRet['cod']=$_REQUEST["cod"];
    $arrRet['tipo']=$_REQUEST["tipo"];
    $arrRet['certificado']=$_REQUEST["certificado"];
    $arrRet['fecha']=$_REQUEST["fecha"];
    $arrRet['porcentaje']=$_REQUEST["porcentaje"];
    $arrRet['monto'] = $_REQUEST["monto"];
                
    alta_retencion($arrRet);
} 
// borrar retencion
if(isset($_REQUEST["bajaRetencion"])&&$_REQUEST["bajaRetencion"]==1){
    $key=$_REQUEST["key"];
    borrar_retencion($key);
} 
// vaciar la retenciones
if(isset($_REQUEST["vaciarRetencion"])&&$_REQUEST["vaciarRetencion"]==1){
    vaciar_retenciones();
}

if(isset($_REQUEST["traeDescuentos"])&&$_REQUEST["traeDescuentos"]==1){
    
    trae_descuentos($connV);
}
if(isset($_REQUEST["traeRetencionCli"])&&$_REQUEST["traeRetencionCli"]==1){
    
    trae_retencion_cli($connV);
}
if(isset($_REQUEST["listaRetencion"])&&$_REQUEST["listaRetencion"]==1){
    
   lista_retencion();
}

if(isset($_REQUEST["altaDescuento"])&&$_REQUEST["altaDescuento"]==1){
    $porcentaje=$_REQUEST['porcentaje'];
    alta_descuento($porcentaje);
}

// borro un descuento

if(isset($_REQUEST["bajaDescuento"])&&$_REQUEST["bajaDescuento"]==1){
    borrar_descuento();
}

/*
 * MEDIOS DE COBRO
 * ============================================================================
 * ============================================================================
 */

// EFECTIVO
// ============================================================================

// control de totales
//if(isset($_REQUEST["totalReciboEfectivo"])&&$_REQUEST["totalReciboEfectivo"]==1){
//    total_recibo_efectivo();
//} 
// guardo el efectivo
if(isset($_REQUEST["altaEfectivo"])&&$_REQUEST["altaEfectivo"]==1){
    $arrEfe['idcaja']=$_REQUEST["idcaja"];
    $arrEfe['efectivo']=$_REQUEST["pesos"];
    
    $arrEfe['dolar']=$_REQUEST["dolar"];
    $arrEfe['coti']=$_REQUEST["coti"];
    $arrEfe['subtotal']=$_REQUEST["subtotal"];
//echo "<pre>";
//print_r($_REQUEST);
//echo "</pre>";
alta_efectivo($arrEfe);
    
}

// baja de efectivo.
if(isset($_REQUEST["bajaEfectivo"])&&$_REQUEST["bajaEfectivo"]==1){
    $tipo=$_REQUEST["tipo"];
    borrar_efectivo($tipo);
}


// traigo la caja efectivo del usuario.
if(isset($_REQUEST["traeCajaEfectivo"])&&$_REQUEST["traeCajaEfectivo"]==1){
    trae_caja_efectivo($connV);
}

// cotizacion del dolar
if(isset($_REQUEST["traeCotiDolar"])&&$_REQUEST["traeCotiDolar"]==1){
    trae_coti_dolar($connV);
}


// CHEQUES
// ============================================================================
// caje de cheque
if(isset($_REQUEST["traeCajaCheque"])&&$_REQUEST["traeCajaCheque"]==1){
    trae_caja_cheque($connV);
}

// trae banco

if(isset($_REQUEST["traeBancos"])&&$_REQUEST["traeBancos"]==1){
    trae_bancos($connV);
    
}

// alta de cheque
if(isset($_REQUEST["altaCheque"])&&$_REQUEST["altaCheque"]==1){
    $arrCh=array();
    $arrCh['codbanco']=$_REQUEST["codbanco"]; 
    $arrCh['banco']=$_REQUEST["banco"]; 
    $arrCh['cuitbanco']=$_REQUEST["cuitbanco"]; 
    $arrCh['librador']=$_REQUEST["librador"]; 
    $arrCh['cuitlibrador']=$_REQUEST["cuitlibrador"]; 
    $arrCh['numero']=$_REQUEST["numero"]; 
    $arrCh['importe']=$_REQUEST["importe"]; 
    $arrCh['emison']=$_REQUEST["emison"];     
    $arrCh['cobro']=$_REQUEST["cobro"]; 
    $arrCh['tipo']=$_REQUEST["tipo"];
    
    alta_cheque($arrCh);
}
// totales de cheques.

if(isset($_REQUEST['totalReciboCheque'])&&$_REQUEST['totalReciboCheque']==1){
    total_recibo_cheque();
    
}


// listado de cheques agregados

if(isset($_REQUEST['listaCheques'])&&$_REQUEST['listaCheques']==1){
    $idCajaCheque=$_REQUEST['idCaja'];
    lista_cheques($idCajaCheque);
}

if(isset($_REQUEST['borraCheque'])&&$_REQUEST['borraCheque']==1){
    $arrCh['cod']=$_REQUEST["cod"];
    $arrCh['numero']= $_REQUEST['numero'];
    $arrCh['importe']= $_REQUEST['importe'];
    borrar_cheque($arrCh);
}

// vaciar todos los cheques.
if(isset($_REQUEST["vaciarCheques"])&&$_REQUEST["vaciarCheques"]==1){
    vaciar_cheques();
}


/*
 * FIN RECIBO: RESUMEN
 * ============================================================================
 */

// resumen del recibo.

if(isset($_REQUEST["traeResumenRecibo"])&& $_REQUEST["traeResumenRecibo"]==1){
    trae_resumen_recibo();
}

// trae resumen tabla imputacion.
if(isset($_REQUEST["traeResumenImputacion"])&& $_REQUEST["traeResumenImputacion"]==1){
    trae_resumen_imputacion();
}

// trae resumen medios cobro con el final del recibo
if(isset($_REQUEST["traeResumenMedios"])&& $_REQUEST["traeResumenMedios"]==1){
    trae_resumen_medios();
}

// control final del recibo en resumen 
if(isset($_REQUEST["controlFinalRecibo"])&& $_REQUEST["controlFinalRecibo"]==1){
    control_final_recibo();
}


/*
 *  FIN RECIBO GUARDAR EN BASE DE DATOS
 * =============================================================================
 */

// recuperar la sesion completita.
if(isset($_REQUEST["guardarRecibo"])&& $_REQUEST["guardarRecibo"]==1){
    
//    echo "<pre>";
//    print_r($_SESSION["recibo"]);
//    echo "</pre>";
    
    guardar_recibo($connV);
}

/*
 * SALIR DE UN RECIBO 
 * =============================================================================
 */

if(isset($_REQUEST["salirRecibo"])&& $_REQUEST["salirRecibo"]==1){
    salir_recibo();
}

