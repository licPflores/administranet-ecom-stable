<?php
require_once 'sesion.inc.php';
// funciones de alta cliente.

function tipo_cliente($connV){
    $sql="SELECT tc.IDTipoCliente,tc.NombreTipoCliente FROM tipo_cliente AS tc "
            . "WHERE tc.Anulado='No' "
            . "ORDER BY tc.NombreTipoCliente ASC";
    $hacer=mysqli_query($connV,$sql) or die("no puedo recuperar el tipo de cliente".mysqli_error($connV)."<pre>".$sql."</pre>");
    $tp=array();
    while($t=mysqli_fetch_assoc($hacer)){
        $tp[$t["IDTipoCliente"]]=$t["NombreTipoCliente"];
    }

//    return json_encode($tp);
    return $tp;
}

function tipo_iva($connV){
    $sql="SELECT * FROM Contribuyentes AS c ORDER BY IDIva ASC";
    $hacer=mysqli_query($connV,$sql) or die("no puedo recuperar el iva".mysqli_error($connV)."<pre>".$sql."</pre>");
    $iv=array();
    while($i=mysqli_fetch_assoc($hacer)){
        $iv[$i["IDIva"]]=$i["Abreviado"];
    }
//    return json_encode($iv);
    return $iv;
}
/*
 * buscaProvincia
 */

function busca_provincia($idPais=null,$connV){
    $where="";
    if($idPais){
        $where.=" AND p.id_pais=".$idPais;
    }
    $sqlProv="SELECT p.CodProvincia AS codigo ,p.Provincia as provincia "
            . "FROM provincia AS p "
            . "WHERE p.Anulado='No' {$where} "
            . "ORDER BY p.Provincia ASC";
    $hacer= mysqli_query($connV,$sqlProv) or die ("error pronvicia ".mysqli_error($connV)."<pre>".$sqlProv."</pre>");
    if($hacer){
        $prov = array();
        while($p = mysqli_fetch_assoc($hacer)){
            $prov[$p["codigo"]]=$p["provincia"];
        }
//        return json_encode($prov);
        return $prov;
    }else{
        return false;
    }
}

function busca_departamento($idProvincia,$connV){
    $where="";
    if($idProvincia){
        $where.=" AND dp.CodProvincia=".$idProvincia;
    }
    $sqlDepto="SELECT dp.IDDepartamento AS codigo ,dp.NombreDepartamento AS depto "
            . "FROM departamento AS dp "
            . "WHERE dp.Anulado='No' {$where} "
            . "ORDER BY dp.NombreDepartamento ASC";
    $hacer= mysqli_query($connV,$sqlDepto) or die ("error pronvicia ".mysqli_error($connV)."<pre>".$sqlDepto."</pre>");
    if($hacer){
        $depto = array();
        while($dp = mysqli_fetch_assoc($hacer)){
            $depto[$dp["codigo"]]=$dp["depto"];
        }
        return json_encode($depto);
    }else{
        return false;
    }
}
function busca_distrito($idDepartamento,$connV){
    $where="";
    if($idDepartamento){
        $where.=" AND d.IDDepartamento=".$idDepartamento;
    }
    $sqlProv="SELECT d.IDDistrito AS codigo ,d.NombreDistrito AS distrito "
            . "FROM distrito AS d "
            . "WHERE d.Anulado='No' {$where} "
            . "ORDER BY d.NombreDistrito ASC";
    $hacer= mysqli_query($connV,$sqlProv) or die ("error distrito ".mysqli_error($connV)."<pre>".$sqlProv."</pre>");
    if($hacer){
        $di = array();
        while($d = mysqli_fetch_assoc($hacer)){
            $di[$d["codigo"]]=$d["distrito"];
        }
        return json_encode($di);
    }else{
        return false;
    }
}

