<?php
ini_set("display_errors",1);
setlocale(LC_MONETARY, 'es_AR');
require_once("../sesion.inc.php");

$sql="";
if(isset($_SESSION['SQL'])===true)$sql=$_SESSION['SQL'];

if(empty($sql))$sql="SELECT IDArt AS Codigo , NombreArticulo AS NCLIENTE, PrecioCosto AS SALDO
 FROM articulo  order by RAND() limit 40;";
//$sql="select * from marca limit 100";
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


    //Creamos la conexión con la función anterior
    $conexion = connectDB();
	
	if(!isset($sql))die("No tengo SQL");

    //generamos la consulta

        mysqli_set_charset($conexion, "utf8"); //formato de datos utf8

    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

		$contar = mysqli_num_rows($result);
    $contar--;
	//print_r(mysqli_fetch_field($result));/*

	
if(isset($_SESSION['columnas']))$columnas=$_SESSION['columnas'];	
if(!isset($_SESSION['columnas']))die('No tengo columnas');	
	
	
if(isset($_SESSION['datosFormateado']))$datosFormateado=$_SESSION['datosFormateado'];	
if(!isset($_SESSION['datosFormateado']))die('No tengo datosFormateado');
	
if(isset($_SESSION['totalesFormateado']))$totalesFormateado=$_SESSION['totalesFormateado'];	
if(!isset($_SESSION['totalesFormateado']))die('No tengo totalesFormateado');
	



if(isset($_SESSION['datos']))$datos=$_SESSION['datos'];	
if(!isset($_SESSION['datos']))die('No tengo datos');
	
if(isset($_SESSION['footer']))$footer=$_SESSION['footer'];	
if(!isset($_SESSION['footer']))die('No tengo footer');
	
?>
<!DOCTYPE html>
<html>
<head>
 
    <link rel="stylesheet" type="text/css" href="../_css/main_styles.css">   
<link rel="stylesheet" type="text/css" href="../_css/tablas.css">

<link rel="stylesheet" type="text/css" href="../../sistema/_scripts/datatables/datatables.min.css">

<script type="text/javascript" charset="utf8" src="../../sistema/_scripts/jquery-3.3.1.min.js"></script>
<script type="text/javascript" charset="utf8" src="../../sistema/_scripts/datatables/datatables.min.js"></script>

<script type="text/javascript" src="../../sistema/_scripts/datatables/Buttons-1.5.4/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="../../sistema/_scripts/datatables/Buttons-1.5.4/js/buttons.flash.min.js"></script>
<script type="text/javascript" src="../../sistema/_scripts/datatables/JSZip-2.5.0/jszip.min.js"></script>
<script type="text/javascript" src="../../sistema/_scripts/datatables/Buttons-1.5.4/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="../../sistema/_scripts/datatables/Buttons-1.5.4/js/buttons.print.min.js"></script>
<style>
#tabla  {display: none;}

</style>
</head>
<body>
<?php 
if(isset($_SESSION['titulo'])){
	echo "<center>\t<h3>".htmlentities($_SESSION['titulo'])."</h3>\t</center>\n";
}



?>
<div id="contiene-tabla">
<!-- Botones -->
<div id="botonera" class="dataTables_wrapper dt-buttons"></div>
<!-- Fin de  Botones -->
<table id="tabla" width="100%" class="nowrap">
	<thead>
		<tr>
			<?php

				
				foreach($columnas as $indice => $valor){
					
					echo "<th>".htmlentities($valor['title'])."</th>\t";
					
				}
				
				echo "\t<th>SubTotal</th>";
				echo "\t<th>%</th>\n";
				
				
			
				
			?>
		</tr>

	</thead><?php
				
				foreach($datos as $indice => $valor){
					
				$rr=0;
				$acu=0;	
				//echo "<pre>";
				foreach($columnas as $subindice => $svalor){
						
					if(isset($valor[$svalor['title']]) && $rr>0)	
						{//echo "\t\t<td>".$valor[$svalor['title']]."</td>\n";
						//print_r($valor[$svalor['title']]);
						$acu = $acu+$valor[$svalor['title']];
						}
					
					//if(!isset($valor[$svalor['title']]))	echo "\t\t<td>\t</td>\n";
					$rr++;	
				}
				$subtotales[]=$acu;
				//echo "</pre>";
				$acu=0;
				}
				
				$totalisima=0;
				if(isset($subtotales))
				if(sizeof($subtotales)>0)
				foreach($subtotales as $valor){
					$totalisima+=$valor;
				}
			  ?><tbody>
		
			<?php
			$fmt = new NumberFormatter('es_AR', NumberFormatter::CURRENCY); 
				$contador=0;
				$TotalTotales=1;
				foreach($datosFormateado as $indice => $valor){
					
						echo "\n<tr>\n";
						$acu=0;//acumulador
						
						
					foreach($columnas as $subindice => $svalor){
						
						if(isset($valor[$svalor['title']])){
							$clasita="";
							
							$pesos=$valor[$svalor['title']];
							if(empty($valor[$svalor['title']])===false)
							//if(preg_match('/\$?[0-9]*(\,[0-9][0-9])/',$valor[$svalor['title']]))$clasita=' class="dt-right "';
							if(preg_match('/\$?[0-9]*(\,[0-9][0-9])/',$valor[$svalor['title']]))$clasita=' class="dt-right"';
							echo "<td".$clasita.">".htmlentities($pesos)."</td>\n";
						}
						//if(isset($valor[$svalor['title']]))	echo "\t\t<td ".$clasita.">".htmlentities($valor[$svalor['title']]);
						if(!isset($valor[$svalor['title']]))	echo "<td></td>\n";

						
					}
						
						echo '<td class="dt-right"> '. $fmt->formatCurrency($subtotales[$contador],'ARS')."</td>\n";
						$TotalTotales+=$subtotales[$contador];
						if($totalisima>0){
						$porcentaje = $subtotales[$contador]*100;
						$porcentaje = $porcentaje/$totalisima;
						$porcentaje = round($porcentaje,2);
						}else
							{
								$porcentaje=0;
							}
						echo '<td class="dt-right"> ';
						echo htmlentities( $porcentaje.'%');
						echo "</td>\t";
						echo "\n</tr>\n\n";
						$contador = $contador+1;
						
				}
			  
			?>
		
	</tbody>
	<tfoot>
		<tr><th><!-- Vacio --></th><?php
						$TotalContador=0;
						foreach($totalesFormateado as $indice => $valor){
					
						echo "<th class='dt-right'>".$totalesFormateado[$indice]."<br>\n";
						if (!preg_match('/total/i',$totalesFormateado[$indice]))
							{
								$scontador=0;
								for($n=0;$n<sizeof($datos);$n++)
								{
									//echo "indice $indice ";
									if($datos[$n][$indice]>0)$scontador=$scontador+1;
								}
								echo $scontador;
								$TotalContador+=$scontador;
							}else
								echo "Registros:";
						echo "</th>\n";
					
						}
						echo "<th class='dt-right'>". $fmt->formatCurrency($TotalTotales,'ARS')."<br>".$TotalContador."</th>\n";
		?></tr>
	
		
		
	</tfoot>
