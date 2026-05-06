<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
$nCampos = Array();
require_once("../sesion.inc.php");

$decimales=2;
$where='';
if(isset($_REQUEST['decimales']))
    if(is_numeric($_REQUEST['decimales']))$decimales=$_REQUEST['decimales'];


$idCaja="Todos";
if(isset($_REQUEST['idCaja']))
    if(is_numeric($_REQUEST['idCaja']))$idCaja=$_REQUEST['idCaja'];

if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
            $periodo="caja.fecha  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."' AND";
//9-06&fechaDesdeDos=&fechaHastaDos=
if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
        if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{ 
		$periodo="(caja.fecha  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."'  
		OR caja.fecha BETWEEN '".$_REQUEST['fechaDesdeDos']."' AND '".$_REQUEST['fechaHastaDos']."' )  
		AND";
			
			}


$puntodeventa='';
$vectorsito= Array();
    

    
if(isset($_REQUEST['listarPor']))$listarPor=$_REQUEST['listarPor'] ;

if(is_numeric($idCaja))
if($idCaja>0 && $idCaja<9999)
$where = $where." AND caja.id_caja_abm_origen='".$idCaja."'";

require_once "tiporesumen.php";

  $sql=" SELECT '' as ID,  date_format(caja.fecha,'".$rango."') as Fecha, caja.tipo_comprobante as Comprobante, 
 caja.nro_comprobante as 'Nro. Comp.', caja.tipo as Tipo, 
 if (caja.tipo_cp='Cliente',cliente.nombre_cliente,proveedor.Nombre) as 'Cliente/Proveedor', 
 FORMAT(if( caja.ingreso is NOT NULL,caja.ingreso,0), ".$decimales.", 'es_AR') as  Ingreso,
 FORMAT(if( caja.egreso is NOT NULL,caja.egreso,0), ".$decimales.", 'es_AR') as  Egreso,
 FORMAT(if( caja.ingreso-caja.egreso is NOT NULL,caja.ingreso-caja.egreso,0), ".$decimales.", 'es_AR') as  Saldo,
 caja.tipo_cp, caja.detalle as Detalle
 FROM   {oj (cliente cliente LEFT OUTER JOIN caja caja ON cliente.Codigo=caja.codigo_cliente) 
 LEFT OUTER JOIN proveedor proveedor ON caja.codigo_prov=proveedor.Codigo}
WHERE ".$periodo." 1=1 ".$where." 
 
 "; 
 //FORMAT(if( caja.ingreso-caja.egreso is NOT NULL,caja.ingreso-caja.egreso,0), ".$decimales.", 'es_AR') as  Saldo,
 $sql=" 
 SELECT '' as '#',  
        date_format(caja.fecha,'".$rango."') as Fecha, 
        caja.tipo_comprobante as Comp, 
        caja.nro_comprobante as 'Nro. Comp.', 
        caja.tipo as Tipo, 
        if (caja.tipo_cp='Cliente',cliente.nombre_cliente,proveedor.Nombre) as 'Cliente/Proveedor', 
        FORMAT(if( caja.ingreso is NOT NULL,caja.ingreso,0), ".$decimales.", 'es_AR') as  Ingreso,
        FORMAT(if( caja.egreso is NOT NULL,caja.egreso,0), ".$decimales.", 'es_AR') as  Egreso,
        FORMAT(if(caja.tipo_comprobante='MCAJ',@T:=0, @T:=@T+(caja.ingreso-caja.egreso)),".$decimales.", 'es_AR') as Saldo,
       
        caja.detalle as Detalle

 FROM   {oj (cliente cliente LEFT OUTER JOIN caja caja ON cliente.Codigo=caja.codigo_cliente) 
 LEFT OUTER JOIN proveedor proveedor ON caja.codigo_prov=proveedor.Codigo}
WHERE ".$periodo." 1=1 ".$where." 
ORDER BY caja.Fecha ASC, caja.id_caja ASC 
 ";
 /* reporte 192
 
 
 
 
 */

    





function connectDB(){
    global $servidor;
    global $baseConecto;
    
        $server = $servidor;
        $user = "administranet";
        $pass = "a7v8xx0805";
        $bd = $baseConecto;

    $conexion = mysqli_connect($server, $user, $pass,$bd);

        if($conexion){
    //        json_encode( 'La conexion de la base de datos se ha hecho satisfactoriamente');
        }else{
            json_encode( 'Ha sucedido un error inexperado en la conexion de la base de datos');
			return false;
        }

    return $conexion;
}