function alta_cliente($p,$connV){
    //viajante default
   
    $status=array();
    $usuario = $_SESSION["vendedor"];
    // $listaPrecio =$_SESSION["lista_precio_defecto"];// Lista 1
    $listaPrecio = $p["listaPrecio"];
    $idSucursal = $_SESSION['id_sucursal'];
    $codViajante = $usuario->CodViajante;
    $tipoDocC=$p["tipoDocCliente"];
    $nombreC= $p["nombreCliente"];
    $cuitC=$p["nroCuitCliente"];
    $dniC = $p["nroDocCliente"];
    $fechaAlta=date("Y/m/d");
    // controlo cliente existente.
    $textoError = valida_cliente_existe($tipoDocC, $nombreC, $cuitC, $dniC,null,$connV);
    if(!empty($textoError)){
        $status["estado"] = "error";
        $status["cartel"] = join(" | ", $textoError);
        return $status;
    }
    
    
    if($tipoDocC=="CUIT"){
        $docu = $cuitC;
    }else{
        $docu=$dniC;
    }

    
    // cuenta contable por defecto
    $id_pc= trae_cuenta_contable_defecto($connV);
    $sql="INSERT INTO cliente SET "
            . "TipoCliente ='".$p["tipoCliente"]."',"
            . "nombre_cliente ='".$p["nombreCliente"]."',"
            . "Descuento = 0,"
            . "Credito = 0,"
            . "CodViajante =".$codViajante.","
            . "CUIT ='".$docu."',"
            . "tipo_doc ='".$tipoDocC."',"
            . "IDIVA = '".$p["ivaCliente"]."',"
            . "estado = 'Activo',"
            . "ListaPrecio = '".$listaPrecio."',"
            . "FechaAlta = '".$fechaAlta."',"
            . "id_cv = 1,"
            . "id_sucursal = '".$idSucursal."',"
            . "Telefono ='".$p["telefonoCliente"]."',"
            . "Fax ='".$p["faxCliente"]."',"       
            . "Email ='".$p["emailCliente"]."',"            
            . "Calle = '".$p["calleCliente"]."',"
            . "NroCalle = '".$p["numeroCliente"]."',"    
            . "Dpto = '".$p["deptoCliente"]."',"            
            . "CodProvincia = '".$p["provinciaCliente"]."',"           
            . "idDepartamento ='".$p["departamentoCliente"]."'," 
            . "IDDistrito ='".$p["distritoCliente"]."',"    
            . "id_pc = '".$id_pc."'";
    
    $hacer = mysqli_query($connV,$sql) or die("error de cliente".mysqli_error($connV)."<br><pre>".$sql."</pre>");
    $codCliente=false;
   if($hacer){
       $codCliente= mysqli_insert_id($connV);
       
       // alta del domicilio en el cliente domicilio repito despues que se modificque por si falla el pedido.
       $sql="INSERT INTO cliente_domicilio SET "            
        . "Calle='{$p["calleCliente"]}',"
        . "NroCalle='{$p["numeroCliente"]}',"
        . "Dpto='{$p["deptoCliente"]}',"
        . "IDDistrito='{$p["distritoCliente"]}',"
        . "CodProvincia='{$p["provinciaCliente"]}',"
        . "IDDepartamento='{$p["departamentoCliente"]}',"
        //. "id_zona='{$p["zonaCliente"]}',"
        . "id_zona='1',"
        . "id_cliente='{$codCliente}',"
        //. "hora_desde='{$hDesde}',"
        //. "hora_hasta='{$hHasta}',"
        //. "periodicidad_visita_vendedor='{$p["visitaVendedor"]}',"
        //. "visita_vendedor_valor='{$p["intervaloVisita"]}',"
        . "anulado='No';";
        $hacerD =mysqli_query($connV,$sql);
        if(!$hacerD){
               echo "no ando domicilio".$sql;
        }
   } 
   return $codCliente;
}

function trae_cuenta_contable_defecto($connV){
    // recuperar de la contabilidad la cuenta contable defecto
    $sql="SELECT id_pc FROM cont_paramatriz WHERE id_paramatriz = 1";
    $hacer=mysqli_query($connV,$sql) or die("error matriz ".mysqli_error($connV)."<pre>".$sql."</pre>");
    $arrConta=mysqli_fetch_assoc($hacer);
    return $arrConta["id_pc"];
   
}