</table>
</div>
<!-- Tabla oculta para botones -->
<div id="Ocultame">
<table id="tablaOculta" width="100%" style="display:none;">
	<thead>
		<tr>
			<?php

				
				foreach($columnas as $indice => $valor){
					
					echo "<th> ".$valor['title'];
					
					echo "</th>\t";
					
				}
				
				
			
				
			?>
		</tr>

	</thead>
	<tbody>
		
			<?php
				foreach($datos as $indice => $valor){
					
					echo "<tr>";
					
				foreach($columnas as $subindice => $svalor){
					
				if(isset($valor[$svalor['title']]))	echo "\t\t<td> ".$valor[$svalor['title']]."</td>\n";
				if(!isset($valor[$svalor['title']]))	echo "\t\t<td>\t</td>\n";
					
				}
					echo "</tr>\n";
				}
			  
			?>
		
	</tbody>
	<tfoot>
		<tr><th><!-- Vacio --></th><?php
						foreach($footer as $indice => $valor){
					
						echo "<th>".$footer[$indice]."</th>\n";
					
						}
		?></tr>
	</tfoot>
</table>
</div>

<script>
$(document).ready( function () {
    $("#botonera").hide(30);
    var tabla = $('#tabla');
	//tabla.css('display','none');
     var tt=tabla.DataTable({
            "columnDefs":  {
                                "searchable": false,
                                "orderable": false,
                                "targets": -1,
								 
                            } ,
            "language": {
                "url": "../Spanish.json"
            }
        });
    tt.on( 'order.dt search.dt', function () {
                            tt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                                cell.innerHTML = i+1;
                            } );
    } ).draw(); 
	tabla.show(3500);
	
	
	
	/*     var tt=tabla.DataTable({
            "columnDefs":  {
                                "searchable": false,
                                "orderable": false,
                                "targets": -1,
								 className: 'dt-right'
                            } ,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json"
            }
        });
    tt.on( 'order.dt search.dt', function () {
                            tt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                                cell.innerHTML = i+1;
                            } );
    } ).draw(); */
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
   
	   //$("#contiene-tabla" ).show()
    tabla = $('#tablaOculta');
    tabla.DataTable({
            "language": {
                    "url": "../Spanish.json",
                    dom: 'Bfrtip',
                    buttons: [
       ,'excel'
    ],
            paging: false,
            searching: false,
            ordering:  false,
            "serverSide": false
            }
    });
	
//inicia botonera
               
 var buttons = new $.fn.dataTable.Buttons(tabla, {
     buttons: [
     
       'excelHtml5',
       'csvHtml5',
       'pdfHtml5',
	   
    ]
}).container().appendTo($('#botonera'));

$("#Ocultame").hide(300);
$("#botonera").show(3000);
// fin botonera	

//$('#tabla tfoot tr th').addClass("dt-right");
//$('#tabla tbody tr td').addClass("dt-right");



//$('#tabla tbody tr td').css("font-family","\'Arial\'");;
//$('#tabla').css("font-family","\'Arial\'");
var numCols = $('#tabla').dataTable().fnSettings().aoColumns.length;
numCols = numCols-1;
//alert("Columnas: "+numCols);
var table = $('#tabla').DataTable();
table.order( [ numCols, 'desc' ] ).draw();
} );
</script>
<pre><?php // echo $sql; ?></pre>
</body>
</html>