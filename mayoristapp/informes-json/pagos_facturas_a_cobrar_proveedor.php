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
            $periodo="`op_factura`.`Fecha`  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."' AND";
//9-06&fechaDesdeDos=&fechaHastaDos=
if(isset($_REQUEST['fechaDesde']) && isset($_REQUEST['fechaHasta']))
    if($_REQUEST['fechaDesde']!="" && $_REQUEST['fechaHasta']!="")
        if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{ 
		$periodo="(`op_factura`.`Fecha`  BETWEEN '".$_REQUEST['fechaDesde']."' AND '".$_REQUEST['fechaHasta']."'  
		OR `op_factura`.`Fecha`  BETWEEN '".$_REQUEST['fechaDesdeDos']."' AND '".$_REQUEST['fechaHastaDos']."' )  
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
		
	
			}
		
		
		
		 
	}
}


if(sizeof($proveedores)>0)
$where .= "\n AND proveedor.Codigo IN ('".implode("','",$proveedores)."') ".PHP_EOL;

if(sizeof($articulos)>0)
$where .= "\n AND articulo.IDArt IN ('".implode("','",$articulos)."') ".PHP_EOL;

/////////////////////////////////




$puntodeventa='';
$vectorsito= Array();

    
    
    
if(isset($_REQUEST['listarPor']))$listarPor=$_REQUEST['listarPor'] ;