function valida_cliente_existe($tipoDocC,$nombreC,$cuitC,$dniC,$idCliente=null,$connV){
    $errNombreCliente=0;
    $errCuit=0;
    $textoError=array();
    $docValido="";
    $where="";
    if($tipoDocC=="CUIT"){
        $docValido=$cuitC;
    }else{
        $docValido=$dniC;
    }
    if($idCliente){
        $where .=" cliente.Codigo<>".$idCliente." AND " ;
    }
    // validar que no exista el nombre
    $sql="SELECT cliente.Codigo,cliente.nombre_cliente,cliente.CUIT "
            . "FROM cliente "
            . "WHERE ".$where
            . " cliente.CUIT<>'00-00000000-0' AND cliente.CUIT<>0 "
            . "AND cliente.tipo_doc='".$tipoDocC."' "
            . "AND (cliente.nombre_cliente='".$nombreC."' OR cliente.CUIT='".$docValido."')";
    // validar que no exista el cuit o dni
    $hacer = mysqli_query($connV,$sql)or die("no puedo recuperar los clientes".mysqli_error($connV)."<pr>".$sql."</pre>");
    while($cc=mysqli_fetch_assoc($hacer)){
        if($nombreC==$cc["nombre_cliente"]){
            $errNombreCliente++;
        }
        if($docValido==$cc["CUIT"]){
            $errCuit++;
        }
    }
    if($errCuit<>0){
        $textoError[]="El Cuit - Dni ingresado ya existe en el sistema";
    }
    if($errNombreCliente<>0){
        $textoError[]="El nombre del cliente ya se encuentrea en el sistema";
    }
    return $textoError;
}

function obtiene_cliente($codigo){
    
}

/*
 * function: selecciona_cliente
 * Obtiene datos del cliente y lo carga en la sesion como seleccionado
 * ************
 */