function disconnectDB($conexion){

    $close = mysqli_close($conexion);

        if($close){
         //   echo 'La desconexion de la base de datos se ha hecho satisfactoriamente';
        }else{
       //     echo 'Ha sucedido un error inexperado en la desconexion de la base de datos';
        }   

    return $close;
}
/*
function getArraySQL($sql){
    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	global $nCampos;
	

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

    $rawdata = array(); //creamos un array

    
    $i=0;
    


	$versor = Array();

	$x=0;
	$contar = mysqli_num_rows($result);
    $contar--;
	while($row = mysqli_fetch_object($result))
	{
	$ide = $row->Codigo;
	$saldo = floatval($row->SALDO);
	$fechita = $row->FECHITA;
	$ncliente = $row->NCLIENTE;
	
	
	$bandera=1;
	for($y=0;$y<sizeof($versor);$y++){
       
		if(isset($versor[$y]['id']))
		if($versor[$y]['Nombre']==$ncliente){
            
		
        $versor[$y] = array_merge($versor[$y], [$fechita=>$saldo]); 
		$bandera=2;
            
		}
		
	}
	if($bandera==1){

           $versor[]= ['id'=>$ide,'Nombre'=>$ncliente, $fechita=>$saldo ];
           }

	$x++;
	
	}

    
  
    
  foreach($versor as $indice => $valor)
{
	
	for($x=2;$x<sizeof($nCampos);$x++){
        $fecha = $nCampos[$x]['title'];
        

        if(!isset($versor[$indice][$fecha]))
        $versor[$indice][$fecha]=0;

										}
}  
    

    disconnectDB($conexion); //desconectamos la base de datos


return $versor;
}

*/





////////////////
/*
function getArraySQL0($sql){
    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	global $nCampos;
	

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(); //si la conexión cancelar programa

    $rawdata = array(); //creamos un array

    //guardamos en un array multidimensional todos los datos de la consulta
    $i=0;
  
	$versor = Array();

	
	$x=0;
	while($row = mysqli_fetch_object($result))
	{
	$ide = $row->Codigo;
	$saldo = floatval($row->SALDO);
	$fechita = $row->FECHITA;
	$ncliente = $row->NCLIENTE;
	$ncliente = ($ncliente);
    $bandera = 1;
        for($y=0;$y<$x;$y++){
           
  
            if(isset($vector[$y]['id']))
            if($vector[$y]['id']==$ide){
           
             $vector[$y] = array_merge($vector[$y], [$fechita=>$saldo]); 
            
              
                $bandera=2;
            }
        }
        
	
if($bandera==1)$vector[$x] =  ['id'=>$ide,'Nombre'=>$ncliente, $fechita=>$saldo];


   
   
        $x++;
	}


foreach($vector as $indice => $valor)
{
	
	for($x=2;$x<sizeof($nCampos);$x++){
        $fecha = $nCampos[$x]['title'];
        

        if(!isset($vector[$indice][$fecha]))
        $vector[$indice][$fecha]=0;

										}
}

    
    disconnectDB($conexion); //desconectamos la base de datos
	
return $vector;
}
///////////////////////////
*/
if(!isset($sql))die("SQL No Terminado");
/*
$Totales = Array();

$myArray = getArraySQL($sql);
    

$Totales["Nombre"]="Total: ";
foreach($nCampos as $clave => $valor)
    {
	
     if($clave>1)
        {
        $fecha= $valor['title'];
        
 

       if(isset($Totales[$fecha])===false)$Totales[$fecha]=0;

         for($n=0;$n<sizeof($myArray);$n++)
            {

            
            if(isset($myArray[$n][$fecha]))$Totales[$fecha]= floatval($myArray[$n][$fecha])+floatval($Totales[$fecha]);
           
				

            
            }
            

        }
     

    }
      
	 $mivector = Array();
$mivector =  getArraySQL($sql);
$vectorBIS =  $mivector;
$n=0;
$fmt = new NumberFormatter( 'es_AR', NumberFormatter::CURRENCY );
foreach($vectorBIS as $valor)
{
        foreach($valor as $indice => $variable){
            if($indice!='id' && $indice!='Nombre'){
                $nvalor = $variable;
                $vectorBIS[$n][$indice]= $fmt->formatCurrency($variable, "ARS");
              // echo "Indice ".$indice." variable ".$variable.'  '.$vectorBIS[$n][$indice].PHP_EOL;  
            }
           
        }
    $n++;
    
}

$TotalesBIS = $Totales;
foreach($TotalesBIS as $indice => $variable){
    
    if($indice!="Nombre")
    {
     $TotalesBIS[$indice]= $fmt->formatCurrency($variable, "ARS");
    }
}


*/





$_SESSION["SQL"]=$sql;
$_SESSION["INFORMESQL"]=$sql;
$_SESSION["IVARIABLES"]=$_REQUEST;
/*
$_SESSION["columnas"]=$nCampos;
$_SESSION["data"]=$myArray;
$_SESSION["footer"]=$Totales;
$_SESSION["datos"]=$mivector;
$_SESSION["datosFormateado"]=$vectorBIS;
$_SESSION["totalesFormateado"]=$TotalesBIS;*/
$_SESSION["titulo"]='Caja efectivo';
if(isset($_REQUEST['titulo']))$_SESSION["titulo"]=$_REQUEST['titulo'];


        $vv = '[{"REQUEST":'.json_encode($_REQUEST).',"SQL":'.json_encode($sql).'}]';
       // $vv = '[{"columns":'.json_encode($nCampos).',"data":'.json_encode($myArray).',"footer":'.json_encode($Totales).',"REQUEST":'.json_encode($_REQUEST).',"SQL":'.json_encode($sql).',"datos":'.json_encode($mivector).',"datosFormateado":'.json_encode($vectorBIS).',"totalesFormateado":'.json_encode($TotalesBIS).'}]';
		echo $vv;
		//echo $sql;

?>
