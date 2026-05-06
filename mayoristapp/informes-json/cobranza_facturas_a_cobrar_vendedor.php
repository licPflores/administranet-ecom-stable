
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


if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
            $periodo="recibo_factura.Fecha  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."' AND";
//9-06&fechaDesdeDos=&fechaHastaDos=
if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
        if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{ 
		$periodo="(cuentacliente.Fecha  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."'  
		OR recibo_factura.Fecha  BETWEEN '".$_REQUEST['fechaDesdeDos']."' AND '".$_REQUEST['fechaHastaDos']."' )  
		AND";
			
			}


			
			
//FILTRADO CLIENTE
    // filtro valores

    $arrFiltros = array();
	if(isset($_REQUEST['filtrarPor']))
    if(!empty($_REQUEST['filtrarPor'])){
		$listaFiltro = explode('||',$_REQUEST['filtrarPor']);
       
	   foreach ($listaFiltro as $valorFiltro){
		
		 $datoFiltro = explode("|",$valorFiltro);
      
            if(isset($datoFiltro[2])){
                $arrFiltros[$datoFiltro[0]][] = $datoFiltro[2];
            }
     
	}
    }
$clientes=Array();
$vendedores=Array();
foreach($arrFiltros as $indice => $valor)
{
		

	if(isset($valor))
	{
	switch($indice){
		case "cliente":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		$clientes[]=$matches['digito'];

		}

		break;
		
		case "vendedor":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		$vendedores[]=$matches['digito'];

		}

		break;
		
	
			}
		
		
		
		 
	}
}


if(sizeof($clientes)>0)
$where .= "\n  AND  cuentacliente.Codigo IN ('".implode("','",$clientes)."') ".PHP_EOL;


if(sizeof($vendedores)>0)
$where .= "\n  AND  cuentacliente.CodViajante IN ('".implode("','",$vendedores)."') ".PHP_EOL;






$puntodeventa='';
$vectorsito= Array();
    
    
    
if(isset($_REQUEST['listarPor']))$listarPor=$_REQUEST['listarPor'] ;

require_once 'tiporesumen.php';

$sql="SELECT cuentacliente.id_cuentacliente as Codigo,
CONCAT(viajantes.Nombre,' (', viajantes.CodViajante,')') AS NCLIENTE,
 DATE_FORMAT(recibo_factura.Fecha,'".$rango."') AS FECHITA,
 ROUND(SUM(
 IF(ISNULL(cuentacliente.ImporteCobro),IF(ISNULL(cuentacliente.ImporteVenta),0,recibo_factura.Saldo), recibo_factura.Saldo*(-1))
 ),".$decimales.") AS SALDO
 FROM   ((cuentacliente cuentacliente 
 INNER JOIN cliente cliente ON cuentacliente.Codigo=cliente.Codigo) INNER JOIN recibo_factura recibo_factura ON cuentacliente.CodigoMovimiento=recibo_factura.CodigoMovimiento) INNER JOIN viajantes viajantes ON cliente.CodViajante=viajantes.CodViajante
 WHERE ".$periodo." 
  recibo_factura.TipoComprobante <> 'INIC' 
  And recibo_factura.Saldo <> 0 
  And recibo_factura.Anulado = 'No'
  And recibo_factura.Estado = 'N/Canc' ".$where."
GROUP BY NCLIENTE,FECHITA
 ORDER BY FECHITA,NCLIENTE";
//////////////
$sql="SELECT cuentacliente.id_cuentacliente as Codigo,
CONCAT(viajantes.Nombre,' (', viajantes.CodViajante,')') AS NCLIENTE,
 DATE_FORMAT(recibo_factura.Fecha,'".$rango."') AS FECHITA,
 ROUND(SUM(
IF(cuentacliente.ImporteCobro IS NOT NULL, recibo_factura.Saldo*(-1),
	IF(cuentacliente.ImporteVenta IS NOT NULL,recibo_factura.Saldo,999999999999) )
 ),".$decimales.") AS SALDO
 FROM   ((cuentacliente cuentacliente 
 INNER JOIN cliente cliente ON cuentacliente.Codigo=cliente.Codigo) INNER JOIN recibo_factura recibo_factura ON cuentacliente.CodigoMovimiento=recibo_factura.CodigoMovimiento) INNER JOIN viajantes viajantes ON cliente.CodViajante=viajantes.CodViajante
 WHERE ".$periodo." 
  recibo_factura.TipoComprobante <> 'INIC' 
  And recibo_factura.Saldo <> 0 
  And recibo_factura.Anulado = 'No'
  And recibo_factura.Estado = 'N/Canc' ".$where."
GROUP BY NCLIENTE,FECHITA
 ORDER BY FECHITA,NCLIENTE";
/*form 20
{recibo_factura.TipoComprobante} <> 'INIC' And {recibo_factura.Saldo} <> 0 And
 ({recibo_factura.Fecha} >= #05-01-2018# and {recibo_factura.Fecha} <= #05-07-2018#) And 
 {recibo_factura.Anulado} = 'No' And {recibo_factura.Estado} = 'N/Canc'
==-------------------------------------------------------
 if not isnull({cuentacliente.ImporteCobro}) then
    formula = ({recibo_factura.Saldo}*(-1))
elseif not isnull ({cuentacliente.ImporteVenta}) then
    formula = {recibo_factura.Saldo}
end if

Traducido:

IF(cuentacliente.ImporteCobro IS NOT NULL, recibo_factura.Saldo*(-1),
	IF(cuentacliente.ImporteVenta IS NOT NULL,recibo_factura.Saldo,999999999999)
)


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

function getArraySQL($sql){
    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	global $nCampos;
	if(!isset($sql))die("No tengo SQL");

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
	//$ncliente = ucwords( strtolower($ncliente));
	
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

//     return $rawdata; //devolvemos el array
return $versor;
}







////////////////
function getArraySQL0($sql){
    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	global $nCampos;
	if(!isset($sql))die("No tengo SQL");

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
           //   $vector[$y] =  ['id'=>$ide,'Nombre'=>$ncliente, $fechita=>$saldo]; 
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



$Totales = Array();
if(!isset($sql))die("No tengo SQL linea 457");
$myArray = getArraySQL($sql);
    
//$Totales["id"]=999999999999999;
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

$_SESSION["SQL"]=$sql;
$_SESSION["INFORMESQL"]=$sql;
$_SESSION["IVARIABLES"]=$_REQUEST;
$_SESSION["columnas"]=$nCampos;
$_SESSION["data"]=$myArray;
$_SESSION["footer"]=$Totales;
$_SESSION["datos"]=$mivector;
$_SESSION["datosFormateado"]=$vectorBIS;
$_SESSION["totalesFormateado"]=$TotalesBIS;
$_SESSION["titulo"]='Cobranza facturas a cobrar Vendedor';    
        $vv = '[{"columns":'.json_encode($nCampos).',"data":'.json_encode($myArray).',"footer":'.json_encode($Totales).',"REQUEST":'.json_encode($_REQUEST).',"SQL":'.json_encode($sql).',"datos":'.json_encode($mivector).',"datosFormateado":'.json_encode($vectorBIS).',"totalesFormateado":'.json_encode($TotalesBIS).'}]';
		echo $vv;
		//echo $sql;