function selecciona_cliente($codigo,$connV){
    $usuario = $_SESSION["vendedor"];
    $codViajante = $usuario->CodViajante;
//    $codigo = 936;
    $sqlCliente = "SELECT 
                    cliente.nombre_cliente AS cliente,
                    cliente.id_cv,
                    cond_venta.Descripcion AS condVenta,
                    cliente.listaPrecio,
                    SUBSTRING(cliente.listaPrecio,6) AS codListaPrecio,
                    {$codViajante} AS codViajante,
                    cliente.Credito,
                    cliente.credito_limite_dias,
                    cliente.id_sucursal,
                    cliente.saldo,
                    cliente.Codigo,
                    cliente.TipoCliente,
                    cliente.Descuento AS descPie,
                    cliente.Email AS email,
                    cliente.EmailContacto As emailcontacto,
                    cliente.descuento_por_cli AS descRenglon,
                    cond_venta.descuento AS descCondventa,
                    contribuyentes.IDIva,
                    contribuyentes.abreviado
                    
                FROM cliente
                LEFT JOIN cond_venta ON cond_venta.Codigo = cliente.id_cv
                LEFT JOIN contribuyentes ON contribuyentes.IDIva = cliente.IDIva
                
                WHERE cliente.Codigo={$codigo}";
    $hacer = mysqli_query($connV,$sqlCliente) or die('No puedo recuperar el cliente' . mysqli_error($connV).$sqlCliente);
    if($hacer){
        $objClienteBusq = mysqli_fetch_object($hacer);
        $sqlAtraso =    "SELECT 
                            MIN(cuentacliente.Fecha) as ultimaf 
                        FROM CuentaCliente 
                        WHERE (cuentacliente.TipoComprobante = 'FA' OR 
                            cuentacliente.TipoComprobante = 'FB' OR 
                            cuentacliente.TipoComprobante = 'FC' OR
                            cuentacliente.TipoComprobante = 'FE' OR 
                            cuentacliente.TipoComprobante = 'FM' OR 
                            cuentacliente.TipoComprobante = 'NDA' OR 
                            cuentacliente.TipoComprobante = 'NDC' OR 
                            cuentacliente.TipoComprobante = 'NDE' OR 
                            cuentacliente.TipoComprobante = 'NDM' OR 
                            cuentacliente.TipoComprobante = 'NDB') AND 
                            cuentacliente.Estado = 'N/Canc' AND 
                            cuentacliente.Anulado = 'No' AND 
                            cuentacliente.Codigo = {$codigo}";
                                        
        $hacerDias = mysqli_query($connV,$sqlAtraso) or die('No puedo consultar los dias de atraso'.  mysqli_error($connV));                              
        $limitesCli = mysqli_fetch_object($hacerDias);
        $autorizaCredito = array();
        if(($limitesCli->ultimaf) && ($limitesCli->ultimaf !='')){
              
//          Resto ultima fecha de F o Nd a la fecha actual
            $datetime1 = strtotime(date('Y-m-d'));
            $datetime2 = strtotime($limitesCli->ultimaf);
            $intervalo = round(abs($datetime1 - $datetime2)/60/60/24); 
            if($objClienteBusq->credito_limite_dias!=0 && $intervalo>$objClienteBusq->credito_limite_dias){
                $aut= 'No Autorizado';
                $detalle = 'Se sobrepaso el limite de vencimiento en dias';
                $autorizaCredito = array(
                                            'limite_credito_dias' => 'No autorizado',
                                            'dias_exceso_limite' => $intervalo ,
                                            'exceso' => 1);
            }else{
                $autorizaCredito = array(
                                            'limite_credito_dias' => 'Autorizado',
                                            'dias_exceso_limite' => 0,
                                            'exceso' => 0);
            }                            
        }
        
       if($objClienteBusq->IDIva==1){
           $ivaIncluido = 'no';
       }
       else{
           $ivaIncluido = 'si';
       }
       /**busco domicilios**/
       /* DOMICILIOS DE ENTREGA DEL CLIENTE*/
        $sqlDomicilios = "SELECT 
                            cm.id_cliente_domicilio AS idDom,
                            cm.Calle,
                            cm.NroCalle,
                            cm.Dpto,
                            pv.Provincia,
                            dp.NombreDepartamento,
                            dt.NombreDistrito,
                            z.nombre_zona,
                            z.id_zona
                            FROM cliente_domicilio AS cm
                            LEFT JOIN provincia  AS pv ON (pv.CodProvincia = cm.CodProvincia)
                            LEFT JOIN departamento AS dp ON (dp.IDDepartamento = cm.IDDepartamento)
                            LEFT JOIN distrito AS dt ON(dt.IDDistrito = cm.IDDistrito)
                            LEFT JOIN erp_zona AS z ON(z.id_zona = cm.id_zona)
                            WHERE cm.id_cliente = {$codigo}
                            AND cm.anulado ='No' ";
        $hacerDom = mysqli_query($connV,$sqlDomicilios) or die('No puedo recuperar los domicilios' . mysqli_error($connV) .'<pre>'.$sqlDomicilios.'</pre>');
        $domEntrega = array();
        while($dd = mysqli_fetch_assoc($hacerDom)){
            $domEntrega[] = $dd;
        }
       
//          echo '<pre>'.print_r($autorizaCredito).'</pre>';
        if(!empty($objClienteBusq)){
//            session_start();
            
            $_SESSION['cliente'] = array($objClienteBusq,$autorizaCredito);
            $_SESSION['idcliente'] = $codigo;
            $_SESSION['domicilios_cliente'] = $domEntrega;
            $_SESSION['ivaIncluido'] = $ivaIncluido;
            unset($_SESSION["jcart"]);
//            echo print_r($objClienteBusq);
            
        }else{
            $autorizaCredito = array();
            $_SESSION['cliente'] = array($objClienteBusq,$autorizaCredito);
            $_SESSION['idcliente'] = $codigo;
            $_SESSION['ivaIncluido'] = $ivaIncluido;
            $_SESSION['domicilios_cliente'] = $domEntrega;
            unset($_SESSION["jcart"]);
        }
    }
}



