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

    if(!$result = mysqli_query($conexion, "  SET @T=0;")) die(mysqli_error($conexion).PHP_EOL."  SET @T=0;"); //si la conexión cancelar programa
    if(!$result = mysqli_query($conexion, $sql)) die(mysqli_error($conexion).PHP_EOL.$sql); //si la conexión cancelar programa

		$contar = mysqli_num_rows($result);
    $contar--;
	//print_r(mysqli_fetch_field($result));/*

?><html>
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
//        echo "<pre>";
//        print_r($_SESSION["IVARIABLES"]);
        $req=$_SESSION["IVARIABLES"];
        $start = (new DateTime($req['fechaDesde']));
        $end = (new DateTime($req['fechaHasta']));
//        echo $start->format('d/m/Y');
//        echo $end->format('d/m/Y');
//        print_r($req);
//        echo "</pre>";
        $titulo=$_SESSION["titulo"];
        $nombreArchivo =$_SESSION['titulo']."_". date('d-m-Y-h-i')."_".$objVendedor->nombre_usuario .'_' .$objVendedor->apellido_usuario;
        if(isset($req["tipoCajaTitulo"])){
            echo "<center>\t<h3>Tipo: <strong>".htmlentities($req["tipoCajaTitulo"])."</strong> - ";
            echo "Caja: <strong>".htmlentities($req["nombreCajaTitulo"])."</strong></h3>\t</center>\n";
            $titulo .='\n Tipo: '.strtoupper($req["tipoCajaTitulo"]);
            $titulo .=' - Caja: '.$req["nombreCajaTitulo"];
            $nombreArchivo =$_SESSION['titulo']."_".$req["tipoCajaTitulo"]."_". date('d-m-Y-h-i')."_".$objVendedor->nombre_usuario .'_' .$objVendedor->apellido_usuario;
        }
}

?>
    <table id="tabla" width="100%" class="display">
	<thead>
		<tr>
			<?php
				while ($property = mysqli_fetch_field($result)) {
				$nombre	= $property->name;
				echo '<th>'.htmlentities($nombre)."</th>\t";
				}
			?>
		</tr>

	</thead>
	<tbody>
		
			<?php
			 while($row = $result->fetch_array(MYSQLI_NUM)){
					echo "<tr>\n";

                            for($x=0;$x<sizeof($row);$x++){
                                $class="";
                                // analizo el tipo de campo para saber si lo hago nowrap y /o alineado a la derecha o izquierda.
                                
                                
                                    if(strlen($row[$x])<=13){
                                         $class .='dt-nowrap ';
                                    }
                                    $numero= str_replace(".", "", $row[$x]);
                                    $numero= str_replace(",",".", $numero);
                                    if(is_numeric($numero)){
                                        $class .='dt-right';
                                    }else{
                                        $class .='dt-left';
                                    }
                                         
                                    
                                    
                                
                                echo '<td class="'.$class.'">'.htmlentities($row[$x])."</td>\t";
                                            
                            }                
					echo "</tr>".PHP_EOL;
			}
			  
			?>
		
	</tbody>
</table>
<script>
//$('#tabla').css({display: "none"});
$(document).ready( function () {
  var tt = $('#tabla').DataTable({
            "language": {
                "url": "../Spanish.json"
            },
             dom: 'plBfrti',
            buttons: [
           
            {
                extend: 'pdfHtml5',
                title: "<?php echo $titulo;?>",
                footer: true, 
                pageSize: 'A4',
                filename: "<?php echo $nombreArchivo;?>",
                orientation: 'landscape',
                messageTop: "Periodo: <?php echo $start->format('d/m/Y')." al ".$end->format('d/m/Y'); ?> \n Emitido el: <?php echo date('d/m/Y h:i');?> \n Generado por: <?php echo $objVendedor->nombre_usuario .' ' .$objVendedor->apellido_usuario;?>",
                exportOptions: { orthogonal: 'export' }
            }
        ]
           
  } );
						
    tt.on( 'order.dt search.dt', function () {
                            tt.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                                cell.innerHTML = i+1;
                            } );
    } ).draw(); 

$('#tabla').show(4000);	
						
   
	
	
	
} );
</script>
<pre><?php //echo $sql; ?></pre>
</body>
</html>