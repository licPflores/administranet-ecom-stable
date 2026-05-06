<?php
ini_set("display_errors",1);
//setlocale(LC_MONETARY, 'es_AR');

//header('Access-Control-Allow-Origin: *'); 
$nCampos = Array();
require_once("sesion.inc.php");

$todas=2;
if(isset($_REQUEST['CajasTodas'])===true)$todas=1;


$sql="SELECT cuenta_banco.CodCuenta,cuenta_banco.CodBanco,cuenta_banco.NroCuenta,banco.Nombre as NombreBanco, 
CONCAT(banco.Nombre,' (',cuenta_banco.NroCuenta,')') as Cuentaa
 FROM cuenta_banco,banco WHERE 
 cuenta_banco.CodBanco = banco.CodBanco AND banco.cuentaabierta = 'Si'";



//Creamos la conexión con la función anterior

    $conexion = $connV;//connectDB();
	
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

		$contar = mysqli_num_rows($result);
    $contar--;
	//print_r(mysqli_fetch_field($result));/*
	while ($property = mysqli_fetch_field($result)) {
    $nombre	= $property->name;
    $tipo	= $property->type;
	//print "Nombre: ".$nombre." Tipo: ".$tipo."<br>".PHP_EOL;
	$Campos[] =  $property->name;
	}



//			echo '<input type="radio" class="easyui-radiobutton" name="idCaja" value=0 title="Todas las Cajas"  label="Seleccionar todas las cajas" checked />'.PHP_EOL;
		?>
		
	

        <div class='panelesBloqueInforme'>      
            <div class="titulo">Datos</div>
<div class="control">
<label for="cuentaBanco" class="parametros">Cuenta Bancaria:</label>		
<select class="easyui-combobox" name="cuentaBanco" id="cuentaBanco">
        <option value="Nada">- selecionar una cuenta -</option>
	<?php
	while($row = mysqli_fetch_object($result))
	{
	echo '<option value="'.$row->CodCuenta.'">'.htmlentities( $row->Cuentaa).'</option>'.PHP_EOL;
	}
        ?>
</select>
    
    </div>
	
    </div>
	<?php
	require_once "estadodecheque.php";