function edita_cliente($p,$connV){
    
    $status=array();
    $usuario = $_SESSION["vendedor"];
    $listaPrecio =$_SESSION["lista_precio_defecto"];
    $idSucursal = $_SESSION['id_sucursal'];
    $pCli= $_SESSION["permiso_alta_cliente"];
    $codViajante = $usuario->CodViajante;
    
    $idCliente=$p["codCliente"];
    
    if($pCli=="Si"){
    // controlo cliente existente.
        $tipoDocC=$p["tipoDocCliente"];
        $nombreC= $p["nombreCliente"];
        $cuitC=$p["nroCuitCliente"];
        $dniC = $p["nroDocCliente"];
        $textoError = valida_cliente_existe($tipoDocC, $nombreC, $cuitC, $dniC,$idCliente,$connV);
        if(!empty($textoError)){
            $status["estado"] = "error";
            $status["cartel"] = join(" | ", $textoError);
            return $status;
        }


        if($tipoDocC=="CUIT"){
            $docu = $cuitC;
        }else{
            $docu=$dniC;
        }
        
         $sql="UPDATE cliente SET "
            . "TipoCliente ='".$p["tipoCliente"]."',"
            . "nombre_cliente ='".$p["nombreCliente"]."',"
            . "CUIT ='".$docu."',"
            . "tipo_doc ='".$tipoDocC."',"
            . "IDIVA = '".$p["ivaCliente"]."',"          
            . "Telefono ='".$p["telefonoCliente"]."',"
            . "Fax ='".$p["faxCliente"]."',"       
            . "Email ='".$p["emailCliente"]."',"            
            . "Calle = '".$p["calleCliente"]."',"
            . "NroCalle = '".$p["numeroCliente"]."',"    
            . "Dpto = '".$p["deptoCliente"]."',"            
            . "CodProvincia = '".$p["provinciaCliente"]."',"           
            . "idDepartamento ='".$p["departamentoCliente"]."'," 
            . "IDDistrito ='".$p["distritoCliente"]."'"
            . " WHERE cliente.Codigo=".$idCliente;    
    }else{
        // no tengo permiso para editar el cliente asi que solo modifico
        // ciertos datos.
        
         $sql="UPDATE cliente SET "                
            . "Telefono ='".$p["telefonoCliente"]."',"            
            . "Email ='".$p["emailCliente"]."',"            
            . "Calle = '".$p["calleCliente"]."',"
            . "NroCalle = '".$p["numeroCliente"]."',"    
            . "Dpto = '".$p["deptoCliente"]."',"            
            . "CodProvincia = '".$p["provinciaCliente"]."',"           
            . "idDepartamento ='".$p["departamentoCliente"]."'," 
            . "IDDistrito ='".$p["distritoCliente"]."'"
            . " WHERE cliente.Codigo=".$idCliente;    
    }
    // cuenta contable por defecto
    //$id_pc= trae_cuenta_contable_defecto();
    
    $hacer = mysqli_query($connV,$sql) or die("error de cliente".mysqli_error($connV)."<br><pre>".$sql."</pre>");
    $codCliente=false;
    
   return $idCliente;
    
}

