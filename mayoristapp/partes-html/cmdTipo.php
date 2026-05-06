<?php
ini_set("display_errors",1);

$nCampos = Array();
require_once("sesion.inc.php");
//require_once '../includes/includes.inc.php';


$todas=2;
if(isset($_REQUEST['CajasTodas'])===true)$todas=1;


$sql="";




//Creamos la conexión con la función anterior

    $conexion = $connV;//connectDB();
	
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

		?>
		
<div class='panelesBloqueInforme'>
<div class="titulo">Datos</div>
<div class="control">
<label for="Supra" class="parametros">Tipo:   </label>		
<select name="Supra" id="Supra">
    <option value="">-tipo de caja-</option>
<?php     
$queCajas="";
if(isset($objVendedor) && ($_SESSION["inf_gerenciales"]=="No" || $objVendedor->id_puesto!=1)):?>
        
<option value="2">Efectivo</option>
<option value="3">Cheque</option>
                
<?php else:?>
<option value="0">Acumulativa</option>
<option value="1">Fondo Fijo</option>
<option value="2">Efectivo Pto Vta.</option>
<option value="3">Cheque</option>
<option value="4">Acumulativa Cheque</option>
<option value="5">Tarjeta</option>
<option value="6">Otro medio de cobro</option>
<option value="7">Acumulativa Otro Medio de Cobro</option>
<?php endif;?>
</select>


<div id="pieza"></div>



<script>
$( "#Supra" ).change(function() {
  let seleccion = $('#Supra').val();

 $('#pieza').load("moto/ncaja.php?s="+seleccion);
 
 //tipo de caja
 $('#uuuu').val(seleccion);
 $('#idCaja').show('slow');
});

</script>

  

 
    </div>
    </div>