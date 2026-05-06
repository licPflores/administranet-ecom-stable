<?php
ini_set("display_errors",0);
setlocale(LC_MONETARY, 'es_AR');
//header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
$nCampos = Array();
require_once("../sesion.inc.php");

$sql="";

if(isset($_REQUEST['s']))$bus=$_REQUEST['s'];
if(!isset($_REQUEST['s']))exit;
$queCajas="";
if(isset($objVendedor)){
    if($_SESSION["inf_gerenciales"]=="No" || $objVendedor->id_puesto!=1){
         $queCajas .=" AND caja_abm.id_caja IN (".$objVendedor->id_caja.",".$objVendedor->id_caja_cheque.") ";       
    }            
}

switch ($bus) {
    case 0:
        $sql="SELECT caja_abm.*,sucursales.id_sucursal,sucursales.nombre_sucursal as nomb_sucursal FROM caja_abm,sucursales "
            . "WHERE (tipo_caja = 'Acumulativa') And caja_abm.id_sucursal = sucursales.id_sucursal {$queCajas} ORDER BY nombre_caja";

        break;
    case 1:
        $sql="SELECT caja_abm.*,sucursales.id_sucursal,sucursales.nombre_sucursal as nomb_sucursal FROM caja_abm,sucursales "
            . "WHERE tipo_caja = 'Fondo Fijo' And caja_abm.id_sucursal = sucursales.id_sucursal {$queCajas} ORDER BY nombre_caja";
        break;
    case 2:
        $sql= "SELECT caja_abm.*,sucursales.id_sucursal,sucursales.nombre_sucursal as nomb_sucursal FROM caja_abm,sucursales "
            . "WHERE (tipo_caja = 'Punto de Venta') And caja_abm.id_sucursal = sucursales.id_sucursal {$queCajas} ORDER BY nombre_caja";
        break;  
	case 3:
        $sql=  "SELECT caja_abm.*,sucursales.id_sucursal,sucursales.nombre_sucursal as nomb_sucursal FROM caja_abm,sucursales "
                . "WHERE (tipo_caja = 'Cheque' OR tipo_caja = 'Acumulativa Cheque') And caja_abm.id_sucursal = sucursales.id_sucursal {$queCajas} ORDER BY nombre_caja";
        break; 
	case 4:
        $sql= "SELECT caja_abm.*,sucursales.id_sucursal,sucursales.nombre_sucursal as nomb_sucursal FROM caja_abm,sucursales "
                . "WHERE tipo_caja = 'Acumulativa Cheque' And caja_abm.id_sucursal = sucursales.id_sucursal {$queCajas} ORDER BY nombre_caja";
        break;
	case 5:
        $sql= "SELECT caja_abm.*,sucursales.id_sucursal,sucursales.nombre_sucursal as nomb_sucursal FROM caja_abm,sucursales "
                . "WHERE tipo_caja = 'Tarjeta' And caja_abm.id_sucursal = sucursales.id_sucursal {$queCajas} ORDER BY nombre_caja";
        break;
    default:
       echo "No tengo caja para esta opcion: ".$bus;
	   exit;
}


	

function connectDB1(){
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


    //Creamos la conexión con la función anterior
    $conexion = connectDB1();
	
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    $result = mysqli_query($conexion, $sql);
	if($result===false)
	{
		echo "Sin Resultados".PHP_EOL;
		echo mysqli_error($conexion).PHP_EOL.$sql;
		exit;
		
	}//		die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

		$contar = mysqli_num_rows($result);
    

	
	?>

<label for="idCaja" class="parametros">Caja:
<select name="idCaja" id="idCaja">
  <?php
		//print_r(mysqli_fetch_field($result));/*
	while ($fila = mysqli_fetch_object($result)) {
    $nombre	= $fila->nombre_caja;
    $idCaja	= $fila->id_caja;
    
	print "<option  value=".$idCaja.">".htmlentities($nombre)."</option>".PHP_EOL;
	}
	?></select>
		</label>
		