function actualizar_cliente_rapido($conexionT, $codViajante) {
    $todosClientes = $_SESSION['todos_clientes'];
    $usaIdManual= $_SESSION["usa_id_manual"];
    $whereC="";
    $campoId = "";
    if ($todosClientes == 'No') {
        $whereC .= " AND cliente.CodViajante =" . $codViajante;
    }


     if($usaIdManual=='Si'){
         $campoId = " COALESCE(cliente.id_manual_cli,cliente.Codigo)";
         
     }
     
     if($usaIdManual=='No'){
         $campoId = "cliente.Codigo";
         
     } 

     $sqlClientes= "SELECT 
                         {$campoId} AS codigo,
                         CONCAT(LTRIM(cliente.nombre_cliente), ' Cod: ',{$campoId}) AS nombre,
                         cliente.codigo AS id

                     FROM cliente 
                     
                     WHERE
                         cliente.Codigo <> 1
                         AND cliente.Estado='Activo'                            
                         {$whereC}                        

                     ORDER BY cliente.nombre_cliente";

// echo '<pre>',$sqlClientes,'</pre>';                    
     $hacerCli = mysqli_query($conexionT,$sqlClientes) or die('No puedo ubicar el busqueda rapida Cliente.'.  mysqli_error($conexionT) .'<br>'.$sqlClientes);
     $cRapido=array();
     while($cli=mysqli_fetch_assoc($hacerCli)){
         //  $cRapido[]=$cli["cliente"];
          $cRapido[]=$cli;


     }







    // $sqlClientes = "SELECT 
    //                          cliente.Codigo AS id,
    //                          cliente.nombre_cliente AS cliente
    //                     FROM cliente 
                        
    //                     WHERE
    //                     cliente.Codigo <> 1
    //                     AND cliente.Estado='Activo'                            
    //                     {$whereC}                        
    //                     ORDER BY cliente.nombre_cliente";


    // $hacerCli = mysqli_query($conexionT, $sqlClientes) or die('No puedo ubicar el busqueda rapida Cliente.' . mysqli_error($conexionT) . '<br>' . $sqlClientes);
    // $cRapido = array();
    // while ($cli = mysqli_fetch_assoc($hacerCli)) {
    //     $cRapido[] = $cli["cliente"];
    // }
    //$_SESSION["clienteRapido"]=json_encode(array());    
    $_SESSION["clienteRapido"] = json_encode($cRapido);
}

/*
 * CONTROLADOR DE ACCIONES
 * *****************************************************************************
 */
// inicializo parametros
if(isset($_REQUEST["accion"])){
    

    $accion=$_REQUEST["accion"];
    if(isset($_REQUEST["codCliente"])){$codCliente=$_REQUEST["codCliente"];}
    $idPais=null;
    if(isset($_REQUEST["idProvincia"])){$idProvincia=$_REQUEST["idProvincia"];}
    if(isset($_REQUEST["idDepartamento"])){$idDepartamento=$_REQUEST["idDepartamento"];}
    if(isset($_REQUEST["idDistrito"])) {$idDistrito=$_REQUEST["idDistrito"];}
    
    $vuelta="";
    switch($accion){
        case "altaCliente":
            $usuario = $_SESSION["vendedor"];
            $codigo=alta_cliente($_POST,$connV);
            if(!is_array($codigo)){
                if($codigo!=false){
                    $codViajante = $usuario->CodViajante;
                    selecciona_cliente($codigo,$connV);
                    actualizar_cliente_rapido($connV,$codViajante);
                }
                $vuelta=json_encode(array("status"=>"ok","cartel"=>"cliente <strpng>".$_POST["nombreCliente"]."</strong> ingresado con exito"));
                
            }else{
                $vuelta= json_encode(array("status"=>"error","cartel"=>$codigo));
            }
            break;
        case "editaCliente":
             $codigo= edita_cliente($_POST,$connV);
            if(!is_array($codigo)){
                if($codigo!=false){
                    selecciona_cliente($codigo,$connV);
                }
                $vuelta=json_encode(array("status"=>"ok","cartel"=>"cliente editado con exito"));
                
            }else{
                $vuelta= json_encode($codigo);
            }
            break;   
        case "obtieneCliente":
            $vuelta =obtiene_cliente($codCliente,$connV);
            break;
        case "provincia":
            $vuelta=busca_provincia($idPais,$connV);
            break;
        case "departamento":
            $vuelta=busca_departamento($idProvincia,$connV);
            break;
        case "distrito":
            $vuelta=busca_distrito($idDepartamento,$connV);
            break;
        case "tipoCliente":
            $vuelta=tipo_cliente($connV);
            break;
        case "ivaCliente":
            $vuelta=tipo_iva($connV);
            break;
        case "inicio":
            $arr["tipoCliente"]=tipo_cliente($connV);
            $arr["ivaCliente"]=tipo_iva($connV);
            $arr["provincia"]= busca_provincia($idPais,$connV);
            $vuelta = json_encode($arr);
    }
    echo  $vuelta;
        
}else{
    echo  false;
}        
/******************************************************************************/
