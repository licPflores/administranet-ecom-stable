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

//periodo por defecto
$periodo="\n librobanco.Fecha between date_sub(curdate(),INTERVAL 1 YEAR) AND CURDATE() AND \n";

if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
            $periodo="librobanco.Fecha  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."' AND";

if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
        if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{ 
		$periodo="(librobanco.Fecha  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."'  
		OR caja.fecha BETWEEN '".$_REQUEST['fechaDesdeDos']."' AND '".$_REQUEST['fechaHastaDos']."' )  
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
$articulos=Array();
$proveedores=Array();
$oes=Array();
foreach($arrFiltros as $indice => $valor)
{
		

	if(isset($valor))
	{
	switch($indice){
		case "proveedor":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		$proveedores[]=$matches['digito'];

		}

		break;
		
		case "articulo":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		$articulos[]=$matches['digito'];

		}

		break;
		
		
		case "nombre_oe":
		
		for($x=0;$x<sizeof($valor);$x++)
		{
			preg_match('/\(cod:(?P<digito>\d+)\)/', $valor[$x], $matches);
		//	$where .= "  AND  cuentacliente.id_cuentacliente='" . $matches['digito']. "' ".PHP_EOL;
		$articulos[]=$matches['digito'];

		}

		break;
		
	
			}
		
		
		
		 
	}
}


if(sizeof($proveedores)>0)
$where .= "\n AND proveedor.Codigo IN ('".implode("','",$proveedores)."') ".PHP_EOL;

if(sizeof($articulos)>0)
$where .= "\n AND articulo.IDArt IN ('".implode("','",$articulos)."') ".PHP_EOL;

if(sizeof($oes)>0)
$where .= "\n AND otro_egreso.nombre_oe IN ('".implode("','",$oes)."') ".PHP_EOL;

/////////////////////////////////		
			
$vectorsito= Array();
 
if(isset($_REQUEST['listarPor']))$listarPor=$_REQUEST['listarPor'] ;

if(is_numeric($cuentabanco))$where = $where." AND (librobanco.CodCuenta='".$cuentabanco."' or librobanco.CodCuentaDestino='".$cuentabanco."')";

$tipoResumen="mes";
if(isset($_REQUEST['tipoResumen']))$tipoResumen=$_REQUEST['tipoResumen'];
    switch ($tipoResumen) {
case "dia":
//// por dia
$rango='%d-%m-%Y';
break;

case "semana":
//////////////////    echo "i es igual a 1";
$rango='%v %m-%Y';
break;

case "ano":
$rango='%Y';
break;

default:

$rango='%m-%Y';

 
}

 $sql="SELECT ' ' as id, 
 DATE_FORMAT( librobanco.Fecha,'".$rango."') AS Fecha,
 librobanco.TipoComp as 'Tipo Comp', 
 librobanco.detalle as 'Detalle',
 librobanco.Comprobante as Comprobante, 
FORMAT(if( librobanco.Debito is NOT NULL,librobanco.Debito,0), 2, 'es_AR') AS Debito,
FORMAT(if( librobanco.Credito is NOT NULL,librobanco.Credito,0), 2, 'es_AR') AS Credito,
FORMAT(librobanco.Saldo, 2, 'es_AR') AS Saldo,
IF(librobanco.conciliado = '1','Si','No') as Conciliado
 FROM   librobanco librobanco
 WHERE ".$periodo." 1=1 ".$where;    





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

$_SESSION["titulo"]='Banco libro Banco';
if(isset($_REQUEST['titulo']))$_SESSION["titulo"]=$_REQUEST['titulo'];
      $jsonFinal = '[{"REQUEST":'.json_encode($_REQUEST).',"SQL":'.json_encode($sql).'}]';
     
		echo $jsonFinal;
		//echo $sql;

?>
