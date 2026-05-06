<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
$nCampos = Array();
require_once("../sesion.inc.php");

$decimales=2;
$where='';
$cuentabanco="Nada";
if(isset($_REQUEST['cuentabanco']))
    if(is_numeric($_REQUEST['cuentabanco']))$cuentabanco=$_REQUEST['cuentabanco'];
 /* Reporte 112
 Fecha de emision --> chequetercero.FechaEmision
Fecha de Cobro --> chequetercero.FechaCobro


*/

$tfecha = "chequetercero.FechaCobro";

if(isset($_REQUEST['tfecha'])){

if($_REQUEST['tfecha']=="emision")$tfecha = "chequetercero.FechaEmision";
if($_REQUEST['tfecha']=="vto")$tfecha = "chequetercero.FechaVto";
}

//periodo por defecto
$periodo="\n ".$tfecha." between date_sub(curdate(),INTERVAL 1 YEAR) AND CURDATE() AND \n";

if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
            $periodo=$tfecha."  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."' AND";

if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
        if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{ 
		$periodo="(".$tfecha."   BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."'  
		OR ".$tfecha."  BETWEEN '".$_REQUEST['fechaDesdeDos']."' AND '".$_REQUEST['fechaHastaDos']."' )  
		AND";
			
			}


			
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
$proveedores=Array();
$oes=Array();
$banderaCliente='No';
$banderaProveedor='No';
foreach($arrFiltros as $indice => $valor)
{
		

	if(isset($valor))
	{
	switch($indice){
		case "proveedor":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		
		if(isset($matches['digito']))$proveedores[]=$matches['digito'];
		if(!isset($matches['digito']))$banderaProveedor='MostrameTodos';

		}

		break;
		
		case "cliente":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		if(isset($matches['digito']))$clientes[]=$matches['digito'];
		if(!isset($matches['digito']))$banderaCliente='MostrameTodos';

		}

		break;
		
		
		case "nombre_oe":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		//$articulos[]=$matches['digito'];
		//if(isset($matches['digito']))$clientes[]=$matches['digito'];
		//if(!isset($matches['digito']))$banderaProveedor='MostrameTodos';

		}

		break;
		
	
			}
		
		
		
		 
	}
}

if($banderaProveedor!="MostrameTodos")
if(sizeof($proveedores)>0)
$where .= "\n AND proveedor.Codigo IN ('".implode("','",$proveedores)."') ".PHP_EOL;

if($banderaCliente!="MostrameTodos")
if(sizeof($clientes)>0)
$where .= "\n AND cliente.Codigo IN ('".implode("','",$clientes)."') ".PHP_EOL;

if(isset($_REQUEST['estadodecheque']))
{
if($_REQUEST['estadodecheque'] == "Depositado")$where .= "\n AND chequetercero.Depositado='Si'".PHP_EOL;	
if($_REQUEST['estadodecheque'] == "Entregado")$where .= "\n AND chequetercero.Entregado='Si'".PHP_EOL;	
if($_REQUEST['estadodecheque'] == "En cartera")$where .= "\n AND chequetercero.Encartera='Si'".PHP_EOL;	
if($_REQUEST['estadodecheque'] == "Rechazado")$where .= "\n AND chequetercero.Rechazado='Si'".PHP_EOL;	



}


//if(sizeof($oes)>0)
//$where .= "\n AND otro_egreso.nombre_oe IN ('".implode("','",$oes)."') ".PHP_EOL;

/////////////////////////////////	

			
			
			
$vectorsito= Array();
    

    
if(isset($_REQUEST['listarPor']))$listarPor=$_REQUEST['listarPor'] ;

//if(is_numeric($cuentabanco))$where = $where." AND (librobanco.CodCuenta='".$cuentabanco."' or librobanco.CodCuentaDestino='".$cuentabanco."')";

require_once "tiporesumen.php";


$sql="SELECT '-', 
chequetercero.NroCheque, 
 IF(banco.Nombre IS NULL,' - ',banco.Nombre) as Banco, 
 IF(chequetercero.Librador IS NULL,' - ',chequetercero.Librador) as Librador,
 DATE_FORMAT( chequetercero.FechaEmision,'".$rango."')  as 'Fecha Emision',
  DATE_FORMAT( chequetercero.FechaCobro ,'".$rango."')  as 'Fecha Cobro',
  DATE_FORMAT( chequetercero.FechaVto ,'".$rango."')  as 'Fecha Vto.',
FORMAT( chequetercero.Importe, 2, 'es_AR') as Importe,
IF(proveedor.Nombre IS NULL,' - ',proveedor.Nombre) as Proveedor,  
 If(chequetercero.Encartera='Si','En cartera',
 If (chequetercero.Rechazado='Si','Rechazado',
 If (chequetercero.Depositado='Si','Depositado',
 If(chequetercero.Entregado='Si','Entregado a proveedor','Ninguno')))) as Estado
 FROM chequetercero 
 left join  banco banco ON  chequetercero.CodBanco=banco.CodBanco  
 left join proveedor proveedor on  chequetercero.CodProveedor=proveedor.Codigo
 WHERE 
     ".$periodo." chequetercero.anulado = 'No' ".$where." 
 ORDER BY ".$tfecha.",chequetercero.CodProveedor,chequetercero.CodBanco
";

 /* Reporte 112
 Fecha de emision --> chequetercero.FechaEmision
Fecha de Cobro --> chequetercero.FechaCobro


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
       die(json_encode( 'Ha sucedido un error inexperado en la desconexion de la base de datos'));
        }   

    return $close;
}





if(!isset($sql))die("SQL No Terminado");




$_SESSION["SQL"]=$sql;
$_SESSION["INFORMESQL"]=$sql;
$_SESSION["IVARIABLES"]=$_REQUEST;

$_SESSION["titulo"]='Banco seguimiento de cheques';
if(isset($_REQUEST['titulo']))$_SESSION["titulo"]=$_REQUEST['titulo'];
      $jsonFinal = '[{"REQUEST":'.json_encode($_REQUEST).',"SQL":'.json_encode($sql).'}]';
     
		echo $jsonFinal;
		//echo $sql;

?>