$tipoResumen="mes";
if(isset($_REQUEST['tipoResumen']))
    {
    switch ($_REQUEST['tipoResumen']) {
    case "dia":
            //// por dia

            
   $sql="SELECT ROUND(op_factura.id_op_factura) AS Codigo,
 CONCAT( `proveedor`.`Nombre`,' (', `proveedor`.`Codigo`,')') AS NCLIENTE,
 DATE_FORMAT(`op_factura`.`Fecha`,'%d-%m-%Y') AS FECHITA, 
 ROUND(SUM(
 IF(ISNULL(cuentaproveedor.ImportePago),
 IF(ISNULL(cuentaproveedor.ImporteCompra),0,op_factura.Saldo)
 ,op_factura.Saldo*(-1))
 ),".$decimales.") AS SALDO
 FROM   (((`op_factura` `op_factura` INNER JOIN `cuentaproveedor` `cuentaproveedor` ON `op_factura`.`CodigoMovimiento`=`cuentaproveedor`.`CodigoMovimiento`) INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo`=`proveedor`.`Codigo`) INNER JOIN `provincia` `provincia` ON `proveedor`.`CodProvincia`=`provincia`.`CodProvincia`) INNER JOIN `departamento` `departamento` ON `proveedor`.`IDDepartamento`=`departamento`.`IDDepartamento`
 WHERE  ".$periodo." 
 `op_factura`.`TipoComprobante`<>'INIC' 
 And op_factura.Saldo <> 0 
 AND op_factura.Anulado = 'No' And op_factura.Estado = 'N/Canc'  ".$where."
 GROUP BY NCLIENTE,FECHITA
 ORDER BY FECHITA,NCLIENTE";    

array_push($nCampos,array("title"=>ucwords("id"),"data"=>"id","visible"=>0));
array_push($nCampos,array("title"=>ucwords("Nombre"),"data"=>"Nombre","visible"=>1));
        $start    = (new DateTime($_REQUEST['fechaDesde']));
        $end      = (new DateTime($_REQUEST['fechaHasta']));
        $interval = DateInterval::createFromDateString('1 day');
        $period   = new DatePeriod($start, $interval, $end);

			$cortador=0;
            foreach ($period as $dt) {
				$cortador=$cortador+1;
                
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
            }
			
			if($cortador>0){
			$dt->add(new DateInterval("P1D"));
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
						}else{
							//Pongo la fecha porque no hay intervalo
						array_push($nCampos, array("title" => ucwords($_REQUEST['fechaDesde']), "data" => $_REQUEST['fechaDesde'], "visible" => 1));	
						}

		
		
		    if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{
				$start    = (new DateTime($_REQUEST['fechaDesdeDos']));
				$end      = (new DateTime($_REQUEST['fechaHastaDos']));
				$interval = DateInterval::createFromDateString('1 day');
				$period   = new DatePeriod($start, $interval, $end);

			$cortador=0;
            foreach ($period as $dt) {
				$cortador=$cortador+1;
                
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
            }
			
			if($cortador>0){
			$dt->add(new DateInterval("P1D"));
                array_push($nCampos, array("title" => ucwords($dt->format("d-m-Y")), "data" => $dt->format("d-m-Y"), "visible" => 1));
						}else{
							//Pongo la fecha porque no hay intervalo
						array_push($nCampos, array("title" => ucwords($_REQUEST['fechaDesde']), "data" => $_REQUEST['fechaDesde'], "visible" => 1));	
						}
	
				
			}
		
		
		
        break;
    case "semana":
    //////////////////    echo "i es igual a 1";
        break;
        
            case "ano":
        // por ano
   $sql="SELECT ROUND(op_factura.id_op_factura) AS Codigo,
 CONCAT( `proveedor`.`Nombre`,' (', `proveedor`.`Codigo`,')') AS NCLIENTE,
 DATE_FORMAT(`op_factura`.`Fecha`,'%Y') AS FECHITA, 
 ROUND(SUM(
 IF(ISNULL(cuentaproveedor.ImportePago),
 IF(ISNULL(cuentaproveedor.ImporteCompra),0,op_factura.Saldo)
 ,op_factura.Saldo*(-1))
 ),".$decimales.") AS SALDO
 FROM   (((`op_factura` `op_factura` INNER JOIN `cuentaproveedor` `cuentaproveedor` ON `op_factura`.`CodigoMovimiento`=`cuentaproveedor`.`CodigoMovimiento`) INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo`=`proveedor`.`Codigo`) INNER JOIN `provincia` `provincia` ON `proveedor`.`CodProvincia`=`provincia`.`CodProvincia`) INNER JOIN `departamento` `departamento` ON `proveedor`.`IDDepartamento`=`departamento`.`IDDepartamento`
 WHERE  ".$periodo." 
 `op_factura`.`TipoComprobante`<>'INIC' 
 And op_factura.Saldo <> 0 
 AND op_factura.Anulado = 'No' And op_factura.Estado = 'N/Canc'  ".$where."
 GROUP BY NCLIENTE,FECHITA
 ORDER BY FECHITA,NCLIENTE";   
            
array_push($nCampos,array("title"=>ucwords("id"),"data"=>"id","visible"=>0));
array_push($nCampos,array("title"=>ucwords("Nombre"),"data"=>"Nombre","visible"=>1));
        $start    = (new DateTime($_REQUEST['fechaDesde']))->modify('first day of this month');
        $end      = (new DateTime($_REQUEST['fechaHasta']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 year');
        $period   = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
            array_push($nCampos,array("title"=>ucwords($dt->format("Y")),"data"=>$dt->format("Y"),"visible"=>1));
        }
		
			if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{
				$start    = (new DateTime($_REQUEST['fechaDesdeDos']));
				$end      = (new DateTime($_REQUEST['fechaHastaDos']));
				$interval = DateInterval::createFromDateString('1 year');
				$period   = new DatePeriod($start, $interval, $end);

				foreach ($period as $dt) {
					array_push($nCampos,array("title"=>ucwords($dt->format("Y")),"data"=>$dt->format("Y"),"visible"=>1));
				}	
				
			}
            
            
        break;
    default:
 
  // ya lo puse por defecto tipo resumen

   $sql="SELECT ROUND(op_factura.id_op_factura) AS Codigo,
 CONCAT( `proveedor`.`Nombre`,' (', `proveedor`.`Codigo`,')') AS NCLIENTE,
 DATE_FORMAT(`op_factura`.`Fecha`,'%m-%Y') AS FECHITA, 
 ROUND(SUM(
 IF(ISNULL(cuentaproveedor.ImportePago),
 IF(ISNULL(cuentaproveedor.ImporteCompra),0,op_factura.Saldo)
 ,op_factura.Saldo*(-1))
 ),".$decimales.") AS SALDO
 FROM   (((`op_factura` `op_factura` INNER JOIN `cuentaproveedor` `cuentaproveedor` ON `op_factura`.`CodigoMovimiento`=`cuentaproveedor`.`CodigoMovimiento`) INNER JOIN `proveedor` `proveedor` ON `cuentaproveedor`.`Codigo`=`proveedor`.`Codigo`) INNER JOIN `provincia` `provincia` ON `proveedor`.`CodProvincia`=`provincia`.`CodProvincia`) INNER JOIN `departamento` `departamento` ON `proveedor`.`IDDepartamento`=`departamento`.`IDDepartamento`
 WHERE  ".$periodo." 
 `op_factura`.`TipoComprobante`<>'INIC' 
 And op_factura.Saldo <> 0 
 AND op_factura.Anulado = 'No' And op_factura.Estado = 'N/Canc'  ".$where."
 GROUP BY NCLIENTE,FECHITA
 ORDER BY FECHITA,NCLIENTE";         
            
array_push($nCampos,array("title"=>ucwords("id"),"data"=>"id","visible"=>0));
array_push($nCampos,array("title"=>ucwords("Nombre"),"data"=>"Nombre","visible"=>1));
        $start    = (new DateTime($_REQUEST['fechaDesde']))->modify('first day of this month');
        $end      = (new DateTime($_REQUEST['fechaHasta']))->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period   = new DatePeriod($start, $interval, $end);

        foreach ($period as $dt) {
         array_push($nCampos,array("title"=>ucwords($dt->format("m-Y")),"data"=>$dt->format("m-Y"),"visible"=>1, 'className'=>'numeros'));
        }
           
			if(isset($_REQUEST['fechaDesdeDos']) && isset($_REQUEST['fechaHastaDos']))
            if($_REQUEST['fechaDesdeDos']!="" && $_REQUEST['fechaHastaDos']!="")
			{
				$start    = (new DateTime($_REQUEST['fechaDesdeDos']))->modify('first day of this month');
				$end      = (new DateTime($_REQUEST['fechaHastaDos']))->modify('first day of this month');
				$interval = DateInterval::createFromDateString('1 month');
				$period   = new DatePeriod($start, $interval, $end);

				foreach ($period as $dt) {
					array_push($nCampos,array("title"=>ucwords($dt->format("m-Y")),"data"=>$dt->format("m-Y"),"visible"=>1));
				}	
				
			}

}
    }




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

function getArraySQL($sql){
    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	global $nCampos;
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

    $rawdata = array(); //creamos un array

    //guardamos en un array multidimensional todos los datos de la consulta
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

            //Si tengo valor lo acumulo en Totales
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
$_SESSION["titulo"]='Pagos facturas a Cobrar por Proveedor';
if(isset($_REQUEST['titulo']))$_SESSION["titulo"]=$_REQUEST['titulo'];
        $vv = '[{"columns":'.json_encode($nCampos).',"data":'.json_encode($myArray).',"footer":'.json_encode($Totales).',"REQUEST":'.json_encode($_REQUEST).',"SQL":'.json_encode($sql).',"datos":'.json_encode($mivector).',"datosFormateado":'.json_encode($vectorBIS).',"totalesFormateado":'.json_encode($TotalesBIS).'}]';
		echo $vv;
?>
